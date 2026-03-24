<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/auth.php';

$userId = requireAuth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get':
        obterConfiguracoes($pdo);
        break;
    case 'save_profile':
        salvarPerfil($pdo);
        break;
    case 'save_contract':
        salvarContrato($pdo);
        break;
    case 'save_logo':
        salvarLogo($pdo);
        break;
    case 'remove_logo':
        removerLogo($pdo);
        break;
    case 'save_signature':
        salvarAssinatura($pdo);
        break;
    case 'remove_signature':
        removerAssinatura($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
}

function obterConfiguracoes($pdo) {
    global $userId;
    try {
        // Tenta buscar pelo user_id
        $stmt = $pdo->prepare("SELECT * FROM configuracoes_eventpro WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $config = $stmt->fetch();

        if (!$config) {
            // Fallback: registro padrão sem user_id (instalação inicial)
            $stmt = $pdo->prepare("SELECT * FROM configuracoes_eventpro WHERE (user_id IS NULL OR user_id = 0) LIMIT 1");
            $stmt->execute();
            $config = $stmt->fetch();

            if ($config) {
                // Associar silenciosamente ao user_id atual
                try {
                    $pdo->prepare("UPDATE configuracoes_eventpro SET user_id = ? WHERE id = ?")->execute([$userId, $config['id']]);
                } catch (PDOException $e) { /* ignora se coluna não existir */ }
            }
        }

        if (!$config) {
            echo json_encode(['success' => true, 'config' => []]);
            return;
        }

        echo json_encode(['success' => true, 'config' => $config]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function salvarPerfil($pdo) {
    global $userId;
    try {
        $dados = [
            'nome_empresa' => $_POST['nome_empresa'] ?? '',
            'nome_corporativo' => $_POST['nome_corporativo'] ?? '',
            'cnpj' => $_POST['cnpj'] ?? '',
            'telefone' => $_POST['telefone'] ?? '',
            'whatsapp' => $_POST['whatsapp'] ?? '',
            'email' => $_POST['email'] ?? '',
            'cidade' => $_POST['cidade'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'endereco' => $_POST['endereco'] ?? '',
            'cep' => $_POST['cep'] ?? '',
            'instagram' => $_POST['instagram'] ?? '',
            'facebook' => $_POST['facebook'] ?? '',
            'youtube' => $_POST['youtube'] ?? '',
            'site' => $_POST['site'] ?? ''
        ];

        $existente = buscarConfigUsuario($pdo, $userId);

        if ($existente) {
            $sql = "UPDATE configuracoes_eventpro SET
                nome_empresa = :nome_empresa,
                nome_corporativo = :nome_corporativo,
                cnpj = :cnpj,
                telefone = :telefone,
                whatsapp = :whatsapp,
                email = :email,
                cidade = :cidade,
                estado = :estado,
                endereco = :endereco,
                cep = :cep,
                instagram = :instagram,
                facebook = :facebook,
                youtube = :youtube,
                site = :site
                WHERE id = :id";
            $dados['id'] = $existente['id'];
        } else {
            $sql = "INSERT INTO configuracoes_eventpro
                (user_id, nome_empresa, nome_corporativo, cnpj, telefone, whatsapp, email, cidade, estado, endereco, cep, instagram, facebook, youtube, site)
                VALUES
                (:user_id, :nome_empresa, :nome_corporativo, :cnpj, :telefone, :whatsapp, :email, :cidade, :estado, :endereco, :cep, :instagram, :facebook, :youtube, :site)";
            $dados['user_id'] = $userId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($dados);

        echo json_encode(['success' => true, 'message' => 'Configurações salvas com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function salvarContrato($pdo) {
    global $userId;
    try {
        $clausulas = $_POST['clausulas_contratuais'] ?? '';

        $existente = buscarConfigUsuario($pdo, $userId);

        if ($existente) {
            $stmt = $pdo->prepare("UPDATE configuracoes_eventpro SET clausulas_contratuais = :clausulas WHERE id = :id");
            $stmt->execute(['clausulas' => $clausulas, 'id' => $existente['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuracoes_eventpro (user_id, clausulas_contratuais) VALUES (:user_id, :clausulas)");
            $stmt->execute(['user_id' => $userId, 'clausulas' => $clausulas]);
        }

        echo json_encode(['success' => true, 'message' => 'Contrato salvo com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ─── Auxiliar: busca registro de config do usuário, com fallback para o registro padrão ───
function buscarConfigUsuario($pdo, $userId) {
    // Tenta encontrar pelo user_id
    $stmt = $pdo->prepare("SELECT id FROM configuracoes_eventpro WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $reg = $stmt->fetch();
    if ($reg) return $reg;

    // Fallback: registro padrão criado no install (sem user_id)
    $stmt = $pdo->prepare("SELECT id FROM configuracoes_eventpro WHERE (user_id IS NULL OR user_id = 0) LIMIT 1");
    $stmt->execute();
    $reg = $stmt->fetch();
    if ($reg) {
        // Associar ao user_id atual
        try {
            $pdo->prepare("UPDATE configuracoes_eventpro SET user_id = ? WHERE id = ?")->execute([$userId, $reg['id']]);
        } catch (PDOException $e) { /* ignora se coluna ainda não existir */ }
        return $reg;
    }
    return null;
}

function salvarLogo($pdo) {
    global $userId;
    try {
        $logoBase64 = $_POST['logo_base64'] ?? '';

        if (empty($logoBase64)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma imagem recebida']);
            return;
        }

        // Base64 de 2MB gera ~2.7MB de string
        if (strlen($logoBase64) > 3 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Imagem muito grande. Máximo 2MB.']);
            return;
        }

        $existente = buscarConfigUsuario($pdo, $userId);

        if ($existente) {
            $stmt = $pdo->prepare("UPDATE configuracoes_eventpro SET logo_base64 = :logo WHERE id = :id");
            $stmt->execute(['logo' => $logoBase64, 'id' => $existente['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuracoes_eventpro (user_id, logo_base64) VALUES (:user_id, :logo)");
            $stmt->execute(['user_id' => $userId, 'logo' => $logoBase64]);
        }

        echo json_encode(['success' => true, 'message' => 'Logo salva com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar logo: ' . $e->getMessage()]);
    }
}

function removerLogo($pdo) {
    global $userId;
    try {
        $existente = buscarConfigUsuario($pdo, $userId);
        if ($existente) {
            $stmt = $pdo->prepare("UPDATE configuracoes_eventpro SET logo_base64 = NULL WHERE id = :id");
            $stmt->execute(['id' => $existente['id']]);
        }
        echo json_encode(['success' => true, 'message' => 'Logo removida!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function salvarAssinatura($pdo) {
    global $userId;
    try {
        $assinaturaBase64 = $_POST['assinatura_base64'] ?? '';

        if (empty($assinaturaBase64)) {
            echo json_encode(['success' => false, 'message' => 'Nenhuma imagem recebida']);
            return;
        }

        if (strlen($assinaturaBase64) > 3 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Imagem muito grande. Máximo 2MB.']);
            return;
        }

        $existente = buscarConfigUsuario($pdo, $userId);

        if ($existente) {
            $stmt = $pdo->prepare("UPDATE configuracoes_eventpro SET assinatura_base64 = :assinatura WHERE id = :id");
            $stmt->execute(['assinatura' => $assinaturaBase64, 'id' => $existente['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuracoes_eventpro (user_id, assinatura_base64) VALUES (:user_id, :assinatura)");
            $stmt->execute(['user_id' => $userId, 'assinatura' => $assinaturaBase64]);
        }

        echo json_encode(['success' => true, 'message' => 'Assinatura salva com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar assinatura: ' . $e->getMessage()]);
    }
}

function removerAssinatura($pdo) {
    global $userId;
    try {
        $existente = buscarConfigUsuario($pdo, $userId);
        if ($existente) {
            $stmt = $pdo->prepare("UPDATE configuracoes_eventpro SET assinatura_base64 = NULL WHERE id = :id");
            $stmt->execute(['id' => $existente['id']]);
        }
        echo json_encode(['success' => true, 'message' => 'Assinatura removida!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
