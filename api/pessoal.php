<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

try {
    require_once '../config/database.php';
    require_once '../config/auth.php';

    $userId = requireAuth();

    $action = $_GET['action'] ?? '';

    // Para POST com JSON body
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        // Receitas
        case 'listarReceitas': listarReceitas($pdo); break;
        case 'criarReceita': criarReceita($pdo, $input); break;
        case 'excluirReceita': excluirReceita($pdo, $input); break;

        // Despesas
        case 'listarDespesas': listarDespesas($pdo); break;
        case 'criarDespesa': criarDespesa($pdo, $input); break;
        case 'excluirDespesa': excluirDespesa($pdo, $input); break;

        // Contas/Cartões
        case 'listarContas': listarContas($pdo); break;
        case 'criarConta': criarConta($pdo, $input); break;
        case 'editarConta': editarConta($pdo, $input); break;
        case 'excluirConta': excluirConta($pdo, $input); break;

        // Contas a Pagar
        case 'listarContasPagar': listarContasPagar($pdo); break;
        case 'criarContaPagar': criarContaPagar($pdo, $input); break;
        case 'editarContaPagar': editarContaPagar($pdo, $input); break;
        case 'excluirContaPagar': excluirContaPagar($pdo, $input); break;
        case 'marcarPaga': marcarPaga($pdo, $input); break;
        case 'desmarcarPaga': desmarcarPaga($pdo, $input); break;

        default:
            echo json_encode(['success' => false, 'error' => 'Ação inválida: ' . $action]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// ============================================
// RECEITAS PESSOAIS
// ============================================
function listarReceitas($pdo) {
    global $userId;
    $stmt = $pdo->prepare("SELECT * FROM receitas_pessoais WHERE user_id = ? ORDER BY data DESC, id DESC");
    $stmt->execute([$userId]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $dados]);
}

function criarReceita($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("
        INSERT INTO receitas_pessoais (user_id, descricao, categoria, valor, data, conta_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $input['descricao'],
        $input['categoria'],
        $input['valor'],
        $input['data'],
        $input['conta_id'] ?: null
    ]);

    // Atualizar saldo da conta se informada
    if (!empty($input['conta_id'])) {
        atualizarSaldoConta($pdo, $input['conta_id'], $input['valor']);
    }

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

function excluirReceita($pdo, $input) {
    global $userId;
    // Buscar receita para reverter saldo
    $stmt = $pdo->prepare("SELECT conta_id, valor FROM receitas_pessoais WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    $receita = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($receita && $receita['conta_id']) {
        atualizarSaldoConta($pdo, $receita['conta_id'], -$receita['valor']);
    }

    $stmt = $pdo->prepare("DELETE FROM receitas_pessoais WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    echo json_encode(['success' => true]);
}

// ============================================
// DESPESAS PESSOAIS
// ============================================
function listarDespesas($pdo) {
    global $userId;
    $stmt = $pdo->prepare("SELECT * FROM despesas_pessoais WHERE user_id = ? ORDER BY data DESC, id DESC");
    $stmt->execute([$userId]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $dados]);
}

function criarDespesa($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("
        INSERT INTO despesas_pessoais (user_id, descricao, categoria, valor, data, conta_id, observacoes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $input['descricao'],
        $input['categoria'],
        $input['valor'],
        $input['data'],
        $input['conta_id'] ?: null,
        $input['observacoes'] ?? null
    ]);

    // Atualizar saldo da conta se informada
    if (!empty($input['conta_id'])) {
        atualizarSaldoConta($pdo, $input['conta_id'], -$input['valor']);
    }

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

function excluirDespesa($pdo, $input) {
    global $userId;
    // Buscar despesa para reverter saldo
    $stmt = $pdo->prepare("SELECT conta_id, valor FROM despesas_pessoais WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    $despesa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($despesa && $despesa['conta_id']) {
        atualizarSaldoConta($pdo, $despesa['conta_id'], $despesa['valor']);
    }

    $stmt = $pdo->prepare("DELETE FROM despesas_pessoais WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    echo json_encode(['success' => true]);
}

// ============================================
// CONTAS/CARTÕES PESSOAIS
// ============================================
function listarContas($pdo) {
    global $userId;
    $stmt = $pdo->prepare("SELECT * FROM contas_pessoais WHERE user_id = ? ORDER BY nome ASC");
    $stmt->execute([$userId]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $dados]);
}

function criarConta($pdo, $input) {
    global $userId;
    $saldoInicial = floatval($input['saldo_inicial'] ?? 0);
    $stmt = $pdo->prepare("
        INSERT INTO contas_pessoais (user_id, nome, tipo, saldo_inicial, saldo_atual, banco, ativa)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([
        $userId,
        $input['nome'],
        $input['tipo'],
        $saldoInicial,
        $saldoInicial,
        $input['banco'] ?? null
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

function editarConta($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("
        UPDATE contas_pessoais SET nome = ?, tipo = ?, saldo_atual = ?, banco = ? WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $input['nome'],
        $input['tipo'],
        floatval($input['saldo_inicial'] ?? 0),
        $input['banco'] ?? null,
        $input['id'],
        $userId
    ]);
    echo json_encode(['success' => true]);
}

function excluirConta($pdo, $input) {
    global $userId;
    // Desvincular transações
    $pdo->prepare("UPDATE receitas_pessoais SET conta_id = NULL WHERE conta_id = ? AND user_id = ?")->execute([$input['id'], $userId]);
    $pdo->prepare("UPDATE despesas_pessoais SET conta_id = NULL WHERE conta_id = ? AND user_id = ?")->execute([$input['id'], $userId]);

    $stmt = $pdo->prepare("DELETE FROM contas_pessoais WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    echo json_encode(['success' => true]);
}

function atualizarSaldoConta($pdo, $contaId, $valor) {
    $stmt = $pdo->prepare("UPDATE contas_pessoais SET saldo_atual = saldo_atual + ? WHERE id = ?");
    $stmt->execute([$valor, $contaId]);
}

// ============================================
// CONTAS A PAGAR
// ============================================
function listarContasPagar($pdo) {
    global $userId;
    $stmt = $pdo->prepare("SELECT * FROM contas_pagar_pessoais WHERE user_id = ? ORDER BY vencimento ASC, id DESC");
    $stmt->execute([$userId]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $dados]);
}

function criarContaPagar($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("
        INSERT INTO contas_pagar_pessoais (user_id, descricao, tipo, valor, vencimento, recorrencia, observacoes, notificar, paga)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
    ");
    $stmt->execute([
        $userId,
        $input['descricao'],
        $input['tipo'],
        $input['valor'],
        $input['vencimento'],
        $input['recorrencia'] ?? 'unica',
        $input['observacoes'] ?? null,
        $input['notificar'] ?? 1
    ]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
}

function editarContaPagar($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("
        UPDATE contas_pagar_pessoais
        SET descricao = ?, tipo = ?, valor = ?, vencimento = ?, recorrencia = ?, observacoes = ?, notificar = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $input['descricao'],
        $input['tipo'],
        $input['valor'],
        $input['vencimento'],
        $input['recorrencia'] ?? 'unica',
        $input['observacoes'] ?? null,
        $input['notificar'] ?? 1,
        $input['id'],
        $userId
    ]);
    echo json_encode(['success' => true]);
}

function excluirContaPagar($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("DELETE FROM contas_pagar_pessoais WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    echo json_encode(['success' => true]);
}

function marcarPaga($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("UPDATE contas_pagar_pessoais SET paga = 1, data_pagamento = CURDATE() WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    echo json_encode(['success' => true]);
}

function desmarcarPaga($pdo, $input) {
    global $userId;
    $stmt = $pdo->prepare("UPDATE contas_pagar_pessoais SET paga = 0, data_pagamento = NULL WHERE id = ? AND user_id = ?");
    $stmt->execute([$input['id'], $userId]);
    echo json_encode(['success' => true]);
}
