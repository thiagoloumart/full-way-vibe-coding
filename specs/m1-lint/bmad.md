---
artefato: bmad
fase: 0.5
dominio: [software]
schema_version: 1
requer:
  - "1. Breakdown — decomposição do problema"
  - "2. Model — modelagem do sistema"
  - "3. Analyze — análise de alternativas"
  - "4. Decide — decisão registrada"
  - "5. Contrato para o Briefing (ponte para Fase 1)"
---

# BMAD — `lint_artefato.py` (M1 lint mínimo)

**Data:** 2026-04-17
**Autor:** Thiago Loumart
**Status:** Validado
**Projeto:** full-way-vibe-coding — brownfield (skill com harness/ existente como doc-only)
**Referência:** W1 track A da adequação v1.2; ADR-002 (stack Python); harness/README.md; harness/rollout.md estágio E1

---

## 1. Breakdown — decomposição do problema

### 1.1 Problema real (1 frase)
Artefatos SDD (spec, bmad, clarify, adr, etc.) são adicionados ao repo sem validação mecânica de que seguem o contrato declarado no próprio front-matter, gerando drift silencioso que só aparece como confusão semanas depois.

### 1.2 Quem sofre, quando, com que frequência
- **Quem:** autor (erra o front-matter e não percebe); revisor de PR (precisa checar manualmente se `requer:` bate com seções); futuros contribuidores (encontram artefatos inconsistentes entre si sem entender se é bug ou decisão).
- **Momento em que dói:** ao ler o artefato dias depois — "por que esta spec não tem seção Edge Cases? é erro ou proposital?"
- **Frequência / intensidade:** proporcional ao volume de artefatos; hoje baixo (repo novo), cresce exponencialmente a partir de W2 (dogfood cria 15 artefatos de uma vez no exemplo canônico).
- **Consequências quando não resolvido:** seções faltando não são detectadas; links apontando para arquivos renomeados/movidos não reclamam; front-matter com typo quebra futuro `gate_fase.py` sem aviso prévio.

### 1.3 Causa-raiz vs sintoma aparente
| Observado (sintoma) | Causa-raiz provável | Evidência |
|---|---|---|
| Score SDD dim 6 = 1 (nota mínima) | Ausência de qualquer enforcement mecânico | Auditoria 2026-04-17; `.github/workflows/` inexistente |
| Drift em `harness/_audit/progress.md` (só corrigido em W0) | Nada valida que plano e execução convergem | Inventory indicou M1.11 como pendente por 4 commits |
| Templates declaram `requer:` mas não há régua | `pyyaml` não lido; regex de seção não executada | W0 validou ADRs manualmente — não escala |

### 1.4 Subproblemas (MECE)
- **A:** Parseamento confiável de YAML front-matter (primeiro `---...---` do arquivo).
- **B:** Extração de seções Markdown (headings `##` e `###` no corpo).
- **C:** Validação cruzada entre campo `requer:` do front-matter e presença de cada seção no corpo.
- **D:** Validação de links relativos internos (ex.: `[x](../foo.md)`) apontando para arquivos que existem.
- **E:** Reportar resultado de forma acionável (humano ou CI).

### 1.5 Core vs periférico
| Subproblema | Core | Periférico |
|---|---|---|
| A Front-matter parse | ✅ | |
| B Seções Markdown | ✅ | |
| C Validar `requer:` × corpo | ✅ | |
| D Links internos | ✅ | |
| E Report | ✅ | |

**Foco deste ciclo:** todos os 5 (escopo mínimo).

Fora de escopo — vai para M2 outros scripts:
- Validação de D-NNN referenciados em FRs (contra-referência ao `decision_log.md`).
- Validação semântica (ex.: "existem ≥2 caminhos em Analyze"); entra em schema específico, M2.
- Execução de regex/schemas custom por artefato (M2 via `harness/schemas/*.yaml`).
- `gate_fase.py`, `smoke_test.py`, `lint_constituicao.py` — W2/W3.

---

## 2. Model — modelagem do sistema

### 2.1 Atores
| Papel | Descrição (1 linha) | Pode (alto nível) | Não pode |
|---|---|---|---|
| Autor de artefato | Dev que cria/edita um `.md` em `specs/`, `templates/`, etc. | Rodar lint local antes do commit | Fazer merge se lint falhar (em E2+) |
| CI (GitHub Actions) | Rodador automatizado em PR/push | Chamar `lint_artefato.py` em todos os `.md` modificados | Editar artefato; alterar comportamento |
| Revisor de PR | Humano aprovando merge | Usar saída do lint como input de review | Ignorar falhas do lint sem ADR de bypass |
| `lint_artefato.py` (sistema) | Script Python | Ler, parsear, validar, reportar | Escrever no artefato; editar; mover |

