---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer: []
---

# Artefato com link dentro de code block

Este exemplo em bloco de código contém um link que deve ser ignorado:

```markdown
Veja também [exemplo falso](./nao_existe.md) que não existe mas está
dentro de um bloco de código e não deve ser validado.
```

FR-010: links em fenced code blocks não são validados (evita falso positivo
em docs de exemplo). Exit 0.
