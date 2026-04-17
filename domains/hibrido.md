# Domínio Híbrido — Processo sustentado por software

> Usado quando a entrega não se encaixa limpamente em D1 software, D2 processo ou D3 playbook — porque **os dois mudam juntos** (ex.: nova cobrança automatizada exige código **e** mudança de fluxo operacional + compliance). Este arquivo é o mapa determinístico de **fusão vs duplicação** de artefatos.

## Declaração obrigatória: eixo primário

Na Pergunta 2 do dispatcher (ver [`../SKILL.md §3`](../SKILL.md#3-dispatcher--pergunta-2-ramificada-por-domínio)), o humano escolhe:

- **eixo = software** — conflitos resolvem-se a favor de D1; ordem de Fase 7 começa pelo código; dono do `[RISCO ASSUMIDO]` final em Fase 6 é o tech lead.
- **eixo = processo** — conflitos resolvem-se a favor de D2; ordem de Fase 7 começa pelo piloto operacional; dono do `[RISCO ASSUMIDO]` é ops lead ou compliance.
- **eixo = ambos igualmente críticos** — **trava**. A skill exige escolha explícita antes de seguir. "Ambos igualmente" é um anti-padrão: força decisão de governança.

O eixo **não é imutável**. Se durante o ciclo ficar claro que o eixo estava errado, registrar nova `D-NNN: revisão de eixo primário` em `decision_log.md` e voltar à Fase 6 antes de avançar.

## Tabela de fusão vs duplicação

| Artefato | D1 produz? | D2 produz? | No híbrido |
|---|---|---|---|
| `bmad.md` | sim | sim | **Fundido** — um arquivo com sub-seção `§1.1a Impacto software` + `§1.1b Impacto processo`; `§2.1 Atores` une papéis técnicos e operacionais; `§2.6 Regras sensíveis` lista ambas as listas §5.4. |
| `decision_log.md` | sim | sim | **Fundido** — numeração única `D-NNN`. Cada entrada tem campo obrigatório `eixo: [software \| processo \| ambos]`. Em caso de `ambos`, ambos os owners assinam. |
| `briefing.md` | sim | sim | **Um só** — seções padrão + seção extra "Impacto no processo" antes de "Módulos". |
| `clarify.md` | sim | sim | **Um só** — cada C-NNN tem tag `regra sensível: [D1 \| D2]`. A lista ativa de regras sensíveis é a **união** das listas D1 e D2 de [`../filosofia.md §7`](../filosofia.md#7-regra-54--decisões-sensíveis-nunca-pela-ia-ampliada-para-3-domínios). |
| `constitution.md` | técnica | operacional | **Uma só**. Camada 1 unifica princípios (conduta + compliance + alçadas máximas). Camada 2 tem duas sub-seções `[D1]` (stack, padrões) e `[D2]` (sistemas de origem, notação de mapa). |
| `spec.md` vs `mapa-to-be.md` | só D1 | só D2 | **Duplicados mas cruzados**. Cada FR do `spec.md` tem origem em passo do `mapa-to-be.md`, e vice-versa. Rastreabilidade bidirecional: tabela em `analyze.md` seção "FR × passo to-be" linter-enforced em M2. |
| `plan.md` / `runbook.md` | plan técnico | runbook operacional | **Duplicados**. `plan.md` referencia o `runbook.md` quando há handoff (ex.: código avisa, pessoa executa). |
| `tasks.md` / tarefas operacionais | só D1 | só D2 | **Duplicados**. Ordem resolvida pelo eixo primário. Tarefas que dependem de outro eixo marcadas com `bloqueia: T-XXX (outro eixo)`. |
| `quickstart.md` / `script-auditoria.md` | só D1 | só D2 | **Duplicados, ambos obrigatórios**. Quickstart valida software; script-auditoria valida processo. Um não substitui o outro. |
| `review.md` / revisão operacional | só D1 | só D2 | **Duplicados, ambos obrigatórios**. Ambos devem estar 🟢 para Fase 11 começar. |
| `retrospective.md` / `retrospectiva-operacional.md` | só D1 | só D2 | **Duplicados**. Ambos feitos após o go-live + merge; sugestões de ADR podem ser unificadas em um mesmo ADR-NNN com `camada_afetada`. |

## Ordem de execução em Fase 7 por eixo

### Eixo = software
1. Implementar código por fase (F1, F2, …).
2. Cada fase do código termina com testes verdes.
3. Piloto operacional começa quando o código de `plan.md §F_<piloto>` está mergado em ambiente de piloto.
4. Script de auditoria validada contra dados gerados pelo código.

### Eixo = processo
1. Piloto operacional começa primeiro (pode-se usar o software existente em estado não-ideal).
2. Código acompanha os gargalos identificados no piloto.
3. Rollout operacional determina a cadência do rollout do código.

## Sinais de que o eixo está errado

Revisitar a escolha de eixo primário se:
- Durante a Fase 4 (Plan), as tarefas do eixo secundário ficam 3x maiores que as do primário.
- Durante a Fase 6 (Analyze), mais bloqueios vêm do eixo secundário.
- Durante a Fase 7, o eixo secundário precisa adiar a Fase 7 do primário repetidamente.

Em qualquer desses sinais: `D-NNN: revisão de eixo primário` + voltar à Fase 6.

## Cuidado especial: não duplicar decisões
- Cada `D-NNN` aparece **uma vez** em `decision_log.md`, mesmo que impacte os dois eixos (campo `eixo: ambos`).
- Cada `C-NNN` aparece **uma vez** em `clarify.md`.
- Cada ADR global aparece **uma vez**, com `camada_afetada` + lista explícita dos eixos impactados.

## Templates em estado contrato (M1) vs concretos (M2)
Em M1 a conversa de híbrido flui com:
- Templates D1 completos.
- Templates D2 em estado contrato (referenciados mas não concretos).
- Fusão feita por condução manual marcando com `[INFERÊNCIA: estrutura fundida manualmente]`.

Em M2:
- Templates D2 concretos + schemas YAML + linter que enforce rastreabilidade bidirecional FR × passo.
- `smoke_test.py` cobre caso canônico híbrido.
