---
artefato: plano
fase: 4
dominio: [software]
schema_version: 1
requer:
  - "1. Escopo do plano"
  - "2. Pré-requisitos"
  - "3. Fases de implementação"
  - "4. Modelo de dados completo"
  - "5. Integrações externas (consolidado)"
  - "6. Decisões técnicas"
  - "7. Riscos técnicos e mitigações"
  - "8. Observabilidade planejada"
  - "9. Plano de rollback"
---

# Plano Técnico — `001-confirmacao-consultas` (Canônico D1)

**Referências:** `spec.md` v2 (pós-Clarify) · `clarify.md` C-001..C-006 · `constitution.md` v1.0 (bicamada) · `decision_log.md` D-001/D-002/D-003
**Data:** 2026-04-20
**Status:** Draft (validação humana via merge do PR `w1b/f4-plan`)
**Autor:** Thiago Loumart (modo Arquiteto)

---

## 1. Escopo do plano

- **Módulo:** Confirmação de Consultas (canônico D1).
- **Stories cobertas pelo plano:** P1 (lembrete + resposta botão), P2 (painel do dia), P3 (intervenção manual), P4 (configuração de janelas).
- **NFRs cobertos:** NFR-001 a NFR-007 (com rate-limit numérico especificado em §3 F10).
- **ADRs locais deste plano:** [`adr_local_001_provedor_whatsapp.md`](adr_local_001_provedor_whatsapp.md).
- **Explicitamente fora do plano:** tudo listado em `spec.md §Out of Scope` — multi-tenant, SMS/e-mail automatizados, pagamento, dashboard analítico rico, app nativo, Google/Outlook, fuso múltiplo.

## 2. Pré-requisitos

### 2.1 Contas e credenciais externas
- **Conta Meta Business Manager** aprovada para uso do Cloud API com número de telefone verificado da clínica.
- **Template de mensagem** do tipo `utility` pré-submetido e aprovado pela Meta (texto base do lembrete com placeholders para clínica / médico / data / hora + botões `Confirmar` / `Cancelar` / `Reagendar`).
- **Laravel Forge** com VPS provisionada (Hetzner ou DigitalOcean) e credencial SSH configurada.

### 2.2 Env vars (produção)

| Variável | Propósito | Fase que a consome |
|---|---|---|
| `APP_ENV`, `APP_KEY`, `APP_URL` | Laravel base | F1 |
| `DB_*` | PostgreSQL 16 | F1 |
| `REDIS_*` | Redis 7 cache + queue | F1 |
| `QUEUE_CONNECTION=redis` | Driver de fila | F1 |
| `MAIL_*` | SMTP para comunicação operacional (NÃO paciente) | F1 |
| `WHATSAPP_DRIVER` | `meta` \| `zapi` \| `noop` (rollback) | F4 |
| `META_PHONE_NUMBER_ID`, `META_ACCESS_TOKEN`, `META_WABA_ID` | Credenciais Meta Cloud API | F4 |
| `META_WEBHOOK_VERIFY_TOKEN` | Verificação de callback | F4 |
| `META_TEMPLATE_LEMBRETE_NAME`, `META_TEMPLATE_LEMBRETE_LANG=pt_BR` | Identificação do template aprovado | F4 |
| `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO=50` | Rate-limit (resolve residual da constituição) | F5, F10 |
| `LOG_CHANNEL=stack`, `LOG_LEVEL=info` (prod); `debug` (dev) | Logging | F10 |

### 2.3 Seeds / dados de referência
- **Migration de bootstrap** cria 1 clínica padrão (`single-tenant MVP`, conforme D-003) com janelas defaults de C-004 (24h/4h/08-20h/3 retries).
- **Nenhuma seed de paciente/médico em produção** — atendente cadastra pela UI.
- Em dev/staging: seeder `DemoDataSeeder` cria 1 clínica + 2 médicos + 5 pacientes + 10 consultas para validação manual (Quickstart Fase 9).

### 2.4 Feature flags
- `WHATSAPP_DRIVER` atua como feature flag de canal — valor `noop` bloqueia envios reais (útil para staging e rollback emergencial).
- Nenhuma outra flag no MVP — simplicidade deliberada.

---

## 3. Fases de implementação

Implementação **por fase** (Manual §12). Cada fase entrega valor incremental ou um degrau necessário isolado, testável e reversível.

**Mapa rápido:** F1 (bootstrap+cadastro) → F2 (agendamento) → F3 (histórico imutável) → F4 (driver WhatsApp) → F5 (job de envio) → F6 (processamento de resposta) → F7 (painel+intervenção) → F8 (config+presença+correção) → F9 (anonimização LGPD) → F10 (observabilidade+CI/CD+rate-limit).

### F1 — Bootstrap + Cadastro mínimo

- **Objetivo:** Laravel 12 rodando com auth básica + tabelas de usuários, clínicas, pacientes e médicos + CRUD mínimo de paciente e médico via Livewire.
- **Depende de:** nada (primeira fase).
- **Arquivos criados/alterados:**
  - `composer.json` · `package.json` · `vite.config.js` — setup.
  - `config/{app,database,queue}.php` — configuração Laravel.
  - `database/migrations/2026_04_21_000001_create_clinicas_table.php`
  - `database/migrations/2026_04_21_000002_create_users_table.php` (com `role`, `is_admin`, `clinica_id`)
  - `database/migrations/2026_04_21_000003_create_pacientes_table.php`
  - `database/migrations/2026_04_21_000004_create_medicos_table.php`
  - `app/Models/{Clinica,User,Paciente,Medico}.php`
  - `app/Domain/Cadastro/Services/CadastrarPaciente.php` · `CadastrarMedico.php`
  - `app/Http/Livewire/Cadastro/{PacienteForm,PacienteList,MedicoForm,MedicoList}.php`
  - `resources/views/livewire/cadastro/*.blade.php`
  - `routes/web.php` — rotas autenticadas para cadastro.
  - `tests/Feature/Cadastro/{CadastrarPacienteTest,CadastrarMedicoTest}.php`
  - `tests/Unit/Domain/Cadastro/*.php`
