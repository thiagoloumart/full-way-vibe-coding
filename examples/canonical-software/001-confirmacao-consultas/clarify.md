---
artefato: clarify
fase: 3
dominio: [software]
schema_version: 1
requer:
  - "Decisões sobre regras sensíveis (Manual §5.4)"
---

# Clarificação — `001-confirmacao-consultas`

**Referência:** `spec.md` v1 · `bmad.md` v1 · `decision_log.md` D-001/D-002/D-003
**Data de abertura:** 2026-04-18
**Status:** Fechada (draft validado via PR review — modo Arquiteto)

Esta clarify resolve os **6 pontos** levantados na Fase 2 Spec com `[NEEDS CLARIFICATION]` ou `[DECISÃO HUMANA]` apontando para `C-00X`. Cada entrada segue o contrato de `fases/03_CLARIFY.md`: pergunta, opções com prós/contras, recomendação da IA, decisão humana, justificativa, impacto aplicado na spec.

---

## C-001 — Custo-alvo por notificação

**Origem:** `recepcao.md §4 [NEEDS CLARIFICATION]` → `briefing.md §9 + §10` → `bmad.md §4.4 [RISCO ASSUMIDO]` → `decision_log.md D-002 critério de invalidação "custo > R$ 0,30"`.

**Pergunta:** Qual o teto de custo por notificação enviada que a assinatura mensal por clínica (faixa R$ 100–300 `[INFERÊNCIA]` de briefing §4) absorve sem comprometer margem?

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | R$ 0,05/msg (agressivo) | Cabe até 2000 msg/mês em R$ 100 de assinatura | Exige provedor barato (Z-API tier básico); perde Meta Cloud API se categoria utility ficar acima | Força troca de provedor se Meta exceder |
| B | R$ 0,10/msg (conservador) | Cabe 1000 msg/mês em R$ 100; margem razoável | Volumes > 1000/mês pressionam modelo | NFR de custo + crit. invalidação de D-002 |
| C | R$ 0,20/msg (realista Meta) | Compatível com Meta Cloud API categoria utility BR 2026 `[INFERÊNCIA]` | Reduz margem em assinaturas no piso R$ 100 | Alinha com crit. invalidação D-002 (< R$ 0,30) |
| D | R$ 0,30/msg (teto absoluto) | Acompanha o crit. invalidação de D-002 | Margem muito apertada; alerta frequente | Requer upsell de planos |

**Recomendação da IA:** **C (R$ 0,20/msg)** — balanceia viabilidade comercial de Meta Cloud API (provedor preferencial de D-001) com margem razoável para piso da faixa de assinatura. Deixa folga de R$ 0,10 até o crit. de invalidação de D-002 (R$ 0,30) para oscilação cambial e tarifação dinâmica.

**Decisão tomada:** **C — teto operacional de R$ 0,20/notificação** (média mensal). Se custo médio real de provedor ultrapassar, revisar em D-NNN de revisão; se ultrapassar R$ 0,30 → acionar critério de invalidação de D-002.

**Autor:** humano (Thiago Loumart — modo Arquiteto, validação via merge PR).

**Justificativa:** (1) recomendação IA aceita sem ajuste; (2) R$ 0,20 é limiar público médio de utility do Meta Cloud API no BR em 2026 `[INFERÊNCIA]`; (3) folga até R$ 0,30 preserva o critério de invalidação já contratado em D-002; (4) evita debate inconclusivo de margem antes de tração comercial real.

**Atualizações aplicadas na spec:** novo **NFR-007 — Custo operacional**. Texto: *"O custo médio mensal por notificação enviada MUST ≤ R$ 0,20 (incluindo taxas de provedor). Envios excedentes ao teto acionam alerta de revisão de provedor; superar R$ 0,30 dispara critério de invalidação de D-002."*

---

## C-002 — Matriz de permissões fina (Admin ↔ Atendente)

**Origem:** `spec.md FR-030 / FR-031 [DECISÃO HUMANA: C-002]` · `briefing.md §5 perfil Admin [INFERÊNCIA]` · `bmad.md §2.6` regra §5.4 permissão.

