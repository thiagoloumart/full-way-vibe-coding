---
artefato: adr
fase: null
dominio: [any]
schema_version: 1
adr_id: ADR-NNN
status: Proposta
camada_afetada: 2
data: YYYY-MM-DD
autor: [humano]
requer:
  - "Contexto"
  - "Decisão"
  - "Alternativas consideradas"
  - "Consequências"
  - "Relação com Constituição"
---

# ADR-NNN — [título curto da decisão]

**Status:** Proposta | Aceita | Superada por: ADR-MMM | Revertida por: ADR-MMM | Rejeitada
**Data:** [YYYY-MM-DD]
**Autor:** [humano responsável]
**Camada afetada:** [1 | 2]
**Bump de Constituição:** [major (vN → v(N+1).0) | minor (vN.M → vN.(M+1))]

---

## Contexto
[Por que esta decisão surge agora. Qual problema ela resolve. Qual aprendizado ou necessidade a precipitou.]

## Decisão
[Texto literal do que passa a valer. Quando fizer sentido, incluir "before/after".]

## Alternativas consideradas
1. **[Alternativa A]** — prós / contras / motivo de descarte.
2. **[Alternativa B]** — prós / contras / motivo de descarte.
3. **[Alternativa C]** — prós / contras / motivo de descarte.

## Consequências
- **Positivas:** [o que fica melhor]
- **Negativas / trade-offs:** [o que fica pior ou mais caro]
- **Migração necessária:** [o que módulos existentes precisam ajustar; prazo]
- **Novas obrigações:** [o que passa a ser exigido em módulos futuros]

## Relação com Constituição
- Esta ADR **altera** a seção [§X] da Constituição (Camada [1|2]).
- Esta ADR **NÃO altera** Camada [1|2].
- Declaração explícita de Camada 1: ["Esta ADR não altera nenhum item de Camada 1." | "Esta ADR altera Camada 1 §Y — aprovação humana de [papel] em [data]."]

## Relação com outros artefatos
- ADRs relacionadas: [ADR-00A, ADR-00B]
- Módulos impactados imediatamente: [lista de módulos já existentes que precisam se adequar]
- `decision_log.md` que passam a citar esta ADR: [referência]

## Plano de reversão (se aplicável)
[Se esta ADR for revertida depois, o que precisa ser feito. Deixar este plano facilita rollback futuro.]

## Aprovação
| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor | ... | ... | ✓ |
| Revisor 1 | ... | ... | ✓ |
| Revisor 2 (se Camada 1) | ... | ... | ✓ |
| Compliance (se D2 regulatório) | ... | ... | ✓ |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| YYYY-MM-DD | Proposta | Criada |
| YYYY-MM-DD | Aceita | Aprovada por [nome] |
| YYYY-MM-DD | — | (se revertida ou superada) |
