# Decision Log — [Nome do Módulo]

**Referência:** `bmad.md` v<x>
**Data de abertura:** [YYYY-MM-DD]
**Status:** Em andamento | Fechado

Registro auditável de decisões **estratégicas** derivadas do BMAD (Fase 0.5) e de revisões posteriores quando Clarify/Analyze/Implement forçarem mudança. Uma entrada `D-NNN` por decisão.

---

## D-001 — [Tema curto]

**Origem:** [BMAD §4.1 | Clarify C-00X | Analyze 6.8 | Implement]
**Contexto:** [o que motivou a decisão em 2–3 frases]

**Decisão:** [frase afirmativa do que foi escolhido]

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| A | ... | ... | ... | ✅ escolhida |
| B | ... | ... | ... | ❌ descartada — motivo: ... |
| C | ... | ... | ... | ❌ descartada — motivo: ... |

**Riscos aceitos:**
- [RISCO ASSUMIDO] ...
- [RISCO ASSUMIDO] ...

**Critérios de invalidação:** (o que força revisão)
- Se ...
- Se ...

**Hipóteses associadas:** (a validar)
- [ ] ...
- [ ] ...

**Autor:** humano
**Data:** [YYYY-MM-DD]
**Impacto:** (o que depende dessa decisão — FRs futuros, stories, arquivos, contratos)

---

## D-002 — [Tema]
...

---

## Decisões em regras sensíveis (Manual §5.4)

Estas **não podem** ficar a cargo da IA. Cada tema aplicável ao módulo precisa ter uma decisão com autor humano.

| Tema | Aplica-se? | Decisão | Autor | Ref |
|---|---|---|---|---|
| Cobrança | sim/não | ... | humano | D-00X |
| Permissão / autorização | ... | ... | humano | ... |
| Estorno / cancelamento | ... | ... | humano | ... |
| Deleção | ... | ... | humano | ... |
| Expiração | ... | ... | humano | ... |
| Visibilidade entre papéis | ... | ... | humano | ... |
| Histórico | ... | ... | humano | ... |
| Auditoria | ... | ... | humano | ... |

---

## Revisões posteriores

Se uma decisão for alterada em fase futura (Clarify, Analyze técnica, Implement):

1. **Não sobrescrever** `D-NNN` existente.
2. Registrar **nova** `D-NNN` com campo `Origem: revisão de D-00Y` e justificativa da mudança.
3. Marcar `D-00Y` original com status `SUPERADA POR D-NNN`.
4. Propagar efeitos para briefing/spec/plano antes de avançar.

---

**Gate de fechamento:**
- [ ] Cada decisão tem alternativas com prós/contras.
- [ ] Nenhuma linha de descarte está vazia.
- [ ] Riscos aceitos marcados `[RISCO ASSUMIDO]`.
- [ ] Critérios de invalidação explícitos.
- [ ] Regras sensíveis aplicáveis todas decididas por humano.
- [ ] Humano assinou fechamento: "OK — decision log fechado".
