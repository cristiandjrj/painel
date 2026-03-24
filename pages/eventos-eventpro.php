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
    <title>Eventos - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css?v=20260228">
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">📅 Eventos</h1>
        <p class="page-subtitle">Gerencie todos os seus eventos e orçamentos</p>
    </div>

    <!-- Stats - Layout inline com gradientes (padrão Gestão Financeira) -->
    <style>
        /* Calendário grid - 7 colunas */
        #calendarGrid { grid-template-columns: repeat(7, 1fr); }

        .ev-stats .stat-card::before { display: none; }
        .ev-stats .stat-card { display: flex; align-items: center; gap: 12px; overflow: visible; padding: 16px 14px; }
        .ev-stats .stat-card .stat-icon { font-size: 24px; width: 48px; height: 48px; min-width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0; margin-bottom: 0; }
        .ev-stats .stat-card .stat-info { flex: 1; min-width: 0; }
        .ev-stats .stat-card .stat-info .stat-label { font-size: 10px; color: #A0AEC0; margin-bottom: 2px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }
        .ev-stats .stat-card .stat-info .stat-value { font-size: 18px; font-weight: 800; margin-bottom: 0; white-space: nowrap; }
        /* Grid responsivo dos cards - igual ao dashboard */
        .ev-stats .stats-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important; }
        @media (max-width: 1024px) { .ev-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; } }
        @media (max-width: 768px)  { .ev-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; } }
        @media (max-width: 430px)  { .ev-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .ev-stats .stat-card { padding: 12px 10px !important; gap: 8px !important; }
            .ev-stats .stat-card .stat-icon { width: 36px !important; height: 36px !important; min-width: 36px !important; font-size: 18px !important; }
            .ev-stats .stat-card .stat-info .stat-value { font-size: 15px !important; }
            .ev-stats .stat-card .stat-info .stat-label { font-size: 9px !important; }
        }

        /* ====== MOBILE EVENTOS ====== */
        @media (max-width: 768px) {
            /* Busca + botão empilhar */
            .ev-search-row {
                flex-direction: column !important;
                gap: 10px !important;
            }
            .ev-search-row .btn {
                width: 100% !important;
            }

            /* Filtros de período empilhar */
            .ev-filtros-periodo {
                flex-direction: column !important;
                gap: 8px !important;
                align-items: stretch !important;
            }
            .ev-filtros-periodo > div {
                width: 100% !important;
            }
            .ev-filtros-periodo select {
                width: 100% !important;
            }
            .ev-filtros-periodo button {
                width: 100% !important;
            }

            /* Filtros de status + toggle */
            .ev-status-row {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: stretch !important;
            }
            .ev-status-row .filters-section {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }
            .ev-status-row .filter-btn {
                flex: 1;
                min-width: 0;
                text-align: center;
                padding: 8px 6px !important;
                font-size: 11px !important;
            }
            .ev-view-toggle {
                display: flex !important;
                width: 100% !important;
            }
            .ev-view-toggle button {
                flex: 1 !important;
            }

            /* Calendário wrapper - reduzir padding */
            .ev-calendar-wrapper {
                padding: 10px !important;
                border-radius: 12px !important;
            }

            /* Calendário grid - cells menores, gap menor */
            #calendarGrid {
                grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
                gap: 2px !important;
            }
            #calendarGrid > div {
                min-height: 44px !important;
                padding: 3px 1px !important;
                font-size: 8px !important;
                overflow: hidden !important;
                word-break: break-all !important;
            }
            #calendarGrid > div > div:first-child {
                font-size: 10px !important;
            }
            #calendarGrid > div > div {
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            /* Calendar header row */
            .ev-calendar-header {
                flex-direction: column !important;
                gap: 10px !important;
                align-items: stretch !important;
                margin-bottom: 12px !important;
            }
            .ev-calendar-header h3 {
                font-size: 18px !important;
                text-align: center;
            }
            .ev-calendar-nav {
                justify-content: center !important;
            }

            /* Modal Editar - grids para 1 coluna */
            #modalEditarEvento .modal-edit-grid-2 {
                grid-template-columns: 1fr !important;
            }
            #modalEditarEvento .modal-edit-grid-3 {
                grid-template-columns: 1fr !important;
            }
            #formEditarEvento div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            #formEditarEvento div[style*="grid-template-columns: 1fr 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            #formEditarEvento div[style*="grid-template-columns: 2fr 1fr"] {
                grid-template-columns: 1fr !important;
            }

            /* Modal Custos - grids para 1 coluna */
            #modalCustoEvento div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }

            /* Modal botões empilhar */
            #formEditarEvento div[style*="justify-content: flex-end"] {
                flex-direction: column !important;
            }
            #formEditarEvento div[style*="justify-content: flex-end"] button {
                width: 100% !important;
            }
            #formCustoEvento div[style*="justify-content: flex-end"] {
                flex-direction: column !important;
            }
            #formCustoEvento div[style*="justify-content: flex-end"] button {
                width: 100% !important;
            }

            /* Modal Detalhes Cliente grids */
            #modalDetalhesCliente div[style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 480px) {
            .ev-calendar-wrapper {
                padding: 4px !important;
            }
            #calendarGrid {
                grid-template-columns: repeat(7, minmax(0, 1fr)) !important;
                gap: 1px !important;
            }
            #calendarGrid > div {
                min-height: 32px !important;
                padding: 2px 0 !important;
            }
            #calendarGrid > div > div:first-child {
                font-size: 9px !important;
            }
            /* Esconder texto dos eventos no calendário - só mostrar dot */
            #calendarGrid > div > div:not(:first-child) {
                font-size: 0 !important;
                line-height: 6px !important;
                height: 6px !important;
                overflow: hidden !important;
            }
            #calendarGrid > div > div:not(:first-child)::before {
                content: '●';
                font-size: 8px !important;
            }
        }
    </style>
    <div class="ev-stats">
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(79, 70, 229, 0.05));">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #4F46E5;">📊</div>
            <div class="stat-info">
                <div class="stat-label">Total de Eventos</div>
                <div class="stat-value" id="totalEventos" style="color: #4F46E5;">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 210, 63, 0.2), rgba(255, 210, 63, 0.05));">
            <div class="stat-icon" style="background: rgba(255, 210, 63, 0.15); color: #FFD23F;">⏳</div>
            <div class="stat-info">
                <div class="stat-label">Aguardando</div>
                <div class="stat-value" id="eventosAguardando" style="color: #FFD23F;">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(6, 214, 160, 0.05));">
            <div class="stat-icon" style="background: rgba(6, 214, 160, 0.15); color: #06D6A0;">🔄</div>
            <div class="stat-info">
                <div class="stat-label">Em Andamento</div>
                <div class="stat-value" id="eventosAndamento" style="color: #06D6A0;">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 107, 53, 0.05));">
            <div class="stat-icon" style="background: rgba(255, 107, 53, 0.15); color: #FF6B35;">✅</div>
            <div class="stat-info">
                <div class="stat-label">Concluídos</div>
                <div class="stat-value" id="eventosConcluidos" style="color: #FF6B35;">0</div>
            </div>
        </div>
    </div>
    </div>

    <!-- ✅ LAYOUT CORRIGIDO: Busca e Botão na mesma linha -->
    <div class="ev-search-row" style="display: flex; gap: 16px; align-items: center; margin: 24px 0;">
        <div class="search-bar" style="flex: 1;">
            <i class="search-icon">🔍</i>
            <input type="text" class="search-input" id="eventSearch" placeholder="Buscar evento..." oninput="buscarEventos()">
        </div>
        <button class="btn btn-primary" onclick="window.location.href='criar-orcamento.php'" style="white-space: nowrap; flex-shrink: 0;">
            + Novo Evento
        </button>
    </div>

    <!-- Filtros de Período -->
    <div class="ev-filtros-periodo" style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">📅 Mês:</label>
            <select id="filtroMes" onchange="aplicarFiltros()" style="padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-primary); font-size: 14px; font-weight: 500;">
                <option value="todos">Todos</option>
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
        
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">📆 Ano:</label>
            <select id="filtroAno" onchange="aplicarFiltros()" style="padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-primary); font-size: 14px; font-weight: 500;">
                <option value="todos">Todos</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
                <option value="2026" selected>2026</option>
                <option value="2027">2027</option>
            </select>
        </div>
        
        <button onclick="limparFiltrosPeriodo()" style="padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-secondary); font-size: 14px; cursor: pointer; transition: all 0.2s;">
            🔄 Limpar
        </button>
    </div>

    <!-- Toggle de Visualização + Filtros de Status -->
    <div class="ev-status-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div class="filters-section" style="margin-bottom: 0;">
            <button class="filter-btn active" data-status="em_andamento" onclick="filtrarEventos('em_andamento')">🔄 Em Andamento</button>
            <button class="filter-btn" data-status="aguardando" onclick="filtrarEventos('aguardando')">⏳ Aguardando</button>
            <button class="filter-btn" data-status="concluido" onclick="filtrarEventos('concluido')">✅ Concluídos</button>
            <button class="filter-btn" data-status="todos" onclick="filtrarEventos('todos')">📊 Todos</button>
        </div>
        <div class="ev-view-toggle" style="display: flex; gap: 8px;">
            <button class="view-toggle-btn active" id="btnViewList" onclick="switchView('list')" style="padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border); background: var(--primary); color: white; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;">📋 Lista</button>
            <button class="view-toggle-btn" id="btnViewCalendar" onclick="switchView('calendar')" style="padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-secondary); font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;">📅 Calendário</button>
        </div>
    </div>

    <!-- Vista Lista -->
    <div id="listView">
        <div id="eventsList"></div>
    </div>

    <!-- Vista Calendário -->
    <div id="calendarView" style="display: none;">
        <div class="ev-calendar-wrapper" style="background: var(--bg-card, #141B3D); border: 1px solid var(--border, #2D3561); border-radius: 16px; padding: 24px;">
            <div class="ev-calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 id="calendarMonth" style="font-size: 22px; font-weight: 700; color: var(--text-primary, white); margin: 0;">Janeiro 2026</h3>
                <div class="ev-calendar-nav" style="display: flex; gap: 10px;">
                    <button onclick="calendarPrevMonth()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border, #2D3561); background: var(--bg-card, #141B3D); color: var(--text-primary, white); cursor: pointer; font-size: 16px; transition: all 0.2s;">◀</button>
                    <button onclick="calendarToday()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid var(--primary, #FF6B35); background: rgba(255, 107, 53, 0.1); color: var(--primary, #FF6B35); cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s;">Hoje</button>
                    <button onclick="calendarNextMonth()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border, #2D3561); background: var(--bg-card, #141B3D); color: var(--text-primary, white); cursor: pointer; font-size: 16px; transition: all 0.2s;">▶</button>
                </div>
            </div>
            <div id="calendarGrid" style="display: grid; gap: 8px;">
                <!-- Calendar rendered by JS -->
            </div>
        </div>

        <!-- Modal de Eventos do Dia -->
        <div id="dayEventsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
            <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 500px; width: 100%; max-height: 80vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
                <div style="background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 id="dayEventsTitle" style="font-size: 18px; font-weight: 700; color: white; margin: 0;">Eventos do Dia</h3>
                    <button onclick="fecharModalDia()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">&times;</button>
                </div>
                <div id="dayEventsList" style="padding: 20px 24px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Evento -->
