USE pagamentos;

SET @tipo_contrato = (SELECT id FROM tipos_obrigacao WHERE nome='Contrato' LIMIT 1);
SET @tipo_empenho_obrigacao = (SELECT id FROM tipos_obrigacao WHERE nome='Empenho que substitui contrato' LIMIT 1);
SET @tipo_carta = (SELECT id FROM tipos_obrigacao WHERE nome='Carta-Contrato' LIMIT 1);
SET @tipo_ordem = (SELECT id FROM tipos_obrigacao WHERE nome='Ordem de Fornecimento/Serviço' LIMIT 1);

SET @doc_nf = (SELECT id FROM tipos_documento_pagamento WHERE nome='Nota Fiscal' LIMIT 1);
SET @doc_fatura = (SELECT id FROM tipos_documento_pagamento WHERE nome='Fatura' LIMIT 1);
SET @doc_recibo = (SELECT id FROM tipos_documento_pagamento WHERE nome='Recibo' LIMIT 1);
SET @doc_boleto = (SELECT id FROM tipos_documento_pagamento WHERE nome='Boleto' LIMIT 1);

SET @st_aguardando = (SELECT id FROM status_inspecao WHERE nome='Aguardando inspeção' LIMIT 1);
SET @st_andamento = (SELECT id FROM status_inspecao WHERE nome='Em andamento' LIMIT 1);
SET @st_pendente = (SELECT id FROM status_inspecao WHERE nome='Pendente de complementação' LIMIT 1);
SET @st_devolvida = (SELECT id FROM status_inspecao WHERE nome='Devolvida para o gestor' LIMIT 1);
SET @st_retornada = (SELECT id FROM status_inspecao WHERE nome='Retornada para inspeção' LIMIT 1);
SET @st_concluida = (SELECT id FROM status_inspecao WHERE nome='Concluída' LIMIT 1);
SET @st_ressalvas = (SELECT id FROM status_inspecao WHERE nome='Concluída com ressalvas' LIMIT 1);
SET @st_cancelada = (SELECT id FROM status_inspecao WHERE nome='Cancelada' LIMIT 1);

INSERT INTO obrigacoes
(id,tipo_obrigacao_id,fornecedor_id,numero,ano,objeto,valor_global,data_inicio,data_fim,sei,ativo,criado_por,criado_em)
VALUES
(9001,@tipo_contrato,9001,'101',2026,'Prestação continuada de serviços de suporte e manutenção de sistemas.',120000.00,'2026-01-01','2026-12-31','2026000000101',1,9002,'2026-01-05 09:00:00'),
(9002,@tipo_empenho_obrigacao,9002,'202600123',2026,'Serviços de manutenção predial sem instrumento contratual autônomo.',25000.00,'2026-03-01','2026-09-30','2026000000202',1,9002,'2026-03-02 10:10:00'),
(9003,@tipo_carta,9003,'15',2026,'Consultoria especializada para revisão de processos administrativos.',18000.00,'2026-04-01','2026-10-31','2026000000303',1,9002,'2026-04-02 14:20:00'),
(9004,@tipo_contrato,9004,'102',2026,'Fornecimento parcelado de materiais e suprimentos administrativos.',45000.00,'2026-02-15','2026-12-15','2026000000404',1,9002,'2026-02-16 08:40:00'),
(9005,@tipo_ordem,9005,'77',2026,'Serviço pontual de diagnóstico e apoio técnico.',12000.00,'2026-05-10','2026-08-31','2026000000505',1,9002,'2026-05-11 11:00:00')
ON DUPLICATE KEY UPDATE
 tipo_obrigacao_id=VALUES(tipo_obrigacao_id),fornecedor_id=VALUES(fornecedor_id),numero=VALUES(numero),ano=VALUES(ano),objeto=VALUES(objeto),valor_global=VALUES(valor_global),data_inicio=VALUES(data_inicio),data_fim=VALUES(data_fim),sei=VALUES(sei),ativo=1,criado_por=VALUES(criado_por);

