---
artefato: bmad
fase: 0.5
dominio: [software]
schema_version: 1
requer:
  - "1. Breakdown — decomposição do problema"
  - "2. Model — modelagem do sistema"
  - "3. Analyze — análise de alternativas"
  - "4. Decide — decisão registrada"
  - "5. Contrato para o Briefing (ponte para Fase 1)"
---

# BMAD — `001-confirmacao-consultas` (Canônico D1)

**Data:** 2026-04-18
**Autor:** Thiago Loumart (modo Arquiteto — pré-escrita com marcadores; validação humana via PR review)
**Status:** Draft
**Projeto:** canônico D1 software — greenfield
**Referência:** reformulação confirmada em `recepcao.md §1`; hipóteses estratégicas em `recepcao.md §5`; ponte em `recepcao.md §6`.

Referências operacionais:
- `fases/00_5_BMAD.md` (contrato da fase)
- `checklists/qualidade-bmad.md` (gate de aprovação)
- `harness/_audit/progress.md §W1 track B` (replanejamento que autoriza este PR)

---

## 1. Breakdown — decomposição do problema

### 1.1 Problema real (1 frase)
**Pacientes de clínicas MPE brasileiras não comparecem a consultas agendadas e o slot é perdido sem tempo hábil para reagendamento, reduzindo receita por profissional-hora.**

> Zero menção a solução. "Confirmação", "lembrete", "WhatsApp" são *respostas* a este problema e moram da seção 3 em diante.

### 1.2 Quem sofre, quando, com que frequência
- **Quem:**
  - **Clínica** (dona do slot): sofre financeiramente — hora de profissional paga sem receita correspondente.
  - **Atendente**: sofre operacionalmente — precisa remanejar agenda em cima da hora, ligar manualmente, ouvir justificativa, reacomodar.
  - **Médico**: sofre por ociosidade e quebra de fluxo de atendimento.
  - **Paciente em fila** (menos visível): perde chance de adiantar sua consulta — sem informação de slot liberado, não aparece cedo.
- **Momento em que dói:** no próprio horário da consulta, quando o paciente não aparece. Em segundo plano, dói também na semana antes (sem sinal do sistema de que aquela consulta é alta chance de no-show).
- **Frequência / intensidade:** `[INFERÊNCIA]` literatura cita **20–40% de no-show em clínicas MPE BR sem sistema de confirmação**. Em uma clínica com 20 consultas/dia, 4–8 slots perdidos/dia. Hipótese a validar em Briefing com 2–3 clínicas reais.
- **Consequências quando não resolvido:**
  - Receita perdida (hora-profissional não cobrada).
  - Ruído operacional (atendente apaga incêndio todo dia).
  - Paciente de espera prolongada (fila não gira).
  - Clínica depende de "overbooking informal" (marcar 2 pacientes no mesmo horário torcendo que um falte) — prática arriscada e desagradável.

### 1.3 Causa-raiz vs sintoma aparente
| Observado (sintoma) | Causa-raiz provável | Evidência |
|---|---|---|
| Paciente não aparece no dia | Paciente esqueceu da consulta ou perdeu o contexto desde o agendamento (agendamento feito semanas antes) | Intervalo típico de agendamento em MPE BR é 2–6 semanas; memória de compromisso decai fortemente após 7 dias `[INFERÊNCIA]` |
| Slot perdido sem reuso | Clínica descobre ausência tarde demais (no horário ou minutos antes) | Sem lembrete explícito, a primeira sinalização de no-show é a própria falta |
| Atendente faz remanejamento reativo e estressante | Clínica não tem janela de decisão proativa sobre presença | Ausência de pergunta explícita "você vem?" 24h antes |
| Receita por profissional-hora abaixo do potencial | Taxa de ocupação efetiva < taxa de agenda | Comparação entre consultas marcadas e consultas atendidas |

**Causa-raiz dominante:** **falta de confirmação explícita antecipada** que permita à clínica (a) saber com antecedência quem virá e (b) acionar reagendamento para o slot duvidoso.

### 1.4 Subproblemas (MECE)
> Mutuamente exclusivos, coletivamente exaustivos. Abaixo cobrem a causa-raiz integralmente, sem sobreposição.

