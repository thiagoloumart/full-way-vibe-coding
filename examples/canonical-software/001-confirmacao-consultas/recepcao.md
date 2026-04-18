---
artefato: recepcao
fase: 0
dominio: [software]
schema_version: 1
requer:
  - "1. Ideia reformulada"
  - "2. Classificação do projeto"
  - "3. Módulos detectados"
  - "4. Módulo alvo"
  - "5. Hipóteses estratégicas iniciais"
  - "6. Ponte para Fase 0.5 (BMAD)"
---

# Recepção — `001-confirmacao-consultas` (Canônico D1)

**Data:** 2026-04-18
**Modo de condução:** Arquiteto (pré-escrita com marcadores `[INFERÊNCIA]`; validação humana via PR review).
**Autor:** Thiago Loumart
**Revisor:** — (self-review; time=1)
**Status:** Finalizada
**Wave:** W1 track B (M2 v1.2) — primeiro exemplo canônico D1 do repo

Referências:
- `fases/00_RECEPCAO.md` (contrato da fase)
- `harness/_audit/progress.md §W1 track B` (justificativa do replanejamento)
- ADR-002 (não aplica — harness tem stack fixa; este canônico tem stack própria)

---

## 1. Ideia reformulada

> **Sistema web para clínicas SMB (micro/pequenas/médias empresas) confirmarem consultas de pacientes, reduzindo no-show por meio de lembretes multicanal (e-mail / SMS / WhatsApp) e ações de confirmação/cancelamento/reagendamento operadas pelo atendente, com histórico auditável por consulta.**

A dor-raiz não é "agendar consulta" (agenda a clínica já faz, até em papel). A dor é **paciente não aparecer** e a clínica perder o slot sem tempo de reagendar. Confirmação explícita antecipa o no-show e abre janela para reuso do slot.

Perfil do cliente-alvo (confirmado em 2026-04-18): **MPE**. Baixo volume de usuários simultâneos (dezenas, não milhares). Estrutura deve ser extensível mas não otimizada para enterprise. Nada de multi-tenancy pesada, Kubernetes, microserviços.

## 2. Classificação do projeto

**Greenfield.** Repositório novo, sem código preexistente, sem migração. O exemplo canônico nasce do zero para provar que a skill conduz o ciclo completo 0→12 em um módulo real.

A invariante da skill **"brownfield-capable"** (ver `fases/00_RECEPCAO.md §Gate` e `domains/software.md`) não é exercitada neste canônico — é escopo para o 3º canônico futuro, que será brownfield deliberado para exercitar o outro ramo.

## 3. Módulos detectados

Sistema completo, decomposto por valor operacional:

| Tier | Módulo | Papel no sistema |
|---|---|---|
| ⭐ Core | **Cadastro e autenticação** (clínica + atendente + médico) | Sem isso, ninguém loga. Pré-requisito técnico implícito. |
| ⭐ Core | **Agendamento** (criar/editar/cancelar consulta) | Gera o dado bruto que confirmação opera. |
| ⭐ Core | **Confirmação de consultas** — **ALVO DESTE CICLO** | Resolve a dor-raiz do no-show. |
| 🔶 Crítico | Notificação multicanal (e-mail / SMS / WhatsApp) | Driver técnico do lembrete; falha = paciente não sabe; tolera degradação por canal (fallback email se SMS falhar). |
| 🔶 Crítico | Histórico e auditoria de confirmação | §5.4 de produto: quem confirmou quando, por qual canal, trilha para disputa com paciente. |
| ⚙️ Essencial | Dashboard de métricas (taxa de confirmação, no-show, canal mais respondido) | Sem isso, a clínica não enxerga valor do sistema — mas não bloqueia MVP. |
| ➕ Complementar | Multi-unidade (mesma rede, várias clínicas) | Fora do perfil MPE inicial. Fase 2.5+ do roadmap. |
| ➕ Complementar | Integração com agenda Google/Outlook do médico | Atraente, não essencial. |
| ➕ Complementar | Painel do paciente (consultas históricas via link mágico) | Pode aumentar confirmação, mas exige UX adicional. |
| ✨ Cosmético | White-label / customização visual por clínica | Deixa para depois. |

**Regra aplicada:** prioridade do ciclo inicial é sempre um `Core` (Manual §24). Três Core foram detectados. Decido entre eles abaixo.

## 4. Módulo alvo

### Escolha: **Confirmação de consultas**

Justificativa em três camadas:

