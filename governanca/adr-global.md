# ADR Global — Architecture Decision Records

> ADRs globais registram decisões **estruturais que afetam múltiplos módulos** (não decisões locais de um módulo — essas vivem em `decision_log.md` do próprio módulo como `D-NNN`).

## O que é um ADR global

Um ADR (Architecture Decision Record) global descreve uma decisão que:
- Impacta a Constituição (Camada 1 ou Camada 2).
- Impacta **decisões futuras** de módulos ainda não criados.
- Precisa ser descoberta por alguém que leia um módulo daqui a 6 meses sem contexto.

Exemplos típicos:
- "Passamos de ORM X para ORM Y." (Camada 2)
- "A partir desta ADR, todo endpoint público é versionado em /v1." (Camada 2)
- "Segurança passa a pesar ≥ 30% em todas as decisões make-vs-buy." (Camada 1 em D3)
- "LGPD Art. 7° I passa a exigir consentimento explícito no fluxo Z." (Camada 1 em D2)

## Quando criar um ADR global
- Durante **Fase 12 (Retrospective)**: o aprendizado do ciclo revelou algo que deveria valer para próximos ciclos.
- Durante **Fase 3.5 (Constituição)**: mudança proposta na Camada 1 ou Camada 2 exige ADR formal.
- Quando 2+ módulos começam a divergir no mesmo tema — unificar via ADR.

## Quando NÃO criar um ADR global
- Decisão só vale para este módulo → fica em `decision_log.md` do módulo como `D-NNN`.
- Decisão é escolha de implementação dentro de um padrão existente.
- Decisão é experimento temporário (marcar como `[RISCO ASSUMIDO]` em `analyze.md`).

## Onde vivem os ADRs globais
No repositório-alvo (o repo onde a skill está sendo aplicada):
```
<repo>/
  docs/
    adrs/
      ADR-001-adoption-of-vibe-coding.md
      ADR-002-postgres-vs-mysql.md
      ADR-003-versioning-of-public-endpoints.md
      ADR-index.md     (lista, status, resumo — atualizado a cada ADR)
```

## Numeração e status

- Numeração sequencial: `ADR-001`, `ADR-002`, …
- Nunca reutilizar um número. ADR revertida permanece com seu número e status `Revertida por: ADR-NNN`.

### Estados possíveis
| Status | Significado |
|---|---|
| `Proposta` | Escrita mas não aprovada. Pode ser discutida/editada. |
| `Aceita` | Aprovada por humano responsável; em vigor a partir de data. |
| `Superada por: ADR-NNN` | Não mais em vigor; substituída por outra ADR. |
| `Revertida por: ADR-NNN` | Foi aceita e depois revertida (registrar motivo). |
| `Rejeitada` | Proposta e discutida, mas não aceita. Fica documentada. |

## Estrutura do ADR

Use [`../templates/adr.md`](../templates/adr.md). Cada ADR tem:
- Front-matter YAML com `adr_id`, `status`, `camada_afetada`, `data`, `autor`.
- Contexto, Decisão, Alternativas, Consequências.
- Relação com Constituição (qual seção afeta).
- Declaração explícita: "Esta ADR [altera / não altera] Camada 1".

## Relação com `decision_log.md` por módulo

- `decision_log.md` de um módulo pode **citar** uma ADR global (ex.: `D-003 implementa a política da ADR-007`).
- Um módulo **não** pode contradizer uma ADR aceita sem registrar isso como exceção em `constitution.md §12`.
- Se o módulo revela que uma ADR tem caso não previsto: abrir nova ADR (revisão) ou exceção formal.

## Fluxo operacional

### Propor ADR
1. Durante Fase 12 (ou outra fase que revelou a necessidade), escrever proposta em `docs/adrs/ADR-NNN-<slug>.md` com status `Proposta`.
2. Notificar time/owners.
3. Discussão (issue, PR, reunião — conforme cultura do time).

### Aceitar ADR
1. Humano com autoridade adequada revisa e assina.
2. Status muda para `Aceita` + data.
3. Aplicar efeito:
   - Se altera Camada 2 da Constituição → editar `constitution.md` dentro dos marcadores `<!-- CAMADA_2_BEGIN/END -->` + minor bump (vN.M → vN.(M+1)).
   - Se altera Camada 1 → editar dentro dos marcadores `<!-- CAMADA_1_BEGIN/END -->` + major bump (vN → v(N+1).0) + aprovação humana explícita + nota de ruptura.

### Reverter ADR
1. Nova ADR com justificativa + referência à ADR revertida.
2. Status da ADR original muda para `Revertida por: ADR-NNN`.
3. Bump de versão da Constituição correspondente (minor ou major).

## Autoridade
| Camada afetada | Quem pode aprovar |
|---|---|
| Camada 2 (escolhas) | Tech lead / Ops lead / autor do playbook (conforme domínio) |
| Camada 1 (invariantes) | Diretoria / Compliance / Comitê de Governança (conforme domínio) |
| Camada 1 **em produto regulado** | Necessariamente Compliance Officer + DPO (se LGPD) |

## Exemplo — ADR de minor bump (Camada 2)

```yaml
---
artefato: adr
adr_id: ADR-007
status: Aceita
camada_afetada: 2
data: 2026-04-16
autor: Ana (tech lead)
---

# ADR-007 — Adotar Redis como cache de sessão

## Contexto
Três módulos (auth, carrinho, checkout) começaram a implementar cache
próprio em memória local. Isso gerou duplicação e incoerência em
horizontal-scaling. Spec do módulo `checkout` exige p95 < 200ms em
validação de token, o que o cache local não garante.

## Decisão
Adotar Redis 7 como cache de sessão compartilhado. Hospedado em
Elasticache. Chaves prefixadas por módulo (`auth:*`, `cart:*`,
`checkout:*`).

## Alternativas consideradas
- Memcached: menor feature-set; sem tipos avançados.
- DynamoDB com TTL: mais caro por operação; latência maior.
- Cache em-memória local: descartado — não resolve horizontal-scaling.

## Consequências
- Nova dependência em Elasticache (impacta Camada 2 da Constituição §4).
- Padrão de chave versionado (`v1:auth:session:<id>`).
- Observabilidade: métrica `cache.hit_ratio` passa a ser obrigatória.

## Relação com Constituição
- Esta ADR altera Camada 2 §4 (Stack).
- Esta ADR NÃO altera Camada 1.
- Bump: v1.2 → v1.3 da Constituição.
```

## Onde este arquivo referenciar
- [`../fases/03_5_CONSTITUICAO.md`](../fases/03_5_CONSTITUICAO.md) menciona ADR em mudanças de Camada.
- [`../fases/12_RETROSPECTIVE.md`](../fases/12_RETROSPECTIVE.md) pode gerar propostas de ADR.
- [`../templates/retrospective.md §4`](../templates/retrospective.md) tem estrutura para propor ADR.
- [`versioning.md`](versioning.md) explica como as versões da Constituição batem com ADRs.
