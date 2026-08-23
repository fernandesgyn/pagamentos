DROP DATABASE IF EXISTS pagamentos;
CREATE DATABASE pagamentos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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
  CONSTRAINT fk_usuarios_perfis FOREIGN KEY (perfil_id) REFERENCES perfis(id)
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
  nome VARCHAR(200) NOT NULL,
  documento VARCHAR(20) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_fornecedor_nome (nome)
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
  numero VARCHAR(30) NOT NULL,
  ano SMALLINT UNSIGNED NOT NULL,
  objeto TEXT NULL,
  valor_global DECIMAL(15,2) NULL,
  data_inicio DATE NULL,
  data_fim DATE NULL,
  sei VARCHAR(50) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_obrigacao_tipo FOREIGN KEY (tipo_obrigacao_id) REFERENCES tipos_obrigacao(id),
  CONSTRAINT fk_obrigacao_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
  CONSTRAINT fk_obrigacao_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  UNIQUE KEY uq_obrigacao_tipo_numero_ano (tipo_obrigacao_id, numero, ano),
  INDEX idx_obrigacao_fornecedor (fornecedor_id),
  INDEX idx_obrigacao_numero_ano (numero, ano)
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
  data_documento DATE NOT NULL,
  valor_bruto DECIMAL(15,2) NOT NULL,
  data_lancamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  data_maxima_liquidacao DATE NULL,
  limite_anotacao DATE NULL,
  data_atesto DATE NULL,
  tipo_servico VARCHAR(150) NULL,
  sei_pagamento VARCHAR(50) NULL,
  observacoes TEXT NULL,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_doc_obrigacao FOREIGN KEY (obrigacao_id) REFERENCES obrigacoes(id),
  CONSTRAINT fk_doc_tipo FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento_pagamento(id),
  CONSTRAINT fk_doc_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  UNIQUE KEY uq_documento_obrigacao_tipo_numero (obrigacao_id, tipo_documento_id, numero),
  INDEX idx_doc_lancamento (data_lancamento),
  INDEX idx_doc_data_maxima (data_maxima_liquidacao)
) ENGINE=InnoDB;

CREATE TABLE status_inspecao (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL UNIQUE,
  permite_avancar TINYINT(1) NOT NULL DEFAULT 0,
  encerra_inspecao TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE inspecoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  documento_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status_id BIGINT UNSIGNED NOT NULL,
  data_envio_cooinsp DATE NULL,
  hora_envio_cooinsp TIME NULL,
  data_devolucao_pendencia DATE NULL,
  motivo_devolucao TEXT NULL,
  data_retorno_pendencia DATE NULL,
  hora_retorno_cooinsp TIME NULL,
  data_conclusao DATE NULL,
  observacoes TEXT NULL,
  responsavel_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_inspecao_documento FOREIGN KEY (documento_id) REFERENCES documentos_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_inspecao_status FOREIGN KEY (status_id) REFERENCES status_inspecao(id),
  CONSTRAINT fk_inspecao_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE inspecao_historico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspecao_id BIGINT UNSIGNED NOT NULL,
  status_id BIGINT UNSIGNED NOT NULL,
  observacao TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  ocorrido_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ih_inspecao FOREIGN KEY (inspecao_id) REFERENCES inspecoes(id) ON DELETE CASCADE,
  CONSTRAINT fk_ih_status FOREIGN KEY (status_id) REFERENCES status_inspecao(id),
  CONSTRAINT fk_ih_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE empenhos_pagamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(30) NOT NULL,
  ano SMALLINT UNSIGNED NOT NULL,
  natureza VARCHAR(30) NULL,
  exercicio SMALLINT UNSIGNED NULL,
  origem_recurso VARCHAR(100) NULL,
  fonte VARCHAR(100) NULL,
  cmdf VARCHAR(100) NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_empenho_numero_ano (numero, ano)
) ENGINE=InnoDB;

