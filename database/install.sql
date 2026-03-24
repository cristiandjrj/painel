-- ============================================================
-- EVENTPRODJ - SCRIPT DE INSTALACAO COMPLETA DO BANCO DE DADOS
-- Sistema Completo de Gestao para DJs e Produtores de Eventos
-- ============================================================
-- Versao: 3.0
-- Data: 10/02/2026
-- Charset: UTF8MB4
-- Engine: InnoDB
-- ============================================================
--
-- INSTRUCOES:
--   1. Execute este arquivo no phpMyAdmin ou via terminal:
--      mysql -u root < install.sql
--   2. Este script eh IDEMPOTENTE: pode ser executado varias vezes
--      sem duplicar dados ou causar erros.
--   3. Todas as tabelas usam IF NOT EXISTS.
--   4. Dados padrao usam ON DUPLICATE KEY UPDATE ou INSERT IGNORE.
--
-- MODULOS:
--   1. Configuracoes do Sistema e da Empresa
--   2. Clientes
--   3. Servicos (Catalogo)
--   4. Eventos / Orcamentos
--   5. Servicos do Evento (N:N)
--   6. Taxas do Evento
--   7. Custos do Evento
--   8. Contas Bancarias (Empresa)
--   9. Transacoes Bancarias
--  10. Categorias Financeiras
--  11. Movimentacoes Financeiras
--  12. Custos da Empresa (Fixos/Variaveis)
--  13. Categorias de Custos
--  14. Contas a Pagar (Empresa)
--  15. Receitas (Parcelas de Clientes)
--  16. Despesas (Generica)
--  17. MEI - CNPJs
--  18. MEI - Vinculos CNPJ x Contas Bancarias
--  19. MEI - Receitas
--  20. Financeiro Pessoal (Contas, Categorias, Receitas, Despesas)
--  21. Configuracoes Chave-Valor
--  22. Logs do Sistema
--  23. Views
-- ============================================================

-- ============================================================
-- NOTA: Na Hostinger, o banco ja foi criado pelo painel.
-- Selecione o banco correto no phpMyAdmin antes de importar.
-- No XAMPP local, descomente as 2 linhas abaixo:
-- CREATE DATABASE IF NOT EXISTS sistema_eventos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE sistema_eventos;
-- ============================================================

-- ============================================================
-- TABELA 1: CONFIGURACOES DA EMPRESA (configuracoes_eventpro)
-- Armazena dados do perfil da empresa, logo, contrato, etc.
-- Tabela com registro unico (uma linha).
-- ============================================================

CREATE TABLE IF NOT EXISTS configuracoes_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL COMMENT 'ID do usuário dono das configurações',

    -- Dados da Empresa
    nome_empresa VARCHAR(255) DEFAULT 'EventProDJ' COMMENT 'Nome fantasia da empresa',
    nome_corporativo VARCHAR(255) DEFAULT NULL COMMENT 'Razao social',
    cnpj VARCHAR(20) DEFAULT NULL COMMENT 'CNPJ formatado',
    telefone VARCHAR(20) DEFAULT NULL COMMENT 'Telefone principal',
    whatsapp VARCHAR(20) DEFAULT NULL COMMENT 'WhatsApp Business',
    email VARCHAR(255) DEFAULT NULL COMMENT 'E-mail de contato',
    endereco VARCHAR(255) DEFAULT NULL COMMENT 'Endereco completo',
    cep VARCHAR(10) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(2) DEFAULT NULL,

    -- Redes Sociais
    instagram VARCHAR(255) DEFAULT NULL,
    facebook VARCHAR(255) DEFAULT NULL,
    youtube VARCHAR(255) DEFAULT NULL,
    site VARCHAR(255) DEFAULT NULL,

    -- Documentos e Imagens (base64)
    logo_base64 LONGTEXT DEFAULT NULL COMMENT 'Logo em base64',
    assinatura_base64 LONGTEXT DEFAULT NULL COMMENT 'Assinatura digital em base64',
    clausulas_contratuais LONGTEXT DEFAULT NULL COMMENT 'Clausulas padrao do contrato',

    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuracoes do perfil da empresa - registro unico';

-- Inserir registro padrao se nao existir
INSERT IGNORE INTO configuracoes_eventpro (id, nome_empresa) VALUES (1, 'EventProDJ');

-- ============================================================
-- TABELA 2: CONFIGURACOES DO SISTEMA (chave-valor)
-- Armazena configuracoes gerais do sistema em formato chave/valor.
-- ============================================================

CREATE TABLE IF NOT EXISTS configuracoes_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE COMMENT 'Identificador unico da configuracao',
    valor TEXT COMMENT 'Valor da configuracao',
    tipo ENUM('text', 'number', 'boolean', 'json', 'decimal') DEFAULT 'text' COMMENT 'Tipo do valor',
    descricao TEXT COMMENT 'Descricao da configuracao',
    categoria VARCHAR(50) DEFAULT 'geral' COMMENT 'Categoria agrupadora',
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_chave (chave),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuracoes do sistema em formato chave-valor';

-- Dados padrao de configuracao
INSERT INTO configuracoes_sistema (chave, valor, tipo, descricao, categoria) VALUES
('nome_empresa', 'EventProDJ', 'text', 'Nome da empresa', 'empresa'),
('cnpj', '', 'text', 'CNPJ da empresa', 'empresa'),
('telefone', '', 'text', 'Telefone principal', 'empresa'),
('email', '', 'text', 'E-mail principal', 'empresa'),
('endereco', '', 'text', 'Endereco completo', 'empresa'),
('logo_base64', '', 'text', 'Logo em base64', 'visual'),
('tema_padrao', 'dark', 'text', 'Tema padrao (dark/light)', 'visual'),
('moeda', 'BRL', 'text', 'Codigo da moeda', 'financeiro'),
('dias_validade_orcamento', '30', 'number', 'Dias de validade do orcamento', 'orcamento'),
('dias_alerta_vencimento', '7', 'number', 'Dias antes para alertar vencimento', 'financeiro'),
('validar_valores_nan', 'true', 'boolean', 'Converte NaN para 0', 'validacao'),
('sincronizar_total_pago', 'true', 'boolean', 'Mantem totalPago sincronizado', 'eventos'),
('auditoria_tolerancia_ok', '0.50', 'decimal', 'Tolerancia auditoria OK (R$)', 'auditoria'),
('auditoria_tolerancia_alerta', '5.00', 'decimal', 'Tolerancia auditoria ALERTA (R$)', 'auditoria')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- ============================================================
-- TABELA 3: CLIENTES (clientes_eventpro)
-- Cadastro completo de clientes com dados pessoais,
-- endereco, origem do lead e campos financeiros.
-- ============================================================

