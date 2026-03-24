<?php
/**
 * API de Contas a Pagar - EventProDJ
 * Gestão de contas com alertas de vencimento
 */

require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // ============================================
        // LISTAR CONTAS A PAGAR
        // ============================================
        
        case 'list':
            $status = $_GET['status'] ?? 'todas';
            $mes = $_GET['mes'] ?? '';
            $ano = $_GET['ano'] ?? date('Y');
            
            $sql = "SELECT * FROM contas_pagar_empresa WHERE user_id = ?";
            $params = [$userId];
            
            // Filtro por status
            if ($status === 'pendentes') {
                $sql .= " AND status = 'pendente'";
            } elseif ($status === 'pagas') {
                $sql .= " AND status = 'paga'";
            } elseif ($status === 'vencidas') {
                $sql .= " AND status != 'paga' AND data_vencimento < CURDATE()";
            } elseif ($status === 'proximas') {
                $sql .= " AND status != 'paga' AND data_vencimento >= CURDATE() AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            }

            // Filtro por mês/ano
            if ($mes && $ano) {
                $sql .= " AND MONTH(data_vencimento) = ? AND YEAR(data_vencimento) = ?";
                $params[] = $mes;
                $params[] = $ano;
            } elseif ($ano) {
                $sql .= " AND YEAR(data_vencimento) = ?";
                $params[] = $ano;
            }

            $sql .= " ORDER BY
                CASE
                    WHEN status = 'paga' THEN 3
                    WHEN status != 'paga' AND data_vencimento < CURDATE() THEN 1
                    WHEN status != 'paga' AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 2
                    ELSE 4
                END,
                data_vencimento ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'contas' => $contas,
                'total' => count($contas)
            ]);
            break;
        
        // ============================================
        // BUSCAR CONTA ESPECÍFICA
        // ============================================
        
        case 'get':
            $id = $_GET['id'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT * FROM contas_pagar_empresa WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            $conta = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conta) {
                echo json_encode([
                    'success' => true,
                    'conta' => $conta
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Conta não encontrada'
                ]);
            }
            break;
        
        // ============================================
        // CRIAR CONTA A PAGAR
        // ============================================
        
        case 'create':
            $stmt = $pdo->prepare("
                INSERT INTO contas_pagar_empresa
                (user_id, descricao, fornecedor, categoria, valor, data_vencimento, data_emissao, tipo, recorrencia, observacoes)
                VALUES (:user_id, :descricao, :fornecedor, :categoria, :valor, :data_vencimento, :data_emissao, :tipo, :recorrencia, :observacoes)
            ");

            $stmt->execute([
                'user_id' => $userId,
                'descricao' => $_POST['descricao'],
                'fornecedor' => $_POST['fornecedor'] ?? null,
                'categoria' => $_POST['categoria'] ?? null,
                'valor' => $_POST['valor'],
                'data_vencimento' => $_POST['data_vencimento'],
                'data_emissao' => $_POST['data_emissao'] ?? date('Y-m-d'),
                'tipo' => $_POST['tipo'] ?? 'variavel',
                'recorrencia' => $_POST['recorrencia'] ?? 'unico',
                'observacoes' => $_POST['observacoes'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conta a pagar cadastrada com sucesso!',
                'conta_id' => $pdo->lastInsertId()
            ]);
            break;
        
        // ============================================
        // ATUALIZAR CONTA A PAGAR
        // ============================================
        
        case 'update':
            $stmt = $pdo->prepare("
                UPDATE contas_pagar_empresa 
                SET descricao = :descricao,
                    fornecedor = :fornecedor,
                    categoria = :categoria,
                    valor = :valor,
                    data_vencimento = :data_vencimento,
                    tipo = :tipo,
                    recorrencia = :recorrencia,
                    observacoes = :observacoes
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                'id' => $_POST['id'],
                'user_id' => $userId,
                'descricao' => $_POST['descricao'],
                'fornecedor' => $_POST['fornecedor'] ?? null,
                'categoria' => $_POST['categoria'] ?? null,
                'valor' => $_POST['valor'],
                'data_vencimento' => $_POST['data_vencimento'],
                'tipo' => $_POST['tipo'],
                'recorrencia' => $_POST['recorrencia'],
                'observacoes' => $_POST['observacoes'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conta atualizada com sucesso!'
            ]);
            break;
        
        // ============================================
        // MARCAR COMO PAGA
        // ============================================
        
        case 'marcar_paga':
            $pdo->beginTransaction();
            
            try {
                // Atualizar conta
                $stmt = $pdo->prepare("
                    UPDATE contas_pagar_empresa
                    SET status = 'paga',
                        data_pagamento = :data_pagamento,
                        conta_id = :conta_id
                    WHERE id = :id AND user_id = :user_id
                ");

                $stmt->execute([
                    'id' => $_POST['id'],
                    'user_id' => $userId,
                    'data_pagamento' => $_POST['data_pagamento'] ?? date('Y-m-d'),
                    'conta_id' => $_POST['conta_id'] ?? null
                ]);

                // Buscar dados da conta para criar movimentação
                $stmt = $pdo->prepare("SELECT * FROM contas_pagar_empresa WHERE id = :id AND user_id = :user_id");
                $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
                $conta = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Criar movimentação financeira
                if ($conta && $_POST['conta_id']) {
                    $stmt = $pdo->prepare("
                        INSERT INTO movimentacoes_financeiras 
                        (conta_id, tipo, descricao, valor, data_movimentacao, status)
                        VALUES (:conta_id, 'despesa', :descricao, :valor, :data_movimentacao, 'concluido')
                    ");
                    
                    $stmt->execute([
                        'conta_id' => $_POST['conta_id'],
                        'descricao' => 'Pagamento: ' . $conta['descricao'],
                        'valor' => $conta['valor'],
                        'data_movimentacao' => $_POST['data_pagamento'] ?? date('Y-m-d')
                    ]);
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pagamento registrado com sucesso!'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
        
        // ============================================
        // DESMARCAR COMO PAGA
        // ============================================
        
        case 'desmarcar_paga':
            $pdo->beginTransaction();
            
            try {
                // Buscar dados antes de desmarcar
                $stmt = $pdo->prepare("SELECT * FROM contas_pagar_empresa WHERE id = :id AND user_id = :user_id");
                $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
                $conta = $stmt->fetch(PDO::FETCH_ASSOC);

                // Atualizar conta
                $stmt = $pdo->prepare("
                    UPDATE contas_pagar_empresa
                    SET status = 'pendente',
                        data_pagamento = NULL,
                        conta_id = NULL
                    WHERE id = :id AND user_id = :user_id
                ");

                $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
                
                // Excluir movimentação financeira relacionada (se existir)
                if ($conta && $conta['conta_id']) {
                    $stmt = $pdo->prepare("
                        DELETE FROM movimentacoes_financeiras 
                        WHERE conta_id = :conta_id 
                        AND descricao = :descricao
                        AND data_movimentacao = :data_pagamento
                        LIMIT 1
                    ");
                    
                    $stmt->execute([
                        'conta_id' => $conta['conta_id'],
                        'descricao' => 'Pagamento: ' . $conta['descricao'],
                        'data_pagamento' => $conta['data_pagamento']
                    ]);
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pagamento desmarcado com sucesso!'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
        
        // ============================================
        // EXCLUIR CONTA A PAGAR
        // ============================================
        
        case 'delete':
            // Verificar se está paga
            $stmt = $pdo->prepare("SELECT status FROM contas_pagar_empresa WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
            $conta = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($conta && $conta['status'] === 'paga') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Não é possível excluir uma conta já paga. Desmarque o pagamento primeiro.'
                ]);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM contas_pagar_empresa WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conta excluída com sucesso!'
            ]);
            break;
        
        // ============================================
        // ALERTAS DE VENCIMENTO
        // ============================================
        
        case 'alertas':
            $diasAlerta = $_GET['dias'] ?? 7;
            
            // Contas vencidas
            $stmt = $pdo->prepare("
                SELECT * FROM contas_pagar_empresa
                WHERE user_id = ? AND status != 'paga' AND data_vencimento < CURDATE()
                ORDER BY data_vencimento ASC
            ");
            $stmt->execute([$userId]);
            $vencidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Contas próximas do vencimento
            $stmt = $pdo->prepare("
                SELECT * FROM contas_pagar_empresa
                WHERE user_id = :user_id AND status != 'paga'
                AND data_vencimento >= CURDATE()
                AND data_vencimento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                ORDER BY data_vencimento ASC
            ");
            $stmt->execute(['user_id' => $userId, 'dias' => $diasAlerta]);
            $proximas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'alertas' => [
                    'vencidas' => $vencidas,
                    'proximas' => $proximas,
                    'total_vencidas' => count($vencidas),
                    'total_proximas' => count($proximas),
                    'valor_vencidas' => array_sum(array_column($vencidas, 'valor')),
                    'valor_proximas' => array_sum(array_column($proximas, 'valor'))
                ]
            ]);
            break;
        
        // ============================================
        // RESUMO GERAL
        // ============================================
        
        case 'resumo':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            
            // Total de contas
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total FROM contas_pagar_empresa
                WHERE user_id = :user_id AND MONTH(data_vencimento) = :mes AND YEAR(data_vencimento) = :ano
            ");
            $stmt->execute(['user_id' => $userId, 'mes' => $mes, 'ano' => $ano]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Contas pagas
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as pagas, COALESCE(SUM(valor), 0) as valor_pago
                FROM contas_pagar_empresa
                WHERE user_id = :user_id AND status = 'paga'
                AND MONTH(data_vencimento) = :mes AND YEAR(data_vencimento) = :ano
            ");
            $stmt->execute(['user_id' => $userId, 'mes' => $mes, 'ano' => $ano]);
            $pagas = $stmt->fetch(PDO::FETCH_ASSOC);

            // Contas pendentes
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as pendentes, COALESCE(SUM(valor), 0) as valor_pendente
                FROM contas_pagar_empresa
                WHERE user_id = :user_id AND status != 'paga'
                AND MONTH(data_vencimento) = :mes AND YEAR(data_vencimento) = :ano
            ");
            $stmt->execute(['user_id' => $userId, 'mes' => $mes, 'ano' => $ano]);
            $pendentes = $stmt->fetch(PDO::FETCH_ASSOC);

            // Contas vencidas
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as vencidas, COALESCE(SUM(valor), 0) as valor_vencido
                FROM contas_pagar_empresa
                WHERE user_id = ? AND status != 'paga' AND data_vencimento < CURDATE()
            ");
            $stmt->execute([$userId]);
            $vencidas = $stmt->fetch(PDO::FETCH_ASSOC);

            // Por categoria
            $stmt = $pdo->prepare("
                SELECT
                    categoria,
                    COUNT(*) as total,
                    COALESCE(SUM(valor), 0) as valor_total
                FROM contas_pagar_empresa
                WHERE user_id = :user_id AND MONTH(data_vencimento) = :mes AND YEAR(data_vencimento) = :ano
                GROUP BY categoria
            ");
            $stmt->execute(['user_id' => $userId, 'mes' => $mes, 'ano' => $ano]);
            $porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'resumo' => [
                    'total_contas' => $total,
                    'contas_pagas' => $pagas['pagas'],
                    'valor_pago' => $pagas['valor_pago'],
                    'contas_pendentes' => $pendentes['pendentes'],
                    'valor_pendente' => $pendentes['valor_pendente'],
                    'contas_vencidas' => $vencidas['vencidas'],
                    'valor_vencido' => $vencidas['valor_vencido'],
                    'por_tipo' => $porTipo
                ]
            ]);
            break;
        
        // ============================================
        // ESTATÍSTICAS
        // ============================================
        
        case 'estatisticas':
            // Custos por categoria (últimos 12 meses)
            $stmt = $pdo->prepare("
                SELECT
                    categoria,
                    COUNT(*) as total,
                    COALESCE(SUM(valor), 0) as valor_total
                FROM contas_pagar_empresa
                WHERE user_id = ? AND status = 'paga'
                AND data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY categoria
                ORDER BY valor_total DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            $porCategoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Custos mensais (últimos 6 meses)
            $stmt = $pdo->prepare("
                SELECT
                    DATE_FORMAT(data_pagamento, '%Y-%m') as mes_ano,
                    categoria,
                    COALESCE(SUM(valor), 0) as total
                FROM contas_pagar_empresa
                WHERE user_id = ? AND status = 'paga'
                AND data_pagamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(data_pagamento, '%Y-%m'), categoria
                ORDER BY mes_ano ASC
            ");
            $stmt->execute([$userId]);
            $mensais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'estatisticas' => [
                    'por_categoria' => $porCategoria,
                    'mensais' => $mensais
                ]
            ]);
            break;
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Ação não reconhecida'
            ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
