-- ============================================
-- SISTEMA MULTI-USUÁRIO - EventProDJ
-- Adiciona suporte a múltiplos usuários com
-- dados independentes e plano de teste 7 dias
-- ============================================

-- 1. TABELA DE USUÁRIOS
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'usuario') DEFAULT 'usuario',
    status ENUM('ativo', 'bloqueado', 'expirado') DEFAULT 'ativo',
    plano ENUM('teste', 'mensal', 'anual', 'vitalicio') DEFAULT 'teste',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATE DEFAULT NULL,
    ultimo_acesso DATETIME DEFAULT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. INSERIR ADMIN PADRÃO (senha: admin123)
INSERT INTO usuarios (id, nome, email, senha, role, status, plano, data_expiracao)
VALUES (1, 'Administrador', 'admin@eventpro.com', '$2y$10$53T4eDDdFOV7nrIW0Znfq.qoy37WuAdW09BcDyE/tlPPIKVJC6Djm', 'admin', 'ativo', 'vitalicio', NULL)
ON DUPLICATE KEY UPDATE nome = nome;

-- 3. ADICIONAR COLUNA user_id EM TODAS AS TABELAS DE DADOS

-- Clientes
ALTER TABLE clientes_eventpro ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE clientes_eventpro ADD INDEX IF NOT EXISTS idx_clientes_user (user_id);

-- Eventos
ALTER TABLE eventos_eventpro ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE eventos_eventpro ADD INDEX IF NOT EXISTS idx_eventos_user (user_id);

-- Serviços
ALTER TABLE servicos_eventpro ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE servicos_eventpro ADD INDEX IF NOT EXISTS idx_servicos_user (user_id);

-- Contas Bancárias
ALTER TABLE contas_bancarias ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE contas_bancarias ADD INDEX IF NOT EXISTS idx_contas_user (user_id);

-- Movimentações Financeiras
ALTER TABLE movimentacoes_financeiras ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE movimentacoes_financeiras ADD INDEX IF NOT EXISTS idx_movimentacoes_user (user_id);

-- Custos da Empresa
ALTER TABLE custos_empresa ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE custos_empresa ADD INDEX IF NOT EXISTS idx_custos_empresa_user (user_id);

-- Custos de Eventos
ALTER TABLE custos_eventos ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE custos_eventos ADD INDEX IF NOT EXISTS idx_custos_eventos_user (user_id);

-- Contas a Pagar
ALTER TABLE contas_pagar_empresa ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE contas_pagar_empresa ADD INDEX IF NOT EXISTS idx_contas_pagar_user (user_id);

-- Categorias Financeiras
ALTER TABLE categorias_financeiras ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE categorias_financeiras ADD INDEX IF NOT EXISTS idx_categorias_fin_user (user_id);

-- Contas Pessoais
ALTER TABLE contas_pessoais ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE contas_pessoais ADD INDEX IF NOT EXISTS idx_contas_pessoais_user (user_id);

-- Receitas Pessoais
ALTER TABLE receitas_pessoais ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE receitas_pessoais ADD INDEX IF NOT EXISTS idx_receitas_pessoais_user (user_id);

-- Despesas Pessoais
ALTER TABLE despesas_pessoais ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE despesas_pessoais ADD INDEX IF NOT EXISTS idx_despesas_pessoais_user (user_id);

-- Configurações do Sistema
ALTER TABLE configuracoes_sistema ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;

-- Configurações EventPro (perfil empresa, logo, assinatura, contrato)
ALTER TABLE configuracoes_eventpro ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE configuracoes_eventpro ADD INDEX IF NOT EXISTS idx_config_eventpro_user (user_id);

-- Categorias de Custos
ALTER TABLE categorias_custos ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE categorias_custos ADD INDEX IF NOT EXISTS idx_categorias_custos_user (user_id);

-- Contas a Pagar Pessoais
ALTER TABLE contas_pagar_pessoais ADD COLUMN IF NOT EXISTS user_id INT DEFAULT 1 AFTER id;
ALTER TABLE contas_pagar_pessoais ADD INDEX IF NOT EXISTS idx_contas_pagar_pessoais_user (user_id);

-- Logs
ALTER TABLE logs_sistema ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER id;

-- 4. MARCAR TODOS OS DADOS EXISTENTES COMO DO ADMIN (user_id = 1)
UPDATE clientes_eventpro SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE eventos_eventpro SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE servicos_eventpro SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE contas_bancarias SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE movimentacoes_financeiras SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE custos_empresa SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE custos_eventos SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE contas_pagar_empresa SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE categorias_custos SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE contas_pagar_pessoais SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE contas_pessoais SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE receitas_pessoais SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE despesas_pessoais SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
UPDATE configuracoes_eventpro SET user_id = 1 WHERE user_id IS NULL OR user_id = 0;
