# Versionamento — Harness e Constituição

> Como versões se comportam na skill, nos artefatos e na Constituição. Resolve ambiguidade clássica: "uma mudança estrutural invalida specs em andamento?"

## Três níveis de versionamento

### 1. Versão da skill `full-way-vibe-coding`
Semver (`vX.Y.Z`) no `SKILL.md` front-matter e no `README.md`.

- **Major (X):** mudança de arquitetura (ex.: nova fase obrigatória; mudança de dispatcher).
- **Minor (Y):** nova capacidade retrocompatível (ex.: novo domínio; novo protocolo).
- **Patch (Z):** correção/ajuste sem mudança de contrato.

Histórico:
| Versão | Descrição |
|---|---|
| v1.0 | Inicial — D1 software; 14 fases (0, 0.5, 1–11); 4 pastas raiz |
| v1.1 | (M1) dual-domain core: D1/D2/D3 + Fase 12 + Constituição bicamada + agentes-e-automacoes |
| v1.2 | (M2) harness Python + GitHub Action + exemplos canônicos + protocolos restantes + templates concretos D2/D3 |

### 2. Versão da Constituição de cada projeto/processo/playbook
Formato `vN.M` (sem patch — não há "correção" na Constituição; tudo é decisão formal).

- **Major (N):** alteração em Camada 1 (invariantes). Exige ADR com `camada_afetada: 1` + aprovação humana + nota de ruptura.
  - Exemplo: v1.0 → v2.0 quando LGPD passa a exigir consentimento explícito num fluxo Z.
- **Minor (M):** alteração em Camada 2 (escolhas). Exige ADR com `camada_afetada: 2`.
  - Exemplo: v1.2 → v1.3 ao adicionar Redis como cache.

### 3. Versão dos schemas YAML e scripts do harness (M2)
Campo `schema_version: N` em cada template e schema. Scripts Python leem esse campo para validar compatibilidade.

- `schema_version = 1`: versão inicial (v1.1 da skill).
- Bump quando: formato do front-matter muda (ex.: nova coluna obrigatória) OU quando um gate bloqueante é adicionado/removido.

## Regra mestre — specs em andamento não quebram

> Specs que **já começaram** seguem a versão da skill em que começaram. Versão nova só afeta specs **novas**.

Implicações:
- Um módulo iniciado em v1.1 da skill **não** é obrigado a adotar harness executável (v1.2) retroativamente.
- Pode adotar opcionalmente: rodar linter em modo warning, ver o que apareceria se estivesse em v1.2, aplicar mudanças estruturais caso faça sentido.
- A retrospectiva (Fase 12) pode recomendar portar para versão nova — vira `D-NNN` do módulo ou, se for padrão, ADR global.

## Quando bumpar a skill

Bump da skill (via PR no repo da skill) exige:
- Major: ADR global explícita no próprio repo da skill (`docs/adrs/ADR-NNN.md`) + atualização do CHANGELOG + migração documentada para usuários existentes.
- Minor: CHANGELOG + atualização do README com as novas capacidades.
- Patch: CHANGELOG de 1 linha.

## Breaking change da skill

Se uma mudança **quebra** retrocompatibilidade (ex.: renomear uma fase, remover um template):
1. É major bump obrigatório.
2. ADR global no repo da skill com plano de migração.
3. Versão anterior permanece acessível por git tag.
4. Aviso em destaque no README da versão nova.

Exemplos de mudanças que **são** breaking change:
- Renumerar fases existentes.
- Remover marcadores epistêmicos.
- Mudar regra de marcação bicamada da Constituição.
- Redefinir significado de `D-NNN`.

## Como registrar a versão num artefato

Todo artefato escrito por uma versão específica da skill deve ter no front-matter:
```yaml
skill_version: v1.1
```
Linter do harness (M2) usa isso para aplicar a validação **da versão em que o artefato foi criado**, não a atual. Isso implementa a regra "specs em andamento não quebram".

## Rollback da skill
- Git tag por versão.
- Para desinstalar v1.2 e voltar a v1.1: `git checkout v1.1.0` no repo da skill.
- Artefatos produzidos em v1.2 podem não validar em v1.1 — documentar.

## Ciclo de deprecação
1. **Depreciação** anunciada em CHANGELOG + README da versão corrente.
2. **Aviso bloqueante** uma versão depois (linter emite warning).
3. **Remoção** uma versão depois disso (linter emite erro).

Total mínimo: 2 versões minor entre anúncio e remoção.

## Registro em `MEMORY.md` (ou equivalente do sistema consumidor)
Se a skill é usada por um sistema (ex.: Claude Code), recomenda-se registrar a versão efetiva nos memos/config do sistema:
```
Skill full-way-vibe-coding em uso: v1.1
Constituição do projeto: v1.3 (ADR-007)
```
