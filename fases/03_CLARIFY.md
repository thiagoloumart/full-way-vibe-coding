# Fase 3 — Clarificação

## Objetivo
Eliminar, antes do código, quatro inimigos silenciosos (Manual §10):
- **Ambiguidade** — a mesma frase aceita duas interpretações.
- **Omissão** — algo essencial simplesmente não foi dito.
- **Contradição** — duas partes da spec se contradizem.
- **Falsa obviedade** — "óbvio" para o humano, invisível para quem implementa.

## Entradas
- `spec.md` com possíveis `[NEEDS CLARIFICATION]`, `[INFERÊNCIA]` e `[DECISÃO HUMANA]`.

## Saídas
- `clarify.md` — decisões registradas, cada uma com:
  - Pergunta original.
  - Opções avaliadas (com prós/contras).
  - Decisão tomada.
  - Justificativa.
  - Autor da decisão (humano ou `[RISCO ASSUMIDO]`).
- `spec.md` atualizado com as decisões incorporadas (os marcadores `[NEEDS CLARIFICATION]` desaparecem).

## Perguntas obrigatórias (Manual §10 — exemplos)
Para cada user story, validar:
- Quem pode ver?
- Quem pode editar?
- Quem pode apagar?
- O que acontece se falhar no meio?
- O que acontece se o usuário sair da tela/sessão?
- O que acontece se faltar crédito / quota / permissão?
- O que acontece se o payload vier inválido?
- O que acontece se a API externa falhar?
- O que acontece se parte da operação funcionar e parte não?
- Há auditoria? O que fica logado?

Para **regra de negócio sensível** (Manual §5.4), validar sempre:
- Cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria.

## Condução
- **Uma pergunta por vez.**
- **3–5 caminhos sugeridos** quando a pergunta aceita opções.
- **Travar** quando a decisão for materialmente impeditiva e a resposta não vier.
- **Nunca** inventar a resposta silenciosamente.

## Estrutura de cada entrada do `clarify.md`
```
### C-NNN — <tema>
**Origem:** FR-XXX / User Story Y / Edge Case Z
**Pergunta:** <texto>
**Opções avaliadas:**
  A) <opção>  — prós: …  / contras: …
  B) <opção>  — prós: …  / contras: …
  C) <opção>  — prós: …  / contras: …
**Decisão:** <letra ou custom>
**Justificativa:** <por que>
**Autor:** <humano / [RISCO ASSUMIDO]>
**Impacto:** <quais FRs/stories mudam>
```

## Riscos da fase
- Tratar clarificação como formalidade e aceitar respostas "meh".
- Deixar `[NEEDS CLARIFICATION]` residuais e seguir para o plano.
- Registrar decisão sem impacto (não atualizar spec).
- Decidir regra sensível internamente.

## Gate de avanço
- [ ] **Zero** `[NEEDS CLARIFICATION]` na spec.
- [ ] Cada decisão registrada em `clarify.md`.
- [ ] Cada decisão com impacto atualizou a spec.
- [ ] Nenhuma decisão de cobrança/permissão/estorno/deleção/expiração/visibilidade/histórico/auditoria ficou a cargo da IA.
- [ ] Humano assinou as decisões (explicitamente "ok" ou "aprovo").

## O que invalida a fase
- Ambiguidade continua após clarificação.
- Decisão contradiz outra decisão já registrada.
- Nova `[NEEDS CLARIFICATION]` surgiu no processo e não foi resolvida.

## Sinal de travamento
- Humano hesita sobre regra sensível → travar formalmente; não avançar.
- Clarificação abre contradição com o briefing → voltar ao briefing antes de continuar.
