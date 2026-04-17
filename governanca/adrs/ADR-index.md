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

## Gates abertos

_Nenhum no momento._ ADR-003 foi Aceita em 2026-04-17 com Opção A (mesmo repo, tag
`v1.2.0` ao fim da adequação).

## Próximas ADRs esperadas (projeção)

Estas ainda não existem; ficam listadas para planejamento:

- **ADR-004** — política de tamanho máximo de PR e self-review obrigatório (em W1, fechando processo).
- **ADR-005** — contrato de `schema_version` nos templates (breaking vs non-breaking changes). Estimada para W2–W3 quando o harness validar schemas.
- **ADR-006** — primeiro exemplo canônico escolhido como prova do método em v1.2. Estimada para início de W2.
- **ADR-007+** — emergem da Fase 12 Retrospective pós-W2 e pós-W3.

## Como propor uma nova ADR

1. Próximo número disponível: **ADR-004**.
2. Usar [`../../templates/adr.md`](../../templates/adr.md).
3. Status inicial: `Proposta`.
4. Abrir PR com o arquivo + atualização deste índice.
5. Ver [`../adr-global.md`](../adr-global.md) para critérios completos (o que merece ADR global vs decisão local em `decision_log.md` de módulo).

## Regras
- Numeração **nunca** é reutilizada.
- ADR revertida mantém o número com status atualizado.
- Alteração de Camada 1 exige autor + revisor distintos (não self-review).
- Alteração em produto regulado exige Compliance Officer + DPO quando LGPD aplicável.
