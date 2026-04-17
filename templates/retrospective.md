---
artefato: retrospective
fase: 12
dominio: [any]
schema_version: 1
requer:
  - "1. Objetivo do módulo e resultado observado"
  - "2. KPIs (previsto vs observado)"
  - "3. Decisões revisitadas"
  - "4. Propostas de ADR global"
  - "5. Propostas de atualização de Constituição"
  - "6. Aprendizados para próximos ciclos"
---

# Retrospective — [Nome do Módulo]

**Data:** [YYYY-MM-DD]
**Módulo:** [NNN-nome]
**Domínio:** [D1 software | D2 processo | D3 playbook | Híbrido eixo=<>]
**Autor:** [humano]
**Revisor:** [humano, se aplicável — par sênior em D3]
**Status:** Draft | Finalizada

Referências:
- `review.md` v<x>
- `decision_log.md` v<x>
- `bmad.md` v<x>
- `analyze.md` v<x>
- Commits: [sha inicial .. sha merge]

---

## 1. Objetivo do módulo e resultado observado

**Objetivo original (de `briefing.md` §1 ou `bmad.md §1.1`):** [frase curta]

**Resultado entregue:** [o que de fato foi mergado / publicado / foi ao ar]

**Tempo do ciclo:** [início do BMAD → merge/go-live/publicação, em dias úteis]

**Veredicto macro:** [🟢 atingiu objetivo | 🟡 atingiu parcialmente | 🔴 não atingiu]

---

## 2. KPIs (previsto vs observado)

Para cada `SC-NNN` (D1) / KPI de `briefing-processo.md` (D2) / métrica em `metrica-eficacia.md` (D3):

| ID | Descrição | Previsto | Observado | Janela de medição | Gap | Causa provável |
|---|---|---|---|---|---|---|
| SC-001 | ... | ... | ... | ... | ... | ... |
| SC-002 | ... | ... | ... | ... | ... | ... |

Se algum KPI não foi observado (sem instrumentação): marcar `[NEEDS CLARIFICATION: instrumentar em ciclo futuro]`.

---

## 3. Decisões revisitadas

Para cada `D-NNN` em `decision_log.md`:

### D-001 — [tema]
**Decisão tomada em BMAD:** [resumo de 1 linha]
**O que aconteceu de fato:** [observação]
**Veredicto:** [🟢 sustentada | 🟡 parcialmente sustentada | 🔴 revertida]
**Se revertida ou parcialmente sustentada — causa:** [análise curta]
**Ação:** [nenhuma | registrar `D-NNN-REVISED` | propor ADR global em §4 | atualizar perguntas-padrão]

### D-002 — [tema]
...

### Riscos assumidos revisitados

| Marcador | Risco assumido em | Materializou? | Custo observado | Lição |
|---|---|---|---|---|
| `[RISCO ASSUMIDO]` | `analyze.md §11 #1` | sim/não | ... | ... |

### Pre-mortems revisitados (do `bmad.md §3.4`)

| Caminho | Falha prevista | Aconteceu? | Observação |
|---|---|---|---|
| A | ... | sim / não / diferente | ... |
| B | ... | ... | ... |

---

## 4. Propostas de ADR global

Só vira proposta se o aprendizado afetar **decisões futuras de outros módulos**. Aprendizado só-deste-módulo fica em §3 e §6.

### Proposta ADR-[próximo número] — [título]
- **Contexto:** [por quê esta proposta surge agora]
- **Decisão proposta:** [texto literal, before/after quando aplicável]
- **Alternativas consideradas:** [lista]
- **Consequências:** [o que passa a valer em ciclos futuros]
- **Camada afetada:** [1 | 2]
- **Dono proposto:** [humano que assinaria]

(Se não houver propostas: escrever "Nenhuma proposta de ADR global neste ciclo." + 1 linha de justificativa.)

---

## 5. Propostas de atualização de Constituição

Distintas de ADR: ADR registra a decisão; a Constituição é onde o efeito aparece.

| Camada | Seção afetada | Mudança proposta | ADR correspondente |
|---|---|---|---|
| 2 | §4 Stack | Adicionar Redis 7 para cache de sessão | ADR-018 |
| 1 | §6 Regras de segurança | Exigir rate-limit em todos endpoints públicos | ADR-019 (major bump) |

(Se não houver: "Nenhuma proposta de atualização.")

---

## 6. Aprendizados para próximos ciclos

### 6.1 Faríamos diferente
- [item concreto, ≤2 linhas]
- ...

### 6.2 Faríamos igual sem pensar duas vezes
- [item concreto]
- ...

### 6.3 O que não sabíamos e vale documentar
- [insight novo sobre o domínio, sobre o produto, sobre o time, sobre o protocolo]
- ...

### 6.4 Impacto em artefatos da skill
- [ ] `perguntas-padrao.md` precisa adicionar pergunta: [qual]
- [ ] `protocolos/travamento.md` precisa cobrir caso: [qual]
- [ ] `checklists/qualidade-<x>.md` precisa gate novo: [qual]
- [ ] `domains/<d>.md` precisa exemplo novo: [qual]

---

## Gate de fechamento

- [ ] Cada `D-NNN` do `decision_log.md` tem veredicto.
- [ ] Cada `[RISCO ASSUMIDO]` foi revisitado.
- [ ] KPIs comparados (previsto vs observado) — ou marcados como não-instrumentados.
- [ ] Propostas de ADR claras o suficiente para outro humano decidir.
- [ ] Aprendizados comunicados ao time (não apenas arquivados).
- [ ] Humano assinou fechamento: "OK — retrospective fechada".
