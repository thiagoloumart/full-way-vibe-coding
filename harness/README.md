# Harness — Enforcement mecânico

> Em v1.1 (M1), esta pasta é **documentação da estratégia**. Em v1.2 (M2), ela ganha scripts Python executáveis, schemas YAML, GitHub Action e exemplos canônicos que provam o protocolo fecha.

## Propósito
Transformar o protocolo — hoje enforced por convenção e leitura humana — em **verificação mecânica** sem sacrificar a legibilidade.

## Estado atual (v1.1 + W1 track A)

| Componente | Estado |
|---|---|
| `README.md` (este arquivo) | ✅ |
| `rollout.md` | ✅ (plano em 3 estágios) |
| `_audit/` | ✅ (before.tree, inventory.md, progress.md; delta.md + handoff.md em M1.11) |
| `schemas/` | ⬜ (vazio; M2) |
| `scripts/lint_artefato.py` | ✅ W1 track A (40 testes) — valida front-matter, seções `requer:`, links relativos |
| `tests/test_lint_artefato.py` | ✅ W1 track A |

---

## Arquivos lintáveis em M1

Nem todo `.md` do repo é artefato SDD. Em M1, o `lint_artefato.py` valida o contrato apenas dos arquivos abaixo. Rodar o lint fora deste escopo gera FRONTMATTER_AUSENTE (falso negativo esperado — esses arquivos não são artefatos).

### ✅ Lintáveis (front-matter obrigatório)

| Padrão | Descrição | Quem alimenta |
|---|---|---|
| `templates/*.md` | Templates reutilizáveis de artefatos | editores da skill |
| `specs/*/*.md` | Artefatos instanciados dentro de módulos/features | autor de um ciclo SDD |
| `examples/*/*/*.md` | Exemplos canônicos (a partir de M2, v1.2) | retrospective dos ciclos |
| `governanca/adrs/ADR-[0-9]*.md` | ADRs globais instanciados | fluxo de ADR |

### ❌ Fora do escopo M1 (documentação livre, sem front-matter obrigatório)

| Padrão | Por quê |
|---|---|
| Raiz: `README.md`, `SKILL.md`, `AGENTS.md`, `CONTRIBUTING.md`, `filosofia.md`, `00_ANALISE_ESTRATEGICA.md` | Documentação de projeto e entrada — não são artefatos SDD |
| `fases/*.md` | Guias de como conduzir cada fase — doc livre |
| `protocolos/*.md` | Regras transversais de conduta — doc livre |
| `checklists/*.md` | Listas de verificação — doc livre |
| `governanca/adr-global.md`, `metricas.md`, `versioning.md` | Documentação operacional global |
| `governanca/adrs/ADR-index.md` | Índice (metadado), não é ADR em si |
| `harness/**/*.md` | Documentação do próprio harness |

Estender front-matter e lint para qualquer padrão de "fora do escopo" é decisão estratégica separada que exige ADR em v1.3+. Em M2, `smoke_test.py` vai iterar automaticamente nos padrões da tabela "lintáveis".

### Uso manual em M1

```bash
# Um arquivo específico
python3 -m harness.scripts.lint_artefato templates/spec.md

# Com saída JSON para consumo por outras ferramentas
python3 -m harness.scripts.lint_artefato templates/spec.md --format json

# Modo warning-only (estágio E1 do rollout — erros não bloqueiam exit)
python3 -m harness.scripts.lint_artefato templates/spec.md --warnings-only

# Desabilitar cor (CI também respeita NO_COLOR env var automaticamente)
python3 -m harness.scripts.lint_artefato templates/spec.md --no-color

# Todos os templates (bash)
for f in templates/*.md; do python3 -m harness.scripts.lint_artefato "$f"; done

# Todos os ADRs numerados
for f in governanca/adrs/ADR-[0-9]*.md; do python3 -m harness.scripts.lint_artefato "$f"; done
```

---

## Catálogo de códigos de erro (M1)

