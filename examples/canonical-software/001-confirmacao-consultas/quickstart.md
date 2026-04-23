---
artefato: quickstart
fase: 9
dominio: [software]
schema_version: 1
requer:
  - "1. Contrato desta fase"
  - "2. Pré-requisitos"
  - "3. Subir localmente"
  - "4. Caminho feliz"
  - "5. Caminho de erro"
  - "6. Caminho de permissão"
  - "7. Caminho de falha parcial"
  - "8. Rollback / limpeza"
  - "9. Quem validou"
  - "10. Veredicto"
---

# Quickstart — `001-confirmacao-consultas` (Fase 9)

**Data:** 2026-04-23
**Versão da feature:** v1 (MVP — Stories P1..P4 + edge cases)
**Autor:** Thiago Loumart (modo Arquiteto)
**Status:** Draft documental (validação humana via merge do PR `w1b/f9-quickstart`)
**Objetivo:** roteiro reprodutível por **qualquer pessoa sem contexto do código** que valida manualmente, ponta a ponta, que o módulo Confirmação de Consultas funciona.

---

## 1. Contrato desta fase

**Em modo canônico documental (C2), o quickstart NÃO é executado neste repositório.** O canônico é um **exemplo da skill** — não o projeto Laravel operacional. O contrato da Fase 9 (`fases/09_QUICKSTART.md`: "Quickstart executado manualmente com sucesso") é substituído pelo contrato documental:

> O roteiro abaixo está **escrito** com nível de detalhe suficiente para que qualquer pessoa o execute em projeto Laravel real derivado deste canônico. Em projeto real, esta seção §1 sai e a §9 ("Quem validou") ganha pelo menos 1 linha preenchida pelo humano que executou.

**`[RISCO ASSUMIDO] canonical-F9`** — quickstart documentado mas não executado. Aceito conscientemente porque o repositório é a **skill**, não o produto. Consequências:
- Comandos podem ter sutilezas de versão (PHP 8.3.x, Laravel 12.x exatos) que só rodada real revela.
- Resultados esperados estão derivados de `spec.md v2`, `plan.md`, `tasks.md v2` e `implement_notes.md` — não de uma execução de fato.
- Em projeto **real** aplicando esta skill, Fase 9 é **execução manual obrigatória** por alguém que NÃO implementou (Manual §16) — não pular.

**Cobertura mapeada para spec.md v2:**

| Caminho do quickstart | User Story / FR / Edge case |
|---|---|
| §4.1 Lembrete enviado e paciente confirma | US1 P1 · FR-006..FR-009 · AS1+AS2 da US1 |
| §4.2 Painel do dia | US2 P2 · FR-019..FR-021 · AS1..AS3 da US2 |
| §4.3 Intervenção manual | US3 P3 · FR-022..FR-026 · AS1..AS4 da US3 |
| §4.4 Configuração de janela | US4 P4 · FR-027..FR-029 · AS1..AS3 da US4 |
| §5.1 Provedor 5xx transitório | Edge "Provedor indisponível" · FR-011 · C-004 |
| §5.2 Número WhatsApp inválido | Edge "Número inválido" · FR-012 · C-003 |
| §5.3 Template reprovado | Edge "Template reprovado/suspenso" · P-03 |
| §5.4 Resposta texto livre | Edge "Texto livre fora dos botões" · FR-016 |
| §5.5 Múltiplas respostas | Edge "Múltiplas respostas" · FR-016 · C-005 |
| §5.6 Data passada no agendamento | Edge implícito · FR-002 · `DataNoPassadoException` |
| §6.1 Atendente sem `is_admin` anonimiza | FR-033 · C-003 · Policy T-063 |
| §6.2 Atendente sem `is_admin` altera config | FR-027 · `ExigeIsAdmin` middleware |
| §6.3 Médico vê consulta de outro médico | FR-021 · Policy T-063 (P-04 do Analyze) |
| §7.1 Webhook após anonimização | T-052 race · R-04 do plan |
| §7.2 Rate-limit 51ª/min postpona | NFR-006 · DT-08 |
| §7.3 Janela 03h posterga p/ 08h | Edge "Horário inadequado" · FR-010 · C-004 |

