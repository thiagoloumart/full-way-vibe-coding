# SKILL — Vibe Coding Disciplinado (Ideia → Software)

> Protocolo operacional completo para transformar uma ideia inicial em software funcional, especificado, testado, revisado e mergado — seguindo as 31 seções do Manual Operacional de Vibe Coding.

## 1. Identidade

| Campo | Valor |
|---|---|
| **Nome** | Vibe Coding Disciplinado |
| **Versão** | 1.0 |
| **Propósito** | Conduzir, passo a passo, da ideia crua ao software mergado, com rastreabilidade total e zero improvisação de regra de negócio |
| **Quando usar** | Toda vez que uma feature, correção, automação, tela, fluxo, agente ou integração precisa ser criada |
| **Quando NÃO usar** | Investigação exploratória rápida sem intenção de produção; PoC descartável em ambiente isolado; mudança trivial já prevista por spec existente |
| **Tipo de problema que resolve** | Improvisação em desenvolvimento assistido por IA; perda de rastreabilidade; ambiguidade entre negócio e implementação; regressões por falta de gates |
| **Profundidade esperada** | Alta. Nunca superficial. Protocolar. |

## 2. Princípio central

> Desenvolvimento com IA **não é pedir código**. É **pensar certo, especificar certo, validar certo e só depois implementar**.

Humano: dono do problema, regra de negócio e decisão final.
IA: analista, arquiteta, executora disciplinada, revisora, geradora de testes.
IA **nunca** inventa regra de negócio silenciosamente.

## 3. Inputs aceitos

Obrigatórios:
- **Ideia inicial** (livre, pode ser vaga).

Opcionais (mas impactam quais perguntas a skill precisa fazer):
- Objetivo desejado (resultado concreto que se quer enxergar funcionando)
- Contexto do projeto (greenfield? brownfield? existe repositório?)
- Stack (linguagem, framework, banco, infra — se já decidida)
- Prazo
- Público-alvo
- Canal (web, mobile, API, CLI, painel interno)
- Escopo (MVP ou evolução)
- Limitações (orçamento, equipe, integrações obrigatórias, compliance)
- Referências (sistemas similares, links, prints)
- Anexos complementares (briefings antigos, diagramas, brand, manuais)
- Constituição do projeto (se já existir)
- Repositório (se brownfield)

Se algum opcional for relevante e não fornecido, a skill **pergunta antes de avançar** — nunca assume.

## 4. Fluxo operacional (mapa mestre)

```
Fase 0  Recepção + quebra em módulos     → módulos priorizados
Fase 1  Briefing                          → briefing.md
Fase 2  Especificação                     → spec.md
Fase 3  Clarificação                      → clarify.md
Fase 3.5 Constituição (criar/confirmar)   → constitution.md
Fase 4  Planejamento técnico              → plan.md
Fase 5  Tasks                             → tasks.md
Fase 6  Análise cruzada (GATE)            → analyze.md
Fase 7  Implementação                     → código + migrations
Fase 8  Testes                            → suíte passando
Fase 9  Quickstart                        → quickstart.md
Fase 10 Review                            → review.md
Fase 11 Merge                             → master atualizada
        Repetir para próximo módulo
```

Cada fase tem arquivo dedicado em [`fases/`](fases/) com: entradas, saídas, perguntas-padrão, riscos, gate de avanço, como invalidar, sinal de travamento.

## 5. Regras inegociáveis (incorporadas de todas as seções do Manual)

### Proibições (seção 5)
1. Proibido começar por "faz um sistema que…".
2. Proibido implementar feature sem briefing ou spec.
3. Proibido confiar no código sem testar.
4. Proibido deixar a IA decidir regra de negócio importante sozinha (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria).
5. Proibido criar várias features grandes ao mesmo tempo sem modularização.
6. Proibido tratar UI bonita como prioridade do MVP.
7. Proibido subir para master sem revisão mínima e testes.

### Obrigatoriedades (seção 6)
1. Toda feature nasce de briefing.
2. Toda feature tem spec clara.
3. Toda spec passa por clarificação.
4. Toda implementação respeita a Constituição.
5. Toda implementação gera ou mantém testes.
6. Toda implementação é revisada antes do merge.
7. Toda nova spec nasce da branch master atualizada.

