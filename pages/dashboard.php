<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
$userId = requireAuthPage();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard Financeiro</h1>
        <p class="page-subtitle">Gestão completa para sua empresa de eventos</p>
    </div>

    <!-- Stats Cards - Layout inline com gradientes (padrão Gestão Financeira) -->
    <style>
        .dash-stats .stat-card::before { display: none; }
        .dash-stats .stat-card { display: flex; align-items: center; gap: 12px; overflow: visible; padding: 16px 14px; }
        .dash-stats .stat-card .stat-icon { font-size: 24px; width: 48px; height: 48px; min-width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0; margin-bottom: 0; }
        .dash-stats .stat-card .stat-info { flex: 1; min-width: 0; }
        .dash-stats .stat-card .stat-info .stat-label { font-size: 10px; color: #A0AEC0; margin-bottom: 2px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }
        .dash-stats .stat-card .stat-info .stat-value { font-size: 18px; font-weight: 800; margin-bottom: 0; white-space: nowrap; }
        /* Grid responsivo dos cards */
        .dash-stats .stats-grid { grid-template-columns: repeat(5, 1fr) !important; }
        @media (max-width: 1024px) { .dash-stats .stats-grid { grid-template-columns: repeat(3, 1fr) !important; } }
        @media (max-width: 768px)  { .dash-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; } }
        @media (max-width: 430px)  { .dash-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .dash-stats .stat-card { padding: 12px 10px !important; gap: 8px !important; }
            .dash-stats .stat-card .stat-icon { width: 36px !important; height: 36px !important; min-width: 36px !important; font-size: 18px !important; }
            .dash-stats .stat-card .stat-info .stat-value { font-size: 15px !important; }
            .dash-stats .stat-card .stat-info .stat-label { font-size: 9px !important; }
        }
    </style>
    <div class="dash-stats">
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(6, 214, 160, 0.05)); cursor: pointer;" onclick="mostrarReceitasDoMes()">
            <div class="stat-icon" style="background: rgba(6, 214, 160, 0.15); color: #06D6A0;">💰</div>
            <div class="stat-info">
                <div class="stat-label">Receitas do Mês</div>
                <div class="stat-value" id="totalReceitas" style="color: #06D6A0;">R$ 0,00</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(79, 70, 229, 0.05)); cursor: pointer;" onclick="mostrarValoresAReceber()">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #4F46E5;">⏳</div>
            <div class="stat-info">
                <div class="stat-label">Valores a Receber</div>
                <div class="stat-value" id="valoresAReceber" style="color: #4F46E5;">R$ 0,00</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(239, 71, 111, 0.2), rgba(239, 71, 111, 0.05)); cursor: pointer;" onclick="mostrarDespesasDoMes()">
            <div class="stat-icon" style="background: rgba(239, 71, 111, 0.15); color: #EF476F;">💸</div>
            <div class="stat-info">
                <div class="stat-label">Despesas do Mês</div>
                <div class="stat-value" id="totalDespesas" style="color: #EF476F;">R$ 0,00</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 107, 53, 0.05)); cursor: pointer;" onclick="mostrarCustosEventos()">
            <div class="stat-icon" style="background: rgba(255, 107, 53, 0.15); color: #FF6B35;">🎯</div>
            <div class="stat-info">
                <div class="stat-label">Custos de Eventos</div>
                <div class="stat-value" id="totalCustosEventos" style="color: #FF6B35;">R$ 0,00</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 210, 63, 0.2), rgba(255, 210, 63, 0.05));">
            <div class="stat-icon" style="background: rgba(255, 210, 63, 0.15); color: #FFD23F;">📈</div>
            <div class="stat-info">
                <div class="stat-label">Lucro Líquido</div>
                <div class="stat-value" id="lucroLiquido" style="color: #06D6A0;">R$ 0,00</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 107, 53, 0.05)); cursor: pointer;">
            <div class="stat-icon" style="background: rgba(255, 107, 53, 0.15); color: #FF6B35;">🎉</div>
            <div class="stat-info">
                <div class="stat-label">Eventos em Andamento</div>
                <div class="stat-value" id="totalEventosAndamento">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(6, 214, 160, 0.05)); cursor: pointer;">
            <div class="stat-icon" style="background: rgba(6, 214, 160, 0.15); color: #06D6A0;">✅</div>
            <div class="stat-info">
                <div class="stat-label">Eventos Concluídos</div>
                <div class="stat-value" id="totalEventosConcluidos">0</div>
            </div>
        </div>
    </div>
    </div>

    <!-- Main Content Grid (INVERTIDO: Transações à ESQUERDA) -->
    <style>
        @media (max-width: 768px) {
            .content-grid { grid-template-columns: 1fr !important; }
            .col-transacoes { order: 2; }
            .col-acoes      { order: 1; }
        }
    </style>
    <div class="content-grid" style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-top: 30px;">

        <!-- COLUNA DA ESQUERDA - Transações Recentes -->
        <div class="col-transacoes">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Transações Recentes</h2>
                    <button class="btn btn-secondary" onclick="abrirModalDespesa()">+ DESPESA</button>
                </div>
                <div id="transacoesRecentes" style="max-height: 800px; overflow-y: auto;">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>
        </div>

        <!-- COLUNA DA DIREITA - Ações Rápidas e Cards -->
        <div class="col-acoes">
            <!-- Ações Rápidas -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h2 class="card-title">Ações Rápidas</h2>
                </div>
                <div class="quick-actions" style="display: grid; gap: 12px;">
                    <button class="action-btn" onclick="abrirModalNovoCliente()">
                        <div class="action-icon bg-accent">👥</div>
                        <div class="action-text">
                            <h4>Cliente</h4>
                            <p>Cadastrar cliente</p>
                        </div>
                    </button>
                    <button class="action-btn" onclick="abrirModalNovoOrcamento()">
                        <div class="action-icon bg-secondary">📋</div>
                        <div class="action-text">
                            <h4>Orçamento</h4>
                            <p>Criar orçamento</p>
                        </div>
                    </button>
                    <button class="action-btn" onclick="abrirModalNovoOrcamento()">
                        <div class="action-icon bg-primary">🎉</div>
                        <div class="action-text">
                            <h4>Novo Evento</h4>
                            <p>Cadastrar evento</p>
                        </div>
                    </button>
                    <button class="action-btn" onclick="abrirModalNovoServico()">
                        <div class="action-icon bg-success">🛠️</div>
                        <div class="action-text">
                            <h4>Serviços</h4>
                            <p>Cadastrar serviço</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Próximos Eventos -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h2 class="card-title">Próximos Eventos</h2>
                </div>
                <div id="proximosEventos" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>

            <!-- Origem dos Clientes -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <h2 class="card-title">Origem dos Clientes</h2>
                </div>
                <div id="origemClientes">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>

            <!-- Tipos de Eventos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Tipos de Eventos</h2>
                </div>
                <div id="tiposEventos">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Cliente -->
