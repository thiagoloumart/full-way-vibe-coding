# Protocolo — Agentes e automações (Manual §29 ampliado)

> Extraído do Manual §29 e ampliado para cobrir D1, D2 e D3. Se o que está sendo construído envolve **agente autônomo**, **automação comercial**, **CRM**, **SaaS operacional**, **automação de processo** ou **decisão automatizada aplicando um playbook** — este protocolo é **obrigatório**.

## Princípio
Toda automação toma decisões. Toda decisão pode falhar. Automação sem especificação explícita de **como decide, o que faz, quando para, o que registra** é uma decisão cega que escala erro sem rastro.

Prioridade máxima (Manual §29):
- Confiabilidade operacional.
- Rastreabilidade de ações.
- Permissão por papel.
- Histórico de eventos.
- Tratamento de falhas.
- Impacto financeiro de automações.
- Não duplicação de lógica.
- Capacidade de evolução modular.

## Os 9 campos obrigatórios por automação

Toda automação/agente DEVE especificar os nove campos abaixo **antes de rodar em produção**. Ausência de qualquer um bloqueia Fase 10 (Review).

| # | Campo | O que descreve | Exemplo bom |
|---|---|---|---|
| 1 | **Gatilho** | O que dispara a execução | "webhook de checkout concluído com status=paid" |
| 2 | **Contexto lido** | Quais dados a automação consulta para decidir | "pedido.id, pedido.valor, cliente.status, último envio de email < 24h" |
| 3 | **Decisão tomada** | Qual caminho foi escolhido e por quê | "enviar email de confirmação porque valor > 0 e não há email recente" |
| 4 | **Ação executada** | O que efetivamente foi feito | "POST /email-service com template=confirmação + id do pedido" |
| 5 | **Condição de bloqueio** | Quando a automação **não** deve agir | "se cliente.opt_out=true OU se email enviado < 24h OU se valor ≤ 0" |
| 6 | **Fallback** | O que acontece se a ação principal falhar | "retry 3x com backoff exponencial; após 3 falhas, log em `alerts` + pausa automação até investigação manual" |
| 7 | **Log gerado** | O que fica registrado | "`audit_log`: (timestamp, automation_id, trigger, decision, action, result, duration_ms)" |
| 8 | **Critério de sucesso** | Como saber que funcionou | "email entregue em < 60s (retorno 2xx do provedor) + usuário não reclama em 24h" |
| 9 | **Risco de falso positivo** | Onde a automação pode errar feio | "enviar 2 emails para o mesmo pedido se webhook duplicar; mitigar via idempotência em pedido.id" |

## Materialização por domínio

### D1 — Software (agentes, CRM, SaaS)
- **Local dos 9 campos:** seção dedicada em `spec.md` (User Story inclui bloco "Automação: ..." com os 9 campos).
- **Rastreabilidade:** cada automação tem `D-NNN` em `decision_log.md`; alteração de qualquer dos 9 campos vira nova `D-NNN-REVISED`.
- **Teste obrigatório (Fase 8):**
  - Idempotência (gatilho duplicado não duplica ação).
  - Condição de bloqueio (testar cada uma das condições).
  - Fallback (simular falha da ação principal).
  - Falso positivo (testar o caso em que a automação **não** deveria agir mas poderia).

### D2 — Processo (automação operacional)
- Aplicável quando um passo do processo é automatizado (ex.: bot que gera fatura, RPA que atualiza CRM).
- Os 9 campos vão no `runbook.md` com destaque; o `mapa-to-be.md` marca esse passo como `[AUTOMATIZADO]` e referencia.
- Compliance: o campo **log gerado** (§7) é revisitado com rigor — se a automação toca dado pessoal, LGPD Art. 7° aplica.

### D3 — Playbook (decisão automatizada)
- Aplicável quando o playbook é operacionalizado como regra em sistema (ex.: aprovação de crédito automatizada com base na árvore).
- Os 9 campos vão no `plano-adocao.md`.
- **Cuidado crítico:** decisão automatizada precisa de **override manual** explícito e log do override; caso contrário a árvore do playbook se torna oráculo opaco.

## Protocolo de implementação

### Fase 2 (Spec / to-be / critérios)
Todo FR/passo/folha que descreve automação tem bloco:
```
Automação A-NNN
  Gatilho: ...
  Contexto lido: ...
  Decisão: ...
  Ação: ...
  Bloqueio: ...
  Fallback: ...
  Log: ...
  Sucesso: ...
  Falso positivo: ...
```

