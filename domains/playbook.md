# Domínio D3 — Playbook / framework de decisão

> Este arquivo é **materialização**, não autoridade. As perguntas canônicas vivem em `fases/*.md`; as regras inegociáveis em [`filosofia.md`](../filosofia.md). Este domínio só **anexa** detalhamento de como cada fase se concretiza quando a entrega é um playbook ou framework que outras pessoas vão aplicar para decidir.

⚠️ **Estado v1.1 (M1):** D3 tem **contrato de fases definido**, mas os templates concretos (`briefing-decisao.md`, `criterios.md`, `arvore-decisao.md`, `exemplos-canonicos.md`, `antipadroes.md`, `plano-adocao.md`, `metrica-eficacia.md`) entram em M2.

## Quando usar este domínio
- Resultado final é um **documento operacional de decisão** (playbook, framework, árvore, checklist estruturado) que outras pessoas vão aplicar em casos futuros.
- A entrega é **repetível**: cada vez que a situação X aparece, alguém usa o playbook para decidir.
- Há critérios, pesos, anti-padrões, casos canônicos, limites de aplicação.

## Quando NÃO usar
- Se o objetivo é rodar código → D1.
- Se o objetivo é rodar processo operacional → D2.
- Se é uma decisão one-off sem reuso → não precisa de playbook; apenas BMAD simples resolve.

## Constituição — o que esperar nesta camada
- **Camada 1 (invariantes):**
  - Princípios de decisão inegociáveis (ex.: "segurança sempre pesa mais que custo").
  - Escopo de aplicação inviolável (onde este playbook vale).
  - Fora de escopo (onde este playbook **não** se aplica).
  - Papéis com autoridade de flexibilização (quem pode aprovar uma exceção).
- **Camada 2 (escolhas):**
  - Estrutura da árvore (número de níveis, formato de folha, notação).
  - Pesos dos critérios (sujeitos a calibração).
  - Template de registro de decisão.
  - Periodicidade de revisão do playbook (trimestral, anual).

Alterar Camada 1 exige ADR major + aprovação do comitê de governança do playbook. Alterar Camada 2 exige ADR minor.

## Regras sensíveis típicas em D3 (§5.4 ampliada)
Nenhuma decidida pela IA. Cada uma → C-NNN em `clarify.md`:

- **Princípios bloqueantes** — valores que não podem ser flexibilizados por conveniência (ex.: "peso de segurança nunca < 30%").
- **Escopo de aplicação** — onde o playbook vale; vazamento para outros contextos é bug conceitual.
- **Pesos de critério mínimos** — piso abaixo do qual o critério perde sentido.
- **Autoridade de flexibilização** — quem pode assinar uma exceção a um critério em um caso específico.
- **Prazo de validade** — quando o playbook precisa ser revisado/reemitido.

## Materialização fase-a-fase

