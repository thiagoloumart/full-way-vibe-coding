---
artefato: test_plan
fase: 8
dominio: [software]
schema_version: 1
requer:
  - "1. Contrato desta fase"
  - "2. Cobertura de testes por fase do plan"
  - "3. Testes críticos"
  - "4. Veredicto"
---

# Plano de Testes — `001-confirmacao-consultas` (Fase 8)

**Data:** 2026-04-20
**Autor:** Thiago Loumart (modo Arquiteto)
**Status:** Draft documental (validação humana via merge do PR `w1b/f8-test`)

---

## 1. Contrato desta fase

**Em modo canônico documental (C2), os testes NÃO são executados neste repositório.** O canônico é um **exemplo da skill** — não o projeto Laravel operacional. O contrato da Fase 8 (`fases/08_TEST.md`: "Todos os testes verdes") é substituído pelo contrato documental:

> Os testes **estão planejados por task em `tasks.md` §"Testes exigidos"** e organizados abaixo por fase do plan. Em projeto real, executar `vendor/bin/pest` no diretório `codigo/` faria a suíte rodar contra PostgreSQL 16 + Redis 7 do `docker-compose` (ver `plan.md §2.2` env vars).

**`[RISCO ASSUMIDO] canonical-F8`** — testes documentados mas não executados. Aceito conscientemente porque o repositório é a **skill**, não o produto. Consequências:
- Possíveis erros de tipagem (PHPStan nível 5) podem existir no código do PR #11.
- Casos de runtime (ex: `DerivarStatus` com evento `correcao` não-referenciado) podem ter bugs que só rodada real pegaria.
- Em projeto **real** aplicando esta skill, Fase 8 é **execução real obrigatória** — não pular.

---

## 2. Cobertura de testes por fase do plan

Cada task em `tasks.md` v2 (T-001..T-063) tem campo **"Testes exigidos"** listando cenários de sucesso/erro/edge. Consolidação por fase:

| Fase do plan | Tasks | Testes planejados | Tipos | Edge cases cobertos |
|---|---|---|---|---|
| F1 Bootstrap + Cadastro | T-001..T-006 | ~10 | feature (auth, cadastro) + unit (VO telefone) | telefone inválido · duplicação parcial |
| F2 Agendamento | T-007..T-010 | ~8 | unit (services) + feature (Livewire) | data passada · estado terminal |
| **F3 Histórico imutável** | T-011..T-018 | **~14** | unit (enums, reducer) + **feature (append-only DB + Eloquent)** | update/delete bloqueados em 2 camadas · correção sobreposta |
| **F4 Driver + webhook** | T-019..T-026 | **~16** | **contract (abstrato compartilhado)** + unit (HTTP fake) + feature (webhook) | transitório 5xx · definitivo numero-invalido · template-rejeitado · callback duplicado · órfão |
| **F5 Job disparo** | T-027..T-033 | **~14** | unit (helper puro, guards) + feature (job completo Queue::fake()) | janela 03h postpone · retry 3× · rate-limit 51ª · idempotência |
| F6 Resposta | T-034..T-037 | ~9 | unit (reconciliar, aplicar) + feature (webhook→job→status) | botão × texto livre · múltiplas respostas · id órfão |
| F7 Painel + intervenção + T-060/T-061 | T-038..T-042, T-060, T-061 | ~12 | feature Livewire + **feature scheduler (T-060)** | destaque sem-resposta · link expirado · intervenção manual |
| F8 Config + presença + correção | T-043..T-047, T-063 | ~11 | feature (ExigeIsAdmin) + **feature Policy (T-063)** + unit (CorrigirMarcacao) | antes do horário · motivo ausente · médico vê consulta de outro |
| **F9 Anonimização LGPD** | T-048..T-052 | **~9** | feature (anonimizar) + **property (GuardIntegridadeReferencial)** + **feature race T-052** | is_admin ausente · motivo ausente · já anonimizado · race com job |
| F10 Observabilidade + CI + rate-limit + command | T-053..T-059, T-062 | ~10 | unit (PII mask) + integration (rate-limit 50 real) + **benchmark T-059 append-only** + feature (command T-062) | PII vazamento · trigger PG degrada >20% |

**Total estimado:** ~113 testes Pest distribuídos em ~40 arquivos de teste.

### Matriz Edge Case × Nível de teste

13 edge cases da spec.md v2 mapeados para nível de teste:

