# Banco de dados

## Produção

O sistema não utiliza migrations. A definição completa da estrutura vigente está em:

```text
database/schema.sql
```

Esse arquivo provisiona uma base nova com todas as tabelas, índices, chaves estrangeiras, restrições, perfis, permissões e dados estruturais. Ele não contém `DROP DATABASE` e não usa `CREATE TABLE IF NOT EXISTS`: em uma base já existente, a execução deve falhar em vez de alterar ou apagar dados silenciosamente.

```bash
mysql -u root -p < database/schema.sql
php scripts/create_admin.php --senha="SenhaForte@123"
```

Fontes de recurso e Naturezas da despesa são cadastros institucionais. RRT e RDO são Origens do Recurso estruturais iniciais.

A arquitetura atual usa `origens_recurso`, `cmdf_grupos` e `cmdf_grupo_parcelas`. Não existem tabelas `tipos_recurso` ou `cmdf_etapas`.

## Homologação

Os arquivos em `database/seeds/` são massa artificial e nunca devem ser executados em produção. Consulte `database/seeds/README.md`.
