---
artefato: constituicao
fase: 3.5
dominio: [any]
schema_version: 1
bicamada: true
requer:
  - "Identidade"
  - "ADRs ativas (referência)"
  - "1. Arquitetura (Camada 1)"
  - "6. Regras de segurança estruturais (Camada 1)"
  - "7. Limites do MVP (Camada 1)"
  - "10. Decisões estruturais permanentes (Camada 1)"
  - "4. Stack / Sistemas de origem (Camada 2)"
  - "8. Estilo / Convenções (Camada 2)"
  - "Histórico de versões"
marcadores_camada:
  camada_1_begin: "<!-- CAMADA_1_BEGIN -->"
  camada_1_end:   "<!-- CAMADA_1_END -->"
  camada_2_begin: "<!-- CAMADA_2_BEGIN -->"
  camada_2_end:   "<!-- CAMADA_2_END -->"
---

# Constituição — [Nome do Projeto / Processo / Playbook]

## Identidade

| Campo | Valor |
|---|---|
| **Versão** | v<N>.<M> |
| **Data** | [YYYY-MM-DD] |
| **Domínio primário** | [D1 software \| D2 processo \| D3 playbook \| Híbrido eixo=<>] |
| **Status** | Draft \| Validada \| Revisão pendente |
| **Origem** | inferida do repositório \| declarada por humano \| mista |
| **Autor** | [humano] |

> Esta é a camada mais importante do sistema (Manual §7). Toda decisão subsequente a consulta. Conflitos entre pedidos pontuais e a constituição devem ser sinalizados, nunca resolvidos em silêncio.

---

## ADRs ativas (referência)

| ADR | Título | Status | Camada afetada | Data | Resumo do efeito |
|---|---|---|---|---|---|
| ADR-001 | [título] | Aceita | 2 | YYYY-MM-DD | [1 linha] |
| ADR-002 | [título] | Aceita | 1 | YYYY-MM-DD | [1 linha] |

(Se nenhuma ADR ainda: "Nenhuma ADR ativa. Esta é a v1.0 inicial.")

---

<!-- CAMADA_1_BEGIN -->

## Camada 1 — Invariantes (não mudam durante o ciclo)

> Alterar qualquer item desta camada exige ADR com `camada_afetada: 1` + **major bump** (vN → v(N+1).0) + aprovação humana explícita.

### 1. Arquitetura (estrutural)
- **Estilo:** [monolito \| monolito modular \| microsserviços \| serverless \| worker+API \| híbrido \| N/A (processo/playbook)]
- **Limites de domínio:** [ex.: billing, accounts, notifications]
- **Comunicação entre domínios:** [chamada direta \| eventos \| fila]

*(Em D2: organização estrutural de papéis — ex.: "Compliance reporta à Diretoria; Ops reporta a COO".)*
*(Em D3: estrutura inviolável da árvore — ex.: "3 níveis, no máximo 4 folhas por nó".)*

