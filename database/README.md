# Banco de dados

## Produção

O sistema não utiliza migrations. A definição completa da estrutura vigente está em:

```text
database/schema.sql
```

Esse arquivo deve ser usado para provisionar **uma base nova**. Ele contém todas as tabelas, índices, chaves estrangeiras, restrições e dados estruturais necessários para iniciar o sistema.

O schema deliberadamente **não contém `DROP DATABASE`** e também não usa `CREATE TABLE IF NOT EXISTS`. Assim:

- uma instalação nova é criada integralmente de uma vez;
- a execução não apaga uma base existente;
- se já houver uma estrutura implantada, a execução falha em vez de tentar alterar silenciosamente o banco;
- atualizações de uma base de produção existente devem ser planejadas explicitamente, e não simuladas por migrations automáticas.

Provisionamento:

```bash
mysql -u root -p < database/schema.sql
```

Depois crie o primeiro administrador:

```bash
php scripts/create_admin.php --senha="SenhaForte@123"
```

Fontes de recurso e Naturezas da despesa são dados institucionais e devem ser cadastrados pela interface após a instalação. RRT e RDO são Tipos de recurso estruturais iniciais.

## Homologação

Os arquivos de `database/seeds/` são massa artificial e nunca devem ser executados em produção.

Consulte `database/seeds/README.md` para a ordem de carga e os cenários cobertos.
