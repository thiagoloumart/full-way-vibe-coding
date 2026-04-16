# Checklist — Qualidade do Briefing

Aplicar no fim da Fase 1, antes de avançar.

## Fundamentos
- [ ] O briefing está escrito em **linguagem de negócio**, sem framework/biblioteca/ORM/banco.
- [ ] Cada ação descrita usa **verbo concreto** (emitir, aprovar, cancelar, cadastrar, avisar).
- [ ] Nenhuma frase usa "tem que ter controle / funcionar bem / organizar tudo" sem detalhamento.

## Cobertura (Manual §8)
- [ ] Problema real descrito com relatos.
- [ ] Quem sofre o problema está claro.
- [ ] Resultado que o sistema entrega está claro.
- [ ] Quem usa está claro (papéis).
- [ ] Fluxo principal está descrito em alto nível.
- [ ] Modelo de cobrança (se existir) está definido.
- [ ] Papéis de usuário estão definidos com permissões de alto nível.
- [ ] Módulos mínimos estão listados.

## Condução (absorve `PROMPT_BRIEFING.md`)
- [ ] Cada módulo passou pela pergunta cíclica: "tem mais alguma ação?"
- [ ] Cada módulo passou pela checagem final leve: "se estivesse pronto hoje, faltaria função?"
- [ ] Respostas vagas foram provocadas e detalhadas.

## Fronteiras
- [ ] Não-objetivos estão listados (o que **não** faz parte).
- [ ] Restrições (prazo, equipe, compliance, integrações obrigatórias) estão registradas.

## Qualidade geral
- [ ] Público primário e secundário identificados.
- [ ] Contexto de uso explicitado (mobile, web, presencial, remoto).
- [ ] Canais / superfícies listados.
- [ ] Itens em aberto listados (vão virar `[NEEDS CLARIFICATION]` na spec).

## Validação
- [ ] Humano leu e aprovou o briefing.
- [ ] Não há decisão técnica oculta travestida de requisito.

Se qualquer item ficou `❌`: **não avançar** para Fase 2.
