---
artefato: briefing
fase: 1
dominio: [software]
schema_version: 1
requer:
  - "1. Visão Geral da Solução"
  - "2. O Problema"
  - "7. Módulos e Casos de Uso"
  - "8. Fluxo Principal (alto nível)"
  - "9. Restrições e Não-objetivos"
---

# Briefing de Software: `lint_artefato.py` — linter mínimo de artefatos SDD

**Data:** 2026-04-17
**Autor do briefing:** Thiago Loumart
**Status:** Validado
**Projeto:** full-way-vibe-coding — brownfield
**Referência:** `bmad.md` v0.1 + `decision_log.md` (D-001, D-002, D-003)

---

## 1. Visão Geral da Solução

Um script Python pequeno que lê um artefato Markdown da skill (spec, bmad, adr, clarify, etc.), confere se o "contrato" declarado no topo do arquivo (front-matter YAML com `requer:`) foi cumprido no corpo, e confere se os links entre artefatos apontam para arquivos que existem. Roda local antes do commit e no CI em cada PR. Quando falha, explica exatamente o que faltou — nunca edita o artefato.

## 2. O Problema

Artefatos da skill crescem em volume a partir da Wave 2 (dogfood canônico produz 15 artefatos de uma vez). Hoje, nada valida mecanicamente que eles seguem o contrato declarado — só olho humano. Consequências observadas:

- Dor principal: **drift silencioso.** Artefato promete cumprir `requer: [A, B, C]` no front-matter, mas o corpo tem só A e B. Ninguém percebe até semanas depois, quando outro artefato referencia C e quebra.
- Quem sofre: autor (não percebe o que esqueceu), revisor de PR (precisa checar manualmente, não escala), futuros contribuidores (encontram inconsistências sem saber se é bug ou decisão).
- Consequências quando não resolvido:
  - Seções obrigatórias sumindo progressivamente.
  - Links apontando para arquivos renomeados/movidos não reclamam.
  - Front-matter com typo (`artefacto:` em vez de `artefato:`) passa silenciosamente e quebra futuras automações.
  - Confiança no método cai (a skill prega rigor que não se aplica).

## 3. Público-Alvo

- **Primário:** desenvolvedores que contribuem com a skill. Editam artefatos `.md` em `specs/`, `templates/`, `governanca/`, `examples/`, `fases/`.
- **Secundário:** CI do GitHub Actions. Executa o lint em cada PR automaticamente.
- **Terciário:** autores de artefatos em **repos que adotaram a skill** (uso futuro, pós-v1.2).
- **Contexto de uso:** terminal local (dev) + CI (automático). Zero UI.

## 4. Modelo de Precificação e Negócio

Não aplicável — lint é doc-tooling open-source dentro de skill open-source. Sem cobrança, sem gatilho comercial.

## 5. Perfis de Acesso

| Perfil | Descrição | Pode (alto nível) |
|---|---|---|
| Autor de artefato | Dev que escreve `.md` | Rodar o lint local (`python -m harness.scripts.lint_artefato <path>`) antes do commit. |
| CI (GitHub Actions) | Automação de PR | Chamar o lint em todos os `.md` modificados no diff; usar o código de saída para aprovar/bloquear merge (em estágio E2+). |
| Revisor de PR | Humano que aprova merge | Ler relatório do lint na saída do CI e usar como input do review. |
| Maintainer do harness | Autor do linter | Estender regras via novos schemas YAML — mas isso é M2, fora deste briefing. |

## 6. Canais / Superfícies

- CLI Python: `python -m harness.scripts.lint_artefato <arquivo> [--format human|json] [--warnings-only]`.
- GitHub Actions: workflow `.github/workflows/lint.yml` que rodará o script em PRs.
- Ambientes: dev local (macOS/Linux com Python 3.11+) + runner do GitHub (Ubuntu latest).

## 7. Módulos e Casos de Uso

### 7.1 Módulo: Validação de front-matter

**Objetivo:** garantir que o bloco YAML entre os primeiros `---...---` existe, parseia sem erro, e tem os campos obrigatórios.

**Ações principais:**
- Autor edita um `.md` e esquece o front-matter → script reporta `FRONTMATTER_AUSENTE` e sai com código 1.
- Autor digita `artefacto:` em vez de `artefato:` → script reporta `CAMPO_OBRIGATORIO_AUSENTE: artefato` na linha do YAML.
- YAML está mal formado (ex.: indentação errada) → script reporta o erro do parser com linha e contexto humano, não stack trace.

**Regras específicas:**
- Campos obrigatórios: `artefato`, `fase`, `dominio`, `schema_version`, `requer`.
- `schema_version` precisa ser inteiro.
- `dominio` precisa ser lista (mesmo com um único item).
- `requer` precisa ser lista de strings.

### 7.2 Módulo: Validação de seções `requer:`

**Objetivo:** cada item listado em `requer:` deve aparecer como um heading `##` ou `###` no corpo.

**Ações principais:**
- Autor declara `requer: ["1. Breakdown"]` e esquece de escrever a seção → script reporta `SECAO_OBRIGATORIA_AUSENTE: "1. Breakdown"`.
- Autor escreve a seção com whitespace diferente (`##  1. Breakdown` com 2 espaços) → script normaliza whitespace antes de comparar e aceita.
- Autor escreve como `####` (profundidade 4) → script rejeita (só aceita `##` e `###` para obrigatórias).

