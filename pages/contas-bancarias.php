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
    <title>Contas Bancárias - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <!-- Cabeçalho -->
    <div class="page-header">
        <h1 class="page-title">🏦 Contas Bancárias</h1>
        <p class="page-subtitle">Gerencie suas contas e acompanhe saldos</p>
    </div>

    <!-- Cards de Resumo -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Saldo Total</div>
            <div class="stat-value" id="saldoTotal">R$ 0,00</div>
            <div class="stat-info">em contas ativas</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏦</div>
            <div class="stat-label">Contas Ativas</div>
            <div class="stat-value" id="contasAtivas">0</div>
            <div class="stat-info">contas disponíveis</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-label">Total de Contas</div>
            <div class="stat-value" id="totalContas">0</div>
            <div class="stat-info">cadastradas</div>
        </div>
    </div>

    <!-- Botão Nova Conta -->
    <div class="search-section">
        <button class="btn btn-primary" onclick="abrirModalNovaConta()">
            + Nova Conta Bancária
        </button>
    </div>

    <!-- Lista de Contas -->
    <div id="listaContas"></div>
</div>

<!-- Modal Nova/Editar Conta -->
<div class="modal" id="modalConta">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalContaTitle">Nova Conta Bancária</h3>
            <button class="close-btn" onclick="fecharModalConta()">×</button>
        </div>
        <form id="formConta" onsubmit="salvarConta(event)">
            <input type="hidden" id="contaId">
            
            <div class="form-group">
                <label class="form-label">Nome da Conta *</label>
                <input type="text" class="form-input" id="contaNome" required placeholder="Ex: Conta Corrente Itaú">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Banco</label>
                    <input type="text" class="form-input" id="contaBanco" placeholder="Ex: Banco Itaú">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select class="form-input" id="contaTipo">
                        <option value="corrente">Conta Corrente</option>
                        <option value="poupanca">Poupança</option>
                        <option value="investimento">Investimento</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Agência</label>
                    <input type="text" class="form-input" id="contaAgencia" placeholder="0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Conta</label>
                    <input type="text" class="form-input" id="contaConta" placeholder="00000-0">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Saldo Inicial *</label>
                <input type="number" class="form-input" id="contaSaldoInicial" step="0.01" required placeholder="0.00">
                <small style="color: var(--text-secondary); font-size: 12px;">
                    ⚠️ O saldo atual será ajustado automaticamente com base nas movimentações
                </small>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModalConta()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Extrato -->
<div class="modal" id="modalExtrato">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modalExtratoTitle">Extrato da Conta</h3>
            <button class="close-btn" onclick="fecharModalExtrato()">×</button>
        </div>
        <div class="modal-body">
            <div class="form-row" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label">Data Início</label>
                    <input type="date" class="form-input" id="extratoDataInicio">
                </div>
                <div class="form-group">
                    <label class="form-label">Data Fim</label>
                    <input type="date" class="form-input" id="extratoDataFim">
                </div>
                <div class="form-group" style="align-self: end;">
                    <button class="btn btn-primary" onclick="carregarExtrato()">Buscar</button>
                </div>
            </div>
            
            <div id="extratoConteudo"></div>
        </div>
    </div>
</div>

<!-- Modal Transferência -->
<div class="modal" id="modalTransferencia">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Transferir entre Contas</h3>
            <button class="close-btn" onclick="fecharModalTransferencia()">×</button>
        </div>
        <form id="formTransferencia" onsubmit="realizarTransferencia(event)">
            <div class="form-group">
                <label class="form-label">Conta Origem *</label>
                <select class="form-input" id="transferenciaOrigem" required>
                    <option value="">Selecione...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Conta Destino *</label>
                <select class="form-input" id="transferenciaDestino" required>
                    <option value="">Selecione...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Valor *</label>
                <input type="number" class="form-input" id="transferenciaValor" step="0.01" required placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea class="form-input" id="transferenciaDescricao" rows="3" placeholder="Motivo da transferência...">Transferência entre contas</textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="fecharModalTransferencia()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Transferir</button>
            </div>
        </form>
    </div>
