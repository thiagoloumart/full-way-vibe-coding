---
artefato: decision_log
fase: 0.5
dominio: [software]
schema_version: 1
requer:
  - "Decisões em regras sensíveis (Manual §5.4)"
  - "Revisões posteriores"
---

# Decision Log — `001-confirmacao-consultas`

**Referência:** `bmad.md` v1 (draft 2026-04-18)
**Data de abertura:** 2026-04-18
**Status:** Em andamento (aguarda assinatura humana via merge do PR da Fase 0.5)

Registro auditável de decisões **estratégicas** do módulo. Uma entrada `D-NNN` por decisão. Revisões posteriores criam nova `D-NNN` e marcam a antiga como `SUPERADA POR`.

---

## D-001 — Stack técnica do módulo

**Origem:** `bmad.md §4` (Decide) — formalização da stack proposta em `recepcao.md §4`.
**Contexto:** Recepção propôs pilha baseada na trajetória do autor (curso formal de Laravel) + fit com perfil MPE (batteries-included, baixo custo operacional, deploy simples). `[INFERÊNCIA]` precisava virar decisão formal antes de Plan e Tasks.

**Decisão:** A pilha técnica do canônico `001-confirmacao-consultas` é:
- **Linguagem / Framework:** Laravel 12 (PHP 8.3+).
- **Camada UI:** Blade + Livewire 3 (server-rendered reativo; sem SPA).
- **Banco de dados:** PostgreSQL 16.
- **Fila / Cache:** Redis 7.
- **Deploy:** Laravel Forge + VPS (Hetzner ou DigitalOcean, faixa R$ 30–80/mês).
- **Provedor de notificação (canal único MVP):** WhatsApp via **Meta Cloud API** ou **Z-API** — escolha final entre os dois fica para Fase 4 Plan (com ADR local), preservando contrato de canal abstrato.

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| A | Laravel 12 + Blade/Livewire + PostgreSQL + Redis + Forge | Curso do autor + batteries-included + fit MPE + mercado BR abundante | Acoplado a 1 linguagem/ecossistema | ✅ escolhida |
| B | Node.js (NestJS) + React + PostgreSQL + Redis | Stack moderno; autor poderia aprender | Autor não tem conhecimento prévio; 2 runtimes (backend + frontend SPA) dobram superfície de bug no MVP | ❌ descartada — motivo: curva de aprendizado bloqueia hipótese de entregar o primeiro canônico no prazo do W1 track B |
| C | Ruby on Rails + Hotwire + PostgreSQL | Filosofia batteries-included semelhante a Laravel; Hotwire = paralelo de Livewire | Mercado BR de Rails menor; autor sem conhecimento prévio | ❌ descartada — motivo: idem B + menor disponibilidade local para contratação futura |
| D | Django + HTMX + PostgreSQL | Stable, autor com alguma familiaridade Python | Menor fit com Livewire-like UX; Django async/queue mais trabalhoso que Laravel Queues | ❌ descartada — motivo: produtividade em MVP MPE é menor que Laravel para este domínio |

**Riscos aceitos:**
- `[RISCO ASSUMIDO]` Dependência de PHP/Laravel amarra o projeto a este ecossistema; migração futura (se surgir demanda) exige reescrita substancial.
- `[RISCO ASSUMIDO]` Livewire acopla front e back no mesmo processo; escala horizontal exige sessão compartilhada (Redis, já previsto).

**Critérios de invalidação:** (o que força revisão)
- Se Laravel 12 depreciar funcionalidade crítica usada pelo módulo (Notifications, Queues, Scheduler).
- Se Meta Cloud API / Z-API exigirem SDK oficial que não tenha port PHP razoável — avaliar caso.
- Se o custo de Forge + VPS BR ultrapassar R$ 150/mês (faixa aceita para canônico).

**Hipóteses associadas:** (a validar)
- [ ] Laravel 12 entrega produtividade esperada no fluxo de Fase 7 Implement (validar executando Fase 4 Plan).
- [ ] Forge + VPS Hetzner tem latência aceitável para clientes BR (RTT < 50ms SP ↔ Frankfurt) — alternativa: DigitalOcean São Paulo.

**Autor:** humano (Thiago Loumart) — confirmação via merge do PR `w1b/f0.5-bmad`.
**Data:** 2026-04-18
**Impacto:** define a stack de todos os FRs e histórias do módulo; determina estrutura de diretórios do código em Fase 7; referenciado por Constituição (Fase 3.5 §Camada 2) e Plan (Fase 4).

---

## D-002 — Caminho estratégico de confirmação (BMAD Decide)

**Origem:** `bmad.md §4.1–4.2` (Decide).
**Contexto:** BMAD Analyze comparou 4 caminhos (A manual-only, B e-mail, C multicanal, D WhatsApp-only + fallback manual). A matriz de trade-offs (§3.2) e o pre-mortem (§3.4) apontaram D como caminho de melhor fit cultural BR com menor superfície de bug.

