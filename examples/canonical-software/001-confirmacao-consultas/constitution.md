---
artefato: constituicao
fase: 3.5
dominio: [software]
schema_version: 1
bicamada: true
requer:
  - "Identidade"
  - "ADRs ativas (referência)"
  - "1. Arquitetura"
  - "6. Regras de segurança estruturais"
  - "7. Limites do MVP"
  - "10. Decisões estruturais permanentes"
  - "4. Stack / Sistemas de origem"
  - "8. Estilo / Convenções"
  - "Histórico de versões"
marcadores_camada:
  camada_1_begin: "<!-- CAMADA_1_BEGIN -->"
  camada_1_end:   "<!-- CAMADA_1_END -->"
  camada_2_begin: "<!-- CAMADA_2_BEGIN -->"
  camada_2_end:   "<!-- CAMADA_2_END -->"
---

# Constituição — `001-confirmacao-consultas` (Canônico D1)

## Identidade

| Campo | Valor |
|---|---|
| **Versão** | v1.0 |
| **Data** | 2026-04-20 |
| **Domínio primário** | D1 software |
| **Status** | Draft (validação humana via merge do PR `w1b/f3.5-constitution`) |
| **Origem** | Declarada pelo humano (modo Arquiteto) + herdada de `decision_log.md` D-001/D-002/D-003 e `clarify.md` C-001 a C-006 |
| **Autor** | Thiago Loumart |

> Esta é a camada mais importante do sistema (Manual §7). Toda decisão subsequente a consulta. Conflitos entre pedidos pontuais e a constituição devem ser sinalizados, nunca resolvidos em silêncio. Alterar Camada 1 exige ADR major (vN → v(N+1).0) + aprovação humana; alterar Camada 2 exige ADR minor (vN.M → vN.(M+1)).

---

## ADRs ativas (referência)

Este canônico é **escopo isolado** dentro do repositório da skill `full-way-vibe-coding`. As ADRs globais catalogadas em [`governanca/adr-global.md`](../../../governanca/adr-global.md) regem o **harness** e a **skill** em si, não o módulo-exemplo. Decisões estratégicas deste módulo vivem em dois lugares dedicados:

| Fonte | Itens | Camada afetada |
|---|---|---|
| [`decision_log.md`](decision_log.md) | **D-001** (stack técnica) · **D-002** (Caminho D WhatsApp-only + fallback) · **D-003** (single-tenant MVP) | 1 e 2 (ver mapeamento abaixo) |
| [`clarify.md`](clarify.md) | **C-001** (custo) · **C-002** (permissões) · **C-003** (deleção LGPD) · **C-004** (janelas & retry) · **C-005** (correção histórico) · **C-006** (auditoria) | 1 e 2 (ver mapeamento abaixo) |

**Mapeamento para as camadas desta constituição:**

| Item | Camada | Onde entra | Justificativa |
|---|---|---|---|
| D-001 stack | **2** | §4 Stack | Stack é escolha mutável via ADR |
| D-002 Caminho D | **2** | §4 Stack (provedor) + §7 Limites MVP | Canal tem critério de invalidação explícito em D-002 — é escolha |
| D-003 single-tenant MVP | **1** | §1 Arquitetura | Mudar para multi-tenant é ruptura estrutural |
| C-001 custo ≤ R$ 0,20 | **2** | §4 Stack (limites operacionais) | Teto comercial ajustável via ADR |
| C-002 atendente + `is_admin` | **2** | §5 Regras de organização | Modelo de autorização é convenção mutável |
| C-003 deleção por anonimização | **1** | §3 Valores bloqueantes + §6 Segurança | LGPD + integridade do histórico — estruturalmente inegociável |
| C-004 janelas & retry (defaults) | **2** | §4 Stack (parâmetros) | Defaults configuráveis por admin |
| C-005 histórico imutável + evento `correcao` | **1** | §3 Valores bloqueantes + §10 Decisões permanentes | Imutabilidade é invariante do domínio |
| C-006 auditoria escopo B + 5 anos | **2** (retenção) + **1** (existência) | §4 (retenção) + §6 (existência) | A **obrigatoriedade** de auditoria é invariante; a **política** de retenção é escolha |

---