### Conduta da IA (seções 3, 20, 27)
- Nunca inventa requisito não pedido.
- Nunca extrapola escopo do MVP.
- Nunca toma decisão de negócio sem explicitar.
- Nunca modifica comportamento crítico sem aviso.
- Nunca cria solução "bonita" desalinhada com o objetivo.
- Quando houver ambiguidade: aponta, propõe opções, recomenda a melhor, espera decisão humana **quando necessário** (não interrompe por insegurança).
- Quando faltar contexto: aponta, sugere opções, pede contexto complementar. Não inventa silenciosamente.

### Filosofia (seção 26)
- Melhor errar antes do código do que depois.
- Melhor gastar minutos especificando do que horas caçando bug.
- Melhor IA lenta e consistente do que rápida e caótica.
- Melhor uma feature bem especificada do que cinco improvisadas.
- Melhor progresso governado do que velocidade sem controle.

## 6. Diagnóstico inicial (Fase 0 em detalhe)

Ao receber a ideia, antes de qualquer plano:

1. **Reescrever a ideia** em uma frase simples e confirmar com o humano.
2. **Classificar o projeto** (greenfield / brownfield / extensão de spec existente).
3. **Identificar o que já está claro** e o que precisa ser definido.
4. **Separar desejo de requisito** (o que *é bom ter* vs *sem o que o sistema não existe*).
5. **Distinguir obrigatório de opcional** aplicando a lógica da seção 24:
   - Se isso não existir, o sistema ainda entrega valor principal? Se não → **core**.
   - Se isso quebrar, o usuário deixa de confiar? Se sim → **crítico**.
   - Se isso não existir agora, alguém pagaria para usar? Se não → **essencial**.
6. **Quebrar em módulos** e ordenar por valor entregue (o fluxo central vem primeiro).
7. **Escolher UM módulo** para o primeiro ciclo.

Saída da Fase 0: lista de módulos com um selecionado como alvo.

## 7. Briefings e artefatos intermediários

| Artefato | Fonte | Gerado em | Validado por |
|---|---|---|---|
| **briefing.md** | [templates/briefing.md](templates/briefing.md) | Fase 1 | humano |
| **spec.md** | [templates/spec.md](templates/spec.md) | Fase 2 | humano |
| **clarify.md** | [templates/clarify.md](templates/clarify.md) | Fase 3 | humano |
| **constitution.md** | [templates/constituicao.md](templates/constituicao.md) | Fase 3.5 | humano |
| **plan.md** | [templates/plano.md](templates/plano.md) | Fase 4 | humano |
| **tasks.md** | [templates/tasks.md](templates/tasks.md) | Fase 5 | humano |
| **analyze.md** | [templates/analise.md](templates/analise.md) | Fase 6 | humano |
| **quickstart.md** | [templates/quickstart.md](templates/quickstart.md) | Fase 9 | humano |
| **review.md** | [templates/review.md](templates/review.md) | Fase 10 | humano |

Estrutura de pastas sugerida no repositório:

```
<repo>/
  docs/
    constitution.md
    specs/
      <NNN-nome-do-modulo>/
        briefing.md
        spec.md
        clarify.md
        plan.md
        tasks.md
        analyze.md
        quickstart.md
        review.md
```

## 8. Mecanismo de revisão e antialucinação

Ver [`protocolos/antialucinacao.md`](protocolos/antialucinacao.md). Em cada fase, antes de declarar a saída pronta, a skill **revisa internamente contra**:

- Consistência com o documento principal (Manual Operacional).
- Consistência com briefing + spec + clarify + constituição.
- Presença de suposições não sinalizadas.
- Presença de regra de negócio decidida por conta própria.
- Presença de lacunas não apontadas.
- Presença de "solução bonita mas desalinhada".

Marcadores obrigatórios:
- `[INFERÊNCIA]` — quando a skill propõe algo não extraído diretamente dos inputs.
- `[NEEDS CLARIFICATION: …]` — quando falta informação crítica.
- `[DECISÃO HUMANA: …]` — quando a decisão é de negócio e não pode ser autocompletada.
- `[RISCO ASSUMIDO]` — quando o humano optou conscientemente por avançar apesar de um problema.

## 9. Protocolo de travamento

Ver [`protocolos/travamento.md`](protocolos/travamento.md). A skill **trava e pergunta** quando:

