---
artefato: review
fase: 10
dominio: [software]
schema_version: 1
requer:
  - "1. Escopo do diff"
  - "3. Verificações mínimas (Manual §17)"
  - "4. Aderência à constituição"
  - "5. Sinal de regras de negócio sensíveis (Manual §5.4)"
  - "9. Resultado de testes"
  - "10. Veredicto"
---

# Review — Fase 3.5 Constituição do canônico `001-confirmacao-consultas`

**Branch:** `w1b/f3.5-constitution`
**PR:** [#7](https://github.com/thiagoloumart/full-way-vibe-coding/pull/7)
**Data:** 2026-04-20
**Revisor:** Thiago Loumart (self-review, time=1)
**Status:** Aprovada

---

## 1. Escopo do diff

- **Commits:** 1 (`f872241` — `feat(canonical-001): Fase 3.5 Constituição v1.0 — bicamada consolidando D-NNN e C-NNN`).
- **Linhas alteradas:** +251 / -2.
- **Arquivos:** 2 — `examples/canonical-software/001-confirmacao-consultas/{constitution.md,README.md}`.

Escopo contido a uma fase. Mantém disciplina "1 PR por fase" dos PRs anteriores.

## 2. Arquivos alterados por categoria

| Categoria | Arquivos |
|---|---|
| Código de produção | — |
| Migrations | — |
| Testes | — |
| Docs — artefato SDD do módulo | `examples/canonical-software/001-confirmacao-consultas/constitution.md` |
| Docs — README do exemplo | `examples/canonical-software/001-confirmacao-consultas/README.md` |
| Configuração | — |
| Infra | — |

## 3. Verificações mínimas (Manual §17)

- [x] **Arquivos alterados** — exatamente 2, ambos previstos. ✅
- [x] **Migrations** — n/a. ✅
- [x] **Testes criados** — n/a. Constituição é contrato para as fases posteriores testarem. ✅
- [x] **Rotas alteradas** — n/a. ✅
- [x] **Policies/permissões alteradas** — n/a. Matriz de autorização **referenciada** de C-002; constituição não redecide. ✅
- [x] **Integrações externas alteradas** — n/a. Contrato de canal abstrato é formalizado (§1 + D-E-02), sem escolher provedor concreto. ✅

## 4. Aderência ao contrato da fase

- [x] **Estrutura de pastas** — `examples/canonical-software/NNN-modulo/constitution.md` é o path canônico.
- [x] **Marcadores HTML obrigatórios:** `<!-- CAMADA_1_BEGIN -->` + `<!-- CAMADA_1_END -->` + `<!-- CAMADA_2_BEGIN -->` + `<!-- CAMADA_2_END -->` todos presentes, delimitando corretamente as 2 camadas.
- [x] **Front-matter `bicamada: true`** presente.
- [x] **Versionamento explícito:** v1.0 com histórico no rodapé.
- [x] **Convenção de markdown** preservada — headings numerados conforme template (1, 4, 6, 7, 8, 10 no `requer:` + 2, 3, 5, 9, 11, 12 livres).
- [x] **Mapeamento explícito** de cada D-NNN e C-NNN para sua camada (Identidade §ADRs ativas + tabela de mapeamento). Nenhum item misturado entre camadas.
- [x] **Coerência com fases anteriores:**
  - D-001 (stack) → Camada 2 §4. ✅
  - D-002 (Caminho D) → Camada 2 §4 (provedor) + §7 Limites MVP. Critério de invalidação preservado. ✅
  - D-003 (single-tenant) → Camada 1 §1 Arquitetura + D-E-02. ✅
  - C-001 (custo) → Camada 2 §4 parâmetros. ✅
  - C-002 (permissões) → Camada 2 §5 + referência a spec §Permissões. ✅
  - C-003 (deleção LGPD) → Camada 1 §3 Valores bloqueantes + §6 Segurança. ✅
  - C-004 (janelas) → Camada 2 §4 parâmetros. ✅
  - C-005 (histórico imutável + evento `correcao`) → Camada 1 §3 + §10 D-E-03. ✅
  - C-006 (auditoria 5 anos) → Camada 1 (existência) + Camada 2 (retenção numérica). ✅
- [x] **Lint passa** em `constitution.md`.
- [x] **Heading-vs-requer** — 9 itens do `requer:` batem com headings do corpo:
  - `Identidade` → `## Identidade` ✅
  - `ADRs ativas (referência)` → `## ADRs ativas (referência)` ✅
  - `1. Arquitetura` → `### 1. Arquitetura` ✅
  - `6. Regras de segurança estruturais` → `### 6. Regras de segurança estruturais` ✅
  - `7. Limites do MVP` → `### 7. Limites do MVP` ✅
  - `10. Decisões estruturais permanentes` → `### 10. Decisões estruturais permanentes` ✅
  - `4. Stack / Sistemas de origem` → `### 4. Stack / Sistemas de origem` ✅
  - `8. Estilo / Convenções` → `### 8. Estilo / Convenções` ✅
  - `Histórico de versões` → `## Histórico de versões` ✅

## 5. Sinal de regras de negócio sensíveis (Manual §5.4)

Constituição é o **ponto de consolidação** das regras §5.4 como invariantes arquiteturais ou como parâmetros escolhidos. Aderência:

- [x] **Nenhuma regra §5.4 foi decidida aqui em silêncio.** Todas herdam de D-NNN ou C-NNN — cada ocorrência tem origem explícita na constituição.
- [x] **§5.4 que virou Camada 1 (invariante):**
  - **Visibilidade** → §1 Arquitetura (single-tenant) + D-E-02 (isolamento por clínica).
  - **Histórico** → §3 Valores bloqueantes + D-E-03 (append-only por construção).
  - **Deleção** → §3 Valores bloqueantes (anonimização, hard delete proibido) + §6 Segurança.
  - **Permissão (estrutura)** → §6 Segurança RBAC (a **existência** de autenticação + autorização é invariante; a matriz fina é Camada 2).
  - **Auditoria (existência)** → §3 Valores bloqueantes (obrigatória).
- [x] **§5.4 que virou Camada 2 (mutável via ADR):**
  - **Permissão (matriz)** → §5 Regras de organização (C-002 formato: 3 perfis + `is_admin`).
  - **Expiração** → §4 parâmetros (C-004 janelas).
  - **Auditoria (retenção)** → §4 parâmetros (C-006 = 5 anos).
- [x] **Cobrança e Estorno** explicitamente declarados fora de escopo no mapeamento (consistente com D-002 tabela §5.4).
- [x] **6 decisões estruturais permanentes (D-E-01..D-E-06)** são novas formalizações que a constituição introduz para **materializar** invariantes que antes estavam dispersos em bmad/clarify:
  - D-E-01: §5.4 sempre com autor humano — materializa Manual §5.4.
  - D-E-02: contrato de canal abstrato — materializa mitigação de D-002.
  - D-E-03: histórico append-only por construção — materializa C-005 no código.
  - D-E-04: paciente sem credenciais — materializa briefing §5.
  - D-E-05: `sem-resposta` nunca silencia — materializa C-004 + spec FR-021.
  - D-E-06: envios respeitam janela operacional — materializa C-004 + FR-010.

## 6. Observações / pontos estranhos

- **Introdução das 6 "Decisões estruturais permanentes" (D-E-01..D-E-06)** é o acréscimo de mais peso deste artefato. Elas **não** são invenção da constituição — cada uma cita origem em fase anterior e reformula como invariante arquitetural testável. É exatamente o que a Camada 1 da constituição deve fazer: extrair a espinha dorsal do que já foi decidido e deixá-la inviolável. Se qualquer delas aparecer violada em Plan ou Implement, o Review (Fase 10) bloqueia o merge.
- **Provedor WhatsApp (Meta vs Z-API)** deliberadamente **não** foi escolhido nesta constituição — ficou como decisão da Fase 4 Plan com ADR local. Isso é correto: constituição fixa o **contrato abstrato** (Camada 1 D-E-02), mas a **escolha** do driver concreto é Camada 2 / Plan. Dívida 7.6 dos reviews anteriores continua válida.
- **`[NEEDS CLARIFICATION]` residual** em Camada 2 §4: "rate limit contra envio em massa acidental — definir em Fase 4 Plan com base em perfil real de uso". É **parâmetro numérico** (não regra §5.4); a **existência** do guard é invariante (§6 Segurança). Logo, não viola o contrato da Fase 3.5 nem o da Fase 3 (que exigia zero NEEDS CLARIFICATION na **spec**, não na constituição).
- **§1 Arquitetura declara "monolito modular"** explicitamente — decisão estrutural que spec/bmad haviam implicitado mas nunca nomeado. Boundaries internos também são nomeados (Confirmação, Cadastro, Agendamento, Notificação). Isso dá ao Plan uma moldura clara para desenhar pastas sem redecidir.
- **§5 "boundaries"** explicita que `app/Domain/Confirmacao/` **pode** importar Cadastro/Agendamento mas **não pode** importar driver concreto de notificação. Essa é a regra técnica que aplica D-E-02 na prática — usável como check em code review futuro.
- **Dívida `#7.5`** author email persiste no commit `f872241`.

## 7. Dívidas conhecidas / TODO

- [ ] **7.1 — `templates/recepcao.md`** continua aberta. — 2º canônico.
- [ ] **7.2 — Fase 4 Plan** precisa escolher Meta vs Z-API com ADR local (dívida 7.6 herdada); definir rate-limit numérico (`[NEEDS CLARIFICATION]` residual da Camada 2); desenhar máquina de estados da Consulta; consolidar os 9 campos §29 em seção dedicada.
- [ ] **7.3 — Validação H-N em piloto** permanece pós-canônico.
- [ ] **7.4 — Template de PR** (`.github/PULL_REQUEST_TEMPLATE.md`) referenciado em Camada 2 §8 — criar em Fase 4 se ausente.
- [ ] **7.5 — Rebase `--reset-author`** antes de W4. Mais um commit afetado.
- [ ] **7.6 — Ferramentas específicas** (PHPStan nível, stack de observabilidade, CI/CD em GitHub Actions) indicadas em Camada 2 com "a confirmar em Fase 4" — formalização em ADR local quando Plan desenhar.

## 8. CRM / Agentes / SaaS (Manual §29)

Aplicável. §11 da constituição **já lista os 9 campos** referenciando onde cada um está pré-endereçado no `spec.md`. A **consolidação** narrativa dos 9 em formato único continua prevista para `plan.md` (Fase 4), mas o contrato de conteúdo está fixado aqui.

## 9. Resultado de testes

- [x] **Lint de `constitution.md`:** `OK` (zero warnings, zero errors).
- [x] **Gate de `fases/03_5_CONSTITUICAO.md`** — 6 critérios ✅ exceto assinatura humana.
- [x] **Checklist interno da constituição** (§Checklist de validação): 9 ✅ + 1 pendente de merge.
- [x] **Grep de marcadores HTML** confirma presença das 4 tags (BEGIN/END × 2).
- [x] **Coerência cruzada com decision_log + clarify + spec:**
  - Nenhum item da constituição contradiz D-001/D-002/D-003 ou C-001..C-006.
  - Nenhum item da constituição contradiz spec.md v2 (FR-007 = D-E-02 conjugado; FR-032 = §1 arquitetura; FR-017 = D-E-03; NFR-003 = §3 valores bloqueantes; etc.).
  - §7 Limites do MVP bate 1:1 com spec §Out of Scope (12 itens cruzados).

## 10. Veredicto

- [x] ✅ **Aprovada** — pode mergar.

Primeira constituição do canônico (v1.0), bicamada, marcadores HTML corretos, 9 decisões §5.4 mapeadas entre Camada 1 (invariantes) e Camada 2 (mutáveis) sem ambiguidade. 6 decisões estruturais permanentes (D-E-01..D-E-06) materializam como invariantes arquiteturais o que antes estava disperso. Contrato de canal de notificação abstrato formalizado em Camada 1 — garantia técnica da mitigação de D-002. `[NEEDS CLARIFICATION]` residual é parâmetro numérico adequadamente transferido para Plan, não viola contrato da fase.

Mergar abre o próximo PR: **Fase 4 Plan** — escolhe Meta vs Z-API (ADR local), define rate-limit, desenha máquina de estados da Consulta, e consolida os 9 campos §29 em seção dedicada.

Assinado por: Thiago Loumart (self-review, 2026-04-20)
