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
# T-007 — strip_code_blocks, normalize, extract_headings (F2 unit tests)
# ---------------------------------------------------------------------------


class TestStripCodeBlocks:
    def test_strip_code_blocks_basic(self) -> None:
        text = "## A\n```\n## B\n```\n## C"
        out = la.strip_code_blocks(text)
        # Resultado preserva 5 linhas; linhas do bloco e do delimitador vazias.
        assert out.split("\n") == ["## A", "", "", "", "## C"]

    def test_strip_code_blocks_with_language(self) -> None:
        text = "## X\n```python\ndef foo(): pass\n```\n## Y"
        out = la.strip_code_blocks(text)
        assert out.split("\n") == ["## X", "", "", "", "## Y"]

    def test_strip_code_blocks_no_block(self) -> None:
        text = "## A\n\ntexto normal\n## B"
        assert la.strip_code_blocks(text) == text

    def test_strip_code_blocks_preserves_line_count(self) -> None:
        text = "a\nb\nc\nd\ne"
        assert len(la.strip_code_blocks(text).split("\n")) == 5


class TestNormalize:
    def test_normalize_whitespace(self) -> None:
        assert la.normalize("  1.   Breakdown  ") == "1. Breakdown"

    def test_normalize_travessao_longo(self) -> None:
        assert la.normalize("A — B") == "A -- B"

    def test_normalize_travessao_curto(self) -> None:
        assert la.normalize("A – B") == "A -- B"

    def test_normalize_case_sensitive(self) -> None:
        assert la.normalize("Breakdown") != la.normalize("breakdown")

    def test_normalize_mixed(self) -> None:
        assert la.normalize("  1.  Breakdown  —  decomposição  ") == (
            "1. Breakdown -- decomposição"
        )


class TestExtractHeadings:
    def test_extract_headings_all_levels(self) -> None:
        text = "# Título\n## Seção 2\n### Sub 3\n#### Nível 4\n##### L5\n###### L6"
        headings = la.extract_headings(text)
        niveis = [h[1] for h in headings]
        assert niveis == [1, 2, 3, 4, 5, 6]

    def test_extract_headings_line_numbers_1based(self) -> None:
        text = "texto\n\n## Breakdown\n\n### Model"
        headings = la.extract_headings(text)
        assert headings[0] == (3, 2, "Breakdown")
        assert headings[1] == (5, 3, "Model")

    def test_extract_headings_skips_code_block_when_stripped(self) -> None:
        text = "## A\n```\n## B\n```\n## C"
        # Precondição: strip_code_blocks primeiro
        headings = la.extract_headings(la.strip_code_blocks(text))
        textos = [h[2] for h in headings]
        assert textos == ["A", "C"]  # B não aparece

    def test_extract_headings_normalizes_text(self) -> None:
        text = "##  1.   Breakdown  —  decomposição"
        headings = la.extract_headings(text)
        assert headings[0][2] == "1. Breakdown -- decomposição"

    def test_extract_headings_empty(self) -> None:
        assert la.extract_headings("") == []
        assert la.extract_headings("texto sem heading") == []


# ---------------------------------------------------------------------------
# T-010 — extract_links, validate_links (F3 unit tests)
# ---------------------------------------------------------------------------


