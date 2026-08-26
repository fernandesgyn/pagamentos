USE pagamentos;

-- Massa exclusiva de homologação. IDs reservados: 9000-9999.
-- Garante o cadastro mestre real antes de montar os cenários de teste.
SOURCE database/seeds/000_naturezas_despesa_reais.sql;

-- Senha comum dos usuários abaixo: Teste@123
SET @senha_teste = '$2y$12$d4LZ3Zgv1yVaMimavBEgfuUGZsUX2tgs2nABvrHZOTZUBXq3KUHnq';

SET @perfil_admin = (SELECT id FROM perfis WHERE nome='Administrador' LIMIT 1);
SET @perfil_gestor = (SELECT id FROM perfis WHERE nome='Gestor' LIMIT 1);
SET @perfil_inspetor = (SELECT id FROM perfis WHERE nome='Inspetor' LIMIT 1);
SET @perfil_liquidacao = (SELECT id FROM perfis WHERE nome='Liquidação' LIMIT 1);
SET @perfil_cmdf = (SELECT id FROM perfis WHERE nome='CMDF' LIMIT 1);
SET @perfil_consulta = (SELECT id FROM perfis WHERE nome='Consulta' LIMIT 1);

INSERT INTO usuarios (id,perfil_id,nome,login,email,senha_hash,ativo,trocar_senha)
VALUES
(9001,@perfil_admin,'Administrador de Teste','admin.teste','admin.teste@exemplo.local',@senha_teste,1,0),
(9002,@perfil_gestor,'Gestor de Teste','gestor.teste','gestor.teste@exemplo.local',@senha_teste,1,0),
(9003,@perfil_inspetor,'Inspetor de Teste','inspetor.teste','inspetor.teste@exemplo.local',@senha_teste,1,0),
(9004,@perfil_liquidacao,'Liquidação de Teste','liquidacao.teste','liquidacao.teste@exemplo.local',@senha_teste,1,0),
(9005,@perfil_cmdf,'CMDF de Teste','cmdf.teste','cmdf.teste@exemplo.local',@senha_teste,1,0),
(9006,@perfil_consulta,'Consulta de Teste','consulta.teste','consulta.teste@exemplo.local',@senha_teste,1,0)
ON DUPLICATE KEY UPDATE
 perfil_id=VALUES(perfil_id),nome=VALUES(nome),email=VALUES(email),senha_hash=VALUES(senha_hash),ativo=1,trocar_senha=0;

INSERT INTO fornecedores (id,razao_social,documento,tipo_pessoa,ativo)
VALUES
(9001,'ALFA SERVIÇOS E TECNOLOGIA LTDA','12345678000195','PJ',1),
(9002,'BETA ENGENHARIA E MANUTENÇÃO LTDA','23456789000106','PJ',1),
(9003,'GAMA SOLUÇÕES ADMINISTRATIVAS S.A.','34567890000117','PJ',1),
(9004,'DELTA COMÉRCIO E SUPRIMENTOS LTDA','45678901000128','PJ',1),
(9005,'MARIA DA SILVA','12345678901','PF',1)
ON DUPLICATE KEY UPDATE
 razao_social=VALUES(razao_social),documento=VALUES(documento),tipo_pessoa=VALUES(tipo_pessoa),ativo=1;

INSERT INTO fontes_recurso (id,codigo,nome,ativo)
VALUES
(9001,'100','Recursos próprios',1),
(9002,'1500','Tesouro',1),
(9003,'1700','Convênio',1),
(9004,'1800','Recursos vinculados',1)
ON DUPLICATE KEY UPDATE codigo=VALUES(codigo),nome=VALUES(nome),ativo=1;
