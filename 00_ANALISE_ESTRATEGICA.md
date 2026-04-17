# 00 — Análise Estratégica

Documento de fundamentação da skill. É o raciocínio que justifica cada decisão da arquitetura. Deve ser lido antes de qualquer execução, e revisitado quando houver dúvida sobre o "porquê" de uma etapa.

Fonte primária: `Manual Operacional De Vibe Coding.pdf` (31 seções).
Fontes auxiliares: `PROMPT_BRIEFING.md`, `PROMPT_SPEC.md`, `spec-template.md`.

---

## 1. Leitura estratégica do documento principal

O documento principal **não é um guia de estilo** nem um tutorial. Ele é um **protocolo operacional** — um contrato de conduta entre humano e IA para desenvolvimento de software. Três camadas emergem da leitura:

### Camada filosófica (seções 1, 25, 26, 30, 31)
- "Desenvolvimento com IA não é pedir código. É pensar certo, especificar certo, validar certo e só depois implementar."
- A IA multiplica clareza, não substitui clareza.
- Qualidade do software é função da qualidade da especificação.
- "Melhor errar antes do código do que depois."

### Camada de papéis (seções 3, 4)
- **Humano:** dono do problema, da regra de negócio, da decisão final; responsável pela qualidade do input e pela validação da saída.
- **IA:** analista de requisitos, arquiteto técnico, executor disciplinado, revisor técnico, gerador de testes. **Nunca autora livre do sistema.**
- Quando há ambiguidade, a IA aponta, propõe opções, recomenda — mas espera decisão humana quando necessário.

### Camada operacional (seções 2, 5–22, 27–29)
Um ciclo obrigatório de **10 etapas** para qualquer feature/fix/automação/tela/fluxo/agente/integração:

1. Briefing → 2. Especificação → 3. Clarificação → 4. Planejamento → 5. Tasks → 6. Análise → 7. Implementação → 8. Testes → 9. Revisão → 10. Merge.

Pular qualquer etapa implica aumento assumido de **risco, ambiguidade e retrabalho**.

A seção 28 oferece um **protocolo de execução expandido em 13 etapas** (inclui recepção de ideia, quebra em módulos, escolha de módulo, repetição). Esse protocolo estendido é o mapa mestre operacional desta skill.

---

## 2. Premissas extraídas

### Premissas explícitas
1. **Nunca começar pelo código.** Toda feature nasce de um briefing.
2. **Toda spec passa por clarificação** antes de virar código.
3. **Toda implementação respeita a Constituição do Projeto.**
4. **Toda implementação gera ou mantém testes.** Se teste falha, feature não está pronta.
5. **Toda implementação é revisada minimamente antes do merge.**
6. **Toda nova spec nasce da branch master atualizada.**
7. **Cada spec trabalha em branch própria.**
8. **Implementação por fase** é preferível a implementação total.
9. **MVP prioriza fluxo central, auth, regra crítica, cobrança (se houver), histórico essencial e retorno funcional.** Não prioriza dark mode, polimento, features periféricas.
10. **A IA não inventa regra de negócio silenciosamente.** Aponta, propõe, recomenda, espera.

### Premissas implícitas
11. Existe uma **Constituição do projeto** (seção 7) como camada mais importante — arquitetura, padrões, stack, regras de segurança, limites do MVP, convenções. Se não existir, deve ser construída ou inferida explicitamente antes de decisões estruturais.
12. A clarificação não é uma única rodada; é um **loop até eliminar ambiguidade, omissão, contradição e falsa obviedade**.
13. A Análise antes do código (seção 13) funciona como **gate** cruzando Constituição × Spec × Plano × Tasks. Problemas achados nessa fase são **baratos**; os mesmos problemas achados após código são caros.
14. Em projetos **brownfield** (código existente), a IA deve primeiro **ler** o que já existe antes de propor estrutura nova.
15. Modelos diferentes servem a fases diferentes. O critério de escolha é **resultado**, não preferência.
16. Para **CRM / agentes / SaaS** (seção 29), confiabilidade operacional, rastreabilidade e permissão por papel são prioridade máxima — toda automação deve ter gatilho, contexto, decisão, ação, fallback, log, critério de sucesso, risco de falso positivo.
17. **Raciocínio estrutural (BMAD) precede requisitos de negócio.** Sem Breakdown/Model/Analyze/Decide formalizados na Fase 0.5, briefing e spec herdam pressupostos implícitos: problema confundido com sintoma, atores descobertos tardiamente, primeira ideia virando única ideia, decisões estratégicas sem registro auditável.