| Fase | Artefato D3 | Exemplo ruim | Exemplo bom |
|---|---|---|---|
| **0 Recepção** | escopo do playbook + casos em que se aplicaria | "playbook de gestão" | "Decidir make vs buy para funcionalidade de software < R$500k anuais." |
| **0.5 BMAD** | `bmad.md` — problema de decisão real, atores decisores, caminhos canônicos | 1 critério ("custo") | 5 critérios com pesos + pre-mortem de viés (ancoragem, custo afundado, aversão à perda) |
| **1 Briefing** | `briefing-decisao.md` *(M2)* — quando aplicar; quando NÃO aplicar | "ajudar a decidir" | "Aplica quando custo anual > R$300k. NÃO aplica em segurança crítica (nesses casos ver playbook X)." |
| **2 Critérios + árvore** | `criterios.md` + `arvore-decisao.md` *(M2)* — critérios ponderados, árvore com ramos e folhas | critérios soltos | 5 critérios com pesos somando 100, árvore com 4 folhas (make / buy / híbrido / adiar) |
| **3 Clarify** | `clarify.md` C-NNN | "depende" | "C-003: peso de segurança nunca < 30%; exceção só com aprovação do CISO." |
| **3.5 Constituição** | Camada 1 (princípios de decisão inegociáveis); Camada 2 (estrutura da árvore, formato do template de registro) | "decisão baseada em dados" | Camada 1: 4 princípios enumerados + escopo inviolável; Camada 2: árvore de 3 níveis + template com 7 campos |
| **4 Plano de adoção** | `plano-adocao.md` *(M2)* — piloto, rollout, onboarding, quem é dono | "divulgar internamente" | F1: piloto com 3 decisões-exemplo reais; F2: expansão a N times; F3: obrigatório em decisões > R$300k |
| **5 Tasks** | `tasks.md` — treinar, criar exemplos canônicos, criar anti-padrões, criar template de decisão | "treinar time" | "T-007: gravar caso canônico (make vs buy do módulo Y) com desfecho." |
| **6 Analyze** | `analyze.md` — matrizes critérios × árvore × exemplos × anti-padrões; cobertura dos anti-padrões | 1 exemplo preenchido | 3 canônicos cobrindo decisão fácil / decisão borderline / decisão contraintuitiva + 3 anti-padrões com justificativa |
| **7 Piloto** | 3 decisões reais aplicando o playbook, com feedback do decisor | piloto simulado | 3 decisões reais nos próximos 30 dias; decisor preencheu template; log comparado com julgamento de senior |
| **8 Validação** | As decisões pilotadas bateram com julgamento sênior? | "achei bom" | 2/3 bateram; 1 divergência revelou peso de critério mal calibrado → C-014 refina peso |
| **9 Guia de uso** | `guia-uso.md` — quickstart do playbook (equivalente a Fase 9 em D1) | "leia e use" | 5 passos com template de decisão preenchível + exemplo completo resolvido |
| **10 Review** | `review.md` com **revisão cega por par sênior externo** (não o autor) | autor revisa a si mesmo | Par externo comenta 3 pontos + assina; autor incorpora feedback; veredicto final registrado |
| **11 Publicação oficial** | v1.0 publicada + comunicado + treinamento + versão congelada; versão anterior arquivada | link no drive | Anúncio oficial, treinamento concluído em 80% do público, v1.0 congelada com hash, roadmap para v1.1 |
| **12 Métrica de eficácia** | `metrica-eficacia.md` *(M2)* — após N usos, taxa de decisão mantida em retrospecto | "uso subjetivo" | "12 decisões em 3 meses: 10 sustentadas, 2 revertidas; 1 revertida por critério mal calibrado → ADR-018 refina peso." |

## Reinterpretações-chave das fases
Fases 2, 7, 8, 10 têm peso diferente em D3:
- **Fase 2** (Spec → Critérios + árvore): não é "o sistema deve fazer X"; é "quando situação X acontecer, o decisor aplica os critérios Y com pesos Z e consulta a árvore". A lógica é declarativa.
- **Fase 7** (Implement → Piloto com decisões reais): 3 decisões reais em contexto real. Nunca simuladas.
- **Fase 8** (Test → Validação por par sênior): validação é "o julgamento do playbook bate com o de um decisor experiente?".
- **Fase 10** (Review → Revisão cega externa): par sênior **externo ao autor** é mandatório; caso contrário, vira autovalidação que viola o princípio de antialucinação.

## Cuidados específicos em D3
- **Viés do autor:** o autor do playbook é quase sempre um especialista; o playbook serve para **outros** — testar sempre com não-autores.
- **Overfitting a casos passados:** a árvore de decisão deve cobrir tipos de caso, não casos específicos; o linter conceitual de Fase 6 checa que não há folhas que descrevem um único caso real.
- **Envelhecimento:** o playbook envelhece. Fase 12 obriga revisão periódica — se não houve métrica em 6 meses, o playbook está `[NEEDS CLARIFICATION: eficácia atual desconhecida]`.

## Templates em estado contrato (M1) vs concretos (M2)
Templates de D3 entram em M2:
- `templates/briefing-decisao.md`
- `templates/criterios.md`
- `templates/arvore-decisao.md`
- `templates/exemplos-canonicos.md`
- `templates/antipadroes.md`
- `templates/plano-adocao.md`
- `templates/metrica-eficacia.md`

Em M1, templates genéricos (`briefing.md`, `clarify.md`, `analise.md`, `review.md`) são adaptados mentalmente. Marcar com `[INFERÊNCIA]` qualquer estrutura improvisada.
