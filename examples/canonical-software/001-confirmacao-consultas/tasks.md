---
artefato: tasks
fase: 5
dominio: [software]
schema_version: 1
requer:
  - "Matriz de rastreabilidade (FR ↔ Task)"
  - "Matriz de rastreabilidade (Edge Case ↔ Task/Teste)"
---

# Tasks — `001-confirmacao-consultas` (Canônico D1)

**Referência:** `plan.md` v1 · `spec.md` v2 · `constitution.md` v1.1 (após merge do ADR-L-001) · `analyze.md` (Fase 6)
**Data:** 2026-04-20 (v1 · 59 tasks) · 2026-04-20 (v2 pós-Analyze · 63 tasks com ajustes P-01 a P-05)
**Status:** Pós-Analyze (v2)
**Autor:** Thiago Loumart (modo Arquiteto)

Implementação **por fase** (Manual §12). Cada task = unidade executável pequena com dependências explícitas e DoD concreto. Agrupadas por F1..F10 do `plan.md §3`.

---

## Legenda

- **Risco:** 🟢 baixo · 🟡 médio · 🔴 alto.
- **Estado:** ⬜ pendente · 🔶 em andamento · ✅ feita · ⛔ bloqueada.
- **DoD padrão** (aplica-se a todas as tasks salvo override na task):
  - [ ] Código escrito no arquivo-alvo respeitando boundaries da `constitution.md §5`.
  - [ ] Testes escritos e passando (Pest); cobertura incrementa.
  - [ ] Lint Pint + PHPStan nível 5 sem novos erros.
  - [ ] Commit em Conventional Commits (`feat|fix|test|docs|refactor|chore(scope): msg`).
  - [ ] Branch dedicada + PR com descrição citando FR(s) e task ID.
  - [ ] Self-review mínimo antes de abrir PR.

---

## Fase F1 — Bootstrap + Cadastro mínimo

### T-001 — Bootstrap Laravel 12 + stack Camada 2 + 1ª migração `clinicas`

- **Estado:** ⬜
- **Depende de:** nenhuma
- **Descrição:** cria o projeto Laravel 12; configura PG 16, Redis 7, filas; cria seeder de Clinica única MVP (single-tenant D-003) com defaults C-004.
- **Arquivos:** `composer.json`, `.env.example`, `config/{database,queue,cache}.php`, `database/migrations/2026_04_21_000001_create_clinicas_table.php`, `database/seeders/ClinicaSeeder.php`, `app/Models/Clinica.php`.
- **Contrato afetado:** `Clinica` (modelo + seeder).
- **Testes exigidos:** sucesso — `php artisan migrate --seed` sobe DB com 1 linha em `clinicas` com defaults; erro — `migrate:rollback` desfaz; edge — rodar seed 2x não duplica (`updateOrCreate`).
- **Risco:** 🟢 — boilerplate Laravel.

### T-002 — Migração + model `User` (role + is_admin) + AuthBreeze

- **Estado:** ⬜
- **Depende de:** T-001
- **Descrição:** Laravel Breeze para auth (login/logout); adiciona colunas `role` (`atendente`|`medico`), `is_admin` (bool), `clinica_id` em `users`. Bloqueia `paciente` como papel.
- **Arquivos:** `database/migrations/2026_04_21_000002_create_users_table.php`, `app/Models/User.php`, `app/Http/Middleware/ExigeAutenticacao.php` (wrapper), `routes/auth.php` (Breeze).
- **Contrato afetado:** `User` + rotas de auth.
- **Testes exigidos:** sucesso — login com credencial válida redireciona; erro — login inválido não revela se é email errado ou senha errada; edge — papel `paciente` no seed é rejeitado.
- **Risco:** 🟢.

### T-003 — Migração + model `Paciente` (PII + anonimizavel + telefone nullable) *(editada pós-Analyze — P-03)*

- **Estado:** ⬜
- **Depende de:** T-001
- **Descrição:** cria tabela `pacientes` com campos PII (`nome`, `telefone_whatsapp` **nullable**, `email nullable`) + `anonimizado_em nullable` + índice único parcial `(clinica_id, telefone_whatsapp)` com `WHERE anonimizado_em IS NULL AND telefone_whatsapp IS NOT NULL`. Regra de negócio: paciente **sem WhatsApp** (`telefone_whatsapp IS NULL`) é válido — consultas desse paciente são criadas com evento inicial `sem-canal` além do `criada`, sinalizando imediatamente ao atendente que não haverá lembrete automático (fallback humano integral, D-E-02 + edge case spec).
- **Arquivos:** `database/migrations/2026_04_21_000003_create_pacientes_table.php`, `app/Models/Paciente.php`, `app/Domain/Cadastro/Valores/TelefoneWhatsapp.php` (VO com validação via `brick/phonenumber`). Enum `TipoEvento` (T-011) inclui `sem_canal`.
- **Contrato afetado:** `Paciente` (model com `telefone_whatsapp` nullable) + VO TelefoneWhatsapp.
- **Testes exigidos:** sucesso — criar paciente com telefone BR válido; sucesso — criar paciente **sem telefone** (null); erro — telefone com formato inválido rejeita; edge — 2 pacientes anonimizados com mesmo telefone antigo não colidem no unique parcial; edge — criar consulta para paciente com `telefone_whatsapp = null` → evento `sem-canal` registrado + painel mostra alerta.
- **Risco:** 🟡 (aumentado de 🟢 após ajuste — introduz estado `sem-canal` que requer listener em F5 para **não** agendar lembrete).

### T-004 — Migração + model `Medico`

- **Estado:** ⬜
- **Depende de:** T-001
- **Descrição:** tabela `medicos` com `nome`, `especialidade`, `ativo`.
- **Arquivos:** `database/migrations/2026_04_21_000004_create_medicos_table.php`, `app/Models/Medico.php`.
- **Testes exigidos:** sucesso — criar médico; edge — desativar médico mantém registro mas flag ativo=false.
- **Risco:** 🟢.

### T-005 — Service `CadastrarPaciente` + `CadastrarMedico`

- **Estado:** ⬜
- **Depende de:** T-003, T-004
- **Descrição:** serviços de domínio em `app/Domain/Cadastro/Services/`; validação de entrada; retorno do modelo criado; exceções tipadas (`DadosInvalidosException`).
- **Arquivos:** `app/Domain/Cadastro/Services/CadastrarPaciente.php`, `CadastrarMedico.php`, `app/Domain/Cadastro/Exceptions/DadosInvalidosException.php`.
- **Contrato afetado:** `CadastrarPaciente::executar(ClinicaId, array): Paciente`; `CadastrarMedico::executar(ClinicaId, array): Medico`.
- **Testes exigidos:** unit — sucesso, validação de campos obrigatórios, detecção de duplicata.
- **Risco:** 🟢.

