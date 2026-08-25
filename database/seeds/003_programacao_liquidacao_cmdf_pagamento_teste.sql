USE pagamentos;

SET @rrt=(SELECT id FROM origens_recurso WHERE codigo='RRT' LIMIT 1);
SET @rdo=(SELECT id FROM origens_recurso WHERE codigo='RDO' LIMIT 1);

INSERT INTO parcelas_pagamento
(id,documento_id,numero_parcela,numero_empenho,natureza_despesa_id,exercicio_orcamentario,fonte_recurso_id,origem_recurso_id,valor_liquido,tipo,data_vencimento,ipof,ap_benner,sequencial,grupo_despesa,historico_parcela,justificativa_ordem_cronologica,criado_por,criado_em)
VALUES
(9001,9001,1,'2026NE000101',9004,2026,9001,@rrt,3000.00,'ISS','2026-07-10','0000001001','0000005001','101','33','Parcela 1 da NF 50 - ISS.',NULL,9002,'2026-07-03 16:20:00'),
(9002,9001,2,'2026NE000102',9003,2026,9001,@rrt,3000.00,'IR','2026-07-12','0000001002','0000005002','101','33','Parcela 2 da NF 50 - IR.',NULL,9002,'2026-07-03 16:25:00'),
(9003,9001,3,'2026NE000103',9003,2026,9002,@rdo,3000.00,'INSS','2026-07-15','0000001003','0000005003','102','33','Parcela 3 da NF 50 - INSS.','Processamento conforme ordem cronológica.',9002,'2026-07-03 16:30:00'),
(9004,9005,1,'2026NE000104',9003,2026,9001,@rdo,7500.00,'DARE','2026-07-30','0000001004','0000005004','201','33','Parcela única da NF 901.',NULL,9002,'2026-07-22 13:40:00'),
(9005,9006,1,'2026NE000105',9003,2026,9002,@rrt,2700.00,'PIS','2026-06-20','0000001005','0000005005','301','44','Parcela única do Recibo 10.',NULL,9002,'2026-06-12 10:30:00'),
(9006,9003,1,'2026NE000106',9002,2026,9003,@rdo,5500.00,'COFINS','2026-07-25','0000001006','0000005006','777','44','Parcela 1 da Fatura 77.',NULL,9002,'2026-07-18 16:00:00'),
(9007,9003,2,'2026NE000107',9002,2026,9003,@rdo,5500.00,'IR','2026-07-25','0000001007','0000005007','777','44','Parcela 2 da Fatura 77.',NULL,9002,'2026-07-18 16:05:00')
ON DUPLICATE KEY UPDATE numero_empenho=VALUES(numero_empenho),natureza_despesa_id=VALUES(natureza_despesa_id),exercicio_orcamentario=VALUES(exercicio_orcamentario),fonte_recurso_id=VALUES(fonte_recurso_id),origem_recurso_id=VALUES(origem_recurso_id),valor_liquido=VALUES(valor_liquido),tipo=VALUES(tipo),data_vencimento=VALUES(data_vencimento),ipof=VALUES(ipof),ap_benner=VALUES(ap_benner),sequencial=VALUES(sequencial),grupo_despesa=VALUES(grupo_despesa),historico_parcela=VALUES(historico_parcela),justificativa_ordem_cronologica=VALUES(justificativa_ordem_cronologica),criado_por=VALUES(criado_por);

INSERT INTO liquidacoes(id,parcela_id,status,data_liquidacao,usuario_id,criado_em,atualizado_em) VALUES
(9001,9001,'LIQUIDADA','2026-07-04',9004,'2026-07-03 16:20:00','2026-07-04 10:15:00'),
(9002,9002,'LIQUIDADA','2026-07-04',9004,'2026-07-03 16:25:00','2026-07-04 10:20:00'),
(9003,9003,'AGUARDANDO',NULL,NULL,'2026-07-03 16:30:00',NULL),
(9004,9004,'LIQUIDADA','2026-07-23',9004,'2026-07-22 13:40:00','2026-07-23 09:10:00'),
(9005,9005,'LIQUIDADA','2026-06-13',9004,'2026-06-12 10:30:00','2026-06-13 09:00:00'),
(9006,9006,'LIQUIDADA','2026-07-19',9004,'2026-07-18 16:00:00','2026-07-19 08:00:00'),
(9007,9007,'LIQUIDADA','2026-07-19',9004,'2026-07-18 16:05:00','2026-07-19 08:05:00')
ON DUPLICATE KEY UPDATE status=VALUES(status),data_liquidacao=VALUES(data_liquidacao),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

