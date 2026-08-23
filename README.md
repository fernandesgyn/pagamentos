# Sistema de Gestão de Liquidação e Pagamentos

Aplicação para gerenciar o fluxo administrativo e financeiro desde a obrigação contratual até inspeção, programação, liquidação, CMDF e pagamento.

A arquitetura segue o padrão do projeto `fernandesgyn/licitacoes`: **PHP puro 8.2+, MySQL 8+, MVC explícito, PDO/prepared statements e AdminLTE**.

## Fluxo

1. **Obrigação** — Contrato, Empenho que substitui contrato, Carta-Contrato, Ordem de Fornecimento/Serviço ou outro tipo configurável.
2. **Documento para pagamento** — Nota Fiscal, Fatura, Recibo, Boleto ou outro tipo configurável. O sistema registra automaticamente a data/hora de lançamento.
3. **Inspeção** — fila própria com histórico e estados. Apenas `Concluída` e `Concluída com ressalvas` liberam a etapa seguinte.
4. **Programação para pagamento** — o documento pode ser dividido em várias parcelas; cada parcela é vinculada a um **Empenho de pagamento**, que é conceitualmente distinto de eventual empenho usado como instrumento da obrigação.
5. **Composição da parcela** — principal/líquido, INSS, ISS, PIS/COFINS, IR, CSLL, DARE e outros componentes configuráveis.
6. **Liquidação** — começa em `AGUARDANDO` e somente conclui com data de liquidação.
7. **CMDF** — criada automaticamente após a liquidação e concluída mediante registro da data e dos marcos da etapa.
8. **Pagamento** — registra data, valor líquido pago, histórico de pagamento e Benner AP.

## Regras principais

- contratos possuem **número somente numérico** e **ano separado**;
- a obrigação e o empenho utilizado para pagar uma parcela são entidades diferentes;
- documento fiscal possui número, data, valor bruto e data/hora de lançamento automática;
- somente inspeções `Concluída` e `Concluída com ressalvas` permitem programação;
- a soma das parcelas nunca pode ultrapassar o valor do documento;
- a liquidação só pode ser concluída quando a soma das parcelas for **exatamente igual** ao valor do documento;
- a composição financeira de cada parcela também precisa fechar **exatamente** o valor da parcela;
- a CMDF só pode ser concluída após liquidação concluída;
- pagamento só pode ser registrado após CMDF concluída.

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

As filas operacionais são separadas para que cada perfil encontre diretamente o trabalho de sua etapa.

## Estrutura

```text
app/
  controllers/     # orquestração; sem SQL direto
  core/            # App, Auth, CSRF, Database e View
  helpers/         # funções utilitárias
  models/          # regras de negócio e acesso ao banco via PDO
  views/           # layouts e páginas AdminLTE
config/
  config.php
  routes.php       # rotas explícitas
public/
  index.php        # Front Controller
  .htaccess
database/
  schema.sql       # schema consolidado e dados iniciais
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

O sistema não replica a planilha como uma tabela única. Campos foram normalizados por entidade e fase. Datas como envio à COOINSP, devolução/retorno de pendência, conclusão da inspeção, liquidação, marcos de CMDF e pagamento ficam nas respectivas etapas. Campos calculáveis, como situação da fila ou fechamento do valor, são derivados pelo sistema em vez de serem digitados repetidamente.
