# Checklist — Qualidade do BMAD

Aplicar no fim da Fase 0.5, antes de avançar para o Briefing.

## Breakdown
- [ ] Problema real escrito em **1 frase**, sem mencionar solução.
- [ ] Quem sofre, quando e com que frequência está claro.
- [ ] Causa-raiz e sintoma aparente foram **distinguidos** (tabela preenchida).
- [ ] Subproblemas listados em estrutura **MECE** (sem sobreposição, sem gap).
- [ ] Core vs periférico classificado.

## Model
- [ ] Todos os atores nomeados com papel, permissões de alto nível e restrições.
- [ ] Fluxo principal narrado em **3–7 passos**, caminho feliz, sem ramificação de erro.
- [ ] Entidades listadas **sem tipos técnicos** (nada de "tabela", "collection", "DTO").
- [ ] Fricções previsíveis apontadas (gargalos, esperas, dependências).
- [ ] O que precisa persistir está explícito.
- [ ] **Regras sensíveis (Manual §5.4)** aplicáveis foram marcadas para Clarify.

## Analyze
- [ ] **≥2 caminhos plausíveis** descritos (1 parágrafo cada).
- [ ] Matriz de trade-offs preenchida com 🟢/🟡/🔴 + justificativa por célula.
- [ ] **Anti-viés de confirmação:** a matriz do caminho escolhido **não está toda 🟢** — há pelo menos 1 🟡 ou 🔴 reconhecido como trade-off aceito. Se está toda verde, o humano foi **provocado** a enxergar desvantagens e registrou que elas não existem.
- [ ] **Pre-mortem** feito para cada caminho (2–3 causas de falha por caminho).
- [ ] Menor caminho funcional identificado.
- [ ] Riscos de overengineering apontados por caminho.

## Decide
- [ ] Caminho escolhido **nominado** (letra + nome curto).
- [ ] Justificativa explícita com critério dominante.
- [ ] **Cada alternativa descartada tem motivo** (nenhuma linha vazia).
- [ ] Riscos aceitos marcados com `[RISCO ASSUMIDO]`.
- [ ] Critérios de invalidação listados.
- [ ] Hipóteses em aberto marcadas como `[ ]` a validar.
- [ ] `decision_log.md` com ao menos uma entrada `D-NNN` assinada.

## Regras sensíveis
- [ ] Nenhuma decisão sobre cobrança / permissão / estorno / deleção / expiração / visibilidade / histórico / auditoria foi tomada pela IA em silêncio.
- [ ] Cada regra sensível aplicável tem autor **humano** no `decision_log.md`.

## Marcadores
- [ ] Suposições marcadas com `[INFERÊNCIA]`.
- [ ] Itens pendentes marcados com `[NEEDS CLARIFICATION: …]`.
- [ ] Decisões humanas pendentes marcadas com `[DECISÃO HUMANA: …]`.

## Contrato para o Briefing
- [ ] Problema real, atores principais, fluxo de alto nível e caminho escolhido foram copiados para a seção "Contrato para o Briefing".
- [ ] Lista de regras sensíveis a detalhar em Clarify foi propagada.

## Coerência com Fase 0
- [ ] `bmad.md` não contradiz a reformulação da Fase 0.
- [ ] Se contradisse, voltou-se à Fase 0 antes de fechar o BMAD.

## Validação
- [ ] Humano leu e aprovou `bmad.md` e `decision_log.md`.

Se qualquer item ficou `❌`: **não avançar** para Fase 1.
