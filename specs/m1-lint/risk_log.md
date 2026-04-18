---
artefato: risk_log
fase: null
dominio: [software]
schema_version: 1
requer:
  - "Legenda"
  - "Riscos ativos"
  - "Riscos mitigados"
  - "Drifts detectados no dogfood"
---

# Risk Log — `lint_artefato.py` M1

**Módulo:** `lint_artefato.py`
**Data de abertura:** 2026-04-18
**Última atualização:** 2026-04-18 (pós-smoke F3)
**Status:** Em andamento

Registro vivo de riscos técnicos, drifts, inferências e premissas em aberto durante o ciclo. Atualizado a cada sessão. Complementa (não substitui) `decision_log.md` (decisões estratégicas com alternativas) e `analyze.md` (gate pré-código). Cada risco tem ID `R-NNN` estável — nunca reutilizado.

## Legenda

- **Status:** 🟠 ativo · 🟢 mitigado · 🔴 materializou · ⚪ descartado
- **Severidade:** 🟢 baixa · 🟡 média · 🔴 alta
- **Origem:** BMAD / Analyze / Dogfood / Implementação

---

## Riscos ativos

### R-001 · Regex de heading em code block aninhado mal fechado
- **Status:** 🟠 ativo
- **Severidade:** 🟡 média
- **Origem:** BMAD D-001 (Analyze §3.4 pre-mortem Caminho B)
- **Descrição:** `strip_code_blocks` funciona bem em Markdown canônico (abertura `\`\`\`` seguida de fechamento `\`\`\``). Se um artefato tem bloco mal fechado (abertura sem fechamento até EOF), o resto do documento é tratado como dentro de bloco — headings reais deixariam de contar.
- **Mitigação M1:** Aceito em D-001 como [RISCO ASSUMIDO]. Templates atuais não têm esse padrão; smoke pós-F2 não detectou caso real.
- **Mitigação M2+:** Se W2 dogfood expor caso real em exemplo canônico, abrir ADR para migrar `strip_code_blocks` para AST Markdown (markdown-it-py).
- **Critério de invalidação:** ≥1 artefato real com bloco mal fechado causando falso negativo em smoke.
- **Watcher:** próximo smoke pós-F3 + smoke de W2.

### R-002 · Âncoras de link (`#section`) não validadas
- **Status:** 🟠 ativo
- **Severidade:** 🟢 baixa
- **Origem:** BMAD D-003, Spec FR-008
- **Descrição:** `[texto](arquivo.md#secao)` só valida existência de `arquivo.md`. Se a âncora não existe dentro do arquivo, lint passa.
- **Mitigação M1:** Out of scope declarado em `spec.md §Out of Scope` e `briefing.md §9`.
- **Mitigação M2+:** Adicionar scan de headings do arquivo-alvo e verificar slug contra âncora.
- **Critério de invalidação:** se W2/W3 expuser falha de integração devido a link com âncora quebrada em artefato canônico.
- **Watcher:** retrospective W3.

### R-003 · Arquivos > 10 MB não tratados
- **Status:** 🟠 ativo
- **Severidade:** 🟢 baixa
- **Origem:** Spec Edge Cases §9
- **Descrição:** Artefato muito grande não tem warning `ARQUIVO_MUITO_GRANDE`; M1 simplesmente processa (pode ficar lento).
- **Mitigação M1:** Nenhum artefato atual da skill chega perto de 10 MB (maior é `spec.md` com ~8 KB).
- **Mitigação M2+:** Task dedicada em M2 para warning + possível short-circuit.
- **Critério de invalidação:** nenhum artefato da skill chegar perto. Se surgir, M2.