---

## 2. Pré-requisitos

Antes de seguir o roteiro, garantir que o ambiente atende a TODOS os itens abaixo. Cada item tem **comando de verificação** e **resultado esperado**.

### 2.1 Sistema operacional e runtime
- [ ] **PHP 8.3.x** instalado: `php -v` → linha começando com `PHP 8.3.`
- [ ] **Composer 2.x** instalado: `composer --version` → linha começando com `Composer version 2.`
- [ ] **Node 20+** instalado: `node -v` → linha começando com `v20.` ou `v22.`
- [ ] **Docker + Docker Compose** funcionando: `docker compose version` → linha não vazia.
- [ ] **Git** com remote configurado: `git remote -v` → mostra `origin`.

### 2.2 Repositório clonado e atualizado
```bash
git clone <repo-url> confirmacao-consultas
cd confirmacao-consultas
git checkout main
git pull
```
Resultado esperado: `Already up to date.` ou pull limpo sem conflitos.

### 2.3 Dependências instaladas
```bash
composer install                    # baixa pacotes PHP; ~30-60s
npm install                         # baixa pacotes Node; ~30-60s
npm run build                       # build Vite; ~10-20s
```
Resultado esperado: nenhum erro fatal; `vendor/` e `node_modules/` populados; `public/build/` gerado.

### 2.4 Banco e cache rodando (Postgres 16 + Redis 7)
```bash
docker compose up -d postgres redis
docker compose ps
```
Resultado esperado: serviços `postgres` e `redis` com status `Up` e `(healthy)`.

### 2.5 Env vars (`.env` na raiz)
Copiar `.env.example` → `.env` e preencher:
```
APP_ENV=local
APP_KEY=                          # será gerada em §3
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=confirmacao
DB_USERNAME=postgres
DB_PASSWORD=postgres
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
QUEUE_CONNECTION=redis
WHATSAPP_DRIVER=noop              # MVP local: noop bloqueia envio real
META_PHONE_NUMBER_ID=             # vazio em local com noop
META_ACCESS_TOKEN=
META_WABA_ID=
META_WEBHOOK_VERIFY_TOKEN=local-validation-token
META_TEMPLATE_LEMBRETE_NAME=lembrete_consulta_v1
META_TEMPLATE_LEMBRETE_LANG=pt_BR
NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO=50
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

**Para validar §4.1 com WhatsApp real (sandbox Meta):** trocar `WHATSAPP_DRIVER=meta` e preencher `META_*` com credenciais de sandbox aprovado. Caso contrário, o teste §4.1 prova o caminho até o `NoopDriver` (que registra evento `lembrete_enviado` sem enviar de fato — útil para validação isolada).

### 2.6 Serviços externos acessíveis (opcional, só se `WHATSAPP_DRIVER=meta`)
- [ ] Acesso à `graph.facebook.com` (firewall não bloqueia).
- [ ] Túnel HTTPS público para webhook: `ngrok http 8000` → URL `https://*.ngrok-free.app`.
- [ ] URL do ngrok configurada no painel Meta Business: `Webhooks → Callback URL = https://<ngrok>.ngrok-free.app/webhook/whatsapp`.

---

## 3. Subir localmente

```bash
php artisan key:generate                    # gera APP_KEY no .env
php artisan migrate --force                 # ~10s; cria 8+ tabelas + trigger PG append-only
php artisan db:seed --class=DemoDataSeeder  # 1 clínica + 2 médicos + 5 pacientes + 10 consultas
php artisan storage:link                    # links public/storage
```
Resultado esperado de `migrate`:
```
Migration table created successfully.
Migrating: 2026_04_21_000001_create_clinicas_table .... DONE
... (8+ migrations)
Migrating: 2026_04_21_000008_add_append_only_trigger_eventos .... DONE
```

