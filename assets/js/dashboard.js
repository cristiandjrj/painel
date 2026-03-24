// ============================================
// DASHBOARD - EventProDJ
// ============================================

// CARREGAR DADOS DO DASHBOARD
async function carregarDashboard() {
    try {
        await Promise.all([
            carregarEstatisticas(),
            carregarProximosEventos(),
            carregarOrigemClientes(),
            carregarTiposEventos(),
            carregarTransacoes()
        ]);
    } catch (error) {
        console.error('Erro ao carregar dashboard:', error);
    }
}

// ESTATÍSTICAS
async function carregarEstatisticas() {
    const mesAtual = new Date().getMonth() + 1;
    const anoAtual = new Date().getFullYear();
    
    try {
        // Eventos
        const resEventos = await fetch('../api/eventos.php?action=list');
        const dataEventos = await resEventos.json();
        
        if (dataEventos.success) {
            const eventos = dataEventos.eventos;
            const hoje = new Date();
            
            // Eventos em andamento (futuros ou do mês atual)
            const emAndamento = eventos.filter(e => {
                const dataEvento = new Date(e.data_evento);
                return e.status !== 'cancelado' && dataEvento >= hoje;
            }).length;
            
            // Eventos concluídos no ano
            const concluidos = eventos.filter(e => {
                const dataEvento = new Date(e.data_evento);
                return dataEvento.getFullYear() === anoAtual && dataEvento < hoje;
            }).length;
            
            // Eventos do mês
            const eventosMes = eventos.filter(e => {
                const dataEvento = new Date(e.data_evento);
                return dataEvento.getMonth() + 1 === mesAtual && 
                       dataEvento.getFullYear() === anoAtual;
            }).length;
            
            document.getElementById('totalEventosAndamento').textContent = emAndamento;
            document.getElementById('totalEventosConcluidos').textContent = concluidos;
            document.getElementById('eventosDoMes').textContent = eventosMes;
            document.getElementById('eventosConcluidosAno').textContent = concluidos;
        }
        
        // Receitas do mês: sinais de eventos (usar data_sinal, fallback data_evento)
        let receitasMes = dataEventos.eventos
            .filter(e => {
                const dataSinal = e.data_sinal || e.data_evento;
                const data = new Date(dataSinal);
                return data.getMonth() + 1 === mesAtual &&
                       data.getFullYear() === anoAtual &&
                       e.status !== 'cancelado' &&
                       parseFloat(e.valor_sinal || 0) > 0;
            })
            .reduce((sum, e) => sum + parseFloat(e.valor_sinal || 0), 0);

        // Somar também pagamentos adicionais de movimentacoes_financeiras
        try {
            const resMov = await fetch('../api/financeiro.php?action=transacoes&mes=' + mesAtual + '&ano=' + anoAtual + '&tipo=receita');
            const dataMov = await resMov.json();
            if (dataMov.success && dataMov.transacoes) {
                const pagamentosExtras = dataMov.transacoes
                    .filter(t => t.tipo_movimentacao === 'pagamento_evento' && !t.descricao?.startsWith('Sinal - '))
                    .reduce((sum, t) => sum + parseFloat(t.valor || 0), 0);
                receitasMes += pagamentosExtras;
            }
        } catch (movError) {
            console.warn('Movimentações não disponíveis para receitas');
        }

        document.getElementById('totalReceitas').textContent = formatarMoeda(receitasMes);
        
        // Custos (buscar do banco diretamente - CORRIGIDO)
        try {
            const response = await fetch('../api/custos.php?action=listar');
            const text = await response.text();
            
            let totalDespesas = 0;
            
            if (text && text.trim() !== '') {
                const dataCustos = JSON.parse(text);
                
                if (dataCustos.success && dataCustos.custos) {
                    const custosGerais = dataCustos.custos.filter(c => {
                        const data = new Date(c.data_custo);
                        return data.getMonth() + 1 === mesAtual && 
                               data.getFullYear() === anoAtual;
                    });
                    
                    totalDespesas = custosGerais.reduce((sum, c) => sum + parseFloat(c.valor || 0), 0);
                    document.getElementById('numeroCustosEventos').textContent = custosGerais.length;
                }
            }
            
            document.getElementById('totalDespesas').textContent = formatarMoeda(totalDespesas);
            document.getElementById('totalCustosEventos').textContent = formatarMoeda(0);
            
            // Lucro
            const lucro = receitasMes - totalDespesas;
            document.getElementById('lucroLiquido').textContent = formatarMoeda(lucro);
            
            const margem = receitasMes > 0 ? ((lucro / receitasMes) * 100).toFixed(1) : 0;
            document.getElementById('percentLucro').textContent = margem;
            
        } catch (custoError) {
            console.warn('Custos não disponíveis, usando valores padrão');
            document.getElementById('totalDespesas').textContent = 'R$ 0,00';
            document.getElementById('totalCustosEventos').textContent = 'R$ 0,00';
            document.getElementById('numeroCustosEventos').textContent = '0';
            
            // Lucro sem despesas
            document.getElementById('lucroLiquido').textContent = formatarMoeda(receitasMes);
            const margem = 100;
            document.getElementById('percentLucro').textContent = margem;
        }
        
        // Valores a receber (simular por enquanto)
        document.getElementById('valoresAReceber').textContent = formatarMoeda(0);
        document.getElementById('numClientes').textContent = 0;
        
    } catch (error) {
        console.error('Erro ao carregar estatísticas:', error);
    }
}