### T-006 — Livewire Cadastro UI (paciente + médico: Form + List)

- **Estado:** ⬜
- **Depende de:** T-005, T-002
- **Descrição:** 4 componentes Livewire (PacienteForm, PacienteList, MedicoForm, MedicoList) + views Blade; rotas autenticadas; menu lateral básico.
- **Arquivos:** `app/Http/Livewire/Cadastro/{PacienteForm,PacienteList,MedicoForm,MedicoList}.php` + views em `resources/views/livewire/cadastro/*.blade.php`, `routes/web.php`.
- **Contrato afetado:** rotas `/pacientes` e `/medicos` (Livewire páginas).
- **Testes exigidos:** feature — atendente cria paciente via form; sem auth redireciona login; atendente sem `is_admin` cria paciente ok mas cadastro de médico requer admin.
- **Risco:** 🟡 — primeira tela real; validar UX Livewire 3.

---

## Fase F2 — Agendamento mínimo

### T-007 — Migração + model `Consulta` + snapshot de janelas

- **Estado:** ⬜
- **Depende de:** T-003, T-004
- **Descrição:** tabela `consultas` com `datahora_agendada` (`timestamptz`), `status_cache` (nullable até F3), `criado_por_user_id` (FK nullable), `janela_lembrete_horas_usada` + `janela_silencio_horas_usada` (snapshot FR-029), índice `(clinica_id, datahora_agendada)`.
- **Arquivos:** `database/migrations/2026_04_21_000005_create_consultas_table.php`, `app/Models/Consulta.php`.
- **Contrato afetado:** `Consulta` (modelo).
- **Testes exigidos:** unit — criar consulta persiste snapshot das janelas vigentes da Clínica; edge — alterar config da Clínica depois não afeta consulta existente.
- **Risco:** 🟢.

### T-008 — Service `CriarConsulta`

- **Estado:** ⬜
- **Depende de:** T-007
- **Descrição:** regra de negócio para criar consulta; rejeita data passada; snapshot das janelas; status inicial `agendada`.
- **Arquivos:** `app/Domain/Agendamento/Services/CriarConsulta.php`, `app/Domain/Agendamento/Exceptions/DataNoPassadoException.php`.
- **Contrato afetado:** `CriarConsulta::executar(ClinicaId, PacienteId, MedicoId, Carbon): Consulta`.
- **Testes exigidos:** unit — sucesso + rejeição data passada + snapshot de janelas registrado.
- **Risco:** 🟢.

### T-009 — Services `EditarConsulta` + `CancelarConsultaPelaClinica`

- **Estado:** ⬜
- **Depende de:** T-008
- **Descrição:** editar altera data/hora ou médico; cancelar transiciona para `cancelada-pela-clinica`; bloqueia edição em estado terminal (`compareceu`, `no-show`).
- **Arquivos:** `app/Domain/Agendamento/Services/{EditarConsulta,CancelarConsultaPelaClinica}.php`, `app/Domain/Agendamento/Exceptions/TransicaoInvalidaException.php`.
- **Testes exigidos:** unit — edição normal ok; edição em status terminal rejeitada; cancelamento gera evento (stub até F3).
- **Risco:** 🟢.

### T-010 — Livewire ConsultaForm + ConsultaList

- **Estado:** ⬜
- **Depende de:** T-008, T-009
- **Descrição:** UI Livewire para criar/editar/cancelar consulta.
- **Arquivos:** `app/Http/Livewire/Agendamento/{ConsultaForm,ConsultaList}.php` + views.
- **Testes exigidos:** feature — atendente agenda consulta; seleciona paciente+médico+data; vê consulta na lista.
- **Risco:** 🟡 — integra 3 domínios (Paciente, Médico, Consulta).

---

## Fase F3 — Histórico imutável + eventos

### T-011 — Enums `TipoEvento`, `AtorTipo`, `Canal`

- **Estado:** ⬜
- **Depende de:** nenhuma
- **Descrição:** enums PHP para garantir tipagem estrita do histórico.
- **Arquivos:** `app/Domain/Confirmacao/Eventos/{TipoEvento,AtorTipo,Canal}.php`.
- **Testes exigidos:** unit — enum cobre todos os tipos listados em plan §4 (criada, editada, cancelada_clinica, lembrete_agendado, lembrete_enviado, lembrete_falha_envio, lembrete_numero_invalido, resposta_recebida_{confirmar,cancelar,reagendar}, resposta_ambigua, status_sem_resposta, compareceu, no_show, correcao, anonimizacao).
- **Risco:** 🟢.

### T-012 — Migração `eventos_consulta` (ULID + append-only schema)

- **Estado:** ⬜
- **Depende de:** T-007, T-011
- **Descrição:** tabela com `id ULID`, campos do plan §4 (tipo, ator_tipo, ator_id, canal, id_externo_provedor, ip, motivo, payload_extra jsonb, ref_evento_id, criado_em), índice `(consulta_id, criado_em)`.
- **Arquivos:** `database/migrations/2026_04_21_000006_create_eventos_consulta_table.php`, `app/Models/EventoConsulta.php` (com overrides de `update()`/`delete()` lançando exception).
- **Testes exigidos:** unit — insert de evento persiste; tentativa `->update()` lança `HistoricoImutavelException`; `->delete()` idem.
- **Risco:** 🟡.

### T-013 — Migração trigger PG `BEFORE UPDATE OR DELETE` append-only

- **Estado:** ⬜
- **Depende de:** T-012
- **Descrição:** função + trigger em `eventos_consulta` que lança exceção PG (`RAISE EXCEPTION 'eventos_consulta: append-only'`) — barreira defense-in-depth (DT-03).
- **Arquivos:** `database/migrations/2026_04_21_000007_add_append_only_trigger_eventos.php`.
- **Testes exigidos:** feature — `DB::table('eventos_consulta')->where(...)->delete()` lança PDO exception; `->update()` idem; INSERT continua funcionando.
- **Risco:** 🔴 — operação em DB com potencial de impacto em performance; precisa benchmark em T-069.

### T-014 — Migração `status_cache` em `consultas`

- **Estado:** ⬜
- **Depende de:** T-012
- **Descrição:** adiciona coluna `status_cache varchar(32)` em `consultas` (nullable). Será preenchida por listener após cada evento.
- **Arquivos:** `database/migrations/2026_04_21_000008_add_status_cache_to_consultas.php`.
- **Testes exigidos:** unit — coluna existe e aceita valores do enum TipoEvento correspondente.
- **Risco:** 🟢.

### T-015 — Service `RegistrarEvento`

