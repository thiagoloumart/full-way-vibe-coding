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

# Review — Fase 0.5 BMAD do canônico `001-confirmacao-consultas`

**Branch:** `w1b/f0.5-bmad`
**PR:** [#3](https://github.com/thiagoloumart/full-way-vibe-coding/pull/3)
**Data:** 2026-04-18
**Revisor:** Thiago Loumart (self-review, time=1)
**Status:** Aprovada

---

## 1. Escopo do diff

- **Commits:** 1 (`9297ae4` — `feat(canonical-001): Fase 0.5 BMAD — decomposição + modelagem + Caminho D + D-001..D-003`).
- **Linhas alteradas:** +460 / -1 (2 arquivos novos + 1 linha de status no README).
- **Arquivos:** 3 — `examples/canonical-software/001-confirmacao-consultas/{bmad.md,decision_log.md,README.md}`.

Escopo deliberadamente contido a **uma fase do ciclo** (Fase 0.5). Próxima fase (Briefing) será PR separado. Mantém a disciplina "1 PR por fase" estabelecida pelo PR #2 e pelo `progress.md §W1 track B`.

## 2. Arquivos alterados por categoria

| Categoria | Arquivos |
|---|---|
| Código de produção | — |
| Migrations | — |
| Testes | — |
| Docs — artefato SDD do módulo | `examples/canonical-software/001-confirmacao-consultas/bmad.md` · `examples/canonical-software/001-confirmacao-consultas/decision_log.md` |
| Docs — README do exemplo | `examples/canonical-software/001-confirmacao-consultas/README.md` (1 linha — status 🟡 Draft) |
| Configuração | — |
| Infra | — |

## 3. Verificações mínimas (Manual §17)

- [x] **Arquivos alterados** — exatamente 3, todos previstos no plano anunciado em `progress.md §W1 track B` e no sumário do PR. Nenhum fora do escopo. ✅
- [x] **Migrations criadas** — n/a. Artefato doc, sem schema. ✅
- [x] **Testes criados** — n/a. Artefato doc; próxima fase que exige teste é Fase 8 Test, múltiplos PRs adiante. Validação desta fase é **lint do artefato**. ✅
- [x] **Rotas alteradas** — n/a. ✅
- [x] **Policies / permissões alteradas** — n/a. Permissão é `D-NNN` futura em Clarify (C-002). ✅
- [x] **Integrações externas alteradas** — n/a. Meta Cloud API / Z-API são **menção estratégica** em `bmad.md §3.1` e `decision_log.md D-002`, não código. ✅

## 4. Aderência à constituição

- [x] **Estrutura de pastas** — `examples/canonical-software/NNN-modulo/{bmad.md,decision_log.md}` segue o path canônico declarado em `SKILL.md`, `fases/00_5_BMAD.md` e usado pelo PR #2 para o mesmo diretório.
- [x] **Convenção de markdown preservada** — headings numerados, tabelas com `|`, bullets, bloco `>` para citação; mesma estética de `recepcao.md` e de `specs/m1-lint/*.md`.
- [x] **Nenhuma lib nova introduzida** — doc-only.
- [x] **Marcadores epistêmicos usados corretamente:**
  - `[INFERÊNCIA]` em 6 pontos (taxas de abertura, custos por notificação, percentual de carteira sem WhatsApp, categoria Meta, provável comportamento do caminho A, frequência de no-show em MPE BR).
  - `[RISCO ASSUMIDO]` em 7 pontos (concentração Meta/Z-API; pacientes sem WhatsApp; custo por mensagem; dependência regulatória; amarração Laravel; acoplamento Livewire; necessidade futura de multi-tenant).
  - `[NEEDS CLARIFICATION: custo-alvo por notificação]` preservada de Fase 0 como C-001 para Fase 3.
  - `[DECISÃO HUMANA]` implícita em cada D-NNN com campo `Autor: humano`; assinatura formal via merge do PR.
- [x] **Consistência cruzada:**
  - `recepcao.md §6` exigiu: problema-raiz 1 frase, atores, entidades, §5.4 candidatas, ≥2 caminhos. **Tudo presente** em `bmad.md §1.1, §2.1, §2.3, §2.6, §3.1`. ✅
  - `recepcao.md §4` listou stack como `[INFERÊNCIA]` aguardando `D-001`. **Formalizada** em `decision_log.md D-001`. ✅
  - `recepcao.md §5` registrou 3 hipóteses → **expandidas** em `bmad.md §4.6` para 5 hipóteses (H-1..H-5) sem contradizer as originais. ✅
  - `fases/00_5_BMAD.md §Saídas` lista 3 saídas obrigatórias (`bmad.md`, `decision_log.md`, contrato explícito para Briefing) → todas presentes (`bmad.md §5`). ✅
  - `checklists/qualidade-bmad.md` — **34 checkitems** em 8 blocos; todos ✅ exceto "humano leu e aprovou" que é a própria assinatura do merge deste PR.
- [x] **Lint passa em 2 artefatos:**
  - `python3 -m harness.scripts.lint_artefato examples/canonical-software/001-confirmacao-consultas/bmad.md` → `OK`
  - `python3 -m harness.scripts.lint_artefato examples/canonical-software/001-confirmacao-consultas/decision_log.md` → `OK`
- [x] **Heading-vs-requer** — 5 itens de `requer:` em `bmad.md` e 2 itens em `decision_log.md` batem literalmente com os headings `## N. …` do corpo.

## 5. Sinal de regras de negócio sensíveis (Manual §5.4)

Fase 0.5 **pode decidir** regras §5.4 quando a decisão vier como parte do caminho estratégico (ver `fases/00_5_BMAD.md §Escopo`). Regras decididas aqui ficam com `D-NNN` assinada; regras ainda ambíguas viram candidatas formais de Clarify.

Aderência completa:

- [x] **3 decisões `D-NNN` tomadas neste PR, nenhuma em silêncio:**
  - **D-001 (stack)** — `[INFERÊNCIA]` de Fase 0 formalizada com 4 alternativas (Laravel/Node/Rails/Django), descartes com motivo, 2 riscos aceitos, 3 critérios de invalidação, 2 hipóteses. Autor humano via merge.
  - **D-002 (Caminho D)** — 4 alternativas A/B/C/D comparadas via matriz trade-offs + pre-mortem, descartes individuais com motivo técnico, 4 riscos aceitos marcados, 5 critérios de invalidação, 5 hipóteses. Autor humano via merge.
  - **D-003 (visibilidade single-tenant MVP)** — 3 alternativas (single/multi-tenant/shared-nothing), descarte com motivo, 1 risco aceito, 2 critérios de invalidação, 1 hipótese. Autor humano via merge.
- [x] **5 regras §5.4 aplicáveis mas não decididas aqui** foram transferidas explicitamente para Clarify:
  - **Permissão** → C-002 (novo no radar; detectado em BMAD, não estava em recepcao.md §6).
  - **Deleção LGPD** → C-003 (novo no radar; idem).
  - **Expiração de silêncio** → C-004 (estava em recepcao.md §6; mantido).
  - **Histórico** → C-005 (estava em recepcao.md §6; mantido).
  - **Auditoria** → C-006 (estava em recepcao.md §6; mantido).
- [x] **Cobrança e Estorno** declarados explicitamente como **fora de escopo** (não são omissões silenciosas).
- [x] **Anti-viés de confirmação** (`checklists/qualidade-bmad.md §Analyze`): Caminho D (escolhido) tem 2 células 🟡 reconhecidas (`bmad.md §3.2`) — não é toda-verde.

Nada escondido; nada decidido sem humano.

## 6. Observações / pontos estranhos

- **Acréscimo ao radar §5.4 vs. recepcao.md §6.** BMAD detectou 2 regras sensíveis novas (**permissão** e **deleção-LGPD**) que não estavam na ponte original. Tratei como transferência para Clarify com aviso explícito (`bmad.md §2.6` nota final + `decision_log.md` tabela §5.4). Ação correta — a ponte pré-fase lista "candidatas conhecidas", não "lista exaustiva"; BMAD pode e deve complementar.
- **Escopo de D-001 vs. princípio "Comportamento > arquitetura"** (`fases/00_5_BMAD.md §Princípios`). O `bmad.md` propriamente dito **não menciona framework / ORM / banco**. Stack vive só no `decision_log.md D-001`, que é precisamente onde ADRs técnicas moram. Separação preservada.
- **Autor email** mantém dívida `#7.5` (`thiagomartins@192.168.0.11` local). Mesma trilha dos PRs anteriores; rebase `--reset-author` postergado para antes de W4 conforme `.review/progress-reconciliacao.md §7`.
- **Dívida `#7.1` (template recepcao.md)** permanece aberta. Decisão adiada: `templates/recepcao.md` ainda não foi criado. Isto **não bloqueou** esta fase porque `templates/bmad.md` e `templates/decision_log.md` já existiam e foram usados como base — mas volta a ser questão no próximo canônico (D2 processo ou outro D1). Reavaliar ao iniciar 2º canônico.

## 7. Dívidas conhecidas / TODO

- [ ] **7.1 — Formalizar `templates/recepcao.md`?** Pendente desde o self-review do PR #2. Não bloqueou Fase 0.5. Reavaliar ao abrir 2º canônico. — Thiago; 2º canônico (D2 em W2 ou D1 seguinte).
- [ ] **7.2 — C-001 (custo-alvo por notificação)** herdada de Fase 0; continua `[NEEDS CLARIFICATION]`. Precisa virar entrada formal em `clarify.md` na Fase 3. — Thiago; Fase 3.
- [ ] **7.3 — C-002 a C-006 (5 regras §5.4 transferidas)** precisam virar entradas `C-NNN` com decisão humana explícita em `clarify.md`. — Thiago; Fase 3.
- [ ] **7.4 — H-1 a H-5 (5 hipóteses)** precisam de plano de validação: H-1/H-2/H-4 em Briefing (2–3 clínicas reais); H-3 em Clarify (2 atendentes); H-5 em Spec/Clarify. — Thiago; Fase 1 e Fase 3.
- [ ] **7.5 — Rebase `--reset-author`** antes de W4, herdado de `.review/progress-reconciliacao.md §7`. Mais um commit afetado agora (`9297ae4`).
- [ ] **7.6 — Escolha final Meta Cloud API vs. Z-API** postergada para Fase 4 Plan com ADR local. Não bloqueia Clarify nem Constituição. — Thiago; Fase 4.

## 8. CRM / Agentes / SaaS (se aplicável — Manual §29)

Parcialmente aplicável a partir deste PR (Fase 7 Implement fará o pleno). BMAD já esboçou — em `bmad.md §2.4` (fricções) e `bmad.md §3.4` (pre-mortem do Caminho D) — os riscos operacionais de uma automação de lembrete multicanal (latência de provedor, template suspenso, duplicidade de envio). Quando Fase 7 tocar o driver de notificação, os 9 campos de automação §29 (gatilho, contexto, decisão, ação, bloqueio, fallback, log, critério de sucesso, falso positivo) serão obrigatórios no `plan.md` e `tasks.md`.

## 9. Resultado de testes

- [x] **Lint de `bmad.md`:** `OK` (zero warnings, zero errors).
- [x] **Lint de `decision_log.md`:** `OK` (zero warnings, zero errors).
- [x] **Lint de `README.md` do exemplo:** doc livre — falso-negativo `FRONTMATTER_AUSENTE` esperado e documentado desde PR #2.
- [x] **Checklist `checklists/qualidade-bmad.md`** — 34 checkitems em 8 blocos auditados manualmente; 33 ✅ + 1 pendente de merge (assinatura humana).
- [x] **Gate de saída `fases/00_5_BMAD.md`** — 10 critérios ✅ conforme `bmad.md §Gate de saída da Fase 0.5`.
- [x] **Consistência cruzada:**
  - `bmad.md §5` Contrato para Briefing bate 1:1 com §1.1, §2.1, §2.2, §4.1, §4.3 (mesmo problema, mesmos atores, mesmo fluxo, mesmo caminho, mesmos descartes).
  - `decision_log.md` D-001/D-002/D-003 são referenciadas em `bmad.md §2.6` e `bmad.md §4` sem contradição.
  - `recepcao.md §5` 3 hipóteses ↔ `bmad.md §4.6` 5 hipóteses: H-1/H-2 são as originais de recepcao.md §5.1; H-3/H-4/H-5 são novas (aditivas, não contradizem).

## 10. Veredicto

- [x] ✅ **Aprovada** — pode mergar.

Escopo contido (3 arquivos, 1 fase), lint verde em 2 artefatos, marcadores epistêmicos aplicados consistentemente, 3 decisões estratégicas registradas com descartes obrigatórios e riscos aceitos, 5 regras §5.4 transferidas para Clarify com integridade, anti-viés de confirmação satisfeito, contrato explícito para Briefing preenchido, 5 hipóteses com plano de validação mapeado. Dívidas conhecidas têm dono e prazo. Mergar abre o próximo PR: **Fase 1 Briefing** no mesmo diretório.

Assinado por: Thiago Loumart (self-review, 2026-04-18)
