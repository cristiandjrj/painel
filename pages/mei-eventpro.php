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
    <title>Controle MEI - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <style>
        .mei-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .mei-table th { padding: 12px; text-align: left; border: 1px solid var(--border); background: var(--bg-card); font-weight: 700; }
        .mei-table td { padding: 12px; border: 1px solid var(--border); }
        .mei-table th:nth-child(5), .mei-table td:nth-child(5) { text-align: right; }
        .mei-table th:nth-child(6), .mei-table td:nth-child(6) { text-align: center; width: 80px; }
        .mei-table tfoot td { font-weight: 700; border-top: 2px solid var(--border); background: var(--bg-card); }
        .mei-table tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
        .mei-table .edit-input { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white; padding: 6px 8px; border-radius: 4px; font-size: 12px; width: 100%; }
        .mei-table .edit-input:focus { border-color: #FF6B35; outline: none; }

        .progress-container { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .progress-bar { flex: 1; background: rgba(255,255,255,0.1); height: 10px; border-radius: 5px; overflow: hidden; }
        .progress-fill { height: 100%; transition: all 0.3s; border-radius: 5px; }
        .progress-label { font-weight: 700; min-width: 50px; text-align: right; }

        .cnpj-card { padding: 20px; border: 2px solid var(--border); border-radius: 12px; margin-bottom: 16px; background: rgba(255,255,255,0.02); transition: all 0.2s; cursor: pointer; }
        .cnpj-card.ativo { border-color: var(--success); background: rgba(6, 214, 160, 0.05); cursor: default; }
        .cnpj-card:not(.ativo):hover { border-color: #FF6B35; background: rgba(255, 107, 53, 0.05); }
        .cnpj-radio { width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--border); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.2s; }
        .cnpj-card.ativo .cnpj-radio { border-color: var(--success); background: var(--success); }
        .cnpj-card.ativo .cnpj-radio::after { content: ''; width: 8px; height: 8px; border-radius: 50%; background: white; }
        .cnpj-card:not(.ativo):hover .cnpj-radio { border-color: #FF6B35; }

        .btn-action { background: none; border: none; cursor: pointer; font-size: 16px; padding: 4px; }
        .btn-action:hover { opacity: 0.7; }

        /* ===== CNPJ QUICK SWITCH ===== */
        .cnpj-pills { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 16px; }
        .cnpj-pill {
            padding: 6px 16px; border: 1px solid var(--border); border-radius: 20px;
            background: var(--bg-card); color: var(--text-secondary);
            cursor: pointer; font-size: 13px; font-family: inherit;
            transition: all 0.2s; white-space: nowrap;
        }
        .cnpj-pill:hover:not(.active) { border-color: #FF6B35; color: #FF6B35; }
        .cnpj-pill.active { background: #FF6B35; border-color: #FF6B35; color: #fff; font-weight: 600; }

        @media print {
            body * { visibility: hidden; }
            #meiRelatorioCard, #meiRelatorioCard * { visibility: visible; }
            #meiRelatorioCard { position: absolute; left: 0; top: 0; width: 100%; }
            .navbar, .page-header, .stats-grid, button, .filters-section { display: none !important; }
            .card { box-shadow: none; border: 1px solid #000; }
            table { page-break-inside: avoid; }
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
            table { display: block; overflow-x: auto; }
            .mei-pills { flex-wrap: wrap !important; }
        }
        @media (max-width: 480px) {
            .container { padding: 12px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 8px !important; }
            .filters-row { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <!-- Cabeçalho -->
    <div class="page-header">
        <h1 class="page-title">Controle MEI</h1>
        <p class="page-subtitle">Relatório Mensal das Receitas Brutas - Anexo I da Resolução CGSN n 140/2018</p>
    </div>

    <!-- Aviso sobre dinheiro -->
    <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin-bottom: 24px; display: flex; align-items: start; gap: 12px;">
        <span style="font-size: 20px;">i</span>
        <div style="flex: 1; font-size: 13px;">
            <strong style="color: #ffc107;">Importante:</strong>
            <span style="color: var(--text-secondary);"> Pagamentos recebidos em <strong style="color: var(--text-primary);">Dinheiro</strong> NAO sao contabilizados neste relatório, pois nao geram nota fiscal/comprovante bancário.</span>
        </div>
    </div>

    <!-- Cards de Controle de Limite MEI -->
    <div class="stats-grid" style="margin-bottom: 24px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
        <!-- Card Limite Mensal -->
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.2); color: #4F46E5;"><i class="fas fa-chart-bar"></i></div>
            <div class="stat-info">
                <div class="stat-label">Faturamento Mensal</div>
                <div class="stat-value" id="meiMensal">R$ 0,00</div>
            </div>
            <div style="margin-top: 12px;">
                <div class="progress-container">
                    <div class="progress-bar">
                        <div id="meiMensalBarra" class="progress-fill" style="background: var(--success); width: 0%;"></div>
                    </div>
                    <span id="meiMensalPercent" class="progress-label">0%</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Limite: <strong style="color: var(--text-primary);">R$ 6.750,00/mes</strong>
                </div>
            </div>
        </div>

        <!-- Card Limite Anual -->
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
                <div class="stat-label">Faturamento Anual</div>
                <div class="stat-value" id="meiAnual">R$ 0,00</div>
            </div>
            <div style="margin-top: 12px;">
                <div class="progress-container">
                    <div class="progress-bar">
                        <div id="meiAnualBarra" class="progress-fill" style="background: var(--warning); width: 0%;"></div>
                    </div>
                    <span id="meiAnualPercent" class="progress-label">0%</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Limite: <strong style="color: var(--text-primary);">R$ 81.000,00/ano</strong>
                </div>
            </div>
        </div>

        <!-- Card Status -->
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(6, 214, 160, 0.2); color: #06D6A0;"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Status MEI</div>
                <div class="stat-value" id="meiStatus" style="font-size: 20px;">Regular</div>
            </div>
            <div style="margin-top: 12px;">
                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">
                    <span id="meiMesesRestantes">12 meses</span> ate fim do ano
                </div>
                <button onclick="abrirModalGerenciarCNPJs()" class="btn btn-secondary" style="width: 100%; padding: 8px; font-size: 13px;">
                    <i class="fas fa-building"></i> Gerenciar CNPJs
                </button>
            </div>
        </div>
    </div>

    <!-- Troca Rápida de CNPJ -->
    <div class="cnpj-pills" id="cnpjQuickSwitch">
        <button class="cnpj-pill active" onclick="selecionarCNPJ('todos')">
            <i class="fas fa-chart-bar"></i> Todos
        </button>
    </div>

    <!-- Seletor de Mes/Ano/CNPJ -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Mes de Referencia</label>
                    <select id="meiMes" class="form-input" onchange="carregarRelatorioMei()">
                        <option value="1">Janeiro</option>
                        <option value="2">Fevereiro</option>
                        <option value="3">Marco</option>
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
                <div class="form-group">
                    <label class="form-label">Ano</label>
                    <select id="meiAno" class="form-input" onchange="carregarRelatorioMei()">
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">CNPJ MEI</label>
                    <select id="meiCNPJ" class="form-input" onchange="selecionarCNPJ(this.value)">
                        <option value="todos">Todos os CNPJs</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button onclick="preencherAutomatico()" class="btn btn-secondary" style="width: 100%;">
                        <i class="fas fa-save"></i> Salvar no Historico
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Relatório (Tabela) -->
    <div class="card" id="meiRelatorioCard">
        <div style="padding: 24px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">
                    RELATÓRIO MENSAL DAS RECEITAS BRUTAS
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary);">
                    Anexo I da Resolução CGSN n 140/2018 - Art. 104-A
                </p>
            </div>

            <!-- Tabela do Relatório -->
            <div style="overflow-x: auto;">
                <table class="mei-table">
                    <thead>
                        <tr>
                            <th>DATA</th>
                            <th>IDENTIFICACAO DO CLIENTE</th>
                            <th>CPF/CNPJ</th>
                            <th>DOCUMENTO FISCAL</th>
                            <th style="text-align: right;">VALOR (R$)</th>
                            <th style="text-align: center; width: 80px;">ACOES</th>
                        </tr>
                    </thead>
                    <tbody id="meiTabelaBody">
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-secondary);">
                                Carregando receitas...
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;">
                                TOTAL DAS RECEITAS DO MES:
                            </td>
                            <td id="meiTotalMes" style="text-align: right; font-size: 16px; color: var(--success);">
                                R$ 0,00
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Botoes de Acao -->
            <div style="display: flex; gap: 12px; margin-top: 24px; flex-wrap: wrap;">
                <button onclick="adicionarLinhaMei()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Receita
                </button>
                <button onclick="salvarRelatorioMei()" class="btn btn-secondary">
                    <i class="fas fa-save"></i> Salvar Relatório
                </button>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button onclick="exportarMeiPDF()" class="btn btn-secondary">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
            </div>

            <!-- Observacoes -->
            <div style="margin-top: 32px; padding: 16px; background: rgba(79, 70, 229, 0.1); border-radius: 8px; border-left: 4px solid #4F46E5;">
                <h4 style="margin-bottom: 8px; color: #4F46E5;">Observacoes Importantes:</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: var(--text-secondary); line-height: 1.8;">
                    <li>Este relatório deve ser preenchido mensalmente pelo MEI</li>
                    <li>Registre todas as receitas brutas do mes, mesmo sem emissao de nota fiscal</li>
                    <li>O documento fiscal pode ser: NF-e, Nota Fiscal Avulsa, Recibo ou "Sem documento"</li>
                    <li>Guarde este relatório por 5 anos para fins de fiscalizacao</li>
                    <li>As receitas dos eventos sao carregadas automaticamente ao selecionar o CNPJ e periodo</li>
                    <li>Use "Salvar no Historico" para gravar os dados no banco para consulta futura</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gerenciar CNPJs MEI -->
<div id="modalGerenciarCNPJs" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-building"></i> Gerenciar CNPJs MEI</h2>
            <button class="close-btn" onclick="fecharModal('modalGerenciarCNPJs')">&times;</button>
        </div>

        <div>
            <div style="margin-bottom: 20px;">
                <button onclick="abrirModalAdicionarCNPJ()" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus"></i> Adicionar Novo CNPJ
                </button>
            </div>

            <div id="listaCNPJsMEI">
                <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                    <div style="font-size: 48px; margin-bottom: 16px;"><i class="fas fa-building"></i></div>
                    <h3 style="margin: 0 0 8px 0; font-size: 18px;">Nenhum CNPJ cadastrado</h3>
                    <p style="margin: 0; font-size: 14px;">Adicione um CNPJ para comecar a controlar seus limites MEI</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar/Editar CNPJ MEI -->
<div id="modalAdicionarCNPJ" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2 class="modal-title" id="tituloModalCNPJ"><i class="fas fa-plus"></i> Novo CNPJ MEI</h2>
            <button class="close-btn" onclick="fecharModal('modalAdicionarCNPJ')">&times;</button>
        </div>

        <form id="formMEI" onsubmit="salvarCNPJ_MEI(event)">
            <input type="hidden" id="meiEditId">

            <div class="form-group">
                <label class="form-label">CNPJ</label>
                <input type="text" class="form-input" id="meiCNPJInput" required
                       placeholder="00.000.000/0000-00"
                       maxlength="18"
                       oninput="formatarCNPJInput(this)">
            </div>

            <div class="form-group">
                <label class="form-label">Nome/Identificacao</label>
                <input type="text" class="form-input" id="meiNomeInput" required
                       placeholder="Ex: EventProDJ - MEI Principal">
            </div>

            <div class="form-group">
                <label class="form-label">Contas Bancarias Vinculadas</label>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px;">
                    Selecione as contas que pertencem a este CNPJ
                </div>
                <div id="meiContasCheckboxes" style="display: grid; gap: 10px; max-height: 200px; overflow-y: auto; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                    <div style="color: var(--text-secondary); font-size: 13px;">Carregando contas...</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                <i class="fas fa-save"></i> Salvar CNPJ
            </button>
        </form>
    </div>
</div>

<script>
    // ===== VARIAVEIS GLOBAIS =====
    let receitasMei = [];
    let cnpjsMei = [];
    let contasBancarias = [];

    const LIMITE_MENSAL = 6750;
    const LIMITE_ANUAL = 81000;

    const MESES_NOME = ['', 'Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho',
                         'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

    // ===== INICIALIZACAO =====
    document.addEventListener('DOMContentLoaded', async () => {
        initAnoSelector();
        await carregarContasBancarias();
        await carregarCNPJs();
        await carregarRelatorioMei();
        await calcularLimiteMEI();
    });

    function initAnoSelector() {
        const anoSelect = document.getElementById('meiAno');
        const anoAtual = new Date().getFullYear();

        for (let ano = anoAtual - 2; ano <= anoAtual + 1; ano++) {
            const option = document.createElement('option');
            option.value = ano;
            option.textContent = ano;
            if (ano === anoAtual) option.selected = true;
            anoSelect.appendChild(option);
        }

        // Selecionar mes atual
        document.getElementById('meiMes').value = new Date().getMonth() + 1;
    }

    async function carregarContasBancarias() {
        try {
            const response = await fetch('../api/contas-bancarias.php?action=list');
            const data = await response.json();
            if (data.success) {
                contasBancarias = data.contas || [];
            }
        } catch (error) {
            console.error('Erro ao carregar contas:', error);
        }
    }

    // ===== CNPJ MANAGEMENT =====
    async function carregarCNPJs() {
        try {
            const response = await fetch('../api/mei.php?action=list_cnpjs');
            const data = await response.json();

            if (data.success) {
                cnpjsMei = data.cnpjs || [];
                atualizarSeletorCNPJ();
            }
        } catch (error) {
            console.error('Erro ao carregar CNPJs:', error);
        }
    }

    function atualizarSeletorCNPJ() {
        const select = document.getElementById('meiCNPJ');
        select.innerHTML = '<option value="todos">Todos os CNPJs</option>';

        if (cnpjsMei.length === 0) {
            const option = document.createElement('option');
            option.value = 'cadastrar';
            option.textContent = 'Cadastre um CNPJ primeiro';
            option.disabled = true;
            select.appendChild(option);
            renderizarPillsCNPJ();
            return;
        }

        cnpjsMei.forEach(mei => {
            const option = document.createElement('option');
            option.value = mei.id;
            option.textContent = `${mei.nome} - ${mei.cnpj}`;
            if (mei.ativo) option.selected = true;
            select.appendChild(option);
        });

        renderizarPillsCNPJ();
    }

    function renderizarPillsCNPJ() {
        const container = document.getElementById('cnpjQuickSwitch');
        if (!container) return;

        const selectedId = String(document.getElementById('meiCNPJ').value);

        let html = `
            <span style="font-size: 12px; color: var(--text-secondary); white-space: nowrap; align-self: center;">
                <i class="fas fa-building"></i> CNPJ:
            </span>
            <button onclick="selecionarCNPJ('todos')"
                    class="cnpj-pill ${selectedId === 'todos' ? 'active' : ''}">
                <i class="fas fa-chart-bar"></i> Todos
            </button>
        `;

        cnpjsMei.forEach(mei => {
            const isActive = String(mei.id) === selectedId;
            html += `
                <button onclick="selecionarCNPJ(${mei.id})"
                        class="cnpj-pill ${isActive ? 'active' : ''}"
                        title="${mei.cnpj}">
                    <i class="fas fa-id-card"></i> ${mei.nome}
                </button>
            `;
        });

        container.innerHTML = html;
    }

    // Setar .value programaticamente nao dispara onchange — sem risco de loop
    function selecionarCNPJ(id) {
        document.getElementById('meiCNPJ').value = id;
        renderizarPillsCNPJ();
        carregarRelatorioMei();
    }

    function abrirModalGerenciarCNPJs() {
        renderListaCNPJs();
        document.getElementById('modalGerenciarCNPJs').classList.add('active');
    }

    function renderListaCNPJs() {
        const container = document.getElementById('listaCNPJsMEI');

        if (cnpjsMei.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                    <div style="font-size: 48px; margin-bottom: 16px;"><i class="fas fa-building"></i></div>
                    <h3 style="margin: 0 0 8px 0; font-size: 18px;">Nenhum CNPJ cadastrado</h3>
                    <p style="margin: 0; font-size: 14px;">Adicione um CNPJ para comecar a controlar seus limites MEI</p>
                </div>
            `;
            return;
        }

        container.innerHTML = cnpjsMei.map(mei => {
            const contasNomes = (mei.contas_nomes || []).join(', ') || 'Nenhuma conta vinculada';
            const isAtivo = mei.ativo == 1;

            return `
                <div class="cnpj-card ${isAtivo ? 'ativo' : ''}" onclick="${!isAtivo ? `ativarMEI(${mei.id}, event)` : 'void(0)'}">
                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px;">
                        <div style="display: flex; align-items: start; gap: 14px; flex: 1; min-width: 0;">
                            <!-- Radio indicator -->
                            <div class="cnpj-radio" style="margin-top: 3px;" title="${isAtivo ? 'CNPJ Ativo' : 'Clique para ativar'}"></div>

                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                                    <h3 style="margin: 0; font-size: 17px; font-weight: 700;">${mei.nome}</h3>
                                    ${isAtivo
                                        ? '<span style="background: var(--success); color: white; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">ATIVO</span>'
                                        : '<span style="border: 1px solid var(--border); color: var(--text-secondary); padding: 3px 10px; border-radius: 6px; font-size: 11px;">clique para ativar</span>'
                                    }
                                </div>
                                <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 8px; font-family: monospace;">
                                    <i class="fas fa-building"></i> ${mei.cnpj}
                                </div>
                                <div style="font-size: 13px; color: var(--text-secondary);">
                                    <i class="fas fa-university"></i> ${contasNomes}
                                </div>
                            </div>
                        </div>

                        <!-- Ações (edit/delete) — stopPropagation para nao ativar o card -->
                        <div style="display: flex; gap: 8px; flex-shrink: 0;">
                            <button onclick="event.stopPropagation(); editarMEI(${mei.id})" class="btn" style="background: rgba(255, 193, 7, 0.2); color: var(--warning); border: 1px solid var(--warning); padding: 6px 12px; font-size: 12px;" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="event.stopPropagation(); excluirMEI(${mei.id})" class="btn" style="background: rgba(239, 71, 111, 0.2); color: var(--danger); border: 1px solid var(--danger); padding: 6px 12px; font-size: 12px;" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function abrirModalAdicionarCNPJ(contasSelecionadas = []) {
        document.getElementById('meiEditId').value = '';
        document.getElementById('meiCNPJInput').value = '';
        document.getElementById('meiNomeInput').value = '';
        document.getElementById('tituloModalCNPJ').innerHTML = '<i class="fas fa-plus"></i> Novo CNPJ MEI';

        preencherContasCheckboxes(contasSelecionadas);

        fecharModal('modalGerenciarCNPJs');
        document.getElementById('modalAdicionarCNPJ').classList.add('active');
    }

    function preencherContasCheckboxes(selecionadas = []) {
        const container = document.getElementById('meiContasCheckboxes');

        if (contasBancarias.length === 0) {
            container.innerHTML = '<div style="color: var(--text-secondary); font-size: 13px;">Nenhuma conta bancaria cadastrada. Cadastre em Financeiro > Gerenciar Contas.</div>';
            return;
        }

        // Converter para numeros para comparacao
        const selNumeros = selecionadas.map(id => parseInt(id));

        container.innerHTML = contasBancarias.map(conta => {
            const checked = selNumeros.includes(parseInt(conta.id)) ? 'checked' : '';
            return `
                <label style="display: flex; align-items: center; gap: 10px; padding: 8px; background: rgba(255,255,255,0.03); border-radius: 6px; cursor: pointer;">
                    <input type="checkbox" value="${conta.id}" ${checked}
                           style="width: 18px; height: 18px; cursor: pointer;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">${conta.nome}</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">${conta.banco || ''} ${conta.tipo || ''}</div>
                    </div>
                </label>
            `;
        }).join('');
    }

    async function salvarCNPJ_MEI(e) {
        e.preventDefault();

        const editId = document.getElementById('meiEditId').value;
        const cnpj = document.getElementById('meiCNPJInput').value;
        const nome = document.getElementById('meiNomeInput').value;

        const checkboxes = document.querySelectorAll('#meiContasCheckboxes input[type="checkbox"]:checked');
        const contas = Array.from(checkboxes).map(cb => parseInt(cb.value));

        try {
            const formData = new FormData();
            formData.append('action', 'save_cnpj');
            formData.append('cnpj', cnpj);
            formData.append('nome', nome);
            formData.append('contas', JSON.stringify(contas));

            if (editId) {
                formData.append('id', editId);
            }

            const response = await fetch('../api/mei.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(editId ? 'CNPJ MEI atualizado com sucesso!' : 'CNPJ MEI cadastrado com sucesso!');
                fecharModal('modalAdicionarCNPJ');
                await carregarCNPJs();
                abrirModalGerenciarCNPJs();
                await calcularLimiteMEI();
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao salvar CNPJ');
        }
    }

    function editarMEI(id) {
        const mei = cnpjsMei.find(m => m.id == id);
        if (!mei) return;

        document.getElementById('meiEditId').value = mei.id;
        document.getElementById('meiCNPJInput').value = mei.cnpj;
        document.getElementById('meiNomeInput').value = mei.nome;
        document.getElementById('tituloModalCNPJ').innerHTML = '<i class="fas fa-edit"></i> Editar CNPJ MEI';

        preencherContasCheckboxes(mei.contas || []);

        fecharModal('modalGerenciarCNPJs');
        document.getElementById('modalAdicionarCNPJ').classList.add('active');
    }

    async function ativarMEI(id, event) {
        if (event) event.stopPropagation();
        try {
            const formData = new FormData();
            formData.append('action', 'activate_cnpj');
            formData.append('id', id);

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                await carregarCNPJs();    // atualiza cnpjsMei + selector + pills
                renderListaCNPJs();       // re-renderiza os cards no modal
                await calcularLimiteMEI(); // atualiza cards de limite
            } else {
                alert('Erro ao ativar CNPJ: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    }

    async function excluirMEI(id) {
        const mei = cnpjsMei.find(m => m.id == id);
        if (!mei) return;

        if (!confirm(`Tem certeza que deseja excluir o CNPJ:\n\n"${mei.nome}"\n${mei.cnpj}\n\nEsta acao nao pode ser desfeita!`)) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete_cnpj');
            formData.append('id', id);

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                alert('CNPJ excluido com sucesso!');
                await carregarCNPJs();
                renderListaCNPJs();
                await calcularLimiteMEI();
                await carregarRelatorioMei();
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    }

    // ===== RELATORIO MEI =====
    async function carregarRelatorioMei() {
        const mes = document.getElementById('meiMes').value;
        const ano = document.getElementById('meiAno').value;
        const cnpjId = document.getElementById('meiCNPJ').value;

        try {
            const response = await fetch(`../api/mei.php?action=get_receitas&mes=${mes}&ano=${ano}&cnpj_id=${cnpjId}`);
            const data = await response.json();

            if (data.success) {
                receitasMei = data.receitas || [];
                renderizarTabelaMei();
                await calcularLimiteMEI();
            }
        } catch (error) {
            console.error('Erro ao carregar relatório:', error);
        }
    }

    function renderizarTabelaMei() {
        const tbody = document.getElementById('meiTabelaBody');

        if (receitasMei.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-secondary);">
                        Nenhuma receita encontrada para este periodo.<br>
                        Adicione eventos com sinais pagos ou clique em "Nova Receita" para adicionar manualmente.
                    </td>
                </tr>
            `;
            document.getElementById('meiTotalMes').textContent = 'R$ 0,00';
            return;
        }

        let total = 0;
        tbody.innerHTML = receitasMei.map((r, index) => {
            const valor = parseFloat(r.valor) || 0;
            total += valor;

            // Formatar data
            let dataFormatada = r.data_receita || r.data || '';
            if (dataFormatada.includes('-')) {
                const partes = dataFormatada.split('-');
                dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
            }

            return `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td>${dataFormatada}</td>
                    <td>${r.cliente || ''}</td>
                    <td>${r.cpf_cnpj ? formatarCpfCnpjString(r.cpf_cnpj) : '-'}</td>
                    <td>${r.documento_fiscal || r.documento || 'Sem documento'}</td>
                    <td style="text-align: right;">${formatMoney(valor)}</td>
                    <td style="text-align: center;">
                        <button onclick="editarLinhaMei(${index})" class="btn-action" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="excluirLinhaMei(${r.id || index})" class="btn-action" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('meiTotalMes').textContent = formatMoney(total);
    }

    function adicionarLinhaMei() {
        const hoje = new Date();
        const mes = document.getElementById('meiMes').value;
        const ano = document.getElementById('meiAno').value;
        const dataDefault = `${ano}-${String(mes).padStart(2, '0')}-${String(hoje.getDate()).padStart(2, '0')}`;

        // Adicionar linha editavel na tabela
        const tbody = document.getElementById('meiTabelaBody');

        // Remover mensagem de vazio se existir
        if (receitasMei.length === 0) {
            tbody.innerHTML = '';
        }

        const tr = document.createElement('tr');
        tr.id = 'novaLinhaMei';
        tr.innerHTML = `
            <td><input type="date" class="edit-input" id="novaData" value="${dataDefault}"></td>
            <td><input type="text" class="edit-input" id="novoCliente" placeholder="Nome do cliente"></td>
            <td><input type="text" class="edit-input" id="novoCpfCnpj" placeholder="CPF/CNPJ" maxlength="18"></td>
            <td><input type="text" class="edit-input" id="novoDocumento" placeholder="Documento fiscal" value="Sem documento"></td>
            <td><input type="number" step="0.01" class="edit-input" id="novoValor" placeholder="0,00" style="text-align: right;"></td>
            <td style="text-align: center;">
                <button onclick="confirmarNovaReceita()" class="btn-action" title="Confirmar" style="color: var(--success);">
                    <i class="fas fa-check"></i>
                </button>
                <button onclick="cancelarNovaReceita()" class="btn-action" title="Cancelar" style="color: var(--danger);">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        document.getElementById('novoCliente').focus();
    }

    async function confirmarNovaReceita() {
        const data = document.getElementById('novaData').value;
        const cliente = document.getElementById('novoCliente').value;
        const cpfCnpj = document.getElementById('novoCpfCnpj').value;
        const documento = document.getElementById('novoDocumento').value;
        const valor = document.getElementById('novoValor').value;

        if (!cliente || !valor) {
            alert('Preencha pelo menos o nome do cliente e o valor.');
            return;
        }

        const cnpjId = document.getElementById('meiCNPJ').value;
        const mes = document.getElementById('meiMes').value;
        const ano = document.getElementById('meiAno').value;

        try {
            const formData = new FormData();
            formData.append('action', 'add_receita');
            formData.append('mes', mes);
            formData.append('ano', ano);
            formData.append('cnpj_id', cnpjId !== 'todos' ? cnpjId : '');
            formData.append('data', data);
            formData.append('cliente', cliente);
            formData.append('cpf_cnpj', cpfCnpj);
            formData.append('documento', documento);
            formData.append('valor', valor);

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                await carregarRelatorioMei();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao adicionar receita');
        }
    }

    function cancelarNovaReceita() {
        const linha = document.getElementById('novaLinhaMei');
        if (linha) linha.remove();

        if (receitasMei.length === 0) {
            renderizarTabelaMei();
        }
    }

    function editarLinhaMei(index) {
        const r = receitasMei[index];
        if (!r) return;

        // Formatar data para input
        let dataValue = r.data_receita || r.data || '';
        if (dataValue.includes('/')) {
            const p = dataValue.split('/');
            dataValue = `${p[2]}-${p[1]}-${p[0]}`;
        }

        const tbody = document.getElementById('meiTabelaBody');
        const rows = tbody.querySelectorAll('tr');
        if (rows[index]) {
            rows[index].innerHTML = `
                <td><input type="date" class="edit-input" id="editData" value="${dataValue}"></td>
                <td><input type="text" class="edit-input" id="editCliente" value="${r.cliente || ''}"></td>
                <td><input type="text" class="edit-input" id="editCpfCnpj" value="${r.cpf_cnpj || ''}" maxlength="18"></td>
                <td><input type="text" class="edit-input" id="editDocumento" value="${r.documento_fiscal || r.documento || ''}"></td>
                <td><input type="number" step="0.01" class="edit-input" id="editValor" value="${r.valor}" style="text-align: right;"></td>
                <td style="text-align: center;">
                    <button onclick="confirmarEdicaoReceita(${r.id}, ${index})" class="btn-action" title="Confirmar" style="color: var(--success);">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="renderizarTabelaMei()" class="btn-action" title="Cancelar" style="color: var(--danger);">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
        }
    }

    async function confirmarEdicaoReceita(id, index) {
        try {
            const formData = new FormData();
            formData.append('action', 'update_receita');
            formData.append('id', id);
            formData.append('data', document.getElementById('editData').value);
            formData.append('cliente', document.getElementById('editCliente').value);
            formData.append('cpf_cnpj', document.getElementById('editCpfCnpj').value);
            formData.append('documento', document.getElementById('editDocumento').value);
            formData.append('valor', document.getElementById('editValor').value);

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                await carregarRelatorioMei();
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    }

    async function excluirLinhaMei(id) {
        if (!confirm('Deseja excluir esta receita?')) return;

        try {
            const formData = new FormData();
            formData.append('action', 'delete_receita');
            formData.append('id', id);

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                await carregarRelatorioMei();
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    }

    // ===== PREENCHER AUTOMATICO =====
    async function preencherAutomatico() {
        const mes = document.getElementById('meiMes').value;
        const ano = document.getElementById('meiAno').value;
        const cnpjId = document.getElementById('meiCNPJ').value;

        try {
            const formData = new FormData();
            formData.append('action', 'auto_fill');
            formData.append('mes', mes);
            formData.append('ano', ano);
            formData.append('cnpj_id', cnpjId);

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Nao ha receitas para este periodo.');
                return;
            }

            const nomeCnpj = cnpjId === 'todos' ? 'todos os CNPJs' : document.getElementById('meiCNPJ').selectedOptions[0].text;
            if (!confirm(`Encontradas ${data.total} receita(s) para ${MESES_NOME[mes]}/${ano}\nCNPJ: ${nomeCnpj}\n\nDeseja importar automaticamente?\n\nATENCAO: Isso substituira as receitas atuais do periodo.`)) {
                return;
            }

            // Salvar receitas importadas
            const saveForm = new FormData();
            saveForm.append('action', 'save_receitas');
            saveForm.append('mes', mes);
            saveForm.append('ano', ano);
            saveForm.append('cnpj_id', cnpjId);
            saveForm.append('receitas', JSON.stringify(data.receitas));

            const saveResponse = await fetch('../api/mei.php', { method: 'POST', body: saveForm });
            const saveData = await saveResponse.json();

            if (saveData.success) {
                alert(`${data.total} receita(s) importada(s) com sucesso!`);
                await carregarRelatorioMei();
            } else {
                alert('Erro ao salvar: ' + saveData.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao preencher automaticamente');
        }
    }

    // ===== SALVAR RELATORIO =====
    async function salvarRelatorioMei() {
        const mes = document.getElementById('meiMes').value;
        const ano = document.getElementById('meiAno').value;
        const cnpjId = document.getElementById('meiCNPJ').value;

        // Montar receitas da tabela
        const receitas = receitasMei.map(r => ({
            data: r.data_receita || r.data,
            cliente: r.cliente,
            cpf_cnpj: r.cpf_cnpj,
            documento: r.documento_fiscal || r.documento,
            valor: r.valor,
            mei_cnpj_id: r.mei_cnpj_id
        }));

        try {
            const formData = new FormData();
            formData.append('action', 'save_receitas');
            formData.append('mes', mes);
            formData.append('ano', ano);
            formData.append('cnpj_id', cnpjId);
            formData.append('receitas', JSON.stringify(receitas));

            const response = await fetch('../api/mei.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                alert('Relatório salvo com sucesso!');
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao salvar relatório');
        }
    }

    // ===== CALCULAR LIMITES MEI =====
    async function calcularLimiteMEI() {
        const cnpjId = document.getElementById('meiCNPJ').value;

        try {
            const response = await fetch(`../api/mei.php?action=get_limites&cnpj_id=${cnpjId}`);
            const data = await response.json();

            if (data.success) {
                atualizarDashboardMEI(data.limites);
            }
        } catch (error) {
            console.error('Erro ao calcular limites:', error);
        }
    }

    function atualizarDashboardMEI(limites) {
        const { totalMes, totalAno, percentMensal, percentAnual, status, mesesRestantes } = limites;

        // Valores
        document.getElementById('meiMensal').textContent = formatMoney(totalMes);
        document.getElementById('meiAnual').textContent = formatMoney(totalAno);

        // Barras de progresso
        const barraMensal = document.getElementById('meiMensalBarra');
        const barraAnual = document.getElementById('meiAnualBarra');

        barraMensal.style.width = percentMensal + '%';
        barraAnual.style.width = percentAnual + '%';

        // Porcentagens
        document.getElementById('meiMensalPercent').textContent = percentMensal.toFixed(1) + '%';
        document.getElementById('meiAnualPercent').textContent = percentAnual.toFixed(1) + '%';

        // Cores das barras
        if (percentMensal >= 100) {
            barraMensal.style.background = '#EF476F';
        } else if (percentMensal >= 80) {
            barraMensal.style.background = '#FFD166';
        } else {
            barraMensal.style.background = '#06D6A0';
        }

        if (percentAnual >= 100) {
            barraAnual.style.background = '#EF476F';
        } else if (percentAnual >= 80) {
            barraAnual.style.background = '#FFD166';
        } else {
            barraAnual.style.background = '#06D6A0';
        }

        // Status
        const elemStatus = document.getElementById('meiStatus');
        let statusIcon = '';
        let statusColor = 'var(--success)';

        if (status === 'LIMITE EXCEDIDO') {
            statusIcon = '<i class="fas fa-exclamation-triangle"></i> ';
            statusColor = 'var(--danger)';
        } else if (status === 'Atenção' || status === 'Próximo do Limite') {
            statusIcon = '<i class="fas fa-exclamation-circle"></i> ';
            statusColor = 'var(--warning)';
        } else {
            statusIcon = '<i class="fas fa-check-circle"></i> ';
        }

        elemStatus.innerHTML = statusIcon + status;
        elemStatus.style.color = statusColor;

        // Meses restantes
        document.getElementById('meiMesesRestantes').textContent =
            mesesRestantes + (mesesRestantes === 1 ? ' mes' : ' meses');
    }

    // ===== EXPORTAR PDF =====
    function exportarMeiPDF() {
        if (receitasMei.length === 0) {
            alert('Nao ha dados para exportar neste periodo.');
            return;
        }

        const mes = document.getElementById('meiMes').value;
        const ano = document.getElementById('meiAno').value;
        const cnpjId = document.getElementById('meiCNPJ').value;
        const nomeCnpj = cnpjId === 'todos' ? 'Todos os CNPJs' : document.getElementById('meiCNPJ').selectedOptions[0].text;

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Cabecalho
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text('RELATÓRIO MENSAL DAS RECEITAS BRUTAS', 105, 20, { align: 'center' });

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text('Anexo I da Resolução CGSN n 140/2018 - Art. 104-A', 105, 27, { align: 'center' });

        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text(`Periodo: ${MESES_NOME[mes]}/${ano}`, 105, 35, { align: 'center' });

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text(`CNPJ: ${nomeCnpj}`, 105, 42, { align: 'center' });

        // Tabela
        const headers = [['DATA', 'CLIENTE', 'CPF/CNPJ', 'DOC. FISCAL', 'VALOR (R$)']];
        const rows = receitasMei.map(r => {
            let dataFormatada = r.data_receita || r.data || '';
            if (dataFormatada.includes('-')) {
                const p = dataFormatada.split('-');
                dataFormatada = `${p[2]}/${p[1]}/${p[0]}`;
            }

            return [
                dataFormatada,
                r.cliente || '',
                r.cpf_cnpj ? formatarCpfCnpjString(r.cpf_cnpj) : '-',
                r.documento_fiscal || r.documento || 'Sem documento',
                formatMoney(parseFloat(r.valor) || 0)
            ];
        });

        doc.autoTable({
            head: headers,
            body: rows,
            startY: 50,
            theme: 'grid',
            styles: { fontSize: 9, cellPadding: 4 },
            headStyles: { fillColor: [79, 70, 229], textColor: 255, fontStyle: 'bold' },
            columnStyles: {
                4: { halign: 'right' }
            }
        });

        // Total
        const total = receitasMei.reduce((sum, r) => sum + (parseFloat(r.valor) || 0), 0);
        const finalY = doc.lastAutoTable.finalY + 10;

        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('TOTAL DAS RECEITAS DO MES:', 20, finalY);
        doc.text(formatMoney(total), 190, finalY, { align: 'right' });

        // Limites
        const limiteY = finalY + 15;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');

        const percentMensal = ((total / LIMITE_MENSAL) * 100).toFixed(1);
        const elemAnual = document.getElementById('meiAnual');
        const totalAnualText = elemAnual ? elemAnual.textContent : 'R$ 0,00';

        doc.text(`Limite Mensal: ${formatMoney(LIMITE_MENSAL)} | Utilizado: ${percentMensal}%`, 20, limiteY);
        doc.text(`Faturamento Anual: ${totalAnualText} | Limite Anual: ${formatMoney(LIMITE_ANUAL)}`, 20, limiteY + 7);

        // Salvar
        doc.save(`relatorio-mei-${MESES_NOME[mes].toLowerCase()}-${ano}.pdf`);
        alert('PDF gerado com sucesso!');
    }

    // ===== UTILIDADES =====
    function formatMoney(valor) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(valor || 0);
    }

    function formatarCpfCnpjString(valor) {
        if (!valor) return '-';
        const numeros = valor.replace(/\D/g, '');
        if (numeros.length === 11) {
            return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        } else if (numeros.length === 14) {
            return numeros.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
        return valor;
    }

    function formatarCNPJInput(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        }
        input.value = value;
    }

    function fecharModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // Fechar modal clicando fora
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