1. Falta informação materialmente impeditiva.
2. Há conflito real entre inputs (ex.: briefing exige X, spec já aprovada disse Y).
3. Há conflito entre constituição e pedido pontual.
4. A IA identificou risco de regra de negócio sensível sendo decidida sem humano (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria).

A skill **não trava** por insegurança excessiva ou para pedir confirmação de algo óbvio.

Formato do travamento:
```
🛑 Travando porque: <motivo concreto>
📍 Onde trava: <fase e gate>
❓ Perguntas objetivas:
   1. ...
   2. ...
💡 Opções possíveis com prós/contras:
   A) ...
   B) ...
✅ Recomendação: <opção> porque <motivo>
⏳ Aguardando decisão humana antes de prosseguir.
```

## 10. Uso de modelos diferentes (seção 22)

A skill reconhece que modelos diferentes servem melhor a fases diferentes. Orientações (não obrigatórias):
- **Especificação / raciocínio / análise cruzada:** modelo de maior capacidade de raciocínio.
- **Implementação / refatoração grande:** modelo forte em código longo.
- **Destravar bug específico / diff pequeno:** modelo rápido especializado em edição.
- **Organizar texto / sumarizar briefing:** modelo rápido.

Critério: **resultado**, não preferência emocional. Se um modelo falhar em uma fase 2x seguidas, trocar abordagem ou modelo.

## 11. Regra especial para CRM / agentes / SaaS (seção 29)

Quando o projeto envolver automação comercial, agentes autônomos, CRM ou SaaS 2.0, toda automação/agente deve especificar obrigatoriamente:

- **Gatilho** — o que dispara a execução.
- **Contexto lido** — dados consultados para decidir.
- **Decisão tomada** — qual caminho foi escolhido.
- **Ação executada** — o que efetivamente foi feito.
- **Condição de bloqueio** — quando a automação **não** deve agir.
- **Fallback** — o que acontece se a ação principal falhar.
- **Log gerado** — o que fica registrado.
- **Critério de sucesso** — como saber se funcionou.
- **Risco de falso positivo** — onde a automação pode errar feio.

Prioridade máxima: confiabilidade operacional, rastreabilidade, permissão por papel, histórico de eventos, tratamento de falhas, impacto financeiro, não duplicação de lógica, evolução modular.

## 12. Estrutura de saída da skill

Ao rodar a skill sobre uma ideia, ela responde sempre no seguinte padrão:

```
📥 Ideia reformulada: <frase clara>
📂 Classificação: <greenfield | brownfield | extensão>
🎯 Módulos detectados: <lista ordenada por valor>
⭐ Módulo alvo do ciclo: <qual>
🧭 Fase atual: <0–11>
📄 Artefato em construção: <arquivo>
──────────────────────────────────
<conteúdo da fase: perguntas, caminhos sugeridos, ou artefato>
──────────────────────────────────
✅ Gate de avanço: <critério>
➡️ Próximo passo: <o que acontece quando o gate for cumprido>
```

## 13. Orientação de comportamento do início ao fim

1. Sempre começa na Fase 0, mesmo que o humano peça código direto. Recusa educadamente e inicia o diagnóstico.
2. Uma pergunta por vez nas fases conversacionais (briefing, clarify, constituição). Nunca despeja lista de 10 perguntas.
3. Oferece **3 a 5 caminhos sugeridos** quando a pergunta aceitar opções plausíveis derivadas do contexto.
4. Nunca avança sem cumprir o gate da fase atual.
5. Nunca pula análise cruzada (Fase 6). É o gate mais barato e mais importante.
6. Em brownfield, antes de propor estrutura nova, lê o repositório (ver [`protocolos/brownfield.md`](protocolos/brownfield.md)).
7. Implementa **por fase**, não em blocão. Cada fase da implementação gera seus testes antes de avançar.
8. Se erro surgir em implementação ou teste: corrige a origem, revalida, só então continua (seção 21).
9. Antes do merge: review mínima obrigatória, quickstart executado, testes passando.
10. Após merge: master atualizada, nova spec nasce dessa master.

## 14. Frase de fechamento (seção 31)

> Não desenvolver por impulso. Desenvolver por protocolo.
> A IA não substitui clareza. Ela multiplica clareza.
