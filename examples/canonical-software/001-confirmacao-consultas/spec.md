---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer:
  - "User Scenarios & Testing *(mandatory)*"
  - "Requirements *(mandatory)*"
  - "Success Criteria *(mandatory)*"
  - "Out of Scope"
---

# Feature Specification: Confirmação de Consultas (Canônico 001)

**Feature Branch:** `001-confirmacao-consultas`
**Created:** 2026-04-18
**Status:** Draft
**Input:** User description: "Sistema web para clínicas MPE confirmarem consultas via WhatsApp, reduzindo no-show."
**Referências:** `briefing.md` v1 · `bmad.md` v1 · `decision_log.md` D-001/D-002/D-003

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Lembrete automático pelo WhatsApp e confirmação por botão (Priority: P1)

A partir do horário da consulta e da janela configurada pela clínica, o sistema envia ao paciente uma mensagem no WhatsApp com os dados da consulta e três botões interativos (Confirmar / Cancelar / Reagendar). Ao tocar em um deles, o paciente atualiza o status da consulta imediatamente; o evento fica registrado no histórico imutável.

**Why this priority:** esta é a operação que entrega a promessa principal do módulo — substituir a ligação manual do atendente pela confirmação explícita do paciente com o menor atrito possível. Sem esta user story, o sistema não resolve a causa-raiz do no-show identificada em `bmad.md §1.3`.

**Independent Test:** com um paciente de teste cadastrado e uma consulta marcada há mais do que a janela de lembrete, acionar o disparo do lembrete (manualmente ou aguardando o job); verificar que a mensagem chega ao WhatsApp do paciente com os três botões; tocar em "Confirmar" e verificar que o status da consulta passou a `confirmada` e que há um evento correspondente no histórico.

**Acceptance Scenarios:**
1. **Given** consulta agendada com antecedência ≥ janela configurada e paciente com número WhatsApp válido, **When** o horário de envio chega, **Then** o sistema envia uma mensagem pelo WhatsApp com identificação da clínica, do médico, data e hora da consulta, e três botões (Confirmar / Cancelar / Reagendar).
2. **Given** paciente recebeu o lembrete e consulta está em status `lembrete-enviado`, **When** o paciente toca em "Confirmar", **Then** o status da consulta passa a `confirmada`, um evento de confirmação é registrado no histórico com timestamp + canal WhatsApp + ator paciente + identificador externo da mensagem, e o painel do atendente reflete o novo status.
3. **Given** paciente recebeu o lembrete, **When** o paciente toca em "Cancelar", **Then** o status da consulta passa a `cancelada-pelo-paciente`, o evento é registrado no histórico e o slot fica disponível para reagendamento pelo atendente.
4. **Given** paciente recebeu o lembrete, **When** o paciente toca em "Reagendar", **Then** o status da consulta passa a `reagendamento-solicitado`, o evento é registrado no histórico e o atendente recebe indicação visual no painel para tratar a solicitação.

---

### User Story 2 — Painel do dia do atendente com status consolidado (Priority: P2)

O atendente abre o painel da clínica e vê a lista de consultas de hoje e amanhã com o status de confirmação de cada uma (`agendada`, `lembrete-enviado`, `confirmada`, `cancelada-pelo-paciente`, `reagendamento-solicitado`, `sem-resposta`). As consultas em `sem-resposta` ficam destacadas visualmente para ação imediata.

**Why this priority:** sem o painel consolidado, a automação da User Story 1 vira invisível para a clínica — o atendente não enxerga quem confirmou, quem não confirmou, quem pediu reagendamento. É a interface que transforma dado em ação operacional. P2 porque depende da P1 funcionar, mas pode ser entregue em um segundo release se necessário.

**Independent Test:** com pelo menos 4 consultas em status diferentes (`agendada`, `confirmada`, `cancelada-pelo-paciente`, `sem-resposta`), abrir o painel e verificar que todas aparecem com o status correto, que as de `sem-resposta` estão visualmente destacadas e que filtros por status funcionam.

