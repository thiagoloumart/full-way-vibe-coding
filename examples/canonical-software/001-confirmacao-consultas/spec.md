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
**Status:** Pós-Clarify (v2 · 2026-04-18)
**Input:** User description: "Sistema web para clínicas MPE confirmarem consultas via WhatsApp, reduzindo no-show."
**Referências:** `briefing.md` v1 · `bmad.md` v1 · `decision_log.md` D-001/D-002/D-003 · `clarify.md` C-001 a C-006

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
- **Paciente pede deleção LGPD** (art. 18) → atendente com flag `is_admin` aciona "Anonimizar paciente"; sistema sobrescreve PII (nome vira `paciente-excluido-<hash>`, telefone e e-mail viram null) preservando integridade referencial de eventos do histórico (conforme FR-033 e C-003).
- **Horário de envio cai em janela inadequada** (ex: 3h da manhã) → sistema respeita janela operacional **08h–20h horário de Brasília** (configurável via FR-028) e posterga o envio para o próximo 08h; se o adiamento ultrapassar o próprio horário da consulta, envio é cancelado e a consulta é sinalizada no painel para ação manual (conforme FR-010 e C-004).
- **Atendente corrige compareceu/no-show marcado por engano** → sistema exige ator, motivo textual obrigatório e novo status; registra evento `correcao` com `ref_evento = <id original>`; evento original permanece no histórico (conforme FR-017 e C-005).
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
- **FR-010:** System MUST respect a janela operacional de envio **08h–20h horário de Brasília** (padrão; configurável por admin via FR-028). Envios que cairiam fora da janela são postergados para o próximo 08h; se o adiamento ultrapassar o horário da própria consulta, o envio é cancelado e a consulta é sinalizada no painel para ação manual do atendente. — *origem: briefing §7.1 regras · bmad.md §2.4 · C-004*
- **FR-011:** System MUST retry envios falhados por erro transitório do provedor com **backoff exponencial** — até **3 tentativas** nos intervalos **5 min / 15 min / 45 min**. Esgotadas as tentativas, o lembrete é marcado como `falha-envio` e o atendente recebe alerta no painel. — *origem: edge case + bmad.md §2.4 · C-004*
- **FR-012:** System MUST NÃO retentar envios falhados por erro definitivo (número inválido); tais envios são marcados imediatamente como `numero-invalido` e visibilizados no painel para ação manual. — *origem: edge case*

#### Processamento de resposta (User Story 1)

- **FR-013:** System MUST receive the paciente's response to the lembrete pelo WhatsApp por callback do provedor e reconciliar com a Consulta correspondente usando o identificador externo da mensagem. — *origem: briefing §7.1 · bmad.md §2.3 Notificação*
- **FR-014:** System MUST update the Consulta status to `confirmada` when the paciente taps "Confirmar"; to `cancelada-pelo-paciente` when taps "Cancelar"; to `reagendamento-solicitado` when taps "Reagendar". — *origem: briefing §7.1 + §8 passos 4–5*
- **FR-015:** System MUST treat text-only responses (fora dos botões) as `resposta-ambigua`, NÃO inferir intenção, e destacar a Consulta no painel para o atendente resolver manualmente. — *origem: edge case · bmad.md §2.4*
- **FR-016:** System MUST accept múltiplas respostas do paciente dentro da janela e aplicar sempre a **última** resposta recebida; todas as respostas ficam registradas no histórico. — *origem: edge case*

#### Histórico e auditoria (regra §5.4)

