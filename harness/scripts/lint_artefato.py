"""Lint mínimo (M1) de artefatos SDD Markdown com front-matter YAML.

Valida:
    1. Front-matter YAML presente, parseável, com campos obrigatórios.
    2. (F2, M1) — seções declaradas em `requer:` existem no corpo.
    3. (F3, M1) — links relativos internos apontam para arquivos existentes.

Invariantes (ver constitution.md Camada 1):
    - Read-only: nunca modifica, cria, renomeia ou deleta arquivo.
    - Stateless: cada invocação é idempotente.
    - Zero rede, zero subprocess, zero eval/exec.
"""
from __future__ import annotations

import argparse
import json
import os
import re
import sys
from dataclasses import asdict, dataclass
from pathlib import Path
from typing import Any, Literal
from urllib.parse import unquote

import yaml


# ---------------------------------------------------------------------------
# Tipos de domínio
# ---------------------------------------------------------------------------


Nivel = Literal["ERRO", "WARN", "INFO"]


@dataclass(frozen=True)
class Diagnostic:
    """Um diagnóstico emitido pelo lint."""

    arquivo: str
    linha: int
    nivel: Nivel
    codigo: str
    mensagem: str


# Campos obrigatórios no front-matter (FR-002).
REQUIRED_FIELDS: dict[str, type | tuple[type, ...]] = {
    "artefato": str,
    "fase": (int, float, type(None)),  # `fase` pode ser null (ex: templates/adr.md)
    "dominio": list,
    "schema_version": int,
    "requer": list,
}


# ---------------------------------------------------------------------------
# Exceções
# ---------------------------------------------------------------------------


class LintArtefatoError(Exception):
    """Base de erros do lint. Diferencia erros de IO (exit 2) de erros de lint (exit 1)."""


class ArquivoNaoEncontrado(LintArtefatoError):
    """Caminho passado não existe no filesystem. Corresponde a exit 2."""


class ArquivoNaoMarkdown(LintArtefatoError):
    """Arquivo passado não termina em `.md`. Corresponde a exit 2."""


class FrontmatterAusente(LintArtefatoError):
    """Arquivo não começa com bloco `---...---`. Corresponde a exit 1."""


class YamlInvalido(LintArtefatoError):
    """Front-matter presente mas não parseável como YAML. Corresponde a exit 1."""


# ---------------------------------------------------------------------------
# Leitura de arquivo
# ---------------------------------------------------------------------------


_UTF8_BOM = "\ufeff"


def strip_bom(text: str) -> str:
    """Remove UTF-8 BOM do início do texto se presente."""
    if text.startswith(_UTF8_BOM):
        return text[len(_UTF8_BOM):]
    return text


def read_file(path: Path) -> str:
    """Lê um arquivo `.md` em UTF-8, removendo BOM se presente.

    Raises:
        ArquivoNaoEncontrado: se o caminho não existe.
        ArquivoNaoMarkdown: se o arquivo não termina em `.md`.
    """
    if not path.exists():
        raise ArquivoNaoEncontrado(f"ARQUIVO_NAO_ENCONTRADO: {path}")
    if path.suffix.lower() != ".md":
        raise ArquivoNaoMarkdown(f"ARQUIVO_NAO_MARKDOWN: {path}")
    raw = path.read_text(encoding="utf-8")
    return strip_bom(raw)


# ---------------------------------------------------------------------------
# Front-matter: parse + validação
# ---------------------------------------------------------------------------


