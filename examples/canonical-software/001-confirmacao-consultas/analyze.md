---
artefato: analise
fase: 6
dominio: [software]
schema_version: 1
requer:
  - "1. Resumo executivo"
  - "2. Matriz Spec × Plano"
  - "3. Matriz Spec × Tasks"
  - "4. Matriz Constituição × Plano"
  - "7. Regras sensíveis × Clarify (Manual §5.4)"
  - "8.5 Spec × Decision Log (rastreabilidade estratégica)"
  - "10. Problemas detectados"
  - "12. Veredicto final"
---

# Análise Cruzada — `001-confirmacao-consultas` (Canônico D1)

**Entradas:**
- `bmad.md` v1 · `decision_log.md` (D-001..D-003) · `constitution.md` v1.1 (após ADR-L-001)
- `spec.md` v2 pós-Clarify · `clarify.md` (C-001..C-006)
- `plan.md` v1 · `adr_local_001_provedor_whatsapp.md` · `tasks.md` v1 (T-001..T-059)

**Data:** 2026-04-20
**Revisor:** Thiago Loumart (modo Arquiteto; gate técnico antes de Implement)
**Status:** 🟡 **Com riscos assumidos** (ver §11) + **5 ajustes aplicados na tasks.md neste mesmo PR** (ver §10)

---

## 1. Resumo executivo

- **Matrizes obrigatórias completadas:** 7 (Spec×Plano · Spec×Tasks · Constituição×Plano · Spec×Constituição · Edge×Tratamento · §5.4×Clarify · Spec×DecisionLog).
- **Problemas detectados:** 9 — 5 de gravidade **média** remediados neste PR via ajuste em `tasks.md`; 4 de gravidade **baixa** aceitos como dívida ou ajuste operacional posterior.
- **Bloqueadores severidade alta:** **0**.
- **Riscos assumidos conscientemente:** 7 (herdados de fases anteriores; nenhum novo).
- **Veredicto:** 🟡 **Pode seguir para Fase 7 Implement** com riscos documentados; ajustes aplicados na `tasks.md` endereçam os 5 problemas de gravidade média.

---

## 2. Matriz Spec × Plano

Cada FR da spec tem **arquivo/contrato** cobrindo no plan.md. Resumo (detalhado já em tasks.md §Matriz FR↔Task; reconferido aqui com foco em "plan cobre").

| FR | Plan cobre? | Arquivo(s) / Contrato no plan | Observação |
|---|---|---|---|
| FR-001 a FR-005 (cadastro + agendamento) | ✅ | F1 (migrations users/clinicas/pacientes/medicos + services Cadastro) + F2 (Consulta + services Agendamento) | — |
| FR-006 a FR-012 (disparo + retry + idempotência) | ✅ | F4 (driver + webhook) + F5 (AgendarLembrete + DispararLembreteJob + guards) | — |
| FR-013 a FR-016 (processamento de resposta) | ✅ | F6 (ReconciliarResposta + AplicarResposta + CallbackIdempotencia) | — |
| FR-017, FR-018 (histórico imutável + auditoria campos) | ✅ | F3 (EventoConsulta + trigger + observer + RegistrarEvento + DerivarStatus) | Defense-in-depth |
| FR-019 (consultar histórico) | ✅ | F7 (`ConsultaHistorico` Livewire) | — |
| FR-020 a FR-022 (painel) | ✅ | F7 (`DashboardDia`) | — |
| FR-023 a FR-024 (intervenção manual) | ✅ | F7 (services + `IntervencaoManualModal`) | — |
| FR-025 (notificar cancelamento tardio) | ⚠️ | F7 `NotificarPacienteCancelamentoTardio` | **depende de template Meta adicional (dívida 7.10)** |
| FR-026, FR-027 (compareceu/no-show) | ✅ | F8 (MarcarCompareceu/MarcarNoShow + guard temporal) | — |
| FR-028, FR-029 (config janelas + snapshot) | ✅ | F8 (`ClinicaConfigForm`) + F2 (snapshot em Consulta) | — |
| FR-030, FR-031 (auth + RBAC) | ✅ | F1 (Breeze) + F8 (`ExigeIsAdmin`) | — |
| FR-032 (isolamento clínica) | ✅ | `clinica_id` em todas as tabelas (F1..F4) | D-003 preservado |
| FR-033 (anonimização LGPD) | ✅ | F9 (`AnonimizarPaciente` + `GuardIntegridadeReferencial` + modal) | — |
| **FR-034** (não expor dados · link seguro) | ⚠️ | Middleware de auth geral (T-002) cobre "não expor"; **mas "link seguro ao paciente" NÃO tem arquivo/task dedicada** | **Problema P-02 (gap) — remediação neste PR** |