- **FR-017:** System MUST registrar todo evento relevante (lembrete agendado, enviado, falhou, resposta recebida, mudança de status, intervenção manual, correção) em um histórico **imutável**. Correções legítimas (ex: atendente marcou no-show por engano) são registradas como **novo evento do tipo `correcao`** com (a) referência ao `id` do evento corrigido, (b) novo status resultante, (c) motivo textual obrigatório. O evento original **nunca** é editado ou removido; a derivação do status da Consulta passa a refletir a correção. — *origem: briefing §7.1 regras + §5.4 · bmad.md §2.6 · C-005*
- **FR-018:** Each evento registrado MUST conter: `timestamp`, `canal` (`whatsapp` | `manual-pelo-painel` | `sistema-automacao`), `ator_tipo` (paciente | atendente | sistema-automacao), `ator_id` (quando aplicável), `id_externo_provedor` (quando canal = whatsapp), `ip` (quando disponível), `motivo` (obrigatório em eventos do tipo `correcao`; opcional nos demais). — *origem: briefing §7.1 regras + §5.4 · bmad.md §2.6 · C-006*
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

- **FR-028:** System MUST permitir a um atendente com flag `is_admin` definir: (a) janela de envio de lembrete (em horas antes do horário da Consulta) — **padrão 24h**; (b) janela de silêncio (em horas antes do horário a partir da qual o status muda para `sem-resposta`) — **padrão 4h**; (c) horário operacional de envio — **padrão 08h–20h BRT**; (d) política de retry — **padrão 3 tentativas com backoff 5/15/45 min**. — *origem: briefing §7.1 regras · §5 perfil Admin · User Story 4 · C-002 · C-004*
- **FR-029:** System MUST NÃO aplicar alterações de configuração a Consultas já criadas com o lembrete já agendado; a configuração vigente é a do **momento da criação** da Consulta. — *origem: User Story 4 scenario 3 · edge case*

#### Permissões e autenticação

- **FR-030:** System MUST autenticar **Atendente** (com ou sem flag `is_admin`) e **Médico** antes de qualquer ação no painel web. **Paciente NÃO loga** — interage apenas via WhatsApp ou via link seguro recebido nele. — *origem: briefing §5 + §9 · C-002*
- **FR-031:** System MUST aplicar a matriz de permissões por perfil conforme **C-002** (tabela §Permissões): (a) **Atendente** opera dia a dia (cadastros de paciente e consulta, intervenções manuais, marcação compareceu/no-show, consulta do histórico); (b) **Atendente com flag `is_admin`** adiciona cadastro de médicos e configuração de janelas (FR-028); (c) **Médico** tem acesso somente-leitura à sua própria agenda, com marcação opcional de compareceu/no-show nas próprias consultas; (d) **Paciente** responde via WhatsApp e consulta detalhes da própria consulta via link seguro. — *origem: briefing §5 · C-002*
- **FR-032:** System MUST garantir isolamento total por clínica (MVP single-tenant — 1 clínica por instalação); não há consulta ou paciente visível entre clínicas diferentes. — *origem: decision_log D-003 · briefing §5 + §9*

#### LGPD e privacidade

- **FR-033:** System MUST atender solicitação de deleção de Paciente conforme LGPD art. 18 via **anonimização atômica**: atendente com flag `is_admin` aciona "Anonimizar paciente"; sistema sobrescreve PII (`nome → "paciente-excluido-<hash-curto>"`, `telefone → null`, `email → null`) preservando integridade referencial de Consulta / Lembrete / Confirmação / Notificação. Um evento `anonimizacao` é registrado no histórico imutável com `ator = atendente-admin` e `motivo = "LGPD art. 18 — solicitação paciente"`. — *origem: briefing §9 + §10 · bmad.md §2.6 · C-003*
- **FR-034:** System MUST NÃO expor dados de Paciente, Médico ou Consulta a usuários não autenticados (exceto via link seguro enviado no próprio WhatsApp do paciente, restrito à consulta dele). — *origem: briefing §5 perfil Paciente + §9*

### Non-Functional Requirements