### Restrições
- Não fazer várias features grandes ao mesmo tempo sem modularização.
- Não subir para master sem revisão mínima e testes.
- Não confiar no código sem testar.
- UI bonita não é prioridade do MVP.

### Critérios de qualidade (seção 25)
Implementação boa é a que: respeita escopo, segue Constituição, tem spec clara, foi analisada, foi testada, pode ser revisada, pode ser continuada sem caos.

---

## 3. Resultado final desejado

A skill produzida deve ser capaz de receber uma **ideia inicial crua** (ex.: "quero um sistema que ajude clínicas a confirmar consultas") e conduzir, de forma disciplinada, até um **software funcional, especificado, testado, revisado e mergado** — passando por todos os artefatos intermediários (briefing, spec, clarificação resolvida, plano, tasks, análise, código, testes, quickstart, review) com rastreabilidade lógica completa.

Três eixos compõem esse resultado:
- **Resultado de produto:** software que resolve o problema original do usuário.
- **Resultado de processo:** histórico auditável de decisões (briefing → spec → análise → merge).
- **Resultado de conduta:** a IA nunca improvisou regra de negócio, nunca pulou gate, nunca mergiu sem revisão.

---

## 4. Lacunas e pontos de inferência

O documento principal **não especifica**:

| Lacuna | Inferência adotada pela skill | Justificativa |
|---|---|---|
| Formato concreto do briefing | Usar estrutura do `PROMPT_BRIEFING.md` (Visão de Negócio → Loop de Requisitos → Grande Resumo) | Auxiliar explícito, alinhado ao foco "dor/uso/fluxo/valor" da seção 8 |
| Formato concreto da spec | Usar `spec-template.md` (User Scenarios, Requirements, Key Entities, Success Criteria) | Auxiliar explícito, cobre os campos da seção 9 |
| Como conduzir a clarificação | Usar modelo "descoberta guiada com caminhos sugeridos" do `PROMPT_SPEC.md` | Alinha-se à seção 10 e à seção 3 (propor opções, recomendar melhor) |
| Como inicializar a Constituição quando não existir | Gerar rascunho inferido a partir de briefing + stack declarada, marcar como "Constituição v0", exigir validação humana antes de tratar como canônica | Seção 7 é obrigatória mas o Manual não trata do caso "projeto zero" |
| Modelo concreto para cada etapa do ciclo | Cada fase tem um arquivo dedicado em `fases/` com: entradas, saídas, perguntas-padrão, riscos, gate de avanço, como invalidar, sinal de travamento | Seção 4.4 do `promptraiz.md` exige isso |
| Como registrar a trilha de auditoria | Sugerir estrutura de pastas `docs/specs/<feature>/` com bmad.md, decision_log.md, briefing.md, spec.md, clarify.md, plan.md, tasks.md, analyze.md, quickstart.md, review.md | Seção 18 (git/merge) implica, mas não nomeia a estrutura |
| Como garantir **raciocínio estrutural pré-briefing** | Adicionar **Fase 0.5 BMAD** (Breakdown/Model/Analyze/Decide) entre Recepção e Briefing, com templates próprios (`bmad.md`, `decision_log.md`) e checklist de qualidade | O Manual trata de decomposição, análise de alternativas e decisão apenas implicitamente, misturados em fases posteriores; Fase 0.5 consolida esse raciocínio num único ponto antes da spec |
| Como suportar **outros domínios além de software** (D2 processo, D3 playbook) | Adicionar **camada de adaptadores** em `domains/` (software, processo, playbook, hibrido), mantendo a numeração das fases; cada domínio materializa fase-a-fase com artefatos próprios (D2: mapa-as-is, mapa-to-be, SLAs, RACI, KPIs, runbook, script-auditoria; D3: critérios, árvore, anti-padrões, plano-adoção, métrica-eficácia) | O Manual §§ 3–5 é explicitamente agnóstico de domínio ("A IA deve agir como analista/arquiteta/executora"); a Fase 7 "implementar" e a Fase 8 "testar" são traduzidas como "executar piloto"/"validar operacionalmente" em D2 e "decisões reais"/"validação por par sênior" em D3. O que muda é o artefato, não o método. |
| Como **capturar aprendizado pós-merge** | Adicionar **Fase 12 Retrospective** com template próprio (`retrospective.md`), decisões revisitadas, propostas de ADR global | O Manual termina em Fase 11 (Merge) sem loop formal de aprendizado; Fase 12 fecha o ciclo e alimenta decisões futuras (`decision_log.md` fica vivo entre ciclos) |
| Como **separar invariantes de escolhas na Constituição** | Reescrever Fase 3.5 em **duas camadas** com marcadores `<!-- CAMADA_1/2_BEGIN/END -->` no template; Camada 1 = invariantes (ADR major); Camada 2 = escolhas mutáveis (ADR minor). ADRs globais em `governanca/adr-global.md` | Manual §7 trata a constituição como um único bloco; na prática, mistura-se arquitetura (inviolável) com stack (mutável). A bicamada permite que stack mude sem riscar princípios |
| Como **ampliar Regra §5.4 para outros domínios** | `filosofia.md §7` traz 3 listas canônicas por domínio (D1 software, D2 processo, D3 playbook) | Manual §5.4 é software-cêntrico (cobrança, permissão, estorno…); em D2 e D3, "sensível" significa alçada, compliance, princípio bloqueante — mesmo espírito, linguagem diferente |
| Como **enforcer checklists e gates mecanicamente** | Documentar plano em `harness/README.md` e `harness/rollout.md`; linter Python + schemas YAML + GitHub Action entram em M2 | Gates em markdown puro dependem de disciplina humana; o harness adiciona segurança sem sacrificar legibilidade |