- **Estado:** ⬜
- **Depende de:** T-012, T-013
- **Descrição:** ponto **único** de escrita no histórico (facade); wrapper de `EventoConsulta::create()` que dispara listener de atualização de status_cache.
- **Arquivos:** `app/Domain/Confirmacao/Services/RegistrarEvento.php`.
- **Contrato afetado:** `RegistrarEvento::executar(ConsultaId, TipoEvento, AtorTipo, ?int $ator_id, Canal, array $extra, ?EventoConsultaId $ref): EventoConsulta`.
- **Testes exigidos:** unit — persiste evento + dispara listener; sobrescrita de `update()` no model nunca é chamada.
- **Risco:** 🟡 — ponto crítico do domínio.

### T-016 — Service `DerivarStatus` (reducer)

- **Estado:** ⬜
- **Depende de:** T-012, T-015
- **Descrição:** reducer puro que lê eventos ordenados por `criado_em`, aplica eventos `correcao` sobre referenciados, retorna string do status.
- **Arquivos:** `app/Domain/Confirmacao/Services/DerivarStatus.php`.
- **Contrato afetado:** `DerivarStatus::executar(ConsultaId): string`.
- **Testes exigidos:** unit — `[criada, lembrete_enviado, resposta_recebida_confirmar]` → `confirmada`; `[..., no_show, correcao(→compareceu)]` → `compareceu`; sem eventos → `agendada` default.
- **Risco:** 🟡.

### T-017 — Listener `AtualizarStatusCacheDaConsulta`

- **Estado:** ⬜
- **Depende de:** T-015, T-016
- **Descrição:** event listener Laravel que observa `EventoConsulta::created` e atualiza `consulta.status_cache = DerivarStatus::executar(consulta_id)`.
- **Arquivos:** `app/Listeners/AtualizarStatusCacheDaConsulta.php`, `app/Providers/EventServiceProvider.php` (registro).
- **Testes exigidos:** feature — criar evento dispara listener; status_cache fica consistente com `DerivarStatus`.
- **Risco:** 🟡.

### T-018 — Eventos iniciais emitidos por CriarConsulta/Editar/Cancelar

- **Estado:** ⬜
- **Depende de:** T-008, T-009, T-015
- **Descrição:** retrofitar services F2 para emitir eventos `criada`, `editada`, `cancelada_clinica` via `RegistrarEvento` (antes era stub em T-009).
- **Arquivos:** `app/Domain/Agendamento/Services/{CriarConsulta,EditarConsulta,CancelarConsultaPelaClinica}.php` (edições).
- **Testes exigidos:** feature — criar consulta gera evento `criada`; cancelar gera `cancelada_clinica`; `status_cache` reflete.
- **Risco:** 🟡 — mudança retroativa em F2.

---

## Fase F4 — Driver abstrato + MetaCloudDriver + webhook

### T-019 — Interface `NotificacaoDriver` + VOs + Exceptions

- **Estado:** ⬜
- **Depende de:** nenhuma
- **Descrição:** contrato abstrato (D-E-02 da constituição); Value Objects `IdExterno`, `StatusEntrega`, `RetornoDriver`, `IdempotencyKey`; exceptions `FalhaTransitoriaException`, `FalhaDefinitivaException`.
- **Arquivos:** `app/Domain/Notificacao/Contracts/NotificacaoDriver.php`, `app/Domain/Notificacao/Valores/{IdExterno,StatusEntrega,RetornoDriver,IdempotencyKey}.php`, `app/Domain/Notificacao/Exceptions/{FalhaTransitoriaException,FalhaDefinitivaException}.php`.
- **Contrato afetado:** `NotificacaoDriver::enviar(Paciente, array $template_params, IdempotencyKey): RetornoDriver`.
- **Testes exigidos:** n/a (interface + VOs; testados indiretamente em contract).
- **Risco:** 🟡 — contrato que amarra 3 drivers; precisa estabilidade.

### T-020 — `NoopDriver` (rollback/staging)

- **Estado:** ⬜
- **Depende de:** T-019
- **Descrição:** implementação que não envia nada; retorna `IdExterno` fake; útil para staging e rollback via `WHATSAPP_DRIVER=noop`.
- **Arquivos:** `app/Infra/Notificacao/NoopDriver.php`.
- **Testes exigidos:** unit — retorna `RetornoDriver` com flag `enviado_real=false`.
- **Risco:** 🟢.

### T-021 — `MetaCloudDriver` (implementação real)

- **Estado:** ⬜
- **Depende de:** T-019
- **Descrição:** adaptador Meta Cloud API; auth Bearer; endpoint `POST /v19.0/{phone_id}/messages`; mapeia erros transitórios (5xx, 429) para `FalhaTransitoriaException` e definitivos (4xx numero inválido) para `FalhaDefinitivaException`.
- **Arquivos:** `app/Infra/Notificacao/MetaCloudDriver.php`, `config/services.php` (credenciais), `.env.example` (novas env vars META_*).
- **Testes exigidos:** unit com HTTP fake — sucesso → IdExterno; 5xx → FalhaTransitoria; 4xx numero inválido → FalhaDefinitiva; 4xx template rejeitado → exceção específica `TemplateRejeitadoException`.
- **Risco:** 🔴 — integração externa; depende de sandbox Meta.

### T-022 — `ZApiDriver` stub

- **Estado:** ⬜
- **Depende de:** T-019
- **Descrição:** implementação irmã para contingência (ADR-L-001 plano de reversão); contrato-compliant mas retorna `not_implemented` se chamado sem env `ZAPI_*`.
- **Arquivos:** `app/Infra/Notificacao/ZApiDriver.php`.
- **Testes exigidos:** unit — sem env `ZAPI_INSTANCE` → lança `DriverNaoConfiguradoException`; passa contract test compartilhado.
- **Risco:** 🟢.

### T-023 — `NotificacaoServiceProvider` (switch via env)

- **Estado:** ⬜
- **Depende de:** T-020, T-021, T-022
- **Descrição:** registra a interface `NotificacaoDriver` no container Laravel resolvendo para Meta/Zapi/Noop conforme `WHATSAPP_DRIVER`.
- **Arquivos:** `app/Providers/NotificacaoServiceProvider.php`, `config/app.php` (registro do provider).
- **Testes exigidos:** feature — `$this->app->make(NotificacaoDriver::class)` retorna a classe correta para cada valor de env.
- **Risco:** 🟢.

### T-024 — Migração + model `Notificacao`

- **Estado:** ⬜
- **Depende de:** T-012
- **Descrição:** tabela para tentativas concretas de entrega; unique `(driver, id_externo)` para idempotência NFR-006.
- **Arquivos:** `database/migrations/2026_04_21_000009_create_notificacoes_table.php`, `app/Models/Notificacao.php`.
- **Testes exigidos:** unit — insert ok; tentativa de inserir (driver, id_externo) duplicado lança unique violation (deve ser capturada em nível superior).
- **Risco:** 🟢.

