<?php
/**
 * API Bot WhatsApp - EventProDJ
 * Gerencia comunicacao entre o painel e o bot WhatsApp via API REST
 */
error_reporting(0);
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'status':
        obterStatus($pdo);
        break;
    case 'get_config':
        obterConfig($pdo);
        break;
    case 'save_config':
        salvarConfig($pdo);
        break;
    case 'save_conexao':
        salvarConexao($pdo);
        break;
    case 'testar_conexao':
        testarConexao($pdo);
        break;
    case 'conversas':
        listarConversas($pdo);
        break;
    case 'conversa_detalhe':
        detalheConversa($pdo);
        break;
    case 'enviar_mensagem':
        enviarMensagem($pdo);
        break;
    case 'converter_cliente':
        converterCliente($pdo);
        break;
    case 'stats':
        obterStats($pdo);
        break;
    case 'restart':
        reiniciarBot($pdo);
        break;
    case 'reset_usuario':
        resetarUsuario($pdo);
        break;
    case 'sync_conversas':
        sincronizarConversas($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
}

// ═══════════════════════════════════════════
// HELPER: Comunicacao com API do Bot
// ═══════════════════════════════════════════

function getBotConfig($pdo) {
    global $userId;
    $stmt = $pdo->prepare("SELECT bot_url, api_key FROM bot_config WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $config = $stmt->fetch();

    if (!$config) {
        // Tenta registro sem user_id
        $stmt = $pdo->prepare("SELECT bot_url, api_key FROM bot_config WHERE (user_id IS NULL OR user_id = 0) LIMIT 1");
        $stmt->execute();
        $config = $stmt->fetch();
    }

    return $config ?: ['bot_url' => 'http://localhost:3001', 'api_key' => 'eventprodj-bot-2026'];
}

function botApiRequest($method, $endpoint, $data = null, $pdo = null) {
    global $userId;

    if ($pdo === null) {
        return ['success' => false, 'message' => 'PDO não disponível'];
    }

    $config = getBotConfig($pdo);
    $url = rtrim($config['bot_url'], '/') . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Api-Key: ' . ($config['api_key'] ?? 'eventprodj-bot-2026'),
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'message' => 'Erro de conexão com o bot: ' . $error, 'offline' => true];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        return ['success' => false, 'message' => 'Resposta inválida do bot (HTTP ' . $httpCode . ')'];
    }

    return $decoded;
}

// ═══════════════════════════════════════════
// ACOES
// ═══════════════════════════════════════════

function obterStatus($pdo) {
    $result = botApiRequest('GET', '/api/status', null, $pdo);

    // Log de status
    registrarLog($pdo, 'conexao', $result['success'] ? 'Bot online' : 'Bot offline');

    echo json_encode($result);
}

