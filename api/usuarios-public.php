<?php
/**
 * API PÚBLICA - REGISTRO DE CONTAS TESTE
 * Não requer autenticação. Apenas criação de contas teste.
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

require_once '../config/database.php';

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create_trial':
            criarContaTeste($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function criarContaTeste($pdo) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    // Validações
    if (!$nome || !$email || !$senha) {
        echo json_encode(['success' => false, 'message' => 'Nome, email e senha são obrigatórios']);
        return;
    }

    if (strlen($senha) < 6) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        return;
    }

    // Verificar se email já existe
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
        return;
    }

    // Limitar criações por IP (anti-spam) - máximo 3 contas por IP por dia
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    // (Opcional: implementar limitação por IP se necessário)

    // Criar conta teste com expiração de 7 dias
    $dataExpiracao = date('Y-m-d', strtotime('+7 days'));
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, telefone, role, status, plano, data_expiracao)
        VALUES (?, ?, ?, ?, 'usuario', 'ativo', 'teste', ?)
    ");
    $stmt->execute([$nome, $email, $senhaHash, $telefone, $dataExpiracao]);

    $newId = $pdo->lastInsertId();

    // Criar categorias financeiras padrão para o novo usuário
    $categorias = [
        ['Serviços', 'receita', '#06D6A0', '💰'],
        ['Eventos', 'receita', '#4F46E5', '🎉'],
        ['Extras', 'receita', '#FF6B35', '⭐'],
        ['Fornecedores', 'despesa', '#EF476F', '🏪'],
        ['Equipamentos', 'despesa', '#FF6B35', '🔧'],
        ['Marketing', 'despesa', '#A855F7', '📢'],
        ['Transporte', 'despesa', '#06B6D4', '🚗'],
        ['Outros', 'despesa', '#6B7280', '📦'],
    ];

    $stmtCat = $pdo->prepare("INSERT INTO categorias_financeiras (user_id, nome, tipo, cor, icone, ativo) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($categorias as $cat) {
        try {
            $stmtCat->execute([$newId, $cat[0], $cat[1], $cat[2], $cat[3]]);
        } catch (Exception $e) {
            // Ignora duplicados
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Conta de teste criada com sucesso! Você tem 7 dias para testar o sistema. Faça login com seu email e senha.',
        'data_expiracao' => $dataExpiracao
    ]);
}
