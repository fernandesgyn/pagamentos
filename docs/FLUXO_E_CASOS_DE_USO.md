# Fluxo e Casos de Uso — Sistema de Liquidação e Pagamentos

Data de referência: 24/08/2026.

## 1. Fluxo macro

1. Cadastros estruturais.
2. Obrigação.
3. Documento para pagamento.
4. Inspeção.
5. Programação em 1..N parcelas.
6. Liquidação individual da parcela.
7. CMDF individual da parcela.
8. Pagamento individual da parcela.

**Regra central:** o documento é o agrupador. A Programação fecha quando a soma das parcelas é exatamente igual ao valor líquido do documento. A partir daí, cada parcela avança de forma independente em Liquidação, CMDF e Pagamento.

## 2. Cadastros estruturais

- `/fornecedores`: Pessoa Física/Jurídica, Razão Social/Nome e CPF/CNPJ.
- `/fontes-recurso`: fontes que podem ser vinculadas 1..N à obrigação.
- `/naturezas-despesa`: naturezas que podem ser vinculadas 1..N à obrigação.
- `/tipos-recurso`: tipos utilizados nas parcelas, como RRT e RDO.
- `/tipos-documento`: Nota Fiscal, Fatura, Recibo, Boleto, Outro ou novos tipos cadastrados.
- `/tipos-obrigacao`: Contrato, Empenho, Taxa/Tarifa, Despesas Judiciais, Diárias, Despesas de Pessoal e Imposto.

As listagens usam DataTable administrativa com pesquisa, filtros, ordenação, paginação e coluna **Ações**. Listagem e formulário são telas separadas.

## 3. Obrigação

Listagem: `/obrigacoes`  
Cadastro: `/obrigacoes/nova`

Passos:

1. Selecionar o tipo.
2. Pesquisar e selecionar o fornecedor.
3. Informar número e ano.
4. Informar Valor Total da Obrigação.
5. Informar, quando aplicável, Nr. SEI da Contratação e datas de início/fim.
6. Vincular uma ou mais Fontes de recurso.
7. Vincular uma ou mais Naturezas da despesa.
8. Salvar.

## 4. Documento

Listagem: `/documentos`  
Cadastro: `/documentos/novo`

Passos:

1. Pesquisar e selecionar o fornecedor.
2. Selecionar somente entre as obrigações daquele fornecedor.
3. Selecionar o tipo de documento.
4. Informar número e Data de emissão.
5. Informar Data do atesto e Data de envio à COOINSP, quando disponíveis.
6. Informar Valor bruto e Valor líquido.
7. Salvar.
8. O sistema registra data/hora de lançamento e cria automaticamente a Inspeção em **Aguardando inspeção**.

Regra: valor líquido > 0 e valor líquido <= valor bruto.

## 5. Inspeção

Fila: `/inspecoes`  
Atuação: `/inspecoes/{documentoId}`

Status:

- Aguardando inspeção;
- Inspeção andamento;
- Pendente de complementação;
- Devolvida para o gestor;
- Retornada para inspeção;
- Finalizada;
- Liberada liquidação de imposto;
- Cancelada.

Somente **Liberada liquidação de imposto** libera o documento para Programação. `Finalizada` encerra a inspeção, mas não libera a etapa seguinte.

## 6. Programação

Fila: `/programacao`  
Atuação: `/programacao/{documentoId}`

Cada parcela recebe:

- Nr. empenho;
- Natureza da despesa, escolhida entre as naturezas da obrigação;
- Exercício orçamentário;
- Fonte de recurso, escolhida entre as fontes da obrigação;
- Tipo do recurso;
- Valor líquido;
- Tipo: IMPOSTO, DARE, INSS, PIS, COFINS, IR ou ISS;
- Data de vencimento opcional;
- Histórico da parcela opcional;
- Justificativa da ordem cronológica opcional.

A numeração da parcela é automática. A soma nunca pode ultrapassar o valor líquido do documento e a Programação só fecha quando a soma for exatamente igual a esse valor.

