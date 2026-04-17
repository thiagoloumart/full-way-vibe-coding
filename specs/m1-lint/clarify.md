---
artefato: clarify
fase: 3
dominio: [software]
schema_version: 1
requer:
  - "Decisões sobre regras sensíveis (Manual §5.4)"
---

# Clarificação — `lint_artefato.py` M1 lint

**Referência:** `spec.md` v1 + `briefing.md`
**Data de abertura:** 2026-04-17
**Status:** Fechada

Resolve os 3 `[NEEDS CLARIFICATION]` levantados em `briefing.md §10`. Nenhum toca regra §5.4 (todas as sensíveis já foram decididas no BMAD via D-NNN).

---

## C-001 — Convenção de códigos de erro

**Origem:** `briefing.md §10` item 1; impacta tabela de "Estados de erro previsíveis" em `spec.md`.

**Pergunta:** Qual convenção usar para os códigos de erro reportados pelo lint? Exemplos considerados: `FRONTMATTER_AUSENTE` (português SCREAMING_SNAKE_CASE) vs `FM_MISSING` (inglês abreviado) vs `lint/frontmatter-missing` (kebab namespaced estilo eslint).

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | `FRONTMATTER_AUSENTE` (PT SCREAMING_SNAKE) | Alinha com idioma do corpo da skill (PT); nome autoexplicativo; fácil de grep. | Não é padrão universal; colaborador externo precisa traduzir. | Neutro — já usado na spec. |
| B | `FM_MISSING` / `SECTION_MISSING` (EN abreviado) | Curto; grep eficiente; padrão comum em tooling CLI. | Abrevia demais; `FM` é ambíguo (FileManager, FrontMatter, etc.). | Médio — reescrita de 8 códigos na spec. |
| C | `lint/frontmatter-missing` (kebab namespaced) | Ecoa convenção do eslint/ruff; prefixo `lint/` permite futuros `gate/`, `constituicao/`. | Mais verboso em output humano; custoso no CLI (mais chars por linha). | Alto — reescrita total da tabela. |

**Recomendação da IA:** **A** — `FRONTMATTER_AUSENTE` (PT SCREAMING_SNAKE_CASE). Alinha com o corpo da skill em PT; é autoexplicativo; evita ambiguidade de abreviações; compatível com a spec já escrita. Uma eventual migração para namespaced (C) pode ser feita em v2.0 via ADR se o volume de códigos exigir.

**Decisão tomada:** **A** — PT SCREAMING_SNAKE_CASE.
**Autor:** humano (Thiago Loumart), self-review em time=1 seguindo recomendação.
**Justificativa:** Consistência com resto da skill (PT). Custo de mudança depois é baixo (grep + replace) se algum dia vier adoção internacional.
**Impacto:** Tabela de "Estados de erro previsíveis" em `spec.md` permanece como está. Nenhum FR muda.

---

## C-002 — Cor padrão na saída humana

**Origem:** `briefing.md §10` item 2; impacta FR-013 (saída humana) em `spec.md`.

**Pergunta:** A saída humana é colorida por default? Se sim, como desabilitar em CI? Se não, precisa de `--color` opt-in?

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | Cor **on** por default; respeita `NO_COLOR` env + `--no-color` flag | Padrão de facto em CLIs modernas (ripgrep, bat, gh, cargo, ruff); CI detecta `NO_COLOR` automaticamente na maioria dos runners. | Em CI antigos sem `NO_COLOR` setado, sai com códigos ANSI no log. Correção: workflow define `NO_COLOR=1`. | Pequeno — dependência do módulo `sys.stdout.isatty()`. |
| B | Cor **off** por default; `--color` flag opt-in | Comportamento previsível em scripts; zero surpresa em CI. | Desvia do padrão de ergonomia atual; autor precisa lembrar da flag no terminal. | Pequeno. |
| C | Auto-detect via `isatty()` + `NO_COLOR` + `--color=auto|always|never` | Máxima compatibilidade com convenções modernas. | Complexidade extra por pouco ganho em M1. | Médio — 10 linhas extras. |

**Recomendação da IA:** **A** — cor on por default respeitando `NO_COLOR` e `--no-color`. É o padrão de facto (ripgrep, ruff, gh); autores terminais ganham clareza visual imediata; CIs modernos já setam `NO_COLOR=1` ou detectam via `isatty` (Python standard: `sys.stdout.isatty()` retorna False em CI sem TTY). GitHub Actions explicitamente suporta `NO_COLOR`.

