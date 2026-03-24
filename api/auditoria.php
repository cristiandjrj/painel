<?php
/**
 * API de Auditoria - EventProDJ
 * Sistema completo de verificação de integridade e discrepâncias
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // ============================================
        // AUDITORIA FINANCEIRA COMPLETA
        // ============================================
        
        case 'auditoria_completa':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            
            $resultado = [
                'success' => true,
                'data_auditoria' => date('Y-m-d H:i:s'),
                'periodo' => "$mes/$ano",
                'erros' => [],
                'avisos' => [],
                'info' => []
            ];
            
            // 1. CONTADORES GERAIS
            $stmt = $pdo->query("
                SELECT 
                    (SELECT COUNT(*) FROM clientes_eventpro WHERE ativo = TRUE) as total_clientes,
                    (SELECT COUNT(*) FROM eventos_eventpro) as total_eventos,
                    (SELECT COUNT(*) FROM movimentacoes_financeiras) as total_movimentacoes,
                    (SELECT COUNT(*) FROM custos_eventos) as total_custos_eventos,
                    (SELECT COUNT(*) FROM contas_bancarias WHERE ativo = TRUE) as total_contas
            ");
            $contadores = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['contadores'] = $contadores;
            
            // 2. AUDITORIA FINANCEIRA DO MÊS
            $stmt = $pdo->query("SELECT * FROM vw_auditoria_financeira");
            $auditoria = $stmt->fetch(PDO::FETCH_ASSOC);
            $resultado['auditoria_financeira'] = $auditoria;
            
            // 3. VERIFICAR DISCREPÂNCIA
            $discrepancia = floatval($auditoria['discrepancia']);
            $resultado['discrepancia'] = $discrepancia;
            $resultado['status'] = $auditoria['status_auditoria'];
            
            if ($discrepancia > 5.00) {
                $resultado['erros'][] = "Discrepância CRÍTICA de R$ " . number_format($discrepancia, 2, ',', '.');
            } elseif ($discrepancia > 0.50) {
                $resultado['avisos'][] = "Discrepância de R$ " . number_format($discrepancia, 2, ',', '.') . " detectada.";
            } else {
                $resultado['info'][] = "Auditoria OK! Discrepância dentro do esperado (R$ " . number_format($discrepancia, 2, ',', '.') . ").";
            }
            
            // 4. VERIFICAR IDS DUPLICADOS
            $tabelas = [
                'clientes_eventpro',
                'eventos_eventpro',
                'movimentacoes_financeiras',
                'custos_eventos',
                'contas_bancarias'
            ];
            
            foreach ($tabelas as $tabela) {
                $stmt = $pdo->prepare("
                    SELECT id, COUNT(*) as total
                    FROM $tabela
                    GROUP BY id
                    HAVING COUNT(*) > 1
                ");
                $stmt->execute();
                $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($duplicados) > 0) {
                    $resultado['erros'][] = "IDs duplicados encontrados em $tabela: " . count($duplicados) . " registros.";
                }
            }
            
            // 5. VERIFICAR VALORES NEGATIVOS INVÁLIDOS
            $stmt = $pdo->query("
                SELECT COUNT(*) as total
                FROM movimentacoes_financeiras
                WHERE valor < 0
            ");
            $negativos = $stmt->fetchColumn();
            
            if ($negativos > 0) {
                $resultado['avisos'][] = "$negativos movimentações com valores negativos encontradas.";
            }
            
            // 6. VERIFICAR EVENTOS SEM CLIENTE
            $stmt = $pdo->query("
                SELECT COUNT(*) as total
                FROM eventos_eventpro
                WHERE cliente_id NOT IN (SELECT id FROM clientes_eventpro)
            ");
            $orfaos = $stmt->fetchColumn();
            
            if ($orfaos > 0) {
                $resultado['erros'][] = "$orfaos eventos sem cliente válido encontrados.";
            }
            
            // 7. VERIFICAR SALDO DAS CONTAS
            $stmt = $pdo->query("
                SELECT 
                    id,
                    nome,
                    banco,
                    saldo_inicial,
                    saldo_atual,
                    (
                        SELECT COALESCE(SUM(CASE 
                            WHEN tipo = 'receita' THEN valor 
                            ELSE -valor 
                        END), 0)
                        FROM movimentacoes_financeiras
                        WHERE conta_id = contas_bancarias.id
                    ) as movimentacoes_total
                FROM contas_bancarias
                WHERE ativo = TRUE
            ");
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultado['validacao_contas'] = [];
            
            foreach ($contas as $conta) {
                $saldo_calculado = floatval($conta['saldo_inicial']) + floatval($conta['movimentacoes_total']);
                $saldo_atual = floatval($conta['saldo_atual']);
                $diferenca = abs($saldo_calculado - $saldo_atual);
                
                $resultado['validacao_contas'][] = [
                    'conta' => $conta['nome'] . ' - ' . $conta['banco'],
                    'saldo_inicial' => $conta['saldo_inicial'],
                    'movimentacoes' => $conta['movimentacoes_total'],
                    'saldo_calculado' => $saldo_calculado,
                    'saldo_atual' => $saldo_atual,
                    'diferenca' => $diferenca,
                    'status' => $diferenca <= 0.50 ? 'OK' : 'DIVERGENTE'
                ];
                
                if ($diferenca > 0.50) {
                    $resultado['avisos'][] = "Diferença de R$ " . number_format($diferenca, 2, ',', '.') . 
                                            " na conta " . $conta['nome'] . ".";
                }
            }
            
            // 8. RESUMO
            $resultado['resumo'] = [
                'total_erros' => count($resultado['erros']),
                'total_avisos' => count($resultado['avisos']),
                'total_info' => count($resultado['info']),
                'status_geral' => count($resultado['erros']) > 0 ? 'CRÍTICO' : 
                                 (count($resultado['avisos']) > 0 ? 'ALERTA' : 'OK')
            ];
            
            echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
        
        // ============================================
        // AUDITORIA RÁPIDA (MÊS ATUAL)
        // ============================================
        
        case 'auditoria_rapida':
            $stmt = $pdo->query("SELECT * FROM vw_auditoria_financeira");
            $auditoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'periodo' => $auditoria['periodo'],
                'total_receitas' => floatval($auditoria['total_receitas']),
                'total_despesas' => floatval($auditoria['total_despesas']),
                'total_custos_eventos' => floatval($auditoria['total_custos_eventos']),
                'total_saidas' => floatval($auditoria['total_saidas']),
                'saldo_calculado' => floatval($auditoria['saldo_calculado']),
                'saldo_contas' => floatval($auditoria['saldo_contas']),
                'discrepancia' => floatval($auditoria['discrepancia']),
                'status' => $auditoria['status_auditoria']
            ]);
            break;
        
        // ============================================
        // MOVIMENTAÇÕES POR TIPO
        // ============================================
        
        case 'movimentacoes_por_tipo':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            
            $stmt = $pdo->prepare("
                SELECT * FROM vw_movimentacoes_por_tipo
                WHERE mes = :mes AND ano = :ano
                ORDER BY tipo_movimentacao
            ");
            $stmt->execute(['mes' => $mes, 'ano' => $ano]);
            $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'movimentacoes' => $movimentacoes
            ]);
            break;
        
        // ============================================
        // LOGS DE AUDITORIA
        // ============================================
        
        case 'logs':
            $limite = $_GET['limite'] ?? 50;
            $tipo = $_GET['tipo'] ?? '';
            
            $sql = "SELECT * FROM logs_sistema WHERE 1=1";
            $params = [];
            
            if ($tipo) {
                $sql .= " AND tipo = :tipo";
                $params['tipo'] = $tipo;
            }
            
            $sql .= " ORDER BY data_hora DESC LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'logs' => $logs
            ]);
            break;
        
        // ============================================
        // CORRIGIR DISCREPÂNCIA (MANUAL)
        // ============================================
        
        case 'corrigir_discrepancia':
            // Esta ação deve ser usada com cuidado!
            // Apenas para ajustes manuais após validação
            
            $conta_id = $_POST['conta_id'];
            $valor_ajuste = $_POST['valor_ajuste'];
            $motivo = $_POST['motivo'];
            
            if (!$conta_id || !$valor_ajuste || !$motivo) {
                throw new Exception('Dados incompletos para correção');
            }
            
            $pdo->beginTransaction();
            
            try {
                // Registrar ajuste
                $stmt = $pdo->prepare("
                    INSERT INTO movimentacoes_financeiras 
                    (conta_id, tipo, categoria, descricao, valor, data_movimentacao, 
                     observacoes, tipo_movimentacao)
                    VALUES 
                    (:conta_id, :tipo, 'ajuste', 'Ajuste de Auditoria', :valor, CURDATE(),
                     :motivo, 'ajuste_auditoria')
                ");
                
                $tipo = $valor_ajuste > 0 ? 'receita' : 'despesa';
                $valor_abs = abs($valor_ajuste);
                
                $stmt->execute([
                    'conta_id' => $conta_id,
                    'tipo' => $tipo,
                    'valor' => $valor_abs,
                    'motivo' => $motivo
                ]);
                
                // Atualizar saldo
                $stmt = $pdo->prepare("
                    UPDATE contas_bancarias 
                    SET saldo_atual = saldo_atual + :valor
                    WHERE id = :conta_id
                ");
                $stmt->execute([
                    'conta_id' => $conta_id,
                    'valor' => $valor_ajuste
                ]);
                
                // Log
                $stmt = $pdo->prepare("
                    INSERT INTO logs_sistema (tipo, mensagem, detalhes)
                    VALUES ('AJUSTE_MANUAL', :mensagem, :detalhes)
                ");
                $stmt->execute([
                    'mensagem' => "Ajuste manual realizado na conta ID $conta_id",
                    'detalhes' => json_encode([
                        'conta_id' => $conta_id,
                        'valor' => $valor_ajuste,
                        'motivo' => $motivo
                    ])
                ]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Ajuste realizado com sucesso'
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;
        
        // ============================================
        // ESTATÍSTICAS DE AUDITORIA
        // ============================================
        
        case 'estatisticas':
            // Últimos 6 meses
            $stmt = $pdo->query("
                SELECT 
                    DATE_FORMAT(data_movimentacao, '%Y-%m') as periodo,
                    SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receitas,
                    SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesas
                FROM movimentacoes_financeiras
                WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(data_movimentacao, '%Y-%m')
                ORDER BY periodo DESC
            ");
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total de logs críticos
            $stmt = $pdo->query("
                SELECT COUNT(*) as total
                FROM logs_sistema
                WHERE tipo = 'AUDITORIA_CRITICA'
                AND data_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $logs_criticos = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'historico' => $historico,
                'logs_criticos_30dias' => $logs_criticos
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