**Acceptance Scenarios:**
1. **Given** existem consultas em estado misto (algumas confirmadas, outras sem-resposta, outras agendadas), **When** o atendente abre o painel do dia, **Then** o sistema exibe todas as consultas de hoje e amanhã com status consolidado, ordenadas por horário.
2. **Given** há consultas em `sem-resposta` no painel, **When** o atendente visualiza a lista, **Then** essas consultas recebem destaque visual diferenciado (cor/ícone/rótulo) para priorizar a ação humana.
3. **Given** atendente está no painel, **When** aplica filtro por status (ex: "só confirmadas"), **Then** a lista reduz para consultas que atendem ao filtro.

---

### User Story 3 — Intervenção manual do atendente (Priority: P3)

Quando paciente não responde ao lembrete, responde fora dos botões (texto livre), não tem WhatsApp ativo, ou responde por outro canal (telefone, presencial), o atendente consegue **confirmar em nome do paciente**, **registrar cancelamento** ou **reagendar** diretamente pela tela. Cada intervenção manual vira um evento no histórico com o ator identificado como o atendente.

**Why this priority:** é o fallback humano que o Caminho D (`decision_log.md D-002`) depende para cobrir a parcela de pacientes sem WhatsApp ou em silêncio. Sem essa story, o sistema deixa slots em limbo — nem confirmados, nem liberados. P3 porque, para o primeiro release de validação, funciona mesmo que a intervenção manual seja feita "à mão" via suporte; mas pro MVP final precisa existir.

**Independent Test:** com uma consulta em status `sem-resposta`, usar a tela do atendente para confirmar manualmente em nome do paciente; verificar que o status virou `confirmada`, que o evento no histórico mostra ator = atendente (não paciente), e que o painel refletiu a mudança imediatamente.

**Acceptance Scenarios:**
1. **Given** consulta em `sem-resposta`, **When** o atendente clica "confirmar em nome do paciente", **Then** o status passa a `confirmada`, o histórico registra um evento com ator = atendente (e não paciente) e canal = manual-pelo-painel.
2. **Given** paciente ligou na clínica para cancelar, **When** o atendente registra o cancelamento manual, **Then** o status passa a `cancelada-pelo-paciente` (intenção do paciente), o histórico registra ator = atendente com canal = manual-pelo-painel e observação livre opcional.
3. **Given** paciente pediu reagendamento por telefone, **When** o atendente cria uma nova consulta para a data sugerida e marca a original como reagendada, **Then** o histórico da original registra `reagendamento-efetivado` com referência à nova consulta criada.
4. **Given** consulta qualquer (qualquer status), **When** o atendente abre o histórico da consulta, **Then** o sistema exibe linha do tempo completa de eventos (lembrete enviado, respostas recebidas, mudanças de status, intervenções manuais) em ordem cronológica com timestamp, canal, ator e identificador externo quando houver.

---

### User Story 4 — Configuração da janela de lembrete pelo admin da clínica (Priority: P4)

O admin da clínica acessa uma tela de configuração e define a janela de antecedência do envio do lembrete (ex: 24h, 48h, 72h antes do horário da consulta) e a janela de silêncio (ex: 4h antes do horário a consulta sem resposta passa a `sem-resposta`). Essas configurações aplicam-se a todas as consultas futuras.

**Why this priority:** P4 porque o MVP pode sair com janelas **fixas** (ex: 24h + 4h hardcoded) e ainda entregar valor. Tornar configurável é melhoria incremental. Mas é quase-obrigatório antes do 2º cliente, já que a janela ideal varia por especialidade (dentista aceita 24h; cirurgia pede 72h `[INFERÊNCIA]`).

**Independent Test:** com o perfil admin, alterar a janela de lembrete de 24h para 48h; criar uma nova consulta e verificar que o próximo lembrete é agendado para 48h antes do horário, não 24h.