<div id="modalEditarEvento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px; overflow-y: auto;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561); margin: auto;">
        <div style="background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: white; margin: 0;">✏️ Editar Evento</h3>
            <button onclick="fecharModalEditar()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">&times;</button>
        </div>

        <form id="formEditarEvento" onsubmit="salvarEdicaoEvento(event)" style="padding: 24px;">
            <input type="hidden" id="editEventoId">

            <!-- Cliente (readonly) -->
            <div style="margin-bottom: 20px; padding: 12px; background: rgba(255, 107, 53, 0.1); border-radius: 8px; border-left: 4px solid var(--primary, #FF6B35);">
                <div style="font-size: 12px; font-weight: 700; color: var(--primary, #FF6B35); text-transform: uppercase; margin-bottom: 6px;">👤 Cliente</div>
                <div id="editClienteNome" style="font-size: 16px; font-weight: 600; color: var(--text-primary, white);"></div>
                <input type="hidden" id="editClienteId">
            </div>

            <!-- Tipo e Data -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Tipo de Evento *</label>
                    <select id="editTipoEvento" required style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                        <option value="">Selecione...</option>
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
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Data do Evento *</label>
                    <input type="date" id="editDataEvento" required style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                </div>
            </div>

            <!-- Horários -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Hora Início</label>
                    <input type="time" id="editHoraInicio" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Hora Fim</label>
                    <input type="time" id="editHoraFim" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Duração</label>
                    <input type="text" id="editDuracao" readonly style="width: 100%; padding: 12px 16px; background: rgba(0, 78, 137, 0.2); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: var(--secondary, #004E89); font-size: 14px; font-weight: 600;">
                </div>
            </div>

            <!-- Endereço -->
            <div style="margin-bottom: 16px; padding: 16px; background: rgba(255,255,255,0.02); border: 1px solid var(--border, #2D3561); border-radius: 12px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--text-secondary, #A0AEC0); text-transform: uppercase; margin-bottom: 12px;">📍 Endereço do Evento</div>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">Rua/Avenida</label>
                        <input type="text" id="editRua" placeholder="Rua" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">Número</label>
                        <input type="text" id="editNumero" placeholder="Nº" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">Bairro</label>
                        <input type="text" id="editBairro" placeholder="Bairro" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">Complemento</label>
                        <input type="text" id="editComplemento" placeholder="Salão, Quadra..." style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">CEP</label>
                        <input type="text" id="editCep" placeholder="00000-000" maxlength="9" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">Cidade</label>
                        <input type="text" id="editCidade" placeholder="Cidade" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary, #A0AEC0);">Estado</label>
                        <input type="text" id="editEstado" placeholder="UF" maxlength="2" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 13px; text-transform: uppercase;">
                    </div>
                </div>
            </div>

            <!-- Serviços e Valores -->
            <div style="margin-bottom: 16px; padding: 16px; background: rgba(6, 214, 160, 0.05); border: 1px solid rgba(6, 214, 160, 0.2); border-radius: 12px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--success, #06D6A0); text-transform: uppercase; margin-bottom: 12px;">Serviços e Valores</div>

                <!-- Lista de serviços disponíveis com checkboxes -->
                <div id="editServicosList" style="max-height: 200px; overflow-y: auto; margin-bottom: 12px;">
                    <p style="text-align: center; color: var(--text-secondary);">Carregando serviços...</p>
                </div>

                <!-- Serviços selecionados -->
                <div id="editServicosSelecionados" style="display: none; margin-bottom: 12px;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Serviços selecionados:</div>
                    <div id="editServicosSelecionadosList"></div>
                </div>

                <!-- Taxas -->
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Outras Taxas:</div>
                    <div id="editTaxasList"></div>
                    <div style="display: flex; gap: 8px; margin-top: 8px;">
                        <input type="text" id="editNovaTaxaDesc" placeholder="Descrição da taxa" onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('editNovaTaxaValor').focus();}" style="flex: 1; padding: 8px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 6px; color: white; font-size: 13px;">
                        <input type="number" step="0.01" id="editNovaTaxaValor" placeholder="Valor" onblur="adicionarTaxaEdit()" onkeydown="if(event.key==='Enter'){event.preventDefault();adicionarTaxaEdit();}" style="width: 100px; padding: 8px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 6px; color: white; font-size: 13px;">
                        <button type="button" onclick="adicionarTaxaEdit()" style="padding: 8px 12px; background: rgba(6, 214, 160, 0.2); border: 1px solid var(--success, #06D6A0); color: var(--success, #06D6A0); border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; white-space: nowrap;">+ Taxa</button>
                    </div>
                </div>

                <!-- Desconto -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Desconto</label>
                        <input type="number" step="0.01" id="editDesconto" placeholder="0,00" oninput="calcularTotalEdit()" style="width: 100%; padding: 8px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 6px; color: white; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Tipo</label>
                        <select id="editDescontoTipo" onchange="calcularTotalEdit()" style="width: 100%; padding: 8px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 6px; color: white; font-size: 13px;">
                            <option value="valor">R$ (Valor)</option>
                            <option value="percentual">% (Percentual)</option>
                        </select>
                    </div>
                </div>

                <!-- Resumo financeiro -->
                <div style="background: rgba(255, 107, 53, 0.1); border: 2px solid var(--primary, #FF6B35); border-radius: 8px; padding: 12px;">
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px;">
                        <span>Subtotal (Serviços):</span>
                        <strong id="editSubtotalServicos" style="color: var(--success, #06D6A0);">R$ 0,00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px;">
                        <span>Outras Taxas:</span>
                        <strong id="editTotalTaxas" style="color: var(--accent, #FFD23F);">R$ 0,00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px;">
                        <span>Desconto:</span>
                        <strong id="editTotalDesconto" style="color: var(--danger, #EF476F);">- R$ 0,00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0 0; border-top: 1px solid rgba(255,107,53,0.3); font-size: 16px;">
                        <strong>VALOR TOTAL:</strong>
                        <strong id="editValorTotalDisplay" style="color: var(--primary, #FF6B35);">R$ 0,00</strong>
                    </div>
                    <input type="hidden" id="editValorTotal">
                </div>
            </div>

            <!-- Financeiro -->
            <div style="margin-bottom: 16px; padding: 16px; background: rgba(0, 78, 137, 0.05); border: 1px solid rgba(0, 78, 137, 0.2); border-radius: 12px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--secondary, #004E89); text-transform: uppercase; margin-bottom: 12px;">Financeiro</div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Valor do Sinal (R$)</label>
                        <input type="number" step="0.01" id="editValorSinal" placeholder="0,00" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Data do Sinal</label>
                        <input type="date" id="editDataSinal" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Forma de Pagamento</label>
                        <select id="editFormaPagamento" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
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
                        <label style="display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary);">Data de Vencimento</label>
                        <input type="date" id="editDataVencimento" style="width: 100%; padding: 10px 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Status</label>
                    <select id="editStatus" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                        <option value="aguardando">Aguardando</option>
                        <option value="em_andamento">Em Andamento</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Observações -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Observações</label>
                <textarea id="editObservacoes" placeholder="Detalhes adicionais..." style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; resize: vertical; min-height: 80px;"></textarea>
            </div>

            <!-- Botões -->
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModalEditar()" style="padding: 12px 24px; background: rgba(239,71,111,0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" style="padding: 12px 24px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer;">💾 Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Custos do Evento -->
<div class="modal-overlay" id="modalCustoEvento" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #FF6B35, #E85A2B); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: white; margin: 0;">🎉 Custos do Evento</h3>
            <button onclick="fecharModalCustos()" style="background: rgba(255,255,255,0.2); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: white;">&times;</button>
        </div>
        <div style="margin: 16px 24px 0; padding: 12px; background: rgba(255, 107, 53, 0.1); border-radius: 8px;">
            <strong id="custoEventoNome" style="color: #FF6B35; font-size: 16px;">Evento</strong>
        </div>

        <!-- Lista de custos existentes -->
        <div id="listaCustosEvento" style="padding: 16px 24px;">
            <p style="text-align: center; color: var(--text-secondary, #A0AEC0);">Carregando custos...</p>
        </div>
        <div style="padding: 0 24px 16px; display: flex; justify-content: space-between; align-items: center;">
            <strong id="totalCustosEvento" style="color: #EF476F; font-size: 18px;">Total: R$ 0,00</strong>
        </div>

        <hr style="border: none; border-top: 1px solid var(--border, #2D3561); margin: 0 24px;">

        <!-- Formulário para adicionar novo custo -->
        <form id="formCustoEvento" onsubmit="salvarCustoEvento(event)" style="padding: 24px;">
            <h4 style="margin: 0 0 16px; font-size: 16px; font-weight: 600; color: var(--text-secondary, #A0AEC0);">+ Adicionar Novo Custo</h4>
            <input type="hidden" id="custoEventoId">

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Descrição *</label>
                <input type="text" id="custoEventoDescricao" required placeholder="Ex: Ajudante, Equipamento, Material" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Categoria *</label>
                    <select id="custoEventoCategoria" required style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                        <option value="">Selecione...</option>
                        <option value="ajudantes">👷 Ajudantes/Equipe</option>
                        <option value="equipamentos">📦 Equipamentos Alugados</option>
                        <option value="materiais">🛠️ Materiais/Consumíveis</option>
                        <option value="transporte">🚚 Transporte</option>
                        <option value="alimentacao">🍽️ Alimentação</option>
                        <option value="decoracao">🎨 Decoração</option>
                        <option value="terceiros">🤝 Serviços Terceiros</option>
                        <option value="estacionamento">🚗 Estacionamento</option>
                        <option value="outros">📌 Outros</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Valor (R$) *</label>
                    <input type="number" step="0.01" id="custoEventoValor" required placeholder="0,00" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Data *</label>
                    <input type="date" id="custoEventoData" required style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Forma de Pagamento</label>
                    <select id="custoEventoFormaPag" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                        <option value="">Selecione...</option>
                        <option value="dinheiro">💵 Dinheiro</option>
                        <option value="pix">📱 PIX</option>
                        <option value="credito">💳 Cartão de Crédito</option>
                        <option value="debito">💳 Cartão de Débito</option>
                        <option value="transferencia">🏦 Transferência</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Conta Bancária 🏦</label>
                <select id="custoEventoConta" style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px;">
                    <option value="">Carregando contas...</option>
                </select>
                <div style="font-size: 11px; color: var(--text-secondary, #A0AEC0); margin-top: 5px;">
                    💡 O valor será deduzido da conta selecionada
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-secondary, #A0AEC0); font-size: 13px; text-transform: uppercase;">Observações</label>
                <textarea id="custoEventoObs" placeholder="Detalhes do custo..." style="width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--border, #2D3561); border-radius: 8px; color: white; font-size: 14px; resize: vertical; min-height: 60px;"></textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="fecharModalCustos()" style="padding: 12px 24px; background: rgba(239,71,111,0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 8px; font-weight: 600; cursor: pointer;">Cancelar</button>
                <button type="submit" style="padding: 12px 24px; background: #FF6B35; border: none; color: white; border-radius: 8px; font-weight: 700; cursor: pointer;">Adicionar Custo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detalhes do Cliente -->
<div id="modalDetalhesCliente" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: var(--bg-card, #141B3D); border-radius: 16px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border, #2D3561);">
        <div style="background: linear-gradient(135deg, #FFD23F, #E8B800); padding: 20px 24px; border-radius: 16px 16px 0 0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10;">
            <h3 style="font-size: 20px; font-weight: 700; color: #1a1f3a; margin: 0;">👤 Detalhes do Cliente</h3>
            <button onclick="fecharModalDetalhesCliente()" style="background: rgba(0,0,0,0.15); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; font-size: 20px; color: #1a1f3a;">&times;</button>
        </div>
        <div id="detalhesClienteConteudo" style="padding: 24px;">
            <p style="text-align: center; color: var(--text-secondary);">Carregando...</p>
        </div>
    </div>
</div>

<script>
    let todosEventos = [];
    let custosEventosMap = {}; // Mapa de custos por evento
    let filtroAtual = 'em_andamento';
    let filtroMesAtual = 'todos';
    let filtroAnoAtual = '2026';

    async function carregarEventos() {
        try {
            const [resEventos, resCustos] = await Promise.all([
                fetch('../api/eventos.php?action=list'),
                fetch('../api/custos.php?action=custos_eventos_list')
            ]);

            const data = await resEventos.json();

            // Carregar custos de todos os eventos e agrupar por evento_id
            custosEventosMap = {};
            try {
                const dataCustos = await resCustos.json();
                if (dataCustos.success && dataCustos.custos) {
                    dataCustos.custos.forEach(c => {
                        const evId = c.evento_id;
                        if (!custosEventosMap[evId]) custosEventosMap[evId] = [];
                        custosEventosMap[evId].push(c);
                    });
                }
            } catch(e) { console.warn('Custos de eventos não disponíveis'); }

            if (data.success && data.eventos) {
                todosEventos = data.eventos;

                // Definir mês atual como padrão
                const mesAtual = new Date().getMonth() + 1;
                document.getElementById('filtroMes').value = mesAtual.toString();
                filtroMesAtual = mesAtual.toString();

                renderizarEventos();
                atualizarEstatisticas();
            } else {
                console.error('Erro ao carregar eventos:', data.message);
                document.getElementById('eventsList').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📅</div>
                        <h3 class="empty-state-title">Erro ao carregar eventos</h3>
                        <p class="empty-state-text">${data.message || 'Erro desconhecido'}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Erro:', error);
            document.getElementById('eventsList').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">❌</div>
                    <h3 class="empty-state-title">Erro de conexão</h3>
                    <p class="empty-state-text">Não foi possível carregar os eventos</p>
                </div>
            `;
        }
    }
    
    function renderizarEventos() {
        const container = document.getElementById('eventsList');
        
        let eventosFiltrados = todosEventos;
        
        // Filtrar por status
        if (filtroAtual !== 'todos') {
            eventosFiltrados = eventosFiltrados.filter(e => e.status === filtroAtual);
        }

        // Filtrar por mês/ano (NÃO aplicar para eventos aguardando - são orçamentos futuros)
        if (filtroAtual !== 'aguardando') {
            if (filtroMesAtual !== 'todos') {
                eventosFiltrados = eventosFiltrados.filter(e => {
                    const dataEvento = new Date(e.data_evento + 'T00:00:00');
                    return (dataEvento.getMonth() + 1) === parseInt(filtroMesAtual);
                });
            }

            if (filtroAnoAtual !== 'todos') {
                eventosFiltrados = eventosFiltrados.filter(e => {
                    const dataEvento = new Date(e.data_evento + 'T00:00:00');
                    return dataEvento.getFullYear() === parseInt(filtroAnoAtual);
                });
            }
        }
        
        eventosFiltrados.sort((a, b) => new Date(a.data_evento) - new Date(b.data_evento));
        
        if (eventosFiltrados.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <h3 class="empty-state-title">Nenhum evento encontrado</h3>
                    <p class="empty-state-text">
                        ${filtroAtual === 'todos' ? 'Comece criando seu primeiro evento' : 'Nenhum evento com este status'}
                    </p>
                    ${filtroAtual === 'todos' ? '<button class="btn btn-primary" onclick="window.location.href=\'criar-orcamento.php\'">+ Criar Evento</button>' : ''}
                </div>
            `;
            return;
        }
        
        const html = eventosFiltrados.map(e => {
            const statusMap = {
                'aguardando': { class: 'status-pending', text: 'AGUARDANDO', emoji: '⏳' },
                'em_andamento': { class: 'status-confirmed', text: 'EM ANDAMENTO', emoji: '🔄' },
                'concluido': { class: 'status-success', text: 'CONCLUÍDO', emoji: '✅' },
                'cancelado': { class: 'status-canceled', text: 'CANCELADO', emoji: '❌' }
            };
            
            const statusInfo = statusMap[e.status] || statusMap['aguardando'];
            
            const valorTotal = parseFloat(e.valor_total || 0);
            const valorSinal = parseFloat(e.valor_sinal || 0);
            const totalPago = parseFloat(e.total_pago || valorSinal);
            const valorAPagar = valorTotal - totalPago;
            
            // Montar endereco sem duplicar campos
            let endereco = '';
            if (e.cidade || e.bairro) {
                const partes = [];
                if (e.endereco) {
                    const rua = e.endereco.split(',')[0].trim();
                    if (rua) partes.push(rua);
                }
                if (e.numero) partes.push(e.numero);
                if (e.bairro) partes.push(e.bairro);
                if (e.cidade && e.estado) partes.push(e.cidade + '/' + e.estado);
                else if (e.cidade) partes.push(e.cidade);
                endereco = partes.join(', ');
            } else if (e.endereco) {
                endereco = e.endereco;
            }
            
            let botaoAcao = '';
            if (e.status === 'aguardando') {
                botaoAcao = `<button class="event-action-btn" onclick="mudarStatus(${e.id}, 'em_andamento')">🔄 Iniciar</button>`;
            } else if (e.status === 'em_andamento') {
                botaoAcao = `<button class="event-action-btn" onclick="mudarStatus(${e.id}, 'concluido')">✅ Concluir</button>`;
            }
            
            return `
                <div class="event-card-full status-${e.status}" data-evento-id="${e.id}">
                    <div class="event-card-main">
                        <div class="event-card-info">
                            <h3>${e.tipo || 'Evento'}</h3>
                            <div class="event-card-client" onclick="event.stopPropagation(); abrirDetalhesCliente(${e.cliente_id})" style="cursor: pointer; text-decoration: underline; color: var(--primary);">
                                👤 ${e.cliente_nome || 'Cliente'}
                            </div>
                            
                            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin: 8px 0; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    📅 <strong>${formatarData(e.data_evento)}</strong>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    🕐 <strong>${e.hora_inicio || '00:00'}</strong> - <strong>${e.hora_fim || '00:00'}</strong>
                                </div>
                                ${e.duracao ? `
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        ⏱️ <strong>${e.duracao}</strong>
                                    </div>
                                ` : ''}
                            </div>
                            
                            ${endereco ? `
                                <div style="margin: 8px 0;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        📍 <strong>${endereco}</strong>
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin: 8px 0; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    💰 Valor Total: <strong>${formatarMoeda(valorTotal)}</strong>
                                </div>
                                ${valorSinal > 0 ? `
                                    <div style="display: flex; align-items: center; gap: 6px; color: var(--success);">
                                        ✅ Sinal: <strong>${formatarMoeda(valorSinal)}</strong>
                                    </div>
                                ` : ''}
                                ${valorAPagar > 0 && (e.status === 'em_andamento' || e.status === 'concluido') ? `
                                    <div style="display: flex; align-items: center; gap: 6px; color: var(--warning);">
                                        ⏰ A Receber: <strong>${formatarMoeda(valorAPagar)}</strong>
                                    </div>
                                ` : ''}
                            </div>
                            ${(() => {
                                const custosEvento = custosEventosMap[e.id] || [];
                                const totalCustos = custosEvento.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
                                if (custosEvento.length > 0) {
                                    const lucroEvento = valorTotal - totalCustos;
                                    const margemEvento = valorTotal > 0 ? ((lucroEvento / valorTotal) * 100).toFixed(1) : 0;
                                    return `
                                        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin: 4px 0; align-items: center;">
                                            <div style="display: flex; align-items: center; gap: 6px; color: #EF476F;">
                                                💸 Custos: <strong>${formatarMoeda(totalCustos)}</strong>
                                                <span style="font-size: 11px; color: var(--text-secondary);">(${custosEvento.length} itens)</span>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px; color: ${lucroEvento >= 0 ? 'var(--success)' : 'var(--danger)'};">
                                                📊 Lucro: <strong>${formatarMoeda(lucroEvento)}</strong>
                                                <span style="font-size: 11px; opacity: 0.7;">(${margemEvento}%)</span>
                                            </div>
                                        </div>
                                    `;
                                }
                                return '';
                            })()}
                            
                            ${renderizarServicosETaxas(e)}
                        </div>
                        <div class="event-status ${statusInfo.class}">
                            ${statusInfo.emoji} ${statusInfo.text}
                        </div>
                    </div>
                    
                    <div class="event-card-actions">
                        <button class="event-action-btn" onclick="abrirModalEditar(${e.id})">✏️ Editar</button>
                        <button class="event-action-btn" onclick="abrirModalCustos(${e.id})">💼 Custos</button>
                        ${botaoAcao}
                        <button class="event-action-btn" onclick="excluirEvento(${e.id})">🗑️ Excluir</button>
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = html;
    }
    
    function atualizarEstatisticas() {
        // Eventos filtrados por período (para contagem de andamento/concluídos)
        let eventosFiltradosPeriodo = todosEventos;

        if (filtroMesAtual !== 'todos') {
            eventosFiltradosPeriodo = eventosFiltradosPeriodo.filter(e => {
                const dataEvento = new Date(e.data_evento + 'T00:00:00');
                return (dataEvento.getMonth() + 1) === parseInt(filtroMesAtual);
            });
        }

        if (filtroAnoAtual !== 'todos') {
            eventosFiltradosPeriodo = eventosFiltradosPeriodo.filter(e => {
                const dataEvento = new Date(e.data_evento + 'T00:00:00');
                return dataEvento.getFullYear() === parseInt(filtroAnoAtual);
            });
        }

        // Aguardando SEMPRE conta todos (são orçamentos futuros, independem do mês)
        const totalAguardando = todosEventos.filter(e => e.status === 'aguardando').length;

        document.getElementById('totalEventos').textContent = eventosFiltradosPeriodo.length;
        document.getElementById('eventosAguardando').textContent = totalAguardando;
        document.getElementById('eventosAndamento').textContent = eventosFiltradosPeriodo.filter(e => e.status === 'em_andamento').length;
        document.getElementById('eventosConcluidos').textContent = eventosFiltradosPeriodo.filter(e => e.status === 'concluido').length;
    }
    
    function aplicarFiltros() {
        filtroMesAtual = document.getElementById('filtroMes').value;
        filtroAnoAtual = document.getElementById('filtroAno').value;
        renderizarEventos();
        atualizarEstatisticas();
    }
    
    function limparFiltrosPeriodo() {
        document.getElementById('filtroMes').value = 'todos';
        document.getElementById('filtroAno').value = '2026';
        filtroMesAtual = 'todos';
        filtroAnoAtual = '2026';
        renderizarEventos();
        atualizarEstatisticas();
    }
    
    function filtrarEventos(status) {
        filtroAtual = status;
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-status="${status}"]`).classList.add('active');
        renderizarEventos();
    }
    
    function buscarEventos() {
        const termo = document.getElementById('eventSearch').value.toLowerCase();
        const eventosFiltrados = todosEventos.filter(e => 
            (e.tipo || '').toLowerCase().includes(termo) ||
            (e.cliente_nome || '').toLowerCase().includes(termo)
        );
    }
    
    async function mudarStatus(id, novoStatus) {
        if (!confirm('Deseja alterar o status deste evento?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', novoStatus);
            
            const response = await fetch('../api/eventos.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Status atualizado!');
                carregarEventos();
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao atualizar status');
        }
    }
    
    async function excluirEvento(id) {
        if (!confirm('Tem certeza que deseja excluir este evento?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            const response = await fetch('../api/eventos.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Evento excluído!');
                carregarEventos();
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir evento');
        }
    }
    
    let custoEventoAtualId = null;

    // Carregar contas bancárias para o select do modal
    async function carregarContasBancariasModal() {
        try {
            const response = await fetch('../api/contas-bancarias.php?action=list');
            const data = await response.json();
            const select = document.getElementById('custoEventoConta');

            if (data.success && data.contas && data.contas.length > 0) {
                select.innerHTML = '<option value="">Não informado</option>' +
                    data.contas.filter(c => c.ativo).map(c =>
                        `<option value="${c.id}">🏦 ${c.nome} - Saldo: ${formatarMoeda(c.saldo_atual || 0)}</option>`
                    ).join('');
            } else {
                select.innerHTML = '<option value="">Nenhuma conta cadastrada</option>';
            }
        } catch (error) {
            console.error('Erro ao carregar contas:', error);
            document.getElementById('custoEventoConta').innerHTML = '<option value="">Erro ao carregar contas</option>';
        }
    }

    function abrirModalCustos(eventoId) {
        custoEventoAtualId = eventoId;
        const evento = todosEventos.find(e => e.id == eventoId);

        if (evento) {
            document.getElementById('custoEventoNome').textContent = `🎉 ${evento.tipo || 'Evento'} - ${evento.cliente_nome || 'Cliente'}`;
        }

        document.getElementById('custoEventoId').value = eventoId;
        document.getElementById('custoEventoData').value = new Date().toISOString().split('T')[0];
        document.getElementById('formCustoEvento').reset();
        document.getElementById('custoEventoId').value = eventoId;
        document.getElementById('custoEventoData').value = new Date().toISOString().split('T')[0];

        // Carregar custos existentes e contas bancárias
        carregarCustosEvento(eventoId);
        carregarContasBancariasModal();

        document.getElementById('modalCustoEvento').style.display = 'flex';
    }

    function fecharModalCustos() {
        document.getElementById('modalCustoEvento').style.display = 'none';
        custoEventoAtualId = null;
    }

    async function carregarCustosEvento(eventoId) {
        const container = document.getElementById('listaCustosEvento');
        container.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 16px;">Carregando...</p>';

        try {
            const response = await fetch(`../api/custos.php?action=evento_custos_list&evento_id=${eventoId}`);
            const data = await response.json();

            if (data.success && data.custos && data.custos.length > 0) {
                const total = data.custos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
                document.getElementById('totalCustosEvento').textContent = 'Total: ' + formatarMoeda(total);

                container.innerHTML = data.custos.map(c => `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.02); border-radius: 8px; margin-bottom: 8px; border-left: 3px solid #EF476F;">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; margin-bottom: 4px;">${c.descricao}</div>
                            <div style="font-size: 12px; color: var(--text-secondary, #A0AEC0);">
                                ${formatarData(c.data_custo)} • ${c.categoria || 'N/A'}
                            </div>
                        </div>
                        <div style="font-size: 16px; font-weight: 700; color: #EF476F; margin-right: 12px;">
                            ${formatarMoeda(c.valor)}
                        </div>
                        <button onclick="excluirCustoEvento(${c.id})" style="background: rgba(239,71,111,0.1); border: 1px solid #EF476F; color: #EF476F; border-radius: 6px; padding: 6px 10px; cursor: pointer; font-size: 12px;">🗑️</button>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p style="text-align: center; color: var(--text-secondary, #A0AEC0); padding: 20px;">Nenhum custo registrado para este evento</p>';
                document.getElementById('totalCustosEvento').textContent = 'Total: R$ 0,00';
            }
        } catch (error) {
            console.error('Erro ao carregar custos:', error);
            container.innerHTML = '<p style="text-align: center; color: #EF476F; padding: 16px;">Erro ao carregar custos</p>';
            document.getElementById('totalCustosEvento').textContent = 'Total: R$ 0,00';
        }
    }

    async function salvarCustoEvento(event) {
        event.preventDefault();

        const eventoId = document.getElementById('custoEventoId').value;
        const formData = new FormData();
        formData.append('action', 'evento_custo_create');
        formData.append('evento_id', eventoId);
        formData.append('descricao', document.getElementById('custoEventoDescricao').value);
        formData.append('categoria', document.getElementById('custoEventoCategoria').value);
        formData.append('valor', document.getElementById('custoEventoValor').value);
        formData.append('data_custo', document.getElementById('custoEventoData').value);

        const formaPag = document.getElementById('custoEventoFormaPag').value;
        if (formaPag) formData.append('forma_pagamento', formaPag);

        const contaId = document.getElementById('custoEventoConta').value;
        if (contaId) formData.append('conta_id', contaId);

        const obs = document.getElementById('custoEventoObs').value;
        if (obs) formData.append('observacoes', obs);

        try {
            const response = await fetch('../api/custos.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Custo adicionado com sucesso!');
                // Limpar formulário
                document.getElementById('custoEventoDescricao').value = '';
                document.getElementById('custoEventoCategoria').value = '';
                document.getElementById('custoEventoValor').value = '';
                document.getElementById('custoEventoFormaPag').value = '';
                document.getElementById('custoEventoConta').value = '';
                document.getElementById('custoEventoObs').value = '';
                // Recarregar lista de custos
                carregarCustosEvento(eventoId);
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao salvar custo');
        }
    }

    async function excluirCustoEvento(custoId) {
        if (!confirm('Deseja excluir este custo?')) return;

        try {
            const formData = new FormData();
            formData.append('action', 'evento_custo_delete');
            formData.append('id', custoId);

            const response = await fetch('../api/custos.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Custo excluído!');
                if (custoEventoAtualId) {
                    carregarCustosEvento(custoEventoAtualId);
                }
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir custo');
        }
    }
    
    async function abrirDetalhesCliente(clienteId) {
        const modal = document.getElementById('modalDetalhesCliente');
        const conteudo = document.getElementById('detalhesClienteConteudo');
        conteudo.innerHTML = '<p style="text-align: center; padding: 40px; color: var(--text-secondary);">Carregando...</p>';
        modal.style.display = 'flex';

        try {
            const resp = await fetch(`../api/clientes.php?action=get&id=${clienteId}`);
            const data = await resp.json();
            if (!data.success || !data.cliente) {
                conteudo.innerHTML = '<p style="text-align:center; color:#EF476F; padding:40px;">Cliente não encontrado</p>';
                return;
            }

            const c = data.cliente;

            // Buscar eventos deste cliente
            const eventosCliente = todosEventos.filter(e => e.cliente_id == clienteId);
            const totalEventos = eventosCliente.length;
            const valorTotalEventos = eventosCliente.reduce((s, e) => s + parseFloat(e.valor_total || 0), 0);
            const totalPagoEventos = eventosCliente.reduce((s, e) => s + parseFloat(e.total_pago || e.valor_sinal || 0), 0);
            const saldoDevedor = valorTotalEventos - totalPagoEventos;

            // Percentual pago
            const percentPago = valorTotalEventos > 0 ? ((totalPagoEventos / valorTotalEventos) * 100).toFixed(0) : 0;

            // Origem labels
            const origemLabels = {
                'facebook': 'Facebook', 'instagram': 'Instagram', 'google': 'Google',
                'indicacao': 'Indicação', 'whatsapp': 'WhatsApp', 'site': 'Site', 'outros': 'Outros'
            };

            // Formatar telefone
            let telFormatado = c.telefone || '';
            if (telFormatado) {
                const nums = telFormatado.replace(/\D/g, '');
                if (nums.length === 11) telFormatado = `(${nums.slice(0,2)}) ${nums.slice(2,7)}-${nums.slice(7)}`;
                else if (nums.length === 10) telFormatado = `(${nums.slice(0,2)}) ${nums.slice(2,6)}-${nums.slice(6)}`;
            }

            // Formatar CPF/CNPJ
            let cpfFormatado = c.cpf || '';
            if (cpfFormatado) {
                const nums = cpfFormatado.replace(/\D/g, '');
                if (nums.length === 11) cpfFormatado = `${nums.slice(0,3)}.${nums.slice(3,6)}.${nums.slice(6,9)}-${nums.slice(9)}`;
                else if (nums.length === 14) cpfFormatado = `${nums.slice(0,2)}.${nums.slice(2,5)}.${nums.slice(5,8)}/${nums.slice(8,12)}-${nums.slice(12)}`;
            }

            // Montar endereço do cliente
            let enderecoCliente = '';
            const partesEnd = [];
            if (c.rua) partesEnd.push(c.rua);
            if (c.numero) partesEnd.push(c.numero);
            if (c.bairro) partesEnd.push(c.bairro);
            if (c.cidade && c.estado) partesEnd.push(c.cidade + '/' + c.estado);
            else if (c.cidade) partesEnd.push(c.cidade);
            enderecoCliente = partesEnd.join(', ');
            if (!enderecoCliente && c.endereco) enderecoCliente = c.endereco;

            // Data aniversário
            let aniversarioFormatado = '';
            const anivVal = c.data_aniversario || c.aniversario || '';
            if (anivVal) aniversarioFormatado = formatarData(anivVal);

            // Eventos do cliente detalhados
            let eventosHtml = '';
            if (eventosCliente.length > 0) {
                eventosHtml = eventosCliente.map(ev => {
                    const statusMap = {
                        'aguardando': { text: 'Aguardando', color: '#FF6B35' },
                        'em_andamento': { text: 'Em Andamento', color: '#06D6A0' },
                        'concluido': { text: 'Concluído', color: '#A0AEC0' },
                        'cancelado': { text: 'Cancelado', color: '#EF476F' }
                    };
                    const st = statusMap[ev.status] || statusMap['aguardando'];
                    const valEvt = parseFloat(ev.valor_total || 0);
                    const pago = parseFloat(ev.total_pago || ev.valor_sinal || 0);
                    const resta = valEvt - pago;

                    // Verificar vencimento
                    let vencTag = '';
                    if (ev.data_vencimento && resta > 0) {
                        const hoje = new Date();
                        const venc = new Date(ev.data_vencimento);
                        if (venc < hoje) vencTag = '<span style="color:#EF476F; font-weight:700; font-size:11px; margin-left:6px;">VENCIDO</span>';
                    }

                    return `
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-left: 3px solid ${st.color}; border-radius: 8px; padding: 12px; margin-bottom: 8px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <strong style="font-size:14px;">${ev.tipo || 'Evento'}</strong>
                                <span style="font-size:11px; padding:3px 8px; border-radius:10px; background:rgba(${st.color === '#FF6B35' ? '255,107,53' : st.color === '#06D6A0' ? '6,214,160' : st.color === '#EF476F' ? '239,71,111' : '160,174,192'},0.15); color:${st.color}; font-weight:600;">${st.text}</span>
                            </div>
                            <div style="font-size:12px; color:var(--text-secondary); margin-bottom:4px;">
                                📅 ${formatarData(ev.data_evento)} ${ev.hora_inicio ? '• 🕐 ' + ev.hora_inicio.substring(0,5) : ''}
                            </div>
                            <div style="display:flex; gap:12px; font-size:13px; margin-top:6px;">
                                <span>💰 ${formatarMoeda(valEvt)}</span>
                                <span style="color:var(--success);">✅ Pago: ${formatarMoeda(pago)}</span>
                                ${resta > 0 ? `<span style="color:#FFD23F;">⏰ Resta: ${formatarMoeda(resta)}${vencTag}</span>` : ''}
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                eventosHtml = '<p style="text-align:center; color:var(--text-secondary); font-size:13px; padding:12px;">Nenhum evento encontrado</p>';
            }

            conteudo.innerHTML = `
                <!-- Dados Pessoais -->
                <div style="margin-bottom:20px;">
                    <div style="font-size:13px; font-weight:700; color:var(--primary); text-transform:uppercase; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--border);">👤 Dados Pessoais</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">Nome</div>
                            <div style="font-weight:600;">${c.nome || '-'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">Telefone</div>
                            <div style="font-weight:600;">${telFormatado || '-'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">E-mail</div>
                            <div style="font-weight:600;">${c.email || '-'}</div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">CPF/CNPJ</div>
                            <div style="font-weight:600;">${cpfFormatado || '-'}</div>
                        </div>
                        ${aniversarioFormatado ? `
                        <div>
                            <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">Aniversário</div>
                            <div style="font-weight:600;">🎂 ${aniversarioFormatado}</div>
                        </div>` : ''}
                        ${c.origem ? `
                        <div>
                            <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">Origem</div>
                            <div style="font-weight:600;">${origemLabels[c.origem] || c.origem}</div>
                        </div>` : ''}
                    </div>
                    ${enderecoCliente ? `
                    <div style="margin-top:10px;">
                        <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">Endereço</div>
                        <div style="font-weight:600;">📍 ${enderecoCliente}${c.cep ? ' • CEP: ' + c.cep : ''}</div>
                    </div>` : ''}
                    ${c.observacoes ? `
                    <div style="margin-top:10px;">
                        <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:2px;">Observações</div>
                        <div style="font-size:13px; color:var(--text-secondary); font-style:italic;">${c.observacoes}</div>
                    </div>` : ''}
                </div>

                <!-- Resumo Financeiro -->
                <div style="margin-bottom:20px;">
                    <div style="font-size:13px; font-weight:700; color:var(--success); text-transform:uppercase; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--border);">💰 Resumo Financeiro</div>
                    <div style="background:rgba(6,214,160,0.05); border:1px solid rgba(6,214,160,0.2); border-radius:12px; padding:16px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                            <div style="text-align:center; padding:10px; background:rgba(255,255,255,0.03); border-radius:8px;">
                                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:4px;">Total Eventos</div>
                                <div style="font-size:20px; font-weight:700; color:var(--primary);">${totalEventos}</div>
                            </div>
                            <div style="text-align:center; padding:10px; background:rgba(255,255,255,0.03); border-radius:8px;">
                                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:4px;">Valor Total</div>
                                <div style="font-size:20px; font-weight:700; color:var(--text-primary);">${formatarMoeda(valorTotalEventos)}</div>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                            <div style="text-align:center; padding:10px; background:rgba(6,214,160,0.1); border-radius:8px;">
                                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:4px;">Total Pago</div>
                                <div style="font-size:18px; font-weight:700; color:var(--success);">${formatarMoeda(totalPagoEventos)}</div>
                            </div>
                            <div style="text-align:center; padding:10px; background:rgba(239,71,111,0.1); border-radius:8px;">
                                <div style="font-size:11px; color:var(--text-secondary); text-transform:uppercase; margin-bottom:4px;">Saldo Devedor</div>
                                <div style="font-size:18px; font-weight:700; color:${saldoDevedor > 0 ? '#EF476F' : 'var(--success)'};">${formatarMoeda(saldoDevedor)}</div>
                            </div>
                        </div>
                        <!-- Barra de progresso -->
                        <div style="margin-top:8px;">
                            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                                <span style="color:var(--text-secondary);">Progresso de Pagamento</span>
                                <span style="font-weight:700; color:var(--success);">${percentPago}%</span>
                            </div>
                            <div style="height:8px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden;">
                                <div style="height:100%; width:${Math.min(percentPago, 100)}%; background:linear-gradient(90deg, #06D6A0, #04B890); border-radius:4px; transition:width 0.5s;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eventos do Cliente -->
                <div>
                    <div style="font-size:13px; font-weight:700; color:#FFD23F; text-transform:uppercase; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--border);">📅 Eventos (${totalEventos})</div>
                    ${eventosHtml}
                </div>

                <!-- Botão ir para página do cliente -->
                <div style="margin-top:16px; text-align:center;">
                    <button onclick="window.location.href='clientes-eventpro.php?cliente_id=${clienteId}'" style="padding:10px 24px; background:rgba(255,107,53,0.1); border:1px solid var(--primary); color:var(--primary); border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                        Ver Página Completa do Cliente →
                    </button>
                </div>
            `;

        } catch (error) {
            console.error('Erro ao carregar detalhes:', error);
            conteudo.innerHTML = '<p style="text-align:center; color:#EF476F; padding:40px;">Erro ao carregar detalhes do cliente</p>';
        }
    }

    function fecharModalDetalhesCliente() {
        document.getElementById('modalDetalhesCliente').style.display = 'none';
    }
    
    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor);
    }
    
    function formatarData(data) {
        if (!data) return 'Não informado';
        return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
    }
    
    function renderizarServicosETaxas(evento) {
        let html = '';
        
        // Renderizar Serviços
        if (evento.servicos && evento.servicos.length > 0) {
            html += `
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                    <div style="font-weight: 600; margin-bottom: 8px; color: var(--text-secondary); font-size: 13px;">
                        📦 Serviços:
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
            `;
            
            evento.servicos.forEach(servico => {
                const valorServico = parseFloat(servico.valor_total || servico.valor || 0);
                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                        <span style="color: var(--text-primary);">• ${servico.servico_nome || servico.nome}</span>
                        <span style="font-weight: 600; color: var(--success);">${formatarMoeda(valorServico)}</span>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        // Renderizar Taxas
        if (evento.taxas && evento.taxas.length > 0) {
            html += `
                <div style="margin-top: 12px; padding-top: 12px; ${evento.servicos && evento.servicos.length > 0 ? '' : 'border-top: 1px solid var(--border);'}">
                    <div style="font-weight: 600; margin-bottom: 8px; color: var(--text-secondary); font-size: 13px;">
                        📋 Outras Taxas:
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
            `;
            
            evento.taxas.forEach(taxa => {
                const valorTaxa = parseFloat(taxa.valor || 0);
                html += `
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                        <span style="color: var(--text-primary);">• ${taxa.descricao}</span>
                        <span style="font-weight: 600; color: var(--warning);">${formatarMoeda(valorTaxa)}</span>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        }
        
        // Renderizar Desconto
        const descontoValor = parseFloat(evento.desconto || 0);
        if (descontoValor > 0) {
            let descontoLabel = 'Desconto';
            if (evento.desconto_json) {
                try {
                    const desc = JSON.parse(evento.desconto_json);
                    if (desc.tipo === 'percentual') {
                        descontoLabel = `Desconto (${desc.valor}%)`;
                    }
                } catch(e) {}
            }
            html += `
                <div style="margin-top: 12px; padding-top: 12px; ${(evento.servicos && evento.servicos.length > 0) || (evento.taxas && evento.taxas.length > 0) ? '' : 'border-top: 1px solid var(--border);'}">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                        <span style="color: var(--text-primary);">🏷️ ${descontoLabel}</span>
                        <span style="font-weight: 600; color: var(--danger, #EF476F);">- ${formatarMoeda(descontoValor)}</span>
                    </div>
                </div>
            `;
        }

        // Mensagem se não tiver serviços nem taxas
        if ((!evento.servicos || evento.servicos.length === 0) && (!evento.taxas || evento.taxas.length === 0) && descontoValor <= 0) {
            html += `
                <div style="margin-top: 16px; padding: 12px; background: rgba(255, 255, 255, 0.02); border-radius: 8px; border: 1px dashed var(--border); text-align: center;">
                    <span style="color: var(--text-secondary); font-size: 13px;">Nenhum serviço ou taxa cadastrado</span>
                </div>
            `;
        }
        
        return html;
    }
    
    // ==================== EDITAR EVENTO (MODAL) ====================
    let editServicosDisponiveis = [];
    let editServicosSelecionados = [];
    let editTaxas = [];

    async function abrirModalEditar(eventoId) {
        try {
            // Carregar evento e serviços disponíveis em paralelo
            const [eventoResp, servicosResp] = await Promise.all([
                fetch(`../api/eventos.php?action=get&id=${eventoId}`),
                fetch('../api/servicos.php?action=list')
            ]);

            const eventoData = await eventoResp.json();
            const servicosData = await servicosResp.json();

            if (!eventoData.success || !eventoData.evento) {
                alert('Erro ao carregar dados do evento');
                return;
            }

            const ev = eventoData.evento;
            editServicosDisponiveis = servicosData.success ? (servicosData.servicos || []) : [];

            // Preencher campos básicos
            document.getElementById('editEventoId').value = ev.id;
            document.getElementById('editClienteId').value = ev.cliente_id || '';
            document.getElementById('editClienteNome').textContent = ev.cliente_nome || 'Cliente';
            document.getElementById('editTipoEvento').value = ev.tipo || '';
            document.getElementById('editDataEvento').value = ev.data_evento || '';

            if (ev.hora_inicio) document.getElementById('editHoraInicio').value = ev.hora_inicio.substring(0, 5);
            else document.getElementById('editHoraInicio').value = '';
            if (ev.hora_fim) document.getElementById('editHoraFim').value = ev.hora_fim.substring(0, 5);
            else document.getElementById('editHoraFim').value = '';

            calcularDuracaoEdit();

            // Endereço
            document.getElementById('editCep').value = ev.cep || '';
            document.getElementById('editNumero').value = ev.numero || '';
            document.getElementById('editComplemento').value = ev.complemento || '';
            document.getElementById('editBairro').value = ev.bairro || '';
            document.getElementById('editCidade').value = ev.cidade || '';
            document.getElementById('editEstado').value = ev.estado || '';

            if (ev.numero && ev.endereco) {
                const partes = ev.endereco.split(',').map(p => p.trim());
                document.getElementById('editRua').value = partes[0] || '';
            } else if (ev.endereco) {
                document.getElementById('editRua').value = ev.endereco;
            } else {
                document.getElementById('editRua').value = '';
            }

            // Status e observações
            document.getElementById('editStatus').value = ev.status || 'aguardando';
            document.getElementById('editObservacoes').value = ev.observacoes || '';

            // ===== SERVIÇOS: Renderizar checkboxes =====
            editServicosSelecionados = [];
            const nomesServicosEvento = [];

            if (ev.servicos && ev.servicos.length > 0) {
                ev.servicos.forEach(s => {
                    const nome = s.servico_nome || s.nome || '';
                    const valor = parseFloat(s.valor_total || s.valor_unitario || s.valor || 0);
                    editServicosSelecionados.push({ id: s.servico_id || s.id, nome: nome, valor: valor });
                    nomesServicosEvento.push(nome.toLowerCase());
                });
            }

            const servicosListEl = document.getElementById('editServicosList');
            if (editServicosDisponiveis.length > 0) {
                servicosListEl.innerHTML = editServicosDisponiveis.map(s => {
                    const isChecked = nomesServicosEvento.includes((s.nome || '').toLowerCase());
                    const valorServico = parseFloat(s.valor_padrao || s.valor_base || s.valor || 0);
                    return `
                        <label style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border, #2D3561); border-radius: 8px; margin-bottom: 6px; cursor: pointer; transition: background 0.2s;">
                            <input type="checkbox" value="${s.id}" data-id="${s.id}" data-nome="${s.nome}" data-valor="${valorServico}" ${isChecked ? 'checked' : ''} onchange="updateEditServicos()" style="width: 18px; height: 18px; accent-color: var(--success, #06D6A0);">
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 500; color: var(--text-primary, white);">${s.nome}</div>
                                <div style="font-size: 12px; color: var(--text-secondary, #A0AEC0);">
                                    ${s.categoria || ''} <span style="color: var(--success, #06D6A0); font-weight: 600;">${formatarMoeda(valorServico)}</span>
                                </div>
                            </div>
                        </label>
                    `;
                }).join('');
            } else {
                servicosListEl.innerHTML = '<p style="text-align: center; color: var(--text-secondary); padding: 12px; font-size: 13px;">Nenhum serviço cadastrado. Cadastre serviços na aba Serviços.</p>';
            }

            // ===== TAXAS =====
            editTaxas = [];
            if (ev.taxas && ev.taxas.length > 0) {
                ev.taxas.forEach(t => {
                    editTaxas.push({ descricao: t.descricao || '', valor: parseFloat(t.valor || 0) });
                });
            }
            renderEditTaxas();

            // ===== DESCONTO =====
            let descontoValor = 0;
            let descontoTipo = 'valor';
            if (ev.desconto_json) {
                try {
                    const desc = JSON.parse(ev.desconto_json);
                    descontoValor = desc.valor || 0;
                    descontoTipo = desc.tipo || 'valor';
                } catch (e) {}
            } else {
                descontoValor = parseFloat(ev.desconto || 0);
            }
            document.getElementById('editDesconto').value = descontoValor || '';
            document.getElementById('editDescontoTipo').value = descontoTipo;

            // ===== FINANCEIRO =====
            document.getElementById('editValorSinal').value = parseFloat(ev.valor_sinal || 0) || '';
            document.getElementById('editDataSinal').value = ev.data_sinal || '';
            document.getElementById('editFormaPagamento').value = ev.forma_pagamento_sinal || '';
            document.getElementById('editDataVencimento').value = ev.data_vencimento || '';

            // Calcular total
            calcularTotalEdit();

            // Abrir modal
            document.getElementById('modalEditarEvento').style.display = 'flex';

            // Listeners
            document.getElementById('editHoraInicio').oninput = calcularDuracaoEdit;
            document.getElementById('editHoraFim').oninput = calcularDuracaoEdit;

        } catch (error) {
            console.error('Erro ao carregar evento:', error);
            alert('Erro ao carregar dados do evento');
        }
    }

    function updateEditServicos() {
        const checkboxes = document.querySelectorAll('#editServicosList input[type="checkbox"]:checked');
        editServicosSelecionados = [];

        checkboxes.forEach(cb => {
            editServicosSelecionados.push({
                id: parseInt(cb.dataset.id),
                nome: cb.dataset.nome,
                valor: parseFloat(cb.dataset.valor) || 0
            });
        });

        // Mostrar selecionados
        const container = document.getElementById('editServicosSelecionados');
        const list = document.getElementById('editServicosSelecionadosList');
        if (editServicosSelecionados.length > 0) {
            container.style.display = 'block';
            list.innerHTML = editServicosSelecionados.map(s => `
                <div style="display: flex; justify-content: space-between; padding: 4px 8px; font-size: 13px;">
                    <span style="color: var(--text-primary, white);">• ${s.nome}</span>
                    <span style="font-weight: 600; color: var(--success, #06D6A0);">${formatarMoeda(s.valor)}</span>
                </div>
            `).join('');
        } else {
            container.style.display = 'none';
        }

        calcularTotalEdit();
    }

    function renderEditTaxas() {
        const container = document.getElementById('editTaxasList');
        if (editTaxas.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: var(--text-secondary); font-size: 12px; padding: 4px;">Nenhuma taxa adicionada</p>';
            return;
        }
        container.innerHTML = editTaxas.map((t, i) => `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 8px; background: rgba(255,255,255,0.03); border-radius: 6px; margin-bottom: 4px;">
                <span style="font-size: 13px; color: var(--text-primary, white);">${t.descricao}</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 13px; font-weight: 600; color: var(--accent, #FFD23F);">${formatarMoeda(t.valor)}</span>
                    <button type="button" onclick="removerTaxaEdit(${i})" style="background: rgba(239,71,111,0.2); border: none; color: var(--danger, #EF476F); padding: 2px 6px; border-radius: 4px; cursor: pointer; font-size: 12px;">x</button>
                </div>
            </div>
        `).join('');
        calcularTotalEdit();
    }

    function adicionarTaxaEdit() {
        const desc = document.getElementById('editNovaTaxaDesc').value.trim();
        const valor = parseFloat(document.getElementById('editNovaTaxaValor').value);
        if (!desc || !valor) return;

        editTaxas.push({ descricao: desc, valor: valor });
        document.getElementById('editNovaTaxaDesc').value = '';
        document.getElementById('editNovaTaxaValor').value = '';
        renderEditTaxas();
    }

    function removerTaxaEdit(index) {
        editTaxas.splice(index, 1);
        renderEditTaxas();
    }

    function calcularTotalEdit() {
        const subtotalServicos = editServicosSelecionados.reduce((sum, s) => sum + s.valor, 0);
        const totalTaxas = editTaxas.reduce((sum, t) => sum + t.valor, 0);

        const descontoValor = parseFloat(document.getElementById('editDesconto').value) || 0;
        const descontoTipo = document.getElementById('editDescontoTipo').value;

        let desconto = 0;
        if (descontoTipo === 'percentual') {
            desconto = (subtotalServicos + totalTaxas) * (descontoValor / 100);
        } else {
            desconto = descontoValor;
        }

        const total = Math.max(0, subtotalServicos + totalTaxas - desconto);

        document.getElementById('editSubtotalServicos').textContent = formatarMoeda(subtotalServicos);
        document.getElementById('editTotalTaxas').textContent = formatarMoeda(totalTaxas);
        document.getElementById('editTotalDesconto').textContent = '- ' + formatarMoeda(desconto);
        document.getElementById('editValorTotalDisplay').textContent = formatarMoeda(total);
        document.getElementById('editValorTotal').value = total.toFixed(2);
    }

    function fecharModalEditar() {
        document.getElementById('modalEditarEvento').style.display = 'none';
    }

    function calcularDuracaoEdit() {
        const inicio = document.getElementById('editHoraInicio').value;
        const fim = document.getElementById('editHoraFim').value;
        if (!inicio || !fim) {
            document.getElementById('editDuracao').value = '';
            return;
        }
        const [hI, mI] = inicio.split(':').map(Number);
        const [hF, mF] = fim.split(':').map(Number);
        let mins = (hF * 60 + mF) - (hI * 60 + mI);
        if (mins < 0) mins += 24 * 60;
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        document.getElementById('editDuracao').value = `${h}h${m > 0 ? m + 'min' : ''}`;
    }

    async function salvarEdicaoEvento(event) {
        event.preventDefault();

        // Garantir que o total está recalculado com as taxas atuais antes de enviar
        calcularTotalEdit();

        const eventoId = document.getElementById('editEventoId').value;
        const formData = new FormData();
        formData.append('action', 'update_orcamento');
        formData.append('evento_id', eventoId);
        formData.append('cliente_id', document.getElementById('editClienteId').value);
        formData.append('tipo_evento', document.getElementById('editTipoEvento').value);
        formData.append('data_evento', document.getElementById('editDataEvento').value);
        formData.append('hora_inicio', document.getElementById('editHoraInicio').value);
        formData.append('hora_fim', document.getElementById('editHoraFim').value);
        formData.append('duracao', document.getElementById('editDuracao').value);
        formData.append('rua', document.getElementById('editRua').value);
        formData.append('numero', document.getElementById('editNumero').value);
        formData.append('bairro', document.getElementById('editBairro').value);
        formData.append('complemento', document.getElementById('editComplemento').value);
        formData.append('cep', document.getElementById('editCep').value);
        formData.append('cidade', document.getElementById('editCidade').value);
        formData.append('estado', document.getElementById('editEstado').value);
        formData.append('valor_total', document.getElementById('editValorTotal').value);
        formData.append('status', document.getElementById('editStatus').value);
        formData.append('observacoes', document.getElementById('editObservacoes').value);

        // Serviços como JSON string (evita problemas de parsing de array aninhado no PHP)
        formData.append('servicos', JSON.stringify(editServicosSelecionados.map(s => ({
            servico_id: s.id,
            nome: s.nome,
            valor: s.valor,
            quantidade: 1
        }))));

        // Taxas como JSON string (evita problemas de parsing de array aninhado no PHP)
        formData.append('taxas', JSON.stringify(editTaxas));

        // Desconto
        const descontoValor = parseFloat(document.getElementById('editDesconto').value) || 0;
        const descontoTipo = document.getElementById('editDescontoTipo').value;
        formData.append('desconto', descontoValor);
        formData.append('desconto_tipo', descontoTipo);
        formData.append('desconto_json', JSON.stringify({ valor: descontoValor, tipo: descontoTipo }));

        // Financeiro
        formData.append('valor_sinal', document.getElementById('editValorSinal').value || 0);
        formData.append('data_sinal', document.getElementById('editDataSinal').value || '');
        formData.append('forma_pagamento_sinal', document.getElementById('editFormaPagamento').value || '');
        formData.append('data_vencimento', document.getElementById('editDataVencimento').value || '');

        try {
            const response = await fetch('../api/eventos.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Evento atualizado com sucesso!');
                fecharModalEditar();
                carregarEventos();
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            alert('Erro ao atualizar evento');
        }
    }

    // Fechar modal editar ao clicar fora
    document.getElementById('modalEditarEvento')?.addEventListener('click', function(e) {
        if (e.target === this) fecharModalEditar();
    });

    // Fechar modal detalhes do cliente ao clicar fora
    document.getElementById('modalDetalhesCliente')?.addEventListener('click', function(e) {
        if (e.target === this) fecharModalDetalhesCliente();
    });

    // ==================== CALENDÁRIO ====================
    let currentCalendarMonth = new Date();
    let currentView = 'list';

    function switchView(view) {
        currentView = view;
        document.getElementById('listView').style.display = view === 'list' ? 'block' : 'none';
        document.getElementById('calendarView').style.display = view === 'list' ? 'none' : 'block';

        // Atualizar botões
        document.getElementById('btnViewList').style.background = view === 'list' ? 'var(--primary)' : 'var(--bg-card)';
        document.getElementById('btnViewList').style.color = view === 'list' ? 'white' : 'var(--text-secondary)';
        document.getElementById('btnViewCalendar').style.background = view === 'calendar' ? 'var(--primary)' : 'var(--bg-card)';
        document.getElementById('btnViewCalendar').style.color = view === 'calendar' ? 'white' : 'var(--text-secondary)';

        if (view === 'calendar') renderCalendar();
    }

    function renderCalendar() {
        const year = currentCalendarMonth.getFullYear();
        const month = currentCalendarMonth.getMonth();

        const monthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                          'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        document.getElementById('calendarMonth').textContent = `${monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDay = firstDay.getDay();
        const totalDays = lastDay.getDate();

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        let html = '';

        // Cabeçalhos dos dias
        ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'].forEach(day => {
            html += `<div style="text-align: center; padding: 10px; font-weight: 700; font-size: 13px; color: var(--text-secondary, #A0AEC0); text-transform: uppercase;">${day}</div>`;
        });

        // Dias do mês anterior
        const prevMonthDays = new Date(year, month, 0).getDate();
        for (let i = startDay - 1; i >= 0; i--) {
            html += `<div style="text-align: center; padding: 12px 8px; border-radius: 8px; opacity: 0.3; min-height: 80px;">
                <div style="font-weight: 600; font-size: 14px; color: var(--text-secondary);">${prevMonthDays - i}</div>
            </div>`;
        }

        // Dias do mês atual
        for (let day = 1; day <= totalDays; day++) {
            const date = new Date(year, month, day);
            date.setHours(0, 0, 0, 0);
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            const dayEvents = todosEventos.filter(e => {
                if (!e.data_evento) return false;
                return e.data_evento === dateStr;
            });

            const isToday = date.getTime() === today.getTime();
            const hasEvent = dayEvents.length > 0;

            let bgStyle = 'background: rgba(255,255,255,0.02);';
            let borderStyle = 'border: 1px solid transparent;';
            if (isToday) {
                bgStyle = 'background: rgba(255, 107, 53, 0.15);';
                borderStyle = 'border: 1px solid #FF6B35;';
            }
            if (hasEvent) {
                bgStyle = 'background: rgba(6, 214, 160, 0.1);';
                borderStyle = 'border: 1px solid rgba(6, 214, 160, 0.3);';
            }
            if (isToday && hasEvent) {
                bgStyle = 'background: linear-gradient(135deg, rgba(255, 107, 53, 0.15), rgba(6, 214, 160, 0.15));';
                borderStyle = 'border: 1px solid #FF6B35;';
            }

            const statusColors = {
                'aguardando': '#FF6B35',
                'em_andamento': '#06D6A0',
                'concluido': '#A0AEC0',
                'cancelado': '#EF476F'
            };

            let eventsHtml = '';
            if (hasEvent) {
                dayEvents.slice(0, 2).forEach(ev => {
                    const cor = statusColors[ev.status] || '#A0AEC0';
                    eventsHtml += `<div style="font-size: 10px; color: ${cor}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">● ${ev.tipo || ev.cliente_nome || 'Evento'}</div>`;
                });
                if (dayEvents.length > 2) {
                    eventsHtml += `<div style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">+${dayEvents.length - 2} mais</div>`;
                }
            }

            html += `
                <div onclick="viewDayEvents('${dateStr}')" style="${bgStyle} ${borderStyle} text-align: center; padding: 8px; border-radius: 8px; min-height: 80px; cursor: ${hasEvent ? 'pointer' : 'default'}; transition: all 0.2s; ${hasEvent ? '' : 'opacity: 0.8;'}">
                    <div style="font-weight: 700; font-size: 14px; color: ${isToday ? '#FF6B35' : 'var(--text-primary, white)'}; margin-bottom: 4px;">${day}</div>
                    ${eventsHtml}
                </div>
            `;
        }

        // Dias do próximo mês
        const totalCells = startDay + totalDays;
        const remainingDays = totalCells <= 35 ? 35 - totalCells : 42 - totalCells;
        for (let day = 1; day <= remainingDays; day++) {
            html += `<div style="text-align: center; padding: 12px 8px; border-radius: 8px; opacity: 0.3; min-height: 80px;">
                <div style="font-weight: 600; font-size: 14px; color: var(--text-secondary);">${day}</div>
            </div>`;
        }

        document.getElementById('calendarGrid').innerHTML = html;
    }

    function calendarPrevMonth() {
        currentCalendarMonth.setMonth(currentCalendarMonth.getMonth() - 1);
        renderCalendar();
    }

    function calendarNextMonth() {
        currentCalendarMonth.setMonth(currentCalendarMonth.getMonth() + 1);
        renderCalendar();
    }

    function calendarToday() {
        currentCalendarMonth = new Date();
        renderCalendar();
    }

    function viewDayEvents(dateStr) {
        const dayEvents = todosEventos.filter(e => e.data_evento === dateStr);

        if (dayEvents.length === 0) return;

        const dateObj = new Date(dateStr + 'T00:00:00');
        const dataFormatada = dateObj.toLocaleDateString('pt-BR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        document.getElementById('dayEventsTitle').textContent = `📅 ${dataFormatada}`;

        const statusLabels = {
            'aguardando': { emoji: '🟠', text: 'Aguardando', color: '#FF6B35' },
            'em_andamento': { emoji: '🟢', text: 'Em Andamento', color: '#06D6A0' },
            'concluido': { emoji: '⚪', text: 'Concluído', color: '#A0AEC0' },
            'cancelado': { emoji: '🔴', text: 'Cancelado', color: '#EF476F' }
        };

        let html = '';
        dayEvents.forEach(ev => {
            const statusInfo = statusLabels[ev.status] || statusLabels['aguardando'];
            const valorTotal = parseFloat(ev.valor_total || 0);
            html += `
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border, #2D3561); border-left: 4px solid ${statusInfo.color}; border-radius: 8px; padding: 16px; margin-bottom: 12px; cursor: pointer;" onclick="fecharModalDia(); document.querySelector('[data-evento-id=&quot;${ev.id}&quot;]')?.scrollIntoView({behavior:'smooth', block:'center'});">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <strong style="color: var(--text-primary, white); font-size: 15px;">${ev.tipo || 'Evento'}</strong>
                        <span style="background: rgba(${statusInfo.color === '#FF6B35' ? '255,107,53' : statusInfo.color === '#06D6A0' ? '6,214,160' : statusInfo.color === '#EF476F' ? '239,71,111' : '160,174,192'}, 0.15); color: ${statusInfo.color}; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">${statusInfo.emoji} ${statusInfo.text}</span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-secondary, #A0AEC0);">
                        👤 ${ev.cliente_nome || 'Cliente'}
                    </div>
                    ${ev.horario ? `<div style="font-size: 13px; color: var(--text-secondary, #A0AEC0); margin-top: 4px;">🕐 ${ev.horario}</div>` : ''}
                    ${ev.local ? `<div style="font-size: 13px; color: var(--text-secondary, #A0AEC0); margin-top: 4px;">📍 ${ev.local}</div>` : ''}
                    ${valorTotal > 0 ? `<div style="font-size: 14px; font-weight: 700; color: var(--success, #06D6A0); margin-top: 8px;">${formatarMoeda(valorTotal)}</div>` : ''}
                </div>
            `;
        });

        document.getElementById('dayEventsList').innerHTML = html;
        document.getElementById('dayEventsModal').style.display = 'flex';
    }

    function fecharModalDia() {
        document.getElementById('dayEventsModal').style.display = 'none';
    }

    // Fechar modal dia ao clicar fora
    document.getElementById('dayEventsModal')?.addEventListener('click', function(e) {
        if (e.target === this) fecharModalDia();
    });

    // ==================== FIM CALENDÁRIO ====================

    // Fechar modal ao clicar fora
    document.getElementById('modalCustoEvento').addEventListener('click', function(e) {
        if (e.target === this) fecharModalCustos();
    });

    document.addEventListener('DOMContentLoaded', async function() {
        await carregarEventos();

        // Verificar se veio de outra página com evento_id na URL
        const urlParams = new URLSearchParams(window.location.search);
        const eventoIdParam = urlParams.get('evento_id');
        if (eventoIdParam) {
            // Limpar filtros para garantir que o evento esteja visível
            filtroAtual = 'todos';
            filtroMesAtual = 'todos';
            filtroAnoAtual = 'todos';
            document.getElementById('filtroMes').value = 'todos';
            if (document.getElementById('filtroAno')) document.getElementById('filtroAno').value = 'todos';
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.status === 'todos') btn.classList.add('active');
            });
            renderizarEventos();

            // Scroll até o evento e destacar
            setTimeout(() => {
                const eventoCard = document.querySelector(`[data-evento-id="${eventoIdParam}"]`);
                if (eventoCard) {
                    eventoCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    eventoCard.style.transition = 'box-shadow 0.3s, transform 0.3s';
                    eventoCard.style.boxShadow = '0 0 0 3px var(--primary), 0 8px 32px rgba(108, 99, 255, 0.3)';
                    eventoCard.style.transform = 'scale(1.02)';
                    // Remover destaque após 3 segundos
                    setTimeout(() => {
                        eventoCard.style.boxShadow = '';
                        eventoCard.style.transform = '';
                    }, 3000);
                }
                // Limpar URL
                history.replaceState(null, null, window.location.pathname);
            }, 300);
        }
    });
</script>

<?php include '../includes/footer.php'; ?>

<script>
(function(){
    if(window.innerWidth <= 768){
        var grid = document.querySelector('.ev-stats .stats-grid');
        if(grid){
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(2,1fr);gap:10px;max-width:100%;overflow:hidden;margin-bottom:30px;';
        }
        document.querySelectorAll('.ev-stats .stat-card').forEach(function(c){
            c.style.minWidth = '0';
            c.style.overflow = 'hidden';
        });
        document.querySelectorAll('.ev-stats .stat-info').forEach(function(i){
            i.style.minWidth = '0';
            i.style.overflow = 'hidden';
        });
        // Calendar fix
        var cal = document.getElementById('calendarGrid');
        if(cal){
            cal.style.cssText = 'display:grid;grid-template-columns:repeat(7,1fr);gap:2px;max-width:100%;overflow:hidden;';
        }
        var wrapper = document.querySelector('.ev-calendar-wrapper');
        if(wrapper){
            wrapper.style.padding = '8px';
        }
    }
})();
</script>

<!-- Mobile fix final - último na cascata -->
<style>
@media (max-width: 768px) {
    .ev-stats .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }
    .ev-stats .stat-card {
        min-width: 0 !important;
        overflow: hidden !important;
        padding: 10px 8px !important;
        gap: 6px !important;
    }
    .ev-stats .stat-card .stat-icon {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        font-size: 15px !important;
        border-radius: 8px !important;
    }
    .ev-stats .stat-card .stat-info {
        min-width: 0 !important;
        overflow: hidden !important;
    }
    .ev-stats .stat-card .stat-info .stat-value {
        font-size: 13px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .ev-stats .stat-card .stat-info .stat-label {
        font-size: 8px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        letter-spacing: 0.3px !important;
    }
    /* Calendário */
    .ev-calendar-wrapper {
        padding: 8px !important;
    }
    #calendarGrid {
        grid-template-columns: repeat(7, 1fr) !important;
        gap: 2px !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }
    #calendarGrid > div {
        min-width: 0 !important;
        min-height: 40px !important;
        padding: 3px 1px !important;
        font-size: 8px !important;
        overflow: hidden !important;
    }
    #calendarGrid > div > div:first-child {
        font-size: 10px !important;
    }
    #calendarGrid > div > div {
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }
}
@media (max-width: 430px) {
    .ev-calendar-wrapper {
        padding: 4px !important;
    }
    #calendarGrid {
        gap: 1px !important;
    }
    #calendarGrid > div {
        min-height: 32px !important;
        padding: 2px 0 !important;
    }
    #calendarGrid > div > div:first-child {
        font-size: 9px !important;
    }
    #calendarGrid > div > div:not(:first-child) {
        font-size: 0 !important;
        height: 6px !important;
        line-height: 6px !important;
        overflow: hidden !important;
    }
    #calendarGrid > div > div:not(:first-child)::before {
        content: '●';
        font-size: 6px !important;
    }
}
</style>

</body>
</html>