Resultado esperado de `db:seed`: linha `Seeded: Database\Seeders\DemoDataSeeder` + ausência de exception.

Em **três terminais separados** rodar:
```bash
# Terminal A — servidor HTTP
php artisan serve
# Esperado: "Server running on [http://127.0.0.1:8000]"

# Terminal B — worker da fila
php artisan queue:work --tries=3
# Esperado: linha "Processing jobs from the [default] queue"

# Terminal C — scheduler (executa job DetectarSemRespostaJob a cada 5min)
php artisan schedule:work
# Esperado: linha "Schedule worker started successfully"
```

**Login inicial** (criado pelo seeder):
- Atendente: `atendente@demo.local` / `password`
- Admin clínica: `admin@demo.local` / `password` (`is_admin = true`)
- Médico Dr. Silva: `dr.silva@demo.local` / `password`
- Médico Dra. Souza: `dra.souza@demo.local` / `password`

Acessar `http://localhost:8000/login` → autenticar como atendente → ver dashboard.

---

## 4. Caminho feliz

Cada subseção é uma **User Story independente** validada de ponta a ponta. Executar **na ordem** porque §4.2..§4.4 dependem de dados criados em §4.1.

### 4.1 — Lembrete enviado e paciente confirma (US1 / P1)

**Pré-condição:** logado como `atendente@demo.local`. Driver = `noop` (ou `meta` em sandbox).

1. Acessar `http://localhost:8000/consultas/nova`
2. Preencher:
   - Paciente: `João Silva` (já no seeder)
   - Médico: `Dr. Silva`
   - Data/hora: **daqui a 25 horas** (≥ janela default 24h da clínica seedada)
3. Clicar em **"Salvar"**.
4. **Esperado:** redirect para `/consultas` com flash `Consulta agendada com sucesso`. Listagem mostra a nova consulta com status `agendada`.
5. Forçar disparo do job (sem esperar a janela natural):
   ```bash
   php artisan tinker
   >>> App\Domain\Confirmacao\Jobs\DispararLembreteJob::dispatchSync(\App\Models\Consulta::latest('id')->first());
   ```
6. **Esperado no terminal B (worker):** linha `Processing: App\Domain\Confirmacao\Jobs\DispararLembreteJob` seguida de `Processed`. Sem exception.
7. **Esperado no banco:**
   ```sql
   SELECT tipo, ator_tipo, canal, criado_em FROM eventos_consulta
     WHERE consulta_id = (SELECT id FROM consultas ORDER BY id DESC LIMIT 1)
     ORDER BY id;
   ```
   Saída: 1 linha — `lembrete_enviado | sistema | whatsapp | <timestamp>`.
8. **Esperado no painel** (`/painel`): consulta migrou de `agendada` → `lembrete-enviado`.
9. Simular resposta do paciente "Confirmar":
   ```bash
   curl -X POST http://localhost:8000/webhook/whatsapp \
     -H "Content-Type: application/json" \
     -d '{"entry":[{"changes":[{"value":{"messages":[{"from":"5511999990001","interactive":{"button_reply":{"id":"confirmar:CONSULTA_<id>"}},"id":"wamid.test1"}]}}]}]}'
   ```
10. **Esperado:** HTTP 200 imediato (webhook retorna sem aguardar processamento).
11. **Esperado pós-job:** novo evento `confirmado | paciente | whatsapp` no histórico; `consultas.status_cache = 'confirmada'`; painel reflete `confirmada` (refresh ou Livewire poll).

**Print/saída de evidência esperado:**
```
> SELECT id, status_cache FROM consultas ORDER BY id DESC LIMIT 1;
 id | status_cache
----+--------------
 11 | confirmada
```

### 4.2 — Painel do dia mostra status consolidado (US2 / P2)