### R-004 · `fase: null` vs `fase: int` — inferência implícita
- **Status:** 🟠 ativo (inferência registrada)
- **Severidade:** 🟢 baixa
- **Origem:** Implementação T-004
- **Descrição:** `[INFERÊNCIA]` durante T-004 ajustei `REQUIRED_FIELDS["fase"]` para aceitar `(int, float, type(None))` após descobrir que `templates/adr.md` e `templates/risk_log.md` (este) usam `fase: null`. Não estava explícito em D-NNN nem C-NNN. `float` incluído por segurança para casos de YAML inferindo número como float.
- **Mitigação M1:** Aceite in-place; testado com `test_validate_fields_fase_null_allowed`.
- **Mitigação M2+:** Formalizar via ADR-004 ou via schema custom por artefato quando `harness/schemas/*.yaml` entrar.
- **Critério de invalidação:** nenhum — inferência conservadora. Só precisa formalização em M2.

### R-005 · Link URL-encoded ou com espaço escapado
- **Status:** 🟠 ativo (coberto por task mas não por teste dedicado)
- **Severidade:** 🟡 média
- **Origem:** Analyze §6 tabela edge cases
- **Descrição:** Link `[x](./arquivo%20com%20espaço.md)` — `pathlib.Path` decodifica? M1 não testou explicitamente.
- **Mitigação M1:** T-014 inclui `test_cli_link_url_encoded` como mini-teste adicional. Edge case está em DoD.
- **Mitigação F3:** Se `extract_links` em T-010 tratar sem decodificar, precisa `urllib.parse.unquote`. Registrar decisão em T-010.
- **Critério de invalidação:** falso negativo em smoke pós-F3.
- **Watcher:** T-010 + T-014.

### R-006 · Link que sobe acima da raiz do repo
- **Status:** 🟠 ativo (coberto por task)
- **Severidade:** 🟢 baixa
- **Origem:** Analyze §6 tabela edge cases
- **Descrição:** Link `[x](../../../../../etc/passwd)` — M1 resolve e valida existência. Aceita se existe. Não é papel deste lint defender contra path traversal em docs.
- **Mitigação M1:** T-014 inclui `test_cli_link_above_repo_root`.
- **Critério de invalidação:** algum caso de segurança real justificar; por enquanto é corretude sem preocupação de segurança.

### R-007 · `lint_artefato.py` passar de 350 linhas
- **Status:** 🟠 ativo (monitorado)
- **Severidade:** 🟢 baixa
- **Origem:** Analyze problema #2 + plan.md §6
- **Descrição:** Decisão "1 arquivo único" com critério quantitativo "350 LoC → ADR-004 + refactor em módulos `harness/scripts/lint/{parser,validator,report,cli}.py`".
- **Estado atual (após F2):** ~280 linhas de código (excluindo docstrings e blank lines por inspeção visual, não via `cloc`). Margem segura.
- **Mitigação:** Rodar `cloc` no fim de F3. Se passar, abrir ADR-004 e refactorar antes do merge.
- **Critério de invalidação:** ultrapassar 350 LoC.
- **Watcher:** fim de F3, obrigatoriamente.

### R-008 · Dogfood pode expor novo padrão inesperado em F3
- **Status:** 🔴 materializou → 🟢 mitigado em commit a seguir (2026-04-18)
- **Severidade:** 🟡 média
- **Origem:** Retrospecto do smoke F2
- **Descrição:** Smoke F2 detectou 1 drift real (constituicao requer). Smoke F3 valida links relativos em TODOS os artefatos lintáveis — probabilidade alta de encontrar link quebrado ou padrão exótico (URL-encoded, âncora Unicode, link sublinhado em UI, etc.).
- **Materialização:** ocorreu exatamente no smoke pós-F3 (2026-04-18). 26 falsos positivos em 6 artefatos (bmad, briefing, decision_log, risk_log, spec, tasks de `specs/m1-lint/`). Ver drift **D-W1A-002** abaixo.
- **Mitigação:** corrigido no mesmo dia (`strip_inline_code` adicionado ao lint). 0 regressões; 77/77 testes verdes; re-smoke 26/26 verde.
- **Aprendizado:** a classe de falso positivo era padrão único (links em backticks inline). Mitigação única cobriu todos os casos — evidência de que o drift era estrutural, não artefato por artefato.

---

## Riscos mitigados

_Os registros aqui viraram 🟢 após mitigação. Mantidos para auditoria._

