# ADR Index — `full-way-vibe-coding`

> Índice canônico das Architecture Decision Records globais da skill. Atualizado a cada
> ADR nova. Toda ADR ganha número sequencial e **nunca** é reutilizada, mesmo se revertida.

## Status legenda

- `Proposta` — escrita mas não aprovada; aberta a discussão.
- `Aceita` — aprovada por humano responsável; em vigor a partir de data.
- `Superada por: ADR-NNN` — não mais em vigor; substituída.
- `Revertida por: ADR-NNN` — foi aceita e depois revertida.
- `Rejeitada` — proposta e discutida, mas não aceita. Fica documentada.

## Índice

| ID | Título | Status | Camada | Data | Autor |
|---|---|---|---|---|---|
| [ADR-001](ADR-001-v1.1-dual-domain.md) | Adoção de arquitetura dual-domain (D1/D2/D3/híbrido) na v1.1 | Aceita (retroativa) | 1 | 2026-04-17 | Thiago Loumart |
| [ADR-002](ADR-002-stack-harness.md) | Stack do harness: Python 3.11+ com `pyyaml` | Aceita | 2 | 2026-04-17 | Thiago Loumart |
| [ADR-003](ADR-003-estrategia-publicacao.md) | Estratégia de publicação v1.2 (mesmo repo, Opção A) | Aceita | 2 | 2026-04-17 | Thiago Loumart |
| [ADR-007](ADR-007-modo-execucao-c1-c2-em-fase-0.md) | Declaração explícita de `modo_execucao` (C1 real / C2 documental) na Fase 0 | **Proposta** | 2 | 2026-04-23 | Thiago Loumart |
| [ADR-008](ADR-008-matriz-pre-mortem-mitigacao-em-analyze.md) | Matriz "Pre-mortem BMAD × Mitigação" obrigatória na Fase 6 Analyze | **Proposta** | 2 | 2026-04-23 | Thiago Loumart |
| [ADR-009](ADR-009-instrumentacao-sc-antes-go-live.md) | Instrumentação de `SC-NNN` obrigatória antes do go-live em projetos `C1_real` | **Proposta** | 2 | 2026-04-23 | Thiago Loumart |

> **Lacuna de numeração ADR-004..006:** os números ficaram reservados para as projeções originais (ver §Próximas ADRs). ADR-007..009 emergiram da Fase 12 Retrospective do canônico-001 (W1 track B) — safra "pós-W1 Retrospective" projetada para ADR-007+ acima. Caso as projeções 004/005/006 sejam escritas depois, **mantêm os números** (regra: numeração sequencial respeita ordem de criação, mas slots projetados têm precedência quando materializados).

## Gates abertos

**3 ADRs em status Proposta aguardando decisão humana** (origem: `examples/canonical-software/001-confirmacao-consultas/retrospective.md §4`):

- **ADR-007** — `modo_execucao: C1_real | C2_documental` em F0. **Dependência de outras**: nenhuma. **Prioridade**: base; habilita ADR-009.
- **ADR-008** — matriz Pre-mortem × Mitigação em F6. **Dependência**: nenhuma. **Prioridade**: independente de ADR-007; pode ser aceita sozinha.
- **ADR-009** — instrumentação SC antes do go-live em C1. **Dependência**: ADR-007 (precisa da distinção C1/C2). **Prioridade**: aceitação só faz sentido após ADR-007 Aceita.

Ordem recomendada de análise: **ADR-008 → ADR-007 → ADR-009** (da menos-dependente para a mais-dependente).

## Próximas ADRs esperadas (projeção)

Estas ainda não existem; ficam listadas para planejamento:

- **ADR-004** — política de tamanho máximo de PR e self-review obrigatório (em W1, fechando processo).
- **ADR-005** — contrato de `schema_version` nos templates (breaking vs non-breaking changes). Estimada para W2–W3 quando o harness validar schemas.
- **ADR-006** — primeiro exemplo canônico escolhido como prova do método em v1.2. **Parcialmente materializada** via canônico-001 mergeado em 2026-04-23 (commits #2..#15); formalização formal ainda pendente.
- **ADR-010+** — emergem da Fase 12 Retrospective pós-W2 e pós-W3 (quando outros canônicos fecharem).

## Como propor uma nova ADR

1. Próximo número disponível: **ADR-004** (slot reservado) ou **ADR-010** (próximo sequencial após safra atual).
2. Usar [`../../templates/adr.md`](../../templates/adr.md).
3. Status inicial: `Proposta`.
4. Abrir PR com o arquivo + atualização deste índice.
5. Ver [`../adr-global.md`](../adr-global.md) para critérios completos (o que merece ADR global vs decisão local em `decision_log.md` de módulo).

## Regras
- Numeração **nunca** é reutilizada.
- ADR revertida mantém o número com status atualizado.
- Alteração de Camada 1 exige autor + revisor distintos (não self-review).
- Alteração em produto regulado exige Compliance Officer + DPO quando LGPD aplicável.
