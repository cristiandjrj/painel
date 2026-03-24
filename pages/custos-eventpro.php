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
    <title>Gestão de Custos - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Syne', sans-serif; background: #0A0E27; color: #FFFFFF; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }
        
        /* Header - usa classes do CSS externo (page-header, page-title, page-subtitle) */
        
        .filters-section { background: #141B3D; border: 1px solid #2D3561; border-radius: 16px; padding: 20px; margin-bottom: 25px; }
        .filters-row { display: grid; grid-template-columns: 180px 120px 150px 100px; gap: 12px; align-items: end; }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-label { font-size: 10px; font-weight: 700; color: #A0AEC0; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px; }
        .filter-select { padding: 10px 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid #2D3561; border-radius: 8px; color: white; font-size: 13px; font-weight: 500; cursor: pointer; }
        .filter-select:focus { outline: none; border-color: #FF6B35; }
        .btn-clear { padding: 10px 16px; background: rgba(239, 71, 111, 0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; white-space: nowrap; font-size: 12px; align-self: flex-end; }
        .btn-clear:hover { background: rgba(239, 71, 111, 0.2); }
        
        .tabs-container { margin-bottom: 30px; }
        .tabs-header { display: flex; gap: 10px; border-bottom: 2px solid #2D3561; }
        .tab-button { padding: 15px 30px; background: transparent; border: none; color: #A0AEC0; font-weight: 600; font-size: 15px; cursor: pointer; position: relative; transition: all 0.2s; border-radius: 8px 8px 0 0; display: flex; align-items: center; gap: 10px; }
        .tab-button:hover { color: white; background: rgba(255, 255, 255, 0.05); }
        .tab-button.active { color: #FF6B35; background: rgba(255, 107, 53, 0.1); }
        .tab-button.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: #FF6B35; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        /* Stats - base usa CSS externo, variantes específicas abaixo */
        .stat-card::before { height: 4px; }
        .stat-card.total::before { background: linear-gradient(90deg, #EF476F, #FF6B35); }
        .stat-card.combustivel::before { background: linear-gradient(90deg, #FFD23F, #FF6B35); }
        .stat-card.impostos::before { background: linear-gradient(90deg, #1877F2, #06D6A0); }
        .stat-card.marketing::before { background: linear-gradient(90deg, #FF6B35, #EF476F); }
        .stat-card.outros::before { background: linear-gradient(90deg, #A0AEC0, #5A6C8F); }
        /* stat-icon usa CSS externo */
        .stat-card.total .stat-icon { background: rgba(239, 71, 111, 0.1); color: #EF476F; }
        .stat-card.combustivel .stat-icon { background: rgba(255, 210, 63, 0.1); color: #FFD23F; }
        .stat-card.impostos .stat-icon { background: rgba(24, 119, 242, 0.1); color: #1877F2; }
        .stat-card.marketing .stat-icon { background: rgba(255, 107, 53, 0.1); color: #FF6B35; }
        .stat-card.outros .stat-icon { background: rgba(160, 174, 192, 0.1); color: #A0AEC0; }
        /* stat-label e stat-value usam CSS externo */
        
        .section { background: #141B3D; border: 1px solid #2D3561; border-radius: 16px; padding: 30px; margin-bottom: 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .section-title { font-size: 20px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px; }
        .section-value { font-size: 24px; font-weight: 800; color: #EF476F; }
        .btn-new { padding: 12px 24px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-new:hover { background: #E85A2B; transform: scale(1.05); }
        
        .custo-item { background: rgba(255, 255, 255, 0.02); border: 1px solid #2D3561; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s; }
        .custo-item:hover { background: rgba(255, 255, 255, 0.05); border-color: #FF6B35; }
        .custo-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .custo-info { flex: 1; margin-left: 15px; }
        .custo-nome { font-weight: 600; font-size: 15px; margin-bottom: 5px; }
        .custo-detalhes { font-size: 13px; color: #A0AEC0; }
        .custo-valor { font-size: 20px; font-weight: 700; color: #EF476F; margin-right: 15px; }
        .custo-actions { display: flex; gap: 8px; }
        .btn-action { padding: 8px 12px; border-radius: 6px; font-size: 13px; cursor: pointer; border: none; transition: all 0.2s; }
        .btn-editar { background: rgba(255, 210, 63, 0.1); color: #FFD23F; }
        .btn-excluir { background: rgba(239, 71, 111, 0.1); color: #EF476F; }
        .btn-action:hover { transform: scale(1.1); }
        
        .ajudante-section { background: rgba(6, 214, 160, 0.05); border: 1px solid rgba(6, 214, 160, 0.2); border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        .ajudante-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .ajudante-title { font-size: 18px; font-weight: 700; color: #06D6A0; display: flex; align-items: center; gap: 10px; }
        .ajudante-card { background: rgba(255, 255, 255, 0.02); border: 1px solid #2D3561; border-radius: 12px; padding: 20px; margin-bottom: 15px; cursor: pointer; transition: all 0.2s; }
        .ajudante-card:hover { background: rgba(6, 214, 160, 0.05); border-color: #06D6A0; }
        .ajudante-card.expanded { border-color: #06D6A0; }
        .ajudante-header-card { display: flex; justify-content: space-between; align-items: center; }
        .ajudante-nome { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .ajudante-stats { display: flex; gap: 30px; align-items: center; }
        .ajudante-stat { text-align: center; }
        .ajudante-stat-label { font-size: 11px; color: #A0AEC0; margin-bottom: 3px; }
        .ajudante-stat-value { font-size: 14px; font-weight: 700; }
        .ajudante-eventos { margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: none; }
        .ajudante-card.expanded .ajudante-eventos { display: block; }
        .evento-item { background: rgba(0, 0, 0, 0.2); border-radius: 8px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .evento-info-mini { flex: 1; }
        .evento-nome { font-size: 14px; font-weight: 600; margin-bottom: 5px; }
        .evento-detalhes-mini { font-size: 12px; color: #A0AEC0; }
        .evento-valor { font-size: 16px; font-weight: 700; color: #06D6A0; }
        
        .evento-select { background: #141B3D; border: 1px solid #2D3561; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
        .evento-select-label { font-size: 13px; font-weight: 600; color: #A0AEC0; margin-bottom: 10px; }
        .evento-select-dropdown { width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid #2D3561; border-radius: 8px; color: white; font-size: 14px; max-height: 300px; overflow-y: auto; }
        .evento-select-dropdown:focus { outline: none; border-color: #FF6B35; }
        .evento-select-dropdown option { background: #141B3D; padding: 10px; }
        
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px; overflow-y: auto; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: #141B3D; border-radius: 16px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid #2D3561; }
        .modal-header { background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .modal-title { font-size: 20px; font-weight: 700; color: white; }
        .modal-close { background: rgba(255, 255, 255, 0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white; }
        .modal-body { padding: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #A0AEC0; font-size: 13px; text-transform: uppercase; }
        .form-input, .form-select, .form-textarea { width: 100%; padding: 12px 16px; background: rgba(255, 255, 255, 0.05); border: 1px solid #2D3561; border-radius: 8px; color: white; font-size: 14px; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #FF6B35; }
        .form-textarea { resize: vertical; min-height: 80px; }
        .modal-footer { padding: 20px 24px; border-top: 1px solid #2D3561; display: flex; gap: 12px; justify-content: flex-end; }
        .btn-cancel { padding: 12px 24px; background: rgba(239, 71, 111, 0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-submit { padding: 12px 24px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #5A6C8F; }
        .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
        .empty-state-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
            .filters-row { grid-template-columns: 1fr 1fr !important; gap: 10px !important; }
            .ajudante-stats { flex-direction: column; gap: 10px; }
            .custo-item { flex-direction: column !important; gap: 12px !important; align-items: flex-start !important; padding: 16px !important; }
            .custo-valor { margin-right: 0 !important; }
            .custo-actions { width: 100% !important; }
            .section { padding: 20px 16px !important; }
            .section-header { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .ajudante-header { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .ajudante-header-card { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .evento-item { flex-direction: column !important; gap: 8px !important; align-items: flex-start !important; }
            .tabs-header { overflow-x: auto !important; -webkit-overflow-scrolling: touch; gap: 4px !important; }
            .tab-button { padding: 10px 14px !important; font-size: 12px !important; white-space: nowrap !important; }
            .modal-content { max-width: 100% !important; margin: 10px !important; }
            .form-row { grid-template-columns: 1fr !important; }
            .modal-footer { flex-direction: column !important; }
            .modal-footer .btn-cancel, .modal-footer .btn-submit { width: 100% !important; text-align: center !important; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .filters-row { grid-template-columns: 1fr !important; }
            .tab-button { padding: 8px 10px !important; font-size: 11px !important; }
        }
    </style>
</head>
<body>
    <?php include '../includes/menu.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">📊 Gestão de Custos</h1>
            <p class="page-subtitle">Controle todos os gastos da empresa e dos eventos</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-label">Total do Mês</div>
                <div class="stat-value" id="totalMesGeral">R$ 0,00</div>
            </div>

            <div class="stat-card combustivel">
                <div class="stat-icon"><i class="fas fa-gas-pump"></i></div>
                <div class="stat-label">Combustível</div>
                <div class="stat-value" id="totalCombustivel">R$ 0,00</div>
            </div>

            <div class="stat-card impostos">
                <div class="stat-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="stat-label">Impostos</div>
                <div class="stat-value" id="totalImpostos">R$ 0,00</div>
            </div>

            <div class="stat-card marketing">
                <div class="stat-icon"><i class="fas fa-bullhorn"></i></div>
                <div class="stat-label">Marketing</div>
                <div class="stat-value" id="totalMarketing">R$ 0,00</div>
            </div>

            <div class="stat-card outros">
                <div class="stat-icon"><i class="fas fa-ellipsis-h"></i></div>
                <div class="stat-label">Outros</div>
                <div class="stat-value" id="totalOutros">R$ 0,00</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar"></i> Mês</label>
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
                </div>
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar-alt"></i> Ano</label>
                    <select class="filter-select" id="filtroAno" onchange="aplicarFiltros()">
                        <option value="2026">2026</option>
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
                    <i class="fas fa-times"></i> LIMPAR
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-button active" onclick="mudarAba(0)">
                    <i class="fas fa-building"></i> Custos Gerais da Empresa
                </button>
                <button class="tab-button" onclick="mudarAba(1)">
                    <i class="fas fa-calendar-check"></i> Custos por Evento
                </button>
            </div>

            <!-- ABA 1: CUSTOS GERAIS DA EMPRESA -->
            <div class="tab-content active" id="tab-custos-gerais">

                <!-- Histórico -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-history"></i> Histórico de Custos Gerais</h2>
                        <button class="btn-new" onclick="abrirModalNovoCustoGeral()">
                            <i class="fas fa-plus"></i> NOVO CUSTO
                        </button>
                    </div>
                    <div id="listaCustosGerais"></div>
                </div>
                
            </div>
            
            <!-- ABA 2: CUSTOS POR EVENTO -->
            <div class="tab-content" id="tab-custos-eventos">
                
                <!-- Cards Totais -->
                <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="stat-card total">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-label">Total Geral de Custos</div>
                        <div class="stat-value" id="totalGeralEventos">R$ 0,00</div>
                        <div style="font-size: 13px; color: #A0AEC0; margin-top: 5px;">
                            Soma de todos os custos de eventos
                        </div>
                        <div style="font-size: 13px; color: #FFD23F; margin-top: 3px;">
                            <span id="totalEventosComCustos">0</span> eventos com custos
                        </div>
                    </div>
                    
                    <div class="stat-card combustivel">
                        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="stat-label">Custos do Mês</div>
                        <div class="stat-value" id="totalMesEventos">R$ 0,00</div>
                        <div style="font-size: 13px; color: #A0AEC0; margin-top: 5px;">
                            Custos de eventos deste mês
                        </div>
                        <div style="font-size: 13px; color: #FFD23F; margin-top: 3px;">
                            <span id="eventosMes">0</span> eventos
                        </div>
                    </div>
                </div>
                
                <!-- Análise por Categoria -->
                <div class="ajudante-section">
                    <div class="ajudante-header">
                        <div class="ajudante-title">
                            <i class="fas fa-chart-pie"></i> Análise Detalhada por Categoria
                        </div>
                        <select class="filter-select" style="width: auto;" id="filtroCategoria" onchange="aplicarFiltros()">
                            <option value="ajudantes">👤 Ajudantes</option>
                            <option value="estacionamento">🚗 Estacionamento</option>
                            <option value="alimentacao">🍽️ Alimentação</option>
                            <option value="transporte">🚕 Transporte</option>
                            <option value="outros">📋 Outros</option>
                        </select>
                    </div>
                    <div style="color: #A0AEC0; font-size: 13px; margin-bottom: 15px;">
                        Visualize gastos agrupados por tipo de custo
                    </div>
                    <div id="listaCategoriasEventos"></div>
                </div>
                
                <!-- Histórico -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Histórico de Custos de Eventos</h2>
                        <div class="section-value" id="totalHistoricoEventos">R$ 0,00</div>
                    </div>
                    <div id="listaCustosEventos"></div>
                    <div id="paginacaoCustos" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;"></div>
                </div>
                
                <!-- Detalhes por Evento -->
                <div class="section">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-calendar-day"></i> Detalhes por Evento</h2>
                    </div>
                    
                    <div class="evento-select">
                        <div class="evento-select-label">
                            ✅ Todos os Eventos <span style="color: #5A6C8F; margin-left: 10px;">Selecione um evento específico...</span>
                        </div>
                        <select class="evento-select-dropdown" id="eventoDetalhe" onchange="carregarDetalhesEvento()">
                            <option value="">Todos os Eventos</option>
                        </select>
                    </div>
                    
                    <div id="detalhesEventoSelecionado" style="display: none;">
                        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="stat-card marketing">
                                <div class="stat-label">Valor do Evento</div>
                                <div class="stat-value" style="color: #FF6B35;" id="detalheValorEvento">R$ 0,00</div>
                            </div>
                            <div class="stat-card total">
                                <div class="stat-label">Total em Custos</div>
                                <div class="stat-value" style="color: #EF476F;" id="detalheTotalCustos">R$ 0,00</div>
                            </div>
                            <div class="stat-card combustivel">
                                <div class="stat-label">Lucro</div>
                                <div class="stat-value" style="color: #06D6A0;" id="detalheLucro">R$ 0,00</div>
                                <div style="font-size: 13px; color: #A0AEC0; margin-top: 5px;" id="detalheLucroPercent">0%</div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 25px;">
                            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 15px; color: #A0AEC0;">
                                <i class="fas fa-list"></i> Custos Registrados
                            </h3>
                            <div id="detalheCustosLista"></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Modal Novo Custo Geral -->
    <div class="modal-overlay" id="modalCustoGeral">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalCustoGeralTitulo">Novo Custo Geral</h3>
                <button class="modal-close" onclick="fecharModalCustoGeral()">&times;</button>
            </div>
            <form id="formCustoGeral" onsubmit="salvarCustoGeral(event)">
                <div class="modal-body">
                    <input type="hidden" id="custoGeralId" name="id">
                    
                    <div class="form-group">
                        <label class="form-label">Descrição *</label>
                        <input type="text" class="form-input" name="descricao" required placeholder="Ex: Retirada de Lucro">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Categoria *</label>
                        <select class="form-select" name="categoria" required>
                            <option value="">Selecione...</option>
                            <option value="combustivel">🚗 Combustível</option>
                            <option value="impostos">📄 Impostos</option>
                            <option value="marketing">📢 Marketing</option>
                            <option value="aluguel">🏢 Aluguel</option>
                            <option value="salarios">💰 Salários</option>
                            <option value="manutencao">🔧 Manutenção</option>
                            <option value="equipamentos">🎛️ Equipamentos</option>
                            <option value="outros">📋 Outros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Valor (R$) *</label>
                        <input type="number" step="0.01" class="form-input" name="valor" required placeholder="0,00">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Forma de Pagamento *</label>
                        <select class="form-select" name="forma_pagamento" required>
                            <option value="">Selecione...</option>
                            <option value="dinheiro">💵 Dinheiro</option>
                            <option value="pix">📱 PIX</option>
                            <option value="credito">💳 Cartão de Crédito</option>
                            <option value="debito">💳 Cartão de Débito</option>
                            <option value="transferencia">🏦 Transferência</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Conta Bancária *</label>
                        <select class="form-select" name="conta_bancaria_id" id="selectContaBancaria" required>
                            <option value="">Carregando contas...</option>
                        </select>
                        <div style="font-size: 11px; color: #A0AEC0; margin-top: 5px;">
                            💡 O valor será deduzido da conta selecionada
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Data *</label>
                        <input type="date" class="form-input" name="data_custo" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea class="form-textarea" name="observacoes" placeholder="Detalhes adicionais..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="fecharModalCustoGeral()">Cancelar</button>
                    <button type="submit" class="btn-submit">Salvar Custo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Custo de Evento -->
    <div class="modal-overlay" id="modalCustoEvento">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Custo de Evento</h3>
                <button class="modal-close" onclick="fecharModalCustoEvento()">&times;</button>
            </div>
            <form id="formCustoEvento" onsubmit="salvarCustoEvento(event)">
                <div class="modal-body">
                    <input type="hidden" id="custoEventoId" name="id">

                    <div class="form-group">
                        <label class="form-label">Descrição *</label>
                        <input type="text" class="form-input" id="custoEventoDescricao" name="descricao" required placeholder="Ex: Estacionamento">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Categoria *</label>
                        <select class="form-select" id="custoEventoCategoria" name="categoria" required>
                            <option value="">Selecione...</option>
                            <option value="ajudantes">👤 Ajudantes</option>
                            <option value="estacionamento">🚗 Estacionamento</option>
                            <option value="alimentacao">🍽️ Alimentação</option>
                            <option value="transporte">🚕 Transporte</option>
                            <option value="combustivel">⛽ Combustível</option>
                            <option value="equipamentos">🎛️ Equipamentos</option>
                            <option value="outros">📋 Outros</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Valor (R$) *</label>
                        <input type="number" step="0.01" class="form-input" id="custoEventoValor" name="valor" required placeholder="0,00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Forma de Pagamento</label>
                        <select class="form-select" id="custoEventoFormaPagamento" name="forma_pagamento">
                            <option value="">Selecione...</option>
                            <option value="dinheiro">💵 Dinheiro</option>
                            <option value="pix">📱 PIX</option>
                            <option value="credito">💳 Cartão de Crédito</option>
                            <option value="debito">💳 Cartão de Débito</option>
                            <option value="transferencia">🏦 Transferência</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Conta Bancária</label>
                        <select class="form-select" id="selectContaBancariaEvento" name="conta_bancaria_id">
                            <option value="">Carregando contas...</option>
                        </select>
                        <div style="font-size: 11px; color: #A0AEC0; margin-top: 5px;">
                            💡 O valor será deduzido da conta selecionada
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Data *</label>
                        <input type="date" class="form-input" id="custoEventoData" name="data_custo" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea class="form-textarea" id="custoEventoObservacoes" name="observacoes" placeholder="Detalhes adicionais..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="fecharModalCustoEvento()">Cancelar</button>
                    <button type="submit" class="btn-submit">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variáveis globais
        let dadosCustos = {
            custosGerais: [],
            custosEventos: [],
            eventos: [],
            ajudantes: []
        };
        
        let paginaAtual = 1;
        let itensPorPagina = 10;
        
        // Carregar dados ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            const mesAtual = new Date().getMonth() + 1;
            const anoAtual = new Date().getFullYear();
            document.getElementById('filtroMes').value = '';
            document.getElementById('filtroAno').value = anoAtual;
            
            carregarDadosCustos();
            carregarContasBancarias();
        });
        
        // Carregar contas bancárias para os selects (modal geral e modal evento)
        async function carregarContasBancarias() {
            try {
                const response = await fetch('../api/contas-bancarias.php?action=list');
                const data = await response.json();

                const opcoesContas = (data.success && data.contas && data.contas.length > 0)
                    ? '<option value="">Selecione uma conta...</option>' +
                      data.contas.filter(c => c.ativo).map(c =>
                          `<option value="${c.id}">🏦 ${c.nome} - Saldo: ${formatarMoeda(c.saldo_atual || 0)}</option>`
                      ).join('')
                    : '<option value="">Nenhuma conta ativa</option>';

                document.getElementById('selectContaBancaria').innerHTML = opcoesContas;
                document.getElementById('selectContaBancariaEvento').innerHTML =
                    '<option value="">(opcional) Selecione uma conta...</option>' +
                    (data.success && data.contas ? data.contas.filter(c => c.ativo).map(c =>
                        `<option value="${c.id}">🏦 ${c.nome} - Saldo: ${formatarMoeda(c.saldo_atual || 0)}</option>`
                    ).join('') : '');
            } catch (error) {
                console.error('Erro ao carregar contas:', error);
                document.getElementById('selectContaBancaria').innerHTML = '<option value="">Erro ao carregar contas</option>';
                document.getElementById('selectContaBancariaEvento').innerHTML = '<option value="">Erro ao carregar contas</option>';
            }
        }
        
        // Carregar dados da API
        async function carregarDadosCustos() {
            try {
                const mes = document.getElementById('filtroMes').value;
                const ano = document.getElementById('filtroAno').value;
                
                // Buscar custos gerais
                const resGerais = await fetch(`../api/custos.php?action=custos_empresa_list&mes=${mes}&ano=${ano}`);
                const dataGerais = await resGerais.json();
                if (dataGerais.success) {
                    dadosCustos.custosGerais = dataGerais.custos || [];
                }
                
                // Buscar custos de eventos
                const resEventos = await fetch(`../api/custos.php?action=custos_eventos_list&mes=${mes}&ano=${ano}`);
                const dataEventos = await resEventos.json();
                if (dataEventos.success) {
                    dadosCustos.custosEventos = dataEventos.custos || [];
                }
                
                // Buscar lista de eventos
                const resEventosList = await fetch(`../api/eventos.php?action=list`);
                const dataEventosList = await resEventosList.json();
                if (dataEventosList.success) {
                    dadosCustos.eventos = dataEventosList.eventos || [];
                    atualizarDropdownEventos();
                }
                
                atualizarInterface();
            } catch (error) {
                console.error('Erro ao carregar dados:', error);
            }
        }
        
        // Atualizar toda interface
        function atualizarInterface() {
            atualizarStatsCustosGerais();
            atualizarListaCustosGerais();
            atualizarStatsCustosEventos();
            atualizarCategoriasEventos();
            atualizarHistoricoCustosEventos();
        }
        
        // Atualizar stats de custos gerais
        function atualizarStatsCustosGerais() {
            const custos = dadosCustos.custosGerais;
            
            const totalGeral = custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const combustivel = custos.filter(c => c.categoria === 'combustivel').reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const impostos = custos.filter(c => c.categoria === 'impostos').reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const marketing = custos.filter(c => c.categoria === 'marketing').reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const outros = custos.filter(c => !['combustivel', 'impostos', 'marketing'].includes(c.categoria)).reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            
            document.getElementById('totalMesGeral').textContent = formatarMoeda(totalGeral);
            document.getElementById('totalCombustivel').textContent = formatarMoeda(combustivel);
            document.getElementById('totalImpostos').textContent = formatarMoeda(impostos);
            document.getElementById('totalMarketing').textContent = formatarMoeda(marketing);
            document.getElementById('totalOutros').textContent = formatarMoeda(outros);
        }
        
        // Atualizar lista de custos gerais
        function atualizarListaCustosGerais() {
            const container = document.getElementById('listaCustosGerais');
            const custos = dadosCustos.custosGerais;
            
            if (custos.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><div class="empty-state-title">Nenhum custo registrado</div><p>Clique em "NOVO CUSTO" para adicionar</p></div>';
                return;
            }
            
            container.innerHTML = custos.map(c => {
                const icone = getIconeCategoria(c.categoria);
                const cor = getCorCategoria(c.categoria);
                return `
                    <div class="custo-item">
                        <div class="custo-icon" style="background: ${cor}20; color: ${cor};">
                            <i class="fas fa-${icone}"></i>
                        </div>
                        <div class="custo-info">
                            <div class="custo-nome">${c.descricao}</div>
                            <div class="custo-detalhes">${formatarData(c.data_custo)} • ${getNomeCategoria(c.categoria)}</div>
                        </div>
                        <div class="custo-valor">${formatarMoeda(c.valor)}</div>
                        <div class="custo-actions">
                            <button class="btn-action btn-editar" onclick="editarCustoGeral(${c.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-excluir" onclick="excluirCustoGeral(${c.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Atualizar stats de custos de eventos
        function atualizarStatsCustosEventos() {
            const custos = dadosCustos.custosEventos;
            const mes = document.getElementById('filtroMes').value;
            
            const totalGeral = custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const eventosUnicos = [...new Set(custos.map(c => c.evento_id))];
            
            let custosMes = custos;
            if (mes) {
                custosMes = custos.filter(c => {
                    const dataCusto = new Date(c.data_custo);
                    return dataCusto.getMonth() + 1 == mes;
                });
            }
            const totalMes = custosMes.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const eventosMes = [...new Set(custosMes.map(c => c.evento_id))];
            
            document.getElementById('totalGeralEventos').textContent = formatarMoeda(totalGeral);
            document.getElementById('totalEventosComCustos').textContent = eventosUnicos.length;
            document.getElementById('totalMesEventos').textContent = formatarMoeda(totalMes);
            document.getElementById('eventosMes').textContent = eventosMes.length;
        }
        
        // Atualizar categorias de eventos (Ajudantes agrupados por nome)
        function atualizarCategoriasEventos() {
            const container = document.getElementById('listaCategoriasEventos');
            const categoria = document.getElementById('filtroCategoria').value;
            const mes = document.getElementById('filtroMes').value;
            const ano = document.getElementById('filtroAno').value;

            // Filtrar custos pela categoria selecionada
            let custos = dadosCustos.custosEventos.filter(c => c.categoria === categoria);

            if (custos.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><div class="empty-state-title">Nenhum custo nesta categoria</div></div>';
                return;
            }

            // Agrupar por NOME DA PESSOA (descricao), igual ao HTML original
            const agrupado = {};
            custos.forEach(c => {
                const nome = c.descricao || 'Sem nome';
                if (!agrupado[nome]) {
                    agrupado[nome] = { nome, custos: [], eventosUnicos: new Set() };
                }
                agrupado[nome].custos.push(c);
                agrupado[nome].eventosUnicos.add(c.evento_id);
            });

            // Ordenar por total anual decrescente
            const lista = Object.values(agrupado).sort((a, b) => {
                const totalA = a.custos.reduce((s, c) => s + parseFloat(c.valor || 0), 0);
                const totalB = b.custos.reduce((s, c) => s + parseFloat(c.valor || 0), 0);
                return totalB - totalA;
            });

            container.innerHTML = lista.map(item => {
                // Total do mês filtrado
                const custosMes = item.custos.filter(c => {
                    if (!mes) return true;
                    const d = new Date(c.data_custo + 'T00:00:00');
                    return d.getMonth() + 1 == mes;
                });
                const totalMes = custosMes.reduce((s, c) => s + parseFloat(c.valor || 0), 0);

                // Total do ano filtrado
                const custosAno = item.custos.filter(c => {
                    if (!ano) return true;
                    const d = new Date(c.data_custo + 'T00:00:00');
                    return d.getFullYear() == ano;
                });
                const totalAno = custosAno.reduce((s, c) => s + parseFloat(c.valor || 0), 0);

                const totalGeral = item.custos.reduce((s, c) => s + parseFloat(c.valor || 0), 0);
                const qtdEventos = item.eventosUnicos.size;

                return `
                    <div class="ajudante-card" onclick="toggleAjudante(this)">
                        <div class="ajudante-header-card">
                            <div class="ajudante-nome">
                                <i class="fas fa-user-circle" style="color: #06D6A0;"></i>
                                ${item.nome}
                            </div>
                            <div class="ajudante-stats">
                                <div class="ajudante-stat">
                                    <div class="ajudante-stat-label"><i class="fas fa-calendar-check"></i> Eventos</div>
                                    <div class="ajudante-stat-value" style="color: #06D6A0;">${qtdEventos}</div>
                                </div>
                                <div class="ajudante-stat">
                                    <div class="ajudante-stat-label">📅 Mês</div>
                                    <div class="ajudante-stat-value" style="color: ${totalMes > 0 ? '#FFD23F' : '#5A6C8F'};">${formatarMoeda(totalMes)}</div>
                                </div>
                                <div class="ajudante-stat">
                                    <div class="ajudante-stat-label">📊 Ano</div>
                                    <div class="ajudante-stat-value" style="color: #06D6A0;">${formatarMoeda(totalAno)}</div>
                                </div>
                                <div class="ajudante-stat">
                                    <div class="ajudante-stat-label" style="font-size: 11px; color: #A0AEC0;">Total Anual</div>
                                    <div class="ajudante-stat-value" style="color: #A0AEC0;">${formatarMoeda(totalGeral)}</div>
                                </div>
                            </div>
                        </div>
                        <div class="ajudante-eventos">
                            ${item.custos.map(c => {
                                const nomeEvento = c.tipo_evento
                                    ? c.tipo_evento + (c.cliente_nome ? ' - ' + c.cliente_nome : '') + ' (' + formatarData(c.data_evento) + ')'
                                    : 'Evento';
                                return `
                                    <div class="evento-item">
                                        <div class="evento-info-mini">
                                            <div class="evento-nome">${nomeEvento}</div>
                                            <div class="evento-detalhes-mini">
                                                ${formatarData(c.data_custo)}
                                                ${c.forma_pagamento ? ' • ' + c.forma_pagamento : ''}
                                            </div>
                                        </div>
                                        <div class="evento-valor">${formatarMoeda(c.valor)}</div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Toggle expansão de ajudante
        function toggleAjudante(element) {
            element.classList.toggle('expanded');
        }
        
        // Atualizar histórico de custos de eventos
        function atualizarHistoricoCustosEventos() {
            const container = document.getElementById('listaCustosEventos');
            const custos = dadosCustos.custosEventos;
            
            if (custos.length === 0) {
                container.innerHTML = '<div class="empty-state"><i class="fas fa-clipboard-list"></i><div class="empty-state-title">Nenhum custo de evento registrado</div></div>';
                document.getElementById('totalHistoricoEventos').textContent = 'R$ 0,00';
                return;
            }
            
            const total = custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            document.getElementById('totalHistoricoEventos').textContent = formatarMoeda(total);
            
            // Paginação
            const inicio = (paginaAtual - 1) * itensPorPagina;
            const fim = inicio + itensPorPagina;
            const custosPagina = custos.slice(inicio, fim);
            
            container.innerHTML = custosPagina.map(c => {
                const icone = getIconeCategoria(c.categoria);
                const cor = getCorCategoria(c.categoria);
                const nomeEvento = c.tipo_evento ? (c.tipo_evento + (c.cliente_nome ? ' - ' + c.cliente_nome : '') + ' (' + formatarData(c.data_evento) + ')') : 'Evento';

                return `
                    <div class="custo-item">
                        <div class="custo-icon" style="background: ${cor}20; color: ${cor};">
                            <i class="fas fa-${icone}"></i>
                        </div>
                        <div class="custo-info">
                            <div class="custo-nome">${c.descricao}</div>
                            <div class="custo-detalhes">
                                ${formatarData(c.data_custo)} • ${getNomeCategoria(c.categoria)} •
                                ${nomeEvento} •
                                <i class="fas fa-credit-card"></i> ${c.forma_pagamento || 'N/A'}
                            </div>
                        </div>
                        <div class="custo-valor">${formatarMoeda(c.valor)}</div>
                        <div class="custo-actions">
                            <button class="btn-action btn-editar" onclick="editarCustoEvento(${c.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-excluir" onclick="excluirCustoEvento(${c.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Atualizar paginação
            atualizarPaginacao(custos.length);
        }
        
        // Atualizar paginação
        function atualizarPaginacao(totalItens) {
            const container = document.getElementById('paginacaoCustos');
            const totalPaginas = Math.ceil(totalItens / itensPorPagina);
            
            if (totalPaginas <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            
            if (paginaAtual > 1) {
                html += `<button class="btn-action btn-editar" onclick="mudarPagina(${paginaAtual - 1})">← Anterior</button>`;
            }
            
            for (let i = 1; i <= totalPaginas; i++) {
                if (i === paginaAtual) {
                    html += `<button class="btn-action" style="background: #FF6B35; color: white;">${i}</button>`;
                } else {
                    html += `<button class="btn-action btn-editar" onclick="mudarPagina(${i})">${i}</button>`;
                }
            }
            
            if (paginaAtual < totalPaginas) {
                html += `<button class="btn-action btn-editar" onclick="mudarPagina(${paginaAtual + 1})">Próxima →</button>`;
            }
            
            html += `<div style="color: #A0AEC0; font-size: 13px; align-self: center; margin-left: 15px;">Mostrando ${((paginaAtual - 1) * itensPorPagina) + 1}-${Math.min(paginaAtual * itensPorPagina, totalItens)} de ${totalItens} custos</div>`;
            
            container.innerHTML = html;
        }
        
        function mudarPagina(pagina) {
            paginaAtual = pagina;
            atualizarHistoricoCustosEventos();
        }
        
        // Atualizar dropdown de eventos
        function atualizarDropdownEventos() {
            const select = document.getElementById('eventoDetalhe');
            const eventos = dadosCustos.eventos;
            
            select.innerHTML = '<option value="">Selecione um evento específico...</option>' +
                eventos.map(e => {
                    const data = formatarData(e.data_evento);
                    return `<option value="${e.id}">${e.tipo_evento} - ${data}</option>`;
                }).join('');
        }
        
        // Carregar detalhes de um evento específico
        async function carregarDetalhesEvento() {
            const eventoId = document.getElementById('eventoDetalhe').value;
            const container = document.getElementById('detalhesEventoSelecionado');
            
            if (!eventoId) {
                container.style.display = 'none';
                return;
            }
            
            const evento = dadosCustos.eventos.find(e => e.id == eventoId);
            const custos = dadosCustos.custosEventos.filter(c => c.evento_id == eventoId);
            
            if (!evento) {
                container.style.display = 'none';
                return;
            }
            
            const totalCustos = custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            const valorEvento = parseFloat(evento.valor_total || 0);
            const lucro = valorEvento - totalCustos;
            const lucroPercent = valorEvento > 0 ? ((lucro / valorEvento) * 100).toFixed(1) : 0;
            
            document.getElementById('detalheValorEvento').textContent = formatarMoeda(valorEvento);
            document.getElementById('detalheTotalCustos').textContent = formatarMoeda(totalCustos);
            document.getElementById('detalheLucro').textContent = formatarMoeda(lucro);
            document.getElementById('detalheLucroPercent').textContent = lucroPercent + '%';
            
            const listaCustos = document.getElementById('detalheCustosLista');
            if (custos.length === 0) {
                listaCustos.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><div class="empty-state-title">Nenhum custo registrado para este evento</div></div>';
            } else {
                listaCustos.innerHTML = custos.map(c => {
                    const icone = getIconeCategoria(c.categoria);
                    const cor = getCorCategoria(c.categoria);
                    return `
                        <div class="custo-item">
                            <div class="custo-icon" style="background: ${cor}20; color: ${cor};">
                                <i class="fas fa-${icone}"></i>
                            </div>
                            <div class="custo-info">
                                <div class="custo-nome">${c.descricao}</div>
                                <div class="custo-detalhes">
                                    ${c.custo_registrado ? formatarData(c.custo_registrado) : formatarData(c.data_custo)} • ${getNomeCategoria(c.categoria)}
                                </div>
                            </div>
                            <div class="custo-valor">${formatarMoeda(c.valor)}</div>
                        </div>
                    `;
                }).join('');
            }
            
            container.style.display = 'block';
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
            paginaAtual = 1;
            carregarDadosCustos();
        }
        
        // Limpar filtros
        function limparFiltros() {
            const anoAtual = new Date().getFullYear();
            document.getElementById('filtroMes').value = '';
            document.getElementById('filtroAno').value = anoAtual;
            document.getElementById('filtroVisualizacao').value = 'mensal';
            if (document.getElementById('filtroCategoria')) {
                document.getElementById('filtroCategoria').value = 'ajudantes';
            }
            paginaAtual = 1;
            carregarDadosCustos();
        }
        
        // Modal de custo geral
        function abrirModalNovoCustoGeral() {
            document.getElementById('modalCustoGeralTitulo').textContent = 'Novo Custo Geral';
            document.getElementById('formCustoGeral').reset();
            document.getElementById('custoGeralId').value = '';
            const hoje = new Date().toISOString().split('T')[0];
            document.querySelector('[name="data_custo"]').value = hoje;
            document.getElementById('modalCustoGeral').classList.add('active');
        }
        
        function fecharModalCustoGeral() {
            document.getElementById('modalCustoGeral').classList.remove('active');
        }
        
        async function salvarCustoGeral(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const custoId = document.getElementById('custoGeralId').value;
            
            formData.append('action', custoId ? 'custo_empresa_update' : 'custo_empresa_create');
            
            try {
                const response = await fetch('../api/custos.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(custoId ? 'Custo atualizado com sucesso!' : 'Custo criado com sucesso!');
                    fecharModalCustoGeral();
                    carregarDadosCustos();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar custo');
            }
        }
        
        async function editarCustoGeral(id) {
            const custo = dadosCustos.custosGerais.find(c => c.id == id);
            
            if (custo) {
                document.getElementById('modalCustoGeralTitulo').textContent = 'Editar Custo Geral';
                document.getElementById('custoGeralId').value = custo.id;
                document.querySelector('[name="descricao"]').value = custo.descricao;
                document.querySelector('[name="categoria"]').value = custo.categoria;
                document.querySelector('[name="valor"]').value = custo.valor;
                document.querySelector('[name="data_custo"]').value = custo.data_custo;
                document.querySelector('[name="observacoes"]').value = custo.observacoes || '';
                document.getElementById('modalCustoGeral').classList.add('active');
            }
        }
        
        async function excluirCustoGeral(id) {
            if (!confirm('Deseja realmente excluir este custo?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'custo_empresa_delete');
                formData.append('id', id);
                
                const response = await fetch('../api/custos.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Custo excluído com sucesso!');
                    carregarDadosCustos();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir custo');
            }
        }
        
        async function editarCustoEvento(id) {
            const custo = dadosCustos.custosEventos.find(c => c.id == id);
            if (custo) {
                document.getElementById('custoEventoId').value = custo.id;
                document.getElementById('custoEventoDescricao').value = custo.descricao || '';
                document.getElementById('custoEventoCategoria').value = custo.categoria || '';
                document.getElementById('custoEventoValor').value = custo.valor || '';
                document.getElementById('custoEventoFormaPagamento').value = custo.forma_pagamento || '';
                document.getElementById('custoEventoData').value = custo.data_custo || custo.data || '';
                document.getElementById('custoEventoObservacoes').value = custo.observacoes || '';
                // Selecionar conta bancária já salva
                const selectConta = document.getElementById('selectContaBancariaEvento');
                if (selectConta && custo.conta_id) selectConta.value = custo.conta_id;
                document.getElementById('modalCustoEvento').classList.add('active');
            }
        }

        function fecharModalCustoEvento() {
            document.getElementById('modalCustoEvento').classList.remove('active');
            document.getElementById('formCustoEvento').reset();
        }

        async function salvarCustoEvento(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('formCustoEvento'));
            formData.append('action', 'evento_custo_update');

            try {
                const response = await fetch('../api/custos.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    fecharModalCustoEvento();
                    carregarDadosCustos();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar custo');
            }
        }
        
        async function excluirCustoEvento(id) {
            if (!confirm('Deseja realmente excluir este custo?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'evento_custo_delete');
                formData.append('id', id);
                
                const response = await fetch('../api/custos.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Custo excluído com sucesso!');
                    carregarDadosCustos();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir custo');
            }
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
        
        function getIconeCategoria(categoria) {
            const icones = {
                'combustivel': 'gas-pump',
                'impostos': 'file-invoice-dollar',
                'marketing': 'bullhorn',
                'aluguel': 'home',
                'salarios': 'money-bill-wave',
                'manutencao': 'tools',
                'equipamentos': 'guitar',
                'ajudantes': 'user-friends',
                'estacionamento': 'parking',
                'alimentacao': 'utensils',
                'transporte': 'taxi',
                'outros': 'ellipsis-h'
            };
            return icones[categoria] || 'dollar-sign';
        }
        
        function getCorCategoria(categoria) {
            const cores = {
                'combustivel': '#FFD23F',
                'impostos': '#1877F2',
                'marketing': '#FF6B35',
                'aluguel': '#06D6A0',
                'salarios': '#EF476F',
                'manutencao': '#A0AEC0',
                'equipamentos': '#FF6B35',
                'ajudantes': '#06D6A0',
                'estacionamento': '#FFD23F',
                'alimentacao': '#EF476F',
                'transporte': '#1877F2',
                'outros': '#A0AEC0'
            };
            return cores[categoria] || '#5A6C8F';
        }
        
        function getNomeCategoria(categoria) {
            const nomes = {
                'combustivel': 'Combustível',
                'impostos': 'Impostos',
                'marketing': 'Marketing',
                'aluguel': 'Aluguel',
                'salarios': 'Salários',
                'manutencao': 'Manutenção',
                'equipamentos': 'Equipamentos',
                'ajudantes': 'Ajudantes',
                'estacionamento': 'Estacionamento',
                'alimentacao': 'Alimentação',
                'transporte': 'Transporte',
                'outros': 'Outros'
            };
            return nomes[categoria] || categoria;
        }
    </script>
<?php include '../includes/footer.php'; ?>
</body>
</html>
