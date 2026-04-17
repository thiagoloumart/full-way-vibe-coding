# Full Way Vibe Coding

Sistema operacional para transformar uma **ideia inicial** em **entrega mergada** — seja software (D1), processo empresarial (D2) ou playbook/framework de decisão (D3).

Estende o protocolo do `Manual Operacional De Vibe Coding.pdf` em três direções:
- **Dual-domain:** mesma espinha dorsal (15 fases) adaptada a 3 domínios + híbrido.
- **Constituição bicamada:** invariantes (Camada 1) × escolhas (Camada 2), rastreadas via ADR global.
- **Fase 12 Retrospective:** captura de aprendizado pós-merge alimenta ADRs e revisões de Constituição.

> **Versão 1.1 — M1 (hoje):** núcleo dual-domain + Fase 12 + bicamada + agentes-e-automacoes + front-matter dos templates + governança doc-only.
> **Versão 1.2 — M2 (próximo):** harness Python funcional (linter + gate + smoke + GitHub Action) + exemplos canônicos + 6 protocolos restantes + templates concretos de D2 e D3.

---

## Como escolher seu ponto de entrada

| Você quer… | Abra |
|---|---|
| **Usar a skill agora** (IA conduzindo seu ciclo) | [`SKILL.md`](SKILL.md) — router com 2 perguntas |
| **Entender a filosofia e as regras inegociáveis** | [`filosofia.md`](filosofia.md) |
| **Ler o raciocínio que justifica a arquitetura** | [`00_ANALISE_ESTRATEGICA.md`](00_ANALISE_ESTRATEGICA.md) |
| **Estender para um domínio específico** | [`domains/`](domains/) |
| **Executar uma fase específica** | [`fases/`](fases/) |
| **Pegar um template pronto** | [`templates/`](templates/) |
| **Conferir um checklist de qualidade** | [`checklists/`](checklists/) |
| **Regras transversais (travamento, antialucinação, brownfield, …)** | [`protocolos/`](protocolos/) |
| **Governança (ADRs, versionamento, métricas)** | [`governanca/`](governanca/) |
| **Enforcement mecânico (doc de rollout agora; Python em M2)** | [`harness/`](harness/) |

---

## Estrutura

```
Skill Vibe Coding Completa/
├── README.md                       ← você está aqui
├── SKILL.md                        router dual-domain (2 perguntas)
├── filosofia.md                    manifesto (princípios, regra §5.4 ampliada, marcadores)
├── 00_ANALISE_ESTRATEGICA.md       análise do Manual + mapa mestre
│
├── fases/                          ciclo passo a passo (15 fases)
│   ├── 00_RECEPCAO.md
│   ├── 00_5_BMAD.md                breakdown/model/analyze/decide
│   ├── 01_BRIEFING.md
│   ├── 02_SPEC.md                  (D1: spec · D2: mapa-to-be · D3: critérios+árvore)
│   ├── 03_CLARIFY.md
│   ├── 03_5_CONSTITUICAO.md        bicamada (invariantes × escolhas)
│   ├── 04_PLAN.md                  (D1: plano técnico · D2/D3: plano de adoção)
│   ├── 05_TASKS.md
│   ├── 06_ANALYZE.md               gate cruzado
│   ├── 07_IMPLEMENT.md             (D1: código · D2: piloto · D3: decisões reais)
│   ├── 08_TEST.md                  (D1: suíte · D2: auditoria · D3: validação par)
│   ├── 09_QUICKSTART.md            (D1: quickstart · D2: runbook · D3: guia de uso)
│   ├── 10_REVIEW.md
│   ├── 11_MERGE.md                 (D1: merge · D2: go-live · D3: publicação)
│   └── 12_RETROSPECTIVE.md         captura de aprendizado, propostas de ADR
│
├── templates/                      artefatos prontos (YAML front-matter em todos)
│   ├── bmad.md · decision_log.md
│   ├── briefing.md · spec.md · clarify.md
│   ├── constituicao.md             bicamada com marcadores HTML
│   ├── plano.md · tasks.md · analise.md
│   ├── quickstart.md · review.md
│   ├── retrospective.md            (novo em v1.1)
│   └── adr.md                      (novo em v1.1)
│
├── checklists/                     gates de qualidade por fase
│   ├── qualidade-bmad.md · qualidade-briefing.md
│   ├── qualidade-spec.md · qualidade-plano.md
│   ├── pre-implementacao.md · pre-merge.md · mvp.md
│
├── protocolos/                     regras transversais de conduta
│   ├── travamento.md · antialucinacao.md · decisao-mvp.md
│   ├── brownfield.md · erros-e-retry.md · perguntas-padrao.md
│   └── agentes-e-automacoes.md     (novo em v1.1 — Manual §29 ampliado)
│
├── domains/                        adaptadores por domínio (novo em v1.1)
│   ├── software.md                 D1 — materialização fase-a-fase
│   ├── processo.md                 D2 — processo empresarial
│   ├── playbook.md                 D3 — framework de decisão
│   └── hibrido.md                  regra de fusão vs duplicação
│
├── governanca/                     ADR + versionamento + métricas (novo em v1.1)
│   ├── adr-global.md
│   ├── versioning.md
│   └── metricas.md
│
├── harness/                        enforcement mecânico
│   ├── README.md                   v1.1 = documentação; v1.2 = Python funcional
│   ├── rollout.md                  plano em 3 estágios (warning → bloqueante)
│   ├── _audit/                     before.tree, inventory, progress, delta, handoff
│   ├── schemas/                    (M2: YAML espelhando checklists/)
│   └── scripts/                    (M2: lint_artefato.py, gate_fase.py, smoke_test.py, …)
│
├── examples/                       (M2: exemplos canônicos)
│   ├── canonical-software/
│   └── canonical-processo/
│
└── integrations/                   (M2: claude-code, cursor, mcp, spec-kit)
```