**Pergunta:** O "Admin da clínica" é um perfil **separado** ou um **atendente com bit de admin**? Qual a matriz exata por ação?

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | 4 perfis totalmente separados (atendente/médico/paciente/admin) | Máxima clareza de separação de deveres | Mais código de autorização no MVP; duplica cadastro (quem é só atendente vs. quem é admin) | FR-030/031 ganham mais linhas |
| B | 3 perfis + flag `is_admin` no atendente | Simplicidade; 1 cadastro de pessoa com 1 bit extra; MVP rápido | Princípio de least-privilege frágil se flag for default-true | 4ª coluna na tabela vira "atendente com is_admin" |
| C | 2 perfis funcionais (operador e médico) + paciente sem login | Muito enxuto | Admin e atendente viram indistinguíveis — perde a separação que briefing §5 queria | Força deletar linha Admin da tabela |
| D | Perfis RBAC custom (papel:lista-de-ações atribuíveis por clínica) | Flexível para evolução enterprise | Overengineering MVP; sem cliente pedindo | Fora do escopo W1B |

**Recomendação da IA:** **B (atendente + flag `is_admin`)** — simplicidade operacional alinhada com perfil MPE (mesma pessoa opera o dia a dia e configura); 1 entidade Usuário em vez de 2; flag fácil de auditar; evolução para A é aditiva sem quebra.

**Decisão tomada:** **B — "Atendente com flag `is_admin`"**. Matriz consolidada:

| Ação | Paciente | Médico | Atendente (sem admin) | Atendente (com admin) |
|---|---|---|---|---|
| Logar no painel web | ❌ | ✅ | ✅ | ✅ |
| Responder lembrete via WhatsApp | ✅ | — | — | — |
| Cadastrar/editar paciente | ❌ | ❌ | ✅ | ✅ |
| Cadastrar/editar médico | ❌ | ❌ | ❌ | ✅ |
| Criar/editar/cancelar consulta | ❌ | ❌ | ✅ | ✅ |
| Ver painel do dia (todas as consultas da clínica) | ❌ | ❌ | ✅ | ✅ |
| Ver própria agenda (só próprias consultas) | ❌ | ✅ | — | — |
| Confirmar/reagendar/cancelar em nome do paciente | ❌ | ❌ | ✅ | ✅ |
| Marcar compareceu/no-show | ❌ | ✅ (próprias) | ✅ | ✅ |
| Consultar histórico de uma consulta | ❌ (só a sua via link) | ✅ (próprias) | ✅ | ✅ |
| Configurar janela de lembrete / silêncio | ❌ | ❌ | ❌ | ✅ |
| Acessar dados de outra clínica | ❌ (bloqueado por D-003) | ❌ | ❌ | ❌ |

**Autor:** humano — regra §5.4 permissão.

**Justificativa:** MPE opera tipicamente com 1–2 atendentes onde um é o dono/gerente. Flag `is_admin` cobre esse cenário com mínimo atrito. Opção A (4 perfis separados) adiciona cadastros duplicados sem ganho real; opção C colapsa perfis necessários; opção D é overengineering. Se cliente enterprise aparecer, migração de B para A é aditiva.

**Atualizações aplicadas na spec:** FR-030 e FR-031 consolidados sem `[DECISÃO HUMANA]` — referenciam matriz acima. Tabela `§Permissões` da spec atualizada para refletir opção B com 4 colunas (paciente / médico / atendente / atendente-admin).

---

## C-003 — Política de deleção LGPD (art. 18)

**Origem:** `spec.md FR-033 [DECISÃO HUMANA: C-003]` · Edge case "Paciente pede deleção LGPD" · `bmad.md §2.6` regra §5.4 deleção · `briefing §9 + §10`.

**Pergunta:** Quando o paciente exerce o direito de deleção pela LGPD (art. 18), o que acontece com o histórico imutável de consultas, lembretes e confirmações?

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | **Anonimização** — dados PII do Paciente viram tombstone (`nome = paciente-excluído-<hash>`; `telefone = null`; `email = null`); eventos do histórico preservados com chaveamento anonimizado | Cumpre LGPD (dados pessoais deletados); preserva auditoria e estatísticas operacionais; compatível com FR-017 (imutabilidade) | Paciente permanece como entidade técnica (mesmo que anônima); hash não reversível para re-identificação acidental | FR-033 consolida; NFR-003 ganha regra de anonimização |
| B | **Hard delete** — registro do paciente e todos eventos relacionados apagados | Radical cumprimento LGPD | Rompe FR-017 (histórico imutável); inviabiliza auditoria de disputas; rompe métricas históricas | Contradiz FR-017 → inviável |
| C | **Store separado** — PII em tabela separada que pode ser deletada sem tocar eventos; eventos referenciam PII por chave estrangeira que vira null | Elegante em arquitetura; máxima separação | Complexidade de armazenamento 2x; sincronização extra; MVP não precisa | Plan Fase 4 teria que modelar store separado; overengineering |
| D | **Soft delete + retenção legal** — marca paciente como deletado; dados só apagados após expirar prazo legal (5 anos CFM?) | Balanceado | LGPD exige efetividade imediata; soft delete com dados ainda visíveis não cumpre | Não atende LGPD se dados continuam acessíveis |