</div>

<script>
let contaExtratoAtual = null;

// ============================================
// CARREGAR CONTAS
// ============================================
async function carregarContas() {
    try {
        const response = await fetch('../api/contas-bancarias.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            exibirContas(data.contas);
            atualizarResumo(data.resumo);
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao carregar contas:', error);
        mostrarNotificacao('Erro ao carregar contas', 'erro');
    }
}

// ============================================
// EXIBIR CONTAS
// ============================================
function exibirContas(contas) {
    const container = document.getElementById('listaContas');
    
    if (contas.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div style="font-size: 64px; margin-bottom: 20px;">🏦</div>
                <h3>Nenhuma conta cadastrada</h3>
                <p>Crie sua primeira conta bancária para começar</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = contas.map(conta => `
        <div class="conta-card ${!conta.ativo ? 'inativa' : ''}">
            <div class="conta-header">
                <div>
                    <div class="conta-nome">
                        ${conta.nome}
                        ${!conta.ativo ? '<span class="badge badge-inactive">Inativa</span>' : ''}
                    </div>
                    <div class="conta-info">
                        ${conta.banco || 'Sem banco'} • ${getTipoLabel(conta.tipo)}
                        ${conta.agencia ? ` • Ag: ${conta.agencia}` : ''}
                        ${conta.conta ? ` • Cc: ${conta.conta}` : ''}
                    </div>
                </div>
                <div class="conta-saldo">
                    <div class="conta-saldo-label">Saldo Atual</div>
                    <div class="conta-saldo-valor ${conta.saldo_atual >= 0 ? 'positivo' : 'negativo'}">
                        ${formatarMoeda(conta.saldo_atual)}
                    </div>
                </div>
            </div>
            
            <div class="conta-footer">
                <div class="conta-detalhe">
                    <span>Saldo Inicial:</span>
                    <strong>${formatarMoeda(conta.saldo_inicial)}</strong>
                </div>
                <div class="conta-acoes">
                    <button class="btn-action btn-extrato" onclick="abrirExtrato(${conta.id}, '${conta.nome}')">
                        📊 Extrato
                    </button>
                    <button class="btn-action btn-editar" onclick="editarConta(${conta.id})">
                        ✏️ Editar
                    </button>
                    <button class="btn-action ${conta.ativo ? 'btn-desativar' : 'btn-ativar'}" 
                            onclick="toggleAtivo(${conta.id})">
                        ${conta.ativo ? '🔒 Desativar' : '🔓 Ativar'}
                    </button>
                    <button class="btn-action btn-excluir" onclick="excluirConta(${conta.id})">
                        🗑️ Excluir
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// ============================================
// ATUALIZAR RESUMO
// ============================================
function atualizarResumo(resumo) {
    document.getElementById('saldoTotal').textContent = formatarMoeda(resumo.saldo_total);
    document.getElementById('contasAtivas').textContent = resumo.contas_ativas;
    document.getElementById('totalContas').textContent = resumo.total_contas;
}

// ============================================
// MODAL NOVA CONTA
// ============================================
function abrirModalNovaConta() {
    document.getElementById('modalContaTitle').textContent = 'Nova Conta Bancária';
    document.getElementById('formConta').reset();
    document.getElementById('contaId').value = '';
    document.getElementById('modalConta').classList.add('active');
}

function fecharModalConta() {
    document.getElementById('modalConta').classList.remove('active');
}

// ============================================
// SALVAR CONTA
// ============================================
async function salvarConta(event) {
    event.preventDefault();
    
    const id = document.getElementById('contaId').value;
    const dados = {
        id: id || undefined,
        nome: document.getElementById('contaNome').value,
        banco: document.getElementById('contaBanco').value,
        agencia: document.getElementById('contaAgencia').value,
        conta: document.getElementById('contaConta').value,
        tipo: document.getElementById('contaTipo').value,
        saldo_inicial: parseFloat(document.getElementById('contaSaldoInicial').value)
    };
    
    const action = id ? 'update' : 'create';
    
    try {
        const response = await fetch(`../api/contas-bancarias.php?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(dados)
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            fecharModalConta();
            carregarContas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao salvar conta:', error);
        mostrarNotificacao('Erro ao salvar conta', 'erro');
    }
}

// ============================================
// EDITAR CONTA
// ============================================
async function editarConta(id) {
    try {
        const response = await fetch(`../api/contas-bancarias.php?action=get&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const conta = data.conta;
            document.getElementById('modalContaTitle').textContent = 'Editar Conta';
            document.getElementById('contaId').value = conta.id;
            document.getElementById('contaNome').value = conta.nome;
            document.getElementById('contaBanco').value = conta.banco || '';
            document.getElementById('contaAgencia').value = conta.agencia || '';
            document.getElementById('contaConta').value = conta.conta || '';
            document.getElementById('contaTipo').value = conta.tipo;
            document.getElementById('contaSaldoInicial').value = conta.saldo_inicial;
            
            document.getElementById('modalConta').classList.add('active');
        }
    } catch (error) {
        console.error('Erro ao carregar conta:', error);
        mostrarNotificacao('Erro ao carregar conta', 'erro');
    }
}

// ============================================
// EXCLUIR CONTA
// ============================================
async function excluirConta(id) {
    if (!confirm('Tem certeza que deseja excluir esta conta?\n\nAtenção: Só é possível excluir contas sem movimentações.')) {
        return;
    }
    
    try {
        const response = await fetch(`../api/contas-bancarias.php?action=delete&id=${id}`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            carregarContas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao excluir conta:', error);
        mostrarNotificacao('Erro ao excluir conta', 'erro');
    }
}

// ============================================
// TOGGLE ATIVO
// ============================================
async function toggleAtivo(id) {
    try {
        const response = await fetch('../api/contas-bancarias.php?action=toggle_ativo', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            carregarContas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
        mostrarNotificacao('Erro ao atualizar status', 'erro');
    }
}

// ============================================
// EXTRATO
// ============================================
function abrirExtrato(contaId, contaNome) {
    contaExtratoAtual = contaId;
    document.getElementById('modalExtratoTitle').textContent = `Extrato - ${contaNome}`;
    
    // Definir datas padrão (mês atual)
    const hoje = new Date();
    const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    document.getElementById('extratoDataInicio').value = primeiroDia.toISOString().split('T')[0];
    document.getElementById('extratoDataFim').value = hoje.toISOString().split('T')[0];
    
    document.getElementById('modalExtrato').classList.add('active');
    carregarExtrato();
}

function fecharModalExtrato() {
    document.getElementById('modalExtrato').classList.remove('active');
}

async function carregarExtrato() {
    if (!contaExtratoAtual) return;
    
    const dataInicio = document.getElementById('extratoDataInicio').value;
    const dataFim = document.getElementById('extratoDataFim').value;
    
    try {
        const response = await fetch(
            `../api/contas-bancarias.php?action=extrato&conta_id=${contaExtratoAtual}&data_inicio=${dataInicio}&data_fim=${dataFim}`
        );
        const data = await response.json();
        
        if (data.success) {
            exibirExtrato(data.movimentacoes, data.resumo);
        } else {
            document.getElementById('extratoConteudo').innerHTML = `
                <div class="empty-state">
                    <p>Erro ao carregar extrato</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro ao carregar extrato:', error);
    }
}

function exibirExtrato(movimentacoes, resumo) {
    const container = document.getElementById('extratoConteudo');
    
    if (movimentacoes.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <p>Nenhuma movimentação no período</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = `
        <div class="extrato-resumo">
            <div class="extrato-item receita">
                <span>Receitas:</span>
                <strong>${formatarMoeda(resumo.total_receitas)}</strong>
            </div>
            <div class="extrato-item despesa">
                <span>Despesas:</span>
                <strong>${formatarMoeda(resumo.total_despesas)}</strong>
            </div>
            <div class="extrato-item saldo">
                <span>Saldo Período:</span>
                <strong>${formatarMoeda(resumo.saldo_periodo)}</strong>
            </div>
        </div>
        
        <div class="extrato-lista">
            ${movimentacoes.map(mov => `
                <div class="extrato-mov ${mov.tipo}">
                    <div class="extrato-mov-info">
                        <div class="extrato-mov-descricao">
                            ${mov.tipo === 'receita' ? '💰' : '💸'} ${mov.descricao}
                            ${mov.cliente_nome ? `<span class="badge">${mov.cliente_nome}</span>` : ''}
                        </div>
                        <div class="extrato-mov-data">${formatarData(mov.data)}</div>
                    </div>
                    <div class="extrato-mov-valor ${mov.tipo}">
                        ${mov.tipo === 'receita' ? '+' : '-'} ${formatarMoeda(mov.valor)}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

// ============================================
// UTILITÁRIOS
// ============================================
function getTipoLabel(tipo) {
    const tipos = {
        'corrente': 'Corrente',
        'poupanca': 'Poupança',
        'investimento': 'Investimento',
        'outros': 'Outros'
    };
    return tipos[tipo] || tipo;
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    }).format(valor);
}

function formatarData(data) {
    if (!data) return '';
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

function mostrarNotificacao(mensagem, tipo) {
    // Implementar sistema de notificação toast
    alert(mensagem);
}

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', carregarContas);
</script>

<style>
.conta-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.conta-card:hover {
    transform: translateY(-2px);
    border-color: var(--primary);
}

.conta-card.inativa {
    opacity: 0.6;
    border-color: var(--text-secondary);
}

.conta-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
}

.conta-nome {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.conta-info {
    font-size: 13px;
    color: var(--text-secondary);
}

.conta-saldo {
    text-align: right;
}

.conta-saldo-label {
    font-size: 12px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.conta-saldo-valor {
    font-size: 28px;
    font-weight: 800;
}

.conta-saldo-valor.positivo {
    color: var(--success);
}

.conta-saldo-valor.negativo {
    color: var(--danger);
}

.conta-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.conta-detalhe {
    font-size: 13px;
    color: var(--text-secondary);
}

.conta-detalhe strong {
    color: var(--text-primary);
    margin-left: 8px;
}

.conta-acoes {
    display: flex;
    gap: 8px;
}

.btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-extrato {
    background: rgba(24, 119, 242, 0.1);
    color: #1877F2;
}

.btn-editar {
    background: rgba(255, 210, 63, 0.1);
    color: var(--accent);
}

.btn-desativar, .btn-ativar {
    background: rgba(160, 174, 192, 0.1);
    color: var(--text-secondary);
}

.btn-excluir {
    background: rgba(239, 71, 111, 0.1);
    color: var(--danger);
}

.btn-action:hover {
    transform: scale(1.05);
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-inactive {
    background: rgba(160, 174, 192, 0.2);
    color: var(--text-secondary);
}

.extrato-resumo {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
}

.extrato-item {
    text-align: center;
}

.extrato-item.receita strong {
    color: var(--success);
}

.extrato-item.despesa strong {
    color: var(--danger);
}

.extrato-item.saldo strong {
    color: var(--accent);
}

.extrato-lista {
    max-height: 400px;
    overflow-y: auto;
}

.extrato-mov {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    margin-bottom: 8px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 8px;
    border-left: 3px solid;
}

.extrato-mov.receita {
    border-left-color: var(--success);
}

.extrato-mov.despesa {
    border-left-color: var(--danger);
}

.extrato-mov-descricao {
    font-weight: 600;
    margin-bottom: 4px;
}

.extrato-mov-data {
    font-size: 12px;
    color: var(--text-secondary);
}

.extrato-mov-valor {
    font-size: 18px;
    font-weight: 700;
}

.extrato-mov-valor.receita {
    color: var(--success);
}

.extrato-mov-valor.despesa {
    color: var(--danger);
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
