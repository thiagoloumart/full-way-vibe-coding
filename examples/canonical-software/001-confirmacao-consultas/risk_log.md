---
artefato: risk_log
fase: 12
dominio: [software]
schema_version: 1
requer:
  - "1. Inventário"
  - "2. Classificação por materialização"
  - "3. Ações herdadas por projeto real derivado"
---

# Risk Log — `001-confirmacao-consultas`

**Data:** 2026-04-23
**Autor:** Thiago Loumart (modo Arquiteto)
**Ciclo:** F0 → F11 (2026-04-18 → 2026-04-23)
**Anexo de:** `retrospective.md §3 Riscos assumidos`

Anexo auditável dos 12 `[RISCO ASSUMIDO]` identificados ao longo do ciclo. Cada entrada tem: **origem** (fase/artefato onde assinado) · **justificativa humana** · **mitigação planejada** · **materializou?** · **custo observado em C2** · **ação herdada por projeto real C1 derivado**.

---

## 1. Inventário

### RA-01 — Fuso único BR no MVP

- **Origem:** `spec.md §Edge Cases` + `bmad.md §2.4 Fricções previsíveis`.
- **Autor:** humano (spec).
- **Justificativa:** perfil-alvo é MPE BR 1 clínica; fuso múltiplo é overengineering no MVP.
- **Mitigação planejada:** migração futura aditiva (adicionar coluna `timezone` em `clinicas`) — trivial.
- **Materializou?** ❌ Não (C2 não rodou; N=0 clientes reais).
- **Custo observado em C2:** zero.
- **Ação herdada C1:** se aparecer lead fora de BRT/BRST ou rede multi-fuso, abrir `D-NNN-REVISED` antes de ativar.

### RA-02 — "Última resposta vale" (cancelamento sobrepõe confirmação)

- **Origem:** `clarify.md C-005` + `decision_log.md` (implícito).
- **Autor:** humano (C-005).
- **Justificativa:** simplicidade; paciente que muda de ideia é caso legítimo. Reducer preserva todas as respostas no histórico (append-only D-E-03).
- **Mitigação planejada:** painel mostra "histórico tem múltiplas respostas" como alerta ao atendente.
- **Materializou?** ❌ Não (sem tráfego real).
- **Custo observado em C2:** zero.
- **Ação herdada C1:** monitorar % de consultas com ≥2 respostas de paciente; se >5%, avaliar UX adicional de "confirmação final travada após X horas".

### RA-03 — Multi-tenant adiado (single-tenant = 1 clínica)

- **Origem:** `decision_log.md D-003` + `bmad.md §2.6`.
- **Autor:** humano (D-003).
- **Justificativa:** perfil MPE 1 clínica; multi-tenant triplicaria superfície de teste de segurança no MVP sem demanda concreta.
- **Mitigação planejada:** `clinica_id` presente em todas as tabelas — migração para multi-tenant é aditiva (policy + tenant_id derivado).
- **Materializou?** ❌ Não.
- **Custo observado em C2:** zero.
- **Ação herdada C1:** gate de revisão "**≥3 leads multi-unidade no funil comercial** → abrir `D-003-REVISED` e bump major v2.0 da constituição do módulo".

### RA-04 — Provedor único Meta (concentração de risco)

- **Origem:** `decision_log.md D-002` + `adr_local_001_provedor_whatsapp.md §Consequências`.
- **Autor:** humano (D-002 + ADR-L-001).
- **Justificativa:** fit cultural BR + oficial (menor risco de ban); 1 integração vs. 3 do Caminho C (redução de superfície de bug).
- **Mitigação planejada:** contrato abstrato `NotificacaoDriver` (D-E-02); `ZApiDriver` como implementação irmã pronta para reversão em ~1 sprint (via env `WHATSAPP_DRIVER=zapi`).
- **Materializou?** ❌ Não (template Meta não submetido em C2).
- **Custo observado em C2:** zero.
- **Ação herdada C1:** monitoramento ativo da categoria `utility` no Meta Business Manager + alerta crítico configurado (plan.md §8); `ADR-L-002` de reversão documentada em ADR-L-001 §Plano de reversão.

### RA-05 — Rate-limit 50 msg/min por clínica (chute educado)

- **Origem:** `analyze.md DT-08` + `plan.md §NFR-006` + implementação `RateLimitClinicaGuard`.
- **Autor:** humano (analyze).
- **Justificativa:** cobre perfil MPE com folga (clínica média envia 100–500 mensagens/mês); limite do Meta Cloud é mais alto. Configurável via env `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO`.
- **Mitigação planejada:** ajustável sem deploy (env); log de postpone em nível debug permite diagnóstico.
- **Materializou?** ❌ Não.
- **Custo observado em C2:** zero.
- **Ação herdada C1:** medir p99 real de envio em sandbox antes do go-live; se clínica aproximar 70% do limite, aumentar antes que degrade UX.

### RA-06 — Custo médio Meta R$ 0,07–0,15 por mensagem `[INFERÊNCIA]`