- **Entidades afetadas:** Clinica, User, Paciente, Medico.
- **Contratos:**
  - `CadastrarPaciente::executar(ClinicaId $c, array $dados): Paciente` — retorna Paciente criado; lança `DadosInvalidosException` em validação.
  - `CadastrarMedico::executar(ClinicaId $c, array $dados): Medico` — idem.
  - `GET /pacientes` · `GET /pacientes/novo` · `POST /pacientes` · `GET /pacientes/{id}/editar` · `PUT /pacientes/{id}` — rotas Livewire.
- **Modelo de dados:** ver §4.
- **Integrações externas:** nenhuma.
- **Testes mínimos:**
  - Feature: atendente autenticado cadastra paciente com dados válidos → criado; com telefone duplicado → 422.
  - Feature: atendente sem `is_admin` tenta cadastrar médico → 403.
  - Unit: serviço rejeita telefone sem DDI + 11 dígitos.
- **Critério de "pronto":** `php artisan migrate` rola limpo; login funciona; atendente consegue cadastrar paciente e médico; testes F1 passam.
- **Riscos técnicos da fase:** divergência de versão entre PHP 8.3 local e 8.3 do Forge → mitigar com `composer require --no-install` + lock.

### F2 — Agendamento mínimo

- **Objetivo:** Atendente consegue criar, editar e cancelar consultas.
- **Depende de:** F1.
- **Arquivos criados/alterados:**
  - `database/migrations/2026_04_21_000005_create_consultas_table.php`
  - `app/Models/Consulta.php`
  - `app/Domain/Agendamento/Services/{CriarConsulta,EditarConsulta,CancelarConsultaPelaClinica}.php`
  - `app/Domain/Agendamento/Exceptions/ConsultaForaDaJanelaException.php`
  - `app/Http/Livewire/Agendamento/{ConsultaForm,ConsultaList}.php`
  - `tests/Feature/Agendamento/*.php`
  - `tests/Unit/Domain/Agendamento/*.php`
- **Entidades afetadas:** Consulta.
- **Contratos:**
  - `CriarConsulta::executar(ClinicaId $c, PacienteId $p, MedicoId $m, Carbon $quando): Consulta` — lança `DataNoPassadoException`.
  - `EditarConsulta::executar(ConsultaId $c, array $mudancas): Consulta` — bloqueia se consulta já está em estado terminal.
  - `CancelarConsultaPelaClinica::executar(ConsultaId $c, string $motivo): void`.
- **Modelo de dados:** ver §4.
- **Testes mínimos:**
  - Feature: criar consulta válida → status inicial `agendada`.
  - Feature: criar consulta com data passada → 422.
  - Feature: editar consulta em estado `confirmada` → permitido; em estado `compareceu` → bloqueado.
  - Unit: cálculo de "janela de envio" = `datahora_agendada - janela_lembrete_horas`.
- **Critério de "pronto":** atendente consegue agendar e editar consulta; transições de status iniciais funcionam; testes F2 passam.
- **Riscos técnicos:** fuso horário — consultas armazenadas em UTC; conversão para BRT na UI. Mitigar com `timestamptz` no PG + `APP_TIMEZONE=UTC`.

### F3 — Histórico imutável + eventos

- **Objetivo:** Toda mudança relevante de uma consulta é registrada como evento imutável numa tabela append-only; status da consulta é derivado do último evento (com cache).
- **Depende de:** F2.
- **Arquivos criados/alterados:**
  - `database/migrations/2026_04_21_000006_create_eventos_consulta_table.php`
  - `database/migrations/2026_04_21_000007_add_status_cache_to_consultas.php`
  - `app/Models/EventoConsulta.php` (sem método `update()` nem `delete()` — lança exception se chamado)
  - `app/Domain/Confirmacao/Eventos/{TipoEvento,AtorTipo,Canal}.php` (enums)
  - `app/Domain/Confirmacao/Services/RegistrarEvento.php` — ponto único de escrita no histórico.
  - `app/Domain/Confirmacao/Services/DerivarStatus.php` — calcula status da Consulta a partir de eventos.
  - `app/Listeners/AtualizarStatusCacheDaConsulta.php` — listener que atualiza `consultas.status_cache` após cada evento.
  - `app/Domain/Confirmacao/Guards/AppendOnlyGuard.php` — bloqueia UPDATE/DELETE em `eventos_consulta` via trigger DB + observer Eloquent.
  - `database/migrations/2026_04_21_000008_add_append_only_trigger_eventos.php` — trigger PG `BEFORE UPDATE OR DELETE ON eventos_consulta` que lança exceção.
  - `tests/Unit/Domain/Confirmacao/{DerivarStatusTest,AppendOnlyGuardTest}.php`
  - `tests/Feature/Confirmacao/HistoricoImutavelTest.php`
- **Entidades afetadas:** Consulta (ganha `status_cache`); nova `EventoConsulta`.
- **Contratos:**
  - `RegistrarEvento::executar(ConsultaId $c, TipoEvento $t, AtorTipo $aTipo, ?int $aId, Canal $canal, array $extra): EventoConsulta` — insere e dispara listener.
  - `DerivarStatus::executar(ConsultaId $c): string` — aplica reducer sobre eventos ordenados por `criado_em`, respeitando eventos `correcao` com `ref_evento_id`.
- **Modelo de dados:** ver §4.
- **Testes mínimos:**
  - Unit: reducer deriva `confirmada` a partir de `[criada, lembrete_enviado, resposta_recebida_confirmar]`.
  - Unit: reducer aplica `correcao` sobre evento referenciado (último evento efetivo é o `correcao`).
  - Feature: tentativa de `EventoConsulta::find(1)->update(...)` → exception `HistoricoImutavelException`.
  - Feature: tentativa de `DB::table('eventos_consulta')->where('id',1)->delete()` → exception PostgreSQL da trigger.
