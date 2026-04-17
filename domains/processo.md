# Domínio D2 — Processo empresarial

> Este arquivo é **materialização**, não autoridade. As perguntas canônicas vivem em `fases/*.md`; as regras inegociáveis em [`filosofia.md`](../filosofia.md). Este domínio só **anexa** detalhamento de como cada fase se concretiza quando a entrega é um processo operacional (onboarding, cobrança, atendimento, auditoria, compliance, handoff interdepartamental).

⚠️ **Estado v1.1 (M1):** D2 tem **contrato de fases definido**, mas os templates concretos (`mapa-as-is.md`, `mapa-to-be.md`, `sla-raci.md`, `kpis.md`, `runbook.md`, `script-auditoria.md`, `retrospectiva-operacional.md`, `briefing-processo.md`) entram em M2. Em M1, a skill pode conduzir as conversas por D2 mas referencia templates em estado "a ser criado".

## Quando usar este domínio
- Resultado final é **um processo rodando**, com pessoas executando passos (possivelmente apoiadas por software existente).
- O "produto" é um fluxo operacional documentado, treinado, medido e governado.
- Há SLAs, papéis (RACI), exceções tratadas, KPIs observados, compliance a respeitar.

## Quando NÃO usar
- Se o núcleo é código novo rodando → D1.
- Se o núcleo é construir um framework de decisão repetível → D3.
- Se processo **e** software mudam juntos → híbrido.

## Constituição — o que esperar nesta camada
- **Camada 1 (invariantes):**
  - Princípios de conduta (humano/IA, §5.4 D2, marcadores).
  - Compliance regulatório aplicável (LGPD, CVM, ANVISA, BACEN, ANS — conforme setor).
  - Código de ética / conduta da organização.
  - Alçadas máximas absolutas (ex.: "nunca aprovar desembolso > R$1MM sem Conselho").
- **Camada 2 (escolhas):**
  - Sistemas de origem (CRM = Pipedrive / Salesforce / HubSpot; ERP; ferramenta de tickets; ferramenta de assinatura).
  - Notação de mapa (BPMN 2.0, fluxograma, UML Activity).
  - Ferramenta de governança (Confluence, Notion, SharePoint).
  - Estrutura de relatório (periodicidade, destinatários, formato).

Alterar Camada 1 exige ADR major + aprovação Diretoria/Compliance. Alterar Camada 2 exige ADR minor.

## Regras sensíveis típicas em D2 (§5.4 ampliada)
Nenhuma decidida pela IA. Cada uma → C-NNN em `clarify.md` com autor humano:

- **Alçada financeira** — teto de aprovação por papel; dupla alçada a partir de quanto.
- **Escalação** — quando um caso sobe (tempo fora de SLA, valor, tipo de exceção) e para quem.
- **Tratamento de exceção regulatória** — o que escapa do fluxo padrão sem violar compliance.
- **Autoridade de aprovação** — quem assina o quê (contratos, exceções comerciais, descontos).
- **Compliance** — LGPD, CVM, setorial; quem é o DPO; o que é reportável.
- **SLA crítico** — o que, se não cumprido, invalida o processo (ex.: notificar cliente em 24h).
- **Janela de manutenção / freeze** — períodos em que mudanças não entram (ex.: fechamento mensal).

## Materialização fase-a-fase

