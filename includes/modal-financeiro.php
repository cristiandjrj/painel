<!-- Modal Financeiro do Cliente -->
<div class="modal" id="modalFinanceiro">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">💰 Dados Financeiros</h2>
            <button class="close-btn" onclick="fecharModalFinanceiro()">×</button>
        </div>
        
        <div style="margin-bottom: 20px; padding: 12px; background: rgba(255, 107, 53, 0.1); border-radius: 8px;">
            <strong id="financeiroClienteNome" style="color: var(--primary); font-size: 16px;">Cliente</strong>
        </div>
        
        <!-- Mensagem informativa para novo cliente -->
        <div id="financeiroNovoClienteMsg" style="display: none; margin-bottom: 20px; padding: 16px; background: rgba(6, 214, 160, 0.1); border: 1px solid var(--success); border-radius: 8px;">
            <div style="font-size: 14px; color: var(--success); font-weight: 600; margin-bottom: 8px;">
                ✅ Cliente cadastrado com sucesso!
            </div>
            <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                Agora você pode registrar os valores e condições de pagamento.
            </div>
        </div>
        
        <form onsubmit="salvarFinanceiro(event)">
            <input type="hidden" id="financeiroClienteId">
            
            <div class="form-group">
                <label class="form-label">Valor Total do Serviço</label>
                <input type="number" step="0.01" class="form-input" id="financeiroValorTotal" placeholder="0,00" oninput="calcularSaldoFinanceiro()">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Valor do Sinal</label>
                    <input type="number" step="0.01" class="form-input" id="financeiroSinalValor" placeholder="0,00" oninput="calcularSaldoFinanceiro()">
                </div>
                <div class="form-group">
                    <label class="form-label">Data do Sinal</label>
                    <input type="date" class="form-input" id="financeiroSinalData">
                </div>
            </div>

            <div class="form-group" id="formaPagamentoSinalGroup" style="display: none;">
                <label class="form-label">Forma de Pagamento do Sinal 💳</label>
                <select class="form-select" id="financeiroSinalFormaPagamento">
                    <option value="">Selecione...</option>
                    <option value="pix">💳 PIX</option>
                    <option value="dinheiro">💵 Dinheiro</option>
                    <option value="debito">💳 Débito</option>
                    <option value="credito">💳 Cartão de Crédito</option>
                    <option value="transferencia">🏦 Transferência</option>
                    <option value="boleto">📄 Boleto</option>
                </select>
            </div>
            
            <div class="form-group" id="contaSinalGroup" style="display: none;">
                <label class="form-label">Conta Bancária 🏦 <span style="font-size: 12px; color: var(--text-secondary);">(opcional para dinheiro)</span></label>
                <select class="form-select" id="financeiroSinalConta">
                    <option value="">Selecione a conta...</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Valor a Pagar</label>
                    <input type="number" step="0.01" class="form-input" id="financeiroValorPagar" placeholder="0,00" readonly style="background: rgba(255, 107, 53, 0.1); font-weight: 600; font-size: 18px; color: var(--primary);">
                </div>
                <div class="form-group">
                    <label class="form-label">Data de Vencimento</label>
                    <input type="date" class="form-input" id="financeiroVencimento">
                </div>
            </div>

            <!-- Seção de Pagamento do Restante -->
            <div id="secaoPagamentoRestante" style="display: none; padding: 16px; background: rgba(6, 214, 160, 0.1); border: 1px solid var(--success); border-radius: 8px; margin-bottom: 16px;">
                <div style="font-size: 14px; color: var(--success); font-weight: 600; margin-bottom: 12px;">
                    💰 Registrar Pagamento
                </div>
                
                <div class="form-group">
                    <label class="form-label">Valor do Pagamento</label>
                    <input type="number" step="0.01" class="form-input" id="financeiroValorPagamentoRestante" placeholder="Digite o valor ou deixe vazio para pagar tudo" oninput="atualizarBotaoPagamentoRestante()">
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">
                        💡 Deixe vazio para pagar o valor total restante (<strong id="valorRestanteDisplay">R$ 0,00</strong>)
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Data do Pagamento</label>
                        <input type="date" class="form-input" id="financeiroDataPagamentoRestante">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Forma de Pagamento 💳</label>
                        <select class="form-select" id="financeiroFormaPagamentoRestante">
                            <option value="">Selecione...</option>
                            <option value="pix">💳 PIX</option>
                            <option value="dinheiro">💵 Dinheiro</option>
                            <option value="debito">💳 Débito</option>
                            <option value="credito">💳 Cartão de Crédito</option>
                            <option value="transferencia">🏦 Transferência</option>
                            <option value="boleto">📄 Boleto</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Conta Bancária 🏦</label>
                    <select class="form-select" id="financeiroContaPagamentoRestante">
                        <option value="">Selecione a conta...</option>
                    </select>
                </div>
                
                <button type="button" onclick="registrarPagamentoRestante()" class="btn btn-primary" id="btnPagamentoRestante" style="width: 100%;">
                    ✅ Confirmar Pagamento
                </button>
            </div>
            
            <!-- Resumo Financeiro -->
            <div id="resumoFinanceiroModal" style="background: rgba(6, 214, 160, 0.1); border: 1px solid var(--success); border-radius: 8px; padding: 16px; margin-top: 16px; display: none;">
                <div style="font-size: 14px; color: var(--success); font-weight: 600; margin-bottom: 12px;">📊 Resumo Financeiro:</div>
                <div style="display: grid; gap: 8px; font-size: 14px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Valor dos Serviços:</span>
                        <strong id="resumoServicosModal">R$ 0,00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid rgba(6, 214, 160, 0.2); align-items: center;">
                        <span style="font-weight: 600;">Valor Total:</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong id="resumoTotalModal" style="font-size: 16px;">R$ 0,00</strong>
                            <button type="button" onclick="editarValorTotal()" 
                                    style="padding: 4px 8px; font-size: 12px; background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; border-radius: 4px; cursor: pointer;"
                                    title="Editar valor total">
                                ✏️
                            </button>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Sinal Recebido:</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong id="resumoSinalModal" style="color: var(--success);">R$ 0,00</strong>
                            <button type="button" onclick="editarSinal()" 
                                    style="padding: 4px 8px; font-size: 12px; background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; border-radius: 4px; cursor: pointer;"
                                    title="Editar sinal">
                                ✏️
                            </button>
                            <button type="button" onclick="deletarSinal()" 
                                    style="padding: 4px 8px; font-size: 12px; background: rgba(239, 71, 111, 0.2); color: var(--danger); border: 1px solid var(--danger); border-radius: 4px; cursor: pointer;"
                                    title="Deletar sinal">
                                🗑️
                            </button>
                        </div>
                    </div>
                    
                    <!-- Histórico de Pagamentos Adicionais -->
                    <div id="historicoPagamentosAdicionais" style="display: none; margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(6, 214, 160, 0.2);">
                        <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; font-weight: 600;">
                            💰 Pagamentos Adicionais:
                        </div>
                        <div id="listaPagamentosAdicionais" style="display: grid; gap: 4px; font-size: 12px;">
                            <!-- Será preenchido dinamicamente -->
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 1px solid rgba(6, 214, 160, 0.3);">
                        <span style="font-weight: 600;">Total Pago:</span>
                        <strong id="resumoTotalPagoModal" style="color: var(--success); font-size: 16px;">R$ 0,00</strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between;">
                        <span>Saldo Devedor:</span>
                        <strong id="resumoSaldoModal" style="color: var(--primary); font-size: 16px;">R$ 0,00</strong>
                    </div>
                </div>
                <div class="progress-bar" style="margin-top: 12px; height: 8px; background: rgba(255, 255, 255, 0.1); border-radius: 10px; overflow: hidden;">
                    <div id="progressBarModal" class="progress-fill" style="height: 100%; background: linear-gradient(90deg, var(--success), var(--accent)); transition: width 0.5s ease;"></div>
                </div>
                <div style="text-align: center; margin-top: 8px; font-size: 12px; color: var(--text-secondary);">
                    <span id="progressPercentModal">0%</span> pago
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-submit" style="width: 100%; margin-top: 20px;">
                💰 Salvar Dados Financeiros
            </button>
        </form>
    </div>