**Decisão:** O MVP de Confirmação de Consultas é implementado como **Caminho D — WhatsApp-only com fallback humano do atendente**. Lembretes automáticos saem exclusivamente via WhatsApp (template com botões interativos Confirmar / Cancelar / Reagendar). Pacientes que não respondem ou não têm WhatsApp caem no fluxo de ação manual do atendente, que já existe como tela-base. Segundo canal (SMS ou e-mail) fica **fora do escopo deste ciclo** e só será considerado após validação de D em produção.

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| D | WhatsApp-only + fallback humano | 1 integração; fit cultural BR; abertura >85%; template com botões elimina parsing de texto livre | Concentração de risco em 1 provedor; dependência regulatória da categoria "utility" do Meta; custo por mensagem | ✅ escolhida |
| A | Manual-only | Zero integração; zero custo | Não resolve causa-raiz (escala); clínica percebe que é só uma planilha online | ❌ descartada — motivo: quebra hipótese de valor-semana-1; fica dentro de D como fallback |
| B | E-mail 1 canal | Custo quase zero (SMTP grátis); 1 integração simples | Abertura estimada em 10–25% em MPE BR; link mágico vulnerável à desconfiança | ❌ descartada — motivo: canal estruturalmente fraco no perfil MPE BR |
| C | Multicanal com fallback | Cobertura máxima; redundância real | 3× integrações; custo SMS alto; orquestrador de fallback complexo; reconciliação entre canais; overengineering MVP | ❌ descartada — motivo: triplica superfície de bug sem ganho proporcional; segundo canal fica para ciclo futuro |

**Riscos aceitos:**
- `[RISCO ASSUMIDO]` Concentração em 1 provedor WhatsApp. Mitigação: contrato de canal abstrato na Spec para troca Z-API ↔ Meta Cloud API em ~1 sprint.
- `[RISCO ASSUMIDO]` Pacientes sem WhatsApp caem 100% no fluxo manual. Aceitável dado que a parcela da carteira MPE sem WhatsApp ativo é pequena `[INFERÊNCIA]` e o fallback humano existe.
- `[RISCO ASSUMIDO]` Custo operacional por notificação (~R$ 0,05–0,20). Teto formal vira C-001 em Clarify.
- `[RISCO ASSUMIDO]` Dependência da categoria de template Meta "utility". Se reclassificada, todo o fluxo para.

**Critérios de invalidação:**
- Meta suspender ou reclassificar a categoria de template "utility" para saúde → revisar para Caminho C (adicionar SMS).
- Custo real médio por notificação ultrapassar R$ 0,30 → revisar provedor ou modelo.
- Taxa de abertura do WhatsApp em teste-piloto ficar < 70% → revisar hipótese.
- <50% dos pacientes da clínica-piloto com WhatsApp ativo → revisar.
- LGPD / ANVISA emitirem restrição específica sobre lembrete de saúde via WhatsApp → revisar imediatamente.

**Hipóteses associadas:**
- [ ] **H-1** Abertura de lembrete WhatsApp em MPE BR > 85%.
- [ ] **H-2** No-show cai de 20–40% para <10% em 30 dias.
- [ ] **H-3** Atendentes aceitam painel server-rendered como UX.
- [ ] **H-4** Custo médio fica em R$ 0,05–0,20 no volume MPE.
- [ ] **H-5** Template de botões cobre 90%+ dos intents sem parser de texto livre.

**Autor:** humano (Thiago Loumart) — confirmação via merge do PR `w1b/f0.5-bmad`.
**Data:** 2026-04-18
**Impacto:** define o escopo de todos os FRs do módulo (lembrete automático, resposta via botões, fallback manual); determina contratos de integração em Plan; moldura todas as entradas de C-001 a C-006 no Clarify.

---

## D-003 — Visibilidade inicial = single-tenant (1 clínica)

**Origem:** `bmad.md §2.6` (Model — Regras sensíveis §5.4).
**Contexto:** Visibilidade entre papéis é regra sensível (§5.4). Recepcao.md (§6) já sinalizou que multi-unidade fica fora do escopo do MVP. BMAD precisa formalizar a decisão para que Spec e Clarify não assumam visibilidade cross-tenant silenciosamente.

**Decisão:** O MVP opera em modelo **single-tenant = 1 clínica instalada**. Não há escopo de "empresa" nem "rede de clínicas" no MVP. Atendente, médico e paciente operam todos dentro da mesma clínica lógica; visibilidade é entre **papéis** (atendente vs. médico vs. paciente), não entre **clínicas**.

