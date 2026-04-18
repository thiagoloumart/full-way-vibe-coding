# Progresso — Milestones

> Esta pasta rastreia o progresso da evolução da skill por milestone.
> M1 = núcleo dual-domain (v1.1) — **concluída**.
> M2 = harness Python funcional (v1.2) — **em andamento**, conduzida em Waves W0–W4
> (dogfood extremo). W0 concluída 2026-04-17; W1 track A concluída 2026-04-18;
> W1 track B iniciando 2026-04-18.

---

## Milestone 1 (v1.1) — núcleo dual-domain · **CONCLUÍDA**

Início: 2026-04-16
Conclusão: 2026-04-17
Escopo: transformar skill de "manual operacional bem escrito" em "sistema operacional
dual-domain (D1 software / D2 processo / D3 playbook)", preservando invariantes
(numeração de fases, 4 pastas raiz, marcadores, §5.4).

### Sub-etapas

- [x] M1.1 — Auditoria inicial, before.tree, inventário, pastas stub (2026-04-17)
- [x] M1.2 — `filosofia.md` + `SKILL.md` como router (2026-04-17)
- [x] M1.3 — `domains/` (software, processo, playbook, hibrido) (2026-04-17)
- [x] M1.4 — Fase 12 (Retrospective) + template (2026-04-17)
- [x] M1.5 — Fase 3.5 reescrita em 2 camadas + template bicamada (2026-04-17)
- [x] M1.6 — `protocolos/agentes-e-automacoes.md` (extrai Manual §29) (2026-04-17)
- [x] M1.7 — YAML front-matter em 11 templates existentes (2026-04-17)
- [x] M1.8 — `README.md` e `00_ANALISE_ESTRATEGICA.md` atualizados (2026-04-17)
- [x] M1.9 — `governanca/` (adr-global, versioning, metricas) + `templates/adr.md` (2026-04-17)
- [x] M1.10 — `harness/README` e `rollout.md` documentados (2026-04-17)
- [x] M1.11 — Commit `6efe197` monolítico em `main` com 39 arquivos (2026-04-17)

### Log

**2026-04-17 — M1.1 concluída**
- Pastas criadas: `domains/`, `harness/_audit/`, `harness/schemas/`, `harness/scripts/`, `examples/canonical-software/`, `examples/canonical-processo/`, `integrations/`, `governanca/` (com `.gitkeep`).
- `before.tree` capturado.
- `inventory.md` preenchido com veredictos por arquivo.

**2026-04-17 — M1.2–M1.11 concluídas em bloco**
- Materializadas no commit `6efe197` (+2543/−431 linhas em 39 arquivos) em `main`.
- Sync `~/.claude/skills/full-way-vibe-coding/` ainda pendente (delta.md/handoff.md serão criados quando a sync acontecer — tarefa operacional, não produto).

---

## Milestone 2 (v1.2) — harness funcional + dogfood extremo · **EM ANDAMENTO**

Início: 2026-04-17 (início de W0)
Previsão de conclusão: 2026-06-17 (60 dias)
Escopo: adequação SDD subindo score de 54/100 para ≥75/100, conforme auditoria SDD de
2026-04-17 e plano mestre da carta sênior. Driver: dogfood extremo (aplicar a própria
skill a si mesma).

Conduzido via 5 Waves sequenciais (W0 → W4). Cada Wave é um ciclo reduzido da skill
(15 fases aplicadas ao escopo da Wave).

### Waves e sub-etapas

#### W0 — Selar o fundador (dias 1–3) — **CONCLUÍDA em 2026-04-17**
- [x] Leitura dos 9 arquivos obrigatórios (2026-04-17)
- [x] Fase 0 Recepção + Fase 0.5 BMAD com papel Arquiteto (2026-04-17)
- [x] Branch `w0/fundadores` criada (2026-04-17)
- [x] `AGENTS.md` raiz criado (2026-04-17)
- [x] `CONTRIBUTING.md` raiz criado (2026-04-17)
- [x] `CODEOWNERS` raiz criado (2026-04-17)
- [x] ADR-001 (v1.1 dual-domain retroativa) criada (2026-04-17)
- [x] ADR-002 (stack harness = Python) criada (2026-04-17)
- [x] ADR-003 (estratégia de publicação) **Aceita** em 2026-04-17 — Opção A (mesmo repo, tag `v1.2.0` ao fim de M2); desbloqueou W4 e autorizou push imediato
- [x] ADR-index.md criado (2026-04-17)
- [x] Drift de M1.1–M1.11 fechado em `progress.md` (2026-04-17)
- [x] Self-review aplicando `templates/review.md` (2026-04-17)
- [x] Commits por tema em `w0/fundadores` + merge em `main` (2026-04-17)
- [x] Push de `main` para `origin` (2026-04-17) — a partir daqui, operação em modo push-forward
- [x] Branch protection em `main` — ativada ao fim de W1 track A (2026-04-18); antes disso era cerimônia sem `required status checks`

#### W1 — Enforcement mínimo + linter (dias 3–10) — **EM ANDAMENTO (track B)**

