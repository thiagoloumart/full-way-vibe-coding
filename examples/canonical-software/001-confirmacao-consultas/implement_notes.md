---
artefato: implement_notes
fase: 7
dominio: [software]
schema_version: 1
requer:
  - "1. Contrato do PR"
  - "2. Arquivos completos"
  - "3. Arquivos stub"
  - "4. Cobertura do plan"
---

# Notas de implementação — `001-confirmacao-consultas` (Fase 7)

**Data:** 2026-04-20
**Autor:** Thiago Loumart (modo Arquiteto)
**Escopo:** estratégia **C2** acordada pós-Fase 6 (ver conversa da skill): arquivos arquiteturalmente críticos implementados **completos e idiomáticos**; arquivos CRUD/UI triviais deixados como **stub** bem tipados com referência à task.

---

## 1. Contrato do PR

Este PR materializa a Fase 7 Implement do canônico D1 como **exemplo documental**. Os arquivos em `codigo/` são:

- **Realistas** — compilariam em Laravel 12 + PHP 8.3 se copiados para projeto greenfield.
- **Exemplares** — seguem decisões documentadas em `constitution.md v1.1`, `plan.md`, `tasks.md v2`.
- **Não executados** — este repositório é a **skill**, não a clínica. Nenhum `php artisan` rodou aqui.

Validação é por **leitura + lint estrutural de artefatos .md + coerência com documentos**. Lint Laravel (Pint, PHPStan, Pest) só rodaria em projeto real (não neste).

---

## 2. Arquivos completos (~20)

Implementados integralmente porque **materializam decisões arquiteturais críticas**. Cada um com docblock explícito sobre origem (FR-NNN, D-NNN, C-NNN, D-E-NN).

### 2.1 F3 — Histórico imutável (pilar D-E-03)

| Arquivo | Origem | Por que é completo |
|---|---|---|
| `app/Domain/Confirmacao/Eventos/TipoEvento.php` | C-005 · FR-017 | Enum fechado; inclui `sem_canal` adicionado pós-Analyze (P-03) |
| `app/Domain/Confirmacao/Eventos/AtorTipo.php` | C-006 · FR-018 | Enum de ator |
| `app/Domain/Confirmacao/Eventos/Canal.php` | C-006 · FR-018 | Enum de canal abstrato |
| `app/Domain/Confirmacao/Exceptions/HistoricoImutavelException.php` | D-E-03 | Tradução de violações do invariante |
| `app/Models/EventoConsulta.php` | FR-017 · DT-03 | **Append-only** no Eloquent (override `update`/`delete`) |
| `app/Domain/Confirmacao/Services/RegistrarEvento.php` | FR-017 · FR-018 | **Ponto único** de escrita no histórico |
| `app/Domain/Confirmacao/Services/DerivarStatus.php` | FR-017 · C-005 | **Reducer** puro com correções (C-005) |
| `app/Listeners/AtualizarStatusCacheDaConsulta.php` | DT-02 | Projeção idempotente |
| `database/migrations/..._create_eventos_consulta_table.php` | FR-017 · FR-018 | Schema com ULID + payload_extra jsonb |
| `database/migrations/..._add_append_only_trigger_eventos.php` | DT-03 | **Trigger PG** defense-in-depth |

### 2.2 F4 — Driver abstrato (pilar D-E-02)

| Arquivo | Origem | Por que é completo |
|---|---|---|
| `app/Domain/Notificacao/Contracts/NotificacaoDriver.php` | D-E-02 · FR-007 | **Interface** — contrato abstrato |
| `app/Domain/Notificacao/Valores/IdempotencyKey.php` | FR-009 · NFR-006 | VO construtor factory |
| `app/Domain/Notificacao/Valores/RetornoDriver.php` | FR-013 | VO imutável |
| `app/Domain/Notificacao/Exceptions/FalhaTransitoriaException.php` | FR-011 · C-004 | Exceção tipada com HTTP context |
| `app/Domain/Notificacao/Exceptions/FalhaDefinitivaException.php` | FR-012 · C-003 · P-03 | Exceção com categorias (numero-invalido, paciente-sem-canal, template-rejeitado, auth-falhou) |
| `app/Infra/Notificacao/MetaCloudDriver.php` | ADR-L-001 | **Adaptador Meta completo** com retry lógico + tradução 10+ códigos de erro |
| `app/Infra/Notificacao/NoopDriver.php` | ADR-L-001 plano reversão | Driver fake para staging |
| `app/Infra/Notificacao/ZApiDriver.php` | ADR-L-001 irmão | Stub contract-compliant (detalhado abaixo) |
| `app/Providers/NotificacaoServiceProvider.php` | D-E-02 | Switch `WHATSAPP_DRIVER` |

