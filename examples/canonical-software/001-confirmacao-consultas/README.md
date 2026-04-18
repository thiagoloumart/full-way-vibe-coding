# Canônico 001 — Confirmação de consultas (D1 software)

Primeiro exemplo canônico D1 do repositório. Dogfood que prova que a skill
`full-way-vibe-coding` conduz um módulo real do zero até merge aplicando
as 15 fases (0 → 12) do manual operacional.

## Status do ciclo

| Fase | Artefato | Status | Branch / PR |
|---|---|---|---|
| 0 — Recepção | `recepcao.md` | 🟢 Finalizada (2026-04-18) | `w1b/f0-recepcao` |
| 0.5 — BMAD | `bmad.md` + `decision_log.md` | 🟢 Finalizada (2026-04-18) | `w1b/f0.5-bmad` |
| 1 — Briefing | `briefing.md` | 🟡 Draft (2026-04-18) | `w1b/f1-briefing` |
| 2 — Spec | `spec.md` | ⏳ | — |
| 3 — Clarify | `clarify.md` | ⏳ | — |
| 3.5 — Constituição | `constitution.md` | ⏳ | — |
| 4 — Plan | `plan.md` | ⏳ | — |
| 5 — Tasks | `tasks.md` | ⏳ | — |
| 6 — Analyze | `analyze.md` | ⏳ | — |
| 7 — Implement | código real | ⏳ | — |
| 8 — Test | suíte | ⏳ | — |
| 9 — Quickstart | `quickstart.md` | ⏳ | — |
| 10 — Review | `.review/canonical-001-*.md` | ⏳ (um por PR) | — |
| 11 — Merge | — | ⏳ | — |
| 12 — Retrospective | `retrospective.md` + `risk_log.md` | ⏳ | — |

Atualizar esta tabela ao fim de cada PR mergeado.

## Escopo declarado

- **Perfil cliente-alvo:** clínicas SMB (micro/pequena/média) no Brasil.
- **Classificação:** greenfield.
- **Módulo alvo:** Confirmação de consultas (detalhado em `recepcao.md §4`).
- **Stack proposta (ainda não ratificada):** Laravel 12 + Blade/Livewire 3 +
  PostgreSQL 16 + Redis + Laravel Forge. Formalização formal vira `D-001`
  na Fase 0.5 Decide.

## Como ler este diretório

Na ordem das fases: `recepcao.md` → `bmad.md` → `decision_log.md` →
`briefing.md` → `spec.md` → … . Cada artefato tem front-matter com `fase`
indicando onde ele entra no ciclo.

Este canônico é **artefato de validação**, não produto para vender. Código
real (se houver) fica em subpasta quando a Fase 7 Implement abrir.
