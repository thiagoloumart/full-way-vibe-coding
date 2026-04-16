# Tasks — [Nome do Módulo]

**Referência:** `plan.md` v<x>
**Data:** [YYYY-MM-DD]
**Status:** Draft | Aprovado | Em execução

Implementação **por fase** (Manual §12). Uma task = uma unidade executável pequena com dependências claras.

---

## Legenda
- 🟢 Baixo risco | 🟡 Médio | 🔴 Alto
- Estado: ⬜ pendente | 🔶 em andamento | ✅ feita | ⛔ bloqueada

---

## Fase F1 — [Nome]

### T-001 — [Título acionável]
- **Estado:** ⬜
- **Depende de:** nenhuma
- **Descrição:** [o que fazer]
- **Arquivos:** `path/...`
- **Contrato afetado:** [endpoint / função / evento]
- **Testes exigidos:**
  - Sucesso: [...]
  - Erro: [...]
  - Edge: [...]
- **Definition of Done:**
  - [ ] Código escrito
  - [ ] Testes escritos e passando
  - [ ] Logs/metrics quando aplicável
  - [ ] Revisão mínima feita
  - [ ] Quickstart atualizado, se aplicável
- **Risco:** 🟢 — [motivo curto]

### T-002 — [Título]
- **Estado:** ⬜
- **Depende de:** T-001
- ...

---

## Fase F2 — [Nome]

### T-010 — [Título]
- **Estado:** ⬜
- **Depende de:** T-00X
- ...

---

## Matriz de rastreabilidade (FR ↔ Task)
| FR | Task(s) | Observação |
|---|---|---|
| FR-001 | T-001, T-002 | ... |
| FR-002 | T-003 | ... |

## Matriz de rastreabilidade (Edge Case ↔ Task/Teste)
| Edge case | Task | Cobertura |
|---|---|---|

---

**Checklist antes de aprovar:**
- [ ] Cada task tem título acionável.
- [ ] Cada task tem DoD concreto.
- [ ] Dependências explícitas.
- [ ] Cobertura completa do plano (nenhum arquivo órfão).
- [ ] Testes distribuídos ao longo das tasks (não concentrados no final).
