# Protocolo de Travamento

> Manual §§3, 5.4, 20. A IA não improvisa. Quando há bloqueio real, a IA para, explica e pergunta.

## Quando travar
A skill **deve travar** (parar o fluxo e pedir decisão humana) quando:

1. **Falta informação materialmente impeditiva.** Não dá para decidir a fase atual sem essa informação, mesmo com inferência razoável.
2. **Conflito real entre inputs.** Ex.: briefing diz X, spec aprovada antes dizia Y, clarify não resolve.
3. **Conflito com a constituição** (Manual §7). Pedido pontual viola decisão estrutural permanente.
4. **Regra de negócio sensível** (Manual §5.4) na iminência de ser decidida sem humano:
   - cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria.
5. **Divergência de direção** — o humano pediu A, o sistema já está caminhando para B há várias rodadas sem alinhamento.
6. **Ambiente inacessível** — repositório, API externa, credencial, seed obrigatório não disponível.

## Quando NÃO travar
A skill **não deve travar** (Manual §20):
- Por insegurança excessiva.
- Para pedir confirmação de algo óbvio.
- Para validar pequenas decisões de estilo que não afetam comportamento.
- Em decisão técnica com precedente claro na constituição ou no código existente.
- Quando há inferência razoável explicitada com `[INFERÊNCIA]`.

Nesses casos: **seguir, marcando explicitamente**, e deixar o humano corrigir se discordar.

## Formato do travamento

```
🛑 Travando — <fase>

📍 Onde travou
<descrição objetiva do ponto do fluxo>

🎯 Por que travou
<motivo concreto; o que não pode ser decidido sem humano>

❓ Perguntas objetivas (agrupadas)
  1. <pergunta>
  2. <pergunta>
  3. <pergunta>

💡 Opções possíveis
  A) <opção>
     prós: ...
     contras: ...
     impacto: ...
  B) <opção>
     ...
  C) <opção>
     ...

✅ Recomendação
  Opção <letra>, porque <motivo>.

⏳ Aguardando decisão humana antes de prosseguir.
📌 Pode retomar com: "decidimos <X>, continue".
```

## Regras de conduta durante o travamento
- **Uma rodada por vez.** Não continue perguntando após receber resposta parcial — incorpore, depois pergunte a próxima.
- **Agrupe perguntas correlatas.** Não travar três vezes em sequência sobre o mesmo tema.
- **Nunca improvise a resposta** e siga.
- **Nunca silenciosamente marque a questão como "resolvida".**
- **Ao retomar**, reforçar o que foi decidido: `✅ Decisão registrada: <X> (C-NNN). Retomando fase <Y>`.

## Pós-travamento
- Registrar decisão em `clarify.md` (se tema de spec) ou em `analyze.md` (se tema estrutural).
- Atualizar o artefato afetado.
- Marcar o travamento como resolvido.
