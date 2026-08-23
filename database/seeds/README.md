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
| NF 50 | R$ 10.000,00 | Inspeção concluída; 2 parcelas fechando o documento; parcela 1 paga; parcela 2 aguardando liquidação |
| NF 51 | R$ 5.000,00 | Inspeção em andamento; sem programação |
| Fatura 77 | R$ 12.000,00 | Concluída com ressalvas; parcela criada; composição propositalmente incompleta |
| NF 900 | R$ 4.500,00 | Devolvida para o gestor por pendência |
| NF 901 | R$ 8.000,00 | Liquidação concluída; CMDF aguardando atendimento da Economia |
| Recibo 10 | R$ 3.000,00 | Fluxo completo até pagamento |
| Boleto B-12 | R$ 2.500,00 | Pendente de complementação |
| NF 888 | R$ 7.000,00 | Inspeção cancelada |
| NF 52 | R$ 6.200,00 | Recém-lançada, aguardando inspeção |
| Fatura 78 | R$ 6.000,00 | Retornada para inspeção após pendência |

## Regras que os seeds ajudam a testar

1. Apenas inspeções `Concluída` e `Concluída com ressalvas` liberam programação.
2. O valor total das parcelas deve fechar exatamente o valor bruto do documento antes da liquidação.
3. A composição de cada parcela deve fechar exatamente seu valor.
4. Liquidação concluída libera a etapa CMDF.
5. CMDF concluída libera pagamento.
6. O empenho usado na parcela é o **empenho de pagamento**, distinto do instrumento de obrigação.
7. As filas por perfil podem ser conferidas com usuários específicos de Gestor, Inspetor, Liquidação, CMDF e Consulta.

Os scripts foram desenhados para serem reexecutáveis sobre uma base de homologação criada a partir do schema consolidado. Para uma recarga totalmente previsível, execute primeiro `999_limpar_testes.sql` e depois os seeds `001` a `003`.
