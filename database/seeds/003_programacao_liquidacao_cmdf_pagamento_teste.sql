USE pagamentos;

SET @rrt = (SELECT id FROM tipos_recurso WHERE codigo='RRT' LIMIT 1);
SET @rdo = (SELECT id FROM tipos_recurso WHERE codigo='RDO' LIMIT 1);

-- NF 50 / documento 9001: programação fechada em três parcelas independentes.
-- P1 paga, P2 na CMDF e P3 aguardando liquidação.
INSERT INTO parcelas_pagamento
(id,documento_id,numero_parcela,numero_empenho,natureza_despesa_id,exercicio_orcamentario,fonte_recurso_id,tipo_recurso_id,valor_liquido,tipo,data_vencimento,historico_parcela,justificativa_ordem_cronologica,criado_por,criado_em)
VALUES
(9001,9001,1,'2026NE000101',9004,2026,9001,@rrt,3000.00,'ISS','2026-07-10','Parcela 1 da NF 50 - ISS.',NULL,9002,'2026-07-03 16:20:00'),
(9002,9001,2,'2026NE000102',9003,2026,9002,@rdo,3000.00,'IR','2026-07-12','Parcela 2 da NF 50 - IR.',NULL,9002,'2026-07-03 16:25:00'),
(9003,9001,3,'2026NE000103',9003,2026,9001,@rrt,3000.00,'INSS',NULL,'Parcela 3 da NF 50 - INSS.','Processamento conforme ordem cronológica.',9002,'2026-07-03 16:30:00'),
(9004,9005,1,'2026NE000104',9003,2026,9001,@rdo,7500.00,'DARE','2026-07-30','Parcela única da NF 901.',NULL,9002,'2026-07-22 13:40:00'),
(9005,9006,1,'2026NE000105',9003,2026,9002,@rrt,2700.00,'PIS',NULL,'Parcela única do Recibo 10.',NULL,9002,'2026-06-12 10:30:00')
ON DUPLICATE KEY UPDATE
 documento_id=VALUES(documento_id),numero_parcela=VALUES(numero_parcela),numero_empenho=VALUES(numero_empenho),natureza_despesa_id=VALUES(natureza_despesa_id),exercicio_orcamentario=VALUES(exercicio_orcamentario),fonte_recurso_id=VALUES(fonte_recurso_id),tipo_recurso_id=VALUES(tipo_recurso_id),valor_liquido=VALUES(valor_liquido),tipo=VALUES(tipo),data_vencimento=VALUES(data_vencimento),historico_parcela=VALUES(historico_parcela),justificativa_ordem_cronologica=VALUES(justificativa_ordem_cronologica),criado_por=VALUES(criado_por);

INSERT INTO liquidacoes
(id,parcela_id,status,data_liquidacao,usuario_id,criado_em,atualizado_em)
VALUES
(9001,9001,'LIQUIDADA','2026-07-04',9004,'2026-07-03 16:20:00','2026-07-04 10:15:00'),
(9002,9002,'LIQUIDADA','2026-07-04',9004,'2026-07-03 16:25:00','2026-07-04 10:20:00'),
(9003,9003,'AGUARDANDO',NULL,NULL,'2026-07-03 16:30:00',NULL),
(9004,9004,'LIQUIDADA','2026-07-23',9004,'2026-07-22 13:40:00','2026-07-23 09:10:00'),
(9005,9005,'LIQUIDADA','2026-06-13',9004,'2026-06-12 10:30:00','2026-06-13 09:00:00')
ON DUPLICATE KEY UPDATE
 status=VALUES(status),data_liquidacao=VALUES(data_liquidacao),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

INSERT INTO cmdf_etapas
(id,parcela_id,status,data_conclusao,usuario_id,criado_em,atualizado_em)
VALUES
(9001,9001,'LIQUIDADA','2026-07-06',9005,'2026-07-04 10:20:00','2026-07-06 14:30:00'),
(9002,9002,'AGUARDANDO',NULL,9005,'2026-07-04 10:25:00',NULL),
(9004,9004,'AGUARDANDO',NULL,9005,'2026-07-23 09:15:00',NULL),
(9005,9005,'LIQUIDADA','2026-06-15',9005,'2026-06-13 09:10:00','2026-06-15 11:00:00')
ON DUPLICATE KEY UPDATE
 status=VALUES(status),data_conclusao=VALUES(data_conclusao),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

INSERT INTO pagamentos
(id,parcela_id,status,data_pagamento,valor_liquido_pago,historico_pagamento,benner_ap,usuario_id,criado_em,atualizado_em)
VALUES
(9001,9001,'PAGO','2026-07-07',3000.00,'Pagamento da parcela 1 da NF 50.','AP-2026-000501',9002,'2026-07-06 14:35:00','2026-07-07 12:20:00'),
(9005,9005,'PAGO','2026-06-16',2700.00,'Pagamento integral do Recibo 10.','AP-2026-000410',9002,'2026-06-15 11:05:00','2026-06-16 11:30:00')
ON DUPLICATE KEY UPDATE
 status=VALUES(status),data_pagamento=VALUES(data_pagamento),valor_liquido_pago=VALUES(valor_liquido_pago),historico_pagamento=VALUES(historico_pagamento),benner_ap=VALUES(benner_ap),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

DELETE FROM auditoria WHERE id BETWEEN 9000 AND 9999;
INSERT INTO auditoria
(id,usuario_id,entidade,entidade_id,acao,dados_anteriores,dados_novos,ip,ocorrido_em)
VALUES
(9001,9002,'documentos_pagamento',9001,'CRIAR',NULL,JSON_OBJECT('numero','50','valor_bruto',10000.00,'valor_liquido',9000.00),'127.0.0.1','2026-07-02 08:15:00'),
(9002,9003,'inspecoes',9001,'LIBERAR',JSON_OBJECT('status','Inspeção andamento'),JSON_OBJECT('status','Liberada liquidação de imposto','data_conclusao','2026-07-03'),'127.0.0.1','2026-07-03 16:00:00'),
(9003,9004,'liquidacoes',9001,'LIQUIDAR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','LIQUIDADA','data_liquidacao','2026-07-04'),'127.0.0.1','2026-07-04 10:15:00'),
(9004,9005,'cmdf_etapas',9001,'CONCLUIR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','LIQUIDADA','data_conclusao','2026-07-06'),'127.0.0.1','2026-07-06 14:30:00'),
(9005,9002,'pagamentos',9001,'PAGAR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','PAGO','data_pagamento','2026-07-07','valor_liquido_pago',3000.00),'127.0.0.1','2026-07-07 12:20:00');
