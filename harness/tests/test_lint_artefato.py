"""Testes de unidade e integração para harness.scripts.lint_artefato."""
from __future__ import annotations

from pathlib import Path

import pytest

from harness.scripts import lint_artefato as la


# ---------------------------------------------------------------------------
# T-003 — read_file, strip_bom, exceções de IO
# ---------------------------------------------------------------------------


class TestReadFile:
    def test_read_file_valid_utf8(self, fixtures_dir: Path) -> None:
        content = la.read_file(fixtures_dir / "valid_minimal.md")
        assert "artefato: spec" in content
        assert content.startswith("---")

    def test_read_file_with_bom(self, tmp_path: Path) -> None:
        path = tmp_path / "with_bom.md"
        path.write_bytes(b"\xef\xbb\xbf---\nartefato: spec\n---\n")
        content = la.read_file(path)
        assert not content.startswith("\ufeff")
        assert content.startswith("---")

    def test_read_file_not_found(self, tmp_path: Path) -> None:
        with pytest.raises(la.ArquivoNaoEncontrado) as exc:
            la.read_file(tmp_path / "inexistente.md")
        assert "ARQUIVO_NAO_ENCONTRADO" in str(exc.value)

    def test_read_file_not_md(self, tmp_path: Path) -> None:
        path = tmp_path / "arquivo.txt"
        path.write_text("conteudo")
        with pytest.raises(la.ArquivoNaoMarkdown) as exc:
            la.read_file(path)
        assert "ARQUIVO_NAO_MARKDOWN" in str(exc.value)
