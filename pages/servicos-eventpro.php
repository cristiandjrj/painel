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
    <title>Gestão de Serviços - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css?v=20260228">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Syne', sans-serif;
            background: #0A0E27;
            color: #FFFFFF;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        
        /* Stats Cards - Layout idêntico ao dashboard principal */
        .svc-stats .stat-card::before { display: none; }
        .svc-stats .stat-card { display: flex; align-items: center; gap: 12px; overflow: visible; padding: 16px 14px; }
        .svc-stats .stat-card .stat-icon { font-size: 24px; width: 48px; height: 48px; min-width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0; margin-bottom: 0; }
        .svc-stats .stat-card .stat-info { flex: 1; min-width: 0; }
        .svc-stats .stat-card .stat-info .stat-label { font-size: 10px; color: #A0AEC0; margin-bottom: 2px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }
        .svc-stats .stat-card .stat-info .stat-value { font-size: 18px; font-weight: 800; margin-bottom: 0; white-space: nowrap; }
        /* Grid responsivo dos cards - igual ao dashboard */
        .svc-stats .stats-grid { grid-template-columns: repeat(4, 1fr) !important; }
        @media (max-width: 1024px) { .svc-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; } }
        @media (max-width: 768px)  { .svc-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; } }
        @media (max-width: 430px)  { .svc-stats .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .svc-stats .stat-card { padding: 12px 10px !important; gap: 8px !important; }
            .svc-stats .stat-card .stat-icon { width: 36px !important; height: 36px !important; min-width: 36px !important; font-size: 18px !important; }
            .svc-stats .stat-card .stat-info .stat-value { font-size: 15px !important; }
            .svc-stats .stat-card .stat-info .stat-label { font-size: 9px !important; }
        }
        
        /* Filters */
        .filters-section {
            background: #141B3D;
            border: 1px solid #2D3561;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filters-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-label {
            font-size: 12px;
            color: #A0AEC0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-select {
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #2D3561;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            cursor: pointer;
            min-width: 150px;
        }
        
        .btn-clear {
            padding: 10px 20px;
            background: rgba(239, 71, 111, 0.1);
            border: 1px solid #EF476F;
            color: #EF476F;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            align-self: flex-end;
            margin-bottom: 2px;
        }
        
        .btn-clear:hover {
            background: rgba(239, 71, 111, 0.2);
        }
        
        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-new-service {
            padding: 12px 24px;
            background: #FF6B35;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-new-service:hover {
            background: #E85A2B;
            transform: scale(1.05);
        }
        
        /* Services List - Compacto */
        .services-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .service-card {
            background: #141B3D;
            border-bottom: 1px solid #2D3561;
            padding: 16px;
            transition: all 0.2s;
        }

        .service-card:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .service-info {
            flex: 1;
        }

        .service-name {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .service-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-secondary, #A0AEC0);
        }

        .service-price {
            font-size: 13px;
            color: var(--text-secondary, #A0AEC0);
            margin-left: 12px;
        }

        .service-icon {
            font-size: 14px;
        }

        .service-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .metric-item {
            text-align: center;
        }

        .metric-value {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .metric-value.green {
            color: #06D6A0;
        }

        .metric-value.blue {
            color: #118AB2;
        }

        .metric-value.orange {
            color: #FF6B35;
        }

        .metric-label {
            font-size: 10px;
            color: #A0AEC0;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .service-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            padding: 6px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit {
            background: rgba(79, 70, 229, 0.2);
            color: var(--primary, #FF6B35);
            border: 1px solid var(--primary, #FF6B35);
        }

        .btn-edit:hover {
            background: rgba(79, 70, 229, 0.3);
        }

        .btn-delete {
            background: rgba(239, 71, 111, 0.2);
            color: #EF476F;
            border: 1px solid #EF476F;
        }

        .btn-delete:hover {
            background: rgba(239, 71, 111, 0.3);
        }

        /* Top 5 Cards */
        .top5-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 24px;
        }

        .top5-card {
            background: #141B3D;
            border: 1px solid #2D3561;
            border-radius: 12px;
            overflow: hidden;
        }

        .top5-header {
            padding: 16px 20px;
            font-size: 16px;
            font-weight: 700;
        }

        .top5-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .top5-item:last-child {
            border-bottom: none;
        }

        .top5-rank {
            font-size: 24px;
            margin-right: 12px;
        }

        .top5-info {
            flex: 1;
        }

        .top5-name {
            font-weight: 600;
            font-size: 14px;
        }

        .top5-sub {
            font-size: 12px;
            color: var(--text-secondary, #A0AEC0);
        }

        .top5-value {
            font-weight: 700;
            font-size: 14px;
        }

        .top5-empty {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary, #A0AEC0);
            font-size: 13px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #A0AEC0;
        }

        .empty-icon {
            font-size: 64px;
            opacity: 0.3;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .service-metrics { grid-template-columns: 1fr; }
            .top5-grid { grid-template-columns: 1fr; }
            .section-header { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .service-header { flex-direction: column !important; gap: 8px !important; }
            .service-actions { justify-content: flex-start !important; }
            .filter-select { min-width: unset !important; width: 100% !important; }
            .filters-row { flex-direction: column !important; align-items: stretch !important; }
            .filter-group { width: 100% !important; }
            .modal-content { max-width: 100% !important; margin: 10px !important; }
            .form-row { grid-template-columns: 1fr !important; }
            .modal-footer { flex-direction: column !important; }
            .modal-footer .btn-cancel, .modal-footer .btn-submit { width: 100% !important; text-align: center !important; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
        }
    </style>
</head>
<body>
    <?php include '../includes/menu.php'; ?>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Gestão de Serviços</h1>
            <p class="page-subtitle">Análise completa de performance e rentabilidade dos seus serviços</p>
        </div>
        
        <!-- Stats Grid - Layout inline com gradientes -->
        <div class="svc-stats">
        <div class="stats-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 107, 53, 0.05));">
                <div class="stat-icon" style="background: rgba(255, 107, 53, 0.15); color: #FF6B35;">🎯</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Serviços</div>
                    <div class="stat-value" id="totalServicos" style="color: #FF6B35;">0</div>
                </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(6, 214, 160, 0.05));">
                <div class="stat-icon" style="background: rgba(6, 214, 160, 0.15); color: #06D6A0;">📊</div>
                <div class="stat-info">
                    <div class="stat-label">Total de Locações</div>
                    <div class="stat-value" id="totalLocacoes" style="color: #06D6A0;">0</div>
                </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(168, 85, 247, 0.05));">
                <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #A855F7;">💰</div>
                <div class="stat-info">
                    <div class="stat-label">Receita Total</div>
                    <div class="stat-value" id="receitaTotal" style="color: #A855F7;">R$ 0,00</div>
                </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, rgba(239, 71, 111, 0.2), rgba(239, 71, 111, 0.05));">
                <div class="stat-icon" style="background: rgba(239, 71, 111, 0.15); color: #EF476F;">⭐</div>
                <div class="stat-info">
                    <div class="stat-label">Serviço Mais Popular</div>
                    <div class="stat-value" id="servicoPopular" style="color: #EF476F; font-size: 18px;">-</div>
                </div>
            </div>
        </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar"></i> Mês</label>
                    <select class="filter-select" id="filtroMes">
                        <option value="">Todos</option>
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
                    <select class="filter-select" id="filtroAno">
                        <option value="">Todos</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-chart-bar"></i> Visualização</label>
                    <select class="filter-select" id="filtroVisualizacao">
                        <option value="mensal">Mensal</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                
                <button class="btn-clear" onclick="limparFiltros()">
                    <i class="fas fa-times"></i> Limpar
                </button>
            </div>
        </div>
        
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Performance dos Serviços
            </h2>
            <button class="btn-new-service" onclick="novoServico()">
                <i class="fas fa-plus"></i>
                NOVO SERVIÇO
            </button>
        </div>
        
        <!-- Services List -->
        <div class="services-list" id="servicesList">
            <div class="empty-state">
                <div class="empty-icon">🎯</div>
                <p>Carregando serviços...</p>
            </div>
        </div>

        <!-- Top 5 Serviços -->
        <div class="top5-grid">
            <div class="top5-card">
                <div class="top5-header">🏆 Top 5 Mais Contratados</div>
                <div id="topServicosContratados">
                    <div class="top5-empty">Nenhuma contratação ainda</div>
                </div>
            </div>
            <div class="top5-card">
                <div class="top5-header">💎 Top 5 Mais Rentáveis</div>
                <div id="topServicosRentaveis">
                    <div class="top5-empty">Nenhuma receita ainda</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let todosServicos = [];
        
        document.addEventListener('DOMContentLoaded', () => {
            carregarServicos();
        });
        
        // Carregar serviços
        async function carregarServicos() {
            try {
                const response = await fetch('../api/servicos.php?action=list&_=' + Date.now());
                const data = await response.json();
                
                if (data.success) {
                    todosServicos = data.servicos;
                    await calcularPerformance();
                    atualizarEstatisticas();
                    renderizarServicos();
                    renderTopServicos();
                }
            } catch (error) {
                console.error('Erro:', error);
                document.getElementById('servicesList').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">❌</div>
                        <p>Erro ao carregar serviços</p>
                    </div>
                `;
            }
        }
        
        // Calcular performance de cada serviço (com filtro de mês/ano)
        async function calcularPerformance() {
            try {
                const response = await fetch('../api/eventos.php?action=list&_=' + Date.now());
                const data = await response.json();

                if (!data.success) return;

                const mesFiltro = document.getElementById('filtroMes').value;
                const anoFiltro = document.getElementById('filtroAno').value;

                // Mapear serviços por ID
                const servicosMap = {};
                todosServicos.forEach(s => {
                    servicosMap[s.id] = {
                        ...s,
                        vezesContratado: 0,
                        receitaTotal: 0,
                        ticketMedio: 0
                    };
                });

                // Contar usos nos eventos (filtrados por mês/ano)
                data.eventos.forEach(evento => {
                    // Só contabilizar eventos confirmados (em andamento ou concluídos)
                    if (evento.status !== 'em_andamento' && evento.status !== 'concluido') return;

                    // Filtrar por mês/ano do evento (só aplica se filtro selecionado)
                    if (mesFiltro || anoFiltro) {
                        const dataEvento = new Date(evento.data_evento + 'T00:00:00');
                        if (mesFiltro && dataEvento.getMonth() + 1 !== parseInt(mesFiltro)) return;
                        if (anoFiltro && dataEvento.getFullYear() !== parseInt(anoFiltro)) return;
                    }

                    if (evento.servicos && Array.isArray(evento.servicos)) {
                        evento.servicos.forEach(servico => {
                            const id = servico.servico_id;
                            if (servicosMap[id]) {
                                // Usar valor_total, fallback para valor_unitario * quantidade
                                let valor = parseFloat(servico.valor_total || 0);
                                if (valor === 0) {
                                    const unitario = parseFloat(servico.valor_unitario || 0);
                                    const qtd = parseInt(servico.quantidade || 1);
                                    valor = unitario * qtd;
                                }
                                servicosMap[id].vezesContratado++;
                                servicosMap[id].receitaTotal += valor;
                            }
                        });
                    }
                });

                // Calcular ticket médio
                Object.values(servicosMap).forEach(s => {
                    if (s.vezesContratado > 0) {
                        s.ticketMedio = s.receitaTotal / s.vezesContratado;
                    } else {
                        // Se nunca foi contratado, usar valor_base como referência
                        s.ticketMedio = parseFloat(s.valor_base || s.valor || 0);
                    }
                });

                todosServicos = Object.values(servicosMap);
            } catch (error) {
                console.error('Erro ao calcular performance:', error);
            }
        }
        
        // Atualizar estatísticas
        function atualizarEstatisticas() {
            const total = todosServicos.length;
            const totalLocacoes = todosServicos.reduce((sum, s) => sum + (s.vezesContratado || 0), 0);
            const receitaTotal = todosServicos.reduce((sum, s) => sum + (s.receitaTotal || 0), 0);
            
            // Encontrar mais popular
            const maisPopular = todosServicos.reduce((prev, curr) => 
                (curr.vezesContratado || 0) > (prev.vezesContratado || 0) ? curr : prev
            , todosServicos[0] || {});
            
            document.getElementById('totalServicos').textContent = total;
            document.getElementById('totalLocacoes').textContent = totalLocacoes;
            document.getElementById('receitaTotal').textContent = formatarMoeda(receitaTotal);
            
            if (maisPopular && maisPopular.nome) {
                document.getElementById('servicoPopular').textContent = maisPopular.nome;
                const elPopInfo = document.getElementById('popularInfo');
                if (elPopInfo) elPopInfo.textContent = `${maisPopular.vezesContratado || 0} locações`;
            }
        }
        
        // Renderizar serviços
        function renderizarServicos() {
            const container = document.getElementById('servicesList');
            
            if (todosServicos.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🎯</div>
                        <p>Nenhum serviço cadastrado</p>
                    </div>
                `;
                return;
            }
            
            // Ordenar por vezes contratado (descendente)
            const servicosOrdenados = [...todosServicos].sort((a, b) => 
                (b.vezesContratado || 0) - (a.vezesContratado || 0)
            );
            
            container.innerHTML = servicosOrdenados.map(servico => {
                const iconesCategoria = {
                    'som_iluminacao': '🎵',
                    'decoracao': '🎨',
                    'audiovisual': '📹',
                    'outros': '⚙️'
                };
                
                const nomesCategoria = {
                    'som_iluminacao': 'Som e Iluminação',
                    'decoracao': 'Decoração',
                    'audiovisual': 'Audiovisual',
                    'outros': 'Outros'
                };
                
                const icone = iconesCategoria[servico.categoria] || '⚙️';
                const nomeCategoria = nomesCategoria[servico.categoria] || servico.categoria;
                
                const valorBase = parseFloat(servico.valor_base || servico.valor || 0);

                return `
                    <div class="service-card">
                        <div style="margin-bottom: 12px;">
                            <div class="service-name">${servico.nome}</div>
                            <div style="display: flex; gap: 12px; flex-wrap: wrap; font-size: 12px;">
                                <span class="service-category">
                                    <span class="service-icon">${icone}</span>
                                    ${nomeCategoria}
                                </span>
                                <span class="service-price">💰 ${formatarMoeda(valorBase)}</span>
                            </div>
                        </div>

                        <div class="service-metrics">
                            <div class="metric-item">
                                <div class="metric-value green">${servico.vezesContratado || 0}</div>
                                <div class="metric-label">Vezes Contratado</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value blue">${formatarMoeda(servico.receitaTotal || 0)}</div>
                                <div class="metric-label">Receita Total</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value orange">${formatarMoeda(servico.ticketMedio || 0)}</div>
                                <div class="metric-label">${servico.vezesContratado > 0 ? 'Ticket Médio' : 'Valor Base'}</div>
                            </div>
                        </div>

                        <div class="service-actions">
                            <button class="btn-action btn-edit" onclick="editarServico(${servico.id})">
                                ✏️ Editar
                            </button>
                            <button class="btn-action btn-delete" onclick="excluirServico(${servico.id})">
                                🗑️ Excluir
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Limpar filtros
        function limparFiltros() {
            document.getElementById('filtroMes').value = '';
            document.getElementById('filtroAno').value = '';
            document.getElementById('filtroVisualizacao').value = 'mensal';
            carregarServicos();
        }

        // Renderizar Top 5 Mais Contratados e Mais Rentáveis
        function renderTopServicos() {
            const medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];

            // Top 5 Mais Contratados
            const topContratados = [...todosServicos]
                .filter(s => (s.vezesContratado || 0) > 0)
                .sort((a, b) => (b.vezesContratado || 0) - (a.vezesContratado || 0))
                .slice(0, 5);

            const htmlContratados = topContratados.map((s, i) => `
                <div class="top5-item">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <span class="top5-rank">${medals[i]}</span>
                        <div class="top5-info">
                            <div class="top5-name">${s.nome}</div>
                            <div class="top5-sub">${s.vezesContratado} contratações</div>
                        </div>
                    </div>
                    <div class="top5-value" style="color: #06D6A0;">${formatarMoeda(s.receitaTotal || 0)}</div>
                </div>
            `).join('');

            document.getElementById('topServicosContratados').innerHTML =
                htmlContratados || '<div class="top5-empty">Nenhuma contratação ainda</div>';

            // Top 5 Mais Rentáveis (ordenar por receita total)
            const topRentaveis = [...todosServicos]
                .filter(s => (s.receitaTotal || 0) > 0)
                .sort((a, b) => (b.receitaTotal || 0) - (a.receitaTotal || 0))
                .slice(0, 5);

            const htmlRentaveis = topRentaveis.map((s, i) => `
                <div class="top5-item">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <span class="top5-rank">${medals[i]}</span>
                        <div class="top5-info">
                            <div class="top5-name">${s.nome}</div>
                            <div class="top5-sub">${s.vezesContratado || 0} contratações</div>
                        </div>
                    </div>
                    <div class="top5-value" style="color: #FFD23F;">${formatarMoeda(s.receitaTotal || 0)}</div>
                </div>
            `).join('');

            document.getElementById('topServicosRentaveis').innerHTML =
                htmlRentaveis || '<div class="top5-empty">Nenhuma receita ainda</div>';
        }
        
        // Funções de ação
        // Modal styles
        const modalStyles = `
            .modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.85);
                backdrop-filter: blur(5px);
                z-index: 9999;
                justify-content: center;
                align-items: center;
            }
            
            .modal-overlay.active {
                display: flex;
            }
            
            .modal-content {
                background: #141B3D;
                border-radius: 16px;
                max-width: 600px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                border: 1px solid #2D3561;
            }
            
            .modal-header {
                background: linear-gradient(135deg, #FF6B35, #E85A2B);
                padding: 20px 24px;
                border-radius: 16px 16px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .modal-title {
                font-size: 20px;
                font-weight: 700;
                color: white;
            }
            
            .modal-close {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 20px;
                color: white;
            }
            
            .modal-body {
                padding: 24px;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .form-label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #A0AEC0;
                font-size: 14px;
            }
            
            .form-input, .form-select, .form-textarea {
                width: 100%;
                padding: 12px 16px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid #2D3561;
                border-radius: 8px;
                color: white;
                font-size: 14px;
            }
            
            .form-input:focus, .form-select:focus, .form-textarea:focus {
                outline: none;
                border-color: #FF6B35;
            }
            
            .form-textarea {
                resize: vertical;
                min-height: 80px;
            }
            
            .modal-actions {
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                margin-top: 24px;
            }
            
            .btn-cancel {
                padding: 12px 24px;
                background: rgba(239, 71, 111, 0.1);
                border: 1px solid #EF476F;
                color: #EF476F;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
            }
            
            .btn-submit {
                padding: 12px 24px;
                background: #FF6B35;
                border: none;
                color: white;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
            }
        `;
        
        // Adicionar styles
        const styleSheet = document.createElement("style");
        styleSheet.textContent = modalStyles;
        document.head.appendChild(styleSheet);
        
        function novoServico() {
            fecharModal();
            const modalHTML = `
                <div class="modal-overlay active" id="modalServico">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">
                                <i class="fas fa-plus-circle"></i> Novo Serviço
                            </h3>
                            <button class="modal-close" onclick="fecharModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="formServico" onsubmit="salvarServico(event)">
                                <input type="hidden" id="servicoId" name="id" value="">
                                
                                <div class="form-group">
                                    <label class="form-label">Nome do Serviço *</label>
                                    <input type="text" class="form-input" id="servicoNome" name="nome" required placeholder="Ex: Pista de Led 4x4">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Categoria *</label>
                                    <select class="form-select" id="servicoCategoria" name="categoria" required>
                                        <option value="som_iluminacao">🎵 Som e Iluminação</option>
                                        <option value="decoracao">🎨 Decoração</option>
                                        <option value="audiovisual">📹 Audiovisual</option>
                                        <option value="outros">⚙️ Outros</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Valor Base *</label>
                                    <input type="number" step="0.01" min="0" class="form-input" id="servicoValor" name="valor_base" required placeholder="0.00">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Descrição</label>
                                    <textarea class="form-textarea" id="servicoDescricao" name="descricao" placeholder="Descrição opcional do serviço"></textarea>
                                </div>
                                
                                <div class="modal-actions">
                                    <button type="button" class="btn-cancel" onclick="fecharModal()">Cancelar</button>
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-check"></i> Salvar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        function editarServico(id) {
            fecharModal();
            const servico = todosServicos.find(s => s.id == id);
            if (!servico) {
                alert('Serviço não encontrado');
                return;
            }

            const valorAtual = parseFloat(servico.valor_base || servico.valor_padrao || 0);

            const modalHTML = `
                <div class="modal-overlay active" id="modalServico">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">
                                <i class="fas fa-edit"></i> Editar Serviço
                            </h3>
                            <button class="modal-close" onclick="fecharModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="formServico" onsubmit="salvarServico(event)">
                                <input type="hidden" id="servicoId" name="id" value="${servico.id}">

                                <div class="form-group">
                                    <label class="form-label">Nome do Serviço *</label>
                                    <input type="text" class="form-input" id="servicoNome" name="nome" required value="${servico.nome}">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Categoria *</label>
                                    <select class="form-select" id="servicoCategoria" name="categoria" required>
                                        <option value="som_iluminacao" ${servico.categoria === 'som_iluminacao' ? 'selected' : ''}>🎵 Som e Iluminação</option>
                                        <option value="decoracao" ${servico.categoria === 'decoracao' ? 'selected' : ''}>🎨 Decoração</option>
                                        <option value="audiovisual" ${servico.categoria === 'audiovisual' ? 'selected' : ''}>📹 Audiovisual</option>
                                        <option value="outros" ${servico.categoria === 'outros' ? 'selected' : ''}>⚙️ Outros</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Valor Base *</label>
                                    <input type="number" step="0.01" min="0" class="form-input" id="servicoValor" name="valor_base" required value="${valorAtual}">
                                    <small style="color: #A0AEC0; font-size: 11px; margin-top: 6px; display: block;">
                                        <i class="fas fa-info-circle"></i> Alterar o valor aqui afeta apenas novos orçamentos. Eventos já fechados mantêm o valor original.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Descrição</label>
                                    <textarea class="form-textarea" id="servicoDescricao" name="descricao">${servico.descricao || ''}</textarea>
                                </div>

                                <div class="modal-actions">
                                    <button type="button" class="btn-cancel" onclick="fecharModal()">Cancelar</button>
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-check"></i> Atualizar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        function fecharModal() {
            const modal = document.getElementById('modalServico');
            if (modal) {
                modal.remove();
            }
        }
        
        async function salvarServico(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            const id = formData.get('id');
            const action = id ? 'update' : 'create';
            formData.append('action', action);

            // Garantir que o valor seja enviado também como 'valor' para compatibilidade
            const valorBase = formData.get('valor_base');
            if (valorBase && !formData.get('valor')) {
                formData.append('valor', valorBase);
            }

            // Desabilitar botão para evitar duplo clique
            const btnSubmit = form.querySelector('.btn-submit');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            }

            try {
                const response = await fetch('../api/servicos.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    fecharModal();
                    await carregarServicos();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fas fa-check"></i> ' + (action === 'update' ? 'Atualizar' : 'Salvar');
                    }
                }
            } catch (error) {
                console.error('Erro ao salvar serviço:', error);
                alert('Erro ao salvar serviço. Verifique sua conexão.');
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-check"></i> ' + (action === 'update' ? 'Atualizar' : 'Salvar');
                }
            }
        }
        
        
        async function excluirServico(id) {
            if (!confirm('Tem certeza que deseja excluir este serviço?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                const response = await fetch('../api/servicos.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Serviço excluído com sucesso!');
                    carregarServicos();
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao excluir serviço');
            }
        }
        
        // Utilitários
        function formatarMoeda(valor) {
            return 'R$ ' + parseFloat(valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Aplicar filtros
        document.getElementById('filtroMes').addEventListener('change', carregarServicos);
        document.getElementById('filtroAno').addEventListener('change', carregarServicos);
        document.getElementById('filtroVisualizacao').addEventListener('change', carregarServicos);
    </script>

<?php include '../includes/footer.php'; ?>

<script>
(function(){
    if(window.innerWidth <= 768){
        var grid = document.querySelector('.svc-stats .stats-grid');
        if(grid){
            grid.style.cssText = 'display:grid;grid-template-columns:repeat(2,1fr);gap:10px;max-width:100%;overflow:hidden;margin-bottom:30px;';
        }
        document.querySelectorAll('.svc-stats .stat-card').forEach(function(c){
            c.style.minWidth = '0';
            c.style.overflow = 'hidden';
        });
        document.querySelectorAll('.svc-stats .stat-info').forEach(function(i){
            i.style.minWidth = '0';
            i.style.overflow = 'hidden';
        });
    }
})();
</script>

<!-- Mobile fix final - último na cascata -->
<style>
@media (max-width: 768px) {
    .svc-stats .stats-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }
    .svc-stats .stat-card {
        min-width: 0 !important;
        overflow: hidden !important;
        padding: 10px 8px !important;
        gap: 6px !important;
    }
    .svc-stats .stat-card .stat-icon {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        font-size: 15px !important;
        border-radius: 8px !important;
    }
    .svc-stats .stat-card .stat-info {
        min-width: 0 !important;
        overflow: hidden !important;
    }
    .svc-stats .stat-card .stat-info .stat-value {
        font-size: 13px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .svc-stats .stat-card .stat-info .stat-label {
        font-size: 8px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        letter-spacing: 0.3px !important;
    }
}
</style>
</body>
</html>