| Fase | Artefato D2 | Exemplo ruim | Exemplo bom |
|---|---|---|---|
| **0 Recepção** | lista de módulos do processo (aquisição, onboarding, cobrança, churn, auditoria) + escolha | "melhorar atendimento" | "Módulo: onboarding de cliente PJ nos primeiros 14 dias." |
| **0.5 BMAD** | `bmad.md` + `decision_log.md` (mesmos templates; conteúdo operacional) | 1 alternativa | Processo síncrono vs handoff assíncrono com pre-mortem; candidatas a regra sensível: alçada, escalação, exceção regulatória |
| **1 Briefing** | `briefing-processo.md` *(M2)* — dor operacional, atores, sistemas de origem, KPIs-alvo | "reduzir tempo" | "SDR leva 4h em média; meta 40min; 2 casos por dia fora de SLA." |
| **2 Spec (mapa)** | `mapa-as-is.md` + `mapa-to-be.md` *(M2)* com passos, responsáveis (RACI), SLAs, handoffs, gatilhos | "o time faz X" | "SDR → valida CNPJ em até 2h → se fora do SLA, escala gerente; se CNPJ irregular, bloqueia e reporta compliance." |
| **3 Clarify** | `clarify.md` C-NNN — alçada, escalação, exceção | "gerente decide" | "C-009: alçada até R$50k sem aprovação; acima, dupla alçada Diretor + CFO." |
| **3.5 Constituição** | `constitution.md` — Camada 1 (compliance + ética + alçadas absolutas); Camada 2 (sistemas de origem + notação de mapa) | "seguir as normas" | Camada 1: 6 princípios listados + LGPD Art. 7° + Código de Conduta v2; Camada 2: CRM = Pipedrive, mapa em BPMN 2.0. |
| **4 Plan** | `plan.md` = plano de desenho + plano de piloto + plano de adoção, com gates de expansão | "rodar a partir de segunda" | F1: piloto com 2 clientes por 2 semanas; F2: 25% do volume; F3: 50%; F4: 100%. Gate entre fases: KPI X ≤ baseline. |
| **5 Tasks** | `tasks.md` — tarefas operacionais (criar template, treinar time, ajustar CRM, criar dashboard) | "treinar o time" | "T-011: gravar 3 vídeos de 5min + checklist de certificação + roleplay com 2 atendentes." |
| **6 Analyze** | `analyze.md` — matrizes mapa-to-be × SLAs × RACI × constituição × KPIs × compliance | "falta dono em 2 passos, seguimos" | veredicto 🟡 com 3 [RISCO ASSUMIDO] assinados por Diretoria; matriz compliance × to-be verde |
| **7 Execução piloto + adoção** | rodar o to-be com N% do volume; comparar KPI com baseline; ajustar | go-live completo sem piloto | Piloto 2 semanas, 3 casos por dia, KPI de tempo médio caiu 40%; 1 exceção não prevista → nova C-NNN |
| **8 Validação operacional + auditoria** | SLAs cumpridos? KPIs batendo? `script-auditoria.md` roda em 100% dos casos-piloto sem desvio crítico? | "time achou bom" | Auditoria rodou em 20 casos do piloto: 0 desvios críticos, 2 observações menores registradas |
| **9 Runbook** | `runbook.md` *(M2)* — roteiro operacional para quem executa, com prints, caminho feliz + caminhos de exceção | "entrar no CRM e fazer" | 7 passos com print + critério de sucesso + rollback por passo + número de contato em caso de exceção |
| **10 Review** | `review.md` antes do go-live: quem aprovou, quem treinou, quem sabe reverter | "gerente ok" | 3 aprovadores assinados (Ops + Compliance + Finanças) + rollback testado + comunicação redigida |
| **11 Go-live + comunicação** | data de corte + comunicado interno/externo + documentação viva publicada + versão N+1 em vigor | go-live silencioso | Comunicado em T-7, T-1, T0; treinamento concluído em 98% do time; processo v1.0 publicado em Confluence; `bmad.md` referenciado |
| **12 Retrospectiva operacional** | `retrospectiva-operacional.md` *(M2)* após N ciclos — KPIs, incidentes, exceções, propostas de ADR global | "rodou bem" | "2 exceções/mês, 1 fora do SLA por ferramenta; ADR-021 propõe revisão de alçada em casos X" |

## Reinterpretações-chave das fases
Fases 7, 8, 9, 11 mudam de significado em D2:
- **Fase 7** (Implement → Piloto): não é "escrever código", é "rodar o to-be com amostra controlada".
- **Fase 8** (Test → Validação operacional): suíte é auditoria operacional + comparação de KPI com baseline, não testes automatizados de software.
- **Fase 9** (Quickstart → Runbook): mesmo espírito (alguém sem contexto consegue executar) com foco em ação humana apoiada por sistemas.
- **Fase 11** (Merge → Go-live): a "branch é mergada" vira "o processo v1.0 passa a valer a partir de data D; a versão anterior é arquivada".

## Ponto de contato com compliance
D2 é onde o compliance é mais denso. Em **toda** fase em que a spec/mapa/plano toca regra sensível (§5.4 D2), a skill **trava** até haver assinatura humana com papel adequado (Compliance Officer, DPO, Diretoria). Marcadores `[DECISÃO HUMANA]` em D2 têm peso formal — podem virar evidência em auditoria.

## Templates em estado contrato (M1) vs concretos (M2)
Templates de D2 referenciados em várias fases acima entram em M2:
- `templates/briefing-processo.md`
- `templates/mapa-as-is.md`
- `templates/mapa-to-be.md`
- `templates/sla-raci.md`
- `templates/kpis.md`
- `templates/runbook.md`
- `templates/script-auditoria.md`
- `templates/retrospectiva-operacional.md`

Em M1, a conversa flui com a estrutura deste arquivo + templates genéricos (`briefing.md`, `clarify.md`, `analise.md`, `review.md`) adaptados mentalmente pela condução. A skill sinaliza explicitamente quando um template concreto estaria aqui, marcando com `[INFERÊNCIA]` a estrutura improvisada.