INSERT INTO documentos_pagamento
(id,obrigacao_id,tipo_documento_id,numero,data_documento,valor_bruto,data_lancamento,data_maxima_liquidacao,limite_anotacao,data_atesto,tipo_servico,sei_pagamento,observacoes,criado_por,criado_em)
VALUES
(9001,9001,@doc_nf,'50','2026-07-01',10000.00,'2026-07-02 08:15:00','2026-07-10','2026-07-08','2026-07-01','Suporte e manutenção','2026000010050','Cenário: documento fechado em duas parcelas; uma já paga e outra aguardando liquidação.',9002,'2026-07-02 08:15:00'),
(9002,9001,@doc_nf,'51','2026-08-03',5000.00,'2026-08-04 09:20:00','2026-08-12','2026-08-10','2026-08-03','Suporte e manutenção','2026000010051','Cenário: inspeção em andamento, sem programação.',9002,'2026-08-04 09:20:00'),
(9003,9005,@doc_fatura,'77','2026-07-15',12000.00,'2026-07-16 10:00:00','2026-07-24','2026-07-22','2026-07-15','Consultoria técnica','2026000010077','Cenário: inspeção concluída com ressalvas e composição financeira incompleta.',9002,'2026-07-16 10:00:00'),
(9004,9004,@doc_nf,'900','2026-08-05',4500.00,'2026-08-06 11:30:00','2026-08-14','2026-08-12','2026-08-05','Fornecimento de materiais','2026000010900','Cenário: devolvida ao gestor por pendência documental.',9002,'2026-08-06 11:30:00'),
(9005,9002,@doc_nf,'901','2026-07-20',8000.00,'2026-07-21 08:50:00','2026-07-29','2026-07-27','2026-07-20','Manutenção predial','2026000010901','Cenário: liquidada e aguardando conclusão da CMDF.',9002,'2026-07-21 08:50:00'),
(9006,9003,@doc_recibo,'10','2026-06-10',3000.00,'2026-06-11 13:10:00','2026-06-19','2026-06-17','2026-06-10','Consultoria administrativa','2026000010010','Cenário completo: inspecionado, liquidado, CMDF concluída e pago.',9002,'2026-06-11 13:10:00'),
(9007,9002,@doc_boleto,'B-12','2026-08-11',2500.00,'2026-08-12 15:45:00','2026-08-20','2026-08-18',NULL,'Serviço eventual','2026000010012','Cenário: pendente de complementação.',9002,'2026-08-12 15:45:00'),
(9008,9004,@doc_nf,'888','2026-07-08',7000.00,'2026-07-09 09:05:00','2026-07-17','2026-07-15','2026-07-08','Fornecimento de materiais','2026000010888','Cenário: inspeção cancelada.',9002,'2026-07-09 09:05:00'),
(9009,9001,@doc_nf,'52','2026-08-18',6200.00,'2026-08-19 08:05:00','2026-08-27','2026-08-25','2026-08-18','Suporte e manutenção','2026000010052','Cenário: documento recém-lançado aguardando inspeção.',9002,'2026-08-19 08:05:00'),
(9010,9003,@doc_fatura,'78','2026-08-01',6000.00,'2026-08-02 10:40:00','2026-08-10','2026-08-08','2026-08-01','Consultoria administrativa','2026000010078','Cenário: retornou da pendência e está novamente em inspeção.',9002,'2026-08-02 10:40:00')
ON DUPLICATE KEY UPDATE
 obrigacao_id=VALUES(obrigacao_id),tipo_documento_id=VALUES(tipo_documento_id),numero=VALUES(numero),data_documento=VALUES(data_documento),valor_bruto=VALUES(valor_bruto),data_lancamento=VALUES(data_lancamento),data_maxima_liquidacao=VALUES(data_maxima_liquidacao),limite_anotacao=VALUES(limite_anotacao),data_atesto=VALUES(data_atesto),tipo_servico=VALUES(tipo_servico),sei_pagamento=VALUES(sei_pagamento),observacoes=VALUES(observacoes),criado_por=VALUES(criado_por);

