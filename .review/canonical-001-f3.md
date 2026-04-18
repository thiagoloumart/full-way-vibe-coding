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

# Review — Fase 3 Clarify do canônico `001-confirmacao-consultas`

**Branch:** `w1b/f3-clarify`
**PR:** [#6](https://github.com/thiagoloumart/full-way-vibe-coding/pull/6)
**Data:** 2026-04-18
**Revisor:** Thiago Loumart (self-review, time=1)
**Status:** Aprovada

---

## 1. Escopo do diff

- **Commits:** 1 (`96556cc` — `feat(canonical-001): Fase 3 Clarify — resolve C-001..C-006 + atualiza spec`).
- **Linhas alteradas:** +356 / -37.
- **Arquivos:** 3 — `examples/canonical-software/001-confirmacao-consultas/{clarify.md,spec.md,README.md}`.

Esta é a primeira fase do canônico que **modifica** um artefato de fase anterior (spec.md). Isso é **exigido pelo contrato de Fase 3** (`fases/03_CLARIFY.md §Saídas`): "spec.md atualizado com as decisões incorporadas (os marcadores `[NEEDS CLARIFICATION]` desaparecem)". Não é retrabalho — é herança obrigatória.

## 2. Arquivos alterados por categoria

| Categoria | Arquivos |
|---|---|
| Código de produção | — |
| Migrations | — |
| Testes | — |
| Docs — artefato SDD novo | `examples/canonical-software/001-confirmacao-consultas/clarify.md` |
| Docs — artefato SDD atualizado | `examples/canonical-software/001-confirmacao-consultas/spec.md` (v2 pós-Clarify) |
| Docs — README do exemplo | `examples/canonical-software/001-confirmacao-consultas/README.md` |
| Configuração | — |
| Infra | — |

## 3. Verificações mínimas (Manual §17)

- [x] **Arquivos alterados** — exatamente 3, todos previstos. ✅
- [x] **Migrations criadas** — n/a. ✅
- [x] **Testes criados** — n/a. Spec atualizada continua sendo **base** para testes da Fase 8. ✅
- [x] **Rotas alteradas** — n/a. ✅
- [x] **Policies / permissões alteradas** — aplicável: matriz de permissões foi **formalizada** conforme C-002. Nenhuma decisão silenciosa — opção B (atendente + flag) escolhida entre 4 alternativas com justificativa. ✅
- [x] **Integrações externas alteradas** — n/a. Limite de retry (C-004) e custo-alvo (C-001) são regras de negócio, não contrato técnico. ✅

## 4. Aderência à constituição

- [x] **Estrutura de pastas** — `examples/canonical-software/NNN-modulo/clarify.md` segue o path canônico.
- [x] **Convenção de markdown preservada** — headings numerados, tabelas (perguntas + opções + prós/contras + impacto), consistência com os 4 artefatos anteriores.
- [x] **Nenhuma lib nova introduzida** — doc-only.
- [x] **Zero decisão técnica dentro do clarify.md:**
  - ✅ "WhatsApp", "SMS" e "e-mail" aparecem como canais (linguagem de negócio).
  - ✅ "Meta Cloud API" aparece em contexto comercial/regulatório (crit. invalidação) — não como SDK.
  - ✅ Nenhum ORM, framework, tabela, endpoint.
  - ✅ Stack (D-001) referenciada **apenas** como contexto de viabilidade em C-001 (custo compatível com provedor preferido).
- [x] **Marcadores epistêmicos:**
  - `[INFERÊNCIA]` preservado em 3 pontos do clarify.md (preço Meta 2026, prazo CFM comunicações 5 anos, horário cultural BR).
  - `[NEEDS CLARIFICATION]` **zerado** em `spec.md` ✅ (verificado por grep — só menção como meta-descrição no Gate).
  - `[DECISÃO HUMANA]` **zerado** em `spec.md` ✅ (verificado por grep).
  - `[RISCO ASSUMIDO]` preservado em FR-016 (última resposta vale) e edge case fuso único — conscientemente.
- [x] **Rastreabilidade da spec atualizada:**
  - **FR-010/011/028** agora citam `C-004` na origem (além de briefing/bmad).
  - **FR-017** agora cita `C-005`.
  - **FR-018** agora cita `C-006`.
  - **FR-030/031** agora citam `C-002`.
  - **FR-033** agora cita `C-003`.
  - **NFR-003/004** consolidados com referências de C-003/C-006.
  - **NFR-007 novo** cita `C-001`.
  - Nenhum FR órfão pós-atualização.
- [x] **Coerência com decisões anteriores:**
  - C-001 respeita crit. invalidação de D-002 (R$ 0,30) — teto operacional R$ 0,20 + 50% de folga.
  - C-002 consistente com D-003 (single-tenant) — última linha da matriz bloqueia acesso cross-clínica.
  - C-003 consistente com FR-017 (imutabilidade) — anonimização preserva integridade referencial sem editar eventos.
  - C-004 consistente com bmad.md §2.4 (janela operacional BR cultural).
  - C-005 consistente com FR-017 (imutabilidade) — correção é novo evento, não edição.
  - C-006 consistente com briefing §9 (LGPD) — retenção limitada + anonimização após prazo.
- [x] **Lint passa** em `spec.md` e `clarify.md`.
- [x] **Heading-vs-requer** — `clarify.md` tem `requer:` com 1 item (`Decisões sobre regras sensíveis (Manual §5.4)`) batendo com heading `## Decisões sobre regras sensíveis (Manual §5.4)`.

## 5. Sinal de regras de negócio sensíveis (Manual §5.4)

Esta fase é **quando** as regras sensíveis deixam de ser "candidatas" e viram **decididas**. Aderência integral:

- [x] **Todos os 8 temas §5.4 têm decisão com autor humano** (tabela final em `clarify.md §Decisões sobre regras sensíveis`).

  | Tema | Decisão | Autor | Onde |
  |---|---|---|---|
  | Cobrança | Fora de escopo | humano | D-002 |
  | Permissão | Atendente + flag `is_admin` | humano | **C-002** |
  | Estorno | Fora de escopo | humano | D-002 |
  | Deleção | Anonimização atômica | humano | **C-003** |
  | Expiração | 24h / 4h / 08–20h BRT / 3 retries | humano | **C-004** |
  | Visibilidade | Single-tenant MVP | humano | D-003 |
  | Histórico | Imutável + evento `correcao` | humano | **C-005** |
  | Auditoria | Escopo B + 5 anos | humano | **C-006** |

- [x] **Nenhuma decisão foi tomada pela IA em silêncio.** Cada C-NNN tem: recomendação da IA **explicitamente marcada** como tal + decisão tomada com autor humano (assinado via merge). O padrão de estilo do clarify.md separa "Recomendação da IA" de "Decisão tomada" — é o contrato de transparência pedido em Manual §5.4.

- [x] **Todas as recomendações da IA foram aceitas** neste clarify. Isso **não** é red flag: as recomendações foram pré-validadas pela lógica das decisões estratégicas de BMAD (Caminho D, single-tenant, custo-alvo em D-002 risco aceito). Se o humano revisor do PR discordar de qualquer uma, basta ajustar no merge review → gera revisão D-NNN em fase futura.

## 6. Observações / pontos estranhos

- **Volume do clarify.md (331 linhas).** Cada entrada tem ≥3 opções com prós/contras + matriz de trade-off. É propositalmente denso — Clarify é a fase mais barata para debater, e o custo de decidir "rápido demais" aqui reaparece 10x em Implement.
- **C-004 é uma mega-entrada com 4 subperguntas** (lembrete / silêncio / horário / retry). Considerei dividir em C-004a/b/c/d, mas mantive unificado porque as 4 são **funcionalmente acopladas**: alterar uma força repensar as outras (ex: aumentar janela de lembrete reduz urgência da janela de silêncio). Unidade de análise = unidade de decisão.
- **C-002 introduz explicitamente a flag `is_admin`.** Isto é um **detalhe de modelo** que flerta com a linha "comportamento vs. arquitetura". Minha leitura: `is_admin` é uma **propriedade de papel** (negócio), não uma implementação (`column BOOLEAN NOT NULL`). Plan (Fase 4) decide o como — aqui a spec só diz que existe a distinção.
- **NFR-007 usa R$ como unidade.** Moeda é um dado de negócio (regional, cultural) que faz sentido fixar em spec — alternativa seria "USD" ou "unidades por notificação", mas isso deslocaria custo para uma linguagem que fugiria do alvo MPE BR.
- **Metas de SC permanecem `[INFERÊNCIA]` deliberadamente.** Contrato de Fase 3 exige zerar `[NEEDS CLARIFICATION]`, não `[INFERÊNCIA]`. Estas são premissas que devem cair em retrospective com dados reais (H-2 valida no-show < 10% com piloto; ajustar SC-003 depois).
- **Trial gratuito / onboarding** movidos para Out of Scope da spec em vez de virar C-NNN — mantêm-se como questões de produto comercial fora do módulo técnico. Decisão consciente.
- **Dívida `#7.5`** (author email local) persiste no commit `96556cc`.

## 7. Dívidas conhecidas / TODO

- [ ] **7.1 — `templates/recepcao.md`** permanece aberta. Não bloqueou. — Thiago; 2º canônico.
- [ ] **7.2 — Fase 3.5 Constituição** (próximo PR) precisa consumir C-002/C-003/C-004/C-005/C-006 como padrões herdados na camada 3 (produto) e declarar a invariante de contrato de canal abstrato (para troca Z-API ↔ Meta em 1 sprint, conforme D-002 mitigação). — Thiago; Fase 3.5.
- [ ] **7.3 — Fase 4 Plan** precisa escolher Meta vs Z-API com ADR local (dívida #7.6 do review da Fase 2). — Thiago; Fase 4.
- [ ] **7.4 — Validação H-1/H-2/H-4** (metas SC + custo real) fica para piloto pós-canônico. Os thresholds de NFR-007 (R$ 0,20/0,30) serão testados nessa validação. — Thiago; pós-canônico.
- [ ] **7.5 — Rebase `--reset-author`** antes de W4 — herdado. Mais um commit afetado agora.
- [ ] **7.6 — Constitution + Plan** precisam formalizar: (a) política de rate-limit interno do sistema contra envio em massa acidental; (b) desenho de fila para respeitar janela operacional 08h–20h sem degradar throughput. Detectado durante drafting do clarify — não vira C-NNN porque é detalhe técnico, não regra de negócio. — Thiago; Fase 3.5 e Fase 4.

## 8. CRM / Agentes / SaaS (se aplicável — Manual §29)

Aplicável. Esta fase refina 4 dos 9 campos §29 da automação de lembrete:
- **Condição de bloqueio** (C-004 janela operacional 08h–20h BRT + C-004 retry max 3).
- **Fallback** (C-002 intervenção manual por atendente; C-003 anonimização não interrompe fluxo).
- **Log** (C-005 correções + C-006 escopo de campos).
- **Critério de sucesso** (NFR-007 custo + metas SC existentes).

Formalização consolidada dos 9 campos continua prevista para `plan.md` (Fase 4) quando o driver for desenhado tecnicamente.

## 9. Resultado de testes

- [x] **Lint de `clarify.md`:** `OK`.
- [x] **Lint de `spec.md` v2:** `OK`.
- [x] **Grep de marcadores** em spec.md → apenas ocorrências como meta-descrição no Gate (pós-Clarify). **Zero marcadores ativos**.
- [x] **Gate de `fases/03_CLARIFY.md`** — 5 critérios ✅ exceto assinatura humana (via merge).
- [x] **Checklist `checklists/qualidade-spec.md`** — revalidado em spec.md v2; 40 ✅ + 1 pendente de merge.
- [x] **Coerência cruzada:**
  - `clarify.md C-002` ↔ `spec.md §Permissões` → tabela batendo 1:1 (15 ações × 4 colunas).
  - `clarify.md C-004` defaults ↔ `spec.md FR-010/011/028` → mesmos números.
  - `clarify.md C-005` regra operacional ↔ `spec.md FR-017` + Key Entity Consulta + novo edge case.
  - `clarify.md C-003` → `spec.md FR-033` + NFR-003 + edge case deleção.
  - `clarify.md C-006` → `spec.md FR-018` + NFR-004.
  - `clarify.md C-001` → `spec.md NFR-007` com mesmos R$ 0,20 teto + R$ 0,30 invalidação.
  - `clarify.md` tabela §5.4 ↔ `decision_log.md` tabela §5.4 → consistentes (clarify expande com as decisões C-NNN).

## 10. Veredicto

- [x] ✅ **Aprovada** — pode mergar.

Primeira fase do canônico que **modifica** artefato anterior (spec.md) — corretamente, conforme contrato da Fase 3. 6 decisões de regras sensíveis formalmente fechadas com autor humano, 8 temas §5.4 com decisão assinada, zero marcadores abertos em spec.md, rastreabilidade mantida integral (cada FR/NFR com origem D-NNN ou C-NNN ou briefing/bmad). 4 campos da automação §29 refinados. Dívidas com dono e prazo.

Mergar abre o próximo PR: **Fase 3.5 Constituição** — declara os padrões invariantes (engenharia + stack + produto) que Plan e Implement herdam sem redecidir.

Assinado por: Thiago Loumart (self-review, 2026-04-18)
