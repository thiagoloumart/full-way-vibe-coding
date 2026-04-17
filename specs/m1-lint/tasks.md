---
artefato: tasks
fase: 5
dominio: [software]
schema_version: 1
requer:
  - "Matriz de rastreabilidade (FR ↔ Task)"
  - "Matriz de rastreabilidade (Edge Case ↔ Task/Teste)"
---

# Tasks — `lint_artefato.py` M1

**Referência:** `plan.md` v1.0
**Data:** 2026-04-17
**Status:** Aprovado

Implementação por fase (Manual §12). Todas as tasks são <1 dia de trabalho.

---

## Legenda

- 🟢 Baixo risco | 🟡 Médio | 🔴 Alto
- Estado: ⬜ pendente | 🔶 em andamento | ✅ feita | ⛔ bloqueada

---

## Fase F1 — Núcleo de parsing e validação de front-matter

### T-001 — Bootstrap do pacote Python
- **Estado:** ⬜
- **Depende de:** nenhuma.
- **Descrição:** criar `pyproject.toml` (Python≥3.11, dep `pyyaml≥6.0`, dev-dep `pytest≥8.0`), `harness/__init__.py`, `harness/scripts/__init__.py`, `harness/tests/__init__.py`, `harness/tests/conftest.py` com fixture de path para diretório de fixtures.
- **Arquivos:** `pyproject.toml`, `harness/__init__.py`, `harness/scripts/__init__.py`, `harness/tests/__init__.py`, `harness/tests/conftest.py`.
- **Contrato afetado:** nenhum — apenas infraestrutura.
- **Testes exigidos:** `pytest` roda com 0 tests coletados e 0 erros.
- **Definition of Done:**
  - [ ] `pip install -e .` instala sem erro.
  - [ ] `python -c "import harness.scripts"` retorna sem ImportError.
  - [ ] `pytest harness/tests/` sai código 0 (sem tests ainda, mas sem erro de coleta).
- **Risco:** 🟢 — infraestrutura padrão Python.

### T-002 — Criar fixtures mínimas para F1
- **Estado:** ⬜
- **Depende de:** T-001.
- **Descrição:** criar 5 fixtures Markdown em `harness/tests/fixtures/`:
  - `valid_minimal.md` — front-matter OK com `requer: []` e corpo vazio.
  - `no_frontmatter.md` — Markdown sem bloco `---...---`.
  - `yaml_invalid.md` — front-matter com YAML malformado (tabs misturados).
  - `missing_required_field.md` — faltando campo `artefato:`.
  - `wrong_type.md` — `requer: "string"` em vez de lista.
- **Arquivos:** 5 arquivos em `harness/tests/fixtures/`.
- **Contrato afetado:** nenhum.
- **Testes exigidos:** não aplicável nesta task (fixtures são usadas pelas próximas).
- **Definition of Done:**
  - [ ] 5 arquivos criados com conteúdo coerente.
  - [ ] Arquivo `valid_minimal.md` pode ser aberto e tem YAML válido.
- **Risco:** 🟢.

### T-003 — Implementar `read_file`, `strip_bom` e exceções customizadas
- **Estado:** ⬜
- **Depende de:** T-001.
- **Descrição:** em `harness/scripts/lint_artefato.py`, criar exceções `ArquivoNaoEncontrado`, `ArquivoNaoMarkdown`, `FrontmatterAusente`, `YamlInvalido`, `CampoObrigatorioAusente`, `CampoTipoInvalido` (herdando de `Exception` ou hierarquia comum). Função `read_file(path: Path) -> str` que abre UTF-8, remove BOM, raises exceções de IO.
- **Arquivos:** `harness/scripts/lint_artefato.py` (início do arquivo).
- **Contrato afetado:** funções internas — ainda sem CLI.
- **Testes exigidos:**
  - `test_read_file_valid_utf8` — lê valid_minimal, retorna string.
  - `test_read_file_with_bom` — lê fixture com BOM UTF-8, BOM removido.
  - `test_read_file_not_found` — raises `ArquivoNaoEncontrado`.
  - `test_read_file_not_md` — passar `.txt` raises `ArquivoNaoMarkdown`.