**Acceptance Scenarios:**
1. **Given** admin está autenticado e na tela de configuração, **When** define janela de lembrete = 48h e janela de silêncio = 6h, **Then** o sistema persiste a configuração e confirma visualmente.
2. **Given** configuração foi alterada para janela 48h, **When** uma nova consulta é criada, **Then** o job de envio é agendado para 48h antes do horário da consulta.
3. **Given** configuração alterada **depois** de uma consulta já ter sido criada e do job já estar agendado com a janela antiga, **Then** a consulta mantém a janela com a qual foi criada (evita reagendamento em massa).

---

### Edge Cases

- **Provedor de WhatsApp indisponível** no momento do envio → sistema enfileira retry com backoff exponencial; se falhar após N tentativas, marca lembrete como `falha-envio` e alerta o atendente no painel.
- **Número de WhatsApp do paciente inválido ou inexistente** → provedor retorna erro definitivo; sistema marca lembrete como `numero-invalido`, não retenta, e destaca no painel para ação manual.
- **Template de mensagem reprovado/suspenso pelo provedor** → sistema marca erro sistêmico e alerta admin da clínica (não deve ser escondido do operador — requer troca de template ou de provedor).
- **Paciente responde com texto livre fora dos botões** (ex: "amanhã posso sim") → sistema registra a resposta no histórico como `resposta-ambigua`, NÃO interpreta a intenção, e destaca no painel para o atendente decidir manualmente.
- **Paciente envia múltiplas respostas** (toca "Confirmar" e depois "Cancelar") → sistema aceita a **última resposta** dentro da janela válida, registra ambos os eventos no histórico (decisão irreversível após virar `compareceu` / `no-show` no dia).
- **Idempotência de disparo duplicado** (job acionado duas vezes por bug) → sistema verifica se já existe lembrete `enviado` para a mesma consulta na mesma janela e NÃO envia duplicado.
- **Consulta é cancelada pelo atendente depois do lembrete já ter sido enviado** → sistema envia mensagem de cancelamento ao paciente informando que não precisa mais responder.
- **Admin altera janela no meio do dia** → ver Acceptance Scenario 3 da User Story 4; consultas com job já agendado mantêm a janela original.
- **Paciente sem WhatsApp ativo** (descoberto só ao tentar enviar ou depois) → fallback é o fluxo manual do atendente (User Story 3); sistema sinaliza `sem-canal` na consulta para o atendente atuar.
- **Paciente pede deleção LGPD** (art. 18) → `[DECISÃO HUMANA: política de deleção]` — comportamento exato depende de Clarify C-003. Em alta: histórico preservado com dados de identificação anonimizados; preservar ou não o histórico de eventos é regra sensível.
- **Horário de envio cai em janela inadequada** (ex: 3h da manhã) → sistema respeita janela operacional permitida (ex: 8h–20h BR) e posterga o envio para a próxima janela válida. **Janela operacional exata** `[NEEDS CLARIFICATION: C-004]`.
- **Fuso horário do paciente difere do fuso da clínica** — fora do MVP; assume-se fuso único (BR). `[RISCO ASSUMIDO]` conforme `decision_log.md D-003`.

---

## Requirements *(mandatory)*

Cada FR abaixo tem **origem rastreável** — `D-NNN` do `decision_log.md` ou seção específica do briefing.

### Functional Requirements

#### Scaffolding mínimo (Cadastro e Agendamento)

- **FR-001:** System MUST allow an authenticated Atendente to cadastrar um Paciente com dados mínimos (nome, número de WhatsApp, e-mail opcional). — *origem: briefing §7.2*
- **FR-002:** System MUST allow an authenticated Atendente to cadastrar um Médico com dados mínimos (nome, especialidade). — *origem: briefing §7.2*
- **FR-003:** System MUST allow an authenticated Atendente to criar uma Consulta vinculada a um Paciente e um Médico em uma data/hora específica. — *origem: briefing §7.3 · bmad.md §2.2 passo 1*
- **FR-004:** System MUST allow an authenticated Atendente to editar (alterar data/hora ou médico) ou cancelar uma Consulta existente. — *origem: briefing §7.3*
- **FR-005:** System MUST reject the creation of a Consulta with invalid data (paciente ou médico inexistente; data/hora no passado) com mensagem de erro clara. — *origem: briefing §7.3 + edge case de validação*