- **Origem:** `adr_local_001_provedor_whatsapp.md §Alternativas consideradas (1) Meta`.
- **Autor:** humano (ADR-L-001).
- **Justificativa:** literatura Meta BR 2026; dentro do teto C-001 (R$ 0,20) com folga.
- **Mitigação planejada:** NFR-007 dispara revisão se custo real ultrapassar teto; crit. invalidação D-002 ativa.
- **Materializou?** ❌ Não (não cotado real).
- **Custo observado em C2:** zero.
- **Ação herdada C1:** cotação oficial Meta BSP antes do go-live; métrica `confirmacao_custo_total_reais` (dívida 7.13) instrumentada em Pulse/Prometheus.

### RA-07 — Metas `SC-NNN` são `[INFERÊNCIA]` recalibráveis

- **Origem:** `spec.md §Success Criteria` + `bmad.md §4.6 Hipóteses H-1..H-5`.
- **Autor:** humano (spec).
- **Justificativa:** derivadas de literatura e hipóteses sem dados primários; ajuste após piloto.
- **Mitigação planejada:** retrospectiva Fase 12 revisita metas contra observação (esta seção).
- **Materializou?** ✅ **Sim** — este risco materializa a própria existência desta retrospectiva; SC-001..SC-008 ficam `[NÃO MEDIDA — C2]`.
- **Custo observado em C2:** zero (design explicitamente não mede).
- **Ação herdada C1:** instrumentação obrigatória antes do go-live (proposta ADR-G-003 no `retrospective.md §4`); primeira retrospectiva C1 deve calibrar metas com 30d de dados reais.

### RA-08 — `[RISCO ASSUMIDO] canonical-F8` (não-execução da Fase 8 Test)

