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
    <title>Gestão de Custos - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #0A0E27;
            color: #FFFFFF;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-title {
            font-size: 36px;
            font-weight: 800;
            color: #FF6B35;
            margin-bottom: 8px;
        }
        
        .page-subtitle {
            color: #A0AEC0;
            font-size: 16px;
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
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .tab {
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid #2D3561;
            border-radius: 8px;
            color: #A0AEC0;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab:hover {
            background: rgba(255, 107, 53, 0.1);
            border-color: #FF6B35;
            color: #FF6B35;
        }
        
        .tab.active {
            background: #FF6B35;
            border-color: #FF6B35;
            color: white;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #141B3D;
            border: 1px solid #2D3561;
            border-radius: 16px;
            padding: 24px;
            position: relative;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #FF6B35;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.2);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #FF6B35, #FFD23F);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .stat-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #A0AEC0;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .stat-info {
            font-size: 13px;
            color: #A0AEC0;
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
        
        .btn-new {
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
        
        .btn-new:hover {
            background: #E85A2B;
            transform: scale(1.05);
        }
        
        /* Cost List */
        .costs-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .cost-item {
            background: #141B3D;
            border: 1px solid #2D3561;
            border-left: 4px solid #EF476F;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .cost-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 30px rgba(239, 71, 111, 0.2);
        }
        
        .cost-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .cost-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: rgba(239, 71, 111, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #EF476F;
        }
        
        .cost-details {
            flex: 1;
        }
        
        .cost-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .cost-date {
            font-size: 13px;
            color: #A0AEC0;
        }
        
        .cost-value {
            font-size: 22px;
            font-weight: 800;
            color: #EF476F;
        }
        
        .cost-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit {
            background: rgba(255, 210, 63, 0.1);
            color: #FFD23F;
            border: 1px solid #FFD23F;
        }
        
        .btn-delete {
            background: rgba(239, 71, 111, 0.1);
            color: #EF476F;
            border: 1px solid #EF476F;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #A0AEC0;
        }
        
        .empty-icon {
            font-size: 64px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
            .cost-item { flex-direction: column; gap: 15px; }
            .cost-actions { width: 100%; justify-content: flex-end; }
            .section-header { flex-direction: column !important; gap: 10px !important; align-items: flex-start !important; }
            .filter-select { min-width: unset !important; width: 100% !important; }
            .filters-row { flex-direction: column !important; align-items: stretch !important; }
            .filter-group { width: 100% !important; }
            .tabs { flex-wrap: wrap !important; gap: 6px !important; }
            .tab { padding: 8px 14px !important; font-size: 12px !important; }
            .modal-content { max-width: 100% !important; margin: 10px !important; }
            .form-row { grid-template-columns: 1fr !important; }
            .modal-footer { flex-direction: column !important; }
            .modal-footer .btn-cancel, .modal-footer .btn-submit { width: 100% !important; text-align: center !important; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
        }
    </style>
</head>
<body>
    <?php include '../includes/menu.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Gestão de Custos</h1>
            <p class="page-subtitle">Controle todos os gastos da empresa e dos eventos</p>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar"></i> Mês</label>
                    <select class="filter-select" id="filtroMes">
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
                    <i class="fas fa-broom"></i> Limpar
                </button>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" data-tab="gerais" onclick="mudarAba('gerais', this)">
                <i class="fas fa-building"></i>
                Custos Gerais da Empresa
            </div>
            <div class="tab" data-tab="eventos" onclick="mudarAba('eventos', this)">
                <i class="fas fa-calendar-alt"></i>
                Custos por Evento
            </div>
        </div>
        
        <!-- Tab Content: Custos Gerais -->
        <div id="tab-gerais" class="tab-content active">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💸</div>
                    <div class="stat-label">Total do Mês</div>
                    <div class="stat-value" id="totalMesGerais">R$ 0,00</div>
                    <div class="stat-info">Custos gerais</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⛽</div>
                    <div class="stat-label">Combustível</div>
                    <div class="stat-value" id="combustivel">R$ 0,00</div>
                    <div class="stat-info">Transportes</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-label">Impostos</div>
                    <div class="stat-value" id="impostos">R$ 0,00</div>
                    <div class="stat-info">Tributos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📢</div>
                    <div class="stat-label">Marketing</div>
                    <div class="stat-value" id="marketing">R$ 0,00</div>
                    <div class="stat-info">Publicidade</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💼</div>
                    <div class="stat-label">Outros</div>
                    <div class="stat-value" id="outros">R$ 0,00</div>
                    <div class="stat-info">Diversos</div>
                </div>
            </div>
            
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Histórico de Custos Gerais
                </h2>
                <button class="btn-new" onclick="novoCustoGeral()">
                    <i class="fas fa-plus"></i>
                    + NOVO CUSTO
                </button>
            </div>
            
            <!-- Costs List -->
            <div class="costs-list" id="custosGeraisList">
                <div class="empty-state">
                    <div class="empty-icon">💸</div>
                    <p>Carregando custos gerais...</p>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: Custos por Evento -->
        <div id="tab-eventos" class="tab-content">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-label">Total Geral de Custos</div>
                    <div class="stat-value" id="totalGeralEventos">R$ 0,00</div>
                    <div class="stat-info">Soma de todos os custos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-label">Custos do Mês</div>
                    <div class="stat-value" id="totalMesEventos">R$ 0,00</div>
                    <div class="stat-info">Custos de eventos este mês</div>
                </div>
            </div>
            
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Histórico de Custos de Eventos
                </h2>
            </div>
            
            <!-- Costs List -->
            <div class="costs-list" id="custosEventosList">
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <p>Carregando custos de eventos...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let custosGerais = [];
        let custosEventos = [];
        
        document.addEventListener('DOMContentLoaded', () => {
            const mes = new Date().getMonth() + 1;
            document.getElementById('filtroMes').value = mes;
            carregarCustos();
        });
        
        function mudarAba(aba, element) {
            // Atualizar tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
            
            // Atualizar conteúdo
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + aba).classList.add('active');
        }
        
        async function carregarCustos() {
            await Promise.all([
                carregarCustosGerais(),
                carregarCustosEventos()
            ]);
        }
        
        async function carregarCustosGerais() {
            try {
                const response = await fetch('../api/financeiro.php?action=custos_gerais');
                const data = await response.json();
                
                if (data.success && data.custos) {
                    custosGerais = data.custos;
                    atualizarEstatisticasGerais();
                    renderizarCustosGerais();
                } else {
                    document.getElementById('custosGeraisList').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon">💸</div>
                            <p>Nenhum custo geral cadastrado</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        async function carregarCustosEventos() {
            try {
                const response = await fetch('../api/financeiro.php?action=custos_eventos');
                const data = await response.json();
                
                if (data.success && data.custos) {
                    custosEventos = data.custos;
                    atualizarEstatisticasEventos();
                    renderizarCustosEventos();
                } else {
                    document.getElementById('custosEventosList').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon">📅</div>
                            <p>Nenhum custo de evento cadastrado</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }
        
        function atualizarEstatisticasGerais() {
            const categorias = {
                combustivel: 0,
                impostos: 0,
                marketing: 0,
                outros: 0
            };
            
            let total = 0;
            
            custosGerais.forEach(c => {
                const valor = parseFloat(c.valor || 0);
                total += valor;
                
                const cat = c.categoria?.toLowerCase() || 'outros';
                if (categorias.hasOwnProperty(cat)) {
                    categorias[cat] += valor;
                } else {
                    categorias.outros += valor;
                }
            });
            
            document.getElementById('totalMesGerais').textContent = formatarMoeda(total);
            document.getElementById('combustivel').textContent = formatarMoeda(categorias.combustivel);
            document.getElementById('impostos').textContent = formatarMoeda(categorias.impostos);
            document.getElementById('marketing').textContent = formatarMoeda(categorias.marketing);
            document.getElementById('outros').textContent = formatarMoeda(categorias.outros);
        }
        
        function atualizarEstatisticasEventos() {
            const total = custosEventos.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
            
            document.getElementById('totalGeralEventos').textContent = formatarMoeda(total);
            document.getElementById('totalMesEventos').textContent = formatarMoeda(total);
        }
        
        function renderizarCustosGerais() {
            const container = document.getElementById('custosGeraisList');
            
            if (custosGerais.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">💸</div>
                        <p>Nenhum custo geral encontrado</p>
                    </div>
                `;
                return;
            }
            
            const icones = {
                combustivel: '⛽',
                impostos: '📋',
                marketing: '📢',
                outros: '💼'
            };
            
            container.innerHTML = custosGerais.map(custo => {
                const cat = custo.categoria?.toLowerCase() || 'outros';
                const icone = icones[cat] || '💸';
                
                return `
                    <div class="cost-item">
                        <div class="cost-info">
                            <div class="cost-icon">${icone}</div>
                            <div class="cost-details">
                                <div class="cost-name">${custo.descricao || 'Sem descrição'}</div>
                                <div class="cost-date">${formatarData(custo.data_custo)}</div>
                            </div>
                        </div>
                        <div class="cost-value">${formatarMoeda(custo.valor)}</div>
                        <div class="cost-actions">
                            <button class="btn-action btn-edit" onclick="editarCusto(${custo.id}, 'geral')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="excluirCusto(${custo.id}, 'geral')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function renderizarCustosEventos() {
            const container = document.getElementById('custosEventosList');
            
            if (custosEventos.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <p>Nenhum custo de evento encontrado</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = custosEventos.map(custo => `
                <div class="cost-item">
                    <div class="cost-info">
                        <div class="cost-icon">🎉</div>
                        <div class="cost-details">
                            <div class="cost-name">${custo.descricao || 'Sem descrição'}</div>
                            <div class="cost-date">${formatarData(custo.data_custo)} • ${custo.evento_nome || 'Evento'}</div>
                        </div>
                    </div>
                    <div class="cost-value">${formatarMoeda(custo.valor)}</div>
                    <div class="cost-actions">
                        <button class="btn-action btn-edit" onclick="editarCusto(${custo.id}, 'evento')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="excluirCusto(${custo.id}, 'evento')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        function limparFiltros() {
            const mes = new Date().getMonth() + 1;
            document.getElementById('filtroMes').value = mes;
            document.getElementById('filtroAno').value = '2026';
            document.getElementById('filtroVisualizacao').value = 'mensal';
            carregarCustos();
        }
        
        function novoCustoGeral() {
            alert('Função de novo custo geral será implementada em breve.\n\nPor enquanto, você pode adicionar custos via:\nFinanceiro → Movimentações → Nova Despesa');
        }
        
        function editarCusto(id, tipo) {
            alert('Função de edição será implementada');
        }
        
        async function excluirCusto(id, tipo) {
            if (!confirm('Tem certeza que deseja excluir este custo?')) return;
            
            alert('Função de exclusão será implementada');
        }
        
        function formatarMoeda(valor) {
            return 'R$ ' + parseFloat(valor || 0).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        function formatarData(data) {
            if (!data) return '--/--/----';
            return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
        }
        
        document.getElementById('filtroMes').addEventListener('change', carregarCustos);
        document.getElementById('filtroAno').addEventListener('change', carregarCustos);
    </script>
<?php include '../includes/footer.php'; ?>
</body>
</html>
