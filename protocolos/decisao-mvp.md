# Protocolo de Decisão do MVP

> Para toda dúvida de priorização, usar a lógica das seções 23 e 24 do Manual.

## Fase 0 — escolha do módulo inicial
O módulo inicial do ciclo deve ser **core**, ou seja:
- Sem ele, o sistema não entrega valor principal.
- É o ponto no qual o usuário sente o produto "acontecendo".

Ordem recomendada de módulos em um projeto zero (ajustar ao contexto):
1. Autenticação mínima.
2. Fluxo central (o caso de uso para o qual o sistema existe).
3. Regra de negócio crítica (cobrança, estoque, agenda, etc., conforme o produto).
4. Histórico essencial (auditoria mínima).
5. Feedback ao usuário (retorno funcional claro).
6. Outros módulos (por prioridade).

## Fase 2 — priorização de User Stories
Aplicar as 3 perguntas (Manual §24):

### Pergunta 1 — É core?
> Se isso não existir, o sistema ainda entrega valor principal?
- Se **não** → é core. P1.

### Pergunta 2 — É crítico?
> Se isso quebrar, o usuário deixa de confiar no sistema?
- Se **sim** → é crítico. P1 ou P2 conforme impacto.

### Pergunta 3 — É essencial?
> Se isso não existir agora, alguém ainda pagaria para usar?
- Se **não** → é essencial. P2.

Se nenhuma das três dispara → **não é MVP**. Mover para backlog.

## Não fazer no MVP (Manual §23)
- Dark mode.
- Microdetalhes visuais.
- Polimento cosmético.
- Features periféricas (upsell, integrações "bom ter", dashboards avançados).
- Abstração especulativa para o futuro.

## Como lidar com pedidos de features cosméticas
- Registrar no backlog.
- Explicar ao humano o critério das 3 perguntas.
- Se o humano insistir: marcar como `[RISCO ASSUMIDO]` e incluir na spec. A skill registra mas avisa custo.

## Sinal de alarme
- Todas as User Stories viraram P1.
- "Tudo é importante" = nada é MVP. Voltar ao briefing e re-escolher o módulo inicial.