INSERT INTO inspecoes
(id,documento_id,status_id,data_envio_cooinsp,hora_envio_cooinsp,data_devolucao_pendencia,motivo_devolucao,data_retorno_pendencia,hora_retorno_cooinsp,data_conclusao,observacoes,responsavel_id,criado_em,atualizado_em)
VALUES
(9001,9001,@st_concluida,'2026-07-02','08:30:00',NULL,NULL,NULL,NULL,'2026-07-03','Inspeção conclusiva sem apontamentos.',9003,'2026-07-02 08:20:00','2026-07-03 16:00:00'),
(9002,9002,@st_andamento,'2026-08-04','09:40:00',NULL,NULL,NULL,NULL,NULL,'Documentação em análise.',9003,'2026-08-04 09:30:00','2026-08-04 10:00:00'),
(9003,9003,@st_ressalvas,'2026-07-16','10:20:00',NULL,NULL,NULL,NULL,'2026-07-18','Concluída com ressalva formal sem impedir o pagamento.',9003,'2026-07-16 10:10:00','2026-07-18 15:30:00'),
(9004,9004,@st_devolvida,'2026-08-06','11:45:00','2026-08-07','Ausência de documento complementar de recebimento.',NULL,NULL,NULL,'Aguardando providência do gestor.',9003,'2026-08-06 11:40:00','2026-08-07 14:00:00'),
(9005,9005,@st_concluida,'2026-07-21','09:10:00',NULL,NULL,NULL,NULL,'2026-07-22','Serviço confirmado e documentação regular.',9003,'2026-07-21 09:00:00','2026-07-22 11:20:00'),
(9006,9006,@st_concluida,'2026-06-11','13:30:00',NULL,NULL,NULL,NULL,'2026-06-12','Inspeção concluída.',9003,'2026-06-11 13:20:00','2026-06-12 10:00:00'),
(9007,9007,@st_pendente,'2026-08-12','16:00:00','2026-08-13','Necessária comprovação adicional do serviço.',NULL,NULL,NULL,'Aguardando complementação.',9003,'2026-08-12 15:55:00','2026-08-13 09:30:00'),
(9008,9008,@st_cancelada,'2026-07-09','09:30:00',NULL,NULL,NULL,NULL,'2026-07-10','Documento cancelado pelo gestor antes da programação.',9003,'2026-07-09 09:20:00','2026-07-10 08:50:00'),
(9009,9009,@st_aguardando,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Aguardando análise inicial.',NULL,'2026-08-19 08:05:00',NULL),
(9010,9010,@st_retornada,'2026-08-02','11:00:00','2026-08-03','Solicitado ajuste no relatório de execução.','2026-08-05','09:20:00',NULL,'Documentação retornou e aguarda nova análise.',9003,'2026-08-02 10:50:00','2026-08-05 09:30:00')
ON DUPLICATE KEY UPDATE
 status_id=VALUES(status_id),data_envio_cooinsp=VALUES(data_envio_cooinsp),hora_envio_cooinsp=VALUES(hora_envio_cooinsp),data_devolucao_pendencia=VALUES(data_devolucao_pendencia),motivo_devolucao=VALUES(motivo_devolucao),data_retorno_pendencia=VALUES(data_retorno_pendencia),hora_retorno_cooinsp=VALUES(hora_retorno_cooinsp),data_conclusao=VALUES(data_conclusao),observacoes=VALUES(observacoes),responsavel_id=VALUES(responsavel_id),atualizado_em=VALUES(atualizado_em);

DELETE FROM inspecao_historico WHERE id BETWEEN 9000 AND 9999;
INSERT INTO inspecao_historico (id,inspecao_id,status_id,observacao,usuario_id,ocorrido_em)
VALUES
(9001,9001,@st_aguardando,'Documento lançado.',9002,'2026-07-02 08:15:00'),
(9002,9001,@st_andamento,'Análise iniciada.',9003,'2026-07-02 08:30:00'),
(9003,9001,@st_concluida,'Inspeção finalizada sem pendências.',9003,'2026-07-03 16:00:00'),
(9004,9003,@st_andamento,'Análise iniciada.',9003,'2026-07-16 10:20:00'),
(9005,9003,@st_ressalvas,'Concluída com ressalva não impeditiva.',9003,'2026-07-18 15:30:00'),
(9006,9004,@st_andamento,'Análise iniciada.',9003,'2026-08-06 11:45:00'),
(9007,9004,@st_devolvida,'Falta documento de recebimento.',9003,'2026-08-07 14:00:00'),
(9008,9007,@st_pendente,'Solicitada comprovação adicional.',9003,'2026-08-13 09:30:00'),
(9009,9010,@st_andamento,'Primeira análise.',9003,'2026-08-02 11:00:00'),
(9010,9010,@st_devolvida,'Ajustar relatório de execução.',9003,'2026-08-03 15:00:00'),
(9011,9010,@st_retornada,'Documentação corrigida e reenviada.',9002,'2026-08-05 09:20:00');
