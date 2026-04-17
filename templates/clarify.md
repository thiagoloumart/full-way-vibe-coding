---
artefato: clarify
fase: 3
dominio: [any]
schema_version: 1
requer:
  - "Decisões sobre regras sensíveis (Manual §5.4)"
---

# Clarificação — [Nome do Módulo]

**Referência:** `spec.md` v<x>
**Data de abertura:** [YYYY-MM-DD]
**Status:** Em andamento | Fechada

Cada item abaixo remove **ambiguidade, omissão, contradição ou falsa obviedade** identificada na spec.

---

## C-001 — [Tema curto]

**Origem:** [FR-XXX | User Story Y | Edge Case Z | `[NEEDS CLARIFICATION]` do spec]
**Pergunta:** [texto exato colocado ao humano]

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | ... | ... | ... | ... |
| B | ... | ... | ... | ... |
| C | ... | ... | ... | ... |

**Recomendação da IA:** [letra] — porque [motivo curto]

**Decisão tomada:** [letra ou resposta livre]
**Autor:** humano / `[RISCO ASSUMIDO]`
**Justificativa:** [por que]
**Atualizações aplicadas na spec:** [FR-XXX foi alterado; User Story Y ganhou AC novo]

---

## C-002 — [Tema]
...

---

## Decisões sobre regras sensíveis (Manual §5.4)

Estas não podem ficar a cargo da IA. Listar e garantir que cada uma foi decidida por humano.

| Tema | Decisão | Autor | Referência |
|---|---|---|---|
| Cobrança | ... | humano | C-00X |
| Permissão | ... | humano | C-00Y |
| Estorno | ... | humano | C-00Z |
| Deleção | ... | humano | ... |
| Expiração | ... | humano | ... |
| Visibilidade | ... | humano | ... |
| Histórico | ... | humano | ... |
| Auditoria | ... | humano | ... |

---

**Gate de fechamento:**
- [ ] Zero `[NEEDS CLARIFICATION]` na spec.
- [ ] Cada C-NNN aplicada na spec.
- [ ] Regras sensíveis todas decididas por humano.
- [ ] Humano assinou fechamento: "OK — clarify fechada".
