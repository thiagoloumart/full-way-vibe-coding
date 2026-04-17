# Domínio D1 — Software

> Este arquivo é **materialização**, não autoridade. As perguntas canônicas vivem em `fases/*.md`; as regras inegociáveis em [`filosofia.md`](../filosofia.md). Este domínio só **anexa** detalhamento de como cada fase se concretiza quando a entrega é software (feature, correção, automação, tela, API, agente, CRM).

## Quando usar este domínio
- Resultado final é **código em produção** (ou código descartado por decisão consciente).
- Código é escrito, testado, mergado.
- O usuário/o sistema/o agente passa a se comportar de forma nova em runtime.

## Quando NÃO usar
- Se o núcleo da entrega é "pessoas mudarem o jeito de trabalhar" → D2.
- Se o núcleo é "critério de decisão" → D3.
- Se ambos valem → híbrido, eixo primário = software.

## Constituição — o que esperar nesta camada
- **Camada 1 (invariantes):** papéis humano/IA, proibições/obrigatoriedades do Manual, §5.4 D1, marcadores.
- **Camada 2 (escolhas):** stack (linguagem, framework, banco, cache, fila, infra), padrões de código, convenções de nomenclatura, regras de organização de pastas, política de auth/autz, política de logs/observabilidade, convenção de commit e branch.

Alterações de Camada 2 exigem ADR em `governanca/adr-global.md` com `camada_afetada: 2` + minor bump. Alterações de Camada 1 exigem ADR com `camada_afetada: 1` + major bump + aprovação humana explícita.

## Regras sensíveis típicas em D1 (§5.4)
Nenhuma delas pode ser decidida pela IA sozinha. Cada uma vira C-NNN em `clarify.md` com autor humano:

- **Cobrança** — trigger, valor, moeda, recorrência, rollback de cobrança duplicada.
- **Permissão / autorização** — papéis, alçadas, revogação.
- **Estorno** — janela temporal, condições, autorização.
- **Deleção** — hard vs soft, cascade, recuperação.
- **Expiração** — TTL de sessão, token, cache, registro.
- **Visibilidade** — quem vê o quê; vazamento entre papéis.
- **Histórico** — o que fica, o que é purgado, quem pode consultar.
- **Auditoria** — o que é logado, granularidade, retenção.

## Materialização fase-a-fase

| Fase | Artefato D1 | Exemplo ruim | Exemplo bom |
|---|---|---|---|
| **0 Recepção** | lista de módulos + escolha do módulo alvo | "quero um SaaS" | "Módulo: notificação 24h antes da consulta via WhatsApp." |
| **0.5 BMAD** | `bmad.md` + `decision_log.md` | 1 caminho único | 2–3 caminhos (push vs polling vs webhook) com pre-mortem por caminho |
| **1 Briefing** | `briefing.md` focado em dor/uso/fluxo/valor | "ter controle de clientes" | "Atendente cria lead, qualifica em 3 status, promove a oportunidade." |
| **2 Spec** | `spec.md` com FR-NNN, stories P1..Pn, Given/When/Then, SC mensuráveis | "sistema rápido" | "FR-012 MUST retornar lista em ≤500ms p95." |
| **3 Clarify** | `clarify.md` com C-NNN por regra sensível | "decido depois" | "C-007 assinado pelo humano: estorno só com aprovação de gerente e janela de 7 dias." |
| **3.5 Constituição** | `constitution.md` v0 ou vN com bicamada | "clean code" | "Camada 2: Python 3.12 + FastAPI + Postgres 16 (ADR-003)." |
| **4 Plan** | `plan.md` com fases F1..FN, contratos, modelo de dados, rollback | plano monolítico de 1 fase | F1 migration + F2 endpoint + F3 UI, cada uma com testes mínimos |
| **5 Tasks** | `tasks.md` T-NNN com dependências, DoD, risco | "implementar módulo" | "T-014 criar migration 002 com DoD: migration roda e reverte limpo." |
| **6 Analyze** | `analyze.md` matrizes spec×constituição × plano × tasks × decision_log | "LGTM" sem ler | "FR-003 sem task → bloqueado; C-007 decide estorno → ok; D-003 silenciosamente revertida por FR-014 → bloqueio." |
| **7 Implement** | código + migrations + testes **por fase** | arquivo grande, PR gigante | F2 commitada, testes verdes, antes de F3; §5.4 respeitada sem improvisar regra |
| **8 Test** | suíte unit/integração/contrato/E2E/regressão verde | `.skip` para passar | testes cobrindo permissão negada, rollback, idempotência |
| **9 Quickstart** | `quickstart.md` com feliz + erro + permissão | "abra a tela e veja" | "rode `make seed`, faça login como `auditor`, tente deletar → esperado 403." |
| **10 Review** | `review.md` com verificações mínimas (§17) | "🚀 LGTM" | revisor anotou policy nova + confirmou migration reversível |
| **11 Merge** | branch derivada de master atualizada, commits separados (docs + impl), merge | commit misto docs+code | commits separados, push, master atualizada |
| **12 Retrospective** | `retrospective.md` com KPI atingido vs previsto, decisões revisitadas, propostas de ADR | "foi ok" | "3 bugs escaparam por ausência de E2E; proposta: ADR-019 adiciona E2E obrigatório em endpoints de cobrança." |

## Ponto de contato com a Constituição
- Fase 3.5 valida Camada 1 + Camada 2.
- Fase 4 (Plan) respeita Camada 2 (stack, padrões); qualquer desvio exige ADR.
- Fase 6 (Analyze) executa matriz "Plano × Constituição" — linter M2 vai automatizar essa matriz.
- Fase 10 (Review) cruza diff com Camada 2 (bibliotecas novas? log formato? convenção de commit?).
- Fase 11 (Merge) se bloqueia se houve alteração de Camada 1 sem ADR `camada_afetada: 1`.
- Fase 12 (Retrospective) propõe ADR quando aprendizado do ciclo justifica mudança estrutural.

## Agentes / CRM / SaaS operacional (§29 do Manual)
Quando o software for agente autônomo, automação comercial, CRM ou SaaS com automações — ler [`../protocolos/agentes-e-automacoes.md`](../protocolos/agentes-e-automacoes.md). Resumo: toda automação DEVE especificar **gatilho, contexto lido, decisão tomada, ação executada, condição de bloqueio, fallback, log gerado, critério de sucesso, risco de falso positivo**.
