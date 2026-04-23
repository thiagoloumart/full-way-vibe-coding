---
artefato: adr
fase: null
dominio: [any]
schema_version: 1
adr_id: ADR-007
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

# ADR-007 — Declaração explícita de `modo_execucao` (C1 real / C2 documental) na Fase 0

**Status:** Proposta
**Data:** 2026-04-23
**Autor:** Thiago Loumart
**Camada afetada:** 2 (processo da skill — `fases/*`, `templates/*`)
**Bump de Constituição:** minor — materializa em próxima v1.M dos artefatos da skill (não da constituição de módulo)

**Origem:** `examples/canonical-software/001-confirmacao-consultas/retrospective.md §4` proposta ADR-G-001.

---

## Contexto

Durante o ciclo W1B do canônico `001-confirmacao-consultas` (2026-04-18 → 2026-04-23), a decisão de **não executar testes (F8) nem quickstart (F9)** surgiu tacitamente em F7 Implement (estratégia C2 negociada na conversa). Essa decisão **não estava declarada na Fase 0 Recepção** e só virou `[RISCO ASSUMIDO] canonical-F8` / `canonical-F9` em cada fase downstream, gerando:

1. **Retrabalho de registro** — cada fase (7, 8, 9) precisou explicitar o contrato documental em §1 de seu artefato principal.
2. **Expectativa mal calibrada** — leitor do canônico que começa em F0 Recepção não tem sinal de que F8/F9 serão documentais; descobre só em F7 ou depois.
3. **Gate invariável indevidamente** — `fases/08_TEST.md` original diz "todos os testes verdes" como gate; em C2 isso precisou ser **substituído** por contrato documental em `test_plan.md §1`, não pela Fase 8 em si.
4. **Dívida operacional em projeto derivado** — quando alguém extrair o canônico para projeto C1 real, precisa inverter os `[RISCO ASSUMIDO]` manualmente, sem lista-mestre de "o que precisa virar execução real".

O retrospective §6.1 aprendizado #1 registrou isto como "faríamos diferente".

## Decisão

**A Fase 0 Recepção passa a exigir declaração explícita de `modo_execucao` com dois valores possíveis:**

- **`C1_real`** — execução real obrigatória em todas as fases operacionais (F7 Implement · F8 Test · F9 Quickstart). Gates de cada fase são cumpridos literalmente conforme `fases/NN_*.md` atual.
- **`C2_documental`** — modo canônico/exemplar. Fases operacionais (F7/F8/F9) têm contrato documental declarado no próprio artefato (`§1 Contrato desta fase`). Gate da fase é substituído por "documento completo + rastreável + testes/quickstart/execução declaradas como obrigação de projeto derivado".

**Operacionalmente:**

1. `templates/recepcao.md` ganha campo obrigatório `modo_execucao: C1_real | C2_documental` no front-matter.
2. `fases/00_RECEPCAO.md` ganha seção "**Declaração de modo de execução**" com pergunta-padrão única:
   > "Este ciclo vai **executar** contra infra real (DB, provedor, usuário piloto) ou é **exemplar/canônico** (skill/treinamento, sem execução)?"
3. `fases/07_IMPLEMENT.md`, `fases/08_TEST.md`, `fases/09_QUICKSTART.md` ganham seção "**Gate em modo C1** / **Gate em modo C2**" com contratos distintos explícitos.
4. `templates/test_plan.md` (novo, derivado de `examples/canonical-software/001-confirmacao-consultas/test_plan.md`) e `templates/quickstart.md` ganham `§1 Contrato desta fase` parametrizado por modo.
5. `.review/*.md` checa coerência do modo declarado em F0 com contratos cumpridos em F7/F8/F9.

## Alternativas consideradas

### (a) Manter implícito (status quo pré-ADR)

**Prós:** nenhuma mudança em templates; flexibilidade para decidir modo no meio do ciclo.
**Contras:**
- Retrabalho de registro em cada fase downstream (observado 3× no canônico-001).
- Expectativa mal calibrada para leitor novo.
- Contrato de Fase 8/9 fica internamente inconsistente com `fases/*.md` quando em C2.
- Sem lista-mestre para projeto derivado extrair.

**Motivo de descarte:** todos os contras são **observados**, não hipotéticos — ciclo canônico-001 materializou.

### (b) Criar um terceiro modo "C3 híbrido" (código real + testes doc)

**Prós:** cobre casos em que code review é útil mas ambiente não está pronto.
**Contras:**
- Sem caso concreto observado para justificar.
- Adicionar modos antes de ter dados é overengineering — mesmo erro que o Caminho C do canônico-001 (multicanal) cometeria.

