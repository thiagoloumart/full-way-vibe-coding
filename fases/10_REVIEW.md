# Fase 10 — Review

> Manual §17. Mesmo que o responsável não seja programador sênior, deve existir revisão mínima.

## Objetivo
Garantir que o código pronto pode ser mergado com segurança, sem pagar dívida oculta.

## Entradas
- Código implementado, testado, com quickstart validado.
- Diff da branch.

## Saídas
- `review.md` (usar [`templates/review.md`](../templates/review.md)).

## Objetivos da review (Manual §17)
**Não é:**
- Provar domínio profundo da linguagem.

**É:**
- Entender o que foi mexido.
- Identificar mudanças grandes demais.
- Observar validações.
- Observar padrões repetidos.
- Treinar leitura de código.
- Perceber coisas estranhas.

## Revisão mínima (Manual §17)
- **Arquivos alterados** — lista.
- **Migrations criadas** — verificar reversibilidade e idempotência.
- **Testes criados** — cobrem sucesso e erro?
- **Rotas alteradas** — expostas como deveria? versionadas?
- **Policies / permissões alteradas** — respeitam o modelo de papéis?
- **Integrações externas alteradas** — timeout, retry, fallback, idempotência?

## Perguntas-chave
- Alguma task do plano ficou fora do diff?
- Algum arquivo no diff não estava previsto no plano? (Se sim, foi necessário? atualizou o plano?)
- Mudanças estão dentro do escopo do módulo ou extrapolaram?
- Algum trecho viola a constituição?
- Algum mock de teste esconde comportamento real?
- Algum log expõe dado sensível?
- Alguma env var nova foi adicionada sem documentar?

## Em CRM / agentes / SaaS (Manual §29)
Revisar explicitamente:
- Gatilho, contexto, decisão, ação, bloqueio, fallback, log, critério de sucesso, falso positivo.
- Todas as ações têm log auditável?
- Papéis respeitados?

## Riscos da fase
- Revisão "🚀 LGTM" sem ler.
- Ignorar mudanças em policies/permissões.
- Aprovar diff com migrations não reversíveis.
- Deixar TODO/FIXME sem endereçar.

## Gate de avanço
- [ ] `review.md` preenchido.
- [ ] Checklist [`checklists/pre-merge.md`](../checklists/pre-merge.md) cumprido.
- [ ] Humano aprova explicitamente.

## O que invalida a fase
- Diff diverge do plano sem registro.
- Migrations sem plano de rollback.
- Policies alteradas sem teste.
- Review assinado por alguém que não leu.

## Sinal de travamento
- Diff contém mudanças fora do escopo do módulo → tirar da branch (cada spec tem sua branch, Manual §18).
- Review revela regra de negócio decidida pela IA sozinha → voltar à clarificação.
