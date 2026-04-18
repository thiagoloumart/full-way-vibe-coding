---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer: []
---

# Artefato com link para âncora

[seção vizinha](./links_ok_nearby.md#secao-inexistente)

FR-008: lint valida só existência do arquivo, não da âncora. O arquivo
`links_ok_nearby.md` existe, então lint retorna OK (exit 0) mesmo com
âncora inventada.