CREATE TABLE parcelas_pagamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  documento_id BIGINT UNSIGNED NOT NULL,
  empenho_pagamento_id BIGINT UNSIGNED NOT NULL,
  numero_parcela SMALLINT UNSIGNED NOT NULL,
  valor_total DECIMAL(15,2) NOT NULL,
  historico_liquidacao VARCHAR(119) NULL,
  fila VARCHAR(50) NULL,
  justificativa_ordem_cronologica VARCHAR(150) NULL,
  justificativa_atraso VARCHAR(255) NULL,
  criado_por BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_parcela_documento FOREIGN KEY (documento_id) REFERENCES documentos_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_parcela_empenho FOREIGN KEY (empenho_pagamento_id) REFERENCES empenhos_pagamento(id),
  CONSTRAINT fk_parcela_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id),
  UNIQUE KEY uq_parcela_documento_numero (documento_id, numero_parcela)
) ENGINE=InnoDB;

CREATE TABLE tipos_componente_pagamento (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nome VARCHAR(100) NOT NULL,
  categoria ENUM('PRINCIPAL','IMPOSTO','DARE','OUTRO') NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem SMALLINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE parcela_componentes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcela_id BIGINT UNSIGNED NOT NULL,
  tipo_componente_id BIGINT UNSIGNED NOT NULL,
  valor DECIMAL(15,2) NOT NULL,
  observacao VARCHAR(255) NULL,
  CONSTRAINT fk_pc_parcela FOREIGN KEY (parcela_id) REFERENCES parcelas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_pc_tipo FOREIGN KEY (tipo_componente_id) REFERENCES tipos_componente_pagamento(id)
) ENGINE=InnoDB;

CREATE TABLE liquidacoes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcela_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status ENUM('AGUARDANDO','CONCLUIDA','ANULADA') NOT NULL DEFAULT 'AGUARDANDO',
  data_liquidacao DATE NULL,
  justificativa_anulacao VARCHAR(150) NULL,
  usuario_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_liquidacao_parcela FOREIGN KEY (parcela_id) REFERENCES parcelas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_liquidacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE cmdf_etapas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parcela_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status ENUM('AGUARDANDO','CONCLUIDA','DEVOLVIDA') NOT NULL DEFAULT 'AGUARDANDO',
  data_envio_seinfra DATE NULL,
  data_despacho_seinfra DATE NULL,
  data_envio_economia DATE NULL,
  data_atendimento_economia DATE NULL,
  data_conclusao DATE NULL,
  observacoes TEXT NULL,
  usuario_id BIGINT UNSIGNED NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL,
  CONSTRAINT fk_cmdf_parcela FOREIGN KEY (parcela_id) REFERENCES parcelas_pagamento(id) ON DELETE CASCADE,
  CONSTRAINT fk_cmdf_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
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
  CONSTRAINT fk_pagamento_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
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
(10,'usuario.gerir','Gerenciar usuários e perfis');

INSERT INTO perfil_permissoes SELECT 1,id FROM permissoes;
INSERT INTO perfil_permissoes VALUES
(2,1),(2,2),(2,3),(2,5),(2,8),(2,9),
(3,1),(3,4),
(4,1),(4,6),
(5,1),(5,7),
(6,1);

INSERT INTO tipos_obrigacao (nome,exige_numero_ano) VALUES
('Contrato',1),('Empenho que substitui contrato',1),('Carta-Contrato',1),('Ordem de Fornecimento/Serviço',1),('Outro',1);

INSERT INTO tipos_documento_pagamento (nome,exige_numero) VALUES
('Nota Fiscal',1),('Fatura',1),('Recibo',1),('Boleto',1),('Outro',1);

INSERT INTO status_inspecao (nome,permite_avancar,encerra_inspecao,ordem) VALUES
('Aguardando inspeção',0,0,10),
('Em andamento',0,0,20),
('Pendente de complementação',0,0,30),
('Devolvida para o gestor',0,0,40),
('Retornada para inspeção',0,0,50),
('Concluída',1,1,60),
('Concluída com ressalvas',1,1,70),
('Cancelada',0,1,80);

INSERT INTO tipos_componente_pagamento (codigo,nome,categoria,ordem) VALUES
('LIQUIDO','Valor líquido/principal','PRINCIPAL',10),
('INSS','INSS','IMPOSTO',20),
('ISS','ISS','IMPOSTO',30),
('PIS_COFINS','PIS/COFINS','IMPOSTO',40),
('IR','IR','IMPOSTO',50),
('CSLL','CSLL','IMPOSTO',60),
('DARE','DARE','DARE',70),
('OUTRO','Outro componente','OUTRO',80);
