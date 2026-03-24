// ============================================
// RECEITAS (PARCELAS) - EventProDJ
// ============================================

let receitasData = [];
let clientesData = [];
let eventosData = [];
let contasData = [];

// ============================================
// INICIALIZAÇÃO
// ============================================
document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([
        carregarClientes(),
        carregarEventos(),
        carregarContas(),
        carregarReceitas()
    ]);
    
    // Definir data de hoje como padrão
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('pagoDataPagamento').value = hoje;
});

// ============================================
// CARREGAR DADOS
// ============================================
async function carregarClientes() {
    try {
        const res = await fetch('../api/clientes.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            clientesData = data.clientes;
            
            // Preencher selects
            const selects = [
                'filtroCliente',
                'receitaClienteId',
                'parcelasClienteId'
            ];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (!select) return;
                
                // Limpar e adicionar opção padrão (exceto no filtro)
                if (selectId === 'filtroCliente') {
                    select.innerHTML = '<option value="">Todos os Clientes</option>';
                } else {
                    select.innerHTML = '<option value="">Selecione...</option>';
                }
                
                // Adicionar clientes
                clientesData.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = c.nome;
                    select.appendChild(option);
                });
            });
        }
    } catch (error) {
        console.error('Erro ao carregar clientes:', error);
    }
}

async function carregarEventos() {
    try {
        const res = await fetch('../api/eventos.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            eventosData = data.eventos;
            
            const selects = ['receitaEventoId', 'parcelasEventoId'];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (!select) return;
                
                select.innerHTML = '<option value="">Nenhum</option>';
                
                eventosData.forEach(e => {
                    const option = document.createElement('option');
                    option.value = e.id;
                    option.textContent = `${e.tipo} - ${e.cliente_nome} (${formatarData(e.data_evento)})`;
                    select.appendChild(option);
                });
            });
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
    }
}