- **NFR-001 — Performance.** O processamento de resposta do paciente (do recebimento pelo provedor até a atualização do status visível no painel) MUST ser concluído em mediano < 10 segundos no perfil de carga MPE típico (< 100 consultas/dia por clínica). `[INFERÊNCIA]` — alvo validável em piloto.
- **NFR-002 — Disponibilidade do envio.** O sistema MUST processar, com sucesso, ≥ 99% dos disparos de lembrete agendados dentro da janela operacional válida (excluindo erros definitivos de número inválido). `[INFERÊNCIA]` — alvo a validar em piloto.
- **NFR-003 — Privacidade.** Dados PII do Paciente (nome, número de WhatsApp, e-mail) MUST ser armazenados em campos passíveis de **anonimização atômica** (UPDATE único sem cascade delete) e com criptografia-em-repouso. Logs operacionais NÃO devem conter payload completo de mensagens nem número de WhatsApp em claro.
- **NFR-004 — Auditoria.** Eventos do histórico imutável (FR-017/FR-018) MUST ser persistidos por **5 anos** a partir da data do evento. Após 5 anos, o sistema anonimiza automaticamente campos de PII do evento (`ator_id` e `ip` viram null) mas preserva o evento para métrica estatística indefinidamente. Origem: C-006.
- **NFR-005 — Responsividade.** O painel do atendente MUST ser utilizável em telas de 13"+ (desktop/notebook) e em celulares 5.5"+ para consulta rápida pelo médico.
- **NFR-006 — Idempotência de integração.** Callbacks duplicados do provedor de WhatsApp (mesma mensagem processada 2x) MUST ser detectados e descartados sem gerar eventos duplicados.
- **NFR-007 — Custo operacional.** O custo médio mensal por notificação enviada MUST ≤ **R$ 0,20** (incluindo taxas do provedor de WhatsApp). Excesso ao teto aciona alerta de revisão de provedor; ultrapassar **R$ 0,30** dispara o critério de invalidação de D-002 (revisão estratégica do Caminho D). Origem: C-001.

### Key Entities

- **Consulta** — unidade central do módulo. Representa o compromisso de um Paciente com um Médico em uma data/hora. Possui um status enumerado (`agendada`, `lembrete-enviado`, `confirmada`, `cancelada-pelo-paciente`, `cancelada-pela-clinica`, `reagendamento-solicitado`, `reagendada`, `sem-resposta`, `compareceu`, `no-show`, `falha-envio`, `numero-invalido`). Derivação do status vem do último evento aplicável registrado no histórico, aplicando **eventos `correcao`** por cima dos eventos por eles referenciados (ver FR-017 + C-005).
- **Paciente** — quem agendou a consulta. Atributos-chave no MVP: nome, número de WhatsApp, e-mail opcional. Não tem credenciais no sistema (não loga).
- **Médico** — profissional que atende. Atributos-chave: nome, especialidade. Pode ter múltiplas consultas no mesmo dia.
- **Lembrete** — evento de intenção de envio. Agendado no momento da criação da Consulta; evolui entre `agendado`, `enviado`, `falha-envio` (retry) ou `numero-invalido` (terminal).
- **Confirmação** — evento de resposta do paciente ou do atendente. Registra intenção explícita sobre a Consulta (confirmar / cancelar / reagendar). Um único Lembrete pode gerar múltiplas Confirmações; vale a última dentro da janela.
- **Notificação** — tentativa concreta de entrega pela integração externa (provedor de WhatsApp). Herdeira do Lembrete; persiste retornos do provedor (`delivered`, `read`, `failed`) para reconciliação e métrica.
- **Configuração da Clínica** — entidade única no MVP (por D-003 single-tenant). Armazena janela de lembrete (horas antes) e janela de silêncio (horas antes).

### Permissões

Conforme **C-002**: 3 perfis funcionais distintos (Paciente, Médico, Atendente) + flag `is_admin` no Atendente elevando privilégios de configuração.