**Transições automáticas de status (detectado durante análise):**
| Transição | Gatilho esperado | Plano cobre? | Observação |
|---|---|---|---|
| `lembrete-enviado` → `sem-resposta` (passou janela de silêncio sem resposta) | Scheduler periódico + evento `status_sem_resposta` | ❌ | **Problema P-01 (gap) — remediação neste PR** |
| `status_cache` dessincronizado do reducer (listener falhou) | Comando de reconciliação | ❌ | **Problema P-04 (gap) — remediação neste PR** |

---

## 3. Matriz Spec × Tasks

Já consolidada integralmente em `tasks.md §Matriz de rastreabilidade (FR ↔ Task)`. Cobertura **34/34 FRs + 7/7 NFRs**.

Após ajustes deste PR (ver §10), cobertura fica **34/34 + 7/7 + 4 novas tasks T-060..T-063** para os gaps detectados. Nova matriz consolidada fica em `tasks.md` (atualizada neste PR).

## 4. Matriz Constituição × Plano

| Decisão técnica (plano) | Camada da Constituição | Alinhamento | Observação |
|---|---|---|---|
| DT-01 ULID para EventoConsulta | Camada 2 §8 (convenções) | ✅ | Adiciona `symfony/uid` — lib já embarcada no Laravel 12 |
| DT-02 cache de status + listener | Camada 1 §10 D-E-03 (histórico imutável) | ✅ | Cache é projeção derivada; reducer permanece source-of-truth |
| DT-03 append-only defense-in-depth | Camada 1 §10 D-E-03 | ✅ | Trigger PG + Observer Eloquent reforçam invariante |
| DT-04 Meta Cloud API (via ADR-L-001) | Camada 2 §4 (stack) | ✅ | ADR minor bump v1.0 → v1.1; D-E-02 preservado (contrato abstrato mantém trocabilidade) |
| DT-05 Redis queue | Camada 2 §4 | ✅ | D-001 |
| DT-06 Livewire wire:poll 30s | Camada 2 §4 | ✅ | D-001 + C-002 UX |
| DT-07 libphonenumber | Camada 2 (nova lib) | ✅ | Justificada em T-003; minor bump implícito — documentar em histórico v1.2 se materializar |
| DT-08 rate-limit 50 msg/min | Camada 2 §4 (resolve residual) | ✅ | Resolve `[NEEDS CLARIFICATION]` da constituição v1.0 |
| DT-09 lock pessimista anonimização | Camada 1 §6 (segurança) | ✅ | Materializa proteção R-04 |
| DT-10 library métricas (a decidir F10) | Camada 2 §4 | ⚠️ | **Aberta**; dívida 7.9 ADR minor em execução de F10 — aceitável em análise |

**Camada 1 D-E-01..D-E-06 respeitada integralmente:**
| Invariante | Materializada em | Task ref |
|---|---|---|
| D-E-01 (§5.4 com autor humano) | Cada C-NNN tem tabela §5.4 no clarify.md; tasks de §5.4 referenciam origem | T-048 (anonimização com admin explícito) · T-046 (correção com motivo obrigatório) |
| D-E-02 (contrato abstrato de canal) | Interface `NotificacaoDriver` + 3 adaptadores irmãos | T-019, T-020, T-021, T-022 |
| D-E-03 (histórico append-only) | Trigger PG + Observer + RegistrarEvento único | T-012, T-013, T-015 |
| D-E-04 (paciente sem credenciais) | Nenhum campo `senha_hash` em Paciente; `User.role` bloqueia `paciente` | T-002, T-003 |
| D-E-05 (`sem-resposta` alcança atendente) | Painel + scheduler de transição | T-038 + **T-060 (NOVA via este PR)** |
| D-E-06 (envios respeitam janela) | `RespeitaJanelaOperacional` helper puro | T-027 |

