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


# ---------------------------------------------------------------------------
# T-004 — parse_frontmatter, validate_frontmatter_fields, Diagnostic
# ---------------------------------------------------------------------------


class TestParseFrontmatter:
    def test_parse_frontmatter_ok(self, fixtures_dir: Path) -> None:
        text = (fixtures_dir / "valid_minimal.md").read_text(encoding="utf-8")
        fm, linha_fim = la.parse_frontmatter(text)
        assert fm["artefato"] == "spec"
        assert fm["fase"] == 2
        assert fm["dominio"] == ["software"]
        assert fm["schema_version"] == 1
        assert fm["requer"] == []
        assert linha_fim > 1

    def test_parse_frontmatter_missing(self, fixtures_dir: Path) -> None:
        text = (fixtures_dir / "no_frontmatter.md").read_text(encoding="utf-8")
        with pytest.raises(la.FrontmatterAusente) as exc:
            la.parse_frontmatter(text)
        assert "FRONTMATTER_AUSENTE" in str(exc.value)

    def test_parse_frontmatter_missing_closing_delimiter(self) -> None:
        text = "---\nartefato: spec\n\n# Sem fechamento"
        with pytest.raises(la.FrontmatterAusente):
            la.parse_frontmatter(text)

    def test_parse_frontmatter_yaml_invalid(self, fixtures_dir: Path) -> None:
        text = (fixtures_dir / "yaml_invalid.md").read_text(encoding="utf-8")
        with pytest.raises(la.YamlInvalido) as exc:
            la.parse_frontmatter(text)
        assert "YAML_INVALIDO" in str(exc.value)


class TestValidateFrontmatterFields:
    def test_validate_fields_ok(self) -> None:
        fm = {
            "artefato": "spec",
            "fase": 2,
            "dominio": ["software"],
            "schema_version": 1,
            "requer": [],
        }
        diags = la.validate_frontmatter_fields(fm, arquivo="x.md", linha_fm=5)
        assert diags == []

    def test_validate_fields_fase_null_allowed(self) -> None:
        """`fase: null` é válido (templates/adr.md usa)."""
        fm = {
            "artefato": "adr",
            "fase": None,
            "dominio": ["any"],
            "schema_version": 1,
            "requer": [],
        }
        assert la.validate_frontmatter_fields(fm, arquivo="x.md", linha_fm=5) == []

    def test_validate_fields_missing_required(self) -> None:
        fm = {
            # "artefato" ausente
            "fase": 2,
            "dominio": ["software"],
            "schema_version": 1,
            "requer": [],
        }
        diags = la.validate_frontmatter_fields(fm, arquivo="x.md", linha_fm=5)
        assert len(diags) == 1
        assert diags[0].codigo == "CAMPO_OBRIGATORIO_AUSENTE"
        assert "artefato" in diags[0].mensagem
        assert diags[0].nivel == "ERRO"

    def test_validate_fields_wrong_type(self) -> None:
        fm = {
            "artefato": "spec",
            "fase": 2,
            "dominio": ["software"],
            "schema_version": 1,
            "requer": "isto deveria ser lista",
        }
        diags = la.validate_frontmatter_fields(fm, arquivo="x.md", linha_fm=5)
        assert len(diags) == 1
        assert diags[0].codigo == "CAMPO_TIPO_INVALIDO"
        assert "requer" in diags[0].mensagem
        assert "list" in diags[0].mensagem

    def test_validate_fields_unknown_key_accepted(self) -> None:
        """FR-017: chaves extras são aceitas em M1 sem erro."""
        fm = {
            "artefato": "spec",
            "fase": 2,
            "dominio": ["software"],
            "schema_version": 1,
            "requer": [],
            "custom_field": "valor",
            "outra_chave_extra": ["a", "b"],
        }
        assert la.validate_frontmatter_fields(fm, arquivo="x.md", linha_fm=5) == []


# ---------------------------------------------------------------------------
# T-005 — main CLI com argparse integrando F1
# ---------------------------------------------------------------------------


class TestCLI:
    def _run(self, fixture: Path, capsys: pytest.CaptureFixture[str]) -> tuple[int, str, str]:
        code = la.main([str(fixture)])
        captured = capsys.readouterr()
        return code, captured.out, captured.err

    def test_cli_valid_minimal(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "valid_minimal.md", capsys)
        assert code == 0
        assert "OK" in out

    def test_cli_no_frontmatter(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "no_frontmatter.md", capsys)
        assert code == 1
        assert "FRONTMATTER_AUSENTE" in out

    def test_cli_yaml_invalid(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "yaml_invalid.md", capsys)
        assert code == 1
        assert "YAML_INVALIDO" in out

    def test_cli_missing_required(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "missing_required_field.md", capsys)
        assert code == 1
        assert "CAMPO_OBRIGATORIO_AUSENTE" in out
        assert "artefato" in out

    def test_cli_wrong_type(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "wrong_type.md", capsys)
        assert code == 1
        assert "CAMPO_TIPO_INVALIDO" in out
        assert "requer" in out

    def test_cli_unknown_frontmatter_key_accepted(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        """FR-017: chaves desconhecidas no front-matter não bloqueiam o lint."""
        code, out, _ = self._run(fixtures_dir / "unknown_key.md", capsys)
        assert code == 0
        assert "OK" in out

    def test_cli_not_found(
        self, tmp_path: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, _, err = self._run(tmp_path / "inexistente.md", capsys)
        assert code == 2
        assert "ARQUIVO_NAO_ENCONTRADO" in err

    def test_cli_not_md(
        self, tmp_path: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        path = tmp_path / "arquivo.txt"
        path.write_text("conteudo")
        code, _, err = self._run(path, capsys)
        assert code == 2
        assert "ARQUIVO_NAO_MARKDOWN" in err
