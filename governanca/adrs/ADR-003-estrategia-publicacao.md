---
artefato: adr
fase: null
dominio: [any]
schema_version: 1
adr_id: ADR-003
status: Aceita
camada_afetada: 2
data: 2026-04-17
autor: Thiago Loumart
requer:
  - "Contexto"
  - "Decisão"
  - "Alternativas consideradas"
  - "Consequências"
  - "Relação com Constituição"
---

# ADR-003 — Estratégia de publicação da v1.2 (mesmo repo vs novo repo)

**Status:** Aceita
**Data:** 2026-04-17
**Autor:** Thiago Loumart
**Camada afetada:** 2 (escolha — afeta visibilidade e continuidade histórica)
**Bump de Constituição:** minor (v1.1 → v1.1.2) — materializado quando `constitution.md` do próprio repo for instanciado

> Decisão **bloqueante para W4**. Não pode ser deixada implícita porque afeta:
> (a) continuidade do histórico de commits, (b) confiança pública do método, (c) URLs
> públicas que outros projetos podem ter referenciado, (d) estratégia de marketing do
> dogfood (narrativa de evolução vs narrativa de recomeço).
>
> **Esta ADR não será marcada Aceita sem aprovação humana explícita.** Qualquer sessão
> de IA que a encontrar em status `Proposta` deve **travar** ao chegar em W4 (publicação)
> e pedir decisão.

---

## Contexto

Durante W0 da adequação v1.2 (dogfood extremo da própria skill para subir score SDD de
54/100 para ≥75/100), o autor levantou a hipótese: "quando formos subir, acho que é
melhor criar outro repositório pois são muitas mudanças, concorda?"

O repo atual `thiagoloumart/full-way-vibe-coding` tem:
- 4 commits em `main` (Initial → feat(bmad) → refactor → feat(v1.1)).
- Commit `6efe197` monolítico (+2543/−431/39 arquivos) — violação do princípio de PRs pequenos.
- Todos os commits têm `author email` = `thiagomartins@192.168.0.11` (hostname local,
  não email verificado) — GitHub não linka ao perfil `thiagoloumart`.
- Score SDD entrada: 54/100 (Frágil).

A escolha é estrutural: continuar no mesmo repo preserva histórico e a narrativa
"score 54 → 75 em 60 dias via dogfood"; criar repo novo oferece história limpa.

## Decisão

**Opção A — continuar em `thiagoloumart/full-way-vibe-coding`, tag `v1.2.0` ao fim da adequação.**

Decisão do proprietário do repo em 2026-04-17, na sequência do fim de W0. Recomendação
do autor da ADR confirmada.

Complemento operacional decidido na mesma conversa:
- **Push imediato** dos commits de W0 para `origin/main` (em vez do plano original de
  "nada no GitHub até W4"), para preservar backup, habilitar CI em W1 e fortalecer a
  narrativa pública de evolução.
- **Branch protection** ativada **ao fim de W1** (quando o lint entrar em CI e os
  `required status checks` ganharem dentes); antes disso é cerimônia.

Ver seção "Alternativas consideradas" para os 3 caminhos reais e justificativa.

## Alternativas consideradas

### A) Continuar em `thiagoloumart/full-way-vibe-coding`, tag `v1.2.0` (recomendada)

Todas as Waves W0–W4 ocorrem neste repo. No fim, tag `v1.2.0` marca a adequação.
Os 4 commits pré-v1.2 ficam como "história do diagnóstico".

- **Prós:**
  - Narrativa forte: "do commit `6efe197` com score 54 à tag `v1.2.0` com score ≥75 em 60 dias via dogfood extremo" — exemplo vivo do método.
  - Pratica o princípio da skill: "toda nova spec nasce da master atualizada".
  - Zero ambiguidade pública sobre qual repo é o oficial.
  - Links externos continuam válidos.
  - Quem clonou em v1.1 recebe a evolução via `git pull`.
- **Contras:**
  - Histórico pré-v1.2 carrega o `author email` incorreto. Mitigação: usar `git rebase --root --reset-author` **uma única vez antes de W4** (aceitável porque o histórico público ainda é pequeno e a reescrita seria anunciada em `CHANGELOG.md`).
  - Commit `6efe197` monolítico fica visível para sempre. Mitigação: marcar em ADR como "commit de transição — antes da disciplina de PRs pequenos entrar em vigor".

### B) Novo repo limpo (ex: `thiagoloumart/full-way-vibe-coding-v2` ou `full-way-sdd`)

Repo novo com histórico do zero. v1.1 permanece disponível para arqueologia em link explícito.

- **Prós:**
  - Histórico limpo sem commits com `author email` incorreto.
  - Commit inicial pode ser bem formado (artefatos já organizados).
  - Chance de rebranding / renomeação se o posicionamento mudou.
