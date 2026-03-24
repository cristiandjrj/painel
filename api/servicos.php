<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
error_reporting(0);

try {
    require_once '../config/database.php';
    require_once '../config/auth.php';

    $userId = requireAuth();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'list':
            listarServicos($pdo, $userId);
            break;
        case 'get':
            buscarServico($pdo, $userId);
            break;
        case 'create':
            criarServico($pdo, $userId);
            break;
        case 'update':
            atualizarServico($pdo, $userId);
            break;
        case 'delete':
            excluirServico($pdo, $userId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listarServicos($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT *, valor_padrao AS valor_base FROM servicos_eventpro WHERE ativo = 1 AND user_id = ? ORDER BY nome ASC");
        $stmt->execute([$userId]);
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'servicos' => $servicos]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao listar serviços']);
    }
}

function buscarServico($pdo, $userId) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT *, valor_padrao AS valor_base FROM servicos_eventpro WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($servico) {
            echo json_encode(['success' => true, 'servico' => $servico]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Serviço não encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar serviço']);
    }
}

function criarServico($pdo, $userId) {
    try {
        // Aceitar tanto 'valor' quanto 'valor_base'
        $valor = floatval($_POST['valor'] ?? $_POST['valor_base'] ?? 0);

        $stmt = $pdo->prepare("
            INSERT INTO servicos_eventpro (user_id, nome, categoria, descricao, valor_padrao, ativo)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([
            $userId,
            $_POST['nome'],
            $_POST['categoria'] ?? 'Outros',
            $_POST['descricao'] ?? null,
            $valor
        ]);
        
        $id = $pdo->lastInsertId();
        
        // Verificar se salvou corretamente
        $check = $pdo->prepare("SELECT valor_padrao FROM servicos_eventpro WHERE id = ?");
        $check->execute([$id]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Serviço criado',
            'id' => $id,
            'valor_salvo' => $result['valor_padrao']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar serviço: ' . $e->getMessage()]);
    }
}

function atualizarServico($pdo, $userId) {
    try {
        // Aceitar tanto 'valor' quanto 'valor_base'
        $valor = floatval($_POST['valor'] ?? $_POST['valor_base'] ?? 0);

        $stmt = $pdo->prepare("
            UPDATE servicos_eventpro SET nome = ?, categoria = ?, descricao = ?, valor_padrao = ? WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([
            $_POST['nome'],
            $_POST['categoria'] ?? 'Outros',
            $_POST['descricao'] ?? null,
            $valor,
            $_POST['id'],
            $userId
        ]);
        
        // Verificar se atualizou
        $check = $pdo->prepare("SELECT valor_padrao FROM servicos_eventpro WHERE id = ?");
        $check->execute([$_POST['id']]);
        $result = $check->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Serviço atualizado',
            'valor_atualizado' => $result['valor_padrao']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar serviço: ' . $e->getMessage()]);
    }
}

function excluirServico($pdo, $userId) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }
    
    try {
        // Soft delete
        $stmt = $pdo->prepare("UPDATE servicos_eventpro SET ativo = 0 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        echo json_encode(['success' => true, 'message' => 'Serviço excluído']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir serviço']);
    }
}
