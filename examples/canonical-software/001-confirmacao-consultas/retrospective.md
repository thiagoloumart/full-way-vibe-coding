---
artefato: retrospective
fase: 12
dominio: [software]
schema_version: 1
requer:
  - "1. Objetivo do módulo e resultado observado"
  - "2. KPIs (previsto vs observado)"
  - "3. Decisões revisitadas"
  - "4. Propostas de ADR global"
  - "5. Propostas de atualização de Constituição"
  - "6. Aprendizados para próximos ciclos"
---

# Retrospective — `001-confirmacao-consultas`

**Data:** 2026-04-23
**Módulo:** canônico-001 Confirmação de Consultas (D1 software, greenfield)
**Autor:** Thiago Loumart (modo Arquiteto)
**Status:** Finalizada

Referências:
- `review.md` via `.review/canonical-001-f{0..9}.md` (10 self-reviews)
- `decision_log.md` (D-001..D-003) + `adr_local_001_provedor_whatsapp.md`
- `bmad.md` v1 · `analyze.md` v1
- **Anexo:** `risk_log.md` (revisão dos 12 `[RISCO ASSUMIDO]`)
- **Tempo do ciclo:** 2026-04-18 (abertura W1B) → 2026-04-23 (merge F11), **5 dias úteis**, **11 fases (0..11)**, **14 PRs merged** (#1..#14).

> **Modo de execução:** C2 **canônico documental**. Este módulo é exemplo da skill `full-way-vibe-coding`, não produto operacional. Nenhuma fase rodou contra infraestrutura real (pest, docker, Meta sandbox). O que foi entregue: **artefato ponta-a-ponta rastreável** (15 docs + ~20 arquivos PHP completos + ~35 stubs). Este modo **afeta toda a retrospectiva** — KPIs empíricos não existem; veredictos são sobre aderência de implementação, não sobre validação de produção.

---

## 1. Objetivo do módulo e resultado observado

**Objetivo original (BMAD §1.1):**
> Pacientes de clínicas MPE brasileiras não comparecem a consultas agendadas e o slot é perdido sem tempo hábil para reagendamento, reduzindo receita por profissional-hora.

**Resultado entregue:**
- 15 artefatos `.md` fase-por-fase: `recepcao` · `bmad` · `decision_log` · `briefing` · `spec` · `clarify` · `constitution` · `plan` · `adr_local_001` · `tasks` · `analyze` · `implement_notes` · `test_plan` · `quickstart` · `retrospective` (este arquivo) + `risk_log`.
- `codigo/` com ~20 arquivos PHP completos materializando **invariantes D-E-01..D-E-06** da constituição Camada 1 + ~35 stubs referenciando tasks.
- 10 self-reviews em `.review/canonical-001-f{0..9}.md` totalizando ~100 KB de escrutínio estrutural.
- Matriz de rastreabilidade integral: 34 FRs × 7 NFRs × 63 tasks × 13 edge cases × 6 temas §5.4.

**Veredicto macro:** 🟢 **atingiu objetivo como canônico** — a skill conduziu do "problema" até o "merge" passando por todas as 11 fases sem romper nenhum gate. Como **produto operacional**: 🟡 **parcial** (C2 declarado; execução real fica para projeto derivado).

---

## 2. KPIs (previsto vs observado)

Todos os 8 `SC-NNN` da `spec.md §Success Criteria` dependem de operação real. Em C2: **zero observação empírica**.

| ID | Descrição | Previsto | Observado | Janela | Gap | Causa |
|---|---|---|---|---|---|---|
| SC-001 | ≥ 98% consultas elegíveis com lembrete em <15min | 98% | `[NÃO MEDIDA — C2]` | — | n/a | C2 não roda scheduler contra DB real |
| SC-002 | ≥ 70% consultas com confirmação explícita (H-1) | 70% | `[NÃO MEDIDA — C2]` | — | n/a | C2 não submete template Meta |
| SC-003 | No-show < 10% em 30 dias (H-2) | <10% | `[NÃO MEDIDA — C2]` | — | n/a | C2 não opera com clínica-piloto |
| SC-004 | Mediana resposta paciente < 4h | <4h | `[NÃO MEDIDA — C2]` | — | n/a | idem SC-002 |
| SC-005 | 100% respostas reconciliadas em <10s (NFR-001) | 100% / <10s | `[NÃO MEDIDA — C2]` | — | n/a | C2 não executa pipeline |
| SC-006 | ≥ 99% disparos bem-sucedidos (NFR-002) | 99% | `[NÃO MEDIDA — C2]` | — | n/a | idem SC-005 |
| SC-007 | 100% sem-resposta <4h destacadas no painel | 100% | `[NÃO MEDIDA — C2]` | — | n/a | C2 não renderiza UI contra DB |
| SC-008 | 100% eventos com timestamp+canal+ator | 100% | `[NÃO MEDIDA — C2]` | — | n/a | design materializa (D-E-03 + C-006); empírica fica para real |

**[NEEDS CLARIFICATION: instrumentar em ciclo futuro]** — projeto real derivado deste canônico deve:
1. Provisionar Grafana/Prometheus antes do go-live (não depois).
2. Instrumentar métricas Laravel Pulse ou Telescope em `MetaCloudDriver` + `DispararLembreteJob` + `DerivarStatus`.
3. Configurar dashboard de `SC-001..SC-008` com janela móvel de 30d.
4. Coletar linha-de-base de 7 dias antes do rollout para baselining de `SC-003` (no-show).

**Esta obrigação de instrumentação pré-go-live vira proposta de ADR global** (ver §4).

---

## 3. Decisões revisitadas

### D-001 — Stack Laravel 12 + Livewire 3 + PG 16 + Redis 7 + Forge

**Decisão:** pilha PHP/Laravel batteries-included para perfil MPE BR.
**O que aconteceu:** F4 Plan + F7 Implement materializaram 100% em Laravel; `composer.json` espelha a stack; CI Pint+PHPStan+Pest configurado.
**Veredicto:** 🟢 **Sustentada**.
**Ressalva:** hipóteses associadas (produtividade Laravel, latência Forge/Hetzner SP) ficam `[NÃO MEDIDA — C2]`. Critérios de invalidação (depreciação de Notifications/Queues, custo > R$ 150/mês) **não dispararam**.
**Ação:** nenhuma.

### D-002 — Caminho D (WhatsApp-only + fallback humano)

**Decisão:** WhatsApp único canal + fallback manual via atendente.
**O que aconteceu:** 4 User Stories + 34 FRs da spec v2 todos neste caminho; pipeline F4→F5→F6 completo com 4 guards (janela, idempotência, rate-limit, anonimização); US3 materializa fallback.
**Veredicto:** 🟢 **Sustentada**.
**Ressalva:** 5 hipóteses H-1..H-5 `[NÃO MEDIDA — C2]`. Nenhum critério de invalidação disparou (não houve custo real para medir, template não submetido).
**Ação:** nenhuma.

### D-003 — Single-tenant = 1 clínica

**Decisão:** MVP opera para 1 clínica/instalação; visibilidade entre papéis, não entre clínicas.
**O que aconteceu:** `clinica_id` aparece como FK em todas as tabelas (materialmente preparando para multi-tenant futuro), policies em T-063 validam visibilidade por papel; nenhum lead multi-unidade apareceu no ciclo (N=0 clientes reais em C2).
**Veredicto:** 🟢 **Sustentada**.
**Ressalva:** em ciclo real derivado, monitorar "≥3 leads multi-unidade" como gatilho de revisão para `D-NNN-REVISED` multi-tenant (bump major v2.0 da constituição).
**Ação:** nenhuma agora; gate de revisão documentado.

### ADR-L-001 — Meta Cloud API default, Z-API irmão

**Decisão:** `MetaCloudDriver` como default; `ZApiDriver` e `NoopDriver` como stubs irmãos no mesmo contrato `NotificacaoDriver`.
**O que aconteceu:** F7 implementou `MetaCloudDriver` completo (timeout/retry/idempotency-key/tradução de erros Meta); invariante D-E-02 respeitado; `NotificacaoServiceProvider` é o único ponto que referencia implementação concreta.
**Veredicto:** 🟢 **Sustentada**, passa de "Proposta" → "Aceita" via merge F4 (#8).
**Obrigações herdadas** (para execução real):
1. Monitoramento ativo da categoria `utility` no Meta Business Manager (critério de invalidação D-002).
2. Alerta crítico em `plan.md §8` quando categoria reclassificar.
3. Contract test `NotificacaoDriverContractTest` rodando em CI contra 3 implementações.

### Riscos assumidos revisitados

Tabela resumida; detalhe completo em `risk_log.md`:

| # | Risco | Materializou? | Custo | Lição |
|---|---|---|---|---|
| RA-01 | Fuso único BR | não | — | perfil MPE 1 clínica confirma hipótese; migração aditiva preserva otimização |
| RA-02 | Última resposta vale | não | — | `DerivarStatus` reducer respeita; design cobre |
| RA-03 | Multi-tenant adiado | não | — | cache de confiança +1; gate "≥3 leads" permanece ativo |
| RA-04 | Provedor único Meta | não | — | ADR-L-001 + Z-API stub pronto para reversão em 1 sprint |
| RA-05 | Rate-limit 50/min | não | — | configurável por env; sem feedback empírico |
| RA-06 | Custo Meta R$ 0,07–0,15 `[INFERÊNCIA]` | não medido | — | cotação real pré-go-live é obrigação de projeto derivado |
| RA-07 | Metas SC recalibráveis | **materializou** | — | **justifica esta retrospectiva**; SC-001..SC-008 ficam todos `[NÃO MEDIDA]` |
| RA-08 | `canonical-F8` não-execução | **materializou (consciente)** | testes de tipagem/runtime não rodados | em projeto real, Fase 8 executa pest — não pular |
| RA-09 | `canonical-F9` não-execução | **materializou (consciente)** | sutilezas de versão não reveladas | em projeto real, Fase 9 executa quickstart manual — não pular |
| RA-10 | Coverage pest 60% inicial | não medido | — | dívida 7.11 ativa; subir 70% ao fim de W2 |
| RA-11 | ~35 stubs (não completos) | **materializou (consciente)** | materializar antes do quickstart real | obrigação clara em `quickstart.md §10` |
| RA-12 | H-1..H-5 não medidas | **materializou (consciente)** | — | projeto real derivado herda obrigação de instrumentar |

**Padrão detectado:** em C2, **nenhum risco estratégico se materializou negativamente** (RA-01 a RA-06); **todos os riscos operacionais se materializaram conscientemente** (RA-08 a RA-12) — **por design da metodologia canônica**. Isto é **esperado**; o canônico é um "cobertor de confiança" que troca validação empírica por velocidade de prova metodológica.

### Pre-mortems revisitados (BMAD §3.4)

**Apenas os 3 pre-mortems do Caminho D escolhido** são relevantes:

| # | Falha prevista | Aconteceu? | Observação |
|---|---|---|---|
| D#1 | Meta reclassificar categoria "utility" → envio bloqueado | ⏸️ não exercido | template não submetido em C2; mitigação "alerta crítico em plan §8" permanece armada |
| D#2 | Z-API banido pelo WhatsApp | ✅ neutralizado por design | ADR-L-001 escolheu Meta; Z-API ficou como stub irmão, não operacional |
| D#3 | Paciente idoso sem WhatsApp → percepção "sistema não automatiza tudo" | 🟡 mitigado na técnica | US3 + P-03 (evento `sem-canal` via `paciente->temCanalDeContato()`); percepção qualitativa `[NÃO MEDIDA — C2]` |

**5 modos de falha descobertos durante o ciclo** (não previstos em BMAD):

| Modo | Descoberto em | Materializado em |
|---|---|---|
| R-04 Race anonimização-em-trânsito | F4 Plan (matriz risco) | T-052 teste race + §7.1 quickstart |
| Template Meta `REJECTED` vs `SUSPENDED` diferenciação | F7 Implement (`traduzirErro`) | `FalhaDefinitivaException categoria=template-rejeitado` |
| Correção compareceu/no-show pós-consulta | F6 Analyze (P-01/P-02) | `CorrigirMarcacao` + ajuste reducer `DerivarStatus` |
| Rate-limit escopo clínica vs global | F6 Analyze (DT-08) | `RateLimitClinicaGuard` + NFR-006 |
| Callback Meta duplicado (idempotência webhook) | F5 Tasks (T-036 derivado) | `CallbackIdempotenciaGuard` |

**Insight estrutural:** BMAD cobriu modos **estratégicos** (provedor bane / reclassifica / canal cultural); modos **técnicos** emergiram em Plan/Analyze. Não é falha do BMAD — é a **fronteira natural** entre estratégia (F0.5) e implementação (F4/F6). O gate de escudo técnico é a **Fase 6 Analyze**, e ela funcionou: capturou 9 problemas técnicos (5 remediados no mesmo PR).

---

## 4. Propostas de ADR global

Critério `fases/12_RETROSPECTIVE.md`: só vira proposta se afetar **decisões futuras de outros módulos/canônicos**.

### Proposta ADR-G-001 — "Modo de execução canônico (C1 real / C2 documental) declarado na Fase 0"

- **Contexto:** A decisão de "canônico documental" (não-execução de F8/F9) apareceu tacitamente em F7 e só virou `[RISCO ASSUMIDO]` explícito em F8/F9. Isto deveria ser declarado **na Fase 0 Recepção** para alinhar expectativa desde o início e dimensionar riscos de cada fase coerentemente.
- **Decisão proposta:** Fase 0 passa a exigir declaração `modo_execucao: C1_real | C2_documental`. Em C2, Fases 7/8/9 ficam com contrato documental declarado no template; em C1, execução real é obrigatória.
- **Alternativas:**
  - (a) Deixar como está (tácito) — contras: retrabalho de registro de `[RISCO ASSUMIDO]` em cada fase downstream; expectativa mal calibrada.
  - (b) Criar modo C3 (híbrido) para casos em que código é real mas testes são doc — não convencido da utilidade; adiar até aparecer caso concreto.
- **Consequências:** templates de F7/F8/F9 ganham seção `Contrato desta fase (modo: CN)`; review por PR checa coerência com modo declarado.
- **Camada afetada:** processo da skill (não afeta Camada 1 da constituição dos módulos).
- **Dono proposto:** Thiago Loumart (autor da skill).

### Proposta ADR-G-002 — "Matriz Pre-mortem × Mitigação obrigatória na Fase 6 Analyze"

- **Contexto:** Os 3 pre-mortems de D-002 foram registrados em F0.5 mas suas mitigações ficaram dispersas em F4/F5/F7 (ex: D#1 mitigado via alerta plan.md §8; D#3 mitigado via evento `sem-canal` em P-03). A Fase 6 Analyze deveria **fechar o loop** com matriz explícita "Pre-mortem × Task/Artefato que mitiga".
- **Decisão proposta:** `fases/06_ANALYZE.md` ganha matriz obrigatória onde cada pre-mortem do BMAD §3.4 é cruzado com (task | arquivo de plan | ADR-L) que o mitiga. Pre-mortem sem mitigação vira gap bloqueador de severidade alta.
- **Alternativas:**
  - (a) Manter implícito — contras: pre-mortem fica só como ornamento retórico; não tem garantia de virar código.
  - (b) Exigir matriz no BMAD (F0.5) — contras: prematuro; mitigação técnica só emerge em F4+.
- **Consequências:** F6 Analyze ganha linha de teste adicional; F0.5 BMAD não muda.
- **Camada afetada:** processo da skill.
- **Dono proposto:** Thiago Loumart.

### Proposta ADR-G-003 — "Instrumentação de SC-NNN obrigatória antes do go-live em projeto real"

- **Contexto:** Em C2 canônico, SC-001..SC-008 ficaram `[NÃO MEDIDA]`. Em projeto real derivado, **se a instrumentação não for obrigatória antes do go-live**, a retrospectiva seguinte repetirá o mesmo padrão. O "cache de confiança" deste canônico não transfere para o projeto real.
- **Decisão proposta:** `checklists/pre-merge.md` ganha item bloqueador para projetos C1 real: *"SC-NNN instrumentados com métricas coletáveis antes do go-live — dashboard configurado; linha-de-base de 7 dias coletada"*. Em C2 documental, item fica marcado como "obrigação do projeto derivado".
- **Alternativas:**
  - (a) Deixar para Fase 12 Retrospective detectar ausência — contras: tarde demais; go-live já aconteceu.
  - (b) Instrumentação opcional — contras: sabemos como termina.
- **Consequências:** projeto C1 real que não instrumentar bloqueia merge F11; projeto C2 herda obrigação como nota de aprendizado transversal.
- **Camada afetada:** processo da skill (`checklists/pre-merge.md`).
- **Dono proposto:** Thiago Loumart.

---

## 5. Propostas de atualização de Constituição

**Nenhuma para a constituição do módulo `001-confirmacao-consultas`.**

Camada 1 (D-E-01..D-E-06) foi respeitada integralmente e empiricamente sustentada **via implementação**; Camada 2 (stack) já incorporou ADR-L-001 em v1.1; nenhum aprendizado deste ciclo justifica bump de v1.1 para v1.2 nesta constituição.

**Para a skill-constituição (não é a mesma coisa — é o próprio manual operacional):** as 3 propostas de ADR-G em §4 acima alteram `fases/00_RECEPCAO.md`, `fases/06_ANALYZE.md` e `checklists/pre-merge.md`. Se as 3 forem aceitas, `SKILL.md §4 Fluxo operacional (mapa mestre)` ganha nota sobre "modo de execução declarado em F0".

---

## 6. Aprendizados para próximos ciclos

### 6.1 Faríamos diferente

1. **Declarar modo C1/C2 na Fase 0**, não tacitamente em F7. Poupa retrabalho de registro de `[RISCO ASSUMIDO] canonical-FN` em cada fase downstream. Proposta ADR-G-001.
2. **BMAD pre-mortem técnico obrigatório (≥1)** além dos estratégicos — teria antecipado R-04 (race anonimização) que só apareceu em F4. Critério: "Se o caminho escolhido falhar por motivo técnico em 30d, qual será?"
3. **Rebase entre PRs stacked mais cedo** — perdi ~15 min resolvendo rebases encadeados (F8→F9→F10) que poderiam ter sido antecipados com `gh pr merge --rebase` policy ou ferramenta de stacked PRs (Graphite/Spr).
4. **Instrumentação obrigatória antes do go-live** (proposta ADR-G-003). Não instrumentar é mais caro que instrumentar — sempre.
5. **Branch author email** (dívida 7.5) — configurar `git config user.email` no bootstrap do projeto para evitar commits com email errado herdados em 8+ PRs.

### 6.2 Faríamos igual sem pensar duas vezes

1. **BMAD antes de spec.** Pre-mortems filtraram caminhos A/B/C antes de gastar horas em spec. Manual §5 proíbe "faz um sistema que..." — BMAD é o enforcement.
2. **Doc-first em F7 Implement** (implement_notes antes do código). Disciplina Manual §18 materializada; ajudou rastreabilidade por arquivo via docblock `Origem: FR-NNN · D-NNN · C-NNN · D-E-NN`.
3. **Template de review minimalista** (`templates/review.md` com `requer:` schema). Permitiu 10 self-reviews produtivos sem paralisia de checklist.
4. **Analyze como gate técnico** (F6). 5 gaps detectados e remediados no mesmo PR (#10); evitou bugs latentes em F7.
5. **Branch por fase + PR separado** (F0→F10 em 11 PRs distintos). Histórico legível; rollback cirúrgico possível; self-review por PR naturalmente focado.
6. **Constitution bicamada** (Camada 1 invariantes × Camada 2 convenções). Absorveu ADR-L-001 com bump minor sem disruptar; Camada 1 ficou intocada.
7. **`.review/canonical-001-f{N}.md` como padrão** (um review por PR, schema `requer:`). Proposta: extrair para `templates/review-por-pr.md` no repo da skill — §6.4 abaixo.

### 6.3 O que não sabíamos e vale documentar

1. **Stubs em C2 deveriam ter docblock `@stub-reason`** — sem isso, autor futuro não distingue "stub consciente (por estratégia C2)" de "stub esquecido". Proposta: `domains/software.md` ganha convenção PHP `@stub-reason: <tasks.md ref>`.
2. **Gates conversacionais cansam em auto-mode.** Se o humano sinaliza "confia no que você fizer" (3× consecutivas neste ciclo F12), concentrar blocos (12.c+12.d+12.e em um documento) reduz fricção sem perder rigor. Registrado como feedback de memória.
3. **Em C2, a prova de valor é metodológica, não empírica.** Confundir os dois gera expectativa errada sobre o que o canônico entrega. A retrospectiva precisa declarar isto no §1 (feito aqui).
4. **`[NÃO MEDIDA — canonical C2]` é melhor marcador que `[NEEDS CLARIFICATION]` neste contexto** — o segundo sugere que falta algo fácil de resolver; o primeiro explicita a natureza da não-medição.
5. **Rate-limit de 50/min é "chute educado"** (RA-05) — em projeto real, DevOps precisa medir p99 real de Meta Cloud API e ajustar. Projetar estimativa como constante em `.env` desde o início foi boa decisão; configurar sem medir seria dívida de origem.

### 6.4 Impacto em artefatos da skill

- [ ] **`templates/review-por-pr.md`** — criar novo template (extração do padrão `.review/canonical-001-f{N}.md` deste ciclo). Atualmente `templates/review.md` é review por módulo/branch; proposta é ter os dois.
- [ ] **`fases/00_RECEPCAO.md`** — adicionar seção "Modo de execução (C1 real / C2 documental)" (depende de ADR-G-001).
- [ ] **`fases/06_ANALYZE.md`** — adicionar matriz "Pre-mortem BMAD × Mitigação" obrigatória (depende de ADR-G-002).
- [ ] **`fases/00_5_BMAD.md`** — exigir ≥1 pre-mortem técnico além dos estratégicos.
- [ ] **`checklists/pre-merge.md`** — adicionar gate "SC-NNN instrumentados antes do go-live (C1)" (depende de ADR-G-003).
- [ ] **`domains/software.md`** — convenção PHP `@stub-reason: <tasks.md ref>` para stubs conscientes em C2.
- [ ] **`protocolos/antialucinacao.md`** — adicionar marcador `[NÃO MEDIDA — canonical C2]` ao lado de `[INFERÊNCIA]` / `[NEEDS CLARIFICATION]` / `[DECISÃO HUMANA]` / `[RISCO ASSUMIDO]`.

Todos são **propostas**, não mudanças aplicadas neste PR. Implementação posterior fica como dívida da skill W2.

---

## Gate de fechamento

- [x] Cada `D-NNN` + ADR-L do `decision_log.md` tem veredicto (§3).
- [x] Cada `[RISCO ASSUMIDO]` foi revisitado (§3 Riscos + anexo `risk_log.md`).
- [x] KPIs comparados (previsto vs observado) — todos marcados `[NÃO MEDIDA — canonical C2]` com justificativa estrutural.
- [x] 3 propostas de ADR global claras com contexto + alternativas + consequências.
- [x] Aprendizados estruturados em §6.1/6.2/6.3/6.4 — 7 artefatos da skill identificados como candidatos a ajuste.
- [x] Pre-mortems do BMAD §3.4 confrontados com realidade; 5 modos de falha novos descobertos no ciclo registrados.
- [x] Humano assinará fechamento via merge do PR `w1b/f12-retrospective`.

**Veredicto do Arquiteto:** 🟢 retrospectiva fechada. Ciclo canônico-001 completo.

Assinado por: Thiago Loumart (modo Arquiteto, 2026-04-23)