- **Subproblema A — Antecipação:** como **obter sinal explícito** do paciente, com antecedência ≥X horas, sobre a intenção de comparecer.
- **Subproblema B — Ação sobre o sinal:** como **tratar o sinal** recebido (confirmado / cancelado / reagendado / silêncio) de forma que libere o slot quando possível.
- **Subproblema C — Rastreabilidade:** como **registrar** quem respondeu o quê, quando e por qual canal, para disputa futura e análise de padrão.
- **Subproblema D — Cobertura do paciente não alcançável:** como **tratar o caso** em que paciente não responde (sem sinal, ambiguidade).

Gap-check (MECE): existe algum recorte do problema real que não cabe em A/B/C/D? Não. Receita, ociosidade e fila se resolvem pela conjunção de A+B. Auditoria/histórico se resolve por C. Paciente "silencioso" se resolve por D.

Sobreposição-check: A não decide ação (B decide); C não gera sinal (A gera); D é ortogonal a A/B/C porque trata o **complemento** (ausência de sinal).

### 1.5 Core vs periférico
| Subproblema | Core (sem isso não resolve) | Periférico (alivia mas não resolve) |
|---|---|---|
| A — Antecipação (gerar sinal) | ✅ | |
| B — Ação sobre o sinal (consumir sinal) | ✅ | |
| C — Rastreabilidade (histórico/auditoria) | ✅ | |
| D — Cobertura do silêncio (janela + alerta ao atendente) | ✅ | |

**Todos os quatro subproblemas são core.** Sem A não há insumo; sem B o sinal não vira valor; sem C não há defesa em disputa e sem métrica; sem D o sistema falha silenciosamente quando o canal falha.

**Foco deste ciclo:** A + B + C + D, todos core, com D implementado inicialmente como "alerta visual ao atendente na tela de consultas do dia" — não como motor de decisão automática sobre slot.

---

## 2. Model — modelagem do sistema

### 2.1 Atores
| Papel | Descrição (1 linha) | Pode (alto nível) | Não pode |
|---|---|---|---|
| Paciente | Quem agendou a consulta | Responder ao lembrete com confirmação / cancelamento / reagendamento; consultar detalhes da sua própria consulta via link recebido | Ver consultas de outros pacientes; editar dados clínicos; editar agenda do médico |
| Atendente | Operador humano da clínica no dia a dia | Criar/editar consulta; confirmar manualmente em nome do paciente; registrar presença/no-show no dia; ver painel do dia | Alterar dados clínicos; acessar consultas de outra clínica (MVP = 1 clínica) |
| Médico | Profissional que atende | Ver sua agenda do dia com status de confirmação; marcar no-show | Operar a fila de confirmação; cancelar consulta de terceiros |
| Sistema | O aplicativo em si | Disparar lembrete na janela configurada; registrar resposta do paciente; mudar status da Consulta; notificar atendente quando paciente não responde | Decidir regra de negócio sensível sozinho (expiração, visibilidade, deleção) — essas são decisões humanas (§5.4) |
| Integração externa (provedor de notificação) | Serviço que entrega mensagem ao paciente | Receber `payload` do Sistema e entregar ao paciente; devolver `delivered`/`read`/`reply` quando suportado; enfileirar retry em caso de falha temporária | Persistir estado próprio do domínio da clínica; tomar decisão de negócio |
| Auditor | `[INFERÊNCIA]` papel condicional | Consultar histórico imutável de confirmações sob demanda (disputa, LGPD, fiscalização) | Editar histórico |

**Decisão de escopo:** Auditor como ator **existe por consequência** (LGPD art. 18 dá direito de acesso ao titular; disputas contratuais exigem trilha). Entra no modelo, mas sem UI dedicada no MVP — acesso via consulta operacional ao histórico.

### 2.2 Fluxo principal ponta a ponta (3–7 passos)
> Caminho feliz. Sem ramificações de erro.

