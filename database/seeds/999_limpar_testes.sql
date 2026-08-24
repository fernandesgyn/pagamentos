USE pagamentos;

-- Remove somente a faixa de IDs reservada para homologação (9000-9999).
-- A ordem respeita todas as chaves estrangeiras do schema atual.

DELETE FROM auditoria WHERE id BETWEEN 9000 AND 9999 OR usuario_id BETWEEN 9000 AND 9999;
DELETE FROM anexos WHERE id BETWEEN 9000 AND 9999 OR criado_por BETWEEN 9000 AND 9999;
DELETE FROM pagamentos WHERE id BETWEEN 9000 AND 9999 OR parcela_id BETWEEN 9000 AND 9999;
DELETE FROM cmdf_etapas WHERE id BETWEEN 9000 AND 9999 OR parcela_id BETWEEN 9000 AND 9999;
DELETE FROM liquidacoes WHERE id BETWEEN 9000 AND 9999 OR parcela_id BETWEEN 9000 AND 9999;
DELETE FROM parcelas_pagamento WHERE id BETWEEN 9000 AND 9999 OR documento_id BETWEEN 9000 AND 9999;
DELETE FROM inspecao_historico WHERE id BETWEEN 9000 AND 9999 OR inspecao_id BETWEEN 9000 AND 9999 OR usuario_id BETWEEN 9000 AND 9999;
DELETE FROM inspecoes WHERE id BETWEEN 9000 AND 9999 OR documento_id BETWEEN 9000 AND 9999 OR responsavel_id BETWEEN 9000 AND 9999;
DELETE FROM documentos_pagamento WHERE id BETWEEN 9000 AND 9999;
DELETE FROM obrigacao_fontes_recurso WHERE obrigacao_id BETWEEN 9000 AND 9999 OR fonte_recurso_id BETWEEN 9000 AND 9999;
DELETE FROM obrigacao_naturezas_despesa WHERE obrigacao_id BETWEEN 9000 AND 9999 OR natureza_despesa_id BETWEEN 9000 AND 9999;
DELETE FROM obrigacoes WHERE id BETWEEN 9000 AND 9999;
DELETE FROM fontes_recurso WHERE id BETWEEN 9000 AND 9999;
DELETE FROM naturezas_despesa WHERE id BETWEEN 9000 AND 9999;
DELETE FROM fornecedores WHERE id BETWEEN 9000 AND 9999;
DELETE FROM usuarios WHERE id BETWEEN 9000 AND 9999;
