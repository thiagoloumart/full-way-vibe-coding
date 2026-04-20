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

# Review — Fase 4 Plan do canônico `001-confirmacao-consultas`

**Branch:** `w1b/f4-plan`
**PR:** [#8](https://github.com/thiagoloumart/full-way-vibe-coding/pull/8)
**Data:** 2026-04-20
**Revisor:** Thiago Loumart (self-review, time=1)
**Status:** Aprovada

---

## 1. Escopo do diff

- **Commits:** 1 (`449b5b4` — `feat(canonical-001): Fase 4 Plan — 10 fases + modelo de dados + ADR-L-001 provedor WhatsApp`).
- **Linhas alteradas:** +724 / -2.
- **Arquivos:** 3 — `examples/canonical-software/001-confirmacao-consultas/{plan.md,adr_local_001_provedor_whatsapp.md,README.md}`.

PR mais denso do canônico até aqui. `plan.md` é o artefato que materializa a arquitetura antes do código; `adr_local_001` resolve explicitamente dívida 7.6 (Meta vs Z-API). Escopo ainda contido a uma fase, mesmo com o volume.

## 2. Arquivos alterados por categoria

| Categoria | Arquivos |
|---|---|
| Código de produção | — |
| Migrations | — |
| Testes | — |
| Docs — artefato SDD | `examples/canonical-software/001-confirmacao-consultas/plan.md` |
| Docs — ADR local | `examples/canonical-software/001-confirmacao-consultas/adr_local_001_provedor_whatsapp.md` |
| Docs — README | `examples/canonical-software/001-confirmacao-consultas/README.md` |
| Configuração | — |
| Infra | — |

## 3. Verificações mínimas (Manual §17)

- [x] **Arquivos alterados** — exatamente 3, todos previstos. ✅
- [x] **Migrations** — nenhuma neste PR (migrations são artefatos de F1-F9 na implementação, não do Plan). Todas as 9 migrations planejadas estão **listadas** em §3 com path e propósito. ✅
- [x] **Testes criados** — n/a. Plano lista ~25 arquivos de teste nominalmente em §3 para implementação posterior; Plan em si não tem testes executáveis, mas é a **base** para Test (Fase 8). ✅
- [x] **Rotas alteradas** — n/a. Plano descreve rotas (§3 F1, F4, F7, F8, F9) sem implementar. ✅
- [x] **Policies / permissões alteradas** — n/a. Matriz RBAC herda de C-002 (constituição §5); middleware `ExigeIsAdmin` **listado** em F8 sem implementação. ✅
- [x] **Integrações externas alteradas** — n/a. Meta Cloud API e Z-API **especificadas** com auth/timeout/retry/fallback/idempotência em §5, mas sem contrato implementado. ✅

## 4. Aderência à constituição

- [x] **Estrutura de pastas dos artefatos** — `examples/canonical-software/NNN-modulo/{plan,adr_local_001}.md` respeita path canônico.
- [x] **Convenção de markdown preservada** — headings numerados, tabelas, blocos de código para modelo de dados, consistência com os 5 artefatos anteriores.
- [x] **Nenhuma lib nova não autorizada introduzida.** Stack core de D-001 integralmente preservada; acréscimos (`brick/phonenumber`, `symfony/uid` para ULID, libs de métricas) **estão registrados como DT-07 e DT-10** com justificativa — minor bump futuro quando adicionados em F1/F10.
- [x] **Camada 1 da constituição — invariantes NÃO tocadas:**
  - **§1 Arquitetura** (monolito modular + single-tenant + contrato de canal abstrato) — respeitada em todas as 10 fases. F4 implementa contrato abstrato **em separado** do adaptador concreto, materializando D-E-02.
  - **§3 Valores bloqueantes** — F3 implementa histórico imutável (D-E-03 materializado via DT-03: trigger PG + observer Eloquent, defesa em profundidade); F9 implementa anonimização LGPD (C-003) sem deletar histórico.
  - **§6 Regras de segurança** — autenticação (F1) + RBAC (F1+F8) + rate-limit (F5+F10) + idempotência (F4+F5+F6) + isolamento por clínica (presente em todas as fases via `clinica_id` em queries).
  - **§7 Limites do MVP** — nenhuma fase do plan.md introduz funcionalidade listada em "Fora". ✅
  - **§10 Decisões estruturais permanentes (D-E-01..D-E-06)** — todas materializadas:
    - D-E-01 (§5.4 com autor humano) → decisões técnicas DT-NN assinadas por autor no Plan + ADR-L-001 com aprovação humana explícita.
    - D-E-02 (contrato abstrato) → F4 cria `NotificacaoDriver` interface + 3 adaptadores irmãos (Meta, ZApi, Noop).
    - D-E-03 (histórico append-only) → F3 implementa trigger PG + observer + testes específicos (`AppendOnlyGuardTest`, `HistoricoImutavelTest`).
    - D-E-04 (paciente sem credenciais) → modelo de dados: `Paciente` não tem campo `senha_hash`; apenas `User` tem.
    - D-E-05 (`sem-resposta` chega ao atendente) → FR-021 implementado em F7 `DashboardDia` com destaque visual.
    - D-E-06 (envios respeitam janela operacional) → F5 implementa `RespeitaJanelaOperacional` como helper puro testado.
- [x] **Camada 2 da constituição — consumida sem redecidir:**
  - Stack D-001 (Laravel 12 + Livewire 3 + PG 16 + Redis 7 + Forge) usada em todas as fases sem variação.
  - Parâmetros defaults de C-001/C-004/C-006 respeitados (valores dos seeders + UI de config em F8).
  - Padrões de estilo (Pest, Pint, PHPStan, Conventional Commits) honrados em F10.
  - **Escolha de provedor WhatsApp** — formalizada em ADR-L-001 (Camada 2 minor bump v1.0 → v1.1). Correto — decisão de driver concreto é precisamente Camada 2.
- [x] **Rastreabilidade integral:**
  - 10 fases do plano cobrem P1 a P4 da spec: F1+F2+F3 (coração); F4+F5+F6 (automação P1); F7 (P2+P3); F8 (P4+compareceu/no-show); F9 (LGPD); F10 (infra).
  - Cada fase cita FRs e NFRs atendidos em "Objetivo" e "Entidades afetadas".
  - Modelo de dados §4 bate 1:1 com Key Entities de spec §Requirements + adiciona `Clinica` e `User` explícitos (já implícitos na spec).
  - 9 campos §29 em tabela §5 — completos.
- [x] **Lint passa** em ambos os artefatos.
- [x] **Heading-vs-requer:**
  - `plan.md`: 9 itens de `requer:` batem com 9 headings `## N. …`. ✅
  - `adr_local_001_provedor_whatsapp.md`: 5 itens de `requer:` (Contexto, Decisão, Alternativas consideradas, Consequências, Relação com Constituição) batem com 5 headings `## …`. ✅

## 5. Sinal de regras de negócio sensíveis (Manual §5.4)

Plan é a primeira fase que **implementa a aplicação** das decisões §5.4 em código planejado. Aderência:

- [x] **Nenhuma regra §5.4 decidida em silêncio** pelo plano. Cada uma herda de D-NNN ou C-NNN:
  - **Histórico imutável (C-005)** → F3 (trigger PG + observer + `ref_evento_id`); tipo de evento `correcao` existe no enum `TipoEvento`.
  - **Deleção LGPD (C-003)** → F9 (`AnonimizarPaciente` transacional + lock pessimista + guard de integridade referencial).
  - **Expiração (C-004)** → F5 (`AgendarLembrete` respeita janela + retry policy + cancela se postergar ultrapassar horário da consulta).
  - **Visibilidade (D-003)** → modelo de dados tem `clinica_id` em todas as tabelas relevantes; `clinicas` com única linha MVP; FR-032 implementado naturalmente.
  - **Permissão (C-002)** → matriz materializada em `User.role` + `User.is_admin` + middleware `ExigeIsAdmin` em F8.
  - **Auditoria (C-006)** → `EventoConsulta` com 10 campos definidos; retenção 5 anos via `AnonimizacaoTemporalJob` em F9.
- [x] **Cobrança e Estorno** continuam **fora do escopo** — nenhuma entidade, migration ou FR do plano toca o tema.
- [x] **Lock pessimista no Paciente** (DT-09) durante anonimização é materialização defensiva de D-E-04 + NFR-003 (nenhum envio escapa após anonimização solicitada). R-04 documenta o risco + mitigação explícita.

## 6. Observações / pontos estranhos

- **Volume do plan.md (~560 linhas + ADR ~120 linhas).** Densidade natural da Fase 4 — Plan é o documento mais técnico antes do código. Decisão consciente: não enxugar sacrificando rastreabilidade; cada seção tem função verificável no gate.
- **10 fases é mais granular que típico** (plano médio é 4-6 fases). Decisão consciente para este canônico porque (a) é um **exemplo canônico** cuja didática vale mais que velocidade; (b) cada fase é testável isoladamente, o que é o critério do Manual §12. Em projeto real, F1+F2 poderiam fundir (bootstrap+cadastro+agendamento) sem perder sentido — aqui mantive separados para que cada um sirva de exemplo de PR isolado em Waves futuras.
- **ADR-L vs ADR global.** Escolhi numeração `ADR-L-NNN` (L = local do módulo) deliberadamente para não conflitar com numeração global `ADR-NNN` do repo (`governanca/adr-global.md`). Convenção nova; vale documentar em `governanca/versioning.md` quando o 2º canônico também criar ADR local (dívida nova #7.7).
- **DT-10 "library de métricas = a decidir em F10".** Deliberado — decisão de observabilidade específica envolve infra de produção que só faz sentido escolher quando o código estiver próximo de subir. ADR minor local quando for decidido. Não é `[NEEDS CLARIFICATION]` bloqueante porque o plano tem fallback ("logs JSON + métricas Prometheus exporter genérico" como opção default).
- **Campo `janela_lembrete_horas_usada` em Consulta** é um snapshot — escolha deliberada para implementar FR-029 (config posterior não afeta consultas já criadas). Alternativa seria armazenar apenas o timestamp calculado, mas preservar as horas é mais útil para auditoria e reschedule.
- **EventoConsulta ULID vs bigint** (DT-01) — escolhi ULID por ordenação cronológica natural, mas isso adiciona ~1.5x o tamanho de armazenamento vs bigint. Em volume MPE isso é irrelevante; em escala enterprise pode virar ADR minor de revisão.
- **Plan não especifica CI do canônico em detalhe** (F10 diz "GitHub Actions com Pint + PHPStan + Pest"). Decisão consciente: este canônico não roda CI próprio (é exemplo documental dentro do repo da skill, que tem seu próprio CI de linting de artefatos). Em projeto real, F10 listaria jobs CI nominalmente.
- **Dívida `#7.5`** author email persiste no commit `449b5b4`.

## 7. Dívidas conhecidas / TODO

- [ ] **7.1 — `templates/recepcao.md`** permanece aberta. — 2º canônico.
- [ ] **7.2 — Fase 5 Tasks** quebra as 10 fases em cartões operacionais T-NNN com estimativa em horas + responsável + ordem + checklist de pronto por cartão. — Thiago; Fase 5.
- [ ] **7.3 — Validação H-1/H-2 em piloto real** — continua pós-canônico.
- [ ] **7.4 — Template de PR** `.github/PULL_REQUEST_TEMPLATE.md` planejado em F10; como este canônico é doc dentro do repo da skill, não cria template de PR próprio — projeto real criaria.
- [ ] **7.5 — Rebase `--reset-author`** antes de W4. Mais um commit afetado (`449b5b4`).
- [ ] **7.6 — Meta vs Z-API** → **RESOLVIDA** via ADR-L-001. Marcar fechada no próximo self-review.
- [ ] **7.7 (nova) — Documentar convenção ADR-L (local) vs ADR (global) em `governanca/versioning.md`** quando 2º canônico também criar ADR local — Thiago; 2º canônico.
- [ ] **7.8 (nova) — Validar custo real Meta Cloud API em F4** — cotação formal precisa sair antes de deploy produção, confirmando `[INFERÊNCIA]` de R$ 0,07–0,15/msg dentro do teto C-001. Thiago; F4 execution.
- [ ] **7.9 (nova) — DT-10 library de métricas** — ADR minor local quando F10 for executada.

## 8. CRM / Agentes / SaaS (Manual §29)

**Aplicável integralmente.** §5 do plan.md consolida os 9 campos em tabela dedicada — primeira fase do ciclo em que os 9 aparecem consolidados em um único lugar narrativo. Checklist:

- [x] Gatilho: evento `criada` → `AgendarLembrete`.
- [x] Contexto lido: Consulta + Paciente + Medico + Clinica.janelas.
- [x] Decisão tomada: enviar / postergar / cancelar (regra composta).
- [x] Ação executada: `NotificacaoDriver::enviar` + eventos.
- [x] Condição de bloqueio: janela / idempotência / numero-invalido / rate-limit.
- [x] Fallback: retry 3x + intervenção manual.
- [x] Log: evento imutável com escopo C-006.
- [x] Critério de sucesso: NFR-007 + SC-001/005/006.
- [x] Risco de falso positivo: FR-025 + guard de anonimização em trânsito.

## 9. Resultado de testes

- [x] **Lint de `plan.md`:** `OK`.
- [x] **Lint de `adr_local_001_provedor_whatsapp.md`:** `OK`.
- [x] **Gate `fases/04_PLAN.md`** — 8 critérios ✅ exceto assinatura humana.
- [x] **Checklist `checklists/qualidade-plano.md`** — ~20 checkitems, todos ✅ exceto "humano aprovou".
- [x] **Coerência com artefatos anteriores:**
  - Zero contradição com `briefing.md`, `bmad.md`, `spec.md v2`, `clarify.md`, `constitution.md v1.0`, `decision_log.md`.
  - `plan.md §3` cobre integralmente as 4 User Stories e os 34 FRs + 7 NFRs da spec.
  - `plan.md §4` modelo de dados bate com Key Entities da spec (adiciona Clinica e User explícitos + relações).
  - `plan.md §5` §29 consolidado é consistente com referências dispersas em spec (FRs 006-018) e em constitution §11.
  - `ADR-L-001` respeita crit. invalidação de D-002 (R$ 0,30) e teto de C-001 (R$ 0,20).
- [x] **Heading-vs-requer** — 9/9 no plan + 5/5 no ADR.

## 10. Veredicto

- [x] ✅ **Aprovada** — pode mergar.

Primeiro PR denso do canônico (~844 linhas entre plan + ADR + review). Cobre: 10 fases de implementação isoladas e testáveis, modelo de dados completo com invariantes, §29 consolidado, 10 decisões técnicas justificadas, ADR-L-001 resolvendo dívida 7.6 (Meta vs Z-API), rate-limit residual resolvido (DT-08), rollback por fase + global, observabilidade planejada. Constituição v1.0 Camada 1 **não tocada**; Camada 2 consumida (stack) + acrescida (ADR-L-001 minor bump v1.0 → v1.1).

Mergar abre o próximo PR: **Fase 5 Tasks** — quebra F1..F10 em cartões operacionais T-NNN com estimativa/responsável/ordem/pronto-por-cartão.

**Recomendação para o humano:** este é o gate natural de revisão arquitetural antes do código. Vale uma leitura atenta de `ADR-L-001` e `plan.md §3 F3/F9` e `§6 Decisões técnicas` antes de autorizar Fase 5.

Assinado por: Thiago Loumart (self-review, 2026-04-20)