## 5. Matriz Spec × Constituição

Cada requisito da spec checado contra regras estruturais.

| Requisito da spec | Conflito com constituição? | Resolução |
|---|---|---|
| FR-007 (WhatsApp único canal) | Não — Camada 2 §4 escolhe driver concreto; FR-007 executa D-002 | — |
| FR-017/018 (histórico + auditoria) | Não — reforça §3 Valores bloqueantes + D-E-03 | — |
| FR-032 (single-tenant) | Não — executa D-003 + §1 Arquitetura | — |
| FR-033 (anonimização LGPD) | Não — executa §3 Valores bloqueantes | — |
| NFR-001 (<10s reconciliação) | Não — compatível com Redis queue + Livewire polling 30s | — |
| NFR-002 (99% disponibilidade) | Não — alvo operacional; sem conflito estrutural | — |
| NFR-004 (retenção 5a + anonimização temporal) | Não — executa C-006 | — |
| NFR-007 (custo ≤ R$ 0,20) | Não — executa C-001 + crit. invalidação D-002 | — |
| Story P4 (config janelas pelo admin) | Não — mas **validação defensiva "lembrete ≥ 2h"** não está na spec, aparece só em tasks.md R-08 e T-044 | Adicionar em spec.md §Requirements como FR novo? **Análise:** é regra defensiva de UX, não regra de negócio; aceitável documentar em tasks.md. Não é conflito. |

**Conclusão:** zero conflito; spec e constituição são internamente coerentes.

## 6. Matriz Edge Cases × Tratamento

Os 13 edge cases da `spec.md` (12 originais + 1 criado em Clarify para correção) têm tratamento + teste associado em pelo menos uma task. Conferência:

| Edge case | Onde é tratado (task) | Teste? | Status |
|---|---|---|---|
| Provedor WhatsApp indisponível (transitório) | T-021 + T-032 (retry 3x) | unit `MetaCloudDriver` + feature `DispararLembreteJob` | ✅ |
| Número WhatsApp inválido (definitivo) | T-021 + T-032 | unit `FalhaDefinitivaException` + feature `lembrete_numero_invalido` | ✅ |
| Template reprovado | T-021 + T-042 | unit `TemplateRejeitadoException` + alerta crítico (plan §8) | ✅ |
| Resposta texto livre | T-034 + T-035 | unit reconciliar + feature `resposta_ambigua` | ✅ |
| Múltiplas respostas (última vale) | T-035 + T-016 | unit reducer | ✅ |
| Idempotência disparo duplicado | T-030 + T-032 | unit `IdempotenciaLembreteGuard` | ✅ |
| Consulta cancelada após lembrete | T-042 (FR-025) | feature | ✅ (risco: template Meta adicional — dívida 7.10) |
| Admin altera config no meio do dia | T-007 (snapshot) + T-044 | feature | ✅ |
| Paciente sem WhatsApp ativo | **Modelo de dados T-003 atual não trata (telefone NOT NULL implícito)** | — | **Problema P-03 — remediação neste PR** |
| Paciente pede deleção LGPD | T-048 + T-050 + T-052 | feature + guard + race test | ✅ |
| Horário de envio cai fora (03h) | T-027 + T-028 | unit `RespeitaJanelaOperacional` | ✅ |
| Correção de compareceu/no-show | T-046 + T-047 | unit + feature | ✅ |
| Fuso único BR | Documentado como `[RISCO ASSUMIDO]` em spec §Edge Cases | — | ✅ (risco aceito) |
| Callback duplicado Meta | T-036 | unit `CallbackIdempotenciaGuard` | ✅ |

## 7. Regras sensíveis × Clarify (Manual §5.4)

| Tema | Decidido em | Autor | OK |
|---|---|---|---|
| Cobrança | D-002 (fora de escopo) | humano | ✅ |
| Permissão | **C-002** (atendente + `is_admin`) | humano | ✅ |
| Estorno | D-002 (não aplica) | humano | ✅ |
| Deleção | **C-003** (anonimização) | humano | ✅ |
| Expiração | **C-004** (24h/4h/08–20h BRT/3 retries) | humano | ✅ |
| Visibilidade | **D-003** (single-tenant MVP) | humano | ✅ |
| Histórico | **C-005** (imutável + evento `correcao`) | humano | ✅ |
| Auditoria | **C-006** (escopo B + 5a retenção) | humano | ✅ |

