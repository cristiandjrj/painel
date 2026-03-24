<?php
/**
 * API - CONTAS BANCÁRIAS
 * Gestão completa de contas bancárias com saldos automáticos
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

// Capturar ação
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Debug
error_log("Contas API - Action: $action");
error_log("Contas API - GET: " . print_r($_GET, true));
error_log("Contas API - POST: " . print_r($_POST, true));

try {
    switch ($action) {
        case 'list':
            listarContas($pdo);
            break;
        case 'get':
            obterConta($pdo);
            break;
        case 'create':
            criarConta($pdo);
            break;
        case 'update':
            atualizarConta($pdo);
            break;
        case 'delete':
            excluirConta($pdo);
            break;
        case 'toggle_ativo':
            toggleAtivo($pdo);
            break;
        case 'extrato':
            obterExtrato($pdo);
            break;
        case 'resumo':
            obterResumo($pdo);
            break;
        case 'transferir':
            transferirEntreContas($pdo);
            break;
        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ============================================
// LISTAR CONTAS
// ============================================
function listarContas($pdo) {
    global $userId;
    // Pegar filtros de mês/ano opcionais
    $mes = $_GET['mes'] ?? null;
    $ano = $_GET['ano'] ?? null;

    $stmt = $pdo->prepare("
        SELECT
            id,
            nome,
            banco,
            agencia,
            conta,
            tipo,
            saldo_inicial,
            saldo_atual,
            ativo,
            data_cadastro
        FROM contas_bancarias
        WHERE user_id = ?
        ORDER BY ativo DESC, nome ASC
    ");
    $stmt->execute([$userId]);

    $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totais
    $total_saldo = 0;
    $contas_ativas = 0;

    // Construir filtros de período para movimentações
    $filtroMes = "";
    $filtroAno = "";
    $paramsBase = [];

    if ($mes && $mes !== 'todos') {
        $filtroMes = "AND MONTH(data_movimentacao) = ?";
    }
    if ($ano && $ano !== 'todos') {
        $filtroAno = "AND YEAR(data_movimentacao) = ?";
    }

    foreach ($contas as &$conta) {
        $conta['saldo_inicial'] = floatval($conta['saldo_inicial'] ?? 0);
        $conta['saldo_atual'] = floatval($conta['saldo_atual'] ?? $conta['saldo'] ?? 0);
        $conta['ativo'] = (bool) $conta['ativo'];

        // Calcular total recebido do período (da tabela movimentacoes_financeiras)
        $params = [$conta['id']];
        if ($mes && $mes !== 'todos') $params[] = intval($mes);
        if ($ano && $ano !== 'todos') $params[] = intval($ano);

        $stmtRecebido = $pdo->prepare("
            SELECT COALESCE(SUM(valor), 0) as total
            FROM movimentacoes_financeiras
            WHERE conta_id = ? AND tipo = 'receita' {$filtroMes} {$filtroAno}
        ");
        $stmtRecebido->execute($params);
        $conta['total_recebido'] = floatval($stmtRecebido->fetch(PDO::FETCH_ASSOC)['total']);

        // Calcular total pago do período
        $stmtPago = $pdo->prepare("
            SELECT COALESCE(SUM(valor), 0) as total
            FROM movimentacoes_financeiras
            WHERE conta_id = ? AND tipo = 'despesa' {$filtroMes} {$filtroAno}
        ");
        $stmtPago->execute($params);
        $conta['total_pago'] = floatval($stmtPago->fetch(PDO::FETCH_ASSOC)['total']);

        // Saldo do período = recebido - pago
        $conta['saldo'] = $conta['total_recebido'] - $conta['total_pago'];

        if ($conta['ativo']) {
            $total_saldo += $conta['saldo_atual'];
            $contas_ativas++;
        }
    }

    echo json_encode([
        'success' => true,
        'contas' => $contas,
        'resumo' => [
            'total_contas' => count($contas),
            'contas_ativas' => $contas_ativas,
            'saldo_total' => $total_saldo
        ]
    ]);
}

// ============================================
// OBTER CONTA ESPECÍFICA
// ============================================
function obterConta($pdo) {
    global $userId;
    $id = $_GET['id'] ?? 0;

    if (!$id) {
        throw new Exception('ID da conta não informado');
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            nome,
            banco,
            agencia,
            conta,
            tipo,
            saldo_inicial,
            saldo_atual,
            ativo,
            data_cadastro
        FROM contas_bancarias
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$id, $userId]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$conta) {
        throw new Exception('Conta não encontrada');
    }
    
    $conta['saldo_inicial'] = floatval($conta['saldo_inicial']);
    $conta['saldo_atual'] = floatval($conta['saldo_atual']);
    $conta['ativo'] = (bool) $conta['ativo'];
    
    echo json_encode([
        'success' => true,
        'conta' => $conta
    ]);
}

// ============================================
// CRIAR CONTA
// ============================================
function criarConta($pdo) {
    global $userId;
    $data = json_decode(file_get_contents('php://input'), true);

    // Validações
    if (empty($data['nome'])) {
        throw new Exception('Nome da conta é obrigatório');
    }

    $nome = trim($data['nome']);
    $banco = trim($data['banco'] ?? '');
    $agencia = trim($data['agencia'] ?? '');
    $conta = trim($data['conta'] ?? '');
    $tipo = $data['tipo'] ?? 'corrente';
    $saldo_inicial = floatval($data['saldo_inicial'] ?? 0);

    // Verificar se já existe conta com mesmo nome para este usuário
    $stmtCheck = $pdo->prepare("SELECT id FROM contas_bancarias WHERE nome = ? AND user_id = ?");
    $stmtCheck->execute([$nome, $userId]);
    if ($stmtCheck->fetch()) {
        throw new Exception('Já existe uma conta com este nome');
    }

    // Inserir
    $stmt = $pdo->prepare("
        INSERT INTO contas_bancarias (
            user_id, nome, banco, agencia, conta, tipo, saldo_inicial, saldo_atual, ativo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)
    ");

    $stmt->execute([
        $userId,
        $nome,
        $banco,
        $agencia,
        $conta,
        $tipo,
        $saldo_inicial,
        $saldo_inicial // Saldo atual começa igual ao inicial
    ]);
    
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Conta criada com sucesso!',
        'id' => $id
    ]);
}

// ============================================
// ATUALIZAR CONTA
// ============================================
function atualizarConta($pdo) {
    global $userId;
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'] ?? 0;

    if (!$id) {
        throw new Exception('ID da conta não informado');
    }

    // Buscar conta atual
    $stmtConta = $pdo->prepare("SELECT saldo_inicial, saldo_atual FROM contas_bancarias WHERE id = ? AND user_id = ?");
    $stmtConta->execute([$id, $userId]);
    $contaAtual = $stmtConta->fetch(PDO::FETCH_ASSOC);
    
    if (!$contaAtual) {
        throw new Exception('Conta não encontrada');
    }
    
    $nome = trim($data['nome']);
    $banco = trim($data['banco'] ?? '');
    $agencia = trim($data['agencia'] ?? '');
    $conta = trim($data['conta'] ?? '');
    $tipo = $data['tipo'] ?? 'corrente';
    $saldo_inicial = floatval($data['saldo_inicial'] ?? 0);
    
    // Se mudou o saldo inicial, ajustar o saldo atual proporcionalmente
    $saldo_inicial_antigo = floatval($contaAtual['saldo_inicial']);
    $saldo_atual_antigo = floatval($contaAtual['saldo_atual']);
    $diferenca = $saldo_inicial - $saldo_inicial_antigo;
    $novo_saldo_atual = $saldo_atual_antigo + $diferenca;
    
    $stmt = $pdo->prepare("
        UPDATE contas_bancarias SET
            nome = ?,
            banco = ?,
            agencia = ?,
            conta = ?,
            tipo = ?,
            saldo_inicial = ?,
            saldo_atual = ?
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([
        $nome,
        $banco,
        $agencia,
        $conta,
        $tipo,
        $saldo_inicial,
        $novo_saldo_atual,
        $id,
        $userId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conta atualizada com sucesso!'
    ]);
}

// ============================================
// EXCLUIR CONTA
// ============================================
function excluirConta($pdo) {
    global $userId;
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;

    if (!$id) {
        throw new Exception('ID da conta não informado');
    }

    // Verificar se tem movimentações
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) as total FROM (
            SELECT id FROM receitas WHERE conta_id = ?
            UNION ALL
            SELECT id FROM custos_empresa WHERE conta_id = ?
        ) as movimentacoes
    ");
    $stmtCheck->execute([$id, $id]);
    $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($result['total'] > 0) {
        throw new Exception('Não é possível excluir conta com movimentações. Desative a conta ao invés de excluir.');
    }

    $stmt = $pdo->prepare("DELETE FROM contas_bancarias WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Conta excluída com sucesso!'
    ]);
}

// ============================================
// ATIVAR/DESATIVAR CONTA
// ============================================
function toggleAtivo($pdo) {
    global $userId;
    $id = $_POST['id'] ?? 0;

    if (!$id) {
        throw new Exception('ID da conta não informado');
    }

    $stmt = $pdo->prepare("
        UPDATE contas_bancarias
        SET ativo = NOT ativo
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$id, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso!'
    ]);
}

// ============================================
// OBTER EXTRATO DA CONTA
// ============================================
function obterExtrato($pdo) {
    global $userId;
    $conta_id = $_GET['conta_id'] ?? 0;
    $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
    $data_fim = $_GET['data_fim'] ?? date('Y-m-t');

    if (!$conta_id) {
        throw new Exception('ID da conta não informado');
    }

    // Verificar se a conta pertence ao usuário
    $stmtCheck = $pdo->prepare("SELECT id FROM contas_bancarias WHERE id = ? AND user_id = ?");
    $stmtCheck->execute([$conta_id, $userId]);
    if (!$stmtCheck->fetch()) {
        throw new Exception('Conta não encontrada');
    }
    
    // Buscar receitas da tabela receitas
    $stmtReceitas = $pdo->prepare("
        SELECT 
            'receita' as tipo,
            r.id,
            r.descricao,
            r.valor,
            r.data_pagamento as data,
            r.forma_pagamento,
            c.nome as cliente_nome
        FROM receitas r
        INNER JOIN clientes_eventpro c ON r.cliente_id = c.id
        WHERE r.conta_id = ? 
        AND r.status = 'pago'
        AND r.data_pagamento BETWEEN ? AND ?
        ORDER BY r.data_pagamento DESC
    ");
    $stmtReceitas->execute([$conta_id, $data_inicio, $data_fim]);
    $receitas = $stmtReceitas->fetchAll(PDO::FETCH_ASSOC);
    
    // NOVO: Buscar também transações bancárias (sinais de clientes)
    $transacoesBancarias = [];
    try {
        $stmtTransacoes = $pdo->prepare("
            SELECT 
                'receita' as tipo,
                id,
                descricao,
                valor,
                data_transacao as data,
                forma_pagamento,
                '' as cliente_nome
            FROM transacoes_bancarias
            WHERE conta_id = ? 
            AND tipo = 'entrada'
            AND data_transacao BETWEEN ? AND ?
            ORDER BY data_transacao DESC
        ");
        $stmtTransacoes->execute([$conta_id, $data_inicio, $data_fim]);
        $transacoesBancarias = $stmtTransacoes->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Tabela transacoes_bancarias pode não existir
    }
    
    // Mesclar receitas com transações bancárias
    $receitas = array_merge($receitas, $transacoesBancarias);
    
    // Buscar despesas
    $stmtDespesas = $pdo->prepare("
        SELECT 
            'despesa' as tipo,
            id,
            descricao,
            valor,
            data_pagamento as data,
            forma_pagamento,
            '' as cliente_nome
        FROM custos_empresa
        WHERE conta_id = ? 
        AND status = 'pago'
        AND data_pagamento BETWEEN ? AND ?
        ORDER BY data_pagamento DESC
    ");
    $stmtDespesas->execute([$conta_id, $data_inicio, $data_fim]);
    $despesas = $stmtDespesas->fetchAll(PDO::FETCH_ASSOC);
    
    // Juntar e ordenar
    $movimentacoes = array_merge($receitas, $despesas);
    usort($movimentacoes, function($a, $b) {
        return strtotime($b['data']) - strtotime($a['data']);
    });
    
    // Calcular saldo
    $total_receitas = array_sum(array_column($receitas, 'valor'));
    $total_despesas = array_sum(array_column($despesas, 'valor'));
    
    echo json_encode([
        'success' => true,
        'movimentacoes' => $movimentacoes,
        'resumo' => [
            'total_receitas' => floatval($total_receitas),
            'total_despesas' => floatval($total_despesas),
            'saldo_periodo' => floatval($total_receitas - $total_despesas)
        ]
    ]);
}

// ============================================
// RESUMO GERAL DAS CONTAS
// ============================================
function obterResumo($pdo) {
    global $userId;
    $mes = $_GET['mes'] ?? date('m');
    $ano = $_GET['ano'] ?? date('Y');

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total_contas,
            SUM(CASE WHEN ativo = TRUE THEN 1 ELSE 0 END) as contas_ativas,
            COALESCE(SUM(CASE WHEN ativo = TRUE THEN saldo_atual ELSE 0 END), 0) as saldo_total
        FROM contas_bancarias
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);

    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'resumo' => [
            'total_contas' => intval($resumo['total_contas']),
            'contas_ativas' => intval($resumo['contas_ativas']),
            'saldo_total' => floatval($resumo['saldo_total'])
        ]
    ]);
}

// ============================================
// TRANSFERIR ENTRE CONTAS
// ============================================
function transferirEntreContas($pdo) {
    global $userId;
    $data = json_decode(file_get_contents('php://input'), true);

    $conta_origem_id = $data['conta_origem_id'] ?? 0;
    $conta_destino_id = $data['conta_destino_id'] ?? 0;
    $valor = floatval($data['valor'] ?? 0);
    $descricao = trim($data['descricao'] ?? 'Transferência entre contas');

    if (!$conta_origem_id || !$conta_destino_id) {
        throw new Exception('Contas de origem e destino são obrigatórias');
    }

    if ($conta_origem_id === $conta_destino_id) {
        throw new Exception('Conta de origem e destino não podem ser iguais');
    }

    if ($valor <= 0) {
        throw new Exception('Valor deve ser maior que zero');
    }

    // Verificar se conta origem tem saldo e pertence ao usuário
    $stmtOrigem = $pdo->prepare("SELECT saldo_atual FROM contas_bancarias WHERE id = ? AND user_id = ?");
    $stmtOrigem->execute([$conta_origem_id, $userId]);
    $contaOrigem = $stmtOrigem->fetch(PDO::FETCH_ASSOC);
    
    if (!$contaOrigem) {
        throw new Exception('Conta de origem não encontrada');
    }
    
    if (floatval($contaOrigem['saldo_atual']) < $valor) {
        throw new Exception('Saldo insuficiente na conta de origem');
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Debitar da origem
        $stmtDebito = $pdo->prepare("
            UPDATE contas_bancarias
            SET saldo_atual = saldo_atual - ?
            WHERE id = ? AND user_id = ?
        ");
        $stmtDebito->execute([$valor, $conta_origem_id, $userId]);

        // Creditar no destino
        $stmtCredito = $pdo->prepare("
            UPDATE contas_bancarias
            SET saldo_atual = saldo_atual + ?
            WHERE id = ? AND user_id = ?
        ");
        $stmtCredito->execute([$valor, $conta_destino_id, $userId]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Transferência realizada com sucesso!'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception('Erro ao realizar transferência: ' . $e->getMessage());
    }
}