Códigos em SCREAMING_SNAKE_CASE português (ver `specs/m1-lint/clarify.md §C-001`). Estáveis entre versões — adicionar código novo = minor bump; renomear ou remover = major bump + ADR.

### Erros de lint (exit 1)

| Código | Nível | Gatilho | FR de origem |
|---|---|---|---|
| `FRONTMATTER_AUSENTE` | ERRO | Arquivo não começa com `---\n` ou delimitador de fechamento ausente | FR-001 |
| `YAML_INVALIDO` | ERRO | Front-matter presente mas `pyyaml.safe_load` falha (ou raiz não é dict) | FR-001 |
| `CAMPO_OBRIGATORIO_AUSENTE` | ERRO | Um dos 5 campos obrigatórios (`artefato`, `fase`, `dominio`, `schema_version`, `requer`) não está no front-matter | FR-002 |
| `CAMPO_TIPO_INVALIDO` | ERRO | Campo obrigatório presente mas com tipo errado (ex: `requer: "string"` em vez de lista) | FR-003 |
| `SECAO_OBRIGATORIA_AUSENTE` | ERRO | Item de `requer:` não aparece como heading `##` ou `###` no corpo (após normalização de whitespace e travessão) | FR-004 |
| `SECAO_OBRIGATORIA_NIVEL_INVALIDO` | ERRO | Item de `requer:` aparece como heading mas em nível ≥4 | FR-006 |
| `LINK_QUEBRADO` | ERRO | Link relativo `.md` aponta para arquivo inexistente no filesystem. Ignora externos (http/https/mailto/ftp), fragmentos-apenas (`#secao`), imagens (`![alt](src)`), inline code e fenced blocks | FR-007, FR-008, FR-009, FR-010 |

### Erros de IO (exit 2, via stderr)

| Código | Nível | Gatilho |
|---|---|---|
| `ARQUIVO_NAO_ENCONTRADO` | — | Caminho passado como argumento não existe |
| `ARQUIVO_NAO_MARKDOWN` | — | Arquivo não termina em `.md` |

### Comportamentos especiais

- **Chaves extras no front-matter:** aceitas em M1 sem erro (FR-017). Validação estrita por schema custom fica para M2.
- **`fase: null`:** aceita (templates como `adr.md` e `risk_log.md` usam). Tipos aceitos: `int`, `float`, `None`.
- **UTF-8 BOM:** removido silenciosamente (FR-016).
- **`--warnings-only`:** degrada todos os ERRO em WARN e força exit 0 (FR-015, rollout E1).

### Flags do CLI

