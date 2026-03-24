<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
$userId = requireAuthPage();
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot WhatsApp - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <style>
        /* Status indicator */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-indicator.online {
            background: rgba(6, 214, 160, 0.15);
            color: var(--success);
        }
        .status-indicator.offline {
            background: rgba(239, 71, 111, 0.15);
            color: var(--danger);
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-dot.online {
            background: var(--success);
            box-shadow: 0 0 8px var(--success);
            animation: pulse 2s infinite;
        }
        .status-dot.offline {
            background: var(--danger);
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Stats grid */
        .bot-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }
        .bot-stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .bot-stat-card .stat-value {
            font-size: 28px;
            font-weight: 800;
            font-family: 'Syne', sans-serif;
            color: var(--primary);
        }
        .bot-stat-card .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Conversation list */
        .conversa-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
            gap: 12px;
        }
        .conversa-item:hover {
            background: var(--bg-card-hover);
        }
        .conversa-info {
            flex: 1;
            min-width: 0;
        }
        .conversa-nome {
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .conversa-telefone {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 2px;
        }
        .conversa-meta {
            text-align: right;
            flex-shrink: 0;
        }
        .conversa-data {
            font-size: 11px;
            color: var(--text-secondary);
        }
        .conversa-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 4px;
        }
        .conversa-badge.ativo { background: rgba(6,214,160,0.15); color: var(--success); }
        .conversa-badge.atendente { background: rgba(255,210,63,0.15); color: var(--accent); }
        .conversa-badge.inativo { background: rgba(160,174,192,0.15); color: var(--text-secondary); }
        .conversa-badge.convertido { background: rgba(255,107,53,0.15); color: var(--primary); }

        .conversa-acoes {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }
        .conversa-acoes .btn-action {
            padding: 6px 10px;
            font-size: 11px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .conversa-acoes .btn-action:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        /* Bot info header */
        .bot-info-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 20px;
        }
        .bot-info-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .bot-info-number {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Menu option editor */
        .opcao-editor {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--bg-card-hover);
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .opcao-editor .form-input {
            flex: 1;
        }
        .opcao-editor .btn-remove {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 18px;
            padding: 4px 8px;
        }

        /* Search bar */
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .search-bar .form-input {
            flex: 1;
            min-width: 200px;
        }
        .search-bar .form-select {
            min-width: 150px;
        }

        /* Historico de mensagens */
        .msg-historico {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
        }
        .msg-item {
            padding: 8px 12px;
            margin-bottom: 6px;
            border-radius: 8px;
            font-size: 13px;
            max-width: 80%;
        }
        .msg-item.recebida {
            background: var(--bg-card-hover);
            color: var(--text-primary);
            margin-right: auto;
        }
        .msg-item.enviada {
            background: rgba(255,107,53,0.15);
            color: var(--text-primary);
            margin-left: auto;
        }
        .msg-item .msg-data {
            font-size: 10px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h3 {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            color: var(--text-primary);
        }
        .modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .bot-stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
            .bot-info-header { flex-direction: column; align-items: flex-start; }
            .conversa-item { flex-direction: column; align-items: flex-start; }
            .conversa-meta { text-align: left; }
            .conversa-acoes { width: 100%; flex-wrap: wrap; }
            .search-bar { flex-direction: column; }
            .search-bar .form-input, .search-bar .form-select { min-width: unset; width: 100%; }
            .opcao-editor { flex-direction: column; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
            .bot-stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
        }
    </style>
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <!-- Cabecalho -->
    <div class="page-header">
        <h1 class="page-title">Bot WhatsApp</h1>
        <p class="page-subtitle">Gerencie seu bot de autoatendimento WhatsApp</p>
    </div>

    <!-- Sub-navegacao -->
    <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
        <button class="btn btn-primary" id="btnBotDashboard" onclick="mostrarSecao('dashboard')">
            Dashboard
        </button>
        <button class="btn btn-secondary" id="btnBotConversas" onclick="mostrarSecao('conversas')">
            Conversas
        </button>
        <button class="btn btn-secondary" id="btnBotMensagens" onclick="mostrarSecao('mensagens')">
            Mensagens
        </button>
        <button class="btn btn-secondary" id="btnBotConfig" onclick="mostrarSecao('config')">
            Configuracoes
        </button>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- SECAO: DASHBOARD DO BOT -->
    <!-- ═══════════════════════════════════════ -->
    <div id="secaoDashboard">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Status do Bot</h2>
            </div>
            <div style="padding: 24px;">
                <div class="bot-info-header">
                    <div class="bot-info-left">
                        <span class="status-indicator offline" id="botStatusBadge">
                            <span class="status-dot offline" id="botStatusDot"></span>
                            <span id="botStatusText">Verificando...</span>
                        </span>
                        <span class="bot-info-number" id="botNumero"></span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-secondary" onclick="sincronizarConversas()" id="btnSync">Sincronizar</button>
                        <button class="btn btn-primary" onclick="reiniciarBot()" id="btnRestart">Reiniciar Bot</button>
                    </div>
                </div>

                <div class="bot-stats-grid" id="botStatsGrid">
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statUptime">--</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statTotalContatos">0</div>
                        <div class="stat-label">Total Contatos</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statHoje">0</div>
                        <div class="stat-label">Atendimentos Hoje</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statAtendente">0</div>
                        <div class="stat-label">Aguardando Atendente</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statFollowUps">0</div>
                        <div class="stat-label">Follow-ups Ativos</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statConversao">0%</div>
                        <div class="stat-label">Taxa Conversao</div>
                    </div>
                </div>

                <!-- Stats por opcao -->
                <h3 style="margin: 24px 0 12px; font-family: 'Syne', sans-serif; font-size: 16px; color: var(--text-primary);">
                    Opcoes mais acessadas
                </h3>
                <div class="bot-stats-grid" id="statsOpcoes">
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statValores">0</div>
                        <div class="stat-label">Valores</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statRegioes">0</div>
                        <div class="stat-label">Regioes</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statReservar">0</div>
                        <div class="stat-label">Info Sinal</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statInstagram">0</div>
                        <div class="stat-label">Instagram</div>
                    </div>
                    <div class="bot-stat-card">
                        <div class="stat-value" id="statAtendenteOpcao">0</div>
                        <div class="stat-label">Atendente</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- SECAO: CONVERSAS / LEADS -->
    <!-- ═══════════════════════════════════════ -->
    <div id="secaoConversas" style="display:none;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Conversas / Leads</h2>
            </div>
            <div style="padding: 24px;">
                <div class="search-bar">
                    <input type="text" class="form-input" id="buscaConversa" placeholder="Buscar por nome ou telefone..." oninput="filtrarConversas()">
                    <select class="form-input form-select" id="filtroStatus" onchange="filtrarConversas()">
                        <option value="">Todos os status</option>
                        <option value="ativo">Ativo</option>
                        <option value="atendente">Aguardando atendente</option>
                        <option value="inativo">Inativo</option>
                        <option value="convertido">Convertido</option>
                    </select>
                    <button class="btn btn-secondary" onclick="sincronizarConversas()">Sincronizar</button>
                </div>

                <div id="listaConversas">
                    <p style="text-align: center; color: var(--text-secondary); padding: 40px;">
                        Carregando conversas...
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- SECAO: MENSAGENS DO BOT -->
    <!-- ═══════════════════════════════════════ -->
    <div id="secaoMensagens" style="display:none;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mensagens do Bot</h2>
            </div>
            <div style="padding: 24px;">
                <form onsubmit="salvarMensagens(event)">
                    <!-- Saudacao -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Saudacao (primeiro contato)</label>
                        <textarea class="form-input" id="msgSaudacao" rows="3"
                            placeholder="Use {assinatura}, {saudacao} e {nome}"></textarea>
                        <small style="color: var(--text-secondary);">Variaveis: {assinatura} = assinatura, {saudacao} = Bom dia/Boa tarde/Boa noite, {nome} = nome do contato</small>
                    </div>

                    <!-- Menu Intro -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Introducao do Menu</label>
                        <textarea class="form-input" id="msgMenuIntro" rows="2"
                            placeholder="Mensagem antes do menu"></textarea>
                    </div>

                    <!-- Texto do Menu -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Texto do Menu</label>
                        <textarea class="form-input" id="msgMenuTexto" rows="2"
                            placeholder="Texto que aparece com os botoes"></textarea>
                    </div>

                    <!-- Opcoes do Menu -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Opcoes do Menu</label>
                        <div id="opcoesMenuEditor"></div>
                        <button type="button" class="btn btn-secondary" onclick="adicionarOpcao()" style="margin-top: 8px;">
                            + Adicionar opcao
                        </button>
                    </div>

                    <hr style="border-color: var(--border); margin: 24px 0;">

                    <!-- Respostas por opcao -->
                    <h3 style="font-family: 'Syne', sans-serif; font-size: 16px; color: var(--text-primary); margin-bottom: 16px;">
                        Respostas por opcao
                    </h3>
                    <div id="respostasEditor"></div>

                    <hr style="border-color: var(--border); margin: 24px 0;">

                    <!-- Outras mensagens -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Mensagem de retorno (cliente antigo)</label>
                        <textarea class="form-input" id="msgClienteRetorno" rows="3"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Mensagem de follow-up (apos inatividade)</label>
                        <textarea class="form-input" id="msgFollowUp" rows="2"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn btn-secondary" onclick="carregarConfigBot()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Mensagens</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- SECAO: CONFIGURACOES DO BOT -->
    <!-- ═══════════════════════════════════════ -->
    <div id="secaoConfig" style="display:none;">
        <!-- Conexao -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h2 class="card-title">Conexao com o Bot</h2>
            </div>
            <div style="padding: 24px;">
                <form onsubmit="salvarConexao(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">URL da API do Bot *</label>
                            <input type="url" class="form-input" id="cfgBotUrl" required
                                placeholder="http://IP-DO-SERVIDOR:3001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">API Key *</label>
                            <input type="text" class="form-input" id="cfgApiKey" required
                                placeholder="Chave de autenticacao">
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 12px;">
                        <button type="button" class="btn btn-secondary" onclick="testarConexao()">Testar Conexao</button>
                        <button type="submit" class="btn btn-primary">Salvar Conexao</button>
                    </div>
                    <div id="resultadoTesteConexao" style="margin-top: 12px;"></div>
                </form>
            </div>
        </div>

        <!-- Configuracoes gerais -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Configuracoes Gerais</h2>
            </div>
            <div style="padding: 24px;">
                <form onsubmit="salvarConfigGeral(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nome da Empresa</label>
                            <input type="text" class="form-input" id="cfgNomeEmpresa" placeholder="Cristian DJ Sonorizacao">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assinatura</label>
                            <input type="text" class="form-input" id="cfgAssinatura" placeholder="*Cristian DJ Sonorizacao* 🎧">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Link do Catalogo</label>
                            <input type="url" class="form-input" id="cfgLinkCatalogo" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Link do Instagram</label>
                            <input type="url" class="form-input" id="cfgLinkInstagram" placeholder="https://instagram.com/...">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tempo do Lembrete (minutos)</label>
                            <input type="number" class="form-input" id="cfgTempoLembrete" min="1" value="60">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tempo do Follow-up (minutos)</label>
                            <input type="number" class="form-input" id="cfgTempoFollowUp" min="1" value="300">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Silencio - Hora Inicio</label>
                            <input type="number" class="form-input" id="cfgSilencioInicio" min="0" max="23" value="0">
                            <small style="color: var(--text-secondary);">Bot nao envia msgs entre estas horas</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Silencio - Hora Fim</label>
                            <input type="number" class="form-input" id="cfgSilencioFim" min="0" max="23" value="9">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 16px;">
                        <label class="form-label" style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="checkbox" id="cfgAtivo" checked style="width: 18px; height: 18px;">
                            Bot Ativo (responde mensagens automaticamente)
                        </label>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Salvar Configuracoes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════ -->
<!-- MODAL: Enviar Mensagem -->
<!-- ═══════════════════════════════════════ -->
<div class="modal-overlay" id="modalEnviarMsg">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Enviar Mensagem</h3>
            <button class="modal-close" onclick="fecharModal('modalEnviarMsg')">&times;</button>
        </div>
        <form onsubmit="enviarMensagemManual(event)">
            <div class="form-group">
                <label class="form-label">Telefone</label>
                <input type="text" class="form-input" id="modalTelefone" readonly>
            </div>
            <div class="form-group">
                <label class="form-label">Mensagem</label>
                <textarea class="form-input" id="modalMensagem" rows="4" required placeholder="Digite a mensagem..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModal('modalEnviarMsg')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════ -->
<!-- MODAL: Detalhe da Conversa -->
<!-- ═══════════════════════════════════════ -->
<div class="modal-overlay" id="modalDetalhe">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalDetalheNome">Detalhe</h3>
            <button class="modal-close" onclick="fecharModal('modalDetalhe')">&times;</button>
        </div>
        <div id="modalDetalheConteudo">
            <p style="color: var(--text-secondary);">Carregando...</p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="../assets/js/theme-toggle.js"></script>
<script src="../assets/js/bot-whatsapp.js"></script>
<script>
    // Inicializa ao carregar
    document.addEventListener('DOMContentLoaded', () => {
        carregarStatus();
        carregarStats();
        carregarConversas();
        carregarConfigBot();

        // Polling de status a cada 30s
        setInterval(carregarStatus, 30000);
    });
</script>
</body>
</html>
