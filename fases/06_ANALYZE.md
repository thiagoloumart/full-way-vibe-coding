# Fase 6 — Análise Cruzada (GATE CRÍTICO)

> Manual §13. Este é o gate **mais barato** e **mais importante** do processo. Problemas pegos aqui custam minutos. Os mesmos problemas descobertos após código custam horas ou dias.

## Objetivo
Rodar análise **TÉCNICA** cruzada entre **Constituição × Spec × Plano × Tasks × Decision Log** para detectar, antes da primeira linha de código:
- Inconsistência.
- Duplicação.
- Ambiguidade restante.
- Conflito com arquitetura existente.
- Conflito com versão de biblioteca.
- Cobertura insuficiente.
- **Decisão estratégica silenciosamente revertida** (spec/plano/tasks contradizendo `decision_log.md` sem nova `D-NNN`).

⚠️ **Esta fase NÃO analisa estratégia** — isso foi feito na **Fase 0.5 (BMAD)**. Se surgir aqui uma dúvida estratégica (ex: "o caminho escolhido ainda faz sentido?"), **voltar à Fase 0.5** e registrar nova `D-NNN` em `decision_log.md` antes de seguir.

## Entradas
- `bmad.md` (rastreabilidade estratégica)
- `decision_log.md` (rastreabilidade de decisões `D-NNN`)
- `constitution.md`
- `spec.md` (pós-clarify)
- `clarify.md`
- `plan.md`
- `tasks.md`

## Saídas
- `analyze.md` (usar [`templates/analise.md`](../templates/analise.md)).

## Matrizes obrigatórias

### 6.1 — Spec × Plano
Para cada FR da spec: existe arquivo/contrato no plano que o atende? Listar.
Para cada plano de arquivo: existe FR que justifica sua criação?

### 6.2 — Spec × Tasks
Cada FR tem pelo menos uma task implementando-o?
Cada task mapeia para um FR ou uma decisão técnica justificada?

### 6.3 — Constituição × Plano
Cada decisão técnica do plano está alinhada à constituição?
Se não: foi explicitamente justificada como exceção, com aprovação humana?

### 6.4 — Spec × Constituição
A spec exige algo que a constituição proíbe (ex.: spec pede tempo real e constituição diz "sem websockets")?
Se sim: travar, decidir qual prevalece.

### 6.5 — Edge cases × Tasks
Cada edge case da spec tem um teste ou tratamento previsto em alguma task?

### 6.6 — Regras sensíveis × Clarify
Todas as regras sensíveis (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria) têm entrada correspondente em `clarify.md` com decisão humana?

### 6.7 — Brownfield: duplicação
Algum arquivo/entidade/rota/componente proposto duplica algo que já existe no repo?
Se sim: reutilizar; atualizar plano e tasks.

### 6.8 — Spec × Decision Log (rastreabilidade estratégica)
Cada decisão `D-NNN` do `decision_log.md` foi respeitada pela spec/plano/tasks?
Existe alguma decisão que foi **silenciosamente revertida** (FR contradiz `D-NNN` sem nova entrada de revisão)?
Cada FR tem origem rastreável (`D-NNN` ou seção do briefing)?
Se houve reversão: foi registrada como nova `D-NNN` com campo `Origem: revisão de D-00Y`?

## Formato do `analyze.md`

```
# Análise Cruzada — <módulo>

## Resumo executivo
Status: 🟢 Limpa | 🟡 Riscos assumidos | 🔴 Bloqueada

## Matriz Spec × Plano
| FR | Plano cobre? | Arquivo(s) / Contrato | Observação |
| FR-001 | ✅ | ... | ... |
| FR-002 | ⚠️ | ... | cobertura parcial, ver task T-012 |
| FR-003 | ❌ | — | falta; adicionar no plano |

## Matriz Spec × Tasks
| FR | Task(s) | Observação |

## Matriz Constituição × Plano
| Decisão técnica | Constituição | Alinhamento | Observação |

## Matriz Edge cases × Tratamento
| Edge case | Tratado em | Teste? |

## Problemas detectados
1. <descrição> — gravidade: <baixa | média | alta> — ação: <corrigir spec | corrigir plano | corrigir task | travar>

## Riscos assumidos (com aprovação humana)
- <risco> — autor: humano — justificativa: <...>

## Veredicto
Pode seguir para implementação? <sim | não>
```

## Regra da fase (Manual §13)
> "Se a análise encontrar problemas, eles devem ser remediados antes do código."

Exceção: o humano pode optar por seguir com **`[RISCO ASSUMIDO]`**, desde que registrado em `analyze.md`.

## Riscos da fase
- Análise superficial ("parece ok") — esse gate só vale se for rigoroso.
- Marcar coisas como "risco assumido" sem o humano ter lido.
- Não atualizar spec/plano/tasks depois de remediar.

## Gate de avanço
- [ ] Todas as matrizes preenchidas.
- [ ] Problemas de gravidade alta resolvidos ou formalmente aceitos pelo humano.
- [ ] `analyze.md` validado pelo humano.
- [ ] Se houve `[RISCO ASSUMIDO]`, está registrado com autor.

## O que invalida a fase
- Matriz com `—` sem justificativa.
- Conflito crítico não resolvido (spec × constituição irreconciliáveis).
- Análise gerada sem ler a constituição (checar que foi referenciada explicitamente).

## Sinal de travamento
- Spec e constituição contradizem-se irreconciliavelmente → travar, escolher qual muda.
- O volume de problemas indica que o plano precisa ser refeito → voltar à Fase 4 e refazer.
