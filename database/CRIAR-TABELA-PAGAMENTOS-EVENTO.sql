-- Criar tabela de pagamentos parciais por evento
CREATE TABLE IF NOT EXISTS `pagamentos_evento` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evento_id` INT NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `data_pagamento` DATE NOT NULL,
    `descricao` VARCHAR(255) DEFAULT 'Pagamento',
    `forma_pagamento` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`evento_id`) REFERENCES `eventos_eventpro`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
