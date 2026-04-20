---
artefato: adr
fase: null
dominio: [software]
schema_version: 1
adr_id: ADR-L-001
status: Proposta
camada_afetada: 2
data: 2026-04-20
autor: Thiago Loumart
requer:
  - "Contexto"
  - "Decisão"
  - "Alternativas consideradas"
  - "Consequências"
  - "Relação com Constituição"
---

# ADR-L-001 — Provedor de WhatsApp = Meta Cloud API

**Status:** Proposta (vira Aceita ao merge do PR `w1b/f4-plan`)
**Data:** 2026-04-20
**Autor:** Thiago Loumart
**Camada afetada:** 2
**Bump de Constituição:** minor (v1.0 → v1.1)
**Escopo:** local ao módulo `001-confirmacao-consultas` (ADR-L, não ADR global do repositório)

---

## Contexto

`decision_log.md D-001` formalizou em Fase 0.5 a **stack** do canônico e mencionou "Twilio (SMS) + Z-API ou Meta Cloud API (WhatsApp) + SMTP (e-mail)" como opções de integração externa de canal. `decision_log.md D-002` fechou o **caminho estratégico** em WhatsApp-only com fallback humano, e na tabela de decisões **deixou explicitamente pendente** a escolha final entre **Meta Cloud API** e **Z-API**. `constitution.md v1.0` Camada 2 §4 reafirmou o pendente: "Meta Cloud API ou Z-API — escolha final fica para Fase 4 Plan com ADR local".

Esta é a ADR local que fecha a escolha. Escolher agora é necessário porque:
- Fase 4 F4 do plano implementa o **adaptador concreto** — precisa saber qual;
- Pré-requisitos §2.1 listam credenciais do provedor — precisam ser provisionadas antes de F4;
- Os 9 campos §29 da automação (plan.md §5) dependem do perfil de limites/latência/formato de resposta do provedor.

O contrato abstrato (`NotificacaoDriver`) **continua**; esta ADR só escolhe qual **implementação concreta** é a default no MVP.

## Decisão

**Adotar Meta Cloud API como provedor default de WhatsApp no canônico `001-confirmacao-consultas`.**

Operacionalmente:
- `WHATSAPP_DRIVER=meta` no `.env` de produção (prioridade sobre `zapi` e `noop`).
- `MetaCloudDriver` implementa `NotificacaoDriver` (`app/Infra/Notificacao/MetaCloudDriver.php`).
- `ZApiDriver` e `NoopDriver` permanecem como **implementações irmãs** no mesmo contrato — trocáveis via env em ~1 sprint de trabalho operacional (cumprindo a mitigação de D-002 e o invariante D-E-02 da constituição).
- Template de mensagem `lembrete_consulta_utility_v1` submetido ao Meta Business Manager, categoria `utility`, idioma `pt_BR`.

## Alternativas consideradas

1. **Meta Cloud API (oficial).**
   **Prós:**
   - Integração **oficial** com WhatsApp Business Platform — menor risco de ban da conta da clínica por violação de ToS.
   - Botões interativos (`quick_reply` / `url`) amplamente maduros e bem documentados.
   - Bilhetagem previsível por categoria de template (`marketing`, `utility`, `authentication`); `utility` cobre lembrete de consulta.
   - Webhooks estáveis com assinatura HMAC para verificação.
   - HSM (High Structured Message) aprovado por template + idioma reduz latência de entrega.
   - Suporte corporativo em escala.

   **Contras:**
   - Requer conta Meta Business Manager verificada (processo de 1-3 dias úteis em BR).
   - Custo por mensagem de categoria `utility` BR 2026 estimado em ~R$ 0,07–0,15 `[INFERÊNCIA]` — dentro do teto C-001 (R$ 0,20) com folga.
   - Aprovação de template é assíncrona (1-24h); mudança de template reinicia processo.
   - Reclassificação de categoria (Meta pode reclassificar `utility` → `marketing` por conteúdo) — é um critério de invalidação de D-002 formalmente.

2. **Z-API.**
   **Prós:**
   - Setup mais simples — login via QR code do WhatsApp do número da clínica.
   - Sem dependência de Meta Business Manager.
   - Mensagens não limitadas a categorias/templates rígidos (aceita texto livre).
   - Preço tier básico pode ser mais barato.

   **Contras:**
   - **Não oficial.** Opera via Web WhatsApp emulado — sujeita a suspensão pela Meta (risco de ban da conta da clínica). Histórico de provedores não oficiais sendo bloqueados é consistente.
   - Botões interativos menos maduros (dependem da versão do WhatsApp Web).
   - Sem SLO corporativo — risco de indisponibilidade sem canal de suporte escalável.
   - Vai contra o princípio de durabilidade do MVP — trocar depois custa mais do que começar oficial.