async function carregarContas() {
    try {
        const res = await fetch('../api/contas-bancarias.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            contasData = data.contas.filter(c => c.ativo);
            
            const select = document.getElementById('pagoContaId');
            if (select) {
                select.innerHTML = '<option value="">Selecione a conta...</option>';
                
                contasData.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = `${c.nome} (${formatarMoeda(c.saldo_atual)})`;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar contas:', error);
    }
}

async function carregarReceitas() {
    try {
        const filtroStatus = document.getElementById('filtroStatus').value;
        const filtroCliente = document.getElementById('filtroCliente').value;
        const filtroMes = document.getElementById('filtroMes').value;
        
        let url = '../api/receitas.php?action=list';
        const params = [];
        
        if (filtroStatus) params.push(`status=${filtroStatus}`);
        if (filtroCliente) params.push(`cliente_id=${filtroCliente}`);
        if (filtroMes) params.push(`mes=${filtroMes}`);
        
        if (params.length > 0) {
            url += '&' + params.join('&');
        }
        
        const res = await fetch(url);
        const data = await res.json();
        
        if (data.success) {
            receitasData = data.receitas;
            renderReceitas();
            atualizarResumo();
        }
    } catch (error) {
        console.error('Erro ao carregar receitas:', error);
        mostrarNotificacao('Erro ao carregar receitas', 'erro');
    }
}

// ============================================
// RENDERIZAR RECEITAS
// ============================================
function renderReceitas() {
    const container = document.getElementById('listaReceitas');
    
    if (receitasData.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                <div style="font-size: 64px; margin-bottom: 20px;">💰</div>
                <h3>Nenhuma receita cadastrada</h3>
                <p style="margin: 10px 0 20px;">Crie sua primeira receita ou gere parcelas automáticas</p>
                <button class="btn btn-primary" onclick="abrirModalNovaReceita()">+ Nova Receita</button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = receitasData.map(r => {
        const statusReal = r.status_real || r.status;
        const diasAtraso = parseInt(r.dias_atraso) || 0;
        
        let statusBadge = '';
        let statusClass = '';
        
        if (statusReal === 'pago') {
            statusBadge = '<span class="badge-status badge-pago">✅ PAGO</span>';
            statusClass = 'paga';
        } else if (statusReal === 'atrasado') {
            statusBadge = `<span class="badge-status badge-atrasado">⚠️ ATRASADO (${diasAtraso}d)</span>`;
            statusClass = 'atrasada';
        } else {
            statusBadge = '<span class="badge-status badge-pendente">⏳ PENDENTE</span>';
            statusClass = 'pendente';
        }
        
        const parcelaBadge = r.total_parcelas > 1 
            ? `<span class="parcela-badge">Parcela ${r.numero_parcela}/${r.total_parcelas}</span>`
            : '';
        
        return `
            <div class="receita-item ${statusClass}">
                <div class="receita-header">
                    <div class="receita-info">
                        <h4>${r.descricao}</h4>
                        <p>
                            👤 ${r.cliente_nome} 
                            ${r.evento_tipo ? `| 🎉 ${r.evento_tipo}` : ''}
                            ${r.conta_nome ? `| 🏦 ${r.conta_nome}` : ''}
                        </p>
                    </div>
                    <div class="receita-valor">
                        <div class="receita-valor-amount" style="color: ${statusReal === 'pago' ? 'var(--success)' : 'var(--accent)'};">
                            ${formatarMoeda(r.valor)}
                        </div>
                        ${parcelaBadge}
                    </div>
                </div>
                
                <div class="receita-footer">
                    <div style="display: flex; gap: 12px; align-items: center;">
                        ${statusBadge}
                        <span style="font-size: 13px; color: var(--text-secondary);">
                            📅 Venc: ${formatarData(r.data_vencimento)}
                            ${r.data_pagamento ? ` | ✅ Pago em: ${formatarData(r.data_pagamento)}` : ''}
                        </span>
                        ${r.forma_pagamento ? `<span style="font-size: 13px; color: var(--text-secondary);">| 💳 ${r.forma_pagamento}</span>` : ''}
                    </div>
                    
                    <div class="receita-acoes">
                        ${statusReal !== 'pago' ? `
                            <button class="btn-acao btn-pagar" onclick="abrirModalPago(${r.id})">
                                💰 Marcar como Pago
                            </button>
                        ` : `
                            <button class="btn-acao btn-editar" onclick="marcarComoPendente(${r.id})">
                                ↩️ Estornar
                            </button>
                        `}
                        <button class="btn-acao btn-editar" onclick="editarReceita(${r.id})">
                            ✏️ Editar
                        </button>
                        <button class="btn-acao btn-excluir" onclick="excluirReceita(${r.id})">
                            🗑️ Excluir
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// ============================================
// ATUALIZAR RESUMO
// ============================================
function atualizarResumo() {
    const totalPago = receitasData
        .filter(r => r.status === 'pago')
        .reduce((sum, r) => sum + parseFloat(r.valor), 0);
    
    const totalPendente = receitasData
        .filter(r => r.status === 'pendente')
        .reduce((sum, r) => sum + parseFloat(r.valor), 0);
    
    const totalAtrasado = receitasData
        .filter(r => r.status_real === 'atrasado')
        .reduce((sum, r) => sum + parseFloat(r.valor), 0);
    
    const totalGeral = receitasData.reduce((sum, r) => sum + parseFloat(r.valor), 0);
    
    document.getElementById('totalPago').textContent = formatarMoeda(totalPago);
    document.getElementById('totalPendente').textContent = formatarMoeda(totalPendente);
    document.getElementById('totalAtrasado').textContent = formatarMoeda(totalAtrasado);
    document.getElementById('totalGeral').textContent = formatarMoeda(totalGeral);
}

// ============================================
// MODAL NOVA RECEITA
// ============================================
function abrirModalNovaReceita() {
    document.getElementById('modalReceitaTitle').textContent = 'Nova Receita';
    document.getElementById('formReceita').reset();
    document.getElementById('receitaId').value = '';
    
    // Definir data de vencimento padrão (30 dias)
    const dataFutura = new Date();
    dataFutura.setDate(dataFutura.getDate() + 30);
    document.getElementById('receitaDataVencimento').value = dataFutura.toISOString().split('T')[0];
    
    document.getElementById('modalReceita').classList.add('active');
}

function fecharModalReceita() {
    document.getElementById('modalReceita').classList.remove('active');
}

async function salvarReceita(event) {
    event.preventDefault();
    
    const id = document.getElementById('receitaId').value;
    const dados = {
        id: id || undefined,
        cliente_id: document.getElementById('receitaClienteId').value,
        evento_id: document.getElementById('receitaEventoId').value || null,
        descricao: document.getElementById('receitaDescricao').value,
        valor: parseFloat(document.getElementById('receitaValor').value),
        data_vencimento: document.getElementById('receitaDataVencimento').value,
        forma_pagamento: document.getElementById('receitaFormaPagamento').value || null,
        observacoes: document.getElementById('receitaObservacoes').value || null
    };
    
    const action = id ? 'update' : 'create';
    
    try {
        const res = await fetch(`../api/receitas.php?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(dados)
        });
        
        const data = await res.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            fecharModalReceita();
            carregarReceitas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao salvar receita:', error);
        mostrarNotificacao('Erro ao salvar receita', 'erro');
    }
}

// ============================================
// MODAL PARCELAS
// ============================================
function abrirModalParcelas() {
    document.getElementById('formParcelas').reset();
    
    // Data padrão (primeiro dia do próximo mês)
    const hoje = new Date();
    const proximoMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 1);
    document.getElementById('parcelasDataPrimeira').value = proximoMes.toISOString().split('T')[0];
    
    document.getElementById('modalParcelas').classList.add('active');
}

function fecharModalParcelas() {
    document.getElementById('modalParcelas').classList.remove('active');
}

async function salvarParcelas(event) {
    event.preventDefault();
    
    const dados = {
        cliente_id: document.getElementById('parcelasClienteId').value,
        evento_id: document.getElementById('parcelasEventoId').value || null,
        descricao_base: document.getElementById('parcelasDescricao').value,
        valor_total: parseFloat(document.getElementById('parcelasValorTotal').value),
        quantidade_parcelas: parseInt(document.getElementById('parcelasQuantidade').value),
        data_primeira_parcela: document.getElementById('parcelasDataPrimeira').value,
        forma_pagamento: document.getElementById('parcelasFormaPagamento').value || null
    };
    
    try {
        const res = await fetch('../api/receitas.php?action=create_parcelas', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(dados)
        });
        
        const data = await res.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            fecharModalParcelas();
            carregarReceitas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao criar parcelas:', error);
        mostrarNotificacao('Erro ao criar parcelas', 'erro');
    }
}

