---
artefato: adr
fase: null
dominio: [any]
schema_version: 1
adr_id: ADR-008
status: Proposta
camada_afetada: 2
data: 2026-04-23
autor: Thiago Loumart
requer:
  - "Contexto"
  - "Decisão"
  - "Alternativas consideradas"
  - "Consequências"
  - "Relação com Constituição"
---

# ADR-008 — Matriz "Pre-mortem BMAD × Mitigação" obrigatória na Fase 6 Analyze

**Status:** Proposta
**Data:** 2026-04-23
**Autor:** Thiago Loumart
**Camada afetada:** 2 (processo da skill — `fases/06_ANALYZE.md` + `templates/analise.md`)
**Bump de Constituição:** minor da skill (sincronizado com ADR-007 na mesma safra v1.3)

**Origem:** `examples/canonical-software/001-confirmacao-consultas/retrospective.md §4` proposta ADR-G-002.

---

## Contexto

No ciclo canônico-001, o `bmad.md §3.4` registrou **3 pre-mortems** para o Caminho D escolhido:

1. Meta Cloud API reclassificar categoria `utility` → envio bloqueado.
2. Z-API banido pelo WhatsApp → contas das clínicas perderem acesso.
3. Paciente idoso sem WhatsApp → clínica percebe que sistema "não automatiza tudo".

Durante o ciclo, **as mitigações foram implementadas** — mas **dispersas** nos artefatos:

- Pre-mortem #1 → mitigado por "alerta crítico na categoria" em `plan.md §8` (F4) + crit. invalidação D-002.
- Pre-mortem #2 → neutralizado por design em ADR-L-001 (F4) — Meta como default, Z-API stub irmão.
- Pre-mortem #3 → mitigado por evento `sem-canal` adicionado em P-03 do Analyze (F6) + US3 fallback humano (F2).

**O problema:** sem matriz explícita cruzando pre-mortem × mitigação, um auditor (ou IA futura) que ler só o BMAD e depois só o Analyze **não consegue verificar** se cada pre-mortem ganhou escudo. A mitigação fica como "retórica estratégica" em vez de virar artefato rastreável.

No retrospective §3 "Pre-mortems revisitados", a tabela foi construída **manualmente** pela IA — isto é o sinal que ela **deveria existir no Analyze**. O retrospective §6.1 aprendizado #2 registrou como "faríamos diferente".

## Decisão

**`fases/06_ANALYZE.md` passa a exigir nova matriz obrigatória na seção `§6.a Pre-mortem × Mitigação`**, com estrutura:

| Pre-mortem BMAD §3.4 | Task / Arquivo / ADR que mitiga | Tipo de mitigação | Gate de verificação | Status |
|---|---|---|---|---|
| ... | ... | (design ∥ teste ∥ monitoramento ∥ plano B) | ... | 🟢 coberto ∥ 🟡 parcial ∥ 🔴 gap |

**Regra:** qualquer pre-mortem do **caminho escolhido** no BMAD §4.1 **sem linha na matriz ou com status 🔴** vira **gap bloqueador severidade alta** no Analyze — merge F6 **não passa** sem remediação.

Pre-mortems dos caminhos **descartados** (A/B/C no canônico-001) ficam **opcionais** na matriz (eles justificam o descarte, não exigem mitigação ativa).

**Operacionalmente:**

1. `templates/analise.md` ganha seção `§6.a Pre-mortem × Mitigação` obrigatória (via `requer:` no frontmatter).
2. `fases/06_ANALYZE.md` "Gate de avanço" ganha item: *"Matriz Pre-mortem × Mitigação completa; zero linha 🔴 no caminho escolhido"*.
3. `checklists/pre-implementacao.md` ganha item correspondente.
4. Lint artefato valida presença da seção (harness).

## Alternativas consideradas

### (a) Manter implícito (status quo pré-ADR)

**Prós:** zero mudança.
**Contras:**
- Auditor externo não consegue verificar cobertura sem reconstruir manualmente (observado no retrospective).
- Mitigação fica dispersa; risco de pre-mortem virar ornamento sem escudo real.
- Regressão fácil: se uma mitigação sair em refactor, ninguém nota até pre-mortem acontecer de fato.