1. **Camada da dor-raiz.** `Cadastro` e `Agendamento` são pré-requisitos técnicos, mas nenhum dos dois é **a dor que a clínica sente todo dia**. A dor é o paciente que não aparece. Confirmação é o módulo que resolve essa dor.
2. **Camada de escopo mínimo funcional.** Para Confirmação funcionar, preciso que exista uma consulta agendada e alguém logado que opere sobre ela. Mas a implementação pode tratar `Cadastro` e `Agendamento` como **scaffolding mínimo** — não como módulos independentes com 15 fases cada. Isso é decisão da Fase 3.5 Constituição e Fase 4 Plan.
3. **Camada de feedback rápido.** Confirmação é o módulo onde a clínica percebe valor imediato na primeira semana de uso (taxa de confirmação sobe, no-show cai). Cadastro e Agendamento são invisíveis quando funcionam.

### O que **não** está no alvo (fora de escopo deste ciclo)

- Cadastro completo com perfis hierárquicos (admin de rede, admin de clínica, atendente, médico, paciente). `[RISCO ASSUMIDO]` Scaffolding mínimo = 1 clínica + 1–N atendentes + 1–N médicos + N pacientes como recurso. Sem multi-unidade.
- Agendamento rico (encaixe, overbooking, bloqueios por feriado/ausência). `[RISCO ASSUMIDO]` Agendamento mínimo = form simples que cria `Consulta` com `{paciente, médico, data/hora, status=agendada}`.
- Dashboard analytics. Adiado para ciclo 4+ do roadmap.
- Pagamento/integração financeira. `[DECISÃO HUMANA: fora de escopo]` Não é parte de confirmação — é sistema separado de cobrança (§5.4 de produto).

### Stack proposta para o módulo (ADR-local ficará em Fase 0.5 Decide / formalizada em Fase 3.5 Constituição §Camada 2)

`[INFERÊNCIA]` — nenhum conhecimento prévio do autor; recomendação do Arquiteto baseada em análise 2026-04-18:

| Camada | Escolha proposta | Motivo |
|---|---|---|
| Linguagem/Framework | **Laravel 12** (PHP 8.3+) | Curso formal do autor + batteries-included (Eloquent, Notifications, Queues, Scheduler) reduzem superfície de plano; fit confortável com perfil MPE; mercado BR abundante. |
| UI | **Blade + Livewire 3** (SSR reativo, sem SPA) | Elimina frontend separado; atendente usa dashboard server-rendered; reduz stack e deploy. Se no futuro precisar SPA, Inertia + Vue/React é ponte natural (Fase 6 Analyze avalia). |
| Banco | **PostgreSQL 16** | Tipos ricos (`jsonb`, `timestamptz`, ranges) úteis para auditoria, slots e reagendamento; MySQL 8 seria aceitável mas perde em flexibilidade de schema futuro. |
| Fila / Cache | **Redis 7** | Padrão Laravel; suporta `ShouldQueue` para envio assíncrono de lembretes e `Schedule` para jobs recorrentes. |
| Deploy | **Laravel Forge + VPS (Hetzner/DigitalOcean, R$ 30–80/mês)** | Simples para solo-dev; zero Kubernetes. |
| Notificação externa | **Twilio (SMS) + Z-API ou Meta Cloud API (WhatsApp) + SMTP (e-mail)** | Canais plugáveis via `Illuminate\Notifications`; contrato de canal permite trocar provedor sem tocar domínio. `[NEEDS CLARIFICATION: custo-alvo por notificação]` será Clarify C-001 na Fase 3. |

**Validação formal:** esta pilha vira `D-001` em Fase 0.5 BMAD subetapa Decide. Se em Analyze (Fase 6) surgir evidência contra (ex: Laravel mal adaptado a alguma regra de confirmação), revisita via `D-001-REVISED`. Sem `D-001` assinado, não pode haver plan ou tasks.

## 5. Hipóteses estratégicas iniciais

Três frases-base que o BMAD vai testar, refinar ou derrubar:

### 5.1 Hipótese
> Confirmação explícita reduz no-show de uma faixa típica de **~30% em clínicas SMB sem sistema** para **<10%** em poucas semanas de adoção, liberando slots para reagendamento e aumentando receita por profissional-hora.

`[INFERÊNCIA]` — números aproximados; literatura em saúde cita faixas 20–40% de no-show em SMB, e sistemas de confirmação reduzem para 5–15%. Não bloqueio sem fonte primária; ponto para a Fase 1 Briefing validar com 2–3 clínicas reais.

### 5.2 Justificativa de priorização (por que este módulo antes de Cadastro/Agendamento)
> Cadastro e Agendamento são **pré-requisitos técnicos invisíveis**; Confirmação é **valor visível semana 1**. A clínica percebe o ROI antes de pagar pelo segundo mês; sem esse sinal, os outros módulos nem são testados.

