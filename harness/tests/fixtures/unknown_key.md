---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer: []
custom_field: este-campo-nao-e-conhecido
outra_chave_extra:
  - item_a
  - item_b
---

# Artefato com chave extra no front-matter

FR-017: em M1, chaves extras no front-matter são aceitas sem erro. Validação
estrita (rejeitar desconhecidas) fica para M2 via schemas custom.

O lint deve retornar exit 0 para este arquivo.