CREATE TABLE IF NOT EXISTS clientes_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Dados Pessoais
    nome VARCHAR(255) NOT NULL COMMENT 'Nome completo do cliente',
    telefone VARCHAR(20) DEFAULT NULL COMMENT 'Telefone com DDD',
    email VARCHAR(255) DEFAULT NULL,
    cpf VARCHAR(14) DEFAULT NULL COMMENT 'CPF formatado',
    data_aniversario DATE DEFAULT NULL COMMENT 'Data de nascimento / aniversario',

    -- Endereco
    cep VARCHAR(10) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL COMMENT 'Logradouro completo',
    rua VARCHAR(255) DEFAULT NULL COMMENT 'Nome da rua (campo detalhado)',
    numero VARCHAR(20) DEFAULT NULL,
    complemento VARCHAR(100) DEFAULT NULL,
    bairro VARCHAR(100) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(2) DEFAULT NULL,

    -- Origem do Lead
    origem VARCHAR(50) DEFAULT NULL COMMENT 'instagram, facebook, indicacao, google, whatsapp, site, outros',

    -- Financeiro vinculado ao cliente
    valor_total DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor total do servico',
    valor_sinal DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor do sinal pago',
    data_sinal DATE DEFAULT NULL COMMENT 'Data do pagamento do sinal',
    data_vencimento DATE DEFAULT NULL COMMENT 'Data de vencimento do pagamento',
    forma_pagamento_sinal VARCHAR(50) DEFAULT NULL COMMENT 'Forma de pagamento do sinal',
    conta_sinal INT DEFAULT NULL COMMENT 'ID da conta bancaria que recebeu o sinal',

    -- Observacoes
    observacoes TEXT DEFAULT NULL,

    -- Controle
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Indices
    INDEX idx_nome (nome),
    INDEX idx_telefone (telefone),
    INDEX idx_cpf (cpf),
    INDEX idx_data_aniversario (data_aniversario),
    INDEX idx_origem (origem),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cadastro de clientes do sistema de eventos';

-- ============================================================
-- TABELA 4: SERVICOS (servicos_eventpro)
-- Catalogo de servicos oferecidos (DJ, iluminacao, pirotecnia, etc.)
-- ============================================================

CREATE TABLE IF NOT EXISTS servicos_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Dados do Servico
    nome VARCHAR(255) NOT NULL COMMENT 'Nome do servico',
    descricao TEXT DEFAULT NULL COMMENT 'Descricao detalhada',
    valor_padrao DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor padrao de tabela',
    categoria VARCHAR(100) DEFAULT NULL COMMENT 'som_iluminacao, audiovisual, pirotecnia, decoracao, outros',

    -- Controle
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_nome (nome),
    INDEX idx_categoria (categoria),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catalogo de servicos oferecidos pela empresa';

-- ============================================================
-- TABELA 5: EVENTOS / ORCAMENTOS (eventos_eventpro)
-- Registro de eventos com dados de local, valores, status e datas.
-- Campos financeiros incluem sinal, desconto e total pago.
-- ============================================================