Inferências explicitamente sinalizadas com `[INFERÊNCIA]` nos artefatos.

---

## 5. Mapa mestre do processo

```
┌────────────────────────────────────────────────────────────────┐
│                    ENTRADA: ideia crua                          │
└────────────────────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 0 — RECEPÇÃO E QUEBRA EM MÓDULOS           │
    │ Inputs: ideia, contexto, stack, prazo           │
    │ Output: lista de módulos + hipóteses iniciais   │
    │ Gate: humano escolhe módulo inicial (MVP)       │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 0.5 — BMAD                                  │
    │ Breakdown / Model / Analyze / Decide            │
    │ Raciocínio estrutural pré-spec                  │
    │ Output: bmad.md + decision_log.md               │
    │ Gate: decisão estratégica assinada por humano   │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 1 — BRIEFING                                │
    │ Herda contrato do BMAD                          │
    │ Foco: dor, uso, fluxo, valor (detalhados)       │
    │ Output: briefing.md                             │
    │ Gate: humano valida briefing                    │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 2 — ESPECIFICAÇÃO                           │
    │ User stories, edge cases, FR, key entities, SC  │
    │ Output: spec.md                                 │
    │ Gate: spec estável                              │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 3 — CLARIFICAÇÃO                            │
    │ Elimina ambiguidade, omissão, contradição        │
    │ Output: clarify.md (decisões registradas)        │
    │ Gate: zero [NEEDS CLARIFICATION] residuais       │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 3.5 — CONSTITUIÇÃO (criar/atualizar)        │
    │ Arquitetura, stack, padrões, segurança, limites │
    │ Output: constitution.md (ou confirmação da v)   │
    │ Gate: humano aprova constituição                 │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 4 — PLANEJAMENTO TÉCNICO                    │
    │ Ordem, dependências, arquivos, contratos, dados │
    │ Output: plan.md                                 │
    │ Gate: plano coerente com constituição            │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 5 — TASKS                                   │
    │ Fases pequenas com dependências claras          │
    │ Output: tasks.md                                │
    │ Gate: cada task é executável e testável          │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 6 — ANÁLISE CRUZADA (GATE CRÍTICO)          │
    │ Constituição × Spec × Plano × Tasks             │
    │ Detecta: inconsistência, duplicação, ambiguidade │
    │ Output: analyze.md                              │
    │ Gate: análise limpa OU risco assumido por escrito│
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 7 — IMPLEMENTAÇÃO (por fase, não total)     │
    │ Respeitar constituição, reaproveitar padrões     │
    │ Durante: corrigir erros intermediários           │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 8 — TESTES                                  │
    │ Sucesso, erro, edge, rollback, regressão        │
    │ Gate: todos passam                              │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 9 — QUICKSTART / TESTE MANUAL               │
    │ Roteiro: comandos, telas, ações, resultados     │
    │ Output: quickstart.md                           │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 10 — REVIEW                                 │
    │ Arquivos, migrations, testes, rotas, policies   │
    │ Output: review.md                               │
    │ Gate: humano aprova                              │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 11 — MERGE / GO-LIVE / PUBLICAÇÃO           │
    │ Commit documental → commit implementação → merge│
    │ (D1: merge; D2: go-live + comunicação;           │
    │  D3: publicação oficial v1.0)                    │
    │ Gate: master atualizada / processo rodando /     │
    │       playbook publicado                         │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
    ┌─────────────────────────────────────────────────┐
    │ FASE 12 — RETROSPECTIVE                          │
    │ Revisita decisões (D-NNN) com veredicto          │
    │ KPI previsto vs observado                        │
    │ Propostas de ADR global → governanca/            │
    │ Propostas de update da Constituição              │
    │ Gate: cada D-NNN com veredicto + aprendizados    │
    │       comunicados ao time                        │
    └─────────────────────────────────────────────────┘
                           │
                           ▼
         ┌────────────────────────────────────┐
         │ REPETIR para o próximo módulo      │
         │ Novas specs sempre da master       │
         └────────────────────────────────────┘
```

