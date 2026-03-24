<?php
/**
 * MIDDLEWARE DE AUTENTICAÇÃO
 * Incluir este arquivo em todas as APIs e páginas que precisam de autenticação.
 * Fornece funções para verificar sessão, obter user_id e validar plano.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retorna o user_id da sessão atual ou null se não logado
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Retorna o role do usuário atual
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? 'usuario';
}

/**
 * Verifica se o usuário é admin
 */
function isAdmin() {
    return getUserRole() === 'admin';
}

/**
 * Verifica se o usuário está logado. Se não, retorna JSON de erro (para APIs).
 */
function requireAuth() {
    if (!getUserId()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Não autorizado', 'redirect' => '../login.php']);
        exit;
    }
    return getUserId();
}

/**
 * Verifica se o usuário está logado. Se não, redireciona para login (para páginas).
 */
function requireAuthPage() {
    if (!getUserId()) {
        header('Location: ../login.php');
        exit;
    }
    return getUserId();
}

/**
 * Verifica se o plano do usuário está ativo (não expirado/bloqueado).
 * Precisa do $pdo para consultar o banco.
 */
function checkPlanActive($pdo, $userId = null) {
    $userId = $userId ?? getUserId();
    if (!$userId) return false;

    try {
        $stmt = $pdo->prepare("SELECT status, plano, data_expiracao FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;
        if ($user['status'] === 'bloqueado') return false;

        // Verificar expiração do plano teste
        if ($user['plano'] === 'teste' && $user['data_expiracao']) {
            if (date('Y-m-d') > $user['data_expiracao']) {
                // Atualizar status para expirado
                $pdo->prepare("UPDATE usuarios SET status = 'expirado' WHERE id = ?")->execute([$userId]);
                return false;
            }
        }

        if ($user['status'] === 'expirado') return false;

        return true;
    } catch (Exception $e) {
        return true; // Em caso de erro, permite acesso (fail-open para não travar o admin)
    }
}

/**
 * Atualiza último acesso do usuário
 */
function updateLastAccess($pdo, $userId = null) {
    $userId = $userId ?? getUserId();
    if (!$userId) return;

    try {
        $pdo->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?")->execute([$userId]);
    } catch (Exception $e) {
        // Silencioso
    }
}
