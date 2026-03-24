<?php
/**
 * API de Custos - EventProDJ
 * Gestão de custos da empresa e custos de eventos
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // LISTAR CUSTOS (COMPATIBILIDADE)
    if ($action === 'listar') {
        $stmt = $pdo->prepare("
            SELECT
                id,
                descricao,
                categoria,
                tipo,
                valor,
                data_custo,
                recorrente,
                observacoes
            FROM custos_empresa
            WHERE user_id = ?
            ORDER BY data_custo DESC
            LIMIT 50
        ");
        $stmt->execute([$userId]);

        $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'custos' => $custos
        ]);
        exit;
    }
    
    switch ($action) {
        
        // ============================================
        // CATEGORIAS DE CUSTOS
        // ============================================
        
        case 'categorias_custos_list':
            $tipo = $_GET['tipo'] ?? '';
            
            $sql = "SELECT * FROM categorias_custos WHERE ativo = TRUE AND user_id = ?";
            $params = [$userId];
            
            if ($tipo) {
                $sql .= " AND tipo = ?";
                $params[] = $tipo;
            }

            $sql .= " ORDER BY nome ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias
            ]);
            break;
        
        case 'categoria_custo_create':
            $stmt = $pdo->prepare("
                INSERT INTO categorias_custos (user_id, nome, tipo, cor, icone)
                VALUES (:user_id, :nome, :tipo, :cor, :icone)
            ");

            $stmt->execute([
                'user_id' => $userId,
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'cor' => $_POST['cor'] ?? '#EF476F',
                'icone' => $_POST['icone'] ?? 'tag'
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoria criada com sucesso!',
                'categoria_id' => $pdo->lastInsertId()
            ]);
            break;
        
        case 'categoria_custo_update':
            $stmt = $pdo->prepare("
                UPDATE categorias_custos 
                SET nome = :nome, tipo = :tipo, cor = :cor, icone = :icone
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                'id' => $_POST['id'],
                'user_id' => $userId,
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'cor' => $_POST['cor'] ?? '#EF476F',
                'icone' => $_POST['icone'] ?? 'tag'
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Categoria atualizada com sucesso!'
            ]);
            break;

        case 'categoria_custo_delete':
            $stmt = $pdo->prepare("UPDATE categorias_custos SET ativo = FALSE WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoria excluída com sucesso!'
            ]);
            break;
        
        // ============================================
        // CUSTOS DA EMPRESA
        // ============================================
        
        case 'custos_empresa_list':
            $mes = $_GET['mes'] ?? '';
            $ano = $_GET['ano'] ?? '';
            $tipo = $_GET['tipo'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $sql = "SELECT * FROM custos_empresa WHERE user_id = ?";
            $params = [$userId];

            if ($mes && $ano) {
                $sql .= " AND MONTH(data_custo) = ? AND YEAR(data_custo) = ?";
                $params[] = $mes;
                $params[] = $ano;
            }

            if ($tipo) {
                $sql .= " AND tipo = ?";
                $params[] = $tipo;
            }

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY data_custo DESC, id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'custos' => $custos,
                'total' => count($custos)
            ]);
            break;
        
        case 'custo_empresa_create':
            try {
                // Iniciar transação
                $pdo->beginTransaction();

                // Auto-migrate: adicionar colunas se não existirem
                try {
                    $pdo->exec("ALTER TABLE custos_empresa ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL");
                    $pdo->exec("ALTER TABLE custos_empresa ADD COLUMN IF NOT EXISTS forma_pagamento VARCHAR(50) DEFAULT NULL");
                    $pdo->exec("ALTER TABLE custos_empresa ADD COLUMN IF NOT EXISTS conta_bancaria_id INT DEFAULT NULL");
                } catch (Exception $e) {
                    // Ignorar se já existem
                }

                $forma_pagamento = $_POST['forma_pagamento'] ?? null;
                $conta_bancaria_id = $_POST['conta_bancaria_id'] ?? null;
                $valor = floatval($_POST['valor']);

                // Inserir custo
                $stmt = $pdo->prepare("
                    INSERT INTO custos_empresa
                    (user_id, descricao, categoria, tipo, valor, data_custo, recorrente, dia_vencimento, status, observacoes, forma_pagamento, conta_bancaria_id)
                    VALUES (:user_id, :descricao, :categoria, :tipo, :valor, :data_custo, :recorrente, :dia_vencimento, :status, :observacoes, :forma_pagamento, :conta_bancaria_id)
                ");

                $stmt->execute([
                    'user_id' => $userId,
                    'descricao' => $_POST['descricao'],
                    'categoria' => $_POST['categoria'] ?? null,
                    'tipo' => $_POST['tipo'] ?? 'variavel',
                    'valor' => $valor,
                    'data_custo' => $_POST['data_custo'] ?? date('Y-m-d'),
                    'recorrente' => $_POST['recorrente'] ?? 0,
                    'dia_vencimento' => $_POST['dia_vencimento'] ?? null,
                    'status' => 'pago',
                    'observacoes' => $_POST['observacoes'] ?? null,
                    'forma_pagamento' => $forma_pagamento,
                    'conta_bancaria_id' => $conta_bancaria_id
                ]);
                
                $custo_id = $pdo->lastInsertId();
                
                // Deduzir valor da conta bancária se informada
                if ($conta_bancaria_id) {
                    $stmt = $pdo->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual - :valor
                        WHERE id = :conta_id AND user_id = :user_id AND ativo = TRUE
                    ");

                    $stmt->execute([
                        'valor' => $valor,
                        'conta_id' => $conta_bancaria_id,
                        'user_id' => $userId
                    ]);
                    
                    // Verificar se atualizou
                    if ($stmt->rowCount() === 0) {
                        throw new Exception('Conta bancária não encontrada ou inativa');
                    }
                }
                
                // Confirmar transação
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => $conta_bancaria_id 
                        ? 'Custo cadastrado e deduzido da conta bancária com sucesso!' 
                        : 'Custo cadastrado com sucesso!',
                    'custo_id' => $custo_id
                ]);
            } catch (Exception $e) {
                // Reverter transação em caso de erro
                $pdo->rollBack();
                
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao cadastrar custo: ' . $e->getMessage()
                ]);
            }
            break;
        
        case 'custo_empresa_update':
            $stmt = $pdo->prepare("
                UPDATE custos_empresa 
                SET descricao = :descricao, 
                    categoria_id = :categoria_id, 
                    tipo = :tipo, 
                    valor = :valor, 
                    data_custo = :data_custo, 
                    data_vencimento = :data_vencimento,
                    recorrente = :recorrente,
                    frequencia = :frequencia,
                    dia_vencimento = :dia_vencimento,
                    observacoes = :observacoes
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                'id' => $_POST['id'],
                'user_id' => $userId,
                'descricao' => $_POST['descricao'],
                'categoria_id' => $_POST['categoria_id'] ?? null,
                'tipo' => $_POST['tipo'],
                'valor' => $_POST['valor'],
                'data_custo' => $_POST['data_custo'],
                'data_vencimento' => $_POST['data_vencimento'] ?? null,
                'recorrente' => $_POST['recorrente'] ?? false,
                'frequencia' => $_POST['frequencia'] ?? null,
                'dia_vencimento' => $_POST['dia_vencimento'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? null
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Custo atualizado com sucesso!'
            ]);
            break;
        
        case 'custo_empresa_pagar':
            $stmt = $pdo->prepare("
                UPDATE custos_empresa 
                SET status = 'pago',
                    data_pagamento = :data_pagamento,
                    valor_pago = :valor_pago,
                    conta_id = :conta_id
                WHERE id = :id AND user_id = :user_id
            ");

            $stmt->execute([
                'id' => $_POST['id'],
                'user_id' => $userId,
                'data_pagamento' => $_POST['data_pagamento'] ?? date('Y-m-d'),
                'valor_pago' => $_POST['valor_pago'],
                'conta_id' => $_POST['conta_id']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Pagamento registrado com sucesso!'
            ]);
            break;
        
        case 'custo_empresa_delete':
            $stmt = $pdo->prepare("DELETE FROM custos_empresa WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Custo excluído com sucesso!'
            ]);
            break;
        
        // ============================================
        // CUSTOS DE EVENTOS
        // ============================================
        
        case 'custos_eventos_list':
            $mes = $_GET['mes'] ?? '';
            $ano = $_GET['ano'] ?? '';

            $sql = "SELECT ec.*, ec.data as data_custo, e.tipo as tipo_evento, e.data_evento, c.nome as cliente_nome
                    FROM custos_eventos ec
                    LEFT JOIN eventos_eventpro e ON ec.evento_id = e.id
                    LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
                    WHERE e.user_id = ?";
            $params = [$userId];

            if ($mes && $ano) {
                $sql .= " AND MONTH(ec.data) = ? AND YEAR(ec.data) = ?";
                $params[] = $mes;
                $params[] = $ano;
            } elseif ($ano) {
                $sql .= " AND YEAR(ec.data) = ?";
                $params[] = $ano;
            }

            $sql .= " ORDER BY ec.data DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'custos' => $custos
            ]);
            break;

        case 'evento_custos_list':
            $evento_id = $_GET['evento_id'] ?? '';
            
            if (!$evento_id) {
                throw new Exception('evento_id é obrigatório');
            }
            
            $stmt = $pdo->prepare("
                SELECT ec.*, ec.data as data_custo
                FROM custos_eventos ec
                INNER JOIN eventos_eventpro e ON ec.evento_id = e.id AND e.user_id = ?
                WHERE ec.evento_id = ?
                ORDER BY ec.data DESC
            ");

            $stmt->execute([$userId, $evento_id]);
            $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'custos' => $custos
            ]);
            break;
        
        case 'evento_custo_create':
            $conta_id = $_POST['conta_id'] ?? null;
            $forma_pagamento = $_POST['forma_pagamento'] ?? null;
            $valor = floatval($_POST['valor']);

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO custos_eventos (evento_id, descricao, categoria, valor, data, forma_pagamento, conta_id, observacoes, status)
                    VALUES (:evento_id, :descricao, :categoria, :valor, :data_custo, :forma_pagamento, :conta_id, :observacoes, 'pago')
                ");

                $stmt->execute([
                    'evento_id' => $_POST['evento_id'],
                    'descricao' => $_POST['descricao'],
                    'categoria' => $_POST['categoria'] ?? 'Outros',
                    'valor' => $valor,
                    'data_custo' => $_POST['data_custo'] ?? date('Y-m-d'),
                    'forma_pagamento' => $forma_pagamento,
                    'conta_id' => $conta_id ?: null,
                    'observacoes' => $_POST['observacoes'] ?? null
                ]);

                $custo_id = $pdo->lastInsertId();

                // Deduzir valor da conta bancária se informada
                if ($conta_id) {
                    $stmtConta = $pdo->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual - :valor
                        WHERE id = :conta_id AND user_id = :user_id AND ativo = TRUE
                    ");
                    $stmtConta->execute([
                        'valor' => $valor,
                        'conta_id' => $conta_id,
                        'user_id' => $userId
                    ]);
                }

                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Custo adicionado ao evento!',
                    'custo_id' => $custo_id
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
        
        case 'evento_custo_update':
            $stmt = $pdo->prepare("
                UPDATE custos_eventos
                SET descricao = :descricao, categoria = :categoria, valor = :valor,
                    data = :data_custo, forma_pagamento = :forma_pagamento,
                    conta_id = :conta_id, observacoes = :observacoes
                WHERE id = :id AND evento_id IN (SELECT id FROM eventos_eventpro WHERE user_id = :user_id)
            ");

            $stmt->execute([
                'id'              => $_POST['id'],
                'user_id'         => $userId,
                'descricao'       => $_POST['descricao'],
                'categoria'       => $_POST['categoria'],
                'valor'           => $_POST['valor'],
                'data_custo'      => $_POST['data_custo'],
                'forma_pagamento' => $_POST['forma_pagamento'] ?? null,
                'conta_id'        => !empty($_POST['conta_bancaria_id']) ? $_POST['conta_bancaria_id'] : null,
                'observacoes'     => $_POST['observacoes'] ?? null,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Custo atualizado com sucesso!'
            ]);
            break;
        
        case 'evento_custo_delete':
            $stmt = $pdo->prepare("DELETE FROM custos_eventos WHERE id = :id AND evento_id IN (SELECT id FROM eventos_eventpro WHERE user_id = :user_id)");
            $stmt->execute(['id' => $_POST['id'], 'user_id' => $userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Custo excluído com sucesso!'
            ]);
            break;
        
        // ============================================
        // RELATÓRIOS E ESTATÍSTICAS
        // ============================================
        
        case 'resumo_custos_empresa':
            $uid = intval($userId);
            $stmt = $pdo->query("
                SELECT
                    COUNT(*) as total_custos,
                    COALESCE(SUM(valor), 0) as valor_total,
                    COALESCE(SUM(CASE WHEN status = 'pago' THEN valor ELSE 0 END), 0) as valor_pago,
                    COALESCE(SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END), 0) as valor_pendente
                FROM custos_empresa WHERE user_id = {$uid}
            ");
            $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'resumo' => $resumo
            ]);
            break;
        
        case 'custos_mensais':
            $limite = $_GET['limite'] ?? 12;

            $stmt = $pdo->prepare("
                SELECT
                    MONTH(data_custo) as mes,
                    YEAR(data_custo) as ano,
                    COUNT(*) as total_custos,
                    COALESCE(SUM(valor), 0) as valor_total
                FROM custos_empresa
                WHERE user_id = :user_id
                GROUP BY YEAR(data_custo), MONTH(data_custo)
                ORDER BY ano DESC, mes DESC
                LIMIT :limite
            ");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'custos_mensais' => array_reverse($custos)
            ]);
            break;
        
        case 'top_categorias_custos':
            $uid = intval($userId);
            $stmt = $pdo->query("
                SELECT categoria, COUNT(*) as total, COALESCE(SUM(valor), 0) as valor_total
                FROM custos_empresa WHERE user_id = {$uid}
                GROUP BY categoria ORDER BY valor_total DESC LIMIT 10
            ");
            $top = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'top_categorias' => $top
            ]);
            break;
        
        case 'custos_fixos_recorrentes':
            $stmt = $pdo->prepare("
                SELECT * FROM custos_empresa
                WHERE user_id = ? AND recorrente = TRUE AND tipo = 'fixo'
                ORDER BY valor DESC
            ");
            $stmt->execute([$userId]);
            $custos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'custos_fixos' => $custos
            ]);
            break;
        
        // ============================================
        // CUSTOS DE EVENTOS
        // ============================================
        
        case 'custo_evento_create':
            $pdo->beginTransaction();
            
            try {
                // Inserir custo de evento
                $stmt = $pdo->prepare("
                    INSERT INTO custos_eventos 
                    (evento_id, descricao, categoria, valor, data, observacoes, gera_despesa)
                    VALUES (:evento_id, :descricao, :categoria, :valor, :data, :observacoes, TRUE)
                ");
                
                $stmt->execute([
                    'evento_id' => $_POST['evento_id'],
                    'descricao' => $_POST['descricao'],
                    'categoria' => $_POST['categoria'],
                    'valor' => $_POST['valor'],
                    'data' => $_POST['data'],
                    'observacoes' => $_POST['observacoes'] ?? null
                ]);
                
                $custo_id = $pdo->lastInsertId();
                
                // Se foi informada uma conta E NÃO é "dinheiro"
                $conta_id = $_POST['conta_id'] ?? null;
                
                if ($conta_id && $conta_id !== 'dinheiro') {
                    // Buscar nome do evento
                    $stmt = $pdo->prepare("
                        SELECT tipo FROM eventos_eventpro WHERE id = :id AND user_id = :user_id
                    ");
                    $stmt->execute(['id' => $_POST['evento_id'], 'user_id' => $userId]);
                    $evento_nome = $stmt->fetchColumn() ?: 'Evento';
                    
                    // Criar movimentação financeira
                    $stmt = $pdo->prepare("
                        INSERT INTO movimentacoes_financeiras 
                        (conta_id, tipo, categoria, descricao, valor, data_movimentacao, 
                         observacoes, tipo_movimentacao)
                        VALUES 
                        (:conta_id, 'despesa', :categoria, :descricao, :valor, :data,
                         :observacoes, 'custo_evento')
                    ");
                    
                    $stmt->execute([
                        'conta_id' => $conta_id,
                        'categoria' => $_POST['categoria'],
                        'descricao' => '[Custo Evento: ' . $evento_nome . '] ' . $_POST['descricao'],
                        'valor' => $_POST['valor'],
                        'data' => $_POST['data'],
                        'observacoes' => $_POST['observacoes'] ?? 'Custo do evento ' . $evento_nome
                    ]);
                    
                    // Atualizar saldo da conta
                    $stmt = $pdo->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual - :valor
                        WHERE id = :conta_id AND user_id = :user_id
                    ");
                    $stmt->execute([
                        'conta_id' => $conta_id,
                        'valor' => $_POST['valor'],
                        'user_id' => $userId
                    ]);
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Custo de evento adicionado com sucesso!' . 
                                ($conta_id === 'dinheiro' ? ' (Pago em dinheiro)' : ''),
                    'custo_id' => $custo_id,
                    'gera_despesa' => $conta_id && $conta_id !== 'dinheiro'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
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
        'message' => 'Erro: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
