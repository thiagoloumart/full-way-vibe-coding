# Fase 5 — Tasks

## Objetivo
Quebrar o plano em **tarefas executáveis** pequenas, com dependências claras e agrupamento por fase (Manual §12).

## Entradas
- `plan.md` validado.

## Saídas
- `tasks.md` (usar [`templates/tasks.md`](../templates/tasks.md)).

## Cada task deve (Manual §12)
- Ser **pequena o suficiente** para execução controlada.
- Ter **dependências claras** (quais tasks precisam estar prontas antes).
- **Estar agrupada por fase** (mesma numeração do plano).
- **Permitir acompanhamento visual** (estado, responsável, artefato gerado).

## Princípio (Manual §12)
> "Implementação por fase é preferível à implementação total de uma vez."

Por quê: maior controle, teste incremental, correção antecipada, menos retrabalho.

## Estrutura de cada task

```
### T-NNN — <título acionável>
**Fase:** F<x>
**Depende de:** T-AAA, T-BBB (ou "nenhuma")
**Descrição:** <o que fazer, sem implementar aqui>
**Arquivos:** <lista nominal>
**Contrato afetado:** <endpoint/função/evento>
**Testes exigidos:** <sucesso, erro, edge>
**Definition of Done:**
  - [ ] código escrito
  - [ ] testes escritos e passando
  - [ ] revisão mínima feita
  - [ ] quickstart atualizado (se aplicável)
**Risco:** <baixo | médio | alto> — <motivo curto>
```

## Tipos típicos de task
- Migrations / modelagem de dados.
- Implementação de contrato (endpoint, função, evento).
- UI (tela, componente).
- Integração externa.
- Policies / permissões.
- Testes automatizados.
- Observabilidade / logs.
- Documentação operacional.

## Ordenação
1. Dependências primeiro (não colocar T que depende de T ainda não criada).
2. Dentro de uma fase, caminho crítico antes de periféricos.
3. Migrations antes de código que depende do schema.
4. Testes em paralelo com o código, não depois.

## Riscos da fase
- Task genérica ("implementar módulo X") — quebrar.
- Task sem DoD.
- Task com dependência escondida (vai travar no meio).
- "Fazer tudo" como uma task só.
- Testes como task final única — fatiar por contrato.

## Gate de avanço
- [ ] Cada task tem título acionável.
- [ ] Cada task tem DoD concreto.
- [ ] Dependências estão declaradas.
- [ ] Agrupamento por fase respeita o plano.
- [ ] Tasks cobrem integralmente o plano (nenhum arquivo do plano ficou sem task).

## O que invalida a fase
- Task sem dependência declarada acaba dependendo na prática.
- DoD subjetivo ("quando ficar bom").
- Tasks em ordem inexequível (código antes da migration).

## Sinal de travamento
- Plano não permite decomposição em tasks pequenas → voltar à Fase 4 e re-dividir fases.