#### Disparo de lembrete automático (User Story 1)

- **FR-006:** System MUST enfileirar automaticamente, no momento da criação de uma Consulta, um disparo de lembrete para ocorrer na janela configurada antes do horário da Consulta. — *origem: briefing §7.1 ações 1 · bmad.md §2.2 passo 2 · D-002*
- **FR-007:** System MUST send the lembrete via WhatsApp as the single automatic channel; nenhum outro canal automático é usado no MVP. — *origem: briefing §6 + §9 · D-002*
- **FR-008:** System MUST include, in each lembrete, identificação da clínica, identificação do médico, data e hora da consulta, e três botões interativos rotulados "Confirmar", "Cancelar", "Reagendar". — *origem: briefing §7.1 ações · bmad.md §2.2 passo 2*
- **FR-009:** System MUST garantir idempotência do disparo: no máximo um lembrete enviado por Consulta por janela. Acionamentos duplicados do job NÃO devem gerar mensagem duplicada para o paciente. — *origem: briefing §7.1 regras + edge case*
- **FR-010:** System MUST respect a janela operacional de envio (horário do dia permitido) — envios que cairiam fora da janela são postergados para a próxima janela válida. A **janela operacional exata** [NEEDS CLARIFICATION: C-004] — padrão sugerido 08h–20h BR `[INFERÊNCIA]`. — *origem: briefing §7.1 regras · bmad.md §2.4*
- **FR-011:** System MUST retry envios falhados por erro transitório do provedor com backoff exponencial, até um limite máximo de tentativas `[NEEDS CLARIFICATION: limite de tentativas e intervalo — C-004 estendido]`, e marcar como `falha-envio` após o limite. — *origem: edge case + bmad.md §2.4*
- **FR-012:** System MUST NÃO retentar envios falhados por erro definitivo (número inválido); tais envios são marcados imediatamente como `numero-invalido` e visibilizados no painel para ação manual. — *origem: edge case*

#### Processamento de resposta (User Story 1)

- **FR-013:** System MUST receive the paciente's response to the lembrete pelo WhatsApp por callback do provedor e reconciliar com a Consulta correspondente usando o identificador externo da mensagem. — *origem: briefing §7.1 · bmad.md §2.3 Notificação*
- **FR-014:** System MUST update the Consulta status to `confirmada` when the paciente taps "Confirmar"; to `cancelada-pelo-paciente` when taps "Cancelar"; to `reagendamento-solicitado` when taps "Reagendar". — *origem: briefing §7.1 + §8 passos 4–5*
- **FR-015:** System MUST treat text-only responses (fora dos botões) as `resposta-ambigua`, NÃO inferir intenção, e destacar a Consulta no painel para o atendente resolver manualmente. — *origem: edge case · bmad.md §2.4*
- **FR-016:** System MUST accept múltiplas respostas do paciente dentro da janela e aplicar sempre a **última** resposta recebida; todas as respostas ficam registradas no histórico. — *origem: edge case*

#### Histórico e auditoria (regra §5.4)

- **FR-017:** [DECISÃO HUMANA: histórico imutável — C-005] System MUST registrar todo evento relevante (lembrete agendado, enviado, falhou, resposta recebida, mudança de status, intervenção manual) em um histórico **imutável**. Correções legítimas (ex: atendente marcou no-show por engano) são registradas como **novo evento sobreposto**, sem editar eventos anteriores. — *origem: briefing §7.1 regras + §5.4 · bmad.md §2.6*
- **FR-018:** [DECISÃO HUMANA: escopo de auditoria — C-006] Each evento registrado MUST conter, no mínimo: timestamp, canal (`whatsapp` | `manual-pelo-painel` | `sistema-automacao`), ator identificado (paciente/atendente/sistema), identificador externo do provedor quando aplicável. Campos adicionais (IP, user-agent) ficam para Clarify. — *origem: briefing §7.1 regras + §5.4 · bmad.md §2.6*
- **FR-019:** System MUST allow an Atendente to consult the complete histórico de uma Consulta em ordem cronológica. — *origem: briefing §7.1 ações · User Story 3 scenario 4*

