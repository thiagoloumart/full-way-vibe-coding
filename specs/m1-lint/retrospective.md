---
artefato: retrospective
fase: 12
dominio: [software]
schema_version: 1
requer:
  - "1. Objetivo do módulo e resultado observado"
  - "2. KPIs (previsto vs observado)"
  - "3. Decisões revisitadas"
  - "4. Propostas de ADR global"
  - "5. Propostas de atualização de Constituição"
  - "6. Aprendizados para próximos ciclos"
---

# Retrospective — `lint_artefato.py` M1 (W1 track A)

**Data:** 2026-04-18
**Módulo:** `lint_artefato.py` — linter mínimo de artefatos SDD
**Domínio:** D1 software
**Autor:** Thiago Loumart
**Revisor:** — (self-review time=1)
**Status:** Finalizada

Referências:
- `.review/w1a-lint.md`
- `decision_log.md` (D-001, D-002, D-003) e `clarify.md` (C-001, C-002, C-003)
- `bmad.md`, `analyze.md`, `risk_log.md`
- Commits: `6ec8967..2b5a230` (inclui 10 commits docs pré-código + 19 commits código/fix na branch `w1a/lint-artefato`)

---

## 1. Objetivo do módulo e resultado observado

**Objetivo original (de `bmad.md §1.1`):** *"Artefatos SDD são adicionados ao repo sem validação mecânica do contrato declarado em front-matter, gerando drift silencioso."*

**Resultado entregue:** `lint_artefato.py` com **3 classes de validação** (front-matter, seções `requer:`, links relativos internos), **80 testes** cobrindo happy + sad paths, **26/26 artefatos lintáveis** verdes no smoke manual, CLI com flags `--format`, `--warnings-only`, `--no-color`, exit codes estáveis 0/1/2, ANSI colors com auto-detecção de TTY.

**Tempo do ciclo:** ~1 dia (início W1 track A → merge). Todas as 16 tasks de `tasks.md` executadas em sequência com commits atômicos.

**Veredicto macro:** 🟢 **atingiu objetivo plenamente.** Plus: detectou 2 drifts reais no próprio dogfood (valor bônus documentado).

---

## 2. KPIs (previsto vs observado)

| ID | Descrição | Previsto | Observado | Janela | Gap | Causa provável |
|---|---|---|---|---|---|---|
| SC-001 | Zero falsos positivos em artefatos lintáveis | 0 falsos em templates/, specs/, ADRs | 26/26 verdes | smoke pós-F3 | 0 | — (após fix de D-W1A-002) |
| SC-002 | 100% de defeitos plantados em fixtures | 7+ classes de defeito detectadas | 7 classes × 17 fixtures, 80 testes | pytest automático | 0 | — |
| SC-003 | Execução <500ms em artefato de 200 linhas | 500ms | **57ms** (125 linhas) | `time` CLI | +443ms folga | startup Python + pyyaml eficientes |
| SC-004 | Saída JSON parseável | 100% | `json.loads` OK em vazio + com erros | testes unit + CLI | 0 | `json.dumps` + dataclass asdict |
| SC-005 | Exit codes 0/1/2 conforme contrato | 3 cenários | 0=OK, 1=lint err, 2=IO err | testes CLI | 0 | `argparse` + `sys.exit` |
| SC-006 | Self-lint dos artefatos W1 track A | 100% | 11/11 de `specs/m1-lint/` verdes | smoke | 0 | — |

**Nenhum KPI não observado.** Nenhum `[NEEDS CLARIFICATION]` de instrumentação.

---

## 3. Decisões revisitadas

### D-001 — Stack = pyyaml + regex
**Decisão tomada em BMAD:** usar `pyyaml.safe_load` para front-matter + `re` stdlib para corpo Markdown; AST Markdown descartado como overengineered.
**O que aconteceu de fato:** regex cobriu 100% dos casos dos artefatos atuais. `pyyaml.safe_load` forneceu `problem_mark` útil para mensagens de `YAML_INVALIDO`. Nenhum caso onde AST teria evitado drift real.
**Veredicto:** 🟢 **sustentada.**
**Ação:** nenhuma. Manter critério de invalidação ativo (se ≥3 artefatos falsamente rejeitados em W2 dogfood → migrar para AST).

