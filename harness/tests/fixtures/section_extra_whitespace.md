---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer:
  - "1. Breakdown"
---

# Artefato com whitespace extra no heading

##  1.   Breakdown

Heading tem 2 espaços após `##` e múltiplos espaços entre tokens. Após
normalização (collapse de whitespace), deve bater com `requer: ["1. Breakdown"]`.

O lint deve retornar OK (exit 0).
