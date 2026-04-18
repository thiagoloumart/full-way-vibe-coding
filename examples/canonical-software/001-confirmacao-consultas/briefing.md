---
artefato: briefing
fase: 1
dominio: [software]
schema_version: 1
requer:
  - "1. Visão Geral da Solução"
  - "2. O Problema"
  - "3. Público-Alvo"
  - "4. Modelo de Precificação e Negócio"
  - "5. Perfis de Acesso"
  - "6. Canais / Superfícies"
  - "7. Módulos e Casos de Uso"
  - "8. Fluxo Principal (alto nível)"
  - "9. Restrições e Não-objetivos"
  - "10. Itens ainda em aberto"
---

# Briefing de Software: Confirmação de Consultas para Clínicas MPE

**Data:** 2026-04-18
**Autor do briefing:** Thiago Loumart (modo Arquiteto — pré-escrita com marcadores; validação humana via PR review)
**Status:** Draft
**Projeto:** `001-confirmacao-consultas` (canônico D1) — greenfield

Referências:
- `bmad.md §5 Contrato para o Briefing` (herdado)
- `decision_log.md` D-001 (stack), D-002 (Caminho D), D-003 (single-tenant MVP)
- `fases/01_BRIEFING.md` (contrato desta fase)
- `checklists/qualidade-briefing.md` (gate de aprovação)

---

## 1. Visão Geral da Solução

Sistema web para clínicas micro, pequenas e médias brasileiras reduzirem o problema de pacientes que não aparecem em consultas agendadas. A clínica agenda a consulta normalmente; o sistema envia um lembrete automático pelo WhatsApp 24 horas antes; o paciente confirma, cancela ou pede reagendamento com um toque de botão na própria conversa. Quem não responde até a janela-limite fica visível para o atendente intervir manualmente — ligando, confirmando pela tela ou reagendando. No fim do dia, a clínica enxerga com clareza quem veio, quem não veio e por qual canal cada confirmação chegou, com trilha auditável para disputas e análise de desempenho.

## 2. O Problema

**Dor principal:** pacientes não comparecem a consultas agendadas e o horário do profissional é perdido sem tempo hábil para colocar outro paciente no lugar. Em clínicas MPE brasileiras sem sistema de confirmação, a taxa de no-show fica tipicamente entre 20% e 40% `[INFERÊNCIA]`.

**Quem sofre:**
- **A clínica** (dona do slot): paga a hora do profissional sem receber pelo atendimento.
- **O atendente**: vira um "apagador de incêndio" — descobre a ausência na hora, tenta remanejar agenda apressadamente, precisa ligar para o paciente seguinte perguntando se pode adiantar.
- **O médico**: fica ocioso no horário que deveria estar atendendo, quebra o ritmo do consultório.
- **O paciente da fila de espera** (dor menos visível): perde a chance de adiantar o atendimento porque ninguém avisa a tempo que um slot abriu.

**Consequências quando não resolvido:**
- Receita por hora-profissional fica bem abaixo do potencial teórico da agenda.
- Clínica recorre a **overbooking informal** (marcar dois pacientes no mesmo horário torcendo que um falte), prática estressante e arriscada.
- Atendente opera em modo reativo permanente, o que gera erro humano, retrabalho e insatisfação.
- Paciente em fila espera mais do que precisaria, porque ninguém consegue girar a agenda em tempo real.

**Relatos concretos representativos** `[INFERÊNCIA]` *(validação formal com 2–3 clínicas reais fica como hipótese H-2 a exercitar em piloto — ver §10; este é exemplo de padrão conhecido no segmento MPE BR):*
- *"Agendei consulta há três semanas. No dia, esqueci completamente e só lembrei no outro dia quando a clínica ligou cobrando a falta."*
- *"Recebia confirmação por e-mail, mas nunca abria — caía no spam ou eu não lia. Um lembrete no WhatsApp teria funcionado."*
- *"Minha atendente passa a manhã inteira ligando pra confirmar consulta da tarde. Quando sobra algum slot livre, ela tenta reencaixar, mas geralmente já é tarde demais."*

## 3. Público-Alvo

