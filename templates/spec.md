---
artefato: spec
fase: 2
dominio: [any]
schema_version: 1
requer:
  - "User Scenarios & Testing *(mandatory)*"
  - "Requirements *(mandatory)*"
  - "Success Criteria *(mandatory)*"
  - "Out of Scope"
---

# Feature Specification: [NOME DO MÓDULO/FEATURE]

**Feature Branch:** `[NNN-nome-do-modulo]`
**Created:** [YYYY-MM-DD]
**Status:** Draft | Em clarificação | Estável
**Input:** User description: "$IDEIA_INICIAL"
**Referências:** [briefing.md v<x>]

---

## User Scenarios & Testing *(mandatory)*

<!--
  User stories são journeys priorizados. Cada story deve ser INDEPENDENTEMENTE TESTÁVEL —
  implementar só uma e ter um MVP viável.
  Prioridades P1 (mais crítica), P2, P3, ... sem limite artificial de quantidade.
-->

### User Story 1 — [Título curto] (Priority: P1)
[Descrição em linguagem simples.]

**Why this priority:** [valor entregue isolado e por que é P1]
**Independent Test:** [como testar sozinho; que valor entrega]

**Acceptance Scenarios:**
1. **Given** [estado inicial], **When** [ação], **Then** [resultado esperado]
2. **Given** [estado inicial], **When** [ação], **Then** [resultado esperado]

---

### User Story 2 — [Título curto] (Priority: P2)
...

### User Story 3 — [Título curto] (Priority: P3)
...

---

### Edge Cases
- O que acontece se [condição de borda]?
- Como o sistema lida com [erro externo]?
- O que acontece se [payload inválido]?
- O que acontece se [permissão negada]?
- O que acontece se [parte da operação funcionar e parte não]?
- O que acontece se [o usuário cancelar no meio]?

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001:** System MUST [capacidade verificável]
- **FR-002:** System MUST [capacidade verificável]
- **FR-003:** Users MUST be able to [interação-chave]
- **FR-004:** System MUST [requisito de dados]
- **FR-005:** System MUST [comportamento de erro/segurança]

*Marcar ambíguos:*
- **FR-00N:** System MUST [...] [NEEDS CLARIFICATION: campo não especificado]

*Marcar decisões humanas pendentes (regras sensíveis Manual §5.4):*
- **FR-00M:** System MUST [...] [DECISÃO HUMANA: política de estorno]

*Marcar inferências:*
- **FR-00K:** [INFERÊNCIA] System MUST [...] — baseado em [...]

### Non-Functional Requirements (quando relevante)
- **NFR-001:** Performance — [métrica mensurável]
- **NFR-002:** Disponibilidade — [alvo]
- **NFR-003:** Privacidade — [regra]
- **NFR-004:** Auditoria — [o que fica logado]

### Key Entities *(quando envolve dados)*

- **[Entidade 1]:** [o que representa; atributos-chave sem implementação]
- **[Entidade 2]:** [o que representa; relações]

### Permissões
| Perfil | Ações permitidas | Ações bloqueadas |
|---|---|---|
| Admin | ... | ... |
| Cliente | ... | ... |

### Estados de erro previsíveis
- [Erro 1] → [mensagem ao usuário] / [log]
- [Erro 2] → ...

---

## Success Criteria *(mandatory)*

### Measurable Outcomes
- **SC-001:** [métrica tecnologia-agnóstica e mensurável]
- **SC-002:** [métrica]
- **SC-003:** [métrica de UX]
- **SC-004:** [métrica de negócio]

---

## Out of Scope
- [o que está **explicitamente** fora deste módulo]
- ...

---

**Checklist antes de aprovar:**
- [ ] Zero nomes de biblioteca/framework/ORM.
- [ ] Cada FR é verificável.
- [ ] Cada User Story tem Given/When/Then.
- [ ] Edge cases mapeados.
- [ ] `[NEEDS CLARIFICATION]` serão resolvidos na Fase 3.
- [ ] Regras sensíveis marcadas com `[DECISÃO HUMANA]`.