### 2.2 Fluxo principal ponta a ponta (5 passos)
1. Autor edita `specs/007-checkout/spec.md` e commita na branch.
2. CI dispara em push para PR; workflow roda `python -m harness.scripts.lint_artefato <arquivo>` para cada `.md` no diff.
3. Script lê o arquivo, parseia front-matter, identifica `requer:`, escaneia corpo por headings, valida cada item de `requer:` como presente, checa cada link relativo interno.
4. Script imprime relatório (humano ou JSON) no stdout e retorna código 0 (ok) ou 1 (falha).
5. CI reporta: verde → PR liberado para review; vermelho (em E1: warning; em E2+: bloqueio).

### 2.3 Entidades
- **Artefato:** arquivo `.md` com front-matter YAML (campo `artefato`, `fase`, `dominio`, `requer`).
- **FrontMatter:** bloco YAML entre `---...---` no topo.
- **SeçãoObrigatória:** string declarada em `requer:` que deve aparecer como heading (`##` ou `###`) no corpo.
- **Link:** padrão `[texto](caminho/relativo.md)` ou `[texto](caminho/relativo.md#ancora)`.
- **Diagnóstico:** struct `{arquivo, nível, código, linha, mensagem}`.

### 2.4 Fricções previsíveis
- Casos de heading ambíguo: `## 1. Breakdown — decomposição do problema` vs `requer: "1. Breakdown — decomposição do problema"` (precisa normalizar espaços, travessões, números). **Decisão de normalização vira `D-NNN`.**
- Links com âncora (`#section-id`) — só valido o arquivo, não a âncora nesta versão.
- Links absolutos (`https://…`) — não validar (fora do escopo mínimo).
- Arquivo binário ou fora de `.md` passado por engano — script deve falhar gracioso.
- Front-matter ausente — deve ser erro claro, não crash.
- YAML mal formado — deve reportar linha + contexto, não stack trace Python.

### 2.5 O que precisa persistir
Nada. `lint_artefato.py` é **stateless** — cada chamada lê artefato e reporta. Estado de cobertura agregada vive no `smoke_test.py` (W3), não neste script.

### 2.6 Regras sensíveis (Manual §5.4)

| Regra | Aplica? | Já decidida em | Status |
|---|---|---|---|
| Cobrança | não | — | N/A — script doc-tooling |
| Permissão / autorização | não | — | N/A |
| Estorno / cancelamento | não | — | N/A |
| Deleção | **sim — o script pode falhar em apagar conteúdo?** | — | decidida no BMAD: **NÃO. Script é read-only, nunca edita. Invariante registrado em Decide §4.3 e harness/README.md já declara.** |
| Expiração | não | — | N/A |
| Visibilidade entre papéis | não | — | N/A — todo artefato é público no repo |
| Histórico | não | — | N/A — saída do script não é persistida |
| Auditoria | tangencial | — | Script imprime relatórios; se logados pelo CI, ficam rastreáveis. Não é auditoria sensível §5.4. |

Única regra relevante (Deleção) já decidida no BMAD: **script read-only sempre**.

---

## 3. Analyze — análise de alternativas

### 3.1 Caminhos plausíveis (3)

| # | Caminho | Descrição |
|---|---|---|
| A | **Regex puro** | Sem `pyyaml`. Extrai front-matter com regex `^---\n(.*?)\n---\n`, parseia manualmente chaves `artefato:`, `requer:`. Valida seções com regex `^#{2,3}\s+(.+)$`. Links com regex `\[([^\]]+)\]\(([^)]+)\)`. |
| B | **`pyyaml` para front-matter + regex para corpo** | Usa `yaml.safe_load` para front-matter (robusto contra listas aninhadas), regex só para extrair seções e links. Decisão já fechada em ADR-002 (pyyaml como dep única). |
| C | **Parser completo de Markdown (ex.: `markdown-it-py`)** | Usa AST de Markdown para extrair seções e links com precisão total. |

### 3.2 Matriz de trade-offs

| Caminho | Velocidade (dev) | Qualidade (correção) | Risco | Reversibilidade | Custo |
|---|---|---|---|---|---|
| A | 🟢 dep zero | 🔴 parse manual de YAML-lists é frágil | 🟡 regex YAML falha em `requer:` com aninhamento | 🟢 fácil migrar para B | 🟢 |
| **B** | 🟢 `pyyaml` resolve YAML | 🟢 front-matter robusto; seções/links simples o bastante para regex | 🟢 pyyaml é maduro; regex escopo limitado | 🟢 fácil migrar para C | 🟡 1 dep |
| C | 🔴 lento (curva do AST) | 🟢 correção total | 🔴 overengineered para 200 linhas de script | 🟡 2 deps (markdown-it-py + sua transitiva) | 🔴 |

### 3.3 Menor caminho funcional
**B** — o front-matter já exige `pyyaml` pela ADR-002; seções e links via regex cobrem 95% dos casos reais em Markdown bem-comportado (o que a skill já exige).