**Público primário:** **clínicas MPE brasileiras** (micro, pequenas e médias empresas de saúde). Perfil típico: 1 a 3 consultórios, 1 a 8 profissionais, até ~100 consultas/semana. Maturidade digital média-baixa — atendente costuma trabalhar com agenda em papel, planilha simples ou sistema legado pouco usado. Cliente valoriza ferramenta que "funciona no celular" e "não precisa de treinamento de uma semana".

**Público secundário:**
- **Pacientes** das clínicas — idade e contexto muito variáveis; no perfil MPE BR, **WhatsApp é o canal universal de comunicação com a clínica** `[INFERÊNCIA]` (validar em H-1 — ver §10).
- **Médicos** da própria clínica — consumidores da informação "quem confirmou", mas não operadores do sistema no dia a dia.

**Contexto de uso:**
- **Atendente** opera o sistema no **navegador do computador da recepção** (web, desktop, presencial).
- **Paciente** recebe e responde lembretes **no próprio WhatsApp do celular** — não precisa abrir app, não precisa instalar nada, não precisa criar conta.
- **Médico** consulta a agenda do dia, preferencialmente via **web mobile** (olhada rápida entre atendimentos).

## 4. Modelo de Precificação e Negócio

**Modelo:** **assinatura mensal por clínica** `[INFERÊNCIA]` — valor único por clínica instalada, sem cobrança por paciente, por consulta ou por mensagem enviada. Faixa-alvo MPE BR: R$ 100 a R$ 300/mês por clínica `[INFERÊNCIA]` (validar com potenciais clientes — ver §10).

**Quem paga:** a clínica contratante.

**Gatilho de cobrança:** contratação inicial (pode incluir trial gratuito de 14 ou 30 dias `[NEEDS CLARIFICATION]`) + renovação mensal recorrente.

**Observação sobre cobrança interna ao módulo:** conforme `decision_log.md D-002` (tabela §5.4), o **módulo de confirmação em si não cobra nada** — paciente não paga pelo lembrete, clínica não paga por mensagem enviada (o custo de envio é absorvido na assinatura). Pagamento de consulta é **sistema separado**, fora do escopo deste módulo e deste canônico.

## 5. Perfis de Acesso

| Perfil | Descrição | Pode (alto nível) |
|---|---|---|
| **Atendente** | Operador humano da recepção da clínica. Usa o sistema diariamente para agendar consultas, acompanhar confirmações, intervir manualmente quando paciente não responde, marcar presença/ausência. | Cadastrar paciente · criar, editar e cancelar consulta · confirmar/reagendar/cancelar em nome do paciente · ver painel do dia com status de todas as consultas · marcar compareceu/no-show · consultar histórico de uma consulta. |
| **Médico** | Profissional que atende. Consumidor passivo do sistema — vê sua agenda do dia já com status de confirmação consolidado. | Ver sua própria agenda do dia · ver status de confirmação das suas consultas · marcar no-show (opcional) · **não** opera a fila de confirmação nem edita dados de outros médicos. |
| **Paciente** | Quem agendou a consulta. **Não loga no sistema.** Interage exclusivamente via WhatsApp, respondendo ao lembrete com um toque de botão. | Responder ao lembrete (confirmar / cancelar / pedir reagendamento) · consultar detalhes da sua própria consulta via link seguro recebido no lembrete. |
| **Admin da clínica** | Papel administrativo dentro da clínica (tipicamente o dono ou gerente). Configura o sistema e os profissionais. `[INFERÊNCIA]` em MVP pode ser o próprio atendente com permissão adicional — ficar a decidir em Clarify (C-002). | Cadastrar e editar médicos · configurar janela de envio do lembrete (ex: 24h antes ou 48h antes) · ver relatórios simples de confirmação e no-show · **não** acessa dados de outras clínicas (MVP é single-tenant, conforme D-003). |

**Auditor** é papel **condicional** `[INFERÊNCIA]` — não há UI dedicada no MVP; o acesso a histórico imutável para fins de disputa ou LGPD acontece via consulta operacional ao painel. Formalização do papel fica para Clarify (C-006).

## 6. Canais / Superfícies