| Ação | Paciente | Médico | Atendente | Atendente+`is_admin` |
|---|:-:|:-:|:-:|:-:|
| Logar no painel web | ❌ | ✅ | ✅ | ✅ |
| Responder lembrete via WhatsApp | ✅ | — | — | — |
| Consultar detalhes da própria consulta via link seguro | ✅ | — | — | — |
| Cadastrar/editar paciente | ❌ | ❌ | ✅ | ✅ |
| Cadastrar/editar médico | ❌ | ❌ | ❌ | ✅ |
| Criar/editar/cancelar consulta | ❌ | ❌ | ✅ | ✅ |
| Ver painel do dia (todas as consultas da clínica) | ❌ | ❌ | ✅ | ✅ |
| Ver própria agenda (só próprias consultas) | ❌ | ✅ | — | — |
| Confirmar/reagendar/cancelar em nome do paciente | ❌ | ❌ | ✅ | ✅ |
| Marcar compareceu/no-show | ❌ | ✅ (próprias) | ✅ | ✅ |
| Corrigir evento de compareceu/no-show com motivo | ❌ | ❌ | ✅ | ✅ |
| Consultar histórico completo de uma consulta | ❌ | ✅ (próprias) | ✅ | ✅ |
| Configurar janela de lembrete / silêncio / janela operacional / retry | ❌ | ❌ | ❌ | ✅ |
| Anonimizar paciente (LGPD art. 18) | ❌ | ❌ | ❌ | ✅ |
| Acessar dados de outra clínica | ❌ | ❌ | ❌ | ❌ (bloqueado por D-003) |

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
- **Onboarding autônomo da clínica** — assinatura e setup envolvem contato humano no MVP (decisão comercial do SaaS, fora do escopo do módulo); automação de onboarding fica para ciclo futuro.
- **Trial gratuito** — decisão comercial fora do escopo técnico do módulo; quando for decidido pelo produto, não afeta FRs do módulo de confirmação.

---

## Gate pós-Clarify (`fases/03_CLARIFY.md` + `checklists/qualidade-spec.md`)

- [x] **Zero marcadores abertos na spec**: nenhum `[NEEDS CLARIFICATION]` residual; nenhum `[DECISÃO HUMANA]` sem decisão aplicada; `[INFERÊNCIA]` remanescentes apenas em metas de SC (recalibradas em retrospective).
- [x] Todas as seções do template preenchidas (User Scenarios · Requirements · Success Criteria · Out of Scope).
- [x] Zero regras ditadas em modo "como fazer" — nenhum nome de framework/ORM/banco/SDK.
- [x] Cada FR é mensurável e verifica-se por cenário Given/When/Then.
- [x] Cada User Story tem prioridade P1..P4, Independent Test explícito e ≥ 1 cenário Given/When/Then.
- [x] Edge cases mapeados (13 itens) — todos consolidados pós-Clarify.
- [x] Permissões detalhadas conforme **C-002** (matriz por ação × perfil).
- [x] Key Entities definidas (7 entidades) sem tipos técnicos; evento `correcao` integrado conforme C-005.
- [x] Success Criteria mensuráveis (8 SC) tecnologia-agnósticos; alvos `[INFERÊNCIA]` recalibráveis em retrospective pós-piloto.
- [x] Out of Scope explícito (12 itens) cruzando com briefing §9.
- [x] **Rastreabilidade completa**: cada FR (001 a 034) + cada NFR (001 a 007) referencia sua origem — `D-NNN`, `C-NNN` ou seção do briefing/bmad. Nenhum FR/NFR órfão.
- [x] **Coerência com decision_log + clarify**: FR-007 implementa D-002; FR-032 implementa D-003; FR-010/011/028 implementam C-004; FR-017 implementa C-005; FR-018 + NFR-004 implementam C-006; FR-030/031 + §Permissões implementam C-002; FR-033 + NFR-003 implementam C-003; NFR-007 implementa C-001.
- [ ] **Validação humana** — pendente; ocorre via merge do PR de Fase 3 Clarify.

**Veredicto do Arquiteto:** 🟢 spec estável pós-Clarify. Próxima fase: **Fase 3.5 Constituição** (`constitution.md`) — declara padrões invariantes (camadas 1: engenharia; 2: stack; 3: produto) que Plan e Implement herdam sem redecidir.
