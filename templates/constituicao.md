# Constituição do Projeto — [Nome]

**Versão:** v<N>
**Data:** [YYYY-MM-DD]
**Status:** Draft | Validada | Revisão pendente
**Origem:** inferida do repositório | declarada por humano | mista

> Esta é a camada mais importante do sistema (Manual §7). Toda decisão de código, estrutura, tela, fluxo, modelo de dados e integração deve considerá-la antes de ser tomada. Conflitos entre pedidos pontuais e a constituição devem ser sinalizados, nunca resolvidos em silêncio.

---

## 1. Arquitetura
- Estilo: [monolito | monolito modular | microsserviços | serverless | worker + API | híbrido]
- Limites de domínio: [ex.: billing, accounts, notifications]
- Comunicação entre domínios: [chamada direta | eventos | fila]

## 2. Padrões
- [layered | hexagonal | CQRS | REST | RPC | event-driven]
- Pacotes / módulos / boundaries: [regra de organização]

## 3. Linguagem e runtime
- Linguagem(s): [versão]
- Runtime(s): [versão]
- Ferramenta de build: [...]

## 4. Stack
| Camada | Tecnologia | Versão | Observações |
|---|---|---|---|
| Backend framework | ... | ... | ... |
| Frontend framework | ... | ... | ... |
| Banco primário | ... | ... | ... |
| Cache | ... | ... | ... |
| Fila / stream | ... | ... | ... |
| Observabilidade | ... | ... | ... |
| Infra / deploy | ... | ... | ... |

## 5. Regras de organização
- Estrutura de pastas: [...]
- Naming: [arquivos, classes, funções, testes]
- Boundaries: [o que pode importar o quê]

## 6. Regras de segurança
- Autenticação: [...]
- Autorização: [modelo de papéis e granularidade]
- Proteção de dados sensíveis: [criptografia, masking, PII]
- Rate limit / antifraude: [...]
- Secrets: [onde ficam; como rotacionar]
- Logs e auditoria: [o que é obrigatório logar; o que é proibido logar]

## 7. Limites do MVP
Dentro:
- [...]
Fora (para evolução futura):
- [...]

## 8. Estilo de implementação
- Formatação / linter: [...]
- Convenções de commit: [ex.: Conventional Commits]
- Convenções de branch: [ex.: `NNN-nome-modulo`]
- Testes obrigatórios: [quais níveis]

## 9. Convenções de código
- Tratamento de erro: [...]
- Logging: [estrutura, nível, correlação]
- Tracing / métricas: [...]
- Validação de input: [onde; como]

## 10. Decisões estruturais permanentes
- [Decisão 1 + motivo] — ex.: "Não usamos ORM X pela política Y."
- [Decisão 2 + motivo]
- [Decisão 3 + motivo]

## 11. Regra especial — CRM / agentes / SaaS (Manual §29)
(Preencher apenas se o projeto é deste tipo.)

Toda automação/agente deve especificar: gatilho, contexto, decisão, ação, bloqueio, fallback, log, critério de sucesso, risco de falso positivo.

Prioridade máxima: confiabilidade operacional, rastreabilidade, permissão por papel, histórico de eventos, tratamento de falhas, impacto financeiro, não duplicação de lógica, evolução modular.

## 12. Exceções aprovadas
Sempre que uma feature precisar romper a constituição, registrar:
| Data | Feature | Regra rompida | Justificativa | Autor |
|---|---|---|---|---|

---

**Checklist de validação:**
- [ ] Todos os campos obrigatórios preenchidos.
- [ ] Stack reflete o que já está em uso (em brownfield).
- [ ] Regras de segurança explicitam autenticação **e** autorização.
- [ ] Limites do MVP estão listados com "dentro" e "fora".
- [ ] Humano validou ou assumiu `[RISCO ASSUMIDO]` se é v0 inferida.
