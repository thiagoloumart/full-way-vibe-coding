# Protocolo de Antialucinação

> Objetivo: garantir que a skill **não** invente fato, requisito, regra, padrão, biblioteca, integração ou premissa que não esteja sustentada pelos inputs.

## Fontes autoritativas (em ordem de prioridade)
1. Documento principal: `Manual Operacional De Vibe Coding.pdf`.
2. Artefatos da feature atual: `briefing.md`, `spec.md`, `clarify.md`, `constitution.md`, `plan.md`, `tasks.md`, `analyze.md`.
3. Código existente do repositório (em brownfield).
4. Arquivos auxiliares explicitamente anexados (`PROMPT_BRIEFING.md`, `PROMPT_SPEC.md`, `spec-template.md`, `boas-praticas.pdf`).

Qualquer coisa fora disso é **inferência** e precisa ser marcada.

## Regras de marcação
- `[INFERÊNCIA]` — a skill deduziu algo plausível que não está literalmente nos inputs.
- `[NEEDS CLARIFICATION: tema]` — a skill não tem base suficiente; espera resposta.
- `[DECISÃO HUMANA: tema]` — regra sensível; não pode ser autocompletada.
- `[RISCO ASSUMIDO]` — o humano conscientemente avançou sem resolver.

## Checagens internas a cada fase
Antes de declarar um artefato "pronto", rodar mentalmente:

1. **Existe fonte?** Cada afirmação não marcada como `[INFERÊNCIA]` vem de qual fonte autoritativa? Se não houver → marcar.
2. **Consistência cruzada.** A afirmação contradiz algo escrito em outra fonte autoritativa? Se sim → travar.
3. **Regra de negócio.** A afirmação decide alguma regra sensível (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria)? Se sim e não veio de humano → marcar `[DECISÃO HUMANA]` e travar.
4. **Biblioteca/framework.** A skill mencionou uma lib/framework que não está declarada na constituição e não foi pedida? Se sim → remover ou marcar `[INFERÊNCIA]` com justificativa.
5. **Integração externa.** A skill presumiu que existe integração X? Se sim e não foi declarada → `[NEEDS CLARIFICATION]`.
6. **Quantitativos.** A skill citou "99,9% uptime", "latência <200ms", "até 1000 usuários" — estes números vieram do humano ou estão sendo inventados? Se inventados → marcar ou travar.
7. **Tempo / prazo.** A skill assumiu prazo? Se não foi dado → perguntar.

## Erros comuns a evitar
- Citar biblioteca por nome quando nada exigiu.
- Criar endpoint não pedido "porque é útil".
- Criar tabela não pedida "para o futuro".
- Copiar padrão "clean code" sem verificar se é a constituição do projeto.
- Assumir que stack do último projeto se aplica.
- Assumir idioma, fuso horário, moeda, formato de data sem ter certeza.
- Inventar unidades de medida de SC ("performance boa" → SC-001 vira "tempo de resposta < 200ms" sem base).

## Gate de saída
Antes de apresentar qualquer artefato ao humano:
- [ ] Zero afirmações não rastreáveis a uma fonte autoritativa.
- [ ] Zero regras sensíveis decididas pela IA.
- [ ] `[INFERÊNCIA]` visível onde deduzi plausivelmente.
- [ ] `[NEEDS CLARIFICATION]` visível onde falta base.
- [ ] Nenhum número, nome de lib ou integração "apareceu do nada".
