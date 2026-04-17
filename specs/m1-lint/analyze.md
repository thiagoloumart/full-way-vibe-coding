---
artefato: analise
fase: 6
dominio: [any]
schema_version: 1
requer:
  - "1. Resumo executivo"
  - "2. Matriz Spec × Plano"
  - "3. Matriz Spec × Tasks"
  - "4. Matriz Constituição × Plano"
  - "7. Regras sensíveis × Clarify (Manual §5.4)"
  - "8.5 Spec × Decision Log (rastreabilidade estratégica)"
  - "10. Problemas detectados"
  - "12. Veredicto final"
---

# Análise Cruzada — `lint_artefato.py` M1

**Entradas:** `bmad.md` v1, `decision_log.md` (D-001, D-002, D-003), `constitution.md` v1.0, `spec.md` v1.0 (com FR-001 a FR-018), `clarify.md` (fechada — C-001, C-002, C-003), `plan.md` v1.0, `tasks.md` v1.0 (T-001 a T-016)
**Data:** 2026-04-17
**Status:** 🟢 Limpa

---

## 1. Resumo executivo

- **Problemas detectados:** 2 (ambos baixa severidade, mitigáveis em execução).
- **Bloqueadores (severidade alta):** 0.
- **Riscos assumidos conscientemente:** 3 (herdados de D-001 e plano §7 — todos registrados com mitigação).
- **Veredicto:** 🟢 **Pode seguir para Fase 7 Implement.**

---

## 2. Matriz Spec × Plano

Cada FR da spec é coberto por uma fase do plano?

| FR | Plano cobre? | Fase do plano | Arquivo(s) / Contrato | Observação |
|---|---|---|---|---|
| FR-001 | ✅ | F1 | `harness/scripts/lint_artefato.py` (`parse_frontmatter`) | — |
| FR-002 | ✅ | F1 | `validate_frontmatter_fields` | — |
| FR-003 | ✅ | F1 | `validate_frontmatter_fields` | — |
| FR-004 | ✅ | F2 | `extract_headings` + `normalize` + `validate_required_sections` | — |
| FR-005 | ✅ | F2 | `strip_code_blocks` | `[RISCO ASSUMIDO]` herdado de D-001 |
| FR-006 | ✅ | F2 | `extract_headings` (filtra níveis 2-3) | — |
| FR-007 | ✅ | F3 | `extract_links` + `validate_links` | — |
| FR-008 | ✅ | F3 | `extract_links` (não valida âncora) | — |
| FR-009 | ✅ | F3 | `extract_links` (filtra http/https/mailto) | — |
| FR-010 | ✅ | F3 | `extract_links` usa texto pós `strip_code_blocks` | — |
| FR-011 | ✅ | F1-F3 | **invariante** — ausência de qualquer operação de escrita | Validação via revisão de código em T-014 |
| FR-012 | ✅ | F1, F2, F3 | `main` retorna 0/1/2 | — |
| FR-013 | ✅ | F3 | `format_human` | — |
| FR-014 | ✅ | F3 | `format_json` + flag `--format json` | — |
| FR-015 | ✅ | F3 | flag `--warnings-only` em `main` | — |
| FR-016 | ✅ | F1 | `strip_bom` dentro de `read_file` | — |
| FR-017 | ✅ | F1 | comportamento default de `pyyaml.safe_load` + `validate_frontmatter_fields` ignora chaves extras | — |
| FR-018 | ✅ | F3 | `supports_color` + `format_human(use_color=...)` | — |

**Zero FR órfão.** Todos cobertos.

---

## 3. Matriz Spec × Tasks

Cada FR tem ao menos uma task implementando-o?