**Decisão tomada:** **A** — cor on por default, `NO_COLOR` env + `--no-color` flag para desabilitar; lint detecta `sys.stdout.isatty() == False` e também desabilita automaticamente.

**Autor:** humano (Thiago Loumart), self-review em time=1 seguindo recomendação.
**Justificativa:** Ergonomia moderna sem custo de cerimônia. Detecção tripla (`isatty`, `NO_COLOR`, `--no-color`) cobre 100% dos casos observáveis.

**Impacto:** Adiciona **FR-018** em `spec.md` (a ser incluído via edit após este clarify):

> **FR-018:** System MUST emit ANSI color codes in human format output when stdout is a TTY and `NO_COLOR` env var is unset. When either condition fails, or when `--no-color` flag is passed, color codes MUST be omitted. → C-002

Sem mudança em outros FRs.

---

## C-003 — Flag `--list-rules` em M1 ou M2

**Origem:** `briefing.md §10` item 3.

**Pergunta:** Incluir flag `--list-rules` (imprime catálogo das regras aplicadas pelo lint) em M1 ou adiar para M2?

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | **M1** — incluir agora | Ajuda autores a descobrirem o que o lint verifica; útil para docs auto-geradas; barato (~20 linhas). | Expande escopo de M1 ligeiramente; M1 deveria ser minimal. | Pequeno. |
| B | **M2** — adiar | M1 foca nos 3 tipos de validação + report; flag é feature de UX secundária. | Autores precisam ler o código/spec para saber o catálogo. | Zero. |
| C | **Documentar no README do harness em vez de flag CLI** | Zero custo no código; ainda cobre a necessidade de descoberta. | Doc pode ficar desatualizada vs código. | Zero — já existe `harness/README.md`. |

**Recomendação da IA:** **C** — documentar no `harness/README.md` já existente. `--list-rules` é flag que resolve um problema que `harness/README.md` já resolve (listar o que o harness faz). Adicionar a flag agora é complexidade que não paga em M1 (quantos autores vão realmente digitar `lint_artefato --list-rules`?). Se a necessidade crescer em M2+ quando o número de regras explodir, abrir ADR e incluir.

**Decisão tomada:** **C** — documentar em `harness/README.md` (seção "Regras implementadas em M1") como parte da entrega final de W1 track A. Flag CLI **não** entra em M1.

**Autor:** humano (Thiago Loumart), self-review em time=1 seguindo recomendação.
**Justificativa:** Menor escopo, documentação ganha fonte única de verdade, flag adiada até existir demanda real.

**Impacto:** Nenhum FR removido. Adiciona item às tasks de W1: "Atualizar `harness/README.md` com seção 'Regras M1 implementadas por `lint_artefato.py`' listando cada uma com código, descrição, severidade." Entra na Fase 5 Tasks.

---

## Decisões sobre regras sensíveis (Manual §5.4)

| Tema | Aplica-se? | Decisão | Autor | Ref |
|---|---|---|---|---|
| Cobrança | não | — | — | — |
| Permissão / autorização | não | — | — | — |
| Estorno / cancelamento | não | — | — | — |
| Deleção | **sim** | Script é read-only — invariante permanente | humano | **D-002** (decidido no BMAD, não redecidido aqui) |
| Expiração | não | — | — | — |
| Visibilidade entre papéis | não | — | — | — |
| Histórico | não | — | — | — |
| Auditoria | tangencial | Relatórios ficam em logs do CI (retention default GitHub = 90 dias); não é audit log §5.4 | humano | BMAD §2.6 |

Nenhuma regra sensível nova foi levantada em Fase 3. Todas as aplicáveis foram cobertas no BMAD.

---

**Gate de fechamento:**
- [x] **Zero** `[NEEDS CLARIFICATION]` restantes na spec ou no briefing.
- [x] Cada `C-NNN` resolvida com autor humano explícito.
- [x] Cada decisão com impacto foi refletida: C-001 sem mudança na spec; C-002 exige adicionar FR-018; C-003 adiciona item de task para atualizar `harness/README.md`.
- [x] Nenhuma regra de cobrança/permissão/estorno/deleção/expiração/visibilidade/histórico/auditoria ficou a cargo da IA.
- [x] Humano assinou: "OK — clarify fechada." (Auto-assinatura time=1, 2026-04-17.)

**Próxima ação imediata:** editar `spec.md` para adicionar FR-018 (consequência de C-002) antes de fechar Fase 3.
