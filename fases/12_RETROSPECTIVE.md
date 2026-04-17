# Fase 12 — Retrospective

> Captura de aprendizado pós-merge. A fase que fecha o ciclo e alimenta os próximos.

## Objetivo
Transformar a execução do ciclo (da ideia ao merge/go-live/publicação) em aprendizado formal, rastreável e acionável. Decisões tomadas em `decision_log.md` são revisitadas contra o que aconteceu de fato. Propostas de mudança estrutural viram ADR globais. A Constituição é atualizada quando o aprendizado justifica.

## Entradas
- `review.md` do módulo (Fase 10, aprovada).
- `decision_log.md` com todas as `D-NNN`.
- `bmad.md` com os pre-mortems e caminhos descartados.
- `analyze.md` com riscos assumidos.
- Commits da branch (histórico real da implementação).
- KPIs ou métricas observadas após o merge/go-live/publicação (quando disponíveis).

## Saídas
- `retrospective.md` (usar [`../templates/retrospective.md`](../templates/retrospective.md)) com:
  - KPI previsto vs observado.
  - Decisões revisitadas (cada `D-NNN` ganha veredicto: sustentada / parcialmente sustentada / revertida).
  - Propostas de ADR global.
  - Propostas de atualização de Constituição (Camada 2 por ADR minor; Camada 1 por major + aprovação).
  - Aprendizados gerais para próximos ciclos.

## Materialização por domínio
- **D1 software:** ver [`../domains/software.md §Fase 12`](../domains/software.md#materialização-fase-a-fase). Foco em bugs que escaparam, cobertura de teste, falhas de análise em Fase 6, overengineering/underengineering observado.
- **D2 processo:** ver [`../domains/processo.md §Fase 12`](../domains/processo.md#materialização-fase-a-fase). Foco em SLAs batidos/furados, exceções não previstas, compliance em operação real.
- **D3 playbook:** ver [`../domains/playbook.md §Fase 12`](../domains/playbook.md#materialização-fase-a-fase). Foco em taxa de decisão sustentada após N usos, critérios mal calibrados, anti-padrões reais descobertos.

## Condução

### 12.a — Revisão de decisões
Para cada `D-NNN` em `decision_log.md`:
1. Qual foi a decisão?
2. O que aconteceu de fato?
3. Veredicto: sustentada / parcialmente sustentada / revertida.
4. Se revertida: registrar nova entrada `D-NNN-REVISED` ou `D-MMM` citando a revisão.
5. Se padrão de reversão se repete entre módulos: **proposta de ADR global**.

### 12.b — Revisão de pre-mortems (BMAD §3.4)
Os modos de falha previstos em `bmad.md §3.4` aconteceram?
- **Aconteceu como previsto** → mitigação planejada funcionou; documentar.
- **Aconteceu diferente** → update no pre-mortem canônico para próximo ciclo.
- **Não aconteceu** → ok; registrar para calibrar estimativas futuras.

### 12.c — Revisão de riscos assumidos
Para cada `[RISCO ASSUMIDO]` em `analyze.md` ou `clarify.md`:
1. O risco materializou?
2. Se sim: custo observado; revisitar se a decisão de assumir foi correta.
3. Se não: cache de confiança +1; próxima decisão similar pode ser feita mais rápida.

### 12.d — KPI previsto vs observado
- Qual era o `SC-NNN` ou KPI previsto? (spec D1, briefing D2, métrica-eficácia D3.)
- Qual foi o observado? Em que janela?
- Gap: por quê?
- Se gap for estrutural (decisão ruim na Fase 0.5 ou Fase 2): proposta de atualização de Constituição ou de `perguntas-padrao.md`.

### 12.e — Aprendizados para próximos ciclos
- O que **faríamos diferente** se começássemos agora?
- O que **faríamos igual** sem pensar duas vezes?
- O que **não sabíamos** que vale documentar para o próximo autor de spec?

## Riscos da fase
- Retrospectiva vazia ("foi ok"). Aceitável apenas se todas as `D-NNN` foram sustentadas e não há gap de KPI — mesmo assim, exigir 1 linha de aprendizado.
- Autoflagelação sem ação. Se uma `D-NNN` foi ruim, **proposta concreta** de mudança, não lamento.
- Propor ADR para todo aprendizado (inflação de ADR). Usar critério: só vira ADR se o aprendizado afeta **decisões futuras de outros módulos**.
- Não contestar pre-mortem que não aconteceu — pode virar complacência.

## Gate de avanço (fim do ciclo)
- [ ] `retrospective.md` preenchido, com todas as seções do template.
- [ ] Cada `D-NNN` do `decision_log.md` tem veredicto.
- [ ] Cada `[RISCO ASSUMIDO]` foi revisitado.
- [ ] Propostas de ADR criadas em `governanca/adr-global.md` (quando houver).
- [ ] Propostas de atualização de Constituição registradas (quando houver).
- [ ] Aprendizados transversais compartilhados com o time (comunicação; não apenas arquivo).

## O que invalida a fase
- `D-NNN` sem veredicto.
- KPI não observado e retrospectiva adiada indefinidamente — preferível fechar com `[NEEDS CLARIFICATION: KPI a medir em T+N]` do que nunca fechar.
- Propostas de ADR sem contexto suficiente para que outro humano decida (deve incluir: contexto, decisão proposta, alternativas, consequências).

## Como revisar
- Se a retrospectiva revela que a Fase 6 (Analyze) falhou sistematicamente: revisar `fases/06_ANALYZE.md` e `checklists/pre-implementacao.md`.
- Se a retrospectiva revela que uma regra sensível foi decidida implicitamente durante o código: proposta de atualização em `filosofia.md §7` e em `protocolos/travamento.md`.
- Se a retrospectiva revela que o `bmad.md` descartou um caminho que teria sido melhor: documentar em `protocolos/antialucinacao.md` como sinal de alarme.

## Sinal de travamento
- Impossível obter KPI observado porque não foi instrumentado → bloquear merge futuro deste tipo sem instrumentação; registrar em `governanca/metricas.md`.
- Par sênior indisponível para validar retrospectiva em D3 → travar publicação de v1.1 do playbook até que haja revisão cega externa.