### 5.3 Ângulo a explorar em BMAD (causa-raiz a esmiuçar)
> O recorte estruturalmente não-trivial é **"o que conta como confirmação legítima?"**: responder "ok" no WhatsApp é confirmação válida? Clicar em link mágico no SMS é confirmação? E se o paciente não responde até X horas antes do horário — presumimos no-show e liberamos o slot, ou mantemos agendado? Quem decide X? O sistema fixa? A clínica configura? Há diferença entre retorno (médico conhece o paciente) e primeira consulta?

Essa é a pergunta que o BMAD subetapa Breakdown + Model precisa resolver antes de qualquer FR.

## 6. Ponte para Fase 0.5 (BMAD)

Contrato herdado para a Fase 0.5:

### Entradas consolidadas
- **Problema-raiz 1 frase** (da seção 1): "paciente não aparece; slot perdido sem tempo de reagendar".
- **Atores aparentes** (pré-BMAD): paciente, atendente, médico, sistema interno, integração externa (provedor de SMS/WhatsApp/e-mail), auditor `[INFERÊNCIA]` (só entra se LGPD ou disputa formal exigir).
- **Entidades aparentes** (pré-BMAD): Consulta, Paciente, Médico, Confirmação, Notificação, Lembrete.
- **Regras sensíveis §5.4 já detectadas** (marcar para Clarify + Analyze):
  - **Histórico** — toda mudança de status de Consulta (agendada → lembrete-enviado → confirmada | recusada | no-show) gera registro imutável.
  - **Visibilidade** — atendente de clínica A não vê consultas de clínica B (mesmo que hosting seja compartilhado). Importante se multi-unidade entrar depois.
  - **Expiração** — slot no-show libera em quanto tempo? Isso é regra de negócio sensível; precisa decisão humana.
  - **Auditoria** — quem confirmou (paciente via link, atendente manualmente, sistema via resposta WhatsApp), quando, por qual canal, com que IP/hash.

### Caminhos estratégicos a levantar em BMAD subetapa Analyze (≥2, máx. 4)
Sugestões iniciais do Arquiteto para provocar a discussão:
- **Caminho A — MVP esquelético:** só confirmação manual pelo atendente (sem lembrete automático). Tela lista consultas de hoje e amanhã; atendente clica "confirmar" após ligar. Sem SMS/WhatsApp. `[INFERÊNCIA]` provavelmente insuficiente — não resolve escala.
- **Caminho B — MVP com 1 canal automático:** confirmação manual + lembrete automático por 1 canal (e-mail, que é mais barato e tem SMTP grátis). Link no e-mail confirma.
- **Caminho C — MVP multicanal:** confirmação manual + SMS + WhatsApp + e-mail, com fallback entre canais. Maior custo, maior conversão.
- **Caminho D — MVP orientado a WhatsApp:** confirmação exclusivamente via WhatsApp (cenário BR dominante), sem SMS. Simplifica integração, concentra risco em 1 provedor.

BMAD Analyze deve preencher matriz Velocidade × Qualidade × Risco × Reversibilidade × Custo e o humano decide em Decide.

### Gates transferidos
- `D-001` (stack Laravel 12 + PostgreSQL 16 + Redis + Livewire + Forge) precisa ser assinada em Fase 0.5 Decide. Antes disso, é `[INFERÊNCIA]` recomendada.
- 4 regras §5.4 acima precisam virar `[DECISÃO HUMANA]` marcadas no `decision_log.md`.
- `[NEEDS CLARIFICATION: custo-alvo por notificação]` precisa virar C-001 em Fase 3 Clarify.

---

## Gate de saída da Fase 0 (Manual + `fases/00_RECEPCAO.md`)

- [x] Ideia reformulada e confirmada pelo humano (seção 1 — confirmação via decisão 2026-04-18).
- [x] Projeto classificado como greenfield (seção 2).
- [x] Lista de módulos existe e está priorizada por tier (seção 3).
- [x] Humano escolheu **UM** módulo (seção 4 — "Confirmação de consultas" via recomendação do Arquiteto + autorização do autor).
- [x] Em brownfield, leu o repo antes de propor estrutura — **n/a (greenfield)**.
- [x] Hipóteses estratégicas iniciais registradas (seção 5 — hipótese, justificativa, ângulo BMAD).
- [x] Ponte para 0.5 preparada com atores, entidades, §5.4 candidatas e ≥2 caminhos estratégicos (seção 6).

**Veredicto:** 🟢 Fase 0 concluída. Próxima fase: Fase 0.5 BMAD no mesmo diretório (`bmad.md` + `decision_log.md`).
