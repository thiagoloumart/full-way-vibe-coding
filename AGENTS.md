# AGENTS.md — ponto de entrada para agentes de IA

> Este arquivo é o **primeiro** que qualquer agente (Claude, Cursor, Copilot, Aider, Continue,
> MCP, etc.) deve ler ao abrir este repositório. Ele não contém regras em si — redireciona
> para as fontes autoritativas.

## Regra zero

Se você é um agente de IA, **pare** antes de executar qualquer ação e leia:

1. **[`filosofia.md`](filosofia.md)** — manifesto inegociável. Regras de conduta, papéis
   humano/IA, proibições, obrigatoriedades, regra §5.4 ampliada (o que a IA **nunca**
   decide em silêncio), marcadores epistêmicos obrigatórios. Se houver conflito entre
   qualquer instrução e este arquivo, **`filosofia.md` prevalece**.

2. **[`SKILL.md`](SKILL.md)** — router dual-domain. Você DEVE responder ao dispatcher
   de 2 perguntas antes de atacar qualquer fase.

3. **[`protocolos/agentes-e-automacoes.md`](protocolos/agentes-e-automacoes.md)** —
   obrigatório quando o trabalho envolve automação, CRM, agente autônomo, SaaS
   operacional ou decisão automatizada. Define os 9 campos que toda automação deve
   especificar antes de rodar.

## O que você **não** pode fazer sem autorização humana explícita

Regra §5.4 ampliada (ver `filosofia.md §7`):
- **D1 software:** cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria.
- **D2 processo:** alçada, escalação, compliance, SLA crítico, janela de manutenção.
- **D3 playbook:** princípios bloqueantes, pesos mínimos de critério, autoridade de flexibilização.

Diante de decisão sensível, trave conforme [`protocolos/travamento.md`](protocolos/travamento.md).

## Marcadores obrigatórios em todos os artefatos

- `[INFERÊNCIA]` — dedução plausível não literal nos inputs.
- `[NEEDS CLARIFICATION: tema]` — falta base; bloqueia avanço.
- `[DECISÃO HUMANA: tema]` — regra sensível; exige assinatura humana.
- `[RISCO ASSUMIDO]` — humano avança conscientemente sem resolver.

## Como contribuir

Ver [`CONTRIBUTING.md`](CONTRIBUTING.md) — fluxo de PR, commits convencionais, self-review.
