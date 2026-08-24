USE pagamentos;

SET @tipo_contrato = (SELECT id FROM tipos_obrigacao WHERE nome='Contrato' LIMIT 1);
SET @tipo_empenho = (SELECT id FROM tipos_obrigacao WHERE nome='Empenho' LIMIT 1);
SET @tipo_taxa = (SELECT id FROM tipos_obrigacao WHERE nome='Taxa / Tarifa' LIMIT 1);
SET @tipo_judicial = (SELECT id FROM tipos_obrigacao WHERE nome='Despesas Judiciais' LIMIT 1);
SET @tipo_diaria = (SELECT id FROM tipos_obrigacao WHERE nome='Diárias' LIMIT 1);

SET @doc_nf = (SELECT id FROM tipos_documento_pagamento WHERE nome='Nota Fiscal' LIMIT 1);
SET @doc_fatura = (SELECT id FROM tipos_documento_pagamento WHERE nome='Fatura' LIMIT 1);
SET @doc_recibo = (SELECT id FROM tipos_documento_pagamento WHERE nome='Recibo' LIMIT 1);
SET @doc_boleto = (SELECT id FROM tipos_documento_pagamento WHERE nome='Boleto' LIMIT 1);

SET @st_aguardando = (SELECT id FROM status_inspecao WHERE nome='Aguardando inspeção' LIMIT 1);
SET @st_andamento = (SELECT id FROM status_inspecao WHERE nome='Inspeção andamento' LIMIT 1);
SET @st_pendente = (SELECT id FROM status_inspecao WHERE nome='Pendente de complementação' LIMIT 1);
SET @st_devolvida = (SELECT id FROM status_inspecao WHERE nome='Devolvida para o gestor' LIMIT 1);
SET @st_retornada = (SELECT id FROM status_inspecao WHERE nome='Retornada para inspeção' LIMIT 1);
SET @st_finalizada = (SELECT id FROM status_inspecao WHERE nome='Finalizada' LIMIT 1);
SET @st_liberada = (SELECT id FROM status_inspecao WHERE nome='Liberada liquidação de imposto' LIMIT 1);
SET @st_cancelada = (SELECT id FROM status_inspecao WHERE nome='Cancelada' LIMIT 1);

-- Obrigações de homologação.
-- 9001 e 9006 usam a mesma referência Contrato 101/2026 em fornecedores diferentes.
-- Isso valida a cardinalidade Fornecedor 1:N Obrigações sem impor unicidade global indevida.
INSERT INTO obrigacoes
(id,tipo_obrigacao_id,fornecedor_id,numero,ano,valor_total,nr_sei_contratacao,data_inicio,data_fim,ativo,criado_por,criado_em)
VALUES
(9001,@tipo_contrato,9001,'101',2026,120000.00,'2026000000101','2026-01-01','2026-12-31',1,9002,'2026-01-05 09:00:00'),
(9002,@tipo_empenho,9002,'2026NE000123',2026,25000.00,'2026000000202','2026-03-01','2026-09-30',1,9002,'2026-03-02 10:10:00'),
(9003,@tipo_taxa,9003,'15',2026,18000.00,'2026000000303','2026-04-01','2026-10-31',1,9002,'2026-04-02 14:20:00'),
(9004,@tipo_judicial,9004,'102',2026,45000.00,'2026000000404','2026-02-15','2026-12-15',1,9002,'2026-02-16 08:40:00'),
(9005,@tipo_diaria,9005,'77',2026,12000.00,'2026000000505','2026-05-10','2026-08-31',1,9002,'2026-05-11 11:00:00'),
(9006,@tipo_contrato,9002,'101',2026,60000.00,'2026000000606','2026-02-01','2026-11-30',1,9002,'2026-02-02 10:00:00')
ON DUPLICATE KEY UPDATE
 tipo_obrigacao_id=VALUES(tipo_obrigacao_id),fornecedor_id=VALUES(fornecedor_id),numero=VALUES(numero),ano=VALUES(ano),valor_total=VALUES(valor_total),nr_sei_contratacao=VALUES(nr_sei_contratacao),data_inicio=VALUES(data_inicio),data_fim=VALUES(data_fim),ativo=1,criado_por=VALUES(criado_por);

INSERT INTO obrigacao_fontes_recurso (obrigacao_id,fonte_recurso_id) VALUES
(9001,9001),(9001,9002),
(9002,9001),
(9003,9002),
(9004,9002),(9004,9004),
(9005,9003),
(9006,9001)
ON DUPLICATE KEY UPDATE fonte_recurso_id=VALUES(fonte_recurso_id);

INSERT INTO obrigacao_naturezas_despesa (obrigacao_id,natureza_despesa_id) VALUES
(9001,9003),(9001,9004),
(9002,9003),
(9003,9003),
(9004,9002),(9004,9003),
(9005,9002),
(9006,9003)
ON DUPLICATE KEY UPDATE natureza_despesa_id=VALUES(natureza_despesa_id);

