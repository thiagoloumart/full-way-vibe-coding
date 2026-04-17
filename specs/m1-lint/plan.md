---
artefato: plano
fase: 4
dominio: [software]
schema_version: 1
requer:
  - "1. Escopo do plano"
  - "2. Pré-requisitos"
  - "3. Fases de implementação"
  - "4. Modelo de dados completo"
  - "5. Integrações externas (consolidado)"
  - "6. Decisões técnicas"
  - "7. Riscos técnicos e mitigações"
  - "8. Observabilidade planejada"
  - "9. Plano de rollback"
---

# Plano Técnico — `lint_artefato.py` M1

**Referências:** `spec.md` v1.0 (Estável), `clarify.md` (fechada), `constitution.md` v1.0
**Data:** 2026-04-17
**Status:** Aprovado (self-review)

---

## 1. Escopo do plano

- **Módulo:** `lint_artefato.py` + `harness/tests/test_lint_artefato.py` + `pyproject.toml`.
- **Stories cobertas:** US1 (P1), US2 (P1), US3 (P1), US4 (P2), US5 (P2), US6 (P3) — todas as 6.
- **FRs cobertos:** FR-001 a FR-018 — todos.
- **Explicitamente fora do plano:** tudo listado em `spec.md §Out of Scope`.

## 2. Pré-requisitos

- **Env vars:** nenhuma obrigatória. Respeita `NO_COLOR` se presente (C-002).
- **Dependências externas:** `pyyaml ≥6.0` instalável via `pip install pyyaml`.
- **Ambiente:** Python 3.11+ local; Ubuntu latest no CI.
- **Seeds / dados de referência:** fixtures em `harness/tests/fixtures/` (7 artefatos-mínimos cobrindo casos happy + sad).
- **Feature flags:** nenhuma. Comportamento governado por flags CLI (`--format`, `--warnings-only`, `--no-color`).

## 3. Fases de implementação

Implementação em 3 fases pequenas. Cada fase é testável isoladamente.

### F1 — Núcleo de parsing e validação de front-matter (FR-001 a FR-003, FR-012, FR-016, FR-017)

- **Objetivo:** o script já rejeita artefatos sem front-matter, com YAML inválido, ou sem os 5 campos obrigatórios — mesmo que ainda não valide corpo/links.
- **Depende de:** nada.
- **Arquivos criados/alterados:**
  - `pyproject.toml` — dep `pyyaml`, Python `>=3.11`, script entry.
  - `harness/__init__.py` — vazio.
  - `harness/scripts/__init__.py` — vazio.
  - `harness/scripts/lint_artefato.py` — módulo principal, apenas seções 1 (CLI entry), 2 (read_file + strip_bom), 3 (parse_frontmatter).
  - `harness/tests/__init__.py` — vazio.
  - `harness/tests/conftest.py` — fixtures path fixture.
  - `harness/tests/fixtures/valid_minimal.md` — artefato OK mínimo.
  - `harness/tests/fixtures/no_frontmatter.md` — sem `---...---`.
  - `harness/tests/fixtures/yaml_invalid.md` — YAML quebrado.
  - `harness/tests/fixtures/missing_required_field.md` — falta `artefato:`.
  - `harness/tests/fixtures/wrong_type.md` — `requer` como string em vez de lista.
  - `harness/tests/test_lint_artefato.py` — testes TF1-1 a TF1-6.
- **Contratos internos (funções):**
  - `read_file(path: Path) -> str` — lê UTF-8 com BOM tolerado; raises `ArquivoNaoEncontrado` ou `ArquivoNaoMarkdown` (custom exceptions).
  - `parse_frontmatter(text: str) -> tuple[dict, int]` — retorna `(dict_yaml, last_line_of_frontmatter)`. Raises `FrontmatterAusente`, `YamlInvalido`.
  - `validate_frontmatter_fields(fm: dict) -> list[Diagnostic]` — retorna lista de diagnósticos (pode ser vazia).
  - `main(argv: list[str]) -> int` — entry point; retorna exit code.