#### Painel do atendente (User Story 2)

- **FR-020:** System MUST exibir ao Atendente um painel listando Consultas de hoje e amanhã com status consolidado, ordenadas por horário. — *origem: briefing §7.1 ações · §8 passo 6*
- **FR-021:** System MUST destacar visualmente (cor, ícone ou rótulo diferenciado) Consultas em estado `sem-resposta` no painel do Atendente. — *origem: User Story 2 scenario 2 · briefing §7.1 ações*
- **FR-022:** System MUST permitir filtro do painel por status de Consulta (ex: `confirmada`, `sem-resposta`). — *origem: User Story 2 scenario 3*

#### Intervenção manual do atendente (User Story 3)

- **FR-023:** System MUST permitir ao Atendente confirmar uma Consulta **em nome do paciente** diretamente pela tela; o evento resultante é registrado com ator = atendente e canal = manual-pelo-painel (distinto de confirmação direta do paciente). — *origem: briefing §7.1 ações · User Story 3 scenario 1*
- **FR-024:** System MUST permitir ao Atendente registrar manualmente cancelamento ou solicitação de reagendamento em nome do paciente, seguindo a mesma regra de rastreabilidade do FR-023. — *origem: briefing §7.1 ações · User Story 3 scenarios 2–3*
- **FR-025:** System MUST notificar o paciente pelo WhatsApp (quando possível) quando a Consulta for cancelada pelo Atendente **depois** do lembrete já ter sido enviado, para evitar que o paciente compareça desnecessariamente. — *origem: edge case*

#### Marcação de presença / no-show

- **FR-026:** System MUST permitir ao Atendente (e opcionalmente ao Médico) marcar uma Consulta como `compareceu` ou `no-show` após o horário. — *origem: briefing §7.1 ações · §8 passo 8*
- **FR-027:** System MUST prevenir marcação de `compareceu`/`no-show` antes do horário da Consulta (exceto para correção em estados terminais com justificativa). — *origem: inferência lógica de negócio · `[INFERÊNCIA]`*

#### Configuração (User Story 4)

- **FR-028:** System MUST permitir a um Admin da clínica definir a janela de envio de lembrete (em horas antes do horário da Consulta) e a janela de silêncio (em horas antes do horário a partir da qual o status muda para `sem-resposta`). — *origem: briefing §7.1 regras · §5 perfil Admin · User Story 4*
- **FR-029:** System MUST NÃO aplicar alterações de configuração a Consultas já criadas com o lembrete já agendado; a configuração vigente é a do **momento da criação** da Consulta. — *origem: User Story 4 scenario 3 · edge case*

#### Permissões e autenticação

- **FR-030:** [DECISÃO HUMANA: matriz de permissões — C-002] System MUST autenticar Atendente, Médico e Admin antes de qualquer ação no painel web. **Paciente NÃO loga** — interage apenas via WhatsApp. — *origem: briefing §5 + §9*
- **FR-031:** [DECISÃO HUMANA: matriz de permissões — C-002] System MUST aplicar a matriz de permissões por perfil: Atendente opera o dia a dia (cadastros, consultas, intervenções manuais); Médico tem acesso somente-leitura à sua própria agenda (+ marcação de no-show opcional); Admin pode configurar janelas e cadastrar médicos. — *origem: briefing §5*
- **FR-032:** [DECISÃO HUMANA: visibilidade — D-003] System MUST garantir isolamento total por clínica (MVP single-tenant — 1 clínica por instalação); não há consulta ou paciente visível entre clínicas diferentes. — *origem: decision_log D-003 · briefing §5 + §9*

#### LGPD e privacidade