- **Critério de "pronto":** eventos append-only garantidos em DB e Eloquent; derivação de status bate com o campo `status_cache`; testes F3 passam.
- **Riscos técnicos:** trigger PG pode impactar performance de INSERT em massa → benchmark simples em F10.

### F4 — Driver abstrato de notificação + adaptador Meta Cloud API + webhook

- **Objetivo:** Contrato abstrato de canal (constitution §D-E-02) + implementação Meta + rota de webhook para receber callbacks.
- **Depende de:** F1.
- **Arquivos criados/alterados:**
  - `app/Domain/Notificacao/Contracts/NotificacaoDriver.php` (interface)
  - `app/Domain/Notificacao/Valores/{IdExterno,StatusEntrega,RetornoDriver}.php`
  - `app/Domain/Notificacao/Exceptions/{FalhaTransitoriaException,FalhaDefinitivaException}.php`
  - `app/Infra/Notificacao/MetaCloudDriver.php`
  - `app/Infra/Notificacao/ZApiDriver.php` (stub para permitir troca — teste de contrato compartilhado)
  - `app/Infra/Notificacao/NoopDriver.php` (para staging/rollback)
  - `app/Providers/NotificacaoServiceProvider.php` (registra driver conforme `WHATSAPP_DRIVER`)
  - `database/migrations/2026_04_21_000009_create_notificacoes_table.php`
  - `app/Models/Notificacao.php`
  - `app/Http/Controllers/WhatsappWebhookController.php` — verificação + recepção.
  - `routes/webhooks.php` — `GET /webhooks/whatsapp` (verify) + `POST /webhooks/whatsapp` (receive).
  - `tests/Contract/NotificacaoDriverContractTest.php` (roda contra mock + contra Meta sandbox se credencial disponível)
  - `tests/Feature/Webhooks/WhatsappWebhookTest.php`
- **Entidades afetadas:** nova `Notificacao`.
- **Contratos:**
  - `NotificacaoDriver::enviar(Paciente $p, array $template_params, IdempotencyKey $k): RetornoDriver` — retorna `IdExterno` ou lança `FalhaTransitoriaException` / `FalhaDefinitivaException`.
  - `POST /webhooks/whatsapp` — recebe JSON do provedor; retorna 200 imediatamente; processa assíncrono.
  - `GET /webhooks/whatsapp?hub.mode=subscribe&hub.challenge=...&hub.verify_token=...` — responde `challenge` se token bate.
- **Integrações externas:** **Meta Cloud API** — ver §5.
- **Testes mínimos:**
  - Contract: qualquer driver que implementa `NotificacaoDriver` passa bateria comum (envio ok / falha transitória / falha definitiva / idempotência).
  - Feature: webhook recebe callback duplicado → segundo é descartado por idempotência.
  - Feature: webhook recebe texto livre → gera evento `resposta_ambigua` (preparação para F6).
- **Critério de "pronto":** `NotificacaoDriver` abstrato + 1 adaptador (Meta) + webhook respondendo 200 com idempotência; testes F4 passam; nenhum `use App\Infra\Notificacao\MetaCloudDriver` fora de `app/Infra/` ou `app/Providers/`.
- **Riscos técnicos:** Meta rejeita template por categoria → ADR local ADR-L-001 descreve mitigação (template de fallback em "categoria revisável" + alerta manual ao admin).

### F5 — Job de disparo de lembrete + janela operacional + retry + rate-limit + idempotência

- **Objetivo:** Quando uma consulta é criada, um job é agendado para a janela apropriada; job respeita janela operacional, faz retry com backoff, garante idempotência e obedece rate-limit por clínica.
- **Depende de:** F3, F4.
- **Arquivos criados/alterados:**
  - `app/Domain/Confirmacao/Jobs/DispararLembreteJob.php` (implementa `ShouldQueue`; lê config de janela da clínica)
  - `app/Domain/Confirmacao/Services/AgendarLembrete.php` — calcula `datahora_envio` = `consulta.datahora_agendada - janela_lembrete_horas`; ajusta para próxima janela operacional se cair fora.
  - `app/Domain/Confirmacao/Services/RespeitaJanelaOperacional.php` — helper puro.
  - `app/Listeners/AgendarLembreteAoCriarConsulta.php` — escuta evento de criação.
  - `app/Domain/Confirmacao/Guards/RateLimitClinicaGuard.php` — Token Bucket em Redis; limite default 50 msg/min/clínica (env `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO`).
  - `app/Domain/Confirmacao/Guards/IdempotenciaLembreteGuard.php` — chave `lembrete:{consulta_id}:{janela_id}`; lock Redis SETNX.
  - `tests/Unit/Domain/Confirmacao/{AgendarLembreteTest,RespeitaJanelaOperacionalTest,RateLimitClinicaGuardTest,IdempotenciaLembreteGuardTest}.php`
  - `tests/Feature/Confirmacao/DispararLembreteJobTest.php`
- **Entidades afetadas:** Notificacao (nova linha por tentativa); EventoConsulta (tipos `lembrete_agendado`, `lembrete_enviado`, `lembrete_falha_envio`, `lembrete_numero_invalido`).
- **Contratos:**
  - `AgendarLembrete::executar(ConsultaId $c): void` — cria evento `lembrete_agendado` e agenda o job.
  - `DispararLembreteJob::handle(NotificacaoDriver $d): void` — chama `$d->enviar(...)`, persiste Notificacao + evento, respeita guards.
- **Modelo de dados:** ver §4 (Notificacao + EventoConsulta).
- **Testes mínimos:**
  - Unit: `AgendarLembrete` posterga para próximo 08h se cálculo cair às 03h; cancela se postergar ultrapassar `datahora_agendada`.
  - Unit: `IdempotenciaLembreteGuard` bloqueia segunda execução do mesmo job com mesma chave.
  - Unit: `RateLimitClinicaGuard` bloqueia 51ª chamada dentro de 1 min.
  - Feature: criar consulta com janela 24h → `lembrete_agendado` evento aparece; job executa na hora prevista (testar com `Queue::fake()` + `Carbon::setTestNow()`); envio dispara driver mock.
  - Feature: simular falha transitória → 3 retries; após esgotar → evento `lembrete_falha_envio`.