function obterConfig($pdo) {
    global $userId;
    try {
        // Busca config do banco
        $stmt = $pdo->prepare("SELECT * FROM bot_config WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $config = $stmt->fetch();

        if (!$config) {
            $stmt = $pdo->prepare("SELECT * FROM bot_config WHERE (user_id IS NULL OR user_id = 0) LIMIT 1");
            $stmt->execute();
            $config = $stmt->fetch();
        }

        // Busca config do bot remoto
        $botConfig = botApiRequest('GET', '/api/config', null, $pdo);

        echo json_encode([
            'success' => true,
            'config' => $config,
            'botConfig' => $botConfig['config'] ?? null,
            'botOnline' => $botConfig['success'] ?? false,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function salvarConexao($pdo) {
    global $userId;
    try {
        $botUrl = $_POST['bot_url'] ?? 'http://localhost:3001';
        $apiKey = $_POST['api_key'] ?? '';

        $stmt = $pdo->prepare("SELECT id FROM bot_config WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $existente = $stmt->fetch();

        if ($existente) {
            $stmt = $pdo->prepare("UPDATE bot_config SET bot_url = ?, api_key = ? WHERE id = ?");
            $stmt->execute([$botUrl, $apiKey, $existente['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO bot_config (user_id, bot_url, api_key) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $botUrl, $apiKey]);
        }

        echo json_encode(['success' => true, 'message' => 'Dados de conexão salvos!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function testarConexao($pdo) {
    $result = botApiRequest('GET', '/api/status', null, $pdo);
    echo json_encode($result);
}

function salvarConfig($pdo) {
    global $userId;
    try {
        $dados = [
            'nome_empresa' => $_POST['nome_empresa'] ?? '',
            'assinatura' => $_POST['assinatura'] ?? '',
            'link_catalogo' => $_POST['link_catalogo'] ?? '',
            'link_instagram' => $_POST['link_instagram'] ?? '',
            'saudacao_template' => $_POST['saudacao_template'] ?? '',
            'menu_intro' => $_POST['menu_intro'] ?? '',
            'menu_texto' => $_POST['menu_texto'] ?? '',
            'menu_botao_texto' => $_POST['menu_botao_texto'] ?? '',
            'opcoes_menu' => $_POST['opcoes_menu'] ?? '[]',
            'respostas' => $_POST['respostas'] ?? '{}',
            'tempo_lembrete_min' => intval($_POST['tempo_lembrete_min'] ?? 60),
            'tempo_followup_min' => intval($_POST['tempo_followup_min'] ?? 300),
            'hora_silencio_inicio' => intval($_POST['hora_silencio_inicio'] ?? 0),
            'hora_silencio_fim' => intval($_POST['hora_silencio_fim'] ?? 9),
            'ativo' => intval($_POST['ativo'] ?? 1),
        ];

        $stmt = $pdo->prepare("SELECT id FROM bot_config WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $existente = $stmt->fetch();

        if ($existente) {
            $sql = "UPDATE bot_config SET
                nome_empresa = ?, assinatura = ?, link_catalogo = ?, link_instagram = ?,
                saudacao_template = ?, menu_intro = ?, menu_texto = ?, menu_botao_texto = ?,
                opcoes_menu = ?, respostas = ?,
                tempo_lembrete_min = ?, tempo_followup_min = ?,
                hora_silencio_inicio = ?, hora_silencio_fim = ?, ativo = ?
                WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $dados['nome_empresa'], $dados['assinatura'], $dados['link_catalogo'], $dados['link_instagram'],
                $dados['saudacao_template'], $dados['menu_intro'], $dados['menu_texto'], $dados['menu_botao_texto'],
                $dados['opcoes_menu'], $dados['respostas'],
                $dados['tempo_lembrete_min'], $dados['tempo_followup_min'],
                $dados['hora_silencio_inicio'], $dados['hora_silencio_fim'], $dados['ativo'],
                $existente['id'],
            ]);
        } else {
            $sql = "INSERT INTO bot_config (user_id, nome_empresa, assinatura, link_catalogo, link_instagram,
                saudacao_template, menu_intro, menu_texto, menu_botao_texto,
                opcoes_menu, respostas,
                tempo_lembrete_min, tempo_followup_min,
                hora_silencio_inicio, hora_silencio_fim, ativo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $userId, $dados['nome_empresa'], $dados['assinatura'], $dados['link_catalogo'], $dados['link_instagram'],
                $dados['saudacao_template'], $dados['menu_intro'], $dados['menu_texto'], $dados['menu_botao_texto'],
                $dados['opcoes_menu'], $dados['respostas'],
                $dados['tempo_lembrete_min'], $dados['tempo_followup_min'],
                $dados['hora_silencio_inicio'], $dados['hora_silencio_fim'], $dados['ativo'],
            ]);
        }

        // Envia para o bot remoto
        $configBot = [
            'nomeEmpresa' => $dados['nome_empresa'],
            'assinatura' => $dados['assinatura'],
            'linkCatalogo' => $dados['link_catalogo'],
            'linkInstagram' => $dados['link_instagram'],
            'saudacao' => $dados['saudacao_template'],
            'menuIntro' => $dados['menu_intro'],
            'menu' => [
                'texto' => $dados['menu_texto'],
                'botaoTexto' => $dados['menu_botao_texto'],
                'opcoes' => json_decode($dados['opcoes_menu'], true),
            ],
            'respostas' => json_decode($dados['respostas'], true),
            'tempoLembreteMs' => $dados['tempo_lembrete_min'] * 60 * 1000,
            'tempoFollowUpMs' => $dados['tempo_followup_min'] * 60 * 1000,
            'horaSilencioInicio' => $dados['hora_silencio_inicio'],
            'horaSilencioFim' => $dados['hora_silencio_fim'],
        ];

        $botResult = botApiRequest('PUT', '/api/config', $configBot, $pdo);

        registrarLog($pdo, 'config', 'Configurações do bot atualizadas');

        echo json_encode([
            'success' => true,
            'message' => 'Configurações salvas!',
            'botSynced' => $botResult['success'] ?? false,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function listarConversas($pdo) {
    global $userId;
    try {
        $stmt = $pdo->prepare("
            SELECT bc.*, ce.nome as cliente_nome, ce.id as cliente_vinculado_id
            FROM bot_conversas bc
            LEFT JOIN clientes_eventpro ce ON bc.cliente_id = ce.id
            WHERE bc.user_id = ?
            ORDER BY bc.ultima_interacao DESC
        ");
        $stmt->execute([$userId]);
        $conversas = $stmt->fetchAll();

        echo json_encode(['success' => true, 'conversas' => $conversas, 'total' => count($conversas)]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function detalheConversa($pdo) {
    $jid = $_GET['jid'] ?? '';
    if (!$jid) {
        echo json_encode(['success' => false, 'message' => 'JID não informado']);
        return;
    }

    $result = botApiRequest('GET', '/api/conversations/' . urlencode($jid), null, $pdo);
    echo json_encode($result);
}

function enviarMensagem($pdo) {
    $telefone = $_POST['telefone'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';

    if (!$telefone || !$mensagem) {
        echo json_encode(['success' => false, 'message' => 'Telefone e mensagem são obrigatórios']);
        return;
    }

    $result = botApiRequest('POST', '/api/send', [
        'telefone' => $telefone,
        'mensagem' => $mensagem,
    ], $pdo);

    if ($result['success'] ?? false) {
        registrarLog($pdo, 'envio', "Mensagem enviada para $telefone");
    }

    echo json_encode($result);
}

function converterCliente($pdo) {
    global $userId;
    try {
        $jid = $_POST['jid'] ?? '';
        $nome = $_POST['nome'] ?? 'Cliente';
        $telefone = $_POST['telefone'] ?? '';

        if (!$jid || !$telefone) {
            echo json_encode(['success' => false, 'message' => 'JID e telefone são obrigatórios']);
            return;
        }

        // Verifica se ja existe cliente com esse telefone
        $stmt = $pdo->prepare("SELECT id FROM clientes_eventpro WHERE telefone = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$telefone, $userId]);
        $existente = $stmt->fetch();

        if ($existente) {
            // Vincula conversa ao cliente existente
            $clienteId = $existente['id'];
        } else {
            // Cria novo cliente
            $stmt = $pdo->prepare("INSERT INTO clientes_eventpro (user_id, nome, telefone, origem) VALUES (?, ?, ?, 'whatsapp')");
            $stmt->execute([$userId, $nome, $telefone]);
            $clienteId = $pdo->lastInsertId();
        }

        // Atualiza conversa com referencia ao cliente
        $stmt = $pdo->prepare("UPDATE bot_conversas SET cliente_id = ? WHERE jid = ? AND user_id = ?");
        $stmt->execute([$clienteId, $jid, $userId]);

        registrarLog($pdo, 'mensagem', "Lead convertido em cliente: $nome ($telefone)");

        echo json_encode([
            'success' => true,
            'message' => $existente ? 'Conversa vinculada ao cliente existente!' : 'Novo cliente criado com sucesso!',
            'cliente_id' => $clienteId,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function obterStats($pdo) {
    $result = botApiRequest('GET', '/api/stats', null, $pdo);

    // Adiciona stats do banco local
    global $userId;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bot_conversas WHERE user_id = ?");
        $stmt->execute([$userId]);
        $totalLocal = $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bot_conversas WHERE user_id = ? AND cliente_id IS NOT NULL");
        $stmt->execute([$userId]);
        $convertidos = $stmt->fetch()['total'];

        if ($result['success'] ?? false) {
            $result['stats']['totalConversasLocal'] = intval($totalLocal);
            $result['stats']['leadsConvertidos'] = intval($convertidos);
            $result['stats']['taxaConversao'] = $totalLocal > 0
                ? round(($convertidos / $totalLocal) * 100, 1)
                : 0;
        }
    } catch (PDOException $e) {
        // Ignora erros de stats locais
    }

    echo json_encode($result);
}

function reiniciarBot($pdo) {
    $result = botApiRequest('POST', '/api/restart', null, $pdo);
    registrarLog($pdo, 'conexao', 'Reinício do bot solicitado');
    echo json_encode($result);
}

function resetarUsuario($pdo) {
    $jid = $_POST['jid'] ?? '';
    if (!$jid) {
        echo json_encode(['success' => false, 'message' => 'JID não informado']);
        return;
    }

    $result = botApiRequest('POST', '/api/conversations/' . urlencode($jid) . '/reset', null, $pdo);
    echo json_encode($result);
}

function sincronizarConversas($pdo) {
    global $userId;

    $result = botApiRequest('GET', '/api/conversations', null, $pdo);

    if (!($result['success'] ?? false)) {
        echo json_encode($result);
        return;
    }

    $conversas = $result['conversas'] ?? [];
    $sincronizadas = 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_conversas (user_id, jid, nome_contato, telefone, primeira_interacao, ultima_interacao,
                total_interacoes, ultima_opcao, status, ja_recebeu_menu, follow_up_enviado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                nome_contato = VALUES(nome_contato),
                ultima_interacao = VALUES(ultima_interacao),
                total_interacoes = VALUES(total_interacoes),
                ultima_opcao = VALUES(ultima_opcao),
                status = VALUES(status),
                ja_recebeu_menu = VALUES(ja_recebeu_menu),
                follow_up_enviado = VALUES(follow_up_enviado)
        ");

        foreach ($conversas as $conv) {
            $telefone = $conv['telefone'] ?? '';
            // Formata telefone brasileiro
            if (strlen($telefone) >= 12) {
                $telefone = preg_replace('/^(\d{2})(\d{2})(\d{4,5})(\d{4})$/', '+$1 ($2) $3-$4', $telefone);
            }

            $stmt->execute([
                $userId,
                $conv['jid'],
                $conv['nome'] ?? 'Cliente',
                $telefone,
                $conv['primeiroContato'] ?? null,
                $conv['ultimaInteracao'] ?? null,
                intval($conv['totalInteracoes'] ?? 1),
                $conv['ultimaOpcao'] ?? null,
                $conv['status'] ?? 'ativo',
                $conv['jaRecebeuMenu'] ? 1 : 0,
                $conv['followUpEnviado'] ?? null,
            ]);
            $sincronizadas++;
        }

        registrarLog($pdo, 'mensagem', "Sincronizadas $sincronizadas conversas do bot");

        echo json_encode([
            'success' => true,
            'message' => "$sincronizadas conversas sincronizadas!",
            'total' => $sincronizadas,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ═══════════════════════════════════════════
// HELPER: Registrar log
// ═══════════════════════════════════════════

function registrarLog($pdo, $tipo, $descricao, $dados = null) {
    global $userId;
    try {
        $stmt = $pdo->prepare("INSERT INTO bot_logs (user_id, tipo, descricao, dados_json) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $tipo, $descricao, $dados ? json_encode($dados) : null]);
    } catch (PDOException $e) {
        // Silencia erros de log
    }
}
