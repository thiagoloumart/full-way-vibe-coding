# Fase 4 — Planejamento Técnico

## Objetivo
Converter spec clarificada + constituição em um **plano técnico executável**, sem escrever código ainda (Manual §11).

## Entradas
- `spec.md` (pós-clarificação).
- `clarify.md`.
- `constitution.md`.

## Saídas
- `plan.md` (usar [`templates/plano.md`](../templates/plano.md)).

## O plano deve definir (Manual §11)
- **Ordem de implementação** (por fase, não total).
- **Dependências** (técnicas e lógicas).
- **Pré-requisitos** (env vars, configs, contas externas, seeds).
- **Arquivos que serão criados ou alterados** (lista nominal).
- **Contratos técnicos** — inputs/outputs de cada função ou endpoint, sem código: apenas tipo e propósito.
- **Modelo de dados** — entidades, campos, relações, integridade referencial, índices críticos. Sem DDL ainda, apenas modelagem.
- **Decisões técnicas relevantes** — por que tal padrão, por que tal biblioteca, por que tal estratégia.

## O plano deve respeitar (Manual §11)
- Arquitetura existente.
- Código já existente (em brownfield).
- Padrões do projeto (constituição).
- Bibliotecas já adotadas (não introduzir duplicata).
- Convenções atuais.

## Em brownfield (obrigatório)
Antes de escrever o plano:
1. Analisar o repositório (ver [`protocolos/brownfield.md`](../protocolos/brownfield.md)).
2. Responder: isso já existe? já há tabela semelhante? já há fluxo semelhante? já há policy semelhante? já há componente de UI semelhante?
3. **Reutilizar antes de criar novo.**

## Estrutura do plano

```
## Escopo do plano
Feature/módulo: <nome>
Referências: spec.md v<x>, clarify.md v<x>, constitution.md v<x>

## Fases de implementação
### F1 — <nome>
  Objetivo: <o que F1 entrega isolado>
  Depende de: <nada | F0>
  Arquivos: <lista>
  Entidades: <lista>
  Contratos: <endpoints/funções/eventos>
  Testes mínimos: <lista>
  Critério de "pronto": <mensurável>

### F2 — ...

## Modelo de dados
Entidade X
  Campos: ...
  Chaves: ...
  Relações: ...

## Integrações externas
<Serviço, auth, rate limit, fallback, idempotência>

## Decisões técnicas
- <decisão>: <motivo>

## Riscos técnicos
- <risco>: <mitigação>
```

## Riscos da fase
- Plano monolítico (uma fase só).
- Plano que ignora constituição.
- Plano que reimplementa algo já existente.
- Plano sem contratos (vai-se descobrir interface durante código).
- Decisão técnica invisível (escolha não justificada).

## Gate de avanço
- [ ] Fases ordenadas e cada uma entrega valor isolado.
- [ ] Lista de arquivos alvo explícita.
- [ ] Contratos técnicos descritos.
- [ ] Modelo de dados coerente com key entities da spec.
- [ ] Cada decisão técnica tem justificativa.
- [ ] Plano respeita a constituição (nenhum ponto colide).
- [ ] Em brownfield: análise do repo anexada.

## O que invalida a fase
- Plano contradiz spec ou constituição.
- Plano propõe biblioteca duplicada.
- Alguma fase do plano não tem critério de "pronto".

## Sinal de travamento
- Constituição proíbe abordagem que a spec exige → travar; revisar constituição ou spec.
- Não há como isolar fases (tudo acoplado) → travar; revisar escopo do módulo na Fase 0.