- **Definition of Done:**
  - [ ] 4 testes unitários passam.
  - [ ] Exceções têm mensagens claras.
- **Risco:** 🟢.

### T-004 — Implementar `parse_frontmatter` e `validate_frontmatter_fields`
- **Estado:** ⬜
- **Depende de:** T-002, T-003.
- **Descrição:** funções em `harness/scripts/lint_artefato.py`:
  - `parse_frontmatter(text: str) -> tuple[dict, int]` — extrai YAML entre os primeiros dois `---`. Raises `FrontmatterAusente` ou `YamlInvalido` (contendo linha/coluna se disponível).
  - `validate_frontmatter_fields(fm: dict) -> list[Diagnostic]` — checa presença de `artefato`, `fase`, `dominio`, `schema_version`, `requer` e seus tipos.
  - Dataclass `Diagnostic(arquivo, linha, nivel, codigo, mensagem)`.
- **Arquivos:** `harness/scripts/lint_artefato.py`.
- **Contrato afetado:** Diagnostic.
- **Testes exigidos:**
  - `test_parse_frontmatter_ok` — valid_minimal retorna dict + int.
  - `test_parse_frontmatter_missing` — no_frontmatter raises `FrontmatterAusente`.
  - `test_parse_frontmatter_yaml_invalid` — yaml_invalid raises `YamlInvalido` com linha.
  - `test_validate_fields_ok` — dict completo retorna `[]`.
  - `test_validate_fields_missing_required` — dict sem `artefato` retorna 1 diagnostic `CAMPO_OBRIGATORIO_AUSENTE`.
  - `test_validate_fields_wrong_type` — dict com `requer: "string"` retorna 1 diagnostic `CAMPO_TIPO_INVALIDO`.
- **Definition of Done:**
  - [ ] 6 testes passam.
  - [ ] Dataclass Diagnostic é importável.
- **Risco:** 🟡 — edge cases de pyyaml podem escapar do `YAMLError` genérico; testar variantes.

### T-005 — Implementar `main` com CLI `argparse` e integração F1
- **Estado:** ⬜
- **Depende de:** T-004.
- **Descrição:** função `main(argv: list[str]) -> int` com argparse (argumento posicional `arquivo`; flags placeholder `--format`, `--warnings-only`, `--no-color` — implementação real em F3). Integra `read_file` + `parse_frontmatter` + `validate_frontmatter_fields`. Imprime diagnósticos em formato humano. Retorna 0, 1 ou 2. Entry point via `if __name__ == "__main__": sys.exit(main(sys.argv[1:]))`.
- **Arquivos:** `harness/scripts/lint_artefato.py` (fim do arquivo).
- **Contrato afetado:** CLI de exit codes (0/1/2) — contrato Camada 1 §10.
- **Testes exigidos:**
  - `test_cli_valid_minimal` — argv=fixture → exit 0, stdout "OK".
  - `test_cli_no_frontmatter` — argv=fixture → exit 1, stdout contém `FRONTMATTER_AUSENTE`.
  - `test_cli_yaml_invalid` — argv=fixture → exit 1, stdout contém `YAML_INVALIDO`.
  - `test_cli_not_found` — argv=path_inexistente → exit 2, stderr contém `ARQUIVO_NAO_ENCONTRADO`.
  - `test_cli_not_md` — argv=.txt → exit 2, stderr contém `ARQUIVO_NAO_MARKDOWN`.
  - `test_cli_missing_required` — argv=fixture → exit 1, stdout contém `CAMPO_OBRIGATORIO_AUSENTE: artefato`.
  - `test_cli_wrong_type` — argv=fixture → exit 1, stdout contém `CAMPO_TIPO_INVALIDO: requer`.
- **Definition of Done:**
  - [ ] 7 testes passam.
  - [ ] `python -m harness.scripts.lint_artefato harness/tests/fixtures/valid_minimal.md` roda via CLI e retorna código correto.
  - [ ] F1 está "pronta" conforme `plan.md §3 F1`.
- **Risco:** 🟡 — argparse tem comportamentos default em erro (stderr vs stdout) que podem precisar custom; documentar.

---

## Fase F2 — Validação de seções `requer:`