_Nenhum ainda — W1 track A está em F3, todos os riscos acima estão em observação._

---

## Drifts detectados no dogfood

Drifts reais expostos pelo próprio lint sendo construído.

### D-W1A-001 · `templates/constituicao.md` com `requer:` inconsistente com headings
- **Status:** 🟢 corrigido em commit `808ad40`
- **Severidade:** 🟡 média (seria alta se tivéssemos mais módulos herdando do template)
- **Detectado em:** 2026-04-18, smoke pós-F2
- **Descrição:** `requer:` declarava `"1. Arquitetura (Camada 1)"` e variantes com anotação `(Camada 1)`/`(Camada 2)` — mas os headings reais não tinham essa anotação. 6 itens de `requer:` sem heading correspondente.
- **Causa-raiz:** autor do template (na v1.1) tratou o item de `requer:` como anotação semântica em vez de literal do heading esperado.
- **Correção:** simplificar `requer:` para prefixo comum (ex: `"1. Arquitetura"`) que via `_heading_matches_requer` cobre as variações.
- **Propagação:** `specs/m1-lint/constitution.md` herdou via W1 track A — corrigido no mesmo commit.
- **Aprendizado para retrospective W1:** contratos declarativos em `requer:` precisam ser auditados junto com headings; M2 schema pode exigir "cada item de `requer:` tem exatamente 1 heading que bate via prefix".

### D-W1A-002 · Falso positivo LINK_QUEBRADO em exemplos inline de documentação (drift do próprio lint)
- **Status:** 🟢 corrigido no commit que registra este drift
- **Severidade:** 🟡 média — causava 26 falsos positivos em 6 artefatos de `specs/m1-lint/`
- **Detectado em:** 2026-04-18, smoke pós-F3 (exatamente o cenário previsto em R-008)
- **Descrição:** `extract_links` não diferencia links Markdown reais de links em backticks inline (`` `[x](./foo.md)` ``). Artefatos que documentam sintaxe de link (spec.md, bmad.md, tasks.md, risk_log.md próprio) disparavam LINK_QUEBRADO para cada exemplo explicativo.
- **Causa-raiz:** `LINK_RE` regex matcha `[texto](target)` independentemente de contexto. `strip_code_blocks` só cobre **fenced** code (```...```), não inline code.
- **Correção:** adicionar `strip_inline_code(text)` que remove conteúdo entre pares de backticks na mesma linha, preservando quebras de linha. Aplicar **antes** de `extract_links` (mas não antes de `extract_headings`, pois headings raramente aparecem em inline code — decisão baseada em análise dos templates atuais).
- **Decisão técnica tomada durante a correção:** multi-backticks (``` `` ... `` ```) não são tratados em M1 — padrão raro em Markdown técnico; se surgir, abrir ADR.
- **Aprendizado:** drift do próprio lint é esperado no dogfood e é **valor** — expôs lacuna na spec original (FR-010 fala de fenced code blocks mas não de inline code). Atualizar FR-010 na spec para cobrir ambos os casos (item de retrospective W1).
- **Validação pós-correção:** 77/77 pytest; smoke 26/26 lintáveis verdes; zero regressão em testes pré-existentes.

---

## Próximas ações de watcher

- [ ] **Fim de F3:** rodar `cloc` em `lint_artefato.py` e decidir R-007 (refactor ou não).
- [ ] **Fim de F3:** smoke completo em templates/, specs/m1-lint/, governanca/adrs/ verificando links — atualizar R-008 com descobertas.
- [ ] **Fim de W1 track A:** registrar aprendizados de D-W1A-001 em `retrospective.md` + considerar ADR para regra de validação de `requer:` por prefix.
- [ ] **W2:** smoke em exemplo canônico — pode expor R-001 (code block aninhado) se não surgir antes.

---

**Gate de fechamento deste log:**
- [ ] Todo risco 🟠 tem mitigação planejada OU critério de invalidação explícito.
- [ ] Todo drift 🟢 tem link para commit que corrigiu.
- [ ] Retrospective do módulo referenciou este arquivo.
