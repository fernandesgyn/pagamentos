# Seeds de homologação

Os seeds são exclusivamente de homologação/testes e usam IDs de 9000 a 9999. Não execute em produção.

## Carga

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p pagamentos < database/seeds/001_cadastros_teste.sql
mysql -u root -p pagamentos < database/seeds/002_fluxo_documentos_inspecoes_teste.sql
mysql -u root -p pagamentos < database/seeds/003_programacao_liquidacao_cmdf_pagamento_teste.sql
```

Os três seeds podem ser executados novamente na mesma base; a CI valida duas cargas consecutivas.

## Usuários

Senha comum: `Teste@123`.

- `admin.teste` — Administrador
- `gestor.teste` — Gestor
- `inspetor.teste` — Inspetor
- `liquidacao.teste` — Liquidação
- `cmdf.teste` — CMDF
- `consulta.teste` — Consulta

## Cenários

O seed 002 mantém 10 documentos e cobre os status de Inspeção. `Finalizada` e `Liberada liquidação de imposto` liberam Programação.

O seed 003 usa a estrutura atual de parcelas: Origem do Recurso, Data de vencimento, IPOF (10 dígitos), AP Benner (10 dígitos), Sequencial (3 dígitos), Grupo de Despesa (2 dígitos) e Histórico obrigatório.

### CMDF

- Grupo 9001 — `ATENDIDA`, com parcelas 9001 e 9002, mesma Fonte/Exercício/Sequencial/Grupo de Despesa/Origem. As duas possuem Data do atesto. Uma já está PAGA e a outra aguarda Pagamento.
- Grupo 9002 — `LIBERADA`, com parcela 9004.
- Grupo 9003 — `FECHADA`, com parcela 9005.
- Parcelas 9006 e 9007 — liquidadas, atestadas, sem grupo e com a mesma chave CMDF. Servem para testar o agrupamento automático.
- Parcela 9003 — ainda em Liquidação `AGUARDANDO` e não pode entrar na CMDF.

Somente grupos `ATENDIDA` liberam suas parcelas para Pagamento.

## Limpeza

```bash
mysql -u root -p pagamentos < database/seeds/999_limpar_testes.sql
```