<!-- CAMADA_1_BEGIN -->

## Camada 1 — Invariantes (não mudam durante o ciclo)

> Alterar qualquer item desta camada exige ADR com `camada_afetada: 1` + **major bump** (v1.0 → v2.0) + aprovação humana explícita.

### 1. Arquitetura

- **Estilo:** **monolito modular** — uma base de código, múltiplos módulos lógicos (Confirmação, Cadastro-scaffolding, Agendamento-scaffolding) com boundaries internos claros. Não há microsserviços nem SPA separada no MVP.
- **Limites de domínio internos:** `confirmacao` (alvo do ciclo), `cadastro` (scaffolding mínimo paciente/médico), `agendamento` (scaffolding mínimo da consulta), `notificacao` (driver de canal abstrato).
- **Comunicação entre domínios:** **chamada direta em processo** (método-para-método no monolito). Filas/jobs in-process para disparo assíncrono de lembretes.
- **Tenancy:** **single-tenant por instalação** (1 clínica por deploy) conforme D-003. Multi-tenant é ruptura arquitetural — alterar exige v2.0 + ADR major.
- **Contrato de canal de notificação:** **abstração obrigatória** — a camada de domínio Confirmação **nunca** referencia nome de provedor. Toda implementação específica (Meta Cloud API, Z-API, ...) vive atrás de um contrato de driver intercambiável. Invariante que materializa a mitigação estratégica de D-002.

### 2. Papéis e conduta

- **Humano vs IA:** conforme `filosofia.md §3`. Humano é dono do problema e da regra de negócio; IA é executora disciplinada, nunca inventa regra sensível.
- **Marcadores obrigatórios:** `[INFERÊNCIA]`, `[NEEDS CLARIFICATION]`, `[DECISÃO HUMANA]`, `[RISCO ASSUMIDO]`. Qualquer artefato que precise pular uma decisão sensível deixa marcador visível — nunca omite.
- **Regra §5.4 ativa para D1:** cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria → **sempre** decisão humana explícita com autor identificado; tabela consolidada em `clarify.md §Decisões sobre regras sensíveis`.

### 3. Valores bloqueantes do domínio

- **Segurança por default** — toda rota do painel exige autenticação antes de qualquer ação (FR-030). Paciente **não tem credenciais** no sistema.
- **Privacidade de PII (LGPD)** — dados pessoais do Paciente (nome, telefone, e-mail) são tratados como sensíveis; criptografia at-rest; logs operacionais jamais contêm PII em claro (NFR-003).
- **Direito à deleção** — paciente tem direito à anonimização sob demanda conforme LGPD art. 18; operacionalizado via sobrescrita atômica de PII preservando integridade referencial (C-003 / FR-033). Hard delete do paciente é **proibido** — viola o invariante de histórico imutável.
- **Histórico imutável** — nenhum evento registrado no histórico pode ser editado ou removido. Correções legítimas são feitas via **novo evento sobreposto** do tipo `correcao` com referência ao evento original e motivo textual obrigatório (C-005 / FR-017). Este é o princípio estrutural do módulo — romper exige ADR major.
- **Auditoria é obrigatória** — toda ação relevante (envio, resposta, mudança de status, intervenção manual, anonimização) gera evento no histórico. O **escopo** dos campos e a **retenção** (atualmente 5 anos) são decisões de Camada 2 (C-006); a existência da trilha é invariante.
- **Custo humano do fallback** — o Caminho D assume que existe atendente humano capaz de intervir quando paciente não responde ou não tem WhatsApp. Sistema **nunca** fica sem caminho de saída para uma consulta — ou automação resolve, ou humano resolve, jamais limbo silencioso.

### 6. Regras de segurança estruturais