- **Critério de "pronto":** pipeline de envio passa testes end-to-end com driver mock; guards funcionam; rate-limit aplicado; testes F5 passam.
- **Riscos técnicos:** clock skew entre app e banco → usar `NOW() AT TIME ZONE 'UTC'` consistentemente; cobertura em teste de janela.

### F6 — Processamento de resposta (webhook in + reconciliação)

- **Objetivo:** Recebe callback do provedor, reconcilia com o lembrete correspondente via `id_externo`, registra evento apropriado e atualiza status da consulta.
- **Depende de:** F3, F4, F5.
- **Arquivos criados/alterados:**
  - `app/Domain/Confirmacao/Jobs/ProcessarCallbackWhatsappJob.php` — enfileirado pelo controller do webhook.
  - `app/Domain/Confirmacao/Services/ReconciliarResposta.php` — encontra lembrete via id_externo; mapeia botão → tipo de evento; texto livre → `resposta_ambigua`.
  - `app/Domain/Confirmacao/Services/AplicarResposta.php` — registra evento; regra "última resposta vale" (FR-016).
  - `app/Domain/Confirmacao/Guards/CallbackIdempotenciaGuard.php` — idempotência por `provider_message_id` (NFR-006).
  - `tests/Unit/Domain/Confirmacao/{ReconciliarRespostaTest,AplicarRespostaTest}.php`
  - `tests/Feature/Confirmacao/ProcessarCallbackWhatsappJobTest.php`
- **Entidades afetadas:** EventoConsulta (tipos `resposta_recebida_confirmar`, `resposta_recebida_cancelar`, `resposta_recebida_reagendar`, `resposta_ambigua`).
- **Contratos:**
  - `ReconciliarResposta::executar(array $payload): ?ResultadoReconciliacao` — retorna null se não bate com nenhum lembrete (descarta).
  - `AplicarResposta::executar(ResultadoReconciliacao $r): EventoConsulta`.
- **Testes mínimos:**
  - Unit: botão "Confirmar" → evento `resposta_recebida_confirmar`; texto livre "amanhã" → `resposta_ambigua`.
  - Unit: duas respostas em sequência → ambas registradas; derivação final reflete a última.
  - Feature: callback duplicado (mesmo `provider_message_id`) → segundo descartado; nenhum evento duplicado.
  - Feature: callback órfão (sem lembrete correspondente) → log warning + nenhum evento.
- **Critério de "pronto":** paciente clica botão → status muda em <10s mediana (NFR-001); testes F6 passam.
- **Riscos técnicos:** payload Meta muda formato minor → testes de contrato com payload real capturado em dev.

### F7 — Painel do dia + intervenção manual + histórico

- **Objetivo:** UI Livewire que materializa User Stories 2 e 3.
- **Depende de:** F2, F3, F6.
- **Arquivos criados/alterados:**
  - `app/Http/Livewire/Confirmacao/DashboardDia.php` + view Blade.
  - `app/Http/Livewire/Confirmacao/ConsultaHistorico.php` + view Blade.
  - `app/Http/Livewire/Confirmacao/IntervencaoManualModal.php` + view Blade.
  - `app/Domain/Confirmacao/Services/{ConfirmarEmNomeDoPaciente,CancelarEmNomeDoPaciente,RegistrarReagendamentoManual}.php`
  - `app/Domain/Confirmacao/Services/NotificarPacienteCancelamentoTardio.php` (FR-025 — usa driver).
  - `tests/Feature/Livewire/{DashboardDiaTest,IntervencaoManualModalTest,ConsultaHistoricoTest}.php`
- **Entidades afetadas:** EventoConsulta (ator = atendente; canal = manual-pelo-painel).
- **Contratos:**
  - `ConfirmarEmNomeDoPaciente::executar(ConsultaId $c, UserId $atendente, ?string $obs): EventoConsulta`.
  - `CancelarEmNomeDoPaciente::executar(...)`.
  - `RegistrarReagendamentoManual::executar(ConsultaId $original, Carbon $novaDataHora, UserId $atendente): Consulta` (cria nova consulta + evento `reagendamento-efetivado` na original).
- **Testes mínimos:**
  - Feature: Livewire lista consultas de hoje e amanhã ordenadas; `sem-resposta` aparece com atributo CSS destacado.
  - Feature: atendente clica "Confirmar manualmente" → evento registrado com ator = user; status muda.
  - Feature: cancelar após lembrete enviado dispara `NotificarPacienteCancelamentoTardio` (FR-025).
  - Feature: histórico renderiza eventos em ordem cronológica, incluindo correções com ícone ⚠.
- **Critério de "pronto":** User Stories 2 e 3 testáveis manualmente (Quickstart F9); testes F7 passam.
- **Riscos técnicos:** polling do Livewire pode gerar carga em banco → limit query + cache de `status_cache` no modelo.

### F8 — Configuração de janelas + marcação compareceu/no-show + correção

- **Objetivo:** Admin configura janelas/retries; atendente marca presença; correção via evento `correcao` (C-005).
- **Depende de:** F2, F3, F7.
- **Arquivos criados/alterados:**
  - `app/Http/Livewire/Config/ClinicaConfigForm.php` + view.
  - `app/Domain/Confirmacao/Services/{MarcarCompareceu,MarcarNoShow,CorrigirMarcacao}.php`.
  - `app/Http/Middleware/ExigeIsAdmin.php`.
  - `tests/Feature/Config/{ClinicaConfigFormTest,ExigeIsAdminTest}.php`
  - `tests/Feature/Confirmacao/{MarcarCompareceuTest,CorrigirMarcacaoTest}.php`
