# Inventário da skill — estado antes de M1

Snapshot: 2026-04-17
Fonte de verdade: `/Users/thiagomartins/Desktop/Vibe Coding/Skill Vibe Coding Completa/`
Espelho defasado: `~/.claude/skills/full-way-vibe-coding/` (será sincronizado ao final de M1)
Remote: https://github.com/thiagoloumart/full-way-vibe-coding

Veredicto calculado **contra o alvo da missão** (sistema operacional dual-domain com harness). INCRÍVEL = pronto pro alvo; ACEITÁVEL = funciona pro alvo atual (D1-only) mas precisa evolução em M1; FRÁGIL = precisa reescrita em M1; AUSENTE = não existe e é obrigatório.

## Raiz

| Caminho | Bytes | Resumo | Veredicto |
|---|---|---|---|
| `README.md` | ~7.3k | Índice da skill com fluxo resumido e princípios | ACEITÁVEL (atualizar em M1.8) |
| `SKILL.md` | ~13k | Skill mestre descritiva — identidade, fluxo, regras | FRÁGIL (vira router em M1.2) |
| `00_ANALISE_ESTRATEGICA.md` | ~19k | Leitura do Manual + mapa mestre + premissas | ACEITÁVEL (atualizar em M1.8 com Fase 12) |
| `filosofia.md` | — | Manifesto extraído do Manual §§1–6, §§26–31 | AUSENTE (criar em M1.2) |

## fases/

| Caminho | Resumo | Veredicto |
|---|---|---|
| `00_RECEPCAO.md` | Recepção + quebra em módulos + hipóteses estratégicas | ACEITÁVEL |
| `00_5_BMAD.md` | Breakdown/Model/Analyze/Decide (pré-briefing) | ACEITÁVEL |
| `01_BRIEFING.md` | Dor/uso/fluxo/valor herdando do BMAD | ACEITÁVEL |
| `02_SPEC.md` | User stories + FR + edge cases + rastreabilidade D-NNN | ACEITÁVEL |
| `03_CLARIFY.md` | Eliminar ambiguidade/omissão/contradição/falsa obviedade | ACEITÁVEL |
| `03_5_CONSTITUICAO.md` | Arquitetura/stack/padrões | FRÁGIL (reescrever bicamada em M1.5) |
| `04_PLAN.md` | Plano técnico por fase | ACEITÁVEL |
| `05_TASKS.md` | Tasks executáveis com DoD e dependências | ACEITÁVEL |
| `06_ANALYZE.md` | Gate cruzado — constituição × spec × plano × tasks × decision_log | ACEITÁVEL |
| `07_IMPLEMENT.md` | Implementação por fase respeitando constituição | ACEITÁVEL |
| `08_TEST.md` | Testes obrigatórios (sucesso/erro/edge/rollback/regressão) | ACEITÁVEL |
| `09_QUICKSTART.md` | Roteiro manual de validação | ACEITÁVEL |
| `10_REVIEW.md` | Revisão mínima antes do merge | ACEITÁVEL |
| `11_MERGE.md` | Git/branches/merge disciplinado | ACEITÁVEL |
| `12_RETROSPECTIVE.md` | Captura de aprendizado pós-merge | AUSENTE (criar em M1.4) |

## templates/

