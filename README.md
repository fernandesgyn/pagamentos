# Sistema de Gestão de Liquidação e Pagamentos

Aplicação para gerenciar o fluxo administrativo e financeiro desde a obrigação até inspeção, programação das parcelas, liquidação, CMDF e pagamento.

A arquitetura segue o padrão do projeto `fernandesgyn/licitacoes`: **PHP 8.2+, MySQL 8+, MVC explícito, PDO/prepared statements e AdminLTE**.

## Banco de dados: modelo de produção

O projeto **não utiliza migrations**. Existe um único arquivo consolidado:

```text
database/schema.sql
```

Ele representa integralmente a estrutura atual de produção: tabelas, chaves estrangeiras, índices, restrições, perfis, permissões, tipos estruturais e status do fluxo.

O `schema.sql` é destinado ao **provisionamento de uma base nova**. Ele:

- cria o banco `pagamentos` se o banco ainda não existir;
- cria todas as tabelas em ordem compatível com as chaves estrangeiras;
- não executa `DROP DATABASE`;
- não tenta migrar uma estrutura antiga;
- não contém dados de homologação;
- falha caso seja executado sobre tabelas já existentes, evitando alterações destrutivas silenciosas em produção.

Os arquivos em `database/seeds/` são exclusivamente de homologação e não devem ser executados em produção.

## Fluxo atual

1. **Obrigação**
   - Contrato;
   - Empenho;
   - Taxa / Tarifa;
   - Despesas Judiciais;
   - Diárias;
   - Despesas de Pessoal;
   - Imposto.

   Cada obrigação possui fornecedor, número/ano, Valor Total da Obrigação, Nr. SEI da Contratação e vínculos 1..N com Fontes de recurso e Naturezas da despesa.

2. **Documento para pagamento**
   - fornecedor pesquisável;
   - obrigação restrita ao fornecedor selecionado;
   - tipo e número;
   - data de emissão;
   - data do atesto;
   - data de envio à COOINSP;
   - valor bruto;
   - valor líquido;
   - data/hora de lançamento automática.

3. **Inspeção**
   - Aguardando inspeção;
   - Inspeção andamento;
   - Pendente de complementação;
   - Devolvida para o gestor;
   - Retornada para inspeção;
   - Finalizada;
   - Liberada liquidação de imposto;
   - Cancelada.

   **Somente `Liberada liquidação de imposto` permite Programação.**

4. **Programação das parcelas**

   Cada parcela possui numeração automática e registra:

   - Nr. empenho digitado diretamente;
   - Natureza da despesa pertencente à obrigação;
   - exercício orçamentário com quatro dígitos;
   - Fonte de recurso pertencente à obrigação;
   - Tipo do recurso (`RRT`, `RDO` ou outro cadastro futuro);
   - valor líquido;
   - tipo (`IMPOSTO`, `DARE`, `INSS`, `PIS`, `COFINS`, `IR`, `ISS`);
   - data de vencimento opcional;
   - histórico da parcela;
   - justificativa de ordem cronológica opcional.

   A soma das parcelas não pode ultrapassar o valor líquido do documento. A programação só fecha quando:

   ```text
   SUM(valor_liquido das parcelas) = valor_liquido do documento
   ```

5. **Liquidação por parcela**
   - Aguardando liquidação;
   - Liquidada;
   - Cancelada;
   - Anulada.

   Somente `Liquidada`, com data de liquidação, libera a CMDF da própria parcela.

6. **CMDF por parcela**
   - Aguardando CMDF;
   - Liquidada;
   - Cancelada;
   - Anulada.

   Somente CMDF `Liquidada` libera o Pagamento da própria parcela.

7. **Pagamento por parcela**
   - data do pagamento;
   - valor líquido pago;
   - histórico;
   - Benner AP.

## Independência das parcelas

O documento é o agrupador. Depois que sua Programação fecha o valor líquido, cada parcela percorre Liquidação → CMDF → Pagamento de forma independente.

Exemplo válido para o mesmo documento:

- Parcela 1: `PAGO`;
- Parcela 2: CMDF `AGUARDANDO`;
- Parcela 3: Liquidação `AGUARDANDO`.

Uma parcela irmã não bloqueia o avanço de outra.

## Cadastros

Cada cadastro possui rota, DataTable e formulário próprios:

- `/fornecedores` — PF/PJ, Razão Social/Nome e CPF/CNPJ;
- `/fontes-recurso`;
- `/naturezas-despesa`;
- `/tipos-recurso`;
- `/tipos-documento`;
- `/tipos-obrigacao`;
- `/usuarios`;
- `/perfis` — perfis e permissões separados de usuários.

Listagens e formulários não ficam misturados na mesma tela.

## Padrão das tabelas

As listagens seguem o componente do projeto `licitacoes`:

- pesquisa e filtros;
- 10, 25, 50 ou 100 registros por página;
- paginação;
- ordenação;
- coluna **Ações** não ordenável;
- botões de ação com ícone e texto.

## Instalação de produção em base nova

### 1. Ambiente

```powershell
copy .env.example .env
```

Configure no `.env`:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pagamentos
DB_USERNAME=...
DB_PASSWORD=...
```

### 2. Provisionar o banco

```powershell
mysql -u root -p < database/schema.sql
```

Não execute esse comando como mecanismo de atualização de um banco de produção já existente. Como não existem migrations, alterações futuras de uma base já implantada devem ser planejadas explicitamente antes do deploy.

### 3. Criar o primeiro administrador

```powershell
php scripts/create_admin.php --senha="MinhaSenha@123"
```

Opcionalmente:

```powershell
php scripts/create_admin.php --login=admin --nome="Administrador" --email="admin@exemplo.com" --senha="MinhaSenha@123"
```

### 4. Configuração inicial pela interface

Antes da primeira obrigação, cadastre as Fontes de recurso e Naturezas da despesa reais da instituição. RRT e RDO já são disponibilizados como Tipos de recurso iniciais.

### 5. Servidor local

```powershell
php -S localhost:8000 -t public server.php
```

## Homologação

Depois de criar uma base descartável com `schema.sql`, a massa de testes pode ser carregada:

```powershell
mysql -u root -p pagamentos < database/seeds/001_cadastros_teste.sql
mysql -u root -p pagamentos < database/seeds/002_fluxo_documentos_inspecoes_teste.sql
mysql -u root -p pagamentos < database/seeds/003_programacao_liquidacao_cmdf_pagamento_teste.sql
```

Usuários de teste: `admin.teste`, `gestor.teste`, `inspetor.teste`, `liquidacao.teste`, `cmdf.teste` e `consulta.teste`, todos com senha `Teste@123`.

Para remover somente a massa de homologação:

```powershell
mysql -u root -p pagamentos < database/seeds/999_limpar_testes.sql
```

Detalhes: `database/seeds/README.md`.

## Estrutura

```text
app/
  controllers/
  core/
  helpers/
  models/
  views/
config/
  config.php
  routes.php
public/
  index.php
  assets/
database/
  schema.sql
  seeds/
scripts/
  create_admin.php
server.php
```

## Segurança

- autenticação por sessão;
- `password_hash` / `password_verify`;
- permissões por perfil;
- CSRF em POST;
- prepared statements via PDO;
- SQL concentrado nos models;
- validações de integridade também reforçadas por chaves estrangeiras e `CHECK` constraints no MySQL.
