USE pagamentos;

SET @cmp_liquido = (SELECT id FROM tipos_componente_pagamento WHERE codigo='LIQUIDO' LIMIT 1);
SET @cmp_inss = (SELECT id FROM tipos_componente_pagamento WHERE codigo='INSS' LIMIT 1);
SET @cmp_iss = (SELECT id FROM tipos_componente_pagamento WHERE codigo='ISS' LIMIT 1);
SET @cmp_ir = (SELECT id FROM tipos_componente_pagamento WHERE codigo='IR' LIMIT 1);

-- Documento 9001 / NF 50: duas parcelas fechando R$ 10.000,00.
-- Parcela 1 já percorreu o fluxo completo; parcela 2 aguarda liquidação.
INSERT INTO parcelas_pagamento
(id,documento_id,empenho_pagamento_id,numero_parcela,valor_total,historico_liquidacao,fila,justificativa_ordem_cronologica,justificativa_atraso,criado_por,criado_em)
VALUES
(9001,9001,9001,1,6000.00,'Serviços de suporte referentes ao período de junho/2026.','NORMAL',NULL,NULL,9002,'2026-07-03 16:20:00'),
(9002,9001,9002,2,4000.00,'Complemento dos serviços de suporte do período de junho/2026.','NORMAL',NULL,NULL,9002,'2026-07-03 16:25:00'),
(9003,9003,9003,1,12000.00,'Consultoria técnica conforme Fatura 77.','NORMAL','Ressalva de inspeção não impede o processamento.',NULL,9002,'2026-07-18 16:00:00'),
(9004,9005,9004,1,8000.00,'Manutenção predial executada em julho/2026.','PRIORITÁRIA',NULL,NULL,9002,'2026-07-22 13:40:00'),
(9005,9006,9005,1,3000.00,'Consultoria administrativa conforme Recibo 10.','NORMAL',NULL,NULL,9002,'2026-06-12 10:30:00')
ON DUPLICATE KEY UPDATE
 documento_id=VALUES(documento_id),empenho_pagamento_id=VALUES(empenho_pagamento_id),numero_parcela=VALUES(numero_parcela),valor_total=VALUES(valor_total),historico_liquidacao=VALUES(historico_liquidacao),fila=VALUES(fila),justificativa_ordem_cronologica=VALUES(justificativa_ordem_cronologica),justificativa_atraso=VALUES(justificativa_atraso),criado_por=VALUES(criado_por);

DELETE FROM parcela_componentes WHERE id BETWEEN 9000 AND 9999;
INSERT INTO parcela_componentes (id,parcela_id,tipo_componente_id,valor,observacao)
VALUES
-- Parcela 9001 = 5.200 + 500 + 300 = 6.000
(9001,9001,@cmp_liquido,5200.00,'Valor líquido/principal'),
(9002,9001,@cmp_inss,500.00,'Retenção de INSS'),
(9003,9001,@cmp_iss,300.00,'Retenção de ISS'),
-- Parcela 9002 = 3.500 + 500 = 4.000
(9004,9002,@cmp_liquido,3500.00,'Valor líquido/principal'),
(9005,9002,@cmp_ir,500.00,'Retenção de IR'),
-- Parcela 9003 propositalmente incompleta: 10.000 de 12.000
(9006,9003,@cmp_liquido,10000.00,'Cenário de teste: faltam R$ 2.000,00 na composição'),
-- Parcela 9004 = 7.000 + 500 + 500 = 8.000
(9007,9004,@cmp_liquido,7000.00,'Valor líquido/principal'),
(9008,9004,@cmp_iss,500.00,'Retenção de ISS'),
(9009,9004,@cmp_ir,500.00,'Retenção de IR'),
-- Parcela 9005 = 2.700 + 150 + 150 = 3.000
(9010,9005,@cmp_liquido,2700.00,'Valor líquido/principal'),
(9011,9005,@cmp_ir,150.00,'Retenção de IR'),
(9012,9005,@cmp_iss,150.00,'Retenção de ISS');