</div>

<script>
// ===================================
// MODAL FINANCEIRO - FUNÇÕES
// ===================================

function fecharModalFinanceiro() {
    document.getElementById('modalFinanceiro').classList.remove('active');
}

function abrirModalFinanceiro(clienteId, novoCliente = false) {
    console.log('🔍 abrirModalFinanceiro chamado:', {clienteId, novoCliente});
    
    // Carregar contas bancárias primeiro
    fetch('../api/contas-bancarias.php?action=list')
        .then(response => response.json())
        .then(contasData => {
            console.log('🏦 Contas bancárias recebidas:', contasData);
            
            // Preencher selects de conta bancária
            const selectSinal = document.getElementById('financeiroSinalConta');
            const selectRestante = document.getElementById('financeiroContaPagamentoRestante');
            
            if (selectSinal) {
                selectSinal.innerHTML = '<option value="">Selecione a conta...</option>';
                if (contasData.success && contasData.contas) {
                    contasData.contas.forEach(conta => {
                        if (conta.ativo) {
                            const option = document.createElement('option');
                            option.value = conta.id;
                            option.textContent = `${conta.nome} - ${conta.banco || ''}`;
                            selectSinal.appendChild(option);
                        }
                    });
                    console.log('✅ Contas carregadas no select:', contasData.contas.length);
                }
            }
            
            if (selectRestante) {
                selectRestante.innerHTML = '<option value="">Selecione a conta...</option>';
                if (contasData.success && contasData.contas) {
                    contasData.contas.forEach(conta => {
                        if (conta.ativo) {
                            const option = document.createElement('option');
                            option.value = conta.id;
                            option.textContent = `${conta.nome} - ${conta.banco || ''}`;
                            selectRestante.appendChild(option);
                        }
                    });
                }
            }
            
            // Continuar com o carregamento do cliente
            return fetch(`../api/clientes.php?action=get&id=${clienteId}`);
        })
        .then(response => {
            console.log('📡 Resposta cliente recebida:', response.status);
            return response.json();
        })
        .then(clienteData => {
            console.log('📦 Dados cliente recebidos:', clienteData);
            
            // Buscar evento do cliente
            return fetch(`../api/eventos.php?action=list`)
                .then(res => res.json())
                .then(eventosData => {
                    console.log('📦 Eventos recebidos:', eventosData);
                    
                    // Encontrar evento do cliente
                    let evento = null;
                    if (eventosData.success && eventosData.eventos) {
                        evento = eventosData.eventos.find(e => e.cliente_id == clienteId);
                        console.log('🎯 Evento encontrado:', evento);
                    }
                    
                    return {cliente: clienteData, evento: evento};
                });
        })
        .then(data => {
            const clienteData = data.cliente;
            const evento = data.evento;
            
            if (clienteData.success && clienteData.cliente) {
                const cliente = clienteData.cliente;
                console.log('✅ Cliente encontrado:', cliente.nome);
                
                document.getElementById('financeiroClienteId').value = clienteId;
                document.getElementById('financeiroClienteNome').textContent = cliente.nome;
                
                // Se é novo cliente, mostrar mensagem
                if (novoCliente) {
                    document.getElementById('financeiroNovoClienteMsg').style.display = 'block';
                } else {
                    document.getElementById('financeiroNovoClienteMsg').style.display = 'none';
                }
                
                // Preencher valor total do evento (se existir)
                if (evento && evento.valor_total) {
                    document.getElementById('financeiroValorTotal').value = evento.valor_total;
                    console.log('💰 Valor total preenchido:', evento.valor_total);
                } else if (cliente.valor_total) {
                    document.getElementById('financeiroValorTotal').value = cliente.valor_total;
                }
                
                // Preencher data de vencimento como data do evento (se existir)
                if (evento && evento.data_evento) {
                    document.getElementById('financeiroVencimento').value = evento.data_evento;
                    console.log('📅 Data vencimento preenchida:', evento.data_evento);
                } else if (cliente.data_vencimento) {
                    document.getElementById('financeiroVencimento').value = cliente.data_vencimento;
                }
                
                // Preencher sinal se existir
                if (cliente.valor_sinal) {
                    document.getElementById('financeiroSinalValor').value = cliente.valor_sinal;
                }
                if (cliente.data_sinal) {
                    document.getElementById('financeiroSinalData').value = cliente.data_sinal;
                }
                
                // Preencher forma de pagamento do sinal se existir
                if (cliente.forma_pagamento_sinal) {
                    const selectFormaPag = document.getElementById('financeiroSinalFormaPagamento');
                    if (selectFormaPag) {
                        selectFormaPag.value = cliente.forma_pagamento_sinal;
                    }
                }

                // Preencher conta bancária do sinal se existir
                if (cliente.conta_sinal) {
                    const selectConta = document.getElementById('financeiroSinalConta');
                    if (selectConta) {
                        selectConta.value = cliente.conta_sinal;
                    }
                }

                // Mostrar/ocultar seções conforme o caso
                if (cliente.valor_sinal > 0) {
                    // Cliente já tem sinal - mostrar forma de pagamento e conta
                    document.getElementById('formaPagamentoSinalGroup').style.display = 'block';
                    // Mostrar conta bancária se não for dinheiro
                    const formaPagSinal = cliente.forma_pagamento_sinal || '';
                    if (formaPagSinal && formaPagSinal !== 'dinheiro') {
                        document.getElementById('contaSinalGroup').style.display = 'block';
                    } else if (formaPagSinal === 'dinheiro') {
                        document.getElementById('contaSinalGroup').style.display = 'none';
                    } else {
                        document.getElementById('contaSinalGroup').style.display = 'block';
                    }

                    // Calcular saldo para mostrar pagamento restante
                    const valorTotal = parseFloat(evento ? evento.valor_total : cliente.valor_total) || 0;
                    const saldoDevedor = valorTotal - parseFloat(cliente.valor_sinal || 0);

                    if (saldoDevedor > 0) {
                        document.getElementById('secaoPagamentoRestante').style.display = 'block';
                    } else {
                        document.getElementById('secaoPagamentoRestante').style.display = 'none';
                    }
                    document.getElementById('resumoFinanceiroModal').style.display = 'block';
                } else {
                    // Cliente sem sinal, mostrar formulário normal
                    document.getElementById('secaoPagamentoRestante').style.display = 'none';
                    document.getElementById('resumoFinanceiroModal').style.display = 'none';
                }

                calcularSaldoFinanceiro();
                document.getElementById('modalFinanceiro').classList.add('active');
                console.log('✅ Modal aberto!');
            } else {
                console.error('❌ Erro na resposta:', clienteData);
                // Abrir modal mesmo assim com dados básicos
                document.getElementById('financeiroClienteId').value = clienteId;
                document.getElementById('financeiroClienteNome').textContent = 'Cliente #' + clienteId;
                document.getElementById('financeiroNovoClienteMsg').style.display = novoCliente ? 'block' : 'none';
                document.getElementById('secaoPagamentoRestante').style.display = 'none';
                document.getElementById('resumoFinanceiroModal').style.display = 'none';
                
                // Tentar preencher do evento mesmo sem cliente
                if (evento && evento.valor_total) {
                    document.getElementById('financeiroValorTotal').value = evento.valor_total;
                }
                if (evento && evento.data_evento) {
                    document.getElementById('financeiroVencimento').value = evento.data_evento;
                }
                
                calcularSaldoFinanceiro();
                document.getElementById('modalFinanceiro').classList.add('active');
                console.log('⚠️ Modal aberto com dados básicos');
            }
        })
        .catch(error => {
            console.error('❌ Erro ao carregar:', error);
            // Abrir modal mesmo com erro
            document.getElementById('financeiroClienteId').value = clienteId;
            document.getElementById('financeiroClienteNome').textContent = 'Cliente #' + clienteId;
            document.getElementById('modalFinanceiro').classList.add('active');
            console.log('⚠️ Modal aberto mesmo com erro');
        });
}

