# Checklist — Pré-Merge (antes da Fase 11)

> Manual §§17–18: revisão mínima e disciplina de git são obrigatórias antes do merge para master.

## Código
- [ ] Todos os testes verdes (unitários, integração, contrato, E2E, regressão).
- [ ] Nenhum teste foi desativado/ignorado para passar.
- [ ] Nenhuma `TODO/FIXME` bloqueante deixada sem dono.

## Documentação da feature
- [ ] `quickstart.md` atualizado e validado manualmente por alguém que não implementou.
- [ ] `review.md` preenchido e aprovado.
- [ ] `analyze.md` reflete o diff real; divergências estão justificadas.

## Git
- [ ] Branch deriva de master atualizada.
- [ ] Commits separam docs, implementação por fase, ajustes, testes.
- [ ] Branch dedicada a esta spec (não há mistura com outra feature).
- [ ] Conflitos com master resolvidos.

## Constituição e regras sensíveis (Manual §5.4 e §7)
- [ ] Nenhuma biblioteca nova foi adicionada fora do que a constituição permite.
- [ ] Nenhum log expõe dado sensível.
- [ ] Nenhuma regra sensível foi introduzida no código sem passar por `clarify.md`.
- [ ] Migrations são reversíveis e têm plano de rollback.

## CRM / agentes / SaaS (Manual §29 — quando aplicável)
- [ ] Cada automação registra: gatilho, contexto, decisão, ação, bloqueio, fallback, log, critério de sucesso, risco de falso positivo.
- [ ] Logs auditáveis em todas as ações críticas.
- [ ] Papéis respeitados.

## Aprovação
- [ ] Humano assinou a review.

Se qualquer item `❌`: **não mergar**.