### 3.4 Pre-mortem por caminho

- **Caminho A:** "falhamos porque listas aninhadas no `requer:` são raras hoje mas comuns quando virmos regras custom (M2); parse manual de YAML quebrou em artefato X e ninguém percebeu até o smoke_test."
- **Caminho B:** "falhamos porque a regex `^#{2,3}\s+(.+)$` matcha código dentro de blocos ```; precisa skipar blocos de código antes de matchar headings. Refactor em sessão 3."
- **Caminho C:** "falhamos porque markdown-it-py introduz 2 deps transitivas (`mdurl`, …) e a resolução em `pip` em CI ocasionalmente falha; debug impossível para colaboradores externos."

### 3.5 Riscos de overengineering
- **A:** escrever YAML parser próprio. Descartar agora.
- **B:** generalizar a regex de seção além de `##` e `###` para capturar qualquer profundidade — não faz falta em M1.
- **C:** apreciar AST por elegância quando 200 linhas resolvem.

---

## 4. Decide — decisão registrada

### 4.1 Caminho escolhido
**B — `pyyaml` para front-matter + regex para corpo.**

### 4.2 Justificativa (critério dominante)
**Reversibilidade + alinhamento com ADR-002**. B usa exatamente a única dep já decidida (`pyyaml`) e mantém o script pequeno (~200 linhas). Se em M2/M3 surgir necessidade de AST (pouco provável para doc-tooling), migrar de B para C é trivial porque a interface pública do script é "caminho → stdout + exit code" — nada exposto ao chamador depende do método interno de parsing.

### 4.3 Alternativas descartadas
| # | Caminho | Motivo do descarte |
|---|---|---|
| A | Regex puro | Parse manual de YAML é frágil para estruturas aninhadas; ganhar 0 deps não compensa. |
| C | AST Markdown completo | Overengineered para tarefa de regex simples; 2 deps transitivas em CI aumentam risco de falha não-relacionada ao lint. |

### 4.4 Riscos aceitos
- `[RISCO ASSUMIDO]` a regex de heading pode matchar `##` dentro de bloco de código ```. Mitigação em M1: documentar na saída — se algum artefato sofrer, abrir ADR-NNN para migrar para C. Probabilidade em artefatos da skill: baixa (templates não têm ``` com `##` dentro; spec pode ter, mas aí a regex precisa pular blocos de código, o que é ajuste pequeno).
- `[RISCO ASSUMIDO]` links para âncoras (`#section`) são validados só até o arquivo, não a âncora. Mitigação: M2 estende quando necessário.

### 4.5 Critérios de invalidação
- Se ≥3 artefatos reais (durante W2 dogfood) forem falsamente rejeitados por regex → abrir ADR para migrar para C.
- Se `pyyaml` for descontinuado ou tiver CVE crítico sem patch → reavaliar stack (ADR-002 revisitada).

### 4.6 Hipóteses em aberto
- [ ] A regex de heading funciona para todos os headings usados nos templates atuais — a validar no primeiro teste do dogfood W2.
- [ ] Normalização de string de comparação (`requer: "X"` vs heading `## X`) cobre todos os casos — a testar com 20 artefatos de exemplo.

---

## 5. Contrato para o Briefing (ponte para Fase 1)

- **Problema real:** Artefatos SDD crescem sem régua mecânica, amplificando drift.
- **Atores principais:** Autor (dev), CI (GitHub Actions), Revisor (humano), `lint_artefato.py`.
- **Fluxo de alto nível:** autor commita → CI roda lint → lint reporta → review decide.
- **Caminho escolhido:** `pyyaml` + regex, script único `harness/scripts/lint_artefato.py` stateless.
- **Alternativas descartadas:** regex puro (frágil), AST completo (overengineered).
- **Regras sensíveis a detalhar em Clarify:** nenhuma — só Deleção tangencia, já decidida como invariante.

---

**Checklist antes de aprovar:**
- [x] Problema real em 1 frase, sem solução.
- [x] Causa-raiz vs sintoma distinguidos.
- [x] Subproblemas MECE.
- [x] Core vs periférico classificado.
- [x] Todos os atores com papel claro.
- [x] Fluxo principal em 3–7 passos.
- [x] Entidades sem tipos técnicos (obs: "Artefato", "FrontMatter", "Link" são entidades de domínio, não tipos Python).
- [x] Regras sensíveis (§5.4) marcadas.
- [x] ≥2 caminhos em Analyze com matriz de trade-offs.
- [x] Pre-mortem feito para cada caminho.
- [x] Decide com descartes explícitos por alternativa.
- [x] Riscos aceitos marcados `[RISCO ASSUMIDO]`.
- [x] `decision_log.md` com ≥1 `D-NNN` assinada.
- [x] Contrato para o Briefing preenchido.
- [x] Humano validou — **pendente para fim de W1 track A.**