**8/8 ✅.** Zero lacunas. Cada tema com autor humano identificado e referência cruzada consistente em constitution v1.1 (Camada 1 ou Camada 2 conforme aplicável).

## 8. Brownfield — duplicação

**N/A.** Canônico é greenfield (D-003 + classificação da Fase 0). Nenhuma entidade/rota/componente existente para duplicar. Verificação obrigatória cumprida por omissão justificada.

## 8.5 Spec × Decision Log (rastreabilidade estratégica)

Cada `D-NNN` respeitada por spec/plano/tasks? Alguma silenciosamente revertida?

| D-NNN | Tema | Respeitada? | FR/Task ref | Observação |
|---|---|---|---|---|
| **D-001** | Stack Laravel 12 + Livewire 3 + PG 16 + Redis 7 + Forge | ✅ | Constitution Camada 2 §4 + plan §2.2 env vars + tasks T-001 setup | Integralmente preservada. `ADR-L-001` refina (escolhe Meta), não reverte. |
| **D-002** | Caminho D — WhatsApp-only + fallback humano | ✅ | FR-007 + Story P3 (intervenção) + FR-025 (notificar cancelamento tardio) + NFR-007 (teto de custo) | Nenhum FR abre outro canal. Crit. invalidação (custo > R$ 0,30; template reclassificado) preservado em NFR-007 + plan R-01. |
| **D-003** | Single-tenant MVP (1 clínica / instalação) | ✅ | FR-032 + modelo de dados com `clinica_id` em toda tabela + seeder 1 Clínica | Constitution Camada 1 §1 materializa. Mudança futura para multi-tenant é major bump (v2.0). |

**Nenhuma D-NNN silenciosamente revertida.** Nenhuma nova D-NNN precisa ser aberta pela Fase 6 (os problemas detectados são gaps de implementação, não reversões estratégicas).

## 9. Consistência interna

- [x] **Nomenclatura consistente** entre spec, plan e tasks. Verificado por varredura: `EventoConsulta`, `NotificacaoDriver`, `Consulta.status_cache`, `janela_lembrete_horas` aparecem com mesmo nome em 3 artefatos.
- [x] **Nenhum FR sem task** — 34/34 com mapeamento explícito (após ajustes §10, 34/34 + novas T-060..T-063).
- [x] **Nenhuma task sem origem** — cada task em tasks.md tem ligação com FR ou decisão técnica DT-NN.
- [x] **Nenhuma migration sem rollback** — plan §9.1 documenta reversibilidade por fase; migrations listadas são todas `Schema::create` / `Schema::table` (Eloquent reverte por `Schema::drop` / reverse).
- [x] **Nenhuma integração externa sem timeout/retry/fallback** — plan §5 tabela tem todos os 4 para Meta e Z-API.
- [x] **Nenhum caminho de "silêncio"** (consulta ficar em limbo) — após §10 ajustes, scheduler T-060 garante transição `lembrete-enviado` → `sem-resposta`.

## 10. Problemas detectados

### Consolidado