### T-025 — `WhatsappWebhookController` (verify + receive) + rotas

- **Estado:** ⬜
- **Depende de:** T-021, T-024
- **Descrição:** 2 endpoints — `GET /webhooks/whatsapp` (verificação Meta com challenge) + `POST /webhooks/whatsapp` (recebe eventos; retorna 200 imediato; enfileira processamento).
- **Arquivos:** `app/Http/Controllers/WhatsappWebhookController.php`, `routes/webhooks.php`.
- **Contrato afetado:** duas rotas externas.
- **Testes exigidos:** feature — verify retorna challenge se token correto; receive enfileira job; payload malformado responde 200 mas loga warning (não bloqueia Meta).
- **Risco:** 🟡 — ponto de entrada de rede público.

### T-026 — `NotificacaoDriverContractTest` (teste compartilhado)

- **Estado:** ⬜
- **Depende de:** T-019, T-020, T-021, T-022
- **Descrição:** bateria comum de testes de contrato rodada contra **cada** driver (NoOp, Meta-mocked, ZApi-mocked). Garante que qualquer implementação futura satisfaz o contrato.
- **Arquivos:** `tests/Contract/NotificacaoDriverContractTest.php`, `tests/Contract/AbstractNotificacaoDriverContract.php`.
- **Testes exigidos:** os próprios — casos sucesso, transitório, definitivo, idempotência.
- **Risco:** 🟡.

---

## Fase F5 — Job disparo + janela + retry + rate-limit + idempotência

### T-027 — Service `RespeitaJanelaOperacional` (helper puro)

- **Estado:** ⬜
- **Depende de:** T-001 (Clinica config)
- **Descrição:** função pura `proximoMomentoEnviavel(Carbon $agora, int $ini_hora, int $fim_hora, Carbon $deadline): ?Carbon` — retorna next envio ou null se postergar ultrapassa `deadline`.
- **Arquivos:** `app/Domain/Confirmacao/Services/RespeitaJanelaOperacional.php`.
- **Testes exigidos:** unit — 10h BRT dentro de 08-20 → retorna agora; 03h BRT → retorna próximo 08h; deadline antes do próximo 08h → retorna null.
- **Risco:** 🟢 (lógica pura).

### T-028 — Service `AgendarLembrete`

- **Estado:** ⬜
- **Depende de:** T-007, T-015, T-027
- **Descrição:** calcula `datahora_envio = datahora_agendada - janela_lembrete_horas_usada`; ajusta via `RespeitaJanelaOperacional`; registra evento `lembrete_agendado`; agenda `DispararLembreteJob`.
- **Arquivos:** `app/Domain/Confirmacao/Services/AgendarLembrete.php`.
- **Contrato afetado:** `AgendarLembrete::executar(ConsultaId): void`.
- **Testes exigidos:** unit — caso normal agenda; caso fora de janela posterga; caso posterga ultrapassa horário → não agenda, emite evento `sem-canal` ou equivalente; teste com `Queue::fake()` confirma enfileiramento.
- **Risco:** 🟡.

### T-029 — Listener `AgendarLembreteAoCriarConsulta`

- **Estado:** ⬜
- **Depende de:** T-028
- **Descrição:** listener de `EventoConsulta::created` com `tipo=criada` → chama `AgendarLembrete`.
- **Arquivos:** `app/Listeners/AgendarLembreteAoCriarConsulta.php`, registro em `EventServiceProvider`.
- **Testes exigidos:** feature — criar consulta emite evento `criada` → lembrete agendado.
- **Risco:** 🟢.

### T-030 — Guard `IdempotenciaLembrete` (Redis SETNX)

- **Estado:** ⬜
- **Depende de:** T-001 (Redis)
- **Descrição:** lock SETNX `lembrete:{consulta_id}:{janela_id}` com TTL 1h para garantir não-duplicação de envio (FR-009).
- **Arquivos:** `app/Domain/Confirmacao/Guards/IdempotenciaLembreteGuard.php`.
- **Testes exigidos:** unit — 1ª chamada adquire; 2ª retorna false; TTL expira após 1h (simular com Redis fake).
- **Risco:** 🟡.

### T-031 — Guard `RateLimitClinicaGuard` (token bucket)

- **Estado:** ⬜
- **Depende de:** T-001
- **Descrição:** Token bucket em Redis com 50 tokens/min por `clinica_id` (DT-08); resolve residual da constituição.
- **Arquivos:** `app/Domain/Confirmacao/Guards/RateLimitClinicaGuard.php`.
- **Testes exigidos:** unit — 50 calls dentro de 1 min aprovadas; 51ª bloqueada; após 1 min resetado.
- **Risco:** 🟡.

### T-032 — Job `DispararLembreteJob`

- **Estado:** ⬜
- **Depende de:** T-021, T-024, T-028, T-030, T-031
- **Descrição:** implementa `ShouldQueue`; dentro do `handle(NotificacaoDriver $d)`: aplica guards (idempotência + rate-limit + janela ativa) → `$d->enviar(...)` → persiste `Notificacao` + evento `lembrete_enviado` ou `lembrete_falha_envio` ou `lembrete_numero_invalido`. Retry 3x em `FalhaTransitoria` (Laravel `$tries=3` + `retryUntil`).
- **Arquivos:** `app/Domain/Confirmacao/Jobs/DispararLembreteJob.php`.
- **Contrato afetado:** job enfileirável.
- **Testes exigidos:** feature — pipeline completo com driver fake → evento `lembrete_enviado` registrado + `Notificacao` linha criada; falha transitória retenta; após 3 falhas → `lembrete_falha_envio`; `numero-invalido` → sem retry, evento específico.
- **Risco:** 🔴 — integra 4 guards + driver + eventos.

### T-033 — Teste end-to-end F5 (criar→agenda→dispara→evento)

- **Estado:** ⬜
- **Depende de:** T-018, T-029, T-032
- **Descrição:** teste integração que cobre o fluxo completo sem sair da aplicação (driver fake).
- **Arquivos:** `tests/Integration/PipelineDispararLembreteTest.php`.
- **Testes exigidos:** os próprios.
- **Risco:** 🟡.

---

## Fase F6 — Processamento de resposta (webhook in + reconciliação)

### T-034 — Service `ReconciliarResposta`