| Flag | Default | Efeito |
|---|---|---|
| `--format {human,json}` | `human` | Formato de saída. JSON é array estável de `{arquivo, linha, nivel, codigo, mensagem}`. |
| `--warnings-only` | off | Todos ERRO viram WARN, exit 0 garantido. |
| `--no-color` | off | Desabilita ANSI. `NO_COLOR` env var também desabilita (convenção https://no-color.org/). Cor auto-desabilita se stdout não é TTY (CI). |

## Estado alvo (v1.2)

```
harness/
├── README.md
├── rollout.md
├── _audit/
│   ├── before.tree
│   ├── after.tree
│   ├── inventory.md
│   ├── progress.md
│   ├── delta.md
│   └── handoff.md
├── schemas/
│   ├── qualidade-briefing.yaml
│   ├── qualidade-bmad.yaml
│   ├── qualidade-spec.yaml
│   ├── qualidade-plano.yaml
│   ├── pre-implementacao.yaml
│   ├── pre-merge.yaml
│   ├── mvp.yaml
│   ├── qualidade-generica.yaml  (fallback)
│   └── adr.yaml
├── scripts/
│   ├── lint_artefato.py
│   ├── gate_fase.py
│   ├── extract_invariantes.py
│   ├── smoke_test.py
│   ├── gerar_context_pack.py
│   ├── lint_constituicao.py
│   └── custom_hooks.py
└── tests/
    ├── test_lint_artefato.py
    ├── test_gate_fase.py
    ├── test_extract_invariantes.py
    ├── test_smoke.py
    ├── test_lint_constituicao.py
    └── test_gerar_context_pack.py
```

## Contratos de cada script (M2)

### `lint_artefato.py`
```
uso: python -m harness.scripts.lint_artefato <caminho_artefato> [--schema <nome>]
     [--format human|json] [--warnings-only]
retorna: 0 se válido; 1 se inválido (stderr com diagnóstico)
```
Carrega schema YAML pelo `artefato` do front-matter. Verificadores: `regex`, `presença_seção`, `presença_FR`, `frontmatter_campo`, `contra_referencia`, `custom`.

### `gate_fase.py`
```
uso: python -m harness.scripts.gate_fase <docs/specs/NNN-modulo> <fase_alvo>
retorna: 0 se fases anteriores prontas; 1 caso contrário
```
Mapa fase → artefatos obrigatórios, respeitando domínio.

### `extract_invariantes.py`
```
uso: python -m harness.scripts.extract_invariantes <spec.md>
saída: <spec.md>.invariantes.json
```
Extrai FRs + cenários Given/When/Then + SCs. JSON usado em drift detection.

### `smoke_test.py`
```
uso: python -m harness.scripts.smoke_test <examples/canonical-*>
```
Roda lint_artefato em todos os arquivos + valida rastreabilidade + roda gate_fase.

### `gerar_context_pack.py`
```
uso: python -m harness.scripts.gerar_context_pack <docs/specs/NNN-modulo>
     [--out context_pack.md]
```
Empacota constituição + ADRs + últimas 3 entradas de `decision_log` + artefato da fase atual.

### `lint_constituicao.py`
```
uso: python -m harness.scripts.lint_constituicao [--diff-file <patch>]
retorna: 0 ok; 1 se mudança em Camada 1 sem [major bump] + ADR
```
Usa marcadores `<!-- CAMADA_1_BEGIN/END -->` e `<!-- CAMADA_2_BEGIN/END -->` no `constitution.md`.

## Dependências (M2)
- Python 3.11+.
- `pyyaml` (único pacote externo).
- Git (para ler diffs).

## GitHub Action (M2)
`.github/workflows/harness.yml` (no repo-alvo, não no repo da skill):
- Trigger: PR para `main` tocando `docs/specs/**`, `docs/constitution.md`, `docs/adrs/**`.
- Steps: checkout → setup-python → pip install pyyaml → roda lint em arquivos modificados → roda gate_fase → roda lint_constituicao quando aplicável.
- Política: bloqueia merge via required status check.

## Rollout (ver [`rollout.md`](rollout.md))

Três estágios:
1. **E1 — Warning-only:** CI roda mas não falha; calibra regex e custom hooks contra artefatos reais.
2. **E2 — Bloqueante parcial:** só `lint_artefato` bloqueia; `gate_fase` e `lint_constituicao` ainda warning.
3. **E3 — Bloqueante total:** todos bloqueiam; required status check ativo.

## Invariantes que o harness **nunca** quebra
- Não altera conteúdo de artefato (lint **lê**; não edita).
- Não altera numeração de fases.
- Não altera marcadores epistêmicos.
- Sempre preserva a regra "specs em andamento seguem a versão da skill em que iniciaram".
- Tem **kill switch global** (`HARNESS_ENFORCEMENT=off`) para emergências.

## Para começar a usar em M2 (preview)

```bash
# Instalar deps
pip install pyyaml

# Rodar lint em um artefato
python -m harness.scripts.lint_artefato docs/specs/007-checkout/spec.md

# Verificar se a spec está pronta para Fase 4
python -m harness.scripts.gate_fase docs/specs/007-checkout 4

# Gerar context pack para passar para outro modelo
python -m harness.scripts.gerar_context_pack docs/specs/007-checkout
```

## Para contribuir no harness

Em M2:
1. Cada schema novo vem com caso de teste em `harness/tests/`.
2. Cada regex novo em schema passa em pelo menos 2 exemplos canônicos antes de virar bloqueante.
3. Qualquer mudança no comportamento do linter = bump `schema_version` + ADR global no repo da skill.
