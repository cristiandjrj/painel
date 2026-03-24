<?php
/**
 * API de Financeiro Pessoal - EventProDJ
 * Gestão de finanças pessoais separadas da empresa
 * Baseado na lógica exata do HTML
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        
        // ============================================
        // CONTAS PESSOAIS
        // ============================================
        
        case 'contas_list':
            $stmt = $pdo->query("
                SELECT * FROM contas_pessoais 
                WHERE ativo = TRUE 
                ORDER BY nome ASC
            ");
            $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'contas' => $contas
            ]);
            break;
        
        case 'conta_create':
            $saldoInicial = $_POST['saldo_inicial'] ?? 0;
            
            $stmt = $pdo->prepare("
                INSERT INTO contas_pessoais 
                (nome, tipo, banco, saldo_inicial, saldo_atual, cor, icone)
                VALUES (:nome, :tipo, :banco, :saldo_inicial, :saldo_atual, :cor, :icone)
            ");
            
            $stmt->execute([
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'banco' => $_POST['banco'] ?? null,
                'saldo_inicial' => $saldoInicial,
                'saldo_atual' => $saldoInicial, // Saldo atual inicia igual ao inicial
                'cor' => $_POST['cor'] ?? '#FF6B35',
                'icone' => $_POST['icone'] ?? 'wallet'
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conta adicionada com sucesso!',
                'conta_id' => $pdo->lastInsertId()
            ]);
            break;
        
        case 'conta_update':
            $stmt = $pdo->prepare("
                UPDATE contas_pessoais 
                SET nome = :nome,
                    tipo = :tipo,
                    banco = :banco,
                    cor = :cor,
                    icone = :icone
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $_POST['id'],
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'banco' => $_POST['banco'] ?? null,
                'cor' => $_POST['cor'] ?? '#FF6B35',
                'icone' => $_POST['icone'] ?? 'wallet'
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conta atualizada com sucesso!'
            ]);
            break;
        
        case 'conta_delete':
            $stmt = $pdo->prepare("UPDATE contas_pessoais SET ativo = FALSE WHERE id = :id");
            $stmt->execute(['id' => $_POST['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conta removida com sucesso!'
            ]);
            break;
        
        // ============================================
        // CATEGORIAS PESSOAIS
        // ============================================
        
        case 'categorias_list':
            $tipo = $_GET['tipo'] ?? '';
            
            $sql = "SELECT * FROM categorias_pessoais WHERE ativo = TRUE";
            $params = [];
            
            if ($tipo) {
                $sql .= " AND tipo = :tipo";
                $params['tipo'] = $tipo;
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
        
        case 'categoria_create':
            $stmt = $pdo->prepare("
                INSERT INTO categorias_pessoais (nome, tipo, cor, icone)
                VALUES (:nome, :tipo, :cor, :icone)
            ");
            
            $stmt->execute([
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'cor' => $_POST['cor'] ?? '#FF6B35',
                'icone' => $_POST['icone'] ?? 'tag'
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoria criada com sucesso!',
                'categoria_id' => $pdo->lastInsertId()
            ]);
            break;
        
        // ============================================
        // RECEITAS PESSOAIS
        // ============================================
        
        case 'receitas_list':
            $mes = $_GET['mes'] ?? '';
            $ano = $_GET['ano'] ?? '';
            $conta_id = $_GET['conta_id'] ?? '';
            
            $sql = "
                SELECT 
                    rp.*,
                    cp.nome as conta_nome,
                    cat.nome as categoria_nome,
                    cat.cor as categoria_cor,
                    cat.icone as categoria_icone
                FROM receitas_pessoais rp
                LEFT JOIN contas_pessoais cp ON rp.conta_id = cp.id
                LEFT JOIN categorias_pessoais cat ON rp.categoria_id = cat.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($mes && $ano) {
                $sql .= " AND MONTH(rp.data_receita) = :mes AND YEAR(rp.data_receita) = :ano";
                $params['mes'] = $mes;
                $params['ano'] = $ano;
            }
            
            if ($conta_id) {
                $sql .= " AND rp.conta_id = :conta_id";
                $params['conta_id'] = $conta_id;
            }
            
            $sql .= " ORDER BY rp.data_receita DESC, rp.id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'receitas' => $receitas
            ]);
            break;
        
        case 'receita_create':
            // LÓGICA EXATA DO HTML: 
            // 1. Criar receita
            // 2. Atualizar saldo da conta (trigger faz isso automaticamente)
            
            $stmt = $pdo->prepare("
                INSERT INTO receitas_pessoais 
                (descricao, categoria_id, valor, data_receita, conta_id, observacoes)
                VALUES (:descricao, :categoria_id, :valor, :data_receita, :conta_id, :observacoes)
            ");
            
            $stmt->execute([
                'descricao' => $_POST['descricao'],
                'categoria_id' => $_POST['categoria_id'] ?? null,
                'valor' => $_POST['valor'],
                'data_receita' => $_POST['data_receita'],
                'conta_id' => $_POST['conta_id'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? null
            ]);
            
            // O trigger trg_after_insert_receita_pessoal atualiza o saldo automaticamente
            
            echo json_encode([
                'success' => true,
                'message' => 'Receita pessoal adicionada com sucesso!',
                'receita_id' => $pdo->lastInsertId()
            ]);
            break;
        
        case 'receita_delete':
            // O trigger trg_after_delete_receita_pessoal reverte o saldo automaticamente
            
            $stmt = $pdo->prepare("DELETE FROM receitas_pessoais WHERE id = :id");
            $stmt->execute(['id' => $_POST['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Receita excluída com sucesso!'
            ]);
            break;
        
        // ============================================
        // DESPESAS PESSOAIS
        // ============================================
        
        case 'despesas_list':
            $mes = $_GET['mes'] ?? '';
            $ano = $_GET['ano'] ?? '';
            $conta_id = $_GET['conta_id'] ?? '';
            
            $sql = "
                SELECT 
                    dp.*,
                    cp.nome as conta_nome,
                    cat.nome as categoria_nome,
                    cat.cor as categoria_cor,
                    cat.icone as categoria_icone
                FROM despesas_pessoais dp
                LEFT JOIN contas_pessoais cp ON dp.conta_id = cp.id
                LEFT JOIN categorias_pessoais cat ON dp.categoria_id = cat.id
                WHERE 1=1
            ";
            $params = [];
            
            if ($mes && $ano) {
                $sql .= " AND MONTH(dp.data_despesa) = :mes AND YEAR(dp.data_despesa) = :ano";
                $params['mes'] = $mes;
                $params['ano'] = $ano;
            }
            
            if ($conta_id) {
                $sql .= " AND dp.conta_id = :conta_id";
                $params['conta_id'] = $conta_id;
            }
            
            $sql .= " ORDER BY dp.data_despesa DESC, dp.id DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'despesas' => $despesas
            ]);
            break;
        
        case 'despesa_create':
            // LÓGICA EXATA DO HTML:
            // 1. Criar despesa
            // 2. Atualizar saldo da conta (trigger faz isso automaticamente)
            
            $stmt = $pdo->prepare("
                INSERT INTO despesas_pessoais 
                (descricao, categoria_id, valor, data_despesa, conta_id, observacoes)
                VALUES (:descricao, :categoria_id, :valor, :data_despesa, :conta_id, :observacoes)
            ");
            
            $stmt->execute([
                'descricao' => $_POST['descricao'],
                'categoria_id' => $_POST['categoria_id'] ?? null,
                'valor' => $_POST['valor'],
                'data_despesa' => $_POST['data_despesa'],
                'conta_id' => $_POST['conta_id'] ?? null,
                'observacoes' => $_POST['observacoes'] ?? null
            ]);
            
            // O trigger trg_after_insert_despesa_pessoal atualiza o saldo automaticamente
            
            echo json_encode([
                'success' => true,
                'message' => 'Despesa pessoal adicionada com sucesso!',
                'despesa_id' => $pdo->lastInsertId()
            ]);
            break;
        
        case 'despesa_delete':
            // O trigger trg_after_delete_despesa_pessoal reverte o saldo automaticamente
            
            $stmt = $pdo->prepare("DELETE FROM despesas_pessoais WHERE id = :id");
            $stmt->execute(['id' => $_POST['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Despesa excluída com sucesso!'
            ]);
            break;
        
        // ============================================
        // RESUMO E ESTATÍSTICAS
        // ============================================
        
        case 'resumo':
            $stmt = $pdo->query("SELECT * FROM vw_resumo_financeiro_pessoal");
            $resumo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'resumo' => $resumo
            ]);
            break;
        
        case 'despesas_por_categoria':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            
            $stmt = $pdo->prepare("
                SELECT 
                    cat.nome,
                    cat.cor,
                    cat.icone,
                    COUNT(dp.id) as total_despesas,
                    COALESCE(SUM(dp.valor), 0) as total_valor
                FROM categorias_pessoais cat
                LEFT JOIN despesas_pessoais dp ON cat.id = dp.categoria_id
                    AND MONTH(dp.data_despesa) = :mes
                    AND YEAR(dp.data_despesa) = :ano
                WHERE cat.tipo = 'despesa' AND cat.ativo = TRUE
                GROUP BY cat.id, cat.nome, cat.cor, cat.icone
                HAVING total_valor > 0
                ORDER BY total_valor DESC
            ");
            
            $stmt->execute(['mes' => $mes, 'ano' => $ano]);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias
            ]);
            break;
        
        case 'receitas_por_categoria':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            
            $stmt = $pdo->prepare("
                SELECT 
                    cat.nome,
                    cat.cor,
                    cat.icone,
                    COUNT(rp.id) as total_receitas,
                    COALESCE(SUM(rp.valor), 0) as total_valor
                FROM categorias_pessoais cat
                LEFT JOIN receitas_pessoais rp ON cat.id = rp.categoria_id
                    AND MONTH(rp.data_receita) = :mes
                    AND YEAR(rp.data_receita) = :ano
                WHERE cat.tipo = 'receita' AND cat.ativo = TRUE
                GROUP BY cat.id, cat.nome, cat.cor, cat.icone
                HAVING total_valor > 0
                ORDER BY total_valor DESC
            ");
            
            $stmt->execute(['mes' => $mes, 'ano' => $ano]);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias
            ]);
            break;
        
        case 'fluxo_mensal':
            $limite = $_GET['limite'] ?? 6;
            
            $stmt = $pdo->prepare("
                SELECT 
                    DATE_FORMAT(data, '%Y-%m') as mes_ano,
                    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as receitas,
                    COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as despesas,
                    COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE -valor END), 0) as saldo
                FROM (
                    SELECT data_receita as data, valor, 'receita' as tipo FROM receitas_pessoais
                    UNION ALL
                    SELECT data_despesa as data, valor, 'despesa' as tipo FROM despesas_pessoais
                ) movimentacoes
                WHERE data >= DATE_SUB(CURDATE(), INTERVAL :limite MONTH)
                GROUP BY DATE_FORMAT(data, '%Y-%m')
                ORDER BY mes_ano DESC
                LIMIT :limite
            ");
            
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            
            $fluxo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'fluxo' => array_reverse($fluxo)
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
