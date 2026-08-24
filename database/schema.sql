-- Schema consolidado do sistema de Liquidação e Pagamentos.
-- Uso: provisionamento de banco NOVO. Não há migrations.
-- O script não apaga nem altera estruturas preexistentes: se já houver tabelas,
-- a execução falha e exige intervenção explícita do administrador.

CREATE DATABASE IF NOT EXISTS pagamentos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE pagamentos;

CREATE TABLE perfis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  descricao VARCHAR(255) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  perfil_id BIGINT UNSIGNED NOT NULL,
  nome VARCHAR(150) NOT NULL,
  login VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(150) NULL,
  senha_hash VARCHAR(255) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  trocar_senha TINYINT(1) NOT NULL DEFAULT 0,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_usuarios_perfis FOREIGN KEY (perfil_id) REFERENCES perfis(id),
  INDEX idx_usuarios_nome (nome),
  INDEX idx_usuarios_perfil (perfil_id)
) ENGINE=InnoDB;

CREATE TABLE permissoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chave VARCHAR(100) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE perfil_permissoes (
  perfil_id BIGINT UNSIGNED NOT NULL,
  permissao_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (perfil_id, permissao_id),
  CONSTRAINT fk_pp_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id) ON DELETE CASCADE,
  CONSTRAINT fk_pp_permissao FOREIGN KEY (permissao_id) REFERENCES permissoes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE fornecedores (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  razao_social VARCHAR(200) NOT NULL,
  documento VARCHAR(14) NOT NULL UNIQUE,
  tipo_pessoa ENUM('PF','PJ') NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT chk_fornecedor_documento CHECK (
    (tipo_pessoa='PF' AND CHAR_LENGTH(documento)=11) OR
    (tipo_pessoa='PJ' AND CHAR_LENGTH(documento)=14)
  ),
  INDEX idx_fornecedor_razao (razao_social),
  INDEX idx_fornecedor_tipo (tipo_pessoa)
) ENGINE=InnoDB;

CREATE TABLE fontes_recurso (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE naturezas_despesa (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE tipos_recurso (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(150) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE tipos_obrigacao (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  exige_numero_ano TINYINT(1) NOT NULL DEFAULT 1,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE obrigacoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_obrigacao_id BIGINT UNSIGNED NOT NULL,
  fornecedor_id BIGINT UNSIGNED NOT NULL,
  numero VARCHAR(50) NOT NULL,
  ano SMALLINT UNSIGNED NOT NULL,
  valor_total DECIMAL(15,2) NOT NULL,
  nr_sei_contratacao VARCHAR(50) NULL,
  data_inicio DATE NULL,
  data_fim DATE NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_obrigacao_tipo FOREIGN KEY (tipo_obrigacao_id) REFERENCES tipos_obrigacao(id),
  CONSTRAINT fk_obrigacao_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
  CONSTRAINT fk_obrigacao_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  CONSTRAINT chk_obrigacao_ano CHECK (ano BETWEEN 2000 AND 2100),
  CONSTRAINT chk_obrigacao_valor CHECK (valor_total > 0),
  CONSTRAINT chk_obrigacao_datas CHECK (data_fim IS NULL OR data_inicio IS NULL OR data_fim >= data_inicio),
  -- Um fornecedor pode ter N obrigações. A mesma referência pode existir para fornecedores distintos,
  -- mas não pode ser duplicada para o mesmo fornecedor.
  UNIQUE KEY uq_obrigacao_fornecedor_tipo_numero_ano (fornecedor_id, tipo_obrigacao_id, numero, ano),
  INDEX idx_obrigacao_fornecedor (fornecedor_id),
  INDEX idx_obrigacao_ano (ano)
) ENGINE=InnoDB;

CREATE TABLE obrigacao_fontes_recurso (
  obrigacao_id BIGINT UNSIGNED NOT NULL,
  fonte_recurso_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (obrigacao_id, fonte_recurso_id),
  CONSTRAINT fk_ofr_obrigacao FOREIGN KEY (obrigacao_id) REFERENCES obrigacoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_ofr_fonte FOREIGN KEY (fonte_recurso_id) REFERENCES fontes_recurso(id)
) ENGINE=InnoDB;

CREATE TABLE obrigacao_naturezas_despesa (
  obrigacao_id BIGINT UNSIGNED NOT NULL,
  natureza_despesa_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (obrigacao_id, natureza_despesa_id),
  CONSTRAINT fk_ond_obrigacao FOREIGN KEY (obrigacao_id) REFERENCES obrigacoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_ond_natureza FOREIGN KEY (natureza_despesa_id) REFERENCES naturezas_despesa(id)
) ENGINE=InnoDB;

CREATE TABLE tipos_documento_pagamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  exige_numero TINYINT(1) NOT NULL DEFAULT 1,
  ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE documentos_pagamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  obrigacao_id BIGINT UNSIGNED NOT NULL,
  tipo_documento_id BIGINT UNSIGNED NOT NULL,
  numero VARCHAR(80) NOT NULL,
  data_emissao DATE NOT NULL,
  data_atesto DATE NULL,
  data_envio_cooinsp DATE NULL,
  valor_bruto DECIMAL(15,2) NOT NULL,
  valor_liquido DECIMAL(15,2) NOT NULL,
  data_lancamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_doc_obrigacao FOREIGN KEY (obrigacao_id) REFERENCES obrigacoes(id),
  CONSTRAINT fk_doc_tipo FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento_pagamento(id),
  CONSTRAINT fk_doc_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  CONSTRAINT chk_doc_valor_bruto CHECK (valor_bruto > 0),
  CONSTRAINT chk_doc_valor_liquido CHECK (valor_liquido > 0 AND valor_liquido <= valor_bruto),
  UNIQUE KEY uq_documento_obrigacao_tipo_numero (obrigacao_id, tipo_documento_id, numero),
  INDEX idx_documento_obrigacao (obrigacao_id),
  INDEX idx_documento_emissao (data_emissao),
  INDEX idx_documento_lancamento (data_lancamento)
) ENGINE=InnoDB;

CREATE TABLE status_inspecao (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL UNIQUE,
  permite_avancar TINYINT(1) NOT NULL DEFAULT 0,
  encerra_inspecao TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE inspecoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  documento_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status_id BIGINT UNSIGNED NOT NULL,
  data_conclusao DATE NULL,
  responsavel_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_inspecao_documento FOREIGN KEY (documento_id) REFERENCES documentos_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_inspecao_status FOREIGN KEY (status_id) REFERENCES status_inspecao(id),
  CONSTRAINT fk_inspecao_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id),
  INDEX idx_inspecao_status (status_id)
) ENGINE=InnoDB;

CREATE TABLE inspecao_historico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspecao_id BIGINT UNSIGNED NOT NULL,
  status_id BIGINT UNSIGNED NOT NULL,
  observacao VARCHAR(500) NULL,
  usuario_id BIGINT UNSIGNED NULL,
  ocorrido_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ih_inspecao FOREIGN KEY (inspecao_id) REFERENCES inspecoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_ih_status FOREIGN KEY (status_id) REFERENCES status_inspecao(id),
  CONSTRAINT fk_ih_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_ih_inspecao_data (inspecao_id, ocorrido_em)
) ENGINE=InnoDB;