- **Entidades afetadas:** Clinica (campos de config); EventoConsulta (tipos `compareceu`, `no_show`, `correcao`).
- **Contratos:**
  - `MarcarCompareceu::executar(ConsultaId $c, UserId $ator): EventoConsulta` — bloqueia se `datahora_agendada` > agora (FR-027).
  - `CorrigirMarcacao::executar(EventoConsultaId $original, TipoEvento $novoStatus, UserId $ator, string $motivo): EventoConsulta` — motivo obrigatório.
- **Testes mínimos:**
  - Feature: admin altera janela de 24h para 48h → próxima consulta criada agenda job para 48h; consultas com job já agendado mantêm janela original (FR-029).
  - Feature: atendente sem `is_admin` tenta acessar config → 403.
  - Feature: marcar compareceu antes do horário → 422.
  - Feature: corrigir sem motivo → 422; com motivo → evento `correcao` registrado; derivação de status reflete correção.
- **Critério de "pronto":** User Story 4 testável; testes F8 passam.

### F9 — Anonimização LGPD + retenção temporal

- **Objetivo:** Atendente-admin anonimiza paciente conforme LGPD art. 18 preservando integridade referencial; job agendado aplica anonimização temporal de eventos após 5 anos.
- **Depende de:** F3.
- **Arquivos criados/alterados:**
  - `app/Domain/Lgpd/Services/AnonimizarPaciente.php` — transação: sobrescreve PII + registra evento `anonimizacao`.
  - `app/Domain/Lgpd/Jobs/AnonimizacaoTemporalJob.php` — scheduler diário; busca eventos > 5 anos e nulifica `ator_id`, `ip`.
  - `app/Http/Livewire/Lgpd/AnonimizarPacienteModal.php` + view.
  - `app/Domain/Lgpd/Guards/GuardIntegridadeReferencial.php` — verifica que UPDATE não quebra FKs.
  - `tests/Feature/Lgpd/{AnonimizarPacienteTest,AnonimizacaoTemporalTest}.php`
- **Entidades afetadas:** Paciente (campos `anonimizado_em`, PII sobrescrita); EventoConsulta (tipo `anonimizacao` + anonimização temporal de `ator_id`/`ip`).
- **Contratos:**
  - `AnonimizarPaciente::executar(PacienteId $p, UserId $admin): void` — transacional.
- **Testes mínimos:**
  - Feature: admin anonimiza → nome vira `paciente-excluido-<hash>`, telefone/email nulos; consultas do paciente **continuam** visíveis com referência intacta.
  - Feature: evento `anonimizacao` é registrado com ator = user admin.
  - Feature: job temporal processa eventos > 5 anos → `ator_id` e `ip` viram null; demais campos preservados.
- **Critério de "pronto":** FR-033 testado com integridade referencial garantida; testes F9 passam.
- **Riscos técnicos:** corrida entre anonimização e disparo de lembrete → lock pessimista no paciente durante a operação.

### F10 — Observabilidade + CI/CD + template de PR

- **Objetivo:** Logging estruturado, métricas, CI verde com Pint+PHPStan+Pest, template de PR, validação do rate-limit em cenário real.
- **Depende de:** todas as fases anteriores.
- **Arquivos criados/alterados:**
  - `config/logging.php` — canal JSON em prod.
  - `app/Logging/MascarararPiiProcessor.php` — middleware de logging que remove telefone/email dos logs.
  - `app/Metrics/ConfirmacaoMetrics.php` — contadores e histogramas (via `laravel-prometheus` ou equivalente a definir em Plan F10).
  - `.github/workflows/ci.yml` — jobs: Pint check, PHPStan nível 5, Pest com coverage mínimo 70%, security audit `composer audit`.
  - `.github/PULL_REQUEST_TEMPLATE.md` — seções: FRs implementados, D-NNN/C-NNN tocados, testes adicionados, checklist de §5.4.
  - `phpstan.neon`, `pint.json` — configs.
  - `README.md` (raiz do módulo) — seção de setup local, env vars, rodar testes.
  - `tests/Integration/RateLimitFluxoRealTest.php` — valida `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO=50` com envio em rajada simulada.
- **Entidades afetadas:** nenhuma (infra).
- **Contratos:** n/a.
- **Testes mínimos:**
  - CI passa em PR dummy.
  - Rate limit bloqueia 51ª msg por clínica dentro de 1 min em teste de integração.
  - Logs de produção não contêm telefone em claro (teste de processador).
- **Critério de "pronto":** CI verde no main; métricas visíveis (pelo menos no `/metrics` exposto em rota protegida); template de PR ativo; coverage ≥ 70%; testes F10 passam.
- **Riscos técnicos:** coverage inicial pode ser < 70% — ajustar piso se necessário via ADR local minor.

---

## 4. Modelo de dados completo