- **Autenticação:** painel web exige autenticação explícita (sessão web padrão). Paciente interage exclusivamente via WhatsApp ou via **link seguro assinado** (com token curto + expiração atrelada à consulta).
- **Autorização:** **RBAC** com 3 perfis funcionais (Paciente, Médico, Atendente) + 1 flag (`is_admin`) no Atendente. Matriz detalhada em C-002 / `spec.md §Permissões`.
- **Proteção de dados sensíveis:** PII do Paciente (`nome`, `telefone`, `email`) armazenada com criptografia at-rest e em campos passíveis de **anonimização atômica** (UPDATE único sem cascade delete). Transporte sempre via TLS.
- **Rate limit estrutural contra envio em massa acidental:** o sistema **não permite** disparar lembretes em volume > N por minuto sem gatilho natural (agendamento de consulta); triggers manuais (ex: reenvio em lote) exigem confirmação explícita de atendente com `is_admin`. Limite numérico exato vive em Camada 2 (parâmetro). Invariante é a existência do guard.
- **Idempotência estrutural:** disparos de lembrete e callbacks de resposta do provedor são sempre deduplicados antes de produzir eventos no histórico (FR-009 / NFR-006).
- **Secrets:** credenciais de provedor (WhatsApp) e banco **nunca** em código ou repositório; vivem em ambiente de runtime gerenciado pelo deploy.
- **Isolamento por clínica:** reforço técnico de D-003 — nenhuma query do domínio Confirmação pode retornar dados de outra clínica. Verificação em camada de repositório, não apenas em UI.

### 7. Limites do MVP

**Dentro:**
- Agendamento, cadastro e confirmação de consultas de uma única clínica.
- Envio automático de lembrete via **WhatsApp** + resposta por botões interativos + atualização de status + histórico imutável.
- Painel web do atendente com status consolidado, destaque para `sem-resposta`, intervenção manual.
- Marcação de compareceu/no-show (e sua correção por evento `correcao`).
- Configuração de janelas e políticas pelo atendente com `is_admin`.
- Anonimização LGPD operada pelo atendente com `is_admin`.

**Fora (para evolução futura — romper exige v2.0 + ADR major):**
- Multi-tenant / multi-clínica / redes com visibilidade cruzada.
- Canais além de WhatsApp (SMS, e-mail automatizado, robocall).
- Pagamento/cobrança de consulta.
- Dashboard analítico avançado (além do painel do dia).
- Painel próprio do paciente (paciente só responde via WhatsApp + link seguro).
- Integração com agenda externa (Google, Outlook, iCal).
- App mobile nativo.
- Encaixe, overbooking, bloqueios por feriado/ausência.
- Prontuário, histórico clínico, integração com plano de saúde.
- Fuso horário múltiplo.

### 10. Decisões estruturais permanentes

- **D-E-01.** Nenhum FR, tarefa ou PR pode decidir regra §5.4 sem autor humano identificado. Violação = bloqueio de merge.
- **D-E-02.** Contrato de canal de notificação **abstrato** na camada de domínio — domínio nunca depende de provedor concreto. Troca de provedor é mudança em 1 implementação do driver, não no domínio. Garantia da mitigação de D-002.
- **D-E-03.** **Histórico imutável por construção** — toda persistência de evento é append-only. Nenhum caminho de código pode fazer UPDATE em evento do histórico; correção é sempre INSERT de evento `correcao`. Violação = bloqueio de merge.
- **D-E-04.** **Paciente nunca tem credenciais** no painel. Interação com paciente acontece exclusivamente via canal de mensagem externo (WhatsApp) ou via link seguro unidirecional derivado da consulta.
- **D-E-05.** Toda consulta em `sem-resposta` é **garantida** de chegar ao painel do atendente antes da janela de silêncio se esgotar. O sistema não pode silenciar uma consulta em limbo (FR-021 + D-E-02).
- **D-E-06.** Automações de envio sempre respeitam a **janela operacional** vigente. Envio fora da janela é **proibido**, não apenas desencorajado — um envio que cairia fora da janela é postergado (ou cancelado se postergar ultrapassar o horário da consulta).

<!-- CAMADA_1_END -->

---

<!-- CAMADA_2_BEGIN -->

## Camada 2 — Escolhas (mutáveis via ADR)

> Alterar qualquer item desta camada exige ADR com `camada_afetada: 2` + **minor bump** (v1.0 → v1.1).

### 4. Stack / Sistemas de origem

Herda **D-001** integralmente:

| Camada | Tecnologia | Versão | Observações | Origem |
|---|---|---|---|---|
| Linguagem | PHP | 8.3+ | Runtime do Laravel | D-001 |
| Backend framework | Laravel | 12.x | Escolha batteries-included; Notifications, Queues, Scheduler nativos | D-001 |
| Frontend | Blade + Livewire | 3.x | SSR reativo — sem SPA; reduz stack e deploy | D-001 |
| Banco primário | PostgreSQL | 16 | Tipos `jsonb`, `timestamptz`, ranges úteis para histórico e slots | D-001 |
| Cache / Fila | Redis | 7.x | `ShouldQueue`, `Schedule`, sessão compartilhada | D-001 |
| Infra / deploy | Laravel Forge + VPS (Hetzner ou DigitalOcean) | — | Faixa R$ 30–80/mês alvo | D-001 |
| Provedor de notificação (WhatsApp) | **Meta Cloud API** ou **Z-API** | — | Escolha final fica para Fase 4 Plan com ADR local; contrato de driver é intercambiável (garantia de D-E-02 em Camada 1) | D-001 + D-002 |

**Parâmetros operacionais-padrão** (configuráveis por atendente com `is_admin`):

| Parâmetro | Default | Origem |
|---|---|---|
| Janela de lembrete | **24h** antes do horário da consulta | C-004 |
| Janela de silêncio | **4h** antes do horário | C-004 |
| Horário operacional de envio | **08h–20h** horário de Brasília | C-004 |
| Retry em erro transitório | **3 tentativas**, backoff 5 / 15 / 45 min | C-004 |
| Custo-alvo por notificação | ≤ **R$ 0,20** (teto operacional); R$ 0,30 aciona invalidação de D-002 | C-001 |
| Retenção de auditoria | **5 anos** + anonimização temporal | C-006 |
| Rate limit contra envio em massa acidental | `[NEEDS CLARIFICATION: definir em Fase 4 Plan com base em perfil real de uso]` | constituição |

### 5. Regras de organização

- **Estrutura de pastas:** convenção Laravel (`app/Domain/<Modulo>/`, `app/Http/Livewire/`, `app/Jobs/`, `database/migrations/`, `tests/`). Domínio Confirmação **não referencia** nome de provedor concreto — só drivers atrás de interface.
- **Naming:** classes em `PascalCase`, métodos em `camelCase`, variáveis em `snake_case` dentro de views Blade; arquivos de teste terminam em `Test.php` (unitário) ou `FeatureTest.php` (integração).
- **Boundaries:** `app/Domain/Confirmacao/` **pode** importar `app/Domain/Cadastro/` e `app/Domain/Agendamento/`; **não pode** importar driver concreto de notificação — apenas interfaces `app/Domain/Notificacao/Contracts/`.
- **Configuração de perfis:** modelo único `User` com campo `role` (`atendente` | `medico`) + flag booleana `is_admin` elevando privilégios do atendente (C-002). `Paciente` é modelo separado sem credencial.

### 8. Estilo / Convenções

- **Formatação / linter:** `Pint` (formatter oficial Laravel, wrapper PHP-CS-Fixer) + `PHPStan` nível 5+ (a confirmar em Fase 4).
- **Convenção de commit:** Conventional Commits — `feat|fix|docs|refactor|test|chore(escopo): assunto`.
- **Convenção de branch:** `w<wave>[tracl]/f<fase>-<tema-curto>` (padrão atual do repo) ou `NNN-nome-modulo` (padrão SKILL.md) — unificar na Fase 4 com ADR local se houver divergência.
- **Testes obrigatórios:** **Pest** como runner. Cobertura mínima por nível:
  - **Unit** — regras de domínio (derivação de status, cálculo de janela, idempotência).
  - **Feature / integration** — FRs com persistência e flow HTTP.
  - **Contract** — driver abstrato de notificação (suite contra mock que simula provedor).
  - **E2E** — **fora** do MVP; painel é validado manualmente no quickstart.
- **Pull Request:** título e descrição seguindo template `.github/PULL_REQUEST_TEMPLATE.md` (a criar em Fase 4 se ausente). Rastreabilidade obrigatória: cada PR cita FR(s) implementados + D-NNN/C-NNN tocados.

### 9. Convenções de código / operação

