---
artefato: spec
fase: 2
dominio: [software]
schema_version: 1
requer:
  - "1. Breakdown"
---

# Artefato com seção em nível errado

#### 1. Breakdown

A seção existe mas em nível 4. Só `##` e `###` contam como obrigatórias
cumpridas — o lint deve acionar SECAO_OBRIGATORIA_NIVEL_INVALIDO.
