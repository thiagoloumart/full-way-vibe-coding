# Fase 9 — Quickstart / Teste Manual

> Manual §16. Toda spec implementada deve gerar um roteiro de validação manual.

## Objetivo
Produzir um roteiro curto que permita a qualquer pessoa validar, **na mão**, que a feature funciona — inclusive quem não participou da implementação.

## Entradas
- Código implementado e com testes verdes.
- Spec + clarify.

## Saídas
- `quickstart.md` (usar [`templates/quickstart.md`](../templates/quickstart.md)).

## O roteiro deve dizer (Manual §16)
- Quais **comandos** rodar.
- Quais **telas** abrir.
- Quais **ações** executar.
- Quais **resultados** esperar.

## Estrutura recomendada
```
# Quickstart — <módulo>

## Pré-requisitos
- Dependências instaladas: <comando>
- Banco rodando: <comando>
- Env vars necessárias: <lista>
- Seeds aplicados: <comando>

## Subir localmente
1. Comando A → resultado esperado
2. Comando B → resultado esperado

## Caminho feliz
1. Acessar <url/tela>
2. Executar <ação>
3. Observar <resultado>

## Caminho de erro
1. Executar <ação inválida>
2. Observar <mensagem de erro>

## Caminho de permissão
1. Entrar como <papel sem permissão>
2. Tentar <ação>
3. Observar <bloqueio>

## Como reverter / limpar
- <comando de rollback> (quando aplicável)
```

## Princípios
- Deve ser reprodutível por alguém sem contexto do código.
- Cada passo tem **resultado esperado** explícito.
- Preferir comandos idempotentes (pode rodar de novo).
- Incluir o caminho de rollback quando houver operação crítica.

## Riscos da fase
- Quickstart genérico ("abra a tela e veja se funciona").
- Omitir env vars ou seeds necessários.
- Resultado esperado ambíguo.
- Não testar caminho de erro / permissão.

## Gate de avanço
- [ ] Quickstart escrito.
- [ ] Quickstart executado manualmente com sucesso pelo humano ou por alguém que não implementou.
- [ ] Caminho feliz + erro + permissão cobertos.

## O que invalida a fase
- Alguém segue o quickstart e não consegue reproduzir.
- Passos fora de ordem (dependências quebradas).

## Sinal de travamento
- A execução manual revelou divergência com a spec → voltar à implementação.
- Falta env var ou credencial → pedir ao humano antes de declarar pronto.