**Alternativas consideradas:**
| # | Opção | Prós | Contras | Status |
|---|---|---|---|---|
| A | Single-tenant 1 clínica | Modelo de dados simples; sem políticas de isolamento cross-tenant; entrega rápida | Se cliente multi-unidade aparecer cedo, exige redesign | ✅ escolhida |
| B | Multi-tenant com tenant_id em toda entidade | Suporta crescimento | Triplica superfície de teste de segurança no MVP; não há cliente real pedindo | ❌ descartada — motivo: overengineering para perfil MPE-alvo (clínica individual); postpone até cliente concreto |
| C | Múltiplas clínicas via deploys separados (shared-nothing) | Zero acoplamento de tenants | Opera custo de deploy N× | ❌ descartada — motivo: inviável para escala comercial posterior; B é o caminho correto quando vier |

**Riscos aceitos:**
- `[RISCO ASSUMIDO]` Se no futuro aparecer cliente multi-unidade cedo, haverá retrabalho para introduzir tenant_id e políticas de isolamento. Aceito sob a lógica de "primeiro cliente é MPE com 1 clínica".

**Critérios de invalidação:**
- Se 3+ leads de clínicas em rede aparecerem no Briefing / validação inicial → revisar para B (multi-tenant).
- Se regra de negócio do MVP exigir compartilhamento de paciente entre clínicas (ex: paciente atende em 2 clínicas da mesma dona com mesmo cadastro) → revisar.

**Hipóteses associadas:**
- [ ] O público-alvo MPE das primeiras validações é composto majoritariamente de clínicas individuais, não redes (validar em Briefing).

**Autor:** humano (Thiago Loumart) — confirmação via merge do PR `w1b/f0.5-bmad`.
**Data:** 2026-04-18
**Impacto:** simplifica modelo de dados (sem `tenant_id`); elimina políticas de isolamento horizontal no MVP; **Visibilidade** deixa de ser candidata aberta em Clarify (confirmação em vez de decisão).

---

## Decisões em regras sensíveis (Manual §5.4)

Cada tema aplicável ao módulo precisa de autor **humano**. Esta tabela é a referência rápida para Clarify (Fase 3) e Analyze (Fase 6).

| Tema | Aplica-se? | Decisão | Autor | Ref |
|---|---|---|---|---|
| Cobrança | **não** | Fora de escopo do módulo de confirmação; pagamento é sistema separado. | humano | — |
| Permissão / autorização | **sim** | Pendente — perfis mínimos (atendente, médico, paciente) e matriz de permissões ficam para Clarify. | humano | **C-002 (pendente)** |
| Estorno / cancelamento | **não** | N/A — cancelamento de consulta é transição de status, sem componente financeiro. | humano | — |
| Deleção | **sim (parcial)** | Pendente — direito do paciente à deleção (LGPD art. 18) e impacto no histórico imutável ficam para Clarify. | humano | **C-003 (pendente)** |
| Expiração | **sim** | Pendente — janela de silêncio antes de sistema presumir no-show e liberar slot fica para Clarify. | humano | **C-004 (pendente)** |
| Visibilidade entre papéis | **sim** | **Single-tenant MVP (1 clínica).** Visibilidade entre papéis (atendente vs. médico vs. paciente) fica para Clarify refinar. | humano | **D-003** (este log) + C-002 complementa |
| Histórico | **sim** | Pendente — imutabilidade, retenção, acesso pelo paciente ficam para Clarify. | humano | **C-005 (pendente)** |
| Auditoria | **sim** | Pendente — escopo (timestamp + canal + ator + IP?), retenção, ficam para Clarify. | humano | **C-006 (pendente)** |

**Observação:** `C-001 — custo-alvo por notificação` já estava marcado em `recepcao.md §4` e segue válido; não é regra §5.4 propriamente (é restrição operacional), mas é pendente de Clarify.

---

## Revisões posteriores

*(vazio — nenhuma revisão até esta data.)*

Regra: se uma decisão for alterada em fase futura (Clarify, Analyze técnica, Implement), **não sobrescrever** `D-NNN` existente. Registrar nova `D-NNN` com `Origem: revisão de D-00Y` e marcar a `D-00Y` original como `SUPERADA POR D-NNN`.

---

## Gate de fechamento da Fase 0.5 sobre este log

- [x] Cada decisão tem alternativas com prós/contras (D-001 tem 4 opções; D-002 tem 4; D-003 tem 3).
- [x] Nenhuma linha de descarte está vazia.
- [x] Riscos aceitos marcados `[RISCO ASSUMIDO]`.
- [x] Critérios de invalidação explícitos em cada D-NNN.
- [x] Regras sensíveis §5.4 todas com autor humano (D-003 para visibilidade; C-002 a C-006 pendentes de Clarify — autoria humana preservada).
- [ ] **Humano assina fechamento: "OK — decision log fechado"** (pendente → vira merge do PR `w1b/f0.5-bmad`).