1. **Atendente agenda consulta** no sistema (ator = Atendente; insumo = dados de Paciente + Médico + data/hora). Status inicial: `agendada`.
2. **Sistema dispara lembrete** na janela configurada antes do horário (ator = Sistema; gatilho = job recorrente; payload = identificação mínima da consulta + link/instrução de resposta). Status passa a `lembrete-enviado`.
3. **Paciente responde** pelo canal (ator = Paciente; insumo = confirmação | cancelamento | reagendamento). Sistema normaliza a resposta e registra.
4. **Sistema atualiza status** da Consulta para `confirmada` | `cancelada-pelo-paciente` | `reagendamento-solicitado`.
5. **Atendente visualiza painel do dia** com status consolidado por consulta e toma ação operacional (reabrir slot cancelado, agendar reposição, etc.).
6. **Médico atende** no horário. Após a consulta, **atendente (ou sistema via check-in) marca** presença efetiva. Status final: `compareceu` ou `no-show`.

### 2.3 Entidades
- **Consulta:** unidade central. Representa o compromisso Paciente↔Médico em data/hora. Criada pelo Atendente; lida por todos; tem campo de status que evolui conforme eventos (§2.2).
- **Paciente:** quem agendou. Criado pelo Atendente (MVP). Dado mínimo: nome, canal de contato (identificador do canal), preferência de canal. Paciente não loga em UI no MVP — interage só via canal de lembrete.
- **Médico:** profissional. Criado pelo Admin/Atendente na configuração inicial. Dado mínimo: nome, especialidade, slots de trabalho.
- **Confirmação:** evento imutável. Representa "em T, por canal C, ator A confirmou/cancelou/reagendou a Consulta K". Não é campo da Consulta — é registro separado, auditável. Consulta lê a *última* Confirmação para derivar status.
- **Lembrete:** evento imutável. Representa "em T, Sistema enviou lembrete da Consulta K pelo canal C com identificador externo X". Base para reconciliar resposta recebida com envio feito.
- **Notificação:** tentativa de entrega. Representa "em T, provedor P recebeu/entregou/leu/falhou entrega Y". Herda de Lembrete. Persistida para retry, auditoria e métrica de canal.

Nenhum tipo técnico mencionado (sem "tabela", "collection", "DTO").

### 2.4 Fricções previsíveis
- **Latência do provedor externo:** entrega de notificação pode atrasar minutos ou falhar (provedor fora do ar, categoria de template reprovada, número inválido).
- **Resposta ambígua do paciente:** em canais de texto livre, paciente pode responder "amanhã posso" ou "talvez" — interpretação não é trivial.
- **Paciente sem canal ativo:** idoso sem WhatsApp; número trocado; e-mail no spam.
- **Decisão sobre "silêncio":** paciente não responde até X horas antes — sistema presume no-show ou mantém agendada? Decisão sensível (§5.4 expiração).
- **Janela operacional de envio:** enviar lembrete às 22h ou 6h é rude; clínica tem horário cultural aceito (9h–20h típico BR) `[INFERÊNCIA]`.
- **Fuso horário:** MVP BR assume único fuso, mas cliente em múltiplos fusos futuro precisa resolver.
- **Reagendamento via canal:** paciente pede reagendamento "para semana que vem" em texto livre — sistema não pode inventar horário; atendente precisa intervir.
- **Duplicidade de lembrete:** job recorrente pode disparar 2x por erro; contrato de idempotência é obrigatório.

### 2.5 O que precisa persistir
- **Entre sessões:** Consulta, Paciente, Médico, configuração de janela de lembrete da clínica.
- **Histórico imutável:** Lembrete (cada disparo), Confirmação (cada resposta), Notificação (cada tentativa de entrega e retorno do provedor).
- **Auditoria:** para cada Confirmação, registrar: timestamp, canal, ator (paciente direto | atendente em nome | sistema por automação), identificador externo (ID da mensagem no provedor), IP/origem quando disponível.
- **Métrica** (derivado, não necessariamente persistido como agregado no MVP): taxa de confirmação por canal, taxa de no-show, tempo médio até resposta.

### 2.6 Regras sensíveis (Manual §5.4)
Cada regra aplicável está mapeada ao `decision_log.md`. Itens com `D-NNN` preenchido foram decididos no BMAD; itens sem `D-NNN` são **candidatas a Clarify** na Fase 3.

