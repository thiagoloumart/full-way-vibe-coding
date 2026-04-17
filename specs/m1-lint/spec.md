---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer:
  - "User Scenarios & Testing"
  - "Requirements"
  - "Success Criteria"
  - "Out of Scope"
---

# Feature Specification: `lint_artefato.py` — linter mínimo M1

**Feature Branch:** `w1a/lint-artefato`
**Created:** 2026-04-17
**Status:** Estável (pós-clarify 2026-04-17)
**Input:** `briefing.md` + `bmad.md` + `decision_log.md` (D-001, D-002, D-003) + `clarify.md` (C-001, C-002, C-003)
**Referências:** ADR-002 (stack Python); `harness/README.md §Contratos`; `harness/rollout.md §E1`

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Validação de front-matter (Priority: P1)

**Origem:** briefing §7.1, D-001, D-003

Autor edita um artefato e o script confere que o bloco YAML entre os primeiros `---...---` existe, parseia sem erro, e contém os 5 campos obrigatórios (`artefato`, `fase`, `dominio`, `schema_version`, `requer`).

**Why P1:** Sem front-matter válido, toda outra validação é impossível. É o bloqueio na raiz da árvore de dependências de regras do linter.

**Independent Test:** rodar lint em 6 fixtures: (1) front-matter válido → exit 0; (2) sem front-matter → `FRONTMATTER_AUSENTE` exit 1; (3) YAML mal formado → `YAML_INVALIDO` com linha + exit 1; (4) falta `artefato:` → `CAMPO_OBRIGATORIO_AUSENTE: artefato` exit 1; (5) `requer:` como string (não lista) → `CAMPO_TIPO_INVALIDO: requer` exit 1; (6) `schema_version` como string → `CAMPO_TIPO_INVALIDO: schema_version` exit 1.

**Acceptance Scenarios:**
1. **Given** um artefato válido, **When** rodo `python -m harness.scripts.lint_artefato <arquivo>`, **Then** stdout imprime `OK` e exit code é 0.
2. **Given** um artefato sem bloco `---...---`, **When** rodo o lint, **Then** stdout imprime `<arquivo>:1: [ERRO] FRONTMATTER_AUSENTE` e exit code é 1.
3. **Given** front-matter com YAML quebrado (ex: `requer:\n - A\n- B` com indentação inconsistente), **When** rodo o lint, **Then** stdout imprime `<arquivo>:<linha>: [ERRO] YAML_INVALIDO <contexto>` e exit code é 1.
4. **Given** front-matter válido mas sem o campo `artefato`, **When** rodo o lint, **Then** stdout imprime `CAMPO_OBRIGATORIO_AUSENTE: artefato` e exit 1.

---

### User Story 2 — Validação de seções `requer:` (Priority: P1)

**Origem:** briefing §7.2, D-001, D-003

Cada string declarada em `requer:` deve aparecer como heading (`##` ou `###`) no corpo do Markdown, com comparação tolerante a whitespace e travessões.

**Why P1:** É o coração do contrato dos templates. Toda a dívida diagnóstica da dim 3/4/5 da auditoria deriva de seções prometidas mas não entregues. Sem isso, o linter é ornamental.