```
Clinica
  id              : bigint PK
  nome            : varchar(120)
  janela_lembrete_horas     : smallint default 24   -- C-004
  janela_silencio_horas     : smallint default 4    -- C-004
  envio_inicio_hora         : smallint default 8    -- C-004
  envio_fim_hora            : smallint default 20   -- C-004
  retry_max                 : smallint default 3    -- C-004
  timestamps
  -- MVP: 1 linha apenas (D-003 single-tenant)

User
  id              : bigint PK
  clinica_id      : bigint FK → Clinica
  nome            : varchar(120)
  email           : varchar(180) unique
  senha_hash      : varchar
  role            : enum('atendente','medico')    -- C-002
  is_admin        : boolean default false         -- C-002
  remember_token  : varchar nullable
  timestamps

Paciente
  id              : bigint PK
  clinica_id      : bigint FK → Clinica
  nome            : varchar(180)                  -- PII; NFR-003
  telefone_whatsapp : varchar(20)                 -- PII; único por clínica
  email           : varchar(180) nullable          -- PII
  anonimizado_em  : timestamptz nullable           -- C-003 / FR-033
  timestamps
  unique (clinica_id, telefone_whatsapp) -- desativado quando telefone = null por anonimização

Medico
  id              : bigint PK
  clinica_id      : bigint FK → Clinica
  nome            : varchar(180)
  especialidade   : varchar(120)
  ativo           : boolean default true
  timestamps

Consulta
  id              : bigint PK
  clinica_id      : bigint FK → Clinica
  paciente_id     : bigint FK → Paciente
  medico_id       : bigint FK → Medico
  datahora_agendada : timestamptz                  -- UTC
  status_cache    : varchar(32)                    -- projeção; derivado de eventos
  criado_por_user_id : bigint FK → User nullable
  janela_lembrete_horas_usada : smallint           -- snapshot no momento da criação (FR-029)
  janela_silencio_horas_usada : smallint           -- snapshot
  timestamps
  index (clinica_id, datahora_agendada)

EventoConsulta            -- APPEND-ONLY (trigger + observer)
  id              : ULID PK
  consulta_id     : bigint FK → Consulta
  tipo            : varchar(40)                    -- enum TipoEvento
  ator_tipo       : varchar(24)                    -- 'paciente' | 'atendente' | 'sistema-automacao'
  ator_id         : bigint FK → User nullable      -- anonimizável após 5a
  canal           : varchar(24)                    -- 'whatsapp' | 'manual-pelo-painel' | 'sistema-automacao'
  id_externo_provedor : varchar(80) nullable       -- Meta / Z-API message id
  ip              : varchar(45) nullable           -- anonimizável após 5a
  motivo          : text nullable                  -- obrigatório em tipo 'correcao' e 'anonimizacao'
  payload_extra   : jsonb                          -- metadados tipo-específicos
  ref_evento_id   : ULID FK → EventoConsulta nullable  -- usado em 'correcao'
  criado_em       : timestamptz
  index (consulta_id, criado_em)

Notificacao
  id              : bigint PK
  evento_consulta_id : ULID FK → EventoConsulta (tipo lembrete_enviado)
  driver          : varchar(16)                    -- 'meta' | 'zapi' | 'noop'
  id_externo      : varchar(80)                    -- Meta message id
  status_entrega  : varchar(16)                    -- 'enviado' | 'delivered' | 'read' | 'failed'
  retorno_provedor : jsonb
  tentativa       : smallint                       -- 1..retry_max
  timestamps
  unique (driver, id_externo)                      -- idempotência NFR-006
```

**Relações:**
- Clinica 1—N User, Paciente, Medico, Consulta
- Paciente 1—N Consulta
- Medico 1—N Consulta
- Consulta 1—N EventoConsulta
- EventoConsulta 1—0..1 Notificacao (apenas eventos `lembrete_enviado`)
- EventoConsulta 1—0..1 EventoConsulta (via `ref_evento_id` — usado em `correcao`)

**Invariantes de integridade:**
- `EventoConsulta` é append-only (trigger PG + Observer Eloquent — F3).
- `Paciente.anonimizado_em != null` ⇒ `nome` é tombstone + `telefone_whatsapp = null` + `email = null`.
- `EventoConsulta.tipo = 'correcao'` ⇒ `motivo != null` E `ref_evento_id != null`.
- `EventoConsulta.tipo = 'anonimizacao'` ⇒ `motivo != null` E `ator_tipo = 'atendente'` E aplicador tem `is_admin = true`.

---

## 5. Integrações externas (consolidado)

| Serviço | Finalidade | Auth | Rate limit | Timeout | Retry | Fallback |
|---|---|---|---|---|---|---|
| **Meta Cloud API (WhatsApp)** | Envio de lembretes + recepção de callbacks | Bearer token (`META_ACCESS_TOKEN`) | Aplicativo: definido pela categoria do template `utility`; app-lado: `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO=50` | 10s no envio | 3× com backoff 5/15/45 min em `FalhaTransitoriaException`; 0 em `FalhaDefinitivaException` (numero-invalido) | (1) Troca para `NoopDriver` via env em emergência; (2) Intervenção manual do atendente (User Story 3); (3) Se categoria do template mudar, notifica admin e bloqueia envios (`WHATSAPP_DRIVER=noop` automático) |
| **Z-API** (alternativa) | Mesma finalidade; driver substituto via `WHATSAPP_DRIVER=zapi` | API key no header | Varia por plano do provedor | 10s | Idem Meta | Idem Meta |

**Idempotência obrigatória (NFR-006):**
- **Envio:** header `Idempotency-Key` = hash(`lembrete:{consulta_id}:{janela_id}`).
- **Recepção:** `CallbackIdempotenciaGuard` usa `provider_message_id` como chave única; duplicados descartados.

**Manual §29 — 9 campos da automação de lembrete consolidados:**
| Campo | Especificação |
|---|---|
| Gatilho | Evento `criada` da Consulta dispara `AgendarLembrete` → agenda `DispararLembreteJob` para `datahora_agendada - janela_lembrete_horas` (ajustado à janela operacional). |
| Contexto lido | Consulta + Paciente + Medico + Clinica.janelas. |
| Decisão tomada | Enviar se (paciente tem WhatsApp válido ∧ janela operacional ativa ∧ rate-limit disponível ∧ sem envio prévio do mesmo lembrete). Caso contrário postergar ou cancelar. |
| Ação executada | `NotificacaoDriver::enviar(...)` + persistência de `EventoConsulta(lembrete_enviado)` + `Notificacao(enviado)`. |
| Condição de bloqueio | Janela fora de 08–20 BRT → posterga; idempotência detectada → descarta; `numero-invalido` → registra falha sem retry; rate-limit cheio → reagenda +1min. |
| Fallback | Retry 3× em transitório; se esgotar → evento `lembrete_falha_envio` + alerta no painel → User Story 3. |
| Log | Evento imutável com escopo C-006 (ts + canal + ator + id_externo + ip + motivo opcional). |
| Critério de sucesso | NFR-007 (R$ 0,20) + SC-001 (98% cobertura) + SC-005 (100% reconciliação) + SC-006 (99% disponibilidade). |
| Risco de falso positivo | Consulta cancelada após lembrete enviado → FR-025 dispara mensagem de cancelamento; se evento `anonimizacao` ocorrer em trânsito → lembrete é cancelado antes do envio efetivo. |

