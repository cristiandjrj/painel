-- ============================================
-- TABELAS DO MÓDULO FINANCEIRO PESSOAL
-- Execute este SQL no phpMyAdmin (XAMPP e Hostinger)
-- Selecione o banco de dados antes de executar
-- ============================================

-- Contas/Cartões Pessoais
CREATE TABLE IF NOT EXISTS contas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('corrente', 'poupanca', 'cartao_credito', 'cartao_debito', 'investimento', 'dinheiro') NOT NULL DEFAULT 'corrente',
    saldo_inicial DECIMAL(12,2) DEFAULT 0.00,
    saldo_atual DECIMAL(12,2) DEFAULT 0.00,
    banco VARCHAR(255) DEFAULT NULL,
    ativa TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Receitas Pessoais
CREATE TABLE IF NOT EXISTS receitas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL COMMENT 'salario, freelance, investimentos, aluguel, venda, retirada_empresa, outros',
    valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    data DATE NOT NULL,
    conta_id INT DEFAULT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_id) REFERENCES contas_pessoais(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Despesas Pessoais
CREATE TABLE IF NOT EXISTS despesas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL COMMENT 'moradia, alimentacao, transporte, saude, educacao, lazer, vestuario, contas, cartao, prestacao, outros',
    valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    data DATE NOT NULL,
    conta_id INT DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_id) REFERENCES contas_pessoais(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contas a Pagar Pessoais
CREATE TABLE IF NOT EXISTS contas_pagar_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    tipo VARCHAR(100) NOT NULL COMMENT 'cartao, prestacao, seguro, aluguel, condominio, luz, agua, internet, telefone, streaming, escola, saude, academia, emprestimo, financiamento, boleto, outros',
    valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    vencimento DATE NOT NULL,
    recorrencia ENUM('unica', 'mensal', 'anual') DEFAULT 'unica',
    observacoes TEXT DEFAULT NULL,
    notificar TINYINT(1) DEFAULT 1,
    paga TINYINT(1) DEFAULT 0,
    data_pagamento DATE DEFAULT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