- **Estado:** ⬜
- **Depende de:** T-024
- **Descrição:** recebe payload do webhook; extrai `provider_message_id` ou `context.id`; encontra `Notificacao` correspondente; retorna `ResultadoReconciliacao` com `ConsultaId` + `TipoResposta` (botão ou texto livre).
- **Arquivos:** `app/Domain/Confirmacao/Services/ReconciliarResposta.php`, `app/Domain/Confirmacao/Valores/ResultadoReconciliacao.php`.
- **Testes exigidos:** unit — botão Confirmar bate → `resposta_recebida_confirmar`; botão Cancelar → `resposta_recebida_cancelar`; botão Reagendar → `resposta_recebida_reagendar`; texto livre → `resposta_ambigua`; id órfão → retorna null (payload descartado).
- **Risco:** 🟡 — formato Meta pode mudar.

### T-035 — Service `AplicarResposta`

- **Estado:** ⬜
- **Depende de:** T-015, T-034
- **Descrição:** recebe `ResultadoReconciliacao`; registra evento correspondente via `RegistrarEvento`. Regra "última resposta vale" (FR-016) é natural via reducer em `DerivarStatus`.
- **Arquivos:** `app/Domain/Confirmacao/Services/AplicarResposta.php`.
- **Testes exigidos:** unit — cada tipo de resposta gera evento correto; duas respostas em sequência → ambas registradas, última vale em `status_cache`.
- **Risco:** 🟢.

### T-036 — Guard `CallbackIdempotenciaGuard`

- **Estado:** ⬜
- **Depende de:** T-024
- **Descrição:** dedup de callbacks Meta via `provider_message_id` (NFR-006); implementação — tentativa de INSERT em tabela dedicada de `callbacks_recebidos` com unique `(provider, message_id)`; se viola, descarta.
- **Arquivos:** `app/Domain/Confirmacao/Guards/CallbackIdempotenciaGuard.php`, `database/migrations/2026_04_21_000010_create_callbacks_recebidos_table.php`.
- **Testes exigidos:** unit — 1ª recepção ok; duplicata descartada sem evento duplicado.
- **Risco:** 🟡.

### T-037 — Job `ProcessarCallbackWhatsappJob`

- **Estado:** ⬜
- **Depende de:** T-025, T-034, T-035, T-036
- **Descrição:** enfileirado pelo webhook controller; aplica guards; delega a `ReconciliarResposta` + `AplicarResposta`.
- **Arquivos:** `app/Domain/Confirmacao/Jobs/ProcessarCallbackWhatsappJob.php`.
- **Testes exigidos:** feature — callback completo → status muda em <10s mediana (NFR-001 simulado).
- **Risco:** 🟡.

---

## Fase F7 — Painel + intervenção manual + histórico

### T-038 — Livewire `DashboardDia`

- **Estado:** ⬜
- **Depende de:** T-017, T-037
- **Descrição:** lista consultas de hoje e amanhã ordenadas; destaque visual para `sem-resposta` (classe CSS + ícone); filtros por status; polling `wire:poll.30s`.
- **Arquivos:** `app/Http/Livewire/Confirmacao/DashboardDia.php` + view.
- **Testes exigidos:** feature — renderiza lista correta; filtro `status=confirmada` reduz; destaque presente em `sem-resposta`.
- **Risco:** 🟡 — primeira tela com volume de dados.

### T-039 — Livewire `ConsultaHistorico`

- **Estado:** ⬜
- **Depende de:** T-038
- **Descrição:** linha do tempo de eventos da consulta; mostra ícone ⚠ em eventos corrigidos.
- **Arquivos:** `app/Http/Livewire/Confirmacao/ConsultaHistorico.php` + view.
- **Testes exigidos:** feature — histórico renderiza eventos cronologicamente; eventos `correcao` aparecem com referência ao original.
- **Risco:** 🟢.

### T-040 — Services `ConfirmarEmNomeDoPaciente` / `CancelarEmNomeDoPaciente` / `RegistrarReagendamentoManual`

- **Estado:** ⬜
- **Depende de:** T-015
- **Descrição:** 3 services de intervenção manual; cada um registra evento com `ator_tipo=atendente`, `canal=manual-pelo-painel`.
- **Arquivos:** `app/Domain/Confirmacao/Services/{ConfirmarEmNomeDoPaciente,CancelarEmNomeDoPaciente,RegistrarReagendamentoManual}.php`.
- **Testes exigidos:** unit — cada um gera evento esperado com campos corretos; reagendar cria nova consulta + evento `reagendamento-efetivado` na original.
- **Risco:** 🟢.

### T-041 — Livewire `IntervencaoManualModal`

- **Estado:** ⬜
- **Depende de:** T-038, T-040
- **Descrição:** modal acionado do `DashboardDia` com 3 botões (Confirmar / Cancelar / Reagendar manualmente).
- **Arquivos:** `app/Http/Livewire/Confirmacao/IntervencaoManualModal.php` + view.
- **Testes exigidos:** feature — atendente aciona modal em consulta `sem-resposta` → confirma manualmente → status muda + evento registrado.
- **Risco:** 🟡.

### T-042 — Service `NotificarPacienteCancelamentoTardio` (FR-025)

- **Estado:** ⬜
- **Depende de:** T-021, T-009 (CancelarConsultaPelaClinica)
- **Descrição:** quando atendente cancela consulta **após** lembrete já enviado, dispara mensagem de cancelamento via driver (usando template diferente `cancelamento_consulta`).
- **Arquivos:** `app/Domain/Confirmacao/Services/NotificarPacienteCancelamentoTardio.php`, retrofit em `CancelarConsultaPelaClinica`.
- **Testes exigidos:** feature — cancelar com `lembrete_enviado` prévio → dispara notificação cancelamento; cancelar antes do lembrete → não dispara.
- **Risco:** 🟡 — requer template adicional aprovado pela Meta (registrar dívida).

---

## Fase F8 — Config + presença + correção

### T-043 — Middleware `ExigeIsAdmin` + Policies Laravel *(ampliada pós-Analyze — P-05)*

- **Estado:** ⬜
- **Depende de:** T-002
- **Descrição:** middleware que rejeita com 403 se `user->is_admin = false` **+ criação de Policies Laravel** para escopo fino: `ConsultaPolicy`, `PacientePolicy`, `MedicoPolicy`. Policies aplicam (a) isolamento por `clinica_id` (D-003) e (b) escopo "médico vê só própria agenda" (C-002).
- **Arquivos:** `app/Http/Middleware/ExigeIsAdmin.php`, `app/Policies/{ConsultaPolicy,PacientePolicy,MedicoPolicy}.php`, registro em `bootstrap/app.php` + `AuthServiceProvider`.
- **Testes exigidos:** feature — atendente sem admin em rota de config → 403; com admin → passa. Policy: médico A tenta ver consulta do médico B (mesma clínica) → 403; médico A vê própria consulta → 200; atendente tenta ver dados de outra clínica (simulado) → 403.
- **Risco:** 🟡 (aumentado de 🟢 após ajuste — Policies é camada adicional de autorização que precisa teste rigoroso).