| # | Descrição | Gravidade | Ação recomendada | Status neste PR |
|---|---|---|---|---|
| **P-01** | Transição automática `lembrete-enviado` → `sem-resposta` sem task dedicada. Risco de consulta ficar em `lembrete-enviado` para sempre, violando D-E-05. | 🟡 média | Criar task **T-060** (F7) — scheduler periódico que registra evento `status_sem_resposta` quando janela de silêncio esgota | ✅ **Remediado neste PR** (T-060 adicionada) |
| **P-02** | FR-034 "não expor dados exceto via link seguro" não tem task. Rota/token/assinatura do link não listada. | 🟡 média | Criar task **T-061** (F7) — gerador de URL assinada Laravel (`URL::temporarySignedRoute`) + rota `/consulta/{consulta}/publico` + inclusão do link no payload de lembrete (T-021) | ✅ **Remediado neste PR** (T-061 adicionada) |
| **P-03** | `telefone_whatsapp` é `varchar(20)` sem tratamento explícito de nullable. Edge case "paciente sem WhatsApp ativo" não tem representação de modelo. | 🟡 média | Ajustar **T-003** — tornar `telefone_whatsapp` nullable + regra "se null, consulta cria com evento `sem-canal` automático, fallback humano imediato" | ✅ **Remediado neste PR** (T-003 editada) |
| **P-04** | Listener `AtualizarStatusCacheDaConsulta` pode falhar transiente → cache dessincronizado do reducer. Sem comando para reconciliar. | 🟡 média | Criar task **T-062** (F10) — `php artisan consultas:reconciliar-status-cache` scheduler semanal + teste | ✅ **Remediado neste PR** (T-062 adicionada) |
| **P-05** | Policy Laravel para filtro "médico vê só própria agenda" não listada em tasks. | 🟡 média | Ajustar **T-043** — ampliar para incluir `MedicoPolicy` + `AtendentePolicy` com escopo por `clinica_id` + `user_id` em Médico | ✅ **Remediado neste PR** (T-043 ampliada; nova T-063 dedicada a policies) |
| P-06 | Threshold de alerta de custo operacional (NFR-007) não definido numericamente | 🟢 baixa | Definir em ADR minor durante execução de T-054 (métricas) | 📋 dívida 7.12 nova |
| P-07 | Template Meta `cancelamento_consulta` (FR-025) precisa aprovação adicional | 🟢 baixa | Já dívida 7.10 — executar durante T-042 com buffer de tempo | 📋 dívida pré-existente |
| P-08 | Validação real em sandbox Meta (integração completa) não tem task dedicada | 🟢 baixa | Ampliar T-021 para incluir smoke test em sandbox — dívida 7.8 ampliada | 📋 dívida pré-existente ampliada |
| P-09 | Métrica específica `confirmacao_custo_total_reais` não listada | 🟢 baixa | Derivável de `confirmacao_lembretes_enviados_total × custo_unitario` — ajuste em T-054 | 📋 dívida 7.13 nova |

### Detalhe dos ajustes aplicados na `tasks.md`

Para cada problema média gravidade, a correção foi aplicada **neste mesmo PR** editando `tasks.md`:

**Ajuste A — Edição de T-003** (telefone nullable):
- `telefone_whatsapp` passa a ser nullable na migração.
- Regra de negócio: se criar consulta para paciente com `telefone_whatsapp = null`, evento `sem-canal` é registrado imediatamente junto com o `criada`, status vai para `sem-canal` (novo estado enumerado).
- Acréscimo ao enum `TipoEvento`: `sem_canal` (T-011 ampliada implicitamente via spec — a implementação materializa).
- Teste: criar consulta com paciente sem telefone → status `sem-canal` + painel mostra para atendente agir manualmente.

**Ajuste B — T-043 ampliada + T-063 nova**:
- T-043 agora inclui criação de `app/Policies/{ConsultaPolicy,PacientePolicy,MedicoPolicy}.php`.
- T-063 (nova, F8) testa policies explicitamente: médico tentando ver consulta de outro médico → 403.

**Ajuste C — Nova T-060 (F7, scheduler sem-resposta)**:
- Cria `app/Domain/Confirmacao/Jobs/DetectarSemRespostaJob.php`.
- Scheduler a cada 15 min: `select consultas where status_cache = 'lembrete_enviado' and (datahora_agendada - janela_silencio_horas_usada) < now()`.
- Para cada match: `RegistrarEvento(status_sem_resposta, sistema-automacao)` → listener atualiza status_cache → painel destaca.
- Teste feature: setup com 1 consulta em `lembrete_enviado` + horário passou janela silêncio → rodar scheduler → status vira `sem-resposta`.

**Ajuste D — Nova T-061 (F7, link seguro ao paciente)**:
- Cria rota `GET /consulta/publico/{signed_token}` → Livewire `ConsultaPublica` (somente-leitura).
- Token assinado via `URL::temporarySignedRoute('consulta.publico', now()->addHours(48), [...])`.
- Inclusão do link no payload do template Meta (T-021 ampliada para passar URL como parâmetro).
- Teste feature: atendente cria consulta → lembrete enviado com link → paciente abre link → vê detalhes; link expirado → 403.

