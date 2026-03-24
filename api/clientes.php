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
            listarClientes($pdo, $userId);
            break;
        case 'get':
            buscarCliente($pdo, $userId);
            break;
        case 'create':
            criarCliente($pdo, $userId);
            break;
        case 'update':
            atualizarCliente($pdo, $userId);
            break;
        case 'update_financeiro':
            atualizarFinanceiro($pdo, $userId);
            break;
        case 'delete':
            excluirCliente($pdo, $userId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listarClientes($pdo, $userId) {
    try {
        // Buscar clientes com dados financeiros do evento associado
        $stmt = $pdo->prepare("
            SELECT c.*,
                   e.id as evento_id,
                   e.valor_total as evento_valor_total,
                   e.total_pago as evento_total_pago,
                   e.valor_sinal as evento_valor_sinal,
                   e.data_sinal as evento_data_sinal,
                   e.forma_pagamento_sinal as evento_forma_pagamento_sinal,
                   e.conta_sinal as evento_conta_sinal,
                   e.data_vencimento as evento_data_vencimento,
                   e.status as evento_status,
                   e.tipo as evento_tipo,
                   e.data_evento as evento_data
            FROM clientes_eventpro c
            LEFT JOIN eventos_eventpro e ON e.cliente_id = c.id AND e.user_id = ?
            WHERE c.user_id = ?
            ORDER BY c.nome ASC
        ");
        $stmt->execute([$userId, $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar: um cliente pode ter vários eventos
        // Pegar os dados financeiros do evento mais relevante (em_andamento > concluido > outros)
        $clientesMap = [];
        foreach ($rows as $row) {
            $cid = $row['id'];
            if (!isset($clientesMap[$cid])) {
                $cliente = [
                    'id' => $row['id'],
                    'nome' => $row['nome'],
                    'telefone' => $row['telefone'],
                    'email' => $row['email'],
                    'cpf' => $row['cpf'],
                    'data_aniversario' => $row['data_aniversario'],
                    'cep' => $row['cep'],
                    'endereco' => $row['endereco'],
                    'rua' => $row['rua'] ?? null,
                    'numero' => $row['numero'],
                    'complemento' => $row['complemento'],
                    'bairro' => $row['bairro'],
                    'cidade' => $row['cidade'],
                    'estado' => $row['estado'],
                    'origem' => $row['origem'],
                    'observacoes' => $row['observacoes'],
                    'ativo' => $row['ativo'],
                    'data_cadastro' => $row['data_cadastro'],
                    // Campos financeiros (serão preenchidos pelo evento)
                    'valor_total' => 0,
                    'valor_sinal' => 0,
                    'total_pago' => 0,
                    'data_sinal' => null,
                    'data_vencimento' => null,
                    'forma_pagamento_sinal' => null,
                    'conta_sinal' => null,
                    'evento_status' => null,
                    'evento_tipo' => null,
                    'evento_data' => null,
                    'evento_id' => null
                ];
                $clientesMap[$cid] = $cliente;
            }

            // Se tem evento, pegar os dados financeiros do mais relevante
            if ($row['evento_id']) {
                $eventoAtual = $clientesMap[$cid];
                $statusPrioridade = ['em_andamento' => 1, 'aguardando' => 2, 'confirmado' => 3, 'concluido' => 4, 'cancelado' => 5];
                $prioridadeAtual = $statusPrioridade[$eventoAtual['evento_status'] ?? ''] ?? 99;
                $prioridadeNovo = $statusPrioridade[$row['evento_status'] ?? ''] ?? 99;

                // Usar evento com maior prioridade (em_andamento > aguardando > concluido)
                // Ou se ainda não tem evento selecionado
                if (!$eventoAtual['evento_id'] || $prioridadeNovo < $prioridadeAtual) {
                    $clientesMap[$cid]['valor_total'] = floatval($row['evento_valor_total'] ?? 0);
                    $clientesMap[$cid]['valor_sinal'] = floatval($row['evento_valor_sinal'] ?? 0);
                    $clientesMap[$cid]['total_pago'] = floatval($row['evento_total_pago'] ?? 0);
                    $clientesMap[$cid]['data_sinal'] = $row['evento_data_sinal'];
                    $clientesMap[$cid]['data_vencimento'] = $row['evento_data_vencimento'];
                    $clientesMap[$cid]['forma_pagamento_sinal'] = $row['evento_forma_pagamento_sinal'];
                    $clientesMap[$cid]['conta_sinal'] = $row['evento_conta_sinal'];
                    $clientesMap[$cid]['evento_status'] = $row['evento_status'];
                    $clientesMap[$cid]['evento_tipo'] = $row['evento_tipo'];
                    $clientesMap[$cid]['evento_data'] = $row['evento_data'];
                    $clientesMap[$cid]['evento_id'] = $row['evento_id'];
                }
            }
        }

        $clientes = array_values($clientesMap);
        echo json_encode(['success' => true, 'clientes' => $clientes]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao listar clientes: ' . $e->getMessage()]);
    }
}

function buscarCliente($pdo, $userId) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM clientes_eventpro WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cliente) {
            echo json_encode(['success' => true, 'cliente' => $cliente]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar cliente']);
    }
}

function criarCliente($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO clientes_eventpro (user_id, nome, email, telefone, cpf, data_aniversario, origem, observacoes, cep, rua, numero, bairro, complemento, cidade, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $_POST['nome'],
            $_POST['email'] ?? null,
            $_POST['telefone'],
            $_POST['cpf'] ?? null,
            $_POST['aniversario'] ?? $_POST['data_aniversario'] ?? null,
            $_POST['origem'] ?? null,
            $_POST['observacoes'] ?? null,
            $_POST['cep'] ?? null,
            $_POST['rua'] ?? null,
            $_POST['numero'] ?? null,
            $_POST['bairro'] ?? null,
            $_POST['complemento'] ?? null,
            $_POST['cidade'] ?? null,
            $_POST['estado'] ?? null
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Cliente criado', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar cliente: ' . $e->getMessage()]);
    }
}

function atualizarCliente($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE clientes_eventpro SET
                nome = ?, email = ?, telefone = ?, cpf = ?,
                data_aniversario = ?, origem = ?, observacoes = ?,
                cep = ?, rua = ?, numero = ?, bairro = ?, complemento = ?, cidade = ?, estado = ?
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([
            $_POST['nome'],
            $_POST['email'] ?? null,
            $_POST['telefone'],
            $_POST['cpf'] ?? null,
            $_POST['aniversario'] ?? $_POST['data_aniversario'] ?? null,
            $_POST['origem'] ?? null,
            $_POST['observacoes'] ?? null,
            $_POST['cep'] ?? null,
            $_POST['rua'] ?? null,
            $_POST['numero'] ?? null,
            $_POST['bairro'] ?? null,
            $_POST['complemento'] ?? null,
            $_POST['cidade'] ?? null,
            $_POST['estado'] ?? null,
            $_POST['id'],
            $userId
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Cliente atualizado']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar cliente: ' . $e->getMessage()]);
    }
}

function excluirCliente($pdo, $userId) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM clientes_eventpro WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        echo json_encode(['success' => true, 'message' => 'Cliente excluído']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir cliente']);
    }
}