**Motivo de descarte:** o custo de construir a matriz **1×** no Analyze é menor que custo de **reconstruí-la** no Retrospective — primeiro caso tem incentivo (gate); segundo só existe se ciclo chegar ao fim sem acidente. Acidente quebra incentivo.

### (b) Exigir matriz no BMAD (Fase 0.5) em vez de Analyze (Fase 6)

**Prós:** mais cedo no ciclo.
**Contras:**
- **Prematuro.** Mitigação técnica só emerge em F4 Plan / F6 Analyze (as mitigações do canônico-001 vieram do plan + analyze, não do BMAD). BMAD registra **o risco**; Analyze **verifica cobertura**. Exigir matriz no BMAD forçaria escrever mitigação antes de saber como.
- Violaria disciplina F0.5 "não inventar solução" — BMAD é modelagem, não execução.

**Motivo de descarte:** fronteira entre risco (BMAD) e cobertura (Analyze) é o princípio; fundir é perder clareza de fase.

### (c) Exigir só para pre-mortems do caminho escolhido (o que esta ADR faz)

Esta é a **decisão adotada** — ver §Decisão acima. Distinta de (a) e (b) por escolher o momento certo (F6) e escopo certo (só caminho escolhido).

## Consequências

### Positivas
- **Rastreabilidade estrutural** pre-mortem → mitigação verificável em 1 matriz.
- **Gate bloqueador** no Analyze detecta gaps cedo — antes de F7 Implement.
- **Retrospective §3 Pre-mortems revisitados** fica mais simples: copia status da matriz + atualiza "aconteceu em produção?".
- **IA futura** que aplicar a skill tem artefato único para consultar cobertura (em vez de varrer plan/tasks/ADRs/FRs).
- **Pre-mortem deixa de ser ornamento** — passa a ter consequência direta em gate.

### Negativas / trade-offs
- **Uma seção a mais** em `templates/analise.md` — overhead incremental (estimado: +5-10 min por ciclo).
- **Pre-mortem ruim** (mal formulado em F0.5) vira dívida amplificada em F6 — força alinhamento entre fases; pode ser visto como rigidez.
- **Módulos antigos** (pré-ADR) não têm a matriz; backfill retroativo opcional.

### Migração necessária
- **Canônico-001 retrofit:** opcional. Pode ser feito para servir de exemplo ("matriz construída retroativamente a partir do retrospective §3"). Recomendado mas não bloqueante.
- **Templates:** adequação em W2.
- **Projetos em curso:** aplica a partir da próxima F6 Analyze.

### Novas obrigações
- F6 Analyze não fecha sem matriz completa.
- Lint artefato valida presença (quando harness suportar).
- Self-review do PR de F6 checa linha 🟢 em todos os pre-mortems do caminho escolhido.

## Relação com Constituição

- Esta ADR **altera** processo da skill (`fases/06_ANALYZE.md` + `templates/analise.md` + `checklists/pre-implementacao.md`), **não altera** constituição de módulo individual.
- Esta ADR **NÃO altera** Camada 1 de nenhum módulo.
- Esta ADR **NÃO altera** Camada 2 de nenhum módulo.
- Bump semântico da skill: minor, sincronizado com ADR-007 na mesma safra v1.3.

## Relação com outros artefatos

- **ADRs relacionadas:** ADR-007 (mesma safra, retrospective §4).
- **Módulos impactados imediatamente:** nenhum diretamente; canônico-001 pode receber retrofit ilustrativo.
- **`decision_log.md` que passam a citar esta ADR:** D-NNN futuros que referenciem "mitigação obrigatória conforme ADR-008 matriz".

## Plano de reversão

1. Criar `ADR-NNN-REVERSED` com motivo (ex: matriz virou ritual sem valor; 90% das linhas sempre 🟢).
2. Remover seção obrigatória de `templates/analise.md`.
3. Downgrade de gate para opcional em `fases/06_ANALYZE.md`.
4. Canônicos que construíram matriz mantêm (não quebra).

## Aprovação

| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor | Thiago Loumart | 2026-04-23 | ✓ |
| Revisor 1 | — | — | pendente |
| Revisor 2 (se Camada 1) | — | — | n/a |
| Compliance | — | — | n/a |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| 2026-04-23 | Proposta | Criada em `w2/adr-propostas-canonical-001-retro` a partir de `retrospective.md §4 ADR-G-002` do canônico-001. |
| (data futura) | Aceita | A definir. |
