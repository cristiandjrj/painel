-- ============================================================
-- CORREÇÃO: Adicionar user_id na tabela configuracoes_eventpro
-- Execute este SQL no phpMyAdmin da Hostinger
-- ============================================================

-- 1. Adicionar coluna user_id (ignora se já existir)
ALTER TABLE `configuracoes_eventpro`
    ADD COLUMN IF NOT EXISTS `user_id` INT DEFAULT NULL COMMENT 'ID do usuário dono das configurações' AFTER `id`;

-- 2. Garantir que colunas de imagem são LONGTEXT (suportam base64 de imagens grandes)
ALTER TABLE `configuracoes_eventpro`
    MODIFY COLUMN `logo_base64` LONGTEXT DEFAULT NULL COMMENT 'Logo em base64',
    MODIFY COLUMN `assinatura_base64` LONGTEXT DEFAULT NULL COMMENT 'Assinatura digital em base64';

-- 3. Verificar resultado
SELECT id, user_id, nome_empresa,
       LENGTH(logo_base64) as tamanho_logo,
       LENGTH(assinatura_base64) as tamanho_assinatura
FROM configuracoes_eventpro;
