# Sistema de Gestão de Liquidação e Pagamentos

Aplicação para gerenciar o fluxo administrativo e financeiro desde a obrigação contratual até inspeção, programação, liquidação, CMDF e pagamento.

A arquitetura segue o padrão do projeto `fernandesgyn/licitacoes`: **PHP puro 8.2+, MySQL 8+, MVC explícito, PDO/prepared statements e AdminLTE**.

## Fluxo

1. **Obrigação** — Contrato, Empenho que substitui contrato, Carta-Contrato, Ordem de Fornecimento/Serviço ou outro tipo configurável.
2. **Documento para pagamento** — Nota Fiscal, Fatura, Recibo, Boleto ou outro tipo configurável. O sistema registra automaticamente a data/hora de lançamento.
3. **Inspeção** — fila própria com histórico e estados. Apenas `Concluída` e `Concluída com ressalvas` liberam a etapa seguinte.
4. **Programação para pagamento** — o documento pode ser dividido em várias parcelas; cada parcela é vinculada a um **Empenho de pagamento**, que é conceitualmente distinto de eventual empenho usado como instrumento da obrigação.
5. **Composição da parcela** — principal/líquido, INSS, ISS, PIS/COFINS, IR, CSLL, DARE e outros componentes configuráveis.
6. **Liquidação por parcela** — cada parcela inicia em `AGUARDANDO` e é concluída individualmente com sua própria data de liquidação.
7. **CMDF por parcela** — criada individualmente após a liquidação daquela parcela e concluída mediante os marcos da etapa.
8. **Pagamento por parcela** — registra data, valor líquido pago, histórico de pagamento e Benner AP da parcela.

## Independência das parcelas

A Nota Fiscal/documento funciona como agrupador e controle do valor total. A programação só é considerada fechada quando a soma dos valores das parcelas for exatamente igual ao valor bruto do documento.

**Depois que a programação estiver fechada, cada parcela percorre o restante do fluxo de maneira totalmente independente.** Não existe exigência de que parcelas irmãs estejam na mesma etapa ou sejam concluídas juntas.

Exemplo para uma NF de R$ 10.000,00 dividida em três parcelas:

- Parcela 1 — R$ 4.000,00 — `PAGO`;
- Parcela 2 — R$ 3.500,00 — `CMDF / AGUARDANDO`;
- Parcela 3 — R$ 2.500,00 — `LIQUIDAÇÃO / AGUARDANDO`.

A situação de uma parcela não bloqueia o avanço de outra, desde que a parcela que pretende avançar cumpra suas próprias regras. Por exemplo, a composição financeira da Parcela 2 pode estar incompleta sem impedir a liquidação ou o pagamento da Parcela 1.

## Regras principais

- contratos possuem **número somente numérico** e **ano separado**;
- a obrigação e o empenho utilizado para pagar uma parcela são entidades diferentes;
- documento fiscal possui número, data, valor bruto e data/hora de lançamento automática;
- somente inspeções `Concluída` e `Concluída com ressalvas` permitem programação;
- a soma das parcelas nunca pode ultrapassar o valor do documento;
- antes de qualquer parcela seguir para liquidação, a programação do documento precisa estar fechada: soma das parcelas = valor do documento;
- a partir desse ponto, **Liquidação, CMDF e Pagamento são controlados individualmente por parcela**;
- a composição financeira da parcela que pretende avançar precisa fechar exatamente o valor dessa parcela; a composição das demais parcelas não interfere;
- a CMDF de uma parcela só pode ser concluída após a liquidação daquela mesma parcela;
- o pagamento de uma parcela só pode ser registrado após a CMDF daquela mesma parcela estar concluída.

## Padrão de tabelas administrativas

As listagens seguem o mesmo componente adotado no projeto `licitacoes`. Toda tabela administrativa principal deve usar `data-admin-table` e possuir:

- pesquisa e filtros contextuais;
- seleção de **10, 25, 50 ou 100 itens por página**;
- paginação no rodapé com contador de registros;
- ordenação pelos cabeçalhos aplicáveis;
- coluna **Ações** como última coluna e fora da ordenação;
- botões de ação com ícone e texto, direcionados a uma operação real do registro.

Os componentes reutilizáveis ficam em `app/views/components/admin_table_filters.php`, `app/views/components/admin_table_footer.php`, `public/assets/js/admin-table.js` e `public/assets/css/admin-table.css`. A CI valida a presença desse padrão nas principais telas para reduzir regressões.