### T-044 — Livewire `ClinicaConfigForm`

- **Estado:** ⬜
- **Depende de:** T-043
- **Descrição:** edita janelas + retries da Clinica; validação defensiva (`lembrete≥2h`, `lembrete>silencio`, etc.).
- **Arquivos:** `app/Http/Livewire/Config/ClinicaConfigForm.php` + view.
- **Testes exigidos:** feature — admin altera janela de 24h→48h → próxima consulta criada agenda para 48h; consultas com job já agendado mantêm (FR-029 via T-007 snapshot).
- **Risco:** 🟡.

### T-045 — Services `MarcarCompareceu` / `MarcarNoShow`

- **Estado:** ⬜
- **Depende de:** T-015
- **Descrição:** services com guard temporal (bloqueia se `datahora_agendada` > agora, FR-027).
- **Arquivos:** `app/Domain/Confirmacao/Services/{MarcarCompareceu,MarcarNoShow}.php`, `app/Domain/Confirmacao/Exceptions/MarcacaoAntesDoHorarioException.php`.
- **Testes exigidos:** unit — marcar após horário ok; antes → 422.
- **Risco:** 🟢.

### T-046 — Service `CorrigirMarcacao` (C-005)

- **Estado:** ⬜
- **Depende de:** T-045
- **Descrição:** service que registra evento `correcao` com `ref_evento_id` + `motivo` obrigatório; rejeita sem motivo.
- **Arquivos:** `app/Domain/Confirmacao/Services/CorrigirMarcacao.php`.
- **Testes exigidos:** unit — sem motivo → rejeita; com motivo → evento `correcao` registrado; derivação reflete correção.
- **Risco:** 🟡.

### T-047 — Botões de marcação no painel + modal de correção

- **Estado:** ⬜
- **Depende de:** T-038, T-045, T-046
- **Descrição:** UI dos botões "Marcar compareceu" / "Marcar no-show" / "Corrigir marcação (com motivo)".
- **Arquivos:** incrementa views de `DashboardDia` e `ConsultaHistorico`.
- **Testes exigidos:** feature — fluxos de marcação e correção via UI.
- **Risco:** 🟢.

---

## Fase F9 — Anonimização LGPD + retenção temporal

### T-048 — Service `AnonimizarPaciente`

- **Estado:** ⬜
- **Depende de:** T-003, T-015
- **Descrição:** transacional; lock pessimista (`SELECT FOR UPDATE` no Paciente); sobrescreve PII; registra evento `anonimizacao`. Exige `is_admin`.
- **Arquivos:** `app/Domain/Lgpd/Services/AnonimizarPaciente.php`, `app/Domain/Lgpd/Exceptions/AnonimizacaoProibidaException.php`.
- **Contrato afetado:** `AnonimizarPaciente::executar(PacienteId, UserId $admin): void`.
- **Testes exigidos:** feature — admin anonimiza → PII sobrescrita + evento registrado + consultas do paciente continuam acessíveis; sem admin → 403.
- **Risco:** 🔴 — operação sem rollback.

### T-049 — Guard `GuardIntegridadeReferencial` (teste)

- **Estado:** ⬜
- **Depende de:** T-048
- **Descrição:** não é código em `app/`, é **teste de propriedade**; valida que após qualquer anonimização, todas as FKs entre `pacientes` e `consultas` / `eventos_consulta` continuam íntegras.
- **Arquivos:** `tests/Feature/Lgpd/GuardIntegridadeReferencialTest.php`.
- **Testes exigidos:** propriedade — forçar cenários de anonimização e verificar FKs.
- **Risco:** 🟡.

### T-050 — Livewire `AnonimizarPacienteModal` (confirmação forte)

- **Estado:** ⬜
- **Depende de:** T-048
- **Descrição:** modal com confirmação dupla — atendente-admin deve **digitar o nome do paciente** antes de confirmar (prevenção contra clique acidental).
- **Arquivos:** `app/Http/Livewire/Lgpd/AnonimizarPacienteModal.php` + view.
- **Testes exigidos:** feature — confirmação sem digitar nome correto → bloqueado; digitar nome correto + submit → anonimização executada.
- **Risco:** 🟡.

### T-051 — Job `AnonimizacaoTemporalJob` (5 anos)

- **Estado:** ⬜
- **Depende de:** T-012
- **Descrição:** scheduler diário; busca eventos com `criado_em < now() - 5 years`; nulifica `ator_id` e `ip`; registra meta-evento (ou log, a decidir) sobre a anonimização temporal.
- **Arquivos:** `app/Domain/Lgpd/Jobs/AnonimizacaoTemporalJob.php`, registrar em `app/Console/Kernel.php`.
- **Testes exigidos:** feature — seeder com evento > 5a → job processa → `ator_id` e `ip` viram null; evento < 5a permanece.
- **Risco:** 🟡.

### T-052 — Proteção race: job disparo checa `anonimizado_em` antes de enviar

- **Estado:** ⬜
- **Depende de:** T-032, T-048
- **Descrição:** retrofitar `DispararLembreteJob` para abortar se `paciente->anonimizado_em != null` no início do handler (R-04 mitigação).
- **Arquivos:** edição em `app/Domain/Confirmacao/Jobs/DispararLembreteJob.php`.
- **Testes exigidos:** feature — anonimizar paciente com lembrete enfileirado → job detecta e aborta; nenhum envio ocorre.
- **Risco:** 🔴.

---

## Fase F10 — Observabilidade + CI + rate-limit + template PR

### T-053 — Logging estruturado JSON + `MascarararPiiProcessor`

- **Estado:** ⬜
- **Depende de:** nenhuma (pode rodar em paralelo com F1..F9)
- **Descrição:** driver JSON em `config/logging.php`; processor que remove telefone/email dos logs (NFR-003).
- **Arquivos:** `config/logging.php`, `app/Logging/MascarararPiiProcessor.php`.
- **Testes exigidos:** unit — log com `telefone_whatsapp=+5511...` → output não contém o número.
- **Risco:** 🟢.

### T-054 — Métricas (contadores + histogramas)

- **Estado:** ⬜
- **Depende de:** T-015
- **Descrição:** instrumentação nos pontos críticos (T-032, T-035, T-048); 8 métricas de plan §8.
- **Arquivos:** `app/Metrics/ConfirmacaoMetrics.php`, `routes/web.php` (rota `/metrics` protegida por `is_admin`).
- **Testes exigidos:** feature — após disparo, contador `confirmacao_lembretes_enviados_total{clinica_id=1}` incrementou.
- **Risco:** 🟡 — DT-10 pendente de ADR local.

### T-055 — Tracing OTEL nos pontos críticos