### T-006 — Fixtures para F2
- **Estado:** ⬜
- **Depende de:** T-005.
- **Descrição:** criar 5 fixtures em `harness/tests/fixtures/`:
  - `sections_ok.md` — `requer: ["1. Breakdown", "2. Model"]` com `## 1. Breakdown` e `## 2. Model` no corpo.
  - `section_missing.md` — `requer: ["1. Breakdown", "2. Model"]` com apenas `## 1. Breakdown`.
  - `section_wrong_level.md` — `requer: ["1. Breakdown"]` com `#### 1. Breakdown` (nível 4).
  - `section_in_code_block.md` — `requer: ["1. Breakdown"]` com `## 1. Breakdown` dentro de ```.
  - `section_extra_whitespace.md` — `requer: ["1. Breakdown"]` com `##  1. Breakdown` (espaço duplo).
- **Arquivos:** 5 fixtures.
- **Testes exigidos:** nenhum nesta task.
- **Definition of Done:** 5 arquivos criados; cada um tem YAML front-matter válido (então T-005 não reclama da parte anterior).
- **Risco:** 🟢.

### T-007 — Implementar `strip_code_blocks`, `extract_headings`, `normalize`
- **Estado:** ⬜
- **Depende de:** T-005, T-006.
- **Descrição:** funções em `harness/scripts/lint_artefato.py`:
  - `strip_code_blocks(text: str) -> str` — substitui linhas dentro de ```...``` por string vazia, preserva número de linhas.
  - `normalize(s: str) -> str` — collapse whitespace, trim, substitui `—` por `--`. Case-sensitive.
  - `extract_headings(text_pos_strip: str) -> list[tuple[int, int, str]]` — retorna `(linha, nivel, texto_normalizado)` para todos os `##` e `###` no corpo pós-strip.
- **Arquivos:** `harness/scripts/lint_artefato.py`.
- **Contrato afetado:** funções internas.
- **Testes exigidos:**
  - `test_strip_code_blocks_basic` — `"## A\n```\n## B\n```\n## C"` → resultado sem `## B`, preservando 5 linhas.
  - `test_normalize_whitespace` — `"  1.   Breakdown  "` → `"1. Breakdown"`.
  - `test_normalize_travessao` — `"## A — B"` → `"A -- B"` após normalização do texto do heading.
  - `test_extract_headings_levels_2_3` — corpo com `#`, `##`, `###`, `####` → retorna só os `##` e `###`.
  - `test_extract_headings_with_code_block` — heading dentro de ``` não aparece.
- **Definition of Done:** 5 testes passam.
- **Risco:** 🟡 — `strip_code_blocks` com aninhamento mal fechado é o risco de D-001 [RISCO ASSUMIDO]; documentar em docstring.

### T-008 — Implementar `validate_required_sections` e integrar em `main`
- **Estado:** ⬜
- **Depende de:** T-007.
- **Descrição:** `validate_required_sections(requer: list[str], headings: list[tuple[int, int, str]]) -> list[Diagnostic]`. Para cada item em `requer:`, normalizar e verificar se existe heading com texto que começa com ele (prefix match) e nível ∈ {2, 3}. Caso não exista em nenhum nível → `SECAO_OBRIGATORIA_AUSENTE`. Caso exista só em nível ≥4 → `SECAO_OBRIGATORIA_NIVEL_INVALIDO`. Atualizar `main` para chamar a nova validação após `validate_frontmatter_fields`.
- **Arquivos:** `harness/scripts/lint_artefato.py`.
- **Testes exigidos:**
  - `test_cli_sections_ok` — argv=sections_ok → exit 0.
  - `test_cli_section_missing` — argv=section_missing → exit 1, stdout contém `SECAO_OBRIGATORIA_AUSENTE`.
  - `test_cli_section_wrong_level` — argv=section_wrong_level → exit 1, contém `SECAO_OBRIGATORIA_NIVEL_INVALIDO`.
  - `test_cli_section_in_code_block` — argv=section_in_code_block → exit 1, contém `SECAO_OBRIGATORIA_AUSENTE`.
  - `test_cli_section_extra_whitespace` — argv=section_extra_whitespace → exit 0 (normalização funciona).
- **Definition of Done:**
  - [ ] 5 testes passam.
  - [ ] Smoke manual: `python -m harness.scripts.lint_artefato templates/spec.md` → exit 0.
  - [ ] Smoke manual em TODOS os templates atuais → exit 0.
  - [ ] F2 "pronta" conforme `plan.md §3 F2`.
- **Risco:** 🟡 — templates atuais podem ter algum heading em nível inesperado; smoke manual é crítico.

---

## Fase F3 — Links + report + flags

### T-009 — Fixtures para F3
- **Estado:** ⬜
- **Depende de:** T-008.
- **Descrição:** criar 6 fixtures:
  - `links_ok.md` — `[target](./links_ok_nearby.md)` e `[README](../../../README.md)`.
  - `links_ok_nearby.md` — arquivo alvo referenciado.
  - `link_broken.md` — `[x](./nao_existe.md)`.
  - `link_external.md` — `[github](https://github.com/foo/bar)`.
  - `link_with_anchor.md` — `[x](./links_ok_nearby.md#section-a)`.
  - `link_in_code_block.md` — `[x](./nao_existe.md)` dentro de ```.
- **Arquivos:** 6 fixtures.
- **Definition of Done:** fixtures criadas; referências relativas entre elas consistentes.
- **Risco:** 🟢.

### T-010 — Implementar `extract_links` e `validate_links`
- **Estado:** ⬜
- **Depende de:** T-008, T-009.
- **Descrição:**
  - `extract_links(text_pos_strip: str, source_path: Path) -> list[tuple[int, str, Path]]` — regex `\[([^\]]+)\]\(([^)]+)\)`. Filtra: começa com `http://`/`https://`/`mailto:` → ignora; senão, split em `#` e resolve parte antes de `#` relativa a `source_path.parent`.
  - `validate_links(links: list) -> list[Diagnostic]` — `Path.exists()`; se falso → `LINK_QUEBRADO`.