**Motivo de descarte:** prematuro. Se aparecer caso concreto em W3+, abrir `ADR-NNN-REVISED` com C3.

### (c) Exigir modo só em F7 Implement (não em F0)

**Prós:** declaração fica mais próxima do momento em que o modo importa.
**Contras:**
- Tardio — spec (F2), clarify (F3), constituição (F3.5) são escritas sem saber se serão exercidas; dimensionamento errado.
- Quebra a disciplina de "sinalizar contratos no início" que toda a skill defende (Manual §6).

**Motivo de descarte:** F0 é o momento certo — é onde "escopo declarado" já vive (ver `examples/canonical-software/001-confirmacao-consultas/README.md §Escopo declarado`).

## Consequências

### Positivas
- **Zero retrabalho** de `[RISCO ASSUMIDO] canonical-FN` em cada fase downstream.
- **Expectativa calibrada** para leitor — modo é visível no primeiro parágrafo do canônico.
- **Gate de cada fase** se adapta coerentemente; `fases/*.md` deixa de ter contrato único que precisa ser substituído.
- **Projeto derivado** herda lista-mestre explícita: "tudo marcado como `[RISCO ASSUMIDO] canonical-FN` vira obrigação aqui".
- **Auditoria** fica mais simples: `grep modo_execucao:` em frontmatter revela natureza do ciclo sem ler corpo.

### Negativas / trade-offs
- **Mudança em 4 templates** + 3 arquivos de fase — trabalho real de adequação em W2.
- **Canônicos existentes** (só o 001 até agora) precisam ser retrofited para declarar `modo_execucao: C2_documental` em `recepcao.md` — trivial mas não-zero.
- **Modo declarado não pode mudar no meio do ciclo** sem `D-NNN-REVISED` — disciplina nova; risco de rigidez se equipe mudar de ideia.

### Migração necessária
- **Canônicos existentes:** retrofit de `modo_execucao` no `recepcao.md`. Afeta `examples/canonical-software/001-confirmacao-consultas/recepcao.md` (1 arquivo); ADR-007 Aceita dispara este retrofit como parte do merge.
- **Templates:** adequação em W2 após aceitação.
- **Projetos em curso (W1+):** nenhum afetado retroativamente; aplica a partir da próxima Fase 0.

### Novas obrigações
- F0 não pode fechar sem `modo_execucao` declarado (gate em `fases/00_RECEPCAO.md`).
- `.review/*.md` valida coerência (feedback de regressão).

## Relação com Constituição

- Esta ADR **altera** processo da skill (`fases/*` e `templates/*`), **não altera** a constituição de nenhum módulo individual.
- Esta ADR **NÃO altera** Camada 1 de nenhum módulo.
- Esta ADR **NÃO altera** Camada 2 de nenhum módulo (impacta processo, não stack).
- Bump semântico da skill: minor (v1.2 → v1.3) após materialização das mudanças em W2.

## Relação com outros artefatos

- **ADRs relacionadas:** nenhuma até agora; primeira da safra "pós-W1 Retrospective" prevista em `ADR-index §Próximas ADRs esperadas`.
- **Módulos impactados imediatamente:** `examples/canonical-software/001-confirmacao-consultas` (retrofit `modo_execucao: C2_documental`).
- **`decision_log.md` que passam a citar esta ADR:** nenhum no momento; próximo canônico (002+) cita em D-NNN da F0.

## Plano de reversão

Se esta ADR for revertida:

1. Criar `ADR-NNN-REVERSED` com motivo (ex: modo único é suficiente em prática; overhead de declaração maior que ganho).
2. Remover campo `modo_execucao` de `templates/recepcao.md`.
3. Remover seções "Gate em modo CN" de `fases/07_*`, `fases/08_*`, `fases/09_*`.
4. Canônicos existentes mantêm declaração (não quebra) — `grep` continua funcionando.
5. Bump minor da skill explicando reversão.

## Aprovação

| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor | Thiago Loumart | 2026-04-23 | ✓ (via commit `w2/adr-propostas-canonical-001-retro`) |
| Revisor 1 | — | — | pendente — ADR em status `Proposta` |
| Revisor 2 (se Camada 1) | — | — | n/a (Camada 2 de processo) |
| Compliance | — | — | n/a (não regulatório) |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| 2026-04-23 | Proposta | Criada em `w2/adr-propostas-canonical-001-retro` a partir de `retrospective.md §4 ADR-G-001` do canônico-001. |
| (data futura) | Aceita | A definir. |