def parse_frontmatter(text: str) -> tuple[dict[str, Any], int]:
    """Extrai e parseia o bloco YAML entre os primeiros dois `---`.

    Returns:
        (dados_yaml, linha_final_frontmatter) onde linha_final é 1-based e
        aponta para a linha do segundo `---` (fim do bloco).

    Raises:
        FrontmatterAusente: se o texto não começa com `---\\n` ou não há
            segundo `---` delimitador.
        YamlInvalido: se o bloco YAML falha ao parsear.
    """
    lines = text.split("\n")
    if not lines or lines[0].rstrip() != "---":
        raise FrontmatterAusente("FRONTMATTER_AUSENTE: arquivo não começa com `---`")

    # Encontra linha do segundo `---`
    end_line_idx = None
    for idx in range(1, len(lines)):
        if lines[idx].rstrip() == "---":
            end_line_idx = idx
            break
    if end_line_idx is None:
        raise FrontmatterAusente(
            "FRONTMATTER_AUSENTE: delimitador `---` de fechamento não encontrado"
        )

    yaml_block = "\n".join(lines[1:end_line_idx])
    try:
        data = yaml.safe_load(yaml_block)
    except yaml.YAMLError as exc:
        mark = getattr(exc, "problem_mark", None)
        linha_fm = (mark.line + 2) if mark is not None else 1  # +2 = +1 para 1-based, +1 para pular `---` de abertura
        raise YamlInvalido(
            f"YAML_INVALIDO: front-matter não parseável (linha {linha_fm}: {exc})"
        ) from exc

    if not isinstance(data, dict):
        raise YamlInvalido(
            "YAML_INVALIDO: front-matter deve ser um mapeamento (dict), não "
            f"{type(data).__name__}"
        )

    return data, end_line_idx + 1  # +1 para 1-based


def validate_frontmatter_fields(
    fm: dict[str, Any],
    *,
    arquivo: str,
    linha_fm: int,
) -> list[Diagnostic]:
    """Valida presença e tipos dos campos obrigatórios do front-matter.

    Retorna lista de Diagnostic. Lista vazia = sem erros. Não lança exceção.

    Chaves extras no front-matter são **aceitas** em M1 (FR-017).
    """
    diags: list[Diagnostic] = []

    for campo, tipo_esperado in REQUIRED_FIELDS.items():
        if campo not in fm:
            diags.append(
                Diagnostic(
                    arquivo=arquivo,
                    linha=linha_fm,
                    nivel="ERRO",
                    codigo="CAMPO_OBRIGATORIO_AUSENTE",
                    mensagem=f"campo obrigatório ausente no front-matter: `{campo}`",
                )
            )
            continue

        valor = fm[campo]
        if not isinstance(valor, tipo_esperado):
            tipo_nome = (
                tipo_esperado.__name__
                if isinstance(tipo_esperado, type)
                else " | ".join(t.__name__ for t in tipo_esperado)
            )
            diags.append(
                Diagnostic(
                    arquivo=arquivo,
                    linha=linha_fm,
                    nivel="ERRO",
                    codigo="CAMPO_TIPO_INVALIDO",
                    mensagem=(
                        f"campo `{campo}` deveria ser `{tipo_nome}`, "
                        f"recebido `{type(valor).__name__}`"
                    ),
                )
            )

    return diags


# ---------------------------------------------------------------------------
# Corpo Markdown: strip de code blocks, normalização, extração de headings (F2)
# ---------------------------------------------------------------------------


# Regex para captura de heading Markdown em 1 linha (níveis 1-6).
# Grupo 1 = hashes (##, ###, ...), Grupo 2 = texto do heading.
HEADING_RE = re.compile(r"^(#{1,6})\s+(.+?)\s*$")


# Inline code: conteúdo entre pares de backticks na mesma linha (``...``).
# Usado apenas para links (não afeta extract_headings, pois headings são
# line-level e raramente aparecem dentro de backticks inline).
_INLINE_CODE_RE = re.compile(r"`[^`\n]*`")


def strip_inline_code(text: str) -> str:
    """Remove conteúdo entre pares de backticks na mesma linha, preservando
    as quebras de linha.

    Aplicado **antes de `extract_links`** para evitar falso positivo em
    exemplos de documentação (ex.: `[x](./foo.md)` em texto explicativo
    sobre sintaxe Markdown — o link é decorativo, não real).

    Multi-backticks (``` dupla) não são tratados em M1 — raro em Markdown
    técnico; se surgir caso real, abrir ADR.
    """
    return _INLINE_CODE_RE.sub("", text)


