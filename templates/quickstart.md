# Quickstart — [Nome do Módulo]

**Data:** [YYYY-MM-DD]
**Versão da feature:** v<x>
**Objetivo:** permitir que qualquer pessoa valide manualmente que a feature funciona.

---

## 1. Pré-requisitos
- [ ] Repositório atualizado: `git pull`
- [ ] Dependências instaladas: `<comando>`
- [ ] Banco rodando: `<comando>`
- [ ] Env vars necessárias (exemplo `.env.sample`):
  - `VAR_A=...`
  - `VAR_B=...`
- [ ] Seeds aplicados (se aplicável): `<comando>`
- [ ] Serviços externos acessíveis (webhooks, APIs): [lista]

## 2. Subir localmente
```bash
<comando 1>      # resultado esperado: ...
<comando 2>      # resultado esperado: ...
```

## 3. Caminho feliz
1. Acessar [URL / tela / endpoint]
2. Executar [ação]
3. **Esperado:** [resultado concreto]

Exemplo de payload / print / saída:
```
...
```

## 4. Caminho de erro
1. Executar [ação com payload inválido]
2. **Esperado:** [código de erro / mensagem]

## 5. Caminho de permissão
1. Entrar como [perfil sem permissão]
2. Tentar [ação restrita]
3. **Esperado:** [bloqueio / 403 / mensagem]

## 6. Caminho de falha parcial (se aplicável)
1. Simular [indisponibilidade de serviço externo]
2. **Esperado:** [comportamento de fallback / rollback]

## 7. Rollback / limpeza
- Desfazer migrations: `<comando>`
- Remover seeds criados: `<comando>`
- Limpar cache: `<comando>`

## 8. Quem validou
| Data | Pessoa | Ambiente | Resultado |
|---|---|---|---|

---

**Checklist de qualidade do quickstart:**
- [ ] Passos reproduzíveis por alguém sem contexto.
- [ ] Cada passo tem "resultado esperado".
- [ ] Cobre feliz + erro + permissão (e falha parcial, se aplicável).
- [ ] Rollback descrito quando a operação é crítica.