### 2.3 F5 — Job disparo + guards

| Arquivo | Origem | Por que é completo |
|---|---|---|
| `app/Domain/Confirmacao/Services/RespeitaJanelaOperacional.php` | FR-010 · C-004 · D-E-06 | **Helper puro** — testável sem Laravel |
| `app/Domain/Confirmacao/Guards/IdempotenciaLembreteGuard.php` | FR-009 | Lock Redis SETNX |
| `app/Domain/Confirmacao/Guards/RateLimitClinicaGuard.php` | DT-08 | Token bucket Redis (resolve residual constituição) |
| `app/Domain/Confirmacao/Jobs/DispararLembreteJob.php` | FR-006..FR-012 | **Pipeline completo** — 4 guards + driver + eventos |
| `app/Http/Controllers/WhatsappWebhookController.php` | FR-013 | Verify + receive com 200-imediato |

### 2.4 F7/F8 — Scheduler sem-resposta + correção

| Arquivo | Origem | Por que é completo |
|---|---|---|
| `app/Domain/Confirmacao/Jobs/DetectarSemRespostaJob.php` | D-E-05 · T-060 (pós-Analyze) | Scheduler que materializa invariante |
| `app/Domain/Confirmacao/Services/CorrigirMarcacao.php` | C-005 · FR-017 | Correção via evento novo com ref |

### 2.5 F9 — Anonimização LGPD (operação sem rollback)

| Arquivo | Origem | Por que é completo |
|---|---|---|
| `app/Domain/Lgpd/Services/AnonimizarPaciente.php` | FR-033 · C-003 · R-04 | **Transacional + lock pessimista + guards** |
| `app/Domain/Lgpd/Exceptions/AnonimizacaoProibidaException.php` | C-003 · D-E-01 | Exceção com factory methods |

### 2.6 Models do domínio + infra

| Arquivo | Origem | Por que é completo |
|---|---|---|
| `app/Models/Paciente.php` | FR-001 · FR-033 · P-03 | PII + anonimizável + `telefone_whatsapp` nullable |
| `app/Models/Clinica.php` | FR-028 · D-003 | Single-tenant + config C-004 |
| `app/Models/Consulta.php` | FR-003..FR-029 | Inclui `linkPublicoAssinado()` (T-061) |
| `app/Logging/MascarararPiiProcessor.php` | NFR-003 | Monolog processor que remove PII dos logs |
| `.github/workflows/ci.yml` | T-056 | CI completo com services PG+Redis |
| `composer.json`, `.env.example`, `phpstan.neon`, `pint.json` | D-001 · C-004 | Stack Camada 2 materializada |

---

## 3. Arquivos stub (~35)

**Não implementados** — apenas skeleton com namespace, classe, assinatura e `// TODO (T-NNN)` apontando para tasks.md. São CRUD triviais ou UI bem documentadas nas tasks que não acrescentariam didática além do que já foi decidido:

- `app/Models/{User,Medico,Notificacao}.php` — Eloquent triviais.
- `app/Domain/Cadastro/Services/{CadastrarPaciente,CadastrarMedico}.php` — orquestração simples.
- `app/Domain/Agendamento/Services/{CriarConsulta,EditarConsulta,CancelarConsultaPelaClinica}.php` — regras de negócio simples.
- `app/Domain/Confirmacao/Services/{AgendarLembrete,ReconciliarResposta,AplicarResposta,ConfirmarEmNomeDoPaciente,...}.php` — orquestração.
- `app/Http/Livewire/**/*.php` — componentes UI (padrão Livewire 3).
- `app/Http/Middleware/ExigeIsAdmin.php` — middleware trivial.
- `app/Policies/{ConsultaPolicy,PacientePolicy,MedicoPolicy}.php` — escopo por clinica_id + user.
- Migrations para `users`, `medicos`, `notificacoes`, `callbacks_recebidos`, `status_cache` (existente no create_consultas mas separado em task original) — todas com schema apontado em `plan.md §4`.
- `tests/**/*.php` — testes Pest (escopos listados em cada task).
- `routes/{web,webhooks}.php` — rotas estruturais.
- `config/services.php` — credenciais meta/zapi.

