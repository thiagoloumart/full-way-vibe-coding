# Fase 8 — Testes

> Manual §15. "Testes são obrigatórios. Se o teste falhar, a feature não está pronta."

## Objetivo
Validar que a implementação cumpre a spec e resiste aos edge cases.

## Entradas
- Código implementado.
- `spec.md` (fonte de critérios de aceite).
- `analyze.md` (matriz de edge cases × tratamento).

## Saídas
- Suíte de testes passando.
- Relatório curto incluso em `review.md`.

## Toda implementação deve (Manual §15)
- Criar testes de **sucesso**.
- Criar testes de **erro**.
- Cobrir **edge cases importantes**.
- **Preservar testes existentes** (regressão zero).

## Sempre testar (Manual §15)
- Entrada inválida.
- Ausência de campo obrigatório.
- Duplicação.
- Permissão negada.
- Falha parcial (parte da operação ok, parte não).
- Falha total.
- Rollback / estorno quando aplicável.

## Regra extra (Manual §15)
Mesmo se o responsável não programa em profundidade, deve pedir testes adicionais para:
- Cenários extremos.
- Dados inválidos.
- Falhas de API.
- Conflitos de permissão.
- Regressão em features já existentes.

## Níveis de teste
- **Unit** — funções puras, pequenas unidades.
- **Integração** — componentes reais + banco em memória ou containerizado (sem mocks onde possível).
- **Contrato** — endpoints / eventos respeitam o contrato prometido na spec.
- **E2E / smoke** — fluxo principal ponta a ponta.
- **Regressão** — reexecutar os testes antigos para garantir que nada quebrou.

## Em projetos com CRM / agentes / SaaS (Manual §29)
Testar adicionalmente:
- Idempotência (o gatilho duplicado não duplica efeito).
- Condição de bloqueio (quando a automação não deve agir).
- Fallback (o que acontece se a ação falha).
- Log (o evento ficou registrado).
- Falso positivo (a automação não aciona para quem não deveria).

## Execução
1. Rodar toda a suíte.
2. Se falha: diagnosticar origem, corrigir, reexecutar (Manual §21). **Não pular.**
3. Garantir que a mudança nova não quebrou a antiga.
4. Persistir resultado.

## Riscos da fase
- Testar apenas caminho feliz.
- Mockar o que deveria ser integração real.
- Esconder falha com `.skip` ou timeout frouxo.
- Ignorar edge case declarado na spec.

## Gate de avanço
- [ ] Todos os testes verdes.
- [ ] Todos os edge cases da spec têm teste correspondente.
- [ ] Testes de regressão rodaram.
- [ ] Nenhum teste foi desativado para passar na fase.

## O que invalida a fase
- Teste desativado silenciosamente.
- Edge case da spec sem teste.
- Dependência externa mockada de forma que esconde erros reais (ex.: sempre retorna 200).

## Sinal de travamento
- Teste falha repetidamente e a causa raiz está na spec → voltar à spec, re-clarificar.
- Ambiente de teste instável → corrigir ambiente antes, não tolerar flakiness.