CREATE TABLE parcelas_pagamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  documento_id BIGINT UNSIGNED NOT NULL,
  numero_parcela SMALLINT UNSIGNED NOT NULL,
  numero_empenho VARCHAR(80) NOT NULL,
  natureza_despesa_id BIGINT UNSIGNED NOT NULL,
  exercicio_orcamentario SMALLINT UNSIGNED NOT NULL,
  fonte_recurso_id BIGINT UNSIGNED NOT NULL,
  tipo_recurso_id BIGINT UNSIGNED NOT NULL,
  valor_liquido DECIMAL(15,2) NOT NULL,
  tipo ENUM('IMPOSTO','DARE','INSS','PIS','COFINS','IR','ISS') NOT NULL,
  data_vencimento DATE NULL,
  historico_parcela VARCHAR(255) NULL,
  justificativa_ordem_cronologica VARCHAR(150) NULL,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_parcela_documento FOREIGN KEY (documento_id) REFERENCES documentos_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_parcela_natureza FOREIGN KEY (natureza_despesa_id) REFERENCES naturezas_despesa(id),
  CONSTRAINT fk_parcela_fonte FOREIGN KEY (fonte_recurso_id) REFERENCES fontes_recurso(id),
  CONSTRAINT fk_parcela_tipo_recurso FOREIGN KEY (tipo_recurso_id) REFERENCES tipos_recurso(id),
  CONSTRAINT fk_parcela_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  CONSTRAINT chk_parcela_numero CHECK (numero_parcela > 0),
  CONSTRAINT chk_parcela_exercicio CHECK (exercicio_orcamentario BETWEEN 2000 AND 2100),
  CONSTRAINT chk_parcela_valor CHECK (valor_liquido > 0),
  UNIQUE KEY uq_parcela_documento_numero (documento_id, numero_parcela),
  INDEX idx_parcela_documento (documento_id),
  INDEX idx_parcela_empenho (numero_empenho)
) ENGINE=InnoDB;

CREATE TABLE liquidacoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcela_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status ENUM('AGUARDANDO','LIQUIDADA','CANCELADA','ANULADA') NOT NULL DEFAULT 'AGUARDANDO',
  data_liquidacao DATE NULL,
  usuario_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_liquidacao_parcela FOREIGN KEY (parcela_id) REFERENCES parcelas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_liquidacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT chk_liquidacao_data CHECK (status <> 'LIQUIDADA' OR data_liquidacao IS NOT NULL),
  INDEX idx_liquidacao_status (status)
) ENGINE=InnoDB;

