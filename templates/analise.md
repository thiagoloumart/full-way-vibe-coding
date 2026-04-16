# Análise Cruzada — [Nome do Módulo]

**Entradas:** `constitution.md` v<x>, `spec.md` v<x>, `clarify.md` v<x>, `plan.md` v<x>, `tasks.md` v<x>
**Data:** [YYYY-MM-DD]
**Status:** 🟢 Limpa | 🟡 Com riscos assumidos | 🔴 Bloqueada

---

## 1. Resumo executivo
- Problemas detectados: [N]
- Bloqueadores (severidade alta): [N]
- Riscos assumidos conscientemente: [N]
- Veredicto: [pode seguir | deve voltar para fase X]

## 2. Matriz Spec × Plano
| FR | Plano cobre? | Arquivo(s) / Contrato | Task | Observação |
|---|---|---|---|---|
| FR-001 | ✅ | ... | T-001 | ... |
| FR-002 | ⚠️ | ... | T-003 | cobertura parcial |
| FR-003 | ❌ | — | — | falta no plano |

## 3. Matriz Spec × Tasks
| FR | Task(s) | Observação |
|---|---|---|

## 4. Matriz Constituição × Plano
| Decisão técnica | Regra da constituição | Alinhamento | Observação |
|---|---|---|---|

## 5. Matriz Spec × Constituição
| Requisito da spec | Conflito com constituição? | Resolução |
|---|---|---|

## 6. Matriz Edge Cases × Tratamento
| Edge case | Onde é tratado | Teste? | Observação |
|---|---|---|---|

## 7. Regras sensíveis × Clarify (Manual §5.4)
| Tema | Decidido em | Autor | OK |
|---|---|---|---|
| Cobrança | C-00X | humano | ✅ |
| Permissão | C-00Y | humano | ✅ |
| Estorno | — | — | ❌ falta |
| Deleção | ... | ... | ... |
| Expiração | ... | ... | ... |
| Visibilidade | ... | ... | ... |
| Histórico | ... | ... | ... |
| Auditoria | ... | ... | ... |

## 8. Brownfield — duplicação
| Entidade/arquivo/rota proposto | Já existe algo similar? | Onde | Ação |
|---|---|---|---|

## 9. Consistência interna
- [ ] Nomenclatura consistente entre spec, plan e tasks.
- [ ] Nenhum FR sem task.
- [ ] Nenhuma task sem FR ou decisão técnica justificada.
- [ ] Nenhuma migration sem rollback.
- [ ] Nenhuma integração externa sem timeout/retry/fallback.

## 10. Problemas detectados
| # | Descrição | Gravidade | Ação recomendada | Status |
|---|---|---|---|---|
| 1 | ... | 🔴 | corrigir em [plan/spec] | resolvido/pendente |

## 11. Riscos assumidos
| # | Descrição | Autor | Justificativa | Mitigação futura |
|---|---|---|---|---|

## 12. Veredicto final
- [ ] Análise limpa
- [ ] Análise com riscos conscientes
- [ ] Bloqueada — deve voltar à fase [...]

Assinado por: [humano]
Data: [YYYY-MM-DD]
