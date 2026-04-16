# Protocolo de Erros e Retry

> Manual §21. Como a IA deve se comportar quando algo dá errado durante o ciclo.

## Erro em implementação
1. **Não pular** para a próxima fase.
2. **Não ignorar** a falha.
3. **Corrigir a origem**, não o sintoma (não adicione try/except só para esconder o erro).
4. **Revalidar** a fase depois da correção.
5. **Só então** continuar.

## Erro em teste
1. **Corrigir** a causa raiz.
2. **Reexecutar** o teste.
3. **Garantir** que a mudança nova não quebrou a antiga (regressão).
4. Se o teste expõe uma contradição na spec → voltar à spec, re-clarificar, re-analisar.

## Se o problema persistir
Critério: o mesmo erro repetiu em **duas tentativas** apesar de correções feitas.

Opções (Manual §§21, 22):
- **Trocar abordagem** — o desenho atual pode estar errado; voltar a uma fase anterior e redesenhar.
- **Trocar modelo** — outro modelo pode ser mais eficaz para essa tarefa específica.
- **Fatiar mais fino** — a fase pode estar grande demais; quebrar em passos menores.
- **Travar e pedir input humano** — o contexto talvez esteja insuficiente.

## Não fazer
- Inserir `try/catch` genérico para fazer o teste passar.
- Silenciar logs de erro.
- Marcar teste como `skip` sem registro.
- Commitar "fix temporário" sem dono e sem data de resolução.
- Alterar a assertion para acomodar o bug.

## Registro
Cada tentativa que não resolveu deve ser documentada brevemente em `analyze.md` ou em comentário de commit, com:
- O que foi tentado.
- Por que falhou.
- Qual é a hipótese para a próxima tentativa.

## Sinal de parar
- 3 tentativas sem progresso → travar (ver [`travamento.md`](travamento.md)).
- Evidência de que a spec está errada → voltar à Fase 2.
- Evidência de que a constituição é incompatível com o que o humano quer → travar e negociar.