INSERT INTO cmdf_grupos(id,fonte_recurso_id,exercicio_orcamentario,sequencial,grupo_despesa,origem_recurso_id,status,gerado_automaticamente,criado_por,atualizado_por,criado_em,atualizado_em) VALUES
(9001,9001,2026,'101','33',@rrt,'ATENDIDA',1,9005,9005,'2026-07-04 11:00:00','2026-07-06 14:30:00'),
(9002,9001,2026,'201','33',@rdo,'LIBERADA',0,9005,9005,'2026-07-23 09:20:00','2026-07-24 10:00:00'),
(9003,9002,2026,'301','44',@rrt,'FECHADA',0,9005,NULL,'2026-06-13 09:15:00',NULL)
ON DUPLICATE KEY UPDATE status=VALUES(status),gerado_automaticamente=VALUES(gerado_automaticamente),atualizado_por=VALUES(atualizado_por),atualizado_em=VALUES(atualizado_em);

INSERT INTO cmdf_grupo_parcelas(grupo_id,parcela_id,adicionado_por,adicionado_em) VALUES
(9001,9001,9005,'2026-07-04 11:00:00'),(9001,9002,9005,'2026-07-04 11:00:00'),(9002,9004,9005,'2026-07-23 09:20:00'),(9003,9005,9005,'2026-06-13 09:15:00')
ON DUPLICATE KEY UPDATE grupo_id=VALUES(grupo_id),adicionado_por=VALUES(adicionado_por),adicionado_em=VALUES(adicionado_em);

INSERT INTO pagamentos(id,parcela_id,status,data_pagamento,valor_liquido_pago,historico_pagamento,usuario_id,criado_em,atualizado_em) VALUES
(9001,9001,'PAGO','2026-07-07',3000.00,'Pagamento da parcela 1 da NF 50.',9002,'2026-07-06 14:35:00','2026-07-07 12:20:00'),
(9002,9002,'AGUARDANDO',NULL,NULL,NULL,NULL,'2026-07-06 14:35:00',NULL)
ON DUPLICATE KEY UPDATE status=VALUES(status),data_pagamento=VALUES(data_pagamento),valor_liquido_pago=VALUES(valor_liquido_pago),historico_pagamento=VALUES(historico_pagamento),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

DELETE FROM auditoria WHERE id BETWEEN 9000 AND 9999;
INSERT INTO auditoria(id,usuario_id,entidade,entidade_id,acao,dados_anteriores,dados_novos,ip,ocorrido_em) VALUES
(9001,9002,'documentos_pagamento',9001,'CRIAR',NULL,JSON_OBJECT('numero','50','valor_liquido',9000.00),'127.0.0.1','2026-07-02 08:15:00'),
(9002,9003,'inspecoes',9001,'LIBERAR',JSON_OBJECT('status','Inspeção andamento'),JSON_OBJECT('status','Liberada liquidação de imposto'),'127.0.0.1','2026-07-03 16:00:00'),
(9003,9004,'liquidacoes',9001,'LIQUIDAR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','LIQUIDADA'),'127.0.0.1','2026-07-04 10:15:00'),
(9004,9005,'cmdf_grupos',9001,'ATENDER',JSON_OBJECT('status','LIBERADA'),JSON_OBJECT('status','ATENDIDA'),'127.0.0.1','2026-07-06 14:30:00'),
(9005,9002,'pagamentos',9001,'PAGAR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','PAGO'),'127.0.0.1','2026-07-07 12:20:00');
