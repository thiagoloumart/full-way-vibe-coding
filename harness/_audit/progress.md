# Progresso — Milestones

> Esta pasta rastreia o progresso da evolução da skill por milestone.
> M1 = núcleo dual-domain (v1.1) — **concluída**.
> M2 = harness Python funcional (v1.2) — **em planejamento**, iniciada pela adequação
> conduzida em Waves W0–W4 (dogfood extremo).

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

#### W0 — Selar o fundador (dias 1–3) — **EM ANDAMENTO**
- [x] Leitura dos 9 arquivos obrigatórios (2026-04-17)
- [x] Fase 0 Recepção + Fase 0.5 BMAD com papel Arquiteto (2026-04-17)
- [x] Branch `w0/fundadores` criada (2026-04-17)
- [x] `AGENTS.md` raiz criado (2026-04-17)
- [x] `CONTRIBUTING.md` raiz criado (2026-04-17)
- [x] `CODEOWNERS` raiz criado (2026-04-17)
- [x] ADR-001 (v1.1 dual-domain retroativa) criada (2026-04-17)
- [x] ADR-002 (stack harness = Python) criada (2026-04-17)
- [x] ADR-003 (estratégia de publicação) criada em status `Proposta` — **bloqueia W4** (2026-04-17)
- [x] ADR-index.md criado (2026-04-17)
- [x] Drift de M1.1–M1.11 fechado em `progress.md` (este commit) (2026-04-17)
- [ ] Self-review aplicando `templates/review.md`
- [ ] Commits por tema em `w0/fundadores`
- [ ] 👍 humano para `merge` (local) de `w0/fundadores` em `main`
- [ ] Branch protection em `main` (operação GitHub; fica para quando publicação for autorizada)

#### W1 — Enforcement mínimo + linter (dias 3–10)
*Começa após 👍 humano fechando W0.*

#### W2 — Dogfood completo (dias 10–25)
*Exemplo canônico: `examples/canonical-software/001-confirmacao-consultas/`.*

#### W3 — CI bloqueante + retrospective M1 (dias 25–45)

#### W4 — Publicação e 2º dogfood (dias 45–60)
*Bloqueada por ADR-003 `Aceita`.*

### Log

**2026-04-17 — Início de W0**
- Auditoria SDD entregue: 54/100 (Frágil).
- Prompt operacional da adequação escrito e aprovado pelo humano.
- Caminho B (60 dias, 5 Waves) escolhido em D-000 do BMAD W0.
- Trabalho começou na branch local `w0/fundadores` — sem push para GitHub até ADR-003 ser resolvida.