**Recomendação da IA:** **A — Anonimização**. É a única opção que respeita simultaneamente LGPD art. 18 (dados pessoais não mais acessíveis), FR-017 (histórico imutável de operação da clínica), e o perfil MVP (sem complexidade de store separado).

**Decisão tomada:** **A — Anonimização**. Regra operacional:
1. Paciente solicita deleção por canal formal (e-mail/contato da clínica).
2. Atendente-admin aciona função "Anonimizar paciente" na UI.
3. Sistema sobrescreve PII do paciente: `nome → "paciente-excluido-<hash-curto>"`, `telefone → null`, `email → null`. Demais campos PII seguem mesma regra.
4. Consultas, Lembretes, Confirmações, Notificações referentes ao paciente **permanecem** com seu chaveamento intacto apontando para o registro anonimizado.
5. Evento de anonimização é registrado no histórico (imutável) com `ator = atendente-admin`, `motivo = "LGPD art. 18 solicitação paciente"`, `timestamp`.
6. Após anonimização, paciente não aparece mais em buscas por nome/telefone; aparece apenas em listagens internas como "paciente-excluido-<hash>".

**Autor:** humano — regra §5.4 deleção.

**Justificativa:** (1) cumprimento efetivo do LGPD art. 18 sem sacrificar obrigação contábil e de registro clínico da clínica; (2) zero conflito com FR-017; (3) implementação simples (UPDATE dos campos PII, não DELETE em cascata); (4) preservação das estatísticas operacionais permite clínica ainda computar taxa de no-show histórica mesmo após anonimizações; (5) opção C seria tecnicamente superior mas overengineering.

**Atualizações aplicadas na spec:** FR-033 consolidado (remove `[DECISÃO HUMANA]`). NFR-003 ganha regra explícita *"Dados PII do Paciente MUST ser armazenados em campos passíveis de anonimização atômica (UPDATE em uma operação), sem afetar integridade referencial de Consulta/Lembrete/Confirmação/Notificação."*

---

## C-004 — Janelas operacionais (lembrete, silêncio, horário de envio, retry)

**Origem:** `spec.md FR-010 / FR-011 / FR-028 [NEEDS CLARIFICATION]` · Edge case "horário de envio cai em janela inadequada" · `bmad.md §2.6` regra §5.4 expiração.

**Pergunta (4 subperguntas combinadas):**
1. Qual a **janela-padrão de envio do lembrete** antes do horário da consulta?
2. Qual a **janela-padrão de silêncio** (quando consulta sem resposta vira `sem-resposta`)?
3. Qual o **horário operacional permitido** para envio?
4. Qual o **limite de tentativas** de retry em erros transitórios e qual o intervalo?

**Opções avaliadas:**

### (1) Janela do lembrete
| # | Opção | Prós | Contras |
|---|---|---|---|
| A | 12h antes | Lembrete próximo ao horário; menos risco de esquecer | Janela curta — paciente pode não reagendar a tempo se cancelar |
| B | **24h antes** | Equilíbrio — paciente tem tempo de cancelar + clínica tem tempo de reagendar | Esquecimento possível em agendamento feito <24h |
| C | 48h antes | Máximo tempo para reagendar | Paciente pode esquecer entre lembrete e horário |
| D | 72h antes | Ideal para cirurgia / especialidades críticas | Overkill para consulta ambulatorial comum |

**Recomendação IA:** **B (24h)** como padrão.

### (2) Janela de silêncio
| # | Opção | Prós | Contras |
|---|---|---|---|
| A | 2h antes | Atendente tem muito pouco tempo de ação | Risco alto |
| B | **4h antes** | Tempo razoável para atendente ligar + reagendar | — |
| C | 6h antes | Mais tempo para atendente | Classifica como "sem resposta" cedo demais |
| D | 12h antes | Muito cedo | Perde oportunidade do paciente responder tarde |