---

## Fluxo resumido — 15 fases

```
📥 ideia bruta
   │
   ▼
[Fase 0]    Recepção + quebra em módulos
[Fase 0.5]  BMAD ────────→ bmad.md + decision_log.md
[Fase 1]    Briefing ────→ briefing.md (+ extensões D2/D3)
[Fase 2]    Spec/To-be/Critérios → spec.md | mapa-to-be.md | criterios.md+arvore
[Fase 3]    Clarify ─────→ clarify.md
[Fase 3.5]  Constituição → constitution.md (Camada 1 + Camada 2)
[Fase 4]    Plan ────────→ plan.md | plano-adocao.md
[Fase 5]    Tasks ───────→ tasks.md
[Fase 6]    Analyze (GATE) → analyze.md
[Fase 7]    Implement ───→ código (D1) | piloto (D2) | decisões reais (D3)
[Fase 8]    Test ────────→ suíte verde | auditoria | validação par
[Fase 9]    Quickstart ──→ quickstart.md | runbook.md | guia-uso.md
[Fase 10]   Review ──────→ review.md
[Fase 11]   Merge/Go-live/Publicação → master atualizada | processo rodando | playbook oficial
[Fase 12]   Retrospective → retrospective.md + propostas de ADR
   │
   ▼
🔁 próximo módulo
```

Cada fase tem: entradas, saídas, perguntas-padrão, riscos, gate de avanço, invalidação e sinal de travamento. Ver [`fases/`](fases/).

---

## Princípios inegociáveis (resumo)

Texto completo em [`filosofia.md`](filosofia.md). Resumo:

- Nunca começar pelo código / pela execução / pela publicação.
- Toda entrega passa por **BMAD (Fase 0.5)** antes do briefing.
- Toda spec/to-be/critérios passa por clarificação.
- Toda implementação respeita a **Constituição** — Camada 1 inviolável no ciclo.
- Toda implementação gera ou mantém testes (D1) / auditoria (D2) / validação por par (D3).
- Toda entrega é revisada antes do merge/go-live/publicação.
- **Regra §5.4 ampliada:** regra sensível (D1: cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria; D2: alçada, escalação, compliance; D3: princípios bloqueantes) **nunca** é decidida pela IA.
- Todo ciclo termina em **Fase 12 Retrospective** com decisões revisitadas.

---

## Comportamento esperado da skill

- Uma pergunta por vez nas fases conversacionais.
- 3–5 caminhos sugeridos quando a pergunta aceita opções.
- Marca explicitamente: `[INFERÊNCIA]` / `[NEEDS CLARIFICATION]` / `[DECISÃO HUMANA]` / `[RISCO ASSUMIDO]`.
- Nunca avança sem cumprir o gate da fase atual.
- Nunca decide regra sensível em silêncio.
- Trava formalmente quando falta informação materialmente impeditiva.

---

## Limitações conhecidas

- **Qualidade do input humano é o teto de qualidade da saída** (Manual §30).
- **Não substitui conhecimento de domínio** em produtos regulados — humano traz o especialista.
- **Para em Merge/Go-live/Publicação**: infra, deploy, rollout técnico ficam fora.
- **v1.1 (M1) tem D2 e D3 com contrato definido mas templates concretos em M2**. A skill avisa quando um template referenciado está em estado "contrato-only".
- **Harness mecânico (linter + CI) chega em v1.2 (M2)**. Em v1.1, a conformidade é por convenção, não enforcement.

---

## Riscos conhecidos (com mitigação embutida)

| Risco | Mitigação |
|---|---|
| Humano pula briefing | Router recusa e inicia Fase 0/0.5 |
| IA improvisa regra de negócio | `filosofia.md §7` + `protocolos/travamento.md` + `protocolos/antialucinacao.md` |
| Feature grande demais | Fase 0 quebra em módulos; Fase 5 força fases pequenas |
| Spec e plano divergem em silêncio | Fase 6 (análise cruzada) |
| Teste desativado para "passar" | Checklist de pré-merge detecta; M2 linter bloqueia |
| Brownfield duplicado | `protocolos/brownfield.md` obriga leitura antes |
| Constituição fraca/inexistente | Fase 3.5 bicamada exige v0 e validação |
| Decisão estratégica silenciosamente revertida | Matriz Spec × Decision Log em Fase 6 |
| Automação sem rastro | `protocolos/agentes-e-automacoes.md` — 9 campos obrigatórios |
| Aprendizado do ciclo perdido | Fase 12 (Retrospective) formaliza |

---

## Repositório

- **Remote:** https://github.com/thiagoloumart/full-way-vibe-coding
- **Instalação como skill do Claude Code:** `~/.claude/skills/full-way-vibe-coding/` (sync automática ao final de cada milestone).
- **Licença:** a ser definida (proposta: MIT).

---

## Fechamento (Manual §31, nos 3 domínios)

> Não desenvolver por impulso. Não mudar processo por impulso. Não decidir por impulso.
> **Desenvolver por protocolo.**
> A IA não substitui clareza. Ela multiplica clareza.