- **Arquivos:** `harness/scripts/lint_artefato.py`.
- **Testes exigidos:**
  - `test_extract_links_relative` — parseia 2 links relativos.
  - `test_extract_links_external_ignored` — link `https://` não aparece.
  - `test_extract_links_with_anchor` — `foo.md#bar` → target resolvido é `foo.md`.
  - `test_extract_links_in_code_block_ignored` — link em ``` não aparece.
- **Definition of Done:** 4 testes passam.
- **Risco:** 🟢.

### T-011 — Implementar `format_human`, `format_json`, `supports_color`
- **Estado:** ⬜
- **Depende de:** T-005.
- **Descrição:**
  - `supports_color() -> bool` — `sys.stdout.isatty() and 'NO_COLOR' not in os.environ`.
  - `format_human(diags: list[Diagnostic], use_color: bool) -> str` — linhas no formato `<arquivo>:<linha>: [NIVEL] CODIGO mensagem`, ordenadas por linha crescente, `[ERRO]` antes de `[WARN]`. ANSI se `use_color`.
  - `format_json(diags: list[Diagnostic]) -> str` — `json.dumps([asdict(d) for d in diags], ensure_ascii=False, indent=None)`.
- **Arquivos:** `harness/scripts/lint_artefato.py`.
- **Testes exigidos:**
  - `test_format_human_ordering` — 3 diags em linhas 10, 2, 5 → saída ordenada 2, 5, 10.
  - `test_format_human_error_before_warn` — 1 erro linha 10 + 1 warn linha 2 → erro primeiro.
  - `test_format_human_color` — com ANSI quando `use_color=True`.
  - `test_format_human_no_color` — sem ANSI quando `use_color=False`.
  - `test_format_json_valid` — output passa `json.loads`.
  - `test_format_json_empty` — lista vazia → `[]`.
  - `test_supports_color_no_color_env` — monkeypatch `NO_COLOR=1` → retorna False.
- **Definition of Done:** 7 testes passam.
- **Risco:** 🟢.

### T-012 — Integrar flags CLI e warnings-only
- **Estado:** ⬜
- **Depende de:** T-010, T-011.
- **Descrição:** completar `argparse` em `main` com `--format {human,json}`, `--warnings-only`, `--no-color`. Integrar `validate_links` após `validate_required_sections`. Aplicar `--warnings-only`: downgrade `[ERRO]` → `[WARN]`, exit 0. Aplicar `--no-color`: força `use_color=False`. Usar `format_json` quando `--format=json`.
- **Arquivos:** `harness/scripts/lint_artefato.py`.
- **Testes exigidos:**
  - `test_cli_link_broken` — exit 1, contém `LINK_QUEBRADO`.
  - `test_cli_link_external_ok` — exit 0.
  - `test_cli_link_with_anchor_ok` — exit 0.
  - `test_cli_link_in_code_block_ok` — exit 0 (ignora).
  - `test_cli_format_json` — saída parseável por `json.loads`.
  - `test_cli_format_json_empty` — exit 0, stdout `[]`.
  - `test_cli_warnings_only_errors` — fixture com erros + `--warnings-only` → exit 0, stdout `[WARN]`.
  - `test_cli_no_color` — com `--no-color` → stdout sem ANSI.
  - `test_cli_no_color_env` — monkeypatch `NO_COLOR=1` → stdout sem ANSI.
- **Definition of Done:**
  - [ ] 9 testes passam.
  - [ ] Smoke manual em TODOS os templates atuais (.md em `templates/`, `fases/`, `protocolos/`, `checklists/`, `governanca/`, `examples/` onde aplicável): exit 0 em cada.
  - [ ] SC-001: zero falsos positivos em todos os `.md` da skill.
  - [ ] SC-002: 7 fixtures defeituosas detectam 100%.
  - [ ] SC-003: `time python -m harness.scripts.lint_artefato templates/spec.md` < 500ms.
- **Risco:** 🟡 — smoke em todos os templates pode revelar padrões inesperados; prever 1 rodada de ajuste.

### T-013 — Atualizar `harness/README.md` com catálogo M1 (C-003)
- **Estado:** ⬜
- **Depende de:** T-012.
- **Descrição:** adicionar seção "Regras M1 implementadas por `lint_artefato.py`" em `harness/README.md`, listando os 10 códigos de erro (tabela de `spec.md §Estados de erro previsíveis`) com descrição, severidade, FR de origem.
- **Arquivos:** `harness/README.md`.
- **Testes exigidos:** nenhum (doc-only).
- **Definition of Done:** seção presente; lint do próprio `harness/README.md` continua verde (garantindo que nenhum exemplo em código quebre a regex de heading).
- **Risco:** 🟢.

### T-014 — Fase 8 (Testes): validar SCs e matar regressões
- **Estado:** ⬜
- **Depende de:** T-012, T-013.
- **Descrição:** corresponde à Fase 8 do ciclo. Rodar `pytest -v`; garantir ≥30 testes passando (soma das tasks); rodar smoke em todos os `.md` do repo e confirmar SC-001 a SC-005; medir tempo (SC-003); documentar execução em quickstart.
- **Arquivos:** nenhum novo; aumenta testes se regressão aparecer.
- **Definition of Done:**
  - [ ] `pytest` todos verdes.
  - [ ] SC-001 a SC-005 marcados como cumpridos.
  - [ ] Nenhum teste falhando nem skipado sem justificativa.
- **Risco:** 🟡.

### T-015 — Fase 9 (Quickstart) + Fase 10 (Review) + Fase 11 (Merge)
- **Estado:** ⬜
- **Depende de:** T-014.
- **Descrição:** escrever `specs/m1-lint/quickstart.md` (roteiro manual executável); preencher `.review/w1a-lint.md` com self-review; merge FF de `w1a/lint-artefato` para `main`; push.
- **Arquivos:** `specs/m1-lint/quickstart.md`, `.review/w1a-lint.md`.
- **Definition of Done:**
  - [ ] Quickstart passo a passo executa sem falha.
  - [ ] Self-review aprovado.
  - [ ] `w1a/lint-artefato` mergeada em `main`.
  - [ ] Push para `origin/main` executado.
  - [ ] Branch protection em `main` ativada no GitHub após merge (conforme decisão C-002 combinada).
- **Risco:** 🟡 — ativar branch protection é irreversível leve; documentar em ADR-004.

### T-016 — Fase 12 (Retrospective) do W1 track A
- **Estado:** ⬜
- **Depende de:** T-015.
- **Descrição:** escrever `specs/m1-lint/retrospective.md` revisitando D-001 a D-003 e C-001 a C-003 contra o que realmente aconteceu na implementação; identificar ≥3 ajustes a aplicar nos templates-mestre (se algum template se mostrar inadequado durante o lint); propor ADR-004+ se necessário.
- **Arquivos:** `specs/m1-lint/retrospective.md`; potencialmente `templates/*.md` se ajustes forem identificados (em PR separado).
- **Definition of Done:**
  - [ ] Retrospective escrita.
  - [ ] KPI comparados: estimativa 3-5 dias vs real; <500ms vs observado; 100% fixtures vs real.
  - [ ] ≥3 ajustes identificados (ou explicitamente "nenhum ajuste necessário" se for o caso).
- **Risco:** 🟢.

---

## Matriz de rastreabilidade (FR ↔ Task)

| FR | Task(s) | Fase |
|---|---|---|
| FR-001 | T-004 | F1 |
| FR-002 | T-004 | F1 |
| FR-003 | T-004 | F1 |
| FR-004 | T-007, T-008 | F2 |
| FR-005 | T-007 (strip_code_blocks) | F2 |
| FR-006 | T-008 | F2 |
| FR-007 | T-010 | F3 |
| FR-008 | T-010 | F3 |
| FR-009 | T-010 | F3 |
| FR-010 | T-010 | F3 |
| FR-011 | **invariante** — garantido pela ausência de qualquer operação de escrita no código; validado em T-014 revisão de código | F1-F3 |
| FR-012 | T-005, T-008, T-012 | F1-F3 |
| FR-013 | T-011 | F3 |
| FR-014 | T-011, T-012 | F3 |
| FR-015 | T-012 | F3 |
| FR-016 | T-003 | F1 |
| FR-017 | T-004 (comportamento default) | F1 |
| FR-018 | T-011, T-012 | F3 |

## Matriz de rastreabilidade (Edge Case ↔ Task/Teste)

| Edge case | Task | Cobertura |
|---|---|---|
| Arquivo não existe | T-005 (test_cli_not_found) | ✅ |
| Arquivo não é .md | T-005 (test_cli_not_md) | ✅ |
| Arquivo vazio | T-005 (via no_frontmatter fixture — equivalente) | ✅ |
| Front-matter presente, corpo vazio, `requer: []` | T-005 (valid_minimal é exatamente este caso) | ✅ |
| Link sobe acima da raiz | T-010 (test_extract_links_relative com ../) | ✅ parcial — aceita se existir |
| BOM UTF-8 | T-003 (test_read_file_with_bom) | ✅ |
| Front-matter com chave desconhecida | [INFERÊNCIA] comportamento default é aceitar (pyyaml não reclama); teste implícito em T-004 | 🟡 teste explícito seria bom em T-014 |
| Link URL-encoded (`%20`) | [INFERÊNCIA] `Path` do Python aceita; teste explícito em T-010 se identificado | 🟡 postergado |
| Arquivo > 10 MB | não coberto em M1; warning `ARQUIVO_MUITO_GRANDE` é opcional | ⛔ deferido para M2 (criar task lá) |

---

**Checklist antes de aprovar:**
- [x] Cada task tem título acionável.
- [x] Cada task tem DoD concreto.
- [x] Dependências explícitas em cadeia executável.
- [x] Cobertura completa do plano (nenhum arquivo órfão).
- [x] Testes distribuídos ao longo das tasks (não concentrados no final).
- [x] Matriz FR ↔ Task cobre todos os 18 FRs.
- [x] Edge cases tracados; 2 postergações explícitas (chave desconhecida e >10MB).
- [x] Tasks ordenáveis: T-001 até T-016 em sequência respeitando dependências.
- [x] 14 tasks de implementação (T-001 a T-014) + 2 de entrega (T-015, T-016) = 16 total.
- [x] Cada task <1 dia; soma ~3-5 dias de trabalho linear.