| Regra | Aplica? | Já decidida em | Status |
|---|---|---|---|
| Cobrança | **não** | — | fora de escopo (confirmar: não há cobrança em módulo de confirmação de consulta; pagamento é sistema separado) |
| Permissão / autorização | **sim** | — | candidata a Clarify C-002 — perfis mínimos (atendente, médico, paciente) e o que cada um pode ver/fazer |
| Estorno / cancelamento | **não** | — | fora de escopo (cancelamento de consulta é mudança de status, não estorno financeiro) |
| Deleção | **sim (parcial)** | — | candidata a Clarify C-003 — direito do paciente à deleção (LGPD art. 18); impacto no histórico imutável |
| Expiração | **sim** | — | candidata a Clarify C-004 — janela de silêncio antes de sistema presumir no-show |
| Visibilidade entre papéis | **sim** | D-003 | decidida parcialmente no BMAD: **MVP = single-tenant (1 clínica)**; multi-clínica e visibilidade cruzada fica para ciclo futuro |
| Histórico | **sim** | — | candidata a Clarify C-005 — imutabilidade, retenção, acesso pelo paciente |
| Auditoria | **sim** | — | candidata a Clarify C-006 — escopo (timestamp + canal + ator + IP?), retenção |

**Regra §5.4 adicional detectada durante BMAD (não estava em recepcao.md):** **Permissão** (quem vê/edita o quê) e **Deleção** (LGPD). Acrescentadas ao radar de Clarify — transferência limpa, sem decisão silenciosa.

---

## 3. Analyze — análise de alternativas

### 3.1 Caminhos plausíveis (4)
Os 4 caminhos foram pré-abertos em `recepcao.md §6`. Mantive os nomes originais para continuidade rastreável.

| # | Caminho | Descrição (1 parágrafo) |
|---|---|---|
| A | **MVP esquelético — só manual** | Tela lista consultas de hoje/amanhã. Atendente liga para cada paciente, clica "confirmar" após a ligação. Nenhum canal automático. Sistema é basicamente uma *checklist colaborativa*. |
| B | **MVP 1 canal — e-mail** | Manual da A + job automático dispara e-mail 24h antes com link mágico. Paciente clica "confirmo" / "não vou" no link. Link atualiza status. SMTP grátis via Amazon SES ou similar; custo marginal quase zero. |
| C | **MVP multicanal com fallback** | Manual + e-mail + SMS + WhatsApp, orquestrado por política: tenta WhatsApp primeiro; se falha entrega em N minutos, tenta SMS; se falha, e-mail. Paciente responde por qualquer canal; sistema reconcilia. Alta cobertura, alto custo e alta complexidade. |
| D | **MVP WhatsApp-only + fallback humano** | Manual + lembrete automático **apenas via WhatsApp** (Z-API ou Meta Cloud API) 24h antes. Paciente responde pelo próprio WhatsApp com template de botões ("Confirmar" / "Cancelar" / "Reagendar"). Sistema reconcilia resposta. Paciente sem WhatsApp cai no fluxo manual da A (atendente liga). |

### 3.2 Matriz de trade-offs
> 🟢 favorável · 🟡 aceitável com ressalva · 🔴 problemático. Uma frase por célula.

| Caminho | Velocidade | Qualidade | Risco | Reversibilidade | Custo |
|---|---|---|---|---|---|
| A | 🟢 entrega em dias, zero integração | 🔴 não resolve causa-raiz (confirmação manual não escala além de 10–15 consultas/dia por atendente) | 🟢 zero dependência externa | 🟢 trivial — desligar a tela | 🟢 zero custo marginal |
| B | 🟡 1 integração (SMTP), job scheduler, template de e-mail | 🔴 abertura de e-mail em pacientes MPE BR estimada em 10–25% `[INFERÊNCIA]`; canal fraco para a dor real | 🟢 SMTP é estável e barato | 🟢 fácil remover e trocar | 🟢 ~R$ 0 (SES free tier cobre volume MPE) |
| C | 🔴 3 integrações, orquestração de fallback entre canais, reconciliação de respostas duplicadas | 🟢 maior cobertura teórica; redundância real | 🔴 3× superfície de falha; provedor SMS caro/instável BR; regras de prioridade difíceis de testar | 🟡 remover canal depois é tocar orquestrador | 🔴 SMS ~R$ 0,15–0,30/msg, WhatsApp ~R$ 0,05–0,10/msg, em volume isso pesa |
| D | 🟢 1 integração WhatsApp; template de botões resolve resposta ambígua; fallback humano já existe como scaffolding | 🟢 WhatsApp tem abertura >85% em BR `[INFERÊNCIA]` (referência cultural forte); resposta via botões elimina parsing de texto | 🟡 concentração em 1 provedor (Z-API pode instabilizar; Meta pode suspender categoria de template) | 🟡 trocar Z-API↔Meta é 1 sprint via contrato de canal abstrato; adicionar e-mail depois é aditivo | 🟡 ~R$ 0,05–0,20/mensagem conforme provedor `[NEEDS CLARIFICATION: custo-alvo por notificação]` |

