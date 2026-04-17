---
artefato: review
fase: 10
dominio: [any]
schema_version: 1
requer:
  - "1. Escopo do diff"
  - "3. Verificações mínimas (Manual §17)"
  - "4. Aderência à constituição"
  - "5. Sinal de regras de negócio sensíveis (Manual §5.4)"
  - "9. Resultado de testes"
  - "10. Veredicto"
---

# Review — W0 Fundadores (adequação v1.2)

**Branch:** `w0/fundadores`
**Data:** 2026-04-17
**Revisor:** Thiago Loumart (self-review, time=1)
**Status:** Pendente — aguardando assinatura humana

---

## 1. Escopo do diff

- Commits: a serem criados em 6 commits temáticos (ver seção 2).
- Linhas alteradas (aprox.): +1050 / −26 (docs-only, meta/governança).
- Arquivos alterados/criados: 9 (3 raiz + 4 ADRs + progress.md + self-review).

## 2. Arquivos alterados por categoria

| Categoria | Arquivos |
|---|---|
| Código de produção | — (n/a: repo é metodologia SDD; sem código executável nesta Wave) |
| Migrations | — |
| Testes | — (lint vai em W1) |
| Docs — governança | `AGENTS.md`, `CONTRIBUTING.md`, `CODEOWNERS` |
| Docs — ADRs | `governanca/adrs/ADR-001-v1.1-dual-domain.md`, `ADR-002-stack-harness.md`, `ADR-003-estrategia-publicacao.md`, `ADR-index.md` |
| Docs — auditoria | `harness/_audit/progress.md` (fechamento de drift M1.1–M1.11) |
| Metadata do review | `.review/w0-fundadores.md` (este arquivo) |
| Configuração | — |
| Infra | — |

## 3. Verificações mínimas (Manual §17)

- [x] **Arquivos alterados** — todos previstos no plano W0 da carta sênior? **Sim.** Desvio único: `.review/w0-fundadores.md` — não estava no plano; adicionei porque a disciplina de self-review exige persistência do checklist. Justificativa: CONTRIBUTING.md recém-criado exige self-review de `templates/review.md`; o artefato resultante precisa morar em algum lugar do repo. Pasta `.review/` é a mais neutra.
- [x] **Migrations criadas** — n/a (doc-only).
- [x] **Testes criados** — n/a (harness em M2; W1 adiciona lint).
- [x] **Rotas alteradas** — n/a.
- [x] **Policies / permissões alteradas** — n/a.
- [x] **Integrações externas alteradas** — n/a.

## 4. Aderência à constituição

- [x] Estrutura de pastas respeitada: ADRs em `governanca/adrs/` conforme `governanca/adr-global.md §"Onde vivem os ADRs globais"`.
- [x] Convenções: todos os ADRs usam front-matter exigido por `templates/adr.md`.
- [x] Nenhuma lib nova não autorizada: zero código, zero deps.
- [x] Logs / padrões: n/a.

## 5. Sinal de regras de negócio sensíveis (Manual §5.4 ampliada)

Nenhuma regra §5.4 de D1 (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria) foi decidida no código sem passar por clarify.

**Decisão estrutural sensível detectada (mas tratada corretamente):**

- ADR-003 (estratégia de publicação) **toca** "visibilidade" tangencialmente (escolher repo define quem vê o quê). **Tratamento:** ADR-003 está em status `Proposta` com marcador explícito `[DECISÃO HUMANA: tema=estratégia-publicação]`. Não foi auto-decidida.

## 6. Observações / pontos estranhos

1. **Commit monolítico de v1.1 (`6efe197`) permanece no histórico.** Esta Wave não reescreve esse commit. Reescrita fica como opção em W4 (após ADR-003 resolvida), via `git rebase --root --reset-author` + `git push --force-with-lease`. Aceitável por ora.
2. **CODEOWNERS só tem 1 owner.** Realidade: time=1. Revisitar quando entrar segundo mantenedor.
3. **Branch protection não ativada nesta Wave** — instrução explícita do humano foi "nada no github ainda". Fica como item de W4 quando publicação for autorizada.

## 7. Dívidas conhecidas / TODO

- [ ] ADR-003 aguardando assinatura humana antes de W4. **Bloqueador W4.**
- [ ] ADR-004 (política de tamanho de PR e self-review) a ser escrita em W1.
- [ ] `~/.claude/skills/full-way-vibe-coding/` ainda defasado em relação ao Desktop (sync vai para W4 ou separadamente).
- [ ] `CHANGELOG.md` raiz a ser criado em W3 cobrindo v1.0, v1.1, v1.2.
- [ ] Reescrita opcional de histórico (`--reset-author`) a avaliar em W4.

## 8. CRM / Agentes / SaaS (Manual §29)

Nenhuma automação introduzida nesta Wave. `protocolos/agentes-e-automacoes.md` já existe desde v1.1.

## 9. Resultado de testes

- [x] Lint sintático de YAML front-matter nos 4 ADRs — **conferido manualmente** (harness Python entra em W1; até lá, validação humana).
- [x] Links internos relativos — **conferidos manualmente** nos 4 ADRs + AGENTS.md + CONTRIBUTING.md. Nenhum link aponta para arquivo inexistente.
- [x] Quickstart manual — n/a (sem código).

## 10. Veredicto

- [ ] ✅ Aprovada — pode mergar.
- [x] 🟡 **Aprovada com dívidas registradas** — mergar após 👍 humano explícito.
- [ ] 🔴 Reprovada.

**Dívidas aceitas como `[RISCO ASSUMIDO]`:**
- Self-review em time de 1 pessoa é metodologicamente fraco. Mitigação: checklist preenchido item por item + decisão estrutural §5.4 (ADR-003) explicitamente marcada `[DECISÃO HUMANA]` aguardando assinatura humana distinta.

Assinado por: Thiago Loumart (auto-assinatura, time=1) — **pendente de confirmação humana** para considerar a Wave W0 encerrada.
