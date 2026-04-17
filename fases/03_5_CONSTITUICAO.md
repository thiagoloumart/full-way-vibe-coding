# Fase 3.5 — Constituição do Projeto (bicamada)

> Manual §7: "A constituição é a camada mais importante do sistema." Esta fase formaliza a constituição em **duas camadas**: invariantes (Camada 1) e escolhas (Camada 2). O objetivo é separar o que **não muda durante o ciclo** do que é **mutável via ADR**.

## Objetivo
Garantir que existe, para o projeto/processo/playbook atual, um documento canônico que:
- Fixa os **princípios invariantes** (Camada 1) que nenhuma fase pode violar.
- Registra as **escolhas atuais** (Camada 2) — stack técnica (D1) / sistemas de origem (D2) / estrutura de árvore (D3) — com rastreabilidade via ADRs em [`../governanca/adr-global.md`](../governanca/adr-global.md).

Toda decisão subsequente respeita essas duas camadas; conflitos são sinalizados, nunca resolvidos em silêncio.

## Quando executar
- **Sempre** antes da Fase 4 (Plan / Plano-adoção).
- Se já existe constituição e cobre o módulo atual: apenas confirmar, versionar, referenciar.
- Se não existe ou está desatualizada: criar ou atualizar antes do plano.

## Entradas
- `briefing.md` + `spec.md`/`mapa-to-be.md`/`criterios.md` + `clarify.md` validados.
- Stack/sistema/estrutura declarada pelo humano (se houver).
- Em brownfield: leitura do repositório feita (ver [`../protocolos/brownfield.md`](../protocolos/brownfield.md)).
- ADRs ativas em `governanca/adr-global.md` (se houver).

## Saídas
- `constitution.md` (usar [`../templates/constituicao.md`](../templates/constituicao.md)) com:
  - Bloco `<!-- CAMADA_1_BEGIN -->` … `<!-- CAMADA_1_END -->` marcando invariantes.
  - Bloco `<!-- CAMADA_2_BEGIN -->` … `<!-- CAMADA_2_END -->` marcando escolhas.
  - Seção "ADRs ativas" listando ADR-NNN vigentes e o que cada uma alterou.
  - Campo `bicamada: true` no front-matter.
  - Versionamento explícito: `vN.M` (N = major; M = minor).

## As duas camadas — o que vai em cada uma

### Camada 1 (invariantes) — não muda durante o ciclo
**Alterar qualquer item desta camada exige ADR com `camada_afetada: 1` + major bump (vN → v(N+1).0) + aprovação humana explícita.**

Conteúdo típico por domínio:

| Seção | D1 software | D2 processo | D3 playbook |
|---|---|---|---|
| Princípios de conduta | Humano/IA, §5.4 D1, marcadores | Humano/IA, §5.4 D2, marcadores | Humano/IA, §5.4 D3, marcadores |
| Valores bloqueantes | Segurança, privacidade, PII, política de dados | LGPD, CVM/setorial, código de ética, alçadas máximas absolutas | Princípios de decisão inegociáveis, escopo inviolável |
| Arquitetura estrutural | Estilo (monolito modular/microsserviços); boundaries | Compliance framework; organização de papéis (RACI macro) | Estrutura inviolável da árvore |
| Regras de segurança estruturais | Auth/autz framework, rate-limit policy, secrets policy | Regulação aplicável, DPO, canais de denúncia | Autoridade de flexibilização |
| Limites do MVP | O que está dentro/fora | O que está dentro/fora | O que está dentro/fora |
| Decisões estruturais permanentes | "Não usamos ORM X" / "Todo endpoint público é versionado em /v1" | "Todo contrato PJ passa por compliance" | "Nenhum critério tem peso < 10%" |

### Camada 2 (escolhas) — mutável via ADR
**Alterar qualquer item desta camada exige ADR com `camada_afetada: 2` + minor bump (vN.M → vN.(M+1)).**

Conteúdo típico por domínio:

| Seção | D1 software | D2 processo | D3 playbook |
|---|---|---|---|
| Stack principal | Linguagem + framework + banco + cache + fila + infra | Sistemas de origem (CRM, ERP, ticketing) | N/A |
| Padrões | Convenções de código, commit, branch, teste | Notação de mapa (BPMN 2.0, fluxograma) | Formato da árvore (notação, níveis) |
| Ferramentas operacionais | Linter, formatter, CI/CD | Ferramenta de governança (Confluence, Notion) | Template de registro de decisão |
| Estilo / formato | Convenção de logs/métricas/traces | Periodicidade de relatório + destinatários | Periodicidade de revisão do playbook |

