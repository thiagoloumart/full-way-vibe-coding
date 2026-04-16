# Fase 11 — Merge

> Manual §18. Regras de git, commits, branches e integração com master.

## Objetivo
Integrar a feature com a master de forma rastreável, reversível e sem conflito.

## Entradas
- Branch da feature com código + testes + quickstart + review aprovados.

## Saídas
- Commits organizados.
- Merge na master.
- Branch de feature encerrada (ou mantida por política).

## Regras (Manual §18)
- **Cada spec trabalha em branch própria.**
- **Antes de implementar:** fazer commit do estado documental (briefing, spec, clarify, constitution, plan, tasks, analyze).
- **Depois de validar o código:** fazer commit da implementação.
- **Depois disso:** merge para master.
- **Novas specs sempre começam da master atualizada.**

## Objetivo das regras
- **Rollback seguro** — sempre dá para voltar.
- **Isolamento de mudanças** — uma coisa por vez.
- **Histórico claro** — dá para ler a evolução.
- **Menos conflito** — branches curtas mergem mais rápido.

## Sequência recomendada
```
1. git checkout master && git pull
2. git checkout -b <NNN-nome-do-modulo>
3. Criar/editar artefatos documentais (briefing → analyze)
4. git add docs/... && git commit -m "docs(<módulo>): briefing + spec + plan"
5. Implementar por fase (F1, F2, ...)
   Após cada fase:
     git add <arquivos da fase> && git commit -m "feat(<módulo>): F<x> — <resumo>"
6. Rodar testes (Fase 8). Garantir verde.
7. Atualizar quickstart e review. Commit.
8. Abrir PR da branch para master.
9. Aguardar aprovação humana (Fase 10).
10. Merge (preferir "merge commit" ou "squash" conforme política).
11. git checkout master && git pull && git branch -d <branch>
```

## Regra adicional (Manual §18)
Se o humano não domina Git, a IA pode executar os comandos **mas o fluxo correto continua obrigatório**. Não pular commits ou pular branch para "ganhar tempo".

## Riscos da fase
- Merge sem testes verdes.
- Merge sem review aprovada.
- Nova spec iniciada de uma branch desatualizada.
- Mistura de duas features na mesma branch.
- Commits gigantes misturando docs + código + ajuste.

## Gate de avanço
- [ ] Branch deriva de master atualizada.
- [ ] Commits de docs e de código estão separados.
- [ ] Testes verdes na branch.
- [ ] Review aprovada.
- [ ] Checklist [`checklists/pre-merge.md`](../checklists/pre-merge.md) cumprido.
- [ ] Merge concluído sem conflito pendente.

## O que invalida a fase
- Merge com teste vermelho.
- Merge com `[NEEDS CLARIFICATION]` residual em qualquer doc.
- Merge com review assinada por conveniência.

## Sinal de travamento
- Conflito com master → resolver antes de mergar; se o conflito é semântico, voltar à análise.
- Master foi alterada por outra feature que afeta esta → reavaliar impacto e, se necessário, re-analisar (Fase 6).

## Após o merge
1. Branch encerrada.
2. Novo ciclo começa pela Fase 0 para o próximo módulo.
3. Novas specs sempre nascem da master agora atualizada.