**Pré-condição:** seeder criou 10 consultas em estados misturados (4 `agendada`, 2 `lembrete-enviado`, 2 `confirmada`, 1 `cancelada-pelo-paciente`, 1 `sem-resposta`).

1. Acessar `http://localhost:8000/painel` como `atendente@demo.local`.
2. **Esperado:** lista renderizada agrupada por horário do dia (hoje + amanhã), uma linha por consulta com colunas: paciente, médico, hora, status (badge colorido), última atualização.
3. **Esperado:** consultas em `sem-resposta` aparecem com **destaque vermelho/laranja** (badge + ícone de alerta) e ficam no topo da seção delas.
4. Aplicar filtro **"Só confirmadas"** no seletor.
5. **Esperado:** lista reduz para 2 consultas; URL ganha querystring `?status=confirmada`; recarregar a página mantém o filtro.
6. Limpar filtro → todas reaparecem.

### 4.3 — Intervenção manual do atendente (US3 / P3)

**Pré-condição:** identificar uma consulta em `sem-resposta` no painel (ex: `id=7`).

1. Clicar na linha → abre modal "Ações da consulta".
2. Clicar em **"Confirmar em nome do paciente"**.
3. Modal exige campo `observacao` (livre). Preencher: `paciente confirmou por telefone`.
4. **Esperado:** modal fecha; flash `Consulta confirmada manualmente`; status na lista vira `confirmada`; ícone do ator muda para "operador" (não "paciente").
5. Acessar `/consultas/7/historico`.
6. **Esperado:** timeline com mínimo 3 eventos:
   - `lembrete_enviado | sistema | whatsapp | <T-Nh>`
   - (sem evento de confirmação automática — paciente não respondeu)
   - `confirmado | atendente | manual-pelo-painel | <agora> | observacao="paciente confirmou por telefone"`
7. Repetir o teste para **"Cancelar manualmente"** em outra consulta `sem-resposta`. Status vira `cancelada-pelo-paciente`, ator = atendente, motivo obrigatório.

### 4.4 — Configuração da janela pelo admin (US4 / P4)

**Pré-condição:** logout do atendente; login como `admin@demo.local` (`is_admin=true`).

1. Acessar `http://localhost:8000/configuracao`.
2. **Esperado:** formulário com campos `janela_lembrete_horas` (default 24), `janela_silencio_horas` (default 4), `horario_operacional_inicio` (08:00), `horario_operacional_fim` (20:00), `max_retries` (3).
3. Alterar `janela_lembrete_horas` de `24` → `48` e clicar **"Salvar"**.
4. **Esperado:** flash `Configuração atualizada`; banco `clinicas.janela_lembrete_horas = 48`.
5. Criar **nova** consulta (atendente) para `daqui a 50 horas`.
6. **Esperado:** novo registro em `jobs` (Redis) agendado para `consulta.datahora_agendada - 48 horas`. Verificar com:
   ```bash
   php artisan tinker
   >>> Cache::store('redis')->connection()->keys('queues:default*');
   ```
7. **Esperado AS3:** consultas que **já tinham job agendado** com janela 24h **mantêm** a janela antiga. Verificar que a `consulta_id` do passo §4.1 não foi reagendada.

---

## 5. Caminho de erro

### 5.1 — Provedor WhatsApp 5xx transitório (FR-011)

**Pré-requisito:** `WHATSAPP_DRIVER=meta` + credenciais de sandbox Meta + URL Meta substituível.

1. Apontar `META_GRAPH_URL` (env opcional) para um servidor mock que retorna `503` nas 2 primeiras chamadas e `200` na 3ª. Reiniciar `queue:work`.
2. Forçar `DispararLembreteJob` para uma consulta de teste.
3. **Esperado terminal B:** 2 linhas `Job failed, retrying` + 1 linha `Processed`.
4. **Esperado eventos:** ÚNICO evento final `lembrete_enviado` (não 3). Cada tentativa intermediária NÃO gera evento — guard `IdempotenciaLembreteGuard` impede duplicação.
5. Para forçar **falha definitiva** (3 tries esgotados): mock retorna `503` sempre. **Esperado:** evento final `lembrete_falha_envio | sistema | whatsapp` com `payload_extra.motivo = transitorio_esgotado`; consulta destacada no painel.

