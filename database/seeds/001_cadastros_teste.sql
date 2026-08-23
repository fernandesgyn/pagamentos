USE pagamentos;

-- IDs de homologação: 9000-9999.
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

INSERT INTO fornecedores (id,nome,documento,ativo)
VALUES
(9001,'ALFA SERVIÇOS E TECNOLOGIA LTDA','12345678000195',1),
(9002,'BETA ENGENHARIA E MANUTENÇÃO LTDA','23456789000106',1),
(9003,'GAMA SOLUÇÕES ADMINISTRATIVAS S.A.','34567890000117',1),
(9004,'DELTA COMÉRCIO E SUPRIMENTOS LTDA','45678901000128',1),
(9005,'EPSILON CONSULTORIA LTDA','56789012000139',1)
ON DUPLICATE KEY UPDATE nome=VALUES(nome),documento=VALUES(documento),ativo=1;

INSERT INTO empenhos_pagamento
(id,numero,ano,natureza,exercicio,origem_recurso,fonte,cmdf,ativo)
VALUES
(9001,'2026NE000101',2026,'3.3.90.39',2026,'Recursos próprios','100','CMDF-001/2026',1),
(9002,'2026NE000102',2026,'3.3.90.39',2026,'Recursos próprios','100','CMDF-002/2026',1),
(9003,'2026NE000103',2026,'3.3.90.40',2026,'Tesouro','1500','CMDF-003/2026',1),
(9004,'2026NE000104',2026,'3.3.90.30',2026,'Recursos próprios','100','CMDF-004/2026',1),
(9005,'2026NE000105',2026,'3.3.90.39',2026,'Convênio','1700','CMDF-005/2026',1),
(9006,'2026NE000106',2026,'3.3.90.39',2026,'Recursos próprios','100','CMDF-006/2026',1),
(9007,'2026NE000107',2026,'3.3.90.39',2026,'Tesouro','1500','CMDF-007/2026',1),
(9008,'2026NE000108',2026,'3.3.90.30',2026,'Recursos próprios','100','CMDF-008/2026',1),
(9009,'2025NE000999',2025,'3.3.90.39',2025,'Recursos próprios','100','CMDF-999/2025',1)
ON DUPLICATE KEY UPDATE
 numero=VALUES(numero),ano=VALUES(ano),natureza=VALUES(natureza),exercicio=VALUES(exercicio),origem_recurso=VALUES(origem_recurso),fonte=VALUES(fonte),cmdf=VALUES(cmdf),ativo=1;