| FR | Task(s) | Testes explicitos na task |
|---|---|---|
| FR-001 | T-004 | test_parse_frontmatter_ok/missing/yaml_invalid |
| FR-002 | T-004 | test_validate_fields_missing_required |
| FR-003 | T-004 | test_validate_fields_wrong_type |
| FR-004 | T-007, T-008 | test_normalize_*, test_cli_sections_ok, test_cli_section_missing |
| FR-005 | T-007 | test_strip_code_blocks_basic |
| FR-006 | T-008 | test_cli_section_wrong_level |
| FR-007 | T-010, T-012 | test_extract_links_relative, test_cli_link_broken |
| FR-008 | T-010 | test_extract_links_with_anchor |
| FR-009 | T-010 | test_extract_links_external_ignored |
| FR-010 | T-010 | test_extract_links_in_code_block_ignored |
| FR-011 | **todas** + T-014 (revisão) | verificação estrutural, não teste unitário |
| FR-012 | T-005, T-008, T-012 | test_cli_not_found (exit 2), test_cli_valid_minimal (exit 0), test_cli_link_broken (exit 1) |
| FR-013 | T-011 | test_format_human_ordering, test_format_human_error_before_warn |
| FR-014 | T-011, T-012 | test_format_json_valid, test_cli_format_json |
| FR-015 | T-012 | test_cli_warnings_only_errors |
| FR-016 | T-003 | test_read_file_with_bom |
| FR-017 | [INFERÊNCIA] T-004 implicitamente aceita | **GAP:** nenhum teste explícito para chave extra no front-matter |
| FR-018 | T-011, T-012 | test_cli_no_color, test_cli_no_color_env, test_supports_color_no_color_env |

**Problema detectado #1:** FR-017 (aceitar chaves extras no front-matter) não tem teste explícito — só comportamento inferido. Ver seção 10.

**Cada task mapeia para FR ou decisão justificada:**
- T-001 (bootstrap) → infraestrutura, não FR direto. Justificado.
- T-002, T-006, T-009 (criar fixtures) → pré-requisitos de testes. Justificado.
- T-013 (atualizar harness/README.md) → mapeia para C-003. Justificado.
- T-014 (validação SCs) → mapeia para SC-001 a SC-005. Justificado.
- T-015 (Quickstart + Review + Merge) → mapeia para Fase 9/10/11. Justificado.
- T-016 (Retrospective) → mapeia para Fase 12. Justificado.

**Zero task órfão.**

---

## 4. Matriz Constituição × Plano

Cada decisão técnica do plano está alinhada à constituição?

| Decisão técnica (plan.md §6) | Regra da constituição | Alinhamento | Observação |
|---|---|---|---|
| Python 3.11+ + pyyaml | Camada 2 §4 | ✅ | Referência direta a ADR-002 |
| Parser Markdown = regex | Camada 2 §4 + D-001 | ✅ | — |
| `--fix` = não | Camada 1 §3 (read-only) | ✅ | FR-011 materializa |
| CLI library = argparse | Camada 2 §4 (dep externa única = pyyaml) | ✅ | argparse é stdlib |
| Cor = ANSI inline | Camada 2 §8 | ✅ | Evita dep externa de color |
| Testes = pytest | Camada 2 §4 | ✅ | — |
| Organização = 1 arquivo | [INFERÊNCIA] | 🟡 | Se passar de 300 linhas, ADR-004 (plan.md §6 já declara) |

**Problema detectado #2:** decisão "1 arquivo único" é `[INFERÊNCIA]` com plano de contingência em ADR-004, mas **não tem critério quantitativo claro de "quando disparar o refactor"**. Ver seção 10.

**Invariantes de segurança da Camada 1 §6:**
- ✅ Zero rede — plan.md §5 "Integrações externas: Nenhuma"
- ✅ Zero subprocess/os.system — nenhuma task usa
- ✅ Zero eval/exec/pickle — nenhuma task usa
- ✅ Zero operação de escrita no filesystem — FR-011 + todas as tasks só leem
- ✅ Dados do artefato não persistidos — apenas stdout/stderr

---

## 5. Matriz Spec × Constituição

A spec exige algo que a constituição proíbe?

| Requisito da spec | Conflito com constituição? | Resolução |
|---|---|---|
| FR-011 script é read-only | ✅ alinha perfeitamente | Camada 1 §3 |
| FR-018 ANSI color codes | ✅ alinha | Camada 2 §8 declara ANSI inline |
| FR-017 aceita chaves extras (M1) | ✅ alinha | Camada 1 §7 "zero validação semântica em M1" |
| FR-014 JSON format | ✅ alinha | Camada 1 §10 (contrato JSON estável) |
| FR-012 exit codes 0/1/2 | ✅ alinha | Camada 1 §10 (contrato estável) |

Zero conflito.

---

## 6. Matriz Edge Cases × Tratamento