INSERT INTO liquidacoes
(id,parcela_id,status,data_liquidacao,justificativa_anulacao,usuario_id,criado_em,atualizado_em)
VALUES
(9001,9001,'CONCLUIDA','2026-07-04',NULL,9004,'2026-07-03 16:20:00','2026-07-04 10:15:00'),
(9002,9002,'AGUARDANDO',NULL,NULL,NULL,'2026-07-03 16:25:00',NULL),
(9003,9003,'AGUARDANDO',NULL,NULL,NULL,'2026-07-18 16:00:00',NULL),
(9004,9004,'CONCLUIDA','2026-07-23',NULL,9004,'2026-07-22 13:40:00','2026-07-23 09:10:00'),
(9005,9005,'CONCLUIDA','2026-06-13',NULL,9004,'2026-06-12 10:30:00','2026-06-13 09:00:00')
ON DUPLICATE KEY UPDATE
 status=VALUES(status),data_liquidacao=VALUES(data_liquidacao),justificativa_anulacao=VALUES(justificativa_anulacao),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

INSERT INTO cmdf_etapas
(id,parcela_id,status,data_envio_seinfra,data_despacho_seinfra,data_envio_economia,data_atendimento_economia,data_conclusao,observacoes,usuario_id,criado_em,atualizado_em)
VALUES
(9001,9001,'CONCLUIDA','2026-07-04','2026-07-05','2026-07-05','2026-07-06','2026-07-06','Fluxo CMDF concluído sem pendências.',9005,'2026-07-04 10:20:00','2026-07-06 14:30:00'),
(9004,9004,'AGUARDANDO','2026-07-23','2026-07-24','2026-07-24',NULL,NULL,'Aguardando atendimento pela Economia.',9005,'2026-07-23 09:15:00','2026-07-24 15:40:00'),
(9005,9005,'CONCLUIDA','2026-06-13','2026-06-14','2026-06-14','2026-06-15','2026-06-15','CMDF finalizada.',9005,'2026-06-13 09:10:00','2026-06-15 11:00:00')
ON DUPLICATE KEY UPDATE
 status=VALUES(status),data_envio_seinfra=VALUES(data_envio_seinfra),data_despacho_seinfra=VALUES(data_despacho_seinfra),data_envio_economia=VALUES(data_envio_economia),data_atendimento_economia=VALUES(data_atendimento_economia),data_conclusao=VALUES(data_conclusao),observacoes=VALUES(observacoes),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

INSERT INTO pagamentos
(id,parcela_id,status,data_pagamento,valor_liquido_pago,historico_pagamento,benner_ap,usuario_id,criado_em,atualizado_em)
VALUES
(9001,9001,'PAGO','2026-07-07',5200.00,'Pagamento da parcela 1 da NF 50. Retenções tratadas nos componentes da parcela.','AP-2026-000501',9002,'2026-07-06 14:35:00','2026-07-07 12:20:00'),
(9005,9005,'PAGO','2026-06-16',2700.00,'Pagamento integral do Recibo 10 após retenções.','AP-2026-000410',9002,'2026-06-15 11:05:00','2026-06-16 11:30:00')
ON DUPLICATE KEY UPDATE
 status=VALUES(status),data_pagamento=VALUES(data_pagamento),valor_liquido_pago=VALUES(valor_liquido_pago),historico_pagamento=VALUES(historico_pagamento),benner_ap=VALUES(benner_ap),usuario_id=VALUES(usuario_id),atualizado_em=VALUES(atualizado_em);

-- Auditoria de homologação: alguns eventos representativos do fluxo.
DELETE FROM auditoria WHERE id BETWEEN 9000 AND 9999;
INSERT INTO auditoria
(id,usuario_id,entidade,entidade_id,acao,dados_anteriores,dados_novos,ip,ocorrido_em)
VALUES
(9001,9002,'documentos_pagamento',9001,'CRIAR',NULL,JSON_OBJECT('numero','50','valor_bruto',10000.00),'127.0.0.1','2026-07-02 08:15:00'),
(9002,9003,'inspecoes',9001,'CONCLUIR',JSON_OBJECT('status','Em andamento'),JSON_OBJECT('status','Concluída','data_conclusao','2026-07-03'),'127.0.0.1','2026-07-03 16:00:00'),
(9003,9004,'liquidacoes',9001,'CONCLUIR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','CONCLUIDA','data_liquidacao','2026-07-04'),'127.0.0.1','2026-07-04 10:15:00'),
(9004,9005,'cmdf_etapas',9001,'CONCLUIR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','CONCLUIDA','data_conclusao','2026-07-06'),'127.0.0.1','2026-07-06 14:30:00'),
(9005,9002,'pagamentos',9001,'PAGAR',JSON_OBJECT('status','AGUARDANDO'),JSON_OBJECT('status','PAGO','data_pagamento','2026-07-07','valor_liquido_pago',5200.00),'127.0.0.1','2026-07-07 12:20:00');
