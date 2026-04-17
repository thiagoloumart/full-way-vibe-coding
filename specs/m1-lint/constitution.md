---
artefato: constituicao
fase: 3.5
dominio: [software]
schema_version: 1
bicamada: true
requer:
  - "Identidade"
  - "ADRs ativas (referência)"
  - "1. Arquitetura (Camada 1)"
  - "6. Regras de segurança estruturais (Camada 1)"
  - "7. Limites do MVP (Camada 1)"
  - "10. Decisões estruturais permanentes (Camada 1)"
  - "4. Stack / Sistemas de origem (Camada 2)"
  - "8. Estilo / Convenções (Camada 2)"
  - "Histórico de versões"
marcadores_camada:
  camada_1_begin: "<!-- CAMADA_1_BEGIN -->"
  camada_1_end:   "<!-- CAMADA_1_END -->"
  camada_2_begin: "<!-- CAMADA_2_BEGIN -->"
  camada_2_end:   "<!-- CAMADA_2_END -->"
---

# Constituição — `lint_artefato.py` (M1 lint)

## Identidade

| Campo | Valor |
|---|---|
| **Versão** | v1.0 |
| **Data** | 2026-04-17 |
| **Domínio primário** | D1 software |
| **Status** | Validada |
| **Origem** | Inferida de ADR-002 + `harness/README.md` + `harness/rollout.md` + `decision_log.md §D-001–D-003` |
| **Autor** | Thiago Loumart |

> Esta constituição aplica-se exclusivamente ao módulo `lint_artefato.py` e seus testes.
> Herda invariantes da constituição global da skill (em breve `constitution.md` raiz, v1.2+).
> Conflitos entre pedidos pontuais e esta constituição devem virar ADR, nunca ser resolvidos em silêncio.

---

## ADRs ativas (referência)

| ADR | Título | Status | Camada afetada | Data | Resumo do efeito |
|---|---|---|---|---|---|
| [ADR-001](../../governanca/adrs/ADR-001-v1.1-dual-domain.md) | Adoção de arquitetura dual-domain v1.1 | Aceita | 1 | 2026-04-17 | Define contexto em que o lint opera (router + domínios). |
| [ADR-002](../../governanca/adrs/ADR-002-stack-harness.md) | Stack do harness: Python 3.11+ com pyyaml | Aceita | 2 | 2026-04-17 | **Determina toda a Camada 2 deste módulo.** |
| [ADR-003](../../governanca/adrs/ADR-003-estrategia-publicacao.md) | Estratégia de publicação v1.2 (mesmo repo) | Aceita | 2 | 2026-04-17 | Impacta onde o lint é publicado; não afeta código. |

---

<!-- CAMADA_1_BEGIN -->

## Camada 1 — Invariantes (não mudam durante o ciclo)

> Alterar qualquer item desta camada exige ADR com `camada_afetada: 1` + **major bump** (v1 → v2.0) + aprovação humana explícita.

### 1. Arquitetura (estrutural)
- **Estilo:** script Python single-file executável via `python -m harness.scripts.lint_artefato`.
- **Limites de domínio:** um script, um propósito (validar um artefato por chamada). Stateless. Sem efeitos colaterais no filesystem.
- **Comunicação:** chamada direta (CLI); saída via stdout; código de retorno inteiro.
- **Interfaces fronteira:** (a) caminho de arquivo como argumento posicional; (b) flags `--format`, `--warnings-only`, `--no-color`; (c) stdout (humano ou JSON); (d) exit code.