- **Painel web** — interface principal usada por **atendente** (uso intensivo diário) e **admin da clínica** (uso pontual para configuração). Responsivo o bastante para **médico** consultar agenda no celular.
- **WhatsApp** — canal de contato com o **paciente**. Mensagens com botões interativos ("Confirmar", "Cancelar", "Reagendar"). Paciente nunca entra em página de login; toda a experiência dele acontece dentro da conversa.
- **API / CLI / mobile nativo / painel interno** — **fora de escopo** do MVP.

**Ambientes:** produção única inicialmente (MVP atende clínica-piloto); dev/staging/prod maduro vira preocupação de Fase 4 Plan e Fase 7 Implement.

## 7. Módulos e Casos de Uso

O módulo-alvo deste ciclo é **Confirmação de Consultas**. Conforme `recepcao.md §4` e `bmad.md §1.5`, **Cadastro** e **Agendamento** aparecem como *scaffolding mínimo* — existem para Confirmação poder funcionar, mas não recebem toda a profundidade de um ciclo Vibe Coding completo neste MVP.

### 7.1 Módulo Confirmação de Consultas (alvo do ciclo)

**Objetivo do módulo:** fazer com que o maior número possível de consultas tenha status **confirmado**, **cancelado** ou **reagendado** antes do horário — para que slots duvidosos sejam identificados cedo e o atendente possa reusá-los ou liberá-los.

**Ações principais:**
- **Sistema** envia lembrete automático pelo WhatsApp para o paciente na janela configurada antes do horário.
- **Paciente** confirma a consulta respondendo com um toque no botão "Confirmar".
- **Paciente** cancela a consulta respondendo com um toque no botão "Cancelar".
- **Paciente** solicita reagendamento respondendo com um toque no botão "Reagendar" — o sistema encaminha a solicitação para o atendente tratar.
- **Sistema** atualiza o status da consulta conforme a resposta recebida.
- **Sistema** registra cada evento (lembrete enviado, resposta recebida, mudança de status) em histórico imutável, incluindo canal, data/hora e ator.
- **Sistema** sinaliza no painel do atendente, alguns horários antes da consulta, as consultas que **não receberam resposta** (situação de silêncio).
- **Atendente** vê o painel do dia com status consolidado de todas as consultas e age sobre as de silêncio (liga, confirma pela tela, reagenda).
- **Atendente** pode **confirmar em nome do paciente** diretamente pela tela quando o paciente responde por outro canal (telefone, presencial).
- **Atendente** marca **compareceu** ou **no-show** após a consulta.
- **Atendente** consulta o histórico completo de uma consulta (envios, respostas, mudanças de status) quando precisar.

**Regras de negócio específicas deste módulo:**
- Todo evento de status gera registro **imutável** — não existe "editar a confirmação que já aconteceu"; correção é novo evento sobreposto ao anterior, com ambos preservados (detalhe vira Clarify C-005).
- **Janela do lembrete** (quantas horas antes do horário?) é configurada pelo admin da clínica; padrão-sugerido `[INFERÊNCIA]` 24 horas antes; detalhe vira Clarify C-004.
- **Janela do silêncio** (a partir de quando o sistema considera "paciente não respondeu"?) é regra sensível e vira Clarify C-004.
- **Horário de envio** respeita janela operacional cultural (envio em horário razoável, não madrugada nem muito cedo); detalhe vira Clarify.
- Respostas por texto livre fora dos botões são tratadas como **ambíguas** e passam para fluxo de atenção do atendente; sistema não interpreta intenção.
- Duplicidade de envio (sistema dispara duas vezes o mesmo lembrete por erro) é **prevenida por idempotência** — paciente recebe no máximo uma mensagem por consulta por janela.

### 7.2 Módulo Cadastro (scaffolding mínimo)

**Objetivo:** existir o mínimo para que Confirmação funcione.

**Ações principais:**
- **Atendente** cadastra **paciente** com dados mínimos: nome, número de WhatsApp, e-mail opcional.
- **Atendente** cadastra/edita **médico** (dados mínimos: nome, especialidade).
- **Admin** configura janela de lembrete da clínica.

**Fora de escopo deste MVP:**
- Perfis hierárquicos completos (admin-de-rede, admin-de-clínica, perfis clínicos variados).
- Onboarding autônomo do paciente (paciente não cadastra a si mesmo).
- Prontuário médico, histórico clínico, integração com plano de saúde.

### 7.3 Módulo Agendamento (scaffolding mínimo)