| Edge case | Nível | Task de referência |
|---|---|---|
| Provedor indisponível (transitório) | unit + feature | T-021, T-032 |
| Número inválido (definitivo) | unit | T-021 |
| Template reprovado | unit (exception específica) | T-021 |
| Resposta texto livre | unit (reducer) + feature | T-034, T-035 |
| Múltiplas respostas (última vale) | unit (reducer) | T-035 |
| Idempotência disparo duplicado | unit (guard) + feature (job 2×) | T-030, T-032 |
| Cancelamento após lembrete | feature | T-042 |
| Alteração config mid-day | feature | T-044 |
| Paciente sem WhatsApp | feature (criar consulta + status sem-canal) | T-003, T-008 |
| Deleção LGPD | feature + property | T-048, T-049 |
| Horário inadequado (03h) | unit (RespeitaJanelaOperacional) | T-027 |
| Correção compareceu/no-show | unit + feature | T-046, T-047 |
| Callback duplicado Meta | unit (CallbackIdempotenciaGuard) + feature | T-036 |
| Fuso único BR | documentado `[RISCO ASSUMIDO]` | — |

**Cobertura:** 13/14 edge cases com teste; 1 aceito como risco documentado.

### Matriz Manual §29 × Teste

| Campo §29 | Teste |
|---|---|
| Gatilho | feature "criar consulta → lembrete agendado" (T-029) |
| Contexto lido | unit "job carrega relações corretas" (T-032) |
| Decisão | unit guards (T-030, T-031) |
| Ação | contract NotificacaoDriver (T-026) |
| Condição de bloqueio | feature "janela 03h posterga" (T-028) |
| Fallback | feature "3× retry esgotado → lembrete_falha_envio" (T-032) |
| Log | feature "evento LembreteEnviado com id_externo" (T-032) |
| Critério de sucesso | integration "pipeline completo" (T-033) + benchmark T-058 |
| Risco de falso positivo | feature "anonimização em trânsito cancela envio" (T-052) |

---

## 3. Testes críticos

**Ordem de execução recomendada** em projeto real (quem tem ambiente Laravel deveria rodar nesta sequência, pegando regressões cedo):

1. **Unit de `DerivarStatus`** (T-016) — núcleo da integridade de status derivado. Se falhar, todo o painel mostra valores errados. Alta densidade de edge cases (`correcao`, múltiplas respostas, estado inicial vazio).
2. **Feature `HistoricoImutavelTest`** (T-013) — verifica trigger PG + Observer Eloquent. Se falhar, invariante D-E-03 está violável via SQL cru.
3. **Contract `NotificacaoDriverContractTest`** (T-026) — bateria comum rodada contra NoopDriver + MetaCloudDriver (HTTP fake) + ZApiDriver (stub). Se falhar, D-E-02 está quebrado.
4. **Feature `DispararLembreteJobTest`** (T-032) — pipeline completo: 4 guards + driver + evento + Notificacao. Testes individuais dos guards primeiro (T-030, T-031), depois integrado.
5. **Feature `AnonimizarPacienteTest`** (T-048) — transação + lock + guards + sobrescrita. **Sem rollback em produção** — se falhar, dado foi perdido.
6. **Feature race T-052** — anonimização em trânsito cancela envio pendente. R-04 do plan é o risco mais sutil; este teste é o escudo.
7. **Benchmark T-059** — trigger PG não degrada INSERT > 20%. Não bloqueia CI (roda manual) mas obrigatório antes de produção.

### Testes que SERIAM rodados pelo CI (`.github/workflows/ci.yml`)

```
job lint            ./vendor/bin/pint --test                (instantâneo)
job static-analysis ./vendor/bin/phpstan analyse            (~30s)
job tests           ./vendor/bin/pest --coverage --min=60   (~2-5 min)
job audit           composer audit --no-dev                 (instantâneo)
```

Todos gate-blocking no PR. Threshold coverage 60% inicial (dívida 7.11); meta 70% ao fim de W2.

---

## 4. Veredicto

Esta fase **não executa testes** (canônico = doc). A cobertura está **planejada** task a task em `tasks.md` v2, cruzada com edge cases em `analyze.md §6`, e distribuída por nível de teste (unit / feature / contract / integration / benchmark) acima.

**Aprovado** como artefato documental da Fase 8 em modo canônico. Em projeto **real** derivado deste canônico:

1. Copiar `codigo/` para projeto Laravel 12 greenfield.
2. `composer install` + `cp .env.example .env` + `php artisan key:generate`.
3. Provisionar PG 16 + Redis 7 (docker-compose ou serviços locais).
4. `php artisan migrate` + `php artisan db:seed --class=ClinicaSeeder`.
5. Implementar os ~35 stubs conforme `tasks.md` v2.
6. `vendor/bin/pest` — **os ~113 testes acima passam a virar gate real**.

**Riscos aceitos nesta fase:**
- `[RISCO ASSUMIDO] canonical-F8` — não-execução, como declarado em §1.
- `[RISCO ASSUMIDO]` herdado — coverage Pest inicial pode ficar abaixo de 70% (ajuste em ADR minor; dívida 7.11).

**Próxima fase:** Fase 9 Quickstart — roteiro manual de validação ponta a ponta (abrir painel, criar consulta, receber lembrete sandbox, responder via botão, ver status mudar). Em modo canônico, documenta o roteiro **que seria executado** em projeto real.

Assinado por: Thiago Loumart (modo Arquiteto, 2026-04-20)
