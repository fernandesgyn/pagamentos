# Fluxo e Casos de Uso — Sistema de Liquidação e Pagamentos

## Fluxo macro

1. Cadastros estruturais.
2. Obrigação.
3. Documento para pagamento.
4. Inspeção.
5. Programação em 1..N parcelas.
6. Liquidação individual da parcela.
7. CMDF por grupos de parcelas.
8. Pagamento individual da parcela.

## Programação

Uma parcela possui obrigatoriamente:

- Nr. empenho;
- Natureza da despesa;
- Exercício orçamentário;
- Fonte de recurso;
- Origem do Recurso;
- Valor líquido;
- Tipo: IMPOSTO, DARE, INSS, PIS, COFINS, IR ou ISS;
- Data de vencimento;
- IPOF com 10 dígitos;
- AP Benner com 10 dígitos;
- Sequencial com 3 dígitos;
- Grupo de Despesa com 2 dígitos;
- Histórico da parcela.

Justificativa da ordem cronológica é opcional, com até 150 caracteres.

A soma das parcelas não pode superar o valor líquido do documento. A Programação fecha somente quando a soma for exatamente igual ao valor líquido. Depois disso, cada parcela segue individualmente para Liquidação.

## Inspeção

`Finalizada` e `Liberada liquidação de imposto` encerram a Inspeção e liberam o documento para Programação.

## Liquidação

Status: `AGUARDANDO`, `LIQUIDADA`, `CANCELADA`, `ANULADA`.

Somente `LIQUIDADA` torna a parcela elegível para CMDF. Para entrar em um grupo CMDF, o documento da parcela também precisa possuir Data do atesto.

## CMDF por grupos

A CMDF não possui mais status individual por parcela. O status pertence ao grupo.

### Regras de composição

Todas as parcelas de um grupo precisam:

- estar com Liquidação = `LIQUIDADA`;
- possuir Data do atesto no documento;
- ter a mesma Fonte de recurso;
- ter o mesmo Exercício orçamentário;
- ter o mesmo Sequencial;
- ter o mesmo Grupo de Despesa;
- ter a mesma Origem do Recurso.

A Data do atesto precisa existir, mas não precisa ser igual entre as parcelas.

### Agrupamento inteligente

A tela `/cmdf` apresenta sugestões de grupos para parcelas ainda não agrupadas. O sistema agrupa pelas cinco características comuns: Fonte, Exercício, Sequencial, Grupo de Despesa e Origem do Recurso, considerando somente documentos com Data do atesto.

Usuários com `cmdf.grupo.ajustar` podem:

- criar os grupos sugeridos automaticamente;
- selecionar manualmente parcelas compatíveis para formar um grupo;
- adicionar parcelas compatíveis a um grupo `FECHADA`;
- remover parcelas de um grupo `FECHADA`.

O backend valida novamente todas as regras; não é possível burlar a composição apenas manipulando o formulário.

### Status do grupo

Fluxo obrigatório:

`FECHADA → LIBERADA → ATENDIDA`

- `FECHADA`: composição ainda pode ser ajustada por usuário autorizado.
- `LIBERADA`: composição fica bloqueada.
- `ATENDIDA`: libera todas as parcelas do grupo para a fase de Pagamento.

Não há retrocesso automático de status. Somente `ATENDIDA` avança para Pagamento.

## Pagamento

Apesar da CMDF ser coletiva, o Pagamento continua individual por parcela. Ao marcar um grupo como `ATENDIDA`, o sistema prepara um registro `AGUARDANDO` para cada parcela do grupo. Cada parcela pode então ser paga independentemente das demais.

O AP Benner é informado na Programação e apenas exibido no Pagamento; não é digitado novamente.

## Perfis

- Administrador: acesso total.
- Gestor: Obrigações, Documentos, Programação, Pagamento e Cadastros.
- Inspetor: Inspeção.
- Liquidação: Liquidação.
- CMDF: processa status dos grupos; o perfil padrão também recebe `cmdf.grupo.ajustar` para compor grupos.
- Consulta: Painel.

A permissão `cmdf.grupo.ajustar` pode ser removida do perfil CMDF ou atribuída a outro perfil independentemente de `cmdf.gerir`.

## Banco

Produção usa exclusivamente `database/schema.sql`. Não há migrations. Seeds ficam separados em `database/seeds/` e são apenas de homologação.
