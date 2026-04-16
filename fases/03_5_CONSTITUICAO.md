# Fase 3.5 — Constituição do Projeto

> Manual §7: "A constituição é a camada mais importante do sistema."

## Objetivo
Garantir que existe, para o projeto, um **documento canônico** que define arquitetura, stack, padrões, regras de segurança, limites do MVP, convenções de código e decisões estruturais permanentes. Toda decisão técnica subsequente deve respeitar essa constituição.

## Quando executar
- **Sempre** antes da Fase 4 (planejamento técnico).
- Se a constituição **já existe** e cobre o módulo atual: apenas confirmar e referenciar.
- Se **não existe** ou está desatualizada: criar ou atualizar antes do plano.

## Entradas
- Briefing + spec + clarify validados.
- Stack declarada pelo humano (se houver).
- Em brownfield: leitura do repositório feita (ver [`protocolos/brownfield.md`](../protocolos/brownfield.md)).

## Saídas
- `constitution.md` (usar [`templates/constituicao.md`](../templates/constituicao.md)).
- Versionamento explícito: `Constituição v<N>`.
- Em v0 gerada por inferência, marcar como `[INFERÊNCIA]` até validação humana formal.

## Conteúdo obrigatório (Manual §7)
- **Arquitetura** — estilo (monolito, modular, microsserviços, worker, serverless).
- **Padrões** — layered / hexagonal / CQRS / REST / RPC / event-driven.
- **Linguagem e runtime.**
- **Stack** — framework backend, frontend, banco, cache, fila, infra.
- **Regras de organização** — estrutura de pastas, naming, boundaries.
- **Regras de segurança** — auth, autorização, proteção de dados sensíveis, rate limit, secrets, logs.
- **Limites do MVP** — o que está dentro e o que está fora.
- **Estilo de implementação** — convenções de código, formatação, linter, commits.
- **Convenções de código** — nomes de entidades, testes, branches.
- **Decisões estruturais permanentes** — "não usamos ORM X", "todo endpoint público é versionado em /v1", etc.

## Conduta
- **Brownfield:** extrair a constituição **do que já existe** (via leitura do repo), antes de sugerir mudanças.
- **Greenfield:** oferecer 3–5 caminhos para cada decisão estrutural, explicar prós/contras, recomendar.
- **Conflitos** entre pedido pontual do módulo e constituição devem ser sinalizados (Manual §7): nunca resolver silenciosamente.

## Guardrail
A partir daqui, toda fase operacional deve consultar a constituição antes de:
- Criar código.
- Propor estrutura.
- Alterar modelo de dados.
- Criar tela.
- Criar fluxo.
- Criar integração.

## Riscos da fase
- Constituição genérica ("microsserviços", "clean code") sem compromisso real.
- Constituição v0 tratada como canônica sem validação humana.
- Brownfield: propor nova constituição ignorando padrões existentes (gera duplicação e caos).
- Spec entrar em rota de colisão com constituição sem sinalizar.

## Gate de avanço
- [ ] `constitution.md` existe e cobre todos os campos obrigatórios.
- [ ] Humano validou (ou, em v0 gerada, autorizou explicitamente com `[RISCO ASSUMIDO]`).
- [ ] Conflitos entre spec e constituição foram sinalizados e resolvidos.

## O que invalida a fase
- Campos obrigatórios em branco.
- Contradição com spec já aprovada.
- Constituição referencia stack não disponível no ambiente.

## Sinal de travamento
- Humano quer stack incompatível com a feature → travar e mostrar o trade-off.
- Em brownfield, o repo revela padrões contraditórios entre si → travar, pedir ao humano a decisão de qual padrão prevalece.