function atualizarFinanceiro($pdo, $userId) {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }
    
    // Converter campos vazios para NULL ou 0
    $valor_total = $_POST['valor_total'] ?? 0;
    if ($valor_total === '' || $valor_total === null) $valor_total = 0;
    $valor_total = floatval($valor_total);
    
    $valor_sinal = $_POST['valor_sinal'] ?? 0;
    if ($valor_sinal === '' || $valor_sinal === null) $valor_sinal = 0;
    $valor_sinal = floatval($valor_sinal);
    
    $data_sinal = $_POST['data_sinal'] ?? null;
    if ($data_sinal === '') $data_sinal = null;
    
    $data_vencimento = $_POST['data_vencimento'] ?? null;
    if ($data_vencimento === '') $data_vencimento = null;
    
    $forma_pagamento_sinal = $_POST['forma_pagamento_sinal'] ?? null;
    if ($forma_pagamento_sinal === '') $forma_pagamento_sinal = null;
    
    // Corrigir conta_sinal: se vazio, usar NULL ao invés de string vazia
    $conta_sinal = $_POST['conta_sinal'] ?? null;
    if ($conta_sinal === '' || $conta_sinal === '0') {
        $conta_sinal = null;
    }
    
    try {
        // Iniciar transação
        $pdo->beginTransaction();
        
        // 1. Atualizar dados financeiros do cliente
        $stmt = $pdo->prepare("
            UPDATE clientes_eventpro 
            SET valor_total = ?, 
                valor_sinal = ?, 
                data_sinal = ?, 
                data_vencimento = ?,
                forma_pagamento_sinal = ?,
                conta_sinal = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $valor_total,
            $valor_sinal,
            $data_sinal,
            $data_vencimento,
            $forma_pagamento_sinal,
            $conta_sinal,
            $id
        ]);
        
        // 2. Atualizar também o EVENTO do cliente (se existir)
        $stmtEvento = $pdo->prepare("
            UPDATE eventos_eventpro 
            SET valor_total = ?,
                valor_sinal = ?,
                data_sinal = ?,
                data_vencimento = ?,
                forma_pagamento_sinal = ?,
                conta_sinal = ?
            WHERE cliente_id = ?
        ");
        
        $stmtEvento->execute([
            $valor_total,
            $valor_sinal,
            $data_sinal,
            $data_vencimento,
            $forma_pagamento_sinal,
            $conta_sinal,
            $id
        ]);
        
        // 3. Se tem conta bancária e valor de sinal, registrar entrada na conta
        if ($conta_sinal && $valor_sinal > 0 && $data_sinal) {
            // Buscar nome do cliente
            $stmtCliente = $pdo->prepare("SELECT nome FROM clientes_eventpro WHERE id = ?");
            $stmtCliente->execute([$id]);
            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
            $nomeCliente = $cliente ? $cliente['nome'] : 'Cliente';
            
            // Verificar se tabela transacoes_bancarias existe
            try {
                $pdo->query("SELECT 1 FROM transacoes_bancarias LIMIT 1");
                $tabelaExiste = true;
            } catch (PDOException $e) {
                $tabelaExiste = false;
            }
            
            if ($tabelaExiste) {
                // Verificar se já existe transação para este cliente e sinal
                $stmtCheck = $pdo->prepare("
                    SELECT id FROM transacoes_bancarias
                    WHERE conta_id = ?
                    AND descricao LIKE ?
                    AND valor = ?
                    LIMIT 1
                ");
                $stmtCheck->execute([$conta_sinal, "Sinal recebido - {$nomeCliente}%", $valor_sinal]);

                // Só criar se não existir
                if (!$stmtCheck->fetch()) {
                    // Criar transação na conta bancária
                    $stmtTransacao = $pdo->prepare("
                        INSERT INTO transacoes_bancarias
                        (conta_id, tipo, categoria, descricao, valor, data_transacao, forma_pagamento)
                        VALUES (?, 'entrada', 'Sinal de Cliente', ?, ?, ?, ?)
                    ");

                    $descricao = "Sinal recebido - {$nomeCliente}";

                    $stmtTransacao->execute([
                        $conta_sinal,
                        $descricao,
                        $valor_sinal,
                        $data_sinal,
                        $forma_pagamento_sinal
                    ]);

                    // Atualizar saldo da conta
                    $stmtSaldo = $pdo->prepare("
                        UPDATE contas_bancarias
                        SET saldo_atual = saldo_atual + ?
                        WHERE id = ?
                    ");
                    $stmtSaldo->execute([$valor_sinal, $conta_sinal]);
                }
            }

            // Registrar/atualizar em movimentacoes_financeiras para aparecer no
            // dashboard, financeiro, transações recentes e gestão de conta
            try {
                // Deletar registro anterior deste cliente (evita duplicata em re-edição)
                $pdo->prepare("
                    DELETE FROM movimentacoes_financeiras
                    WHERE tipo_movimentacao = 'sinal_evento' AND cliente_id = ? AND user_id = ?
                ")->execute([$id, $userId]);

                // Inserir novo registro
                $pdo->prepare("
                    INSERT INTO movimentacoes_financeiras
                        (user_id, tipo, valor, descricao, data_movimentacao, forma_pagamento,
                         categoria, tipo_movimentacao, conta_id, cliente_id)
                    VALUES (?, 'receita', ?, ?, ?, ?, 'evento', 'sinal_evento', ?, ?)
                ")->execute([
                    $userId, $valor_sinal, "Sinal - {$nomeCliente}",
                    $data_sinal, $forma_pagamento_sinal, $conta_sinal, $id
                ]);
            } catch (Exception $e) {
                // Não falhar o processo principal se movimentacoes der erro
            }
        } else {
            // Sinal removido: limpar registro de movimentacoes se existir
            try {
                $pdo->prepare("
                    DELETE FROM movimentacoes_financeiras
                    WHERE tipo_movimentacao = 'sinal_evento' AND cliente_id = ? AND user_id = ?
                ")->execute([$id, $userId]);
            } catch (Exception $e) {}
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Dados financeiros atualizados']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar dados financeiros: ' . $e->getMessage()]);
    }
}