**Anti-viés de confirmação** (checklist §Analyze): caminho escolhido (D) tem **2 células 🟡 reconhecidas** (risco de concentração e custo) — não é todo-verde. Trade-offs assumidos conscientemente.

### 3.3 Menor caminho funcional
**Caminho A** é tecnicamente o menor caminho funcional (prova que a tela+fluxo+status funcionam). Mas **A isoladamente não resolve a causa-raiz** (escala). O menor caminho que **também resolve** é **D** — 1 canal eficaz + fallback manual.

Interpretação: A é o **esqueleto** sobre o qual D é construído. Implementação de D **usa** A internamente (fluxo de confirmação manual pelo atendente continua existindo em D; ele é o fallback).

### 3.4 Pre-mortem por caminho
> "Se daqui a 30 dias este caminho falhou, por quê terá sido?"

- **Caminho A:**
  1. Clínica percebeu que continua ligando como antes — sistema virou "cadastro chique" e voltou para planilha.
  2. Atendente não consegue atualizar status em tempo real durante a ligação (UX ruim) e acaba não usando.
  3. Métrica de no-show não melhorou — clínica cancela assinatura no fim do primeiro mês.
- **Caminho B:**
  1. Paciente não abre e-mail — taxa de abertura em MPE BR ficou em 10–15%; no-show caiu ~5% apenas.
  2. E-mail foi parar no spam da maioria dos pacientes (domínio novo, sem reputação).
  3. Paciente que abriu não clicou no link (desconfiança de phishing).
- **Caminho C:**
  1. Projeto atrasou 3× o estimado por complexidade de orquestração; MVP não saiu no tempo.
  2. Custo operacional (SMS + WhatsApp) comeu margem e clínica achou o sistema caro.
  3. Respostas duplicadas (paciente respondeu WhatsApp *e* SMS) geraram conflito de status; usuário viu bug e perdeu confiança.
- **Caminho D:**
  1. Meta Cloud API suspendeu a categoria de template "utility" ou reclassificou como "marketing" — envio bloqueado.
  2. Z-API (provedor não-oficial) foi banido pelo WhatsApp; contas dos clientes perderam acesso.
  3. Paciente idoso sem WhatsApp ficou 100% dependente do atendente ligar, e a clínica percebeu que ainda precisa do fluxo manual — percepção de que o sistema "não automatiza tudo".

### 3.5 Riscos de overengineering
- **Caminho A:** não há overengineering à vista — o risco é o oposto (underdelivery).
- **Caminho B:** risco de sobre-engenharia no sistema de templates de e-mail + link mágico com token seguro + expiração; construir isso custa semana para entregar canal fraco.
- **Caminho C:** alto risco. Orquestrador de fallback, circuit-breaker por canal, deduplicação entre canais, UI de "qual canal o paciente prefere" — cada item é trabalho real para ganho marginal no MVP.
- **Caminho D:** risco moderado. Tentação de escrever abstração de "canal" demais cedo (Strategy pattern, driver plugável, filas dedicadas por canal). MVP deve ter contrato mínimo que suporte **1 canal** com **troca de provedor** — não N canais.

---

## 4. Decide — decisão registrada

### 4.1 Caminho escolhido
**Caminho D — MVP WhatsApp-only com fallback humano do atendente.**