| Edge case (spec.md) | Tratado em | Task | Teste? |
|---|---|---|---|
| Arquivo não existe | F1 (`read_file` raises `ArquivoNaoEncontrado`) | T-003, T-005 | ✅ test_cli_not_found |
| Arquivo não é .md | F1 (`read_file` raises `ArquivoNaoMarkdown`) | T-003, T-005 | ✅ test_cli_not_md |
| Arquivo vazio | F1 (`parse_frontmatter` raises `FrontmatterAusente`) | T-004, T-005 | ✅ (mesmo caminho de no_frontmatter) |
| Front-matter presente, corpo vazio, `requer: []` | F2 (lista vazia → 0 diags) | T-008 | ✅ valid_minimal fixture cobre |
| Link sobe acima da raiz do repo | F3 (`Path.resolve()` + `exists()`) | T-010 | 🟡 **sem teste explícito** |
| BOM UTF-8 | F1 (`strip_bom`) | T-003 | ✅ test_read_file_with_bom |
| Chave desconhecida no front-matter | F1 (pyyaml aceita; validate_fields ignora) | T-004 | 🟡 **sem teste explícito** (problema #1) |
| Link URL-encoded (`%20`) | F3 (`Path` decodifica) | T-010 | 🟡 **sem teste explícito** |
| Arquivo > 10 MB | deferido para M2 | — | ⛔ não coberto em M1 (aceito) |

**Mitigação:** adicionar 3 testes pequenos em T-014 para os 3 casos 🟡 — custo baixo, benefício médio. Agregado na seção 10.

---

## 7. Regras sensíveis × Clarify (Manual §5.4)

| Tema | Aplica-se a este módulo? | Decidido em | Autor | OK |
|---|---|---|---|---|
| Cobrança | não | — | — | ✅ N/A |
| Permissão / autorização | não | — | — | ✅ N/A |
| Estorno / cancelamento | não | — | — | ✅ N/A |
| **Deleção** | **sim** | **D-002** (BMAD) + **FR-011** (spec) + **Camada 1 §3** (constituição) | humano | ✅ triplamente rastreada |
| Expiração | não | — | — | ✅ N/A |
| Visibilidade entre papéis | não | — | — | ✅ N/A |
| Histórico | não | — | — | ✅ N/A |
| Auditoria | tangencial | BMAD §2.6 | humano | ✅ "não é audit log §5.4" |

Todas as regras §5.4 aplicáveis têm decisão humana assinada. Nenhuma pendente.

---

## 8. Brownfield — duplicação

| Entidade/arquivo/rota proposto | Já existe algo similar? | Onde | Ação |
|---|---|---|---|
| `harness/scripts/lint_artefato.py` | não | `harness/scripts/.gitkeep` | Criar |
| `harness/tests/test_lint_artefato.py` | não | nenhum | Criar |
| `pyproject.toml` raiz | não | repo é doc-only hoje | Criar |
| Padrões de diagnóstico (formato humano) | não | nenhum precedente no repo | Definir (FR-013) |
| Dataclass `Diagnostic` | não | — | Criar |
| Fixtures de artefatos | não | `harness/tests/fixtures/` existe só como planejado | Criar 16 fixtures (F1: 5, F2: 5, F3: 6) |

**Invariante brownfield respeitada:** script não duplica lógica existente; não há lógica de parse Markdown/YAML no repo antes deste lint. Convenções de código seguem `constitution.md` deste módulo + `ADR-002` global. Reuso: `pyyaml.safe_load` (dep já decidida), `re` stdlib, `pathlib`.

---

## 8.5 Spec × Decision Log (rastreabilidade estratégica)

| D-NNN | Tema | Respeitada por spec/plano/tasks? | FR/Task ref | Observação |
|---|---|---|---|---|
| D-001 | Stack = pyyaml + regex | ✅ | FR-001, FR-004, FR-007, FR-018 + T-004, T-007, T-010, T-011 | Todas as escolhas downstream consistentes |
| D-002 | Script read-only (invariante) | ✅ | FR-011 + Camada 1 §3 + T-014 (revisão) | Triplamente ancorada |
| D-003 | Escopo M1 = 3 validações | ✅ | FR-001-FR-018 (tudo cabe em 3 classes: front-matter, seções, links) + `spec.md §Out of Scope` | Limites respeitados |

**Nenhuma D-NNN silenciosamente revertida. Nenhuma linha ausente.**

Cross-check C-NNN do clarify:

| C-NNN | Implementada? | Onde |
|---|---|---|
| C-001 códigos PT SCREAMING_SNAKE | ✅ | `spec.md §Estados de erro` + T-004 messages |
| C-002 cor auto-detect | ✅ | FR-018 + T-011 (supports_color) + T-012 (--no-color) |
| C-003 flag `--list-rules` fora, docs dentro | ✅ | T-013 (atualizar harness/README.md) |

---

## 9. Consistência interna

- [x] Nomenclatura consistente entre spec, plan e tasks (`Diagnostic`, `Heading`, `Link`, nomes de função).
- [x] Nenhum FR sem task.
- [x] Nenhuma task sem FR ou decisão técnica justificada.
- [x] Nenhuma migration (não aplicável — projeto sem banco).
- [x] Nenhuma integração externa (não aplicável — invariante Camada 1).
- [x] Nenhum FR contradiz Camada 1.
- [x] Ordem de implementação respeita dependências (T-001 → T-016 linear).

---

## 10. Problemas detectados

| # | Descrição | Gravidade | Ação recomendada | Status |
|---|---|---|---|---|
| 1 | FR-017 (chaves extras no front-matter) sem teste explícito; só comportamento inferido do pyyaml. | 🟡 média | Adicionar teste `test_cli_unknown_frontmatter_key_accepted` em T-004 + fixture `unknown_key.md` em T-002. | ✅ **Resolvido** — `tasks.md` T-002 e T-004 atualizados no mesmo commit desta remediação. |
| 2 | Decisão "1 arquivo único" sem critério quantitativo de quando refactorar. | 🟢 baixa | Registrar em `plan.md §6` como "se `lint_artefato.py` ultrapassar 350 linhas de código (excluindo docstrings e blank lines), abrir ADR-004 e fragmentar em módulos." | ✅ **Resolvido** — `plan.md §6` atualizado. |
| 3 | Edge cases "link sobe raiz", "chave extra front-matter", "URL-encoded" sem teste em T-014. | 🟡 média | Adicionar 3 mini-testes no DoD de T-014. | ✅ **Resolvido** — `tasks.md` T-014 atualizado com 3 mini-testes. |

**Todos os 3 problemas resolvidos em linha.** Nenhum bloqueia Fase 7.

---

## 11. Riscos assumidos

| # | Descrição | Autor | Justificativa | Mitigação futura |
|---|---|---|---|---|
| 1 | [RISCO ASSUMIDO] Regex de heading dentro de bloco de código `` ``` `` aninhado mal fechado pode gerar falso positivo. | humano (D-001) | Probabilidade baixa nos artefatos da skill (templates não aninham code blocks irregularmente). | Se W2 dogfood expor caso real: migrar para AST via ADR. |
| 2 | [RISCO ASSUMIDO] Âncoras de link (`#section`) não validadas. | humano (D-003) | Escopo M1; muito custo de parsing por pouco benefício. | M2 pode estender. |
| 3 | [RISCO ASSUMIDO] Edge case "arquivo > 10 MB" não coberto. | humano (spec.md) | Zero artefatos atuais da skill chegam perto; risco extremamente raro. | M2 task específica. |

Nenhum risco é bloqueador.

---

## 12. Veredicto final

- [x] **✅ Análise limpa com 3 problemas menores corrigidos em linha**
- [ ] 🟡 Análise com riscos conscientes (não aplicável — os 3 riscos são documentados mas não alteram o veredicto)
- [ ] 🔴 Bloqueada

**Veredicto:** 🟢 **LIMPA.** Pode seguir para Fase 7 Implement com os ajustes abaixo aplicados antes do primeiro commit de código.

**Ajustes a aplicar antes de Fase 7 (em commits pequenos nesta mesma branch):**
1. `tasks.md` T-004: adicionar teste `test_cli_unknown_frontmatter_key_accepted` + fixture `unknown_key.md`.
2. `tasks.md` T-014: adicionar 3 mini-testes explícitos (link sobe raiz, chave extra, URL-encoded).
3. `plan.md` §6: adicionar critério quantitativo "350 linhas de código → ADR-004 + refactor em módulos".

Assinado por: Thiago Loumart (self-review, time=1)
Data: 2026-04-17
