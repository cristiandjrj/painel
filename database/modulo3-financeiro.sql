-- ============================================
-- MÓDULO 3: FINANCEIRO COMPLETO
-- EventProDJ - Sistema de Gestão Financeira
-- ============================================

USE sistema_eventos;

-- ============================================
-- TABELA DE BANCOS/CONTAS
-- ============================================
CREATE TABLE IF NOT EXISTS contas_bancarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados da Conta
    nome VARCHAR(255) NOT NULL, -- "Banco do Brasil - CC", "Nubank", "Caixa Físico"
    tipo ENUM('conta_corrente', 'conta_poupanca', 'carteira', 'outros') DEFAULT 'conta_corrente',
    banco VARCHAR(100), -- "Banco do Brasil", "Nubank", "Caixa", etc
    agencia VARCHAR(20),
    conta VARCHAR(50),
    
    -- Saldo
    saldo_inicial DECIMAL(10,2) DEFAULT 0.00,
    saldo_atual DECIMAL(10,2) DEFAULT 0.00,
    
    -- Configurações
    cor VARCHAR(7) DEFAULT '#FF6B35', -- Cor para identificação visual
    icone VARCHAR(50) DEFAULT 'bank', -- Ícone da conta
    ativo BOOLEAN DEFAULT TRUE,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE CATEGORIAS FINANCEIRAS
-- ============================================
CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados da Categoria
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    cor VARCHAR(7) DEFAULT '#FF6B35',
    icone VARCHAR(50) DEFAULT 'tag',
    
    -- Controle
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE MOVIMENTAÇÕES FINANCEIRAS
-- ============================================
CREATE TABLE IF NOT EXISTS movimentacoes_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Relacionamentos
    conta_id INT NOT NULL,
    categoria_id INT,
    cliente_id INT, -- Se for pagamento de cliente
    evento_id INT, -- Se for relacionado a evento
    
    -- Dados da Movimentação
    tipo ENUM('receita', 'despesa', 'transferencia') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_movimentacao DATE NOT NULL,
    
    -- Transferência (se tipo = transferencia)
    conta_destino_id INT, -- Conta de destino da transferência
    
    -- Status
    status ENUM('pendente', 'concluido', 'cancelado') DEFAULT 'concluido',
    recorrente BOOLEAN DEFAULT FALSE,
    
    -- Observações
    observacoes TEXT,
    comprovante VARCHAR(255), -- Caminho do arquivo de comprovante
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes_eventpro(id) ON DELETE SET NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_destino_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,
    
    INDEX idx_conta (conta_id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_evento (evento_id),
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_movimentacao),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE CONTAS A PAGAR
-- ============================================
CREATE TABLE IF NOT EXISTS contas_pagar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados da Conta
    descricao VARCHAR(255) NOT NULL,
    categoria_id INT,
    fornecedor VARCHAR(255),
    valor DECIMAL(10,2) NOT NULL,
    
    -- Datas
    data_emissao DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    
    -- Status
    status ENUM('pendente', 'pago', 'atrasado', 'cancelado') DEFAULT 'pendente',
    
    -- Pagamento
    conta_id INT, -- Conta que será usada para pagar
    valor_pago DECIMAL(10,2) DEFAULT 0.00,
    
    -- Recorrência
    recorrente BOOLEAN DEFAULT FALSE,
    frequencia ENUM('mensal', 'bimestral', 'trimestral', 'semestral', 'anual'),
    
    -- Observações
    observacoes TEXT,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento),
    INDEX idx_categoria (categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE CONTAS A RECEBER
-- ============================================
CREATE TABLE IF NOT EXISTS contas_receber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados da Conta
    descricao VARCHAR(255) NOT NULL,
    categoria_id INT,
    cliente_id INT,
    evento_id INT,
    valor DECIMAL(10,2) NOT NULL,
    
    -- Datas
    data_emissao DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    data_recebimento DATE,
    
    -- Status
    status ENUM('pendente', 'recebido', 'atrasado', 'cancelado') DEFAULT 'pendente',
    
    -- Recebimento
    conta_id INT, -- Conta que receberá
    valor_recebido DECIMAL(10,2) DEFAULT 0.00,
    
    -- Observações
    observacoes TEXT,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes_eventpro(id) ON DELETE SET NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento),
    INDEX idx_cliente (cliente_id),
    INDEX idx_evento (evento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VIEWS - FINANCEIRO
-- ============================================

-- View: Resumo Financeiro Geral
CREATE OR REPLACE VIEW vw_resumo_financeiro AS
SELECT 
    -- Saldo total das contas
    (SELECT COALESCE(SUM(saldo_atual), 0) FROM contas_bancarias WHERE ativo = TRUE) as saldo_total_contas,
    
    -- Receitas do mês
    (SELECT COALESCE(SUM(valor), 0) 
     FROM movimentacoes_financeiras 
     WHERE tipo = 'receita' 
     AND MONTH(data_movimentacao) = MONTH(CURDATE())
     AND YEAR(data_movimentacao) = YEAR(CURDATE())
     AND status = 'concluido') as receitas_mes,
    
    -- Despesas do mês
    (SELECT COALESCE(SUM(valor), 0) 
     FROM movimentacoes_financeiras 
     WHERE tipo = 'despesa' 
     AND MONTH(data_movimentacao) = MONTH(CURDATE())
     AND YEAR(data_movimentacao) = YEAR(CURDATE())
     AND status = 'concluido') as despesas_mes,
    
    -- Lucro do mês
    (SELECT COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0)
     FROM movimentacoes_financeiras 
     WHERE tipo IN ('receita', 'despesa')
     AND MONTH(data_movimentacao) = MONTH(CURDATE())
     AND YEAR(data_movimentacao) = YEAR(CURDATE())
     AND status = 'concluido') as lucro_mes,
    
    -- Contas a pagar (pendentes)
    (SELECT COALESCE(SUM(valor), 0) FROM contas_pagar WHERE status = 'pendente') as contas_pagar_pendentes,
    
    -- Contas a receber (pendentes)
    (SELECT COALESCE(SUM(valor), 0) FROM contas_receber WHERE status = 'pendente') as contas_receber_pendentes,
    
    -- Contas atrasadas a pagar
    (SELECT COUNT(*) FROM contas_pagar 
     WHERE status = 'pendente' 
     AND data_vencimento < CURDATE()) as contas_pagar_atrasadas,
    
    -- Contas atrasadas a receber
    (SELECT COUNT(*) FROM contas_receber 
     WHERE status = 'pendente' 
     AND data_vencimento < CURDATE()) as contas_receber_atrasadas;

