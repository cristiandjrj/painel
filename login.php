<?php
session_start();

// Se já está logado, redireciona
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

$erro = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    try {
        require_once 'config/database.php';

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Verificar se está bloqueado
            if ($usuario['status'] === 'bloqueado') {
                $erro = 'Sua conta está bloqueada. Entre em contato com o administrador.';
            }
            // Verificar se plano teste expirou
            elseif ($usuario['plano'] === 'teste' && $usuario['data_expiracao'] && date('Y-m-d') > $usuario['data_expiracao']) {
                $pdo->prepare("UPDATE usuarios SET status = 'expirado' WHERE id = ?")->execute([$usuario['id']]);
                $erro = 'Seu período de teste de 7 dias expirou. Entre em contato com o administrador para ativar seu plano.';
            }
            elseif ($usuario['status'] === 'expirado') {
                $erro = 'Seu plano expirou. Entre em contato com o administrador.';
            }
            else {
                // Login OK
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_nome'] = $usuario['nome'];
                $_SESSION['user_email'] = $usuario['email'];
                $_SESSION['user_role'] = $usuario['role'];
                $_SESSION['user_plano'] = $usuario['plano'];

                // Atualizar último acesso
                $pdo->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?")->execute([$usuario['id']]);

                header('Location: pages/dashboard.php');
                exit;
            }
        } else {
            $erro = 'Email ou senha incorretos';
        }
    } catch (Exception $e) {
        $erro = 'Erro de conexão com o banco de dados. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Syne', sans-serif;
            background: linear-gradient(135deg, #0A0E27 0%, #1a1f3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(20, 27, 61, 0.95);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 107, 53, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .logo h1 {
            font-size: 28px;
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        
        .logo p {
            color: #A0AEC0;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #A0AEC0;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(10, 14, 39, 0.8);
            border: 1px solid rgba(255, 107, 53, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.15);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        }
        
        .error {
            background: rgba(239, 71, 111, 0.15);
            border: 1px solid rgba(239, 71, 111, 0.5);
            color: #FF6B6B;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .info-box {
            background: rgba(6, 214, 160, 0.1);
            border: 1px solid rgba(6, 214, 160, 0.3);
            color: #06D6A0;
            padding: 16px;
            border-radius: 10px;
            margin-top: 24px;
            font-size: 13px;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .info-box div {
            margin: 4px 0;
            padding: 4px 0;
        }
        
        .divider {
            text-align: center;
            margin: 24px 0;
            color: #A0AEC0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">🎵</div>
            <h1>EventProDJ</h1>
            <p>Sistema de Gestão de Eventos</p>
        </div>
        
        <?php if ($erro): ?>
            <div class="error">
                ❌ <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label>📧 Email</label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    placeholder="Digite seu email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label>🔒 Senha</label>
                <input 
                    type="password" 
                    name="senha" 
                    required 
                    placeholder="Digite sua senha"
                >
            </div>
            
            <button type="submit" class="btn-login">
                Entrar no Sistema
            </button>
        </form>
        
        <div class="divider">━━━━━ ou ━━━━━</div>

        <div class="info-box" style="cursor: pointer; text-align: center;" onclick="document.getElementById('testeSection').style.display = document.getElementById('testeSection').style.display === 'none' ? 'block' : 'none'">
            <strong>🚀 Teste grátis por 7 dias!</strong>
            <div>Clique aqui para criar sua conta de teste</div>
        </div>

        <div id="testeSection" style="display: none; margin-top: 16px;">
            <form id="formTeste" onsubmit="criarContaTeste(event)">
                <div class="form-group">
                    <label>👤 Seu Nome</label>
                    <input type="text" id="testeNome" required placeholder="Nome completo">
                </div>
                <div class="form-group">
                    <label>📧 Seu Email</label>
                    <input type="email" id="testeEmail" required placeholder="seu@email.com">
                </div>
                <div class="form-group">
                    <label>🔒 Criar Senha</label>
                    <input type="password" id="testeSenha" required placeholder="Mínimo 6 caracteres" minlength="6">
                </div>
                <div class="form-group">
                    <label>📱 WhatsApp (opcional)</label>
                    <input type="text" id="testeTelefone" placeholder="(21) 99999-9999">
                </div>
                <button type="submit" class="btn-login" id="btnTeste" style="background: linear-gradient(135deg, #06D6A0 0%, #04B890 100%);">
                    Criar Conta de Teste (7 dias grátis)
                </button>
                <div id="testeMsg" style="margin-top: 12px; font-size: 13px; text-align: center;"></div>
            </form>
        </div>

        <script>
        async function criarContaTeste(e) {
            e.preventDefault();
            const btn = document.getElementById('btnTeste');
            const msg = document.getElementById('testeMsg');
            btn.disabled = true;
            btn.textContent = 'Criando...';

            const formData = new FormData();
            formData.append('action', 'create_trial');
            formData.append('nome', document.getElementById('testeNome').value);
            formData.append('email', document.getElementById('testeEmail').value);
            formData.append('senha', document.getElementById('testeSenha').value);
            formData.append('telefone', document.getElementById('testeTelefone').value);

            try {
                const res = await fetch('api/usuarios-public.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    msg.style.color = '#06D6A0';
                    msg.innerHTML = '✅ Conta criada! Faça login com seu email e senha.';
                    document.getElementById('formTeste').reset();
                    document.getElementById('testeSection').style.display = 'none';
                } else {
                    msg.style.color = '#EF476F';
                    msg.innerHTML = '❌ ' + data.message;
                }
            } catch (err) {
                msg.style.color = '#EF476F';
                msg.innerHTML = '❌ Erro de conexão';
            }

            btn.disabled = false;
            btn.textContent = 'Criar Conta de Teste (7 dias grátis)';
        }
        </script>
    </div>
</body>
</html>