**Independent Test:** fixture com `requer: ["1. Breakdown"]` e variações do corpo: (1) `## 1. Breakdown` → pass; (2) `## 1.  Breakdown` (espaço duplo) → pass; (3) `## 1. Breakdown — decomposição do problema` → pass se `requer:` combinar prefixo; (4) `#### 1. Breakdown` (nível 4) → fail `SECAO_OBRIGATORIA_NIVEL_INVALIDO`; (5) seção ausente → fail `SECAO_OBRIGATORIA_AUSENTE: "1. Breakdown"`; (6) seção dentro de bloco de código ```` ``` ```` → não conta (fail `SECAO_OBRIGATORIA_AUSENTE`).

**Acceptance Scenarios:**
1. **Given** `requer: ["1. Breakdown"]` e corpo com `## 1. Breakdown`, **When** rodo o lint, **Then** aceita (OK).
2. **Given** `requer: ["1. Breakdown"]` e corpo sem heading correspondente, **When** rodo o lint, **Then** `<arquivo>:<linha-fim-frontmatter>: [ERRO] SECAO_OBRIGATORIA_AUSENTE "1. Breakdown"` e exit 1.
3. **Given** `requer: ["Decisão"]` e corpo com `## Decisão — registrada`, **When** rodo o lint, **Then** aceita (prefixo bate após normalização).
4. **Given** `requer: ["1. Breakdown"]` e corpo com `#### 1. Breakdown` (nível 4), **When** rodo o lint, **Then** rejeita com `SECAO_OBRIGATORIA_NIVEL_INVALIDO` (só `##` e `###` contam).
5. **Given** `requer: ["X"]` e corpo com `## X` dentro de bloco de código ```, **When** rodo o lint, **Then** rejeita com `SECAO_OBRIGATORIA_AUSENTE` (blocos de código não contam) — herda [RISCO ASSUMIDO] de D-001.

---

### User Story 3 — Validação de links relativos internos (Priority: P1)

**Origem:** briefing §7.3, D-003

Todo link na sintaxe `[texto](caminho/relativo.md)` ou `[texto](caminho/relativo.md#anchor)` aponta para arquivo que existe no filesystem (resolução relativa ao arquivo onde o link aparece).

**Why P1:** Links quebrados são a forma mais frequente de drift silencioso quando arquivos são movidos ou renomeados. Detecção mecânica é trivial e protege muito.

**Independent Test:** fixture com 5 casos: (1) `[x](./foo.md)` existe → pass; (2) `[x](../bar.md)` existe → pass; (3) `[x](./inexistente.md)` → fail `LINK_QUEBRADO ./inexistente.md`; (4) `[x](https://example.com)` → ignora (fora de escopo); (5) `[x](./foo.md#section)` → valida só o arquivo, não a âncora.

**Acceptance Scenarios:**
1. **Given** um link `[README](../README.md)` e `../README.md` existe, **When** rodo o lint, **Then** aceita.
2. **Given** um link `[X](./inexistente.md)`, **When** rodo o lint, **Then** `<arquivo>:<linha>: [ERRO] LINK_QUEBRADO "./inexistente.md"` e exit 1.
3. **Given** um link `[github](https://github.com/foo/bar)`, **When** rodo o lint, **Then** ignora (link externo, fora do escopo M1).
4. **Given** um link `[X](./foo.md#secao-inexistente)` e `./foo.md` existe, **When** rodo o lint, **Then** aceita (âncora não é validada em M1).
5. **Given** um link `[X](./foo.md)` dentro de um bloco de código ```, **When** rodo o lint, **Then** ignora (evita falso positivo em docs de exemplo).

---

### User Story 4 — Saída humana legível (Priority: P2)

**Origem:** briefing §7.4

Diagnóstico em formato `<arquivo>:<linha>: [NÍVEL] CÓDIGO mensagem`, ordenados por linha ascendente, erros antes de warnings.

**Why P2:** P2 porque P1 funciona com qualquer saída; isso é qualidade de UX. Mas é o modo que 99% dos autores vão ver.

**Acceptance Scenarios:**
1. **Given** um artefato com 3 erros (linhas 5, 2, 10), **When** rodo o lint, **Then** stdout lista em ordem de linha: 2, 5, 10.
2. **Given** um artefato com 1 erro e 2 warnings, **When** rodo o lint, **Then** stdout lista o erro primeiro, depois os warnings em ordem de linha.

---

### User Story 5 — Saída JSON estruturada (Priority: P2)

**Origem:** briefing §7.4; `harness/README.md §Contratos` sugere `--format human|json`

Flag `--format json` muda saída para array JSON de diagnósticos `{arquivo, linha, nivel, codigo, mensagem}`.

**Why P2:** Consumo por outras ferramentas (CI parser, editor LSP futuro). Não bloqueia P1/P2/P3 do autor humano.

**Acceptance Scenarios:**
1. **Given** 2 erros no artefato, **When** rodo com `--format json`, **Then** stdout é JSON válido parseável por `jq`, com array de 2 objetos.
2. **Given** nenhum erro, **When** rodo com `--format json`, **Then** stdout é `[]` e exit 0.

---

### User Story 6 — Modo warnings-only (Priority: P3)

**Origem:** briefing §7.4; `harness/rollout.md §E1`

Flag `--warnings-only` degrada todos os erros para warnings: mesmo relatório, mas exit code sempre 0. Usado no estágio E1 do rollout.

**Why P3:** Necessário para E1 mas não é a forma principal de uso. Depois de E1 estabilizar, a flag raramente é usada.

**Acceptance Scenarios:**
1. **Given** 3 erros no artefato, **When** rodo com `--warnings-only`, **Then** stdout lista os 3 itens como `[WARN]` e exit code é 0.
2. **Given** nenhum erro, **When** rodo com `--warnings-only`, **Then** exit 0 e stdout "OK" (comportamento idêntico ao modo normal nesse caso).

---

### Edge Cases

- **Arquivo não existe no path fornecido** → exit 2 (diferente de erro de lint), stderr `ARQUIVO_NAO_ENCONTRADO`.
- **Arquivo não tem extensão `.md`** → exit 2, stderr `ARQUIVO_NAO_MARKDOWN`.
- **Arquivo vazio (0 bytes)** → exit 1 com `FRONTMATTER_AUSENTE`.
- **Front-matter presente mas corpo vazio** → `requer:` não-vazio dispara `SECAO_OBRIGATORIA_AUSENTE`; `requer:` vazio aceita.
- **Link relativo sobe acima da raiz do repo** (ex: `../../../../etc/passwd`) → valida existência do path mas resolve relativo ao arquivo; se existe, aceita. (Não é função deste lint defender contra path traversal em docs.)
- **Arquivo com BOM UTF-8 no início** → aceitar (decodificar BOM, parsear front-matter normalmente).
- **Front-matter com chave desconhecida** (ex: `custom_field: x`) → aceitar em M1 (não reclamar de chaves extras). Validação estrita entra em M2 via schemas.
- **Link com espaço escapado ou URL-encoded** (ex: `[x](./foo%20bar.md)`) → resolver decodificando o caminho; validar existência do arquivo decodificado.
- **Arquivo > 10 MB** → aceitar mas imprimir warning `ARQUIVO_MUITO_GRANDE: <N> bytes — performance pode degradar` (não bloqueia).

---

## Requirements *(mandatory)*

### Functional Requirements

Cada FR rastreia sua origem: `→ D-NNN` (decision log) ou `→ briefing §X.Y`.

- **FR-001:** System MUST parse YAML front-matter delimited by the first two `---` lines using `pyyaml.safe_load`. → D-001
- **FR-002:** System MUST validate presence of required fields: `artefato`, `fase`, `dominio`, `schema_version`, `requer`. → briefing §7.1
- **FR-003:** System MUST validate field types: `artefato` (string), `fase` (number or `null`), `dominio` (list of strings), `schema_version` (integer), `requer` (list of strings). → briefing §7.1
- **FR-004:** System MUST, for each item in `requer:`, validate that a matching heading (`##` or `###`) exists in the body, using normalized comparison (collapse whitespace, trim, normalize `—`↔`--`). → D-001, D-003
- **FR-005:** System MUST NOT consider headings inside fenced code blocks (```) as satisfying `requer:`. → D-001 [RISCO ASSUMIDO]
- **FR-006:** System MUST NOT consider headings at depth `####` or deeper as satisfying `requer:`. → briefing §7.2
- **FR-007:** System MUST extract internal relative Markdown links matching `\[([^\]]+)\]\(([^)]+\.md)(#[^)]*)?\)` and validate that each target file exists on the filesystem, resolved relative to the artefact being validated. → D-003
- **FR-008:** System MUST NOT validate link anchors (`#section`) in M1 — only file existence. → briefing §9 non-goals, D-003
- **FR-009:** System MUST NOT validate external links (`https://…`, `http://…`, `mailto:`). → briefing §9 non-goals
- **FR-010:** System MUST NOT flag links inside fenced code blocks. → FR-005 consistency
- **FR-011:** System MUST NOT modify, create, rename or delete any file under validation or anywhere else. → **D-002 (invariante §5.4)**
- **FR-012:** System MUST exit with code 0 when validation passes; code 1 when any `[ERRO]` is detected; code 2 when the file cannot be read (not found, not Markdown, IO error). → briefing §7.4
- **FR-013:** System MUST output diagnostics in human-readable format by default: `<file>:<line>: [LEVEL] CODE message`, sorted by line ascending, errors before warnings. → briefing §7.4
- **FR-014:** Users MUST be able to request structured JSON output via `--format json`, producing a valid JSON array of `{arquivo, linha, nivel, codigo, mensagem}` objects. → briefing §7.4
- **FR-015:** Users MUST be able to run in warnings-only mode via `--warnings-only`, which downgrades all `[ERRO]` to `[WARN]` and forces exit code 0 (supports rollout stage E1). → briefing §7.4, `harness/rollout.md §E1`
- **FR-016:** System MUST skip UTF-8 BOM if present at file start. → Edge Cases
- **FR-017:** System MUST accept unknown front-matter keys without error in M1 (strict schema validation deferred to M2). → Edge Cases, briefing §9
- **FR-018:** System MUST emit ANSI color codes in human format output when stdout is a TTY AND `NO_COLOR` env var is unset. When either condition fails, or when `--no-color` flag is passed, color codes MUST be omitted. JSON format MUST never emit color codes. → **C-002**

### Non-Functional Requirements

- **NFR-001:** Single-file validation MUST complete in under 500ms for a 200-line artefact on a reference laptop (Apple M-series or equivalent). [INFERÊNCIA: baseline baseada em profiling de tooling similar; revisar se smoke test indicar pior]
- **NFR-002:** Full-repo validation (~70 artefatos) MUST complete in under 5s using `smoke_test.py` in M2. (Meta para M2, não bloqueante em M1.)
- **NFR-003:** Runtime dependencies limited to `pyyaml` plus Python 3.11+ standard library. → ADR-002
- **NFR-004:** Exit codes MUST be stable across versions (breaking change requires major bump + ADR).

### Key Entities (domain, not Python types)

- **Artefato:** arquivo `.md` com front-matter YAML; unidade de validação.
- **FrontMatter:** conteúdo YAML parseado entre os dois primeiros `---`.
- **RequerItem:** string declarada em `requer:` que deve ter heading correspondente.
- **Heading:** linha do corpo começando com `##` ou `###`, com texto normalizado e número de linha.
- **Link:** par `(texto, target)` extraído de sintaxe Markdown, com número de linha.
- **Diagnostic:** `{arquivo, linha, nivel ∈ {ERRO, WARN, INFO}, codigo, mensagem}`.

### Permissões

Não aplicável — lint é ferramenta CLI sem sistema de papéis.

### Estados de erro previsíveis

| Código | Nível | Gatilho | Exit |
|---|---|---|---|
| `FRONTMATTER_AUSENTE` | ERRO | Arquivo não começa com `---` | 1 |
| `YAML_INVALIDO` | ERRO | pyyaml lança exceção ao parsear | 1 |
| `CAMPO_OBRIGATORIO_AUSENTE` | ERRO | Um dos 5 campos obrigatórios falta | 1 |
| `CAMPO_TIPO_INVALIDO` | ERRO | Campo presente mas com tipo errado | 1 |
| `SECAO_OBRIGATORIA_AUSENTE` | ERRO | Item de `requer:` sem heading correspondente | 1 |
| `SECAO_OBRIGATORIA_NIVEL_INVALIDO` | ERRO | Heading existe mas em `####+` | 1 |
| `LINK_QUEBRADO` | ERRO | Link relativo `.md` aponta para arquivo inexistente | 1 |
| `ARQUIVO_NAO_ENCONTRADO` | — | Arquivo passado como argumento não existe | 2 |
| `ARQUIVO_NAO_MARKDOWN` | — | Arquivo passado não termina em `.md` | 2 |
| `ARQUIVO_MUITO_GRANDE` | WARN | Arquivo > 10 MB | 0/1 (dep. outros erros) |

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001:** Zero falsos positivos ao rodar em **todos** os `.md` atuais da skill (~70 arquivos) — os artefatos existentes refletem o padrão que o linter deve aceitar. *(→ smoke test manual no fim de W1 track A.)*
- **SC-002:** 100% dos defeitos plantados em suite de 7 fixtures (sem front-matter, YAML inválido, campo faltando, tipo errado, seção ausente, nível errado, link quebrado) são detectados pelo lint. *(→ suite `pytest`.)*
- **SC-003:** Execução <500ms para artefato de 200 linhas no laptop de referência. *(→ test com `time`.)*
- **SC-004:** Saída JSON passa `jq '.'` sem erro em todas as condições (erro/sem-erro/misto). *(→ test de integração.)*
- **SC-005:** Exit codes conformes com FR-012 em 3 cenários: sucesso (0), erro de lint (1), erro de IO (2). *(→ suite `pytest`.)*
- **SC-006:** Self-lint — o próprio `lint_artefato.py` não é artefato `.md`, mas os artefatos desta spec (`bmad.md`, `briefing.md`, `spec.md`, `clarify.md`) passam verde quando o lint estiver pronto. *(→ smoke test de dogfood.)*

---

## Out of Scope

- **Validação semântica de artefatos** (ex: "spec tem ≥2 caminhos em Analyze", "bmad tem ≥4 subetapas preenchidas"). → M2 via `harness/schemas/*.yaml`.
- **Contra-referência D-NNN ↔ decision_log** (ex: FR-042 referencia D-099; D-099 existe?). → M2.
- **`gate_fase.py`** (determinar se fase Y pode avançar com base nos artefatos presentes). → M2.
- **`lint_constituicao.py`** (validar Camada 1/Camada 2 da constituição bicamada). → M2.
- **`smoke_test.py` integrado** (roda lint + gate + lint_constituicao em toda a skill). → M2.
- **`gerar_context_pack.py`**. → M2.
- **Auto-fix** de artefatos (renomear seção, adicionar link quebrado). → nunca (D-002 invariante).
- **Validação de âncoras** (`#section`). → M2.
- **Validação de links externos** (`https://...`). → nunca no lint; deixar para ferramentas dedicadas como `lychee`.
- **Validação strict de chaves extras no front-matter.** → M2 via schemas.
- **Parser AST de Markdown.** → apenas se M2/M3 mostrar que regex não escala (D-001 critério de invalidação).

---

**Checklist antes de aprovar:**
- [x] Zero nomes de biblioteca novos além de `pyyaml` (já em ADR-002).
- [x] Cada FR é verificável em teste automatizado.
- [x] Cada User Story tem Given/When/Then.
- [x] Edge cases mapeados (9 itens).
- [x] `[NEEDS CLARIFICATION]` — os 3 do briefing serão resolvidos em Fase 3 Clarify (não repito aqui; ver `clarify.md`).
- [x] Regras sensíveis marcadas — D-002 (Deleção, read-only) rastreada em FR-011.
- [x] Cada FR mapeado a `D-NNN` ou seção do briefing.
- [x] Nenhuma contradição com `decision_log.md`.
- [x] Humano validou — **pendente; este Spec será validado após fechar Clarify em W1 track A.**