- **Testes mínimos desta fase:**
  - Sucesso: valid_minimal → exit 0.
  - Erro: no_frontmatter → FRONTMATTER_AUSENTE, exit 1.
  - Erro: yaml_invalid → YAML_INVALIDO, exit 1.
  - Erro: missing_required → CAMPO_OBRIGATORIO_AUSENTE, exit 1.
  - Erro: wrong_type → CAMPO_TIPO_INVALIDO, exit 1.
  - IO: arquivo inexistente → exit 2, stderr `ARQUIVO_NAO_ENCONTRADO`.
  - IO: arquivo .txt → exit 2, stderr `ARQUIVO_NAO_MARKDOWN`.
- **Critério de "pronto":** todos os 7 testes passam; `python -m harness.scripts.lint_artefato <fixture>` roda via CLI.
- **Riscos técnicos da fase:** pyyaml tem edge cases de formatting YAML (tabs vs espaços). Mitigação: usar `yaml.safe_load` e documentar exceções capturadas.

### F2 — Validação de seções `requer:` (FR-004 a FR-006, FR-013)

- **Objetivo:** o script agora matcha cada item de `requer:` contra headings do corpo, respeitando nível (`##`/`###`), normalização de whitespace/travessões, e skip de blocos de código.
- **Depende de:** F1.
- **Arquivos criados/alterados:**
  - `harness/scripts/lint_artefato.py` — adiciona funções `extract_headings`, `normalize`, `validate_required_sections`, `strip_code_blocks`.
  - `harness/tests/fixtures/sections_ok.md` — corpo com todos os headings de `requer:`.
  - `harness/tests/fixtures/section_missing.md` — uma seção de `requer:` ausente.
  - `harness/tests/fixtures/section_wrong_level.md` — heading em nível 4.
  - `harness/tests/fixtures/section_in_code_block.md` — heading dentro de ```.
  - `harness/tests/fixtures/section_extra_whitespace.md` — `##  1. Breakdown` (2 espaços).
  - `harness/tests/test_lint_artefato.py` — adiciona testes TF2-1 a TF2-5.
- **Contratos internos:**
  - `strip_code_blocks(text: str) -> str` — remove conteúdo entre ``` ... ``` para evitar falso positivo; preserva linhas (vira string em branco) para manter line numbers.
  - `extract_headings(text: str) -> list[tuple[int, int, str]]` — retorna `(linha, nivel, texto_normalizado)`. Apenas níveis 2 e 3. Usa texto pós-`strip_code_blocks`.
  - `normalize(s: str) -> str` — collapse whitespace, trim, substitui `—` por `--`, lowercase? **Não — case-sensitive** (templates usam case exato).
  - `validate_required_sections(requer: list[str], headings: list[tuple]) -> list[Diagnostic]`.
  - Atualização de `main` para chamar a nova validação.
- **Testes mínimos:**
  - Sucesso: sections_ok → OK.
  - Erro: section_missing → SECAO_OBRIGATORIA_AUSENTE, exit 1.
  - Erro: section_wrong_level → SECAO_OBRIGATORIA_NIVEL_INVALIDO, exit 1.
  - Erro: section_in_code_block → SECAO_OBRIGATORIA_AUSENTE (blocos não contam), exit 1.
  - Sucesso: section_extra_whitespace → OK (normalização funciona).
- **Critério de "pronto":** 12 testes passam (7 de F1 + 5 de F2); lint em todos os templates de `templates/` retorna OK (smoke manual).
- **Riscos:** regex de heading pode matchar dentro de blocos de código aninhados mal fechados. Mitigação: `strip_code_blocks` greedy ```...``` primeiro; documentar como [RISCO ASSUMIDO] conhecido.

### F3 — Validação de links, report humano/JSON, warnings-only, cor (FR-007 a FR-011, FR-014, FR-015, FR-018)