- **Estado:** ⬜
- **Depende de:** T-032, T-037, T-048
- **Descrição:** spans em `NotificacaoDriver::enviar`, webhook controller, `ReconciliarResposta`, `AnonimizarPaciente`.
- **Arquivos:** configuração OTEL + `@Span` annotations (ou manual) nos services.
- **Testes exigidos:** feature — traces capturados em collector em dev.
- **Risco:** 🟡.

### T-056 — CI GitHub Actions

- **Estado:** ⬜
- **Depende de:** nenhuma
- **Descrição:** workflow com jobs Pint check, PHPStan nível 5, Pest com coverage >= 70% (ou threshold inicial 60%), composer audit.
- **Arquivos:** `.github/workflows/ci.yml`, `phpstan.neon`, `pint.json`.
- **Testes exigidos:** CI passa em PR dummy; quebra em erro intencional.
- **Risco:** 🟡.

### T-057 — Template de PR

- **Estado:** ⬜
- **Depende de:** nenhuma
- **Descrição:** template com seções: FRs implementados · D-NNN/C-NNN tocados · testes adicionados · checklist §5.4.
- **Arquivos:** `.github/PULL_REQUEST_TEMPLATE.md`.
- **Testes exigidos:** n/a.
- **Risco:** 🟢.

### T-058 — Teste de integração `RateLimitFluxoRealTest`

- **Estado:** ⬜
- **Depende de:** T-031, T-032
- **Descrição:** valida DT-08 (50 msg/min/clínica) em cenário realista com Redis + job real enfileirado.
- **Arquivos:** `tests/Integration/RateLimitFluxoRealTest.php`.
- **Testes exigidos:** propriedade — enfileirar 60 envios em ≤1min → 50 enviados, 10 reagendados.
- **Risco:** 🟡.

### T-059 — Benchmark append-only trigger (R-02)

- **Estado:** ⬜
- **Depende de:** T-013
- **Descrição:** benchmark de INSERT em massa (10k eventos/s simulados) para confirmar que trigger PG não degrada > 20%.
- **Arquivos:** `tests/Benchmark/AppendOnlyPerformanceBench.php` (standalone; roda manual, não no CI padrão).
- **Testes exigidos:** benchmark com threshold; se degradar > 20%, alerta em log + dívida aberta.
- **Risco:** 🟡.

---

## Ajustes pós-Analyze (Fase 6) — tasks adicionadas em 2026-04-20

### T-060 — Scheduler `DetectarSemRespostaJob` *(F7; nova pós-Analyze — P-01)*

- **Estado:** ⬜
- **Depende de:** T-015, T-017 (evento + listener de status_cache)
- **Descrição:** scheduler Laravel a cada 15 min; busca `consultas` com `status_cache = 'lembrete-enviado'` e `(datahora_agendada - interval '1 hour' * janela_silencio_horas_usada) < NOW()`; para cada, registra evento `status_sem_resposta` com `ator_tipo = sistema-automacao`. Listener T-017 atualiza `status_cache` → painel T-038 destaca imediatamente. Materializa D-E-05.
- **Arquivos:** `app/Domain/Confirmacao/Jobs/DetectarSemRespostaJob.php`, registro em `app/Console/Kernel.php` (`->everyFifteenMinutes()`).
- **Contrato afetado:** job agendado.
- **Testes exigidos:** feature — setup com consulta `lembrete-enviado` cujo horário da consulta - silêncio já passou → rodar job manualmente → status_cache vira `sem-resposta` + evento `status_sem_resposta` registrado; consulta ainda dentro da janela de silêncio → job não altera; consulta que já respondeu → job não altera.
- **Risco:** 🟡.

### T-061 — Link seguro assinado para paciente *(F7; nova pós-Analyze — P-02)*

- **Estado:** ⬜
- **Depende de:** T-021 (driver Meta; precisa incluir URL no payload do template)
- **Descrição:** rota Laravel assinada temporalmente (`URL::temporarySignedRoute`) com TTL = janela_lembrete + buffer; Livewire somente-leitura `ConsultaPublica` mostra dados da consulta (clínica, médico, data/hora, endereço) sem exigir login. Materializa FR-034.
- **Arquivos:** `app/Http/Livewire/Confirmacao/ConsultaPublica.php` + view, `routes/web.php` (rota `/consulta/publico/{consulta}` com middleware `signed`), ajuste em `DispararLembreteJob` (T-032) para gerar URL e incluir como parâmetro do template.
- **Contrato afetado:** rota pública assinada.
- **Testes exigidos:** feature — link assinado válido → renderiza detalhes; link expirado → 403; link adulterado → 403; dados de consulta de outra clínica NÃO acessíveis mesmo com token (defense against token leak).
- **Risco:** 🟡.

### T-062 — Command `consultas:reconciliar-status-cache` *(F10; nova pós-Analyze — P-04)*

- **Estado:** ⬜
- **Depende de:** T-016, T-017
- **Descrição:** comando Artisan que itera todas as consultas da clínica (pode aceitar `--clinica=N` opcional); recalcula `DerivarStatus` puro; compara com `status_cache` persistido; atualiza se divergente + loga divergência. Scheduler semanal (domingos 03h — fora do horário operacional) + disponível para invocação manual.
- **Arquivos:** `app/Console/Commands/ReconciliarStatusCache.php`, registro em `app/Console/Kernel.php` (`->weekly()`).
- **Contrato afetado:** comando Artisan.
- **Testes exigidos:** feature — sabotar `status_cache` artificialmente (setar para valor errado) → rodar comando → cache reconciliado; dry-run opcional via `--dry-run` só imprime diferenças; log estruturado inclui `consulta_id`, `status_antigo`, `status_novo`.
- **Risco:** 🟢.

### T-063 — Testes de Policy dedicados *(F8; nova pós-Analyze — P-05 complemento)*

- **Estado:** ⬜
- **Depende de:** T-043 (Policies criadas)
- **Descrição:** suite de testes específica que exercita cada Policy criada em T-043 contra cenários adversariais (médico A tentando operar como médico B; atendente tentando acessar consulta de outra clínica simulada; paciente — sem login — NUNCA deve passar qualquer Policy).
- **Arquivos:** `tests/Feature/Policies/{ConsultaPolicyTest,PacientePolicyTest,MedicoPolicyTest}.php`.
- **Testes exigidos:** os próprios — matriz de autorização 4×N ações cobrindo todos os cenários de C-002.
- **Risco:** 🟡.

---

## Matriz de rastreabilidade (FR ↔ Task)

