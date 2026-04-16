# Checklist — Pré-Implementação (antes da Fase 7)

> Manual §14: a IA só deve implementar depois que este checklist estiver integralmente cumprido — ou, para os itens não cumpridos, risco deve estar explicitamente assumido.

## Documentação
- [ ] `briefing.md` validado.
- [ ] `spec.md` estável (sem `[NEEDS CLARIFICATION]`).
- [ ] `clarify.md` fechada; todas as decisões sensíveis são humanas.
- [ ] `constitution.md` validada (v0 com `[RISCO ASSUMIDO]` explícito, se for o caso).
- [ ] `plan.md` aprovado.
- [ ] `tasks.md` com dependências claras.
- [ ] `analyze.md` com veredicto "pode seguir" ou riscos formalmente assumidos.

## Ambiente
- [ ] Branch criada a partir de master atualizada.
- [ ] Commit documental feito (docs antes do código — Manual §18).
- [ ] Env vars e credenciais disponíveis.
- [ ] Banco e serviços dependentes acessíveis.
- [ ] Suíte de testes existente passando na master.

## Conduta
- [ ] Implementação será feita **por fase**, não em blocão.
- [ ] A ordem das tasks segue as dependências declaradas.
- [ ] Nenhuma regra sensível pendente está no caminho da primeira fase sem decisão humana.
- [ ] Em brownfield: leitura do repositório concluída; reutilização identificada.

Se algum item falhou e não há `[RISCO ASSUMIDO]` registrado → **não iniciar** a implementação.