---

## 6. Decisões técnicas

| # | Decisão | Opções consideradas | Escolhida | Motivo | Alinha com constituição? |
|---|---|---|---|---|---|
| DT-01 | ULID vs UUIDv7 para `EventoConsulta.id` | UUIDv4 (aleatório) / UUIDv7 (time-ordered) / ULID (time-ordered + base32) | **ULID** | Ordenação lexicográfica por tempo (útil em consulta de histórico cronológico) + compacidade em logs + suporte via `symfony/uid` já embutido no Laravel 12 | Camada 2 (implementação); sem conflito com invariantes |
| DT-02 | Derivação de status: projeção pura ou cache | Cada leitura recalcula a partir de eventos / cache em `consultas.status_cache` atualizado via listener / materialized view | **Cache via listener** | Painel faz leituras frequentes; cache em coluna é suficientemente robusto e simples; listener é idempotente e pode ser re-rodado para reconstruir | Camada 2; invariante de C-005 preservado (reducer é a fonte da verdade; cache é projeção descartável) |
| DT-03 | Guard append-only: aplicação, DB ou ambos | Só Eloquent observer / só trigger PG / ambos | **Ambos** | Defesa em profundidade: observer pega 99% dos casos em dev/teste; trigger PG é a barreira final contra DELETE via SQL cru | D-E-03 (histórico imutável por construção) |
| DT-04 | Provedor WhatsApp | Meta Cloud API / Z-API / Twilio WhatsApp | **Meta Cloud API** | Ver [`adr_local_001_provedor_whatsapp.md`](adr_local_001_provedor_whatsapp.md) | Camada 2 §4; contrato abstrato preservado (troca em 1 sprint) |
| DT-05 | Queue driver | Redis / Database / SQS | **Redis** (já em Camada 2) | Já parte da stack; performance adequada para volume MPE; integração Laravel nativa | Camada 2 §4 (D-001) |
| DT-06 | UI state live | Livewire 3 polling / Livewire wire:poll / Turbo Streams / SSE | **Livewire `wire:poll` a cada 30s** no painel | Polling leve é aceitável para MPE (< 100 consultas/dia); SSE/WS é overkill no MVP | Camada 2 §4; sem conflito |
| DT-07 | Validação de número WhatsApp | Regex simples / libphonenumber / validação do provedor | **libphonenumber (brick/phonenumber)** | Parse robusto DDI+DDD+número; aceita ou rejeita antes de gastar envio | Camada 2 (lib nova — adicionar ao composer.json é ADR minor, registrar histórico em F1) |
| DT-08 | Rate limit numérico residual da constituição | 20/min / 50/min / 100/min por clínica | **50/min** | MPE típica envia ~40 lembretes/dia; 50/min cobre picos sem bloquear uso legítimo; ajustável via env | Resolve `[NEEDS CLARIFICATION]` da Camada 2 §4 da constituição |
| DT-09 | Locks de concorrência (anonimização vs envio) | Lock pessimista na row do Paciente / fila serializada / optimistic + retry | **Lock pessimista** (`SELECT FOR UPDATE`) | Baixa contenção no perfil MPE; garantia forte de consistência | Camada 2 |
| DT-10 | Library de métricas | Prometheus (exporter próprio) / Laravel Telescope / OpenTelemetry | **A decidir em F10 com ADR minor** | Decisão envolve infra; preservar flexibilidade | Camada 2 — ADR minor em F10 |

---

## 7. Riscos técnicos e mitigações

| # | Risco | Probabilidade | Impacto | Mitigação |
|---|---|---|---|---|
| R-01 | Meta reprova/suspende template `utility` para saúde | Média | Alto (para produção) | Template pré-aprovado em dev + template "backup" em categoria revisável; alerta admin imediato se rejeitado; envio bloqueado via `WHATSAPP_DRIVER=noop` automático (driver detecta erro de categoria) |
| R-02 | Trigger append-only impacta performance de INSERT | Baixa | Médio | Benchmark F10 com 10k inserts/s simulados; se degradar > 20%, reverter para observer-only + fingerprint de integridade |
| R-03 | Clock skew entre servidor e banco causa agendamento errado | Baixa | Médio | `APP_TIMEZONE=UTC` + `SET TIMEZONE TO 'UTC'` na conexão; teste de unit com `Carbon::setTestNow` |
| R-04 | Race anonimização vs lembrete em trânsito | Baixa | Alto (envio pós-anonimização viola LGPD) | Lock pessimista no Paciente durante anonimização (DT-09); job checa `anonimizado_em != null` no início do `handle()` e aborta se sim |
| R-05 | Callback chega com delay muito alto (> 4h) | Média | Médio | Idempotência na reconciliação; evento registrado com `criado_em` real do callback; painel mostra "resposta atrasada" visualmente; derivação de status aplica mesmo assim (FR-016 última resposta vale) |
| R-06 | Livewire polling sobrecarrega DB com 100 atendentes simultâneos | Baixa no MPE | Baixo | MPE típico tem 1-2 atendentes simultâneos; cache `status_cache` em coluna absorve; se escalar, trocar para Turbo Streams/SSE em fase futura |
| R-07 | `libphonenumber` grande no bundle | Baixa | Baixo | Tamanho é no backend apenas; ignorar |
| R-08 | Atendente configura janela absurda (ex: 1h lembrete + 0h silêncio) | Média | Médio | Validação na Livewire: `lembrete ≥ 2h E lembrete > silencio E silencio ≥ 1h`; alerta UX se config for "agressiva" |
| R-09 | Webhook receber callback malformado (ataque ou bug Meta) | Baixa | Baixo | Validação estrita do payload + 200 sempre para não bloquear Meta + log de warning; descartar sem processar |
| R-10 | Coverage Pest < 70% no MVP | Média | Baixo | Ajustar threshold para 60% como piso inicial com ADR minor em F10; objetivo 70% ao fim de W2 |

---

## 8. Observabilidade planejada