### 4.2 Justificativa (critério dominante)
**Velocidade + fit cultural BR.** Em clínicas MPE brasileiras, WhatsApp é o canal dominante de comunicação paciente↔clínica — usá-lo como canal único de lembrete **aproveita hábito já instalado** e evita educar o usuário sobre um canal novo (e-mail) ou pagar por um canal caro (SMS). A taxa de abertura estimada do WhatsApp (>85%) é 4–8× a de e-mail, o que é o delta que converte o sistema em valor visível na primeira semana — corroborando a justificativa de priorização em `recepcao.md §5.2`. O critério secundário é **velocidade de entrega**: 1 integração vs. 3 do Caminho C, permitindo que as Fases 4–8 caibam no orçamento de tempo do primeiro canônico. Os dois 🟡 da matriz (concentração e custo) são aceitos explicitamente em §4.4 com critérios de invalidação em §4.5.

### 4.3 Alternativas descartadas
> Obrigatório: nenhum descarte vazio.

| # | Caminho | Motivo do descarte |
|---|---|---|
| A | MVP só manual | Não resolve a causa-raiz (escala). Seria aceitável como fase pré-produto (piloto de 1 clínica) mas, como MVP vendável, quebra a hipótese de que "clínica percebe valor semana 1" de `recepcao.md §5.2`. Continua presente **dentro** do Caminho D como fallback humano. |
| B | MVP e-mail | Canal estruturalmente fraco no perfil MPE BR: abertura estimada 10–25% e taxa de clique em link mágico ainda menor. Pre-mortem mostrou 3 modos plausíveis de falha com a mesma raiz: canal inadequado. |
| C | MVP multicanal | Overengineering para o MVP: 3× integrações, 3× contratos com provedor, orquestrador de fallback, reconciliação entre canais. Ganho marginal em cobertura (poucos pacientes têm só SMS) não compensa tripling de superfície de bug e custo operacional. Segundo canal deve entrar **depois** da primeira validação do Caminho D em produção. |

### 4.4 Riscos aceitos
- `[RISCO ASSUMIDO]` **Concentração de risco em 1 provedor WhatsApp.** Mitigação parcial: contrato de canal abstrato na Spec (Fase 2) permite trocar provedor (Z-API ↔ Meta Cloud API) em ~1 sprint sem tocar domínio.
- `[RISCO ASSUMIDO]` **Pacientes sem WhatsApp ativo** dependem 100% do fluxo manual do atendente para receber lembrete. Aceitável porque a parcela da carteira MPE BR sem WhatsApp é pequena `[INFERÊNCIA]` e já está contemplada pelo fallback humano — nenhum paciente fica sem opção de confirmação.
- `[RISCO ASSUMIDO]` **Custo por notificação** (~R$ 0,05–0,20) é aceito sob a premissa de que o custo fica muito abaixo do valor de uma consulta recuperada (hora-médico tipicamente R$ 150–500 BR). `[NEEDS CLARIFICATION: custo-alvo por notificação]` → C-001 na Fase 3.
- `[RISCO ASSUMIDO]` **Dependência regulatória da categoria de template** do Meta: se "utility" for reclassificada para "marketing" para saúde, todo o fluxo para. Critério de invalidação em §4.5.

### 4.5 Critérios de invalidação
> O que força revisão dessa decisão.

- **Se Meta Cloud API suspender a categoria de template "utility"** para saúde ou bloquear HSM para lembretes de consulta → revisar para Caminho C ou adicionar SMS como 2º canal obrigatório (nova `D-NNN` assinada).
- **Se custo médio real por notificação passar de R$ 0,30** (teto a formalizar em C-001) → revisar provedor ou modelo.
- **Se taxa de abertura efetiva do WhatsApp em teste-piloto ficar < 70%** → revisar hipótese e considerar Caminho C.
- **Se <50% dos pacientes da clínica-piloto tiverem WhatsApp ativo** → revisar (cenário improvável em MPE BR mas mensurável em Briefing).
- **Se LGPD ou ANVISA emitirem restrição específica** sobre envio de lembrete de saúde via WhatsApp → revisar imediatamente.

### 4.6 Hipóteses em aberto
> A validar durante ou depois.