def strip_code_blocks(text: str) -> str:
    r"""Remove conteúdo dentro de fenced code blocks (```...```) preservando
    número de linhas (substitui por linhas vazias).

    As linhas dos próprios delimitadores ``\`\`\``` são preservadas como vazias
    para manter a contagem de linhas consistente com o original.

    Aceita blocos com especificação de linguagem (```python, ```markdown, etc.).
    Em caso de bloco mal fechado (abertura sem fechamento), o comportamento é
    remover tudo até o fim do arquivo — documentado como [RISCO ASSUMIDO] de D-001.
    """
    lines = text.split("\n")
    out: list[str] = []
    in_block = False
    for line in lines:
        stripped = line.lstrip()
        if stripped.startswith("```"):
            out.append("")
            in_block = not in_block
            continue
        out.append("" if in_block else line)
    return "\n".join(out)


def normalize(s: str) -> str:
    """Normaliza string para comparação: collapse whitespace, trim, e converte
    travessão longo/curto (—, –) em dois hífens (--).

    Case-sensitive — templates usam o case literal declarado em `requer:`.
    """
    # Travessões unicode → ASCII double-hyphen
    s = s.replace("\u2014", "--").replace("\u2013", "--")
    # Collapse de whitespace (sequências → 1 espaço) + trim
    return " ".join(s.split()).strip()


def extract_headings(text: str) -> list[tuple[int, int, str]]:
    """Extrai todos os headings Markdown do texto.

    Retorna lista de `(linha_1based, nivel, texto_normalizado)` para cada
    heading. Espera `text` **após** `strip_code_blocks` — headings dentro
    de code blocks são naturalmente ignorados porque as linhas foram
    substituídas por vazio.

    Extrai **todos** os níveis (1-6); filtragem por nível fica para a
    validação de seções (que aceita 2 e 3 como válidos e reporta 4+ como
    `SECAO_OBRIGATORIA_NIVEL_INVALIDO`).
    """
    headings: list[tuple[int, int, str]] = []
    for idx, line in enumerate(text.split("\n"), start=1):
        m = HEADING_RE.match(line)
        if m:
            nivel = len(m.group(1))
            texto_norm = normalize(m.group(2))
            headings.append((idx, nivel, texto_norm))
    return headings


# Regex para extração de links Markdown inline: `[texto](target)`.
# Captura `target` completo; split de âncora (#) é feito depois.
# Evita matchar dentro de `![...](...)` (imagens) via lookbehind negativo.
LINK_RE = re.compile(r"(?<!\!)\[([^\]]+)\]\(([^)\s]+)\)")

# Prefixos que indicam link externo — ignorados pelo lint (FR-009).
_EXTERNAL_PREFIXES = ("http://", "https://", "mailto:", "ftp://", "ftps://")


def extract_links(
    text: str, source_path: Path
) -> list[tuple[int, str, Path]]:
    """Extrai links Markdown relativos internos do texto.

    Retorna lista de `(linha_1based, target_original, target_resolvido)`.
    Espera `text` **após** `strip_code_blocks` — links em fenced blocks são
    naturalmente ignorados (FR-010).

    Ignora:
    - Links externos (http/https/mailto/ftp) — FR-009.
    - Links para imagens `![...](...)` — via lookbehind negativo na regex.
    - Fragmentos puros (`#secao`) e links vazios.

    Para links com âncora (`arquivo.md#secao`), o target resolvido ignora a
    âncora — só o arquivo é validado (FR-008). Percent-encoding no caminho
    é decodificado via `urllib.parse.unquote` antes de resolver (R-005).

    `source_path` é usado para resolver caminhos relativos via `source_path.parent`.
    """
    results: list[tuple[int, str, Path]] = []
    base = source_path.parent

    for idx, line in enumerate(text.split("\n"), start=1):
        for match in LINK_RE.finditer(line):
            target = match.group(2).strip()
            if not target or target.startswith("#"):
                continue
            if target.lower().startswith(_EXTERNAL_PREFIXES):
                continue
            # Separar âncora; só o arquivo é validado
            file_part = target.split("#", 1)[0]
            if not file_part:
                continue
            # Decodifica percent-encoding no path (ex.: %20 → espaço)
            file_part_decoded = unquote(file_part)
            resolved = (base / file_part_decoded).resolve()
            results.append((idx, target, resolved))

    return results