- **FR-033:** [DECISÃO HUMANA: política de deleção — C-003] System MUST atender solicitação de deleção de Paciente conforme LGPD art. 18, preservando o histórico imutável de Consultas por meio de **anonimização** dos dados de identificação (nome, número de WhatsApp, e-mail). Comportamento exato `[NEEDS CLARIFICATION: C-003]`. — *origem: briefing §9 + §10 · bmad.md §2.6*
- **FR-034:** System MUST NÃO expor dados de Paciente, Médico ou Consulta a usuários não autenticados (exceto via link seguro enviado no próprio WhatsApp do paciente, restrito à consulta dele). — *origem: briefing §5 perfil Paciente + §9*

### Non-Functional Requirements

- **NFR-001 — Performance.** O processamento de resposta do paciente (do recebimento pelo provedor até a atualização do status visível no painel) MUST ser concluído em mediano < 10 segundos no perfil de carga MPE típico (< 100 consultas/dia por clínica). `[INFERÊNCIA]` — alvo validável em piloto.
- **NFR-002 — Disponibilidade do envio.** O sistema MUST processar, com sucesso, ≥ 99% dos disparos de lembrete agendados dentro da janela operacional válida (excluindo erros definitivos de número inválido). `[INFERÊNCIA]` — alvo a validar em piloto.
- **NFR-003 — Privacidade.** Dados sensíveis do Paciente (número de WhatsApp, nome, e-mail) MUST ser armazenados com criptografia-em-repouso. Logs operacionais NÃO devem conter payload completo de mensagens nem número de WhatsApp em claro.
- **NFR-004 — Auditoria.** Eventos do histórico imutável (FR-017/FR-018) MUST ser persistidos e recuperáveis por no mínimo 5 anos `[NEEDS CLARIFICATION: retenção — C-006]` — alinhado com boas práticas de registro clínico brasileiro.
- **NFR-005 — Responsividade.** O painel do atendente MUST ser utilizável em telas de 13"+ (desktop/notebook) e em celulares 5.5"+ para consulta rápida pelo médico.
- **NFR-006 — Idempotência de integração.** Callbacks duplicados do provedor de WhatsApp (mesma mensagem processada 2x) MUST ser detectados e descartados sem gerar eventos duplicados.

### Key Entities

- **Consulta** — unidade central do módulo. Representa o compromisso de um Paciente com um Médico em uma data/hora. Possui um status enumerado (`agendada`, `lembrete-enviado`, `confirmada`, `cancelada-pelo-paciente`, `cancelada-pela-clinica`, `reagendamento-solicitado`, `reagendada`, `sem-resposta`, `compareceu`, `no-show`, `falha-envio`, `numero-invalido`). Derivação do status vem do último evento aplicável registrado no histórico.
- **Paciente** — quem agendou a consulta. Atributos-chave no MVP: nome, número de WhatsApp, e-mail opcional. Não tem credenciais no sistema (não loga).
- **Médico** — profissional que atende. Atributos-chave: nome, especialidade. Pode ter múltiplas consultas no mesmo dia.
- **Lembrete** — evento de intenção de envio. Agendado no momento da criação da Consulta; evolui entre `agendado`, `enviado`, `falha-envio` (retry) ou `numero-invalido` (terminal).
- **Confirmação** — evento de resposta do paciente ou do atendente. Registra intenção explícita sobre a Consulta (confirmar / cancelar / reagendar). Um único Lembrete pode gerar múltiplas Confirmações; vale a última dentro da janela.
- **Notificação** — tentativa concreta de entrega pela integração externa (provedor de WhatsApp). Herdeira do Lembrete; persiste retornos do provedor (`delivered`, `read`, `failed`) para reconciliação e métrica.
- **Configuração da Clínica** — entidade única no MVP (por D-003 single-tenant). Armazena janela de lembrete (horas antes) e janela de silêncio (horas antes).

### Permissões