**Objetivo:** existir o mínimo para que Confirmação tenha consultas para operar.

**Ações principais:**
- **Atendente** cria consulta escolhendo paciente, médico, data e hora.
- **Atendente** edita consulta (altera data/hora, troca médico).
- **Atendente** cancela consulta manualmente (independente de resposta do paciente).

**Fora de escopo deste MVP:**
- Encaixe, overbooking explícito, bloqueios por feriado/ausência.
- Agenda recorrente (consulta de retorno automática, sessões de tratamento).
- Integração com agenda externa (Google Calendar, Outlook).

## 8. Fluxo Principal (alto nível)

1. **Atendente** cadastra paciente e cria consulta no painel web (paciente, médico, data e hora).
2. **Sistema** agenda automaticamente o envio do lembrete para a janela configurada (por padrão, 24 horas antes do horário da consulta).
3. Na hora agendada, **sistema** envia o lembrete ao paciente pelo WhatsApp — mensagem com identificação da clínica, do médico, data e hora, e três botões: "Confirmar", "Cancelar", "Reagendar".
4. **Paciente** toca em um dos três botões.
5. **Sistema** recebe a resposta, atualiza o status da consulta (confirmada / cancelada-pelo-paciente / reagendamento-solicitado) e registra o evento no histórico.
6. **Atendente** acompanha pelo painel do dia quem confirmou, quem cancelou e quem pediu reagendamento. Para cada reagendamento solicitado, o atendente entra em contato e propõe novo horário.
7. Se o paciente **não responde** até a janela de silêncio, o sistema sinaliza a consulta como "sem resposta" no painel do atendente, que intervém manualmente (ligação, confirmação em nome do paciente, reagendamento, cancelamento).
8. No dia da consulta, **médico** consulta sua agenda já com status consolidado. Após o atendimento, **atendente** (ou médico) marca compareceu ou no-show.
9. **Sistema** mantém trilha auditável completa de todos os eventos, consultável pelo atendente e pelo admin da clínica.

## 9. Restrições e Não-objetivos

**Fora de escopo deste ciclo:**
- **Outros canais além do WhatsApp** — sem SMS, sem e-mail automatizado, sem ligação por robocall. Segundo canal só é considerado após validação do canal único em produção, conforme critério de invalidação em `decision_log.md D-002`.
- **Multi-unidade / multi-clínica** — MVP opera como **uma clínica por instalação** (D-003). Redes com várias clínicas ficam para ciclo futuro.
- **Pagamento e cobrança de consulta** — sistema de cobrança do paciente pela consulta é sistema separado; este módulo só trata confirmação.
- **Dashboard analítico rico** — métricas agregadas (taxa de confirmação por canal, padrão de no-show por médico/dia/horário) ficam para ciclo futuro. MVP exibe números básicos no painel do dia.
- **Painel dedicado de paciente** — paciente não acessa painel próprio; a interação dele se esgota no WhatsApp.
- **Integração com agenda externa** — sem Google Calendar, Outlook, iCal no MVP.
- **App nativo** — sem iOS, Android. Painel web responsivo é suficiente.

**Restrições declaradas:**
- **Prazo:** cadência do canônico D1 W1 track B. MVP deve caber no budget de tempo que permite encerrar o canônico no horizonte do Milestone M2 v1.2.
- **Equipe:** solo-dev inicialmente (o autor). Sem equipe de design, sem QA dedicado no MVP.
- **Compliance:** **LGPD aplicável** — paciente tem direito de acesso aos seus dados e, em certos casos, direito à deleção (art. 18). Impacto na regra de deleção vai para Clarify C-003. Não há requisito ANVISA específico conhecido para lembrete de consulta, mas revisar se surgir sinal.
- **Integração obrigatória:** provedor de WhatsApp é dependência estratégica; contrato de canal deve permitir troca de provedor sem tocar o domínio (conforme `bmad.md §4.4`).
- **Orçamento operacional alvo:** custo por notificação enviada dentro da faixa que permita a assinatura mensal da clínica cobrir o envio com folga `[NEEDS CLARIFICATION]` — vira C-001 de Clarify.

