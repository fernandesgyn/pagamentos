# Seeds e dados de referência

## Naturezas da Despesa reais

`000_naturezas_despesa_reais.sql` carrega o cadastro mestre de **1.157 Naturezas da Despesa reais**, extraídas da planilha institucional fornecida em 26/08/2026.

A fonte possui dois atributos:

- `Natureza Despesa` → gravado em `naturezas_despesa.codigo`;
- `Descrição` → gravado em `naturezas_despesa.nome`.

O nome interno da coluna `nome` é mantido por compatibilidade com a arquitetura atual; funcionalmente ele representa a descrição oficial. O cadastro mestre usa IDs de `100001` a `101157`, não pertence à massa descartável de homologação e **não é removido** por `999_limpar_testes.sql`.

O arquivo mestre é idempotente e pode ser reaplicado para atualizar as descrições oficiais pelo código.

## Massa de homologação

Os seeds `001`, `002` e `003` são exclusivamente de homologação/testes e usam IDs de 9000 a 9999 para seus registros próprios. Não execute essa massa de teste em produção.

### Carga para homologação

A partir da raiz do repositório:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p pagamentos < database/seeds/000_naturezas_despesa_reais.sql
mysql -u root -p pagamentos < database/seeds/001_cadastros_teste.sql
mysql -u root -p pagamentos < database/seeds/002_fluxo_documentos_inspecoes_teste.sql
mysql -u root -p pagamentos < database/seeds/003_programacao_liquidacao_cmdf_pagamento_teste.sql
```

Os seeds podem ser executados novamente na mesma base; a CI valida duas cargas consecutivas. Os cenários de teste referenciam as Naturezas da Despesa reais pelo `codigo`, sem duplicar cadastros fictícios.

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

## Limpeza da homologação

```bash
mysql -u root -p pagamentos < database/seeds/999_limpar_testes.sql
```

A limpeza remove somente a massa de homologação. As 1.157 Naturezas da Despesa reais permanecem cadastradas.