### Logs
- **Canal prod:** `stack` com driver `single` + formatter JSON.
- **Campos padrão:** `timestamp`, `level`, `message`, `request_id`, `user_id`, `clinica_id`, `consulta_id`, `evento_id`.
- **Nível:** `info` padrão; `debug` só em dev via `.env.local`.
- **PII masking (NFR-003):** `MascarararPiiProcessor` remove `telefone_whatsapp`, `email`, e qualquer campo com nome contendo `pii:` antes de emitir.

### Métricas
| Métrica | Tipo | Labels | Alerta |
|---|---|---|---|
| `confirmacao_lembretes_enviados_total` | counter | `clinica_id`, `driver` | — |
| `confirmacao_lembretes_falhados_total` | counter | `clinica_id`, `driver`, `tipo_falha` | > 5% de `lembretes_enviados` em 1h → PagerDuty |
| `confirmacao_respostas_recebidas_total` | counter | `clinica_id`, `tipo_resposta` | — |
| `confirmacao_confirmacoes_explicitas_total` | counter | `clinica_id` | — |
| `confirmacao_no_show_total` | counter | `clinica_id` | — |
| `confirmacao_tempo_resposta_paciente_segundos` | histogram | `clinica_id` | p50 > 4h → alerta informativo |
| `notificacao_queue_depth` | gauge | `clinica_id` | > 100 → alerta |
| `notificacao_callback_orfaos_total` | counter | — | > 10/h → alerta |

### Traces
- **Pontos instrumentados:** entrada do `NotificacaoDriver::enviar` + persistência do evento; entrada e saída do webhook controller; `ReconciliarResposta`; `AnonimizarPaciente`.
- **Export:** OpenTelemetry OTLP (destino específico — coletor/Tempo/Jaeger — decidido em ADR minor F10).

### Alertas
- Falha de template Meta → alerta crítico imediato.
- Rate de falha > 5% em 1h → alerta alto.
- Queue depth > 100 → alerta médio.
- Callback órfão > 10/h → alerta médio (pode indicar template divergente).

---

## 9. Plano de rollback

### 9.1 Rollback por fase
- **F1-F2 (bootstrap + agendamento):** `migrate:rollback` é seguro (tabelas vazias). Deploy revertido via `git revert` + redeploy.
- **F3 (histórico):** migrations reversíveis; trigger PG cai no `DROP TRIGGER`. Dado histórico acumulado fica intacto (não é deletado).
- **F4 (driver):** `WHATSAPP_DRIVER=noop` desativa envio real em < 1min. Rotas de webhook ficam ativas mas descartam payload (idempotência garante consistência).
- **F5-F6 (envio + callback):** `QUEUE_CONNECTION=sync` desativa processamento assíncrono; `schedule:run` pausado via supervisor.
- **F7-F8 (UI + config):** reversão estética; dados consistentes.
- **F9 (anonimização):** **sem rollback** possível — anonimização é operação final por contrato LGPD. Se ferramenta de anonimização introduzir bug que anonimize erroneamente, é incidente grave; mitigação preventiva = lock pessimista + confirmação explícita "Digite o nome do paciente" antes de confirmar.
- **F10 (infra):** CI pode ser revertido sem afetar runtime; métricas podem ser desativadas.

### 9.2 Rollback global do módulo
Se o canônico precisar ser totalmente revertido:
1. `WHATSAPP_DRIVER=noop` (para qualquer envio imediatamente).
2. `schedule:run` desativado.
3. Redirecionar `/` para página estática "em manutenção".
4. DB preservado; dados podem ser exportados para análise.

### 9.3 Estratégia de deploy
- **Ambiente único inicialmente** (dev local + 1 prod clínica-piloto). Staging separado vira necessidade quando houver 2ª clínica.
- **Deploy via Forge**: push para main → Forge hook → `composer install --no-dev --optimize-autoloader` → `php artisan migrate --force` → `php artisan config:cache route:cache view:cache` → `php artisan queue:restart` → `php artisan horizon:terminate` (se Horizon).
- **Janela de manutenção:** usar `php artisan down` para deploys que envolvem migration não-aditiva; deploys aditivos (só adicionar colunas/tabelas) sem janela.

---

## Gate pós-Plan (`fases/04_PLAN.md` + `checklists/qualidade-plano.md`)

- [x] 10 fases de implementação com objetivo isolado e critério de "pronto" mensurável.
- [x] Dependências entre fases explícitas (F3 depende de F2; F5 depende de F3+F4; etc.).
- [x] Arquivos criados/alterados listados nominalmente em cada fase (~80 arquivos mapeados).
- [x] Contratos técnicos descritos (assinatura de serviços + endpoints + eventos).
- [x] Modelo de dados completo cobrindo 7 entidades + invariantes de integridade.
- [x] 2 integrações externas listadas com auth/timeout/retry/fallback/idempotência (Meta primária + Z-API alternativa).
- [x] 10 decisões técnicas (DT-01..DT-10) com alternativas e alinhamento com constituição.
- [x] 10 riscos técnicos com mitigação.
- [x] Observabilidade: logs + métricas + traces + alertas.
- [x] Plano de rollback por fase + global + estratégia de deploy.
- [x] ADR local **ADR-L-001** (provedor WhatsApp) criado e referenciado em DT-04.
- [x] Plano respeita constituição: Camada 1 (D-E-01..D-E-06) não é violada em nenhuma fase; Camada 2 (stack D-001 + parâmetros C-NNN) é consumida, não redecidida.
- [x] **Resolve residual de constituição:** rate-limit numérico especificado em DT-08 (50/min/clínica, configurável via env).
- [x] Lint `OK` em `plan.md` e `adr_local_001_provedor_whatsapp.md`.
- [ ] Validação humana — pendente, via merge do PR `w1b/f4-plan`.

**Veredicto do Arquiteto:** 🟢 draft fechado. Próxima fase: **Fase 5 Tasks** — quebra cada F1..F10 em cartões operacionais T-NNN com estimativa, checklist de pronto, ordem e responsável.