## Status de inspeção iniciais

- Aguardando inspeção
- Em andamento
- Pendente de complementação
- Devolvida para o gestor
- Retornada para inspeção
- Concluída
- Concluída com ressalvas
- Cancelada

## Perfis e permissões

O schema cria os perfis iniciais:

- **Administrador** — acesso total;
- **Gestor** — obrigações, documentos, programação, pagamento e cadastros;
- **Inspetor** — fila e execução da inspeção;
- **Liquidação** — fila e conclusão da liquidação;
- **CMDF** — fila e conclusão da CMDF;
- **Consulta** — dashboard/consulta.

As filas de Liquidação, CMDF e Pagamento trabalham com **parcelas**, e não com o documento inteiro. Assim, parcelas da mesma Nota Fiscal podem aparecer simultaneamente em filas diferentes.

## Estrutura

```text
app/
  controllers/     # orquestração; sem SQL direto
  core/            # App, Auth, CSRF, Database e View
  helpers/         # funções utilitárias
  models/          # regras de negócio e acesso ao banco via PDO
  views/           # layouts, componentes e páginas AdminLTE
config/
  config.php
  routes.php       # rotas explícitas
public/
  index.php        # Front Controller
  .htaccess
  assets/          # JS/CSS próprios, incluindo tabela administrativa
database/
  schema.sql       # schema consolidado e dados iniciais
  seeds/
    001_cadastros_teste.sql
    002_fluxo_documentos_inspecoes_teste.sql
    003_programacao_liquidacao_cmdf_pagamento_teste.sql
    999_limpar_testes.sql
    README.md
scripts/
  create_admin.php
server.php
```

## Requisitos

- PHP 8.2 ou superior;
- extensões PDO/MySQL e mbstring;
- MySQL 8 ou superior.

## Instalação

1. Copie o arquivo de ambiente:

```powershell
copy .env.example .env
```

2. Ajuste as credenciais do MySQL no `.env`.

3. Execute o schema consolidado:

```powershell
mysql -u root -p < database/schema.sql
```

O script recria o banco `pagamentos` do zero.

4. Crie o primeiro administrador:

```powershell
php scripts/create_admin.php --senha="MinhaSenha@123"
```

Também é possível informar login, nome e e-mail:

```powershell
php scripts/create_admin.php --login=andre --nome="Administrador" --email="andre@exemplo.com" --senha="MinhaSenha@123"
```

5. Inicie o servidor local:

```powershell
php -S localhost:8000 -t public server.php
```

Acesse `http://localhost:8000`.

## Seeds de homologação

Após executar o schema, a massa completa pode ser carregada nesta ordem:

```powershell
mysql -u root -p pagamentos < database/seeds/001_cadastros_teste.sql
mysql -u root -p pagamentos < database/seeds/002_fluxo_documentos_inspecoes_teste.sql
mysql -u root -p pagamentos < database/seeds/003_programacao_liquidacao_cmdf_pagamento_teste.sql
```

Os usuários de homologação usam a senha `Teste@123` e existem para os perfis Administrador, Gestor, Inspetor, Liquidação, CMDF e Consulta.

A massa usa IDs reservados de **9000 a 9999** e contém cenários em todas as principais etapas, inclusive parcelas do mesmo documento em fases diferentes.

Para limpar somente a massa de homologação:

```powershell
mysql -u root -p pagamentos < database/seeds/999_limpar_testes.sql
```

A matriz completa de cenários está em `database/seeds/README.md`.

## Cadastros iniciais pela interface

Após entrar como Administrador, use **Cadastros auxiliares** para incluir:

- fornecedores;
- empenhos de pagamento;
- novos tipos de documento para pagamento;
- novos tipos de obrigação.

Depois use **Usuários e perfis** para criar os usuários responsáveis por cada etapa.

## Segurança

- autenticação por sessão;
- senhas com `password_hash`/`password_verify`;
- permissões por perfil;
- prepared statements via PDO;
- proteção CSRF global nas rotas POST;
- separação MVC, mantendo SQL fora dos controllers.

## Observação sobre a planilha original

O sistema não replica a planilha como uma tabela única. Campos foram normalizados por entidade e fase. Datas como envio à COOINSP, devolução/retorno de pendência, conclusão da inspeção, liquidação, marcos de CMDF e pagamento ficam nas respectivas etapas. Depois da programação, essas etapas pertencem à **parcela**, permitindo que parcelas de um mesmo documento avancem em ritmos diferentes.
