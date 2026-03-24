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
    <title>Gestão Financeira - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css?v=20260228">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Syne', sans-serif; background: #0A0E27; color: #FFFFFF; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        
        /* Header - usa classes do CSS externo (page-header, page-title, page-subtitle) */
        
        /* Filtros */
        .filters-section { background: #141B3D; border: 1px solid #2D3561; border-radius: 16px; padding: 20px; margin-bottom: 25px; }
        .filters-row { display: grid; grid-template-columns: 180px 120px 150px 100px; gap: 12px; align-items: end; }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-label { font-size: 10px; font-weight: 700; color: #A0AEC0; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px; }
        .filter-select { padding: 10px 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid #2D3561; border-radius: 8px; color: white; font-size: 13px; font-weight: 500; cursor: pointer; }
        .filter-select:focus { outline: none; border-color: #FF6B35; }
        .btn-clear { padding: 10px 16px; background: rgba(239, 71, 111, 0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; white-space: nowrap; font-size: 12px; align-self: flex-end; }
        .btn-clear:hover { background: rgba(239, 71, 111, 0.2); }
        
        /* Tabs */
        .tabs-container { margin-bottom: 30px; }
        .tabs-header { display: flex; gap: 10px; border-bottom: 2px solid #2D3561; }
        .tab-button { padding: 15px 30px; background: transparent; border: none; color: #A0AEC0; font-weight: 600; font-size: 15px; cursor: pointer; position: relative; transition: all 0.2s; border-radius: 8px 8px 0 0; }
        .tab-button:hover { color: white; background: rgba(255, 255, 255, 0.05); }
        .tab-button.active { color: #FF6B35; background: rgba(255, 107, 53, 0.1); }
        .tab-button.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: #FF6B35; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Stat Cards - idêntico ao dashboard principal */
        .fin-stats .stat-card::before { display: none; }
        .fin-stats .stat-card { display: flex; align-items: center; gap: 12px; overflow: visible; padding: 16px 14px; }
        .fin-stats .stat-card .stat-icon { font-size: 24px; width: 48px; height: 48px; min-width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0; margin-bottom: 0; }
        .fin-stats .stat-card .stat-info { flex: 1; min-width: 0; }
        .fin-stats .stat-card .stat-info .stat-label { font-size: 10px; color: #A0AEC0; margin-bottom: 2px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }
        .fin-stats .stat-card .stat-info .stat-value { font-size: 18px; font-weight: 800; margin-bottom: 0; white-space: nowrap; }
        /* Grid responsivo dos cards - igual ao dashboard */
        .fin-stats .stats-grid { grid-template-columns: repeat(6, 1fr) !important; }
        @media (max-width: 1024px) { .fin-stats .stats-grid { grid-template-columns: repeat(3, 1fr) !important; } }
        @media (max-width: 768px)  { .fin-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; } }
        @media (max-width: 430px)  { .fin-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .fin-stats .stat-card { padding: 12px 10px !important; gap: 8px !important; }
            .fin-stats .stat-card .stat-icon { width: 36px !important; height: 36px !important; min-width: 36px !important; font-size: 18px !important; }
            .fin-stats .stat-card .stat-info .stat-value { font-size: 15px !important; }
            .fin-stats .stat-card .stat-info .stat-label { font-size: 9px !important; }
        }
        
        /* Section */
        .section { background: #141B3D; border: 1px solid #2D3561; border-radius: 16px; padding: 30px; margin-bottom: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .section-title { font-size: 20px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px; }
        .btn-new { padding: 12px 24px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-new:hover { background: #E85A2B; transform: scale(1.05); }
        
        /* Lista */
        .list-item { background: rgba(255, 255, 255, 0.02); border: 1px solid #2D3561; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; }
        .list-item:hover { background: rgba(255, 255, 255, 0.05); border-color: #FF6B35; }
        .item-info { flex: 1; }
        .item-title { font-weight: 600; font-size: 15px; margin-bottom: 5px; }
        .item-subtitle { font-size: 13px; color: #A0AEC0; }
        .item-value { font-size: 18px; font-weight: 700; }
        .item-value.positivo { color: #06D6A0; }
        .item-value.negativo { color: #EF476F; }
        
        /* Conta Card */
        .conta-card { background: linear-gradient(135deg, rgba(255, 107, 53, 0.1), rgba(6, 214, 160, 0.1)); border: 1px solid #2D3561; border-radius: 16px; padding: 25px; margin-bottom: 20px; }
        .conta-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; }
        .conta-nome { font-size: 20px; font-weight: 700; margin-bottom: 5px; }
        .conta-tipo { font-size: 13px; color: #A0AEC0; }
        .conta-saldo { text-align: right; }
        .conta-saldo-label { font-size: 12px; color: #A0AEC0; margin-bottom: 5px; }
        .conta-saldo-valor { font-size: 28px; font-weight: 800; color: #06D6A0; }
        .conta-stats { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .conta-stat { text-align: center; }
        .conta-stat-label { font-size: 11px; color: #A0AEC0; text-transform: uppercase; margin-bottom: 5px; }
        .conta-stat-value { font-size: 16px; font-weight: 700; }
        
        /* Ações */
        .actions { display: flex; gap: 10px; }
        .btn-action { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .btn-extrato { background: rgba(24, 119, 242, 0.1); color: #1877F2; border: 1px solid rgba(24, 119, 242, 0.3); }
        .btn-editar { background: rgba(255, 210, 63, 0.1); color: #FFD23F; border: 1px solid rgba(255, 210, 63, 0.3); }
        .btn-excluir { background: rgba(239, 71, 111, 0.1); color: #EF476F; border: 1px solid rgba(239, 71, 111, 0.3); }
        .btn-desativar { background: rgba(160, 174, 192, 0.1); color: #A0AEC0; border: 1px solid rgba(160, 174, 192, 0.3); }
        .btn-action:hover { transform: scale(1.05); }
        
        /* Forma Pagamento */
        .forma-pag-list { display: grid; gap: 15px; }
        .forma-pag-item { background: rgba(255, 255, 255, 0.02); border: 1px solid #2D3561; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .forma-pag-info { display: flex; align-items: center; gap: 15px; }
        .forma-pag-icon { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; background: rgba(255, 107, 53, 0.1); color: #FF6B35; }
        .forma-pag-nome { font-weight: 600; font-size: 15px; }
        .forma-pag-count { font-size: 13px; color: #A0AEC0; }
        .forma-pag-valor { font-size: 18px; font-weight: 700; color: #06D6A0; }
        .forma-pag-percent { font-size: 24px; font-weight: 700; color: #FFD23F; }
        
        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: #141B3D; border-radius: 16px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid #2D3561; }
        .modal-header { background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .modal-title { font-size: 20px; font-weight: 700; color: white; }
        .modal-close { background: rgba(255, 255, 255, 0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white; }
        .modal-body { padding: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #A0AEC0; font-size: 13px; text-transform: uppercase; }
        .form-input, .form-select { width: 100%; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid #2D3561; border-radius: 8px; color: white; font-size: 14px; }
        .form-input:focus, .form-select:focus { outline: none; border-color: #FF6B35; }
        .modal-footer { padding: 20px 24px; border-top: 1px solid #2D3561; display: flex; gap: 12px; justify-content: flex-end; }
        .btn-cancel { padding: 12px 24px; background: rgba(239, 71, 111, 0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-submit { padding: 12px 24px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; color: #5A6C8F; }
        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
        .empty-state-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .filters-row { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
            .conta-stats { grid-template-columns: 1fr !important; }
            .conta-header { flex-direction: column !important; gap: 12px !important; }
            .conta-saldo { text-align: left !important; }
            .conta-saldo-valor { font-size: 22px !important; }
            .list-item { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; padding: 16px !important; }
            .actions { flex-wrap: wrap !important; width: 100% !important; }
            .btn-action { font-size: 11px !important; padding: 6px 10px !important; }
            .section { padding: 20px 16px !important; }
            .section-header { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .tabs-header { overflow-x: auto !important; -webkit-overflow-scrolling: touch; gap: 4px !important; }
            .tab-button { padding: 10px 16px !important; font-size: 12px !important; white-space: nowrap !important; }
            .forma-pag-item { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .modal-content { max-width: 100% !important; margin: 10px !important; }
            .form-row { grid-template-columns: 1fr !important; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
            .filters-row { grid-template-columns: 1fr !important; }
            .tab-button { padding: 8px 10px !important; font-size: 11px !important; }
        }
    </style>
</head>
<body>
    <?php include '../includes/menu.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">💰 Gestão Financeira</h1>
            <p class="page-subtitle">Controle completo de receitas, despesas e contas bancárias</p>
        </div>

        <!-- Stats Cards - 6 cards como o original -->
        <div class="fin-stats">
        <div class="stats-grid">
            <!-- 1. Total de Receitas -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(6, 214, 160, 0.05)); cursor: pointer;" onclick="mostrarDetalhes('receitas')">
                <div class="stat-icon" style="background: rgba(6, 214, 160, 0.15); color: #06D6A0;">💰</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Receitas</div>
                    <div class="stat-value" id="totalReceitas" style="color: #06D6A0;">R$ 0,00</div>
                </div>
            </div>

            <!-- 2. Valores a Receber -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(79, 70, 229, 0.05)); cursor: pointer;" onclick="mostrarDetalhes('receber')">
                <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #4F46E5;">⏳</div>
                <div class="stat-info">
                    <div class="stat-label">Valores a Receber</div>
                    <div class="stat-value" id="valoresReceber" style="color: #4F46E5;">R$ 0,00</div>
                </div>
            </div>

            <!-- 3. Despesas do Mês -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(239, 71, 111, 0.2), rgba(239, 71, 111, 0.05)); cursor: pointer;" onclick="mostrarDetalhes('despesas')">
                <div class="stat-icon" style="background: rgba(239, 71, 111, 0.15); color: #EF476F;">💸</div>
                <div class="stat-info">
                    <div class="stat-label">Despesas do Mês</div>
                    <div class="stat-value" id="totalDespesas" style="color: #EF476F;">R$ 0,00</div>
                </div>
            </div>

            <!-- 4. Custos de Eventos -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 107, 53, 0.05)); cursor: pointer;" onclick="mostrarDetalhes('custos_eventos')">
                <div class="stat-icon" style="background: rgba(255, 107, 53, 0.15); color: #FF6B35;">🎯</div>
                <div class="stat-info">
                    <div class="stat-label">Custos de Eventos</div>
                    <div class="stat-value" id="custosEventos" style="color: #FF6B35;">R$ 0,00</div>
                </div>
            </div>

            <!-- 5. Total de Despesas -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(168, 85, 247, 0.05)); cursor: pointer;" onclick="mostrarDetalhes('total_despesas')">
                <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #A855F7;">📊</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Despesas</div>
                    <div class="stat-value" id="totalDespesasCompleto" style="color: #A855F7;">R$ 0,00</div>
                </div>
            </div>

            <!-- 6. Lucro Líquido -->
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 210, 63, 0.2), rgba(255, 210, 63, 0.05)); cursor: pointer;" onclick="mostrarDetalhes('lucro')">
                <div class="stat-icon" style="background: rgba(255, 210, 63, 0.15); color: #FFD23F;">📈</div>
                <div class="stat-info">
                    <div class="stat-label">Lucro Líquido</div>
                    <div class="stat-value" id="lucroLiquido" style="color: #06D6A0;">R$ 0,00</div>
                </div>
            </div>
        </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar"></i> Mês</label>
                    <select class="filter-select" id="filtroMes" onchange="aplicarFiltros()">
                        <option value="todos">Todos os meses</option>
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
                </div>
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar-alt"></i> Ano</label>
                    <select class="filter-select" id="filtroAno" onchange="aplicarFiltros()">
                        <option value="todos">Todos os anos</option>
                        <option value="2027">2027</option>
                        <option value="2026" selected>2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-chart-bar"></i> Visualização</label>
                    <select class="filter-select" id="filtroVisualizacao" onchange="aplicarFiltros()">
                        <option value="mensal">Mensal</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <button class="btn-clear" onclick="limparFiltros()">
                    <i class="fas fa-times"></i> Limpar
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-button active" onclick="mudarAba(0)">
                    <i class="fas fa-chart-line"></i> VISÃO GERAL
                </button>
                <button class="tab-button" onclick="mudarAba(1)">
                    <i class="fas fa-bank"></i> POR CONTA
                </button>
                <button class="tab-button" onclick="mudarAba(2)">
                    <i class="fas fa-cog"></i> GERENCIAR CONTAS
                </button>
            </div>

            <!-- ABA 1: VISÃO GERAL -->
            <div class="tab-content active" id="tab-visao-geral">

                <!-- Valores a Receber -->
                <div class="section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-family: 'Syne', sans-serif; margin: 0; font-size: 18px;">💸 Valores a Receber</h3>
                        <span id="totalAReceber" style="font-size: 18px; font-weight: 700; color: #FFD23F;">R$ 0,00</span>
                    </div>
                    <div id="listaValoresReceber" style="display: grid; gap: 8px;">
                        <p style="text-align: center; color: #A0AEC0; padding: 20px;">✅ Não há valores a receber!</p>
                    </div>
                </div>
                
                <!-- Últimas Transações -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-list"></i> Últimas Transações</h2>
                        <select class="filter-select" style="width: auto;" id="filtroTipoTransacao" onchange="aplicarFiltros()">
                            <option value="">Todas</option>
                            <option value="receita">Receitas</option>
                            <option value="despesa">Despesas</option>
                        </select>
                    </div>
                    <div id="listaTransacoes"></div>
                </div>
                
                <!-- Sinais Recebidos -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-hand-holding-usd"></i> Sinais Recebidos por Forma de Pagamento</h2>
                        <div style="font-size: 12px; color: #A0AEC0;">Apenas entradas iniciais dos eventos (30% do valor)</div>
                    </div>
                    <div id="sinaisRecebidos" class="forma-pag-list"></div>
                </div>
                
                <!-- Receitas por Forma -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-chart-pie"></i> Receitas por Forma de Pagamento</h2>
                    </div>
                    <div id="receitasPorForma" class="forma-pag-list"></div>
                </div>
                
            </div>
            
            <!-- ABA 2: POR CONTA -->
            <div class="tab-content" id="tab-por-conta">
                
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-university"></i> Saldo por Conta Bancária</h2>
                    </div>
                    <div id="listaContasSaldo"></div>
                </div>
                
            </div>
            
            <!-- ABA 3: GERENCIAR CONTAS -->
            <div class="tab-content" id="tab-gerenciar">
                
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-building"></i> Contas Bancárias</h2>
                        <button class="btn-new" onclick="abrirModalNovaConta()">
                            <i class="fas fa-plus"></i> NOVA CONTA
                        </button>
                    </div>
                    <div id="listaContasGerenciar"></div>
                </div>
                
            </div>
            
        </div>
    </div>
    
    <!-- Modal Nova/Editar Conta -->
    <div class="modal-overlay" id="modalConta">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalContaTitulo">Nova Conta Bancária</h3>
                <button class="modal-close" onclick="fecharModalConta()">&times;</button>
            </div>
            <form id="formConta" onsubmit="salvarConta(event)">
                <div class="modal-body">
                    <input type="hidden" id="contaId" name="id">
                    
                    <div class="form-group">
                        <label class="form-label">Nome da Conta *</label>
                        <input type="text" class="form-input" id="contaNome" name="nome" required placeholder="Ex: Banco do Brasil CNPJ">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Banco *</label>
                        <input type="text" class="form-input" id="contaBanco" name="banco" required placeholder="Ex: Banco do Brasil">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tipo de Conta *</label>
                        <select class="form-select" id="contaTipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="corrente">Conta Corrente</option>
                            <option value="poupanca">Poupança</option>
                            <option value="investimento">Investimento</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Saldo Inicial (R$)</label>
                        <input type="number" step="0.01" class="form-input" id="contaSaldoInicial" name="saldo_inicial" value="0" placeholder="0,00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="fecharModalConta()">Cancelar</button>
                    <button type="submit" class="btn-submit">Salvar Conta</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Variáveis globais
        let dadosFinanceiros = {
            receitas: [],
            despesas: [],
            contas: [],
            transacoes: []
        };
        
        // Carregar dados ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            const mesAtual = new Date().getMonth() + 1;
            const anoAtual = new Date().getFullYear();
            document.getElementById('filtroMes').value = mesAtual;
            document.getElementById('filtroAno').value = anoAtual;
            
            carregarDadosFinanceiros();
        });
        
        // Carregar dados da API
        async function carregarDadosFinanceiros() {
            try {
                const mes = document.getElementById('filtroMes').value;
                const ano = document.getElementById('filtroAno').value;
                const visualizacao = document.getElementById('filtroVisualizacao').value;
                
                // Buscar dados da API (se "todos", não enviar parâmetro para a API calcular tudo)
                const urlResumo = new URLSearchParams({ action: 'resumo_financeiro', visualizacao });
                if (mes !== 'todos') urlResumo.set('mes', mes);
                if (ano !== 'todos') urlResumo.set('ano', ano);
                const response = await fetch(`../api/financeiro.php?${urlResumo.toString()}`);
                const data = await response.json();
                
                if (data.success) {
                    // Adaptar estrutura para o formato esperado
                    dadosFinanceiros = {
                        receitas: [{ valor: data.resumo.total_receitas || 0 }],
                        despesas: [{ valor: data.resumo.total_saidas || 0 }],
                        custosGerais: data.resumo.custos_gerais || 0,
                        custosEventosTotal: data.resumo.custos_eventos || 0,
                        contas: [],
                        transacoes: [],
                        valoresReceber: [],
                        sinaisRecebidos: [],
                        receitasPorForma: []
                    };
                    
                    // Buscar dados adicionais
                    await carregarDadosAdicionais(mes, ano);
                    
                    atualizarInterface();
                } else {
                    console.error('Erro ao carregar dados:', data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        // Carregar dados adicionais
        async function carregarDadosAdicionais(mes, ano) {
            try {
                // Buscar contas bancárias com filtro de período
                const contasParams = new URLSearchParams({action: 'list'});
                if (mes && mes !== 'todos') contasParams.set('mes', mes);
                if (ano && ano !== 'todos') contasParams.set('ano', ano);
                const contasResponse = await fetch(`../api/contas-bancarias.php?${contasParams.toString()}`);
                const contasData = await contasResponse.json();

                if (contasData.success && contasData.contas) {
                    dadosFinanceiros.contas = contasData.contas;
                }
            } catch (error) {
                console.error('Erro ao carregar contas:', error);
            }

            try {
                // Buscar transações (receitas e despesas) da tabela movimentacoes_financeiras
                const urlParams = new URLSearchParams();
                urlParams.set('action', 'transacoes');
                if (mes !== 'todos') urlParams.set('mes', mes);
                if (ano !== 'todos') urlParams.set('ano', ano);
                const resTransacoes = await fetch(`../api/financeiro.php?${urlParams.toString()}`);
                const dataTransacoes = await resTransacoes.json();
                if (dataTransacoes.success) {
                    dadosFinanceiros.transacoes = (dataTransacoes.transacoes || []).map(t => ({
                        tipo: t.tipo,
                        descricao: t.descricao,
                        valor: parseFloat(t.valor || 0),
                        data: t.data,
                        forma_pagamento: t.forma_pagamento || 'N/A',
                        categoria: t.categoria
                    }));
                }
            } catch (error) {
                console.error('Erro ao carregar transações:', error);
            }

            // Buscar custos de eventos e adicionar às transações
            try {
                const urlCustosEv = new URLSearchParams({ action: 'custos_eventos_list' });
                if (mes !== 'todos') urlCustosEv.set('mes', mes);
                if (ano !== 'todos') urlCustosEv.set('ano', ano);
                const resCustosEv = await fetch(`../api/custos.php?${urlCustosEv.toString()}`);
                const dataCustosEv = await resCustosEv.json();
                if (dataCustosEv.success && dataCustosEv.custos) {
                    dataCustosEv.custos.forEach(c => {
                        dadosFinanceiros.transacoes.push({
                            tipo: 'despesa',
                            descricao: `[Evento] ${c.descricao}`,
                            valor: parseFloat(c.valor),
                            data: c.data_custo || c.data,
                            forma_pagamento: 'N/A',
                            categoria: c.categoria
                        });
                    });
                }
            } catch (error) {
                console.error('Erro ao carregar custos de eventos:', error);
            }

            // Buscar custos gerais e adicionar às transações
            try {
                const resCustosGerais = await fetch('../api/custos.php?action=listar');
                const textCG = await resCustosGerais.text();
                if (textCG && textCG.trim() !== '') {
                    const dataCG = JSON.parse(textCG);
                    if (dataCG.success && dataCG.custos) {
                        const custosGeraisFiltrados = dataCG.custos.filter(c => {
                            const d = new Date(c.data_custo);
                            const matchMes = mes === 'todos' || d.getMonth() + 1 == parseInt(mes);
                            const matchAno = ano === 'todos' || d.getFullYear() == parseInt(ano);
                            return matchMes && matchAno;
                        });

                        custosGeraisFiltrados.forEach(c => {
                            dadosFinanceiros.transacoes.push({
                                tipo: 'despesa',
                                descricao: `[Custo Geral] ${c.descricao}`,
                                valor: parseFloat(c.valor),
                                data: c.data_custo,
                                forma_pagamento: 'N/A',
                                categoria: c.categoria
                            });
                        });
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar custos gerais:', error);
            }

            // Ordenar todas as transações por data
            dadosFinanceiros.transacoes.sort((a, b) => {
                const dA = new Date(a.data || '2000-01-01');
                const dB = new Date(b.data || '2000-01-01');
                return dB - dA;
            });

            // Calcular valores a receber e sinais recebidos baseado nos eventos
            try {
                const resEventos = await fetch('../api/eventos.php?action=list');
                const dataEventos = await resEventos.json();

                if (dataEventos.success && dataEventos.eventos) {
                    const valoresReceber = [];
                    const sinaisMap = {};
                    const receitasFormaMap = {};

                    dataEventos.eventos.forEach(evento => {
                        // Usar data_sinal (data do pagamento) com fallback para data_evento
                        const dataSinal = evento.data_sinal || evento.data_evento;
                        const dataRef = new Date(dataSinal);
                        const mesEvento = dataRef.getMonth() + 1;
                        const anoEvento = dataRef.getFullYear();

                        // Filtrar pelo mês/ano selecionado
                        if (parseInt(mes) && mesEvento !== parseInt(mes)) return;
                        if (parseInt(ano) && anoEvento !== parseInt(ano)) return;

                        // Sinais recebidos (por forma de pagamento)
                        const valorSinal = parseFloat(evento.valor_sinal || 0);
                        if (valorSinal > 0 && evento.status !== 'cancelado') {
                            const formaPag = evento.forma_pagamento_sinal || 'Não informado';
                            if (!sinaisMap[formaPag]) sinaisMap[formaPag] = { valor: 0, quantidade: 0 };
                            sinaisMap[formaPag].valor += valorSinal;
                            sinaisMap[formaPag].quantidade++;

                            // Receitas por forma
                            if (!receitasFormaMap[formaPag]) receitasFormaMap[formaPag] = { valor: 0 };
                            receitasFormaMap[formaPag].valor += valorSinal;
                        }

                        // Valores a receber: só eventos em andamento ou concluídos (não aguardando)
                        if (evento.status !== 'em_andamento' && evento.status !== 'concluido') return;

                        const valorTotal = parseFloat(evento.valor_total || 0);
                        const totalPago = parseFloat(evento.total_pago || evento.valor_sinal || 0);
                        const saldo = valorTotal - totalPago;

                        if (saldo > 0.01) {
                            valoresReceber.push({
                                valor: saldo,
                                cliente_nome: evento.cliente_nome || 'Cliente',
                                evento_data: evento.data_evento,
                                evento_tipo: evento.tipo || 'Evento',
                                vencimento: evento.data_vencimento || evento.data_evento
                            });
                        }
                    });

                    dadosFinanceiros.valoresReceber = valoresReceber;

                    // Converter sinais para array
                    dadosFinanceiros.sinaisRecebidos = Object.entries(sinaisMap).map(([forma, info]) => ({
                        forma_pagamento: forma,
                        valor: info.valor,
                        quantidade: info.quantidade
                    })).sort((a, b) => b.valor - a.valor);

                    // Converter receitas por forma para array
                    dadosFinanceiros.receitasPorForma = Object.entries(receitasFormaMap).map(([forma, info]) => ({
                        forma_pagamento: forma,
                        valor: info.valor
                    })).sort((a, b) => b.valor - a.valor);
                }
            } catch (error) {
                console.error('Erro ao carregar eventos para valores a receber:', error);
            }
        }
        
        // Atualizar toda interface
        function atualizarInterface() {
            atualizarStats();
            atualizarValoresReceber();
            atualizarTransacoes();
            atualizarSinaisRecebidos();
            atualizarReceitasPorForma();
            atualizarContasSaldo();
            atualizarContasGerenciar();
        }
        
        // Atualizar cards de estatísticas (6 cards como o original)
        function atualizarStats() {
            const totalReceitas = dadosFinanceiros.receitas?.reduce((sum, r) => sum + parseFloat(r.valor || 0), 0) || 0;
            const aReceber = dadosFinanceiros.valoresReceber?.reduce((sum, v) => sum + parseFloat(v.valor || 0), 0) || 0;

            // Separar custos gerais e custos de eventos para os cards separados
            const custosDespesas = dadosFinanceiros.custosGerais || 0;
            const custosEventos = dadosFinanceiros.custosEventosTotal || 0;
            const totalDespesasCompleto = custosDespesas + custosEventos;
            const lucro = totalReceitas - totalDespesasCompleto;

            document.getElementById('totalReceitas').textContent = formatarMoeda(totalReceitas);
            document.getElementById('valoresReceber').textContent = formatarMoeda(aReceber);
            document.getElementById('totalDespesas').textContent = formatarMoeda(custosDespesas);

            const elCustosEventos = document.getElementById('custosEventos');
            if (elCustosEventos) elCustosEventos.textContent = formatarMoeda(custosEventos);

            const elTotalDespesas = document.getElementById('totalDespesasCompleto');
            if (elTotalDespesas) elTotalDespesas.textContent = formatarMoeda(totalDespesasCompleto);

            const elLucro = document.getElementById('lucroLiquido');
            if (elLucro) {
                elLucro.textContent = formatarMoeda(lucro);
                elLucro.style.color = lucro >= 0 ? '#06D6A0' : '#EF476F';
            }
        }
        
        // Atualizar valores a receber (layout igual ao original)
        function atualizarValoresReceber() {
            const container = document.getElementById('listaValoresReceber');
            const valores = dadosFinanceiros.valoresReceber || [];

            if (valores.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #A0AEC0; padding: 20px;">✅ Não há valores a receber!</p>';
                document.getElementById('totalAReceber').textContent = 'R$ 0,00';
                return;
            }

            const total = valores.reduce((sum, v) => sum + parseFloat(v.valor || 0), 0);
            document.getElementById('totalAReceber').textContent = formatarMoeda(total);

            container.innerHTML = valores.map(v => {
                const vencimento = v.vencimento || v.evento_data || '';
                const isVencido = vencimento && new Date(vencimento + 'T00:00:00') < new Date();
                const corBorda = isVencido ? '#EF476F' : '#FFD23F';
                const corFundo = isVencido ? 'rgba(239, 71, 111, 0.1)' : 'rgba(255, 193, 7, 0.1)';

                return `
                <div style="background: ${corFundo}; border-left: 3px solid ${corBorda}; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: all 0.2s;"
                     onmouseover="this.style.transform='translateX(4px)'; this.style.background='rgba(255,193,7,0.15)'"
                     onmouseout="this.style.transform=''; this.style.background='${corFundo}'">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">${v.cliente_nome || 'Cliente'}</div>
                        <div style="font-size: 12px; color: #A0AEC0; margin-top: 2px;">📅 Vencimento: ${vencimento ? formatarData(vencimento) : 'Não definido'}${isVencido ? ' <span style="color: #EF476F; font-weight: 700;">⚠️ VENCIDO</span>' : ''}</div>
                        <div style="font-size: 11px; color: #A0AEC0; margin-top: 2px;">💰 Clique para registrar pagamento</div>
                    </div>
                    <div style="font-size: 18px; font-weight: 700; color: ${corBorda};">${formatarMoeda(v.valor)}</div>
                </div>`;
            }).join('');
        }
        
        // Atualizar transações
        function atualizarTransacoes() {
            const container = document.getElementById('listaTransacoes');
            const transacoes = dadosFinanceiros.transacoes || [];
            const filtroTipo = document.getElementById('filtroTipoTransacao')?.value;
            
            let transacoesFiltradas = transacoes;
            if (filtroTipo) {
                transacoesFiltradas = transacoes.filter(t => t.tipo === filtroTipo);
            }
            
            if (transacoesFiltradas.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><div class="empty-state-title">Nenhuma transação encontrada</div></div>';
                return;
            }
            
            container.innerHTML = transacoesFiltradas.slice(0, 10).map(t => {
                const isReceita = t.tipo === 'receita';
                return `
                    <div class="list-item">
                        <div class="item-info">
                            <div class="item-title">${isReceita ? '<i class="fas fa-arrow-up" style="color: #06D6A0;"></i>' : '<i class="fas fa-arrow-down" style="color: #EF476F;"></i>'} ${t.descricao}</div>
                            <div class="item-subtitle">${formatarData(t.data)} - ${t.forma_pagamento || 'N/A'}</div>
                        </div>
                        <div class="item-value ${isReceita ? 'positivo' : 'negativo'}">${isReceita ? '+' : '-'} ${formatarMoeda(Math.abs(t.valor))}</div>
                    </div>
                `;
            }).join('');
        }
        
        // Atualizar sinais recebidos
        function atualizarSinaisRecebidos() {
            const container = document.getElementById('sinaisRecebidos');
            const sinais = dadosFinanceiros.sinaisRecebidos || [];
            
            if (sinais.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-hand-holding-usd"></i><div class="empty-state-title">Nenhum sinal recebido</div></div>';
                return;
            }
            
            const total = sinais.reduce((sum, s) => sum + parseFloat(s.valor || 0), 0);
            
            container.innerHTML = sinais.map(s => `
                <div class="forma-pag-item">
                    <div class="forma-pag-info">
                        <div class="forma-pag-icon"><i class="fas fa-${getIconeFormaPagamento(s.forma_pagamento)}"></i></div>
                        <div>
                            <div class="forma-pag-nome">${s.forma_pagamento}</div>
                            <div class="forma-pag-count">${s.quantidade} pagamentos</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="forma-pag-valor">${formatarMoeda(s.valor)}</div>
                        <div class="forma-pag-percent" style="font-size: 14px;">${((s.valor / total) * 100).toFixed(1)}%</div>
                    </div>
                </div>
            `).join('');
        }
        
        // Atualizar receitas por forma
        function atualizarReceitasPorForma() {
            const container = document.getElementById('receitasPorForma');
            const receitas = dadosFinanceiros.receitasPorForma || [];
            
            if (receitas.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-chart-pie"></i><div class="empty-state-title">Nenhuma receita registrada</div></div>';
                return;
            }
            
            const total = receitas.reduce((sum, r) => sum + parseFloat(r.valor || 0), 0);
            
            container.innerHTML = receitas.map(r => `
                <div class="forma-pag-item">
                    <div class="forma-pag-info">
                        <div class="forma-pag-icon"><i class="fas fa-${getIconeFormaPagamento(r.forma_pagamento)}"></i></div>
                        <div class="forma-pag-nome">${r.forma_pagamento}</div>
                    </div>
                    <div style="text-align: right;">
                        <div class="forma-pag-valor">${formatarMoeda(r.valor)}</div>
                        <div class="forma-pag-percent">${((r.valor / total) * 100).toFixed(1)}%</div>
                    </div>
                </div>
            `).join('');
        }
        
        // Atualizar contas com saldo
        function atualizarContasSaldo() {
            const container = document.getElementById('listaContasSaldo');
            const contas = dadosFinanceiros.contas || [];
            
            if (contas.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-university"></i><div class="empty-state-title">Nenhuma conta cadastrada</div></div>';
                return;
            }
            
            container.innerHTML = contas.map(c => `
                <div class="conta-card">
                    <div class="conta-header">
                        <div>
                            <div class="conta-nome"><i class="fas fa-university"></i> ${c.nome}</div>
                            <div class="conta-tipo">${c.banco} - ${c.tipo}</div>
                        </div>
                        <div class="conta-saldo">
                            <div class="conta-saldo-label">Saldo do Período</div>
                            <div class="conta-saldo-valor">${formatarMoeda(c.saldo || 0)}</div>
                        </div>
                    </div>
                    <div class="conta-stats">
                        <div class="conta-stat">
                            <div class="conta-stat-label">Total Recebido</div>
                            <div class="conta-stat-value" style="color: #06D6A0;">${formatarMoeda(c.total_recebido || 0)}</div>
                        </div>
                        <div class="conta-stat">
                            <div class="conta-stat-label">Total Pago</div>
                            <div class="conta-stat-value" style="color: #EF476F;">${formatarMoeda(c.total_pago || 0)}</div>
                        </div>
                        <div class="conta-stat">
                            <div class="conta-stat-label">Saldo Atual</div>
                            <div class="conta-stat-value" style="color: #06D6A0;">${formatarMoeda(c.saldo_atual || 0)}</div>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 15px; color: #A0AEC0;">Transações desta Conta</h4>
                        <div id="transacoes-conta-${c.id}"></div>
                    </div>
                </div>
            `).join('');
            
            // Carregar transações de cada conta
            contas.forEach(c => {
                carregarTransacoesConta(c.id);
            });
        }
        
        // Carregar transações de uma conta específica
        async function carregarTransacoesConta(contaId) {
            try {
                const mes = document.getElementById('filtroMes').value;
                const ano = document.getElementById('filtroAno').value;
                
                const response = await fetch(`../api/financeiro.php?action=movimentacoes_list&conta_id=${contaId}&mes=${mes}&ano=${ano}`);
                const data = await response.json();
                
                const container = document.getElementById(`transacoes-conta-${contaId}`);
                
                if (data.success && data.movimentacoes && data.movimentacoes.length > 0) {
                    container.innerHTML = data.movimentacoes.slice(0, 5).map(t => {
                        const isReceita = t.tipo === 'receita';
                        return `
                            <div class="list-item" style="margin-bottom: 10px; padding: 15px;">
                                <div class="item-info">
                                    <div class="item-title" style="font-size: 14px;">${isReceita ? '+' : '-'} ${t.descricao}</div>
                                    <div class="item-subtitle">${formatarData(t.data_movimentacao)} • ${t.categoria || 'N/A'}</div>
                                </div>
                                <div class="item-value ${isReceita ? 'positivo' : 'negativo'}" style="font-size: 16px;">${formatarMoeda(Math.abs(t.valor))}</div>
                            </div>
                        `;
                    }).join('');
                } else {
                    container.innerHTML = '<div style="text-align: center; color: #5A6C8F; padding: 20px; font-size: 13px;">Nenhuma transação neste período</div>';
                }
            } catch (error) {
                console.error('Erro ao carregar transações da conta:', error);
            }
        }
        
        // Atualizar contas para gerenciar
        function atualizarContasGerenciar() {
            const container = document.getElementById('listaContasGerenciar');
            const contas = dadosFinanceiros.contas || [];
            
            if (contas.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-university"></i><div class="empty-state-title">Nenhuma conta cadastrada</div><p>Clique em "NOVA CONTA" para adicionar</p></div>';
                return;
            }
            
            container.innerHTML = contas.map(c => `
                <div class="list-item">
                    <div class="item-info">
                        <div class="item-title"><i class="fas fa-university"></i> ${c.nome}</div>
                        <div class="item-subtitle">${c.banco} - ${c.tipo}</div>
                    </div>
                    <div class="actions">
                        <button class="btn-action btn-extrato" onclick="verExtrato(${c.id})">
                            <i class="fas fa-file-alt"></i> Extrato
                        </button>
                        <button class="btn-action btn-editar" onclick="editarConta(${c.id})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn-action btn-excluir" onclick="excluirConta(${c.id})">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                        <button class="btn-action btn-desativar" onclick="desativarConta(${c.id})">
                            <i class="fas fa-ban"></i> Desativar
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        // Mudar aba
        function mudarAba(indice) {
            const botoes = document.querySelectorAll('.tab-button');
            const conteudos = document.querySelectorAll('.tab-content');
            
            botoes.forEach((btn, i) => {
                if (i === indice) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            
            conteudos.forEach((content, i) => {
                if (i === indice) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }
        
        // Aplicar filtros
        function aplicarFiltros() {
            carregarDadosFinanceiros();
        }
        
        // Limpar filtros
        function limparFiltros() {
            const mesAtual = new Date().getMonth() + 1;
            const anoAtual = new Date().getFullYear();
            document.getElementById('filtroMes').value = mesAtual;
            document.getElementById('filtroAno').value = anoAtual;
            document.getElementById('filtroVisualizacao').value = 'mensal';
            if (document.getElementById('filtroTipoTransacao')) {
                document.getElementById('filtroTipoTransacao').value = '';
            }
            carregarDadosFinanceiros();
        }
        
        // Modal de conta
        function abrirModalNovaConta() {
            document.getElementById('modalContaTitulo').textContent = 'Nova Conta Bancária';
            document.getElementById('formConta').reset();
            document.getElementById('contaId').value = '';
            document.getElementById('modalConta').classList.add('active');
        }
        
        function fecharModalConta() {
            document.getElementById('modalConta').classList.remove('active');
        }
        
        async function salvarConta(event) {
            event.preventDefault();
            
            const contaId = document.getElementById('contaId').value;
            
            // Montar objeto de dados
            const dados = {
                nome: document.getElementById('contaNome').value,
                banco: document.getElementById('contaBanco').value,
                tipo: document.getElementById('contaTipo').value,
                saldo_inicial: parseFloat(document.getElementById('contaSaldoInicial').value) || 0
            };
            
            const action = contaId ? 'update' : 'create';
            if (contaId) {
                dados.id = contaId;
            }
            
            try {
                const response = await fetch(`../api/contas-bancarias.php?action=${action}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(dados)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(contaId ? 'Conta atualizada com sucesso!' : 'Conta criada com sucesso!');
                    fecharModalConta();
                    carregarDadosFinanceiros();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar conta');
            }
        }
        
        async function editarConta(id) {
            try {
                // Como não temos endpoint específico, vamos buscar da lista
                const conta = dadosFinanceiros.contas.find(c => c.id == id);
                
                if (conta) {
                    document.getElementById('modalContaTitulo').textContent = 'Editar Conta Bancária';
                    document.getElementById('contaId').value = conta.id;
                    document.querySelector('[name="nome"]').value = conta.nome;
                    document.querySelector('[name="banco"]').value = conta.banco;
                    document.querySelector('[name="tipo"]').value = conta.tipo;
                    document.querySelector('[name="saldo_inicial"]').value = conta.saldo_inicial || 0;
                    document.getElementById('modalConta').classList.add('active');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar conta');
            }
        }
        
        async function excluirConta(id) {
            if (!confirm('Deseja realmente excluir esta conta? Esta ação não pode ser desfeita.')) {
                return;
            }
            
            try {
                const response = await fetch(`../api/contas-bancarias.php?action=delete&id=${id}`, {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Conta excluída com sucesso!');
                    carregarDadosFinanceiros();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir conta');
            }
        }
        
        async function desativarConta(id) {
            if (!confirm('Deseja desativar esta conta?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'desativar_conta');
                formData.append('id', id);
                
                const response = await fetch('../api/financeiro.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Conta desativada com sucesso!');
                    carregarDadosFinanceiros();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao desativar conta');
            }
        }
        
        function verExtrato(contaId) {
            alert('Funcionalidade de extrato em desenvolvimento');
            // Redirecionar para página de extrato ou abrir modal
        }
        
        // Funções auxiliares
        function formatarMoeda(valor) {
            return 'R$ ' + parseFloat(valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        function formatarData(data) {
            if (!data) return '';
            const d = new Date(data + 'T00:00:00');
            return d.toLocaleDateString('pt-BR');
        }
        
        function getIconeFormaPagamento(forma) {
            const icones = {
                'PIX': 'qrcode',
                'Crédito': 'credit-card',
                'Débito': 'credit-card',
                'Dinheiro': 'money-bill',
                'Transferência': 'exchange-alt',
                'Boleto': 'barcode'
            };
            return icones[forma] || 'dollar-sign';
        }
    </script>

<?php include '../includes/footer.php'; ?>

<script>
// Fix mobile stats grid via JS
(function(){
    if(window.innerWidth <= 768){
        var grid = document.querySelector('.fin-stats .stats-grid');
        if(grid){
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(2,1fr);gap:10px;max-width:100%;overflow:hidden;margin-bottom:30px;';
        }
        document.querySelectorAll('.fin-stats .stat-card').forEach(function(c){
            c.style.minWidth = '0';
            c.style.overflow = 'hidden';
        });
        document.querySelectorAll('.fin-stats .stat-info').forEach(function(i){
            i.style.minWidth = '0';
            i.style.overflow = 'hidden';
        });
    }
})();
</script>

<!-- Mobile fix final - último na cascata -->
<style>
@media (max-width: 768px) {
    .fin-stats .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }
    .fin-stats .stat-card {
        min-width: 0 !important;
        overflow: hidden !important;
        padding: 10px 8px !important;
        gap: 6px !important;
    }
    .fin-stats .stat-card .stat-icon {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        font-size: 15px !important;
        border-radius: 8px !important;
    }
    .fin-stats .stat-card .stat-info {
        min-width: 0 !important;
        overflow: hidden !important;
    }
    .fin-stats .stat-card .stat-info .stat-value {
        font-size: 13px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .fin-stats .stat-card .stat-info .stat-label {
        font-size: 8px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        letter-spacing: 0.3px !important;
    }
}
</style>
</body>
</html>
