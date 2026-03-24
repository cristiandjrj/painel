<?php
/**
 * API - RECEITAS (Parcelas de Clientes)
 * Gestão completa de parcelas com atualização automática de saldos
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/database.php';

// Capturar ação
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listarReceitas($pdo);
            break;
        case 'get':
            obterReceita($pdo);
            break;
        case 'create':
            criarReceita($pdo);
            break;
        case 'create_parcelas':
            criarParcelas($pdo);
            break;
        case 'update':
            atualizarReceita($pdo);
            break;
        case 'delete':
            excluirReceita($pdo);
            break;
        case 'marcar_pago':
            marcarComoPago($pdo);
            break;
        case 'marcar_pendente':
            marcarComoPendente($pdo);
            break;
        case 'por_cliente':
            listarPorCliente($pdo);
            break;
        case 'resumo':
            obterResumo($pdo);
            break;
        case 'atrasadas':
            listarAtrasadas($pdo);
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
// LISTAR RECEITAS
// ============================================
function listarReceitas($pdo) {
    $filtro_status = $_GET['status'] ?? '';
    $filtro_cliente = $_GET['cliente_id'] ?? '';
    $mes = $_GET['mes'] ?? '';
    $ano = $_GET['ano'] ?? '';
    
    $sql = "
        SELECT 
            r.id,
            r.cliente_id,
            c.nome as cliente_nome,
            c.telefone as cliente_telefone,
            r.evento_id,
            e.tipo as evento_tipo,
            r.conta_id,
            cb.nome as conta_nome,
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
            END as status_real,
            CASE 
                WHEN r.status = 'pendente' AND r.data_vencimento < CURDATE() 
                THEN DATEDIFF(CURDATE(), r.data_vencimento)
                ELSE 0
            END as dias_atraso
        FROM receitas r
        INNER JOIN clientes_eventpro c ON r.cliente_id = c.id
        LEFT JOIN eventos_eventpro e ON r.evento_id = e.id
        LEFT JOIN contas_bancarias cb ON r.conta_id = cb.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($filtro_status) {
        $sql .= " AND r.status = ?";
        $params[] = $filtro_status;
    }
    
    if ($filtro_cliente) {
        $sql .= " AND r.cliente_id = ?";
        $params[] = $filtro_cliente;
    }
    
    if ($mes && $ano) {
        $sql .= " AND MONTH(r.data_vencimento) = ? AND YEAR(r.data_vencimento) = ?";
        $params[] = $mes;
        $params[] = $ano;
    }
    
    $sql .= " ORDER BY r.data_vencimento DESC, r.id DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Converter valores
    foreach ($receitas as &$receita) {
        $receita['valor'] = floatval($receita['valor']);
        $receita['dias_atraso'] = intval($receita['dias_atraso']);
    }
    
    // Calcular totais
    $total_valor = array_sum(array_column($receitas, 'valor'));
    $total_pago = array_sum(array_map(function($r) {
        return $r['status'] === 'pago' ? $r['valor'] : 0;
    }, $receitas));
    $total_pendente = $total_valor - $total_pago;
    
    echo json_encode([
        'success' => true,
        'receitas' => $receitas,
        'resumo' => [
            'total_receitas' => count($receitas),
            'total_valor' => $total_valor,
            'total_pago' => $total_pago,
            'total_pendente' => $total_pendente
        ]
    ]);
}

// ============================================
// OBTER RECEITA ESPECÍFICA
// ============================================
function obterReceita($pdo) {
    $id = $_GET['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('ID da receita não informado');
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            c.nome as cliente_nome,
            e.tipo as evento_tipo,
            cb.nome as conta_nome
        FROM receitas r
        INNER JOIN clientes_eventpro c ON r.cliente_id = c.id
        LEFT JOIN eventos_eventpro e ON r.evento_id = e.id
        LEFT JOIN contas_bancarias cb ON r.conta_id = cb.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$id]);
    $receita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$receita) {
        throw new Exception('Receita não encontrada');
    }
    
    $receita['valor'] = floatval($receita['valor']);
    
    echo json_encode([
        'success' => true,
        'receita' => $receita
    ]);
}

// ============================================
// CRIAR RECEITA ÚNICA
// ============================================
function criarReceita($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validações
    if (empty($data['cliente_id'])) {
        throw new Exception('Cliente é obrigatório');
    }
    if (empty($data['descricao'])) {
        throw new Exception('Descrição é obrigatória');
    }
    if (empty($data['valor']) || $data['valor'] <= 0) {
        throw new Exception('Valor deve ser maior que zero');
    }
    if (empty($data['data_vencimento'])) {
        throw new Exception('Data de vencimento é obrigatória');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO receitas (
            cliente_id, evento_id, conta_id, descricao, valor,
            data_vencimento, data_pagamento, status, numero_parcela,
            total_parcelas, forma_pagamento, observacoes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $data['cliente_id'],
        $data['evento_id'] ?? null,
        $data['conta_id'] ?? null,
        $data['descricao'],
        $data['valor'],
        $data['data_vencimento'],
        $data['data_pagamento'] ?? null,
        $data['status'] ?? 'pendente',
        $data['numero_parcela'] ?? 1,
        $data['total_parcelas'] ?? 1,
        $data['forma_pagamento'] ?? null,
        $data['observacoes'] ?? null
    ]);
    
    $id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Receita criada com sucesso!',
        'id' => $id
    ]);
}

// ============================================
// CRIAR MÚLTIPLAS PARCELAS
// ============================================
function criarParcelas($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validações
    if (empty($data['cliente_id'])) {
        throw new Exception('Cliente é obrigatório');
    }
    if (empty($data['descricao_base'])) {
        throw new Exception('Descrição é obrigatória');
    }
    if (empty($data['valor_total']) || $data['valor_total'] <= 0) {
        throw new Exception('Valor total deve ser maior que zero');
    }
    if (empty($data['quantidade_parcelas']) || $data['quantidade_parcelas'] < 1) {
        throw new Exception('Quantidade de parcelas inválida');
    }
    if (empty($data['data_primeira_parcela'])) {
        throw new Exception('Data da primeira parcela é obrigatória');
    }
    
    $quantidade = intval($data['quantidade_parcelas']);
    $valor_total = floatval($data['valor_total']);
    $valor_parcela = $valor_total / $quantidade;
    $data_base = new DateTime($data['data_primeira_parcela']);
    
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO receitas (
                cliente_id, evento_id, conta_id, descricao, valor,
                data_vencimento, status, numero_parcela, total_parcelas,
                forma_pagamento, observacoes
            ) VALUES (?, ?, ?, ?, ?, ?, 'pendente', ?, ?, ?, ?)
        ");
        
        $parcelas_criadas = [];
        
        for ($i = 1; $i <= $quantidade; $i++) {
            $descricao = $data['descricao_base'] . " - Parcela $i/$quantidade";
            $data_vencimento = clone $data_base;
            
            // Adicionar meses (não dias, para manter sempre o mesmo dia do mês)
            if ($i > 1) {
                $data_vencimento->modify('+' . ($i - 1) . ' month');
            }
            
            $stmt->execute([
                $data['cliente_id'],
                $data['evento_id'] ?? null,
                $data['conta_id'] ?? null,
                $descricao,
                $valor_parcela,
                $data_vencimento->format('Y-m-d'),
                $i,
                $quantidade,
                $data['forma_pagamento'] ?? null,
                $data['observacoes'] ?? null
            ]);
            
            $parcelas_criadas[] = [
                'id' => $pdo->lastInsertId(),
                'numero' => $i,
                'valor' => $valor_parcela,
                'data_vencimento' => $data_vencimento->format('Y-m-d')
            ];
        }
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "$quantidade parcelas criadas com sucesso!",
            'parcelas' => $parcelas_criadas
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception('Erro ao criar parcelas: ' . $e->getMessage());
    }
}

// ============================================
// ATUALIZAR RECEITA
// ============================================
function atualizarReceita($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('ID da receita não informado');
    }
    
    $stmt = $pdo->prepare("
        UPDATE receitas SET
            descricao = ?,
            valor = ?,
            data_vencimento = ?,
            forma_pagamento = ?,
            observacoes = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data['descricao'],
        $data['valor'],
        $data['data_vencimento'],
        $data['forma_pagamento'] ?? null,
        $data['observacoes'] ?? null,
        $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Receita atualizada com sucesso!'
    ]);
}

// ============================================
// EXCLUIR RECEITA
// ============================================
function excluirReceita($pdo) {
    $id = $_GET['id'] ?? $_POST['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('ID da receita não informado');
    }
    
    // Verificar se está paga (o trigger cuidará do saldo)
    $stmtCheck = $pdo->prepare("SELECT status, conta_id FROM receitas WHERE id = ?");
    $stmtCheck->execute([$id]);
    $receita = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($receita['status'] === 'pago' && $receita['conta_id']) {
        // O trigger tr_receita_excluir vai remover do saldo automaticamente
    }
    
    $stmt = $pdo->prepare("DELETE FROM receitas WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Receita excluída com sucesso!'
    ]);
}

// ============================================
// MARCAR COMO PAGO
// ============================================
function marcarComoPago($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = $data['id'] ?? 0;
    $conta_id = $data['conta_id'] ?? null;
    $data_pagamento = $data['data_pagamento'] ?? date('Y-m-d');
    $forma_pagamento = $data['forma_pagamento'] ?? null;
    
    if (!$id) {
        throw new Exception('ID da receita não informado');
    }
    
    if (!$conta_id) {
        throw new Exception('Conta bancária é obrigatória para marcar como pago');
    }
    
    // O trigger tr_receita_pagar vai adicionar ao saldo automaticamente
    $stmt = $pdo->prepare("
        UPDATE receitas SET
            status = 'pago',
            data_pagamento = ?,
            conta_id = ?,
            forma_pagamento = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $data_pagamento,
        $conta_id,
        $forma_pagamento,
        $id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Receita marcada como paga!'
    ]);
}

// ============================================
// MARCAR COMO PENDENTE (ESTORNAR)
// ============================================
function marcarComoPendente($pdo) {
    $id = $_POST['id'] ?? 0;
    
    if (!$id) {
        throw new Exception('ID da receita não informado');
    }
    
    // O trigger tr_receita_pagar vai remover do saldo automaticamente
    $stmt = $pdo->prepare("
        UPDATE receitas SET
            status = 'pendente',
            data_pagamento = NULL
        WHERE id = ?
    ");
    
    $stmt->execute([$id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Receita marcada como pendente!'
    ]);
}

// ============================================
// LISTAR POR CLIENTE
// ============================================
function listarPorCliente($pdo) {
    $cliente_id = $_GET['cliente_id'] ?? 0;
    
    if (!$cliente_id) {
        throw new Exception('ID do cliente não informado');
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            cb.nome as conta_nome,
            e.tipo as evento_tipo
        FROM receitas r
        LEFT JOIN contas_bancarias cb ON r.conta_id = cb.id
        LEFT JOIN eventos_eventpro e ON r.evento_id = e.id
        WHERE r.cliente_id = ?
        ORDER BY r.data_vencimento ASC
    ");
    
    $stmt->execute([$cliente_id]);
    $receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($receitas as &$receita) {
        $receita['valor'] = floatval($receita['valor']);
    }
    
    $total = array_sum(array_column($receitas, 'valor'));
    $pago = array_sum(array_map(function($r) {
        return $r['status'] === 'pago' ? $r['valor'] : 0;
    }, $receitas));
    
    echo json_encode([
        'success' => true,
        'receitas' => $receitas,
        'resumo' => [
            'total' => $total,
            'pago' => $pago,
            'pendente' => $total - $pago
        ]
    ]);
}

// ============================================
// RESUMO GERAL
// ============================================
function obterResumo($pdo) {
    $mes = $_GET['mes'] ?? date('m');
    $ano = $_GET['ano'] ?? date('Y');
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_receitas,
            COALESCE(SUM(valor), 0) as valor_total,
            COALESCE(SUM(CASE WHEN status = 'pago' THEN valor ELSE 0 END), 0) as valor_pago,
            COALESCE(SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END), 0) as valor_pendente,
            COALESCE(SUM(CASE WHEN status = 'pendente' AND data_vencimento < CURDATE() THEN valor ELSE 0 END), 0) as valor_atrasado
        FROM receitas
        WHERE MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ?
    ");
    
    $stmt->execute([$mes, $ano]);
    $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'resumo' => [
            'total_receitas' => intval($resumo['total_receitas']),
            'valor_total' => floatval($resumo['valor_total']),
            'valor_pago' => floatval($resumo['valor_pago']),
            'valor_pendente' => floatval($resumo['valor_pendente']),
            'valor_atrasado' => floatval($resumo['valor_atrasado'])
        ]
    ]);
}

// ============================================
// LISTAR ATRASADAS
// ============================================
function listarAtrasadas($pdo) {
    $stmt = $pdo->query("
        SELECT 
            r.id,
            r.cliente_id,
            c.nome as cliente_nome,
            c.telefone as cliente_telefone,
            r.descricao,
            r.valor,
            r.data_vencimento,
            DATEDIFF(CURDATE(), r.data_vencimento) as dias_atraso
        FROM receitas r
        INNER JOIN clientes_eventpro c ON r.cliente_id = c.id
        WHERE r.status = 'pendente'
        AND r.data_vencimento < CURDATE()
        ORDER BY r.data_vencimento ASC
    ");
    
    $atrasadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($atrasadas as &$receita) {
        $receita['valor'] = floatval($receita['valor']);
        $receita['dias_atraso'] = intval($receita['dias_atraso']);
    }
    
    echo json_encode([
        'success' => true,
        'receitas_atrasadas' => $atrasadas,
        'total_atrasado' => array_sum(array_column($atrasadas, 'valor'))
    ]);
}