// Calcular saldo devedor
function calcularSaldoFinanceiro() {
    const valorTotal = parseFloat(document.getElementById('financeiroValorTotal').value) || 0;
    const valorSinal = parseFloat(document.getElementById('financeiroSinalValor').value) || 0;
    const valorPagar = valorTotal - valorSinal;
    
    document.getElementById('financeiroValorPagar').value = valorPagar.toFixed(2);
    
    // Mostrar/ocultar campos conforme necessário
    if (valorSinal > 0) {
        const formaPagamento = document.getElementById('financeiroSinalFormaPagamento').value;
        document.getElementById('formaPagamentoSinalGroup').style.display = 'block';
        
        // Só mostrar conta bancária se NÃO for dinheiro
        if (formaPagamento && formaPagamento !== 'dinheiro') {
            document.getElementById('contaSinalGroup').style.display = 'block';
        } else {
            document.getElementById('contaSinalGroup').style.display = 'none';
        }
    } else {
        document.getElementById('formaPagamentoSinalGroup').style.display = 'none';
        document.getElementById('contaSinalGroup').style.display = 'none';
    }
    
    // Atualizar resumo
    document.getElementById('resumoTotalModal').textContent = 'R$ ' + valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('resumoSinalModal').textContent = 'R$ ' + valorSinal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('resumoSaldoModal').textContent = 'R$ ' + valorPagar.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('resumoTotalPagoModal').textContent = 'R$ ' + valorSinal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    document.getElementById('valorRestanteDisplay').textContent = 'R$ ' + valorPagar.toLocaleString('pt-BR', {minimumFractionDigits: 2});
    
    // Atualizar barra de progresso
    const percentPago = valorTotal > 0 ? (valorSinal / valorTotal) * 100 : 0;
    document.getElementById('progressBarModal').style.width = percentPago + '%';
    document.getElementById('progressPercentModal').textContent = percentPago.toFixed(0) + '%';
}

