# Fase 1 — Briefing

## Objetivo
Tirar a ideia da cabeça e colocar em **linguagem de negócio clara**. Sem técnica. Foco em **dor, uso, fluxo e valor** (Manual §8).

## Entradas
- Módulo escolhido na Fase 0.
- Ideia reformulada e confirmada.
- Qualquer contexto auxiliar já coletado.

## Saídas
- `briefing.md` (usar [`templates/briefing.md`](../templates/briefing.md)).

## O briefing deve responder (Manual §8)
- Qual **problema real** existe?
- **Quem** sofre esse problema?
- Qual **resultado** o sistema entrega?
- **Quem usa** o sistema?
- Qual o **fluxo principal**?
- Qual o **modelo de cobrança**, se existir?
- Quais **papéis de usuário** existem?
- Quais **módulos mínimos** existem?

## O briefing NÃO deve focar em
- Framework.
- Biblioteca.
- Implementação técnica profunda.
- Banco de dados, servidores, APIs internas.

## Condução (absorve `PROMPT_BRIEFING.md`)

### Princípios
- **Uma pergunta por vez.** Nunca uma lista.
- **Zero jargão técnico.**
- **Empatia + provocação:** se a resposta for vaga, peça exemplo prático.
- **Verbos de ação:** o usuário faz, o sistema envia, o atendente aprova.

### Fase 1.a — Visão de Negócio
Perguntas em ordem (uma por vez):
1. Qual é a principal dor que o software vai resolver? Quais os relatos reais?
2. Como você descreveria esse sistema em um parágrafo para um investidor?
3. Quem vai usar? Como vão acessar? (app cliente + painel interno? só interno? só cliente?)
4. Qual o modelo de precificação? (assinatura, por transação, licença, free com add-on?)
5. Quais níveis de usuário existem? (admin, gerente, cliente, prestador, etc.)

### Fase 1.b — Loop de Requisitos (por módulo)
Para cada módulo:
1. "Quais são as grandes áreas ou módulos?"
2. Quando o usuário citar um módulo, **não avance**. Entre no detalhe:
   - "O que o usuário faz nesse módulo?"
   - "Qual a primeira coisa que ele precisa conseguir fazer aqui?"
   - "Depois disso, o que mais precisa acontecer?"
3. Se a resposta for vaga ("tem que ter controle", "tem que funcionar bem"):
   - "Quando você diz 'controlar', o que exatamente a pessoa vai fazer?"
   - "Me dê um exemplo real do dia a dia usando essa parte."
   - "Quem faz essa ação e o que acontece depois?"
4. Perguntas de desbloqueio (se travar a memória):
   - Tem consulta/busca?
   - Tem cadastro/edição/exclusão?
   - Tem aprovação/cancelamento/confirmação?
   - Tem notificação/lembrete?
   - Tem acompanhamento de status?
   - Tem relatório/visualização?
5. **Pergunta cíclica obrigatória** (ao fim de cada módulo):
   > "Tem mais alguma ação que precisa acontecer nesse módulo ou podemos ir para o próximo? Lembre-se: o que mais o sistema precisa fazer?"
6. Checagem final leve (antes de sair do módulo):
   > "Se esse módulo estivesse pronto hoje, faltaria alguma função importante para ele funcionar de verdade no dia a dia?"

### Fase 1.c — Grande Resumo
Compilar tudo em `briefing.md` seguindo o template. **Não é resumo bruto**: é reescrita estruturada, com verbos de ação claros.

## Riscos da fase
- Entrar em decisão técnica (cair em stack/ORM/lib).
- Aceitar respostas vagas como requisito final.
- Pular a pergunta cíclica e perder ações do módulo.
- Virar "briefing infinito" sem o humano dizer "terminei".

## Gate de avanço
- [ ] Todas as 5 perguntas da Visão de Negócio respondidas.
- [ ] Cada módulo teve loop de requisitos com pergunta cíclica respondida ("terminei").
- [ ] `briefing.md` gerado, reescrito e **validado pelo humano**.
- [ ] Checklist [`checklists/qualidade-briefing.md`](../checklists/qualidade-briefing.md) cumprido.

## O que invalida a fase
- Briefing contém decisão técnica (framework, banco).
- Briefing tem campos em branco ou "a definir" em pontos-chave.
- Humano não revisou e não confirmou.
- Requisitos escritos em linguagem passiva ou vaga.

## Como revisar
Se, ao clarificar ou especificar, descobrir-se que uma ação importante de um módulo ficou de fora: voltar ao briefing, adicionar e re-validar. Nunca remendar só na spec.

## Sinal de travamento
- Humano não consegue descrever a dor real → ver [`protocolos/travamento.md`](../protocolos/travamento.md), seção "dor vaga".
- Humano diverge sobre quem é o público principal → **travar e escolher**, não seguir com dois públicos paralelos sem decisão.