| Perfil | Ações permitidas | Ações bloqueadas |
|---|---|---|
| **Atendente** | Cadastrar/editar paciente e médico · criar/editar/cancelar consulta · confirmar/reagendar/cancelar em nome do paciente · marcar compareceu/no-show · ver painel do dia · consultar histórico de qualquer consulta da clínica · receber alerta de "sem resposta". | Configurar janelas de lembrete (exclusivo Admin) `[DECISÃO HUMANA: C-002]` · ver/operar dados de outras clínicas (bloqueado por D-003). |
| **Médico** | Ver própria agenda do dia · ver status de confirmação das próprias consultas · marcar compareceu/no-show nas próprias consultas. | Criar/editar/cancelar consultas de terceiros · operar painel operacional de outras clínicas · editar cadastro de paciente. |
| **Paciente** | Responder ao lembrete via botões do WhatsApp · consultar detalhes da própria consulta via link seguro recebido. | Criar consulta · ver consultas de outros pacientes · logar no painel. |
| **Admin da clínica** | Tudo que o Atendente pode `[DECISÃO HUMANA: C-002 — Admin = Atendente + config, ou papel separado?]` · configurar janela de lembrete e janela de silêncio · cadastrar médicos (se diferenciar de Atendente). | Acessar dados de outras clínicas (bloqueado por D-003). |

### Estados de erro previsíveis

| Erro | Mensagem ao usuário / Comportamento | Log |
|---|---|---|
| Provedor WhatsApp indisponível (erro transitório) | "Lembrete em nova tentativa" no painel; retry automático conforme FR-011 | evento `falha-envio` com tentativa N |
| Número de WhatsApp inválido (erro definitivo) | "Paciente sem WhatsApp válido — confirmar manualmente" destacado no painel | evento `numero-invalido`, canal `whatsapp`, resposta do provedor |
| Template de mensagem reprovado | "Sistema temporariamente não consegue enviar lembretes — suporte avisado" no painel; envio bloqueado global | evento de erro sistêmico; alerta para admin técnico |
| Paciente respondeu texto livre (não botão) | "Resposta recebida — revisar manualmente" na consulta | evento `resposta-ambigua` com texto preservado |
| Callback duplicado do provedor | sem efeito para o usuário | evento descartado por idempotência (FR-019 / NFR-006) |
| Consulta não existe (ID inválido) | "Consulta não encontrada" | log de acesso negado |
| Ação permitida apenas ao Admin executada por Atendente | "Você não tem permissão para esta ação" | log de autorização negada |
| Tentativa de editar histórico imutável | "Histórico não pode ser editado — use correção por novo evento" | log de violação + bloqueio (FR-017) |

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001 — Cobertura de envio.** ≥ 98% das Consultas elegíveis (número de WhatsApp válido + dentro da janela operacional) têm lembrete enviado com sucesso dentro de 15 minutos do horário planejado.
- **SC-002 — Confirmação explícita.** ≥ 70% das Consultas elegíveis recebem confirmação explícita (botão Confirmar/Cancelar/Reagendar) do paciente antes da janela de silêncio `[INFERÊNCIA]` — meta derivada da hipótese H-1 de `bmad.md §4.6`; ajustar após piloto.
- **SC-003 — Redução de no-show.** Taxa de no-show cai de baseline da clínica (tipicamente 20–40% `[INFERÊNCIA]`) para < 10% em até 30 dias de operação com o sistema — meta derivada da hipótese H-2 de `bmad.md §4.6`.
- **SC-004 — Tempo de resposta do paciente.** Mediana do tempo entre envio do lembrete e primeira resposta do paciente < 4 horas.
- **SC-005 — Reconciliação.** 100% das respostas válidas recebidas (botão) são reconciliadas com a Consulta correta e refletidas no painel do Atendente em < 10 segundos (NFR-001).
- **SC-006 — Disponibilidade do envio.** ≥ 99% dos disparos agendados são processados com sucesso dentro da janela operacional (NFR-002).
- **SC-007 — Intervenção manual efetiva.** 100% das Consultas em `sem-resposta` a < 4h do horário aparecem destacadas no painel do Atendente com ação imediata disponível (confirmar manual / cancelar / reagendar).
- **SC-008 — Auditoria completa.** 100% dos eventos relevantes (envios, respostas, mudanças de status, intervenções manuais) geram registro no histórico imutável com timestamp + canal + ator identificados.