**Regras específicas:**
- Comparação normalizada: collapse de múltiplos espaços, trim, case-sensitive mas normaliza travessões (`—` e `--`).
- Seções em níveis mais profundos (`####` ou mais) não contam como "obrigatórias satisfeitas".
- Headings dentro de blocos de código ``` não contam (aceitar `[RISCO ASSUMIDO]` de D-001).

### 7.3 Módulo: Validação de links relativos internos

**Objetivo:** todo link na sintaxe `[texto](caminho/relativo.md)` aponta para arquivo que existe no filesystem.

**Ações principais:**
- Autor renomeia `templates/briefing.md` → `templates/briefing_d1.md` e não atualiza links → script reporta `LINK_QUEBRADO: ../templates/briefing.md` com linha onde aparece.
- Link para âncora (`caminho.md#secao`) → só o arquivo é validado; a âncora não (documentado como limitação).
- Link absoluto (`https://...`) → ignorado (fora do escopo M1).

**Regras específicas:**
- Resolução de caminho: relativa ao arquivo onde o link aparece.
- `./foo.md`, `../foo.md`, `foo.md` — todos validados.
- Links em blocos de código ``` → ignorados (evitar falso positivo em docs de exemplo).

### 7.4 Módulo: Report

**Objetivo:** comunicar resultados de forma acionável para humano e máquina.

**Ações principais:**
- Formato humano (default): saída colorida com `arquivo:linha: [NIVEL] CODIGO mensagem`.
- Formato JSON (`--format json`): array de diagnósticos estruturados para consumo por outras ferramentas.
- Código de saída: 0 se nenhum erro; 1 se ≥1 erro.
- Flag `--warnings-only`: trata erros como warnings (usado no estágio E1 do rollout).

**Regras específicas:**
- Ordem dos diagnósticos: por linha crescente, erros antes de warnings.
- Mensagens em português ou inglês? **Decisão:** português (alinha com o corpo da skill). Inglês se o usuário final pedir via ADR futura.

## 8. Fluxo Principal (alto nível)

1. Autor edita `specs/007-checkout/spec.md` localmente.
2. Antes de commitar, roda `python -m harness.scripts.lint_artefato specs/007-checkout/spec.md` — opcional mas recomendado.
3. Se erro: autor lê relatório, corrige o artefato, roda de novo, verde.
4. Commit e push para branch; abre PR.
5. GitHub Action detecta `.md` modificados e chama o lint para cada um.
6. Em E1 (warning-only): lint falha = comentário no PR, não bloqueia merge.
7. Em E2+ (bloqueante): lint falha = merge bloqueado via required status check.
8. Merge liberado quando todos os `.md` modificados passam verde (ou `[RISCO ASSUMIDO: harness-bypass]` explícito no artefato).

## 9. Restrições e Não-objetivos

### Não-objetivos (fora do escopo desta iteração)
- **Não** valida semântica (ex.: "spec tem ≥2 caminhos em Analyze"). Isso exige schema custom por artefato — M2.
- **Não** valida contra-referências (ex.: FR-042 referencia D-099; D-099 existe em `decision_log.md`?). M2.
- **Não** executa `gate_fase.py` — M2.
- **Não** valida constituição bicamada (`lint_constituicao.py`) — M2.
- **Não** gera context packs — M2.
- **Não** edita artefatos. Read-only é invariante (D-002).
- **Não** valida âncoras de link (`#section-id`).
- **Não** valida links externos (`https://...`).
- **Não** testa arquivos binários ou não-`.md`; detecta e sai com erro claro.

### Restrições
- **Stack:** Python 3.11+ (ADR-002), `pyyaml` como dep externa única, `pytest` como dev-only.
- **Tamanho:** alvo ~200 linhas de código de produção; tolerância ≤300. Se passar de 300, fragmentar em módulos dentro de `harness/scripts/lint/`.
- **Tempo de execução:** <500ms para um artefato típico de 200 linhas. <5s para toda a skill (smoke test, ~70 arquivos) — **meta**, não requisito bloqueante em M1.
- **Dependências externas bloqueantes:** nenhuma além de `pyyaml`. Sem banco, sem API, sem rede.

## 10. Itens ainda em aberto

- [ ] `[NEEDS CLARIFICATION]` Nome exato dos códigos de erro: `FRONTMATTER_AUSENTE` vs `FM_MISSING` vs outro padrão? Decisão para Fase 3 Clarify.
- [ ] `[NEEDS CLARIFICATION]` Saída humana colorida por padrão, ou só com `--color`? Maioria dos CIs desabilita cor automaticamente via variável de ambiente `NO_COLOR` — verificar em Fase 3.
- [ ] `[NEEDS CLARIFICATION]` Flag para listar regras aplicadas (`--list-rules`) entra em M1 ou M2? Útil para docs, não crítico.

---

**Checklist mínimo antes de aprovar:**
- [x] Zero decisões técnicas não-necessárias no texto (menções a `pyyaml`, Python 3.11+ herdam de ADR-002, não são decisões novas).
- [x] Cada ação usa verbo concreto.
- [x] Perfis estão definidos.
- [x] Módulos foram explorados com pergunta cíclica — 4 módulos identificados, cada um com ações e regras.
- [x] Humano validou — **pendente; este briefing será validado ao fim de W1 track A ou em Clarify.**
