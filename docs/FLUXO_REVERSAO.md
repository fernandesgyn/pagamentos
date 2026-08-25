# Fluxo de reversão e correção

O sistema permite corrigir lançamentos já realizados sem quebrar a integridade das etapas posteriores.

## Regra geral

O retorno deve ocorrer sempre na ordem inversa do fluxo:

`Pagamento → CMDF → Liquidação → Programação → Inspeção`

Uma etapa não pode ser desfeita enquanto existir uma etapa posterior dependente dela.

Toda reversão exige **motivo obrigatório** e gera registro na tabela `auditoria`, com estado anterior, estado resultante, usuário, data/hora e IP.

## Pagamento

- `PAGO → AGUARDANDO`.
- Limpa data, valor pago, histórico e usuário do pagamento atual.
- A CMDF permanece Atendida até que o usuário também decida desfazê-la.

## CMDF

- `ATENDIDA → LIBERADA`, somente se não houver parcela PAGA no grupo.
- Ao voltar de Atendida para Liberada, registros de pagamento ainda `AGUARDANDO` são removidos, pois deixam de estar liberados para a fase seguinte.
- `LIBERADA → FECHADA`.
- Em `FECHADA`, a composição pode ser corrigida por inclusão/remoção de parcelas conforme as regras de compatibilidade já existentes.

## Liquidação

- Qualquer Liquidação já movimentada volta para `AGUARDANDO`.
- Data e usuário da Liquidação são limpos.
- Não é possível desfazer enquanto a parcela pertencer a um grupo CMDF.

## Programação

- A reversão é individual por parcela.
- Só é permitida quando a Liquidação da parcela estiver `AGUARDANDO`, sem grupo CMDF e sem Pagamento.
- A parcela é removida e as parcelas restantes são renumeradas sequencialmente.
- O valor programado diminui e o saldo do documento volta a ficar disponível para nova Programação.

## Inspeção

- Uma inspeção encerrada pode ser reaberta para `Inspeção andamento`.
- A Data de conclusão é limpa.
- Não é possível reabrir enquanto houver qualquer parcela programada para o documento.
- A reabertura também é registrada no histórico da inspeção.

## Exemplo completo

Se uma parcela já estiver paga e a Programação original estiver errada:

1. Desfazer o Pagamento.
2. Desfazer CMDF: Atendida → Liberada.
3. Desfazer CMDF: Liberada → Fechada.
4. Remover a parcela do grupo CMDF.
5. Desfazer a Liquidação: Liquidada → Aguardando.
6. Desfazer a Programação da parcela.
7. Corrigir e programar novamente.
8. Se necessário reabrir também a Inspeção, primeiro devem ser removidas todas as parcelas do documento.

O projeto permanece com banco consolidado em `database/schema.sql` e **sem migrations**.