### 2. Papéis e conduta
- Humano × IA: ver [`../../filosofia.md §3`](../../filosofia.md#3-papéis--humano--ia).
- Marcadores obrigatórios nos artefatos gerados pelo processo de dev do lint: `[INFERÊNCIA]`, `[NEEDS CLARIFICATION]`, `[DECISÃO HUMANA]`, `[RISCO ASSUMIDO]`.
- Regra §5.4 ativa para D1 — ver [`../../filosofia.md §7.1`](../../filosofia.md#71-d1--software). A única regra aplicável é **Deleção** — invariantemente tratada por FR-011.

### 3. Valores bloqueantes do domínio (D1)
- **Read-only absoluto.** O script **nunca** modifica, cria, renomeia ou deleta arquivo. Esta é a materialização de D-002 e FR-011, e é **invariante permanente** (não pode ser flexibilizada nem por ADR de Camada 2 — exige ADR de Camada 1 + major bump).
- **Stateless.** Nenhum estado persistente entre chamadas. Cada invocação é idempotente e isolada.
- **Determinismo.** Dada a mesma entrada (arquivo + filesystem), a saída é sempre igual.

### 6. Regras de segurança estruturais (Camada 1)
- Nenhuma chamada de rede no script.
- Nenhum `subprocess` ou `os.system`; apenas leitura de arquivos.
- Nenhum `eval`, `exec`, ou `pickle.load`.
- Nenhum uso de `os.remove`, `os.rename`, `shutil.move`, `open(..., 'w')` no path do artefato validado.
- Validação de path: `pathlib.Path.resolve()` antes de abrir, nunca `../../../../etc/passwd` é tratado especial — o lint é read-only; se o path aponta para arquivo legítimo do filesystem, lê; ninguém é atacado via docs.
- **Nenhum dado do artefato é persistido fora da saída stdout/stderr.**

### 7. Limites do MVP (Camada 1)
Estas restrições **não podem** ser relaxadas em M1 sem ADR:
- Exatamente 3 classes de validação (front-matter, seções `requer:`, links relativos `.md`).
- Zero validação semântica (validar regra de negócio do artefato). → M2.
- Zero contra-referência entre artefatos (validar D-NNN ↔ decision_log). → M2.
- Zero gate de fase. → `gate_fase.py` em M2.
- Zero auto-fix. → **nunca**, por Camada 1 §3.
- Saída em português (C-001).

### 10. Decisões estruturais permanentes (Camada 1)
- **Contrato de exit code:** 0 = OK; 1 = erro de lint; 2 = erro de IO. Estável entre versões; breaking change exige major bump + ADR.
- **Contrato de formato JSON:** array de `{arquivo, linha, nivel, codigo, mensagem}`. Chaves estáveis; adição de chaves nova = minor bump (retrocompatível); remoção ou renomeação = major bump.
- **Códigos de erro:** SCREAMING_SNAKE_CASE em português (C-001). Adicionar código novo = minor; renomear/remover = major.
- **Uma invocação = um artefato.** Para validar múltiplos, o chamador itera. Batch dentro do script fica para M2.

<!-- CAMADA_1_END -->

---

<!-- CAMADA_2_BEGIN -->

## Camada 2 — Escolhas (podem mudar via ADR minor)

> Alterar qualquer item desta camada exige ADR com `camada_afetada: 2` + **minor bump** (v1.0 → v1.1).

### 4. Stack / Sistemas de origem (Camada 2)
- **Linguagem:** Python 3.11+ → [ADR-002](../../governanca/adrs/ADR-002-stack-harness.md).
- **Dep externa única:** `pyyaml` (≥6.0).
- **Dep dev:** `pytest` (≥8.0).
- **Empacotamento:** `pyproject.toml` minimalista no root do repo (M2 pode mover para `harness/pyproject.toml` se o diretório crescer).
- **Parsing:** `pyyaml.safe_load` para front-matter; `re` (stdlib) para corpo Markdown → [D-001](decision_log.md#d-001--stack-e-abordagem-de-parsing).
- **Testes:** pytest com fixtures em `harness/tests/fixtures/`.
- **Execução:** `python -m harness.scripts.lint_artefato <arquivo>`.

### 8. Estilo / Convenções (Camada 2)
- **Formatter:** `ruff format` (dep dev opcional em M1; obrigatório em M2).
- **Linter Python:** `ruff check` com preset `E,F,W,I,UP,B,SIM` (dev opcional em M1).
- **Tipagem:** type hints em todas as funções públicas (`def validate(...) -> list[Diagnostic]:`). `mypy` estrito como evolução em M2.
- **Nomes:** snake_case para funções/variáveis, PascalCase para dataclasses/tipos, SCREAMING_SNAKE para constantes.
- **Docstrings:** triple-quoted em funções públicas. Google style ou simples — preferência pela simples em M1.
- **Imports:** stdlib primeiro, terceiros (pyyaml) depois, locais por último — separados por linha em branco.
- **Log de erro:** `print(..., file=sys.stderr)` para erros de IO (exit 2); `print(..., file=sys.stdout)` para diagnósticos (exit 0/1).
- **Cor:** via ANSI codes inline; sem dep externa de color (mantém `pyyaml` como única externa). → C-002.

<!-- CAMADA_2_END -->

---

## Histórico de versões

| Versão | Data | Alteração | ADR | Autor |
|---|---|---|---|---|
| v1.0 | 2026-04-17 | Criação inicial, derivada de ADR-002 + `decision_log.md §D-001-D-003` + `clarify.md §C-001-C-003`. | ADR-002 | Thiago Loumart |

---

**Checklist antes de aprovar:**
- [x] Camada 1 contém **apenas** invariantes (arquitetura, segurança, limites MVP, decisões permanentes).
- [x] Camada 2 contém **apenas** escolhas (stack, estilo, convenções).
- [x] ADRs ativas referenciadas com link e estado.
- [x] Read-only (D-002) está em Camada 1 como invariante não-negociável.
- [x] Regra §5.4 ativa para D1 referenciada.
- [x] Marcadores HTML `<!-- CAMADA_X_BEGIN/END -->` presentes (necessários para `lint_constituicao.py` de M2).
- [x] Humano assinou.
