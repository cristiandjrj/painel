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
    <title>Receitas - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
</head>
<body>

<!-- Menu Simples -->
<nav style="background: #141B3D; border-bottom: 1px solid #2D3561; padding: 20px 30px; position: sticky; top: 0; z-index: 1000;">
    <div style="max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
        <a href="dashboard.php" style="font-size: 24px; font-weight: 800; color: #FF6B35; text-decoration: none;">EVENTPRODJ</a>
        <div style="display: flex; gap: 20px;">
            <a href="dashboard.php" style="color: #A0AEC0; text-decoration: none; font-weight: 600;">Dashboard</a>
            <a href="clientes-eventpro.php" style="color: #A0AEC0; text-decoration: none; font-weight: 600;">Clientes</a>
            <a href="eventos-eventpro.php" style="color: #A0AEC0; text-decoration: none; font-weight: 600;">Eventos</a>
            <a href="servicos-eventpro.php" style="color: #A0AEC0; text-decoration: none; font-weight: 600;">Serviços</a>
            <a href="custos-eventpro.php" style="color: #A0AEC0; text-decoration: none; font-weight: 600;">Custos</a>
            <a href="contas-bancarias.php" style="color: #A0AEC0; text-decoration: none; font-weight: 600;">Contas</a>
            <a href="receitas.php" style="color: #FF6B35; text-decoration: none; font-weight: 600;">Receitas</a>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Cabeçalho -->
    <div class="page-header">
        <h1 class="page-title">💰 Receitas (Parcelas)</h1>
        <p class="page-subtitle">Gerencie pagamentos de clientes e parcelas</p>
    </div>

    <!-- Cards de Resumo -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(6, 214, 160, 0.2), rgba(6, 214, 160, 0.05));">
            <div class="stat-icon" style="background: rgba(6, 214, 160, 0.2); color: #06D6A0;">💰</div>
            <div class="stat-label">Total Pago</div>
            <div class="stat-value" id="totalPago" style="color: #06D6A0;">R$ 0,00</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 210, 63, 0.2), rgba(255, 210, 63, 0.05));">
            <div class="stat-icon" style="background: rgba(255, 210, 63, 0.2); color: #FFD23F;">⏳</div>
            <div class="stat-label">A Receber</div>
            <div class="stat-value" id="totalPendente" style="color: #FFD23F;">R$ 0,00</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(239, 71, 111, 0.2), rgba(239, 71, 111, 0.05));">
            <div class="stat-icon" style="background: rgba(239, 71, 111, 0.2); color: #EF476F;">⚠️</div>
            <div class="stat-label">Atrasado</div>
            <div class="stat-value" id="totalAtrasado" style="color: #EF476F;">R$ 0,00</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(79, 70, 229, 0.05));">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.2); color: #4F46E5;">📊</div>
            <div class="stat-label">Total Geral</div>
            <div class="stat-value" id="totalGeral" style="color: #4F46E5;">R$ 0,00</div>
        </div>
    </div>

    <!-- Filtros e Ações -->
    <div class="search-section" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <select class="form-select" id="filtroStatus" onchange="carregarReceitas()" style="flex: 1; min-width: 150px;">
            <option value="">Todos os Status</option>
            <option value="pago">✅ Pago</option>
            <option value="pendente">⏳ Pendente</option>
            <option value="atrasado">⚠️ Atrasado</option>
        </select>
        
        <select class="form-select" id="filtroCliente" onchange="carregarReceitas()" style="flex: 1; min-width: 200px;">
            <option value="">Todos os Clientes</option>
        </select>
        
        <select class="form-select" id="filtroMes" onchange="carregarReceitas()" style="flex: 1; min-width: 150px;">
            <option value="">Todos os Meses</option>
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
        
        <button class="btn btn-secondary" onclick="limparFiltros()">🔄 Limpar</button>
        <button class="btn btn-secondary" onclick="abrirModalParcelas()">📊 Criar Parcelas</button>
        <button class="btn btn-primary" onclick="abrirModalNovaReceita()">+ Nova Receita</button>
    </div>

    <!-- Lista de Receitas -->
    <div id="listaReceitas" style="margin-top: 24px;"></div>
</div>

<!-- Modal Nova Receita -->
<div class="modal" id="modalReceita">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalReceitaTitle">Nova Receita</h3>
            <button class="close-btn" onclick="fecharModalReceita()">×</button>
        </div>
        <form id="formReceita" onsubmit="salvarReceita(event)">
            <input type="hidden" id="receitaId">
            
            <div class="form-group">
                <label class="form-label">Cliente *</label>
                <select class="form-input" id="receitaClienteId" required>
                    <option value="">Selecione...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Evento (opcional)</label>
                <select class="form-input" id="receitaEventoId">
                    <option value="">Nenhum</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Descrição *</label>
                <input type="text" class="form-input" id="receitaDescricao" required placeholder="Ex: Sinal - Casamento">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Valor *</label>
                    <input type="number" class="form-input" id="receitaValor" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Data Vencimento *</label>
                    <input type="date" class="form-input" id="receitaDataVencimento" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Forma de Pagamento</label>
                <select class="form-input" id="receitaFormaPagamento">
                    <option value="">Selecione...</option>
                    <option value="Dinheiro">💵 Dinheiro</option>
                    <option value="PIX">📱 PIX</option>
                    <option value="Cartão Débito">💳 Cartão Débito</option>
                    <option value="Cartão Crédito">💳 Cartão Crédito</option>
                    <option value="Transferência">🏦 Transferência</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea class="form-input" id="receitaObservacoes" rows="3"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModalReceita()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Criar Parcelas -->
