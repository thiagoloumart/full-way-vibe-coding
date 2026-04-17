# Harness — Enforcement mecânico

> Em v1.1 (M1), esta pasta é **documentação da estratégia**. Em v1.2 (M2), ela ganha scripts Python executáveis, schemas YAML, GitHub Action e exemplos canônicos que provam o protocolo fecha.

## Propósito
Transformar o protocolo — hoje enforced por convenção e leitura humana — em **verificação mecânica** sem sacrificar a legibilidade.

## Estado atual (v1.1)

| Componente | Estado |
|---|---|
| `README.md` (este arquivo) | ✅ |
| `rollout.md` | ✅ (plano em 3 estágios) |
| `_audit/` | ✅ (before.tree, inventory.md, progress.md, delta.md, handoff.md em M1.11) |
| `schemas/` | ⬜ (vazio; M2) |
| `scripts/` | ⬜ (vazio; M2) |
| `tests/` | ⬜ (vazio; M2) |

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