### D-002 — Script read-only (invariante)
**Decisão tomada em BMAD:** nenhuma operação de escrita, nunca. Invariante permanente.
**O que aconteceu de fato:** revisão manual do código confirma ausência de qualquer `open(..., 'w')`, `os.remove`, `shutil.move`, etc. Verificação por hash md5 incluída no quickstart.
**Veredicto:** 🟢 **sustentada.**
**Ação:** manter. Em M2, adicionar teste automatizado que valide invariante via análise estática (ex: AST scan por nomes proibidos) — item de melhoria, não bloqueador.

### D-003 — Escopo M1 = 3 validações
**Decisão tomada em BMAD:** front-matter, seções `requer:`, links relativos internos. Tudo mais é M2.
**O que aconteceu de fato:** escopo cumprido exatamente. `gate_fase.py`, `smoke_test.py`, `lint_constituicao.py`, schemas YAML custom, auto-fix — todos corretamente adiados.
**Veredicto:** 🟢 **sustentada.**
**Ação:** nenhuma. Disciplina de escopo valeu — evitou anti-padrão #2 da carta sênior.

### C-001 — Convenção de códigos de erro (PT SCREAMING_SNAKE)
**Decisão tomada em Clarify:** PT SCREAMING_SNAKE_CASE (ex: `FRONTMATTER_AUSENTE`).
**O que aconteceu de fato:** 9 códigos implementados seguindo a convenção. Catálogo documentado em `harness/README.md §Catálogo M1`.
**Veredicto:** 🟢 **sustentada.**

### C-002 — Cor auto-detecta TTY + respeita `NO_COLOR`
**Decisão tomada em Clarify:** `supports_color()` detecta `sys.stdout.isatty()` + ausência de `NO_COLOR` env var; `--no-color` flag força off.
**O que aconteceu de fato:** implementado em T-011. Testes cobrem as 3 condições (NO_COLOR, non-TTY, TTY sem NO_COLOR). Compatível com GitHub Actions.
**Veredicto:** 🟢 **sustentada.**

### C-003 — `--list-rules` vira doc em `harness/README.md`
**Decisão tomada em Clarify:** em vez de flag CLI, catalogar no README.
**O que aconteceu de fato:** catálogo completo em `harness/README.md §Catálogo M1` com 9 códigos de erro + 3 flags documentadas.
**Veredicto:** 🟢 **sustentada.** Decisão economizou ~30 linhas de código e entregou doc mais rica.

---

## 4. Propostas de ADR global

### Proposta ADR-004 — Refactor de `lint_artefato.py` em módulos
**Origem:** R-007 materializado (369 LoC > 350 threshold de `plan.md §6`).
**Proposta:** quando `lint_artefato.py` passar de 500 LoC efetivas **ou** quando M2 começar implementando `gate_fase.py` (que compartilha código de parse), fragmentar em `harness/scripts/lint/{parser,validator,report,cli}.py`.
**Status sugerido:** Proposta (decisão humana em W1 track B ou M2 kickoff).
**Responsável:** Thiago Loumart.

### Proposta ADR-005 — `requer:` como contrato auditável
**Origem:** D-W1A-001 (drift em `templates/constituicao.md`).
**Proposta:** formalizar na Camada 2 da Constituição global (em `constitution.md` raiz quando escrito): "todo item de `requer:` deve corresponder literalmente ao prefixo de um heading `##` ou `###` no corpo, comparado após normalização". Schema YAML em M2 (`harness/schemas/qualidade-<artefato>.yaml`) pode validar estaticamente.
**Status sugerido:** Proposta.

### Proposta ADR-006 — Extensão de front-matter para doc livre (v1.3+)
**Origem:** smoke F2 revelou que 32/52 arquivos do repo não têm front-matter (fases/, protocolos/, checklists/, governanca/ raiz). `harness/README.md` já marca como "doc livre" para M1.
**Proposta:** decidir em v1.3 se: (a) manter doc livre com lint ignorando; (b) adicionar front-matter "doc-livre" com `artefato: doc`, `requer: []`; (c) criar novo pattern de lint (`lint_doc.py`) com contrato mais leve.
**Status sugerido:** Proposta futura (não urgente).

---

## 5. Propostas de atualização de Constituição

### `constitution.md` do módulo
Nenhuma. Camada 1 e 2 sustentadas integralmente.

