---
artefato: briefing
fase: 1
dominio: [any]
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

# Briefing de Software: [Nome do Sistema ou Módulo]

**Data:** [YYYY-MM-DD]
**Autor do briefing:** [humano responsável]
**Status:** Draft | Validado
**Projeto:** [nome] — [greenfield | brownfield]

---

## 1. Visão Geral da Solução
[Um parágrafo bem redigido descrevendo o sistema em linguagem de negócio. Sem jargão técnico. Responde: "o que é, para quem, por quê".]

## 2. O Problema
[Resumo das dores reais, com relatos concretos quando possível.]

- Dor principal:
- Quem sofre:
- Consequências quando não resolvido:

## 3. Público-Alvo
[Quem vai usar. Perfis, contexto, nível de maturidade digital.]

- Público primário:
- Público secundário:
- Contexto de uso (presencial, remoto, mobile, desktop):

## 4. Modelo de Precificação e Negócio
[Como o projeto se sustenta.]

- Modelo: [assinatura | por transação | licença única | freemium | interno]
- Quem paga:
- Gatilho de cobrança, se aplicável:

## 5. Perfis de Acesso
[Quem acessa o quê.]

| Perfil | Descrição | Pode (alto nível) |
|---|---|---|
| Admin | ... | ... |
| Cliente | ... | ... |
| ... | ... | ... |

## 6. Canais / Superfícies
- Web / mobile / API / CLI / painel interno / outro:
- Ambientes (dev, staging, prod), se já decidido:

## 7. Módulos e Casos de Uso
[O que o sistema faz. Usar verbos de ação claros. Um módulo por seção.]

### 7.1 Módulo [Nome]
**Objetivo do módulo:** [1 linha]
**Ações principais:**
- [Perfil] [verbo] [objeto] — ex.: "Admin emite boleto"
- [Perfil] [verbo] [objeto]
- Sistema [verbo] [objeto]

**Regras de negócio específicas deste módulo:**
- ...

### 7.2 Módulo [Nome]
...

## 8. Fluxo Principal (alto nível)
[Narrativa curta do caminho feliz, ponta a ponta.]

1. Usuário ...
2. Sistema ...
3. ...

## 9. Restrições e Não-objetivos
- [o que **não** faz parte do escopo]
- [limitações declaradas: prazo, equipe, compliance, integrações obrigatórias]

## 10. Itens ainda em aberto
- [ ] [ponto que precisa virar `[NEEDS CLARIFICATION]` na spec]
- [ ] ...

---

**Checklist mínimo antes de aprovar:**
- [ ] Zero decisões técnicas no texto.
- [ ] Cada ação usa verbo concreto.
- [ ] Perfis estão definidos.
- [ ] Módulos foram explorados com a pergunta cíclica ("tem mais alguma ação?").
- [ ] Humano validou.