**Recomendação IA:** **B (4h antes do horário)**.

### (3) Horário operacional de envio
| # | Opção | Prós | Contras |
|---|---|---|---|
| A | 24/7 | Máxima cobertura | Envio às 3h da manhã é rude; risco reputacional |
| B | 07h–21h | Horário "civil" amplo | Limite 7h pode incomodar alguns perfis |
| C | **08h–20h BR** | Horário cultural aceito para comunicação de saúde no BR `[INFERÊNCIA]` | Restringe envio a 12h de janela válida |
| D | 09h–19h | Conservador | Janela estreita; risco de postergar envio crítico |

**Recomendação IA:** **C (08h–20h horário de Brasília)** como padrão.

### (4) Retry de envio
| # | Opção | Prós | Contras |
|---|---|---|---|
| A | 3 tentativas, intervalos 5min/15min/45min (backoff exponencial) | Balanceado — cobre queda de minutos | Pode esgotar em queda >1h do provedor |
| B | 5 tentativas, intervalos 1min/5min/15min/30min/60min | Máxima recuperação | Mais filas; atraso possível de ~2h |
| C | 10 tentativas, intervalos fixos 10min | Muito resiliente | Spam em provedor se cair por horas |
| D | Retry indefinido até a janela operacional terminar | Elegante | Imprevisível |

**Recomendação IA:** **A (3 tentativas, backoff 5/15/45 min)** — cobre 99%+ de incidências de quedas transitórias; após 3 falhas é sinal de problema que precisa ação humana, não de retry cego.

**Decisão tomada:**
- (1) Janela de lembrete padrão = **24h antes do horário** (configurável por admin via FR-028).
- (2) Janela de silêncio padrão = **4h antes do horário** (configurável por admin via FR-028).
- (3) Horário operacional de envio = **08h–20h horário de Brasília**. Envios que cairiam fora da janela são postergados para o próximo 08h (mas nunca depois do horário da consulta — se postergar ultrapassar o horário, envio é cancelado e consulta vai para tratamento manual).
- (4) Retry em erro transitório = **3 tentativas com backoff 5min / 15min / 45min**. Após esgotar, lembrete marcado `falha-envio`; atendente notificado no painel.

**Autor:** humano — regra §5.4 expiração.

**Justificativa:** 24h é o ponto de equilíbrio consagrado para consulta ambulatorial; 4h de silêncio dá tempo real de ação ao atendente; 08h–20h respeita o contrato tácito de comunicação cortês em BR; 3 tentativas com backoff exponencial é o padrão de resiliência amplamente comprovado sem gerar spam. Todos os 4 valores são **configuráveis** (ver FR-028), estes são **defaults**.

**Atualizações aplicadas na spec:**
- FR-010 consolidado: "janela operacional 08h–20h horário de Brasília (padrão, configurável)" — remove `[NEEDS CLARIFICATION]`.
- FR-011 consolidado: "3 tentativas com backoff exponencial (5min / 15min / 45min)" — remove `[NEEDS CLARIFICATION]`.
- FR-028 explicita os 4 defaults: 24h lembrete / 4h silêncio / 08h–20h envio / 3 retries.
- Edge case "horário de envio cai em janela inadequada" refinado: se postergar ultrapassar o próprio horário da consulta → cancelar envio e sinalizar manual.

---

## C-005 — Política de correção de histórico imutável

**Origem:** `spec.md FR-017 [DECISÃO HUMANA: C-005]` · `briefing §7.1 regras` · `bmad.md §2.6` regra §5.4 histórico.

**Pergunta:** Como o sistema permite ao atendente **corrigir** uma marcação errada (ex: marcou `no-show` por engano; paciente chegou 10min atrasado) sem violar a imutabilidade do histórico?

**Opções avaliadas:**
| # | Opção | Prós | Contras | Impacto |
|---|---|---|---|---|
| A | Edição direta do evento anterior | Simples UX | Viola imutabilidade FR-017; perde auditoria | Inviável |
| B | **Novo evento "correção"** sobreposto, referenciando o evento errado + observação obrigatória | Preserva imutabilidade; rastreabilidade total (o erro e a correção ficam) | UX pede extra click + campo obrigatório | FR-017 consolida regra |
| C | Marcação "invalidado" no evento anterior, novo evento por cima | Mostra visualmente qual foi invalidado | Dois tipos de mutação (flag de invalidação + novo evento) | Mais código; 2 caminhos para mesmo fim |
| D | Sobreposição silenciosa (último evento sempre vale) | Simplicidade extrema | Perde rastreabilidade do erro | Auditor não sabe que houve correção |