**Princípios de passagem entre fases:**
- Nenhuma fase avança se a saída da fase anterior não for válida contra seu checklist.
- Qualquer avanço sem gate cumprido é sinalizado como **risco assumido conscientemente** em `analyze.md`.
- Erros em qualquer fase param o fluxo até origem ser corrigida (não pula; não ignora).

---

## 6. Riscos antecipados

| Risco | Mitigação |
|---|---|
| IA improvisa regra de negócio | Protocolo de travamento obrigatório (§ `protocolos/travamento.md`) |
| Humano empurra "faz um sistema que…" pulando briefing | Skill mestre recusa e inicia Fase 1 |
| Feature grande demais vira implementação total | Fase 0 quebra em módulos; Fase 5 força fases pequenas |
| Spec muda depois do plano, criando inconsistência silenciosa | Fase 6 (análise cruzada) detecta; ciclo volta à spec |
| Testes quebram e time ignora | Regra seção 15: "se teste falha, feature não está pronta" — bloqueia merge |
| Constituição não existe em projeto novo | Fase 3.5 cria v0 e exige validação humana |
| Em projeto brownfield, duplica-se algo existente | `protocolos/brownfield.md` exige leitura prévia |
| Modelo errado para o tipo de tarefa | Seção 22: trocar abordagem/modelo quando problema persiste |
| Spec começa com ângulo de solução implícito | Fase 0.5 BMAD força análise de ≥2 caminhos com trade-offs antes de requirements |
| Decisões estratégicas ficam só na memória do humano | Fase 0.5 produz `decision_log.md` com `D-NNN` auditável (descartes, riscos aceitos, critérios de invalidação) |
| Atores/fluxos descobertos tardiamente dentro da spec | Fase 0.5 Model formaliza atores, fluxo e entidades antes do briefing |

---

## 7. Conclusão da análise

Esta skill é fiel ao documento principal em três sentidos:
1. **Estrutura:** o ciclo de 10 etapas (seção 2) e o protocolo de 13 etapas (seção 28) são a espinha dorsal.
2. **Conduta:** papéis humano/IA, proibições, obrigatoriedades, filosofia operacional são regras inegociáveis incorporadas em cada fase.
3. **Formato:** briefing, spec, clarify absorvem os auxiliares (`PROMPT_BRIEFING.md`, `PROMPT_SPEC.md`, `spec-template.md`) como templates operacionais.

Divergências com o documento principal: **nenhuma identificada**. Onde o principal é silencioso, o auxiliar contribui; onde o principal fala, ele prevalece.