CREATE TABLE cmdf_etapas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcela_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status ENUM('AGUARDANDO','LIQUIDADA','CANCELADA','ANULADA') NOT NULL DEFAULT 'AGUARDANDO',
  data_conclusao DATE NULL,
  usuario_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_cmdf_parcela FOREIGN KEY (parcela_id) REFERENCES parcelas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_cmdf_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT chk_cmdf_data CHECK (status <> 'LIQUIDADA' OR data_conclusao IS NOT NULL),
  INDEX idx_cmdf_status (status)
) ENGINE=InnoDB;

CREATE TABLE pagamentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcela_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status ENUM('AGUARDANDO','PAGO','CANCELADO') NOT NULL DEFAULT 'AGUARDANDO',
  data_pagamento DATE NULL,
  valor_liquido_pago DECIMAL(15,2) NULL,
  historico_pagamento TEXT NULL,
  benner_ap VARCHAR(100) NULL,
  usuario_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_pagamento_parcela FOREIGN KEY (parcela_id) REFERENCES parcelas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_pagamento_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  CONSTRAINT chk_pagamento_pago CHECK (status <> 'PAGO' OR (data_pagamento IS NOT NULL AND valor_liquido_pago IS NOT NULL AND valor_liquido_pago > 0)),
  INDEX idx_pagamento_status (status),
  INDEX idx_pagamento_data (data_pagamento)
) ENGINE=InnoDB;

CREATE TABLE anexos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entidade VARCHAR(50) NOT NULL,
  entidade_id BIGINT UNSIGNED NOT NULL,
  nome_original VARCHAR(255) NOT NULL,
  caminho VARCHAR(500) NOT NULL,
  mime VARCHAR(150) NULL,
  tamanho BIGINT UNSIGNED NULL,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_anexo_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  INDEX idx_anexo_entidade (entidade, entidade_id)
) ENGINE=InnoDB;

CREATE TABLE auditoria (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NULL,
  entidade VARCHAR(80) NOT NULL,
  entidade_id BIGINT UNSIGNED NULL,
  acao VARCHAR(50) NOT NULL,
  dados_anteriores JSON NULL,
  dados_novos JSON NULL,
  ip VARCHAR(45) NULL,
  ocorrido_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_auditoria_entidade (entidade, entidade_id),
  INDEX idx_auditoria_data (ocorrido_em)
) ENGINE=InnoDB;

-- Dados estruturais do sistema. Não são dados de homologação.
INSERT INTO perfis (id,nome,descricao) VALUES
(1,'Administrador','Acesso total'),
(2,'Gestor','Cadastro de obrigações, documentos e programação'),
(3,'Inspetor','Execução da fase de inspeção'),
(4,'Liquidação','Execução da liquidação'),
(5,'CMDF','Execução da etapa CMDF'),
(6,'Consulta','Somente leitura');

INSERT INTO permissoes (id,chave,nome) VALUES
(1,'dashboard.ver','Visualizar dashboard'),
(2,'obrigacao.gerir','Gerenciar obrigações'),
(3,'documento.gerir','Gerenciar documentos de pagamento'),
(4,'inspecao.gerir','Executar inspeção'),
(5,'parcela.gerir','Gerenciar programação/parcelas'),
(6,'liquidacao.gerir','Executar liquidação'),
(7,'cmdf.gerir','Executar CMDF'),
(8,'pagamento.gerir','Registrar pagamento'),
(9,'cadastro.gerir','Gerenciar cadastros auxiliares'),
(10,'usuario.gerir','Gerenciar usuários'),
(11,'perfil.gerir','Gerenciar perfis e permissões');

INSERT INTO perfil_permissoes (perfil_id,permissao_id)
SELECT 1,id FROM permissoes;
INSERT INTO perfil_permissoes (perfil_id,permissao_id) VALUES
(2,1),(2,2),(2,3),(2,5),(2,8),(2,9),
(3,1),(3,4),
(4,1),(4,6),
(5,1),(5,7),
(6,1);

INSERT INTO tipos_recurso (codigo,nome) VALUES
('RRT','RRT'),
('RDO','RDO');

INSERT INTO tipos_obrigacao (nome,exige_numero_ano) VALUES
('Contrato',1),
('Empenho',1),
('Taxa / Tarifa',1),
('Despesas Judiciais',1),
('Diárias',1),
('Despesas de Pessoal',1),
('Imposto',1);

INSERT INTO tipos_documento_pagamento (nome,exige_numero) VALUES
('Nota Fiscal',1),
('Fatura',1),
('Recibo',1),
('Boleto',1),
('Outro',1);

INSERT INTO status_inspecao (nome,permite_avancar,encerra_inspecao,ordem) VALUES
('Aguardando inspeção',0,0,10),
('Inspeção andamento',0,0,20),
('Pendente de complementação',0,0,30),
('Devolvida para o gestor',0,0,40),
('Retornada para inspeção',0,0,50),
('Finalizada',0,1,60),
('Liberada liquidação de imposto',1,1,70),
('Cancelada',0,1,80);