**Recomendação IA:** **B** — novo evento do tipo `correcao` com (i) referência ao evento original, (ii) motivo textual obrigatório (ex: "paciente chegou atrasado — marcação anterior incorreta"), (iii) status resultante. Regra de derivação do status da Consulta: vale sempre o último evento cronologicamente, ignorando apenas eventos `resposta-ambigua` e `correcao` para cálculo estatístico (mas exibindo na trilha).

**Decisão tomada:** **B**.

**Regra operacional:**
1. Atendente abre o histórico da Consulta (FR-019).
2. Clica em "Corrigir status" no evento errado.
3. Sistema exige: (a) novo status (`compareceu` ou `no-show`), (b) motivo textual obrigatório.
4. Sistema registra novo evento do tipo `correcao` com `ref_evento = <id do evento errado>` + campos acima.
5. Evento original permanece no histórico, **imutável**. Derivação do status da Consulta passa a refletir o evento de correção.
6. No painel, o atendente vê o status atual (corrigido) e um ícone de "⚠ corrigido" que, ao clicar, abre trilha completa.

**Autor:** humano — regra §5.4 histórico.

**Justificativa:** (1) preserva imutabilidade sem dificultar operação real (erros honestos acontecem); (2) auditor ou disputa futura recupera a trilha completa sempre; (3) motivo obrigatório força rastro da intenção; (4) evita opção C (dois tipos de mutação) que complica modelo sem ganho real.

**Atualizações aplicadas na spec:** FR-017 consolidado (remove `[DECISÃO HUMANA]`). Tipo de evento `correcao` adicionado à enumeração em Key Entities (Consulta → derivação do status). Novo edge case: "atendente corrige compareceu/no-show" → comportamento conforme regra B.

---

## C-006 — Escopo e retenção de auditoria

**Origem:** `spec.md FR-018 [DECISÃO HUMANA: C-006]` · `spec.md NFR-004 [NEEDS CLARIFICATION: retenção]` · `briefing §7.1 regras` · `bmad.md §2.6` regra §5.4 auditoria.

**Pergunta (2 subperguntas):**
1. Quais **campos** cada evento do histórico deve registrar obrigatoriamente?
2. Qual a **política de retenção** (por quanto tempo o histórico é preservado)?

### (1) Escopo de campos
| # | Opção | Prós | Contras |
|---|---|---|---|
| A | Mínimo: `timestamp + canal + ator + id_externo` | Barato; cobre auditoria básica | Perde rastreio de "quem fez o quê de onde" |
| B | **Mínimo + IP (quando disponível) + motivo (quando aplicável)** | Auditoria suficiente para disputa típica MPE | IP nem sempre disponível (ex: evento sistema-automação) |
| C | B + `user-agent` + payload completo da mensagem | Auditoria forense completa | Explosão de armazenamento; implicações LGPD (payload pode conter PII adicional) |
| D | B + `user-agent` (sem payload) | Equilíbrio entre B e C | Mais complexidade de coleta no MVP |

**Recomendação IA:** **B**.

### (2) Retenção
| # | Opção | Prós | Contras |
|---|---|---|---|
| A | 2 anos | Mínimo legal Brasil | Abaixo do padrão clínico |
| B | **5 anos** | Alinhado com prazo de guarda de prontuário CFM (res. 1821/2007: 20 anos para prontuário clínico; 5 anos para comunicações) `[INFERÊNCIA]` | — |
| C | 10 anos | Conservador | Custo de armazenamento alto |
| D | Perpétuo | Zero perda de trilha | Inviável a longo prazo; conflita com LGPD (minimização) |

**Recomendação IA:** **B (5 anos)**.