INSERT INTO documentos_pagamento
(id,obrigacao_id,tipo_documento_id,numero,data_emissao,data_atesto,data_envio_cooinsp,valor_bruto,valor_liquido,data_lancamento,criado_por,criado_em)
VALUES
(9001,9001,@doc_nf,'50','2026-07-01','2026-07-01','2026-07-02',10000.00,9000.00,'2026-07-02 08:15:00',9002,'2026-07-02 08:15:00'),
(9002,9001,@doc_nf,'51','2026-08-03','2026-08-03','2026-08-04',5000.00,4500.00,'2026-08-04 09:20:00',9002,'2026-08-04 09:20:00'),
(9003,9005,@doc_fatura,'77','2026-07-15','2026-07-15','2026-07-16',12000.00,11000.00,'2026-07-16 10:00:00',9002,'2026-07-16 10:00:00'),
(9004,9004,@doc_nf,'900','2026-08-05','2026-08-05','2026-08-06',4500.00,4200.00,'2026-08-06 11:30:00',9002,'2026-08-06 11:30:00'),
(9005,9002,@doc_nf,'901','2026-07-20','2026-07-20','2026-07-21',8000.00,7500.00,'2026-07-21 08:50:00',9002,'2026-07-21 08:50:00'),
(9006,9003,@doc_recibo,'10','2026-06-10','2026-06-10','2026-06-11',3000.00,2700.00,'2026-06-11 13:10:00',9002,'2026-06-11 13:10:00'),
(9007,9002,@doc_boleto,'B-12','2026-08-11',NULL,'2026-08-12',2500.00,2400.00,'2026-08-12 15:45:00',9002,'2026-08-12 15:45:00'),
(9008,9004,@doc_nf,'888','2026-07-08','2026-07-08','2026-07-09',7000.00,6500.00,'2026-07-09 09:05:00',9002,'2026-07-09 09:05:00'),
(9009,9001,@doc_nf,'52','2026-08-18','2026-08-18',NULL,6200.00,5800.00,'2026-08-19 08:05:00',9002,'2026-08-19 08:05:00'),
(9010,9003,@doc_fatura,'78','2026-08-01','2026-08-01','2026-08-02',6000.00,5600.00,'2026-08-02 10:40:00',9002,'2026-08-02 10:40:00')
ON DUPLICATE KEY UPDATE
 obrigacao_id=VALUES(obrigacao_id),tipo_documento_id=VALUES(tipo_documento_id),numero=VALUES(numero),data_emissao=VALUES(data_emissao),data_atesto=VALUES(data_atesto),data_envio_cooinsp=VALUES(data_envio_cooinsp),valor_bruto=VALUES(valor_bruto),valor_liquido=VALUES(valor_liquido),data_lancamento=VALUES(data_lancamento),criado_por=VALUES(criado_por);

INSERT INTO inspecoes
(id,documento_id,status_id,data_conclusao,responsavel_id,criado_em,atualizado_em)
VALUES
(9001,9001,@st_liberada,'2026-07-03',9003,'2026-07-02 08:20:00','2026-07-03 16:00:00'),
(9002,9002,@st_andamento,NULL,9003,'2026-08-04 09:30:00','2026-08-04 10:00:00'),
(9003,9003,@st_finalizada,'2026-07-18',9003,'2026-07-16 10:10:00','2026-07-18 15:30:00'),
(9004,9004,@st_devolvida,NULL,9003,'2026-08-06 11:40:00','2026-08-07 14:00:00'),
(9005,9005,@st_liberada,'2026-07-22',9003,'2026-07-21 09:00:00','2026-07-22 11:20:00'),
(9006,9006,@st_liberada,'2026-06-12',9003,'2026-06-11 13:20:00','2026-06-12 10:00:00'),
(9007,9007,@st_pendente,NULL,9003,'2026-08-12 15:55:00','2026-08-13 09:30:00'),
(9008,9008,@st_cancelada,'2026-07-10',9003,'2026-07-09 09:20:00','2026-07-10 08:50:00'),
(9009,9009,@st_aguardando,NULL,NULL,'2026-08-19 08:05:00',NULL),
(9010,9010,@st_retornada,NULL,9003,'2026-08-02 10:50:00','2026-08-05 09:30:00')
ON DUPLICATE KEY UPDATE
 status_id=VALUES(status_id),data_conclusao=VALUES(data_conclusao),responsavel_id=VALUES(responsavel_id),atualizado_em=VALUES(atualizado_em);

DELETE FROM inspecao_historico WHERE id BETWEEN 9000 AND 9999;
INSERT INTO inspecao_historico (id,inspecao_id,status_id,observacao,usuario_id,ocorrido_em)
VALUES
(9001,9001,@st_aguardando,'Documento lançado.',9002,'2026-07-02 08:15:00'),
(9002,9001,@st_andamento,'Análise iniciada.',9003,'2026-07-02 08:30:00'),
(9003,9001,@st_liberada,'Inspeção liberada para programação.',9003,'2026-07-03 16:00:00'),
(9004,9003,@st_andamento,'Análise iniciada.',9003,'2026-07-16 10:20:00'),
(9005,9003,@st_finalizada,'Inspeção finalizada sem liberação para a próxima fase.',9003,'2026-07-18 15:30:00'),
(9006,9004,@st_andamento,'Análise iniciada.',9003,'2026-08-06 11:45:00'),
(9007,9004,@st_devolvida,'Documento devolvido ao gestor.',9003,'2026-08-07 14:00:00'),
(9008,9007,@st_pendente,'Solicitada comprovação adicional.',9003,'2026-08-13 09:30:00'),
(9009,9010,@st_andamento,'Primeira análise.',9003,'2026-08-02 11:00:00'),
(9010,9010,@st_devolvida,'Solicitado ajuste.',9003,'2026-08-03 15:00:00'),
(9011,9010,@st_retornada,'Documentação corrigida e reenviada.',9002,'2026-08-05 09:20:00');