### `constitution.md` global (raiz — a ser escrita em v1.2)
Ao escrever a constituição global, considerar:
- Adicionar **Camada 2 §8 Estilo/Convenções**: regra "todo item de `requer:` em front-matter deve bater como prefixo de heading no corpo" (derivada de ADR-005 proposta).
- Adicionar **Camada 1 §3 Valores bloqueantes**: reforçar invariante "scripts do harness são read-only" (derivado de D-002).

---

## 6. Aprendizados para próximos ciclos

### 🟢 O que funcionou

1. **BMAD rigoroso produziu spec sem ambiguidade estratégica.** As 3 D-NNN ficaram estáveis até o fim — nenhuma reversão.
2. **Clarify fechou 3 ambiguidades menores antes do código.** Custo: 1 documento. Valor: zero retrabalho em CLI durante T-011/T-012.
3. **Fase 6 Analyze detectou 3 problemas menores antes da primeira linha de código.** Todos remediados em ~30 min. Economia estimada: 2–3h de retrabalho pós-código.
4. **Dogfood expôs 2 drifts reais no caminho:** D-W1A-001 (constituicao requer inconsistente) e D-W1A-002 (falso positivo em inline code). Ambos corrigidos estrutuarlmente (uma regra cobriu todos os casos).
5. **`risk_log.md` como artefato vivo** permitiu documentar em tempo real decisões que antes viveriam só em commit messages. Preferência do usuário capturada em memory — aplicada consistentemente.
6. **Commits atômicos por task** (`T-001` a `T-014`) tornaram rastreabilidade quase livre.

### 🟡 O que poderia ter sido melhor

1. **Smoke escopo mal definido em `plan.md §3 F2 DoD`:** "smoke em todos os templates" era ambíguo — incluía ou não fases/protocolos/checklists? Drift R-008 materializou porque dei smoke amplo demais pós-F2. Fix estrutural: SC-001 reescrito com lista explícita de padrões lintáveis + documentado em `harness/README.md`. **Aprendizado:** next time, DoD de smoke precisa listar paths explícitos.
2. **FR-010 da spec original cobria só fenced blocks, não inline code.** Lacuna descoberta no dogfood (D-W1A-002). Spec atualizada em commit `682a90b`. **Aprendizado:** ao escrever FR de "ignora links em code", cobrir **todas** as formas sintáticas (fenced + inline + indented).
3. **Threshold de 350 LoC** de `plan.md §6` é um tanto arbitrário (minha `[INFERÊNCIA]`). Cruzou em 19 linhas, decisão de postergar refactor. **Aprendizado:** critérios quantitativos precisam vir com justificativa do número específico, não apenas "≥350".

### 🔴 O que falhou (ou quase falhou)

Nada falhou dentro de W1 track A. Mas dois riscos em aberto podem falhar em W2+:

1. **R-001 (code block mal fechado)** ainda não foi exercitado por fixture real. W2 dogfood pode expor. Plano: migrar para AST via ADR se materializar.
2. **Invariante read-only (D-002)** não tem teste automatizado — só verificação manual. Se alguém adicionar feature que toque FS no futuro, nada reclama. **Proposta M2:** AST scan em CI procurando nomes proibidos (`os.remove`, `shutil.move`, etc).

### 📌 Para próximos módulos

1. **DoD de smoke sempre com paths explícitos.** Nunca "todos os .md".
2. **FRs sobre "ignora X" precisam enumerar todas as formas sintáticas de X.**
3. **Critérios quantitativos em plan** (ex: "N LoC", "M ms") precisam de justificativa do número.
4. **Self-lint do próprio review** — como aconteceu aqui — é dogfood extra gratuito. Manter padrão.
5. **Artefatos W1 track B+** devem nascer já tipo `requer:` cujos headings batem EXATAMENTE com o texto. Prefix match é escape hatch, não regra principal.

---

**Gate de fechamento desta retrospective:**
- [x] Todas as `D-NNN` revisitadas com veredicto.
- [x] Todas as `C-NNN` revisitadas.
- [x] 6 SCs medidos com valores reais.
- [x] 3 propostas de ADR levantadas (ADR-004, 005, 006).
- [x] 2 propostas de atualização de constituição global registradas.
- [x] 6 aprendizados agrupados em 🟢 / 🟡 / 🔴 / 📌.
- [x] `risk_log.md` consolidado — R-008 marcado materializado/mitigado, R-007 materializado/postergado.
- [x] Humano assinou.