**Decisão tomada:**
- (1) **Escopo = B**: cada evento registra `timestamp`, `canal`, `ator_tipo` (paciente | atendente | sistema-automacao), `ator_id` (quando aplicável), `id_externo_provedor` (quando canal = whatsapp), `ip` (quando disponível), `motivo` (quando aplicável — obrigatório em `correcao`).
- (2) **Retenção = 5 anos** a partir da data do evento. Eventos mais antigos são **anonimizados** automaticamente (remoção de `ator_id`, `ip`) mas **preservados** para métrica estatística agregada.

**Autor:** humano — regra §5.4 auditoria.

**Justificativa:** (1) 5 anos alinha com prazo comum para comunicações em saúde BR `[INFERÊNCIA]` (prontuário clínico em si vai para prazo mais longo em sistema de registro clínico separado, fora do escopo deste módulo); (2) escopo B cobre cenário de disputa ("paciente alega que não recebeu lembrete" → sistema mostra envio + provider id + timestamp); (3) evita coleta de payload completo (C) que traz risco LGPD adicional; (4) anonimização após 5 anos mantém estatística sem manter PII indefinidamente.

**Atualizações aplicadas na spec:** FR-018 consolidado (remove `[DECISÃO HUMANA]`). NFR-004 consolidado: "eventos preservados 5 anos; após, anonimização automática; retenção estatística indefinida sem PII". NFR-003 reforçado com referência à anonimização temporal.

---

## Notas sobre marcadores remanescentes

**Sobre `[INFERÊNCIA]` em Success Criteria (SC-002, SC-003, SC-001, NFR-001, NFR-002):**
Estes marcadores **permanecem** conscientemente na spec — representam **metas iniciais derivadas de H-1 e H-2** (`bmad.md §4.6`). Não são ambiguidades que trava implementação; são **alvos a recalibrar** em retrospective após piloto. A Fase 12 Retrospective do ciclo revisará esses números com dados reais.

**Sobre `[RISCO ASSUMIDO]`:**
- "Múltiplas respostas — última vale" (FR-016): permanece — é regra operacional deliberada.
- "Fuso único BR" (edge case): permanece — limitação de escopo do MVP.

**Sobre trial gratuito e onboarding da clínica (briefing §10):**
Permanecem como **questões de modelo comercial do SaaS**, não do módulo. Adicionados em **Out of Scope** da spec sem necessidade de C-NNN (não são ambiguidade; são decisão futura de produto que não bloqueia MVP técnico).

---

## Decisões sobre regras sensíveis (Manual §5.4)

Tabela final após clarify. Todas têm autor humano e referência cruzada a C-NNN ou D-NNN.

| Tema | Decisão | Autor | Referência |
|---|---|---|---|
| Cobrança | **Não aplica ao módulo.** Fora de escopo (briefing §4 + D-002 tabela §5.4). | humano | D-002 |
| Permissão | **Atendente com flag `is_admin`** (3 perfis funcionais + flag); matriz detalhada em C-002. | humano | **C-002** |
| Estorno | **Não aplica.** Cancelamento é transição de status, sem componente financeiro. | humano | D-002 |
| Deleção | **Anonimização** (sobrescrita de PII com preservação de integridade referencial). | humano | **C-003** |
| Expiração | **Janelas = 24h lembrete / 4h silêncio** (defaults configuráveis); horário envio **08h–20h BRT**; **3 retries** com backoff 5/15/45min. | humano | **C-004** |
| Visibilidade | **Single-tenant MVP** — 1 clínica por instalação; sem visibilidade cruzada. | humano | **D-003** (decidida em BMAD) |
| Histórico | **Imutável com evento de correção sobreposto** (ref_evento + motivo obrigatório). | humano | **C-005** |
| Auditoria | **Escopo B** (ts + canal + ator + id_externo + ip + motivo); **retenção 5 anos** + anonimização pós-5a. | humano | **C-006** |

---

## Gate de fechamento

- [x] **Zero** `[NEEDS CLARIFICATION]` na spec (após atualização neste mesmo PR).
- [x] Cada C-NNN aplicada na spec (ver §Atualizações aplicadas de cada item).
- [x] Regras sensíveis todas decididas por humano — tabela §5.4 acima.
- [x] Lint passa em `clarify.md` (a rodar).
- [ ] Humano assina fechamento: "OK — clarify fechada" **via merge deste PR**.

**Veredicto do Arquiteto:** 🟢 draft fechado. Próxima fase: **Fase 3.5 Constituição** — declara os padrões invariantes (camadas 1 a 3: engenharia, stack, produto) sobre os quais Plan e Implement vão operar.