**Premissas assumidas** (marcadas `[RISCO ASSUMIDO]` para revisão se provarem falsas):
- A grande maioria dos pacientes das clínicas-piloto tem WhatsApp ativo.
- A clínica-piloto aceita treinamento leve (1–2 horas) para usar o painel web.
- O admin da clínica é a mesma pessoa que o atendente no MVP (até surgir clínica com estrutura mais formal).

## 10. Itens ainda em aberto

Vão virar entradas formais em `clarify.md` na Fase 3:

- [ ] **C-001** — Custo-alvo por notificação. Qual o teto por mensagem que o modelo comercial (assinatura mensal por clínica) absorve sem comprometer margem? *(Herdado de `recepcao.md §4`.)*
- [ ] **C-002** — Matriz de permissões fina. Quais ações exatas cada perfil pode (atendente/médico/paciente/admin)? O admin é perfil próprio no MVP ou é atendente com flag?
- [ ] **C-003** — Direito à deleção (LGPD art. 18). O que acontece com o histórico imutável quando o paciente pede deleção? Anonimização? Tombstone? Hard delete com invalidação do histórico?
- [ ] **C-004** — Janelas de tempo. Quanto tempo antes da consulta o lembrete é enviado (padrão e configurável)? A partir de quando o silêncio vira "sem resposta"? Horário operacional permitido para envio?
- [ ] **C-005** — Histórico imutável. Como o sistema trata correções legítimas de status (ex: atendente marcou no-show por engano)? Edição direta é proibida, mas qual a forma canônica de sobrepor com novo evento?
- [ ] **C-006** — Auditoria. Que campos cada evento registra obrigatoriamente (timestamp, canal, ator, IP, identificador externo)? Qual a política de retenção? Quem pode acessar?
- [ ] **Validação de hipótese H-1** — taxa de abertura de lembrete via WhatsApp em clínicas MPE BR > 85% (piloto).
- [ ] **Validação de hipótese H-2** — redução de no-show de 20–40% para <10% em 30 dias (piloto).
- [ ] **Validação de hipótese H-3** — atendentes MPE BR aceitam painel server-rendered como UX diária (Clarify com 2 atendentes).
- [ ] **Validação de hipótese H-4** — custo médio por notificação fica em faixa viável para o volume MPE (cotação formal com provedor em Fase 4 Plan).
- [ ] **Validação de hipótese H-5** — template de botões cobre 90%+ dos intents esperados (Spec).
- [ ] **Trial gratuito** — existe? Duração? Com ou sem cartão? (§4.)
- [ ] **Modelo de onboarding da clínica** — clínica se cadastra sozinha ou exige contato humano? (Fora do módulo de confirmação, mas precisa estar decidido antes do launch.)

---

## Gate de saída da Fase 1 (`fases/01_BRIEFING.md` + `checklists/qualidade-briefing.md`)

- [x] **Fundamentos**: linguagem de negócio; sem framework/biblioteca/ORM/banco; verbos concretos; zero "tem que ter controle / funcionar bem".
- [x] **Cobertura (Manual §8)**: problema com relatos, quem sofre, resultado entregue, quem usa, fluxo principal, modelo de cobrança, papéis e módulos mínimos — tudo presente.
- [x] **Condução**: em modo Arquiteto, a pergunta cíclica ("tem mais ação neste módulo?") e a checagem final foram aplicadas **internamente** ao drafar cada módulo (§7.1/§7.2/§7.3). Validação conversacional com humano acontece via PR review.
- [x] **Fronteiras**: não-objetivos listados (§9); restrições (prazo, equipe, LGPD, integração obrigatória) registradas.
- [x] **Qualidade geral**: público primário e secundário (§3); contexto de uso (§3); canais (§6); itens em aberto listados (§10).
- [x] **Coerência com BMAD**: nenhum item contradiz `bmad.md §1.1` (problema), `bmad.md §2.1` (atores), `bmad.md §2.2` (fluxo), `bmad.md §4.1` (Caminho D), `decision_log.md` D-001/D-002/D-003.
- [ ] **Validação humana** — pendente; ocorre via merge deste PR.

**Veredicto do Arquiteto:** 🟢 draft fechado. Próxima fase: Fase 2 Spec (`spec.md`), que recebe §7 deste briefing como entrada para estruturar user stories, FRs e NFRs.