-- View: Movimentações Completas
CREATE OR REPLACE VIEW vw_movimentacoes_completas AS
SELECT 
    m.*,
    c.nome as conta_nome,
    c.banco as conta_banco,
    cat.nome as categoria_nome,
    cat.tipo as categoria_tipo,
    cat.cor as categoria_cor,
    cli.nome as cliente_nome,
    e.tipo as evento_tipo,
    cd.nome as conta_destino_nome
FROM movimentacoes_financeiras m
LEFT JOIN contas_bancarias c ON m.conta_id = c.id
LEFT JOIN categorias_financeiras cat ON m.categoria_id = cat.id
LEFT JOIN clientes_eventpro cli ON m.cliente_id = cli.id
LEFT JOIN eventos_eventpro e ON m.evento_id = e.id
LEFT JOIN contas_bancarias cd ON m.conta_destino_id = cd.id
ORDER BY m.data_movimentacao DESC, m.id DESC;

-- View: Fluxo de Caixa Mensal
CREATE OR REPLACE VIEW vw_fluxo_caixa_mensal AS
SELECT 
    YEAR(data_movimentacao) as ano,
    MONTH(data_movimentacao) as mes,
    DATE_FORMAT(data_movimentacao, '%Y-%m') as mes_ano,
    
    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as receitas,
    COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as despesas,
    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0) as saldo
    
FROM movimentacoes_financeiras
WHERE status = 'concluido'
  AND tipo IN ('receita', 'despesa')
GROUP BY YEAR(data_movimentacao), MONTH(data_movimentacao)
ORDER BY ano DESC, mes DESC;

-- View: Top Categorias (Receitas)
CREATE OR REPLACE VIEW vw_top_categorias_receitas AS
SELECT 
    c.id,
    c.nome,
    c.cor,
    COUNT(m.id) as total_movimentacoes,
    COALESCE(SUM(m.valor), 0) as total_valor
FROM categorias_financeiras c
LEFT JOIN movimentacoes_financeiras m ON c.id = m.categoria_id AND m.tipo = 'receita' AND m.status = 'concluido'
WHERE c.tipo = 'receita' AND c.ativo = TRUE
GROUP BY c.id, c.nome, c.cor
ORDER BY total_valor DESC
LIMIT 10;

-- View: Top Categorias (Despesas)
CREATE OR REPLACE VIEW vw_top_categorias_despesas AS
SELECT 
    c.id,
    c.nome,
    c.cor,
    COUNT(m.id) as total_movimentacoes,
    COALESCE(SUM(m.valor), 0) as total_valor
