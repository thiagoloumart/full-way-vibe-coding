# Checklist — Qualidade da Spec

Aplicar no fim da Fase 2.

## Forma
- [ ] Spec em Markdown válido.
- [ ] Cada FR no formato `System MUST <capacidade verificável>`.
- [ ] Cada User Story tem prioridade (P1, P2, …).
- [ ] Cada User Story tem **Independent Test** que explica como testá-la isolada.
- [ ] Cada User Story tem pelo menos um cenário Given/When/Then.

## Comportamento vs arquitetura
- [ ] Zero menção a banco específico, framework, ORM, biblioteca.
- [ ] Zero menção a estrutura de pastas ou padrões internos de código.
- [ ] Requisitos descrevem **o que o sistema faz**, não **como faz**.

## Cobertura
- [ ] Edge cases mapeados (cancelamento no meio, payload inválido, falha de API, falha parcial, permissão negada, falta de crédito/quota).
- [ ] Permissões por papel descritas.
- [ ] Estados de erro previsíveis listados.
- [ ] Key Entities definidas quando há dados persistentes.
- [ ] Success Criteria mensuráveis e tecnologia-agnósticos.
- [ ] "Out of Scope" explícito.

## Itens comuns de software (`PROMPT_SPEC.md`)
Verificar se cada um foi abordado ou descartado conscientemente:
- [ ] Autenticação / autorização.
- [ ] Validações.
- [ ] Estados vazios / mensagens de erro.
- [ ] Integração externa.
- [ ] Auditoria.
- [ ] Notificações.
- [ ] Performance.
- [ ] Responsividade.
- [ ] Disponibilidade.
- [ ] Privacidade / LGPD.
- [ ] Rastreabilidade.
- [ ] Relatórios.
- [ ] Importação / exportação.
- [ ] Permissões administrativas.

## Marcadores
- [ ] Ambiguidades marcadas com `[NEEDS CLARIFICATION: …]`.
- [ ] Regras sensíveis (cobrança, permissão, estorno, deleção, expiração, visibilidade, histórico, auditoria) marcadas com `[DECISÃO HUMANA: …]`.
- [ ] Suposições marcadas com `[INFERÊNCIA]`.

## Priorização
- [ ] Pelo menos uma User Story P1 existe e entrega MVP funcional sozinha.
- [ ] Priorização não é "tudo P1".

## Validação
- [ ] Spec validada pelo humano.

Se algum item ficou `❌`: corrigir antes da Fase 3.
