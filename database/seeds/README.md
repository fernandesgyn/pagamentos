# Seeds de homologação

Os seeds usam a faixa de IDs **9000 a 9999**, reservada exclusivamente para testes/homologação.

## Ordem de carga

Após executar `database/schema.sql`, carregue:

```bash
mysql -u root -p pagamentos < database/seeds/001_cadastros_teste.sql
mysql -u root -p pagamentos < database/seeds/002_fluxo_documentos_inspecoes_teste.sql
mysql -u root -p pagamentos < database/seeds/003_programacao_liquidacao_cmdf_pagamento_teste.sql
```

Para remover somente os dados de teste:

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

## Cenários disponíveis

| Documento | Valor | Situação principal |
|---|---:|---|
| NF 50 | R$ 10.000,00 | **Demonstra fluxo independente:** 2 parcelas fecham a NF; parcela 1 já está paga e parcela 2 continua aguardando liquidação |
| NF 51 | R$ 5.000,00 | Inspeção em andamento; sem programação |
| Fatura 77 | R$ 12.000,00 | Concluída com ressalvas; parcela criada; composição propositalmente incompleta |
| NF 900 | R$ 4.500,00 | Devolvida para o gestor por pendência |
| NF 901 | R$ 8.000,00 | Liquidação concluída; CMDF aguardando atendimento da Economia |
| Recibo 10 | R$ 3.000,00 | Fluxo completo até pagamento |
| Boleto B-12 | R$ 2.500,00 | Pendente de complementação |
| NF 888 | R$ 7.000,00 | Inspeção cancelada |
| NF 52 | R$ 6.200,00 | Recém-lançada, aguardando inspeção |
| Fatura 78 | R$ 6.000,00 | Retornada para inspeção após pendência |

## Regra de independência das parcelas

O documento precisa ter sua **programação fechada** — soma dos valores das parcelas igual ao valor bruto da NF/fatura. Depois disso, Liquidação, CMDF e Pagamento pertencem à parcela.

Assim, parcelas irmãs podem estar simultaneamente em etapas diferentes. O cenário da **NF 50** comprova essa regra na massa de testes:

- parcela `9001`: liquidação concluída → CMDF concluída → `PAGO`;
- parcela `9002`: `LIQUIDAÇÃO / AGUARDANDO`;
- ambas pertencem ao documento `9001` (NF 50).

Não deve existir regra que obrigue a parcela 9002 a avançar para que a parcela 9001 permaneça paga, nem regra que exija que todas as parcelas concluam Liquidação/CMDF juntas.

## Regras que os seeds ajudam a testar

1. Apenas inspeções `Concluída` e `Concluída com ressalvas` liberam programação.
2. O valor total das parcelas deve fechar exatamente o valor bruto do documento antes de elas avançarem da programação.
3. A composição **da própria parcela** deve fechar exatamente seu valor para aquela parcela ser liquidada.
4. Depois da programação, uma parcela não depende da etapa das demais parcelas do documento.
5. Liquidação concluída de uma parcela libera a CMDF daquela parcela.
6. CMDF concluída de uma parcela libera o pagamento daquela parcela.
7. O empenho usado na parcela é o **empenho de pagamento**, distinto do instrumento de obrigação.
8. As filas por perfil podem ser conferidas com usuários específicos de Gestor, Inspetor, Liquidação, CMDF e Consulta.

Os scripts foram desenhados para serem reexecutáveis sobre uma base de homologação criada a partir do schema consolidado. Para uma recarga totalmente previsível, execute primeiro `999_limpar_testes.sql` e depois os seeds `001` a `003`.