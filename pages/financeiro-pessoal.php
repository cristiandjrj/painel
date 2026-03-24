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
    <title>Financeiro Pessoal - EventProDJ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            background: linear-gradient(135deg, #004E89 0%, #003566 100%);
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .stat-card.success::before {
            background: linear-gradient(90deg, var(--success), transparent);
        }
        
        .stat-card.danger::before {
            background: linear-gradient(90deg, var(--danger), transparent);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
        }
        
        .stat-value.success {
            color: var(--success);
        }
        
        .stat-value.danger {
            color: var(--danger);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: var(--bg-card);
            border: 2px solid var(--border);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        
        .action-btn:hover {
            border-color: var(--primary);
            background: rgba(255, 107, 53, 0.1);
            transform: translateY(-3px);
        }
        
        .action-btn i {
            font-size: 40px;
            color: var(--primary);
        }
        
        .action-btn span {
            font-weight: 600;
            font-size: 16px;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--border);
        }
        
        .tab {
            padding: 15px 30px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            font-weight: 600;
        }
        
        .tab.active {
            border-bottom-color: var(--primary);
            color: var(--primary);
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* List */
        .list-container {
            background: var(--bg-card);
            border-radius: 15px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--border);
            border-left: 4px solid transparent;
        }
        
        .list-item:last-child {
            border-bottom: none;
        }
        
        .list-item.receita {
            border-left-color: var(--success);
        }
        
        .list-item.despesa {
            border-left-color: var(--danger);
        }
        
        .item-main {
            flex: 1;
        }
        
        .item-descricao {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .item-detalhes {
            font-size: 13px;
            color: var(--text-secondary);
            display: flex;
            gap: 15px;
        }
        
        .item-valor {
            font-size: 24px;
            font-weight: 700;
            min-width: 150px;
            text-align: right;
        }
        
        .item-valor.receita {
            color: var(--success);
        }
        
        .item-valor.despesa {
            color: var(--danger);
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
            background: var(--danger);
            border-color: var(--danger);
            color: white;
        }
        
        /* Chart Card */
        .chart-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
        }
        
        .chart-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            max-width: 500px;
            border: 1px solid var(--border);
        }
        
        .modal-header {
            padding: 25px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header.success {
            background: linear-gradient(135deg, var(--success), #05B58B);
        }
        
        .modal-header.danger {
            background: linear-gradient(135deg, var(--danger), #D84060);
        }
        
        .modal-header.info {
            background: linear-gradient(135deg, var(--info), #003566);
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            color: white;
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
            width: 100%;
            justify-content: center;
            font-size: 16px;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #05B58B);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #D84060);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, var(--info), #003566);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
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
        
        /* Contas Grid */
        .contas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .conta-card {
            background: var(--bg-card);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .conta-card:hover {
            transform: translateX(5px);
        }
        
        .conta-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .conta-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: rgba(255, 107, 53, 0.2);
            color: var(--primary);
        }
        
        .conta-info h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        
        .conta-tipo {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .conta-saldo {
            font-size: 28px;
            font-weight: 700;
            color: var(--success);
            margin: 15px 0;
        }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
            .content-grid { grid-template-columns: 1fr !important; }
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
            <h1 class="page-title">👤 Financeiro Pessoal</h1>
            <p style="opacity: 0.9;">Controle suas finanças pessoais separado da empresa</p>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-icon">💵</div>
                <div class="stat-label">Receitas Pessoais</div>
                <div class="stat-value success" id="receitasPessoais">R$ 0,00</div>
                <div style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">Este mês</div>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-icon">💳</div>
                <div class="stat-label">Despesas Pessoais</div>
                <div class="stat-value danger" id="despesasPessoais">R$ 0,00</div>
                <div style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">Este mês</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--primary);">📊</div>
                <div class="stat-label">Saldo Pessoal</div>
                <div class="stat-value" style="color: var(--primary);" id="saldoPessoal">R$ 0,00</div>
                <div style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">Disponível</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: var(--info);">🏦</div>
                <div class="stat-label">Total em Contas</div>
                <div class="stat-value" style="color: var(--info);" id="totalContas">R$ 0,00</div>
                <div style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">Saldo atual</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-btn" onclick="abrirModal('receita')">
                <i class="fas fa-plus-circle"></i>
                <span>Nova Receita</span>
            </div>
            
            <div class="action-btn" onclick="abrirModal('despesa')">
                <i class="fas fa-minus-circle"></i>
                <span>Nova Despesa</span>
            </div>
            
            <div class="action-btn" onclick="abrirModal('conta')">
                <i class="fas fa-wallet"></i>
                <span>Nova Conta</span>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="mudarTab('receitas')">Receitas</div>
            <div class="tab" onclick="mudarTab('despesas')">Despesas</div>
            <div class="tab" onclick="mudarTab('contas')">Contas</div>
        </div>
        
        <!-- Tab: Receitas -->
        <div class="tab-content active" id="tabReceitas">
            <div class="list-container" id="receitasList">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Carregando receitas...</p>
                </div>
            </div>
        </div>
        
        <!-- Tab: Despesas -->
        <div class="tab-content" id="tabDespesas">
            <div class="chart-card">
                <h3 class="chart-title"><i class="fas fa-chart-pie"></i> Despesas por Categoria</h3>
                <canvas id="despesasChart"></canvas>
            </div>
            
            <div class="list-container" id="despesasList">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Carregando despesas...</p>
                </div>
            </div>
        </div>
        
        <!-- Tab: Contas -->
        <div class="tab-content" id="tabContas">
            <div class="contas-grid" id="contasGrid">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Carregando contas...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Receita -->
    <div class="modal" id="modalReceita">
        <div class="modal-content">
            <div class="modal-header success">
                <h2>💵 Nova Receita Pessoal</h2>
                <button class="modal-close" onclick="fecharModal('receita')">×</button>
            </div>
            <div class="modal-body">
                <form id="formReceita" onsubmit="salvarReceita(event)">
                    <div class="form-group">
                        <label>Descrição *</label>
                        <input type="text" name="descricao" required placeholder="Ex: Salário, Freelance, Venda">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Categoria *</label>
                            <select name="categoria_id" id="categoriaReceita" required>
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Valor (R$) *</label>
                            <input type="number" step="0.01" name="valor" required placeholder="0,00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data *</label>
                            <input type="date" name="data_receita" id="dataReceita" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Conta</label>
                            <select name="conta_id" id="contaReceita">
                                <option value="">Nenhuma</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Observações</label>
                        <textarea name="observacoes" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        💾 Adicionar Receita
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Despesa -->
    <div class="modal" id="modalDespesa">
        <div class="modal-content">
            <div class="modal-header danger">
                <h2>💳 Nova Despesa Pessoal</h2>
                <button class="modal-close" onclick="fecharModal('despesa')">×</button>
            </div>
            <div class="modal-body">
                <form id="formDespesa" onsubmit="salvarDespesa(event)">
                    <div class="form-group">
                        <label>Descrição *</label>
                        <input type="text" name="descricao" required placeholder="Ex: Mercado, Conta de Luz, Aluguel">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Categoria *</label>
                            <select name="categoria_id" id="categoriaDespesa" required>
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Valor (R$) *</label>
                            <input type="number" step="0.01" name="valor" required placeholder="0,00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data *</label>
                            <input type="date" name="data_despesa" id="dataDespesa" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Conta</label>
                            <select name="conta_id" id="contaDespesa">
                                <option value="">Nenhuma</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Observações</label>
                        <textarea name="observacoes" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-danger">
                        💾 Adicionar Despesa
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Conta -->
    <div class="modal" id="modalConta">
        <div class="modal-content">
            <div class="modal-header info">
                <h2>🏦 Nova Conta Pessoal</h2>
                <button class="modal-close" onclick="fecharModal('conta')">×</button>
            </div>
            <div class="modal-body">
                <form id="formConta" onsubmit="salvarConta(event)">
                    <div class="form-group">
                        <label>Nome da Conta *</label>
                        <input type="text" name="nome" required placeholder="Ex: Conta Corrente, Cartão Nubank">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tipo *</label>
                            <select name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="conta_corrente">🏦 Conta Corrente</option>
                                <option value="conta_poupanca">💰 Poupança</option>
                                <option value="carteira">💵 Carteira/Dinheiro</option>
                                <option value="investimento">📈 Investimento</option>
                                <option value="outros">📌 Outros</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Saldo Inicial (R$)</label>
                            <input type="number" step="0.01" name="saldo_inicial" placeholder="0,00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Banco/Instituição</label>
                        <input type="text" name="banco" placeholder="Ex: Nubank, Inter, Banco do Brasil">
                    </div>
                    
                    <button type="submit" class="btn btn-info">
                        💾 Adicionar Conta
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        let receitas = [];
        let despesas = [];
        let contas = [];
        let categoriasReceita = [];
        let categoriasDespesa = [];
        let despesasChart = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            const hoje = new Date().toISOString().split('T')[0];
            document.getElementById('dataReceita').value = hoje;
            document.getElementById('dataDespesa').value = hoje;
            
            carregarCategorias();
            carregarContas();
            carregarResumo();
            carregarReceitas();
            carregarDespesas();
            carregarGraficoDespesas();
        });
        
        async function carregarCategorias() {
            try {
                // Receitas
                const respReceitas = await fetch('../api/financeiro-pessoal.php?action=categorias_list&tipo=receita');
                const dataReceitas = await respReceitas.json();
                
                if (dataReceitas.success) {
                    categoriasReceita = dataReceitas.categorias;
                    const select = document.getElementById('categoriaReceita');
                    categoriasReceita.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                    });
                }
                
                // Despesas
                const respDespesas = await fetch('../api/financeiro-pessoal.php?action=categorias_list&tipo=despesa');
                const dataDespesas = await respDespesas.json();
                
                if (dataDespesas.success) {
                    categoriasDespesa = dataDespesas.categorias;
                    const select = document.getElementById('categoriaDespesa');
                    categoriasDespesa.forEach(c => {
                        select.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function carregarContas() {
            try {
                const response = await fetch('../api/financeiro-pessoal.php?action=contas_list');
                const data = await response.json();
                
                if (data.success) {
                    contas = data.contas;
                    
                    // Preencher selects
                    const selectReceita = document.getElementById('contaReceita');
                    const selectDespesa = document.getElementById('contaDespesa');
                    
                    contas.forEach(c => {
                        selectReceita.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                        selectDespesa.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                    });
                    
                    renderizarContas();
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function renderizarContas() {
            const grid = document.getElementById('contasGrid');
            
            if (contas.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-wallet"></i>
                        <h3>Nenhuma conta cadastrada</h3>
                        <p>Adicione uma conta para começar</p>
                    </div>
                `;
                return;
            }
            
            const tipos = {
                'conta_corrente': 'Conta Corrente',
                'conta_poupanca': 'Poupança',
                'carteira': 'Carteira',
                'investimento': 'Investimento',
                'outros': 'Outros'
            };
            
            grid.innerHTML = contas.map(c => `
                <div class="conta-card">
                    <div class="conta-header">
                        <div class="conta-icon">
                            <i class="fas fa-${c.icone}"></i>
                        </div>
                        <div class="conta-info">
                            <h3>${c.nome}</h3>
                            <div class="conta-tipo">${tipos[c.tipo]}</div>
                        </div>
                    </div>
                    <div class="conta-saldo">R$ ${parseFloat(c.saldo_atual).toFixed(2)}</div>
                    ${c.banco ? `<div style="font-size: 14px; color: var(--text-secondary);">🏦 ${c.banco}</div>` : ''}
                </div>
            `).join('');
        }
        
        async function carregarResumo() {
            try {
                const response = await fetch('../api/financeiro-pessoal.php?action=resumo');
                const data = await response.json();
                
                if (data.success) {
                    const r = data.resumo;
                    document.getElementById('receitasPessoais').textContent = 'R$ ' + parseFloat(r.receitas_mes).toFixed(2);
                    document.getElementById('despesasPessoais').textContent = 'R$ ' + parseFloat(r.despesas_mes).toFixed(2);
                    document.getElementById('saldoPessoal').textContent = 'R$ ' + (parseFloat(r.receitas_mes) - parseFloat(r.despesas_mes)).toFixed(2);
                    document.getElementById('totalContas').textContent = 'R$ ' + parseFloat(r.saldo_total).toFixed(2);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function carregarReceitas() {
            try {
                const hoje = new Date();
                const response = await fetch(`../api/financeiro-pessoal.php?action=receitas_list&mes=${hoje.getMonth() + 1}&ano=${hoje.getFullYear()}`);
                const data = await response.json();
                
                if (data.success) {
                    receitas = data.receitas;
                    renderizarReceitas();
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function renderizarReceitas() {
            const container = document.getElementById('receitasList');
            
            if (receitas.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-coins"></i>
                        <h3>Nenhuma receita este mês</h3>
                        <p>Adicione uma receita pessoal</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = receitas.map(r => `
                <div class="list-item receita">
                    <div class="item-main">
                        <div class="item-descricao">${r.descricao}</div>
                        <div class="item-detalhes">
                            <span><i class="fas fa-calendar"></i> ${formatarData(r.data_receita)}</span>
                            ${r.categoria_nome ? `<span><i class="fas fa-tag"></i> ${r.categoria_nome}</span>` : ''}
                            ${r.conta_nome ? `<span><i class="fas fa-wallet"></i> ${r.conta_nome}</span>` : ''}
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="item-valor receita">+ R$ ${parseFloat(r.valor).toFixed(2)}</div>
                        <button class="btn-icon" onclick="excluirReceita(${r.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        async function carregarDespesas() {
            try {
                const hoje = new Date();
                const response = await fetch(`../api/financeiro-pessoal.php?action=despesas_list&mes=${hoje.getMonth() + 1}&ano=${hoje.getFullYear()}`);
                const data = await response.json();
                
                if (data.success) {
                    despesas = data.despesas;
                    renderizarDespesas();
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function renderizarDespesas() {
            const container = document.getElementById('despesasList');
            
            if (despesas.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>Nenhuma despesa este mês</h3>
                        <p>Adicione uma despesa pessoal</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = despesas.map(d => `
                <div class="list-item despesa">
                    <div class="item-main">
                        <div class="item-descricao">${d.descricao}</div>
                        <div class="item-detalhes">
                            <span><i class="fas fa-calendar"></i> ${formatarData(d.data_despesa)}</span>
                            ${d.categoria_nome ? `<span><i class="fas fa-tag"></i> ${d.categoria_nome}</span>` : ''}
                            ${d.conta_nome ? `<span><i class="fas fa-wallet"></i> ${d.conta_nome}</span>` : ''}
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="item-valor despesa">- R$ ${parseFloat(d.valor).toFixed(2)}</div>
                        <button class="btn-icon" onclick="excluirDespesa(${d.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        async function carregarGraficoDespesas() {
            try {
                const hoje = new Date();
                const response = await fetch(`../api/financeiro-pessoal.php?action=despesas_por_categoria&mes=${hoje.getMonth() + 1}&ano=${hoje.getFullYear()}`);
                const data = await response.json();
                
                if (data.success && data.categorias.length > 0) {
                    const ctx = document.getElementById('despesasChart').getContext('2d');
                    
                    if (despesasChart) {
                        despesasChart.destroy();
                    }
                    
                    despesasChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.categorias.map(c => c.nome),
                            datasets: [{
                                data: data.categorias.map(c => parseFloat(c.total_valor)),
                                backgroundColor: data.categorias.map(c => c.cor),
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#A0AEC0',
                                        padding: 15
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function mudarTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            event.target.classList.add('active');
            
            if (tab === 'receitas') {
                document.getElementById('tabReceitas').classList.add('active');
            } else if (tab === 'despesas') {
                document.getElementById('tabDespesas').classList.add('active');
            } else if (tab === 'contas') {
                document.getElementById('tabContas').classList.add('active');
            }
        }
        
        function abrirModal(tipo) {
            document.getElementById('modal' + tipo.charAt(0).toUpperCase() + tipo.slice(1)).classList.add('active');
        }
        
        function fecharModal(tipo) {
            document.getElementById('modal' + tipo.charAt(0).toUpperCase() + tipo.slice(1)).classList.remove('active');
        }
        
        async function salvarReceita(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'receita_create');
            
            try {
                const response = await fetch('../api/financeiro-pessoal.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Receita adicionada com sucesso!');
                    fecharModal('receita');
                    event.target.reset();
                    carregarContas();
                    carregarResumo();
                    carregarReceitas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function salvarDespesa(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'despesa_create');
            
            try {
                const response = await fetch('../api/financeiro-pessoal.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Despesa adicionada com sucesso!');
                    fecharModal('despesa');
                    event.target.reset();
                    carregarContas();
                    carregarResumo();
                    carregarDespesas();
                    carregarGraficoDespesas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function salvarConta(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'conta_create');
            
            try {
                const response = await fetch('../api/financeiro-pessoal.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Conta adicionada com sucesso!');
                    fecharModal('conta');
                    event.target.reset();
                    carregarContas();
                    carregarResumo();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function excluirReceita(id) {
            if (!confirm('Deseja excluir esta receita?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'receita_delete');
                formData.append('id', id);
                
                const response = await fetch('../api/financeiro-pessoal.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Receita excluída!');
                    carregarContas();
                    carregarResumo();
                    carregarReceitas();
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function excluirDespesa(id) {
            if (!confirm('Deseja excluir esta despesa?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'despesa_delete');
                formData.append('id', id);
                
                const response = await fetch('../api/financeiro-pessoal.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarToast('✅ Despesa excluída!');
                    carregarContas();
                    carregarResumo();
                    carregarDespesas();
                    carregarGraficoDespesas();
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
