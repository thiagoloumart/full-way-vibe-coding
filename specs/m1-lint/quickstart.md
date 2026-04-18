---
artefato: quickstart
fase: 9
dominio: [software]
schema_version: 1
requer:
  - "1. Pré-requisitos"
  - "2. Subir localmente"
  - "3. Caminho feliz"
  - "4. Caminho de erro"
  - "5. Caminho de permissão"
  - "7. Rollback / limpeza"
  - "8. Quem validou"
---

# Quickstart — `lint_artefato.py` M1

**Data:** 2026-04-18
**Versão da feature:** v1.2 (em W1 track A — inclui F1+F2+F3 do lint)
**Objetivo:** permitir que qualquer pessoa valide manualmente que o lint funciona.

---

## 1. Pré-requisitos

- [ ] Repositório atualizado: `git pull` (ou `git checkout w1a/lint-artefato` para a branch em andamento).
- [ ] **Python 3.11+** instalado. Verificar: `python3 --version` deve retornar `>= 3.11`.
- [ ] **`pyyaml` ≥ 6.0** instalado. Instalação rápida: `pip install pyyaml` (ou `pip install -e .` a partir da raiz para instalar com o `pyproject.toml`).
- [ ] **`pytest` ≥ 8.0** instalado (apenas para rodar suite de testes). Instalação: `pip install pytest`.
- [ ] Sem banco, sem serviços externos, sem env vars obrigatórias — o lint é offline, stateless e read-only.

## 2. Subir localmente

```bash
# A partir da raiz do repo
python3 --version          # esperado: 3.11.x ou superior
python3 -c "import yaml"   # silencioso se pyyaml ok
python3 -c "import harness.scripts.lint_artefato"   # silencioso se bootstrap ok
```

Se `import harness.*` falhar, verifique que está rodando a partir da raiz do repo (o `pyproject.toml` configura `setuptools.packages.find` no diretório atual).

## 3. Caminho feliz

Validar um artefato bem formado:

```bash
python3 -m harness.scripts.lint_artefato templates/spec.md
# Esperado (stdout):
#   OK
# Exit code: 0
```

Confirmar exit code:
```bash
python3 -m harness.scripts.lint_artefato templates/spec.md; echo "exit=$?"
# Esperado:
#   OK
#   exit=0
```

Validar todos os artefatos lintáveis de uma vez (smoke manual):
```bash
for f in templates/*.md specs/m1-lint/*.md governanca/adrs/ADR-[0-9]*.md; do
  python3 -m harness.scripts.lint_artefato "$f" >/dev/null 2>&1 \
    && echo "OK   $f" \
    || echo "FAIL $f"
done
# Esperado: 26 linhas "OK ..." (em W1 track A).
```

Validar com saída JSON (consumível por outras ferramentas):
```bash
python3 -m harness.scripts.lint_artefato templates/spec.md --format json
# Esperado: []
# Exit code: 0
```

## 4. Caminho de erro

### 4.1 Front-matter ausente
```bash
python3 -m harness.scripts.lint_artefato harness/tests/fixtures/no_frontmatter.md
# Esperado (stdout):
#   harness/tests/fixtures/no_frontmatter.md:1: [ERRO] FRONTMATTER_AUSENTE ...
# Exit code: 1
```

### 4.2 Seção obrigatória ausente
```bash
python3 -m harness.scripts.lint_artefato harness/tests/fixtures/section_missing.md
# Esperado:
#   .../section_missing.md:X: [ERRO] SECAO_OBRIGATORIA_AUSENTE ...
# Exit code: 1
```

### 4.3 Link quebrado
```bash
python3 -m harness.scripts.lint_artefato harness/tests/fixtures/link_broken.md
# Esperado:
#   .../link_broken.md:X: [ERRO] LINK_QUEBRADO ...
# Exit code: 1
```

### 4.4 Arquivo não existe (exit 2, stderr)
```bash
python3 -m harness.scripts.lint_artefato /tmp/nao_existe.md
# Esperado (stderr):
#   ARQUIVO_NAO_ENCONTRADO: /tmp/nao_existe.md
# Exit code: 2
```

### 4.5 Modo warning-only (estágio E1 do rollout)
```bash
python3 -m harness.scripts.lint_artefato harness/tests/fixtures/link_broken.md --warnings-only
# Esperado (stdout):
#   .../link_broken.md:X: [WARN] LINK_QUEBRADO ...
# Exit code: 0   (erros degradados para warning, exit forçado)
```

## 5. Caminho de permissão

Não aplicável — o lint é CLI read-only sem sistema de papéis/autenticação.
FR-011 garante que o script **nunca** modifica arquivo (invariante Camada 1).

Verificação rápida do invariante: antes e depois de rodar o lint, o hash do arquivo alvo não muda:
```bash
md5 templates/spec.md                                          # hash X
python3 -m harness.scripts.lint_artefato templates/spec.md     # roda lint
md5 templates/spec.md                                          # ainda X
```

## 6. Caminho de falha parcial (se aplicável)

O lint é offline e stateless. Não há integração externa que possa falhar parcialmente.

Único risco conhecido: `pyyaml` ausente ou versão incompatível:
```bash
python3 -c "import yaml; print(yaml.__version__)"
# Se ImportError → `pip install pyyaml>=6.0`.
```

## 7. Rollback / limpeza

O lint não cria estado persistente. "Rollback" após um uso é no-op.

Para remover a instalação `pip install -e .`:
```bash
pip uninstall full-way-vibe-coding
# OU simplesmente delete o .egg-info:
rm -rf full_way_vibe_coding.egg-info
```

Para limpar caches:
```bash
rm -rf .pytest_cache harness/**/__pycache__
```

## 8. Quem validou

| Data | Pessoa | Ambiente | Resultado |
|---|---|---|---|
| 2026-04-18 | Thiago Loumart (self-review time=1) | macOS / Python 3.11.9 / pyyaml 6.0.3 / pytest 9.0.3 | ✅ 80/80 testes · 26/26 smoke · SCs 001–005 verdes |

---

**Checklist de qualidade do quickstart:**
- [x] Passos reproduzíveis por alguém sem contexto.
- [x] Cada passo tem "resultado esperado".
- [x] Cobre caminho feliz, 5 variantes de erro, warning-only, JSON, e rollback.
- [x] Invariante read-only (FR-011) tem verificação por hash.
- [x] Nenhuma dependência fora do ADR-002 (Python + pyyaml + pytest).