## Fluxo da Fase

### 3.5.a Levantamento
1. Existe `constitution.md` no projeto? Se sim, qual versão?
2. Carregar ADRs ativas em `governanca/adr-global.md` → listar na seção "ADRs ativas" da constituição.
3. Em brownfield: extrair Camada 2 de fato do repositório (stack real, padrões reais) antes de propor mudança.

### 3.5.b Decisão por camada
- Oferecer 3–5 caminhos para cada item de Camada 2 ainda não decidido, com prós/contras.
- Tocar em Camada 1 **só** se briefing/spec/clarify revelou necessidade e há preparo para ADR major + aprovação humana.
- Conflitos entre pedido pontual do módulo e constituição devem ser sinalizados: nunca resolver silenciosamente.

### 3.5.c Versionamento
- Nova constituição v0 (greenfield): marcar como `[INFERÊNCIA]` até humano validar formalmente; então vira v1.0.
- Mudança em Camada 2: `vN.M → vN.(M+1)`, ADR referenciada no cabeçalho.
- Mudança em Camada 1: `vN → v(N+1).0`, ADR com `camada_afetada: 1`, nota de ruptura.
- Registro no rodapé da constituição: histórico de versões com link para cada ADR.

### 3.5.d Materialização por domínio
- **D1:** ver [`../domains/software.md §Constituição`](../domains/software.md#constituição--o-que-esperar-nesta-camada).
- **D2:** ver [`../domains/processo.md §Constituição`](../domains/processo.md#constituição--o-que-esperar-nesta-camada).
- **D3:** ver [`../domains/playbook.md §Constituição`](../domains/playbook.md#constituição--o-que-esperar-nesta-camada).
- **Híbrido:** Camada 1 unificada; Camada 2 com sub-seções `[D1]` e `[D2]`. Ver [`../domains/hibrido.md`](../domains/hibrido.md).

## Guardrail — como cada fase posterior consulta
A partir daqui, toda fase posterior consulta a constituição:

| Fase | O que checa |
|---|---|
| 4 Plan | Camada 2 (stack/sistemas) é respeitada; mudanças propostas viram proposta de ADR |
| 5 Tasks | Convenções de código/estrutura (Camada 2) |
| 6 Analyze | Matriz dedicada Spec × Constituição; bloqueio se Camada 1 foi tocada sem ADR major |
| 7 Implement | Reaproveita padrões; não introduz lib nova sem ADR minor |
| 10 Review | Diff cruzado com Camada 2; logs/métricas seguem padrão |
| 11 Merge | Bloqueia se houve alteração de Camada 1 sem ADR `camada_afetada: 1` e aprovação |
| 12 Retrospective | Aprendizado pode gerar proposta de mudança de Camada 2 (minor) ou Camada 1 (major) |

## Riscos da fase
- Constituição genérica ("microsserviços", "clean code") sem compromisso real.
- Constituição v0 gerada por inferência tratada como canônica sem validação humana.
- Brownfield: propor Camada 2 ignorando padrões existentes (gera duplicação e caos).
- Spec/to-be/critérios entrar em rota de colisão com Camada 1 sem sinalizar.
- Misturar camadas: item que deveria ser Camada 1 acabando na Camada 2, abrindo espaço para mudança leve em algo inviolável.

## Gate de avanço
- [ ] `constitution.md` existe, com marcadores `<!-- CAMADA_1_BEGIN/END -->` e `<!-- CAMADA_2_BEGIN/END -->` corretos.
- [ ] Campo `bicamada: true` no front-matter.
- [ ] Todos os campos obrigatórios do domínio ativo preenchidos.
- [ ] Humano validou (ou, em v0, autorizou explicitamente com `[RISCO ASSUMIDO]`).
- [ ] Conflitos entre spec/to-be/critérios e constituição foram sinalizados e resolvidos.
- [ ] ADRs citadas existem em `governanca/adr-global.md`.

## O que invalida a fase
- Item misturado entre camadas (ex.: "segurança" em Camada 2).
- Marcadores `<!-- CAMADA_*_* -->` ausentes ou incorretos.
- Contradição com spec/to-be/critérios já aprovados.
- Camada 2 propõe stack/sistema não disponível no ambiente.
- Mudança de Camada 1 sem ADR major registrado.

## Sinal de travamento
- Humano quer stack/sistema incompatível com a feature → travar e mostrar o trade-off.
- Em brownfield, o repo revela padrões contraditórios entre si → travar, pedir decisão de qual prevalece.
- Camada 1 e requisito colidem irreconciliavelmente → travar, escolher qual muda antes de seguir.