## 7. Liquidação

Fila: `/liquidacoes`  
Atuação: `/liquidacoes/{parcelaId}`

Status:

- AGUARDANDO;
- LIQUIDADA;
- CANCELADA;
- ANULADA.

Somente `LIQUIDADA`, com Data de liquidação, cria/libera a CMDF daquela parcela.

## 8. CMDF

Fila: `/cmdf`  
Atuação: `/cmdf/{parcelaId}`

Status:

- AGUARDANDO;
- LIQUIDADA;
- CANCELADA;
- ANULADA.

Somente parcelas com Liquidação = `LIQUIDADA` podem estar na CMDF. CMDF = `LIQUIDADA`, com Data de conclusão, libera a parcela para Pagamento.

## 9. Pagamento

Fila: `/pagamentos`

Para a parcela liberada pela CMDF:

1. informar Data do pagamento;
2. conferir/informar Valor líquido pago;
3. informar Benner AP, quando aplicável;
4. registrar Histórico do pagamento;
5. clicar em Pagar.

O valor pago deve ser positivo e não pode superar o valor líquido da parcela.

## 10. Independência das parcelas

Após o fechamento da Programação, parcelas irmãs podem estar simultaneamente em etapas distintas.

Exemplo:

- Parcela 1: PAGA;
- Parcela 2: LIQUIDADA e aguardando CMDF;
- Parcela 3: AGUARDANDO LIQUIDAÇÃO.

Uma parcela não bloqueia o avanço de outra após o fechamento da Programação.

## 11. Perfis e casos de uso

### Administrador

Possui acesso total. Pode administrar cadastros, usuários, perfis/permissões e executar todas as etapas do fluxo.

### Gestor

Permissões padrão:

- Painel;
- Obrigações;
- Documentos;
- Programação;
- Pagamento;
- Cadastros auxiliares.

Fluxo típico: preparar cadastros -> cadastrar obrigação -> cadastrar documento -> aguardar Inspeção -> programar parcelas -> registrar pagamentos liberados pela CMDF.

### Inspetor

Permissões padrão: Painel e Inspeção. Analisa documentos, altera status, registra histórico e usa **Liberada liquidação de imposto** quando o documento puder seguir.

### Liquidação

Permissões padrão: Painel e Liquidação. Trabalha individualmente em cada parcela, podendo manter Aguardando, Liquidar, Cancelar ou Anular. A Data de liquidação é obrigatória para `LIQUIDADA`.

### CMDF

Permissões padrão: Painel e CMDF. Processa somente parcelas já liquidadas e libera individualmente a parcela para Pagamento ao concluir a CMDF como `LIQUIDADA`.

### Consulta

Permissão padrão: Painel. Não possui permissão de alteração no fluxo padrão.

## 12. Matriz resumida

| Módulo | Admin | Gestor | Inspetor | Liquidação | CMDF | Consulta |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| Painel | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Obrigações | ✓ | ✓ |  |  |  |  |
| Documentos | ✓ | ✓ |  |  |  |  |
| Inspeção | ✓ |  | ✓ |  |  |  |
| Programação | ✓ | ✓ |  |  |  |  |
| Liquidação | ✓ |  |  | ✓ |  |  |
| CMDF | ✓ |  |  |  | ✓ |  |
| Pagamento | ✓ | ✓ |  |  |  |  |
| Cadastros | ✓ | ✓ |  |  |  |  |
| Usuários | ✓ |  |  |  |  |  |
| Perfis e permissões | ✓ |  |  |  |  |  |

## 13. Controles técnicos

- autenticação por sessão;
- autorização por perfil/permissão;
- CSRF nas operações POST;
- PDO com prepared statements;
- banco consolidado em `database/schema.sql`, sem migrations;
- seeds de homologação separados do provisionamento de produção;
- CI de schema/lint e smoke test HTTP das rotas para detectar warnings, notices, variáveis indefinidas e erros fatais antes do merge.