FROM categorias_financeiras c
LEFT JOIN movimentacoes_financeiras m ON c.id = m.categoria_id AND m.tipo = 'despesa' AND m.status = 'concluido'
WHERE c.tipo = 'despesa' AND c.ativo = TRUE
GROUP BY c.id, c.nome, c.cor
ORDER BY total_valor DESC
LIMIT 10;

-- ============================================
-- TRIGGERS - ATUALIZAR SALDOS
-- ============================================

DELIMITER //

-- Trigger: Atualizar saldo ao adicionar movimentação
CREATE TRIGGER trg_after_insert_movimentacao
AFTER INSERT ON movimentacoes_financeiras
FOR EACH ROW
BEGIN
    IF NEW.status = 'concluido' THEN
        -- Atualizar conta de origem
        IF NEW.tipo = 'receita' THEN
            UPDATE contas_bancarias SET saldo_atual = saldo_atual + NEW.valor WHERE id = NEW.conta_id;
        ELSEIF NEW.tipo = 'despesa' THEN
            UPDATE contas_bancarias SET saldo_atual = saldo_atual - NEW.valor WHERE id = NEW.conta_id;
        ELSEIF NEW.tipo = 'transferencia' THEN
            UPDATE contas_bancarias SET saldo_atual = saldo_atual - NEW.valor WHERE id = NEW.conta_id;
            UPDATE contas_bancarias SET saldo_atual = saldo_atual + NEW.valor WHERE id = NEW.conta_destino_id;
        END IF;
    END IF;
END//

-- Trigger: Atualizar saldo ao deletar movimentação
CREATE TRIGGER trg_after_delete_movimentacao
AFTER DELETE ON movimentacoes_financeiras
FOR EACH ROW
BEGIN
    IF OLD.status = 'concluido' THEN
        -- Reverter movimento na conta
        IF OLD.tipo = 'receita' THEN
            UPDATE contas_bancarias SET saldo_atual = saldo_atual - OLD.valor WHERE id = OLD.conta_id;
        ELSEIF OLD.tipo = 'despesa' THEN
            UPDATE contas_bancarias SET saldo_atual = saldo_atual + OLD.valor WHERE id = OLD.conta_id;
        ELSEIF OLD.tipo = 'transferencia' THEN
            UPDATE contas_bancarias SET saldo_atual = saldo_atual + OLD.valor WHERE id = OLD.conta_id;
            UPDATE contas_bancarias SET saldo_atual = saldo_atual - OLD.valor WHERE id = OLD.conta_destino_id;
        END IF;
    END IF;
END//

DELIMITER ;

-- ============================================
-- DADOS DE EXEMPLO
-- ============================================

-- Contas Bancárias
INSERT INTO contas_bancarias (nome, tipo, banco, saldo_inicial, saldo_atual, cor, icone) VALUES
('Banco do Brasil - CC', 'conta_corrente', 'Banco do Brasil', 5000.00, 5000.00, '#FDB632', 'university'),
('Nubank', 'conta_corrente', 'Nubank', 2000.00, 2000.00, '#8A05BE', 'credit-card'),
('Caixa Físico', 'carteira', NULL, 500.00, 500.00, '#06D6A0', 'wallet');

-- Categorias de Receita
INSERT INTO categorias_financeiras (nome, tipo, cor, icone) VALUES
('Eventos', 'receita', '#06D6A0', 'calendar-check'),
('Serviços', 'receita', '#06D6A0', 'briefcase'),
('Sinal/Entrada', 'receita', '#FFD23F', 'hand-holding-usd'),
('Outros', 'receita', '#A0AEC0', 'plus-circle');

-- Categorias de Despesa
INSERT INTO categorias_financeiras (nome, tipo, cor, icone) VALUES
('Ajudantes', 'despesa', '#EF476F', 'users'),
('Transporte/Combustível', 'despesa', '#EF476F', 'car'),
('Equipamentos', 'despesa', '#EF476F', 'tools'),
('Manutenção', 'despesa', '#EF476F', 'wrench'),
('Marketing', 'despesa', '#EF476F', 'bullhorn'),
('Alimentação', 'despesa', '#EF476F', 'utensils'),
('Outros', 'despesa', '#EF476F', 'minus-circle');

-- ============================================
-- VERIFICAÇÃO
-- ============================================

SELECT '✅ Módulo Financeiro instalado com sucesso!' as status;
SELECT COUNT(*) as total_contas FROM contas_bancarias;
SELECT COUNT(*) as total_categorias FROM categorias_financeiras;
SELECT 'Tabelas criadas: 5' as info;
SELECT 'Views criadas: 5' as info;
SELECT 'Triggers criados: 2' as info;
