<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

try {
    require_once '../config/database.php';
    require_once '../config/auth.php';

    $userId = requireAuth();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'resumo_financeiro':
            resumoFinanceiro($pdo, $userId);
            break;
        case 'dashboard':
            dashboardFinanceiro($pdo, $userId);
            break;
        case 'transacoes':
            listarTransacoes($pdo, $userId);
            break;
        case 'movimentacoes_list':
            listarMovimentacoesPorConta($pdo, $userId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function resumoFinanceiro($pdo, $userId) {
    $mes = $_GET['mes'] ?? null;
    $ano = $_GET['ano'] ?? null;
    $visualizacao = $_GET['visualizacao'] ?? 'mensal';

    // Se não recebeu mês/ano, usar mês atual
    if (!$mes) $mes = date('m');
    if (!$ano) $ano = date('Y');

    // Montar cláusula WHERE dinâmica (suporta "todos")
    $filtroMes = ($mes !== 'todos') ? "AND MONTH(data_movimentacao) = " . intval($mes) : "";
    $filtroAno = ($ano !== 'todos') ? "AND YEAR(data_movimentacao) = " . intval($ano) : "";
    $filtroMesCusto = ($mes !== 'todos') ? "AND MONTH(data_custo) = " . intval($mes) : "";
    $filtroAnoCusto = ($ano !== 'todos') ? "AND YEAR(data_custo) = " . intval($ano) : "";
    $filtroMesCustoEv = ($mes !== 'todos') ? "AND MONTH(data) = " . intval($mes) : "";
    $filtroAnoCustoEv = ($ano !== 'todos') ? "AND YEAR(data) = " . intval($ano) : "";
    $filtroMesEvento = ($mes !== 'todos') ? "AND MONTH(data_evento) = " . intval($mes) : "";
    $filtroAnoEvento = ($ano !== 'todos') ? "AND YEAR(data_evento) = " . intval($ano) : "";

    try {
        $uid = intval($userId);

        // Receitas: movimentacoes_financeiras + sinais direto de eventos_eventpro
        $totalReceitas = 0;
        try {
            // Receitas de movimentacoes (excluindo sinais que já vêm de eventos_eventpro)
            $stmtReceitas = $pdo->query("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM movimentacoes_financeiras
                WHERE user_id = {$uid} AND tipo = 'receita'
                AND (tipo_movimentacao IS NULL OR tipo_movimentacao != 'sinal_evento')
                AND descricao NOT LIKE 'Sinal - %'
                {$filtroMes} {$filtroAno}
            ");
            $receitas = $stmtReceitas->fetch(PDO::FETCH_ASSOC);
            $totalReceitas = floatval($receitas['total']);
        } catch (Exception $e) {}

        // Sinais de eventos (usar data_sinal com fallback para data_evento)
        try {
            $filtroMesSinal = ($mes !== 'todos') ? "AND MONTH(COALESCE(e.data_sinal, e.data_evento)) = " . intval($mes) : "";
            $filtroAnoSinal = ($ano !== 'todos') ? "AND YEAR(COALESCE(e.data_sinal, e.data_evento)) = " . intval($ano) : "";
            $stmtSinais = $pdo->query("
                SELECT COALESCE(SUM(e.valor_sinal), 0) as total
                FROM eventos_eventpro e
                WHERE e.user_id = {$uid}
                AND e.status != 'cancelado'
                AND e.valor_sinal > 0
                {$filtroMesSinal} {$filtroAnoSinal}
            ");
            $sinais = $stmtSinais->fetch(PDO::FETCH_ASSOC);
            $totalReceitas += floatval($sinais['total']);
        } catch (Exception $e) {}

        // Despesas (movimentações)
        $totalDespesas = 0;
        try {
            $stmtDespesas = $pdo->query("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM movimentacoes_financeiras
                WHERE user_id = {$uid} AND tipo = 'despesa' {$filtroMes} {$filtroAno}
            ");
            $despesas = $stmtDespesas->fetch(PDO::FETCH_ASSOC);
            $totalDespesas = floatval($despesas['total']);
        } catch (Exception $e) {}

        // Custos gerais
        $totalCustosGerais = 0;
        try {
            $stmtCustos = $pdo->query("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM custos_empresa
                WHERE user_id = {$uid} {$filtroMesCusto} {$filtroAnoCusto}
            ");
            $custos = $stmtCustos->fetch(PDO::FETCH_ASSOC);
            $totalCustosGerais = floatval($custos['total']);
        } catch (Exception $e) {}

        // Custos de eventos
        $totalCustosEventos = 0;
        try {
            $stmtCustosEv = $pdo->query("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM custos_eventos
                WHERE user_id = {$uid} {$filtroMesCustoEv} {$filtroAnoCustoEv}
            ");
            $custosEv = $stmtCustosEv->fetch(PDO::FETCH_ASSOC);
            $totalCustosEventos = floatval($custosEv['total']);
        } catch (Exception $e) {}

        // Total saídas
        $totalSaidas = $totalDespesas + $totalCustosGerais + $totalCustosEventos;
        $saldo = $totalReceitas - $totalSaidas;

        // Eventos do período
        $stmtEventos = $pdo->query("
            SELECT COUNT(*) as total
            FROM eventos_eventpro
            WHERE user_id = {$uid} {$filtroMesEvento} {$filtroAnoEvento}
        ");
        $eventos = $stmtEventos->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'resumo' => [
                'receitas' => $totalReceitas,
                'total_receitas' => $totalReceitas,
                'despesas' => $totalDespesas,
                'custos_gerais' => $totalCustosGerais,
                'custos_eventos' => $totalCustosEventos,
                'total_saidas' => $totalSaidas,
                'saldo' => $saldo,
                'eventos' => intval($eventos['total']),
                'mes' => $mes,
                'ano' => $ano
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar resumo financeiro: ' . $e->getMessage()]);
    }
}

function dashboardFinanceiro($pdo, $userId) {
    try {
        $mesAtual = date('m');
        $anoAtual = date('Y');

        // Receitas do mês (de movimentacoes_financeiras)
        $totalReceitas = 0;
        try {
            $stmtReceitas = $pdo->prepare("
                SELECT COALESCE(SUM(valor), 0) as total
                FROM movimentacoes_financeiras
                WHERE user_id = ? AND tipo = 'receita'
                AND MONTH(data_movimentacao) = ? AND YEAR(data_movimentacao) = ?
            ");
            $stmtReceitas->execute([$userId, $mesAtual, $anoAtual]);
            $receitas = $stmtReceitas->fetch(PDO::FETCH_ASSOC);
            $totalReceitas = floatval($receitas['total']);
        } catch (Exception $e) {}

        // Eventos pendentes
        $stmtPendentes = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM eventos_eventpro
            WHERE user_id = ? AND status = 'aguardando'
        ");
        $stmtPendentes->execute([$userId]);
        $pendentes = $stmtPendentes->fetch(PDO::FETCH_ASSOC);

        // Eventos do mês
        $stmtEventos = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM eventos_eventpro
            WHERE user_id = ? AND MONTH(data_evento) = ? AND YEAR(data_evento) = ?
        ");
        $stmtEventos->execute([$userId, $mesAtual, $anoAtual]);
        $eventos = $stmtEventos->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'dashboard' => [
                'receitas_mes' => $totalReceitas,
                'eventos_pendentes' => intval($pendentes['total']),
                'eventos_mes' => intval($eventos['total'])
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar dashboard']);
    }
}

function listarTransacoes($pdo, $userId) {
    $mes = $_GET['mes'] ?? null;
    $ano = $_GET['ano'] ?? null;
    $tipo = $_GET['tipo'] ?? null;

    try {
        $where = ["user_id = ?"];
        $params = [$userId];

        if ($mes && $mes !== 'todos') {
            $where[] = "MONTH(data_movimentacao) = ?";
            $params[] = intval($mes);
        }
        if ($ano && $ano !== 'todos') {
            $where[] = "YEAR(data_movimentacao) = ?";
            $params[] = intval($ano);
        }
        if ($tipo && $tipo !== 'todos') {
            $where[] = "tipo = ?";
            $params[] = $tipo;
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $stmt = $pdo->prepare("
            SELECT
                id,
                descricao,
                valor,
                data_movimentacao as data,
                tipo,
                categoria,
                forma_pagamento,
                tipo_movimentacao,
                conta_id
            FROM movimentacoes_financeiras
            {$whereClause}
            ORDER BY data_movimentacao DESC
            LIMIT 100
        ");
        $stmt->execute($params);
        $transacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'transacoes' => $transacoes]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao listar transações: ' . $e->getMessage()]);
    }
}

function listarMovimentacoesPorConta($pdo, $userId) {
    $contaId = $_GET['conta_id'] ?? null;
    $mes = $_GET['mes'] ?? null;
    $ano = $_GET['ano'] ?? null;

    if (!$contaId) {
        echo json_encode(['success' => false, 'message' => 'conta_id é obrigatório']);
        return;
    }

    try {
        $where = ["user_id = ?", "conta_id = ?"];
        $params = [$userId, intval($contaId)];

        if ($mes && $mes !== 'todos') {
            $where[] = "MONTH(data_movimentacao) = ?";
            $params[] = intval($mes);
        }
        if ($ano && $ano !== 'todos') {
            $where[] = "YEAR(data_movimentacao) = ?";
            $params[] = intval($ano);
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $stmt = $pdo->prepare("
            SELECT
                id,
                descricao,
                valor,
                data_movimentacao,
                tipo,
                categoria,
                forma_pagamento
            FROM movimentacoes_financeiras
            {$whereClause}
            ORDER BY data_movimentacao DESC
            LIMIT 50
        ");
        $stmt->execute($params);
        $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'movimentacoes' => $movimentacoes]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
}