class TestExtractLinks:
    def test_extract_links_relative(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        text = "Veja [x](./foo.md) e [y](../bar.md)."
        links = la.extract_links(text, source)
        assert len(links) == 2
        assert links[0][1] == "./foo.md"
        assert links[1][1] == "../bar.md"

    def test_extract_links_ignores_external(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        text = (
            "[https](https://example.com) [http](http://example.com) "
            "[mail](mailto:a@b.com) [ftp](ftp://host/x)"
        )
        assert la.extract_links(text, source) == []

    def test_extract_links_ignores_images(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        text = "![alt](./image.png) com link normal [doc](./doc.md)"
        links = la.extract_links(text, source)
        assert len(links) == 1
        assert links[0][1] == "./doc.md"

    def test_extract_links_strips_anchor_on_target(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        target = tmp_path / "outro.md"
        target.touch()
        text = "[sec](./outro.md#minha-secao)"
        links = la.extract_links(text, source)
        assert len(links) == 1
        assert links[0][1] == "./outro.md#minha-secao"
        # resolvido aponta para o arquivo, sem a âncora
        assert links[0][2] == target

    def test_extract_links_percent_encoded(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        target = tmp_path / "arquivo com espaço.md"
        target.touch()
        text = "[x](./arquivo%20com%20espa%C3%A7o.md)"
        links = la.extract_links(text, source)
        assert len(links) == 1
        assert links[0][2] == target

    def test_extract_links_ignores_fragment_only(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        text = "[topo](#inicio)"
        assert la.extract_links(text, source) == []

    def test_extract_links_line_numbers_1based(self, tmp_path: Path) -> None:
        source = tmp_path / "doc.md"
        source.touch()
        text = "linha 1\n\nlinha 3 com [x](./y.md)\n\nlinha 5 [z](./w.md)"
        links = la.extract_links(text, source)
        assert links[0][0] == 3
        assert links[1][0] == 5


class TestValidateLinks:
    def test_validate_links_all_exist(self, tmp_path: Path) -> None:
        existing = tmp_path / "existe.md"
        existing.touch()
        links = [(1, "./existe.md", existing)]
        assert la.validate_links(links, arquivo=str(tmp_path / "src.md")) == []

    def test_validate_links_broken(self, tmp_path: Path) -> None:
        inexistente = tmp_path / "inexistente.md"
        links = [(7, "./inexistente.md", inexistente)]
        diags = la.validate_links(links, arquivo="src.md")
        assert len(diags) == 1
        d = diags[0]
        assert d.codigo == "LINK_QUEBRADO"
        assert d.linha == 7
        assert "./inexistente.md" in d.mensagem

    def test_validate_links_mixed(self, tmp_path: Path) -> None:
        exists = tmp_path / "a.md"
        exists.touch()
        broken = tmp_path / "b.md"
        links = [
            (1, "./a.md", exists),
            (2, "./b.md", broken),
        ]
        diags = la.validate_links(links, arquivo="src.md")
        assert len(diags) == 1
        assert diags[0].linha == 2


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


# ---------------------------------------------------------------------------
# T-008 — CLI integração F2 (validação de seções requer:)
# ---------------------------------------------------------------------------


class TestCLIF2Sections:
    def _run(self, fixture: Path, capsys: pytest.CaptureFixture[str]) -> tuple[int, str, str]:
        code = la.main([str(fixture)])
        captured = capsys.readouterr()
        return code, captured.out, captured.err

    def test_cli_sections_ok(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "sections_ok.md", capsys)
        assert code == 0
        assert "OK" in out

    def test_cli_section_missing(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "section_missing.md", capsys)
        assert code == 1
        assert "SECAO_OBRIGATORIA_AUSENTE" in out
        assert "2. Model" in out

    def test_cli_section_wrong_level(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(fixtures_dir / "section_wrong_level.md", capsys)
        assert code == 1
        assert "SECAO_OBRIGATORIA_NIVEL_INVALIDO" in out
        assert "nível 4" in out

    def test_cli_section_in_code_block(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        """Heading dentro de fenced code block não conta como obrigatória (FR-005)."""
        code, out, _ = self._run(fixtures_dir / "section_in_code_block.md", capsys)
        assert code == 1
        assert "SECAO_OBRIGATORIA_AUSENTE" in out

    def test_cli_section_extra_whitespace(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        """Whitespace extra no heading é normalizado antes da comparação (FR-004)."""
        code, out, _ = self._run(fixtures_dir / "section_extra_whitespace.md", capsys)
        assert code == 0
        assert "OK" in out


# ---------------------------------------------------------------------------
# T-011 — format_human (com cor), format_json, supports_color (F3 unit)
# ---------------------------------------------------------------------------


class TestSupportsColor:
    def test_supports_color_no_color_env(
        self, monkeypatch: pytest.MonkeyPatch
    ) -> None:
        monkeypatch.setenv("NO_COLOR", "1")
        # Stream "isatty=True" ainda assim retorna False quando NO_COLOR setado
        class FakeTTY:
            def isatty(self) -> bool:
                return True

        assert la.supports_color(FakeTTY()) is False

    def test_supports_color_non_tty(
        self, monkeypatch: pytest.MonkeyPatch
    ) -> None:
        monkeypatch.delenv("NO_COLOR", raising=False)
        class NonTTY:
            def isatty(self) -> bool:
                return False

        assert la.supports_color(NonTTY()) is False

    def test_supports_color_tty_without_no_color(
        self, monkeypatch: pytest.MonkeyPatch
    ) -> None:
        monkeypatch.delenv("NO_COLOR", raising=False)
        class TTY:
            def isatty(self) -> bool:
                return True

        assert la.supports_color(TTY()) is True


class TestFormatHuman:
    def _mk(self, arquivo: str, linha: int, nivel: str, codigo: str) -> "la.Diagnostic":
        return la.Diagnostic(
            arquivo=arquivo, linha=linha, nivel=nivel,  # type: ignore[arg-type]
            codigo=codigo, mensagem="msg",
        )

    def test_format_human_empty_returns_ok(self) -> None:
        assert la.format_human([]) == "OK"

    def test_format_human_ordering_by_line(self) -> None:
        diags = [
            self._mk("a.md", 10, "ERRO", "X"),
            self._mk("a.md", 2, "ERRO", "Y"),
            self._mk("a.md", 5, "ERRO", "Z"),
        ]
        out = la.format_human(diags)
        # ordem por linha ascendente: 2 → 5 → 10
        linhas = out.split("\n")
        assert linhas[0].startswith("a.md:2:")
        assert linhas[1].startswith("a.md:5:")
        assert linhas[2].startswith("a.md:10:")

    def test_format_human_errors_before_warnings(self) -> None:
        diags = [
            self._mk("a.md", 2, "WARN", "W"),
            self._mk("a.md", 10, "ERRO", "E"),
        ]
        out = la.format_human(diags)
        linhas = out.split("\n")
        assert "[ERRO]" in linhas[0]
        assert "[WARN]" in linhas[1]

    def test_format_human_without_color(self) -> None:
        diags = [self._mk("a.md", 1, "ERRO", "X")]
        out = la.format_human(diags, use_color=False)
        assert "\033[" not in out

    def test_format_human_with_color(self) -> None:
        diags = [self._mk("a.md", 1, "ERRO", "X")]
        out = la.format_human(diags, use_color=True)
        assert "\033[31m" in out  # vermelho para ERRO
        assert "\033[0m" in out   # reset


class TestFormatJson:
    def test_format_json_empty(self) -> None:
        assert la.format_json([]) == "[]"

    def test_format_json_valid_and_parseable(self) -> None:
        import json as _json
        diags = [
            la.Diagnostic(
                arquivo="a.md", linha=3, nivel="ERRO",
                codigo="X", mensagem="msg com acento",
            ),
        ]
        out = la.format_json(diags)
        data = _json.loads(out)
        assert isinstance(data, list)
        assert len(data) == 1
        assert data[0] == {
            "arquivo": "a.md",
            "linha": 3,
            "nivel": "ERRO",
            "codigo": "X",
            "mensagem": "msg com acento",
        }

    def test_format_json_preserves_utf8(self) -> None:
        diags = [
            la.Diagnostic(
                arquivo="a.md", linha=1, nivel="ERRO",
                codigo="X", mensagem="decisão — registrada",
            ),
        ]
        out = la.format_json(diags)
        # ensure_ascii=False preserva os caracteres
        assert "decisão" in out
        assert "—" in out


# ---------------------------------------------------------------------------
# T-012 — CLI integração F3 completa (flags + links + format + warnings-only)
# ---------------------------------------------------------------------------


class TestCLIF3:
    def _run(
        self,
        args: list[str],
        capsys: pytest.CaptureFixture[str],
    ) -> tuple[int, str, str]:
        code = la.main(args)
        captured = capsys.readouterr()
        return code, captured.out, captured.err

    def test_cli_link_ok(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run([str(fixtures_dir / "links_ok.md")], capsys)
        assert code == 0
        assert "OK" in out

    def test_cli_link_broken(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run([str(fixtures_dir / "link_broken.md")], capsys)
        assert code == 1
        assert "LINK_QUEBRADO" in out

    def test_cli_link_external_ok(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run([str(fixtures_dir / "link_external.md")], capsys)
        assert code == 0

    def test_cli_link_with_anchor_ok(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run([str(fixtures_dir / "link_with_anchor.md")], capsys)
        assert code == 0

    def test_cli_link_in_code_block_ok(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run([str(fixtures_dir / "link_in_code_block.md")], capsys)
        assert code == 0

    def test_cli_format_json_errors(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        import json as _json
        code, out, _ = self._run(
            [str(fixtures_dir / "link_broken.md"), "--format", "json"], capsys
        )
        assert code == 1
        data = _json.loads(out)
        assert isinstance(data, list)
        assert any(d["codigo"] == "LINK_QUEBRADO" for d in data)

    def test_cli_format_json_empty(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(
            [str(fixtures_dir / "valid_minimal.md"), "--format", "json"], capsys
        )
        assert code == 0
        assert out.strip() == "[]"

    def test_cli_warnings_only_downgrades_errors(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(
            [str(fixtures_dir / "link_broken.md"), "--warnings-only"], capsys
        )
        assert code == 0  # FR-015 força exit 0
        assert "[WARN]" in out
        assert "[ERRO]" not in out
        assert "LINK_QUEBRADO" in out

    def test_cli_warnings_only_ok_case(
        self, fixtures_dir: Path, capsys: pytest.CaptureFixture[str]
    ) -> None:
        code, out, _ = self._run(
            [str(fixtures_dir / "valid_minimal.md"), "--warnings-only"], capsys
        )
        assert code == 0
        assert "OK" in out

    def test_cli_no_color_flag(
        self,
        fixtures_dir: Path,
        capsys: pytest.CaptureFixture[str],
        monkeypatch: pytest.MonkeyPatch,
    ) -> None:
        monkeypatch.delenv("NO_COLOR", raising=False)
        code, out, _ = self._run(
            [str(fixtures_dir / "link_broken.md"), "--no-color"], capsys
        )
        assert code == 1
        assert "\033[" not in out

    def test_cli_no_color_env(
        self,
        fixtures_dir: Path,
        capsys: pytest.CaptureFixture[str],
        monkeypatch: pytest.MonkeyPatch,
    ) -> None:
        monkeypatch.setenv("NO_COLOR", "1")
        code, out, _ = self._run(
            [str(fixtures_dir / "link_broken.md")], capsys
        )
        assert code == 1
        assert "\033[" not in out