### 5.2 — Número WhatsApp inválido (FR-012, C-003)

1. Cadastrar paciente com telefone `+55 11 00000-0000` (número Meta retorna como inválido em sandbox).
2. Criar consulta + forçar job.
3. **Esperado:** **zero retries** (FalhaDefinitivaException categoria `numero-invalido` não retentável).
4. **Esperado evento:** `lembrete_falha_envio | sistema | whatsapp` com `payload_extra.categoria = numero-invalido`.
5. **Esperado painel:** consulta com badge vermelho + tooltip "Número inválido — atualize cadastro".

### 5.3 — Template reprovado/suspenso pelo provedor (P-03 do Analyze)

1. Em ambiente sandbox, marcar template `lembrete_consulta_v1` como `REJECTED` no painel Meta.
2. Forçar job.
3. **Esperado:** `FalhaDefinitivaException categoria=template-rejeitado`. Evento `lembrete_falha_envio` com mesmo motivo.
4. **Esperado:** notificação por e-mail ao admin da clínica (`MAIL_*` configurado) — operador NÃO deve descobrir o problema só pela consulta isolada (Manual §29 fallback).

### 5.4 — Resposta texto livre fora dos botões (FR-016)

1. Repetir o webhook do §4.1, trocando o payload `interactive.button_reply` por:
   ```json
   {"messages":[{"from":"5511999990001","text":{"body":"amanhã eu confirmo"},"id":"wamid.test2"}]}
   ```
2. **Esperado:** webhook retorna 200; novo evento `resposta_ambigua | paciente | whatsapp | payload_extra.texto="amanhã eu confirmo"`.
3. **Esperado painel:** consulta NÃO muda status (continua `lembrete-enviado`); ganha badge "Resposta requer revisão" para o atendente decidir manualmente (US3).

### 5.5 — Múltiplas respostas em sequência (FR-016, C-005)

1. Disparar dois webhooks consecutivos: `confirmar:CONSULTA_X` e depois `cancelar:CONSULTA_X` em < 30s.
2. **Esperado:** 2 eventos no histórico (ordem cronológica preservada).
3. **Esperado status_cache:** `cancelada-pelo-paciente` (a **última** vale, conforme reducer `DerivarStatus`).
4. **Esperado painel:** mostra status final + ícone "histórico tem múltiplas respostas" para alertar atendente.

### 5.6 — Tentativa de agendar consulta no passado

1. Tela `/consultas/nova`, escolher data/hora **24h atrás**.
2. Clicar em "Salvar".
3. **Esperado:** formulário rejeita com mensagem `Não é possível agendar consulta no passado.` (HTTP 422 quando submetido via API; Livewire renderiza inline). Nenhum registro no banco.

---

## 6. Caminho de permissão

### 6.1 — Atendente sem `is_admin` tenta anonimizar paciente (FR-033, C-003, T-063)

1. Logado como `atendente@demo.local` (sem `is_admin`).
2. Acessar `/pacientes/1/anonimizar` ou clicar no botão correspondente.
3. **Esperado:** botão NÃO aparece para esse perfil. Acesso direto pela URL retorna **HTTP 403** (`AnonimizarPacientePolicy::authorize` falha em `ExigeIsAdmin`).
4. Repetir como `admin@demo.local`. Botão aparece; ação executa (ver §7.1 para o caminho completo).

### 6.2 — Atendente sem `is_admin` tenta alterar configuração de janela (FR-027)

1. Logado como atendente.
2. Acessar `/configuracao`.
3. **Esperado:** **HTTP 403** com mensagem `Apenas administradores da clínica podem alterar a configuração.` Middleware `ExigeIsAdmin` aplicado na rota.

