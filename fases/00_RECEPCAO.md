# Fase 0 — Recepção e quebra em módulos

## Objetivo
Transformar uma ideia crua em um **alvo de ciclo** concreto: um módulo único, priorizado, com escopo inicial claro.

## Entradas
- Ideia inicial do humano (pode ser vaga).
- Opcionalmente: objetivo, stack, prazo, contexto de projeto (greenfield/brownfield), anexos.

## Saídas
- Reformulação da ideia em uma frase.
- Classificação do projeto (greenfield | brownfield | extensão).
- Lista de módulos detectados, ordenados por valor.
- Módulo escolhido como alvo do primeiro ciclo.

## Perguntas-padrão (uma por vez)
1. "Entendi sua ideia assim: **<reformulação>**. É isso mesmo, ou quer ajustar?"
2. "É um projeto novo (greenfield) ou já existe código rodando (brownfield)?"
3. Se brownfield: "Me aponte o repositório ou cole a estrutura de pastas para eu ler antes de propor qualquer coisa."
4. "Qual o resultado concreto que você quer ver funcionando primeiro?"
5. "Quem é o usuário principal desse primeiro resultado?"
6. "Tem stack já decidida ou quer sugestão?" (oferecer 3–5 caminhos se pedir sugestão)
7. "Quais módulos você já imagina? Vou propor uma lista e a gente ajusta."

## Quebra em módulos (lógica interna)
Para cada módulo candidato, classificar:
- **Core** — sem ele, o sistema não entrega valor principal.
- **Crítico** — se quebrar, o usuário deixa de confiar.
- **Essencial** — sem ele agora, ninguém pagaria para usar.
- **Complementar** — agrega, mas não é MVP.
- **Cosmético** — fica para depois (dark mode, polimento, microdetalhes).

Prioridade no ciclo inicial: sempre um **core**.

## Caminhos sugeridos (modelo de output)
```
Reformulação: <frase>
Classificação: <tipo>
Módulos detectados:
  ⭐ [Core]       Cadastro/Auth
  ⭐ [Core]       <fluxo central>
  🔶 [Crítico]    <regra de negócio sensível>
  ⚙️ [Essencial]  <cobrança / histórico>
  ➕ [Complementar] <notificações>
  ✨ [Cosmético]  <polimento visual>

Recomendo iniciar por: <módulo>
Porque: <justificativa em 1 linha>
```

## Riscos da fase
- Quebrar em módulos grandes demais (não cabem em um ciclo).
- Escolher módulo cosmético antes do core.
- Classificar "desejo" como "requisito".
- Em brownfield, propor estrutura nova antes de ler o que existe.

## Gate de avanço
Para sair da Fase 0:
- [ ] Ideia reformulada e confirmada pelo humano.
- [ ] Projeto classificado.
- [ ] Lista de módulos existe e está priorizada.
- [ ] Humano escolheu **UM** módulo para o ciclo.
- [ ] Em brownfield, a skill leu o repositório antes de propor estrutura.

## O que invalida a fase
- Módulo escolhido não é core e não há justificativa.
- Nenhum módulo foi selecionado ("vou fazer tudo ao mesmo tempo").
- A ideia reformulada contradiz o que o humano quis dizer.

## Como revisar
Se, durante as fases seguintes, surgir contradição com a reformulação ou com a priorização, voltar à Fase 0 e reabrir a conversa.

## Sinal de travamento
- O humano não consegue escolher um módulo: oferecer lógica de decisão da seção 24 do Manual e [`protocolos/decisao-mvp.md`](../protocolos/decisao-mvp.md).
- Em brownfield, o repositório não é acessível: travar conforme [`protocolos/travamento.md`](../protocolos/travamento.md).