def validate_links(
    links: list[tuple[int, str, Path]],
    *,
    arquivo: str,
) -> list[Diagnostic]:
    """Valida que cada link relativo interno aponta para arquivo existente.

    Retorna lista de Diagnostic com código `LINK_QUEBRADO` para cada link
    cujo target resolvido não existe no filesystem.
    """
    diags: list[Diagnostic] = []
    for linha, target, resolved in links:
        if not resolved.exists():
            diags.append(
                Diagnostic(
                    arquivo=arquivo,
                    linha=linha,
                    nivel="ERRO",
                    codigo="LINK_QUEBRADO",
                    mensagem=f"link aponta para arquivo inexistente: `{target}`",
                )
            )
    return diags


def _heading_matches_requer(heading_texto_norm: str, requer_norm: str) -> bool:
    """Prefix match controlado entre heading e item de `requer:`.

    Aceita: (a) match exato, ou (b) heading começa com `requer_norm + ' '`.
    O espaço separador evita falso positivo (`"A"` não bate `"Aa"`), mas
    aceita o caso comum `requer: "Decisão"` bater `## Decisão — registrada`
    (após normalização vira `"Decisão -- registrada"`, o `.startswith("Decisão ")`
    retorna True).
    """
    if heading_texto_norm == requer_norm:
        return True
    return heading_texto_norm.startswith(requer_norm + " ")


def validate_required_sections(
    requer: list[Any],
    headings: list[tuple[int, int, str]],
    *,
    arquivo: str,
    linha_fm: int,
) -> list[Diagnostic]:
    """Para cada item em `requer:`, verifica se existe heading nível 2-3 que bate.

    - Match válido (nível 2 ou 3) → sem diagnóstico.
    - Match apenas em nível ≥4 → `SECAO_OBRIGATORIA_NIVEL_INVALIDO` apontando linha
      do heading encontrado.
    - Nenhum match → `SECAO_OBRIGATORIA_AUSENTE` apontando linha do front-matter.

    Itens de `requer:` que não sejam strings são ignorados silenciosamente
    (já tratado como CAMPO_TIPO_INVALIDO no nível do container).
    """
    diags: list[Diagnostic] = []
    for item in requer:
        if not isinstance(item, str):
            continue
        req_norm = normalize(item)

        valid_match: tuple[int, int, str] | None = None
        invalid_level_match: tuple[int, int, str] | None = None
        for linha, nivel, texto in headings:
            if _heading_matches_requer(texto, req_norm):
                if nivel in (2, 3):
                    valid_match = (linha, nivel, texto)
                    break
                if invalid_level_match is None:
                    invalid_level_match = (linha, nivel, texto)

        if valid_match is not None:
            continue

        if invalid_level_match is not None:
            linha_inv, nivel_inv, _ = invalid_level_match
            diags.append(
                Diagnostic(
                    arquivo=arquivo,
                    linha=linha_inv,
                    nivel="ERRO",
                    codigo="SECAO_OBRIGATORIA_NIVEL_INVALIDO",
                    mensagem=(
                        f"seção obrigatória `{item}` encontrada em nível "
                        f"{nivel_inv} (esperado 2 ou 3)"
                    ),
                )
            )
        else:
            diags.append(
                Diagnostic(
                    arquivo=arquivo,
                    linha=linha_fm,
                    nivel="ERRO",
                    codigo="SECAO_OBRIGATORIA_AUSENTE",
                    mensagem=f"seção obrigatória não encontrada no corpo: `{item}`",
                )
            )
    return diags