- [ ] **H-1** Taxa de abertura de lembrete via WhatsApp em clínicas MPE BR > 85% (validar em Briefing com 2–3 clínicas reais; medir em piloto).
- [ ] **H-2** Redução de no-show de 20–40% → <10% em 30 dias de uso (validar com métrica em piloto).
- [ ] **H-3** Atendentes MPE BR aceitam painel server-rendered como UX (sem SPA) para fluxo diário (validar em Clarify com 2 atendentes).
- [ ] **H-4** Custo médio por notificação WhatsApp fica em R$ 0,05–0,20 no volume MPE (~100–500 mensagens/mês por clínica) (validar em BM/orçamento de Meta BSP).
- [ ] **H-5** Template de botões do WhatsApp cobre 90%+ dos intents esperados (confirma / cancela / reagenda) sem precisar parser de texto livre (validar em Spec/Clarify).

---

## 5. Contrato para o Briefing (ponte para Fase 1)

- **Problema real** (de §1.1):
  > Pacientes de clínicas MPE brasileiras não comparecem a consultas agendadas e o slot é perdido sem tempo hábil para reagendamento, reduzindo receita por profissional-hora.

- **Atores principais** (de §2.1): **paciente**, **atendente**, **médico**, **sistema**, **integração externa de notificação** (WhatsApp). Auditor é ator condicional, sem UI dedicada no MVP.

- **Fluxo de alto nível** (de §2.2, em 2 parágrafos):
  > Atendente agenda a consulta no sistema. O sistema dispara um lembrete por WhatsApp 24h antes do horário (janela configurável — exato X em Clarify). O paciente responde via botões do WhatsApp (confirmar / cancelar / reagendar); o sistema atualiza o status da consulta e o atendente vê o painel do dia com o status consolidado.
  >
  > Quando o paciente não responde até a janela-limite, o sistema sinaliza "sem resposta" ao atendente, que intervém manualmente (liga, confirma pela tela, reagenda). No dia, a presença efetiva é marcada no sistema (compareceu / no-show). Todo evento — envio, resposta, mudança de status, intervenção manual — fica registrado no histórico imutável para auditoria.

- **Caminho escolhido** (de §4.1–4.2):
  > Confirmação com lembrete automático via WhatsApp, respostas por botões interativos, e fallback humano do atendente para o caso de silêncio ou paciente sem WhatsApp. Canal único no MVP, com contrato abstrato que permite adicionar outros canais em ciclos futuros sem reescrita do domínio.

- **Alternativas descartadas** (de §4.3):
  - Manual-only (não escala);
  - E-mail 1 canal (abertura baixa em MPE BR);
  - Multicanal com fallback (overengineering no MVP).

- **Regras sensíveis a detalhar em Clarify** (de §2.6): **permissão** (C-002), **deleção LGPD** (C-003), **expiração / janela de silêncio** (C-004), **histórico imutável** (C-005), **auditoria** (C-006). **Visibilidade** decidida parcialmente aqui (D-003: single-tenant MVP) e confirmação fica no Briefing.

---

## Gate de saída da Fase 0.5 (`fases/00_5_BMAD.md` + `checklists/qualidade-bmad.md`)

- [x] Problema real escrito em 1 frase, sem solução embutida (§1.1).
- [x] Causa-raiz vs sintoma distinguidos (§1.3).
- [x] Subproblemas em estrutura MECE (§1.4, com gap-check e sobreposição-check).
- [x] Atores, fluxo, entidades, fricções modelados (§2.1–2.4).
- [x] Candidatas a regras sensíveis (§5.4) marcadas (§2.6).
- [x] Analyze com ≥2 caminhos (4 caminhos A/B/C/D em §3.1), matriz de trade-offs preenchida (§3.2), pre-mortem por caminho (§3.4).
- [x] Anti-viés de confirmação satisfeito: caminho escolhido D tem 2 células 🟡 reconhecidas (§3.2).
- [x] Decide com caminho escolhido (§4.1) + justificativa (§4.2) + descartes (§4.3) + riscos aceitos (§4.4) + critérios de invalidação (§4.5).
- [x] `decision_log.md` gerado com entradas D-001, D-002, D-003.
- [x] Contrato de saída (problema/atores/fluxo/caminho) explícito (§5).

**Veredicto do Arquiteto:** 🟢 draft fechado. Pendente: validação humana via review do PR.

**Próxima fase:** Fase 1 Briefing no mesmo diretório (`briefing.md`).