##### W1 track A — `lint_artefato.py` · **CONCLUÍDO em 2026-04-18**
- [x] Branch `w1a/lint-artefato` criada e mergeada em `main` via `gh pr merge --squash --admin` (HEAD pós-merge: `5905bac`).
- [x] Entregas:
  - 3 classes de validação: front-matter YAML, seções `requer:` presentes no corpo, links relativos internos.
  - 80 testes pytest cobrindo happy + sad paths; self-lint 26/26 artefatos lintáveis verdes.
  - 9 códigos de erro PT SCREAMING_SNAKE catalogados em `harness/README.md §Catálogo M1`.
  - CLI com flags `--format`, `--warnings-only`, `--no-color`; exit codes estáveis 0/1/2; ANSI color com auto-detecção de TTY + respeito a `NO_COLOR`.
- [x] Dogfood expôs e mitigou 2 drifts reais: **D-W1A-001** (`templates/constituicao.md` com `requer:` inconsistente) e **D-W1A-002** (falso positivo LINK_QUEBRADO em inline code) — ambos corrigidos estruturalmente.
- [x] 3 propostas de ADR levantadas em retrospective: **ADR-004** (refactor modular quando `lint_artefato.py` > 500 LoC), **ADR-005** (`requer:` como contrato auditável), **ADR-006** (extensão de front-matter para doc livre).
- [x] Branch protection em `main` ativada neste marco (required status checks pendentes de W3).

##### W1 track B — Primeiro exemplo canônico D1 · **INICIANDO 2026-04-18**
- Módulo: `examples/canonical-software/001-confirmacao-consultas/` (tema já validado em dogfood conversacional durante design de Fase 0.5 BMAD).
- Objetivo: dogfood completo — rodar as 15 fases (0 → 12) com `lint_artefato.py` como gate mecânico sobre cada artefato produzido; 1 commit por fase.
- Justificativa do replanejamento (tomada em 2026-04-18): antecipar o primeiro canônico D1 para W1 (antes era W2) porque o lint só ganha legitimidade quando valida artefatos reais de um ciclo completo, e W3 (CI bloqueante) não pode chegar sem esse sinal. W2 realocada para segundo canônico em D2 processo.
- Primeira fase: **Fase 0 — Recepção** (`recepcao.md` com ideia reformulada, classificação greenfield/brownfield, módulos priorizados, alvo escolhido, 3 hipóteses estratégicas para alimentar BMAD).
- [ ] Fases 0 → 12 executadas em sequência com commits atômicos por fase.
- [ ] Retrospective da track B colhe aprendizados cruzados (lint + ciclo completo D1) para alimentar W2 D2.

#### W2 — Segundo dogfood (D2 processo) (dias 10–25)
*Realocada em 2026-04-18.* Escopo original ("dogfood completo D1") foi promovido para W1 track B. Novo foco: primeiro exemplo canônico em D2 processo — `examples/canonical-processo/<NNN-tema>/`. Tema a ser definido no início de W2 via Fase 0 com papel Arquiteto.

#### W3 — CI bloqueante + retrospective M1 (dias 25–45)
*Depende do fechamento de W1 track B (primeiro canônico D1 verde no lint) e de W2 (primeiro canônico D2 verde). Só então `required status checks` do lint em `main` ganham dentes operacionais.*

#### W4 — Publicação v1.2.0 (dias 45–60)
*ADR-003 já Aceita em 2026-04-17 (Opção A). W4 resume a: `CHANGELOG.md`, ajuste de `README.md` raiz refletindo evolução v1.0→v1.1→v1.2, tag anotada `v1.2.0`, anúncio. O "2º dogfood" originalmente planejado aqui foi promovido para W2.*

### Log

**2026-04-17 — Início e fechamento de W0**
- Auditoria SDD entregue: 54/100 (Frágil).
- Prompt operacional da adequação escrito e aprovado pelo humano.
- Caminho B (60 dias, 5 Waves) escolhido em D-000 do BMAD W0.
- Trabalho começou na branch local `w0/fundadores`.
- ADR-003 Aceita no mesmo dia (Opção A: mesmo repo, tag `v1.2.0` ao fim de M2) — decisão explícita do proprietário do repo; desbloqueou W4.
- Consequência operacional: `w0/fundadores` mergeada em `main` e push imediato para `origin/main` autorizado (a partir daí o repo opera em push-forward, não mais "nada no GitHub até W4").
- Branch protection em `main` foi postergada para o fim de W1 track A (quando o lint entra em CI como `required status check`); ativada em 2026-04-18.
- Self-review de W0 aplicando `templates/review.md` concluído; 👍 humano dado.

**2026-04-18 — Fechamento de W1 track A e início de W1 track B**
- `lint_artefato.py` mergeado em `main` via `gh pr merge --squash --admin` (HEAD pós-merge: `5905bac`).
  - 16 tasks executadas em sequência (T-001 → T-016) com commits atômicos por tarefa.
  - 10 commits docs pré-código + 19 commits código/fix + retrospective.
  - 80 testes pytest verdes; smoke 26/26 lintáveis verdes; self-lint 11/11 em `specs/m1-lint/` verde.
  - 2 drifts D-W1A-001 e D-W1A-002 detectados e mitigados estruturalmente (ver `specs/m1-lint/risk_log.md`).
  - 3 propostas de ADR abertas (ADR-004/005/006) — não aceitas ainda, decisão humana pendente no início de W1 track B.
- Branch protection em `main` ativada.
- **Decisão humana 2026-04-18:** W1 ganha track B com primeiro exemplo canônico D1 (`examples/canonical-software/001-confirmacao-consultas/`); W2 passa a ser segundo canônico em D2 processo. Registrado em progress.md nesta mesma atualização (branch `chore/progress-w1a-fechado`, PR dedicado).
- Primeira fase de W1 track B: Fase 0 Recepção.