| FR | Task(s) | Observação |
|---|---|---|
| FR-001 (cadastrar paciente) | T-005, T-006 | Service + Livewire |
| FR-002 (cadastrar médico) | T-005, T-006 | Idem |
| FR-003 (criar consulta) | T-008, T-010 | |
| FR-004 (editar/cancelar consulta) | T-009, T-010 | |
| FR-005 (rejeitar dados inválidos) | T-005, T-008 | Exceptions tipadas |
| FR-006 (enfileirar lembrete ao criar) | T-028, T-029 | |
| FR-007 (WhatsApp único canal) | T-021, T-023 | Driver Meta default |
| FR-008 (lembrete com 3 botões) | T-021, T-026 | Template utility |
| FR-009 (idempotência de disparo) | T-030, T-032 | |
| FR-010 (janela operacional 08-20) | T-027, T-028 | |
| FR-011 (retry 3x backoff 5/15/45) | T-021, T-032 | |
| FR-012 (numero inválido — sem retry) | T-021, T-032 | Exception específica |
| FR-013 (reconciliar resposta por id_externo) | T-034 | |
| FR-014 (atualizar status por botão) | T-035, T-017 | |
| FR-015 (texto livre → ambígua) | T-034, T-035 | |
| FR-016 (última resposta vale) | T-035, T-016 | Reducer |
| FR-017 (histórico imutável + correção) | T-012, T-013, T-015, T-046 | Defense-in-depth |
| FR-018 (campos auditoria) | T-011, T-012, T-015 | |
| FR-019 (consultar histórico) | T-039 | |
| FR-020 (painel do dia) | T-038 | |
| FR-021 (destaque sem-resposta) | T-038, **T-060** | Scheduler sem-resposta (P-01 pós-Analyze) |
| FR-022 (filtros por status) | T-038 | |
| FR-023 (confirmar em nome) | T-040, T-041 | |
| FR-024 (cancelar/reagendar manual) | T-040, T-041 | |
| FR-025 (notificar cancelamento tardio) | T-042 | Template adicional Meta |
| FR-026 (marcar compareceu/no-show) | T-045, T-047 | |
| FR-027 (bloquear marcação antes do horário) | T-045 | |
| FR-028 (config janelas pelo admin) | T-044 | |
| FR-029 (config só afeta consultas futuras) | T-007, T-044 | Snapshot |
| FR-030 (auth obrigatória + paciente não loga) | T-002 | Breeze |
| FR-031 (matriz RBAC C-002) | T-043, T-044 | |
| FR-032 (isolamento por clínica) | T-001, todas | `clinica_id` em todas as tabelas |
| FR-033 (anonimização LGPD) | T-048, T-050 | Transacional + confirmação forte |
| FR-034 (não expor dados não autenticados) | T-002, T-025, **T-061** | Middleware + link seguro assinado (P-02 pós-Analyze) |

**NFRs:**
| NFR | Task(s) |
|---|---|
| NFR-001 (<10s reconciliação) | T-037 |
| NFR-002 (99% disponibilidade envio) | T-032, T-054 |
| NFR-003 (privacidade PII) | T-003, T-053 |
| NFR-004 (retenção 5a) | T-051 |
| NFR-005 (responsivo web) | T-038 |
| NFR-006 (idempotência callback) | T-036 |
| NFR-007 (custo ≤ R$ 0,20) | T-054 (métrica de custo) + operacional |

## Matriz de rastreabilidade (Edge Case ↔ Task/Teste)

| Edge case da spec | Task coberta | Cobertura de teste |
|---|---|---|
| Provedor WhatsApp indisponível | T-021, T-032 | Retry transitório em `MetaCloudDriver` tests + `DispararLembreteJob` tests |
| Número WhatsApp inválido | T-021, T-032 | `FalhaDefinitivaException` unit test + evento `lembrete_numero_invalido` feature |
| Template reprovado | T-021, T-042 | `TemplateRejeitadoException` unit test + alerta |
| Resposta texto livre | T-034, T-035 | Unit reconciliar + feature aplicar → `resposta_ambigua` |
| Múltiplas respostas | T-035 | Unit "duas respostas" → última vale |
| Idempotência disparo | T-030, T-032 | Unit guard + feature "job chamado 2x" |
| Cancelamento após lembrete | T-042 | Feature dispara notificação cancelamento |
| Alteração config no meio do dia | T-007, T-044 | Feature — snapshot preserva |
| Paciente sem WhatsApp | T-032, T-040 | Feature — fallback humano no painel |
| Deleção LGPD | T-048, T-049, T-050, T-052 | Feature + guard integridade + race anonimização |
| Janela inadequada (03h) | T-027, T-028 | Unit `RespeitaJanelaOperacional` postpone |
| Atendente corrige marcação | T-046, T-047 | Unit + feature com motivo obrigatório |
| Fuso único BR | T-007 | Documentado; teste de timezone UTC |
| Callback duplicado Meta | T-036 | Unit idempotência + feature dedup |

---

## Sumário numérico (v2 pós-Analyze)

- **Total de tasks:** **63** (T-001 a T-063; 59 originais + 4 adicionadas pós-Analyze P-01/P-02/P-04/P-05; 2 editadas em escopo: T-003 e T-043).
- **Distribuição por fase:** F1=6 · F2=4 · F3=8 · F4=8 · F5=7 · F6=4 · F7=**7** (+ T-060, T-061) · F8=**6** (+ T-063) · F9=5 · F10=**8** (+ T-062).
- **Distribuição por risco:** 🟢 20 · 🟡 35 · 🔴 8 (T-003 subiu para 🟡 após ampliação; T-043 idem).
- **Estimativa grossa**: ordem de grandeza de **45–65h** de trabalho focado solo-dev (aumento marginal sobre v1 por causa das 4 tasks novas).

---

## Gate pós-Tasks (`fases/05_TASKS.md` + `checklists/qualidade-plano.md` §tasks)

- [x] Cada task tem título acionável (59/59).
- [x] Cada task tem DoD (via DoD padrão + especificidades).
- [x] Dependências explícitas em todas as tasks (T-001 é a única sem dependência).
- [x] Agrupamento por fase respeita `plan.md §3` (F1..F10).
- [x] Cobertura integral do plano: **34/34 FRs** mapeados para tasks; **7/7 NFRs** mapeados; **13/14 edge cases** cobertos (fuso documentado sem task específica — aceito conforme `[RISCO ASSUMIDO]`).
- [x] Testes distribuídos ao longo das tasks (não concentrados no final); cada task inclui "Testes exigidos".
- [x] Nenhuma task depende de arquivo não mencionado no `plan.md §3`.
- [ ] Validação humana — pendente, via merge do PR `w1b/f5-tasks`.

**Veredicto do Arquiteto:** 🟢 draft fechado. Próxima fase: **Fase 6 Analyze** — cruzamento cruzado (spec × plan × tasks × constituição) para detectar contradições ou lacunas antes de começar a escrever código.
