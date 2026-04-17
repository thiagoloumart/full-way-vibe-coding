---
artefato: bmad
fase: 0.5
dominio: [any]
schema_version: 1
requer:
  - "1. Breakdown — decomposição do problema"
  - "2. Model — modelagem do sistema"
  - "3. Analyze — análise de alternativas"
  - "4. Decide — decisão registrada"
  - "5. Contrato para o Briefing (ponte para Fase 1)"
---

# BMAD — [Nome do Módulo]

**Data:** [YYYY-MM-DD]
**Autor:** [humano responsável]
**Status:** Draft | Validado
**Projeto:** [nome] — [greenfield | brownfield | extensão]
**Referência:** reformulação confirmada na Fase 0; hipóteses estratégicas iniciais

---

## 1. Breakdown — decomposição do problema

### 1.1 Problema real (1 frase)
[Enunciado do problema **sem mencionar solução**.]

### 1.2 Quem sofre, quando, com que frequência
- **Quem:**
- **Momento em que dói:**
- **Frequência / intensidade:**
- **Consequências quando não resolvido:**

### 1.3 Causa-raiz vs sintoma aparente
| Observado (sintoma) | Causa-raiz provável | Evidência |
|---|---|---|
| ... | ... | ... |

### 1.4 Subproblemas (MECE)
> Mutuamente exclusivos, coletivamente exaustivos. Sem sobreposição, sem gap.

- **Subproblema A:** [1 frase]
- **Subproblema B:** [1 frase]
- **Subproblema C:** [1 frase]

### 1.5 Core vs periférico
| Subproblema | Core (sem isso não resolve) | Periférico (alivia mas não resolve) |
|---|---|---|
| A | ✅ | |
| B | | ✅ |

**Foco deste ciclo:** [lista apenas os core]

---

## 2. Model — modelagem do sistema

### 2.1 Atores
| Papel | Descrição (1 linha) | Pode (alto nível) | Não pode |
|---|---|---|---|
| Usuário final | ... | ... | ... |
| Admin | ... | ... | ... |
| Sistema | ... | ... | ... |
| Integração externa | ... | ... | ... |

### 2.2 Fluxo principal ponta a ponta (3–7 passos)
> Linguagem natural, sem ramificações de erro. Caminho feliz.

1. [Ator] [verbo] [objeto]
2. [Sistema] [verbo] [objeto]
3. ...

### 2.3 Entidades
- **[Entidade 1]:** [o que representa, quem cria, quem lê, quem modifica]
- **[Entidade 2]:** [...]

### 2.4 Fricções previsíveis
- [Gargalo / espera humana / decisão manual / dependência externa]
- [...]

### 2.5 O que precisa persistir
- [Dado entre sessões / histórico / auditoria]

### 2.6 Regras sensíveis (Manual §5.4)
Marcar cada uma que se aplica ao módulo. Se a decisão **já saiu pronta** do BMAD (ex: com o caminho escolhido em Decide), registrar a `D-NNN` — o Clarify depois apenas **verifica consistência**, não redecide. Se ainda está ambígua, deixar `D-NNN` em branco: vira candidata formal a decisão em Clarify.

| Regra | Aplica? | Já decidida em | Status |
|---|---|---|---|
| Cobrança | sim/não | D-NNN ou — | decidida no BMAD / candidata a Clarify |
| Permissão / autorização | sim/não | — | ... |
| Estorno / cancelamento | sim/não | — | ... |
| Deleção | sim/não | — | ... |
| Expiração | sim/não | — | ... |
| Visibilidade entre papéis | sim/não | — | ... |
| Histórico | sim/não | — | ... |
| Auditoria | sim/não | — | ... |

---

## 3. Analyze — análise de alternativas

### 3.1 Caminhos plausíveis (2–4)
| # | Caminho | Descrição (1 parágrafo) |
|---|---|---|
| A | [nome curto] | [abordagem geral, sem stack] |
| B | [nome curto] | [abordagem geral] |
| C | [nome curto] | [abordagem geral] |

### 3.2 Matriz de trade-offs
> Usar 🟢 / 🟡 / 🔴 + 1 frase de justificativa em cada célula.

| Caminho | Velocidade | Qualidade | Risco | Reversibilidade | Custo |
|---|---|---|---|---|---|
| A | 🟢 ... | 🟡 ... | 🟡 ... | 🟢 ... | 🟢 ... |
| B | 🟡 ... | 🟢 ... | 🟢 ... | 🟡 ... | 🟡 ... |
| C | 🔴 ... | 🟢 ... | 🔴 ... | 🔴 ... | 🔴 ... |

### 3.3 Menor caminho funcional
[MVP esquelético dentre os listados — qual prova valor com mínimo esforço.]

### 3.4 Pre-mortem por caminho
Para cada caminho, responder: **"Se daqui a 30 dias isso falhou, por quê terá sido?"**

- **Caminho A:** 1) ...  2) ...  3) ...
- **Caminho B:** 1) ...  2) ...  3) ...
- **Caminho C:** 1) ...  2) ...  3) ...

### 3.5 Riscos de overengineering
- **Caminho A:** [onde pode virar complexidade desnecessária]
- **Caminho B:** [...]
- **Caminho C:** [...]

---

## 4. Decide — decisão registrada

### 4.1 Caminho escolhido
**[Letra + nome curto]**

### 4.2 Justificativa (critério dominante)
[1 parágrafo — velocidade / reversibilidade / custo / qualidade / alinhamento com restrições]

### 4.3 Alternativas descartadas
> **Obrigatório.** Nenhum descarte pode ficar vazio.

| # | Caminho | Motivo do descarte |
|---|---|---|
| B | ... | ... |
| C | ... | ... |

### 4.4 Riscos aceitos
Marcar com `[RISCO ASSUMIDO]` e registrar no `decision_log.md`.

- [RISCO ASSUMIDO] ...
- [RISCO ASSUMIDO] ...

### 4.5 Critérios de invalidação
> O que força revisão dessa decisão daqui para frente.

- Se ...
- Se ...

### 4.6 Hipóteses em aberto
> Coisas que precisam ser validadas durante ou depois.

- [ ] ...
- [ ] ...

---

## 5. Contrato para o Briefing (ponte para Fase 1)

- **Problema real:** [1 frase extraída de 1.1]
- **Atores principais:** [lista extraída de 2.1]
- **Fluxo de alto nível:** [narrativa de 2.2 em 1–2 parágrafos]
- **Caminho escolhido:** [1 frase de 4.1–4.2]
- **Alternativas descartadas:** [lista curta]
- **Regras sensíveis a detalhar em Clarify:** [lista de 2.6 marcadas]

---

**Checklist antes de aprovar:**
- [ ] Problema real em 1 frase, sem solução.
- [ ] Causa-raiz vs sintoma distinguidos.
- [ ] Subproblemas MECE.
- [ ] Core vs periférico classificado.
- [ ] Todos os atores com papel claro.
- [ ] Fluxo principal em 3–7 passos.
- [ ] Entidades sem tipos técnicos.
- [ ] Regras sensíveis (§5.4) marcadas.
- [ ] ≥2 caminhos em Analyze com matriz de trade-offs.
- [ ] Pre-mortem feito para cada caminho.
- [ ] Decide com descartes explícitos por alternativa.
- [ ] Riscos aceitos marcados `[RISCO ASSUMIDO]`.
- [ ] `decision_log.md` com ≥1 `D-NNN` assinada.
- [ ] Contrato para o Briefing preenchido.
- [ ] Humano validou.
