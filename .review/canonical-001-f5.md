---
artefato: review
fase: 10
dominio: [software]
schema_version: 1
requer:
  - "1. Escopo do diff"
  - "3. Verificações mínimas (Manual §17)"
  - "4. Aderência à constituição"
  - "5. Sinal de regras de negócio sensíveis (Manual §5.4)"
  - "9. Resultado de testes"
  - "10. Veredicto"
---

# Review — Fase 5 Tasks do canônico `001-confirmacao-consultas`

**Branch:** `w1b/f5-tasks`
**PR:** [#9](https://github.com/thiagoloumart/full-way-vibe-coding/pull/9)
**Data:** 2026-04-20
**Revisor:** Thiago Loumart (self-review, time=1)
**Status:** Aprovada

---

## 1. Escopo do diff

- **Commits:** 1 (`5756147` — `feat(canonical-001): Fase 5 Tasks — 59 tasks T-001..T-059 + matrizes de rastreabilidade`).
- **Linhas alteradas:** +714 / -2.
- **Arquivos:** 2 — `examples/canonical-software/001-confirmacao-consultas/{tasks.md,README.md}`.

Contido em uma fase. Denso, mas natural — Tasks é a quebra operacional completa antes do código.

## 2. Arquivos alterados por categoria

| Categoria | Arquivos |
|---|---|
| Código de produção | — |
| Migrations | — |
| Testes | — |
| Docs — artefato SDD | `examples/canonical-software/001-confirmacao-consultas/tasks.md` |
| Docs — README | `examples/canonical-software/001-confirmacao-consultas/README.md` |

## 3. Verificações mínimas (Manual §17)

- [x] **Arquivos alterados** — exatamente 2. ✅
- [x] **Migrations** — n/a no PR de Tasks. Tasks **listam** 10 migrations (T-002, T-003, T-004, T-007, T-012, T-013, T-014, T-024, T-036); implementação vai em Fase 7. ✅
- [x] **Testes criados** — n/a. Tasks listam ~60 testes nominalmente distribuídos em "Testes exigidos" por task. ✅
- [x] **Rotas alteradas** — n/a. ✅
- [x] **Policies/permissões** — n/a. Middleware `ExigeIsAdmin` **planejado** em T-043. ✅
- [x] **Integrações externas** — n/a. Meta Cloud API + Z-API **referenciadas** (T-021, T-022) sem implementação. ✅

## 4. Aderência à constituição + plano

- [x] **Caminho canônico** do arquivo respeitado.
- [x] **Convenção de markdown** preservada — headings numerados, tabelas, blocos `>`, consistência com 7 artefatos anteriores.
- [x] **Camada 1 da constituição respeitada nas tasks:**
  - D-E-01 (§5.4 com autor humano) — todas as tasks tocando §5.4 referenciam D-NNN/C-NNN origem.
  - D-E-02 (contrato abstrato) — T-019 (`NotificacaoDriver`) vem antes dos adaptadores T-020/T-021/T-022.
  - D-E-03 (histórico append-only por construção) — T-013 (trigger PG) + T-012 (overrides model) = defense-in-depth DT-03 preservado.
  - D-E-04 (paciente sem credenciais) — T-002 cria `User` com roles `atendente`/`medico`; `paciente` explicitamente rejeitado como role.
  - D-E-05 (`sem-resposta` chega ao atendente) — T-038 (`DashboardDia`) materializa FR-021.
  - D-E-06 (envios respeitam janela) — T-027 (`RespeitaJanelaOperacional` como helper puro) + T-028 (uso no agendamento).
- [x] **Camada 2 consumida sem redecidir:**
  - Stack (D-001) seguida: PHP 8.3 + Laravel 12 + Livewire 3 + PG 16 + Redis 7.
  - Parâmetros defaults (C-001/C-004/C-006) implementados em T-001 (seeder) + T-031 (rate-limit 50/min) + T-051 (retenção 5a).
  - Convenções de estilo (Pest, Pint, PHPStan, Conventional Commits) materializadas em DoD padrão + T-056 (CI).
- [x] **Plano respeitado:**
  - Cada fase F1..F10 do plan.md tem tasks agrupadas.
  - Dependências entre tasks seguem dependências entre fases (T-017 depende de T-015; T-032 depende de T-021+T-024+T-028+T-030+T-031 — espelhando que F5 depende de F3+F4).
  - **Nenhum arquivo listado em plan §3 fica órfão.** Verificado por inspeção manual das ~80 entradas do plano.
- [x] **Rastreabilidade via matrizes:**
  - Matriz FR↔Task: 34/34 FRs mapeados. ✅
  - Matriz NFR↔Task: 7/7 NFRs mapeados. ✅
  - Matriz Edge↔Task: 13/14 mapeados + 1 documentado como `[RISCO ASSUMIDO]` (fuso único BR) sem task dedicada — aceitável porque o risco está **aceito** em spec.
- [x] **Lint passa** em `tasks.md`.
- [x] **Heading-vs-requer** — 2 itens de `requer:` batem com headings `## Matriz de rastreabilidade (FR ↔ Task)` e `## Matriz de rastreabilidade (Edge Case ↔ Task/Teste)`. ✅

## 5. Sinal de regras de negócio sensíveis (Manual §5.4)

Tasks é onde decisões §5.4 viram **execução planejada**. Aderência:

- [x] **Nenhuma regra §5.4 decidida em silêncio** por Tasks. Todas herdam de C-NNN/D-NNN/constituição com task correspondente:
  - Histórico (C-005 / D-E-03) → T-012 + T-013 + T-015 + T-046 (trigger + override + registrar + correção).
  - Deleção LGPD (C-003) → T-048 + T-049 + T-050 + T-052 (4 tasks dedicadas, incluindo proteção race).
  - Expiração (C-004) → T-027 + T-028 + T-031 (janela + agendamento + rate-limit).
  - Visibilidade (D-003) → `clinica_id` em todas as tabelas criadas; middleware de contexto planejado em T-001.
  - Permissão (C-002) → T-002 + T-043 + T-044 (User role + middleware + config UI).
  - Auditoria (C-006) → T-011 + T-015 + T-051 + T-054 (enums + service + temporal job + métricas).
- [x] **Cobrança e Estorno** continuam **fora do escopo** — nenhuma task toca tema financeiro.
- [x] **Operação sem rollback** (anonimização) tem 4 tasks + **confirmação forte** (T-050 — usuário digita nome) + **race protection** (T-052). Proporcional ao risco.
- [x] **§5.4 de histórico imutável** tem **defense-in-depth** (T-012 Eloquent override + T-013 trigger PG). Nenhuma é redundância: cobrem cenários distintos (código vs SQL cru).

## 6. Observações / pontos estranhos

- **Volume (59 tasks).** Média de ~6 tasks por fase, range 4-8. Cada task é atômica no sentido "um desenvolvedor executa em 1-3h de foco". Existe tentação de fundir tasks pequenas (ex: T-008+T-009 "criar/editar/cancelar consulta" poderia ser 1 task só) — mantive separadas para permitir commits menores e reviews granulares, consistente com o princípio "1 PR por decisão isolada" que o canônico vem materializando.
- **DoD padrão no topo do tasks.md.** Decisão consciente contra duplicação. Cada task só lista overrides específicos + "Testes exigidos". Alternativa seria repetir o DoD em cada uma — inchado sem ganho informacional. Se isso confundir alguém lendo uma task fora de contexto, a seção Legenda no topo aponta para o DoD padrão.
- **Dependências explícitas por T-NNN.** Cada task tem `Depende de:` nominalmente. Isso produz um grafo que permitirá fazer `tasks --graph` em ferramentas futuras. No MVP da ferramenta, a inspeção é manual mas suficiente.
- **T-018 "retrofit F2 para emitir eventos"** aparece em F3 mas **modifica** arquivos de F2 (services de Agendamento). Decisão consciente: Tasks permite "voltar" para ajustar código anterior quando a fase que introduz a abstração (F3 histórico) precisa retroalimentar fases já feitas. Alternativa seria embutir emissão de eventos já em F2 — descartada porque F3 é que define o enum TipoEvento e RegistrarEvento.
- **T-052 (proteção race anonimização × envio)** está em F9 mas retrofita T-032 de F5. Mesmo padrão. Tasks tornam explícita a re-entrada em código anterior; ninguém é pego de surpresa implementando.
- **Sem estimativa de horas por task.** Deliberado. Spec manual §12 pede "task pequena o suficiente" e "dependências claras", não estimativa. Em projeto real, a estimativa entra na Fase 5 com o time que vai executar; aqui o canônico é doc, não execução.
- **`[RISCO ASSUMIDO]` Fuso único BR** sem task específica é coerente com spec.md onde já estava aceito. Não é omissão; é herança.
- **Dívida `#7.5`** author email persiste.

## 7. Dívidas conhecidas / TODO

- [ ] **7.1 — `templates/recepcao.md`** permanece aberta. — 2º canônico.
- [ ] **7.2 — Fase 6 Analyze** (próximo PR) fará **matriz cruzada Spec × Plan × Tasks × Constituição** para detectar contradições ou lacunas antes do código — Thiago; Fase 6.
- [ ] **7.3 — Validação H-N em piloto** — pós-canônico.
- [ ] **7.5 — Rebase `--reset-author`** — herdado.
- [ ] **7.7 — Convenção ADR-L vs ADR global em `governanca/versioning.md`** — pós-2º canônico.
- [ ] **7.8 — Validar custo real Meta em F4 execution** — ao executar T-021 em produção/sandbox.
- [ ] **7.9 — DT-10 library de métricas** — ADR minor local em T-054.
- [ ] **7.10 (nova) — Template Meta adicional para cancelamento tardio** (`cancelamento_consulta` em T-042) — precisa aprovação Meta análoga ao `lembrete_consulta_utility_v1`. Thiago; durante execução de T-042.
- [ ] **7.11 (nova) — Decidir se threshold Pest de coverage começa em 60% ou 70%** — DT implícita em T-056/DT default do plano; se coverage real ficar < 70%, ADR minor ajusta. Thiago; T-056.

## 8. CRM / Agentes / SaaS (Manual §29)

Aplicável; 9 campos têm tasks específicas distribuídas:

| Campo §29 | Task(s) que materializam |
|---|---|
| Gatilho | T-028 (`AgendarLembrete`) + T-029 (listener criar consulta) |
| Contexto lido | T-032 (consumo de Consulta+Paciente+Medico+Clinica no job) |
| Decisão tomada | T-030 + T-031 + T-032 (aplicação dos 3 guards) |
| Ação executada | T-021 (envio Meta) + T-015 (evento) + T-024 (Notificacao) |
| Condição de bloqueio | T-030 + T-031 + T-027 + T-012 (idempotência + rate-limit + janela + append-only) |
| Fallback | T-032 (retry 3x) + T-040-T-041 (intervenção manual painel) |
| Log | T-015 + T-053 (eventos + logging JSON PII masked) |
| Critério de sucesso | T-054 (métricas) + T-058 (rate-limit validado) |
| Risco de falso positivo | T-042 (FR-025 cancelamento tardio) + T-052 (guard anonimização) |

## 9. Resultado de testes

- [x] **Lint de `tasks.md`:** `OK`.
- [x] **Gate `fases/05_TASKS.md`** — 7 critérios ✅.
- [x] **Coerência cruzada:**
  - 34/34 FRs cobertos em tasks (verificado por matriz).
  - 7/7 NFRs cobertos.
  - 13/14 edge cases com task; 1 documentado `[RISCO ASSUMIDO]`.
  - Dependências topológicas sem ciclos (verificado manualmente — T-N depende só de T-M com M<N em ordem de criação, exceto retrofits T-018 e T-052 que são declarações explícitas de edição retroativa).
- [x] **Heading-vs-requer** — 2/2.
- [x] **Grafo de dependências coeso:** raiz = T-001; sem tasks órfãs; última task T-059 (benchmark) depende de T-013.

## 10. Veredicto

- [x] ✅ **Aprovada** — pode mergar.

59 tasks mapeando integralmente 10 fases do plan.md, com DoD padrão + overrides + testes específicos por task + matrizes de rastreabilidade cobrindo 34 FRs, 7 NFRs e 13 edge cases. Camada 1 da constituição intocada; §5.4 todas com autor humano via C-NNN/D-NNN. Dívidas novas (#7.10, #7.11) assinaladas.

Mergar abre o próximo PR: **Fase 6 Analyze** — matriz cruzada Spec × Plan × Tasks × Constituição para detectar contradições ou lacunas. É o **gate técnico mais barato** do ciclo antes do código.

Assinado por: Thiago Loumart (self-review, 2026-04-20)