### 2. Papéis e conduta
- Humano vs IA: ver [`../filosofia.md §3`](../filosofia.md#3-papéis--humano--ia).
- Marcadores obrigatórios: `[INFERÊNCIA]`, `[NEEDS CLARIFICATION]`, `[DECISÃO HUMANA]`, `[RISCO ASSUMIDO]`.
- Regra §5.4 ativa para domínio — ver [`../filosofia.md §7`](../filosofia.md#7-regra-54--decisões-sensíveis-nunca-pela-ia-ampliada-para-3-domínios).

### 3. Valores bloqueantes do domínio
*(preencher conforme domínio ativo)*

**[se D1]**
- Segurança, privacidade de PII, política de retenção de dados.

**[se D2]**
- LGPD (Lei 13.709/2018) + regulação setorial aplicável: [listar].
- Código de ética / conduta da organização: [referência].
- Alçadas máximas absolutas: [ex.: "Nenhum desembolso > R$1MM sem Conselho"].

**[se D3]**
- Princípios de decisão inegociáveis: [listar 3–5].
- Escopo inviolável de aplicação: [onde o playbook vale].
- Fora de escopo: [onde não vale].

### 6. Regras de segurança estruturais (ou compliance, ou autoridade)

**[se D1]**
- Autenticação: [SSO \| OAuth2 \| JWT própria \| ...]
- Autorização: [RBAC \| ABAC \| ...]
- Proteção de dados sensíveis: [criptografia at-rest + in-transit; masking]
- Rate limit / antifraude: [política]
- Secrets: [onde ficam; como rotacionar]

**[se D2]**
- DPO formal: [nome / papel]
- Canais de denúncia: [canal]
- Auditoria obrigatória: [frequência + escopo]

**[se D3]**
- Autoridade de flexibilização de critério: [papel + processo]
- Prazo de revisão do playbook: [trimestral \| anual]

### 7. Limites do MVP
**Dentro:**
- [...]

**Fora (para evolução futura):**
- [...]

### 10. Decisões estruturais permanentes
- [Decisão 1 + motivo] — ex.: "Não usamos ORM Django pela política de performance."
- [Decisão 2 + motivo]
- [Decisão 3 + motivo]

<!-- CAMADA_1_END -->

---

<!-- CAMADA_2_BEGIN -->

## Camada 2 — Escolhas (mutáveis via ADR)

> Alterar qualquer item desta camada exige ADR com `camada_afetada: 2` + **minor bump** (vN.M → vN.(M+1)).

### 4. Stack / Sistemas de origem / Estrutura escolhida

**[se D1]**

| Camada | Tecnologia | Versão | Observações | ADR |
|---|---|---|---|---|
| Backend framework | ... | ... | ... | ADR-00X |
| Frontend framework | ... | ... | ... | — |
| Banco primário | ... | ... | ... | — |
| Cache | ... | ... | ... | — |
| Fila / stream | ... | ... | ... | — |
| Observabilidade | ... | ... | ... | — |
| Infra / deploy | ... | ... | ... | — |

**[se D2]**

| Função | Sistema | Observação | ADR |
|---|---|---|---|
| CRM | [Pipedrive \| Salesforce \| HubSpot] | ... | ADR-00X |
| ERP | ... | ... | — |
| Ticketing | ... | ... | — |
| Governança documental | [Confluence \| Notion \| SharePoint] | ... | — |
| Notação de mapa | [BPMN 2.0 \| fluxograma \| UML Activity] | ... | — |

**[se D3]**

| Elemento | Escolha | ADR |
|---|---|---|
| Formato da árvore | [N níveis, até X folhas por nó] | ADR-00X |
| Template de decisão | [referência ao template] | — |
| Pesos padrão de critério | [tabela inicial; sujeita a calibração] | — |
| Periodicidade de revisão | [trimestral \| anual] | — |

### 5. Regras de organização (convenções mutáveis)

**[se D1]**
- Estrutura de pastas: [...]
- Naming: [arquivos, classes, funções, testes]
- Boundaries: [o que pode importar o quê]

**[se D2]**
- Organização de documentação: [pasta raiz, hierarquia]
- Convenção de numeração de processos: [ex.: NNN-nome-processo]

**[se D3]**
- Numeração de versões: [v1.0, v1.1, …]
- Arquivo de casos canônicos: [local]

### 8. Estilo / Convenções

**[se D1]**
- Formatação / linter: [...]
- Convenção de commit: [Conventional Commits \| outro]
- Convenção de branch: [NNN-nome-modulo]
- Testes obrigatórios: [unit + integração + contrato + E2E]

**[se D2]**
- Convenção de RACI: [tabela padrão]
- Formato de relatório gerencial: [ex.: dashboard X semanal]

**[se D3]**
- Exemplos canônicos: 3 casos por playbook (fácil, borderline, contraintuitivo)
- Anti-padrões documentados separadamente

### 9. Convenções de código / operação / aplicação

**[se D1]**
- Tratamento de erro: [estratégia]
- Logging: [estrutura, nível, correlação]
- Tracing / métricas: [estratégia]
- Validação de input: [onde; como]

**[se D2]**
- Critério de escalação: [regra base; exceções em clarify]
- Horário de execução: [SLA, janela]

**[se D3]**
- Feedback do decisor: [como capturar]

<!-- CAMADA_2_END -->

---

## 11. Regra especial — CRM / agentes / SaaS (Manual §29)
Quando o projeto incluir agentes autônomos, automações comerciais ou SaaS operacional, consultar [`../protocolos/agentes-e-automacoes.md`](../protocolos/agentes-e-automacoes.md).

Resumo: toda automação DEVE especificar **gatilho, contexto lido, decisão tomada, ação executada, condição de bloqueio, fallback, log gerado, critério de sucesso, risco de falso positivo**.

## 12. Exceções aprovadas
Quando uma feature precisa romper com a constituição, registrar aqui E abrir ADR correspondente:

| Data | Feature/Módulo | Regra rompida | Camada | Justificativa | Autor | ADR |
|---|---|---|---|---|---|---|

---

## Histórico de versões

| Versão | Data | Bump | ADR | Descrição |
|---|---|---|---|---|
| v1.0 | [YYYY-MM-DD] | inicial | — | Constituição inicial |
| v1.1 | [YYYY-MM-DD] | minor | ADR-001 | [descrição da mudança de Camada 2] |
| v2.0 | [YYYY-MM-DD] | major | ADR-005 | [descrição da mudança de Camada 1] |

---

## Checklist de validação

- [ ] Todos os marcadores `<!-- CAMADA_*_BEGIN/END -->` presentes e corretos.
- [ ] Campos obrigatórios do domínio ativo preenchidos.
- [ ] Seções condicionais `[se D1]` / `[se D2]` / `[se D3]` ativas apenas onde relevantes.
- [ ] Stack / sistemas / estrutura refletem o que já está em uso (em brownfield).
- [ ] Regras de segurança (Camada 1) explicitam auth **e** autz (D1) ou compliance aplicável (D2) ou autoridade (D3).
- [ ] Limites do MVP listam "dentro" e "fora".
- [ ] ADRs citadas existem em `governanca/adr-global.md`.
- [ ] Versão e histórico atualizados.
- [ ] Humano validou ou assumiu `[RISCO ASSUMIDO]` se é v0 inferida.