# ---------------------------------------------------------------------------
# Orquestração (F1): lint de um artefato (sem validação de seções nem links ainda)
# ---------------------------------------------------------------------------


def lint_artefato(path: Path) -> list[Diagnostic]:
    """Executa o lint F1 (front-matter) + F2 (seções) sobre um artefato.

    Raises:
        ArquivoNaoEncontrado, ArquivoNaoMarkdown: erros de IO (exit 2 no caller).
    """
    text = read_file(path)
    arquivo = str(path)

    try:
        fm, linha_fm = parse_frontmatter(text)
    except FrontmatterAusente as exc:
        return [Diagnostic(arquivo=arquivo, linha=1, nivel="ERRO",
                           codigo="FRONTMATTER_AUSENTE", mensagem=str(exc))]
    except YamlInvalido as exc:
        return [Diagnostic(arquivo=arquivo, linha=1, nivel="ERRO",
                           codigo="YAML_INVALIDO", mensagem=str(exc))]

    diags = validate_frontmatter_fields(fm, arquivo=arquivo, linha_fm=linha_fm)

    # F2 e F3 compartilham o corpo pós-strip de code blocks
    body_stripped = strip_code_blocks(text)

    # F2: validação de seções `requer:` — só faz sentido se `requer` é lista válida.
    requer = fm.get("requer")
    if isinstance(requer, list):
        headings = extract_headings(body_stripped)
        diags += validate_required_sections(
            requer, headings, arquivo=arquivo, linha_fm=linha_fm
        )

    # F3: validação de links relativos internos
    # strip_inline_code antes de extract_links evita falso positivo em exemplos
    # de documentação dentro de backticks (ex.: `[x](./foo.md)` explicativo).
    body_for_links = strip_inline_code(body_stripped)
    links = extract_links(body_for_links, path)
    diags += validate_links(links, arquivo=arquivo)

    return diags


# ---------------------------------------------------------------------------
# Formatação de output (F3)
# ---------------------------------------------------------------------------


# ANSI color codes — aplicados inline; mantém `pyyaml` como única dep externa.
_ANSI_RESET = "\033[0m"
_ANSI_BOLD = "\033[1m"
_ANSI_COLOR: dict[str, str] = {
    "ERRO": "\033[31m",   # vermelho
    "WARN": "\033[33m",   # amarelo
    "INFO": "\033[36m",   # ciano
}


def supports_color(stream: Any = None) -> bool:
    """Decide se o stream aceita ANSI colors.

    Regra (FR-018): habilita cor se **todas** verdadeiras:
    - `NO_COLOR` env var ausente (convenção https://no-color.org/).
    - stream é TTY (`isatty()` True).

    `stream` default é `sys.stdout`. Passar explicitamente facilita testes.
    """
    if "NO_COLOR" in os.environ:
        return False
    if stream is None:
        stream = sys.stdout
    isatty = getattr(stream, "isatty", None)
    return bool(isatty and isatty())


_NIVEL_ORDER: dict[str, int] = {"ERRO": 0, "WARN": 1, "INFO": 2}


def _sort_diags(diags: list[Diagnostic]) -> list[Diagnostic]:
    """Ordena diagnósticos: erros antes de warnings, então por linha ascendente."""
    return sorted(diags, key=lambda d: (_NIVEL_ORDER.get(d.nivel, 99), d.linha))