- **Origem:** `test_plan.md §1 Contrato desta fase`.
- **Autor:** humano (self-review PR #12).
- **Justificativa:** repositório é a **skill**, não o produto; executar pest exigiria ambiente Laravel real que o canônico não provisiona.
- **Mitigação planejada:** cobertura **planejada** em 113 testes × 10 fases × 13/14 edge cases × 9 campos §29; `test_plan.md §4` lista 6 passos para extração em projeto real.
- **Materializou?** ✅ **Sim (consciente)** — design explícito do canônico.
- **Custo observado em C2:** erros de tipagem PHPStan nível 5 possivelmente existem em código F7; casos de runtime (ex: `DerivarStatus` com evento `correcao` não-referenciado) podem ter bugs que rodada real pegaria.
- **Ação herdada C1:** **obrigatório** executar `vendor/bin/pest --coverage --min=60` contra PG 16 + Redis 7 antes do merge F11. Sem execução = **não mergar**.

### RA-09 — `[RISCO ASSUMIDO] canonical-F9` (não-execução do Quickstart)

- **Origem:** `quickstart.md §1 Contrato desta fase`.
- **Autor:** humano (self-review PR #13).
- **Justificativa:** mesma razão de RA-08.
- **Mitigação planejada:** quickstart **escrito** com 15 caminhos × pré-requisitos verificáveis × comandos + resultado esperado. Em projeto real, §9 "Quem validou" torna-se obrigatória com assinatura + data + ambiente.
- **Materializou?** ✅ **Sim (consciente)**.
- **Custo observado em C2:** comandos podem ter sutilezas de versão (PHP 8.3.x, Laravel 12.x exatos) que só rodada real revela. `META_GRAPH_URL` em `.env.example` ausente (dívida 9.5).
- **Ação herdada C1:** **obrigatório** executar quickstart manualmente por pessoa que NÃO implementou (Manual §16); preencher `§9 Quem validou` antes do merge F11.

### RA-10 — Coverage pest 60% inicial (meta 70% final de W2)

- **Origem:** `analyze.md §7` + `constitution.md §8` (dívida 7.11 herdada).
- **Autor:** humano (constitution).
- **Justificativa:** threshold 70% imediato atrasaria implementação MVP; começar em 60% e subir gradualmente é prática comum.
- **Mitigação planejada:** dívida 7.11 registrada; CI `.github/workflows/ci.yml` usa `--min=60` inicial.
- **Materializou?** ❌ Não medido (C2 não executa pest).
- **Custo observado em C2:** zero.
- **Ação herdada C1:** manter 60% em W1B real; subir para 70% até fim de W2 como compromisso fixo; regredir abaixo de 60% bloqueia merge.

### RA-11 — ~35 stubs não materializados em F7

- **Origem:** `implement_notes.md §4 Cobertura por fase` + `quickstart.md §10 Riscos aceitos`.
- **Autor:** humano (estratégia C2 negociada com usuário em F7).
- **Justificativa:** 20 arquivos completos materializam invariantes D-E-01..D-E-06 + decisões C-NNN/D-NNN (onde a **forma** ensina mais que o **algoritmo**); 35 restantes são CRUD/UI triviais sem valor didático adicional no canônico.
- **Mitigação planejada:** cada stub tem TODO referenciando task correspondente (`T-NNN`); docblock indica origem.
- **Materializou?** ✅ **Sim (consciente)**.
- **Custo observado em C2:** zero em C2; em C1 real, ~35 tasks de implementação herdam obrigação.
- **Ação herdada C1:** executor real copia `codigo/` para projeto Laravel 12 greenfield; implementa os ~35 stubs conforme `tasks.md v2`; quickstart só roda completo após isso.

### RA-12 — Hipóteses H-1..H-5 (BMAD) `[NÃO MEDIDA — canonical C2]`

- **Origem:** `bmad.md §4.6` + `spec.md §Success Criteria` (deriva).
- **Autor:** humano (BMAD).
- **Justificativa:** hipóteses estratégicas dependem de piloto real; em C2 não há piloto.
- **Mitigação planejada:** listadas como `[NÃO MEDIDA]` em todas as fases; retrospectiva registra como dívida de instrumentação.
- **Materializou?** ✅ **Sim (consciente)** — todas as 5 ficam sem medição.
- **Custo observado em C2:** zero em C2.
- **Ação herdada C1:**
  - H-1 (abertura > 85%) → métrica `confirmacao_lembretes_lidos_total / confirmacao_lembretes_enviados_total`.
  - H-2 (no-show < 10% em 30d) → métrica `consultas_no_show_30d / consultas_total_30d`; requer linha-de-base de 7d pré-rollout.
  - H-3 (painel server-rendered OK para atendentes) → survey qualitativa + métrica `painel_tempo_medio_ate_acao`.
  - H-4 (custo R$ 0,05–0,20) → `confirmacao_custo_total_reais / lembretes_enviados_total`.
  - H-5 (botões cobrem 90%+ intents) → `respostas_botao_total / (respostas_botao_total + respostas_ambiguas_total)`.

---

## 2. Classificação por materialização

| Materializou em C2 | Riscos |
|---|---|
| ❌ Não (validação empírica ausente) | RA-01, RA-02, RA-03, RA-04, RA-05, RA-06, RA-10 |
| ✅ Sim, consciente (design da metodologia) | RA-07, RA-08, RA-09, RA-11, RA-12 |
| ✅ Sim, negativamente (custo não previsto) | **(nenhum)** |

**Leitura:** 7 riscos estratégicos ficam com **cache de confiança +1** (não materializaram; design foi suficiente). 5 riscos operacionais materializaram-se **conscientemente** — é por **design** do canônico C2, não falha. **Zero riscos materializaram-se negativamente** (nenhuma surpresa custosa).

---

## 3. Ações herdadas por projeto real derivado

Lista consolidada para o executor C1 (quem vai transformar este canônico em produto real):

### Antes do bootstrap

1. **Declarar modo C1 real** em Fase 0 (herdando `modo_execucao: C1_real` quando ADR-G-001 for aceita).
2. **Configurar `.env.example`** com `META_GRAPH_URL` (dívida 9.5).
3. **Configurar git user.email** globalmente para evitar commits com email local (dívida 7.5).

### Antes do go-live

4. **Submeter template** `lembrete_consulta_utility_v1` à Meta Business Manager; aguardar aprovação (1-24h); monitorar categoria `utility`.
5. **Cotar oficialmente** com Meta BSP custo/mensagem (RA-06); configurar alerta crítico se > R$ 0,20 (teto C-001).
6. **Instrumentar `SC-001..SC-008`** em Grafana/Prometheus/Pulse (proposta ADR-G-003).
7. **Coletar linha-de-base de 7 dias** de no-show antes do rollout (necessário para medir SC-003).
8. **Materializar os ~35 stubs** de F7 (tasks.md v2 T-NNN com status stub).
9. **Executar `pest --coverage --min=60`** contra infra real (RA-08).
10. **Executar quickstart manual** por alguém que NÃO implementou (RA-09); assinar §9 "Quem validou".
11. **Medir p99 real** do Meta Cloud API para calibrar `NOTIFICACAO_RATE_LIMIT_POR_CLINICA_MINUTO` (RA-05).

### Durante operação (primeiros 30 dias)

12. **Monitorar 3 critérios de invalidação D-002:**
    - Meta reclassificar categoria `utility` → `marketing`.
    - Custo real médio > R$ 0,30/mensagem.
    - Taxa de abertura WhatsApp < 70% em piloto.
13. **Monitorar gate D-003:** ≥3 leads multi-unidade no funil → revisão multi-tenant.
14. **Primeira retrospectiva C1** em T+30d: calibrar `SC-NNN` com dados reais; reverter `[NÃO MEDIDA]` para valor observado; promover H-1..H-5 de hipótese a fato (ou refutá-las).
15. **Subir threshold de coverage** 60% → 70% em commit dedicado até fim de W2 (RA-10).

---

## Gate de fechamento do risk log

- [x] 12 `[RISCO ASSUMIDO]` identificados ao longo do ciclo todos listados.
- [x] Cada entrada tem origem + autor humano + justificativa + mitigação + status de materialização.
- [x] Classificação por materialização em §2 é coerente com §3 do `retrospective.md`.
- [x] 15 ações herdadas para projeto C1 real enumeradas com ordem temporal (bootstrap → go-live → operação).
- [x] Zero risco materializado negativamente (nenhuma surpresa custosa).

Assinado por: Thiago Loumart (modo Arquiteto, 2026-04-23)