3. **Twilio WhatsApp Business API.**
   **Prós:**
   - Oficial via BSP Twilio.
   - API muito estável, mundialmente usada.

   **Contras:**
   - Custo significativamente mais alto que Meta direto (markup do BSP) — provável violação do teto C-001 `[INFERÊNCIA]`.
   - Overengineering para perfil MPE BR — Twilio brilha em escala internacional, não em MPE local.
   - Em 2024+ Meta abriu Cloud API direto; passar por BSP perdeu motivo.

**Motivo de descarte de (2) Z-API:** risco de ban da conta operacional da clínica é inaceitável em MVP de saúde. A economia marginal não compensa o risco reputacional. Fica como **implementação irmã pronta** (`ZApiDriver`) para cenários de contingência (ex: Meta suspende template genericamente) — **não** como default.

**Motivo de descarte de (3) Twilio:** custo provavelmente rompe o teto C-001 e adiciona camada de BSP que Meta Cloud API elimina. Não fica como implementação irmã por default; se surgir demanda, adicionar depois.

## Consequências

**Positivas:**
- Conformidade oficial com ToS do WhatsApp — reduz risco de ban.
- Botões interativos de alta qualidade — alinha com FR-008 (três botões) e FR-015 (texto livre → `resposta_ambigua`).
- Custo previsível dentro do teto C-001.
- Sólida base de documentação e suporte.

**Negativas / trade-offs:**
- **Processo de aprovação de template lento** — planejar F4 com buffer de ao menos 2 dias úteis para aprovação inicial.
- **Dependência da categoria `utility` permanecer "utility"** — critério de invalidação de D-002 fica ativo: se Meta reclassificar, revisitar caminho estratégico (possivelmente Caminho C multicanal).
- **Conta Meta Business Manager da clínica** precisa ser verificada antes de produção — item do onboarding comercial.

**Migração necessária:** nenhuma (greenfield). Em ciclo futuro, se escolha for revertida para Z-API, trocar valor de `WHATSAPP_DRIVER` no env + validar template local — troca em ~1 sprint.

**Novas obrigações:**
- **Monitoramento ativo** da categoria do template no Meta Business Manager (alerta manual em F10).
- **Alerta crítico** na falha de categoria (plan.md §8 alertas).
- **Documentação de onboarding** da clínica inclui setup do Meta Business Manager (material em Fase 9 Quickstart).
- **Teste de contrato** (`NotificacaoDriverContractTest`) roda sempre em CI contra mock **e** opcionalmente contra sandbox Meta em ambiente dedicado.

## Relação com Constituição

- Esta ADR **altera** a seção **§4 Stack / Sistemas de origem** da Constituição (Camada 2) — concretiza o item "Provedor de notificação (WhatsApp): Meta Cloud API ou Z-API" em **"Meta Cloud API (default); Z-API como implementação irmã para contingência"**.
- Esta ADR **NÃO altera** nenhum item de Camada 1. O contrato abstrato (`D-E-02`) continua invariante; a escolha do driver concreto é, por construção, Camada 2.
- Bump de versão da constituição: **v1.0 → v1.1** (minor).

## Relação com outros artefatos

- **ADRs relacionadas:** nenhuma global aplicável. Primeira ADR-L do módulo.
- **Decisões relacionadas:** `decision_log.md D-002` (Caminho D — WhatsApp-only); `clarify.md C-001` (teto de custo R$ 0,20).
- **Módulos impactados imediatamente:** apenas este canônico. Nenhum outro módulo do repo depende do provedor de WhatsApp.
- **`decision_log.md` que passa a citar esta ADR:** D-002 ganha nota de "provedor default formalizado em ADR-L-001" (adicionar na próxima revisão do decision_log).

## Plano de reversão (se aplicável)

Se esta ADR for revertida (ex: Meta suspender conta da clínica, custo real ultrapassar teto, reclassificação de categoria):

1. Criar `ADR-L-002 — Reversão de provedor WhatsApp para Z-API` com motivo concreto.
2. Superar ADR-L-001 (status → `Superada por: ADR-L-002`).
3. Trocar `WHATSAPP_DRIVER=zapi` no env.
4. Submeter template equivalente em Z-API (conta já logada via QR).
5. Monitorar primeiras 48h em alta frequência.
6. Registrar incidente em `risk_log.md` do canônico (a criar em Fase 12 Retrospective).

A reversão completa não requer tocar domínio (`app/Domain/**`) — só infra. É precisamente o valor da abstração `NotificacaoDriver`.

## Aprovação

| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor | Thiago Loumart | 2026-04-20 | ✓ (via commit `w1b/f4-plan`) |
| Revisor 1 | Thiago Loumart (self-review) | 2026-04-20 | ✓ (via `.review/canonical-001-f4.md`) |
| Revisor 2 (se Camada 1) | — | — | n/a (Camada 2) |
| Compliance (se D2 regulatório) | — | — | n/a (D1) |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| 2026-04-20 | Proposta | Criada em `w1b/f4-plan` |
| (data do merge) | Aceita | Aprovada via merge do PR em `main` |
