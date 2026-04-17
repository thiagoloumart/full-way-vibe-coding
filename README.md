# Skill Vibe Coding Completa

Skill operacional para transformar uma **ideia inicial** em **software funcional, especificado, testado, revisado e mergado**, seguindo integralmente o protocolo do `Manual Operacional De Vibe Coding.pdf` e absorvendo os auxiliares `PROMPT_BRIEFING.md`, `PROMPT_SPEC.md` e `spec-template.md`.

---

## Onde começar

1. Leia [00_ANALISE_ESTRATEGICA.md](00_ANALISE_ESTRATEGICA.md) — explica a fundamentação da skill (premissas, mapa mestre, riscos).
2. Leia [SKILL.md](SKILL.md) — a skill mestre: identidade, inputs, fluxo, regras inegociáveis, comportamento.
3. Execute o ciclo fase a fase usando [`fases/`](fases/).

---

## Estrutura

```
Skill Vibe Coding Completa/
├── README.md                     ← você está aqui
├── 00_ANALISE_ESTRATEGICA.md     análise do documento principal + mapa mestre
├── SKILL.md                      skill mestre operacional
│
├── fases/                        ciclo passo a passo
│   ├── 00_RECEPCAO.md            recepção + quebra em módulos
│   ├── 00_5_BMAD.md              breakdown / model / analyze / decide
│   ├── 01_BRIEFING.md            dor, uso, fluxo, valor
│   ├── 02_SPEC.md                user stories, FR, SC, edge cases
│   ├── 03_CLARIFY.md             elimina ambiguidade/omissão/contradição
│   ├── 03_5_CONSTITUICAO.md      arquitetura, stack, padrões
│   ├── 04_PLAN.md                plano técnico
│   ├── 05_TASKS.md               tasks com DoD e dependências
│   ├── 06_ANALYZE.md             gate crítico (matrizes cruzadas)
│   ├── 07_IMPLEMENT.md           implementação por fase
│   ├── 08_TEST.md                testes obrigatórios
│   ├── 09_QUICKSTART.md          roteiro manual de validação
│   ├── 10_REVIEW.md              revisão mínima
│   └── 11_MERGE.md               git, branches, merge
│
├── templates/                    arquivos prontos para copiar e preencher
│   ├── bmad.md
│   ├── decision_log.md
│   ├── briefing.md
│   ├── spec.md
│   ├── clarify.md
│   ├── constituicao.md
│   ├── plano.md
│   ├── tasks.md
│   ├── analise.md
│   ├── quickstart.md
│   └── review.md
│
├── checklists/                   gates de qualidade
│   ├── qualidade-bmad.md
│   ├── qualidade-briefing.md
│   ├── qualidade-spec.md
│   ├── qualidade-plano.md
│   ├── pre-implementacao.md
│   ├── pre-merge.md
│   └── mvp.md
│
└── protocolos/                   regras transversais de conduta
    ├── travamento.md             quando parar e perguntar
    ├── antialucinacao.md         como não inventar
    ├── decisao-mvp.md            lógica de priorização (§23–24)
    ├── brownfield.md             como agir em projeto existente (§19)
    ├── erros-e-retry.md          quando der errado (§21–22)
    └── perguntas-padrao.md       banco de perguntas por fase
```

---

## Fluxo resumido

```
📥 ideia bruta
   │
   ▼
[Fase 0]    Recepção e módulos
[Fase 0.5]  BMAD ────────→ bmad.md + decision_log.md
[Fase 1]    Briefing ────→ briefing.md
[Fase 2]    Spec ────────→ spec.md
[Fase 3]    Clarify ─────→ clarify.md
[Fase 3.5]  Constituição → constitution.md
[Fase 4]    Plan ────────→ plan.md
[Fase 5]    Tasks ───────→ tasks.md
[Fase 6]    Analyze (GATE) → analyze.md
[Fase 7]    Implement ───→ código + migrations
[Fase 8]    Test ────────→ suíte verde
[Fase 9]    Quickstart ──→ quickstart.md
[Fase 10]   Review ──────→ review.md
[Fase 11]   Merge ───────→ master atualizada
   │
   ▼
🔁 próximo módulo
```