<div id="modalNovoCliente" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #FFD23F, #E8B800); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: #1a1f3a; margin: 0;">Novo Cliente</h3>
            <button onclick="fecharModalNovoCliente()" style="background: rgba(0,0,0,0.15); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: #1a1f3a;">&times;</button>
        </div>

        <form id="formNovoCliente" onsubmit="salvarNovoCliente(event)" style="padding: 24px;">
            <!-- Nome -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Nome Completo *</label>
                <input type="text" id="dashClienteNome" required placeholder="Nome do cliente"
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
            </div>

            <!-- Email + Telefone -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">E-mail</label>
                    <input type="email" id="dashClienteEmail" placeholder="email@exemplo.com (opcional)"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Telefone *</label>
                    <input type="tel" id="dashClienteTelefone" required placeholder="(00) 00000-0000" maxlength="15"
                        oninput="formatarTelefoneDash(this)"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <!-- CPF + Aniversário -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">CPF/CNPJ</label>
                    <input type="text" id="dashClienteCpf" placeholder="CPF: 000.000.000-00 ou CNPJ" maxlength="18"
                        oninput="formatarCpfDash(this)"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Data de Aniversário 🎂</label>
                    <input type="date" id="dashClienteAniversario"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <!-- Origem + Indicação -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Como nos conheceu? 📢 *</label>
                    <select id="dashClienteOrigem" required onchange="toggleIndicacaoDash()"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                        <option value="">Selecione...</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="google">Google</option>
                        <option value="indicacao">Indicação</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>
                <div id="dashIndicacaoDetalhes" style="display: none;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Quem indicou?</label>
                    <input type="text" id="dashClienteIndicacao" placeholder="Nome de quem indicou"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom: 16px; padding: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 12px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 12px;">📍 Endereço</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">CEP 📮</label>
                        <input type="text" id="dashClienteCep" placeholder="00000-000 (opcional)" maxlength="9"
                            oninput="formatarCepDash(this); buscarCepDash()"
                            style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Número</label>
                        <input type="text" id="dashClienteNumero" placeholder="Nº"
                            style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Rua/Avenida</label>
                    <input type="text" id="dashClienteRua" placeholder="Digite a rua ou preencha pelo CEP"
                        style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; box-sizing: border-box;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Bairro</label>
                        <input type="text" id="dashClienteBairro" placeholder="Digite o bairro"
                            style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Complemento</label>
                        <input type="text" id="dashClienteComplemento" placeholder="Apto, Bloco, etc."
                            style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; box-sizing: border-box;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Cidade</label>
                        <input type="text" id="dashClienteCidade" placeholder="Digite a cidade"
                            style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Estado</label>
                        <input type="text" id="dashClienteEstado" placeholder="UF" maxlength="2"
                            style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 13px; text-transform: uppercase; box-sizing: border-box;">
                    </div>
                </div>
                <div id="dashCepLoading" style="display: none; text-align: center; padding: 8px; background: rgba(255, 210, 63, 0.1); border-radius: 8px; margin-top: 8px; font-size: 13px; color: var(--accent);">
                    🔍 Buscando endereço...
                </div>
                <div id="dashCepError" style="display: none; text-align: center; padding: 8px; background: rgba(239, 71, 111, 0.1); border: 1px solid var(--danger); border-radius: 8px; margin-top: 8px; font-size: 13px; color: var(--danger);">
                    ❌ CEP não encontrado. Verifique o número digitado.
                </div>
                <div id="dashCepSuccess" style="display: none; text-align: center; padding: 8px; background: rgba(6, 214, 160, 0.1); border: 1px solid var(--success); border-radius: 8px; margin-top: 8px; font-size: 13px; color: var(--success);">
                    ✅ Endereço encontrado! Preencha o número da residência.
                </div>
            </div>

            <!-- Observações -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Observações</label>
                <textarea id="dashClienteObs" placeholder="Informações adicionais sobre o cliente..."
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; resize: vertical; min-height: 60px; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModalNovoCliente()" style="padding: 12px 24px; background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" id="btnSalvarCliente" style="padding: 12px 24px; background: #FFD23F; border: none; color: #1a1f3a; border-radius: 8px; font-weight: 700; cursor: pointer;">Cadastrar Cliente</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sucesso Cliente -->
<div id="modalSucessoCliente" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 10000; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #06D6A0, #05B88A); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 20px; font-weight: 700; color: white; margin: 0;">✅ Cliente Cadastrado!</h3>
            <button onclick="fecharModalSucessoCliente()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">&times;</button>
        </div>

        <div style="text-align: center; padding: 24px;">
            <div style="font-size: 64px; margin-bottom: 16px;">🎉</div>
            <h3 style="font-family: 'Syne', sans-serif; font-size: 20px; margin-bottom: 8px; color: var(--success);">
                Cliente cadastrado com sucesso!
            </h3>
            <p id="sucessoClienteNome" style="color: var(--text-secondary); margin-bottom: 24px; font-size: 16px;"></p>

            <div style="background: rgba(255, 210, 63, 0.1); border: 1px solid var(--accent); border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: left;">
                <div style="font-size: 14px; color: var(--accent); font-weight: 600; margin-bottom: 8px;">
                    💡 Próximo Passo
                </div>
                <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
                    Escolha uma das opções abaixo para continuar o cadastro:
                </div>
            </div>

            <div style="display: grid; gap: 12px;">
                <button onclick="atalhoOrcamentoDash()" style="width: 100%; padding: 16px; font-size: 15px; font-weight: 600; background: linear-gradient(135deg, var(--accent, #FFD23F), #FFA500); color: #1a1f3a; border: none; border-radius: 8px; cursor: pointer; text-align: center;">
                    💼 Criar Orçamento Completo
                    <div style="font-size: 11px; opacity: 0.8; margin-top: 4px; font-weight: 400;">
                        Monte um orçamento com serviços, taxas e descontos
                    </div>
                </button>

                <button onclick="atalhoEventoDash()" style="width: 100%; padding: 16px; font-size: 15px; font-weight: 600; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text-primary); border-radius: 8px; cursor: pointer; text-align: center;">
                    🎉 Criar Evento Direto
                    <div style="font-size: 11px; opacity: 0.7; margin-top: 4px; font-weight: 400;">
                        Cadastre o evento sem orçamento
                    </div>
                </button>

                <button onclick="atalhoFinanceiroDash()" style="width: 100%; padding: 16px; font-size: 15px; font-weight: 600; background: rgba(0, 78, 137, 0.2); color: var(--secondary, #004E89); border: 1px solid var(--secondary, #004E89); border-radius: 8px; cursor: pointer; text-align: center;">
                    💰 Adicionar Dados Financeiros
                    <div style="font-size: 11px; opacity: 0.7; margin-top: 4px; font-weight: 400;">
                        Registre valores, sinais e prazos
                    </div>
                </button>

                <button onclick="fecharModalSucessoCliente()" style="width: 100%; padding: 12px; font-size: 14px; background: transparent; border: 1px solid var(--border); color: var(--text-secondary); border-radius: 8px; cursor: pointer;">
                    ⏭️ Adicionar Depois
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Orcamento/Evento -->
<div id="modalNovoOrcamento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 900px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: white; margin: 0;">💼 Novo Orçamento Completo</h3>
            <button onclick="fecharModalNovoOrcamento()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">&times;</button>
        </div>

        <form id="formNovoOrcamento" onsubmit="salvarNovoOrcamento(event)" style="padding: 24px;">

            <!-- SEÇÃO 1: DADOS DO CLIENTE -->
            <div style="background: rgba(255, 107, 53, 0.1); border-left: 4px solid var(--primary, #FF6B35); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: var(--primary, #FF6B35);">👤 Dados do Cliente</h3>
                <div style="margin-bottom: 0;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Cliente *</label>
                    <select id="dashOrcCliente" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        <option value="">Selecione o cliente...</option>
                    </select>
                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                        Não encontrou? <a href="#" onclick="fecharModalNovoOrcamento(); abrirModalNovoCliente(); return false;" style="color: var(--primary);">Cadastre um cliente primeiro</a>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO 2: DADOS DO EVENTO -->
            <div style="background: rgba(0, 78, 137, 0.1); border-left: 4px solid #1877F2; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: #1877F2;">🎉 Dados do Evento</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Tipo de Evento *</label>
                        <select id="dashOrcTipo" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                            <option value="">Selecione o tipo...</option>
                            <option value="Casamento">Casamento</option>
                            <option value="Aniversário">Aniversário</option>
                            <option value="Aniversário 15 Anos">Aniversário 15 Anos</option>
                            <option value="Formatura">Formatura</option>
                            <option value="Corporativo">Evento Corporativo</option>
                            <option value="Confraternização">Confraternização</option>
                            <option value="Batizado">Batizado</option>
                            <option value="Chá de Bebê">Chá de Bebê</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Data Prevista *</label>
                        <input type="date" id="dashOrcData" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                    </div>
                </div>

                <!-- Horários com Duração -->
                <div style="background: rgba(0, 78, 137, 0.1); border: 1px solid #1877F2; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <div style="font-size: 13px; color: #1877F2; font-weight: 600; margin-bottom: 8px;">⏰ Horário do Evento</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Hora de Início</label>
                            <input type="time" id="dashOrcHoraInicio" oninput="calcularDuracaoOrc()" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Hora de Término</label>
                            <input type="time" id="dashOrcHoraFim" oninput="calcularDuracaoOrc()" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Duração</label>
                            <input type="text" id="dashOrcDuracao" readonly style="width: 100%; padding: 12px; background: rgba(0, 78, 137, 0.2); border: 1px solid #1877F2; border-radius: 8px; color: #1877F2; font-size: 14px; font-weight: 600;" placeholder="Automático">
                        </div>
                    </div>
                </div>

                <!-- Endereço completo com CEP -->
                <div style="margin-bottom: 0;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Endereço do Evento</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">CEP 📮</label>
                            <input type="text" id="dashOrcCep" placeholder="00000-000" maxlength="9" oninput="formatarCepOrc(this); buscarCepOrc()" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Número</label>
                            <input type="text" id="dashOrcNumero" placeholder="Nº" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Rua/Avenida</label>
                        <input type="text" id="dashOrcRua" placeholder="Digite a rua ou preencha pelo CEP" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Bairro</label>
                            <input type="text" id="dashOrcBairro" placeholder="Bairro" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Complemento</label>
                            <input type="text" id="dashOrcComplemento" placeholder="Salão, Quadra, etc." style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Cidade</label>
                            <input type="text" id="dashOrcCidade" placeholder="Cidade" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Estado</label>
                            <input type="text" id="dashOrcEstado" placeholder="UF" maxlength="2" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; text-transform: uppercase;">
                        </div>
                    </div>
                    <div id="cepOrcLoading" style="display: none; text-align: center; padding: 8px; background: rgba(255, 210, 63, 0.1); border-radius: 8px; margin-top: 8px; font-size: 13px; color: #FFD23F;">🔍 Buscando endereço...</div>
                </div>
            </div>

            <!-- SEÇÃO 3: SERVIÇOS E VALORES -->
            <div style="background: rgba(6, 214, 160, 0.1); border-left: 4px solid #06D6A0; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: #06D6A0;">💰 Serviços e Valores</h3>

                <!-- Seleção de Serviços -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Serviços Inclusos</label>
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span style="font-size: 14px; color: var(--text-secondary);">Selecione os serviços:</span>
                            <button type="button" onclick="fecharModalNovoOrcamento(); abrirModalNovoServico();" style="padding: 8px 16px; font-size: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: #06D6A0; cursor: pointer; font-weight: 600;">+ Novo Serviço</button>
                        </div>
                        <div id="orcServicosCheckboxes" style="max-height: 300px; overflow-y: auto;">
                            <p style="text-align: center; color: var(--text-secondary); padding: 20px; font-size: 13px;">Carregando serviços...</p>
                        </div>
                    </div>
                    <div id="orcServicosSelecionadosBox" style="background: rgba(6, 214, 160, 0.1); border: 1px solid #06D6A0; border-radius: 8px; padding: 12px; display: none;">
                        <div style="font-size: 13px; color: #06D6A0; margin-bottom: 8px; font-weight: 600;">✓ Serviços Selecionados:</div>
                        <div id="orcServicosSelecionadosList"></div>
                    </div>
                </div>

                <!-- Outras Taxas -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Outras Taxas 📋</label>
                    <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); border-radius: 12px; padding: 16px;">
                        <!-- Taxa Deslocamento -->
                        <div id="taxaDeslocamentoBox" style="background: rgba(255, 210, 63, 0.1); border: 1px solid #FFD23F; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                                <div style="flex: 1;">
                                    <div style="font-size: 13px; color: #FFD23F; font-weight: 600; margin-bottom: 4px;">🚗 Deslocamento</div>
                                    <input type="number" step="0.01" id="taxaDeslocamentoValor" placeholder="Valor (R$)" oninput="calcularTotalOrc()" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                                </div>
                                <button type="button" onclick="document.getElementById('taxaDeslocamentoBox').style.display='none'; document.getElementById('taxaDeslocamentoValor').value=''; calcularTotalOrc();" style="background: none; border: none; color: #EF476F; cursor: pointer; font-size: 20px; padding: 8px;">🗑️</button>
                            </div>
                        </div>
                        <div id="outrasTaxasListDash"></div>
                        <button type="button" onclick="adicionarTaxaDash()" style="width: 100%; margin-top: 8px; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: var(--text-secondary); cursor: pointer; font-weight: 600;">+ Adicionar Taxa</button>
                    </div>
                </div>

                <!-- Desconto -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Desconto 🏷️</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <input type="number" step="0.01" id="dashOrcDesconto" placeholder="0,00" oninput="calcularTotalOrc()" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        <select id="dashOrcDescontoTipo" onchange="calcularTotalOrc()" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                            <option value="valor">R$ (Valor)</option>
                            <option value="percentual">% (Percentual)</option>
                        </select>
                    </div>
                </div>

                <!-- Resumo Financeiro -->
                <div style="background: rgba(255, 107, 53, 0.1); border: 2px solid var(--primary, #FF6B35); border-radius: 12px; padding: 16px; margin-top: 16px;">
                    <div style="font-size: 14px; color: var(--primary, #FF6B35); font-weight: 600; margin-bottom: 12px;">💰 Resumo Financeiro:</div>
                    <div style="display: grid; gap: 8px; font-size: 14px;">
                        <div style="display: flex; justify-content: space-between;"><span>Subtotal (Serviços):</span><strong id="orcSubtotalServicos">R$ 0,00</strong></div>
                        <div style="display: flex; justify-content: space-between;"><span>Outras Taxas:</span><strong id="orcTotalTaxas">R$ 0,00</strong></div>
                        <div style="display: flex; justify-content: space-between; color: #EF476F;"><span>Desconto:</span><strong id="orcTotalDesconto">- R$ 0,00</strong></div>
                        <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid var(--primary, #FF6B35); font-size: 18px;">
                            <span style="font-weight: 700;">VALOR TOTAL:</span>
                            <strong id="orcValorTotalDisplay" style="color: #06D6A0;">R$ 0,00</strong>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="dashOrcValorTotal">
            </div>

            <!-- SEÇÃO 4: INFORMAÇÕES ADICIONAIS -->
            <div style="background: rgba(255, 210, 63, 0.1); border-left: 4px solid #FFD23F; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="font-size: 16px; margin-bottom: 12px; color: #FFD23F;">📝 Informações Adicionais</h3>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Status do Evento</label>
                    <select id="dashOrcStatus" required style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px;">
                        <option value="aguardando" selected>⏳ Aguardando Aprovação</option>
                        <option value="andamento">🔄 Em Andamento</option>
                        <option value="concluido">✅ Concluído</option>
                        <option value="cancelado">❌ Cancelado</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Observações do Orçamento</label>
                    <textarea id="dashOrcObs" placeholder="Detalhes, condições especiais, etc..." style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; resize: vertical; min-height: 60px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Data de Criação</label>
                        <input type="date" id="dashOrcDataCriacao" readonly style="width: 100%; padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; color: var(--text-secondary); font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px;">Validade do Orçamento (30 dias)</label>
                        <input type="date" id="dashOrcValidade" readonly style="width: 100%; padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 8px; color: var(--text-secondary); font-size: 14px;">
                    </div>
                </div>
            </div>

            <button type="submit" id="btnSalvarOrcamento" style="width: 100%; padding: 16px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 16px;">💼 Gerar Orçamento e Criar Evento</button>
        </form>
    </div>
</div>

<!-- Modal Novo Servico -->
<div id="modalNovoServico" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #06D6A0, #05B88A); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: #1a1f3a; margin: 0;">Novo Servico</h3>
            <button onclick="fecharModalNovoServico()" style="background: rgba(0,0,0,0.15); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: #1a1f3a;">&times;</button>
        </div>

        <form id="formNovoServico" onsubmit="salvarNovoServico(event)" style="padding: 24px;">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Nome do Servico *</label>
                <input type="text" id="dashServicoNome" required placeholder="Ex: Pista de Led 4x4"
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Categoria *</label>
                    <select id="dashServicoCategoria" required
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                        <option value="som_iluminacao">Som e Iluminacao</option>
                        <option value="decoracao">Decoracao</option>
                        <option value="audiovisual">Audiovisual</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Valor Base (R$) *</label>
                    <input type="number" step="0.01" id="dashServicoValor" required placeholder="0,00"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary); font-size: 13px; text-transform: uppercase;">Descricao</label>
                <textarea id="dashServicoDescricao" placeholder="Descricao opcional do servico..."
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); border-radius: 8px; color: white; font-size: 14px; resize: vertical; min-height: 60px; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModalNovoServico()" style="padding: 12px 24px; background: rgba(255,255,255,0.1); border: 1px solid var(--border); color: white; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" id="btnSalvarServico" style="padding: 12px 24px; background: #06D6A0; border: none; color: #1a1f3a; border-radius: 8px; font-weight: 700; cursor: pointer;">Cadastrar Servico</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Nova Despesa -->
<div id="modalDespesa" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 550px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #EF476F, #D63B5E); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: white; margin: 0;">Novo Custo Geral</h3>
            <button onclick="fecharModalDespesa()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">&times;</button>
        </div>

        <form id="formDespesa" onsubmit="salvarDespesa(event)" style="padding: 24px;">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Descrição *</label>
                <input type="text" id="despesaDescricao" required placeholder="Ex: Aluguel, Internet, Material..."
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Categoria *</label>
                    <select id="despesaCategoria" required
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                        <option value="">Selecione...</option>
                        <option value="retirada_lucros">Retirada de Lucros</option>
                        <option value="prestacao">Prestação/Financiamento</option>
                        <option value="seguro">Seguro</option>
                        <option value="manutencao">Manutenção</option>
                        <option value="marketing">Marketing/Divulgação</option>
                        <option value="software">Software/Ferramentas</option>
                        <option value="aluguel">Aluguel</option>
                        <option value="internet">Internet/Telefone</option>
                        <option value="combustivel">Combustível</option>
                        <option value="alimentacao">Alimentação</option>
                        <option value="transporte">Transporte</option>
                        <option value="equipamentos">Equipamentos</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Valor (R$) *</label>
                    <input type="number" step="0.01" id="despesaValor" required placeholder="0,00"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Data *</label>
                    <input type="date" id="despesaData" required
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Forma de Pagamento</label>
                    <select id="despesaFormaPag"
                        style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                        <option value="">Selecione...</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="pix">PIX</option>
                        <option value="credito">Cartão de Crédito</option>
                        <option value="debito">Cartão de Débito</option>
                        <option value="transferencia">Transferência</option>
                        <option value="boleto">Boleto</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Conta Bancária</label>
                <select id="despesaConta"
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; box-sizing: border-box;">
                    <option value="">Selecione (opcional)</option>
                </select>
                <div style="font-size: 11px; color: var(--text-secondary, #A0AEC0); margin-top: 5px;">
                    O valor será deduzido da conta selecionada
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Observações</label>
                <textarea id="despesaObs" placeholder="Detalhes da despesa..."
                    style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; resize: vertical; min-height: 60px; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModalDespesa()" style="padding: 12px 24px; background: rgba(239,71,111,0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" id="btnSalvarDespesa" style="padding: 12px 24px; background: #EF476F; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer;">Adicionar Despesa</button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================
// CARREGAR DADOS DO DASHBOARD
// ============================================
async function carregarDashboard() {
    try {
        await Promise.all([
            carregarEstatisticas(),
            carregarProximosEventos(),
            carregarOrigemClientes(),
            carregarTiposEventos(),
            carregarTransacoes()
        ]);
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

// ============================================
// ESTATÍSTICAS
// ============================================
async function carregarEstatisticas() {
    const mesAtual = new Date().getMonth() + 1;
    const anoAtual = new Date().getFullYear();
    
    try {
        // Eventos
        const resEventos = await fetch('../api/eventos.php?action=list');
        const dataEventos = await resEventos.json();
        
        if (dataEventos.success) {
            const eventos = dataEventos.eventos;
            const hoje = new Date();
            
            // Eventos em andamento (futuros ou do mês atual)
            const emAndamento = eventos.filter(e => {
                const dataEvento = new Date(e.data_evento);
                return e.status !== 'cancelado' && dataEvento >= hoje;
            }).length;
            
            // Eventos concluídos no ano
            const concluidos = eventos.filter(e => {
                const dataEvento = new Date(e.data_evento);
                return dataEvento.getFullYear() === anoAtual && dataEvento < hoje;
            }).length;
            
            // Eventos do mês
            const eventosMes = eventos.filter(e => {
                const dataEvento = new Date(e.data_evento);
                return dataEvento.getMonth() + 1 === mesAtual && 
                       dataEvento.getFullYear() === anoAtual;
            }).length;
            
            document.getElementById('totalEventosAndamento').textContent = emAndamento;
            document.getElementById('totalEventosConcluidos').textContent = concluidos;
            const elEventosMes = document.getElementById('eventosDoMes');
            if (elEventosMes) elEventosMes.textContent = eventosMes;
            const elConcluidosAno = document.getElementById('eventosConcluidosAno');
            if (elConcluidosAno) elConcluidosAno.textContent = concluidos;
        }
        
        // Buscar receitas do financeiro (inclui eventos + transações bancárias)
        let receitasMes = 0;
        try {
            const responseFinanceiro = await fetch(`../api/financeiro.php?action=resumo_financeiro&mes=${mesAtual}&ano=${anoAtual}`);
            const dataFinanceiro = await responseFinanceiro.json();
            
            if (dataFinanceiro.success) {
                receitasMes = dataFinanceiro.resumo.receitas || dataFinanceiro.resumo.total_receitas || 0;
                document.getElementById('totalReceitas').textContent = formatarMoeda(receitasMes);
            }
        } catch (error) {
            console.error('Erro ao buscar receitas:', error);
            // Fallback: usar valor dos eventos se a API falhar
            receitasMes = dataEventos.eventos
                .filter(e => {
                    const data = new Date(e.data_evento);
                    return data.getMonth() + 1 === mesAtual && 
                           data.getFullYear() === anoAtual;
                })
                .reduce((sum, e) => sum + parseFloat(e.valor_sinal || 0), 0);
            document.getElementById('totalReceitas').textContent = formatarMoeda(receitasMes);
        }
        
        // Custos Gerais da Empresa
        let totalCustosGerais = 0;
        try {
            const response = await fetch('../api/custos.php?action=listar');
            const text = await response.text();

            if (text && text.trim() !== '') {
                const dataCustos = JSON.parse(text);

                if (dataCustos.success && dataCustos.custos) {
                    const custosGeraisMes = dataCustos.custos.filter(c => {
                        const data = new Date(c.data_custo);
                        return data.getMonth() + 1 === mesAtual &&
                               data.getFullYear() === anoAtual;
                    });

                    totalCustosGerais = custosGeraisMes.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
                }
            }
        } catch (custoError) {
            console.warn('Custos gerais não disponíveis');
        }

        // Custos de Eventos (buscar separadamente)
        let totalCustosEventos = 0;
        let numCustosEventos = 0;
        try {
            const resCustosEv = await fetch(`../api/custos.php?action=custos_eventos_list&mes=${mesAtual}&ano=${anoAtual}`);
            const dataCustosEv = await resCustosEv.json();

            if (dataCustosEv.success && dataCustosEv.custos) {
                numCustosEventos = dataCustosEv.custos.length;
                totalCustosEventos = dataCustosEv.custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            }
        } catch (custoEvError) {
            console.warn('Custos de eventos não disponíveis');
        }

        // Atualizar cards
        document.getElementById('totalDespesas').textContent = formatarMoeda(totalCustosGerais);
        document.getElementById('totalCustosEventos').textContent = formatarMoeda(totalCustosEventos);
        const elNumCustos = document.getElementById('numeroCustosEventos');
        if (elNumCustos) elNumCustos.textContent = numCustosEventos;

        // Lucro = Receitas - Custos Gerais - Custos Eventos
        const lucro = receitasMes - totalCustosGerais - totalCustosEventos;
        document.getElementById('lucroLiquido').textContent = formatarMoeda(lucro);

        const margem = receitasMes > 0 ? ((lucro / receitasMes) * 100).toFixed(1) : 0;
        const elPercentLucro = document.getElementById('percentLucro');
        if (elPercentLucro) elPercentLucro.textContent = margem;
        
        // Valores a receber - calcular baseado nos eventos do mês
        let valoresAReceber = 0;
        let clientesPendentes = 0;

        if (dataEventos.success) {
            dataEventos.eventos.forEach(evento => {
                const dataEvento = new Date(evento.data_evento);
                const mesEvento = dataEvento.getMonth() + 1;
                const anoEvento = dataEvento.getFullYear();

                // Apenas eventos do mês atual com status em_andamento ou concluido
                if (mesEvento !== mesAtual || anoEvento !== anoAtual) return;
                if (evento.status !== 'em_andamento' && evento.status !== 'concluido') return;

                const valorTotal = parseFloat(evento.valor_total || 0);
                const totalPago = parseFloat(evento.total_pago || evento.valor_sinal || 0);
                const saldo = valorTotal - totalPago;

                if (saldo > 0.01) {
                    valoresAReceber += saldo;
                    clientesPendentes++;
                }
            });
        }

        document.getElementById('valoresAReceber').textContent = formatarMoeda(valoresAReceber);
        const elNumClientes = document.getElementById('numClientes');
        if (elNumClientes) elNumClientes.textContent = clientesPendentes;
        
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// ============================================
// PRÓXIMOS EVENTOS
// ============================================
async function carregarProximosEventos() {
    try {
        const res = await fetch('../api/eventos.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            const hoje = new Date();
            const proximos = data.eventos
                .filter(e => new Date(e.data_evento) >= hoje && e.status !== 'cancelado')
                .sort((a, b) => new Date(a.data_evento) - new Date(b.data_evento))
                .slice(0, 5);
            
            const container = document.getElementById('proximosEventos');
            
            if (proximos.length === 0) {
                container.innerHTML = '<p style="color: var(--text-secondary); text-align: center; padding: 20px;">Nenhum evento próximo</p>';
                return;
            }
            
            container.innerHTML = proximos.map(e => {
                let status = 'Em Andamento';
                let statusColor = 'var(--success)';
                if (e.status === 'aguardando') { status = 'Aguardando'; statusColor = 'var(--warning)'; }
                else if (e.status === 'confirmado') { status = 'Confirmado'; statusColor = 'var(--success)'; }
                else if (e.status === 'em_andamento') { status = 'Em Andamento'; statusColor = 'var(--success)'; }
                else if (e.status === 'concluido') { status = 'Concluído'; statusColor = '#06D6A0'; }
                else if (e.status === 'cancelado') { status = 'Cancelado'; statusColor = 'var(--danger)'; }
                
                return `
                    <div class="event-item" style="background: rgba(255, 255, 255, 0.02); border-radius: 8px; padding: 12px; cursor: pointer;" 
                         onclick="window.location.href='eventos-eventpro.php'">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <div style="font-weight: 600; margin-bottom: 4px;">${e.tipo}</div>
                                <div style="font-size: 13px; color: var(--text-secondary);">
                                    👤 ${e.cliente_nome} | 📅 ${formatarData(e.data_evento)}
                                </div>
                            </div>
                            <span style="font-size: 11px; padding: 4px 8px; background: ${statusColor}20; color: ${statusColor}; border-radius: 4px; font-weight: 600;">
                                ${status}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar próximos eventos:', error);
    }
}

// ============================================
// ORIGEM DOS CLIENTES
// ============================================
async function carregarOrigemClientes() {
    try {
        const res = await fetch('../api/clientes.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            const clientes = data.clientes;
            const origens = {};
            const totalClientes = clientes.length;
            
            clientes.forEach(c => {
                const origem = c.origem || 'Indicação';
                origens[origem] = (origens[origem] || 0) + 1;
            });
            
            const container = document.getElementById('origemClientes');
            const origensConfig = {
                'Instagram': { icon: '📸', color: '#E1306C', bg: 'rgba(225, 48, 108, 0.15)' },
                'Facebook': { icon: '📘', color: '#1877F2', bg: 'rgba(24, 119, 242, 0.15)' },
                'Google': { icon: '🔍', color: '#4285F4', bg: 'rgba(6, 214, 160, 0.15)' },
                'Indicação': { icon: '👥', color: '#FFD23F', bg: 'rgba(255, 210, 63, 0.15)' },
                'Outros': { icon: '📌', color: '#A0AEC0', bg: 'rgba(160, 174, 192, 0.15)' }
            };

            container.innerHTML = Object.entries(origens)
                .sort((a, b) => b[1] - a[1])
                .map(([origem, qtd]) => {
                    const percent = totalClientes > 0 ? ((qtd / totalClientes) * 100).toFixed(0) : 0;
                    const config = origensConfig[origem] || origensConfig['Outros'];

                    return `
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: ${config.bg}; display: flex; align-items: center; justify-content: center; font-size: 16px;">${config.icon}</div>
                                    <span style="font-size: 14px; font-weight: 600;">${origem}</span>
                                </div>
                                <span style="font-size: 14px; font-weight: 700; color: ${config.color};">${qtd}</span>
                            </div>
                            <div style="height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: ${percent}%; background: ${config.color}; transition: width 0.3s;"></div>
                            </div>
                            <div style="text-align: right; font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                                ${percent}%
                            </div>
                        </div>
                    `;
                }).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar origem clientes:', error);
    }
}

// ============================================
// TIPOS DE EVENTOS
// ============================================
async function carregarTiposEventos() {
    try {
        const res = await fetch('../api/eventos.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            const eventos = data.eventos;
            const tipos = {};
            const totalEventos = eventos.length;
            
            eventos.forEach(e => {
                const tipo = e.tipo || 'Outro';
                tipos[tipo] = (tipos[tipo] || 0) + 1;
            });
            
            const container = document.getElementById('tiposEventos');
            const tiposIcons = {
                'casamento': '💒',
                'aniversário': '🎂',
                'aniversario': '🎂',
                'aniversário 15 anos': '👑',
                'aniversario 15 anos': '👑',
                'formatura': '🎓',
                'corporativo': '🏢',
                'infantil': '🎈',
                'confraternização': '🎊',
                'confraternizacao': '🎊'
            };
            const cores = ['#FF6B35', '#06D6A0', '#4F46E5', '#FFD23F', '#EF476F'];

            container.innerHTML = Object.entries(tipos)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5)
                .map(([tipo, qtd], index) => {
                    const percent = totalEventos > 0 ? ((qtd / totalEventos) * 100).toFixed(0) : 0;
                    const cor = cores[index % cores.length];
                    const icon = tiposIcons[tipo.toLowerCase()] || '📅';

                    return `
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: ${cor}20; display: flex; align-items: center; justify-content: center; font-size: 16px;">${icon}</div>
                                    <span style="font-size: 14px; font-weight: 600;">${tipo}</span>
                                </div>
                                <span style="font-size: 14px; font-weight: 700; color: ${cor};">${qtd}</span>
                            </div>
                            <div style="height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: ${percent}%; background: ${cor}; transition: width 0.3s;"></div>
                            </div>
                            <div style="text-align: right; font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                                ${percent}%
                            </div>
                        </div>
                    `;
                }).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar tipos de eventos:', error);
    }
}

// ============================================
// TRANSAÇÕES RECENTES (dados de movimentacoes_financeiras + custos gerais)
// ============================================
async function carregarTransacoes() {
    try {
        const mesAtual = new Date().getMonth() + 1;
        const anoAtual = new Date().getFullYear();
        const transacoes = [];

        // Buscar sinais de eventos (usar data_sinal com fallback para data_evento)
        try {
            const resEventos = await fetch('../api/eventos.php?action=list');
            const dataEventos = await resEventos.json();

            if (dataEventos.success && dataEventos.eventos) {
                dataEventos.eventos.forEach(e => {
                    if (parseFloat(e.valor_sinal || 0) > 0 && e.status !== 'cancelado') {
                        const dataSinal = e.data_sinal || e.data_evento;
                        const d = new Date(dataSinal + 'T00:00:00');
                        if (d.getMonth() + 1 === mesAtual && d.getFullYear() === anoAtual) {
                            transacoes.push({
                                tipo: 'receita',
                                descricao: `Sinal - ${e.tipo} (${e.cliente_nome})`,
                                valor: parseFloat(e.valor_sinal),
                                data: dataSinal
                            });
                        }
                    }
                });
            }
        } catch (e) {
            console.warn('Erro ao carregar sinais de eventos:', e);
        }

        // Buscar receitas e despesas da tabela movimentacoes_financeiras (mês atual)
        try {
            const resMovs = await fetch(`../api/financeiro.php?action=transacoes&mes=${mesAtual}&ano=${anoAtual}`);
            const dataMovs = await resMovs.json();

            if (dataMovs.success && dataMovs.transacoes) {
                dataMovs.transacoes.forEach(m => {
                    // Evitar duplicar sinais já incluídos acima
                    if (m.tipo_movimentacao === 'sinal_evento' || (m.descricao && m.descricao.startsWith('Sinal - '))) return;
                    transacoes.push({
                        tipo: m.tipo || 'receita',
                        descricao: m.descricao,
                        valor: parseFloat(m.valor || 0),
                        data: m.data
                    });
                });
            }
        } catch (e) {
            console.warn('Erro ao carregar movimentações:', e);
        }

        // Buscar custos gerais do mês atual
        try {
            const response = await fetch('../api/custos.php?action=listar');
            const text = await response.text();

            if (text && text.trim() !== '') {
                const dataCustos = JSON.parse(text);

                if (dataCustos.success && dataCustos.custos) {
                    dataCustos.custos.forEach(c => {
                        const d = new Date(c.data_custo + 'T00:00:00');
                        if (d.getMonth() + 1 !== mesAtual || d.getFullYear() !== anoAtual) return;
                        transacoes.push({
                            tipo: 'despesa',
                            descricao: c.descricao,
                            valor: parseFloat(c.valor),
                            data: c.data_custo
                        });
                    });
                }
            }
        } catch (custoError) {
            console.warn('Custos gerais não disponíveis para transações');
        }

        // Ordenar por data (mais recente primeiro)
        transacoes.sort((a, b) => new Date(b.data + 'T00:00:00') - new Date(a.data + 'T00:00:00'));

        const container = document.getElementById('transacoesRecentes');

        if (transacoes.length === 0) {
            container.innerHTML = '<p style="color: var(--text-secondary); text-align: center; padding: 40px;">Nenhuma transação neste mês</p>';
            return;
        }

        container.innerHTML = transacoes.slice(0, 6).map(t => {
            const isReceita = t.tipo === 'receita';
            const icon = isReceita ? '💰' : '💸';
            const cor = isReceita ? 'var(--success)' : 'var(--danger)';
            const sinal = isReceita ? '+' : '-';

            return `
                <div class="transaction-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255, 255, 255, 0.02); border-radius: 8px; margin-bottom: 8px; border-left: 3px solid ${cor};">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span>${icon}</span>
                            <span style="font-weight: 600;">${t.descricao}</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary);">
                            ${formatarData(t.data)}
                        </div>
                    </div>
                    <div style="font-size: 16px; font-weight: 700; color: ${cor};">
                        ${sinal} ${formatarMoeda(t.valor)}
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Erro ao carregar transações:', error);
    }
}

// ============================================
// UTILITÁRIOS
// ============================================
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    }).format(valor);
}

function formatarData(data) {
    if (!data) return '-';
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

// ============================================
// MODAL - RECEITAS DO MÊS
// ============================================
async function mostrarReceitasDoMes() {
    const mesAtual = new Date().getMonth() + 1;
    const anoAtual = new Date().getFullYear();
    const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                   'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    const mesNome = meses[mesAtual - 1];

    try {
        const receitas = [];

        // Buscar receitas de movimentacoes_financeiras (sinais e pagamentos do mês)
        try {
            const resMovs = await fetch(`../api/financeiro.php?action=transacoes&mes=${mesAtual}&ano=${anoAtual}&tipo=receita`);
            const dataMovs = await resMovs.json();

            if (dataMovs.success && dataMovs.transacoes) {
                dataMovs.transacoes.forEach(m => {
                    receitas.push({
                        descricao: m.descricao,
                        valor: parseFloat(m.valor || 0),
                        data: m.data,
                        formaPagamento: m.forma_pagamento || 'Não informado'
                    });
                });
            }
        } catch(e) {
            console.warn('Erro ao carregar movimentações:', e);
        }

        if (receitas.length === 0) {
            alert('Nenhuma receita registrada neste mês!');
            return;
        }

        // Ordenar por data
        receitas.sort((a, b) => new Date(b.data) - new Date(a.data));

        const totalReceitas = receitas.reduce((sum, r) => sum + r.valor, 0);

        // Agrupar por forma de pagamento
        const porFormaPagamento = {};
        receitas.forEach(r => {
            const forma = r.formaPagamento || 'Não informado';
            if (!porFormaPagamento[forma]) {
                porFormaPagamento[forma] = { total: 0, count: 0 };
            }
            porFormaPagamento[forma].total += r.valor;
            porFormaPagamento[forma].count++;
        });

        let html = `
            <div id="modalReceitas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;" onclick="this.remove()">
                <div style="background: var(--bg-card, #1a1f3a); border-radius: 12px; max-width: 800px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 0;" onclick="event.stopPropagation()">

                    <!-- Header -->
                    <div style="position: sticky; top: 0; background: var(--bg-card, #1a1f3a); padding: 24px; border-bottom: 2px solid var(--border, #2d3561); z-index: 10;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h2 style="margin: 0; font-size: 24px;">Receitas de ${mesNome}/${anoAtual}</h2>
                            <button onclick="document.getElementById('modalReceitas').remove()" style="background: none; border: none; font-size: 28px; cursor: pointer; color: var(--text-secondary, #a0aec0); line-height: 1;">×</button>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div style="padding: 16px; background: rgba(6, 214, 160, 0.1); border-radius: 8px; border-left: 4px solid var(--success, #06D6A0);">
                                <div style="font-size: 13px; color: var(--text-secondary, #a0aec0); margin-bottom: 4px;">Total do Mês</div>
                                <div style="font-size: 24px; font-weight: 700; color: var(--success, #06D6A0);">${formatarMoeda(totalReceitas)}</div>
                            </div>
                            <div style="padding: 16px; background: rgba(79, 70, 229, 0.1); border-radius: 8px; border-left: 4px solid #4F46E5;">
                                <div style="font-size: 13px; color: var(--text-secondary, #a0aec0); margin-bottom: 4px;">Total de Receitas</div>
                                <div style="font-size: 24px; font-weight: 700; color: #4F46E5;">${receitas.length}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo por Forma de Pagamento -->
                    <div style="padding: 24px; border-bottom: 2px solid var(--border, #2d3561);">
                        <h3 style="margin: 0 0 16px 0; font-size: 16px; color: var(--text-secondary, #a0aec0);">Por Forma de Pagamento</h3>
                        <div style="display: grid; gap: 8px;">
                            ${Object.entries(porFormaPagamento).sort((a, b) => b[1].total - a[1].total).map(([forma, data]) => {
                                const percent = ((data.total / totalReceitas) * 100).toFixed(1);
                                return `
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                                        <div>
                                            <div style="font-weight: 600; text-transform: capitalize;">${forma}</div>
                                            <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">${data.count} receita${data.count > 1 ? 's' : ''}</div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-weight: 700; color: var(--success, #06D6A0);">${formatarMoeda(data.total)}</div>
                                            <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">${percent}%</div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <!-- Lista de Receitas -->
                    <div style="padding: 24px;">
                        <h3 style="margin: 0 0 16px 0; font-size: 16px; color: var(--text-secondary, #a0aec0);">Todas as Receitas</h3>
                        <div style="display: grid; gap: 12px;">
                            ${receitas.map(r => `
                                <div style="padding: 16px; border: 1px solid var(--border, #2d3561); border-radius: 8px; background: rgba(255, 255, 255, 0.02);">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; font-size: 15px; margin-bottom: 4px;">${r.descricao || 'Receita'}</div>
                                            <div style="font-size: 13px; color: var(--text-secondary, #a0aec0); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span>${formatarData(r.data)}</span>
                                                <span>•</span>
                                                <span style="text-transform: capitalize;">${r.formaPagamento || 'Não informado'}</span>
                                                ${r.cliente ? '<span>•</span><span>' + r.cliente + '</span>' : ''}
                                            </div>
                                        </div>
                                        <div style="font-size: 18px; font-weight: 700; color: var(--success, #06D6A0); white-space: nowrap; margin-left: 16px;">
                                            ${formatarMoeda(r.valor)}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="position: sticky; bottom: 0; background: var(--bg-card, #1a1f3a); padding: 20px 24px; border-top: 2px solid var(--border, #2d3561); display: flex; gap: 12px;">
                        <button onclick="window.location.href='financeiro-dashboard.php'" class="btn btn-primary" style="flex: 1; padding: 12px; border-radius: 8px; background: var(--primary, #FF6B35); color: white; border: none; cursor: pointer; font-weight: 600;">
                            Ir para Gestão Financeira
                        </button>
                        <button onclick="document.getElementById('modalReceitas').remove()" class="btn btn-secondary" style="flex: 1; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--border, #2d3561); cursor: pointer; font-weight: 600;">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);

    } catch (error) {
        console.error('Erro ao carregar receitas:', error);
        alert('Erro ao carregar receitas do mês.');
    }
}

// ============================================
// MODAL - VALORES A RECEBER
// ============================================
// Variável global para armazenar dados dos eventos pendentes
let dadosEventosPendentes = [];

async function mostrarValoresAReceber() {
    const hoje = new Date();
    const mesAtual = hoje.getMonth() + 1;
    const anoAtual = hoje.getFullYear();

    try {
        const resEventos = await fetch('../api/eventos.php?action=list');
        const dataEventos = await resEventos.json();

        if (!dataEventos.success) {
            alert('Erro ao carregar eventos.');
            return;
        }

        // Filtrar clientes com saldo devedor
        const clientesPendentes = [];
        dadosEventosPendentes = [];

        dataEventos.eventos.forEach(evento => {
            const dataEvento = new Date(evento.data_evento);
            const mesEvento = dataEvento.getMonth() + 1;
            const anoEvento = dataEvento.getFullYear();

            if (mesEvento !== mesAtual || anoEvento !== anoAtual) return;
            if (evento.status !== 'em_andamento' && evento.status !== 'concluido') return;

            // valor_total já inclui o desconto (serviços + taxas - desconto)
            const valorTotal = parseFloat(evento.valor_total || 0);
            const totalPago = parseFloat(evento.total_pago || evento.valor_sinal || 0);
            const saldo = valorTotal - totalPago;

            if (saldo > 0.01) {
                const vencimento = evento.data_vencimento ? new Date(evento.data_vencimento + 'T00:00:00') : new Date(evento.data_evento + 'T00:00:00');
                const atrasado = vencimento < hoje;

                clientesPendentes.push({
                    eventoId: evento.id,
                    clienteId: evento.cliente_id,
                    nome: evento.cliente_nome,
                    tipo: evento.tipo,
                    valorTotal: valorTotal,
                    valorPago: totalPago,
                    saldo: saldo,
                    vencimento: vencimento,
                    atrasado: atrasado,
                    dataEvento: evento.data_evento
                });

                // Guardar dados do evento para uso no modal de pagamento
                dadosEventosPendentes.push(evento);
            }
        });

        if (clientesPendentes.length === 0) {
            alert('Não há valores a receber!\n\nTodos os clientes estão em dia.');
            return;
        }

        // Ordenar: atrasados primeiro, depois por vencimento
        clientesPendentes.sort((a, b) => {
            if (a.atrasado && !b.atrasado) return -1;
            if (!a.atrasado && b.atrasado) return 1;
            return a.vencimento - b.vencimento;
        });

        const totalAReceber = clientesPendentes.reduce((sum, c) => sum + c.saldo, 0);

        let html = `
            <div id="modalValoresReceber" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;" onclick="this.remove()">
                <div style="background: var(--bg-card, #1a1f3a); border-radius: 12px; max-width: 650px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 24px;" onclick="event.stopPropagation()">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; font-size: 24px;">Valores a Receber</h2>
                        <button onclick="document.getElementById('modalValoresReceber').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary, #a0aec0);">×</button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                        <div style="padding: 12px; background: rgba(79, 70, 229, 0.1); border-radius: 8px; border-left: 4px solid #4F46E5;">
                            <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">Total a Receber</div>
                            <div style="font-size: 20px; font-weight: 700; color: #4F46E5;">${formatarMoeda(totalAReceber)}</div>
                        </div>
                        <div style="padding: 12px; background: rgba(239, 71, 111, 0.1); border-radius: 8px; border-left: 4px solid var(--danger, #EF476F);">
                            <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">Clientes Pendentes</div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--danger, #EF476F);">${clientesPendentes.length}</div>
                        </div>
                    </div>

                    ${clientesPendentes.map(c => `
                        <div onclick="abrirModalPagamento(${c.eventoId})" style="padding: 16px; border: 1px solid var(--border, #2d3561); border-radius: 8px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s; ${c.atrasado ? 'background: rgba(239, 71, 111, 0.1); border-color: var(--danger, #EF476F);' : ''}" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 16px;">
                                        ${c.nome}
                                        ${c.atrasado ? '<span style="background: var(--danger, #EF476F); color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;">ATRASADO</span>' : ''}
                                    </div>
                                    <div style="font-size: 13px; color: var(--text-secondary, #a0aec0); margin-top: 4px;">${c.tipo || 'Evento'}</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 20px; font-weight: 700; color: ${c.atrasado ? 'var(--danger, #EF476F)' : '#4F46E5'};">
                                        ${formatarMoeda(c.saldo)}
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--text-secondary, #a0aec0);">
                                <div>
                                    Vencimento: <strong style="color: ${c.atrasado ? 'var(--danger, #EF476F)' : 'var(--text-primary, #e2e8f0)'};">
                                        ${formatarData(c.vencimento.toISOString().split('T')[0])}
                                    </strong>
                                </div>
                                <div style="display: flex; gap: 12px; font-size: 11px;">
                                    <span>Total: ${formatarMoeda(c.valorTotal)}</span>
                                    <span>Pago: ${formatarMoeda(c.valorPago)}</span>
                                </div>
                            </div>
                            <div style="font-size: 11px; color: var(--primary, #FF6B35); font-weight: 600; margin-top: 8px; text-align: right;">
                                Clique para registrar pagamento
                            </div>
                        </div>
                    `).join('')}

                    <div style="margin-top: 20px; display: flex; gap: 12px;">
                        <button onclick="window.location.href='financeiro-dashboard.php'" style="flex: 1; padding: 12px; border-radius: 8px; background: var(--primary, #FF6B35); color: white; border: none; cursor: pointer; font-weight: 600;">
                            Ver Financeiro
                        </button>
                        <button onclick="document.getElementById('modalValoresReceber').remove()" style="flex: 1; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--border, #2d3561); cursor: pointer; font-weight: 600;">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);

    } catch (error) {
        console.error('Erro ao carregar valores a receber:', error);
        alert('Erro ao carregar valores a receber.');
    }
}

// ============================================
// MODAL - PAGAMENTO DO CLIENTE
// ============================================
async function abrirModalPagamento(eventoId) {
    // Fechar modal de valores a receber
    const modalVR = document.getElementById('modalValoresReceber');
    if (modalVR) modalVR.remove();

    // Buscar dados do evento
    const evento = dadosEventosPendentes.find(e => e.id == eventoId);
    if (!evento) {
        alert('Evento não encontrado.');
        return;
    }

    // valor_total já inclui o desconto (serviços + taxas - desconto)
    const valorTotal = parseFloat(evento.valor_total || 0);
    const totalPago = parseFloat(evento.total_pago || evento.valor_sinal || 0);
    const saldoRestante = valorTotal - totalPago;
    const hoje = new Date().toISOString().split('T')[0];

    // Buscar contas bancárias
    let contasOptions = '<option value="">Selecione (opcional)</option>';
    try {
        const resContas = await fetch('../api/contas-bancarias.php?action=list');
        const dataContas = await resContas.json();
        if (dataContas.success && dataContas.contas) {
            dataContas.contas.forEach(conta => {
                contasOptions += `<option value="${conta.id}">${conta.nome} - ${formatarMoeda(parseFloat(conta.saldo_atual || 0))}</option>`;
            });
        }
    } catch(e) {}

    let html = `
        <div id="modalPagamento" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center;" onclick="this.remove()">
            <div style="background: var(--bg-card, #1a1f3a); border-radius: 12px; max-width: 550px; width: 90%; max-height: 85vh; overflow-y: auto; padding: 24px;" onclick="event.stopPropagation()">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 22px;">Registrar Pagamento</h2>
                    <button onclick="document.getElementById('modalPagamento').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-secondary, #a0aec0);">×</button>
                </div>

                <!-- Dados do Cliente -->
                <div style="padding: 16px; background: rgba(79, 70, 229, 0.1); border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4F46E5;">
                    <div style="font-weight: 700; font-size: 18px; margin-bottom: 8px;">${evento.cliente_nome}</div>
                    <div style="font-size: 14px; color: var(--text-secondary, #a0aec0); margin-bottom: 4px;">${evento.tipo || 'Evento'} - ${formatarData(evento.data_evento)}</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-top: 12px;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Valor Total</div>
                            <div style="font-weight: 700; color: var(--text-primary, #e2e8f0);">${formatarMoeda(valorTotal)}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Total Pago</div>
                            <div style="font-weight: 700; color: var(--success, #06D6A0);">${formatarMoeda(totalPago)}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Saldo Restante</div>
                            <div style="font-weight: 700; color: var(--danger, #EF476F);">${formatarMoeda(saldoRestante)}</div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Pagamento -->
                <form onsubmit="registrarPagamento(event, ${eventoId})">
                    <div style="display: grid; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary, #a0aec0);">Valor do Pagamento *</label>
                            <input type="number" id="pagValor" step="0.01" max="${saldoRestante.toFixed(2)}" value="${saldoRestante.toFixed(2)}" required
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2d3561); border-radius: 8px; color: var(--text-primary, #e2e8f0); font-size: 16px; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary, #a0aec0);">Data do Pagamento *</label>
                            <input type="date" id="pagData" value="${hoje}" required
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2d3561); border-radius: 8px; color: var(--text-primary, #e2e8f0); font-size: 14px; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary, #a0aec0);">Forma de Pagamento *</label>
                            <select id="pagFormaPagamento" required
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2d3561); border-radius: 8px; color: var(--text-primary, #e2e8f0); font-size: 14px; box-sizing: border-box;">
                                <option value="">Selecione...</option>
                                <option value="pix">PIX</option>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                                <option value="transferencia">Transferência</option>
                                <option value="boleto">Boleto</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary, #a0aec0);">Conta Bancária</label>
                            <select id="pagContaBancaria"
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2d3561); border-radius: 8px; color: var(--text-primary, #e2e8f0); font-size: 14px; box-sizing: border-box;">
                                ${contasOptions}
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary, #a0aec0);">Descrição</label>
                            <input type="text" id="pagDescricao" value="Pagamento restante - ${evento.cliente_nome}"
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2d3561); border-radius: 8px; color: var(--text-primary, #e2e8f0); font-size: 14px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="margin-top: 24px; display: flex; gap: 12px;">
                        <button type="submit" id="btnRegistrarPag" style="flex: 1; padding: 14px; border-radius: 8px; background: var(--success, #06D6A0); color: #000; border: none; cursor: pointer; font-weight: 700; font-size: 15px;">
                            Registrar Pagamento
                        </button>
                        <button type="button" onclick="document.getElementById('modalPagamento').remove()" style="flex: 0.5; padding: 14px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--border, #2d3561); cursor: pointer; font-weight: 600;">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);
}

// ============================================
// REGISTRAR PAGAMENTO
// ============================================
async function registrarPagamento(e, eventoId) {
    e.preventDefault();

    const btn = document.getElementById('btnRegistrarPag');
    btn.disabled = true;
    btn.textContent = 'Registrando...';

    const valor = parseFloat(document.getElementById('pagValor').value);
    const data = document.getElementById('pagData').value;
    const formaPagamento = document.getElementById('pagFormaPagamento').value;
    const contaId = document.getElementById('pagContaBancaria').value;
    const descricao = document.getElementById('pagDescricao').value;

    if (!valor || valor <= 0) {
        alert('Informe um valor válido.');
        btn.disabled = false;
        btn.textContent = 'Registrar Pagamento';
        return;
    }

    if (!data || !formaPagamento) {
        alert('Preencha a data e a forma de pagamento.');
        btn.disabled = false;
        btn.textContent = 'Registrar Pagamento';
        return;
    }

    try {
        // 1. Registrar pagamento na tabela pagamentos_evento
        const formData = new FormData();
        formData.append('action', 'registrar_pagamento');
        formData.append('evento_id', eventoId);
        formData.append('valor', valor);
        formData.append('data_pagamento', data);
        formData.append('descricao', descricao);
        formData.append('forma_pagamento', formaPagamento);
        formData.append('conta_id', contaId);

        const res = await fetch('../api/eventos.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            alert('Pagamento registrado com sucesso!');
            document.getElementById('modalPagamento').remove();
            // Recarregar dashboard
            carregarDashboard();
        } else {
            alert('Erro ao registrar pagamento: ' + (result.message || 'Erro desconhecido'));
            btn.disabled = false;
            btn.textContent = 'Registrar Pagamento';
        }
    } catch (error) {
        console.error('Erro ao registrar pagamento:', error);
        alert('Erro de conexão ao registrar pagamento.');
        btn.disabled = false;
        btn.textContent = 'Registrar Pagamento';
    }
}

// ============================================
// MODAL - NOVA DESPESA (CUSTO GERAL)
// ============================================
async function abrirModalDespesa() {
    // Preencher data atual
    document.getElementById('despesaData').value = new Date().toISOString().split('T')[0];

    // Carregar contas bancárias
    const select = document.getElementById('despesaConta');
    select.innerHTML = '<option value="">Selecione (opcional)</option>';
    try {
        const res = await fetch('../api/contas-bancarias.php?action=list');
        const data = await res.json();
        if (data.success && data.contas) {
            data.contas.forEach(conta => {
                if (conta.ativo) {
                    select.innerHTML += `<option value="${conta.id}">${conta.nome} - ${formatarMoeda(parseFloat(conta.saldo_atual || 0))}</option>`;
                }
            });
        }
    } catch(e) {
        console.error('Erro ao carregar contas:', e);
    }

    document.getElementById('modalDespesa').style.display = 'flex';
}

function fecharModalDespesa() {
    document.getElementById('modalDespesa').style.display = 'none';
    document.getElementById('formDespesa').reset();
}

async function salvarDespesa(event) {
    event.preventDefault();

    const btn = document.getElementById('btnSalvarDespesa');
    btn.disabled = true;
    btn.textContent = 'Salvando...';

    const formData = new FormData();
    formData.append('action', 'custo_empresa_create');
    formData.append('descricao', document.getElementById('despesaDescricao').value);
    formData.append('categoria', document.getElementById('despesaCategoria').value);
    formData.append('valor', document.getElementById('despesaValor').value);
    formData.append('data_custo', document.getElementById('despesaData').value);

    const formaPag = document.getElementById('despesaFormaPag').value;
    if (formaPag) formData.append('forma_pagamento', formaPag);

    const contaId = document.getElementById('despesaConta').value;
    if (contaId) formData.append('conta_bancaria_id', contaId);

    const obs = document.getElementById('despesaObs').value;
    if (obs) formData.append('observacoes', obs);

    try {
        const res = await fetch('../api/custos.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            alert('Despesa cadastrada com sucesso!');
            fecharModalDespesa();
            carregarDashboard();
        } else {
            alert('Erro ao cadastrar despesa: ' + (result.message || 'Erro desconhecido'));
            btn.disabled = false;
            btn.textContent = 'Adicionar Despesa';
        }
    } catch (error) {
        console.error('Erro ao salvar despesa:', error);
        alert('Erro de conexão ao salvar despesa.');
        btn.disabled = false;
        btn.textContent = 'Adicionar Despesa';
    }
}

// Fechar modal ao clicar fora
document.getElementById('modalDespesa').addEventListener('click', function(e) {
    if (e.target === this) fecharModalDespesa();
});

// ============================================
// MODAL - DESPESAS DO MÊS (CUSTOS GERAIS)
// ============================================
async function mostrarDespesasDoMes() {
    const mesAtual = new Date().getMonth() + 1;
    const anoAtual = new Date().getFullYear();
    const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    const mesNome = meses[mesAtual - 1];

    try {
        const res = await fetch('../api/custos.php?action=listar');
        const text = await res.text();
        if (!text || text.trim() === '') { alert('Nenhuma despesa registrada.'); return; }

        const data = JSON.parse(text);
        if (!data.success || !data.custos) { alert('Nenhuma despesa registrada.'); return; }

        const despesasMes = data.custos.filter(c => {
            const d = new Date(c.data_custo);
            return d.getMonth() + 1 === mesAtual && d.getFullYear() === anoAtual;
        });

        if (despesasMes.length === 0) { alert('Nenhuma despesa registrada neste mês!'); return; }

        despesasMes.sort((a, b) => new Date(b.data_custo) - new Date(a.data_custo));
        const totalDespesas = despesasMes.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);

        // Agrupar por categoria
        const porCategoria = {};
        despesasMes.forEach(c => {
            const cat = c.categoria || 'outros';
            if (!porCategoria[cat]) porCategoria[cat] = { total: 0, count: 0 };
            porCategoria[cat].total += parseFloat(c.valor || 0);
            porCategoria[cat].count++;
        });

        const categoriaNomes = {
            'retirada_lucros':'Retirada de Lucros','prestacao':'Prestação','seguro':'Seguro',
            'manutencao':'Manutenção','marketing':'Marketing','software':'Software',
            'aluguel':'Aluguel','internet':'Internet/Telefone','combustivel':'Combustível',
            'alimentacao':'Alimentação','transporte':'Transporte','equipamentos':'Equipamentos',
            'impostos':'Impostos','salarios':'Salários','outros':'Outros'
        };

        let html = `
            <div id="modalDespesasMes" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;" onclick="this.remove()">
                <div style="background: var(--bg-card, #1a1f3a); border-radius: 12px; max-width: 800px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 0;" onclick="event.stopPropagation()">
                    <div style="position: sticky; top: 0; background: linear-gradient(135deg, #EF476F, #D63B5E); padding: 24px; border-radius: 12px 12px 0 0; z-index: 10;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h2 style="margin: 0; font-size: 24px; color: white;">Despesas de ${mesNome}/${anoAtual}</h2>
                            <button onclick="document.getElementById('modalDespesasMes').remove()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">×</button>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div style="padding: 16px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 4px;">Total do Mês</div>
                                <div style="font-size: 24px; font-weight: 700; color: white;">${formatarMoeda(totalDespesas)}</div>
                            </div>
                            <div style="padding: 16px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 4px;">Total de Lançamentos</div>
                                <div style="font-size: 24px; font-weight: 700; color: white;">${despesasMes.length}</div>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 24px; border-bottom: 2px solid var(--border, #2d3561);">
                        <h3 style="margin: 0 0 16px 0; font-size: 16px; color: var(--text-secondary, #a0aec0);">Por Categoria</h3>
                        <div style="display: grid; gap: 8px;">
                            ${Object.entries(porCategoria).sort((a,b) => b[1].total - a[1].total).map(([cat, info]) => {
                                const percent = ((info.total / totalDespesas) * 100).toFixed(1);
                                return `
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 6px;">
                                        <div>
                                            <div style="font-weight: 600;">${categoriaNomes[cat] || cat}</div>
                                            <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">${info.count} lançamento${info.count > 1 ? 's' : ''}</div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-weight: 700; color: #EF476F;">${formatarMoeda(info.total)}</div>
                                            <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">${percent}%</div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <div style="padding: 24px;">
                        <h3 style="margin: 0 0 16px 0; font-size: 16px; color: var(--text-secondary, #a0aec0);">Todas as Despesas</h3>
                        <div style="display: grid; gap: 12px;">
                            ${despesasMes.map(c => `
                                <div style="padding: 16px; border: 1px solid var(--border, #2d3561); border-radius: 8px; background: rgba(255,255,255,0.02); border-left: 3px solid #EF476F;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; font-size: 15px; margin-bottom: 4px;">${c.descricao}</div>
                                            <div style="font-size: 13px; color: var(--text-secondary, #a0aec0);">
                                                ${formatarData(c.data_custo)} • ${categoriaNomes[c.categoria] || c.categoria || 'N/A'}
                                                ${c.forma_pagamento ? ' • ' + c.forma_pagamento : ''}
                                            </div>
                                        </div>
                                        <div style="font-size: 18px; font-weight: 700; color: #EF476F; white-space: nowrap; margin-left: 16px;">
                                            ${formatarMoeda(c.valor)}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>

                    <div style="position: sticky; bottom: 0; background: var(--bg-card, #1a1f3a); padding: 20px 24px; border-top: 2px solid var(--border, #2d3561); display: flex; gap: 12px;">
                        <button onclick="window.location.href='custos-eventpro.php'" class="btn btn-primary" style="flex: 1; padding: 12px; border-radius: 8px; background: #EF476F; color: white; border: none; cursor: pointer; font-weight: 600;">
                            Ir para Gestão de Custos
                        </button>
                        <button onclick="document.getElementById('modalDespesasMes').remove()" style="flex: 1; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--border, #2d3561); cursor: pointer; font-weight: 600;">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
    } catch (error) {
        console.error('Erro ao carregar despesas:', error);
        alert('Erro ao carregar despesas do mês.');
    }
}

// ============================================
// MODAL - CUSTOS DE EVENTOS
// ============================================
async function mostrarCustosEventos() {
    const mesAtual = new Date().getMonth() + 1;
    const anoAtual = new Date().getFullYear();
    const meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    const mesNome = meses[mesAtual - 1];

    try {
        const [resCustos, resEventos] = await Promise.all([
            fetch(`../api/custos.php?action=custos_eventos_list&mes=${mesAtual}&ano=${anoAtual}`),
            fetch('../api/eventos.php?action=list')
        ]);

        const dataCustos = await resCustos.json();
        const dataEventos = await resEventos.json();

        if (!dataCustos.success || !dataCustos.custos || dataCustos.custos.length === 0) {
            alert('Nenhum custo de evento registrado neste mês!');
            return;
        }

        const custos = dataCustos.custos;
        const eventos = dataEventos.success ? dataEventos.eventos : [];
        const totalCustos = custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);

        // Agrupar por evento
        const porEvento = {};
        custos.forEach(c => {
            const evId = c.evento_id;
            if (!porEvento[evId]) {
                const evento = eventos.find(e => e.id == evId);
                porEvento[evId] = {
                    evento: evento,
                    clienteNome: c.cliente_nome || (evento ? evento.cliente_nome : 'Cliente'),
                    tipoEvento: c.tipo_evento || (evento ? evento.tipo : 'Evento'),
                    dataEvento: c.data_evento || (evento ? evento.data_evento : ''),
                    valorEvento: evento ? parseFloat(evento.valor_total || 0) : 0,
                    custos: [],
                    total: 0
                };
            }
            porEvento[evId].custos.push(c);
            porEvento[evId].total += parseFloat(c.valor || 0);
        });

        let html = `
            <div id="modalCustosEventos" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;" onclick="this.remove()">
                <div style="background: var(--bg-card, #1a1f3a); border-radius: 12px; max-width: 850px; width: 100%; max-height: 85vh; overflow-y: auto; padding: 0;" onclick="event.stopPropagation()">
                    <div style="position: sticky; top: 0; background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 24px; border-radius: 12px 12px 0 0; z-index: 10;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h2 style="margin: 0; font-size: 24px; color: white;">Custos de Eventos - ${mesNome}/${anoAtual}</h2>
                            <button onclick="document.getElementById('modalCustosEventos').remove()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">×</button>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                            <div style="padding: 16px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 4px;">Total em Custos</div>
                                <div style="font-size: 22px; font-weight: 700; color: white;">${formatarMoeda(totalCustos)}</div>
                            </div>
                            <div style="padding: 16px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 4px;">Lançamentos</div>
                                <div style="font-size: 22px; font-weight: 700; color: white;">${custos.length}</div>
                            </div>
                            <div style="padding: 16px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                                <div style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 4px;">Eventos</div>
                                <div style="font-size: 22px; font-weight: 700; color: white;">${Object.keys(porEvento).length}</div>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 24px;">
                        <h3 style="margin: 0 0 16px 0; font-size: 16px; color: var(--text-secondary, #a0aec0);">Custos por Evento</h3>
                        ${Object.values(porEvento).map(ev => {
                            const lucro = ev.valorEvento - ev.total;
                            const margem = ev.valorEvento > 0 ? ((lucro / ev.valorEvento) * 100).toFixed(1) : 0;
                            return `
                                <div style="border: 1px solid var(--border, #2d3561); border-radius: 12px; margin-bottom: 16px; overflow: hidden;">
                                    <div style="padding: 16px; background: rgba(255,107,53,0.1); border-bottom: 1px solid var(--border, #2d3561);">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                            <div>
                                                <div style="font-weight: 700; font-size: 16px;">${ev.tipoEvento}</div>
                                                <div style="font-size: 13px; color: var(--text-secondary, #a0aec0);">
                                                    👤 ${ev.clienteNome} • 📅 ${formatarData(ev.dataEvento)}
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 12px;">
                                            <div style="text-align: center;">
                                                <div style="font-size: 16px; font-weight: 700; color: var(--primary, #FF6B35);">${formatarMoeda(ev.valorEvento)}</div>
                                                <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Valor Evento</div>
                                            </div>
                                            <div style="text-align: center;">
                                                <div style="font-size: 16px; font-weight: 700; color: #EF476F;">${formatarMoeda(ev.total)}</div>
                                                <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Total Custos</div>
                                            </div>
                                            <div style="text-align: center;">
                                                <div style="font-size: 16px; font-weight: 700; color: ${lucro >= 0 ? 'var(--success, #06D6A0)' : '#EF476F'};">${formatarMoeda(lucro)}</div>
                                                <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Lucro</div>
                                            </div>
                                            <div style="text-align: center;">
                                                <div style="font-size: 16px; font-weight: 700; color: ${lucro >= 0 ? 'var(--success, #06D6A0)' : '#EF476F'};">${margem}%</div>
                                                <div style="font-size: 11px; color: var(--text-secondary, #a0aec0);">Margem</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="padding: 12px 16px;">
                                        ${ev.custos.map(c => `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                                <div>
                                                    <div style="font-weight: 600; font-size: 14px;">${c.descricao}</div>
                                                    <div style="font-size: 12px; color: var(--text-secondary, #a0aec0);">
                                                        ${formatarData(c.data_custo || c.data)} • ${c.categoria || 'N/A'}
                                                        ${c.forma_pagamento ? ' • ' + c.forma_pagamento : ''}
                                                    </div>
                                                </div>
                                                <div style="font-weight: 700; color: #EF476F; white-space: nowrap;">${formatarMoeda(c.valor)}</div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>

                    <div style="position: sticky; bottom: 0; background: var(--bg-card, #1a1f3a); padding: 20px 24px; border-top: 2px solid var(--border, #2d3561); display: flex; gap: 12px;">
                        <button onclick="window.location.href='custos-eventpro.php'" class="btn btn-primary" style="flex: 1; padding: 12px; border-radius: 8px; background: #FF6B35; color: white; border: none; cursor: pointer; font-weight: 600;">
                            Ir para Gestão de Custos
                        </button>
                        <button onclick="document.getElementById('modalCustosEventos').remove()" style="flex: 1; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid var(--border, #2d3561); cursor: pointer; font-weight: 600;">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
    } catch (error) {
        console.error('Erro ao carregar custos de eventos:', error);
        alert('Erro ao carregar custos de eventos.');
    }
}

// ============================================
// MODAL - NOVO CLIENTE (Ação Rápida)
// ============================================
let ultimoClienteIdDash = null;
let ultimoClienteNomeDash = '';

function abrirModalNovoCliente() {
    document.getElementById('formNovoCliente').reset();
    document.getElementById('dashIndicacaoDetalhes').style.display = 'none';
    // Limpar campos de endereço
    ['dashClienteCep','dashClienteRua','dashClienteNumero','dashClienteComplemento','dashClienteBairro','dashClienteCidade','dashClienteEstado'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    // Esconder mensagens CEP
    ['dashCepLoading','dashCepError','dashCepSuccess'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });
    document.getElementById('modalNovoCliente').style.display = 'flex';
}

function fecharModalNovoCliente() {
    document.getElementById('modalNovoCliente').style.display = 'none';
    document.getElementById('formNovoCliente').reset();
    document.getElementById('dashIndicacaoDetalhes').style.display = 'none';
}

function fecharModalSucessoCliente() {
    document.getElementById('modalSucessoCliente').style.display = 'none';
}

function toggleIndicacaoDash() {
    const origem = document.getElementById('dashClienteOrigem').value;
    document.getElementById('dashIndicacaoDetalhes').style.display = origem === 'indicacao' ? 'block' : 'none';
}

function formatarTelefoneDash(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 11) v = v.substring(0, 11);
    if (v.length <= 2) {
        // nada
    } else if (v.length <= 6) {
        v = '(' + v.substring(0, 2) + ') ' + v.substring(2);
    } else if (v.length <= 10) {
        v = '(' + v.substring(0, 2) + ') ' + v.substring(2, 6) + '-' + v.substring(6);
    } else {
        v = '(' + v.substring(0, 2) + ') ' + v.substring(2, 7) + '-' + v.substring(7);
    }
    input.value = v;
}

function formatarCpfDash(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length <= 11) {
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.maxLength = 14;
    } else {
        v = v.substring(0, 14);
        v = v.replace(/^(\d{2})(\d)/, '$1.$2');
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
        v = v.replace(/(\d{4})(\d)/, '$1-$2');
        input.maxLength = 18;
    }
    input.value = v;
}

function formatarCepDash(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 8) v = v.substring(0, 8);
    if (v.length > 5) v = v.substring(0, 5) + '-' + v.substring(5);
    input.value = v;
}

let cepTimeoutDash = null;
function buscarCepDash() {
    clearTimeout(cepTimeoutDash);
    const cep = document.getElementById('dashClienteCep').value.replace(/\D/g, '');
    if (cep.length !== 8) return;

    document.getElementById('dashCepLoading').style.display = 'block';
    document.getElementById('dashCepError').style.display = 'none';
    document.getElementById('dashCepSuccess').style.display = 'none';

    cepTimeoutDash = setTimeout(async () => {
        try {
            const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await resp.json();
            document.getElementById('dashCepLoading').style.display = 'none';

            if (data.erro) {
                document.getElementById('dashCepError').style.display = 'block';
                return;
            }

            document.getElementById('dashClienteRua').value = data.logradouro || '';
            document.getElementById('dashClienteBairro').value = data.bairro || '';
            document.getElementById('dashClienteCidade').value = data.localidade || '';
            document.getElementById('dashClienteEstado').value = data.uf || '';
            document.getElementById('dashCepSuccess').style.display = 'block';

            // Focar no campo número
            document.getElementById('dashClienteNumero').focus();
        } catch (err) {
            document.getElementById('dashCepLoading').style.display = 'none';
            document.getElementById('dashCepError').style.display = 'block';
        }
    }, 500);
}

async function salvarNovoCliente(e) {
    e.preventDefault();

    const btn = document.getElementById('btnSalvarCliente');
    btn.disabled = true;
    btn.textContent = 'Salvando...';

    const nomeCliente = document.getElementById('dashClienteNome').value;

    try {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('nome', nomeCliente);
        formData.append('telefone', document.getElementById('dashClienteTelefone').value);
        formData.append('email', document.getElementById('dashClienteEmail').value);
        formData.append('cpf', document.getElementById('dashClienteCpf').value);
        formData.append('origem', document.getElementById('dashClienteOrigem').value);
        formData.append('aniversario', document.getElementById('dashClienteAniversario').value);
        formData.append('observacoes', document.getElementById('dashClienteObs').value);
        // Endereço completo
        formData.append('cep', document.getElementById('dashClienteCep').value);
        formData.append('rua', document.getElementById('dashClienteRua').value);
        formData.append('numero', document.getElementById('dashClienteNumero').value);
        formData.append('bairro', document.getElementById('dashClienteBairro').value);
        formData.append('complemento', document.getElementById('dashClienteComplemento').value);
        formData.append('cidade', document.getElementById('dashClienteCidade').value);
        formData.append('estado', document.getElementById('dashClienteEstado').value);

        const response = await fetch('../api/clientes.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            ultimoClienteIdDash = data.id;
            ultimoClienteNomeDash = nomeCliente;

            // Fechar modal de cadastro
            fecharModalNovoCliente();

            // Abrir modal de sucesso com opções
            setTimeout(() => {
                document.getElementById('sucessoClienteNome').innerHTML =
                    '<strong style="color: var(--primary);">' + nomeCliente + '</strong> foi adicionado à sua base de clientes!';
                document.getElementById('modalSucessoCliente').style.display = 'flex';
            }, 300);

            carregarDashboard();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar cliente');
    }

    btn.disabled = false;
    btn.textContent = 'Cadastrar Cliente';
}

// Atalho: Criar Orçamento após cadastro do cliente
function atalhoOrcamentoDash() {
    fecharModalSucessoCliente();
    setTimeout(async () => {
        await abrirModalNovoOrcamento();
        // Pré-selecionar o cliente recém cadastrado
        if (ultimoClienteIdDash) {
            const select = document.getElementById('dashOrcCliente');
            if (select) select.value = ultimoClienteIdDash;
        }
    }, 300);
}

// Atalho: Criar Evento direto após cadastro do cliente
function atalhoEventoDash() {
    fecharModalSucessoCliente();
    setTimeout(async () => {
        await abrirModalNovoOrcamento();
        // Pré-selecionar o cliente recém cadastrado
        if (ultimoClienteIdDash) {
            const select = document.getElementById('dashOrcCliente');
            if (select) select.value = ultimoClienteIdDash;
        }
    }, 300);
}

// Atalho: Ir para página de clientes com financeiro
function atalhoFinanceiroDash() {
    fecharModalSucessoCliente();
    if (ultimoClienteIdDash) {
        window.location.href = `clientes-eventpro.php?cliente_id=${ultimoClienteIdDash}&financeiro=1`;
    }
}

document.getElementById('modalNovoCliente').addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovoCliente();
});
document.getElementById('modalSucessoCliente').addEventListener('click', function(e) {
    if (e.target === this) fecharModalSucessoCliente();
});

// ============================================
// MODAL - NOVO ORCAMENTO/EVENTO COMPLETO
// ============================================
let orcServicosData = [];
let orcServicosSelecionados = [];
let orcOutrasTaxas = [];

async function abrirModalNovoOrcamento() {
    document.getElementById('formNovoOrcamento').reset();
    orcServicosSelecionados = [];
    orcOutrasTaxas = [];

    // Definir data de criação e validade
    const hoje = new Date();
    const validade = new Date(hoje);
    validade.setDate(validade.getDate() + 30);
    document.getElementById('dashOrcDataCriacao').value = hoje.toISOString().split('T')[0];
    document.getElementById('dashOrcValidade').value = validade.toISOString().split('T')[0];
    document.getElementById('dashOrcDuracao').value = '';

    // Resetar resumo financeiro
    document.getElementById('orcSubtotalServicos').textContent = 'R$ 0,00';
    document.getElementById('orcTotalTaxas').textContent = 'R$ 0,00';
    document.getElementById('orcTotalDesconto').textContent = '- R$ 0,00';
    document.getElementById('orcValorTotalDisplay').textContent = 'R$ 0,00';
    document.getElementById('dashOrcValorTotal').value = '0';

    // Resetar taxas e serviços visuais
    document.getElementById('outrasTaxasListDash').innerHTML = '';
    document.getElementById('taxaDeslocamentoBox').style.display = 'block';
    document.getElementById('taxaDeslocamentoValor').value = '';
    document.getElementById('orcServicosSelecionadosBox').style.display = 'none';
    document.getElementById('orcServicosSelecionadosList').innerHTML = '';

    // Carregar lista de clientes
    const select = document.getElementById('dashOrcCliente');
    select.innerHTML = '<option value="">Selecione o cliente...</option>';
    try {
        const res = await fetch('../api/clientes.php?action=list');
        const data = await res.json();
        if (data.success && data.clientes) {
            data.clientes.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
            });
        }
    } catch(e) { console.error('Erro ao carregar clientes:', e); }

    // Carregar serviços
    await carregarServicosOrc();

    document.getElementById('modalNovoOrcamento').style.display = 'flex';
}

async function carregarServicosOrc() {
    const container = document.getElementById('orcServicosCheckboxes');
    try {
        const res = await fetch('../api/servicos.php?action=list');
        const data = await res.json();
        if (data.success && data.servicos && data.servicos.length > 0) {
            orcServicosData = data.servicos;
            container.innerHTML = data.servicos.map(s => {
                const valor = parseFloat(s.valor_base || s.valor_padrao || 0);
                return `
                <label style="display: flex; align-items: center; gap: 12px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                    <input type="checkbox" data-id="${s.id}" data-nome="${s.nome}" data-valor="${valor}" onchange="toggleServicoOrc(this)" style="width: 18px; height: 18px; cursor: pointer;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 14px;">${s.nome}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">${s.categoria || 'Sem categoria'}</div>
                    </div>
                    <div style="font-weight: 700; color: #06D6A0; font-size: 15px;">R$ ${valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</div>
                </label>`;
            }).join('');
        } else {
            container.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 20px; font-size: 13px;">Nenhum serviço cadastrado. <a href="servicos-eventpro.php" style="color: #FF6B35;">Cadastre serviços primeiro</a></p>';
        }
    } catch(e) {
        container.innerHTML = '<p style="text-align: center; color: #EF476F; padding: 20px;">Erro ao carregar serviços</p>';
    }
}

function toggleServicoOrc(checkbox) {
    const id = checkbox.dataset.id;
    const nome = checkbox.dataset.nome;
    const valor = parseFloat(checkbox.dataset.valor) || 0;

    if (checkbox.checked) {
        orcServicosSelecionados.push({ id, nome, valor });
    } else {
        orcServicosSelecionados = orcServicosSelecionados.filter(s => s.id !== id);
    }
    atualizarListaServicosOrc();
    calcularTotalOrc();
}

function atualizarListaServicosOrc() {
    const box = document.getElementById('orcServicosSelecionadosBox');
    const list = document.getElementById('orcServicosSelecionadosList');

    if (orcServicosSelecionados.length === 0) {
        box.style.display = 'none';
        return;
    }
    box.style.display = 'block';
    list.innerHTML = orcServicosSelecionados.map((s, i) => `
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; border: 1px solid var(--border); border-radius: 4px; margin-bottom: 6px;">
            <div><strong>${s.nome}</strong> <span style="font-size: 12px; color: var(--text-secondary);">R$ ${s.valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
            <button type="button" onclick="removerServicoOrc(${i})" style="background: #EF476F; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;">🗑️</button>
        </div>
    `).join('');
}

function removerServicoOrc(index) {
    const removido = orcServicosSelecionados[index];
    orcServicosSelecionados.splice(index, 1);
    // Desmarcar checkbox
    if (removido) {
        const checkboxes = document.querySelectorAll('#orcServicosCheckboxes input[type="checkbox"]');
        checkboxes.forEach(cb => { if (cb.dataset.id === removido.id) cb.checked = false; });
    }
    atualizarListaServicosOrc();
    calcularTotalOrc();
}

// Taxas
function adicionarTaxaDash() {
    const desc = prompt('Descrição da taxa:');
    if (!desc) return;
    const val = parseFloat(prompt('Valor (R$):'));
    if (isNaN(val) || val <= 0) return alert('Valor inválido!');
    orcOutrasTaxas.push({ descricao: desc, valor: val });
    renderTaxasDash();
    calcularTotalOrc();
}

function renderTaxasDash() {
    const container = document.getElementById('outrasTaxasListDash');
    container.innerHTML = orcOutrasTaxas.map((t, i) => `
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; border: 1px solid var(--border); border-radius: 4px; margin-bottom: 8px;">
            <div><strong>${t.descricao}</strong> <span style="font-size: 12px; color: var(--text-secondary);">R$ ${t.valor.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></div>
            <button type="button" onclick="removerTaxaDash(${i})" style="background: #EF476F; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">🗑️</button>
        </div>
    `).join('');
}

function removerTaxaDash(index) {
    orcOutrasTaxas.splice(index, 1);
    renderTaxasDash();
    calcularTotalOrc();
}

// Cálculos
function calcularTotalOrc() {
    const totalServicos = orcServicosSelecionados.reduce((s, x) => s + x.valor, 0);
    const taxaDeslocamento = parseFloat(document.getElementById('taxaDeslocamentoValor').value) || 0;
    const totalOutrasTaxas = orcOutrasTaxas.reduce((s, t) => s + t.valor, 0);
    const totalTaxas = taxaDeslocamento + totalOutrasTaxas;

    const descontoInput = parseFloat(document.getElementById('dashOrcDesconto').value) || 0;
    const descontoTipo = document.getElementById('dashOrcDescontoTipo').value;
    let desconto = 0;
    if (descontoTipo === 'percentual') {
        desconto = ((totalServicos + totalTaxas) * descontoInput) / 100;
    } else {
        desconto = descontoInput;
    }

    const total = totalServicos + totalTaxas - desconto;

    document.getElementById('orcSubtotalServicos').textContent = formatarDinheiro(totalServicos);
    document.getElementById('orcTotalTaxas').textContent = formatarDinheiro(totalTaxas);
    document.getElementById('orcTotalDesconto').textContent = desconto > 0 ? '- ' + formatarDinheiro(desconto) : 'R$ 0,00';
    document.getElementById('orcValorTotalDisplay').textContent = formatarDinheiro(total);
    document.getElementById('dashOrcValorTotal').value = total.toFixed(2);
}

function formatarDinheiro(v) { return 'R$ ' + (v || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

// Duração
function calcularDuracaoOrc() {
    const inicio = document.getElementById('dashOrcHoraInicio').value;
    const fim = document.getElementById('dashOrcHoraFim').value;
    if (!inicio || !fim) return;
    const [hI, mI] = inicio.split(':').map(Number);
    const [hF, mF] = fim.split(':').map(Number);
    let minI = hI * 60 + mI, minF = hF * 60 + mF;
    if (minF < minI) minF += 1440;
    const diff = minF - minI;
    const h = Math.floor(diff / 60), m = diff % 60;
    let duracao = '';
    if (h > 0) duracao += h + 'h';
    if (m > 0) duracao += (h > 0 ? ' ' : '') + m + 'min';
    document.getElementById('dashOrcDuracao').value = duracao || '0min';
}

// CEP
function formatarCepOrc(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 5) v = v.substring(0,5) + '-' + v.substring(5,8);
    input.value = v;
}

async function buscarCepOrc() {
    const cep = document.getElementById('dashOrcCep').value.replace(/\D/g, '');
    if (cep.length !== 8) return;
    const loading = document.getElementById('cepOrcLoading');
    loading.style.display = 'block';
    try {
        const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await res.json();
        if (!data.erro) {
            document.getElementById('dashOrcRua').value = data.logradouro || '';
            document.getElementById('dashOrcBairro').value = data.bairro || '';
            document.getElementById('dashOrcCidade').value = data.localidade || '';
            document.getElementById('dashOrcEstado').value = data.uf || '';
        }
    } catch(e) { console.error('Erro CEP:', e); }
    loading.style.display = 'none';
}

function fecharModalNovoOrcamento() {
    document.getElementById('modalNovoOrcamento').style.display = 'none';
    document.getElementById('formNovoOrcamento').reset();
    orcServicosSelecionados = [];
    orcOutrasTaxas = [];
}

async function salvarNovoOrcamento(e) {
    e.preventDefault();

    const btn = document.getElementById('btnSalvarOrcamento');
    btn.disabled = true;
    btn.textContent = 'Criando...';

    try {
        // Montar endereço
        const rua = document.getElementById('dashOrcRua').value;
        const numero = document.getElementById('dashOrcNumero').value;
        const complemento = document.getElementById('dashOrcComplemento').value;
        const bairro = document.getElementById('dashOrcBairro').value;
        const cidade = document.getElementById('dashOrcCidade').value;
        const estado = document.getElementById('dashOrcEstado').value;
        let endereco = '';
        if (rua) {
            endereco = rua;
            if (numero) endereco += ', ' + numero;
            if (complemento) endereco += ' - ' + complemento;
            if (bairro) endereco += '\n' + bairro;
            if (cidade && estado) endereco += ' - ' + cidade + '/' + estado;
        } else if (cidade) {
            endereco = cidade + (estado ? '/' + estado : '');
        }

        // Valor total calculado
        const valorTotal = parseFloat(document.getElementById('dashOrcValorTotal').value) || 0;

        // Montar observações completas
        let obsCompleta = '';
        const dataCriacao = document.getElementById('dashOrcDataCriacao').value;
        if (dataCriacao) obsCompleta += 'Orçamento criado em ' + new Date(dataCriacao + 'T00:00:00').toLocaleDateString('pt-BR') + '\n\n';
        if (orcServicosSelecionados.length > 0) {
            obsCompleta += 'Serviços:\n' + orcServicosSelecionados.map(s => '• ' + s.nome + ' - ' + formatarDinheiro(s.valor)).join('\n') + '\n\n';
        }
        const taxaDesl = parseFloat(document.getElementById('taxaDeslocamentoValor').value) || 0;
        if (taxaDesl > 0 || orcOutrasTaxas.length > 0) {
            obsCompleta += 'Taxas:\n';
            if (taxaDesl > 0) obsCompleta += '• Deslocamento - ' + formatarDinheiro(taxaDesl) + '\n';
            orcOutrasTaxas.forEach(t => { obsCompleta += '• ' + t.descricao + ' - ' + formatarDinheiro(t.valor) + '\n'; });
            obsCompleta += '\n';
        }
        const obsUser = document.getElementById('dashOrcObs').value;
        if (obsUser) obsCompleta += 'Observações:\n' + obsUser;

        const formData = new FormData();
        formData.append('action', 'create_orcamento');
        formData.append('cliente_id', document.getElementById('dashOrcCliente').value);
        formData.append('tipo_evento', document.getElementById('dashOrcTipo').value);
        formData.append('data_evento', document.getElementById('dashOrcData').value);
        formData.append('hora_inicio', document.getElementById('dashOrcHoraInicio').value);
        formData.append('hora_fim', document.getElementById('dashOrcHoraFim').value);
        formData.append('cidade', cidade);
        formData.append('local', endereco);
        formData.append('cep', document.getElementById('dashOrcCep').value);
        formData.append('rua', rua);
        formData.append('numero', numero);
        formData.append('bairro', bairro);
        formData.append('complemento', complemento);
        formData.append('estado', estado);
        formData.append('valor_total', valorTotal);
        formData.append('observacoes', obsCompleta);
        formData.append('status', document.getElementById('dashOrcStatus').value);

        // Enviar serviços e taxas como JSON
        formData.append('servicos_json', JSON.stringify(orcServicosSelecionados));
        formData.append('taxas_json', JSON.stringify([
            ...(taxaDesl > 0 ? [{descricao: 'Deslocamento', valor: taxaDesl}] : []),
            ...orcOutrasTaxas
        ]));

        const response = await fetch('../api/eventos.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            alert('✅ Orçamento gerado e evento criado com sucesso!');
            fecharModalNovoOrcamento();
            carregarDashboard();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao criar evento');
    }

    btn.disabled = false;
    btn.textContent = '💼 Gerar Orçamento e Criar Evento';
}

document.getElementById('modalNovoOrcamento').addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovoOrcamento();
});

// ============================================
// MODAL - NOVO SERVICO (Acao Rapida)
// ============================================
function abrirModalNovoServico() {
    document.getElementById('formNovoServico').reset();
    document.getElementById('modalNovoServico').style.display = 'flex';
}

function fecharModalNovoServico() {
    document.getElementById('modalNovoServico').style.display = 'none';
    document.getElementById('formNovoServico').reset();
}

async function salvarNovoServico(e) {
    e.preventDefault();

    const btn = document.getElementById('btnSalvarServico');
    btn.disabled = true;
    btn.textContent = 'Salvando...';

    try {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('nome', document.getElementById('dashServicoNome').value);
        formData.append('categoria', document.getElementById('dashServicoCategoria').value);
        formData.append('valor_base', document.getElementById('dashServicoValor').value);
        formData.append('descricao', document.getElementById('dashServicoDescricao').value);

        const response = await fetch('../api/servicos.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Servico cadastrado com sucesso!');
            fecharModalNovoServico();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar servico');
    }

    btn.disabled = false;
    btn.textContent = 'Cadastrar Servico';
}

document.getElementById('modalNovoServico').addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovoServico();
});

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', carregarDashboard);
</script>

<style>
.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-title {
    font-family: 'Syne', sans-serif;
    font-size: 36px;
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}

.dashboard-subtitle {
    color: var(--text-secondary);
    font-size: 16px;
}

.card-header {
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    font-family: 'Syne', sans-serif;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--text-primary);
    text-align: left;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--primary);
    transform: translateX(4px);
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.action-text h4 {
    margin: 0 0 2px 0;
    font-size: 14px;
    font-weight: 600;
}

.action-text p {
    margin: 0;
    font-size: 12px;
    color: var(--text-secondary);
}

.stat-change {
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
}

.stat-change.positive {
    color: var(--success);
}

.stat-change.negative {
    color: var(--danger);
}

.stat-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

@media (max-width: 1400px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

@media (max-width: 1200px) {
    .content-grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
</body>
</html>
