# Fase 0.5 — BMAD (Breakdown, Model, Analyze, Decide)

> Raciocínio estrutural **antes** do briefing. Sem BMAD rigoroso, briefing e spec herdam pressupostos implícitos e a primeira ideia vira a única ideia.

## Objetivo
Garantir que, antes de escrever requisitos de negócio:
- o **problema real** está separado do sintoma,
- o **modelo do sistema** (atores, fluxo, entidades, fricções) está explícito,
- **alternativas de abordagem** foram comparadas com trade-offs,
- a **decisão estratégica** está registrada com descartes, riscos aceitos e critérios de invalidação.

Esta fase produz o contrato estratégico que o Briefing herda.

## Entradas
- Ideia reformulada e confirmada na Fase 0.
- Classificação do projeto (greenfield | brownfield | extensão).
- Módulo escolhido como alvo do ciclo.
- Hipóteses estratégicas iniciais registradas na Fase 0.
- Contexto auxiliar coletado (stack, prazo, público, restrições).

## Saídas
- `bmad.md` (usar [`templates/bmad.md`](../templates/bmad.md)).
- `decision_log.md` (usar [`templates/decision_log.md`](../templates/decision_log.md)).
- Contrato explícito para o Briefing:
  - problema real (1 frase),
  - atores principais (lista),
  - fluxo de alto nível (3–7 passos),
  - caminho escolhido (1 frase) + descartados.

## Princípios
- **Problema antes de solução.** Nenhuma frase de Breakdown pode conter a solução.
- **Comportamento > arquitetura.** Zero menção a framework, banco, ORM — mesma regra da Spec.
- **Uma pergunta por vez.** Oferecer 3–5 caminhos sugeridos quando couber.
- **Humano decide regra sensível.** IA nunca decide em silêncio sobre cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria (Manual §5.4).
- **Descartes são obrigatórios.** Nenhuma decisão pode ficar com `alternativas: —`.

## Escopo: o que é BMAD vs o que é Spec
Para evitar ambiguidade entre esta fase e a Fase 2:

- **BMAD decide:** qual é o problema, quem são os atores, qual é o **caminho estratégico** (ex: "job automático 24h antes via WhatsApp" vs "botão manual"), quais riscos são aceitos.
- **Spec decide:** **como** o caminho é executado em detalhe verificável — frequência de polling, timeouts, formato exato de payload, estrutura de telas, códigos de erro, volumetria.

Regra prática: se a decisão muda **qual o sistema faz**, é BMAD. Se muda **como o sistema executa o que já foi decidido fazer**, é Spec. Em dúvida, registre como `D-NNN` no `decision_log.md` — mover depois é barato.

## Condução

### 0.5.a — BREAKDOWN (decomposição do problema)
> Separar problema real de sintoma e isolar o núcleo.

Perguntas (uma por vez):
1. Qual é o problema real que esse módulo resolve, em **1 frase sem mencionar solução**?
2. Quem sente essa dor hoje, em que momento, com que frequência?
3. O que é **causa-raiz** e o que é **sintoma aparente** aqui?
4. Quais subproblemas existem dentro desse problema? (regra **MECE** — mutuamente exclusivos, coletivamente exaustivos: sem sobreposição, sem gap)
5. O que é **core** (sem isso o problema não se resolve) vs **periférico** (alivia mas não resolve)?

Saída: árvore de decomposição (problema → subproblemas → core/periférico), 1 frase por nó.

Se o humano não conseguir distinguir causa de sintoma: oferecer 3 hipóteses derivadas da reformulação da Fase 0 e pedir escolha.

### 0.5.b — MODEL (modelagem do sistema)
> Atores, fluxo, entidades, fricções em alto nível.

Perguntas:
1. Quais são os **atores**? (papéis, não pessoas: usuário, admin, sistema, integração externa, operador, auditor)
2. Qual é o **fluxo principal ponta a ponta** em 3–7 passos narrados em linguagem natural? (sem ramificações de erro)
3. Quais **entidades** existem? (o que é criado/lido/modificado/excluído — sem tipos técnicos)
4. Onde estão as **fricções previsíveis**? (gargalo, espera humana, decisão manual, dependência externa)
5. O que precisa **persistir**? (memória entre sessões, histórico, auditoria)
6. O que vira **regra de negócio crítica** e precisa entrar em Clarify depois? (marcar)

Saída: tabela de atores, fluxo numerado, lista de entidades, lista de fricções, lista de candidatas a regras sensíveis (Manual §5.4).

### 0.5.c — ANALYZE (análise de alternativas)
> Mapear 2–4 caminhos de abordagem viáveis e comparar trade-offs.

Perguntas:
1. Quais **caminhos plausíveis** existem para resolver o problema? (mínimo 2, máximo 4; descrever cada um em 1 parágrafo)
2. Qual é o **menor caminho funcional**? (MVP esquelético que ainda prova valor)
3. Qual caminho **maximiza velocidade**? Qual **maximiza qualidade/manutenibilidade**?
4. Onde cada caminho corre risco de **overengineering**?
5. **Pre-mortem:** para cada caminho, "se daqui a 30 dias isso falhou, por quê terá sido?" (2–3 causas por caminho)
6. Qual caminho é **reversível** se der errado? (custo de voltar atrás)