CREATE TABLE IF NOT EXISTS eventos_eventpro (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Cliente vinculado
    cliente_id INT NOT NULL COMMENT 'FK para clientes_eventpro',

    -- Tipo e Data
    tipo VARCHAR(100) NOT NULL COMMENT 'aniversario, casamento, formatura, corporativo, etc.',
    data_evento DATE NOT NULL,
    hora_inicio TIME DEFAULT NULL,
    hora_fim TIME DEFAULT NULL,
    duracao VARCHAR(20) DEFAULT NULL COMMENT 'Duracao calculada, ex: 6h',

    -- Local do Evento
    cep VARCHAR(10) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    numero VARCHAR(20) DEFAULT NULL,
    complemento VARCHAR(100) DEFAULT NULL,
    bairro VARCHAR(100) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(2) DEFAULT NULL,

    -- Valores
    valor_total DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor total do evento',
    desconto DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor do desconto (compatibilidade)',
    desconto_json JSON DEFAULT NULL COMMENT 'Desconto formato: {valor, tipo}',
    total_pago DECIMAL(10,2) DEFAULT 0 COMMENT 'Total ja pago pelo cliente',

    -- Sinal / Entrada
    valor_sinal DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor do sinal',
    data_sinal DATE DEFAULT NULL,
    data_vencimento DATE DEFAULT NULL COMMENT 'Data vencimento do sinal',
    forma_pagamento_sinal VARCHAR(50) DEFAULT NULL,
    conta_sinal INT DEFAULT NULL COMMENT 'Conta que recebeu o sinal',

    -- Status
    status ENUM('pendente', 'confirmado', 'andamento', 'concluido', 'cancelado') DEFAULT 'pendente',

    -- Observacoes
    observacoes TEXT DEFAULT NULL,

    -- Datas de Controle
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_validade DATE DEFAULT NULL COMMENT 'Validade do orcamento (+30 dias)',

    -- Chave Estrangeira
    FOREIGN KEY (cliente_id) REFERENCES clientes_eventpro(id) ON DELETE CASCADE,

    -- Indices
    INDEX idx_cliente (cliente_id),
    INDEX idx_data_evento (data_evento),
    INDEX idx_status (status),
    INDEX idx_data_evento_status (data_evento, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Eventos e orcamentos vinculados a clientes';

-- ============================================================
-- TABELA 6: SERVICOS DO EVENTO (evento_servicos)
-- Relacao N:N entre eventos e servicos, com snapshot de valores.
-- ============================================================

CREATE TABLE IF NOT EXISTS evento_servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    evento_id INT NOT NULL COMMENT 'FK para eventos_eventpro',
    servico_id INT DEFAULT NULL COMMENT 'FK para servicos_eventpro (NULL se customizado)',

    -- Snapshot dos dados do servico no momento da contratacao
    nome VARCHAR(255) DEFAULT NULL COMMENT 'Nome do servico (snapshot)',
    categoria VARCHAR(100) DEFAULT NULL,
    quantidade INT DEFAULT 1,
    valor_unitario DECIMAL(10,2) DEFAULT 0,
    valor DECIMAL(10,2) DEFAULT 0 COMMENT 'Valor total do item (qtd x unitario)',
    valor_total DECIMAL(10,2) DEFAULT 0 COMMENT 'Alias para compatibilidade',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos_eventpro(id) ON DELETE SET NULL,

    INDEX idx_evento (evento_id),
    INDEX idx_servico (servico_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Servicos contratados por evento (relacao N:N)';

-- ============================================================
-- TABELA 7: TAXAS DO EVENTO (evento_taxas)
-- Taxas extras cobradas por evento (deslocamento, hora extra, etc.)
-- ============================================================

CREATE TABLE IF NOT EXISTS evento_taxas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    evento_id INT NOT NULL COMMENT 'FK para eventos_eventpro',

    -- Dados da Taxa
    descricao VARCHAR(255) NOT NULL COMMENT 'Ex: Deslocamento, Taxa extra, etc.',
    valor DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE,

    INDEX idx_evento (evento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Taxas adicionais por evento';

-- ============================================================
-- TABELA 8: PAGAMENTOS DO EVENTO (pagamentos_evento)
-- Registra pagamentos parciais feitos por evento
-- ============================================================

CREATE TABLE IF NOT EXISTS pagamentos_evento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_pagamento DATE NOT NULL,
    descricao VARCHAR(255) DEFAULT 'Pagamento',
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE,
    INDEX idx_evento (evento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Pagamentos parciais por evento';

-- ============================================================
-- TABELA 9: CUSTOS DO EVENTO (custos_eventos)
-- Custos operacionais vinculados a um evento especifico
-- (ajudantes, transporte, equipamento alugado, etc.)
-- ============================================================

CREATE TABLE IF NOT EXISTS custos_eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL COMMENT 'FK para usuarios',
    evento_id INT NOT NULL COMMENT 'FK para eventos_eventpro',
    conta_id INT DEFAULT NULL COMMENT 'FK para contas_bancarias',

    -- Dados do Custo
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL COMMENT 'ajudantes, transporte, equipamento, outros',
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL COMMENT 'Data do custo',
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'pago',
    gera_despesa BOOLEAN DEFAULT TRUE COMMENT 'Se deve gerar movimentacao financeira',

    -- Observacoes
    observacoes TEXT DEFAULT NULL,

    -- Controle
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,

    INDEX idx_evento (evento_id),
    INDEX idx_data (data),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Custos operacionais por evento';

-- ============================================================
-- TABELA 9: CONTAS BANCARIAS DA EMPRESA (contas_bancarias)
-- Cadastro de contas bancarias, carteiras e caixas.
-- ============================================================

CREATE TABLE IF NOT EXISTS contas_bancarias (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Dados da Conta
    nome VARCHAR(255) NOT NULL COMMENT 'Nome identificador da conta',
    banco VARCHAR(100) DEFAULT NULL COMMENT 'Nome do banco',
    agencia VARCHAR(20) DEFAULT NULL,
    conta VARCHAR(50) DEFAULT NULL,
    tipo ENUM('corrente', 'poupanca', 'investimento', 'outros') DEFAULT 'corrente',

    -- Saldos
    saldo_inicial DECIMAL(10,2) DEFAULT 0.00,
    saldo_atual DECIMAL(10,2) DEFAULT 0.00,

    -- Controle
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_nome (nome),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contas bancarias da empresa';

-- ============================================================
-- TABELA 10: TRANSACOES BANCARIAS (transacoes_bancarias)
-- Movimentacoes de entrada e saida por conta bancaria.
-- ============================================================

CREATE TABLE IF NOT EXISTS transacoes_bancarias (
    id INT AUTO_INCREMENT PRIMARY KEY,

    conta_id INT NOT NULL COMMENT 'FK para contas_bancarias',
    tipo ENUM('entrada', 'saida') NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_transacao DATE NOT NULL,
    forma_pagamento VARCHAR(50) DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE CASCADE,

    INDEX idx_conta (conta_id),
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_transacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Transacoes de entrada e saida por conta bancaria';

-- ============================================================
-- TABELA 11: CATEGORIAS FINANCEIRAS (categorias_financeiras)
-- Categorias para classificar receitas e despesas da empresa.
-- ============================================================

CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    cor VARCHAR(7) DEFAULT NULL COMMENT 'Cor hexadecimal para UI',
    icone VARCHAR(50) DEFAULT NULL COMMENT 'Nome do icone FontAwesome ou emoji',

    ativo BOOLEAN DEFAULT TRUE,

    INDEX idx_nome (nome),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorias para classificacao de receitas e despesas';

-- Dados padrao de categorias financeiras
INSERT INTO categorias_financeiras (nome, tipo, cor, icone) VALUES
('Eventos', 'receita', '#06D6A0', 'calendar-check'),
('Servicos', 'receita', '#06D6A0', 'briefcase'),
('Sinal/Entrada', 'receita', '#FFD23F', 'hand-holding-usd'),
('Extras', 'receita', '#FF6B35', 'plus-circle'),
('Outros', 'receita', '#A0AEC0', 'plus-circle'),
('Ajudantes', 'despesa', '#EF476F', 'users'),
('Transporte/Combustivel', 'despesa', '#EF476F', 'car'),
('Equipamentos', 'despesa', '#004E89', 'tools'),
('Manutencao', 'despesa', '#EF476F', 'wrench'),
('Marketing', 'despesa', '#FF6B35', 'bullhorn'),
('Alimentacao', 'despesa', '#EF476F', 'utensils'),
('Fornecedores', 'despesa', '#EF476F', 'box'),
('Outros', 'despesa', '#A0AEC0', 'minus-circle')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ============================================================
-- TABELA 12: MOVIMENTACOES FINANCEIRAS (movimentacoes_financeiras)
-- Registro de todas as movimentacoes financeiras da empresa.
-- ============================================================

CREATE TABLE IF NOT EXISTS movimentacoes_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Relacionamentos
    user_id INT NOT NULL COMMENT 'FK para usuarios',
    conta_id INT DEFAULT NULL COMMENT 'FK para contas_bancarias',

    -- Dados da Movimentacao
    tipo ENUM('receita', 'despesa') NOT NULL,
    tipo_movimentacao VARCHAR(50) DEFAULT 'normal' COMMENT 'normal, custo_evento, custo_geral, pagamento_evento, conta_pagar',
    categoria VARCHAR(100) DEFAULT NULL,
    descricao TEXT NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_movimentacao DATE NOT NULL,
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    comprovante VARCHAR(255) DEFAULT NULL COMMENT 'Caminho do arquivo de comprovante',

    -- Observacoes
    observacoes TEXT DEFAULT NULL,

    -- Controle
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,

    INDEX idx_conta (conta_id),
    INDEX idx_tipo (tipo),
    INDEX idx_tipo_movimentacao (tipo_movimentacao),
    INDEX idx_data (data_movimentacao),
    INDEX idx_tipo_data (tipo, data_movimentacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Movimentacoes financeiras gerais da empresa';

-- ============================================================
-- TABELA 13: CATEGORIAS DE CUSTOS (categorias_custos)
-- Categorias para custos fixos e variaveis da empresa.
-- ============================================================

CREATE TABLE IF NOT EXISTS categorias_custos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(255) NOT NULL,
    tipo ENUM('fixo', 'variavel') NOT NULL,
    cor VARCHAR(7) DEFAULT '#EF476F',
    icone VARCHAR(50) DEFAULT 'tag',

    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorias para custos fixos e variaveis';

-- Dados padrao de categorias de custos
INSERT INTO categorias_custos (nome, tipo, cor, icone) VALUES
('Aluguel', 'fixo', '#EF476F', 'home'),
('Internet', 'fixo', '#EF476F', 'wifi'),
('Energia Eletrica', 'fixo', '#EF476F', 'bolt'),
('Agua', 'fixo', '#EF476F', 'tint'),
('Telefone', 'fixo', '#EF476F', 'phone'),
('Salarios', 'fixo', '#EF476F', 'users'),
('Contador', 'fixo', '#EF476F', 'calculator'),
('Combustivel', 'variavel', '#FFD23F', 'gas-pump'),
('Manutencao Equipamentos', 'variavel', '#FFD23F', 'tools'),
('Material de Escritorio', 'variavel', '#FFD23F', 'paperclip'),
('Marketing', 'variavel', '#FFD23F', 'bullhorn'),
('Alimentacao', 'variavel', '#FFD23F', 'utensils'),
('Outros', 'variavel', '#FFD23F', 'ellipsis-h')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ============================================================
-- TABELA 14: CUSTOS DA EMPRESA (custos_empresa)
-- Custos fixos e variaveis da empresa (aluguel, luz, etc.)
-- ============================================================

CREATE TABLE IF NOT EXISTS custos_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Dados do Custo
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL,
    categoria_id INT DEFAULT NULL COMMENT 'FK para categorias_custos',
    tipo ENUM('fixo', 'variavel') DEFAULT 'variavel',
    valor DECIMAL(10,2) NOT NULL,

    -- Datas
    data_custo DATE NOT NULL,
    data_vencimento DATE DEFAULT NULL,

    -- Recorrencia
    recorrente BOOLEAN DEFAULT FALSE,
    frequencia ENUM('mensal', 'bimestral', 'trimestral', 'semestral', 'anual') DEFAULT NULL,
    dia_vencimento INT DEFAULT NULL COMMENT 'Dia do mes para vencimento',

    -- Status e Pagamento
    status ENUM('pendente', 'pago', 'atrasado') DEFAULT 'pendente',
    conta_id INT DEFAULT NULL COMMENT 'Conta usada para pagar',
    data_pagamento DATE DEFAULT NULL,
    valor_pago DECIMAL(10,2) DEFAULT NULL,

    -- Observacoes
    observacoes TEXT DEFAULT NULL,

    -- Controle
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (categoria_id) REFERENCES categorias_custos(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,

    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_data (data_custo),
    INDEX idx_recorrente (recorrente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Custos fixos e variaveis da empresa';

-- ============================================================
-- TABELA 15: CONTAS A PAGAR (contas_pagar_empresa)
-- Contas a pagar da empresa com controle de vencimento e status.
-- ============================================================

CREATE TABLE IF NOT EXISTS contas_pagar_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,

    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE DEFAULT NULL,
    status ENUM('pendente', 'paga', 'vencida') DEFAULT 'pendente',
    categoria VARCHAR(100) DEFAULT NULL,
    fornecedor VARCHAR(255) DEFAULT NULL,

    -- Vinculo com conta bancaria
    conta_id INT DEFAULT NULL COMMENT 'Conta bancaria para pagamento',

    -- Observacoes
    observacoes TEXT DEFAULT NULL,

    -- Controle
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,

    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento),
    INDEX idx_status_vencimento (status, data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contas a pagar da empresa';

-- ============================================================
-- TABELA 16: RECEITAS / PARCELAS DE CLIENTES (receitas)
-- Parcelas de pagamento vinculadas a clientes e eventos.
-- ============================================================

CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cliente_id INT NOT NULL COMMENT 'FK para clientes_eventpro',
    evento_id INT DEFAULT NULL COMMENT 'FK para eventos_eventpro',
    conta_id INT DEFAULT NULL COMMENT 'FK para contas_bancarias',

    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE DEFAULT NULL,
    status ENUM('pendente', 'pago', 'atrasado', 'cancelado') DEFAULT 'pendente',

    numero_parcela INT DEFAULT 1,
    total_parcelas INT DEFAULT 1,
    forma_pagamento VARCHAR(50) DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,

    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id) REFERENCES clientes_eventpro(id) ON DELETE CASCADE,
    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,

    INDEX idx_cliente (cliente_id),
    INDEX idx_evento (evento_id),
    INDEX idx_conta (conta_id),
    INDEX idx_status (status),
    INDEX idx_data_vencimento (data_vencimento),
    INDEX idx_data_pagamento (data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Parcelas de pagamento (receitas) vinculadas a clientes e eventos';

-- ============================================================
-- TABELA 17: DESPESAS GENERICA (despesas)
-- Tabela de despesas generica usada pelo modulo financeiro basico.
-- ============================================================

CREATE TABLE IF NOT EXISTS despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_despesa DATE NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL,
    conta_id INT DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,

    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_id) REFERENCES contas_bancarias(id) ON DELETE SET NULL,

    INDEX idx_data (data_despesa),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Despesas genericas da empresa';

-- ============================================================
-- TABELA 18: MEI - CNPJs (mei_cnpjs)
-- Cadastro de CNPJs MEI para controle de faturamento.
-- ============================================================

CREATE TABLE IF NOT EXISTS mei_cnpjs (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cnpj VARCHAR(20) NOT NULL COMMENT 'CNPJ formatado do MEI',
    nome VARCHAR(255) NOT NULL COMMENT 'Nome/Razao social do MEI',
    ativo BOOLEAN DEFAULT FALSE COMMENT 'Se este eh o CNPJ ativo para uso',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cnpj (cnpj),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='CNPJs MEI cadastrados para controle de faturamento';

-- ============================================================
-- TABELA 19: MEI - VINCULOS CNPJ x CONTAS (mei_cnpj_contas)
-- Vincula CNPJs MEI a contas bancarias.
-- Uma conta bancaria so pode estar vinculada a um CNPJ.
-- ============================================================

CREATE TABLE IF NOT EXISTS mei_cnpj_contas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    mei_cnpj_id INT NOT NULL COMMENT 'FK para mei_cnpjs',
    conta_bancaria_id INT NOT NULL COMMENT 'FK para contas_bancarias',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (mei_cnpj_id) REFERENCES mei_cnpjs(id) ON DELETE CASCADE,
    FOREIGN KEY (conta_bancaria_id) REFERENCES contas_bancarias(id) ON DELETE CASCADE,

    UNIQUE KEY uk_conta_bancaria (conta_bancaria_id) COMMENT 'Uma conta so pode estar em um CNPJ',
    INDEX idx_mei_cnpj (mei_cnpj_id),
    INDEX idx_conta (conta_bancaria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Vinculo entre CNPJs MEI e contas bancarias';

-- ============================================================
-- TABELA 20: MEI - RECEITAS (mei_receitas)
-- Receitas do MEI para relatorio mensal de faturamento.
-- ============================================================

CREATE TABLE IF NOT EXISTS mei_receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    mei_cnpj_id INT DEFAULT NULL COMMENT 'FK para mei_cnpjs',
    mes INT NOT NULL COMMENT 'Mes de referencia (1-12)',
    ano INT NOT NULL COMMENT 'Ano de referencia',
    data_receita DATE DEFAULT NULL COMMENT 'Data da receita',
    cliente VARCHAR(255) DEFAULT NULL COMMENT 'Nome do cliente',
    cpf_cnpj VARCHAR(20) DEFAULT NULL COMMENT 'CPF/CNPJ do cliente',
    documento_fiscal VARCHAR(255) DEFAULT NULL COMMENT 'Numero ou descricao do documento fiscal',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (mei_cnpj_id) REFERENCES mei_cnpjs(id) ON DELETE SET NULL,

    INDEX idx_mei_cnpj (mei_cnpj_id),
    INDEX idx_mes_ano (mes, ano),
    INDEX idx_data (data_receita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Receitas do MEI para relatorio mensal de faturamento';

-- ============================================================
-- TABELA 21: CONTAS PESSOAIS (contas_pessoais)
-- Contas bancarias do financeiro pessoal do usuario.
-- ============================================================

CREATE TABLE IF NOT EXISTS contas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(255) NOT NULL,
    tipo ENUM('conta_corrente', 'conta_poupanca', 'carteira', 'investimento', 'outros') DEFAULT 'conta_corrente',
    banco VARCHAR(100) DEFAULT NULL,

    saldo_inicial DECIMAL(10,2) DEFAULT 0.00,
    saldo_atual DECIMAL(10,2) DEFAULT 0.00,

    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_nome (nome),
    INDEX idx_tipo (tipo),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Contas bancarias pessoais do usuario';

-- ============================================================
-- TABELA 22: CATEGORIAS PESSOAIS (categorias_pessoais)
-- Categorias de receitas e despesas pessoais.
-- ============================================================

CREATE TABLE IF NOT EXISTS categorias_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    cor VARCHAR(7) DEFAULT NULL,
    icone VARCHAR(50) DEFAULT NULL,

    ativo BOOLEAN DEFAULT TRUE,

    INDEX idx_nome (nome),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Categorias de receitas e despesas pessoais';

-- Dados padrao de categorias pessoais
INSERT INTO categorias_pessoais (nome, tipo, cor, icone) VALUES
('Salario', 'receita', '#06D6A0', 'briefcase'),
('Freelance', 'receita', '#FFD23F', 'laptop'),
('Investimentos', 'receita', '#FF6B35', 'chart-line'),
('Aluguel Recebido', 'receita', '#004E89', 'home'),
('Outros', 'receita', '#A0AEC0', 'plus-circle'),
('Moradia', 'despesa', '#EF476F', 'home'),
('Alimentacao', 'despesa', '#FFD23F', 'utensils'),
('Transporte', 'despesa', '#FF6B35', 'car'),
('Saude', 'despesa', '#06D6A0', 'heartbeat'),
('Educacao', 'despesa', '#004E89', 'graduation-cap'),
('Lazer', 'despesa', '#8B5CF6', 'gamepad'),
('Vestuario', 'despesa', '#EC4899', 'tshirt'),
('Outros', 'despesa', '#A0AEC0', 'minus-circle')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ============================================================
-- TABELA 23: RECEITAS PESSOAIS (receitas_pessoais)
-- Registro de receitas pessoais do usuario.
-- ============================================================

CREATE TABLE IF NOT EXISTS receitas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,

    conta_id INT DEFAULT NULL COMMENT 'FK para contas_pessoais',
    categoria VARCHAR(100) DEFAULT NULL,

    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL,
    recorrente BOOLEAN DEFAULT FALSE,
    observacoes TEXT DEFAULT NULL,

    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_id) REFERENCES contas_pessoais(id) ON DELETE SET NULL,

    INDEX idx_conta (conta_id),
    INDEX idx_data (data),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Receitas pessoais do usuario';

-- ============================================================
-- TABELA 24: DESPESAS PESSOAIS (despesas_pessoais)
-- Registro de despesas pessoais do usuario.
-- ============================================================

CREATE TABLE IF NOT EXISTS despesas_pessoais (
    id INT AUTO_INCREMENT PRIMARY KEY,

    conta_id INT DEFAULT NULL COMMENT 'FK para contas_pessoais',
    categoria VARCHAR(100) DEFAULT NULL,

    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data DATE NOT NULL,
    tipo_despesa VARCHAR(50) DEFAULT 'normal',
    recorrente BOOLEAN DEFAULT FALSE,
    observacoes TEXT DEFAULT NULL,

    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conta_id) REFERENCES contas_pessoais(id) ON DELETE SET NULL,

    INDEX idx_conta (conta_id),
    INDEX idx_data (data),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo_despesa (tipo_despesa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Despesas pessoais do usuario';

-- ============================================================
-- TABELA 25: LOGS DO SISTEMA (logs_sistema)
-- Registro de eventos e acoes do sistema para auditoria.
-- ============================================================

CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,

    tipo VARCHAR(50) NOT NULL COMMENT 'info, warning, error, audit',
    mensagem TEXT NOT NULL,
    detalhes JSON DEFAULT NULL COMMENT 'Dados adicionais em JSON',

    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tipo (tipo),
    INDEX idx_data (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Logs de auditoria e eventos do sistema';

-- ============================================================
-- TABELA 26: FINANCEIRO DO CLIENTE (cliente_financeiro)
-- Controle financeiro consolidado por cliente/evento.
-- ============================================================

CREATE TABLE IF NOT EXISTS cliente_financeiro (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cliente_id INT NOT NULL COMMENT 'FK para clientes_eventpro',
    evento_id INT DEFAULT NULL COMMENT 'FK para eventos_eventpro (NULL = avulso)',

    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_pago DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    data_vencimento DATE DEFAULT NULL,
    data_pagamento DATE DEFAULT NULL,

    status ENUM('pendente', 'pago', 'parcial', 'atrasado') DEFAULT 'pendente',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id) REFERENCES clientes_eventpro(id) ON DELETE CASCADE,

    INDEX idx_cliente (cliente_id),
    INDEX idx_evento (evento_id),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Controle financeiro consolidado por cliente/evento';

-- ============================================================
-- TABELA 27: PAGAMENTOS DO CLIENTE (cliente_pagamentos)
-- Historico detalhado de pagamentos por registro financeiro.
-- ============================================================

CREATE TABLE IF NOT EXISTS cliente_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    financeiro_id INT NOT NULL COMMENT 'FK para cliente_financeiro',

    valor DECIMAL(10,2) NOT NULL,
    data_pagamento DATE NOT NULL,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'transferencia', 'outros') DEFAULT 'dinheiro',

    observacoes TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (financeiro_id) REFERENCES cliente_financeiro(id) ON DELETE CASCADE,

    INDEX idx_financeiro (financeiro_id),
    INDEX idx_data (data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historico de pagamentos por registro financeiro';

-- ============================================================
-- TABELA 28: CUSTOS DO EVENTO (evento_custos) - Formato Alternativo
-- Custos de evento no formato do modulo de orcamentos.
-- ============================================================

CREATE TABLE IF NOT EXISTS evento_custos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    evento_id INT NOT NULL COMMENT 'FK para eventos_eventpro',

    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) DEFAULT NULL COMMENT 'ajudantes, transporte, equipamento, outros',
    valor DECIMAL(10,2) NOT NULL,
    data_custo DATE DEFAULT NULL,

    observacoes TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (evento_id) REFERENCES eventos_eventpro(id) ON DELETE CASCADE,

    INDEX idx_evento (evento_id),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Custos do evento (formato modulo orcamentos)';

-- ============================================================
-- VIEWS
-- ============================================================

-- View: Eventos Completos com dados do cliente e financeiro
CREATE OR REPLACE VIEW vw_eventos_completos AS
SELECT
    e.*,
    c.nome AS cliente_nome,
    c.telefone AS cliente_telefone,
    c.email AS cliente_email,
    COALESCE(e.desconto, 0) AS valor_desconto,
    (e.valor_total - COALESCE(e.desconto, 0)) AS valor_liquido,
    (e.valor_total - COALESCE(e.desconto, 0) - COALESCE(e.total_pago, 0)) AS saldo_devedor
FROM eventos_eventpro e
LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id;

-- View: Movimentacoes com nome da conta
CREATE OR REPLACE VIEW vw_movimentacoes_completas AS
SELECT
    m.*,
    cb.nome AS conta_nome,
    cb.banco AS conta_banco
FROM movimentacoes_financeiras m
LEFT JOIN contas_bancarias cb ON m.conta_id = cb.id;

-- View: Aniversariantes do mes atual
CREATE OR REPLACE VIEW vw_aniversariantes_mes AS
SELECT
    id,
    nome,
    telefone,
    email,
    data_aniversario,
    DAY(data_aniversario) AS dia_aniversario,
    MONTH(data_aniversario) AS mes_aniversario,
    DATEDIFF(
        DATE_ADD(
            DATE_FORMAT(data_aniversario, CONCAT(YEAR(CURDATE()), '-%m-%d')),
            INTERVAL IF(
                DATE_FORMAT(data_aniversario, CONCAT(YEAR(CURDATE()), '-%m-%d')) < CURDATE(), 1, 0
            ) YEAR
        ),
        CURDATE()
    ) AS dias_ate_aniversario
FROM clientes_eventpro
WHERE data_aniversario IS NOT NULL
  AND MONTH(data_aniversario) = MONTH(CURDATE())
ORDER BY DAY(data_aniversario);

-- View: Clientes agrupados por origem
CREATE OR REPLACE VIEW vw_clientes_por_origem AS
SELECT
    COALESCE(origem, 'nao_informado') AS origem,
    COUNT(*) AS total,
    ROUND(
        (COUNT(*) * 100.0 / NULLIF((SELECT COUNT(*) FROM clientes_eventpro WHERE ativo = TRUE), 0)),
        2
    ) AS percentual
FROM clientes_eventpro
WHERE ativo = TRUE
GROUP BY origem
ORDER BY total DESC;

-- View: Despesas puras (sem custos de evento/geral)
CREATE OR REPLACE VIEW vw_despesas_puras AS
SELECT m.*
FROM movimentacoes_financeiras m
WHERE m.tipo = 'despesa'
  AND (m.tipo_movimentacao IS NULL OR m.tipo_movimentacao NOT IN ('custo_evento', 'custo_geral'))
  AND m.descricao NOT LIKE '[Custo Evento:%'
  AND m.descricao NOT LIKE '[Custo Geral:%';

-- View: Resumo financeiro do mes atual
CREATE OR REPLACE VIEW vw_resumo_financeiro AS
SELECT
    COALESCE((
        SELECT SUM(valor)
        FROM movimentacoes_financeiras
        WHERE tipo = 'receita'
        AND MONTH(data_movimentacao) = MONTH(CURDATE())
        AND YEAR(data_movimentacao) = YEAR(CURDATE())
    ), 0) AS total_receitas,

    COALESCE((
        SELECT SUM(valor)
        FROM movimentacoes_financeiras
        WHERE tipo = 'despesa'
        AND (tipo_movimentacao IS NULL OR tipo_movimentacao NOT IN ('custo_evento', 'custo_geral'))
        AND MONTH(data_movimentacao) = MONTH(CURDATE())
        AND YEAR(data_movimentacao) = YEAR(CURDATE())
    ), 0) AS total_despesas,

    COALESCE((
        SELECT SUM(valor)
        FROM custos_empresa
        WHERE MONTH(data_custo) = MONTH(CURDATE())
        AND YEAR(data_custo) = YEAR(CURDATE())
    ), 0) AS total_custos_gerais,

    COALESCE((
        SELECT SUM(valor)
        FROM custos_eventos
        WHERE MONTH(data) = MONTH(CURDATE())
        AND YEAR(data) = YEAR(CURDATE())
    ), 0) AS total_custos_eventos,

    COALESCE((
        SELECT SUM(saldo_atual)
        FROM contas_bancarias
        WHERE ativo = TRUE
    ), 0) AS saldo_contas;

-- View: Fluxo de caixa mensal (ultimos 6 meses)
CREATE OR REPLACE VIEW vw_fluxo_caixa_mensal AS
SELECT
    DATE_FORMAT(data_movimentacao, '%Y-%m') AS mes_ano,
    YEAR(data_movimentacao) AS ano,
    MONTH(data_movimentacao) AS mes,
    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) AS receitas,
    COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) AS despesas,
    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0) AS saldo
FROM movimentacoes_financeiras
WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(data_movimentacao, '%Y-%m'), YEAR(data_movimentacao), MONTH(data_movimentacao)
ORDER BY ano DESC, mes DESC;

-- View: Top categorias de receitas do mes
CREATE OR REPLACE VIEW vw_top_categorias_receitas AS
SELECT
    COALESCE(categoria, 'Sem Categoria') AS categoria,
    COUNT(*) AS quantidade,
    COALESCE(SUM(valor), 0) AS total,
    ROUND(AVG(valor), 2) AS media
FROM movimentacoes_financeiras
WHERE tipo = 'receita'
AND MONTH(data_movimentacao) = MONTH(CURDATE())
AND YEAR(data_movimentacao) = YEAR(CURDATE())
GROUP BY categoria
ORDER BY total DESC
LIMIT 10;

-- View: Top categorias de despesas do mes
CREATE OR REPLACE VIEW vw_top_categorias_despesas AS
SELECT
    COALESCE(categoria, 'Sem Categoria') AS categoria,
    COUNT(*) AS quantidade,
    COALESCE(SUM(valor), 0) AS total,
    ROUND(AVG(valor), 2) AS media
FROM movimentacoes_financeiras
WHERE tipo = 'despesa'
AND MONTH(data_movimentacao) = MONTH(CURDATE())
AND YEAR(data_movimentacao) = YEAR(CURDATE())
GROUP BY categoria
ORDER BY total DESC
LIMIT 10;

-- View: Resumo custos empresa do mes
CREATE OR REPLACE VIEW vw_resumo_custos_empresa AS
SELECT
    MONTH(CURDATE()) AS mes,
    YEAR(CURDATE()) AS ano,
    COALESCE((SELECT SUM(valor) FROM custos_empresa WHERE tipo = 'fixo'
              AND MONTH(data_custo) = MONTH(CURDATE()) AND YEAR(data_custo) = YEAR(CURDATE())), 0) AS custos_fixos,
    COALESCE((SELECT SUM(valor) FROM custos_empresa WHERE tipo = 'variavel'
              AND MONTH(data_custo) = MONTH(CURDATE()) AND YEAR(data_custo) = YEAR(CURDATE())), 0) AS custos_variaveis,
    COALESCE((SELECT SUM(valor) FROM custos_empresa
              WHERE MONTH(data_custo) = MONTH(CURDATE()) AND YEAR(data_custo) = YEAR(CURDATE())), 0) AS total_custos,
    COALESCE((SELECT COUNT(*) FROM custos_empresa
              WHERE MONTH(data_custo) = MONTH(CURDATE()) AND YEAR(data_custo) = YEAR(CURDATE())), 0) AS quantidade;

-- View: Custos mensais (ultimos 6 meses)
CREATE OR REPLACE VIEW vw_custos_mensais AS
SELECT
    DATE_FORMAT(data_custo, '%Y-%m') AS mes_ano,
    YEAR(data_custo) AS ano,
    MONTH(data_custo) AS mes,
    tipo,
    COALESCE(SUM(valor), 0) AS total,
    COUNT(*) AS quantidade
FROM custos_empresa
WHERE data_custo >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(data_custo, '%Y-%m'), YEAR(data_custo), MONTH(data_custo), tipo
ORDER BY ano DESC, mes DESC, tipo;

-- View: Top categorias de custos do mes
CREATE OR REPLACE VIEW vw_top_categorias_custos AS
SELECT
    COALESCE(categoria, 'Sem Categoria') AS categoria,
    COUNT(*) AS quantidade,
    COALESCE(SUM(valor), 0) AS total,
    ROUND(AVG(valor), 2) AS media
FROM custos_empresa
WHERE MONTH(data_custo) = MONTH(CURDATE())
AND YEAR(data_custo) = YEAR(CURDATE())
GROUP BY categoria
ORDER BY total DESC
LIMIT 10;

-- View: Custos empresa completos com status temporal
CREATE OR REPLACE VIEW vw_custos_empresa_completos AS
SELECT
    ce.*,
    CASE
        WHEN ce.data_custo < CURDATE() THEN 'vencido'
        WHEN ce.data_custo = CURDATE() THEN 'hoje'
        WHEN ce.data_custo <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'proximos'
        ELSE 'futuro'
    END AS status_temporal,
    DATEDIFF(ce.data_custo, CURDATE()) AS dias_vencimento
FROM custos_empresa ce
ORDER BY ce.data_custo DESC;

-- View: Contas a pagar com status detalhado
CREATE OR REPLACE VIEW vw_contas_pagar_status AS
SELECT
    cp.*,
    CASE
        WHEN cp.status = 'paga' THEN 'paga'
        WHEN cp.data_vencimento < CURDATE() THEN 'vencida'
        WHEN cp.data_vencimento = CURDATE() THEN 'vence_hoje'
        WHEN cp.data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'proxima'
        ELSE 'futura'
    END AS status_detalhado,
    DATEDIFF(cp.data_vencimento, CURDATE()) AS dias_vencimento
FROM contas_pagar_empresa cp
ORDER BY cp.data_vencimento ASC;

-- View: Receitas detalhadas com dados de cliente e evento
CREATE OR REPLACE VIEW vw_receitas_detalhadas AS
SELECT
    r.id,
    r.cliente_id,
    c.nome AS cliente_nome,
    c.telefone AS cliente_telefone,
    r.evento_id,
    e.tipo AS evento_tipo,
    e.data_evento,
    r.conta_id,
    cb.nome AS conta_nome,
    r.descricao,
    r.valor,
    r.data_vencimento,
    r.data_pagamento,
    r.status,
    r.numero_parcela,
    r.total_parcelas,
    r.forma_pagamento,
    r.observacoes,
    r.data_cadastro,
    CASE
        WHEN r.status = 'pendente' AND r.data_vencimento < CURDATE() THEN 'atrasado'
        ELSE r.status
    END AS status_real,
    CASE
        WHEN r.status = 'pendente' AND r.data_vencimento < CURDATE()
        THEN DATEDIFF(CURDATE(), r.data_vencimento)
        ELSE 0
    END AS dias_atraso
FROM receitas r
INNER JOIN clientes_eventpro c ON r.cliente_id = c.id
LEFT JOIN eventos_eventpro e ON r.evento_id = e.id
LEFT JOIN contas_bancarias cb ON r.conta_id = cb.id;

-- View: Resumo financeiro por cliente
CREATE OR REPLACE VIEW vw_resumo_clientes AS
SELECT
    c.id AS cliente_id,
    c.nome AS cliente_nome,
    c.telefone,
    c.email,
    COUNT(DISTINCT r.evento_id) AS total_eventos,
    COUNT(r.id) AS total_parcelas,
    SUM(CASE WHEN r.status = 'pago' THEN 1 ELSE 0 END) AS parcelas_pagas,
    SUM(CASE WHEN r.status = 'pendente' THEN 1 ELSE 0 END) AS parcelas_pendentes,
    COALESCE(SUM(r.valor), 0) AS valor_total,
    COALESCE(SUM(CASE WHEN r.status = 'pago' THEN r.valor ELSE 0 END), 0) AS valor_pago,
    COALESCE(SUM(CASE WHEN r.status = 'pendente' THEN r.valor ELSE 0 END), 0) AS valor_pendente,
    MIN(CASE WHEN r.status = 'pendente' THEN r.data_vencimento END) AS proxima_parcela
FROM clientes_eventpro c
LEFT JOIN receitas r ON c.id = r.cliente_id
GROUP BY c.id, c.nome, c.telefone, c.email;

-- View: Resumo financeiro pessoal do mes
CREATE OR REPLACE VIEW vw_resumo_financeiro_pessoal AS
SELECT
    (SELECT COALESCE(SUM(valor), 0) FROM receitas_pessoais
     WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())) AS total_receitas,
    (SELECT COALESCE(SUM(valor), 0) FROM despesas_pessoais
     WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())) AS total_despesas,
    (SELECT COALESCE(SUM(saldo_atual), 0) FROM contas_pessoais WHERE ativo = TRUE) AS saldo_total,
    ((SELECT COALESCE(SUM(valor), 0) FROM receitas_pessoais
      WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE())) -
     (SELECT COALESCE(SUM(valor), 0) FROM despesas_pessoais
      WHERE MONTH(data) = MONTH(CURDATE()) AND YEAR(data) = YEAR(CURDATE()))) AS saldo_mes;

-- ============================================================
-- 24. BOT WHATSAPP - Configuracoes
-- ============================================================

CREATE TABLE IF NOT EXISTS bot_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    bot_url VARCHAR(255) DEFAULT 'http://localhost:3001',
    api_key VARCHAR(128) DEFAULT NULL,
    nome_empresa VARCHAR(255) DEFAULT 'Cristian DJ Sonorizacao',
    assinatura VARCHAR(255) DEFAULT '*Cristian DJ Sonorizacao* 🎧',
    link_catalogo VARCHAR(500) DEFAULT 'https://cristiandj.com.br/catalogo',
    link_instagram VARCHAR(500) DEFAULT 'https://www.instagram.com/cristiandj.rj',
    saudacao_template TEXT,
    menu_intro TEXT,
    menu_texto TEXT,
    menu_botao_texto VARCHAR(100) DEFAULT '📋 Ver opcoes',
    menu_secao_titulo VARCHAR(100) DEFAULT 'Menu Principal',
    opcoes_menu JSON,
    respostas JSON,
    tempo_lembrete_min INT DEFAULT 60,
    tempo_followup_min INT DEFAULT 300,
    hora_silencio_inicio INT DEFAULT 0,
    hora_silencio_fim INT DEFAULT 9,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 25. BOT WHATSAPP - Conversas / Leads
-- ============================================================

CREATE TABLE IF NOT EXISTS bot_conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    jid VARCHAR(50) NOT NULL,
    nome_contato VARCHAR(255) DEFAULT 'Cliente',
    telefone VARCHAR(20),
    primeira_interacao DATETIME,
    ultima_interacao DATETIME,
    total_interacoes INT DEFAULT 0,
    ultima_opcao VARCHAR(50),
    status ENUM('ativo','atendente','inativo') DEFAULT 'ativo',
    ja_recebeu_menu TINYINT(1) DEFAULT 0,
    follow_up_enviado DATETIME DEFAULT NULL,
    cliente_id INT DEFAULT NULL,
    observacoes TEXT,
    data_sync TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_jid (jid),
    INDEX idx_status (status),
    INDEX idx_cliente (cliente_id),
    UNIQUE KEY uk_user_jid (user_id, jid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 26. BOT WHATSAPP - Logs
-- ============================================================

CREATE TABLE IF NOT EXISTS bot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    tipo ENUM('mensagem','erro','conexao','config','envio') DEFAULT 'mensagem',
    descricao VARCHAR(500),
    dados_json JSON,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_tipo (tipo),
    INDEX idx_data (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIM DA INSTALACAO
-- ============================================================

SELECT '=== INSTALACAO EVENTPRODJ CONCLUIDA ===' AS status;
SELECT CONCAT('Tabelas criadas: ', COUNT(*)) AS info
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'sistema_eventos' AND TABLE_TYPE = 'BASE TABLE';
SELECT CONCAT('Views criadas: ', COUNT(*)) AS info
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = 'sistema_eventos';
SELECT 'Sistema pronto para uso!' AS mensagem;
