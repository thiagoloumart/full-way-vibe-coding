---
artefato: review
fase: 10
dominio: [any]
schema_version: 1
requer:
  - "1. Escopo do diff"
  - "3. Verificações mínimas (Manual §17)"
  - "4. Aderência à constituição"
  - "5. Sinal de regras de negócio sensíveis (Manual §5.4)"
  - "9. Resultado de testes"
  - "10. Veredicto"
---

# Review — [Nome do Módulo]

**Branch:** `NNN-nome-do-modulo`
**Data:** [YYYY-MM-DD]
**Revisor:** [humano]
**Status:** Aprovada | Pendente | Reprovada

---

## 1. Escopo do diff
- Commits: [lista de SHAs]
- Linhas alteradas (aprox.): [N]
- Arquivos alterados: [N]

## 2. Arquivos alterados por categoria
| Categoria | Arquivos |
|---|---|
| Código de produção | ... |
| Migrations | ... |
| Testes | ... |
| Docs | ... |
| Configuração | ... |
| Infra | ... |

## 3. Verificações mínimas (Manual §17)
- [ ] **Arquivos alterados** — todos previstos no plano? (Se não, justificar.)
- [ ] **Migrations criadas** — reversíveis? idempotentes? testadas?
- [ ] **Testes criados** — cobrem sucesso e erro? cobrem edge cases?
- [ ] **Rotas alteradas** — expostas como deveria? versionadas?
- [ ] **Policies / permissões alteradas** — respeitam o modelo de papéis?
- [ ] **Integrações externas alteradas** — timeout, retry, fallback, idempotência?

## 4. Aderência à constituição
- [ ] Estrutura de pastas respeitada.
- [ ] Convenções de código respeitadas.
- [ ] Nenhuma lib nova não autorizada.
- [ ] Logs seguem padrão (nível, correlação, sem PII).

## 5. Sinal de regras de negócio sensíveis (Manual §5.4)
- [ ] Nenhuma regra de cobrança/permissão/estorno/deleção/expiração/visibilidade/histórico/auditoria foi decidida no código sem passar por clarify.

## 6. Observações / pontos estranhos
- [ponto 1] — [o que chamou atenção]
- [ponto 2]

## 7. Dívidas conhecidas / TODO
- [ ] [item] — [quem resolve; quando]

## 8. CRM / Agentes / SaaS (se aplicável — Manual §29)
- [ ] Cada automação tem: gatilho, contexto, decisão, ação, bloqueio, fallback, log, critério de sucesso, falso positivo.
- [ ] Logs auditáveis em todas as ações.
- [ ] Papéis respeitados nas permissões.

## 9. Resultado de testes
- [ ] Suíte completa verde.
- [ ] Testes de regressão verdes.
- [ ] Quickstart executado manualmente.

## 10. Veredicto
- [ ] ✅ Aprovada — pode mergar.
- [ ] 🟡 Aprovada com dívidas registradas — mergar após ajuste.
- [ ] 🔴 Reprovada — voltar para fase [X].

Assinado por: [humano]