// Listener para forma de pagamento do sinal
document.addEventListener('DOMContentLoaded', function() {
    const formaPagamentoSinal = document.getElementById('financeiroSinalFormaPagamento');
    if (formaPagamentoSinal) {
        formaPagamentoSinal.addEventListener('change', function() {
            const isDinheiro = this.value === 'dinheiro';
            const contaGroup = document.getElementById('contaSinalGroup');
            if (contaGroup) {
                contaGroup.style.display = isDinheiro ? 'none' : 'block';
            }
        });
    }
    
    // Listener para forma de pagamento do restante
    const formaPagamentoRestante = document.getElementById('financeiroFormaPagamentoRestante');
    if (formaPagamentoRestante) {
        formaPagamentoRestante.addEventListener('change', function() {
            const isDinheiro = this.value === 'dinheiro';
            const contaGroup = document.getElementById('financeiroContaPagamentoRestante');
            if (contaGroup) {
                // Tornar obrigatório apenas se não for dinheiro
                contaGroup.required = !isDinheiro;
                if (isDinheiro) {
                    contaGroup.value = ''; // Limpar seleção
                }
            }
        });
    }
});

// Salvar dados financeiros
async function salvarFinanceiro(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_financeiro');
        formData.append('id', document.getElementById('financeiroClienteId').value);
        formData.append('valor_total', document.getElementById('financeiroValorTotal').value);
        formData.append('valor_sinal', document.getElementById('financeiroSinalValor').value);
        formData.append('data_sinal', document.getElementById('financeiroSinalData').value);
        formData.append('data_vencimento', document.getElementById('financeiroVencimento').value);
        formData.append('forma_pagamento_sinal', document.getElementById('financeiroSinalFormaPagamento').value);
        formData.append('conta_sinal', document.getElementById('financeiroSinalConta').value);
        
        const response = await fetch('../api/clientes.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Dados financeiros salvos com sucesso!');
            fecharModalFinanceiro();
            if (typeof carregarDados === 'function') {
                carregarDados();
            }
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar dados financeiros');
    }
}

// Funções adicionais (implementação simplificada)
function atualizarBotaoPagamentoRestante() {
    const valor = document.getElementById('financeiroValorPagamentoRestante').value;
    const btnText = valor ? `Pagar R$ ${parseFloat(valor).toFixed(2)}` : 'Pagar Total Restante';
    document.getElementById('btnPagamentoRestante').textContent = '✅ ' + btnText;
}

function registrarPagamentoRestante() {
    alert('Função de pagamento restante será implementada em breve');
}

function editarValorTotal() {
    document.getElementById('financeiroValorTotal').focus();
}

function editarSinal() {
    document.getElementById('financeiroSinalValor').focus();
}

function deletarSinal() {
    if (confirm('Tem certeza que deseja deletar o sinal recebido?')) {
        document.getElementById('financeiroSinalValor').value = '';
        calcularSaldoFinanceiro();
    }
}

// Expor função globalmente para ser chamada de outros arquivos
window.abrirModalFinanceiro = abrirModalFinanceiro;

// Debug: confirmar que função foi carregada
console.log('✅ Modal financeiro carregado. Função abrirModalFinanceiro disponível:', typeof abrirModalFinanceiro);
</script>
