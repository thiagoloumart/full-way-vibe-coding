# Checklist — Qualidade do Plano

Aplicar no fim da Fase 4.

## Estrutura
- [ ] Fases de implementação existem e estão numeradas.
- [ ] Cada fase tem **objetivo isolado**.
- [ ] Cada fase tem **critério de pronto** mensurável.
- [ ] Dependências entre fases estão explícitas.

## Cobertura
- [ ] Lista nominal de arquivos criados/alterados em cada fase.
- [ ] Contratos técnicos (endpoints, funções, eventos) descritos.
- [ ] Modelo de dados coerente com Key Entities da spec.
- [ ] Integrações externas listadas com timeout/retry/fallback/idempotência.
- [ ] Pré-requisitos (env vars, seeds, credenciais) listados.

## Aderência
- [ ] Plano respeita a constituição (ou exceções estão registradas).
- [ ] Plano não introduz biblioteca duplicada.
- [ ] Em brownfield: análise do repo confirma reaproveitamento antes de criar novo.

## Decisões
- [ ] Cada decisão técnica tem justificativa.
- [ ] Opções consideradas estão descritas para decisões relevantes.

## Qualidade operacional
- [ ] Plano de rollback descrito (migrations reversíveis, feature flag, estratégia de deploy).
- [ ] Observabilidade planejada (logs, métricas, alertas, traces).
- [ ] Riscos técnicos listados com mitigação.

## Exequibilidade
- [ ] Cada fase pode ser testada antes de avançar.
- [ ] Nenhuma fase exige saber algo que só seria descoberto implementando.

## Validação
- [ ] Humano aprovou o plano.

Se algum item ficou `❌`: corrigir antes da Fase 5.