### Fase 3 (Clarify)
Automações com **risco financeiro**, **risco de deleção em massa** ou **risco regulatório** viram **obrigatoriamente** `[DECISÃO HUMANA]` em `clarify.md`. A IA não pode decidir em silêncio sobre:
- Que valor acima do qual a automação **não** deve agir sozinha.
- Quem recebe o fallback.
- Quem autoriza reativar após pausa por falha.

### Fase 6 (Analyze)
Matriz nova: "Automações × 9 campos × logs × testes". Qualquer automação com campo vazio bloqueia.

### Fase 10 (Review)
Checklist de pré-merge inclui:
- [ ] Cada automação da spec/to-be/playbook tem os 9 campos preenchidos.
- [ ] Cada automação tem teste de idempotência.
- [ ] Cada automação tem teste de fallback.
- [ ] Logs não expõem dado sensível.
- [ ] Papel operador da automação definido (quem pausa, reativa, investiga).

### Fase 12 (Retrospective)
Revisitar cada automação:
- Taxa de execução vs prevista.
- Taxa de fallback ativado.
- Casos em que bloqueio salvou o dia.
- Casos em que falso positivo aconteceu (proposta de D-NNN-REVISED).

## Exemplo — Ruim vs Bom

### Ruim
> "A automação envia email quando o pedido é concluído."

Tudo implícito. Sem gatilho definido, sem contexto declarado, sem bloqueio, sem fallback. Impossível auditar; impossível testar; impossível evoluir.

### Bom
```
Automação A-007 — email-confirmacao-checkout

Gatilho:
  Evento: webhook POST /hooks/checkout com status=paid
  Frequência máxima: 1 por (pedido.id)

Contexto lido:
  pedido.id, pedido.valor, pedido.cliente_id
  cliente.email, cliente.opt_out_transacional
  emails.ultimo_enviado(pedido.id)

Decisão:
  Se todos verdadeiros: enviar email.
    - pedido.valor > 0
    - cliente.opt_out_transacional = false
    - emails.ultimo_enviado(pedido.id) = NULL

Ação:
  POST /email-service
    template: "confirmacao-checkout-v3"
    destinatario: cliente.email
    variaveis: { pedido_id, valor_formatado }

Bloqueio:
  pedido.valor ≤ 0 → log "valor inválido" + não enviar
  cliente.opt_out_transacional = true → log "opt-out" + não enviar
  emails.ultimo_enviado(pedido.id) ≠ NULL → log "duplicação detectada" + não enviar

Fallback:
  Em caso de resposta não-2xx do email-service:
    Retry 1: +30s
    Retry 2: +5min
    Retry 3: +30min
  Após 3 falhas: alerta para canal #alerts-automacao + pausa A-007 até intervenção manual.

Log gerado (audit_log):
  (timestamp, "A-007", pedido.id, decisao, acao, resultado, duracao_ms)
  Retenção: 90 dias (LGPD Art. 16).

Critério de sucesso:
  Email entregue em < 60s (retorno 2xx) E zero reclamações no pedido.id em 24h.

Risco de falso positivo:
  Webhook duplicado → 2 emails ao mesmo cliente.
  Mitigação: unicidade por pedido.id (ver campo "Contexto lido" + Bloqueio).
```

## Checklist rápido (imprimível)

```
[ ] Gatilho declarado + frequência máxima
[ ] Contexto lido explícito (não "consulta o necessário")
[ ] Decisão = regra booleana ou árvore clara
[ ] Ação = operação atômica, com parâmetros
[ ] Bloqueio = lista explícita de NÃO-agir
[ ] Fallback = estratégia de retry + escalação
[ ] Log = campos + retenção
[ ] Sucesso = métrica verificável + janela
[ ] Falso positivo = cenário + mitigação
```

## Relação com outras peças da skill
- [`../filosofia.md §7`](../filosofia.md#7-regra-54--decisões-sensíveis-nunca-pela-ia-ampliada-para-3-domínios) — regras sensíveis que automação não decide.
- [`../fases/02_SPEC.md`](../fases/02_SPEC.md) — onde os 9 campos aparecem.
- [`../fases/06_ANALYZE.md`](../fases/06_ANALYZE.md) — matriz de automações.
- [`../fases/10_REVIEW.md`](../fases/10_REVIEW.md) — checklist de review para automações.
- [`../fases/12_RETROSPECTIVE.md`](../fases/12_RETROSPECTIVE.md) — revisão pós-operação.
- [`../protocolos/travamento.md`](travamento.md) — travar quando automação estaria prestes a decidir regra sensível sem humano.
