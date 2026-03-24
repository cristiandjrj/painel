<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    // CNPJ Management
    case 'list_cnpjs':
        listarCNPJs($pdo, $userId);
        break;
    case 'save_cnpj':
        salvarCNPJ($pdo, $userId);
        break;
    case 'activate_cnpj':
        ativarCNPJ($pdo, $userId);
        break;
    case 'delete_cnpj':
        excluirCNPJ($pdo, $userId);
        break;

    // Receitas MEI
    case 'get_receitas':
        obterReceitas($pdo, $userId);
        break;
    case 'save_receitas':
        salvarReceitas($pdo, $userId);
        break;
    case 'add_receita':
        adicionarReceita($pdo, $userId);
        break;
    case 'update_receita':
        atualizarReceita($pdo, $userId);
        break;
    case 'delete_receita':
        excluirReceita($pdo, $userId);
        break;

    // Limites e Relatório
    case 'get_limites':
        obterLimites($pdo, $userId);
        break;
    case 'auto_fill':
        preencherAutomatico($pdo, $userId);
        break;
    case 'debug_contas':
        debugContas($pdo, $userId);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
}

// ===== CNPJ FUNCTIONS =====

function listarCNPJs($pdo, $userId) {
    try {
        // Buscar CNPJs do usuário: tanto os vinculados a contas do usuário
        // quanto os que foram criados pelo usuário mas ainda sem conta vinculada.
        // Usa UNION para garantir que CNPJs sem conta também apareçam.
        $stmt = $pdo->prepare("
            SELECT mc.*,
                   GROUP_CONCAT(mcc.conta_bancaria_id) as contas_ids,
                   GROUP_CONCAT(cb.nome) as contas_nomes
            FROM mei_cnpjs mc
            LEFT JOIN mei_cnpj_contas mcc ON mc.id = mcc.mei_cnpj_id
            LEFT JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id AND cb.user_id = ?
            WHERE (
                -- CNPJs com pelo menos uma conta vinculada ao usuário
                mc.id IN (
                    SELECT DISTINCT mcc2.mei_cnpj_id
                    FROM mei_cnpj_contas mcc2
                    JOIN contas_bancarias cb2 ON mcc2.conta_bancaria_id = cb2.id
                    WHERE cb2.user_id = ?
                )
                OR
                -- CNPJs sem NENHUMA conta vinculada (recém criados)
                mc.id NOT IN (SELECT DISTINCT mei_cnpj_id FROM mei_cnpj_contas)
            )
            GROUP BY mc.id
            ORDER BY mc.ativo DESC, mc.nome
        ");
        $stmt->execute([$userId, $userId]);
        $cnpjs = $stmt->fetchAll();

        // Formatar contas como arrays
        foreach ($cnpjs as &$cnpj) {
            $cnpj['contas'] = $cnpj['contas_ids'] ? array_map('intval', explode(',', $cnpj['contas_ids'])) : [];
            $cnpj['contas_nomes'] = $cnpj['contas_nomes'] ? explode(',', $cnpj['contas_nomes']) : [];
            unset($cnpj['contas_ids']);
        }

        echo json_encode(['success' => true, 'cnpjs' => $cnpjs]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function salvarCNPJ($pdo, $userId) {
    try {
        $id = $_POST['id'] ?? null;
        $cnpj = $_POST['cnpj'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $contas = $_POST['contas'] ?? [];

        if (is_string($contas)) {
            $contas = json_decode($contas, true) ?: [];
        }

        $pdo->beginTransaction();

        // Verificar contas duplicadas em outros CNPJs (dentro do mesmo usuário)
        if (!empty($contas)) {
            $placeholders = implode(',', array_fill(0, count($contas), '?'));
            $sql = "SELECT mcc.conta_bancaria_id, mc.nome as mei_nome, mc.cnpj as mei_cnpj
                    FROM mei_cnpj_contas mcc
                    JOIN mei_cnpjs mc ON mcc.mei_cnpj_id = mc.id
                    JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                    WHERE mcc.conta_bancaria_id IN ($placeholders)
                    AND cb.user_id = ?";
            $params = array_map('intval', $contas);
            $params[] = $userId;

            if ($id) {
                $sql .= " AND mc.id != ?";
                $params[] = intval($id);
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $duplicatas = $stmt->fetchAll();

            if (!empty($duplicatas)) {
                $msgs = [];
                foreach ($duplicatas as $dup) {
                    $msgs[] = "Conta já vinculada ao MEI: {$dup['mei_nome']} ({$dup['mei_cnpj']})";
                }
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => implode('; ', $msgs)]);
                return;
            }
        }

        if ($id) {
            // Atualizar
            $stmt = $pdo->prepare("UPDATE mei_cnpjs SET cnpj = ?, nome = ? WHERE id = ?");
            $stmt->execute([$cnpj, $nome, $id]);

            // Remover contas antigas
            $pdo->prepare("DELETE FROM mei_cnpj_contas WHERE mei_cnpj_id = ?")->execute([$id]);
        } else {
            // Criar novo
            $stmt = $pdo->prepare("INSERT INTO mei_cnpjs (cnpj, nome, ativo) VALUES (?, ?, ?)");
            // Primeiro CNPJ do usuário fica ativo
            $countStmt = $pdo->prepare("
                SELECT COUNT(*) FROM mei_cnpjs mc
                JOIN mei_cnpj_contas mcc ON mc.id = mcc.mei_cnpj_id
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE cb.user_id = ?
            ");
            $countStmt->execute([$userId]);
            $count = $countStmt->fetchColumn();
            $ativo = $count == 0 ? 1 : 0;
            $stmt->execute([$cnpj, $nome, $ativo]);
            $id = $pdo->lastInsertId();
        }

        // Inserir contas vinculadas
        if (!empty($contas)) {
            $stmtConta = $pdo->prepare("INSERT INTO mei_cnpj_contas (mei_cnpj_id, conta_bancaria_id) VALUES (?, ?)");
            foreach ($contas as $contaId) {
                $stmtConta->execute([$id, intval($contaId)]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'CNPJ salvo com sucesso!', 'id' => $id]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function ativarCNPJ($pdo, $userId) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id) throw new Exception('ID não informado');

        $pdo->beginTransaction();

        // Desativar todos os CNPJs do usuário
        $stmt = $pdo->prepare("
            UPDATE mei_cnpjs SET ativo = 0
            WHERE id IN (
                SELECT DISTINCT mcc.mei_cnpj_id
                FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE cb.user_id = ?
            )
        ");
        $stmt->execute([$userId]);

        // Ativar o selecionado (verificando que pertence ao usuário)
        $stmt = $pdo->prepare("
            UPDATE mei_cnpjs SET ativo = 1
            WHERE id = ?
            AND id IN (
                SELECT DISTINCT mcc.mei_cnpj_id
                FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE cb.user_id = ?
            )
        ");
        $stmt->execute([$id, $userId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'CNPJ ativado!']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function excluirCNPJ($pdo, $userId) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id) throw new Exception('ID não informado');

        $pdo->beginTransaction();

        // Verificar se era ativo e pertence ao usuário
        $stmt = $pdo->prepare("
            SELECT mc.ativo FROM mei_cnpjs mc
            JOIN mei_cnpj_contas mcc ON mc.id = mcc.mei_cnpj_id
            JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
            WHERE mc.id = ? AND cb.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $userId]);
        $cnpj = $stmt->fetch();
        if (!$cnpj) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'CNPJ não encontrado.']);
            return;
        }
        $eraAtivo = $cnpj['ativo'];

        // Excluir (cascade deleta contas vinculadas)
        $pdo->prepare("DELETE FROM mei_cnpjs WHERE id = ?")->execute([$id]);

        // Se era ativo, ativar o primeiro restante do usuário
        if ($eraAtivo) {
            $stmt = $pdo->prepare("
                SELECT mc.id FROM mei_cnpjs mc
                JOIN mei_cnpj_contas mcc ON mc.id = mcc.mei_cnpj_id
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE cb.user_id = ?
                ORDER BY mc.id LIMIT 1
            ");
            $stmt->execute([$userId]);
            $primeiro = $stmt->fetch();
            if ($primeiro) {
                $pdo->prepare("UPDATE mei_cnpjs SET ativo = 1 WHERE id = ?")->execute([$primeiro['id']]);
            }
        }

        // Remover receitas vinculadas
        $pdo->prepare("DELETE FROM mei_receitas WHERE mei_cnpj_id = ?")->execute([$id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'CNPJ excluído!']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ===== RECEITAS FUNCTIONS =====

function obterReceitas($pdo, $userId) {
    try {
        $mes = intval($_GET['mes'] ?? date('n'));
        $ano = intval($_GET['ano'] ?? date('Y'));
        $cnpjId = $_GET['cnpj_id'] ?? 'todos';

        // Buscar contas vinculadas ao CNPJ (específico ou todos)
        if ($cnpjId !== 'todos') {
            $stmt = $pdo->prepare("
                SELECT mcc.conta_bancaria_id FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE mcc.mei_cnpj_id = ? AND cb.user_id = ?
            ");
            $stmt->execute([intval($cnpjId), $userId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT mcc.conta_bancaria_id FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE cb.user_id = ?
            ");
            $stmt->execute([$userId]);
        }
        $contasFiltro = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        $todasReceitas = [];

        if (!empty($contasFiltro)) {
            $placeholders = implode(',', array_fill(0, count($contasFiltro), '?'));

            // FONTE 1: Sinais dos eventos (com CPF do cliente via JOIN)
            $stmtSinal = $pdo->prepare("
                SELECT
                    COALESCE(e.data_sinal, e.data_evento) as data_receita,
                    c.nome as cliente,
                    COALESCE(c.cpf, '') as cpf_cnpj,
                    CONCAT('Sinal - ', COALESCE(e.tipo, 'Evento')) as documento_fiscal,
                    e.valor_sinal as valor,
                    NULL as id
                FROM eventos_eventpro e
                JOIN clientes_eventpro c ON e.cliente_id = c.id
                WHERE MONTH(COALESCE(e.data_sinal, e.data_evento)) = ?
                AND YEAR(COALESCE(e.data_sinal, e.data_evento)) = ?
                AND e.user_id = ?
                AND e.status != 'cancelado'
                AND e.valor_sinal > 0
                AND (e.forma_pagamento_sinal IS NULL OR LOWER(e.forma_pagamento_sinal) != 'dinheiro')
                AND e.conta_sinal IS NOT NULL
                AND e.conta_sinal IN ($placeholders)
                ORDER BY data_receita ASC
            ");
            $stmtSinal->execute(array_merge([$mes, $ano, $userId], $contasFiltro));
            $sinais = $stmtSinal->fetchAll();

            // FONTE 2: Pagamentos via movimentacoes_financeiras (restante/parcial)
            // Exclui sinal_evento (já coberto pela FONTE 1) e descrições "Sinal - %"
            // (registros antigos de sinal inseridos via modal de pagamento)
            $stmtPag = $pdo->prepare("
                SELECT
                    mf.data_movimentacao as data_receita,
                    mf.descricao as cliente,
                    '' as cpf_cnpj,
                    mf.descricao as documento_fiscal,
                    mf.valor,
                    CONCAT('mov_', mf.id) as id
                FROM movimentacoes_financeiras mf
                WHERE MONTH(mf.data_movimentacao) = ?
                AND YEAR(mf.data_movimentacao) = ?
                AND mf.user_id = ?
                AND mf.tipo = 'receita'
                AND mf.tipo_movimentacao = 'pagamento_evento'
                AND mf.tipo_movimentacao != 'sinal_evento'
                AND mf.descricao NOT LIKE 'Sinal - %'
                AND (mf.forma_pagamento IS NULL OR LOWER(mf.forma_pagamento) != 'dinheiro')
                AND mf.conta_id IN ($placeholders)
                ORDER BY mf.data_movimentacao ASC
            ");
            $stmtPag->execute(array_merge([$mes, $ano, $userId], $contasFiltro));
            $pagamentos = $stmtPag->fetchAll();

            $todasReceitas = array_merge($sinais, $pagamentos);
        }

        // FONTE 3: Receitas manuais do mei_receitas (entradas avulsas)
        $sqlManual = "
            SELECT mr.id, mr.data_receita, mr.cliente, mr.cpf_cnpj,
                   mr.documento_fiscal, mr.valor, mr.mei_cnpj_id
            FROM mei_receitas mr
            JOIN mei_cnpj_contas mcc ON mr.mei_cnpj_id = mcc.mei_cnpj_id
            JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
            WHERE mr.mes = ? AND mr.ano = ? AND cb.user_id = ?
            AND mr.documento_fiscal NOT LIKE 'Evento:%'
            AND mr.documento_fiscal NOT LIKE 'Sinal -%'
        ";
        $paramsManual = [$mes, $ano, $userId];

        if ($cnpjId !== 'todos') {
            $sqlManual .= " AND mr.mei_cnpj_id = ?";
            $paramsManual[] = intval($cnpjId);
        }

        $sqlManual .= " GROUP BY mr.id ORDER BY mr.data_receita ASC";

        $stmtManual = $pdo->prepare($sqlManual);
        $stmtManual->execute($paramsManual);
        $receitasManuais = $stmtManual->fetchAll();

        // Combinar todas as fontes
        $todasReceitas = array_merge($todasReceitas, $receitasManuais);

        // Ordenar por data
        usort($todasReceitas, function($a, $b) {
            return strcmp($a['data_receita'] ?? '', $b['data_receita'] ?? '');
        });

        $total = array_sum(array_column($todasReceitas, 'valor'));

        echo json_encode([
            'success' => true,
            'receitas' => $todasReceitas,
            'total' => $total
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function salvarReceitas($pdo, $userId) {
    try {
        $mes = $_POST['mes'] ?? null;
        $ano = $_POST['ano'] ?? null;
        $cnpjId = $_POST['cnpj_id'] ?? null;
        $receitas = $_POST['receitas'] ?? '[]';

        if (is_string($receitas)) {
            $receitas = json_decode($receitas, true) ?: [];
        }

        $pdo->beginTransaction();

        if ($cnpjId && $cnpjId !== 'todos') {
            // CNPJ específico: limpar só as receitas desse CNPJ no período
            $pdo->prepare("DELETE FROM mei_receitas WHERE mes = ? AND ano = ? AND mei_cnpj_id = ?")
                ->execute([intval($mes), intval($ano), intval($cnpjId)]);
        } else {
            // "Todos": coletar todos os mei_cnpj_ids das receitas a salvar
            $cnpjIds = [];
            foreach ($receitas as $r) {
                if (!empty($r['mei_cnpj_id'])) {
                    $cnpjIds[intval($r['mei_cnpj_id'])] = true;
                }
            }

            // Limpar receitas dos CNPJs que serão re-importados
            if (!empty($cnpjIds)) {
                $placeholders = implode(',', array_fill(0, count($cnpjIds), '?'));
                $pdo->prepare("DELETE FROM mei_receitas WHERE mes = ? AND ano = ? AND mei_cnpj_id IN ($placeholders)")
                    ->execute(array_merge([intval($mes), intval($ano)], array_keys($cnpjIds)));
            }

            // Limpar também receitas sem CNPJ (órfãs)
            $pdo->prepare("DELETE FROM mei_receitas WHERE mes = ? AND ano = ? AND mei_cnpj_id IS NULL")
                ->execute([intval($mes), intval($ano)]);
        }

        // Inserir novas
        $stmtInsert = $pdo->prepare("
            INSERT INTO mei_receitas (mei_cnpj_id, mes, ano, data_receita, cliente, cpf_cnpj, documento_fiscal, valor)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($receitas as $r) {
            // Converter data DD/MM/YYYY para YYYY-MM-DD
            $dataReceita = $r['data'] ?? '';
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataReceita)) {
                $partes = explode('/', $dataReceita);
                $dataReceita = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
            }

            // Usar o mei_cnpj_id da receita (vindo do preencherAutomatico), ou do CNPJ selecionado
            $meiCnpjId = $r['mei_cnpj_id'] ?? ($cnpjId !== 'todos' ? $cnpjId : null);

            $stmtInsert->execute([
                $meiCnpjId ? intval($meiCnpjId) : null,
                intval($mes),
                intval($ano),
                $dataReceita,
                $r['cliente'] ?? 'Cliente',
                $r['cpf_cnpj'] ?? null,
                $r['documento'] ?? 'Sem documento',
                floatval($r['valor'] ?? 0)
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Relatório salvo com sucesso!']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function adicionarReceita($pdo, $userId) {
    try {
        $mes = $_POST['mes'] ?? date('n');
        $ano = $_POST['ano'] ?? date('Y');
        $cnpjId = $_POST['cnpj_id'] ?? null;
        $data = $_POST['data'] ?? date('Y-m-d');
        $cliente = $_POST['cliente'] ?? '';
        $cpfCnpj = $_POST['cpf_cnpj'] ?? null;
        $documento = $_POST['documento'] ?? 'Sem documento';
        $valor = floatval($_POST['valor'] ?? 0);

        // Converter data DD/MM/YYYY para YYYY-MM-DD
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            $partes = explode('/', $data);
            $data = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }

        $stmt = $pdo->prepare("
            INSERT INTO mei_receitas (mei_cnpj_id, mes, ano, data_receita, cliente, cpf_cnpj, documento_fiscal, valor)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $cnpjId && $cnpjId !== 'todos' ? intval($cnpjId) : null,
            intval($mes),
            intval($ano),
            $data,
            $cliente,
            $cpfCnpj,
            $documento,
            $valor
        ]);

        echo json_encode(['success' => true, 'message' => 'Receita adicionada!', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function atualizarReceita($pdo, $userId) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id) throw new Exception('ID não informado');

        $data = $_POST['data'] ?? '';
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
            $partes = explode('/', $data);
            $data = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
        }

        $stmt = $pdo->prepare("
            UPDATE mei_receitas SET
                data_receita = ?,
                cliente = ?,
                cpf_cnpj = ?,
                documento_fiscal = ?,
                valor = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data,
            $_POST['cliente'] ?? '',
            $_POST['cpf_cnpj'] ?? null,
            $_POST['documento'] ?? 'Sem documento',
            floatval($_POST['valor'] ?? 0),
            $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Receita atualizada!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function excluirReceita($pdo, $userId) {
    try {
        $id = $_POST['id'] ?? null;
        if (!$id) throw new Exception('ID não informado');

        $pdo->prepare("DELETE FROM mei_receitas WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Receita excluída!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ===== LIMITES =====

function obterLimites($pdo, $userId) {
    try {
        $cnpjId = $_GET['cnpj_id'] ?? 'todos';
        $anoAtual = intval(date('Y'));
        $mesAtual = intval(date('n'));

        $LIMITE_MENSAL = 6750;
        $LIMITE_ANUAL = 81000;

        $totalMes = 0;
        $totalAno = 0;

        // Buscar contas vinculadas ao CNPJ (específico ou todos)
        if ($cnpjId !== 'todos') {
            $stmt = $pdo->prepare("
                SELECT mcc.conta_bancaria_id FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE mcc.mei_cnpj_id = ? AND cb.user_id = ?
            ");
            $stmt->execute([intval($cnpjId), $userId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT mcc.conta_bancaria_id FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE cb.user_id = ?
            ");
            $stmt->execute([$userId]);
        }
        $contasVinculadas = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (!empty($contasVinculadas)) {
            $placeholders = implode(',', array_fill(0, count($contasVinculadas), '?'));

            // FONTE 1: Sinais dos eventos (direto de eventos_eventpro, igual obterReceitas)
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(e.valor_sinal), 0) FROM eventos_eventpro e
                WHERE MONTH(COALESCE(e.data_sinal, e.data_evento)) = ?
                AND YEAR(COALESCE(e.data_sinal, e.data_evento)) = ?
                AND e.user_id = ? AND e.status != 'cancelado'
                AND e.valor_sinal > 0
                AND (e.forma_pagamento_sinal IS NULL OR LOWER(e.forma_pagamento_sinal) != 'dinheiro')
                AND e.conta_sinal IS NOT NULL
                AND e.conta_sinal IN ($placeholders)
            ");
            $stmt->execute(array_merge([$mesAtual, $anoAtual, $userId], $contasVinculadas));
            $totalMes += floatval($stmt->fetchColumn());

            // FONTE 1 anual
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(e.valor_sinal), 0) FROM eventos_eventpro e
                WHERE YEAR(COALESCE(e.data_sinal, e.data_evento)) = ?
                AND e.user_id = ? AND e.status != 'cancelado'
                AND e.valor_sinal > 0
                AND (e.forma_pagamento_sinal IS NULL OR LOWER(e.forma_pagamento_sinal) != 'dinheiro')
                AND e.conta_sinal IS NOT NULL
                AND e.conta_sinal IN ($placeholders)
            ");
            $stmt->execute(array_merge([$anoAtual, $userId], $contasVinculadas));
            $totalAno += floatval($stmt->fetchColumn());

            // FONTE 2: Pagamentos via movimentacoes_financeiras (excluindo sinais já contados acima)
            $mfWhere = "mf.user_id = ? AND mf.tipo = 'receita'
                        AND mf.tipo_movimentacao = 'pagamento_evento'
                        AND mf.tipo_movimentacao != 'sinal_evento'
                        AND mf.descricao NOT LIKE 'Sinal - %'
                        AND (mf.forma_pagamento IS NULL OR LOWER(mf.forma_pagamento) != 'dinheiro')
                        AND mf.conta_id IN ($placeholders)";

            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(mf.valor), 0) FROM movimentacoes_financeiras mf
                WHERE MONTH(mf.data_movimentacao) = ? AND YEAR(mf.data_movimentacao) = ?
                AND $mfWhere
            ");
            $stmt->execute(array_merge([$mesAtual, $anoAtual, $userId], $contasVinculadas));
            $totalMes += floatval($stmt->fetchColumn());

            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(mf.valor), 0) FROM movimentacoes_financeiras mf
                WHERE YEAR(mf.data_movimentacao) = ?
                AND $mfWhere
            ");
            $stmt->execute(array_merge([$anoAtual, $userId], $contasVinculadas));
            $totalAno += floatval($stmt->fetchColumn());

            // FONTE 3: Receitas manuais (excluindo auto-preenchidas de eventos/sinais)
            $sqlManualBase = "SELECT COALESCE(SUM(mr.valor), 0) FROM mei_receitas mr
                WHERE mr.ano = ? AND mr.documento_fiscal NOT LIKE 'Evento:%'
                AND mr.documento_fiscal NOT LIKE 'Sinal -%'";

            if ($cnpjId !== 'todos') {
                $sqlManualBase .= " AND mr.mei_cnpj_id = ?";

                // Mês
                $stmt = $pdo->prepare($sqlManualBase . " AND mr.mes = ?");
                $stmt->execute([$anoAtual, intval($cnpjId), $mesAtual]);
                $totalMes += floatval($stmt->fetchColumn());

                // Ano
                $stmt = $pdo->prepare($sqlManualBase);
                $stmt->execute([$anoAtual, intval($cnpjId)]);
                $totalAno += floatval($stmt->fetchColumn());
            } else {
                // Mês (todas receitas manuais do user via contas vinculadas)
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(DISTINCT mr.valor), 0) FROM mei_receitas mr
                    JOIN mei_cnpj_contas mcc ON mr.mei_cnpj_id = mcc.mei_cnpj_id
                    JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                    WHERE mr.mes = ? AND mr.ano = ? AND cb.user_id = ?
                    AND mr.documento_fiscal NOT LIKE 'Evento:%'
                    AND mr.documento_fiscal NOT LIKE 'Sinal -%'
                    GROUP BY mr.id
                ");
                $stmt->execute([$mesAtual, $anoAtual, $userId]);
                $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $totalMes += array_sum($rows);

                // Ano
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(DISTINCT mr.valor), 0) FROM mei_receitas mr
                    JOIN mei_cnpj_contas mcc ON mr.mei_cnpj_id = mcc.mei_cnpj_id
                    JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                    WHERE mr.ano = ? AND cb.user_id = ?
                    AND mr.documento_fiscal NOT LIKE 'Evento:%'
                    AND mr.documento_fiscal NOT LIKE 'Sinal -%'
                    GROUP BY mr.id
                ");
                $stmt->execute([$anoAtual, $userId]);
                $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $totalAno += array_sum($rows);
            }
        }

        $percentMensal = $LIMITE_MENSAL > 0 ? min(($totalMes / $LIMITE_MENSAL) * 100, 100) : 0;
        $percentAnual = $LIMITE_ANUAL > 0 ? min(($totalAno / $LIMITE_ANUAL) * 100, 100) : 0;

        // Determinar status
        $status = 'Regular';
        if ($percentMensal >= 100 || $percentAnual >= 100) {
            $status = 'LIMITE EXCEDIDO';
        } elseif ($percentMensal >= 90 || $percentAnual >= 90) {
            $status = 'Atenção';
        } elseif ($percentMensal >= 80 || $percentAnual >= 80) {
            $status = 'Próximo do Limite';
        }

        $mesesRestantes = 12 - intval(date('n')) + 1;

        echo json_encode([
            'success' => true,
            'limites' => [
                'totalMes' => $totalMes,
                'totalAno' => $totalAno,
                'percentMensal' => round($percentMensal, 1),
                'percentAnual' => round($percentAnual, 1),
                'limiteMensal' => $LIMITE_MENSAL,
                'limiteAnual' => $LIMITE_ANUAL,
                'status' => $status,
                'mesesRestantes' => $mesesRestantes
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function preencherAutomatico($pdo, $userId) {
    try {
        $mes = intval($_POST['mes'] ?? date('n'));
        $ano = intval($_POST['ano'] ?? date('Y'));
        $cnpjId = $_POST['cnpj_id'] ?? 'todos';

        // Montar mapa de conta_bancaria_id => mei_cnpj_id (apenas contas do usuário)
        $contaCnpjMap = [];
        $stmtMap = $pdo->prepare("
            SELECT mcc.conta_bancaria_id, mcc.mei_cnpj_id FROM mei_cnpj_contas mcc
            JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
            WHERE cb.user_id = ?
        ");
        $stmtMap->execute([$userId]);
        foreach ($stmtMap->fetchAll() as $row) {
            $contaCnpjMap[intval($row['conta_bancaria_id'])] = intval($row['mei_cnpj_id']);
        }

        // Buscar contas vinculadas ao CNPJ selecionado (do usuário)
        $contasFiltro = [];
        if ($cnpjId !== 'todos') {
            $stmt = $pdo->prepare("
                SELECT mcc.conta_bancaria_id FROM mei_cnpj_contas mcc
                JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
                WHERE mcc.mei_cnpj_id = ? AND cb.user_id = ?
            ");
            $stmt->execute([intval($cnpjId), $userId]);
            $contasFiltro = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $contasFiltro = array_map('intval', $contasFiltro);

            if (empty($contasFiltro)) {
                echo json_encode(['success' => false, 'message' => 'Este CNPJ não tem contas bancárias vinculadas.']);
                return;
            }
        }

        // FONTE 1: Sinais dos eventos (valor_sinal + conta_sinal), excluindo dinheiro
        $receitas = [];
        $sqlSinal = "
            SELECT
                COALESCE(e.data_sinal, e.data_evento) as data_rec,
                c.nome as cliente_nome,
                c.cpf as cpf_cnpj,
                e.valor_sinal,
                e.conta_sinal,
                e.tipo
            FROM eventos_eventpro e
            JOIN clientes_eventpro c ON e.cliente_id = c.id
            WHERE MONTH(COALESCE(e.data_sinal, e.data_evento)) = ?
            AND YEAR(COALESCE(e.data_sinal, e.data_evento)) = ?
            AND e.user_id = ?
            AND e.status != 'cancelado'
            AND e.valor_sinal > 0
            AND (e.forma_pagamento_sinal IS NULL OR LOWER(e.forma_pagamento_sinal) != 'dinheiro')
            AND e.conta_sinal IS NOT NULL
        ";
        $paramsSinal = [$mes, $ano, $userId];

        if (!empty($contasFiltro)) {
            $placeholders = implode(',', array_fill(0, count($contasFiltro), '?'));
            $sqlSinal .= " AND e.conta_sinal IN ($placeholders)";
            $paramsSinal = array_merge($paramsSinal, $contasFiltro);
        }

        $stmtSinal = $pdo->prepare($sqlSinal);
        $stmtSinal->execute($paramsSinal);
        foreach ($stmtSinal->fetchAll() as $ev) {
            $meiCnpjId = $contaCnpjMap[intval($ev['conta_sinal'])] ?? null;
            $receitas[] = [
                'data' => date('d/m/Y', strtotime($ev['data_rec'])),
                'cliente' => $ev['cliente_nome'],
                'cpf_cnpj' => $ev['cpf_cnpj'] ?? '',
                'documento' => 'Evento: ' . ($ev['tipo'] ?? 'Evento'),
                'valor' => floatval($ev['valor_sinal']),
                'mei_cnpj_id' => $meiCnpjId
            ];
        }

        // FONTE 2: Pagamentos registrados via movimentacoes_financeiras (restante/parcial), excluindo dinheiro
        // Exclui sinal_evento (já coberto pela FONTE 1) e descrições "Sinal - %" (registros antigos)
        $sqlMov = "
            SELECT
                mf.data_movimentacao,
                mf.descricao,
                mf.valor,
                mf.conta_id,
                mf.descricao as cliente_nome,
                '' as cpf_cnpj
            FROM movimentacoes_financeiras mf
            WHERE MONTH(mf.data_movimentacao) = ?
            AND YEAR(mf.data_movimentacao) = ?
            AND mf.user_id = ?
            AND mf.tipo = 'receita'
            AND mf.tipo_movimentacao = 'pagamento_evento'
            AND mf.tipo_movimentacao != 'sinal_evento'
            AND mf.descricao NOT LIKE 'Sinal - %'
            AND (mf.forma_pagamento IS NULL OR LOWER(mf.forma_pagamento) != 'dinheiro')
            AND mf.conta_id IS NOT NULL
        ";
        $paramsMov = [$mes, $ano, $userId];

        if (!empty($contasFiltro)) {
            $placeholders = implode(',', array_fill(0, count($contasFiltro), '?'));
            $sqlMov .= " AND mf.conta_id IN ($placeholders)";
            $paramsMov = array_merge($paramsMov, $contasFiltro);
        }

        $stmtMov = $pdo->prepare($sqlMov);
        $stmtMov->execute($paramsMov);
        foreach ($stmtMov->fetchAll() as $mov) {
            $meiCnpjId = $contaCnpjMap[intval($mov['conta_id'])] ?? null;
            $receitas[] = [
                'data' => date('d/m/Y', strtotime($mov['data_movimentacao'])),
                'cliente' => $mov['cliente_nome'],
                'cpf_cnpj' => $mov['cpf_cnpj'] ?? '',
                'documento' => $mov['descricao'],
                'valor' => floatval($mov['valor']),
                'mei_cnpj_id' => $meiCnpjId
            ];
        }

        if (empty($receitas)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma receita encontrada para este período' . ($cnpjId !== 'todos' ? ' neste CNPJ.' : '.')]);
            return;
        }

        echo json_encode([
            'success' => true,
            'receitas' => $receitas,
            'total' => count($receitas),
            'message' => count($receitas) . ' receita(s) encontrada(s)!'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ===== DEBUG TEMPORÁRIO =====
function debugContas($pdo, $userId) {
    try {
        $mes = intval($_GET['mes'] ?? date('n'));
        $ano = intval($_GET['ano'] ?? date('Y'));

        // 1. CNPJs do usuário (via contas vinculadas)
        $stmt = $pdo->prepare("
            SELECT DISTINCT mc.id, mc.cnpj, mc.nome, mc.ativo
            FROM mei_cnpjs mc
            JOIN mei_cnpj_contas mcc ON mc.id = mcc.mei_cnpj_id
            JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
            WHERE cb.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cnpjs = $stmt->fetchAll();

        // 2. Contas vinculadas ao MEI
        $stmt = $pdo->prepare("
            SELECT mcc.mei_cnpj_id, mcc.conta_bancaria_id, cb.nome as conta_nome
            FROM mei_cnpj_contas mcc
            JOIN contas_bancarias cb ON mcc.conta_bancaria_id = cb.id
            WHERE cb.user_id = ?
        ");
        $stmt->execute([$userId]);
        $contasVinculadas = $stmt->fetchAll();

        // 3. Contas usadas nos eventos do período (conta_sinal)
        $stmt = $pdo->prepare("
            SELECT DISTINCT e.conta_sinal, cb.nome as conta_nome,
                   COUNT(*) as qtd_eventos, SUM(e.valor_sinal) as total_sinal
            FROM eventos_eventpro e
            LEFT JOIN contas_bancarias cb ON e.conta_sinal = cb.id
            WHERE MONTH(COALESCE(e.data_sinal, e.data_evento)) = ?
            AND YEAR(COALESCE(e.data_sinal, e.data_evento)) = ?
            AND e.user_id = ?
            AND e.status != 'cancelado'
            AND e.valor_sinal > 0
            GROUP BY e.conta_sinal, cb.nome
        ");
        $stmt->execute([$mes, $ano, $userId]);
        $contasEventos = $stmt->fetchAll();

        // 4. Contas usadas em movimentacoes_financeiras do período
        $stmt = $pdo->prepare("
            SELECT DISTINCT mf.conta_id, cb.nome as conta_nome,
                   COUNT(*) as qtd_movs, SUM(mf.valor) as total_movs
            FROM movimentacoes_financeiras mf
            LEFT JOIN contas_bancarias cb ON mf.conta_id = cb.id
            WHERE MONTH(mf.data_movimentacao) = ?
            AND YEAR(mf.data_movimentacao) = ?
            AND mf.user_id = ?
            AND mf.tipo = 'receita'
            AND mf.tipo_movimentacao = 'pagamento_evento'
            GROUP BY mf.conta_id, cb.nome
        ");
        $stmt->execute([$mes, $ano, $userId]);
        $contasMovs = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'periodo' => "$mes/$ano",
            'cnpjs_cadastrados' => $cnpjs,
            'contas_vinculadas_ao_mei' => $contasVinculadas,
            'contas_usadas_em_eventos' => $contasEventos,
            'contas_usadas_em_movimentacoes' => $contasMovs,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
