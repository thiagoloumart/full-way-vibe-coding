# Rollout do Harness — 3 estágios

> Como ativar o enforcement mecânico do harness **sem** rejeitar artefatos válidos. Ordem não-negociável: calibrar antes de bloquear.

## Por que rollout em estágios

O harness é invasivo: adiciona YAML front-matter em 11+ templates, 7+ schemas YAML, 6 scripts Python, GitHub Action. Qualquer bug no linter rejeita artefato conceitualmente válido → produtividade cai + confiança no protocolo cai.

Regra: **exemplos canônicos aprovados manualmente são a fonte de verdade**. Um schema só vira bloqueante depois que o `smoke_test.py` passa em todos os exemplos canônicos.

## Estágios

### E1 — Warning-only
**Duração:** 1–2 ciclos de spec.
**Comportamento:**
- GitHub Action roda com `continue-on-error: true`.
- Linter roda com flag `--warnings-only`.
- Violações aparecem como comentário em PR, não bloqueiam merge.
- Logs publicados em `harness/_audit/e1-runs/<yyyy-mm-dd>-<sha>.log`.

**Objetivo:** calibrar regex e custom hooks contra artefatos reais. Diferenciar bloqueante de ruído.

**Gate para sair de E1:**
- Cada schema passou 3 ciclos de spec sem falso positivo não-explicado.
- Exemplos canônicos (M2: `examples/canonical-software/`, `examples/canonical-processo/`) passam 100% verde.
- Smoke test passa em ambos.

### E2 — Bloqueante parcial
**Duração:** 1 ciclo de spec.
**Comportamento:**
- `lint_artefato.py` **bloqueia** merge quando falha.
- `gate_fase.py` ainda warning.
- `lint_constituicao.py` ainda warning.

**Objetivo:** validar que os schemas individuais estão corretos antes de compor (gate_fase depende de lint_artefato funcionando em cadeia).

**Gate para sair de E2:**
- Zero falsos positivos em `lint_artefato` por 3 semanas.
- `gate_fase` testado em 5+ specs reais, ajustes feitos.
- Equipe treinada em como desbloquear um PR rejeitado (ver "Troubleshooting" abaixo).

### E3 — Bloqueante total
**Duração:** permanente.
**Comportamento:**
- Todos os scripts bloqueiam.
- `required status check` ativo no GitHub.
- Merge só permitido com harness verde ou `[RISCO ASSUMIDO: harness-bypass]` explícito.

**Saída de E3:** não há. E3 é o estado estável.

## Kill switch global

Variável de ambiente no workflow:
```yaml
env:
  HARNESS_ENFORCEMENT: on   # valores: on | warning-only | off
```
- `on`: comportamento do estágio atual.
- `warning-only`: força E1 mesmo estando em E2/E3.
- `off`: linter roda mas não reporta; uso **emergencial apenas** (ex.: incidente de produção exige merge imediato).

Toggle do kill switch exige aprovação do tech lead + nota em `harness/_audit/kill-switch-log.md` com motivo e horário de retorno.

## Bypass de gate individual

Para um artefato legítimo rejeitado por falso positivo:

1. Abrir issue descrevendo o caso.
2. Adicionar no artefato:
   ```
   [RISCO ASSUMIDO: harness-bypass GATE=Gx motivo=... ADR-NNN]
   ```
3. Commit com mensagem `harness-bypass: <motivo>`.
4. CI aceita. Bypass fica rastreável em `harness/_audit/bypass-log.md` (preenchido automaticamente em M2).
5. Ajustar o schema ou o custom hook para evitar falso positivo no próximo ciclo.

## Testes unitários mínimos por script (M2)

### `tests/test_lint_artefato.py`
- Caso feliz: spec canônica passa.
- FR sem formato MUST → G1 falha.
- Spec sem `Edge Cases` → G2 falha.
- `[NEEDS CLARIFICATION]` com status=Estável → G5 falha.
- Front-matter ausente → erro `FRONTMATTER_AUSENTE`.
- Referência D-999 inexistente em decision_log → contra_referencia falha.

### `tests/test_gate_fase.py`
- Diretório vazio → falha na fase 0.5.
- `quickstart.md` ausente em `dominio=software` fase 9 → falha.
- `quickstart.md` ausente em `dominio=processo` fase 9 → passa.
- `fase_alvo < fase atual` → passa (fase alvo já passou).

### `tests/test_extract_invariantes.py`
- FRs batem com fixtures.
- Given/When/Then extraídos em ordem.
- FR com `[INFERÊNCIA]` tem flag setada.
- Spec sem user stories → `stories: []` sem crash.

### `tests/test_smoke.py`
- `examples/canonical-software` passa integralmente.
- `examples/canonical-processo` passa integralmente.
- Mutação (apagar `Edge Cases`) → smoke falha com mensagem G2.

### `tests/test_lint_constituicao.py`
- Diff fora de marcadores → warning, exit 0.
- Diff em CAMADA_1 sem `[major bump]` → exit 1.
- Diff em CAMADA_1 com `[major bump]` + ADR `camada_afetada: 1` → exit 0.
- Diff em CAMADA_2 sem ADR → exit 1.
- Marcadores ausentes → erro `MARCADORES_CAMADA_AUSENTES`.

### `tests/test_gerar_context_pack.py`
- Spec completa → pack inclui constitution + 3 D-NNN mais recentes + review.md.
- Spec na fase 2 → pack inclui spec.md como artefato atual.
- ADR-042 inexistente citada → seção com aviso, sem crash.

## Troubleshooting — como desbloquear um PR

### "lint_artefato falha com FRONTMATTER_AUSENTE"
Template no topo do artefato está sem bloco `---`. Adicionar front-matter conforme referência em `templates/`.

### "Seção obrigatória X ausente"
Adicionar seção. Nome exato do heading vem do schema em `harness/schemas/qualidade-<artefato>.yaml` campo `requer`.

### "FR-042 referencia D-099 que não existe em decision_log.md"
Duas causas possíveis:
- `D-099` deveria ter sido criada no `decision_log.md` → criar entrada.
- `FR-042` deveria referenciar outra decisão → corrigir referência.

### "Status Estável mas ainda há [NEEDS CLARIFICATION]"
Resolver cada um em Fase 3 (Clarify) antes de promover status da spec para Estável.

### "lint_constituicao: mudou Camada 1 sem ADR major"
- Se a mudança era Camada 2 acidentalmente escrita dentro dos marcadores de Camada 1: mover o texto.
- Se era intencional: abrir ADR com `camada_afetada: 1` + adicionar tag `[major bump]` em commit.

### "Smoke test passa local mas falha no CI"
Checar diferença de versão Python ou falta de `pyyaml` no workflow.

## Checklist de go-live do harness (fim de M2)

- [ ] `smoke_test.py` passa em ambos os canônicos com verde total.
- [ ] Testes unitários todos passando.
- [ ] GitHub Action rodou com sucesso em 3+ PRs de teste em E1.
- [ ] Falsos positivos em E1 = 0 por 2 semanas.
- [ ] Promoção para E2 anunciada em CHANGELOG.
- [ ] Equipe treinada no Troubleshooting.
- [ ] Kill switch testado (on → off → on).
- [ ] ADR global no repo da skill registrando a ativação do harness.