<div class="modal" id="modalParcelas">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Criar Parcelas Automáticas</h3>
            <button class="close-btn" onclick="fecharModalParcelas()">×</button>
        </div>
        <form id="formParcelas" onsubmit="salvarParcelas(event)">
            <div class="form-group">
                <label class="form-label">Cliente *</label>
                <select class="form-input" id="parcelasClienteId" required>
                    <option value="">Selecione...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Evento (opcional)</label>
                <select class="form-input" id="parcelasEventoId">
                    <option value="">Nenhum</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Descrição Base *</label>
                <input type="text" class="form-input" id="parcelasDescricao" required placeholder="Ex: Casamento João e Maria">
                <small style="color: var(--text-secondary); font-size: 12px;">
                    Será adicionado "- Parcela X/Y" automaticamente
                </small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Valor Total *</label>
                    <input type="number" class="form-input" id="parcelasValorTotal" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Quantidade Parcelas *</label>
                    <input type="number" class="form-input" id="parcelasQuantidade" min="2" max="12" required placeholder="6">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Data da 1ª Parcela *</label>
                <input type="date" class="form-input" id="parcelasDataPrimeira" required>
                <small style="color: var(--text-secondary); font-size: 12px;">
                    As demais parcelas serão geradas mensalmente
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Forma de Pagamento</label>
                <select class="form-input" id="parcelasFormaPagamento">
                    <option value="">Selecione...</option>
                    <option value="Dinheiro">💵 Dinheiro</option>
                    <option value="PIX">📱 PIX</option>
                    <option value="Cartão Débito">💳 Cartão Débito</option>
                    <option value="Cartão Crédito">💳 Cartão Crédito</option>
                    <option value="Transferência">🏦 Transferência</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModalParcelas()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Parcelas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Marcar como Pago -->
<div class="modal" id="modalPago">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Marcar como Pago</h3>
            <button class="close-btn" onclick="fecharModalPago()">×</button>
        </div>
        <form id="formPago" onsubmit="confirmarPagamento(event)">
            <input type="hidden" id="pagoReceitaId">
            
            <div class="form-group">
                <label class="form-label">Conta Bancária *</label>
                <select class="form-input" id="pagoContaId" required>
                    <option value="">Selecione a conta...</option>
                </select>
                <small style="color: var(--text-secondary); font-size: 12px;">
                    O saldo será atualizado automaticamente
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Data do Pagamento *</label>
                <input type="date" class="form-input" id="pagoDataPagamento" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Forma de Pagamento</label>
                <select class="form-input" id="pagoFormaPagamento">
                    <option value="">Selecione...</option>
                    <option value="Dinheiro">💵 Dinheiro</option>
                    <option value="PIX">📱 PIX</option>
                    <option value="Cartão Débito">💳 Cartão Débito</option>
                    <option value="Cartão Crédito">💳 Cartão Crédito</option>
                    <option value="Transferência">🏦 Transferência</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModalPago()">Cancelar</button>
                <button type="submit" class="btn btn-success">Confirmar Pagamento</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/receitas.js"></script>

<style>
.receita-item {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    transition: all 0.3s ease;
}

.receita-item:hover {
    transform: translateY(-2px);
    border-color: var(--primary);
}

.receita-item.atrasada {
    border-left: 4px solid var(--danger);
}

.receita-item.pendente {
    border-left: 4px solid var(--accent);
}

.receita-item.paga {
    border-left: 4px solid var(--success);
    opacity: 0.8;
}

.receita-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 12px;
}

.receita-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
}

.receita-info p {
    margin: 0;
    font-size: 13px;
    color: var(--text-secondary);
}

.receita-valor {
    text-align: right;
}

.receita-valor-amount {
    font-size: 24px;
    font-weight: 800;
}

.receita-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}

.receita-acoes {
    display: flex;
    gap: 8px;
}

.btn-acao {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-pagar {
    background: rgba(6, 214, 160, 0.1);
    color: var(--success);
}

.btn-pagar:hover {
    background: rgba(6, 214, 160, 0.2);
}

.btn-editar {
    background: rgba(255, 210, 63, 0.1);
    color: var(--accent);
}

.btn-excluir {
    background: rgba(239, 71, 111, 0.1);
    color: var(--danger);
}

.badge-status {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.badge-pago {
    background: rgba(6, 214, 160, 0.2);
    color: var(--success);
}

.badge-pendente {
    background: rgba(255, 210, 63, 0.2);
    color: var(--accent);
}

.badge-atrasado {
    background: rgba(239, 71, 111, 0.2);
    color: var(--danger);
}

.parcela-badge {
    padding: 2px 8px;
    background: rgba(79, 70, 229, 0.1);
    color: #4F46E5;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.form-select {
    padding: 10px 12px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    cursor: pointer;
}

.form-select:focus {
    outline: none;
    border-color: var(--primary);
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

<?php include '../includes/footer.php'; ?>
</body>
</html>
