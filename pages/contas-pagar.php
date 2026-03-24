<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
$userId = requireAuthPage();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contas a Pagar - EventProDJ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #FF6B35;
            --success: #06D6A0;
            --danger: #EF476F;
            --warning: #FFD23F;
            --info: #004E89;
            --bg-dark: #0A0E27;
            --bg-card: #141B3D;
            --text-primary: #FFFFFF;
            --text-secondary: #A0AEC0;
            --border: #2D3561;
        }
        
        body {
            background: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Syne', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .page-header {
            background: linear-gradient(135deg, #FFD23F 0%, #E8BC38 100%);
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            color: #0A0E27;
        }
        
        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        /* Alertas */
        .alertas-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .alerta-card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid var(--border);
            border-left: 4px solid;
            animation: pulse 2s infinite;
        }
        
        .alerta-card.vencida {
            border-left-color: var(--danger);
            background: rgba(239, 68, 111, 0.1);
        }
        
        .alerta-card.proxima {
            border-left-color: var(--warning);
            background: rgba(255, 210, 63, 0.1);
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .alerta-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .alerta-icon {
            font-size: 32px;
        }
        
        .alerta-title {
            font-size: 18px;
            font-weight: 700;
        }
        
        .alerta-valor {
            font-size: 24px;
            font-weight: 700;
            margin-top: 8px;
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--warning);
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--warning);
        }
        
        /* Toolbar */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: 2px solid var(--border);
            background: transparent;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
        }
        
        .filter-btn.active {
            background: var(--warning);
            border-color: var(--warning);
            color: #0A0E27;
        }
        
        .filter-select {
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            min-width: 150px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #E85A2B);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* Lista */
        .contas-list {
            background: var(--bg-card);
            border-radius: 15px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        
        .conta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--border);
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }
        
        .conta-item:last-child {
            border-bottom: none;
        }
        
        .conta-item:hover {
            background: rgba(255,255,255,0.03);
        }
        
        .conta-item.vencida {
            border-left-color: var(--danger);
            background: rgba(239, 68, 111, 0.05);
        }
        
        .conta-item.proxima {
            border-left-color: var(--warning);
            background: rgba(255, 210, 63, 0.05);
        }
        
        .conta-item.em_dia {
            border-left-color: var(--success);
        }
        
        .conta-item.paga {
            opacity: 0.6;
            border-left-color: var(--border);
        }
        
        .conta-main {
            flex: 1;
        }
        
        .conta-descricao {
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .badge.paga {
            background: rgba(6, 214, 160, 0.2);
            color: var(--success);
        }
        
        .badge.vencida {
            background: rgba(239, 68, 111, 0.2);
            color: var(--danger);
        }
        
        .badge.proxima {
            background: rgba(255, 210, 63, 0.2);
            color: var(--warning);
        }
        
        .badge.em_dia {
            background: rgba(6, 214, 160, 0.2);
            color: var(--success);
        }
        
        .badge.fixo {
            background: rgba(239, 68, 111, 0.2);
            color: var(--danger);
        }
        
        .badge.variavel {
            background: rgba(255, 210, 63, 0.2);
            color: var(--warning);
        }
        
        .conta-detalhes {
            font-size: 13px;
            color: var(--text-secondary);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .conta-valor {
            font-size: 24px;
            font-weight: 700;
            color: var(--warning);
            min-width: 150px;
            text-align: right;
        }
        
        .conta-acoes {
            display: flex;
            gap: 10px;
        }
        
        .btn-icon {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-secondary);
        }
        
        .btn-icon:hover {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }
        
        .btn-icon.delete:hover {
            background: var(--danger);
            border-color: var(--danger);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--bg-card);
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            border: 1px solid var(--border);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--warning), #E8BC38);
            padding: 25px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #0A0E27;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .modal-close {
            background: rgba(0,0,0,0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            color: #0A0E27;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 15px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
        }
        
        .spinner {
            border: 3px solid rgba(255,255,255,0.1);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 64px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 3000;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
            .form-row { grid-template-columns: 1fr !important; }
            .filters-row { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
            .filter-select { min-width: unset !important; width: 100% !important; }
            .section-header { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .section { padding: 20px 16px !important; }
            .list-item { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; padding: 16px !important; }
            .actions { flex-wrap: wrap !important; width: 100% !important; }
            .btn-action { font-size: 11px !important; padding: 6px 10px !important; }
            .tabs-header { overflow-x: auto !important; -webkit-overflow-scrolling: touch; gap: 4px !important; }
            .tab-button { padding: 10px 14px !important; font-size: 12px !important; white-space: nowrap !important; }
            .modal-content { max-width: 100% !important; margin: 10px !important; }
            .modal-footer { flex-direction: column !important; }
            .modal-footer .btn-cancel, .modal-footer .btn-submit { width: 100% !important; text-align: center !important; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .filters-row { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content" style="margin-left: 280px; padding: 30px;">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">📅 Contas a Pagar</h1>
            <p style="opacity: 0.9;">Gestão de contas com alertas de vencimento</p>
        </div>
        
        <!-- Alertas -->
        <div class="alertas-container" id="alertasContainer" style="display: none;">
            <!-- Preenchido via JS -->
        </div>
        
        <!-- Stats -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-label">Total a Pagar</div>
                <div class="stat-value" id="totalPagar">R$ 0,00</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Contas Vencidas</div>
                <div class="stat-value" id="contasVencidas" style="color: var(--danger);">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Contas Próximas</div>
                <div class="stat-value" id="contasProximas">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pagas no Mês</div>
                <div class="stat-value" id="pagasMes" style="color: var(--success);">R$ 0,00</div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="filters">
                <button class="filter-btn active" data-status="todas" onclick="filtrarPor('todas')">
                    Todas
                </button>
                <button class="filter-btn" data-status="pendentes" onclick="filtrarPor('pendentes')">
                    Pendentes
                </button>
                <button class="filter-btn" data-status="vencidas" onclick="filtrarPor('vencidas')">
                    Vencidas
                </button>
                <button class="filter-btn" data-status="proximas" onclick="filtrarPor('proximas')">
                    Próximas
                </button>
                <button class="filter-btn" data-status="pagas" onclick="filtrarPor('pagas')">
                    Pagas
                </button>
                
                <select class="filter-select" id="filtroMes" onchange="aplicarFiltros()">
                    <option value="">Todos os meses</option>
                    <option value="1">Janeiro</option>
                    <option value="2">Fevereiro</option>
                    <option value="3">Março</option>
                    <option value="4">Abril</option>
                    <option value="5">Maio</option>
                    <option value="6">Junho</option>
                    <option value="7">Julho</option>
                    <option value="8">Agosto</option>
                    <option value="9">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                </select>
                
                <select class="filter-select" id="filtroAno" onchange="aplicarFiltros()">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                </select>
            </div>
            
            <button class="btn btn-primary" onclick="abrirModal()">
                <i class="fas fa-plus"></i> Nova Conta
            </button>
        </div>
        
        <!-- Lista -->
        <div class="contas-list" id="contasList">
            <div class="loading">
                <div class="spinner"></div>
                <p>Carregando contas...</p>
            </div>
        </div>
    </div>
    
    <!-- Modal Criar/Editar -->
    <div class="modal" id="modalConta">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">📅 Nova Conta a Pagar</h2>
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            <div class="modal-body">
                <form id="formConta" onsubmit="salvarConta(event)">
                    <input type="hidden" id="contaId">
                    
                    <div class="form-group">
                        <label>Descrição *</label>
                        <input type="text" name="descricao" id="descricao" required placeholder="Ex: Aluguel, Conta de Luz, etc">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Fornecedor</label>
                            <input type="text" name="fornecedor" id="fornecedor" placeholder="Nome do fornecedor">
                        </div>
                        
                        <div class="form-group">
                            <label>Categoria</label>
                            <input type="text" name="categoria" id="categoria" placeholder="Ex: Aluguel, Serviços">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Valor (R$) *</label>
                            <input type="number" step="0.01" name="valor" id="valor" required placeholder="0,00">
                        </div>
                        
                        <div class="form-group">
                            <label>Vencimento *</label>
                            <input type="date" name="data_vencimento" id="dataVencimento" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tipo *</label>
                            <select name="tipo" id="tipo" required>
                                <option value="variavel">Variável</option>
                                <option value="fixo">Fixo</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Recorrência</label>
                            <select name="recorrencia" id="recorrencia">
                                <option value="unico">Único</option>
                                <option value="mensal">Mensal</option>
                                <option value="bimestral">Bimestral</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Observações</label>
                        <textarea name="observacoes" id="observacoes" rows="3" placeholder="Detalhes adicionais..."></textarea>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            💾 Salvar Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Pagar -->
    <div class="modal" id="modalPagar">
        <div class="modal-content">
            <div class="modal-header">
                <h2>💳 Registrar Pagamento</h2>
                <button class="modal-close" onclick="fecharModalPagar()">×</button>
            </div>
            <div class="modal-body">
                <form id="formPagar" onsubmit="pagarConta(event)">
                    <input type="hidden" id="pagarContaId">
                    
                    <div class="form-group">
                        <label>Conta Bancária *</label>
                        <select name="conta_id" id="contaBancaria" required>
                            <option value="">Selecione a conta...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Data do Pagamento *</label>
                        <input type="date" name="data_pagamento" id="dataPagamento" required>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            ✅ Confirmar Pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        let contas = [];
        let contasBancarias = [];
        let statusAtual = 'todas';
        let contaEditando = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            const hoje = new Date();
            document.getElementById('filtroMes').value = hoje.getMonth() + 1;
            document.getElementById('dataPagamento').value = hoje.toISOString().split('T')[0];
            
            carregarContas Bancarias();
            carregarAlertas();
            carregarResumo();
            carregarContas();
        });
        
        async function carregarContasBancarias() {
            try {
                const response = await fetch('../api/financeiro.php?action=contas_list');
                const data = await response.json();
                
                if (data.success) {
                    contasBancarias = data.contas;
                    const select = document.getElementById('contaBancaria');
                    contasBancarias.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function carregarAlertas() {
            try {
                const response = await fetch('../api/contas-pagar.php?action=alertas');
                const data = await response.json();
                
                if (data.success) {
                    const alertas = data.alertas;
                    const container = document.getElementById('alertasContainer');
                    
                    if (alertas.total_vencidas > 0 || alertas.total_proximas > 0) {
                        container.style.display = 'grid';
                        container.innerHTML = '';
                        
                        if (alertas.total_vencidas > 0) {
                            container.innerHTML += `
                                <div class="alerta-card vencida">
                                    <div class="alerta-header">
                                        <div class="alerta-icon">🔴</div>
                                        <div>
                                            <div class="alerta-title">Contas Vencidas</div>
                                            <div style="color: var(--text-secondary); font-size: 13px;">
                                                ${alertas.total_vencidas} conta(s) vencida(s)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alerta-valor" style="color: var(--danger);">
                                        R$ ${parseFloat(alertas.valor_vencidas).toFixed(2)}
                                    </div>
                                </div>
                            `;
                        }
                        
                        if (alertas.total_proximas > 0) {
                            container.innerHTML += `
                                <div class="alerta-card proxima">
                                    <div class="alerta-header">
                                        <div class="alerta-icon">⚠️</div>
                                        <div>
                                            <div class="alerta-title">Vencimento Próximo</div>
                                            <div style="color: var(--text-secondary); font-size: 13px;">
                                                ${alertas.total_proximas} conta(s) nos próximos 7 dias
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alerta-valor" style="color: var(--warning);">
                                        R$ ${parseFloat(alertas.valor_proximas).toFixed(2)}
                                    </div>
                                </div>
                            `;
                        }
                    }
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function carregarResumo() {
            try {
                const mes = document.getElementById('filtroMes').value;
                const ano = document.getElementById('filtroAno').value;
                
                const response = await fetch(`../api/contas-pagar.php?action=resumo&mes=${mes}&ano=${ano}`);
                const data = await response.json();
                
                if (data.success) {
                    const r = data.resumo;
                    document.getElementById('totalPagar').textContent = 'R$ ' + parseFloat(r.valor_pendente).toFixed(2);
                    document.getElementById('contasVencidas').textContent = r.contas_vencidas;
                    document.getElementById('pagasMes').textContent = 'R$ ' + parseFloat(r.valor_pago).toFixed(2);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function carregarContas() {
            const mes = document.getElementById('filtroMes').value;
            const ano = document.getElementById('filtroAno').value;
            
            const params = new URLSearchParams({
                action: 'list',
                status: statusAtual,
                mes: mes || '',
                ano: ano
            });
            
            try {
                const response = await fetch(`../api/contas-pagar.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    contas = data.contas;
                    
                    // Atualizar contador de próximas
                    const proximas = contas.filter(c => c.status_calculado === 'proxima' && !c.paga).length;
                    document.getElementById('contasProximas').textContent = proximas;
                    
                    renderizarContas();
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function renderizarContas() {
            const container = document.getElementById('contasList');
            
            if (contas.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>Nenhuma conta encontrada</h3>
                        <p>Cadastre uma conta a pagar para começar</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = contas.map(c => {
                const statusTexto = c.paga ? 'Paga' : c.status_calculado === 'vencida' ? 'Vencida' : c.status_calculado === 'proxima' ? `Vence em ${c.dias_restantes} dia(s)` : 'Em dia';
                const statusClass = c.paga ? 'paga' : c.status_calculado;
                
                return `
                    <div class="conta-item ${statusClass}">
                        <div class="conta-main">
                            <div class="conta-descricao">
                                ${c.descricao}
                                <span class="badge ${c.tipo}">${c.tipo}</span>
                                <span class="badge ${statusClass}">${statusTexto}</span>
                                ${c.recorrencia !== 'unico' ? `<span class="badge" style="background: rgba(0,78,137,0.2); color: var(--info);">${c.recorrencia}</span>` : ''}
                            </div>
                            <div class="conta-detalhes">
                                ${c.fornecedor ? `<span><i class="fas fa-building"></i> ${c.fornecedor}</span>` : ''}
                                ${c.categoria ? `<span><i class="fas fa-tag"></i> ${c.categoria}</span>` : ''}
                                <span><i class="fas fa-calendar"></i> Venc: ${formatarData(c.data_vencimento)}</span>
                                ${c.paga ? `<span><i class="fas fa-check-circle"></i> Pago em ${formatarData(c.data_pagamento)}</span>` : ''}
                                ${c.conta_nome ? `<span><i class="fas fa-university"></i> ${c.conta_nome}</span>` : ''}
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div class="conta-valor">R$ ${parseFloat(c.valor).toFixed(2)}</div>
                            <div class="conta-acoes">
                                ${!c.paga ? `
                                    <button class="btn-icon" onclick="abrirModalPagar(${c.id})" title="Marcar como paga">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn-icon" onclick="editarConta(${c.id})" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                ` : `
                                    <button class="btn-icon" onclick="desmarcarPaga(${c.id})" title="Desmarcar pagamento">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                `}
                                <button class="btn-icon delete" onclick="excluirConta(${c.id}, '${c.descricao.replace(/'/g, "\\'")}', ${c.paga})" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function filtrarPor(status) {
            statusAtual = status;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`[data-status="${status}"]`).classList.add('active');
            carregarContas();
        }
        
        function aplicarFiltros() {
            carregarResumo();
            carregarContas();
        }
        
        function abrirModal(conta = null) {
            contaEditando = conta;
            document.getElementById('formConta').reset();
            
            if (conta) {
                document.getElementById('modalTitle').textContent = '✏️ Editar Conta a Pagar';
                document.getElementById('contaId').value = conta.id;
                document.getElementById('descricao').value = conta.descricao;
                document.getElementById('fornecedor').value = conta.fornecedor || '';
                document.getElementById('categoria').value = conta.categoria || '';
                document.getElementById('valor').value = conta.valor;
                document.getElementById('dataVencimento').value = conta.data_vencimento;
                document.getElementById('tipo').value = conta.tipo;
                document.getElementById('recorrencia').value = conta.recorrencia;
                document.getElementById('observacoes').value = conta.observacoes || '';
            } else {
                document.getElementById('modalTitle').textContent = '📅 Nova Conta a Pagar';
                document.getElementById('contaId').value = '';
            }
            
            document.getElementById('modalConta').classList.add('active');
        }
        
        function fecharModal() {
            document.getElementById('modalConta').classList.remove('active');
        }
        
        function abrirModalPagar(id) {
            document.getElementById('pagarContaId').value = id;
            document.getElementById('dataPagamento').value = new Date().toISOString().split('T')[0];
            document.getElementById('modalPagar').classList.add('active');
        }
        
        function fecharModalPagar() {
            document.getElementById('modalPagar').classList.remove('active');
        }
        
        function editarConta(id) {
            const conta = contas.find(c => c.id === id);
            if (conta) abrirModal(conta);
        }
        
        async function salvarConta(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const id = document.getElementById('contaId').value;
            formData.append('action', id ? 'update' : 'create');
            if (id) formData.append('id', id);
            
            try {
                const response = await fetch('../api/contas-pagar.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Conta salva com sucesso!');
                    fecharModal();
                    carregarAlertas();
                    carregarResumo();
                    carregarContas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar conta');
            }
        }
        
        async function pagarConta(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'marcar_paga');
            formData.append('id', document.getElementById('pagarContaId').value);
            
            try {
                const response = await fetch('../api/contas-pagar.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Pagamento registrado!');
                    fecharModalPagar();
                    carregarAlertas();
                    carregarResumo();
                    carregarContas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function desmarcarPaga(id) {
            if (!confirm('Desmarcar o pagamento desta conta?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'desmarcar_paga');
                formData.append('id', id);
                
                const response = await fetch('../api/contas-pagar.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Pagamento desmarcado!');
                    carregarAlertas();
                    carregarResumo();
                    carregarContas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function excluirConta(id, descricao, paga) {
            if (paga) {
                alert('Não é possível excluir uma conta já paga. Desmarque o pagamento primeiro.');
                return;
            }
            
            if (!confirm(`Deseja excluir "${descricao}"?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                const response = await fetch('../api/contas-pagar.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Conta excluída!');
                    carregarAlertas();
                    carregarResumo();
                    carregarContas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function formatarData(data) {
            return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
        }
        
        function mostrarToast(mensagem) {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = mensagem;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
<?php include '../includes/footer.php'; ?>
</body>
</html>