def format_human(diags: list[Diagnostic], *, use_color: bool = False) -> str:
    """Formata diagnósticos para leitura humana.

    Formato: `<arquivo>:<linha>: [NIVEL] CODIGO mensagem`.
    Ordenação: erros antes de warnings, linha ascendente dentro de cada grupo.
    Retorna `"OK"` se a lista estiver vazia (sem cor).
    """
    if not diags:
        return "OK"

    lines: list[str] = []
    for d in _sort_diags(diags):
        if use_color:
            cor_nivel = _ANSI_COLOR.get(d.nivel, "")
            linha = (
                f"{d.arquivo}:{d.linha}: "
                f"{cor_nivel}[{d.nivel}]{_ANSI_RESET} "
                f"{_ANSI_BOLD}{d.codigo}{_ANSI_RESET} {d.mensagem}"
            )
        else:
            linha = f"{d.arquivo}:{d.linha}: [{d.nivel}] {d.codigo} {d.mensagem}"
        lines.append(linha)
    return "\n".join(lines)


def format_json(diags: list[Diagnostic]) -> str:
    """Formata diagnósticos como array JSON estável.

    Chaves estáveis (contrato Camada 1 §10): arquivo, linha, nivel, codigo, mensagem.
    `ensure_ascii=False` preserva UTF-8 nas mensagens em português.
    """
    return json.dumps(
        [asdict(d) for d in _sort_diags(diags)],
        ensure_ascii=False,
    )


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------


def _downgrade_to_warnings(diags: list[Diagnostic]) -> list[Diagnostic]:
    """Transforma todos os ERRO em WARN (FR-015, modo --warnings-only para E1)."""
    return [
        Diagnostic(
            arquivo=d.arquivo,
            linha=d.linha,
            nivel="WARN" if d.nivel == "ERRO" else d.nivel,
            codigo=d.codigo,
            mensagem=d.mensagem,
        )
        for d in diags
    ]


def main(argv: list[str] | None = None) -> int:
    """Entry point do CLI. Retorna código de saída (0=OK, 1=lint error, 2=IO error).

    Flags:
    - `--format {human,json}`: formato de saída (default human).
    - `--warnings-only`: trata erros como warnings e força exit 0 (rollout E1).
    - `--no-color`: desabilita cor ANSI na saída humana.
    """
    parser = argparse.ArgumentParser(
        prog="lint-artefato",
        description="Lint mínimo de artefatos SDD Markdown (M1).",
    )
    parser.add_argument("arquivo", type=Path, help="Caminho do arquivo .md a validar")
    parser.add_argument(
        "--format",
        choices=("human", "json"),
        default="human",
        help="Formato de saída (default: human).",
    )
    parser.add_argument(
        "--warnings-only",
        action="store_true",
        help="Trata erros como warnings e força exit 0 (rollout E1).",
    )
    parser.add_argument(
        "--no-color",
        action="store_true",
        help="Desabilita cor ANSI na saída humana.",
    )

    args = parser.parse_args(argv)

    try:
        diags = lint_artefato(args.arquivo)
    except ArquivoNaoEncontrado as exc:
        print(str(exc), file=sys.stderr)
        return 2
    except ArquivoNaoMarkdown as exc:
        print(str(exc), file=sys.stderr)
        return 2

    # FR-015: --warnings-only degrada ERRO → WARN e força exit 0.
    if args.warnings_only:
        diags = _downgrade_to_warnings(diags)

    # FR-013 / FR-014 / FR-018
    if args.format == "json":
        output = format_json(diags)
    else:
        use_color = (not args.no_color) and supports_color(sys.stdout)
        output = format_human(diags, use_color=use_color)
    print(output)

    if args.warnings_only:
        return 0

    # Exit 1 se houver qualquer ERRO; 0 caso contrário (diags com apenas WARN/INFO
    # não bloqueiam — comportamento razoável que F3 formaliza).
    has_error = any(d.nivel == "ERRO" for d in diags)
    return 1 if has_error else 0


def cli_entry() -> None:
    """Wrapper para entry point em pyproject.toml (sys.exit para código correto)."""
    sys.exit(main(sys.argv[1:]))


if __name__ == "__main__":
    cli_entry()
