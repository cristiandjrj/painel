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
    <title>Financeiro Pessoal - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Syne', sans-serif; background: #0A0E27; color: #FFFFFF; }

        :root {
            --primary: #FF6B35;
            --success: #06D6A0;
            --danger: #EF476F;
            --accent: #FFD23F;
            --bg-card: #141B3D;
            --border: #2D3561;
            --text-secondary: #A0AEC0;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 30px; }

        /* Stats: base (tamanho, padding, border-radius, hover) vem do eventprodj-styles.css */
        .stat-card::before { height: 4px; }
        .stat-card.receitas::before { background: var(--success); }
        .stat-card.despesas::before { background: var(--danger); }
        .stat-card.saldo::before { background: var(--primary); }
        .stat-card.contas::before { background: var(--accent); }

        .stat-card.receitas .stat-icon { background: rgba(6, 214, 160, 0.15); }
        .stat-card.despesas .stat-icon { background: rgba(239, 71, 111, 0.15); }
        .stat-card.saldo .stat-icon { background: rgba(255, 107, 53, 0.15); }
        .stat-card.contas .stat-icon { background: rgba(255, 210, 63, 0.15); }

        .stat-card.receitas .stat-value { color: var(--success); }
        .stat-card.despesas .stat-value { color: var(--danger); }
        .stat-card.saldo .stat-value { color: var(--primary); }
        .stat-card.contas .stat-value { color: var(--accent); }
        .stat-change { font-size: 12px; margin-top: 8px; display: flex; align-items: center; gap: 4px; }
        .stat-change.positive { color: var(--success); }
        .stat-change.negative { color: var(--danger); }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }
        .card-title { font-size: 18px; font-weight: 700; }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 14px;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3); }
        .btn-danger { background: rgba(239, 71, 111, 0.2); color: var(--danger); border: 1px solid var(--danger); }
        .btn-danger:hover { background: rgba(239, 71, 111, 0.3); }
        .btn-secondary { background: rgba(255, 255, 255, 0.05); color: var(--text-secondary); border: 1px solid var(--border); }
        .btn-secondary:hover { color: white; border-color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { opacity: 0.9; }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 30px;
            width: 95%;
            max-width: 550px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .modal-title { font-size: 20px; font-weight: 700; }
        .close-btn {
            background: none; border: none;
            color: var(--text-secondary);
            font-size: 28px; cursor: pointer;
        }
        .close-btn:hover { color: white; }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            font-size: 14px;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: var(--primary); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn-submit { width: 100%; margin-top: 10px; padding: 14px; font-size: 16px; }

        /* Lista de itens */
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }
        .item-row:hover { background: rgba(255, 255, 255, 0.02); }
        .item-info h4 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .item-meta { font-size: 12px; color: var(--text-secondary); }
        .item-valor { font-size: 18px; font-weight: 700; }
        .item-valor.positivo { color: var(--success); }
        .item-valor.negativo { color: var(--danger); }
        .item-actions { display: flex; gap: 8px; margin-left: 12px; }
        .item-actions button { padding: 6px 12px; font-size: 12px; }

        /* Conta Card */
        .conta-card {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            padding: 20px;
            margin: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .conta-info h4 { font-size: 16px; margin-bottom: 4px; }
        .conta-info .banco { font-size: 13px; color: var(--text-secondary); }
        .conta-saldo { text-align: right; }
        .conta-saldo .valor { font-size: 24px; font-weight: 700; }
        .conta-saldo .label { font-size: 12px; color: var(--text-secondary); }

        /* Contas a Pagar */
        .resumo-contas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
        }
        .resumo-item { text-align: center; }
        .resumo-item .label { font-size: 13px; color: var(--text-secondary); margin-bottom: 5px; }
        .resumo-item .numero { font-size: 24px; font-weight: 700; }
        .resumo-item .valor { font-size: 12px; }

        .filtros-btn { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; padding: 0 24px; }
        .filtros-btn .btn.active { background: var(--primary); color: white; }

        .filtro-periodo { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; margin-bottom: 16px; align-items: end; padding: 0 24px; }

        .conta-pagar-card {
            margin: 0 24px 12px;
            border-radius: 12px;
            border-left: 4px solid;
            background: rgba(255, 255, 255, 0.02);
            padding: 16px;
        }
        .conta-pagar-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px; }
        .conta-pagar-status { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .conta-pagar-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px; }
        .conta-pagar-actions button { padding: 8px 16px; font-size: 12px; }

        /* Despesas por Categoria */
        .categoria-bar { margin-bottom: 20px; }
        .categoria-bar .bar-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .categoria-bar .bar-header .nome { font-weight: 600; }
        .categoria-bar .bar-header .valor { color: var(--danger); font-weight: 700; }
        .categoria-bar .bar-track { width: 100%; height: 8px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; overflow: hidden; }
        .categoria-bar .bar-fill { height: 100%; background: var(--danger); transition: width 0.3s; border-radius: 4px; }

        .empty-state { text-align: center; color: var(--text-secondary); padding: 40px; }

        /* Alerta de vencimento */
        .alerta-vencimento {
            position: fixed;
            top: 80px; right: 20px;
            max-width: 350px;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            cursor: pointer;
            animation: slideIn 0.3s ease;
            color: white;
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* ===== MOBILE RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container { padding: 16px !important; }
            .stats-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
            .content-grid { grid-template-columns: 1fr !important; }
            .form-row { grid-template-columns: 1fr !important; }
            .filtro-periodo { grid-template-columns: 1fr !important; }
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
    <?php include_once '../includes/menu.php'; ?>

    <div class="container">
        <!-- Header -->
        <div class="page-header" style="margin-bottom: 30px;">
            <h1 class="page-title" style="font-size: 28px; font-weight: 800;">
                <span style="background: linear-gradient(135deg, #FF6B35, #FF8C42); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    👤 Financeiro Pessoal
                </span>
            </h1>
            <p style="color: #A0AEC0; margin-top: 8px;">Controle suas finanças pessoais separado da empresa</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card receitas">
                <div class="stat-icon">💵</div>
                <div class="stat-label">Receitas Pessoais</div>
                <div class="stat-value" id="receitasPessoais">R$ 0,00</div>
                <div class="stat-change positive"><span>↗</span> Este mês</div>
            </div>
            <div class="stat-card despesas">
                <div class="stat-icon">💳</div>
                <div class="stat-label">Despesas Pessoais</div>
                <div class="stat-value" id="despesasPessoais">R$ 0,00</div>
                <div class="stat-change negative"><span>↘</span> Este mês</div>
            </div>
            <div class="stat-card saldo">
                <div class="stat-icon">📊</div>
                <div class="stat-label">Saldo Pessoal</div>
                <div class="stat-value" id="saldoPessoal">R$ 0,00</div>
                <div class="stat-change" style="color: var(--primary);"><span>💰</span> Disponível</div>
            </div>
            <div class="stat-card contas">
                <div class="stat-icon">🏦</div>
                <div class="stat-label">Contas/Cartões</div>
                <div class="stat-value" id="totalContasPessoais">0</div>
                <div class="stat-change" style="color: var(--accent);"><span>📋</span> Ativas</div>
            </div>
        </div>

        <!-- Receitas e Despesas -->
        <div class="content-grid">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💰 Receitas Pessoais</h2>
                    <button class="btn btn-primary" onclick="openModal('receitaPessoal')">+ Nova Receita</button>
                </div>
                <div id="listaReceitasPessoais"></div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💳 Despesas Pessoais</h2>
                    <button class="btn btn-danger" onclick="openModal('despesaPessoal')">+ Nova Despesa</button>
                </div>
                <div id="listaDespesasPessoais"></div>
            </div>
        </div>

        <!-- Contas e Cartões -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2 class="card-title">🏦 Minhas Contas e Cartões</h2>
                <button class="btn btn-primary" onclick="openModal('contaPessoal')">+ Nova Conta/Cartão</button>
            </div>
            <div id="listaContasPessoais"></div>
        </div>

        <!-- Contas a Pagar -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2 class="card-title">📅 Contas a Pagar</h2>
                <button class="btn btn-primary" onclick="openModal('contaPagar')">+ Nova Conta</button>
            </div>

            <!-- Resumo de Contas -->
            <div style="padding: 20px 24px 0;">
                <div class="resumo-contas">
                    <div class="resumo-item">
                        <div class="label">Vencidas</div>
                        <div class="numero" style="color: var(--danger);" id="contasVencidas">0</div>
                        <div class="valor" style="color: var(--danger);" id="valorVencidas">R$ 0,00</div>
                    </div>
                    <div class="resumo-item">
                        <div class="label">Vencem em 7 dias</div>
                        <div class="numero" style="color: var(--accent);" id="contasProximas">0</div>
                        <div class="valor" style="color: var(--accent);" id="valorProximas">R$ 0,00</div>
                    </div>
                    <div class="resumo-item">
                        <div class="label">Futuras</div>
                        <div class="numero" style="color: var(--success);" id="contasFuturas">0</div>
                        <div class="valor" style="color: var(--success);" id="valorFuturas">R$ 0,00</div>
                    </div>
                    <div class="resumo-item">
                        <div class="label">Total a Pagar</div>
                        <div class="numero" style="color: var(--primary);" id="totalAPagar">0</div>
                        <div class="valor" style="color: var(--primary);" id="valorTotalAPagar">R$ 0,00</div>
                    </div>
                </div>
            </div>

            <!-- Filtro de Período -->
            <div class="filtro-periodo">
                <div>
                    <label class="form-label">📅 Mês</label>
                    <select id="filtroMesContasPagar" class="form-select" onchange="renderContasPagar(); updateResumoContasPagar();">
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
                <div>
                    <label class="form-label">📆 Ano</label>
                    <select id="filtroAnoContasPagar" class="form-select" onchange="renderContasPagar(); updateResumoContasPagar();">
                    </select>
                </div>
                <button class="btn btn-secondary" style="height: 42px;" onclick="resetarFiltrosContasPagar()">🔄 Limpar</button>
            </div>

            <!-- Filtros de status -->
            <div class="filtros-btn">
                <button class="btn btn-secondary active" onclick="filtrarContasPagar('todas')" id="filtroContasTodas">📋 Todas</button>
                <button class="btn btn-secondary" onclick="filtrarContasPagar('vencidas')" id="filtroContasVencidas">⚠️ Vencidas</button>
                <button class="btn btn-secondary" onclick="filtrarContasPagar('hoje')" id="filtroContasHoje">📅 Hoje</button>
                <button class="btn btn-secondary" onclick="filtrarContasPagar('proximas')" id="filtroContasProximas">🔔 Próximas (7 dias)</button>
                <button class="btn btn-secondary" onclick="filtrarContasPagar('pagas')" id="filtroContasPagas">✅ Pagas</button>
            </div>

            <div id="listaContasPagar" style="padding-bottom: 16px;"></div>
        </div>

        <!-- Despesas por Categoria -->
        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2 class="card-title">📊 Despesas por Categoria</h2>
            </div>
            <div id="despesasPorCategoria" style="padding: 20px 24px;"></div>
        </div>
    </div>

    <!-- Modal Receita Pessoal -->
    <div id="modalReceitaPessoal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">💵 Nova Receita Pessoal</h2>
                <button class="close-btn" onclick="closeModal('receitaPessoal')">&times;</button>
            </div>
            <form onsubmit="addReceitaPessoal(event)">
                <div class="form-group">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-input" id="receitaPessoalDesc" required placeholder="Ex: Salário, Freelance, Venda">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Categoria</label>
                        <select class="form-select" id="receitaPessoalCategoria" required>
                            <option value="">Selecione...</option>
                            <option value="salario">💼 Salário</option>
                            <option value="freelance">💻 Freelance</option>
                            <option value="investimentos">📈 Investimentos</option>
                            <option value="aluguel">🏠 Aluguel Recebido</option>
                            <option value="venda">💰 Venda</option>
                            <option value="retirada_empresa">🏢 Retirada da Empresa</option>
                            <option value="outros">📌 Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor</label>
                        <input type="text" inputmode="decimal" class="form-input" id="receitaPessoalValor" required placeholder="R$ 0,00" oninput="mascaraValor(this)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-input" id="receitaPessoalData" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Conta/Cartão</label>
                        <select class="form-select" id="receitaPessoalConta">
                            <option value="">Não informado</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-submit">Adicionar Receita</button>
            </form>
        </div>
    </div>

    <!-- Modal Despesa Pessoal -->
    <div id="modalDespesaPessoal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">💳 Nova Despesa Pessoal</h2>
                <button class="close-btn" onclick="closeModal('despesaPessoal')">&times;</button>
            </div>
            <form onsubmit="addDespesaPessoal(event)">
                <div class="form-group">
                    <label class="form-label">Descrição</label>
                    <input type="text" class="form-input" id="despesaPessoalDesc" required placeholder="Ex: Mercado, Conta de Luz, Aluguel">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Categoria</label>
                        <select class="form-select" id="despesaPessoalCategoria" required>
                            <option value="">Selecione...</option>
                            <option value="moradia">🏠 Moradia</option>
                            <option value="alimentacao">🍔 Alimentação</option>
                            <option value="transporte">🚗 Transporte</option>
                            <option value="saude">⚕️ Saúde</option>
                            <option value="educacao">📚 Educação</option>
                            <option value="lazer">🎮 Lazer</option>
                            <option value="vestuario">👕 Vestuário</option>
                            <option value="contas">📄 Contas e Serviços</option>
                            <option value="cartao">💳 Cartão de Crédito</option>
                            <option value="prestacao">📊 Prestação/Parcelamento</option>
                            <option value="outros">📌 Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor</label>
                        <input type="text" inputmode="decimal" class="form-input" id="despesaPessoalValor" required placeholder="R$ 0,00" oninput="mascaraValor(this)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Data</label>
                        <input type="date" class="form-input" id="despesaPessoalData" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Conta/Cartão</label>
                        <select class="form-select" id="despesaPessoalConta">
                            <option value="">Não informado</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Observações</label>
                    <textarea class="form-textarea" id="despesaPessoalObs" placeholder="Detalhes adicionais..." rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-submit">Adicionar Despesa</button>
            </form>
        </div>
    </div>

    <!-- Modal Conta Pessoal -->
    <div id="modalContaPessoal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">🏦 Nova Conta/Cartão Pessoal</h2>
                <button class="close-btn" onclick="closeModal('contaPessoal')">&times;</button>
            </div>
            <form id="formContaPessoal" onsubmit="addContaPessoal(event)">
                <div class="form-group">
                    <label class="form-label">Nome da Conta/Cartão</label>
                    <input type="text" class="form-input" id="contaPessoalNome" required placeholder="Ex: Conta Corrente, Cartão Nubank">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="contaPessoalTipo" required>
                            <option value="">Selecione...</option>
                            <option value="corrente">🏦 Conta Corrente</option>
                            <option value="poupanca">💰 Poupança</option>
                            <option value="cartao_credito">💳 Cartão de Crédito</option>
                            <option value="cartao_debito">💳 Cartão de Débito</option>
                            <option value="investimento">📈 Investimento</option>
                            <option value="dinheiro">💵 Dinheiro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Saldo Inicial</label>
                        <input type="text" inputmode="decimal" class="form-input" id="contaPessoalSaldo" placeholder="R$ 0,00" oninput="mascaraValor(this)">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Banco/Instituição</label>
                    <input type="text" class="form-input" id="contaPessoalBanco" placeholder="Ex: Nubank, Inter, Banco do Brasil">
                </div>
                <button type="submit" class="btn btn-primary btn-submit">Adicionar Conta/Cartão</button>
            </form>
        </div>
    </div>

    <!-- Modal Conta a Pagar -->
    <div id="modalContaPagar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">📅 Nova Conta a Pagar</h2>
                <button class="close-btn" onclick="closeModal('contaPagar')">&times;</button>
            </div>
            <form id="contaPagarForm" onsubmit="addContaPagar(event)">
                <div class="form-group">
                    <label class="form-label">Descrição da Conta</label>
                    <input type="text" class="form-input" id="contaPagarDesc" required placeholder="Ex: Fatura Cartão Nubank, Prestação do Carro">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo</label>
                        <select class="form-select" id="contaPagarTipo" required>
                            <option value="">Selecione...</option>
                            <option value="cartao">💳 Fatura de Cartão</option>
                            <option value="prestacao">🚗 Prestação</option>
                            <option value="seguro">🛡️ Seguro</option>
                            <option value="aluguel">🏠 Aluguel</option>
                            <option value="condominio">🏢 Condomínio</option>
                            <option value="luz">💡 Conta de Luz</option>
                            <option value="agua">💧 Conta de Água</option>
                            <option value="internet">📡 Internet</option>
                            <option value="telefone">📱 Telefone</option>
                            <option value="streaming">📺 Streaming</option>
                            <option value="escola">🎓 Escola/Faculdade</option>
                            <option value="saude">⚕️ Plano de Saúde</option>
                            <option value="academia">🏋️ Academia</option>
                            <option value="emprestimo">💰 Empréstimo</option>
                            <option value="financiamento">🏦 Financiamento</option>
                            <option value="boleto">📄 Boleto</option>
                            <option value="outros">📌 Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Valor</label>
                        <input type="text" inputmode="decimal" class="form-input" id="contaPagarValor" required placeholder="R$ 0,00" oninput="mascaraValor(this)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Data de Vencimento</label>
                        <input type="date" class="form-input" id="contaPagarVencimento" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Recorrência</label>
                        <select class="form-select" id="contaPagarRecorrencia">
                            <option value="unica">Única (não repete)</option>
                            <option value="mensal">Mensal</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Observações (opcional)</label>
                    <textarea class="form-textarea" id="contaPagarObs" rows="2" placeholder="Ex: Código de barras, número da fatura..."></textarea>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: rgba(255, 255, 255, 0.02); border-radius: 8px;">
                    <input type="checkbox" id="contaPagarNotificar" checked style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="contaPagarNotificar" style="margin: 0; cursor: pointer; font-size: 13px;">
                        🔔 Ativar alertas de vencimento
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-submit">Adicionar Conta</button>
            </form>
        </div>
    </div>

    <script>
    // ============================================
    // UTILITÁRIOS
    // ============================================
    function formatMoney(valor) {
        return 'R$ ' + (parseFloat(valor) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Máscara de valor monetário (R$ 1.234,56)
    function mascaraValor(input) {
        let v = input.value.replace(/\D/g, '');
        if (v === '') { input.value = ''; return; }
        v = (parseInt(v) / 100).toFixed(2);
        v = v.replace('.', ',');
        v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        input.value = 'R$ ' + v;
    }

    // Converter valor mascarado (R$ 1.234,56) para float (1234.56)
    function valorMascaradoParaFloat(valorStr) {
        if (!valorStr) return 0;
        return parseFloat(valorStr.replace(/[R$\s.]/g, '').replace(',', '.')) || 0;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('pt-BR');
    }

    function openModal(nome) {
        const modal = document.getElementById('modal' + nome.charAt(0).toUpperCase() + nome.slice(1));
        if (modal) modal.classList.add('active');
    }

    function closeModal(nome) {
        const nomeCapitalizado = nome.charAt(0).toUpperCase() + nome.slice(1);
        const modal = document.getElementById('modal' + nomeCapitalizado);
        if (modal) modal.classList.remove('active');
    }

    // Fechar modal clicando fora
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });

    // ============================================
    // API CALLS
    // ============================================
    const API = '../api/pessoal.php';

    async function apiCall(action, data = {}) {
        try {
            const response = await fetch(`${API}?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error('Erro API:', error);
            return { success: false, error: error.message };
        }
    }

    async function apiGet(action) {
        try {
            const response = await fetch(`${API}?action=${action}`);
            return await response.json();
        } catch (error) {
            console.error('Erro API:', error);
            return { success: false, error: error.message };
        }
    }

    // ============================================
    // RECEITAS PESSOAIS
    // ============================================
    async function addReceitaPessoal(e) {
        e.preventDefault();

        const dados = {
            descricao: document.getElementById('receitaPessoalDesc').value,
            categoria: document.getElementById('receitaPessoalCategoria').value,
            valor: valorMascaradoParaFloat(document.getElementById('receitaPessoalValor').value),
            data: document.getElementById('receitaPessoalData').value,
            conta_id: document.getElementById('receitaPessoalConta').value || null
        };

        const result = await apiCall('criarReceita', dados);

        if (result.success) {
            alert('✅ Receita pessoal adicionada com sucesso!');
            closeModal('receitaPessoal');
            e.target.reset();
            carregarTudo();
        } else {
            alert('❌ Erro: ' + (result.error || 'Erro desconhecido'));
        }
    }

    async function excluirReceita(id) {
        if (!confirm('Excluir esta receita?')) return;
        const result = await apiCall('excluirReceita', { id });
        if (result.success) carregarTudo();
    }

    // ============================================
    // DESPESAS PESSOAIS
    // ============================================
    async function addDespesaPessoal(e) {
        e.preventDefault();

        const dados = {
            descricao: document.getElementById('despesaPessoalDesc').value,
            categoria: document.getElementById('despesaPessoalCategoria').value,
            valor: valorMascaradoParaFloat(document.getElementById('despesaPessoalValor').value),
            data: document.getElementById('despesaPessoalData').value,
            conta_id: document.getElementById('despesaPessoalConta').value || null,
            observacoes: document.getElementById('despesaPessoalObs').value
        };

        const result = await apiCall('criarDespesa', dados);

        if (result.success) {
            alert('✅ Despesa pessoal adicionada com sucesso!');
            closeModal('despesaPessoal');
            e.target.reset();
            carregarTudo();
        } else {
            alert('❌ Erro: ' + (result.error || 'Erro desconhecido'));
        }
    }

    async function excluirDespesa(id) {
        if (!confirm('Excluir esta despesa?')) return;
        const result = await apiCall('excluirDespesa', { id });
        if (result.success) carregarTudo();
    }

    // ============================================
    // CONTAS/CARTÕES PESSOAIS
    // ============================================
    async function addContaPessoal(e) {
        e.preventDefault();

        const form = e.target;
        const editingId = form.getAttribute('data-editing-id');

        const dados = {
            nome: document.getElementById('contaPessoalNome').value,
            tipo: document.getElementById('contaPessoalTipo').value,
            saldo_inicial: valorMascaradoParaFloat(document.getElementById('contaPessoalSaldo').value),
            banco: document.getElementById('contaPessoalBanco').value
        };

        let result;
        if (editingId) {
            dados.id = parseInt(editingId);
            result = await apiCall('editarConta', dados);
            form.removeAttribute('data-editing-id');
            document.querySelector('#formContaPessoal button[type="submit"]').textContent = 'Adicionar Conta/Cartão';
        } else {
            result = await apiCall('criarConta', dados);
        }

        if (result.success) {
            alert(editingId ? '✅ Conta atualizada!' : '✅ Conta/Cartão adicionado com sucesso!');
            closeModal('contaPessoal');
            form.reset();
            carregarTudo();
        } else {
            alert('❌ Erro: ' + (result.error || 'Erro desconhecido'));
        }
    }

    async function editarConta(id) {
        const result = await apiGet('listarContas');
        if (!result.success) return;

        const conta = result.data.find(c => c.id == id);
        if (!conta) return alert('❌ Conta não encontrada!');

        document.getElementById('contaPessoalNome').value = conta.nome;
        document.getElementById('contaPessoalTipo').value = conta.tipo;
        const inputSaldo = document.getElementById('contaPessoalSaldo');
        inputSaldo.value = conta.saldo_atual || 0;
        mascaraValor(inputSaldo);
        document.getElementById('contaPessoalBanco').value = conta.banco || '';

        const form = document.getElementById('formContaPessoal');
        form.setAttribute('data-editing-id', id);
        form.querySelector('button[type="submit"]').textContent = '💾 Salvar Alterações';

        openModal('contaPessoal');
    }

    async function excluirConta(id) {
        if (!confirm('Tem certeza que deseja excluir esta conta?')) return;
        const result = await apiCall('excluirConta', { id });
        if (result.success) {
            alert('✅ Conta excluída!');
            carregarTudo();
        }
    }

    // ============================================
    // CONTAS A PAGAR
    // ============================================
    let filtroContasPagarAtual = 'todas';

    async function addContaPagar(e) {
        e.preventDefault();

        const form = e.target;
        const editingId = form.getAttribute('data-editing-id');

        const dados = {
            descricao: document.getElementById('contaPagarDesc').value,
            tipo: document.getElementById('contaPagarTipo').value,
            valor: valorMascaradoParaFloat(document.getElementById('contaPagarValor').value),
            vencimento: document.getElementById('contaPagarVencimento').value,
            recorrencia: document.getElementById('contaPagarRecorrencia').value,
            observacoes: document.getElementById('contaPagarObs').value,
            notificar: document.getElementById('contaPagarNotificar').checked ? 1 : 0
        };

        let result;
        if (editingId) {
            dados.id = parseInt(editingId);
            result = await apiCall('editarContaPagar', dados);
            form.removeAttribute('data-editing-id');
            document.querySelector('#modalContaPagar .modal-title').textContent = '📅 Nova Conta a Pagar';
            document.querySelector('#contaPagarForm button[type="submit"]').textContent = 'Adicionar Conta';
        } else {
            result = await apiCall('criarContaPagar', dados);
        }

        if (result.success) {
            alert(editingId ? '✅ Conta atualizada!' : '✅ Conta cadastrada com sucesso!');
            closeModal('contaPagar');
            form.reset();
            document.getElementById('contaPagarNotificar').checked = true;
            carregarContasPagar();
        } else {
            alert('❌ Erro: ' + (result.error || 'Erro desconhecido'));
        }
    }

    async function marcarComoPaga(id) {
        if (!confirm('Marcar esta conta como paga?')) return;
        const result = await apiCall('marcarPaga', { id });
        if (result.success) {
            alert('✅ Conta marcada como paga!');
            carregarContasPagar();
        }
    }

    async function desmarcarPaga(id) {
        if (!confirm('Desmarcar pagamento desta conta?')) return;
        const result = await apiCall('desmarcarPaga', { id });
        if (result.success) carregarContasPagar();
    }

    async function editarContaPagar(id) {
        const result = await apiGet('listarContasPagar');
        if (!result.success) return;

        const conta = result.data.find(c => c.id == id);
        if (!conta) return;

        openModal('contaPagar');

        setTimeout(() => {
            document.getElementById('contaPagarDesc').value = conta.descricao || '';
            document.getElementById('contaPagarTipo').value = conta.tipo || '';
            const inputValor = document.getElementById('contaPagarValor');
            inputValor.value = conta.valor || '';
            mascaraValor(inputValor);
            document.getElementById('contaPagarVencimento').value = conta.vencimento || '';
            document.getElementById('contaPagarRecorrencia').value = conta.recorrencia || 'unica';
            document.getElementById('contaPagarObs').value = conta.observacoes || '';
            document.getElementById('contaPagarNotificar').checked = conta.notificar == 1;

            const form = document.getElementById('contaPagarForm');
            form.setAttribute('data-editing-id', id);
            document.querySelector('#modalContaPagar .modal-title').textContent = '✏️ Editar Conta a Pagar';
            document.querySelector('#contaPagarForm button[type="submit"]').textContent = '💾 Salvar Alterações';
        }, 100);
    }

    async function excluirContaPagar(id) {
        if (!confirm('Tem certeza que deseja excluir esta conta?')) return;
        const result = await apiCall('excluirContaPagar', { id });
        if (result.success) {
            alert('🗑️ Conta excluída!');
            carregarContasPagar();
        }
    }

    // ============================================
    // RENDERIZAÇÃO
    // ============================================
    function getCategoriaLabel(categoria, tipo) {
        const labelsReceita = {
            'salario': '💼 Salário', 'freelance': '💻 Freelance', 'investimentos': '📈 Investimentos',
            'aluguel': '🏠 Aluguel', 'venda': '💰 Venda', 'retirada_empresa': '🏢 Retirada da Empresa', 'outros': '📌 Outros'
        };
        const labelsDespesa = {
            'moradia': '🏠 Moradia', 'alimentacao': '🍔 Alimentação', 'transporte': '🚗 Transporte',
            'saude': '⚕️ Saúde', 'educacao': '📚 Educação', 'lazer': '🎮 Lazer', 'vestuario': '👕 Vestuário',
            'contas': '📄 Contas', 'cartao': '💳 Cartão', 'prestacao': '📊 Prestação', 'outros': '📌 Outros'
        };
        return tipo === 'receita' ? (labelsReceita[categoria] || categoria) : (labelsDespesa[categoria] || categoria);
    }

    function getTipoLabel(tipo) {
        const labels = {
            'cartao': '💳 Cartão', 'prestacao': '🚗 Prestação', 'seguro': '🛡️ Seguro',
            'aluguel': '🏠 Aluguel', 'condominio': '🏢 Condomínio', 'luz': '💡 Luz',
            'agua': '💧 Água', 'internet': '📡 Internet', 'telefone': '📱 Telefone',
            'streaming': '📺 Streaming', 'escola': '🎓 Escola', 'saude': '⚕️ Saúde',
            'academia': '🏋️ Academia', 'emprestimo': '💰 Empréstimo', 'financiamento': '🏦 Financiamento',
            'boleto': '📄 Boleto', 'outros': '📌 Outros'
        };
        return labels[tipo] || tipo;
    }

    function calcularDiasRestantes(vencimento) {
        const hoje = new Date(); hoje.setHours(0,0,0,0);
        const dataVenc = new Date(vencimento + 'T00:00:00'); dataVenc.setHours(0,0,0,0);
        return Math.ceil((dataVenc - hoje) / (1000 * 60 * 60 * 24));
    }

    function getStatusConta(conta) {
        const dias = calcularDiasRestantes(conta.vencimento);
        if (conta.paga) return { text: 'PAGA', icon: '✅', color: 'var(--success)', bgColor: 'rgba(6, 214, 160, 0.2)' };
        if (dias < 0) return { text: `VENCIDA HÁ ${Math.abs(dias)} DIAS`, icon: '⚠️', color: 'var(--danger)', bgColor: 'rgba(239, 71, 111, 0.2)' };
        if (dias === 0) return { text: 'VENCE HOJE', icon: '🔔', color: 'var(--danger)', bgColor: 'rgba(239, 71, 111, 0.2)' };
        if (dias <= 3) return { text: `VENCE EM ${dias} DIAS`, icon: '⏰', color: 'var(--danger)', bgColor: 'rgba(239, 71, 111, 0.2)' };
        if (dias <= 7) return { text: `VENCE EM ${dias} DIAS`, icon: '🔔', color: 'var(--accent)', bgColor: 'rgba(255, 210, 63, 0.2)' };
        return { text: `VENCE EM ${dias} DIAS`, icon: '📅', color: 'var(--success)', bgColor: 'rgba(6, 214, 160, 0.1)' };
    }

    function renderReceitas(receitas) {
        const container = document.getElementById('listaReceitasPessoais');
        if (!receitas || receitas.length === 0) {
            container.innerHTML = '<p class="empty-state">Nenhuma receita pessoal cadastrada.</p>';
            return;
        }
        container.innerHTML = receitas.slice(0, 10).map(r => `
            <div class="item-row">
                <div class="item-info">
                    <h4>${r.descricao}</h4>
                    <div class="item-meta">${formatDate(r.data)} • ${getCategoriaLabel(r.categoria, 'receita')}</div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div class="item-valor positivo">+ ${formatMoney(r.valor)}</div>
                    <div class="item-actions">
                        <button class="btn btn-danger" onclick="excluirReceita(${r.id})">🗑️</button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderDespesas(despesas) {
        const container = document.getElementById('listaDespesasPessoais');
        if (!despesas || despesas.length === 0) {
            container.innerHTML = '<p class="empty-state">Nenhuma despesa pessoal cadastrada.</p>';
            return;
        }
        container.innerHTML = despesas.slice(0, 10).map(d => `
            <div class="item-row">
                <div class="item-info">
                    <h4>${d.descricao}</h4>
                    <div class="item-meta">${formatDate(d.data)} • ${getCategoriaLabel(d.categoria, 'despesa')}</div>
                </div>
                <div style="display: flex; align-items: center;">
                    <div class="item-valor negativo">- ${formatMoney(d.valor)}</div>
                    <div class="item-actions">
                        <button class="btn btn-danger" onclick="excluirDespesa(${d.id})">🗑️</button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderContas(contas) {
        const container = document.getElementById('listaContasPessoais');
        if (!contas || contas.length === 0) {
            container.innerHTML = '<p class="empty-state">Nenhuma conta/cartão cadastrado.</p>';
            return;
        }
        const tipoIcons = { 'corrente': '🏦', 'poupanca': '💰', 'cartao_credito': '💳', 'cartao_debito': '💳', 'investimento': '📈', 'dinheiro': '💵' };
        container.innerHTML = contas.map(c => {
            const saldoColor = (c.saldo_atual || 0) >= 0 ? 'var(--success)' : 'var(--danger)';
            return `
                <div class="conta-card">
                    <div class="conta-info">
                        <h4>${tipoIcons[c.tipo] || '🏦'} ${c.nome}</h4>
                        <div class="banco">${c.banco || 'Sem banco informado'}</div>
                    </div>
                    <div class="conta-saldo">
                        <div class="valor" style="color: ${saldoColor};">${formatMoney(c.saldo_atual || 0)}</div>
                        <div class="label">Saldo Atual</div>
                        <div style="display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end;">
                            <button onclick="editarConta(${c.id})" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">✏️ Editar</button>
                            <button onclick="excluirConta(${c.id})" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">🗑️ Excluir</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    let contasPagarData = [];

    async function carregarContasPagar() {
        const result = await apiGet('listarContasPagar');
        if (result.success) {
            contasPagarData = result.data || [];
            renderContasPagar();
            updateResumoContasPagar();
        }
    }

    function renderContasPagar() {
        const container = document.getElementById('listaContasPagar');
        const mesSelecionado = document.getElementById('filtroMesContasPagar')?.value;
        const anoSelecionado = document.getElementById('filtroAnoContasPagar')?.value;
        const hoje = new Date(); hoje.setHours(0,0,0,0);

        let contas = contasPagarData.filter(c => {
            if (!c.vencimento) return true;
            const dataVenc = new Date(c.vencimento + 'T00:00:00');
            const mesVenc = dataVenc.getMonth() + 1;
            const anoVenc = dataVenc.getFullYear();
            const mesMatch = mesSelecionado === 'todos' || mesVenc === parseInt(mesSelecionado);
            const anoMatch = anoSelecionado === 'todos' || anoVenc === parseInt(anoSelecionado);
            if (mesMatch && anoMatch) return true;
            return !c.paga;
        });

        if (filtroContasPagarAtual === 'vencidas') {
            contas = contas.filter(c => !c.paga && new Date(c.vencimento + 'T00:00:00') < hoje);
        } else if (filtroContasPagarAtual === 'hoje') {
            contas = contas.filter(c => !c.paga && calcularDiasRestantes(c.vencimento) === 0);
        } else if (filtroContasPagarAtual === 'proximas') {
            contas = contas.filter(c => !c.paga && calcularDiasRestantes(c.vencimento) >= 0 && calcularDiasRestantes(c.vencimento) <= 7);
        } else if (filtroContasPagarAtual === 'pagas') {
            contas = contas.filter(c => c.paga);
        }

        contas.sort((a, b) => new Date(a.vencimento) - new Date(b.vencimento));

        if (contas.length === 0) {
            container.innerHTML = `<p class="empty-state">Nenhuma conta ${filtroContasPagarAtual === 'todas' ? '' : filtroContasPagarAtual} encontrada.</p>`;
            return;
        }

        const tipoIcons = { 'cartao': '💳', 'prestacao': '🚗', 'seguro': '🛡️', 'aluguel': '🏠', 'condominio': '🏢', 'luz': '💡', 'agua': '💧', 'internet': '📡', 'telefone': '📱', 'streaming': '📺', 'escola': '🎓', 'saude': '⚕️', 'academia': '🏋️', 'emprestimo': '💰', 'financiamento': '🏦', 'boleto': '📄', 'outros': '📌' };

        container.innerHTML = contas.map(c => {
            const status = getStatusConta(c);
            return `
                <div class="conta-pagar-card" style="border-left-color: ${status.color}; ${c.paga ? 'opacity: 0.6;' : ''}">
                    <div class="conta-pagar-header">
                        <div style="flex: 1;">
                            <h4 style="margin-bottom: 4px; ${c.paga ? 'text-decoration: line-through;' : ''}">
                                ${tipoIcons[c.tipo] || '📄'} ${c.descricao}
                            </h4>
                            <div style="font-size: 12px; color: var(--text-secondary);">
                                ${getTipoLabel(c.tipo)}
                                ${c.recorrencia !== 'unica' ? ` • ${c.recorrencia === 'mensal' ? '📆 Mensal' : '📅 Anual'}` : ''}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 20px; font-weight: 700; color: ${c.paga ? 'var(--success)' : 'var(--text-primary)'};">
                                ${formatMoney(c.valor)}
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding: 10px; background: rgba(255, 255, 255, 0.02); border-radius: 6px;">
                        <div>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 2px;">VENCIMENTO</div>
                            <div style="font-size: 14px; font-weight: 600;">📅 ${formatDate(c.vencimento)}</div>
                        </div>
                        <div style="text-align: right;">
                            ${!c.paga ? `
                                <div class="conta-pagar-status" style="background: ${status.bgColor}; color: ${status.color};">${status.icon} ${status.text}</div>
                            ` : `
                                <div class="conta-pagar-status" style="background: rgba(6, 214, 160, 0.2); color: var(--success);">✅ PAGA</div>
                                ${c.data_pagamento ? `<div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">${formatDate(c.data_pagamento)}</div>` : ''}
                            `}
                        </div>
                    </div>
                    ${c.observacoes ? `
                        <div style="padding: 8px; background: rgba(255, 255, 255, 0.02); border-radius: 6px; margin-bottom: 12px;">
                            <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;">OBSERVAÇÕES</div>
                            <div style="font-size: 13px;">${c.observacoes}</div>
                        </div>
                    ` : ''}
                    <div class="conta-pagar-actions">
                        ${!c.paga ? `
                            <button class="btn btn-success" onclick="marcarComoPaga(${c.id})">✅ Marcar como Paga</button>
                        ` : `
                            <button class="btn btn-secondary" onclick="desmarcarPaga(${c.id})">↩️ Desmarcar</button>
                        `}
                        <button class="btn btn-secondary" onclick="editarContaPagar(${c.id})">✏️ Editar</button>
                        <button class="btn btn-danger" onclick="excluirContaPagar(${c.id})">🗑️ Excluir</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updateResumoContasPagar() {
        const hoje = new Date(); hoje.setHours(0,0,0,0);
        const noPagas = contasPagarData.filter(c => !c.paga);

        let vencidas = 0, valorVencidas = 0, proximas = 0, valorProximas = 0, futuras = 0, valorFuturas = 0;

        noPagas.forEach(c => {
            const dias = calcularDiasRestantes(c.vencimento);
            const val = parseFloat(c.valor) || 0;
            if (dias < 0) { vencidas++; valorVencidas += val; }
            else if (dias <= 7) { proximas++; valorProximas += val; }
            else { futuras++; valorFuturas += val; }
        });

        document.getElementById('contasVencidas').textContent = vencidas;
        document.getElementById('valorVencidas').textContent = formatMoney(valorVencidas);
        document.getElementById('contasProximas').textContent = proximas;
        document.getElementById('valorProximas').textContent = formatMoney(valorProximas);
        document.getElementById('contasFuturas').textContent = futuras;
        document.getElementById('valorFuturas').textContent = formatMoney(valorFuturas);
        document.getElementById('totalAPagar').textContent = noPagas.length;
        document.getElementById('valorTotalAPagar').textContent = formatMoney(valorVencidas + valorProximas + valorFuturas);

        // Alertas de vencimento
        if (vencidas > 0 || proximas > 0) mostrarAlertaVencimento(vencidas, proximas);
    }

    function mostrarAlertaVencimento(vencidas, proximas) {
        const anterior = document.getElementById('alertaContasPagar');
        if (anterior) anterior.remove();

        const alerta = document.createElement('div');
        alerta.id = 'alertaContasPagar';
        alerta.className = 'alerta-vencimento';
        alerta.style.background = vencidas > 0 ? 'var(--danger)' : 'var(--accent)';
        alerta.innerHTML = `
            <div style="display: flex; align-items: start; gap: 12px;">
                <div style="font-size: 32px;">${vencidas > 0 ? '⚠️' : '🔔'}</div>
                <div style="flex: 1;">
                    <div style="font-weight: 700; margin-bottom: 4px; font-size: 15px;">${vencidas > 0 ? 'Contas Vencidas!' : 'Contas Próximas!'}</div>
                    <div style="font-size: 13px; opacity: 0.9;">
                        ${vencidas > 0 ? `${vencidas} conta(s) vencida(s)` : ''}
                        ${vencidas > 0 && proximas > 0 ? ' • ' : ''}
                        ${proximas > 0 ? `${proximas} vence(m) em 7 dias` : ''}
                    </div>
                </div>
                <button onclick="event.stopPropagation(); this.closest('.alerta-vencimento').remove();" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">&times;</button>
            </div>
        `;
        alerta.onclick = () => {
            document.getElementById('listaContasPagar').scrollIntoView({ behavior: 'smooth' });
            if (vencidas > 0) filtrarContasPagar('vencidas');
            alerta.remove();
        };
        document.body.appendChild(alerta);
        setTimeout(() => { if (alerta.parentNode) alerta.remove(); }, 10000);
    }

    function filtrarContasPagar(filtro) {
        filtroContasPagarAtual = filtro;
        ['todas', 'vencidas', 'hoje', 'proximas', 'pagas'].forEach(f => {
            const btn = document.getElementById(`filtroContas${f.charAt(0).toUpperCase() + f.slice(1)}`);
            if (btn) {
                btn.classList.toggle('active', f === filtro);
            }
        });
        renderContasPagar();
    }

    function resetarFiltrosContasPagar() {
        const mesAtual = new Date().getMonth() + 1;
        const anoAtual = new Date().getFullYear();
        document.getElementById('filtroMesContasPagar').value = mesAtual.toString();
        document.getElementById('filtroAnoContasPagar').value = anoAtual.toString();
        filtroContasPagarAtual = 'todas';
        filtrarContasPagar('todas');
    }

    function inicializarFiltrosContasPagar() {
        const selectAno = document.getElementById('filtroAnoContasPagar');
        if (!selectAno) return;

        const anoAtual = new Date().getFullYear();
        const anos = new Set([anoAtual - 1, anoAtual, anoAtual + 1]);

        selectAno.innerHTML = '<option value="todos">Todos os anos</option>';
        Array.from(anos).sort((a, b) => b - a).forEach(ano => {
            selectAno.innerHTML += `<option value="${ano}">${ano}</option>`;
        });
        selectAno.value = anoAtual.toString();

        const selectMes = document.getElementById('filtroMesContasPagar');
        if (selectMes) selectMes.value = (new Date().getMonth() + 1).toString();
    }

    // ============================================
    // DESPESAS POR CATEGORIA
    // ============================================
    function renderDespesasPorCategoria(despesas) {
        const container = document.getElementById('despesasPorCategoria');
        if (!despesas || despesas.length === 0) {
            container.innerHTML = '<p class="empty-state">Nenhuma despesa para análise.</p>';
            return;
        }

        const porCategoria = {};
        let totalDespesas = 0;
        despesas.forEach(d => {
            const val = parseFloat(d.valor) || 0;
            porCategoria[d.categoria] = (porCategoria[d.categoria] || 0) + val;
            totalDespesas += val;
        });

        const categorias = Object.entries(porCategoria).sort((a, b) => b[1] - a[1]).slice(0, 5);

        container.innerHTML = categorias.map(([cat, valor]) => {
            const percent = ((valor / totalDespesas) * 100).toFixed(1);
            return `
                <div class="categoria-bar">
                    <div class="bar-header">
                        <span class="nome">${getCategoriaLabel(cat, 'despesa')}</span>
                        <span class="valor">${formatMoney(valor)} (${percent}%)</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: ${percent}%;"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ============================================
    // STATS
    // ============================================
    function updateStats(receitas, despesas, contas) {
        const totalReceitas = (receitas || []).reduce((sum, r) => sum + (parseFloat(r.valor) || 0), 0);
        const totalDespesas = (despesas || []).reduce((sum, d) => sum + (parseFloat(d.valor) || 0), 0);
        const saldo = totalReceitas - totalDespesas;

        document.getElementById('receitasPessoais').textContent = formatMoney(totalReceitas);
        document.getElementById('despesasPessoais').textContent = formatMoney(totalDespesas);
        const saldoEl = document.getElementById('saldoPessoal');
        saldoEl.textContent = formatMoney(saldo);
        saldoEl.style.color = saldo >= 0 ? 'var(--success)' : 'var(--danger)';
        document.getElementById('totalContasPessoais').textContent = (contas || []).filter(c => c.ativa != 0).length;
    }

    function carregarContasNosSelects(contas) {
        const ativas = (contas || []).filter(c => c.ativa != 0);
        ['receitaPessoalConta', 'despesaPessoalConta'].forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                select.innerHTML = '<option value="">Não informado</option>';
                ativas.forEach(c => { select.innerHTML += `<option value="${c.id}">${c.nome}</option>`; });
            }
        });
    }

    // ============================================
    // CARREGAR TUDO
    // ============================================
    async function carregarTudo() {
        const [receitasRes, despesasRes, contasRes] = await Promise.all([
            apiGet('listarReceitas'),
            apiGet('listarDespesas'),
            apiGet('listarContas')
        ]);

        const receitas = receitasRes.success ? receitasRes.data : [];
        const despesas = despesasRes.success ? despesasRes.data : [];
        const contas = contasRes.success ? contasRes.data : [];

        renderReceitas(receitas);
        renderDespesas(despesas);
        renderContas(contas);
        renderDespesasPorCategoria(despesas);
        updateStats(receitas, despesas, contas);
        carregarContasNosSelects(contas);

        carregarContasPagar();
    }

    // ============================================
    // INICIALIZAÇÃO
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        inicializarFiltrosContasPagar();
        carregarTudo();
    });
    </script>
<?php include '../includes/footer.php'; ?>
</body>
</html>