- **Contras:**
  - **Antipadrão da própria skill**: começar do zero é exatamente o que a filosofia combate. Enfraquece credibilidade.
  - **Perde narrativa de evolução** — "fizemos v2 porque o v1 estava ruim" é pior venda que "subimos v1 de 54 para 78 via dogfood".
  - **Ambiguidade pública**: qual repo é o oficial? Quem tem o link antigo descobre como? Manutenção dupla vira divergência.
  - **Custo de reter 2 repos** para quem queira arqueologia vs o ativo.

### C) Novo repo com cherry-pick seletivo + repo atual arquivado com aviso

Repo novo, mas traz cherry-picks dos commits v1.1 relevantes; repo atual recebe README apontando para o novo + arquiva-se.

- **Prós:** histórico curado; transição explícita.
- **Contras:** todos os de B + ainda complexo de executar; cherry-pick seletivo tem potencial de introduzir bugs sutis; duas fontes de verdade por tempo indeterminado.
- **Descartada em análise inicial.**

## Consequências (depende da opção aceita)

### Se Opção A for aceita (mesmo repo)

- **Positivas:** narrativa forte, continuidade, pratica o método.
- **Negativas / trade-offs:** tratar histórico pré-v1.2 com transparência (ADRs retroativas como esta + um commit de "rebase --reset-author" antes de W4).
- **Migração necessária:** usuários externos — nenhuma; clones locais pegam por `git pull`.
- **Novas obrigações:**
  - `CHANGELOG.md` registrando v1.0, v1.1, v1.2 com dates e principais mudanças.
  - Antes de W4, executar `git rebase -i --root --reset-author` para corrigir `author email` nos commits pré-v1.2 (opcional mas recomendado) + `git push --force-with-lease`.

### Se Opção B for aceita (novo repo)

- **Positivas:** histórico limpo.
- **Negativas / trade-offs:** perder tempo de marketing; escrever migration notes para usuários externos.
- **Migração necessária:**
  - Criar novo remote.
  - Escolher nome (sugestão autor: manter `full-way-vibe-coding` se o escopo não mudou; evitar `v2` no nome — semver resolve isso).
  - Escrever `README.md` do repo antigo apontando para o novo + `ARCHIVED.md` explicando.
  - Arquivar repo antigo no GitHub Settings.
- **Novas obrigações:** idem Opção A em termos de CHANGELOG, mais documentação de migração.

## Relação com Constituição

- Esta ADR **altera** a **Camada 2 §? (Estratégia de distribuição)** da Constituição da
  skill (seção a ser escrita em W0/W1 quando `constitution.md` for instanciado).
- Esta ADR **NÃO altera** Camada 1.
- Declaração explícita: **Esta ADR não altera nenhum item de Camada 1.**

## Relação com outros artefatos

- ADRs relacionadas: ADR-001 (v1.1 dual-domain) — pressuposto.
- Artefatos impactados:
  - `CHANGELOG.md` (a ser criado em W3).
  - `README.md` (reescrita em W4).
  - Se Opção B: `ARCHIVED.md` no repo antigo.

## Plano de reversão

### Se Opção A aceita e depois se quiser ir para repo novo

Sempre viável: push para novo remote + escrever `ARCHIVED.md` no repo atual. Sem perda.

### Se Opção B aceita e depois se quiser voltar para o repo antigo

Caro: reconciliar histórico duplo; merge reverso; quem clonou novo precisa resetar.
**Contra esta irreversibilidade, a recomendação da ADR fica em Opção A.**

## Gate de aprovação

Esta ADR só pode ser aceita com marcador `[DECISÃO HUMANA: tema=estratégia-publicação]`
assinado pelo proprietário do repo em forma explícita:

- Resposta do humano: `"Opção A — mantém o repo"` ou `"Opção B — novo repo, nome=<X>"`.
- Atualização da ADR: status `Aceita`, `Histórico de status` com data e decisão.
- Se aceita em meio à execução, W4 prossegue com a opção aceita.

## Aprovação

| Papel | Nome | Data | Assinatura |
|---|---|---|---|
| Autor da ADR (recomendação Opção A) | Thiago Loumart | 2026-04-17 | ✓ |
| **Proprietário do repo — aprovador final** | Thiago Loumart | 2026-04-17 | ✓ |

## Histórico de status

| Data | Status | Mudança |
|---|---|---|
| 2026-04-17 | Proposta | Criada em W0 por demanda explícita do autor durante sessão de dogfood. |
| 2026-04-17 | Aceita | Aprovada pelo proprietário (Thiago Loumart) — Opção A (mesmo repo, tag v1.2.0) com push imediato de W0 e branch protection ao fim de W1. |