Cada fase tem: entradas, saídas, perguntas-padrão, riscos, gate de avanço, condição de invalidação e sinal de travamento.

---

## Princípios inegociáveis (Manual §§1–6, 26)

- Nunca começar pelo código. Toda feature nasce de briefing.
- Toda spec passa por clarificação.
- Toda implementação respeita a Constituição.
- Toda implementação gera ou mantém testes. Se teste falha, feature não está pronta.
- Toda implementação é revisada antes do merge.
- Toda nova spec nasce da master atualizada.
- Cada spec trabalha em branch própria.
- Implementação **por fase**, não total.
- Regra de negócio sensível (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria) **nunca** é decidida pela IA.

---

## Como usar no dia a dia

1. **Receba a ideia** do usuário (humano) com [Fase 0](fases/00_RECEPCAO.md).
2. **Para cada fase**:
   - Abra o arquivo da fase em `fases/`.
   - Use o template correspondente em `templates/`.
   - Siga as perguntas-padrão em [`protocolos/perguntas-padrao.md`](protocolos/perguntas-padrao.md).
   - Antes de avançar, rode o checklist correspondente em `checklists/`.
3. **Em caso de bloqueio**, siga [`protocolos/travamento.md`](protocolos/travamento.md).
4. **Em dúvida sobre priorização**, siga [`protocolos/decisao-mvp.md`](protocolos/decisao-mvp.md).
5. **Se for brownfield**, leia [`protocolos/brownfield.md`](protocolos/brownfield.md) antes da Fase 4.
6. **Se der erro**, siga [`protocolos/erros-e-retry.md`](protocolos/erros-e-retry.md).

---

## Comportamento esperado da skill

- Uma pergunta por vez nas fases conversacionais.
- 3–5 caminhos sugeridos quando a pergunta aceita opções.
- Marcar `[INFERÊNCIA]` / `[NEEDS CLARIFICATION]` / `[DECISÃO HUMANA]` / `[RISCO ASSUMIDO]` sempre que aplicável.
- Nunca avançar sem cumprir o gate da fase atual.
- Nunca decidir regra sensível em silêncio.
- Travar formalmente quando faltar informação materialmente impeditiva.

---

## Limitações e pontos de atenção

- A skill **depende da qualidade do input humano**. Se o briefing é ruim, o output também será (Manual §30).
- A skill **não substitui conhecimento de domínio**: para produtos regulados (saúde, financeiro, governo) o humano precisa trazer as regras do domínio ou um especialista.
- A skill assume que **o humano consegue aprovar decisões** quando perguntado. Se o humano delegar tudo à IA, a skill vai travar nas regras sensíveis.
- A skill **não substitui infra, CI/CD, deploy**: ela para no merge. Infra e release são responsabilidades externas.
- Em ambientes com várias IAs colaborando, a skill recomenda que uma IA **não** altere o artefato de outra sem passar pela Fase 6 (análise cruzada) novamente.

---

## Riscos conhecidos

| Risco | Mitigação embutida |
|---|---|
| Humano pula briefing | Skill recusa e inicia Fase 1 |
| IA improvisa regra de negócio | `protocolos/travamento.md` + `protocolos/antialucinacao.md` |
| Feature grande demais | Fase 0 quebra em módulos; Fase 5 força fases pequenas |
| Spec e plano divergem em silêncio | Fase 6 (análise cruzada) |
| Teste desativado para "passar" | Checklist de pré-merge detecta |
| Brownfield duplicado | `protocolos/brownfield.md` obriga leitura antes |
| Constituição fraca ou inexistente | Fase 3.5 exige criar v0 e validar |

---

## Fechamento (Manual §31)

> Não desenvolver por impulso. Desenvolver por protocolo.
> A IA não substitui clareza. Ela multiplica clareza.
