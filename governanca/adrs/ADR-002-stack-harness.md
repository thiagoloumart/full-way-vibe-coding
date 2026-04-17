---
artefato: adr
fase: null
dominio: [any]
schema_version: 1
adr_id: ADR-002
status: Aceita
camada_afetada: 2
data: 2026-04-17
autor: Thiago Loumart
requer:
  - "Contexto"
  - "Decisão"
  - "Alternativas consideradas"
  - "Consequências"
  - "Relação com Constituição"
---

# ADR-002 — Stack do harness: Python 3.11+ com `pyyaml` como única dependência

**Status:** Aceita
**Data:** 2026-04-17
**Autor:** Thiago Loumart
**Camada afetada:** 2 (escolha — afeta Stack §4 da Camada 2)
**Bump de Constituição:** minor (v1.1 → v1.1.1) — materializado quando `constitution.md` do projeto for instanciado

> Decisão tomada **antes** de começar a implementação do harness para desbloquear W1 track A.
> Sem esta decisão, o engineer fica paralisado entre Python/shell/TypeScript.

---

## Contexto

O harness é o conjunto de scripts que executa validação mecânica dos artefatos SDD:
`lint_artefato`, `gate_fase`, `smoke_test`, `gerar_context_pack`, `lint_constituicao`.
Contratos e invariantes estão definidos em [`harness/README.md`](../../harness/README.md)
e estágios de rollout em [`harness/rollout.md`](../../harness/rollout.md) — mas nenhum
desses arquivos formaliza a escolha de linguagem.

`harness/README.md` linha 104 menciona "Python 3.11+" como dependência alvo, sem ADR que
fundamente a decisão. Esta ADR fecha a lacuna antes da implementação começar.

Restrições:
- Os scripts rodam em CI (GitHub Actions) e local.
- Precisam ser simples de contribuir (barrera de entrada baixa).
- Lidam com: ler Markdown, parsear YAML front-matter, validar regex, checar existência
  de arquivos, walkar diretórios.
- Zero lógica de negócio complexa; tudo é texto e regex.

## Decisão

O harness é escrito em **Python 3.11+** com **`pyyaml` como única dependência externa**
obrigatória. Testes em `pytest` (dep dev-only).

Estrutura:
```
harness/
├── scripts/
│   ├── lint_artefato.py
│   ├── gate_fase.py
│   ├── smoke_test.py
│   ├── gerar_context_pack.py
│   ├── lint_constituicao.py
│   └── extract_invariantes.py
├── schemas/
│   └── *.yaml          # schemas espelhando checklists/
└── tests/
    └── test_*.py       # pytest
```

Execução: `python -m harness.scripts.<nome>`. Não empacotar em wheel agora — scripts
são executados direto. Empacotamento em `pyproject.toml` fica como evolução futura
se a adoção externa exigir.

## Alternativas consideradas

1. **Shell script (bash/zsh) + `yq`/`jq`.**
   - Prós: zero instalação em CI Linux; familiar a DevOps.
   - Contras: parsing de Markdown em shell é frágil; testes difíceis; portabilidade
     macOS/Linux sofre; lógica de schema complexa vira regex hell.
   - **Descartada:** manutenção e testes custam caro; contribuidores de fora da bolha
     shell não contribuem.

2. **TypeScript + Node/Bun.**
   - Prós: ecossistema moderno; `vitest` é excelente; Bun é rápido; boa integração com
     MCP e agentes.
   - Contras: 2ª dependência de runtime além do git; `package.json` + `tsconfig.json` +
     lock files adicionam peso visual a um repo que já tem ≥70 arquivos; contribuidores
     Python/shell não conseguem estender sem instalar Node.
   - **Descartada:** peso de infraestrutura para scripts de <500 linhas cada. Se o harness
     crescer e precisar de UI/dashboard web, reavaliar via nova ADR.

3. **Rust (clap + serde_yaml).**
   - Prós: binary único, zero dependências em runtime; rápido; sólido.
   - Contras: curva de aprendizado; ciclo de build em CI atrasa; contribuidores quase
     zero para doc-tooling em Rust; over-engineered para I/O-bound texto.
   - **Descartada:** sobreengenharia para o domínio.

4. **Python 3.11+ com `pyyaml` (escolhida).**
   - Prós: `pyyaml` é de facto padrão YAML em Python; `re` e `pathlib` cobrem 100% do
     trabalho; `pytest` é padrão; setup em CI é trivial (`setup-python` + `pip install pyyaml`);
     contribuidores extensos; macros de `dataclass` tornam schemas limpos.
   - Contras: dependência de runtime (Python) — mitigado por Python estar pré-instalado
     em praticamente todo ambiente de dev e em todo runner GitHub.

## Consequências

- **Positivas:**
  - Setup em CI é 3 linhas (`setup-python`, `pip install pyyaml`, `pytest`).
  - Schemas em YAML legíveis por humanos **e** por máquina sem tradução.
  - `pytest` fornece cobertura e relatórios sem deps adicionais.
  - Contribuições externas viáveis.

- **Negativas / trade-offs:**
  - Requer Python 3.11+ instalado localmente (trivial; resolvido por `pyenv`/`uv`).
  - Tipagem não estrita por padrão (mitigar com `mypy` opcional em CI em M2.5).

- **Migração necessária:** nenhuma — harness ainda não tem scripts implementados.

- **Novas obrigações:**
  - Todo script novo vem com test em `harness/tests/`.
  - Cobertura alvo: ≥80% em `scripts/` (não bloqueante em M2; bloqueante em M3).
  - `pyproject.toml` minimalista entra junto com o primeiro script (define Python ≥3.11
    + `pyyaml` + `pytest` dev).

## Relação com Constituição

- Esta ADR altera a **Camada 2 §4 (Stack do harness)** quando `constitution.md` do próprio
  repo for instanciado em W0/W1.
- Esta ADR **NÃO altera** Camada 1.
- Declaração explícita: **Esta ADR não altera nenhum item de Camada 1.**
- Bump da Constituição: minor (v1.1 → v1.1.1) quando instanciada.

## Relação com outros artefatos

- ADRs relacionadas: ADR-001 (dual-domain) — pressuposto.
- Módulos impactados: `harness/` — futuro, não existe ainda.
- Especificações que citarão: `specs/m1-lint/spec.md` (a ser escrita em W1).

## Plano de reversão

Reversão só se descobrirmos que o volume de scripts/complexidade ultrapassa o que Python
economiza (ex: scripts virando servidor web, CLIs complexas, integração MCP via TS-first).
Nesse caso:

1. ADR de reversão citando volumétrico concreto.
2. Minor bump da Constituição.
3. Manter scripts Python existentes em `harness/scripts-py-legacy/` durante 1 ciclo.

## Aprovação

| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor | Thiago Loumart | 2026-04-17 | ✓ |
| Revisor 1 (self-review) | Thiago Loumart | 2026-04-17 | ✓ |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| 2026-04-17 | Aceita | Criada em W0 da adequação v1.2 antes de qualquer linha de código em `harness/scripts/`. |
