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

# Plano Técnico — [Nome do Módulo]

**Referências:** `spec.md` v<x>, `clarify.md` v<x>, `constitution.md` v<x>
**Data:** [YYYY-MM-DD]
**Status:** Draft | Aprovado

---

## 1. Escopo do plano
- Módulo: [nome]
- Stories cobertas: P1 [..], P2 [..], ...
- Explicitamente fora do plano: [...]

## 2. Pré-requisitos
- Env vars necessárias: [lista]
- Dependências externas (contas, credenciais, webhooks): [lista]
- Seeds / dados de referência: [lista]
- Feature flags, se houver: [lista]

## 3. Fases de implementação
Implementação **por fase** (Manual §12). Cada fase é um incremento utilizável ou um degrau necessário.

### F1 — [Nome da fase]
- **Objetivo:** [o que F1 entrega isolado]
- **Depende de:** [nada | fases anteriores]
- **Arquivos criados/alterados:**
  - `path/to/file1.ext` — [propósito]
  - `path/to/file2.ext` — [propósito]
- **Entidades afetadas:** [lista]
- **Contratos (endpoints / funções / eventos):**
  - `POST /api/v1/<recurso>` — input: [...], output: [...], erros: [...]
  - `func <nome>(input)` — retorno: [...], exceções: [...]
- **Modelo de dados:**
  - Nova tabela `X` com campos [...]
  - Alteração na tabela `Y` adicionando coluna [...]
- **Integrações externas:** [se houver — serviço, auth, timeout, retry, fallback, idempotência]
- **Testes mínimos desta fase:** [sucesso / erro / edge / regressão]
- **Critério de "pronto":** [mensurável]
- **Riscos técnicos da fase:** [...]

### F2 — [Nome]
...

### F3 — [Nome]
...

## 4. Modelo de dados completo
```
Entidade A
  id : ...
  campo_1 : tipo
  campo_2 : tipo
  relações: A 1—N B

Entidade B
  ...
```

## 5. Integrações externas (consolidado)
| Serviço | Finalidade | Auth | Rate limit | Timeout | Retry | Fallback |
|---|---|---|---|---|---|---|

## 6. Decisões técnicas
| Decisão | Opções consideradas | Escolhida | Motivo | Alinha com constituição? |
|---|---|---|---|---|

## 7. Riscos técnicos e mitigações
| Risco | Probabilidade | Impacto | Mitigação |
|---|---|---|---|

## 8. Observabilidade planejada
- Logs: [o que registrar; em que nível]
- Métricas: [lista]
- Alertas: [condições]
- Traces: [pontos críticos]

## 9. Plano de rollback
- Migrations: [reversíveis? como?]
- Feature flag: [como desativar]
- Deploy: [estratégia — canary, blue/green, etc.]

---

**Checklist antes de aprovar:**
- [ ] Fases ordenadas e cada uma entrega valor ou um degrau claro.
- [ ] Cada fase tem critério de "pronto".
- [ ] Contratos descritos para todos os endpoints/funções/eventos.
- [ ] Modelo de dados coerente com Key Entities da spec.
- [ ] Cada decisão técnica tem justificativa.
- [ ] Plano respeita a constituição (ou exceções registradas).
- [ ] Em brownfield: análise do repo anexada no arquivo complementar ou no `analyze.md`.
