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

from pathlib import Path


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
