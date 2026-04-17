---
artefato: decision_log
fase: 0.5
dominio: [software]
schema_version: 1
requer:
  - "Decisões em regras sensíveis (Manual §5.4)"
  - "Revisões posteriores"
---

# Decision Log — `lint_artefato.py` (M1 lint mínimo)

**Referência:** `bmad.md` v0.1
**Data de abertura:** 2026-04-17
**Status:** Em andamento

Decisões estratégicas tomadas durante o BMAD W1 track A e revisões posteriores.

---

## D-001 — Stack e abordagem de parsing

**Origem:** BMAD §4.1 (Caminho B escolhido)
**Contexto:** Precisamos escolher como parsear front-matter YAML e extrair seções/links do corpo Markdown. ADR-002 já decidiu `pyyaml` como dep única; esta decisão detalha a abordagem para o corpo.

**Decisão:** Usar `pyyaml.safe_load` para front-matter; usar regex Python built-in (`re`) para extrair headings (`^#{2,3}\s+(.+)$`) e links (`\[([^\]]+)\]\(([^)]+)\)`) do corpo. Nada de AST completo de Markdown.

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| A | Regex puro (sem pyyaml) | zero deps | YAML aninhado frágil | ❌ descartada — motivo: parse manual de YAML escala mal |
| B | pyyaml + regex (escolhida) | dep única já aprovada; simples | regex não é AST-perfeito | ✅ escolhida |
| C | AST Markdown (markdown-it-py) | precisão total | overengineered; 2+ deps transitivas | ❌ descartada — motivo: custo > benefício em M1 |

**Riscos aceitos:** [RISCO ASSUMIDO] regex de heading pode matchar `##` dentro de bloco de código ```. Probabilidade baixa em artefatos da skill; migrar para C em ADR futura se sintomas aparecerem.
**Critérios de invalidação:** ≥3 artefatos falsamente rejeitados em W2 → abre ADR para migrar.
**Hipóteses associadas:** regex cobre 100% dos headings atuais nos templates (a testar).
**Autor:** humano (Thiago Loumart)
**Data:** 2026-04-17
**Impacto:** define estrutura interna de `lint_artefato.py`; afeta FR-NNN futuros na spec.

---

## D-002 — Script é read-only (invariante)

**Origem:** BMAD §2.6 (regra sensível de Deleção) + `harness/README.md §Invariantes`.
**Contexto:** O lint pode emitir relatórios mas nunca edita o artefato validado. Isso é invariante para qualquer versão do script (M1, M2, M3+).

**Decisão:** `lint_artefato.py` **nunca** escreve no arquivo validado. Apenas lê, parseia, valida, imprime relatório em stdout (ou JSON se `--format=json`) e retorna código 0/1.

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| A | Read-only estrito (escolhida) | invariante simples de auditar; zero risco de corromper artefato | autor precisa editar manualmente após ler relatório | ✅ escolhida |
| B | Auto-fix opcional (`--fix`) | conveniência | qualquer bug no auto-fix = corrupção silenciosa; viola princípio §5.4 sobre Deleção | ❌ descartada — motivo: risco > conveniência |

**Riscos aceitos:** nenhum — esta decisão é invariante de segurança.
**Critérios de invalidação:** nunca. Se auto-fix for desejado no futuro, deve ser **outro script** (`fix_artefato.py`) com flag explícita e ADR dedicada.
**Autor:** humano (Thiago Loumart)
**Data:** 2026-04-17
**Impacto:** FRs da spec do lint precisam refletir "MUST NOT modify the file under validation".

---

## D-003 — Escopo de validações em M1

**Origem:** BMAD §1.4 (subproblemas MECE) + carta sênior §5 princípio 3 (enforcement mínimo antes de escala).
**Contexto:** Quais validações o lint faz em M1? Tentação de escalar para `gate_fase.py`, schemas custom por artefato, validação semântica — tudo adiado.

**Decisão:** M1 implementa exatamente 3 validações:
1. Front-matter YAML existe, parseia sem erro, tem campos mínimos (`artefato`, `fase`, `dominio`, `schema_version`, `requer`).
2. Cada item em `requer:` (lista) aparece como heading (`##` ou `###`) no corpo do arquivo, comparado após normalização de whitespace.
3. Cada link relativo interno no corpo (`[x](./foo.md)` ou `[x](../foo.md)`) aponta para arquivo que existe no filesystem.

Tudo mais (schemas YAML custom, contra-referências D-NNN ↔ decision_log, validação semântica de "≥2 caminhos em Analyze", `gate_fase`, `smoke_test`, `lint_constituicao`) fica para M2 conforme `harness/README.md §Estado alvo`.

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| A | Hello-world (só parseia front-matter) | trivial | não protege nada real | ❌ descartada |
| B | Core 3 validações (escolhida) | paga dívida dim 6; libera W2 com régua | cobre 80% dos drifts conhecidos, não 100% | ✅ escolhida |
| C | Harness completo (lint+gate+smoke+schemas) em M1 | máxima proteção | viola princípio "mínimo antes de escalar"; atrasa dogfood | ❌ descartada — motivo: anti-padrão #2 da carta sênior |

**Riscos aceitos:** [RISCO ASSUMIDO] 20% de drifts possíveis (validação semântica, contra-referência) ficam não-detectados até M2. Mitigação: self-review com `templates/review.md` cobre revisão humana nesse gap.
**Critérios de invalidação:** se W2 dogfood mostrar drifts críticos não-detectados → acelerar ADR-NNN para habilitar validação semântica antes de W3.
**Autor:** humano (Thiago Loumart)
**Data:** 2026-04-17
**Impacto:** define FR-001 a FR-0NN da spec; baseline para retrospective W3.

---

## Decisões em regras sensíveis (Manual §5.4)

| Tema | Aplica-se? | Decisão | Autor | Ref |
|---|---|---|---|---|
| Cobrança | não | — | — | — |
| Permissão / autorização | não | — | — | — |
| Estorno / cancelamento | não | — | — | — |
| Deleção | **sim** | Script é read-only — invariante permanente | humano | D-002 |
| Expiração | não | — | — | — |
| Visibilidade entre papéis | não | — | — | — |
| Histórico | não | — | — | — |
| Auditoria | tangencial | Relatórios ficam em logs do CI (GitHub default, 90 dias); não é audit log §5.4 | humano | BMAD §2.6 |

---

## Revisões posteriores

_Nenhuma ainda._ Revisões virão após W1 implementação ou após W2 dogfood expor necessidades não previstas.

---

**Gate de fechamento:**
- [x] Cada decisão tem alternativas com prós/contras.
- [x] Nenhuma linha de descarte está vazia.
- [x] Riscos aceitos marcados `[RISCO ASSUMIDO]`.
- [x] Critérios de invalidação explícitos.
- [x] Regras sensíveis aplicáveis decididas por humano (Deleção em D-002).
- [ ] Humano assinou fechamento: "OK — decision log fechado" — **pendente; será assinado ao fim de W1 track A.**
