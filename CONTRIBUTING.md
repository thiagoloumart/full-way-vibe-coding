# Contribuindo com `full-way-vibe-coding`

> Este repositório é a própria skill. Contribuições passam pelo método que a skill define.
> Não há exceção para "apenas uma correção rápida".

## 1. Antes de contribuir, leia

1. [`AGENTS.md`](AGENTS.md) — ponto de entrada.
2. [`filosofia.md`](filosofia.md) — regras inegociáveis (incluindo §5.4 ampliada).
3. [`SKILL.md`](SKILL.md) — fluxo de 15 fases (0 → 12).
4. [`governanca/adr-global.md`](governanca/adr-global.md) — quando sua mudança exige ADR.

## 2. Fluxo de contribuição (PR obrigatório)

Nenhum commit direto em `main`. Todo trabalho em branch:

1. **Classifique o tipo da mudança** respondendo ao dispatcher de `SKILL.md`:
   - `A` (software do próprio repo): lint/harness, templates, fases.
   - `C` (playbook): novos protocolos, extensões da filosofia.
   - Qualquer mudança em regra inegociável (`filosofia.md §§4, 5, 7`) = **ADR obrigatória**.

2. **Branch name convention:** `<tipo>/<wave-ou-escopo>-<slug-curto>`
   Ex: `w1/lint-artefato`, `w2/canonical-confirmacao-consultas`, `fix/link-broken-bmad`.

3. **Commits convencionais:** `tipo(escopo): descrição`
   - `feat` — nova capacidade.
   - `fix` — corrige bug de documentação/coerência.
   - `docs` — doc-only.
   - `refactor` — reorganiza sem mudar comportamento.
   - `chore` — metadata, config, housekeeping.

4. **Tamanho do PR:** alvo ≤200 linhas de diff, limite duro 500. Acima de 500, fragmentar.

5. **Self-review obrigatório:** antes de pedir merge, abrir [`templates/review.md`](templates/review.md)
   e preencher como se fosse outra pessoa. Anexar ao PR.

## 3. Quando sua mudança exige ADR

Abra ADR em `governanca/adrs/ADR-NNN-<slug>.md` (usando [`templates/adr.md`](templates/adr.md))
quando sua mudança:

- Altera **Camada 1** (invariantes) de `templates/constituicao.md` — exige **major bump**.
- Altera **Camada 2** (escolhas) — exige **minor bump**.
- Muda numeração de fases, nomes de artefatos canônicos, ou marcadores epistêmicos.
- Muda comportamento do harness (lint, gate, smoke) de forma que rejeita artefatos
  anteriormente válidos.

Decisões locais a um módulo ficam em `decision_log.md` do módulo, não em ADR global.

## 4. Marcadores epistêmicos obrigatórios

Em qualquer artefato (spec, bmad, clarify, review, etc.):

- `[INFERÊNCIA]` — dedução plausível, não literal nos inputs.
- `[NEEDS CLARIFICATION: tema]` — falta base; bloqueia avanço da fase.
- `[DECISÃO HUMANA: tema]` — regra sensível §5.4; exige assinatura humana.
- `[RISCO ASSUMIDO]` — humano avança conscientemente sem resolver.

## 5. Harness e lint

- M1 (hoje): harness é doc-only; lint **não** roda em CI.
- M2 (próximo milestone): lint em CI, estágio E1 (warning) → E2 (parcial bloqueante) → E3 (bloqueante total).
- Ver [`harness/rollout.md`](harness/rollout.md) para política de transição.

## 6. Dúvida comum

**"Minha mudança é pequena — preciso de PR?"**
Sim. A skill prega rigor de review; se o próprio repo faz exceção, a skill perde credibilidade.
Self-review é aceito em PR de 1 autor se o checklist de [`templates/review.md`](templates/review.md)
estiver preenchido.

**"Posso editar filosofia.md?"**
Não sem ADR com `camada_afetada: 1` e aprovação humana explícita + major bump.
Ver [`governanca/adr-global.md`](governanca/adr-global.md).

**"Achei um link quebrado, posso consertar direto em main?"**
Não. Branch `fix/link-<destino>` + PR. A disciplina é o ponto.
