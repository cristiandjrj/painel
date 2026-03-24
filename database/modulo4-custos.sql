-- ============================================
-- MÓDULO DE CUSTOS DA EMPRESA
-- EventProDJ - Gestão de Custos Fixos e Variáveis
-- ============================================

USE sistema_eventos;

-- ============================================
-- TABELA DE CATEGORIAS DE CUSTOS
-- ============================================
CREATE TABLE IF NOT EXISTS categorias_custos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados da Categoria
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('fixo', 'variavel') NOT NULL,
    cor VARCHAR(7) DEFAULT '#EF476F',
    icone VARCHAR(50) DEFAULT 'tag',
    
    -- Controle
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABELA DE CUSTOS DA EMPRESA
-- ============================================
CREATE TABLE IF NOT EXISTS custos_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados do Custo
    descricao VARCHAR(255) NOT NULL,
    categoria_id INT,
    tipo ENUM('fixo', 'variavel') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    
    -- Datas
    data_custo DATE NOT NULL,
    data_vencimento DATE,
    
    -- Recorrência
    recorrente BOOLEAN DEFAULT FALSE,
    frequencia ENUM('mensal', 'bimestral', 'trimestral', 'semestral', 'anual'),
    dia_vencimento INT, -- Dia do mês para vencimento (ex: 10 para todo dia 10)
    
    -- Status
    status ENUM('pendente', 'pago', 'atrasado') DEFAULT 'pendente',
    
    -- Pagamento
    conta_id INT, -- Conta usada para pagar
    data_pagamento DATE,
    valor_pago DECIMAL(10,2),
    
    -- Observações
    observacoes TEXT,
    anexo VARCHAR(255),
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (categoria_id) REFERENCES categorias_custos(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,
    
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_data_custo (data_custo),
    INDEX idx_recorrente (recorrente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VIEWS - CUSTOS
-- ============================================

-- View: Resumo de Custos da Empresa
CREATE OR REPLACE VIEW vw_resumo_custos_empresa AS
SELECT 
    -- Custos Fixos
    (SELECT COALESCE(SUM(valor), 0) 
     FROM custos_empresa 
     WHERE tipo = 'fixo' 
     AND MONTH(data_custo) = MONTH(CURDATE())
     AND YEAR(data_custo) = YEAR(CURDATE())
     AND status = 'pago') as custos_fixos_mes,
    
    -- Custos Variáveis
    (SELECT COALESCE(SUM(valor), 0) 
     FROM custos_empresa 
     WHERE tipo = 'variavel' 
     AND MONTH(data_custo) = MONTH(CURDATE())
     AND YEAR(data_custo) = YEAR(CURDATE())
     AND status = 'pago') as custos_variaveis_mes,
    
    -- Total do mês
    (SELECT COALESCE(SUM(valor), 0) 
     FROM custos_empresa 
     WHERE MONTH(data_custo) = MONTH(CURDATE())
     AND YEAR(data_custo) = YEAR(CURDATE())
     AND status = 'pago') as total_custos_mes,
    
    -- Custos pendentes
    (SELECT COALESCE(SUM(valor), 0) 
     FROM custos_empresa 
     WHERE status = 'pendente') as custos_pendentes,
    
    -- Custos atrasados
    (SELECT COUNT(*) 
     FROM custos_empresa 
     WHERE status = 'pendente' 
     AND data_vencimento < CURDATE()) as custos_atrasados,
    
    -- Total de custos fixos recorrentes
    (SELECT COALESCE(SUM(valor), 0) 
     FROM custos_empresa 
     WHERE tipo = 'fixo' 
     AND recorrente = TRUE 
     AND ativo = TRUE) as total_custos_fixos_recorrentes;

-- View: Custos Completos
CREATE OR REPLACE VIEW vw_custos_empresa_completos AS
SELECT 
    ce.*,
    cc.nome as categoria_nome,
    cc.cor as categoria_cor,
    cb.nome as conta_nome,
    CASE 
        WHEN ce.status = 'pendente' AND ce.data_vencimento < CURDATE() THEN 'atrasado'
        ELSE ce.status
    END as status_real,
    DATEDIFF(ce.data_vencimento, CURDATE()) as dias_ate_vencimento
FROM custos_empresa ce
LEFT JOIN categorias_custos cc ON ce.categoria_id = cc.id
LEFT JOIN contas_bancarias cb ON ce.conta_id = cb.id
ORDER BY ce.data_custo DESC, ce.id DESC;

-- View: Custos Mensais (últimos 12 meses)
CREATE OR REPLACE VIEW vw_custos_mensais AS
SELECT 
    YEAR(data_custo) as ano,
    MONTH(data_custo) as mes,
    DATE_FORMAT(data_custo, '%Y-%m') as mes_ano,
    tipo,
    COALESCE(SUM(valor), 0) as total
FROM custos_empresa
WHERE status = 'pago'
GROUP BY YEAR(data_custo), MONTH(data_custo), tipo
ORDER BY ano DESC, mes DESC;

-- View: Top Categorias de Custos
CREATE OR REPLACE VIEW vw_top_categorias_custos AS
SELECT 
    cc.id,
    cc.nome,
    cc.tipo,
    cc.cor,
    COUNT(ce.id) as total_custos,
    COALESCE(SUM(ce.valor), 0) as valor_total
FROM categorias_custos cc
LEFT JOIN custos_empresa ce ON cc.id = ce.categoria_id AND ce.status = 'pago'
WHERE cc.ativo = TRUE
GROUP BY cc.id, cc.nome, cc.tipo, cc.cor
ORDER BY valor_total DESC
LIMIT 10;

-- ============================================
-- TRIGGERS - CUSTOS
-- ============================================

DELIMITER //

-- Trigger: Criar movimentação financeira ao pagar custo
CREATE TRIGGER trg_after_pagar_custo_empresa
AFTER UPDATE ON custos_empresa
FOR EACH ROW
BEGIN
    IF NEW.status = 'pago' AND OLD.status != 'pago' AND NEW.conta_id IS NOT NULL THEN
        -- Criar movimentação de despesa
        INSERT INTO movimentacoes_financeiras 
        (conta_id, categoria_id, tipo, descricao, valor, data_movimentacao, status)
        SELECT 
            NEW.conta_id,
            (SELECT id FROM categorias_financeiras WHERE nome = 'Outros' AND tipo = 'despesa' LIMIT 1),
            'despesa',
            CONCAT('Custo Empresa: ', NEW.descricao),
            NEW.valor_pago,
            NEW.data_pagamento,
            'concluido';
    END IF;
END//

-- Trigger: Gerar custos recorrentes
CREATE TRIGGER trg_after_insert_custo_recorrente
AFTER INSERT ON custos_empresa
FOR EACH ROW
BEGIN
    IF NEW.recorrente = TRUE AND NEW.frequencia IS NOT NULL THEN
        -- Marcar para processamento futuro
        -- Este seria um job separado que gera os custos automaticamente
        -- Por ora apenas registramos que é recorrente
        SET @custo_recorrente_criado = NEW.id;
    END IF;
END//

DELIMITER ;

-- ============================================
-- DADOS DE EXEMPLO
-- ============================================

-- Categorias de Custos Fixos
INSERT INTO categorias_custos (nome, tipo, cor, icone) VALUES
('Aluguel', 'fixo', '#EF476F', 'home'),
('Internet', 'fixo', '#EF476F', 'wifi'),
('Energia Elétrica', 'fixo', '#EF476F', 'bolt'),
('Água', 'fixo', '#EF476F', 'tint'),
('Telefone', 'fixo', '#EF476F', 'phone'),
('Salários', 'fixo', '#EF476F', 'users'),
('Contador', 'fixo', '#EF476F', 'calculator');

-- Categorias de Custos Variáveis
INSERT INTO categorias_custos (nome, tipo, cor, icone) VALUES
('Combustível', 'variavel', '#FFD23F', 'gas-pump'),
('Manutenção Equipamentos', 'variavel', '#FFD23F', 'tools'),
('Material de Escritório', 'variavel', '#FFD23F', 'paperclip'),
('Marketing', 'variavel', '#FFD23F', 'bullhorn'),
('Alimentação', 'variavel', '#FFD23F', 'utensils'),
('Outros', 'variavel', '#FFD23F', 'ellipsis-h');

-- Exemplos de Custos Fixos Recorrentes
INSERT INTO custos_empresa (descricao, categoria_id, tipo, valor, data_custo, data_vencimento, recorrente, frequencia, dia_vencimento, status) 
SELECT 'Aluguel do Galpão', id, 'fixo', 1500.00, CURDATE(), LAST_DAY(CURDATE()), TRUE, 'mensal', 10, 'pendente'
FROM categorias_custos WHERE nome = 'Aluguel' LIMIT 1;

INSERT INTO custos_empresa (descricao, categoria_id, tipo, valor, data_custo, data_vencimento, recorrente, frequencia, dia_vencimento, status) 
SELECT 'Internet Fibra', id, 'fixo', 120.00, CURDATE(), LAST_DAY(CURDATE()), TRUE, 'mensal', 5, 'pendente'
FROM categorias_custos WHERE nome = 'Internet' LIMIT 1;

INSERT INTO custos_empresa (descricao, categoria_id, tipo, valor, data_custo, data_vencimento, recorrente, frequencia, dia_vencimento, status) 
SELECT 'Energia Elétrica', id, 'fixo', 350.00, CURDATE(), LAST_DAY(CURDATE()), TRUE, 'mensal', 15, 'pendente'
FROM categorias_custos WHERE nome = 'Energia Elétrica' LIMIT 1;

-- ============================================
-- VERIFICAÇÃO
-- ============================================

SELECT '✅ Módulo de Custos da Empresa instalado com sucesso!' as status;
SELECT COUNT(*) as total_categorias_custos FROM categorias_custos;
SELECT COUNT(*) as total_custos_exemplo FROM custos_empresa;
SELECT 'Tabelas criadas: 2' as info;
SELECT 'Views criadas: 4' as info;
SELECT 'Triggers criados: 2' as info;