- **Tratamento de erro:** exceções tipadas por domínio (`ConfirmacaoException`, `NotificacaoFalhaDefinitivaException`, `LgpdAnonimizacaoException`, ...) — nunca lançar `\Exception` crua dentro do domínio.
- **Logging:** estruturado (chave=valor) em JSON quando em produção, legível em dev. **Nunca** logar telefone ou e-mail do paciente em claro — hash curto ou id interno.
- **Tracing / métricas:** métricas mínimas no MVP — contadores de disparos, falhas, confirmações, no-show; histogramas de tempo até resposta. Stack de observabilidade específica fica para Fase 4 Plan com ADR local.
- **Validação de input:** camada HTTP (Form Requests) para validar payloads do painel; camada de domínio revalida invariantes (ex: data da consulta no futuro, paciente da clínica certa).
- **Migrations:** reversíveis sempre que possível; nomes descritivos; sem "seed" de dados de teste misturado com migration real.

<!-- CAMADA_2_END -->

---

## 11. Regra especial — CRM / agentes / SaaS (Manual §29)

O módulo de Confirmação **é** uma automação no sentido do Manual §29. Toda implementação de driver de notificação ou job de disparo DEVE especificar, no plan.md / tasks.md / código:

- **Gatilho** — criação da consulta (FR-006) + horário da janela configurada.
- **Contexto lido** — Consulta + Paciente + Médico + Configuração da Clínica.
- **Decisão tomada** — se envia ou posterga (conforme janela operacional e idempotência).
- **Ação executada** — despacho de mensagem via driver de canal abstrato.
- **Condição de bloqueio** — janela operacional violada; idempotência detectada; número marcado `numero-invalido`; rate-limit acionado.
- **Fallback** — intervenção manual do atendente (User Story 3); retry com backoff para erros transitórios.
- **Log gerado** — evento no histórico imutável com escopo C-006.
- **Critério de sucesso** — NFR-007 custo + SC-001..SC-006.
- **Risco de falso positivo** — paciente recebe lembrete de consulta que foi cancelada tarde → FR-025 mitiga com mensagem de cancelamento.

A formalização consolidada dos 9 campos vive em [`spec.md`](spec.md) (dispersa em FRs e Edge Cases) e será **costurada em seção dedicada** no `plan.md` (Fase 4) quando o driver for desenhado tecnicamente.

## 12. Exceções aprovadas

| Data | Feature/Módulo | Regra rompida | Camada | Justificativa | Autor | ADR |
|---|---|---|---|---|---|---|

*(Vazio — nenhuma exceção aprovada até v1.0.)*

---

## Histórico de versões

| Versão | Data | Bump | ADR | Descrição |
|---|---|---|---|---|
| v1.0 | 2026-04-20 | inicial | — | Constituição inicial do canônico `001-confirmacao-consultas`. Consolida D-001/D-002/D-003 (Fase 0.5 BMAD) e C-001..C-006 (Fase 3 Clarify) em duas camadas. |

---

## Checklist de validação

- [x] Marcadores `<!-- CAMADA_1_BEGIN/END -->` e `<!-- CAMADA_2_BEGIN/END -->` presentes e corretos.
- [x] Campo `bicamada: true` no front-matter.
- [x] Campos obrigatórios do domínio D1 preenchidos (arquitetura, segurança, MVP, decisões permanentes, stack, estilo).
- [x] Regras de segurança (Camada 1) explicitam auth **e** autz (RBAC com `is_admin`).
- [x] Limites do MVP listam "dentro" e "fora" — cruzados com `spec.md §Out of Scope`.
- [x] Stack (Camada 2) reflete D-001; parâmetros defaults (Camada 2) refletem C-001/C-004/C-006.
- [x] Histórico de versões preenchido (v1.0 inicial).
- [x] Conflitos entre spec/clarify e constituição — **nenhum**; constituição é a costura consolidada, não redecidiu nada.
- [x] Nenhuma lib nova além das já formalizadas em D-001.
- [ ] Humano validou via merge do PR `w1b/f3.5-constitution` (pendente).

**Veredicto do Arquiteto:** 🟢 draft v1.0 fechado. Próxima fase: **Fase 4 Plan** — desenha o "como" técnico respeitando Camada 1 e **consumindo** Camada 2; qualquer mudança em Camada 2 proposta pelo Plan vira ADR local minor.
