# Checklist — Priorização de MVP

Aplicar na Fase 0 (escolha do módulo) e novamente na Fase 2 (priorização de User Stories).

## Prioridade do MVP (Manual §23)
- [ ] Fluxo central identificado.
- [ ] Autenticação / autorização definidas no nível necessário para o fluxo central.
- [ ] Regras de negócio **críticas** cobertas.
- [ ] Cobrança / crédito incluído, se for parte do produto.
- [ ] Histórico essencial (aquilo sem o que não há auditoria mínima).
- [ ] Retorno funcional ao usuário (sinalização, confirmação, feedback).

## Não priorizar no MVP
- [ ] Dark mode.
- [ ] Microdetalhes visuais / ajustes cosméticos.
- [ ] Polimento estético além do mínimo.
- [ ] Features periféricas que dependem do core já rodando.
- [ ] Complexidade desnecessária (abstração para o futuro).

## Lógica de decisão para priorização (Manual §24)
Para cada item em dúvida, aplicar as 3 perguntas:

### Pergunta 1
Se isso **não** existir, o sistema ainda entrega valor principal?
- Se **não** → é **core**. Priorizar.

### Pergunta 2
Se isso **quebrar**, o usuário deixa de confiar no sistema?
- Se **sim** → é **crítico**. Priorizar.

### Pergunta 3
Se isso **não existir agora**, alguém ainda pagaria para usar?
- Se **não** → provavelmente é **essencial**. Priorizar.

Se nenhum dos três disparou: **não é MVP**; registrar para evolução futura.

## Verificação final
- [ ] O módulo escolhido para o ciclo é **core** (não cosmético, não periférico).
- [ ] User Story P1 da spec é viável sozinha e cumpre as 3 perguntas.
- [ ] O que não é MVP foi movido para "Out of Scope" ou backlog de futuras specs.