### 6.3 — Médico tenta ver consulta de outro médico (Policy T-063, P-04 do Analyze)

1. Logado como `dr.silva@demo.local` (médico).
2. Identificar uma consulta de `dra.souza@demo.local` (ex: `id=4`).
3. Acessar `/consultas/4`.
4. **Esperado:** **HTTP 403** com mensagem `Você só pode visualizar suas próprias consultas.` Policy `ConsultaPolicy::view` valida `consulta.medico_id == auth()->user()->medico_id`.
5. Repetir como `atendente@demo.local` (atendente vê todas) → permitido.
6. Repetir como `admin@demo.local` → permitido.

---

## 7. Caminho de falha parcial

### 7.1 — Webhook chega APÓS anonimização (race T-052, R-04 do plan)

1. Logado como `admin@demo.local`. Acessar `/pacientes/1/anonimizar` → confirmar (motivo obrigatório: `pedido LGPD art. 18`).
2. **Esperado banco:** `pacientes.id=1` com `nome='paciente-excluido-<hash>'`, `telefone=NULL`, `email=NULL`. Eventos do histórico **preservados** (FR-033, integridade referencial).
3. Disparar webhook simulando resposta tardia daquele paciente:
   ```bash
   curl -X POST http://localhost:8000/webhook/whatsapp \
     -H "Content-Type: application/json" \
     -d '{"entry":[{"changes":[{"value":{"messages":[{"from":"<numero-antigo>","interactive":{"button_reply":{"id":"confirmar:CONSULTA_1"}},"id":"wamid.late1"}]}}]}]}'
   ```
4. **Esperado:** webhook retorna 200; evento `confirmado | paciente | whatsapp` registrado **na consulta correta** (matching por `id_externo` ou `consulta_id` no payload, NÃO por telefone — telefone foi anonimizado).
5. **Esperado:** painel **não vaza PII** — coluna paciente mostra `paciente-excluido-<hash>`.

### 7.2 — Rate-limit estourado (NFR-006, DT-08 — 51ª mensagem/min)

1. Em terminal:
   ```bash
   php artisan tinker
   >>> for ($i = 0; $i < 55; $i++) { App\Domain\Confirmacao\Jobs\DispararLembreteJob::dispatch(\App\Models\Consulta::find($i + 1)); }
   ```
2. **Esperado terminal B:** as primeiras 50 mensagens processam normalmente em janela de 1min; da 51ª em diante, jobs são **postponados** (re-enfileirados com `release(60)`) sem exception.
3. **Esperado eventos:** 50 `lembrete_enviado` no minuto N; resto distribui no minuto N+1.
4. **Esperado log:** linha `RateLimitClinicaGuard: postponing dispatch for clinica=1` (LOG_LEVEL=debug).

### 7.3 — Janela operacional 03h da manhã (FR-010, C-004)

1. **Antecipar relógio do servidor** (ou usar `Carbon::setTestNow`) para `03:00` BRT.
2. Disparar job para uma consulta cuja janela natural cairia neste horário.
3. **Esperado:** `RespeitaJanelaOperacional` retorna `false`; job posterga para próximo `08:00` (`release(till_08h)`); evento `lembrete_postponed | sistema | whatsapp | payload_extra.motivo=fora-de-janela`.
4. **Caso o adiamento ultrapasse o próprio horário da consulta:** evento final `lembrete_cancelado_fora_janela`; consulta marcada `sem-canal` no painel para ação manual (US3).

---

## 8. Rollback / limpeza

> **Atenção §5.4 do Manual:** rollback em produção neste módulo tem regras especiais — histórico imutável e anonimização LGPD são **irreversíveis por design**. Leia esta seção antes de tocar em ambiente que tenha dados reais.

