# Protocolo para Projetos Brownfield

> Manual §19. Se já existe código, a IA deve **primeiro ler, depois propor**.

## Regra mestra
Em brownfield, nenhuma Fase 2+ avança sem que a IA tenha feito uma leitura estruturada do repositório.

## Leitura inicial obrigatória
1. **Estrutura de pastas** — mapear em árvore.
2. **Arquivos de configuração** — package.json, pyproject.toml, go.mod, Dockerfile, env samples.
3. **Convenções** — linter, formatter, naming, commit style.
4. **Testes existentes** — que framework, que cobertura, que padrão de fixtures.
5. **Arquitetura** — camadas, boundaries, padrão de entrypoints.
6. **Integrações** — que serviços externos são chamados; como.
7. **Migrations** — estrutura atual do banco; histórico de migrations.
8. **Policies / auth** — modelo de papéis, middlewares.

Saída: seção "Leitura do repositório" anexada em `plan.md` ou `analyze.md`.

## Perguntas obrigatórias (Manual §19)
Antes de propor estrutura, função, tela, fluxo, policy ou componente, perguntar:

- Isso **já existe** em algum lugar do repositório?
- Já há **tabela semelhante**?
- Já há **fluxo semelhante**?
- Já há **policy semelhante**?
- Já há **componente de UI semelhante**?

Se a resposta for "sim": **reutilizar**, não recriar.

## Critérios de decisão
| Situação | Ação |
|---|---|
| Algo já existe e é adequado | Reutilizar |
| Existe algo parcialmente adequado | Estender, não clonar |
| Existe algo mal resolvido | **Registrar dívida em `analyze.md`**; decidir com humano: reutilizar + TODO / refatorar agora |
| Não existe nada semelhante | Criar seguindo a constituição |

## Conflitos com constituição
Se o repositório contradiz a constituição declarada, travar (Manual §7): qual prevalece? Documentar a decisão.

## Conflitos entre partes do repositório
Se o repo tem dois padrões internos inconsistentes (ex.: dois estilos de rota), travar antes de escolher um ao acaso.

## Output mínimo em brownfield
Após leitura:
- Árvore resumida do repo.
- Matriz "Isto já existe?" respondida.
- Decisões de reutilização vs criação registradas.
- Dívidas técnicas encontradas (sem corrigi-las sem permissão).
