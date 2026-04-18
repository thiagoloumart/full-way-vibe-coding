---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer:
  - "1. Breakdown"
---

# Artefato com seção dentro de bloco de código

Este exemplo mostra um heading dentro de um code block — não deve contar:

```markdown
## 1. Breakdown

Este `## 1. Breakdown` está dentro de ```...``` e deve ser ignorado.
```

O lint deve acionar SECAO_OBRIGATORIA_AUSENTE porque fora do bloco não existe
a seção.
