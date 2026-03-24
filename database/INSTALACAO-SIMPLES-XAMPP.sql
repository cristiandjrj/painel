-- ============================================
-- INSTALAÇÃO SIMPLIFICADA - EVENTPRODJ
-- SQL Básico sem Funções/Triggers complexos
-- Para testes rápidos no XAMPP
-- ============================================

DROP DATABASE IF EXISTS sistema_eventos;
CREATE DATABASE sistema_eventos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_eventos;

-- TABELA 1: Configurações
CREATE TABLE configuracoes_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(20) DEFAULT 'text',
    descricao TEXT,
    categoria VARCHAR(50),
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO configuracoes_sistema (chave, valor, tipo, descricao, categoria) VALUES
('nome_empresa', 'EventProDJ', 'text', 'Nome da empresa', 'empresa'),
('cnpj', '', 'text', 'CNPJ', 'empresa'),
('telefone', '', 'text', 'Telefone', 'empresa'),
('email', '', 'text', 'E-mail', 'empresa');

-- TABELA 2: Clientes
CREATE TABLE clientes_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(255),
    cpf VARCHAR(14),
    data_aniversario DATE,
    origem VARCHAR(50),
    endereco VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO clientes_eventpro (nome, telefone, email, origem) VALUES
('Cliente Exemplo', '(11) 99999-9999', 'cliente@exemplo.com', 'indicacao');

-- TABELA 3: Eventos
CREATE TABLE eventos_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    data_evento DATE NOT NULL,
    hora_inicio TIME,
    hora_fim TIME,
    duracao VARCHAR(20),
    cep VARCHAR(10),
    endereco VARCHAR(255),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    valor_total DECIMAL(10,2) DEFAULT 0,
    desconto DECIMAL(10,2) DEFAULT 0,
    desconto_json JSON DEFAULT NULL,
    total_pago DECIMAL(10,2) DEFAULT 0,
    status ENUM('pendente', 'confirmado', 'andamento', 'concluido', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_validade DATE,
    FOREIGN KEY (cliente_id) REFERENCES clientes_eventpro(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 4: Serviços
CREATE TABLE servicos_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    valor_padrao DECIMAL(10,2) DEFAULT 0,
    categoria VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO servicos_eventpro (nome, descricao, valor_padrao, categoria) VALUES
('DJ Completo', 'Serviço completo com equipamentos', 1500.00, 'som'),
('Iluminação', 'Kit de iluminação profissional', 800.00, 'luz'),
('Telão', 'Telão para projeção', 500.00, 'visual');

-- TABELA 5: Evento_Serviços
CREATE TABLE evento_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    valor_unitario DECIMAL(10,2) DEFAULT 0,
    valor_total DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos_eventpro(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 6: Contas Bancárias
CREATE TABLE contas_bancarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    banco VARCHAR(100),
    agencia VARCHAR(20),
    conta VARCHAR(20),
    tipo ENUM('corrente', 'poupanca', 'investimento', 'outros') DEFAULT 'corrente',
    saldo_inicial DECIMAL(10,2) DEFAULT 0,
    saldo_atual DECIMAL(10,2) DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO contas_bancarias (nome, banco, tipo, saldo_inicial, saldo_atual) VALUES
('Conta Principal', 'Banco Exemplo', 'corrente', 1000.00, 1000.00);

-- TABELA 7: Categorias Financeiras
CREATE TABLE categorias_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    cor VARCHAR(7),
    icone VARCHAR(50),
    ativo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias_financeiras (nome, tipo, cor, icone) VALUES
('Serviços', 'receita', '#06D6A0', '💰'),
('Eventos', 'receita', '#FFD23F', '🎉'),
('Fornecedores', 'despesa', '#EF476F', '📦'),
('Equipamentos', 'despesa', '#004E89', '🎧'),
('Marketing', 'despesa', '#FF6B35', '📢');

-- TABELA 8: Movimentações Financeiras
CREATE TABLE movimentacoes_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_id INT,
    tipo ENUM('receita', 'despesa') NOT NULL,
    tipo_movimentacao VARCHAR(50) DEFAULT 'normal',
    categoria VARCHAR(100),
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_movimentacao DATE NOT NULL,
    forma_pagamento VARCHAR(50),
    comprovante VARCHAR(255),
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 9: Custos da Empresa
CREATE TABLE custos_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    tipo ENUM('fixo', 'variavel') DEFAULT 'variavel',
    valor DECIMAL(10,2) NOT NULL,
    data_custo DATE NOT NULL,
    recorrente BOOLEAN DEFAULT FALSE,
    dia_vencimento INT,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 10: Custos de Eventos
CREATE TABLE custos_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL,
    gera_despesa BOOLEAN DEFAULT TRUE,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 11: Contas a Pagar
CREATE TABLE contas_pagar_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    status ENUM('pendente', 'paga', 'vencida') DEFAULT 'pendente',
    categoria VARCHAR(100),
    fornecedor VARCHAR(255),
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 12: Contas Pessoais
CREATE TABLE contas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('conta_corrente', 'conta_poupanca', 'carteira', 'investimento', 'outros') DEFAULT 'conta_corrente',
    banco VARCHAR(100),
    saldo_inicial DECIMAL(10,2) DEFAULT 0,
    saldo_atual DECIMAL(10,2) DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 13: Categorias Pessoais
CREATE TABLE categorias_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    cor VARCHAR(7),
    icone VARCHAR(50),
    ativo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias_pessoais (nome, tipo, cor, icone) VALUES
('Salário', 'receita', '#06D6A0', '💰'),
('Freelance', 'receita', '#FFD23F', '💼'),
('Moradia', 'despesa', '#EF476F', '🏠'),
('Alimentação', 'despesa', '#FFD23F', '🍔'),
('Transporte', 'despesa', '#FF6B35', '🚗');

-- TABELA 14: Receitas Pessoais
CREATE TABLE receitas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_id INT,
    categoria VARCHAR(100),
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL,
    recorrente BOOLEAN DEFAULT FALSE,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_id) REFERENCES contas_pessoais(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 15: Despesas Pessoais
CREATE TABLE despesas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_id INT,
    categoria VARCHAR(100),
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL,
    tipo_despesa VARCHAR(50) DEFAULT 'normal',
    recorrente BOOLEAN DEFAULT FALSE,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_id) REFERENCES contas_pessoais(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABELA 16: Logs do Sistema
CREATE TABLE logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    mensagem TEXT NOT NULL,
    detalhes JSON DEFAULT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- VIEWS BÁSICAS
CREATE OR REPLACE VIEW vw_eventos_completos AS
SELECT 
    e.*,
    c.nome as cliente_nome,
    c.telefone as cliente_telefone,
    COALESCE(e.desconto, 0) as valor_desconto,
    (e.valor_total - COALESCE(e.desconto, 0)) as valor_liquido,
    (e.valor_total - COALESCE(e.desconto, 0) - COALESCE(e.total_pago, 0)) as saldo_devedor
FROM eventos_eventpro e
LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id;

CREATE OR REPLACE VIEW vw_movimentacoes_completas AS
SELECT 
    m.*,
    cb.nome as conta_nome,
    cb.banco as conta_banco
FROM movimentacoes_financeiras m
LEFT JOIN contas_bancarias cb ON m.conta_id = cb.id;

-- MENSAGEM FINAL
SELECT '✅ Banco de dados criado com sucesso!' as status;
SELECT '📊 16 tabelas criadas' as info;
SELECT '🎯 Dados de exemplo incluídos' as info;
SELECT '🚀 Sistema pronto para uso!' as mensagem;
