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

from dataclasses import dataclass
from pathlib import Path
from typing import Any, Literal

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