- **Objetivo:** script completo com todos os FRs cobertos. Reports em ambos formatos, flags `--format`, `--warnings-only`, `--no-color`.
- **Depende de:** F2.
- **Arquivos criados/alterados:**
  - `harness/scripts/lint_artefato.py` — adiciona `extract_links`, `validate_links`, `format_human`, `format_json`, `supports_color`.
  - `harness/tests/fixtures/links_ok.md` — links apontando para fixtures vizinhas válidas.
  - `harness/tests/fixtures/links_ok_nearby.md` — arquivo alvo referenciado por links_ok.md.
  - `harness/tests/fixtures/link_broken.md` — link para arquivo inexistente.
  - `harness/tests/fixtures/link_external.md` — link `https://...`.
  - `harness/tests/fixtures/link_with_anchor.md` — `foo.md#secao`.
  - `harness/tests/fixtures/link_in_code_block.md` — link dentro de ```.
  - `harness/tests/test_lint_artefato.py` — adiciona testes TF3-1 a TF3-10.
  - `harness/README.md` — adiciona seção "Regras M1 implementadas por `lint_artefato.py`" (C-003).
- **Contratos internos:**
  - `extract_links(text: str, source_path: Path) -> list[tuple[int, str, Path]]` — retorna `(linha, texto, target_resolvido)`. Resolve relativo à `source_path.parent`. Ignora URLs absolutas e mailto. Skipa blocos de código.
  - `validate_links(links: list) -> list[Diagnostic]` — checa `target.exists()`.
  - `format_human(diags: list, use_color: bool) -> str`.
  - `format_json(diags: list) -> str`.
  - `supports_color() -> bool` — `sys.stdout.isatty() and not os.environ.get('NO_COLOR')`.
  - Atualização de `main` para flags CLI completas via `argparse`.
- **Testes mínimos:**
  - Sucesso: links_ok → OK.
  - Erro: link_broken → LINK_QUEBRADO, exit 1.
  - Sucesso: link_external → OK (ignora).
  - Sucesso: link_with_anchor → OK (valida arquivo, não âncora).
  - Sucesso: link_in_code_block → OK (ignora).
  - `--format json` com erros → JSON parseável por `json.loads`.
  - `--format json` sem erros → `[]`.
  - `--warnings-only` com erros → exit 0, todos `[WARN]`.
  - `NO_COLOR=1` → saída sem ANSI.
  - `--no-color` → saída sem ANSI.
- **Critério de "pronto":** 22 testes passam (7 + 5 + 10); `SC-001` validado (zero falsos positivos em todos os `.md` atuais da skill); `SC-002` validado (100% dos defeitos plantados detectados); `SC-003` validado (<500ms em fixture de 200 linhas, via `time`); `harness/README.md` atualizado com catálogo M1.
- **Riscos:** `pathlib.Path.exists()` segue symlinks; em caso de link circular de FS, comportamento default de Python resolve. Aceitável.

## 4. Modelo de dados completo

Modelo de domínio (não persistente — apenas em memória durante a execução):

```
Diagnostic
  arquivo: str (path relativo)
  linha:   int (1-based)
  nivel:   Literal["ERRO", "WARN", "INFO"]
  codigo:  str (SCREAMING_SNAKE_CASE)
  mensagem: str

Heading
  linha:   int
  nivel:   int (2 ou 3)
  texto:   str (normalizado)

Link
  linha:   int
  texto:   str
  target:  str (caminho original)
  target_resolvido: Path