Saída: matriz Caminho × (Velocidade, Qualidade, Risco, Reversibilidade, Custo), com notas 🟢/🟡/🔴 e 1 frase de justificativa por célula.

### 0.5.d — DECIDE (decisão registrada)
> Consolidar decisão, descartes, riscos e critérios de invalidação.

Perguntas:
1. Qual caminho foi **escolhido**?
2. **Por quê**? (1 parágrafo com o critério dominante — velocidade / reversibilidade / custo / qualidade)
3. O que foi **descartado** e por quê? (cada alternativa de Analyze tem linha de descarte)
4. Quais **riscos** foram conscientemente aceitos? (marcar `[RISCO ASSUMIDO]`)
5. **O que invalidaria** essa decisão daqui para frente? (condições que forçam revisão)
6. Quais **hipóteses** ficam em aberto e precisam validação?

Saída: entrada `D-NNN` em `decision_log.md` com: tema, decisão, alternativas descartadas (com motivo), riscos aceitos, critérios de invalidação, hipóteses, autor, data, impacto.

## Regras de escrita
- Marcar suposições com `[INFERÊNCIA]`.
- Marcar itens pendentes com `[NEEDS CLARIFICATION: …]` (herdam para a Fase 3).
- Marcar regras sensíveis com `[DECISÃO HUMANA: …]` quando aplicável.
- Marcar riscos aceitos com `[RISCO ASSUMIDO]`.
- Nenhum nome de biblioteca, framework, ORM ou tabela.

## Riscos da fase
- Confundir sintoma com causa-raiz.
- Listar apenas 1 caminho em Analyze ("só tem um jeito").
- Analyze sem pre-mortem — fica superficial.
- Decide sem descartes explicitados.
- IA decidindo regra sensível em silêncio.
- Fazer BMAD como formalidade e não atualizar o briefing com o contrato.

## Gate de avanço
- [ ] Problema real escrito em 1 frase, sem solução embutida.
- [ ] Causa-raiz vs sintoma distinguidos.
- [ ] Subproblemas em estrutura MECE.
- [ ] Atores, fluxo, entidades, fricções modelados.
- [ ] Candidatas a regras sensíveis (Manual §5.4) marcadas.
- [ ] Analyze com **≥2 caminhos**, matriz de trade-offs preenchida, pre-mortem por caminho.
- [ ] Decide com caminho escolhido + justificativa + descartes + riscos aceitos + critérios de invalidação.
- [ ] `decision_log.md` gerado com ≥1 entrada `D-NNN` assinada por humano.
- [ ] Contrato de saída (problema/atores/fluxo/caminho) explícito.
- [ ] Checklist [`checklists/qualidade-bmad.md`](../checklists/qualidade-bmad.md) cumprido.

## O que invalida a fase
- Breakdown sem separação causa/sintoma.
- Model sem fluxo principal narrado ou sem atores.
- Analyze com 1 caminho só, ou sem trade-offs comparados.
- Decide sem descartes (`alternativas: —` é inválido).
- Decisão sobre regra sensível sem autor humano.
- BMAD contradiz a reformulação da Fase 0 sem voltar à Fase 0.

## Como revisar
Se, durante Briefing/Spec/Clarify/Analyze, surgir contradição com `bmad.md` ou `decision_log.md`:
1. Registrar nova decisão `D-NNN` em `decision_log.md` com justificativa da mudança.
2. Atualizar `bmad.md` se a modelagem foi afetada.
3. Propagar efeitos para briefing/spec antes de avançar.

Nunca remendar apenas na spec ou clarify.

## Sinal de travamento
- Humano não consegue formular o problema real em 1 frase → oferecer 3 formulações derivadas da Fase 0 e pedir escolha.
- Analyze retorna apenas 1 caminho → provocar com duas alternativas opostas (mais simples / mais robusta).
- Decisão envolve regra sensível (Manual §5.4) e humano tenta delegar → travar; exigir decisão humana conforme [`protocolos/travamento.md`](../protocolos/travamento.md).
- Contradição irreconciliável com a reformulação da Fase 0 → voltar à Fase 0 antes de continuar.

## Ponte para Fase 1 (Briefing)
O Briefing **não redescobre** problema, atores, fluxo ou abordagem. Ele **detalha em linguagem de negócio** o caminho escolhido em Decide:
- expande o problema real em relato com dores concretas,
- amplia os atores com permissões de alto nível,
- narra o fluxo principal com verbos de ação e exemplos reais,
- preenche modelo de cobrança, papéis, módulos secundários.

Se o Briefing contradisser `bmad.md` ou `decision_log.md` sem registrar nova `D-NNN`: fase inválida.
