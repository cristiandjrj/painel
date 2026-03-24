<?php
// API Simplificada de Eventos - SEM ERROS
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
error_reporting(0); // Desabilitar erros em produção

try {
    require_once '../config/database.php';
    require_once '../config/auth.php';

    $userId = requireAuth();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'list':
            listarEventos($pdo, $userId);
            break;
        case 'get':
            buscarEvento($pdo, $userId);
            break;
        case 'create_orcamento':
            criarOrcamentoCompleto($pdo, $userId);
            break;
        case 'update_orcamento':
            atualizarOrcamentoCompleto($pdo, $userId);
            break;
        case 'update_status':
            atualizarStatus($pdo, $userId);
            break;
        case 'delete':
            excluirEvento($pdo, $userId);
            break;
        case 'pendentes':
            listarEventosPendentes($pdo, $userId);
            break;
        case 'update_financeiro':
            atualizarDadosFinanceiros($pdo, $userId);
            break;
        case 'registrar_pagamento':
            registrarPagamento($pdo, $userId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listarEventos($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                e.*,
                c.nome as cliente_nome
            FROM eventos_eventpro e
            LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
            WHERE e.user_id = ?
            ORDER BY e.data_evento DESC
        ");
        $stmt->execute([$userId]);
        
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada evento, buscar serviços e taxas (se existirem)
        foreach ($eventos as &$evento) {
            // Tentar buscar serviços
            try {
                $stmtServicos = $pdo->prepare("
                    SELECT 
                        es.*,
                        s.nome as servico_nome
                    FROM evento_servicos es
                    LEFT JOIN servicos_eventpro s ON es.servico_id = s.id
                    WHERE es.evento_id = ?
                ");
                $stmtServicos->execute([$evento['id']]);
                $evento['servicos'] = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $evento['servicos'] = [];
            }
            
            // Tentar buscar taxas
            try {
                $stmtTaxas = $pdo->prepare("
                    SELECT * FROM evento_taxas WHERE evento_id = ?
                ");
                $stmtTaxas->execute([$evento['id']]);
                $evento['taxas'] = $stmtTaxas->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $evento['taxas'] = [];
            }
            
            // Usar total_pago do banco, fallback para valor_sinal
            $evento['total_pago'] = floatval($evento['total_pago'] ?? $evento['valor_sinal'] ?? 0);
        }
        
        echo json_encode(['success' => true, 'eventos' => $eventos]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao listar eventos']);
    }
}

function buscarEvento($pdo, $userId) {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT
                e.*,
                c.nome as cliente_nome
            FROM eventos_eventpro e
            LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
            WHERE e.id = ? AND e.user_id = ?
        ");
        $stmt->execute([$id, $userId]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($evento) {
            // Buscar serviços
            try {
                $stmtServicos = $pdo->prepare("
                    SELECT 
                        es.*,
                        s.nome as servico_nome
                    FROM evento_servicos es
                    LEFT JOIN servicos_eventpro s ON es.servico_id = s.id
                    WHERE es.evento_id = ?
                ");
                $stmtServicos->execute([$id]);
                $evento['servicos'] = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $evento['servicos'] = [];
            }
            
            // Buscar taxas
            try {
                $stmtTaxas = $pdo->prepare("
                    SELECT * FROM evento_taxas WHERE evento_id = ?
                ");
                $stmtTaxas->execute([$id]);
                $evento['taxas'] = $stmtTaxas->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $evento['taxas'] = [];
            }
            
            echo json_encode(['success' => true, 'evento' => $evento]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Evento não encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar evento']);
    }
}

function atualizarStatus($pdo, $userId) {
    $id = $_POST['id'] ?? null;
    $novoStatus = $_POST['status'] ?? null;
    
    if (!$id || !$novoStatus) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
        return;
    }
    
    try {
        // Buscar status atual
        $stmt = $pdo->prepare("SELECT status FROM eventos_eventpro WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        $statusAtual = $evento['status'] ?? '';

        // Atualizar status
        $stmt = $pdo->prepare("UPDATE eventos_eventpro SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$novoStatus, $id, $userId]);
        
        // Contabilizar serviços (aguardando → em_andamento)
        if ($statusAtual === 'aguardando' && $novoStatus === 'em_andamento') {
            try {
                // Buscar serviços do evento
                $stmt = $pdo->prepare("
                    SELECT servico_id, quantidade 
                    FROM evento_servicos 
                    WHERE evento_id = ?
                ");
                $stmt->execute([$id]);
                $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Atualizar cada serviço
                $stmtUpdate = $pdo->prepare("
                    UPDATE servicos 
                    SET vezes_contratado = vezes_contratado + ? 
                    WHERE id = ?
                ");
                
                foreach ($servicos as $servico) {
                    $stmtUpdate->execute([
                        $servico['quantidade'] ?? 1,
                        $servico['servico_id']
                    ]);
                }
            } catch (Exception $e) {
                // Ignorar erro se tabelas não existirem
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Status atualizado']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
}

function excluirEvento($pdo, $userId) {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID não informado']);
        return;
    }
    
    try {
        // Tentar excluir serviços
        try {
            $pdo->prepare("DELETE FROM evento_servicos WHERE evento_id = ?")->execute([$id]);
        } catch (Exception $e) {}
        
        // Tentar excluir taxas
        try {
            $pdo->prepare("DELETE FROM evento_taxas WHERE evento_id = ?")->execute([$id]);
        } catch (Exception $e) {}
        
        // Excluir evento
        $stmt = $pdo->prepare("DELETE FROM eventos_eventpro WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Evento excluído']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir evento']);
    }
}

function criarOrcamentoCompleto($pdo, $userId) {
    try {
        // Iniciar transação
        $pdo->beginTransaction();

        // 1. Criar/Buscar Cliente
        $clienteId = $_POST['cliente_id'] ?? null;

        if (!$clienteId) {
            // Criar novo cliente se não existe
            $stmt = $pdo->prepare("
                INSERT INTO clientes_eventpro (user_id, nome, telefone, email, origem)
                VALUES (?, ?, '', '', 'orcamento')
            ");
            $stmt->execute([$userId, $_POST['cliente_nome']]);
            $clienteId = $pdo->lastInsertId();
        }

        // 2. Montar endereço (apenas rua, sem duplicar campos individuais)
        $enderecoCompleto = $_POST['rua'] ?? '';

        // 3. Criar Evento
        $stmt = $pdo->prepare("
            INSERT INTO eventos_eventpro (
                user_id,
                cliente_id,
                tipo,
                data_evento,
                hora_inicio,
                hora_fim,
                duracao,
                endereco,
                numero,
                complemento,
                bairro,
                cep,
                cidade,
                estado,
                valor_total,
                status,
                observacoes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $clienteId,
            $_POST['tipo_evento'],
            $_POST['data_evento'],
            $_POST['hora_inicio'] ?? null,
            $_POST['hora_fim'] ?? null,
            $_POST['duracao'] ?? null,
            $enderecoCompleto,
            $_POST['numero'] ?? null,
            $_POST['complemento'] ?? null,
            $_POST['bairro'] ?? null,
            $_POST['cep'] ?? null,
            $_POST['cidade'] ?? null,
            $_POST['estado'] ?? null,
            $_POST['valor_total'],
            $_POST['status'] ?? 'aguardando',
            $_POST['observacoes'] ?? null
        ]);
        
        $eventoId = $pdo->lastInsertId();
        
        // NOVO: Sincronizar dados financeiros com a tabela de clientes
        // Isso garante que o card do cliente mostre as informações financeiras
        $valorTotal = floatval($_POST['valor_total'] ?? 0);
        if ($valorTotal > 0) {
            $stmtUpdateCliente = $pdo->prepare("
                UPDATE clientes_eventpro 
                SET valor_total = ? 
                WHERE id = ?
            ");
            $stmtUpdateCliente->execute([$valorTotal, $clienteId]);
        }
        
        // 4. Adicionar Serviços
        $servicosJson = $_POST['servicos'] ?? $_POST['servicos_json'] ?? '';
        if (!empty($servicosJson)) {
            $servicos = json_decode($servicosJson, true);
            
            // Inserir serviços com valores corretos
            $stmtServico = $pdo->prepare("
                INSERT INTO evento_servicos (evento_id, servico_id, quantidade, valor_unitario, valor_total)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($servicos as $servico) {
                $quantidade = isset($servico['quantidade']) ? intval($servico['quantidade']) : 1;
                $valorUnitario = floatval($servico['valor'] ?? $servico['valor_base'] ?? 0);
                $valorTotal = $valorUnitario * $quantidade;
                
                try {
                    $stmtServico->execute([
                        $eventoId,
                        $servico['id'],
                        $quantidade,
                        $valorUnitario,
                        $valorTotal
                    ]);
                } catch (Exception $e) {
                    // Log erro mas continua
                    error_log("Erro ao inserir serviço: " . $e->getMessage());
                }
            }
        }
        
        // 5. Adicionar Taxas
        $taxasJson = $_POST['taxas'] ?? $_POST['taxas_json'] ?? '';
        if (!empty($taxasJson)) {
            $taxas = json_decode($taxasJson, true);

            $stmtTaxa = $pdo->prepare("
                INSERT INTO evento_taxas (evento_id, descricao, valor)
                VALUES (?, ?, ?)
            ");

            foreach ($taxas as $taxa) {
                try {
                    $stmtTaxa->execute([
                        $eventoId,
                        $taxa['descricao'] ?? $taxa['nome'] ?? 'Taxa',
                        $taxa['valor']
                    ]);
                } catch (Exception $e) {
                    // Continuar mesmo se falhar
                }
            }
        }
        
        // 6. Commit da transação
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Orçamento criado com sucesso!',
            'evento_id' => $eventoId,
            'cliente_id' => $clienteId
        ]);
        
    } catch (Exception $e) {
        // Rollback em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar orçamento: ' . $e->getMessage()
        ]);
    }
}

function atualizarOrcamentoCompleto($pdo, $userId) {
    try {
        $eventoId = $_POST['evento_id'] ?? null;

        if (!$eventoId) {
            throw new Exception('ID do evento não informado');
        }

        $pdo->beginTransaction();

        // 1. Montar endereço (apenas rua, sem duplicar campos individuais)
        $enderecoCompleto = $_POST['rua'] ?? '';

        // 2. Atualizar evento
        $stmt = $pdo->prepare("
            UPDATE eventos_eventpro SET
                cliente_id = ?,
                tipo = ?,
                data_evento = ?,
                hora_inicio = ?,
                hora_fim = ?,
                duracao = ?,
                endereco = ?,
                numero = ?,
                complemento = ?,
                bairro = ?,
                cep = ?,
                cidade = ?,
                estado = ?,
                valor_total = ?,
                desconto = ?,
                desconto_json = ?,
                status = ?,
                observacoes = ?,
                valor_sinal = ?,
                data_sinal = ?,
                forma_pagamento_sinal = ?,
                data_vencimento = ?
            WHERE id = ? AND user_id = ?
        ");

        $desconto = floatval($_POST['desconto'] ?? 0);
        $descontoJson = $_POST['desconto_json'] ?? null;
        $valorSinal = floatval($_POST['valor_sinal'] ?? 0);
        $dataSinal = !empty($_POST['data_sinal']) ? $_POST['data_sinal'] : null;
        $formaPagSinal = $_POST['forma_pagamento_sinal'] ?? null;
        $dataVencimento = !empty($_POST['data_vencimento']) ? $_POST['data_vencimento'] : null;

        $stmt->execute([
            $_POST['cliente_id'] ?? null,
            $_POST['tipo_evento'],
            $_POST['data_evento'],
            $_POST['hora_inicio'] ?? null,
            $_POST['hora_fim'] ?? null,
            $_POST['duracao'] ?? null,
            $enderecoCompleto,
            $_POST['numero'] ?? null,
            $_POST['complemento'] ?? null,
            $_POST['bairro'] ?? null,
            $_POST['cep'] ?? null,
            $_POST['cidade'] ?? null,
            $_POST['estado'] ?? null,
            $_POST['valor_total'],
            $desconto,
            $descontoJson,
            $_POST['status'] ?? 'aguardando',
            $_POST['observacoes'] ?? null,
            $valorSinal > 0 ? $valorSinal : null,
            $dataSinal,
            $formaPagSinal,
            $dataVencimento,
            $eventoId,
            $userId
        ]);

        // 3. Atualizar serviços: remover antigos e inserir novos
        try {
            $pdo->prepare("DELETE FROM evento_servicos WHERE evento_id = ?")->execute([$eventoId]);
        } catch (Exception $e) {}

        // Suportar ambos formatos: JSON string ou array fields
        $servicos = [];
        if (!empty($_POST['servicos'])) {
            if (is_string($_POST['servicos'])) {
                $servicos = json_decode($_POST['servicos'], true) ?: [];
            } else if (is_array($_POST['servicos'])) {
                $servicos = $_POST['servicos'];
            }
        }

        if (!empty($servicos)) {
            $stmtServico = $pdo->prepare("
                INSERT INTO evento_servicos (evento_id, servico_id, quantidade, valor_unitario, valor_total)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($servicos as $servico) {
                $servicoId = $servico['servico_id'] ?? $servico['id'] ?? null;
                $quantidade = isset($servico['quantidade']) ? intval($servico['quantidade']) : 1;
                $valorUnitario = floatval($servico['valor'] ?? $servico['valor_base'] ?? 0);
                $valorTotal = $valorUnitario * $quantidade;

                if ($servicoId) {
                    try {
                        $stmtServico->execute([
                            $eventoId,
                            $servicoId,
                            $quantidade,
                            $valorUnitario,
                            $valorTotal
                        ]);
                    } catch (Exception $e) {}
                }
            }
        }

        // 4. Atualizar taxas: remover antigas e inserir novas
        try {
            $pdo->prepare("DELETE FROM evento_taxas WHERE evento_id = ?")->execute([$eventoId]);
        } catch (Exception $e) {}

        // Suportar ambos formatos: JSON string ou array fields
        $taxas = [];
        if (!empty($_POST['taxas'])) {
            if (is_string($_POST['taxas'])) {
                $taxas = json_decode($_POST['taxas'], true) ?: [];
            } else if (is_array($_POST['taxas'])) {
                $taxas = $_POST['taxas'];
            }
        }

        if (!empty($taxas)) {
            $stmtTaxa = $pdo->prepare("
                INSERT INTO evento_taxas (evento_id, descricao, valor)
                VALUES (?, ?, ?)
            ");

            foreach ($taxas as $taxa) {
                $descTaxa = $taxa['descricao'] ?? $taxa['nome'] ?? '';
                $valorTaxa = floatval($taxa['valor'] ?? 0);
                if ($descTaxa && $valorTaxa > 0) {
                    $stmtTaxa->execute([
                        $eventoId,
                        $descTaxa,
                        $valorTaxa
                    ]);
                }
            }
        }

        // 5. Atualizar valor_total e dados financeiros no cliente
        $clienteId = $_POST['cliente_id'] ?? null;
        $valorTotalEvento = floatval($_POST['valor_total'] ?? 0);
        if ($clienteId) {
            try {
                $updateFields = ['valor_total = ?'];
                $updateValues = [$valorTotalEvento];

                if ($valorSinal > 0) {
                    $updateFields[] = 'valor_sinal = ?';
                    $updateValues[] = $valorSinal;
                }
                if ($dataSinal) {
                    $updateFields[] = 'data_sinal = ?';
                    $updateValues[] = $dataSinal;
                }
                if ($formaPagSinal) {
                    $updateFields[] = 'forma_pagamento_sinal = ?';
                    $updateValues[] = $formaPagSinal;
                }
                if ($dataVencimento) {
                    $updateFields[] = 'data_vencimento = ?';
                    $updateValues[] = $dataVencimento;
                }

                $updateValues[] = $clienteId;
                $sql = "UPDATE clientes_eventpro SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $pdo->prepare($sql)->execute($updateValues);
            } catch (Exception $e) {}
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Evento atualizado com sucesso!',
            'evento_id' => $eventoId
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao atualizar evento: ' . $e->getMessage()
        ]);
    }
}

function listarEventosPendentes($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                e.*,
                c.nome as cliente_nome,
                (e.valor_total - COALESCE(e.total_pago, e.valor_sinal, 0)) as saldo_devedor
            FROM eventos_eventpro e
            LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
            WHERE e.user_id = ?
            AND e.status IN ('aguardando', 'em_andamento')
            AND (e.valor_total - COALESCE(e.total_pago, e.valor_sinal, 0)) > 0
            ORDER BY e.data_vencimento ASC, e.data_evento ASC
        ");
        $stmt->execute([$userId]);
        
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'eventos' => $eventos
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao listar eventos pendentes: ' . $e->getMessage()
        ]);
    }
}

function atualizarDadosFinanceiros($pdo, $userId) {
    try {
        $id = $_POST['id'] ?? null;
        $valorTotal = $_POST['valor_total'] ?? null;
        $valorSinal = $_POST['valor_sinal'] ?? null;
        $dataSinal = $_POST['data_sinal'] ?? null;
        $formaPagamento = $_POST['forma_pagamento_sinal'] ?? null;
        $contaSinal = $_POST['conta_sinal'] ?? null;
        $dataVencimento = $_POST['data_vencimento'] ?? null;
        
        if (!$id) {
            throw new Exception('ID do evento não informado');
        }
        
        $stmt = $pdo->prepare("
            UPDATE eventos_eventpro
            SET
                valor_total = ?,
                valor_sinal = ?,
                data_sinal = ?,
                forma_pagamento_sinal = ?,
                conta_sinal = ?,
                data_vencimento = ?,
                total_pago = ?
            WHERE id = ? AND user_id = ?
        ");

        $stmt->execute([
            $valorTotal,
            $valorSinal,
            $dataSinal,
            $formaPagamento,
            $contaSinal,
            $dataVencimento,
            $valorSinal, // total_pago = valor_sinal
            $id,
            $userId
        ]);
        
        // Se tiver conta bancária, registrar transação
        if ($contaSinal && $valorSinal > 0) {
            try {
                $stmtTransacao = $pdo->prepare("
                    INSERT INTO transacoes_bancarias
                    (conta_id, tipo, valor, descricao, data, categoria)
                    VALUES (?, 'entrada', ?, ?, ?, 'receita')
                ");
                $stmtTransacao->execute([$contaSinal, $valorSinal, 'Sinal de evento', $dataSinal]);
            } catch (Exception $e) {
                // Ignorar se tabela não existir
            }
        }

        // Registrar/atualizar sinal em movimentacoes_financeiras e saldo da conta
        try {
            // Verificar registro anterior de sinal deste evento
            $stmtOldMov = $pdo->prepare("
                SELECT valor, conta_id FROM movimentacoes_financeiras
                WHERE tipo_movimentacao = 'sinal_evento' AND evento_id = ?
                LIMIT 1
            ");
            $stmtOldMov->execute([$id]);
            $oldMov = $stmtOldMov->fetch(PDO::FETCH_ASSOC);

            // Deletar registro anterior e reverter saldo
            if ($oldMov) {
                $pdo->prepare("
                    DELETE FROM movimentacoes_financeiras
                    WHERE tipo_movimentacao = 'sinal_evento' AND evento_id = ?
                ")->execute([$id]);
                $pdo->prepare("
                    UPDATE contas_bancarias SET saldo_atual = saldo_atual - ? WHERE id = ?
                ")->execute([$oldMov['valor'], $oldMov['conta_id']]);
            }

            // Inserir novo registro de sinal
            if ($contaSinal && $valorSinal > 0 && $dataSinal) {
                // Buscar nome e ID do cliente
                $stmtCli = $pdo->prepare("
                    SELECT c.nome, c.id as cliente_id
                    FROM eventos_eventpro e
                    JOIN clientes_eventpro c ON e.cliente_id = c.id
                    WHERE e.id = ?
                ");
                $stmtCli->execute([$id]);
                $cliInfo = $stmtCli->fetch(PDO::FETCH_ASSOC);

                $pdo->prepare("
                    INSERT INTO movimentacoes_financeiras
                        (user_id, tipo, valor, descricao, data_movimentacao, forma_pagamento,
                         categoria, tipo_movimentacao, conta_id, evento_id, cliente_id)
                    VALUES (?, 'receita', ?, ?, ?, ?, 'evento', 'sinal_evento', ?, ?, ?)
                ")->execute([
                    $userId, $valorSinal,
                    "Sinal - " . ($cliInfo['nome'] ?? 'Cliente'),
                    $dataSinal, $formaPagamento, $contaSinal, $id,
                    $cliInfo['cliente_id'] ?? null
                ]);

                // Atualizar saldo da conta
                $pdo->prepare("
                    UPDATE contas_bancarias SET saldo_atual = saldo_atual + ? WHERE id = ?
                ")->execute([$valorSinal, $contaSinal]);
            }
        } catch (Exception $e) {
            // Não falhar o processo principal se movimentacoes der erro
        }

        echo json_encode([
            'success' => true,
            'message' => 'Dados financeiros atualizados com sucesso!'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao atualizar dados financeiros: ' . $e->getMessage()
        ]);
    }
}

function registrarPagamento($pdo, $userId) {
    try {
        $eventoId = $_POST['evento_id'] ?? null;
        $valor = floatval($_POST['valor'] ?? 0);
        $dataPagamento = $_POST['data_pagamento'] ?? null;
        $descricao = $_POST['descricao'] ?? 'Pagamento';
        $formaPagamento = $_POST['forma_pagamento'] ?? null;
        $contaId = $_POST['conta_id'] ?? null;

        if (!$eventoId || $valor <= 0 || !$dataPagamento) {
            throw new Exception('Dados incompletos para registrar pagamento');
        }

        $pdo->beginTransaction();

        // 1. Registrar na tabela pagamentos_evento
        $stmt = $pdo->prepare("
            INSERT INTO pagamentos_evento (evento_id, valor, data_pagamento, descricao)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$eventoId, $valor, $dataPagamento, $descricao]);

        // 2. Atualizar total_pago no evento
        $stmt = $pdo->prepare("
            UPDATE eventos_eventpro
            SET total_pago = COALESCE(total_pago, 0) + ?
            WHERE id = ?
        ");
        $stmt->execute([$valor, $eventoId]);

        // 3. Buscar dados do evento para montar descrição
        $stmtEvento = $pdo->prepare("
            SELECT e.tipo, c.nome as cliente_nome
            FROM eventos_eventpro e
            LEFT JOIN clientes_eventpro c ON e.cliente_id = c.id
            WHERE e.id = ?
        ");
        $stmtEvento->execute([$eventoId]);
        $eventoInfo = $stmtEvento->fetch(PDO::FETCH_ASSOC);
        $descricaoMovimentacao = $descricao ?: 'Pagamento - ' . ($eventoInfo['tipo'] ?? 'Evento') . ' - ' . ($eventoInfo['cliente_nome'] ?? '');

        // 4. Sempre registrar em movimentacoes_financeiras (para aparecer nas receitas do dashboard)
        $stmtMov = $pdo->prepare("
            INSERT INTO movimentacoes_financeiras
            (user_id, tipo, valor, descricao, data_movimentacao, forma_pagamento, categoria, tipo_movimentacao, conta_id)
            VALUES (?, 'receita', ?, ?, ?, ?, 'evento', 'pagamento_evento', ?)
        ");
        $stmtMov->execute([
            $userId,
            $valor,
            $descricaoMovimentacao,
            $dataPagamento,
            $formaPagamento,
            $contaId ?: null
        ]);

        // 5. Se tiver conta bancária, registrar transação e atualizar saldo
        if ($contaId && $contaId !== '') {
            try {
                $stmtTransacao = $pdo->prepare("
                    INSERT INTO transacoes_bancarias
                    (conta_id, tipo, valor, descricao, data_transacao, forma_pagamento, categoria)
                    VALUES (?, 'entrada', ?, ?, ?, ?, 'receita')
                ");
                $stmtTransacao->execute([
                    $contaId,
                    $valor,
                    $descricaoMovimentacao,
                    $dataPagamento,
                    $formaPagamento
                ]);

                // Atualizar saldo da conta
                $stmtSaldo = $pdo->prepare("
                    UPDATE contas_bancarias
                    SET saldo_atual = saldo_atual + ?
                    WHERE id = ?
                ");
                $stmtSaldo->execute([$valor, $contaId]);
            } catch (Exception $e) {
                // Conta bancária é opcional, não falhar se der erro
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pagamento registrado com sucesso!'
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao registrar pagamento: ' . $e->getMessage()
        ]);
    }
}