```

Nada é persistido. Nada é serializado para disco.

## 5. Integrações externas (consolidado)

| Serviço | Finalidade | Auth | Rate limit | Timeout | Retry | Fallback |
|---|---|---|---|---|---|---|
| _Nenhuma._ | — | — | — | — | — | — |

Zero rede, zero API externa. Invariante de Camada 1 §6.

## 6. Decisões técnicas

| Decisão | Opções consideradas | Escolhida | Motivo | Alinha com constituição? |
|---|---|---|---|---|
| Stack | Python+pyyaml / shell+yq / TS+Bun / Rust | Python+pyyaml | ADR-002 | ✅ Camada 2 §4 |
| Parser Markdown | regex / AST (markdown-it-py) | regex | D-001 (B em Analyze BMAD) | ✅ Camada 2 §4 |
| `--fix` flag | Implementar / Não | Não — invariante | D-002 | ✅ Camada 1 §3 (read-only) |
| CLI library | argparse / click / typer / nada | argparse (stdlib) | Zero dep extra; suficiente | ✅ Camada 2 §4 |
| Cor | dep `colorama` / ANSI inline / sem cor | ANSI inline | Mantém `pyyaml` como única dep externa | ✅ Camada 2 §8 |
| Testes | unittest / pytest | pytest | Mais idiomático; ADR-002 já declara dev-dep | ✅ Camada 2 §4 |
| Organização | 1 arquivo `lint_artefato.py` / módulo dividido | 1 arquivo | ≤300 linhas caberão; dividir prematura | [INFERÊNCIA] espero caber. **Critério quantitativo de refactor:** se o arquivo ultrapassar 350 linhas de código (`cloc --by-file --include-lang=Python lint_artefato.py` sem contar docstrings e linhas em branco), abrir ADR-004 e fragmentar em `harness/scripts/lint/` com sub-módulos (`parser.py`, `validator.py`, `report.py`, `cli.py`). *(Critério adicionado em resposta ao Problema #2 da Fase 6 Analyze.)* |

## 7. Riscos técnicos e mitigações

| Risco | Prob | Impacto | Mitigação |
|---|---|---|---|
| Regex de heading matcha dentro de bloco de código mal fechado | média | médio | `strip_code_blocks` greedy; testes cobrem; `[RISCO ASSUMIDO]` em D-001 |
| `pyyaml.safe_load` lança exceções genéricas difíceis de reportar bem | baixa | baixo | Capturar `yaml.YAMLError` e extrair `problem_mark` quando disponível |
| Link resolvido com symlink circular | muito baixa | baixo | `Path.exists()` não segue links circulares infinitos em Python; aceitar comportamento default |
| Arquivo muito grande causa lentidão | baixa | baixo | Warning `ARQUIVO_MUITO_GRANDE` em >10MB; sem bloqueio |
| BOM UTF-8 quebra `yaml.safe_load` | média | baixo | `strip_bom` antes de `parse_frontmatter`; teste TF1-X cobre |
| argparse gera mensagem de erro diferente do nosso padrão | baixa | baixo | Custom formatter se necessário; em M1 aceitar padrão |
| Script passa de 300 linhas e constituição exige refactor | baixa | médio | Abrir ADR-004 e mover para módulo `harness/scripts/lint/` com sub-arquivos |

## 8. Observabilidade planejada

- **Logs:** zero logs em runtime do lint (CLI). Saída é o "log".
- **Métricas:** zero em M1. `smoke_test.py` em M2 poderá coletar métrica agregada (falsos positivos por schema).
- **Alertas:** nenhum.
- **Traces:** nenhum.

## 9. Plano de rollback

- **Migrations:** não aplicável.
- **Feature flag:** não aplicável (CLI tool).
- **Deploy:** commit revertível em `main`. Usuários que clonaram pegam a reversão em `git pull`.
- **`HARNESS_ENFORCEMENT=off`:** kill switch global descrito em `harness/rollout.md` continua válido; lint respeita (M2 integra no workflow).

---

**Checklist antes de aprovar:**
- [x] Fases ordenadas (F1 → F2 → F3), cada uma entrega valor testável.
- [x] Cada fase tem critério de "pronto" mensurável.
- [x] Contratos internos descritos para cada função pública.
- [x] Modelo de dados coerente com `spec.md §Key Entities`.
- [x] Cada decisão técnica tem justificativa e alinha com constituição.
- [x] Riscos mapeados com mitigação.
- [x] Em brownfield: análise do repo aplicável (harness/scripts/ está vazio; nenhum código duplicado; reuso das convenções de `ADR-002` e `harness/README.md`).
- [x] Plano respeita Camada 1 e Camada 2 da constituição.
- [x] Humano validou — self-review time=1.
