-- ============================================================
-- PORTAL ANS (HostGator) - CRIAÇÃO DE TABELAS + ÍNDICES
-- Banco: e9801218_ans_acionistas
-- Charset recomendado: utf8mb4
-- Engine recomendado: InnoDB
-- ============================================================

-- 0) Seleciona o banco
USE e9801218_ans_acionistas;

-- (Opcional) padroniza charset da sessão
SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- ============================================================
-- 1) usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO',
  email_verificado_em DATETIME NULL,
  ultimo_login_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2) cadastro (1:1 com usuarios via usuario_id)
-- ============================================================
CREATE TABLE IF NOT EXISTS cadastro (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id BIGINT UNSIGNED NOT NULL,

  nome_completo VARCHAR(190) NOT NULL,
  cpf CHAR(11) NOT NULL,                     -- recomendado armazenar só dígitos
  possui_cin TINYINT(1) NOT NULL DEFAULT 0,
  rg VARCHAR(30) NULL,                       -- NULL se possui_cin=1
  telefone VARCHAR(20) NOT NULL,

  cep CHAR(8) NOT NULL,                      -- recomendado só dígitos
  rua VARCHAR(190) NOT NULL,
  numero VARCHAR(20) NOT NULL,
  complemento VARCHAR(120) NULL,
  bairro VARCHAR(120) NOT NULL,
  cidade VARCHAR(120) NOT NULL,
  uf CHAR(2) NOT NULL,

  possui_procurador TINYINT(1) NOT NULL DEFAULT 0,
  sou_procurador TINYINT(1) NOT NULL DEFAULT 0,

  termo_aceite TINYINT(1) NOT NULL DEFAULT 0,
  termo_versao VARCHAR(20) NOT NULL DEFAULT 'v1',
  termo_aceite_em DATETIME NULL,

  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  UNIQUE KEY uq_cadastro_usuario_id (usuario_id),
  UNIQUE KEY uq_cadastro_cpf (cpf),
  KEY idx_cadastro_uf (uf),

  CONSTRAINT fk_cadastro_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3) usuario_vinculos (procurador <-> representado)
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario_vinculos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id BIGINT UNSIGNED NOT NULL,

  representado_nome VARCHAR(190) NOT NULL,
  representado_email VARCHAR(190) NOT NULL,
  representado_cpf CHAR(11) NULL,
  tipo_procuracao VARCHAR(40) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_uv_usuario_id (usuario_id),
  KEY idx_uv_representado_email (representado_email),
  KEY idx_uv_tipo (tipo_procuracao),
  KEY idx_uv_status (status),

  UNIQUE KEY uq_uv_evitar_duplicidade (usuario_id, representado_email, tipo_procuracao),

  CONSTRAINT fk_uv_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4) usuario_senha_eventos (auditoria troca de senha)
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario_senha_eventos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id BIGINT UNSIGNED NOT NULL,

  evento VARCHAR(40) NOT NULL,         -- ex: "RESET_SOLICITADO", "SENHA_ALTERADA"
  origem VARCHAR(30) NOT NULL,         -- ex: "site", "admin", "api"
  ip VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255) NULL,

  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_use_usuario_criado (usuario_id, criado_em),
  KEY idx_use_evento (evento),
  KEY idx_use_origem (origem),

  CONSTRAINT fk_use_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5) usuario_reset_senha (token + OTP 4 dígitos)
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario_reset_senha (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id BIGINT UNSIGNED NOT NULL,

  email VARCHAR(190) NOT NULL,

  token_hash CHAR(64) NOT NULL,         -- sha256 hex (recomendado)
  otp_hash   CHAR(64) NOT NULL,         -- sha256 hex (recomendado)

  tentativas INT UNSIGNED NOT NULL DEFAULT 0,
  expira_em DATETIME NOT NULL,
  usado_em DATETIME NULL,

  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uq_urs_token_hash (token_hash),
  KEY idx_urs_usuario_id (usuario_id),
  KEY idx_urs_email (email),
  KEY idx_urs_expira (expira_em),
  KEY idx_urs_usado (usado_em),

  CONSTRAINT fk_urs_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6) log_navegacao (auditoria leve)
-- ============================================================
CREATE TABLE IF NOT EXISTS log_navegacao (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id BIGINT UNSIGNED NULL,

  ip VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255) NULL,

  pagina VARCHAR(190) NOT NULL,
  referer VARCHAR(190) NULL,
  metodo VARCHAR(10) NOT NULL DEFAULT 'GET',

  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_ln_usuario_criado (usuario_id, criado_em),
  KEY idx_ln_criado (criado_em),
  KEY idx_ln_pagina (pagina),
  KEY idx_ln_ip (ip),

  CONSTRAINT fk_ln_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7) agenda_reunioes (agendamento 15 min)
-- ============================================================
CREATE TABLE IF NOT EXISTS agenda_reunioes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id BIGINT UNSIGNED NULL,

  nome VARCHAR(190) NOT NULL,
  email VARCHAR(190) NOT NULL,
  telefone VARCHAR(20) NOT NULL,

  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reuniao_em DATETIME NOT NULL,
  duracao_min SMALLINT UNSIGNED NOT NULL DEFAULT 15,

  status VARCHAR(20) NOT NULL DEFAULT 'PENDENTE', -- PENDENTE/CONFIRMADA/CANCELADA/CONCLUIDA
  observacao VARCHAR(255) NULL,

  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,

  PRIMARY KEY (id),
  KEY idx_ar_reuniao_em (reuniao_em),
  KEY idx_ar_status (status),
  KEY idx_ar_email (email),
  KEY idx_ar_usuario_id (usuario_id),

  CONSTRAINT fk_ar_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8) agenda_regras (opcional)
-- ============================================================
CREATE TABLE IF NOT EXISTS agenda_regras (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  dias_semana VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5', -- seg=1 ... dom=7
  hora_inicio TIME NOT NULL DEFAULT '08:00:00',
  hora_fim TIME NOT NULL DEFAULT '09:00:00',
  slot_min SMALLINT UNSIGNED NOT NULL DEFAULT 15,

  ativo TINYINT(1) NOT NULL DEFAULT 1,

  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  KEY idx_agenda_regras_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIM
-- ============================================================