**Ajuste E — Nova T-062 (F10, comando reconciliação)**:
- Cria `app/Console/Commands/ReconciliarStatusCache.php`.
- Scheduler semanal (ou manual): itera todas as consultas, recalcula `DerivarStatus`, compara com `status_cache`, atualiza se diverge, loga divergências.
- Teste feature: sabotar `status_cache` artificialmente → comando detecta + corrige.

### Sumário numérico pós-ajustes

- **Tasks antes:** 59 (T-001..T-059).
- **Tasks após ajustes:** 63 (T-001..T-063).
- **Tasks novas:** T-060, T-061, T-062, T-063.
- **Tasks editadas (ampliadas):** T-003 (telefone nullable), T-021 (URL no payload), T-043 (policies).
- **Matriz FR↔Task atualizada** para refletir T-061 → FR-034.

## 11. Riscos assumidos

Herdados de fases anteriores; todos com autor humano e mitigação. Nenhum risco novo descoberto pela análise.

| # | Risco | Autor | Justificativa | Mitigação |
|---|---|---|---|---|
| RA-01 | Fuso único BR no MVP | humano (spec) | Perfil-alvo é MPE BR 1 clínica; fuso múltiplo é overengineering | Documentado em spec §9; migração futura trivial adicionando `timezone` em `clinicas` |
| RA-02 | Última resposta vale (pode sobrepor confirmação com cancelamento) | humano (C-005) | Simplicidade; paciente muda de ideia é caso legítimo | Reducer preserva todas as respostas no histórico |
| RA-03 | Multi-tenant adiado | humano (D-003) | Perfil MPE 1 clínica; multi-tenant é trabalho separado | `clinica_id` presente — migração é aditiva |
| RA-04 | Provedor único Meta (concentração) | humano (D-002 + ADR-L-001) | Fit cultural BR + oficial; ZApi fica como implementação irmã | Contrato abstrato D-E-02 permite troca em 1 sprint |
| RA-05 | Rate-limit 50/min (chute educado) | humano (DT-08) | Cobre perfil MPE com folga | Configurável via env; ajustável em operação |
| RA-06 | Custo médio Meta R$ 0,07–0,15 `[INFERÊNCIA]` | humano (ADR-L-001) | Literatura Meta BR 2026; dentro do teto R$ 0,20 | Validar em F4 cotação + NFR-007 dispara revisão se ultrapassar |
| RA-07 | Metas SC `[INFERÊNCIA]` recalibráveis | humano (spec) | Derivadas de H-1/H-2; sem dados primários antes de piloto | Ajuste em retrospective Fase 12 |

## 12. Veredicto final

- [ ] Análise limpa (🟢)
- [x] **Análise com riscos conscientes (🟡) + 5 ajustes aplicados neste PR na `tasks.md` endereçando gaps detectados**
- [ ] Bloqueada — deve voltar à fase anterior

### Justificativa

A análise cruzada rigorosa detectou **9 problemas**, dos quais:
- **5 de gravidade média (P-01..P-05)** — gaps de implementação genuínos (scheduler sem-resposta; link seguro paciente; telefone nullable; reconciliação cache; policies Laravel). Todos remediados neste PR editando `tasks.md` — nenhum bloqueia o avanço.
- **4 de gravidade baixa (P-06..P-09)** — dívidas operacionais (threshold de alerta custo; template adicional Meta; sandbox Meta; métrica de custo). Registradas como dívidas 7.10..7.13 para execução de fase futura.
- **0 de gravidade alta** — nenhum bloqueador.

**Zero contradição estrutural** (spec × constituição; plan × D-NNN; tasks × plan). Zero reversão silenciosa de D-NNN. Zero lacuna de §5.4 (8/8 temas com autor humano).

Os 7 riscos assumidos são herdados e já foram conscientizados por humano em fases anteriores.

**Pode seguir para Fase 7 Implement** — com `tasks.md` atualizado neste PR para 63 tasks endereçando os 5 gaps de gravidade média.

Assinado por: Thiago Loumart (self-review, 2026-04-20)
