<?php
/**
 * API - GERENCIAMENTO DE USUÁRIOS
 * CRUD completo de usuários, apenas admin pode acessar
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listarUsuarios($pdo, $userId);
            break;
        case 'get':
            obterUsuario($pdo, $userId);
            break;
        case 'create':
            criarUsuario($pdo, $userId);
            break;
        case 'update':
            atualizarUsuario($pdo, $userId);
            break;
        case 'toggle_status':
            toggleStatus($pdo, $userId);
            break;
        case 'delete':
            deletarUsuario($pdo, $userId);
            break;
        case 'stats':
            estatisticasUsuario($pdo, $userId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ============================================
// LISTAR USUÁRIOS (apenas admin)
// ============================================
function listarUsuarios($pdo, $currentUserId) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $stmt = $pdo->query("
        SELECT
            u.id, u.nome, u.email, u.telefone, u.role, u.status, u.plano,
            u.data_cadastro, u.data_expiracao, u.ultimo_acesso,
            (SELECT COUNT(*) FROM eventos_eventpro WHERE user_id = u.id) as total_eventos,
            (SELECT COUNT(*) FROM clientes_eventpro WHERE user_id = u.id) as total_clientes
        FROM usuarios u
        ORDER BY u.data_cadastro DESC
    ");

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar expiração de planos teste
    foreach ($usuarios as &$user) {
        if ($user['plano'] === 'teste' && $user['data_expiracao'] && $user['status'] === 'ativo') {
            if (date('Y-m-d') > $user['data_expiracao']) {
                $user['status'] = 'expirado';
                $pdo->prepare("UPDATE usuarios SET status = 'expirado' WHERE id = ?")->execute([$user['id']]);
            }
        }

        // Calcular dias restantes
        if ($user['data_expiracao']) {
            $hoje = new DateTime();
            $exp = new DateTime($user['data_expiracao']);
            $diff = $hoje->diff($exp);
            $user['dias_restantes'] = $exp > $hoje ? $diff->days : -$diff->days;
        } else {
            $user['dias_restantes'] = null;
        }
    }

    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
}

// ============================================
// OBTER USUÁRIO
// ============================================
function obterUsuario($pdo, $currentUserId) {
    $id = $_GET['id'] ?? 0;

    // Usuário comum só pode ver a si mesmo
    if (!isAdmin() && $id != $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id, nome, email, telefone, role, status, plano, data_cadastro, data_expiracao, ultimo_acesso FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }

    echo json_encode(['success' => true, 'usuario' => $usuario]);
}

// ============================================
// CRIAR USUÁRIO (apenas admin)
// ============================================
function criarUsuario($pdo, $currentUserId) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $plano = $_POST['plano'] ?? 'teste';
    $role = $_POST['role'] ?? 'usuario';

    if (!$nome || !$email || !$senha) {
        echo json_encode(['success' => false, 'message' => 'Nome, email e senha são obrigatórios']);
        return;
    }

    // Verificar se email já existe
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
        return;
    }

    // Calcular data de expiração
    $dataExpiracao = null;
    if ($plano === 'teste') {
        $dataExpiracao = date('Y-m-d', strtotime('+7 days'));
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, telefone, role, status, plano, data_expiracao)
        VALUES (?, ?, ?, ?, ?, 'ativo', ?, ?)
    ");
    $stmt->execute([$nome, $email, $senhaHash, $telefone, $role, $plano, $dataExpiracao]);

    $newId = $pdo->lastInsertId();

    // Criar categorias financeiras padrão para o novo usuário
    criarDadosPadrao($pdo, $newId);

    echo json_encode([
        'success' => true,
        'message' => 'Usuário criado com sucesso',
        'id' => $newId,
        'data_expiracao' => $dataExpiracao
    ]);
}

// ============================================
// CRIAR DADOS PADRÃO PARA NOVO USUÁRIO
// ============================================
function criarDadosPadrao($pdo, $userId) {
    // Categorias financeiras padrão
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

    $stmt = $pdo->prepare("INSERT INTO categorias_financeiras (user_id, nome, tipo, cor, icone, ativo) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($categorias as $cat) {
        try {
            $stmt->execute([$userId, $cat[0], $cat[1], $cat[2], $cat[3]]);
        } catch (Exception $e) {
            // Ignora duplicados
        }
    }
}

// ============================================
// ATUALIZAR USUÁRIO (apenas admin)
// ============================================
function atualizarUsuario($pdo, $currentUserId) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $id = $_POST['id'] ?? 0;
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $plano = $_POST['plano'] ?? null;
    $role = $_POST['role'] ?? null;
    $novaSenha = trim($_POST['nova_senha'] ?? '');

    if (!$id || !$nome || !$email) {
        echo json_encode(['success' => false, 'message' => 'Dados obrigatórios faltando']);
        return;
    }

    // Verificar se email já existe para outro usuário
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $check->execute([$email, $id]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso por outro usuário']);
        return;
    }

    // Atualizar campos base
    $sql = "UPDATE usuarios SET nome = ?, email = ?, telefone = ?";
    $params = [$nome, $email, $telefone];

    if ($plano) {
        $sql .= ", plano = ?";
        $params[] = $plano;

        // Recalcular expiração se mudou para teste
        if ($plano === 'teste') {
            $sql .= ", data_expiracao = ?";
            $params[] = date('Y-m-d', strtotime('+7 days'));
        } elseif (in_array($plano, ['mensal', 'anual', 'vitalicio'])) {
            $sql .= ", data_expiracao = NULL, status = 'ativo'";
        }
    }

    if ($role) {
        $sql .= ", role = ?";
        $params[] = $role;
    }

    if ($novaSenha) {
        $sql .= ", senha = ?";
        $params[] = password_hash($novaSenha, PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    $pdo->prepare($sql)->execute($params);

    echo json_encode(['success' => true, 'message' => 'Usuário atualizado']);
}

// ============================================
// BLOQUEAR / DESBLOQUEAR USUÁRIO
// ============================================
function toggleStatus($pdo, $currentUserId) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $id = $_POST['id'] ?? 0;

    if ($id == $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Não é possível bloquear a si mesmo']);
        return;
    }

    $stmt = $pdo->prepare("SELECT status FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }

    $novoStatus = ($user['status'] === 'ativo') ? 'bloqueado' : 'ativo';
    $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?")->execute([$novoStatus, $id]);

    echo json_encode(['success' => true, 'message' => "Usuário {$novoStatus}", 'novo_status' => $novoStatus]);
}

// ============================================
// DELETAR USUÁRIO E TODOS OS SEUS DADOS
// ============================================
function deletarUsuario($pdo, $currentUserId) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $id = $_POST['id'] ?? 0;

    if ($id == $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Não é possível deletar a si mesmo']);
        return;
    }

    if ($id == 1) {
        echo json_encode(['success' => false, 'message' => 'Não é possível deletar o administrador principal']);
        return;
    }

    // Buscar nome para confirmação
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
        return;
    }

    $pdo->beginTransaction();

    try {
        // Deletar TODOS os dados do usuário em ordem (respeitando FKs)
        // 1. Custos de eventos (depende de eventos)
        $pdo->prepare("DELETE FROM custos_eventos WHERE user_id = ?")->execute([$id]);

        // 2. Serviços de eventos (evento_servicos depende de eventos)
        $pdo->prepare("DELETE es FROM evento_servicos es INNER JOIN eventos_eventpro e ON es.evento_id = e.id WHERE e.user_id = ?")->execute([$id]);

        // 3. Movimentações financeiras
        $pdo->prepare("DELETE FROM movimentacoes_financeiras WHERE user_id = ?")->execute([$id]);

        // 4. Eventos
        $pdo->prepare("DELETE FROM eventos_eventpro WHERE user_id = ?")->execute([$id]);

        // 5. Clientes
        $pdo->prepare("DELETE FROM clientes_eventpro WHERE user_id = ?")->execute([$id]);

        // 6. Serviços
        $pdo->prepare("DELETE FROM servicos_eventpro WHERE user_id = ?")->execute([$id]);

        // 7. Contas bancárias
        $pdo->prepare("DELETE FROM contas_bancarias WHERE user_id = ?")->execute([$id]);

        // 8. Custos da empresa
        $pdo->prepare("DELETE FROM custos_empresa WHERE user_id = ?")->execute([$id]);

        // 9. Contas a pagar
        $pdo->prepare("DELETE FROM contas_pagar_empresa WHERE user_id = ?")->execute([$id]);

        // 10. Categorias financeiras
        $pdo->prepare("DELETE FROM categorias_financeiras WHERE user_id = ?")->execute([$id]);

        // 11. Finanças pessoais
        $pdo->prepare("DELETE FROM receitas_pessoais WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM despesas_pessoais WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM contas_pessoais WHERE user_id = ?")->execute([$id]);

        // 12. Logs
        $pdo->prepare("DELETE FROM logs_sistema WHERE user_id = ?")->execute([$id]);

        // 13. Por fim, deletar o usuário
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => "Usuário '{$user['nome']}' e todos os seus dados foram removidos permanentemente"
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao deletar: ' . $e->getMessage()]);
    }
}

// ============================================
// ESTATÍSTICAS DE UM USUÁRIO
// ============================================
function estatisticasUsuario($pdo, $currentUserId) {
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        return;
    }

    $id = $_GET['id'] ?? 0;

    $stats = [];

    $queries = [
        'eventos' => "SELECT COUNT(*) FROM eventos_eventpro WHERE user_id = ?",
        'clientes' => "SELECT COUNT(*) FROM clientes_eventpro WHERE user_id = ?",
        'servicos' => "SELECT COUNT(*) FROM servicos_eventpro WHERE user_id = ?",
        'movimentacoes' => "SELECT COUNT(*) FROM movimentacoes_financeiras WHERE user_id = ?",
        'receita_total' => "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes_financeiras WHERE user_id = ? AND tipo = 'receita'",
        'despesa_total' => "SELECT COALESCE(SUM(valor), 0) FROM movimentacoes_financeiras WHERE user_id = ? AND tipo = 'despesa'",
    ];

    foreach ($queries as $key => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $stats[$key] = $stmt->fetchColumn();
    }

    echo json_encode(['success' => true, 'stats' => $stats]);
}
