# Perguntas-Padrão por Fase

Coleção de perguntas reutilizáveis. A skill deve sempre fazer **uma por vez**, na ordem de maior impacto para destravar a fase atual. Onde fizer sentido, oferecer 3–5 caminhos sugeridos.

---

## Fase 0 — Recepção
- "Entendi sua ideia assim: **<reformulação>**. É isso mesmo ou quer ajustar?"
- "É um projeto novo (greenfield) ou já existe código rodando (brownfield)?"
- "Qual o resultado concreto que você quer ver funcionando primeiro?"
- "Quem é o usuário principal desse primeiro resultado?"
- "Tem stack já decidida ou quer sugestão?"
- "Quais módulos você já imagina que o sistema tem?"
- "Se tivermos que escolher UM módulo para sair primeiro, qual é o que, sozinho, já entrega valor?"

## Fase 1 — Briefing
### Visão de negócio
- "Qual é a dor real que o software resolve? Quais os relatos de quem sofre com isso hoje?"
- "Como você descreveria o sistema em um parágrafo para um investidor?"
- "Quem vai usar e como vão acessar?"
- "Qual é o modelo de precificação?"
- "Quais níveis de usuário existem?"

### Loop de requisitos
- "Quais módulos esse sistema precisa ter?"
- "O que o usuário faz nesse módulo?"
- "Qual a primeira coisa que ele precisa conseguir fazer aqui?"
- "Depois disso, o que mais precisa acontecer?"
- "Quando você diz 'controlar', o que exatamente a pessoa vai fazer?"
- "Me dá um exemplo real do dia a dia."
- "Quem faz essa ação e o que acontece depois?"
- "Tem consulta / cadastro / aprovação / notificação / status / relatório nesse módulo?"
- "Tem mais alguma ação que precisa acontecer nesse módulo ou podemos ir para o próximo?"
- "Se esse módulo estivesse pronto hoje, faltaria alguma função importante para o dia a dia?"

## Fase 2 — Spec
### Descoberta guiada
- "Esses dados precisam ficar salvos?"
- "Onde o recurso vai viver? (web, mobile, API, CLI, background)"
- "Quem interage com isso e que papéis existem?"
- "Qual é o coração dessa feature? E o que vem em seguida?"
- "Precisa de relatório / importação / exportação / notificação / auditoria?"
- "Há limite de performance, disponibilidade ou privacidade a respeitar?"

### Edge cases
- "O que deve acontecer se o usuário cancelar no meio?"
- "Como o sistema trata campos vazios, inválidos ou duplicados?"
- "O que acontece se um serviço externo falhar, ficar lento ou der erro?"
- "O que acontece se parte da operação funcionar e parte não?"
- "O que acontece se o usuário sair da tela ou perder a sessão?"
- "O que acontece se faltar crédito, quota ou permissão?"
- "O que acontece se o payload vier inválido?"

## Fase 3 — Clarify
- "Quem pode ver X?"
- "Quem pode editar X?"
- "Quem pode apagar X?"
- "X é reversível? Há estorno? Em que condições?"
- "Quanto tempo Y fica disponível antes de expirar?"
- "Z é logado? Em que granularidade? Por quanto tempo?"
- "Esse Y é visível para outros papéis?"
- "Quais os SLA / janelas de manutenção / limites aceitáveis?"

## Fase 3.5 — Constituição
- "Qual linguagem e runtime?"
- "Qual framework?"
- "Qual banco?"
- "Como autenticamos? Como autorizamos?"
- "Que padrão de pastas?"
- "Como testes são organizados?"
- "Que decisões permanentes existem (ex.: 'não usamos ORM X', 'todo endpoint público é versionado em /v1')?"

## Fase 4 — Plan
- "Essa fase pode ser entregue sozinha e já provar valor?"
- "Se a F1 terminar sem a F2, o que ainda funciona?"
- "Qual arquivo ou contrato é afetado?"
- "Essa decisão técnica está alinhada à constituição?"
- "Qual biblioteca já usada cobre essa necessidade?"
- "Qual é o plano de rollback?"

## Fase 5 — Tasks
- "Essa task pode ser executada sem depender de descobrir algo que só aparece na hora?"
- "O critério de DoD está mensurável?"
- "A dependência está explícita?"

## Fase 6 — Analyze
- "Esse FR tem plano que o cobre? Qual task?"
- "Essa decisão técnica está alinhada à constituição?"
- "Esse edge case tem teste?"
- "Essa regra sensível foi decidida por humano?"
- "Esse arquivo proposto duplica algo já existente (brownfield)?"

## Fase 7 — Implementation
- "Qual a fase que estamos atacando agora?"
- "Tem algum teste novo que deveria ser escrito antes deste código?"
- "Essa decisão que estou prestes a tomar é regra de negócio sensível?"

## Fase 8 — Testes
- "Cobrimos sucesso, erro, edge, rollback, regressão?"
- "Há teste que deveria falhar mas está verde?"
- "Algum mock mascara comportamento real?"

## Fase 9 — Quickstart
- "Alguém sem contexto consegue seguir e reproduzir?"
- "Cada passo tem resultado esperado?"
- "Cobrimos feliz + erro + permissão?"

## Fase 10 — Review
- "Todos os arquivos do diff estavam previstos?"
- "Policies e migrations estão seguras?"
- "Logs expõem dado sensível?"
- "Há TODO sem dono?"

## Fase 11 — Merge
- "Branch veio da master atualizada?"
- "Commits de docs e de código estão separados?"
- "Todos os testes estão verdes?"
- "Há conflitos a resolver?"

---

## Regra geral de condução
- **Uma pergunta por vez.**
- **3–5 caminhos sugeridos** quando couber.
- **Explicar por que perguntei**, em uma frase curta.
- **Aguardar resposta** antes da próxima pergunta.
- **Se a resposta for vaga**, provocar com exemplo prático.