### 8.1 Reverter migrations (DEV/STAGING APENAS)
```bash
php artisan migrate:rollback --step=8     # reverte as 8 do MVP
```
**Esperado:** mensagens `Rolled back: ...`. Tabelas removidas. **NÃO RODAR EM PRODUÇÃO** se já houve evento real — o trigger PG `BEFORE UPDATE OR DELETE ON eventos_consulta` será removido junto e perde-se a defesa-em-profundidade.

### 8.2 Rollback emergencial de envios (PRODUÇÃO OK)
```bash
# Parar o canal real sem derrubar o sistema:
WHATSAPP_DRIVER=noop  # alterar no .env de produção
php artisan config:clear
php artisan queue:restart
```
**Esperado:** jobs já enfileirados continuam tentando, mas o driver vira `NoopDriver` — registra evento `lembrete_enviado` sem chamada real à Meta. Útil quando descoberto problema com template/credencial em horário de pico.

### 8.3 Limpar dados de demo
```bash
php artisan migrate:fresh --seed --seeder=DemoDataSeeder
```
**Esperado:** banco zerado e re-seedado. **NUNCA RODAR EM PRODUÇÃO.**

### 8.4 Limpar cache + filas + sessões (problemas estranhos)
```bash
php artisan optimize:clear
php artisan queue:clear
php artisan session:clear
docker compose restart redis
```

### 8.5 O que NÃO é reversível
- **Eventos do histórico** (`eventos_consulta`): trigger PG bloqueia UPDATE/DELETE; restauração só via backup do PG. **Por design** (D-E-03).
- **Anonimização LGPD** (`AnonimizarPaciente`): sobrescreve PII em transação atômica; restauração só via backup. **Por design** (FR-033, C-003).
- **Mensagens já enviadas pelo WhatsApp:** Meta não tem API de "delete sent message". Único mitigador é mensagem complementar (ex: "ignore o lembrete anterior").

---

## 9. Quem validou

> Em modo canônico documental (C2), esta tabela fica **vazia**. Em projeto real derivado, preencher uma linha por execução manual completa (Manual §16 exige que pelo menos UMA pessoa que NÃO implementou o módulo execute o quickstart).

| Data | Pessoa | Ambiente | Driver WA | Resultado | Observações |
|---|---|---|---|---|---|
| — | — | — | — | — | (canonical-F9: não-execução documental) |

---

## 10. Veredicto

Esta fase **não executa o roteiro** (canônico = doc). O quickstart está **escrito** com:

- **15 caminhos de validação** (4 felizes + 6 erro + 3 permissão + 2 falha parcial), todos rastreáveis a FR/User Story/Edge case da `spec.md v2`.
- **Pré-requisitos verificáveis** (5 grupos com comandos + resultado esperado para cada).
- **Rollback explícito** com 3 categorias de reversibilidade (full em dev, parcial em prod via NoopDriver, **nenhum** para histórico/LGPD).
- **Cobertura Manual §16:** comandos · telas · ações · resultados — todos presentes em cada subseção.

**Aprovado** como artefato documental da Fase 9 em modo canônico. Em projeto **real** derivado:

1. Provisionar ambiente conforme §2.
2. Executar §3 → §7 na ordem.
3. Preencher §9 com a execução real (assinatura + data + ambiente + resultado).
4. Se algum passo divergir do esperado → **voltar para Fase 7 Implement** (não tentar corrigir só o quickstart). Manual §16 é gate.

**Riscos aceitos nesta fase:**
- `[RISCO ASSUMIDO] canonical-F9` — não-execução, como declarado em §1.
- `[RISCO ASSUMIDO]` herdado de F7 — ~35 stubs (CRUD/UI triviais) podem precisar materialização antes do roteiro real rodar.

**Próxima fase:** Fase 10 Review — self-review aplicando `templates/review.md` sobre o conjunto F4..F9 (canonical-001 inteiro), com matriz de aderência à constituição + sinal de §5.4 + lint final dos 14 artefatos `.md`.

Assinado por: Thiago Loumart (modo Arquiteto, 2026-04-23)
