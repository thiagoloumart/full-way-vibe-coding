# Fase 7 — Implementação

> Manual §14. A IA só deve implementar depois que briefing, spec, clarify, plano, tasks e análise estiverem prontos (ou o risco tiver sido assumido conscientemente).

## Objetivo
Executar o plano **por fase**, respeitando a constituição e reaproveitando padrões existentes. Entregar código + migrations + testes que passem no gate.

## Entradas
- Todos os artefatos anteriores validados.
- Branch de feature criada a partir de master atualizada (Manual §18).

## Saídas
- Código implementado.
- Migrations, se aplicável.
- Testes automatizados (Fase 8 executa, mas já nascem aqui).
- Atualizações pontuais em `plan.md` / `tasks.md` com status.

## Regras (Manual §14)
A IA deve implementar:
- **Por fase** (nunca tudo de uma vez).
- **Seguindo as tasks** em ordem de dependência.
- **Respeitando a constituição.**
- **Reaproveitando padrões existentes.**
- **Sem improvisar arquitetura nova desnecessariamente.**

Durante a implementação a IA **pode**:
- Rodar comandos.
- Criar migrations.
- Criar testes.
- Corrigir erros intermediários.
- Validar se a fase foi concluída antes de avançar.

## Loop de execução (por fase do plano)
```
1. Selecionar F<x> e suas tasks dependentes-primeiro.
2. Criar testes mínimos da F<x> (ou pelo menos os de sucesso).
3. Implementar tasks na ordem.
4. Rodar testes da F<x>.
5. Se falha:
   - Diagnosticar a origem (Manual §21).
   - Corrigir a origem, não o sintoma.
   - Re-rodar.
   - Só avançar após verde.
6. Revalidar que a F<x> cumpre seu critério de "pronto".
7. Commit documental + commit de implementação.
8. Avançar para F<x+1>.
```

## Comportamento frente a regras sensíveis
Durante o código, se aparecer decisão não prevista em clarify envolvendo cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico ou auditoria: **parar, voltar a clarify, decidir, atualizar spec**. Nunca improvisar.

## Comportamento em brownfield
- Antes de criar um arquivo novo, checar se há arquivo semelhante.
- Antes de criar função utilitária, checar se já existe.
- Antes de adicionar dependência, checar se a função já está em uma lib já adotada.

## Comportamento em projeto novo
- Seguir a constituição estrita.
- Preferir abstração mínima (não antecipar futuro não pedido).
- Preferir clareza a cleverness.

## Riscos da fase
- Implementar feature inteira antes de testar.
- "Pular" a fase de teste para ganhar tempo (Manual §21: proibido).
- Gerar código além do escopo (Manual §3: não extrapolar MVP).
- Introduzir biblioteca sem justificar (Manual §11 / §7).
- Fazer diff gigante sem pausas — dificulta review.

## Gate de avanço
- [ ] Todas as fases do plano foram implementadas na ordem prevista.
- [ ] Cada fase teve seu critério de "pronto" cumprido antes de avançar.
- [ ] Nenhuma regra sensível foi decidida sem passar por clarify.
- [ ] Testes da Fase 8 já foram criados e estão prontos para execução completa.

## O que invalida a fase
- Fase concluída sem teste.
- Código contradiz a constituição.
- Código adiciona arquivo/feature fora do plano sem atualizar plano + análise.

## Sinal de travamento
- Erro persistente após duas tentativas → trocar abordagem ou modelo (Manual §21/§22).
- A implementação revela que a spec estava errada → **parar**, voltar à spec, re-clarificar, re-analisar. Não continuar implementando sobre base quebrada.
