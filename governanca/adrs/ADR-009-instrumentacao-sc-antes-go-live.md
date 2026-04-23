---
artefato: adr
fase: null
dominio: [any]
schema_version: 1
adr_id: ADR-009
status: Proposta
camada_afetada: 2
data: 2026-04-23
autor: Thiago Loumart
requer:
  - "Contexto"
  - "Decisão"
  - "Alternativas consideradas"
  - "Consequências"
  - "Relação com Constituição"
---

# ADR-009 — Instrumentação de `SC-NNN` obrigatória antes do go-live em projetos `C1_real`

**Status:** Proposta
**Data:** 2026-04-23
**Autor:** Thiago Loumart
**Camada afetada:** 2 (processo da skill — `checklists/pre-merge.md` + `fases/11_MERGE.md` + template de instrumentação)
**Bump de Constituição:** minor da skill (sincronizado com ADR-007, ADR-008 na safra v1.3)

**Depende de:** ADR-007 Aceita (distinção `C1_real` × `C2_documental` necessária para esta ADR ter escopo bem definido).

**Origem:** `examples/canonical-software/001-confirmacao-consultas/retrospective.md §4` proposta ADR-G-003.

---

## Contexto

No canônico-001, os 8 `SC-NNN` da `spec.md §Success Criteria` ficaram todos `[NÃO MEDIDA — canonical C2]`. Isto é **esperado** em C2 (não-execução declarada). O risco é outro: em projeto **real derivado deste canônico** (ou qualquer outro C1), **se a instrumentação não for exigida antes do go-live, a retrospectiva seguinte repete o padrão** — SC-NNN ficam "por medir" indefinidamente.

**Padrão conhecido em produto real:**
1. `spec.md` define 8 SC mensuráveis.
2. F7 Implement escreve código sem `metrics.counter('confirmacao_lembretes_enviados_total')`.
3. F8 Test testa comportamento mas não observabilidade.
4. F9 Quickstart executa caminho manual mas não valida dashboard.
5. F11 Merge passa — produto vai para produção.
6. **Pergunta "qual é nosso no-show?" em T+30d** → ninguém sabe; não foi instrumentado.
7. Fase 12 Retrospective registra `[NEEDS CLARIFICATION: instrumentar em ciclo futuro]` — **eternamente**.

Este padrão é o que `filosofia.md §6` chama de "melhor instrumentar antes que medir depois" (implícito, não literal). O retrospective do canônico-001 §3 RA-07 **registra este risco** ("Metas SC recalibráveis" — é precisamente a falta de instrumentação).

Em C2 documental, não há go-live — ADR não aplica. **Por isto depende de ADR-007** que define `C1_real`.

## Decisão

**Em projetos com `modo_execucao: C1_real`, `checklists/pre-merge.md` ganha item bloqueador:**

> **"Instrumentação de `SC-NNN` e KPIs primários completa antes do go-live."**
>
> Para cada `SC-NNN` da `spec.md §Success Criteria`:
> - [ ] Métrica instrumentada no código (Prometheus / CloudWatch / Pulse / Telescope / equivalente).
> - [ ] Dashboard configurado com janela móvel apropriada (30d default).
> - [ ] Linha-de-base coletada (≥7 dias de produção ou proxy staging) quando SC depende de baseline (ex: redução de no-show).
> - [ ] Alerta configurado quando SC cruza threshold documentado em `spec.md` ou `constitution.md` Camada 2.
>
> Exceção: SC derivado 100% de outro SC já instrumentado (ex: `SC-C = SC-A / SC-B`) — suficiente declarar derivação em `plan.md`.

**Em projetos `C2_documental`**: o item fica marcado explicitamente como *"obrigação herdada por projeto C1 derivado — registrar em `risk_log.md`"* (padrão já seguido pelo canônico-001 `risk_log.md §3`).

**Operacionalmente:**

1. `checklists/pre-merge.md` ganha nova seção "Instrumentação (C1 real apenas)" com os 4 sub-itens acima.
2. `fases/11_MERGE.md` "Gate de merge" referencia o checklist.
3. `templates/plan.md` ganha seção `§N Plano de instrumentação` com tabela `SC-NNN × métrica × dashboard × alerta`.
4. `templates/retrospective.md §2 KPIs` ganha linha "Instrumentação presente?" para cada SC, com coluna que não permite `[NEEDS CLARIFICATION: instrumentar em ciclo futuro]` em projetos C1 — force `[LACUNA — bloqueou merge?]` ou valor real.

## Alternativas consideradas

### (a) Instrumentação opcional (status quo)

**Prós:** zero overhead adicional pré-go-live.
**Contras:**
- Observado: leva a retrospective `[NEEDS CLARIFICATION: instrumentar em ciclo futuro]` indefinidamente.
- Primeiro ciclo que pagar dívida custa 3-5× mais que ter instrumentado do zero (retrofit de métricas em código que não foi pensado para isso).

**Motivo de descarte:** falha conhecida em engenharia de software real (literatura SRE).

### (b) Exigir instrumentação já em F7 Implement

**Prós:** força instrumentação no momento mais barato (escrevendo código).
**Contras:**
- **Prematuro para todos os SC** — alguns SC só ficam claros em F9 Quickstart (ex: latência p99 real medida só em ambiente staging).
- Pode virar "tick-box" se exigido cedo demais sem ver o sistema rodando.