Todos os stubs contêm:
```php
// TODO (T-NNN): implementar conforme tasks.md §F<x> + plan.md §3 F<x>.
//   Objetivo: ...
//   Contrato: ...
//   Testes: ...
```

---

## 4. Cobertura do plan

| Fase do plan | Arquivos completos | Arquivos stub | Situação |
|---|:-:|:-:|---|
| F1 Bootstrap + Cadastro | 4 (Clinica, Paciente, composer, env) | 6 | 🟢 núcleo coberto |
| F2 Agendamento | 1 (Consulta) | 4 | 🟡 pilar coberto, services triviais stub |
| F3 Histórico imutável | **10** | 0 | 🟢 **todo completo** — pilar crítico |
| F4 Driver + Meta + webhook | **9** | 2 | 🟢 **pilar D-E-02 completo** |
| F5 Job disparo + guards | **5** | 2 | 🟢 **pipeline completo** |
| F6 Resposta | 0 | 4 | 🟡 stub — didática já coberta por F5 |
| F7 Painel + intervenção + T-060/T-061 | 2 (T-060, link) | 6 | 🟢 T-060 completo (pilar D-E-05) |
| F8 Config + correção | 1 (CorrigirMarcacao) | 6 | 🟢 pilar C-005 completo |
| F9 Anonimização LGPD | **2** | 1 | 🟢 **pilar C-003 completo** |
| F10 Observabilidade + CI | 2 (CI, MascarararPii) | 5 | 🟢 núcleo coberto |

**Total arquivos:** ~20 completos + ~35 stubs = ~55 arquivos. Média task-por-arquivo ≈ 1.15.

---

## 5. O que validar ao ler o código

Se você for ler o código canônico para entender como a skill funciona na prática, siga esta ordem:

1. **`codigo/README.md`** — layout geral.
2. **`app/Models/EventoConsulta.php`** — veja como um model pode ser append-only sem mágica.
3. **`database/migrations/..._add_append_only_trigger_eventos.php`** — defense-in-depth no DB.
4. **`app/Domain/Confirmacao/Services/DerivarStatus.php`** — reducer com eventos de correção.
5. **`app/Domain/Notificacao/Contracts/NotificacaoDriver.php`** — contrato abstrato.
6. **`app/Infra/Notificacao/MetaCloudDriver.php`** — adaptador real com tradução de erro.
7. **`app/Domain/Confirmacao/Jobs/DispararLembreteJob.php`** — orquestração de 4 guards + driver + evento.
8. **`app/Domain/Lgpd/Services/AnonimizarPaciente.php`** — transacional + lock pessimista.

Cada um desses tem docblock de topo explicando qual FR/D/C/D-E materializa.

---

## 6. Limitações declaradas

- Código **não executado**. Erros de tipagem PHPStan nível 5 podem existir e não foram caçados. Bugs de runtime idem.
- Factories e testes Pest **não gerados** (estariam nas tasks de teste; ficam para Fase 8 simbolicamente).
- `EventServiceProvider` não criado — stubs de providers deveriam registrar o listener `AtualizarStatusCacheDaConsulta`.
- `Routes/web.php` com as rotas de Livewire não materializado.
- Alguns edge cases do plan R-01..R-10 têm mitigação **descrita** mas não necessariamente *testada em código* (ex: R-02 benchmark append-only — T-059 standalone).

Limitações aceitas como **`[RISCO ASSUMIDO]` da Fase 7 em modo canônico documental**. Se este código fosse para execução real, Fase 8 Test rodaria toda a suite e descobriria os bugs antes do merge. Como é canônico, "pode seguir".

---

**Veredicto do Arquiteto:** 🟢 implementação canônica completa para didática C2. Próxima fase: **Fase 8 Test** — documentação dos testes planejados (já listados em cada task) + execução simbólica.
