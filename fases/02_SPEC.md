# Fase 2 — Especificação

## Objetivo
Transformar o briefing em uma **specificação técnica de comportamento**, sem ditar arquitetura. Foco em **o que o sistema deve fazer**, não em como ele fará.

## Entradas
- `briefing.md` validado.
- `bmad.md` e `decision_log.md` da Fase 0.5.

Cada FR desta spec deve ser **rastreável** a uma decisão `D-NNN` em `decision_log.md` ou a um item explícito do briefing.

## Saídas
- `spec.md` (usar [`templates/spec.md`](../templates/spec.md), baseado em `spec-template.md`).

## Toda spec deve conter (Manual §9)
- Objetivo do módulo.
- Fluxo principal.
- User stories (priorizadas P1, P2, P3…).
- Critérios de aceitação (formato Given/When/Then).
- Edge cases.
- Restrições.
- Regras de negócio.
- Permissões.
- Possíveis estados de erro.
- Key entities (quando houver dado persistente).
- Success criteria mensuráveis.

## Princípios (absorve `PROMPT_SPEC.md`)
- **Comportamento > arquitetura.** Não dite banco nem framework.
- **"O sistema deve…"** — frases afirmativas e mensuráveis.
- **Cada user story é MVP isolado e testável.** Se implementar só a P1, ainda entrega valor.
- **Sem limite artificial** de quantidade de stories. O escopo determina.
- **Caminhos sugeridos:** 3–5 opções plausíveis para cada pergunta que aceita opções.
- **Uma pergunta por vez** em clarificação.

## Condução

### 2.a Descoberta guiada
Antes de gerar a spec, clarificar. **Reaproveitar o que já foi modelado em `bmad.md`**, não redescobrir:
1. **Valor de negócio:** confirmar com o problema real de `bmad.md §1.1` e o caminho escolhido de `bmad.md §4`.
2. **Atores e entidades:** "Os atores e entidades em `bmad.md §2.1`/`§2.3` estão completos para esta spec? Precisa adicionar/ajustar?"
3. **Persistência:** cruzar com `bmad.md §2.5`. Se o BMAD não clareou algo, perguntar e registrar nova `D-NNN`.
4. **Interface:** onde o recurso vive? (Web, mobile, API, CLI, background? Se o BMAD não clareou e há interface, perguntar.)
5. **Priorização interna:** o que é o coração da feature e como o resto se organiza em P1/P2/P3…
6. **Itens comuns de software ausentes:** autenticação, autorização, validações, estados vazios, mensagens de erro, integração externa, auditoria, notificações, performance, responsividade, disponibilidade, privacidade, rastreabilidade, relatórios, importação/exportação, permissões administrativas. **Perguntar sobre os que ainda não foram ditos** (inclusive os marcados como candidatas a regras sensíveis em `bmad.md §2.6`).

### 2.b Mapeamento de edge cases
Provocar o usuário:
- O que acontece se o usuário cancelar no meio?
- Como o sistema reage a campos vazios, inválidos, duplicados?
- O que acontece se um serviço externo falhar/ficar lento/retornar erro?
- O que acontece se parte da operação funcionar e parte não?
- O que acontece se faltar crédito/permissão?
- O que acontece se o payload vier malformado?

Para cada caso crítico:
- Explicar o risco em linguagem simples.
- Sugerir 3 formas plausíveis de tratamento.
- Pedir para o humano escolher ou ajustar.

### 2.c Geração da spec
Gerar em Markdown, com as seções do template.

## Regras de escrita
- Frases no formato: **FR-NNN: System MUST <capacidade verificável>**.
- Requisitos ambíguos marcados com `[NEEDS CLARIFICATION: …]`.
- Decisões de negócio sensíveis marcadas com `[DECISÃO HUMANA: …]`.
- Suposições marcadas com `[INFERÊNCIA]`.
- Nunca incluir nome de biblioteca, ORM, framework ou tabela.
- Success criteria devem ser **tecnologia-agnósticos e mensuráveis**.
- **Rastreabilidade:** cada FR deve referenciar sua origem — `D-NNN` do `decision_log.md` ou seção do briefing (ex: `FR-007 — origem: D-003 / briefing §7.2`).
- **Coerência:** nenhum FR pode contradizer uma decisão do `decision_log.md` sem que exista nova `D-NNN` de revisão registrada.

## Riscos da fase
- Spec que ordena arquitetura ("usar Postgres") → invalida.
- Spec sem edge cases → invalida.
- Stories não testáveis isoladamente.
- "O sistema funciona bem" como critério de aceitação.
- Priorização preguiçosa (tudo P1).

## Gate de avanço
- [ ] Todas as seções do template preenchidas.
- [ ] Zero regras ditadas no modo "como fazer".
- [ ] Cada FR é mensurável.
- [ ] Cada user story tem cenários Given/When/Then.
- [ ] Edge cases mapeados.
- [ ] Checklist [`checklists/qualidade-spec.md`](../checklists/qualidade-spec.md) cumprido.
- [ ] Humano validou a spec.
- [ ] **Cada FR mapeado** a uma `D-NNN` do `decision_log.md` ou a item do briefing.
- [ ] **Nenhuma contradição** entre a spec e o `decision_log.md`.

## O que invalida a fase
- Presença de jargão técnico de implementação.
- Requisitos em linguagem subjetiva ("deve ser intuitivo").
- Stories dependentes em cascata sem isolamento testável.
- Campos `[NEEDS CLARIFICATION]` ainda sem resposta (vão para Fase 3).
- Spec escrita sem `decision_log.md` assinado pela Fase 0.5.
- FR sem origem rastreável (nem `D-NNN`, nem seção do briefing).

## Sinal de travamento
- Humano não define persistência → travar, ver [`protocolos/travamento.md`](../protocolos/travamento.md).
- Humano pede que a IA decida regra de negócio sensível → travar, exigir decisão humana (Manual §5.4).