**Motivo de descarte:** F7 é cedo para TODOS os SC; F11 é certo — é o gate de go-live, onde ausência de métrica é realmente bloqueador. Porém, `plan.md §N Plano de instrumentação` escrito em F4 já sinaliza a obrigação cedo (sem exigir código).

### (c) Aceitar 1 SC por vez como dívida

**Prós:** flexibilidade; alguns SC são difíceis de instrumentar.
**Contras:**
- Flexibilidade vira permissividade; "1 por vez" vira "todos por vez".
- Sem limite claro, não há gate real.

**Motivo de descarte:** gates sem limite numérico são não-gates. Melhor 100%-obrigatório com exceção explícita (SC derivado) que 80%-obrigatório-na-prática.

### (d) Exigir dashboards mas não alertas

**Prós:** simpler; alertas são "operação", não "definição de produto".
**Contras:**
- Dashboard sem alerta → ninguém olha → equivale a não ter.
- Literatura SRE consistente: instrumentação **começa** em alerta, dashboard é apoio.

**Motivo de descarte:** economia falsa. Alerta é onde instrumentação **ativa** — remover é reduzir ROI quase à zero.

## Consequências

### Positivas
- **Quebra o ciclo** `[NÃO MEDIDA]` → `[NEEDS CLARIFICATION]` → dívida eterna.
- **Linha-de-base pré-rollout** permite medição de impacto real (ex: redução de no-show contra baseline histórico, não contra zero).
- **Retrospective com dados** — §2 KPIs ganha conteúdo empírico desde o primeiro ciclo C1.
- **SRE-grade** desde o MVP — hábito certo instaurado cedo.
- **Compliance auditable** — projetos regulados (D2 compliance) herdam trilha observável natural.

### Negativas / trade-offs
- **Custo de infra de observabilidade** em MVP — clínica MPE R$ 30/mês Hetzner pode precisar +R$ 15/mês Grafana Cloud free tier. Aceitável.
- **Tempo de setup** — estimado +4-8h em F7+F8 dedicadas a instrumentação real. Compensado pela redução drástica de debug em produção.
- **Risco de instrumentação ruim** (métrica errada, cardinalidade alta) — vira observabilidade do próprio sistema de observabilidade. Mitigável com template `plan.md §N`.

### Migração necessária
- **Projetos C1 em curso (W1+):** aplica a partir da próxima F11 Merge. Projeto que já fez merge sem instrumentação fica com dívida registrada em `risk_log.md` — backfill em ciclo de manutenção.
- **Canônico-001 (C2):** nenhuma mudança (ADR não aplica a C2).
- **Templates:** `plan.md`, `pre-merge.md`, `retrospective.md` adequados em W2.

### Novas obrigações
- F11 Merge não fecha em C1 sem instrumentação.
- F4 Plan ganha `§N Plano de instrumentação` obrigatório em C1.
- Retrospective em C1 não aceita `[NEEDS CLARIFICATION: instrumentar em ciclo futuro]` como valor — força `[LACUNA]` (bloqueio) ou valor real.

## Relação com Constituição

- Esta ADR **altera** processo da skill (`checklists/pre-merge.md` + `fases/11_MERGE.md` + `templates/plan.md` + `templates/retrospective.md`), **não altera** constituição de módulo individual.
- Esta ADR **NÃO altera** Camada 1 de nenhum módulo — ela cria **obrigação de processo** sobre projetos C1, não invariante de produto.
- Esta ADR **NÃO altera** Camada 2 de nenhum módulo (observabilidade de um módulo específico é decisão local).
- Bump semântico da skill: minor, sincronizado com ADR-007 e ADR-008 na safra v1.3.

## Relação com outros artefatos

- **ADRs relacionadas:** ADR-007 (dependência; define `C1_real`), ADR-008 (mesma safra; escudo técnico complementar).
- **Módulos impactados imediatamente:** nenhum em C2; primeiro C1 real derivado do canônico-001 (se e quando acontecer) é o primeiro sob esta ADR.
- **`risk_log.md`** passa a ser onde dívidas de instrumentação ficam visíveis em C2 (já acontece organicamente no canônico-001 §3 ações herdadas #6).

## Plano de reversão

1. Criar `ADR-NNN-REVERSED` com motivo (ex: custo de observabilidade bloqueia MVPs de perfil MPE; exigência virou barreira de entrada).
2. Downgrade do item em `checklists/pre-merge.md` para recomendação.
3. Projetos que já instrumentaram mantêm — custo de arrepender-se é zero.
4. Manter linha em `retrospective.md §2 KPIs` como coluna informativa (não bloqueante).

## Aprovação

| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor | Thiago Loumart | 2026-04-23 | ✓ |
| Revisor 1 | — | — | pendente |
| Revisor 2 (se Camada 1) | — | — | n/a |
| Compliance | — | — | n/a |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| 2026-04-23 | Proposta | Criada em `w2/adr-propostas-canonical-001-retro` a partir de `retrospective.md §4 ADR-G-003` do canônico-001. |
| (data futura) | Aceita | A definir. Aceitação deve ser posterior a ADR-007 (dependência). |
