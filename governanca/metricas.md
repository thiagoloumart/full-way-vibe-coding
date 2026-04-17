# Métricas — o que medir (não como)

> Este arquivo define **o que a skill recomenda medir** para saber se o protocolo está funcionando. Coleta, instrumentação e dashboards são tarefa separada (e tipicamente ficam em D2 no repositório-alvo, não na skill).

## Princípio
Sem métrica, o protocolo é uma opinião. Com métrica, vira aprendizado acumulável. A Fase 12 (Retrospective) depende dessas métricas para gerar `D-NNN-REVISED` e propor ADRs globais.

## Categorias de métrica

### 1. Saúde do ciclo — medida **por módulo**

| Métrica | O que mede | Como interpretar |
|---|---|---|
| **Tempo por fase** | Dias úteis entre início e fim de cada fase | Desvio grande vs média indica gargalo estrutural |
| **Tempo total do ciclo** | Fase 0 → Fase 11 (ou 12) | Fatia que cada fase representa; onde concentra |
| **Tempo parado** | Dias em estado "travado" aguardando `[DECISÃO HUMANA]` | Alto = clarify tardia; sinal para antecipar |
| **Nº de `[NEEDS CLARIFICATION]` em spec** | Conta marcadores após Fase 2 | Alto = briefing fraco |
| **Nº de `[DECISÃO HUMANA]` em clarify** | Conta regras sensíveis discutidas | Normal; baixo demais pode indicar que passaram em silêncio |
| **Nº de `[RISCO ASSUMIDO]` em analyze** | Conta concessões conscientes | Alto = dívida conhecida |
| **Nº de C-NNN por módulo** | Itens clarificados | Esperado: ≥1 por regra sensível do domínio |

### 2. Qualidade do aprendizado — medida **entre módulos**

| Métrica | O que mede | Como interpretar |
|---|---|---|
| **Taxa de `D-NNN-REVISED`** | Quantas decisões estratégicas foram revertidas depois | Alto = BMAD está fraco ou contexto muda rápido |
| **Taxa de sustentação de decisão** | `D-NNN` sustentada em Fase 12 / total | Meta: ≥70% em v1.1 (ponto de calibração) |
| **Taxa de falso positivo de pre-mortem** | Modos de falha previstos que não aconteceram | Útil para calibrar próximas Fases 0.5 |
| **Taxa de falso negativo de pre-mortem** | Modos de falha reais não previstos | Alto = BMAD precisa mais diversidade de perspectiva |

### 3. Eficiência do protocolo — medida **no agregado**

| Métrica | O que mede | Como interpretar |
|---|---|---|
| **Tempo de Fast-path (M2)** | Ciclos elegíveis a fast-path e tempo real | Valida se fast-path está capturando os casos certos |
| **Nº de travamentos por protocolo** | Quantas vezes `protocolos/travamento.md` foi acionado | Padrão revela ambiguidades recorrentes na skill |
| **Reversões de eixo em híbrido** | Quantas vezes o eixo primário precisou ser redefinido | Alto = classificação inicial inadequada |
| **ADRs por trimestre** | Quantos ADRs aceitos em 90 dias | Baixo pode indicar estagnação; alto pode indicar falta de invariantes claros |

### 4. Qualidade do output — medida **no resultado**

| Métrica | O que mede | Como interpretar |
|---|---|---|
| **Taxa de bug pós-merge (D1)** | Bugs abertos em 30 dias após merge / total de FRs | Alto = testes fracos ou spec vaga |
| **Taxa de SLA furado (D2)** | Casos fora de SLA / total | Alto = mapa-to-be otimista ou RACI mal definido |
| **Taxa de decisão sustentada (D3)** | Decisões aplicando playbook que não foram revertidas / total | Alto = playbook calibrado |

## Frequência de coleta recomendada

| Categoria | Quando coletar | Onde persiste |
|---|---|---|
| Saúde do ciclo | Por módulo, ao final da Fase 12 | `retrospective.md §2` do próprio módulo |
| Qualidade do aprendizado | Trimestralmente | Relatório agregado em `governanca/relatorio-trimestral-YYYY-Qn.md` (M2) |
| Eficiência do protocolo | Trimestralmente | Idem |
| Qualidade do output | Contínua (instrumentação), reportada mensalmente | Dashboards no repositório-alvo |

## Anti-padrões (o que evitar)

- **Medir para reportar, não para agir.** Métrica sem gatilho de ação é ornamento.
- **Otimizar métrica no lugar de otimizar resultado.** Se começar a "enxugar" tempo por fase cortando rigor, o ciclo degrada.
- **Comparar módulos de naturezas diferentes** (ex.: módulo D3 com módulo D1) — os tempos e proporções têm distribuições diferentes.
- **Esconder métricas ruins.** Retrospectiva vira ritual vazio. Cultura de métricas honestas = aprendizado possível.

## Gatilhos de ação baseados em métricas

| Observação | Ação recomendada |
|---|---|
| `[NEEDS CLARIFICATION]` > 5 na spec de 3 módulos seguidos | Revisar `fases/01_BRIEFING.md` ou `protocolos/perguntas-padrao.md` |
| `D-NNN-REVISED` > 30% em um trimestre | Revisitar `fases/00_5_BMAD.md` (BMAD pode estar superficial) |
| Mesmo travamento ocorre em 3+ módulos | Atualizar `protocolos/travamento.md` com caso canônico |
| Bug pós-merge sempre na mesma categoria (ex.: permissão) | Revisar `protocolos/agentes-e-automacoes.md` e Fase 8 (Test) |
| Spec sempre atrasa Fase 4 (Plan) | Gap entre `spec.md` e `plan.md` — revisar Fase 2 ou Fase 4 |

## Relação com harness (M2)

Em M2, o linter + CI geram alguns sinais **automaticamente**:
- Nº de `[NEEDS CLARIFICATION]` pós-Fase 2 (lint_artefato bloqueia; contar bloqueios).
- Tempo de rastreabilidade `D-NNN` → `FR-NNN` → `T-NNN` (extract_invariantes.py).
- Cobertura FR × Task (smoke_test.py).

Em M1, as métricas acima são **manuais** — preencher no `retrospective.md` do módulo.

## Privacidade
- Métricas agregadas **não** expõem conteúdo de `clarify.md` nem de `decision_log.md`.
- Em D2 com dados pessoais, métricas **não** mencionam clientes específicos — só contagens.
- Compliance: se as métricas são reportadas a terceiros, checar que não há PII.