| Caminho | Resumo | Veredicto |
|---|---|---|
| `bmad.md` | Artefato de Fase 0.5 | ACEITÁVEL (adicionar front-matter em M1.7) |
| `decision_log.md` | Decisões `D-NNN` com revisões | ACEITÁVEL (adicionar front-matter) |
| `briefing.md` | Briefing de software | ACEITÁVEL (adicionar front-matter) |
| `spec.md` | Spec formal (FR, stories, SC) | ACEITÁVEL (adicionar front-matter) |
| `clarify.md` | Registro de clarificações | ACEITÁVEL (adicionar front-matter) |
| `constituicao.md` | Constituição do projeto | FRÁGIL (reescrever bicamada + marcadores em M1.5) |
| `plano.md` | Plano técnico | ACEITÁVEL (adicionar front-matter) |
| `tasks.md` | Quebra de tasks | ACEITÁVEL (adicionar front-matter) |
| `analise.md` | Análise cruzada (matrizes) | ACEITÁVEL (adicionar front-matter) |
| `quickstart.md` | Roteiro manual | ACEITÁVEL (adicionar front-matter) |
| `review.md` | Review pré-merge | ACEITÁVEL (adicionar front-matter) |
| `retrospective.md` | Artefato de Fase 12 | AUSENTE (criar em M1.4) |
| `adr.md` | Architecture Decision Record global | AUSENTE (criar em M1.9) |

## checklists/

| Caminho | Resumo | Veredicto |
|---|---|---|
| `mvp.md` | Critério MVP §23/§24 | ACEITÁVEL |
| `pre-implementacao.md` | Gate antes da Fase 7 | ACEITÁVEL |
| `pre-merge.md` | Gate antes da Fase 11 | ACEITÁVEL |
| `qualidade-bmad.md` | Gates de Fase 0.5 | ACEITÁVEL |
| `qualidade-briefing.md` | Gates de Fase 1 | ACEITÁVEL |
| `qualidade-plano.md` | Gates de Fase 4 | ACEITÁVEL |
| `qualidade-spec.md` | Gates de Fase 2 | ACEITÁVEL |

(schemas YAML irmãos em `harness/schemas/` entram em M2.)

## protocolos/

| Caminho | Resumo | Veredicto |
|---|---|---|
| `antialucinacao.md` | Fontes autoritativas + marcadores + erros comuns | ACEITÁVEL |
| `brownfield.md` | Leitura obrigatória do repo antes de propor | ACEITÁVEL |
| `decisao-mvp.md` | 3 perguntas da §24 | ACEITÁVEL |
| `erros-e-retry.md` | Como reagir a falhas de impl/teste | ACEITÁVEL |
| `perguntas-padrao.md` | Banco de perguntas por fase | ACEITÁVEL |
| `travamento.md` | Quando parar e perguntar | ACEITÁVEL |
| `agentes-e-automacoes.md` | Manual §29 extraído | AUSENTE (criar em M1.6) |
| `fast-path.md`, `context-pack.md`, `seguranca-privacidade.md`, `drift-detection.md`, `multi-agente.md`, `loop-reverso-bmad.md` | Protocolos de extensão | AUSENTE (M2) |

## Pastas a preencher

| Pasta | Estado em M1 | Estado em M2 |
|---|---|---|
| `domains/` | 4 arquivos (software, processo, playbook, hibrido) | mesmos + extensões |
| `harness/` | README + rollout + _audit (doc-only) | scripts Python + schemas YAML + tests |
| `examples/canonical-software/` | vazio (`.gitkeep`) | 1 módulo com 12 artefatos |
| `examples/canonical-processo/` | vazio (`.gitkeep`) | 1 processo com artefatos D2 |
| `integrations/` | vazio | 4 arquivos (claude-code, cursor, mcp, spec-kit) |
| `governanca/` | 3 arquivos (adr-global, versioning, metricas) doc-only | + policies de enforcement |

## Drift Desktop ↔ ~/.claude/skills/

Desktop está na frente. Cópia em `~/.claude/skills/full-way-vibe-coding/` não tem:
- `fases/00_5_BMAD.md`
- `checklists/qualidade-bmad.md`
- `templates/bmad.md`
- `templates/decision_log.md`
- atualizações em `01_BRIEFING`, `02_SPEC`, `06_ANALYZE`, `00_ANALISE_ESTRATEGICA`, `README`, `SKILL`, `templates/analise`, `protocolos/perguntas-padrao`

Ação em M1.11: `rsync -a --delete --exclude .git` do Desktop → skills folder.