// ============================================
// MODAL MARCAR COMO PAGO
// ============================================
function abrirModalPago(receitaId) {
    document.getElementById('pagoReceitaId').value = receitaId;
    
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('pagoDataPagamento').value = hoje;
    
    document.getElementById('modalPago').classList.add('active');
}

function fecharModalPago() {
    document.getElementById('modalPago').classList.remove('active');
}

async function confirmarPagamento(event) {
    event.preventDefault();
    
    const dados = {
        id: document.getElementById('pagoReceitaId').value,
        conta_id: document.getElementById('pagoContaId').value,
        data_pagamento: document.getElementById('pagoDataPagamento').value,
        forma_pagamento: document.getElementById('pagoFormaPagamento').value || null
    };
    
    try {
        const res = await fetch('../api/receitas.php?action=marcar_pago', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(dados)
        });
        
        const data = await res.json();
        
        if (data.success) {
            mostrarNotificacao(data.message + ' ✅ Saldo da conta atualizado!', 'sucesso');
            fecharModalPago();
            carregarReceitas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao marcar como pago:', error);
        mostrarNotificacao('Erro ao marcar como pago', 'erro');
    }
}

// ============================================
// MARCAR COMO PENDENTE (ESTORNAR)
// ============================================
async function marcarComoPendente(receitaId) {
    if (!confirm('Tem certeza que deseja estornar este pagamento?\n\nO valor será removido do saldo da conta.')) {
        return;
    }
    
    try {
        const res = await fetch('../api/receitas.php?action=marcar_pendente', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${receitaId}`
        });
        
        const data = await res.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            carregarReceitas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao estornar:', error);
        mostrarNotificacao('Erro ao estornar pagamento', 'erro');
    }
}

// ============================================
// EDITAR RECEITA
// ============================================
async function editarReceita(receitaId) {
    try {
        const res = await fetch(`../api/receitas.php?action=get&id=${receitaId}`);
        const data = await res.json();
        
        if (data.success) {
            const receita = data.receita;
            
            document.getElementById('modalReceitaTitle').textContent = 'Editar Receita';
            document.getElementById('receitaId').value = receita.id;
            document.getElementById('receitaClienteId').value = receita.cliente_id;
            document.getElementById('receitaEventoId').value = receita.evento_id || '';
            document.getElementById('receitaDescricao').value = receita.descricao;
            document.getElementById('receitaValor').value = receita.valor;
            document.getElementById('receitaDataVencimento').value = receita.data_vencimento;
            document.getElementById('receitaFormaPagamento').value = receita.forma_pagamento || '';
            document.getElementById('receitaObservacoes').value = receita.observacoes || '';
            
            document.getElementById('modalReceita').classList.add('active');
        }
    } catch (error) {
        console.error('Erro ao carregar receita:', error);
        mostrarNotificacao('Erro ao carregar receita', 'erro');
    }
}

// ============================================
// EXCLUIR RECEITA
// ============================================
async function excluirReceita(receitaId) {
    if (!confirm('Tem certeza que deseja excluir esta receita?')) {
        return;
    }
    
    try {
        const res = await fetch(`../api/receitas.php?action=delete&id=${receitaId}`, {
            method: 'POST'
        });
        
        const data = await res.json();
        
        if (data.success) {
            mostrarNotificacao(data.message, 'sucesso');
            carregarReceitas();
        } else {
            mostrarNotificacao(data.message, 'erro');
        }
    } catch (error) {
        console.error('Erro ao excluir receita:', error);
        mostrarNotificacao('Erro ao excluir receita', 'erro');
    }
}

// ============================================
// FILTROS
// ============================================
function limparFiltros() {
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroCliente').value = '';
    document.getElementById('filtroMes').value = '';
    carregarReceitas();
}

// ============================================
// UTILITÁRIOS
// ============================================
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    }).format(valor);
}

function formatarData(data) {
    if (!data) return '-';
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

function mostrarNotificacao(mensagem, tipo) {
    // Implementação simples - você pode melhorar com toast/snackbar
    alert(mensagem);
}