// PRÓXIMOS EVENTOS
async function carregarProximosEventos() {
    try {
        const res = await fetch('../api/eventos.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            const hoje = new Date();
            const proximos = data.eventos
                .filter(e => new Date(e.data_evento) >= hoje && e.status !== 'cancelado')
                .sort((a, b) => new Date(a.data_evento) - new Date(b.data_evento))
                .slice(0, 5);
            
            const container = document.getElementById('proximosEventos');
            
            if (proximos.length === 0) {
                container.innerHTML = '<p style="color: var(--text-secondary); text-align: center; padding: 20px;">Nenhum evento próximo</p>';
                return;
            }
            
            container.innerHTML = proximos.map(e => {
                const status = e.status === 'aguardando' ? 'EM AGUARDO' : 'CONFIRMADO';
                const statusColor = e.status === 'aguardando' ? 'var(--warning)' : 'var(--success)';
                
                return `
                    <div class="event-item" style="background: rgba(255, 255, 255, 0.02); border-radius: 8px; padding: 12px; cursor: pointer;" 
                         onclick="window.location.href='eventos-eventpro.php'">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <div style="font-weight: 600; margin-bottom: 4px;">${e.tipo}</div>
                                <div style="font-size: 13px; color: var(--text-secondary);">
                                    👤 ${e.cliente_nome} | 📅 ${formatarData(e.data_evento)}
                                </div>
                            </div>
                            <span style="font-size: 11px; padding: 4px 8px; background: ${statusColor}20; color: ${statusColor}; border-radius: 4px; font-weight: 600;">
                                ${status}
                            </span>
                        </div>
                    </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar próximos eventos:', error);
    }
}

// ORIGEM DOS CLIENTES
async function carregarOrigemClientes() {
    try {
        const res = await fetch('../api/clientes.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            const clientes = data.clientes;
            const origens = {};
            const totalClientes = clientes.length;
            
            clientes.forEach(c => {
                const origem = c.origem || 'Indicação';
                origens[origem] = (origens[origem] || 0) + 1;
            });
            
            const container = document.getElementById('origemClientes');
            const cores = {
                'Instagram': '#E1306C',
                'Facebook': '#1877F2',
                'Google': '#4285F4',
                'Indicação': '#FFD23F'
            };
            
            container.innerHTML = Object.entries(origens)
                .sort((a, b) => b[1] - a[1])
                .map(([origem, qtd]) => {
                    const percent = totalClientes > 0 ? ((qtd / totalClientes) * 100).toFixed(0) : 0;
                    const cor = cores[origem] || '#A0AEC0';
                    
                    return `
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="font-size: 14px; font-weight: 600;">${origem}</span>
                                <span style="font-size: 14px; font-weight: 700; color: ${cor};">${qtd}</span>
                            </div>
                            <div style="height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: ${percent}%; background: ${cor}; transition: width 0.3s;"></div>
                            </div>
                            <div style="text-align: right; font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                                ${percent}%
                            </div>
                        </div>
                    `;
                }).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar origem clientes:', error);
    }
}

// TIPOS DE EVENTOS
async function carregarTiposEventos() {
    try {
        const res = await fetch('../api/eventos.php?action=list');
        const data = await res.json();
        
        if (data.success) {
            const eventos = data.eventos;
            const tipos = {};
            const totalEventos = eventos.length;
            
            eventos.forEach(e => {
                const tipo = e.tipo || 'Outro';
                tipos[tipo] = (tipos[tipo] || 0) + 1;
            });
            
            const container = document.getElementById('tiposEventos');
            const cores = ['#FF6B35', '#06D6A0', '#4F46E5', '#FFD23F', '#EF476F'];
            
            container.innerHTML = Object.entries(tipos)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5)
                .map(([tipo, qtd], index) => {
                    const percent = totalEventos > 0 ? ((qtd / totalEventos) * 100).toFixed(0) : 0;
                    const cor = cores[index % cores.length];
                    
                    return `
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                <span style="font-size: 14px; font-weight: 600;">${tipo}</span>
                                <span style="font-size: 14px; font-weight: 700; color: ${cor};">${qtd}</span>
                            </div>
                            <div style="height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: ${percent}%; background: ${cor}; transition: width 0.3s;"></div>
                            </div>
                            <div style="text-align: right; font-size: 11px; color: var(--text-secondary); margin-top: 2px;">
                                ${percent}%
                            </div>
                        </div>
                    `;
                }).join('');
        }
    } catch (error) {
        console.error('Erro ao carregar tipos de eventos:', error);
    }
}

// TRANSAÇÕES RECENTES
async function carregarTransacoes() {
    try {
        // Buscar eventos recentes (receitas)
        const resEventos = await fetch('../api/eventos.php?action=list');
        const dataEventos = await resEventos.json();
        
        const transacoes = [];
        
        if (dataEventos.success) {
            dataEventos.eventos.forEach(e => {
                if (parseFloat(e.valor_sinal || 0) > 0 && e.status !== 'cancelado') {
                    transacoes.push({
                        tipo: 'receita',
                        descricao: `Sinal - ${e.tipo}`,
                        valor: parseFloat(e.valor_sinal),
                        data: e.data_sinal || e.data_evento,
                        cliente: e.cliente_nome
                    });
                }
            });
        }

        // Buscar pagamentos adicionais de movimentacoes_financeiras
        try {
            const resMov = await fetch('../api/financeiro.php?action=transacoes&tipo=receita');
            const dataMov = await resMov.json();
            if (dataMov.success && dataMov.transacoes) {
                dataMov.transacoes
                    .filter(t => t.tipo_movimentacao === 'pagamento_evento' && !t.descricao?.startsWith('Sinal - '))
                    .forEach(t => {
                        transacoes.push({
                            tipo: 'receita',
                            descricao: t.descricao || 'Pagamento evento',
                            valor: parseFloat(t.valor),
                            data: t.data,
                            cliente: ''
                        });
                    });
            }
        } catch (movError) {
            console.warn('Movimentações não disponíveis para transações');
        }

        // Tentar buscar custos
        try {
            const response = await fetch('../api/custos.php?action=listar');
            const text = await response.text();
            
            if (text && text.trim() !== '') {
                const dataCustos = JSON.parse(text);
                
                if (dataCustos.success && dataCustos.custos) {
                    dataCustos.custos.forEach(c => {
                        transacoes.push({
                            tipo: 'despesa',
                            descricao: c.descricao,
                            valor: parseFloat(c.valor),
                            data: c.data_custo,
                            categoria: c.categoria
                        });
                    });
                }
            }
        } catch (custoError) {
            console.warn('Custos não disponíveis para transações');
        }
        
        // Ordenar por data
        transacoes.sort((a, b) => new Date(b.data) - new Date(a.data));
        
        const container = document.getElementById('transacoesRecentes');
        
        if (transacoes.length === 0) {
            container.innerHTML = '<p style="color: var(--text-secondary); text-align: center; padding: 40px;">Nenhuma transação registrada</p>';
            return;
        }
        
        container.innerHTML = transacoes.slice(0, 15).map(t => {
            const isReceita = t.tipo === 'receita';
            const icon = isReceita ? '💰' : '💸';
            const cor = isReceita ? 'var(--success)' : 'var(--danger)';
            const sinal = isReceita ? '+' : '-';
            
            return `
                <div class="transaction-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255, 255, 255, 0.02); border-radius: 8px; margin-bottom: 8px; border-left: 3px solid ${cor};">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span>${icon}</span>
                            <span style="font-weight: 600;">${t.descricao}</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary);">
                            ${formatarData(t.data)}
                            ${t.cliente ? ` • ${t.cliente}` : ''}
                            ${t.categoria ? ` • ${t.categoria}` : ''}
                        </div>
                    </div>
                    <div style="font-size: 16px; font-weight: 700; color: ${cor};">
                        ${sinal} ${formatarMoeda(t.valor)}
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Erro ao carregar transações:', error);
    }
}

// UTILITÁRIOS
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

// Carregar ao iniciar
document.addEventListener('DOMContentLoaded', carregarDashboard);
