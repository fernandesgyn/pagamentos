# Seeds de homologação

Os seeds são **exclusivamente de homologação/testes** e usam a faixa de IDs **9000 a 9999**. Eles não fazem parte do provisionamento de produção.

## Pré-requisito

Crie primeiro uma base nova a partir do schema consolidado:

```bash
mysql -u root -p < database/schema.sql
```

Depois carregue os seeds nesta ordem:

```bash
mysql -u root -p pagamentos < database/seeds/001_cadastros_teste.sql
mysql -u root -p pagamentos < database/seeds/002_fluxo_documentos_inspecoes_teste.sql
mysql -u root -p pagamentos < database/seeds/003_programacao_liquidacao_cmdf_pagamento_teste.sql
```

Os três seeds são preparados para serem executados novamente na mesma base de homologação. A CI faz duas cargas consecutivas e exige que a massa continue consistente.

Para remover somente os dados de homologação:

```bash
mysql -u root -p pagamentos < database/seeds/999_limpar_testes.sql
```

## Usuários de teste

Todos usam a senha `Teste@123`.

| Login | Perfil |
|---|---|
| `admin.teste` | Administrador |
| `gestor.teste` | Gestor |
| `inspetor.teste` | Inspetor |
| `liquidacao.teste` | Liquidação |
| `cmdf.teste` | CMDF |
| `consulta.teste` | Consulta |

## Cadastros da massa

O seed `001` cria:

- 5 fornecedores: 4 Pessoas Jurídicas e 1 Pessoa Física;
- 4 Fontes de recurso;
- 4 Naturezas da despesa;
- os 6 usuários de teste.

RRT e RDO são dados estruturais do `schema.sql` e, por isso, não são recriados pelos seeds.

## Obrigações e cardinalidade

O seed `002` cria 6 obrigações. Ele contém propositalmente duas referências **Contrato 101/2026** associadas a fornecedores diferentes. Esse cenário valida a regra:

```text
Fornecedor 1 -> N Obrigações
```

A unicidade é aplicada dentro do fornecedor:

```text
fornecedor + tipo da obrigação + número + ano
```

Assim, fornecedores diferentes podem usar a mesma referência, mas o mesmo fornecedor não pode cadastrar duas vezes a mesma obrigação.

## Cenários de documentos e inspeção

| Documento | Bruto | Líquido | Situação principal |
|---|---:|---:|---|
| NF 50 | R$ 10.000,00 | R$ 9.000,00 | Liberada para programação; três parcelas independentes fecham o valor líquido |
| NF 51 | R$ 5.000,00 | R$ 4.500,00 | Inspeção andamento |
| Fatura 77 | R$ 12.000,00 | R$ 11.000,00 | **Finalizada e liberada para Programação** |
| NF 900 | R$ 4.500,00 | R$ 4.200,00 | Devolvida para o gestor |
| NF 901 | R$ 8.000,00 | R$ 7.500,00 | Liberada; parcela liquidada aguardando CMDF |
| Recibo 10 | R$ 3.000,00 | R$ 2.700,00 | Fluxo completo até pagamento |
| Boleto B-12 | R$ 2.500,00 | R$ 2.400,00 | Pendente de complementação |
| NF 888 | R$ 7.000,00 | R$ 6.500,00 | Inspeção cancelada |
| NF 52 | R$ 6.200,00 | R$ 5.800,00 | Aguardando inspeção |
| Fatura 78 | R$ 6.000,00 | R$ 5.600,00 | Retornada para inspeção |

## Regra de avanço da Inspeção

Existem **dois status que encerram a Inspeção e liberam o documento para Programação**:

- `Finalizada`;
- `Liberada liquidação de imposto`.

Os demais status não disponibilizam o documento para Programação.

## Independência das parcelas

A programação de um documento fecha quando:

```text
SUM(parcelas.valor_liquido) = documentos_pagamento.valor_liquido
```

Depois desse fechamento, Liquidação, CMDF e Pagamento pertencem **individualmente à parcela**.

A NF 50 demonstra três parcelas irmãs simultaneamente em fases diferentes:

- parcela `9001`: `PAGO`;
- parcela `9002`: Liquidação `LIQUIDADA` e CMDF `AGUARDANDO`;
- parcela `9003`: Liquidação `AGUARDANDO`.

Uma parcela não precisa esperar a irmã concluir Liquidação, CMDF ou Pagamento.

## Regras cobertas pelos seeds

1. `Finalizada` e `Liberada liquidação de imposto` permitem Programação;
2. um fornecedor pode possuir N obrigações;
3. a mesma referência de obrigação pode existir para fornecedores diferentes, mas não pode ser duplicada no mesmo fornecedor;
4. uma obrigação pode possuir 1..N Fontes de recurso e 1..N Naturezas da despesa;
5. a Natureza e a Fonte usadas na parcela precisam pertencer à obrigação do documento;
6. o número do empenho é informado diretamente na parcela;
7. a soma das parcelas não pode ultrapassar o valor líquido do documento;
8. nenhuma parcela pode ser liquidada antes de a programação fechar exatamente o valor líquido do documento;
9. somente Liquidação `LIQUIDADA` libera a CMDF daquela parcela;
10. somente CMDF `LIQUIDADA` libera o Pagamento daquela parcela;
11. parcelas do mesmo documento podem permanecer em fases diferentes;
12. fornecedor suporta Pessoa Física (CPF) e Pessoa Jurídica (CNPJ).

Para uma recarga previsível em homologação, pode-se executar `999_limpar_testes.sql` e depois `001`, `002` e `003`. A CI também valida a recarga direta dos seeds sem limpeza prévia.