---

## Out of Scope

Referência cruzada com `briefing.md §9`. Nesta spec:

- **Canais além de WhatsApp** — sem SMS, sem e-mail automatizado, sem ligação por robocall. Segundo canal só é considerado após validação em produção (critério de invalidação de D-002).
- **Multi-unidade / multi-clínica** — MVP single-tenant (D-003). Redes com várias clínicas e visibilidade cruzada ficam para ciclo futuro.
- **Pagamento de consulta** — módulo separado. Spec não trata faturamento, cobrança ou estorno.
- **Dashboard analítico avançado** — métricas agregadas por médico/especialidade/dia/hora ficam para ciclo futuro. MVP tem o painel do dia + contadores básicos.
- **Painel do paciente** — paciente não tem acesso a painel web. Interação se esgota no WhatsApp + link seguro opcional.
- **Integração com agenda externa** (Google, Outlook, iCal) — fora do MVP.
- **App mobile nativo** — fora do MVP. Painel responsivo cobre o uso do médico em celular.
- **Encaixe, overbooking, bloqueios por feriado/ausência** — Agendamento é scaffolding mínimo; funcionalidades avançadas fora do MVP.
- **Prontuário, histórico clínico, integração com plano de saúde** — fora do escopo do módulo.
- **Fuso horário múltiplo** — MVP assume fuso único BR.
- **Onboarding autônomo da clínica** — assinatura e setup envolvem contato humano `[NEEDS CLARIFICATION]`; automação de onboarding fora do MVP.

---

## Gate de saída da Fase 2 (`fases/02_SPEC.md` + `checklists/qualidade-spec.md`)

- [x] Todas as seções do template preenchidas (User Scenarios · Requirements · Success Criteria · Out of Scope).
- [x] Zero regras ditadas em modo "como fazer" — nenhum nome de framework/ORM/banco/SDK.
- [x] Cada FR é mensurável e verifica-se por cenário Given/When/Then (diretos das User Stories ou derivados).
- [x] Cada User Story tem prioridade P1..P4, Independent Test explícito e ≥ 1 cenário Given/When/Then.
- [x] Edge cases mapeados (12 itens) — cobrem provedor indisponível, número inválido, template reprovado, resposta texto livre, múltiplas respostas, idempotência, cancelamento tardio, alteração de config, paciente sem WhatsApp, deleção LGPD, janela operacional inadequada, fuso.
- [x] Permissões por papel descritas (4 perfis) com `[DECISÃO HUMANA: C-002]` nos pontos sensíveis.
- [x] Key Entities definidas (7 entidades) sem tipos técnicos.
- [x] Success Criteria mensuráveis (8 SC) tecnologia-agnósticos.
- [x] Out of Scope explícito (11 itens) cruzando com briefing §9.
- [x] **Rastreabilidade completa**: cada FR (001 a 034) referencia sua origem — `D-NNN` ou seção do briefing/bmad. Nenhum FR órfão.
- [x] **Coerência com decision_log**: nenhum FR contradiz D-001/D-002/D-003. Validação cruzada: FR-007 implementa D-002; FR-032 implementa D-003; FR-017/FR-018/FR-033 preservam regras §5.4 com `[DECISÃO HUMANA]` apontando para Clarify.
- [ ] **Validação humana** — pendente; ocorre via merge deste PR.

**Veredicto do Arquiteto:** 🟢 draft fechado. Próxima fase: Fase 3 Clarify (`clarify.md`) — resolve os 6 `[NEEDS CLARIFICATION]`/`[DECISÃO HUMANA]` e as ambiguidades pendentes (C-001 a C-006 + retenção + limite de retry + janela operacional) com decisão humana explícita para cada.
