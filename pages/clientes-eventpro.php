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
    <title>Clientes - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-30px); }
            60% { transform: translateY(-15px); }
        }

        /* Mobile - Clientes */
        @media (max-width: 768px) {
            .cli-search-row { flex-direction: column !important; gap: 10px !important; }
            .cli-search-row .btn { width: 100% !important; }

            /* Modal responsivo no mobile */
            #modalCliente .modal-content {
                width: 95vw !important;
                max-width: 95vw !important;
                max-height: 90vh !important;
                margin: 5vh auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            /* Form rows empilham no mobile */
            #modalCliente .form-row {
                flex-direction: column !important;
                gap: 0 !important;
            }

            #modalCliente .form-row .form-group {
                width: 100% !important;
                margin-bottom: 12px !important;
            }

            /* Inputs com tamanho adequado para toque */
            #modalCliente .form-input,
            #modalCliente .form-select,
            #modalCliente .form-textarea {
                font-size: 16px !important; /* Evita zoom automático no iOS */
                padding: 12px !important;
            }

            /* Botão submit sempre visível e grande */
            #modalCliente .btn-submit {
                width: 100% !important;
                padding: 16px !important;
                font-size: 16px !important;
                margin-top: 16px !important;
                position: sticky;
                bottom: 0;
                z-index: 10;
            }

            /* Cards de clientes */
            .client-actions {
                flex-wrap: wrap !important;
                gap: 6px !important;
            }
            .client-action-btn {
                font-size: 12px !important;
                padding: 6px 10px !important;
                flex: 1 1 calc(50% - 6px) !important;
                text-align: center !important;
            }
            .client-card-header {
                flex-direction: column !important;
                gap: 10px !important;
            }
            .financial-grid {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <!-- Cabeçalho -->
    <div class="page-header">
        <h1 class="page-title">👥 Clientes</h1>
        <p class="page-subtitle">Gerencie seus clientes e suas informações</p>
    </div>

    <!-- Estatísticas - Layout inline com gradientes (padrão Gestão Financeira) -->
    <style>
        .cli-stats .stat-card::before { display: none; }
        .cli-stats .stat-card { display: flex; align-items: center; gap: 12px; overflow: visible; padding: 16px 14px; }
        .cli-stats .stat-card .stat-icon { font-size: 24px; width: 48px; height: 48px; min-width: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; flex-shrink: 0; margin-bottom: 0; }
        .cli-stats .stat-card .stat-info { flex: 1; min-width: 0; }
        .cli-stats .stat-card .stat-info .stat-label { font-size: 10px; color: #A0AEC0; margin-bottom: 2px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; }
        .cli-stats .stat-card .stat-info .stat-value { font-size: 18px; font-weight: 800; margin-bottom: 0; white-space: nowrap; }
    </style>
    <div class="cli-stats">
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(79, 70, 229, 0.05));">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.15); color: #4F46E5;">👥</div>
            <div class="stat-info">
                <div class="stat-label">Total de Clientes</div>
                <div class="stat-value" id="totalClientes" style="color: #4F46E5;">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(255, 210, 63, 0.2), rgba(255, 210, 63, 0.05));">
            <div class="stat-icon" style="background: rgba(255, 210, 63, 0.15); color: #FFD23F;">🎂</div>
            <div class="stat-info">
                <div class="stat-label">Aniversariantes</div>
                <div class="stat-value" id="aniversariantesProximos" style="color: #FFD23F;">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(225, 48, 108, 0.2), rgba(225, 48, 108, 0.05));">
            <div class="stat-icon" style="background: rgba(225, 48, 108, 0.15); color: #E1306C;">📸</div>
            <div class="stat-info">
                <div class="stat-label">Instagram</div>
                <div class="stat-value" id="clientesInstagram" style="color: #E1306C;">0</div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, rgba(24, 119, 242, 0.2), rgba(24, 119, 242, 0.05));">
            <div class="stat-icon" style="background: rgba(24, 119, 242, 0.15); color: #1877F2;">📘</div>
            <div class="stat-info">
                <div class="stat-label">Facebook</div>
                <div class="stat-value" id="clientesFacebook" style="color: #1877F2;">0</div>
            </div>
        </div>
    </div>
    </div>

    <!-- Busca e Ações -->
    <div class="cli-search-row" style="display: flex; gap: 16px; align-items: center; margin: 24px 0;">
        <div class="search-bar" style="flex: 1;">
            <i class="search-icon">🔍</i>
            <input type="text" class="search-input" id="clientSearch" placeholder="Buscar cliente por nome, telefone ou email..." oninput="searchClients()">
        </div>
        <button class="btn btn-primary" onclick="abrirModalNovoCliente()" style="white-space: nowrap; flex-shrink: 0;">
            + Novo Cliente
        </button>
    </div>

    <!-- Lista de Clientes -->
    <div id="clientsList"></div>
</div>

<!-- Modal Novo/Editar Cliente -->
<div class="modal" id="modalCliente">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalClienteTitle">Novo Cliente</h3>
            <button class="close-btn" onclick="fecharModalCliente()">×</button>
        </div>
        <form id="formCliente" onsubmit="salvarCliente(event)">
            <input type="hidden" id="clienteId">
            
            <div class="form-group">
                <label class="form-label">Nome Completo *</label>
                <input type="text" class="form-input" id="clienteNome" required placeholder="Nome do cliente">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-input" id="clienteEmail" placeholder="email@exemplo.com (opcional)">
                </div>
                <div class="form-group">
                    <label class="form-label">Telefone *</label>
                    <input type="tel" class="form-input" id="clienteTelefone" required placeholder="(00) 00000-0000" maxlength="15" oninput="formatarTelefoneInput(this)">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">CPF/CNPJ</label>
                    <input type="text" class="form-input" id="clienteCpf" placeholder="CPF: 000.000.000-00 ou CNPJ" maxlength="18" oninput="formatarCpfCnpjInput(this)">
                </div>
                <div class="form-group">
                    <label class="form-label">Data de Aniversário 🎂</label>
                    <input type="date" class="form-input" id="clienteAniversario" placeholder="DD/MM/AAAA">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Como nos conheceu? 📢</label>
                    <select class="form-select" id="clienteOrigem" required>
                        <option value="">Selecione...</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="google">Google</option>
                        <option value="indicacao">Indicação</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Endereço</label>
                <div class="form-row" style="margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">CEP 📮</label>
                        <input type="text" class="form-input" id="clienteCep" placeholder="00000-000 (opcional)" maxlength="9" oninput="buscarCep()">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Número</label>
                        <input type="text" class="form-input" id="clienteNumero" placeholder="Nº">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Rua/Avenida</label>
                    <input type="text" class="form-input" id="clienteRua" placeholder="Digite a rua ou preencha pelo CEP">
                </div>
                <div class="form-row" style="margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Bairro</label>
                        <input type="text" class="form-input" id="clienteBairro" placeholder="Digite o bairro">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Complemento</label>
                        <input type="text" class="form-input" id="clienteComplemento" placeholder="Apto, Bloco, etc.">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Cidade</label>
                        <input type="text" class="form-input" id="clienteCidade" placeholder="Digite a cidade">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-input" id="clienteEstado" placeholder="UF" style="text-transform: uppercase;" maxlength="2">
                    </div>
                </div>
                <div id="cepLoading" style="display: none; text-align: center; padding: 8px; background: rgba(255, 210, 63, 0.1); border-radius: 8px; margin-top: 8px; font-size: 13px; color: var(--accent);">
                    🔍 Buscando endereço...
                </div>
                <div id="cepError" style="display: none; text-align: center; padding: 8px; background: rgba(239, 71, 111, 0.1); border: 1px solid var(--danger); border-radius: 8px; margin-top: 8px; font-size: 13px; color: var(--danger);">
                    ❌ CEP não encontrado. Verifique o número digitado.
                </div>
                <div id="cepSuccess" style="display: none; text-align: center; padding: 8px; background: rgba(6, 214, 160, 0.1); border: 1px solid var(--success); border-radius: 8px; margin-top: 8px; font-size: 13px; color: var(--success);">
                    ✅ Endereço encontrado! Preencha o número da residência.
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea class="form-textarea" id="clienteObservacoes" placeholder="Informações adicionais sobre o cliente..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-submit">Cadastrar Cliente</button>
        </form>
    </div>
</div>

<!-- Modal Sucesso Cliente -->
<div class="modal" id="modalSucessoCliente">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 class="modal-title">✅ Cliente Cadastrado!</h2>
            <button class="close-btn" onclick="fecharModalSucesso()">×</button>
        </div>
        
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 64px; margin-bottom: 16px; animation: bounce 1s;">🎉</div>
            <h3 style="font-family: 'Syne', sans-serif; font-size: 20px; margin-bottom: 8px; color: var(--success);">
                Cliente cadastrado com sucesso!
            </h3>
            <p id="sucessoClienteNome" style="color: var(--text-secondary); margin-bottom: 24px; font-size: 16px;">
                <!-- Nome do cliente será inserido aqui -->
            </p>
            
            <div style="background: rgba(255, 210, 63, 0.1); border: 1px solid var(--accent); border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: left;">
                <div style="font-size: 14px; color: var(--accent); font-weight: 600; margin-bottom: 8px;">
                    💡 Próximo Passo
                </div>
                <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
                    Escolha uma das opções abaixo para continuar o cadastro:
                </div>
            </div>
            
            <div style="display: grid; gap: 12px;">
                <button onclick="atalhoOrcamento()" class="btn" style="background: linear-gradient(135deg, var(--accent), #FFA500); color: var(--bg-dark); font-weight: 600; padding: 16px; font-size: 15px; border: none;">
                    💼 Criar Orçamento Completo
                    <div style="font-size: 11px; opacity: 0.8; margin-top: 4px; font-weight: 400;">
                        Monte um orçamento com serviços, taxas e descontos
                    </div>
                </button>
                
                <button onclick="atalhoEvento()" class="btn btn-secondary" style="padding: 16px; font-size: 15px;">
                    🎉 Criar Evento Direto
                    <div style="font-size: 11px; opacity: 0.7; margin-top: 4px; font-weight: 400;">
                        Cadastre o evento sem orçamento
                    </div>
                </button>
                
                <button onclick="atalhoFinanceiro()" class="btn" style="background: rgba(0, 78, 137, 0.2); color: var(--secondary); border: 1px solid var(--secondary); padding: 16px; font-size: 15px;">
                    💰 Adicionar Dados Financeiros
                    <div style="font-size: 11px; opacity: 0.7; margin-top: 4px; font-weight: 400;">
                        Registre valores, sinais e prazos
                    </div>
                </button>
                
                <button onclick="fecharModalSucesso()" class="btn" style="background: transparent; border: 1px solid var(--border); color: var(--text-secondary); padding: 12px;">
                    ⏭️ Adicionar Depois
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluir modal financeiro
$modalPath = __DIR__ . '/../includes/modal-financeiro.php';
if (file_exists($modalPath)) {
    include $modalPath;
    echo "<!-- Modal financeiro incluído com sucesso -->\n";
} else {
    echo "<!-- AVISO: Modal financeiro NÃO encontrado em: $modalPath -->\n";
}
?>

<script>
    // Garantir que a função abrirModalFinanceiro existe
    if (typeof abrirModalFinanceiro === 'undefined') {
        console.error('⚠️ Função abrirModalFinanceiro não foi carregada!');
        
        // Criar função temporária para debug
        window.abrirModalFinanceiro = function(clienteId, novoCliente = false) {
            console.log('📞 Chamando abrirModalFinanceiro:', clienteId, novoCliente);
            
            const modal = document.getElementById('modalFinanceiro');
            if (!modal) {
                alert('❌ Modal financeiro não encontrado no DOM!\n\nVerifique se o arquivo modal-financeiro.php foi incluído.');
                return;
            }
            
            // Abrir modal
            modal.classList.add('active');
            
            // Preencher nome do cliente
            document.getElementById('financeiroClienteId').value = clienteId;
            document.getElementById('financeiroClienteNome').textContent = 'Cliente #' + clienteId;
            
            console.log('✅ Modal aberto!');
        };
    } else {
        console.log('✅ Função abrirModalFinanceiro carregada corretamente');
    }
</script>

<script>
    let todosClientes = [];
    let todosEventos = [];
    let currentClientSearch = '';
    
    // Carregar clientes e eventos
    async function carregarDados() {
        try {
            // Carregar clientes e eventos em paralelo + cache-busting
            const [responseClientes, responseEventos] = await Promise.all([
                fetch('../api/clientes.php?action=list&_=' + Date.now()),
                fetch('../api/eventos.php?action=list&_=' + Date.now())
            ]);

            const [dataClientes, dataEventos] = await Promise.all([
                responseClientes.json(),
                responseEventos.json()
            ]);

            if (dataClientes.success) {
                todosClientes = dataClientes.clientes;
            }

            if (dataEventos.success) {
                todosEventos = dataEventos.eventos;
            }

            renderizarClientes();
            atualizarEstatisticas();

        } catch (error) {
            console.error('Erro ao carregar dados:', error);
        }
    }
    
    // Renderizar lista de clientes
    function renderizarClientes() {
        const container = document.getElementById('clientsList');
        
        let clientesFiltrados = todosClientes;
        
        // Aplicar busca se houver
        if (currentClientSearch) {
            const termo = currentClientSearch.toLowerCase();
            clientesFiltrados = todosClientes.filter(c => 
                c.nome.toLowerCase().includes(termo) ||
                (c.email && c.email.toLowerCase().includes(termo)) ||
                (c.telefone && c.telefone.includes(termo))
            );
        }
        
        // Ordenar alfabeticamente
        clientesFiltrados.sort((a, b) => a.nome.localeCompare(b.nome));
        
        if (clientesFiltrados.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <h3 class="empty-state-title">${currentClientSearch ? 'Nenhum cliente encontrado' : 'Nenhum cliente cadastrado'}</h3>
                    <p class="empty-state-text">
                        ${currentClientSearch ? 'Tente outro termo de busca' : 'Comece adicionando seu primeiro cliente'}
                    </p>
                </div>
            `;
            return;
        }
        
        const html = clientesFiltrados.map(c => {
            const iniciais = obterIniciais(c.nome);
            const hoje = new Date();
            const aniversario = c.data_aniversario ? new Date(c.data_aniversario + 'T00:00:00') : null;
            const isAniversarioMes = aniversario && aniversario.getMonth() === hoje.getMonth();
            
            // Buscar evento do cliente
            const eventoCliente = todosEventos.find(e => e.cliente_id == c.id);
            
            const origemLabels = {
                'facebook': '📘 Facebook',
                'instagram': '📸 Instagram',
                'google': '🔍 Google',
                'indicacao': '👥 Indicação',
                'outros': '📌 Outros'
            };
            
            // Seção financeira - usar dados do CLIENTE, não do evento
            let secaoFinanceira = '';
            const valorTotal = parseFloat(c.valor_total || 0);
            const valorSinal = parseFloat(c.valor_sinal || 0);
            
            if (valorTotal > 0) {
                const totalPago = parseFloat(c.total_pago || c.valor_sinal || 0);
                const saldoDevedor = valorTotal - totalPago;
                const percentPago = valorTotal > 0 ? (totalPago / valorTotal) * 100 : 0;
                
                const isPago = saldoDevedor <= 0;
                const dataVenc = c.data_vencimento ? new Date(c.data_vencimento + 'T00:00:00') : null;
                const isVencido = dataVenc && dataVenc < hoje && !isPago;
                
                let statusClass = 'status-pending';
                let statusText = 'PENDENTE';
                if (isPago) {
                    statusClass = 'status-paid';
                    statusText = 'PAGO';
                } else if (isVencido) {
                    statusClass = 'status-overdue';
                    statusText = 'VENCIDO';
                }
                
                secaoFinanceira = `
                    <div class="client-financial" onclick="event.stopPropagation(); abrirModalFinanceiro(${c.id})" style="cursor: pointer;">
                        <div class="financial-header">
                            <span class="financial-title">💰 Financeiro</span>
                            <span class="financial-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="financial-grid">
                            <div class="financial-item">
                                <div class="financial-label">Valor Total</div>
                                <div class="financial-value total">${formatarMoeda(valorTotal)}</div>
                            </div>
                            <div class="financial-item">
                                <div class="financial-label">Total Pago</div>
                                <div class="financial-value paid">${formatarMoeda(totalPago)}</div>
                                ${c.data_sinal ? `<div class="financial-date">Sinal: ${formatarData(c.data_sinal)}</div>` : ''}
                                ${c.forma_pagamento_sinal ? `<div class="financial-date">Via: ${getFormaPagamentoLabel(c.forma_pagamento_sinal)}</div>` : ''}
                            </div>
                            <div class="financial-item">
                                <div class="financial-label">Saldo Devedor</div>
                                <div class="financial-value pending">${formatarMoeda(saldoDevedor)}</div>
                                ${c.data_vencimento ? `<div class="financial-date">Venc: ${formatarData(c.data_vencimento)}</div>` : ''}
                            </div>
                        </div>
                        <div class="payment-progress">
                            <div class="progress-label">
                                <span>Progresso do Pagamento</span>
                                <span>${percentPago.toFixed(0)}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: ${percentPago}%;"></div>
                            </div>
                        </div>
                        <div style="text-align: center; margin-top: 8px; padding-top: 8px; border-top: 1px solid var(--border); color: var(--text-secondary); font-size: 12px;">
                            👆 Clique para editar informações financeiras
                        </div>
                    </div>
                `;
            } else {
                secaoFinanceira = `
                    <div class="client-financial" onclick="event.stopPropagation(); ${eventoCliente ? `abrirModalFinanceiro(${c.id})` : `alert('Cliente não possui evento cadastrado')`}" style="cursor: pointer; border: 2px dashed var(--border); background: rgba(255, 255, 255, 0.01);">
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;">💰</div>
                            <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 4px;">Nenhuma informação financeira</div>
                            <div style="font-size: 13px; color: var(--primary); font-weight: 600;">
                                👆 ${eventoCliente ? 'Clique para adicionar dados financeiros' : 'Crie um evento primeiro'}
                            </div>
                        </div>
                    </div>
                `;
            }
            
            return `
                <div class="client-card" data-cliente-id="${c.id}">
                    <div class="client-card-header">
                        <div class="client-main-info">
                            <div class="client-avatar">${iniciais}</div>
                            <div class="client-details-main">
                                <h3>${c.nome}</h3>
                                ${eventoCliente ? `
                                    <div onclick="event.stopPropagation(); abrirDetalhesEvento(${eventoCliente.id})" style="font-size: 13px; color: var(--primary); margin-top: 4px; cursor: pointer; text-decoration: underline;">
                                        🎉 ${eventoCliente.tipo || 'Evento'}
                                    </div>
                                ` : ''}
                                <div class="client-email">📧 ${c.email || 'Não informado'}</div>
                                <div class="client-phone">📱 ${formatarTelefone(c.telefone)}</div>
                            </div>
                        </div>
                        <div class="client-badges">
                            ${isAniversarioMes ? '<span class="client-badge badge-birthday">🎂 Aniversário Este Mês</span>' : ''}
                            ${c.origem ? `<span class="client-badge badge-origem">${origemLabels[c.origem] || c.origem}</span>` : ''}
                        </div>
                    </div>
                    
                    ${secaoFinanceira}
                    
                    <div class="client-info-grid">
                        ${c.data_aniversario ? `
                            <div class="client-info-item">
                                🎂 <strong>${formatarData(c.data_aniversario)}</strong>
                            </div>
                        ` : ''}
                        ${c.cpf ? `
                            <div class="client-info-item">
                                📄 <strong>${formatarCPF(c.cpf)}</strong>
                            </div>
                        ` : ''}
                        ${c.endereco ? `
                            <div class="client-info-item">
                                📍 <strong>${c.endereco}</strong>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${c.observacoes ? `
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--border); color: var(--text-secondary); font-size: 13px;">
                            📝 ${c.observacoes}
                        </div>
                    ` : ''}
                    
                    <div class="client-actions">
                        <button class="client-action-btn" onclick="editarCliente(${c.id})">✏️ Editar</button>
                        <button class="client-action-btn" onclick="abrirModalDocumentos(${c.id})">📄 Gerar Documento</button>
                        <button class="client-action-btn" onclick="${eventoCliente ? `abrirModalFinanceiro(${c.id})` : `alert('Crie um evento primeiro')`}">💰 Financeiro</button>
                        <button class="client-action-btn" onclick="novoEventoComCliente(${c.id}, '${c.nome.replace(/'/g, "\\'")}')">🎉 Novo Evento</button>
                        <button class="client-action-btn" onclick="alert('Função em desenvolvimento')">💬 Mensagem</button>
                        <button class="client-action-btn" onclick="excluirCliente(${c.id})">🗑️ Excluir</button>
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = html;
    }
    
    // Atualizar estatísticas
    function atualizarEstatisticas() {
        document.getElementById('totalClientes').textContent = todosClientes.length;
        
        const hoje = new Date();
        const mesAtual = hoje.getMonth();
        
        const aniversariantes = todosClientes.filter(c => {
            if (!c.data_aniversario) return false;
            const aniv = new Date(c.data_aniversario + 'T00:00:00');
            return aniv.getMonth() === mesAtual;
        }).length;
        
        document.getElementById('aniversariantesProximos').textContent = aniversariantes;
        document.getElementById('clientesInstagram').textContent = todosClientes.filter(c => c.origem === 'instagram').length;
        document.getElementById('clientesFacebook').textContent = todosClientes.filter(c => c.origem === 'facebook').length;
    }
    
    // Buscar clientes
    function searchClients() {
        currentClientSearch = document.getElementById('clientSearch').value;
        renderizarClientes();
    }
    
    // Modal Novo Cliente
    function abrirModalNovoCliente() {
        document.getElementById('modalClienteTitle').textContent = 'Novo Cliente';
        document.getElementById('formCliente').reset();
        document.getElementById('clienteId').value = '';
        document.querySelector('#formCliente .btn-submit').textContent = 'Cadastrar Cliente';
        document.getElementById('modalCliente').classList.add('active');
    }
    
    function fecharModalCliente() {
        document.getElementById('modalCliente').classList.remove('active');
    }
    
    // Editar cliente
    async function editarCliente(id) {
        try {
            const response = await fetch(`../api/clientes.php?action=get&id=${id}`);
            const data = await response.json();
            
            if (data.success && data.cliente) {
                document.getElementById('modalClienteTitle').textContent = 'Editar Cliente';
                document.getElementById('clienteId').value = data.cliente.id;
                document.getElementById('clienteNome').value = data.cliente.nome;
                document.getElementById('clienteTelefone').value = data.cliente.telefone;
                document.getElementById('clienteEmail').value = data.cliente.email || '';
                document.getElementById('clienteAniversario').value = data.cliente.data_aniversario || '';
                document.getElementById('clienteCpf').value = data.cliente.cpf || '';
                document.getElementById('clienteOrigem').value = data.cliente.origem || '';
                document.getElementById('clienteObservacoes').value = data.cliente.observacoes || '';
                
                // Novos campos de endereço
                document.getElementById('clienteCep').value = data.cliente.cep || '';
                document.getElementById('clienteNumero').value = data.cliente.numero || '';
                document.getElementById('clienteRua').value = data.cliente.rua || '';
                document.getElementById('clienteBairro').value = data.cliente.bairro || '';
                document.getElementById('clienteComplemento').value = data.cliente.complemento || '';
                document.getElementById('clienteCidade').value = data.cliente.cidade || '';
                document.getElementById('clienteEstado').value = data.cliente.estado || '';
                
                document.querySelector('#formCliente .btn-submit').textContent = 'Salvar Alterações';
                document.getElementById('modalCliente').classList.add('active');
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    }
    
    // Salvar cliente
    let ultimoClienteId = null;
    let ultimoClienteNome = null;
    
    async function salvarCliente(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData();
            const id = document.getElementById('clienteId').value;
            
            formData.append('action', id ? 'update' : 'create');
            if (id) formData.append('id', id);
            formData.append('nome', document.getElementById('clienteNome').value);
            formData.append('telefone', document.getElementById('clienteTelefone').value);
            formData.append('email', document.getElementById('clienteEmail').value);
            formData.append('aniversario', document.getElementById('clienteAniversario').value);
            formData.append('cpf', document.getElementById('clienteCpf').value);
            formData.append('origem', document.getElementById('clienteOrigem').value);
            formData.append('observacoes', document.getElementById('clienteObservacoes').value);
            
            // Novos campos de endereço
            formData.append('cep', document.getElementById('clienteCep').value);
            formData.append('numero', document.getElementById('clienteNumero').value);
            formData.append('rua', document.getElementById('clienteRua').value);
            formData.append('bairro', document.getElementById('clienteBairro').value);
            formData.append('complemento', document.getElementById('clienteComplemento').value);
            formData.append('cidade', document.getElementById('clienteCidade').value);
            formData.append('estado', document.getElementById('clienteEstado').value);
            
            // Montar endereço completo
            const enderecoCompleto = [
                document.getElementById('clienteRua').value,
                document.getElementById('clienteNumero').value,
                document.getElementById('clienteBairro').value,
                document.getElementById('clienteCidade').value,
                document.getElementById('clienteEstado').value
            ].filter(Boolean).join(', ');
            
            formData.append('endereco', enderecoCompleto);
            
            const response = await fetch('../api/clientes.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (!id) {
                    // Cliente novo - mostrar modal de sucesso
                    ultimoClienteId = data.id;
                    ultimoClienteNome = document.getElementById('clienteNome').value;
                    
                    fecharModalCliente();
                    carregarDados();
                    
                    // Mostrar modal de sucesso
                    document.getElementById('sucessoClienteNome').textContent = ultimoClienteNome;
                    document.getElementById('modalSucessoCliente').classList.add('active');
                } else {
                    // Cliente editado - apenas fechar e atualizar
                    alert('Cliente atualizado!');
                    fecharModalCliente();
                    carregarDados();
                }
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao salvar cliente');
        }
    }
    
    // Excluir cliente
    async function excluirCliente(id) {
        if (!confirm('Tem certeza que deseja excluir este cliente?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            const response = await fetch('../api/clientes.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Cliente excluído!');
                carregarDados();
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao excluir cliente');
        }
    }
    
    // Criar novo evento com cliente pré-selecionado
    function novoEventoComCliente(clienteId, clienteNome) {
        // Redirecionar com parâmetros na URL
        window.location.href = `criar-orcamento.php?cliente_id=${clienteId}&cliente_nome=${encodeURIComponent(clienteNome)}`;
    }
    
    // Abrir detalhes do evento
    function abrirDetalhesEvento(eventoId) {
        window.location.href = `eventos-eventpro.php?evento_id=${eventoId}`;
    }
    
    // Funções auxiliares
    function obterIniciais(nome) {
        if (!nome) return '??';
        const partes = nome.trim().split(' ').filter(p => p.length > 0);
        if (partes.length === 0) return '??';
        if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
        return (partes[0][0] + partes[partes.length - 1][0]).toUpperCase();
    }
    
    function getFormaPagamentoLabel(forma) {
        const labels = {
            'pix': '💳 PIX',
            'dinheiro': '💵 Dinheiro',
            'debito': '💳 Débito',
            'credito': '💳 Crédito',
            'transferencia': '🏦 Transferência',
            'boleto': '📄 Boleto'
        };
        return labels[forma] || forma;
    }

    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    }
    
    function formatarData(data) {
        if (!data) return 'Não informado';
        return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
    }
    
    function formatarTelefone(value) {
        let digits = (value || '').replace(/\D/g, '');
        if (digits.length > 11) digits = digits.substring(0, 11);
        
        if (digits.length <= 10) {
            return '(' + digits.substring(0, 2) + ') ' + 
                   digits.substring(2, 6) + '-' + 
                   digits.substring(6);
        } else {
            return '(' + digits.substring(0, 2) + ') ' + 
                   digits.substring(2, 7) + '-' + 
                   digits.substring(7);
        }
    }
    
    function formatarCPF(value) {
        let digits = (value || '').replace(/\D/g, '');
        if (digits.length <= 11) {
            digits = digits.replace(/^(\d{3})(\d)/, '$1.$2');
            digits = digits.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
            digits = digits.replace(/\.(\d{3})(\d)/, '.$1-$2');
            return digits;
        }
        return value;
    }
    
    // Fechar modal de sucesso
    function fecharModalSucesso() {
        document.getElementById('modalSucessoCliente').classList.remove('active');
    }
    
    // Atalho para criar orçamento após cadastrar cliente
    function atalhoOrcamento() {
        fecharModalSucesso();
        // Redirecionar para página de criar orçamento com parâmetros
        window.location.href = `criar-orcamento.php?cliente_id=${ultimoClienteId}&cliente_nome=${encodeURIComponent(ultimoClienteNome)}`;
    }
    
    // Atalho para criar evento direto
    function atalhoEvento() {
        fecharModalSucesso();
        // Redirecionar para página de eventos com parâmetros
        window.location.href = `eventos-eventpro.php?cliente_id=${ultimoClienteId}&cliente_nome=${encodeURIComponent(ultimoClienteNome)}`;
    }
    
    // Atalho para adicionar dados financeiros
    function atalhoFinanceiro() {
        fecharModalSucesso();
        // Abrir modal financeiro
        setTimeout(() => {
            if (typeof abrirModalFinanceiro === 'function') {
                abrirModalFinanceiro(ultimoClienteId, true);
            } else {
                alert('Modal financeiro não disponível nesta página.');
            }
        }, 300);
    }
    
    // Buscar CEP via API ViaCEP
    let cepTimeout;
    
    // ===================================
    // FORMATAÇÃO AUTOMÁTICA
    // ===================================
    function formatarTelefoneInput(input) {
        let valor = input.value.replace(/\D/g, '');
        
        if (valor.length <= 11) {
            if (valor.length <= 10) {
                // Formato: (99) 9999-9999
                valor = valor.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else {
                // Formato: (99) 99999-9999
                valor = valor.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            }
        }
        
        input.value = valor;
    }
    
    function formatarCpfCnpjInput(input) {
        let valor = input.value.replace(/\D/g, '');
        
        if (valor.length <= 11) {
            // CPF: 999.999.999-99
            valor = valor.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2}).*/, '$1.$2.$3-$4');
        } else {
            // CNPJ: 99.999.999/9999-99
            valor = valor.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2}).*/, '$1.$2.$3/$4-$5');
        }
        
        input.value = valor;
    }
    
    // Busca CEP com fallback: tenta ViaCEP primeiro, depois BrasilAPI
    async function consultarCepApi(cep) {
        const fetchComTimeout = (url, ms = 5000) => {
            const ctrl = new AbortController();
            const t = setTimeout(() => ctrl.abort(), ms);
            return fetch(url, { signal: ctrl.signal }).finally(() => clearTimeout(t));
        };

        // 1ª tentativa: ViaCEP
        try {
            const res = await fetchComTimeout(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await res.json();
            if (!data.erro) {
                return { logradouro: data.logradouro || '', bairro: data.bairro || '', localidade: data.localidade || '', uf: data.uf || '' };
            }
        } catch (e) { /* timeout ou erro de rede, tenta fallback */ }

        // 2ª tentativa: BrasilAPI
        const res2 = await fetchComTimeout(`https://brasilapi.com.br/api/cep/v2/${cep}`);
        if (!res2.ok) throw new Error('CEP não encontrado');
        const data2 = await res2.json();
        return { logradouro: data2.street || '', bairro: data2.neighborhood || '', localidade: data2.city || '', uf: data2.state || '' };
    }

    async function buscarCep() {
        const cepInput = document.getElementById('clienteCep');
        const cep = cepInput.value.replace(/\D/g, '');

        // Formatar CEP enquanto digita
        if (cep.length <= 8) {
            const formatted = cep.length > 5 ? cep.substring(0,5) + '-' + cep.substring(5) : cep;
            cepInput.value = formatted;
        }

        // Limpar mensagens
        document.getElementById('cepLoading').style.display = 'none';
        document.getElementById('cepError').style.display = 'none';
        document.getElementById('cepSuccess').style.display = 'none';

        // Só buscar se tiver 8 dígitos
        if (cep.length !== 8) return;

        // Delay para não fazer muitas requisições
        clearTimeout(cepTimeout);
        cepTimeout = setTimeout(async () => {
            try {
                document.getElementById('cepLoading').style.display = 'block';

                const data = await consultarCepApi(cep);

                document.getElementById('cepLoading').style.display = 'none';

                // Preencher campos
                document.getElementById('clienteRua').value = data.logradouro;
                document.getElementById('clienteBairro').value = data.bairro;
                document.getElementById('clienteCidade').value = data.localidade;
                document.getElementById('clienteEstado').value = data.uf;

                document.getElementById('cepSuccess').style.display = 'block';
                document.getElementById('clienteNumero').focus();

                setTimeout(() => {
                    document.getElementById('cepSuccess').style.display = 'none';
                }, 3000);

            } catch (error) {
                document.getElementById('cepLoading').style.display = 'none';
                document.getElementById('cepError').style.display = 'block';
                console.error('Erro ao buscar CEP:', error);
            }
        }, 500);
    }
    
    // Modal financeiro - A função abrirModalFinanceiro é fornecida por modal-financeiro.php
    

    // ===================================
    // GERAÇÃO DE DOCUMENTOS PDF
    // ===================================
    let pdfBlobGlobal    = null;
    let pdfNomeGlobal    = '';
    let pdfMensagemGlobal = '';
    let configEmpresa = {};

    // Carregar configurações da empresa
    async function carregarConfigEmpresa() {
        try {
            const response = await fetch('../api/configuracoes.php?action=get');
            const data = await response.json();
            if (data.success && data.config && data.config.id) {
                configEmpresa = {
                    nomeEmpresa: data.config.nome_empresa || '',
                    nomeCorporativo: data.config.nome_corporativo || '',
                    cnpj: data.config.cnpj || '',
                    telefone: data.config.telefone || '',
                    whatsapp: data.config.whatsapp || '',
                    email: data.config.email || '',
                    cidade: data.config.cidade || '',
                    estado: data.config.estado || '',
                    endereco: data.config.endereco || '',
                    cep: data.config.cep || '',
                    logoBase64: data.config.logo_base64 || null,
                    assinaturaBase64: data.config.assinatura_base64 || null,
                    clausulasContratuais: data.config.clausulas_contratuais || '',
                    instagram: data.config.instagram || '',
                    facebook: data.config.facebook || '',
                    youtube: data.config.youtube || '',
                    site: data.config.site || ''
                };
            }
        } catch (error) {
            console.error('Erro ao carregar config empresa:', error);
        }
    }

    // Abrir modal de documentos
    function abrirModalDocumentos(clienteId) {
        document.getElementById('documentosClienteId').value = clienteId;
        const cliente = todosClientes.find(c => c.id == clienteId);
        if (cliente) {
            document.getElementById('documentosClienteNome').textContent = cliente.nome;
        }
        document.getElementById('modalDocumentos').classList.add('active');
    }

    function fecharModalDocumentos() {
        document.getElementById('modalDocumentos').classList.remove('active');
        // Reabilitar todos os botões ao fechar (evita ficarem travados)
        const botoesModal = document.querySelectorAll('#modalDocumentos button');
        botoesModal.forEach(btn => { btn.disabled = false; btn.style.opacity = '1'; });
        // Remover loading se existir
        const loadingDiv = document.getElementById('loadingGerarDoc');
        if (loadingDiv) loadingDiv.remove();
    }

    // Gerar documento por tipo
    async function gerarDocumento(tipo) {
        const clienteId = parseInt(document.getElementById('documentosClienteId').value);
        const cliente = todosClientes.find(c => c.id === clienteId);
        if (!cliente) return;

        // Feedback visual: desabilitar botões e mostrar loading
        const botoesModal = document.querySelectorAll('#modalDocumentos button');
        botoesModal.forEach(btn => { btn.disabled = true; btn.style.opacity = '0.5'; });
        let loadingDiv = document.getElementById('loadingGerarDoc');
        if (!loadingDiv) {
            loadingDiv = document.createElement('div');
            loadingDiv.id = 'loadingGerarDoc';
            loadingDiv.style.cssText = 'text-align:center;padding:10px;color:#FF6B35;font-size:14px;';
            loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando documento...';
            document.querySelector('#modalDocumentos .modal-body')?.appendChild(loadingDiv);
        }

        const config = configEmpresa;

        // Buscar evento do cliente com dados completos
        let evento = todosEventos.find(e => e.cliente_id == clienteId);

        // Buscar dados completos do evento via API
        if (evento) {
            try {
                const resp = await fetch(`../api/eventos.php?action=get&id=${evento.id}`);
                const evtData = await resp.json();
                if (evtData.success) {
                    evento = evtData.evento;
                }
            } catch (e) {
                console.error('Erro ao buscar evento:', e);
            }
        }

        let nomeArquivo = '';

        try {
            if (tipo === 'orcamento') {
                nomeArquivo = `Orcamento_${cliente.nome.replace(/\s/g, '_')}.pdf`;
                await gerarOrcamentoProfissional(cliente, evento, config, nomeArquivo);
                fecharModalDocumentos();
            } else if (tipo === 'contrato') {
                nomeArquivo = `Contrato_${cliente.nome.replace(/\s/g, '_')}.pdf`;
                await gerarContratoProfissional(cliente, evento, config, nomeArquivo);
                fecharModalDocumentos();
            } else if (tipo === 'recibo') {
                loadingDiv?.remove();
                botoesModal.forEach(btn => { btn.disabled = false; btn.style.opacity = '1'; });
                abrirModalEscolherRecibo(cliente, evento, config);
            } else if (tipo === 'resumo') {
                nomeArquivo = `Resumo_Financeiro_${cliente.nome.replace(/\s/g, '_')}.pdf`;
                await gerarResumoFinanceiroProfissional(cliente, evento, config, nomeArquivo);
                fecharModalDocumentos();
            }
        } catch (error) {
            console.error('Erro ao gerar documento:', error);
            alert('Erro ao gerar documento. Verifique os dados e tente novamente.');
            loadingDiv?.remove();
            botoesModal.forEach(btn => { btn.disabled = false; btn.style.opacity = '1'; });
        }
    }

    // ===== HELPERS PDF =====
    function formatMoney(value) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
    }

    function formatDatePdf(date) {
        if (!date) return 'A definir';
        return new Date(date + 'T00:00:00').toLocaleDateString('pt-BR');
    }

    function formatarCpfCnpjString(value) {
        let digits = (value || '').replace(/\D/g, '');
        if (digits.length <= 11) {
            digits = digits.replace(/^(\d{3})(\d)/, '$1.$2');
            digits = digits.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
            digits = digits.replace(/\.(\d{3})(\d)/, '.$1-$2');
            return digits;
        } else {
            digits = digits.substring(0, 14);
            digits = digits.replace(/^(\d{2})(\d)/, '$1.$2');
            digits = digits.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            digits = digits.replace(/\.(\d{3})(\d)/, '.$1/$2');
            digits = digits.replace(/(\d{4})(\d)/, '$1-$2');
            return digits;
        }
    }

    function formatarTelefoneString(value) {
        let digits = (value || '').replace(/\D/g, '');
        if (digits.length > 11) digits = digits.substring(0, 11);
        if (digits.length <= 2) return digits;
        else if (digits.length <= 6) return '(' + digits.substring(0, 2) + ') ' + digits.substring(2);
        else if (digits.length <= 10) return '(' + digits.substring(0, 2) + ') ' + digits.substring(2, 6) + '-' + digits.substring(6);
        else return '(' + digits.substring(0, 2) + ') ' + digits.substring(2, 7) + '-' + digits.substring(7);
    }

    function numeroParaExtenso(valor) {
        const valorInteiro = Math.floor(valor);
        const unidades = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
        const dezenas = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
        const especiais = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
        const centenas = ['', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];

        if (valorInteiro === 0) return 'zero reais';
        if (valorInteiro < 10) return unidades[valorInteiro] + ' reais';
        if (valorInteiro < 20) return especiais[valorInteiro - 10] + ' reais';
        if (valorInteiro < 100) {
            const dez = Math.floor(valorInteiro / 10);
            const uni = valorInteiro % 10;
            return dezenas[dez] + (uni ? ' e ' + unidades[uni] : '') + ' reais';
        }
        if (valorInteiro < 1000) {
            const cen = Math.floor(valorInteiro / 100);
            const resto = valorInteiro % 100;
            let texto = (valorInteiro === 100) ? 'cem' : centenas[cen];
            if (resto > 0) {
                if (resto < 10) texto += ' e ' + unidades[resto];
                else if (resto < 20) texto += ' e ' + especiais[resto - 10];
                else {
                    const dez = Math.floor(resto / 10);
                    const uni = resto % 10;
                    texto += ' e ' + dezenas[dez] + (uni ? ' e ' + unidades[uni] : '');
                }
            }
            return texto + ' reais';
        }
        if (valorInteiro < 10000) {
            const mil = Math.floor(valorInteiro / 1000);
            const resto = valorInteiro % 1000;
            let texto = (mil === 1) ? 'mil' : unidades[mil] + ' mil';
            if (resto > 0) {
                texto += ' e ' + numeroParaExtenso(resto).replace(' reais', '');
            }
            return texto + ' reais';
        }
        return formatMoney(valor);
    }

    // ===== CABEÇALHO PADRÃO PDF =====
    function adicionarCabecalhoPDF(doc, config, margemEsquerda, margemDireita) {
        const larguraPagina = margemDireita - margemEsquerda;
        let y = 15;

        // --- LOGO (esquerda) ---
        const logoSize = 28;
        let xInfo = margemEsquerda;
        if (config.logoBase64) {
            try {
                doc.addImage(config.logoBase64, 'PNG', margemEsquerda, y, logoSize, logoSize);
                xInfo = margemEsquerda + logoSize + 5;
            } catch (e) { console.log('Erro ao adicionar logo:', e); }
        }

        // --- NOME DA EMPRESA (ao lado do logo) ---
        doc.setFontSize(13);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(30, 30, 30);
        doc.text(config.nomeEmpresa || 'EventProDJ', xInfo, y + 6);

        // --- CONTATOS (abaixo do nome, alinhados à esquerda junto ao logo) ---
        let yD = y + 12;
        doc.setFontSize(8.5);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(50, 50, 50);

        if (config.cnpj) {
            doc.text('CNPJ: ' + config.cnpj, xInfo, yD);
            yD += 4.5;
        }
        if (config.email) {
            doc.text('Email: ' + config.email, xInfo, yD);
            yD += 4.5;
        }
        if (config.telefone) {
            doc.text('Tel: ' + config.telefone, xInfo, yD);
            yD += 4.5;
        }
        if (config.whatsapp && config.whatsapp !== config.telefone) {
            doc.text('WhatsApp: ' + config.whatsapp, xInfo, yD);
            yD += 4.5;
        }

        // --- LINHA SEPARADORA FINA ---
        const yLinha = Math.max(y + logoSize + 4, yD + 3);
        doc.setDrawColor(200, 200, 200);
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, yLinha, margemDireita, yLinha);

        // --- REDES SOCIAIS (linha horizontal abaixo da linha) ---
        const redesSociais = [];
        if (config.instagram) redesSociais.push({
            simbolo: '\u24D8',  // ⓘ — usamos letra estilizada como placeholder visual
            prefixo: 'insta',
            iconeLabel: '\u25CF inst:',
            label: 'Instagram',
            handle: config.instagram.replace(/^@/, ''),
            url: config.instagram.startsWith('http') ? config.instagram : 'https://instagram.com/' + config.instagram.replace(/^@/, ''),
            cor: [225, 48, 108]
        });
        if (config.facebook) redesSociais.push({
            label: 'Facebook',
            handle: config.facebook.replace(/^@/, ''),
            url: config.facebook.startsWith('http') ? config.facebook : 'https://facebook.com/' + config.facebook.replace(/^@/, ''),
            cor: [24, 119, 242]
        });
        if (config.youtube) redesSociais.push({
            label: 'YouTube',
            handle: config.youtube.replace(/^@/, ''),
            url: config.youtube.startsWith('http') ? config.youtube : 'https://youtube.com/' + config.youtube.replace(/^@/, ''),
            cor: [255, 0, 0]
        });
        if (config.site) redesSociais.push({
            label: 'Site',
            handle: config.site.replace(/^https?:\/\/(www\.)?/, ''),
            url: config.site.startsWith('http') ? config.site : 'https://' + config.site,
            cor: [40, 120, 80]
        });

        // Rótulos curtos exibidos antes do handle (estilo da imagem)
        const rotulos = { 'Instagram': 'inst', 'Facebook': 'face', 'YouTube': 'yt', 'Site': 'www' };

        if (redesSociais.length > 0) {
            const yRedes = yLinha + 6;
            const r = 2.8; // raio do círculo ícone

            // Calcular largura total para centralizar o bloco de redes
            doc.setFontSize(8);
            doc.setFont('helvetica', 'normal');

            // Desenhar cada rede lado a lado com espaçamento fixo
            let xCursor = margemEsquerda;
            redesSociais.forEach((rede, i) => {
                const cx = xCursor + r;
                const cy = yRedes - r * 0.2;

                // --- Círculo preto de fundo ---
                doc.setFillColor(20, 20, 20);
                doc.setDrawColor(20, 20, 20);
                doc.circle(cx, cy, r, 'F');

                // --- Símbolo branco da rede dentro do círculo ---
                doc.setTextColor(255, 255, 255);
                doc.setFont('helvetica', 'bold');

                if (rede.label === 'Instagram') {
                    // Câmera: rounded square + círculo + ponto
                    doc.setFillColor(255, 255, 255);
                    doc.setDrawColor(255, 255, 255);
                    doc.setLineWidth(0.35);
                    doc.roundedRect(cx - r*0.6, cy - r*0.6, r*1.2, r*1.2, r*0.25, r*0.25, 'S');
                    doc.circle(cx, cy, r*0.35, 'S');
                    doc.setFillColor(255, 255, 255);
                    doc.circle(cx + r*0.42, cy - r*0.42, r*0.12, 'F');

                } else if (rede.label === 'Facebook') {
                    // "f" branco
                    doc.setFontSize(r * 3.8);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(255, 255, 255);
                    doc.text('f', cx + r*0.08, cy + r*0.42, { align: 'center' });

                } else if (rede.label === 'YouTube') {
                    // Triângulo play branco
                    doc.setFillColor(255, 255, 255);
                    const tx = cx + r*0.08;
                    const ty = cy;
                    doc.triangle(
                        tx - r*0.38, ty - r*0.44,
                        tx + r*0.52, ty,
                        tx - r*0.38, ty + r*0.44,
                        'F'
                    );

                } else {
                    // Site/www: globo com letra W
                    doc.setFontSize(r * 3.2);
                    doc.setFont('helvetica', 'bold');
                    doc.setTextColor(255, 255, 255);
                    doc.text('W', cx, cy + r*0.38, { align: 'center' });
                }

                // --- Handle ao lado do ícone ---
                doc.setFontSize(8);
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(30, 30, 30);
                const handleDisplay = rede.handle.startsWith('http') ? rede.handle.replace(/^https?:\/\/(www\.)?/, '') : rede.handle;
                const xHandle = cx + r + 1.5;
                doc.textWithLink(handleDisplay, xHandle, yRedes, { url: rede.url });
                const largHandle = doc.getTextWidth(handleDisplay);

                // Avançar cursor para próxima rede (ícone + handle + espaço)
                xCursor = xHandle + largHandle + 6;
            });

            doc.setTextColor(0, 0, 0);
            doc.setFillColor(0, 0, 0);
            doc.setDrawColor(0, 0, 0);

            // Linha separadora final (mais grossa)
            const yLinhaFinal = yRedes + 6;
            doc.setDrawColor(60, 60, 60);
            doc.setLineWidth(0.6);
            doc.line(margemEsquerda, yLinhaFinal, margemDireita, yLinhaFinal);
            doc.setDrawColor(0, 0, 0);
            return yLinhaFinal + 10;
        }

        // Se não houver redes, linha final simples
        doc.setDrawColor(60, 60, 60);
        doc.setLineWidth(0.6);
        doc.line(margemEsquerda, yLinha, margemDireita, yLinha);
        doc.setDrawColor(0, 0, 0);
        return yLinha + 10;
    }

    // ===== DADOS DO CLIENTE PDF =====
    function adicionarDadosClientePDF(doc, cliente, margemEsquerda, y) {
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('Cliente: ' + cliente.nome, margemEsquerda, y);
        y += 5;

        doc.setFont('helvetica', 'normal');
        if (cliente.cpf) {
            doc.text('CPF: ' + formatarCpfCnpjString(cliente.cpf), margemEsquerda, y);
            y += 5;
        }
        if (cliente.telefone) {
            doc.text('Telefone: ' + formatarTelefoneString(cliente.telefone), margemEsquerda, y);
            y += 5;
        }
        if (cliente.email) {
            doc.text('Email: ' + cliente.email, margemEsquerda, y);
            y += 5;
        }

        // Endereço
        if (cliente.endereco) {
            const linhas = doc.splitTextToSize(cliente.endereco, 160);
            linhas.forEach(linha => {
                doc.text(linha, margemEsquerda, y);
                y += 4;
            });
        }

        return y;
    }

    // ===== INFO EVENTO PDF =====
    function adicionarInfoEventoPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y) {
        y += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 6, 'F');
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('Informações do Evento', margemEsquerda + 2, y + 4);
        y += 10;

        doc.setFontSize(10);

        doc.setFont('helvetica', 'bold');
        doc.text('Tipo de evento: ', margemEsquerda, y);
        let w = doc.getTextWidth('Tipo de evento: ');
        doc.setFont('helvetica', 'normal');
        doc.text(evento ? (evento.tipo || 'A definir') : 'A definir', margemEsquerda + w, y);

        doc.setFont('helvetica', 'bold');
        doc.text('Data do evento: ', 105, y);
        w = doc.getTextWidth('Data do evento: ');
        doc.setFont('helvetica', 'normal');
        doc.text(evento ? formatDatePdf(evento.data_evento || evento.data) : 'A definir', 105 + w, y);
        y += 5;

        doc.setFont('helvetica', 'bold');
        doc.text('Horário: ', margemEsquerda, y);
        w = doc.getTextWidth('Horário: ');
        doc.setFont('helvetica', 'normal');
        const horaInicio = evento ? (evento.hora_inicio || evento.horaInicio || '') : '';
        const horaFim = evento ? (evento.hora_fim || evento.horaFim || '') : '';
        doc.text(horaInicio && horaFim ? horaInicio + ' - ' + horaFim : 'A definir', margemEsquerda + w, y);

        doc.setFont('helvetica', 'bold');
        doc.text('Duração do Serviço: ', 105, y);
        w = doc.getTextWidth('Duração do Serviço: ');
        doc.setFont('helvetica', 'normal');
        doc.text(evento ? (evento.duracao || '5h') : '5h', 105 + w, y);
        y += 5;

        doc.setFont('helvetica', 'bold');
        doc.text('Local do evento: ', margemEsquerda, y);
        w = doc.getTextWidth('Local do evento: ');
        doc.setFont('helvetica', 'normal');

        let localEvento = 'A definir';
        if (evento) {
            localEvento = evento.complemento || evento.local || 'A definir';
        }
        const localLines = doc.splitTextToSize(localEvento, 140);
        doc.text(localLines, margemEsquerda + w, y);
        y += Math.max(5, localLines.length * 4);

        return y;
    }

    // ===== SERVIÇOS PDF =====
    function adicionarServicosPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y) {
        y += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 6, 'F');
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('Serviços', margemEsquerda + 2, y + 4);
        y += 10;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('Descrição', margemEsquerda, y);
        doc.text('Valor', margemDireita, y, { align: 'right' });
        y += 4;
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);
        y += 5;

        let totalServicos = 0;
        doc.setFont('helvetica', 'normal');

        // Usar array de serviços do evento (PHP retorna servicos[])
        if (evento && evento.servicos && evento.servicos.length > 0) {
            evento.servicos.forEach(s => {
                const nome = s.servico_nome || s.nome || s.descricao || 'Serviço';
                const valor = parseFloat(s.valor_total || s.valor_unitario || s.valor || 0);
                totalServicos += valor;

                doc.setFontSize(10);
                doc.text(nome, margemEsquerda, y);
                doc.setFont('helvetica', 'bold');
                doc.text(formatMoney(valor), margemDireita, y, { align: 'right' });
                doc.setFont('helvetica', 'normal');
                y += 6;
            });
            y += 2;
        }

        // Taxas
        if (evento && evento.taxas && evento.taxas.length > 0) {
            doc.setLineWidth(0.2);
            doc.line(margemEsquerda, y, margemDireita, y);
            y += 6;

            doc.setFontSize(11);
            doc.setFont('helvetica', 'bold');
            doc.text('Outras Taxas', margemEsquerda, y);
            y += 6;

            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');

            evento.taxas.forEach(t => {
                const desc = t.descricao || t.nome || 'Taxa';
                const valor = parseFloat(t.valor || 0);
                totalServicos += valor;

                doc.text(desc, margemEsquerda, y);
                doc.setFont('helvetica', 'bold');
                doc.text(formatMoney(valor), margemDireita, y, { align: 'right' });
                doc.setFont('helvetica', 'normal');
                y += 6;
            });
            y += 2;
        }

        return { y, totalServicos };
    }

    // ===== ASSINATURA PDF =====
    function adicionarAssinaturaPDF(doc, config, margemEsquerda, margemDireita, y) {
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda + 50, y, margemDireita - 50, y);

        if (config.assinaturaBase64) {
            try {
                doc.addImage(config.assinaturaBase64, 'PNG', 75, y - 10, 60, 10);
            } catch (e) { console.log('Erro ao adicionar assinatura:', e); }
        }

        y += 4;
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        doc.text('Assinatura do responsável da empresa', 105, y, { align: 'center' });

        y += 4;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(config.nomeEmpresa || 'EventProDJ', 105, y, { align: 'center' });

        y += 5;
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        const dataExtenso = new Date().toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' });
        doc.text((config.cidade || 'Rio de Janeiro') + ', ' + dataExtenso, 105, y, { align: 'center' });

        return y;
    }

    // ===== MODAL COMPARTILHAR =====
    function mostrarModalCompartilhar(blob, nomeArquivo, mensagem) {
        pdfBlobGlobal     = blob;
        pdfNomeGlobal     = nomeArquivo;
        pdfMensagemGlobal = mensagem || 'Documento gerado pelo EventProDJ';
        document.getElementById('modalCompartilhar').classList.add('active');
    }

    function fecharModalCompartilhar() {
        document.getElementById('modalCompartilhar').classList.remove('active');
        pdfBlobGlobal     = null;
        pdfNomeGlobal     = '';
        pdfMensagemGlobal = '';
    }

    async function compartilharPDF() {
        if (!pdfBlobGlobal || !pdfNomeGlobal) {
            alert('Nenhum documento foi gerado ainda.');
            return;
        }

        try {
            if (navigator.share && navigator.canShare) {
                const file = new File([pdfBlobGlobal], pdfNomeGlobal, { type: 'application/pdf' });
                if (navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: 'Compartilhar Documento',
                        text: pdfMensagemGlobal
                    });
                    return;
                }
            }
            alert('Compartilhamento não disponível. Baixando documento...');
            baixarPDF();
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Erro ao compartilhar:', error);
                baixarPDF();
            }
        }
    }

    function baixarPDF() {
        if (pdfBlobGlobal && pdfNomeGlobal) {
            const url = URL.createObjectURL(pdfBlobGlobal);
            const a = document.createElement('a');
            a.href = url;
            a.download = pdfNomeGlobal;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            fecharModalCompartilhar();
        }
    }

    // ===== 1. ORÇAMENTO =====
    async function gerarOrcamentoProfissional(cliente, evento, config, nomeArquivo) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const margemEsquerda = 15;
        const margemDireita = 195;
        const larguraPagina = margemDireita - margemEsquerda;

        let y = adicionarCabecalhoPDF(doc, config, margemEsquerda, margemDireita);

        // Título
        doc.setFillColor(220, 220, 220);
        doc.rect(margemEsquerda, y, larguraPagina, 8, 'F');
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('Orçamento', margemEsquerda + 2, y + 5.5);
        y += 15;

        // Dados do cliente
        y = adicionarDadosClientePDF(doc, cliente, margemEsquerda, y);

        // Info evento
        y = adicionarInfoEventoPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y);

        // Serviços
        const resultado = adicionarServicosPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y);
        y = resultado.y;
        let totalServicos = resultado.totalServicos;

        // Total
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);
        y += 6;

        const valorTotal = parseFloat(cliente.valor_total || 0) || totalServicos;
        const desconto = totalServicos - valorTotal;

        if (desconto > 0) {
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text('Subtotal', 115, y);
            doc.text(formatMoney(totalServicos), margemDireita, y, { align: 'right' });
            y += 5;
            doc.text('Desconto', 115, y);
            doc.text('- ' + formatMoney(desconto), margemDireita, y, { align: 'right' });
            y += 5;
            doc.setLineWidth(0.2);
            doc.line(margemEsquerda, y, margemDireita, y);
            y += 6;
        }

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('VALOR TOTAL', 115, y);
        doc.text(formatMoney(valorTotal), margemDireita, y, { align: 'right' });

        // Pagamento
        y += 8;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 6, 'F');
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('Pagamento', margemEsquerda + 2, y + 4);
        y += 10;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text('Formas de pagamento: Pix, Transferência, Dinheiro, Cartão de Crédito/Débito', margemEsquerda, y);

        // Assinatura
        y += 35;
        y = adicionarAssinaturaPDF(doc, config, margemEsquerda, margemDireita, y);

        // Rodapé
        doc.setFontSize(9);
        doc.setTextColor(150);
        doc.text('Página 1/1', 105, 280, { align: 'center' });
        doc.setFontSize(10);
        doc.setTextColor(0);
        doc.text('Esse orçamento tem uma validade de 30 dias a partir da data que foi gerado.', 105, 287, { align: 'center' });

        const pdfBlob = doc.output('blob');
        mostrarModalCompartilhar(pdfBlob, nomeArquivo, 'Segue o Orçamento Solicitado.');
    }

    // ===== 2. CONTRATO =====
    async function gerarContratoProfissional(cliente, evento, config, nomeArquivo) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const margemEsquerda = 15;
        const margemDireita = 195;
        const larguraPagina = margemDireita - margemEsquerda;

        let y = adicionarCabecalhoPDF(doc, config, margemEsquerda, margemDireita);

        // Título
        doc.setFillColor(220, 220, 220);
        doc.rect(margemEsquerda, y, larguraPagina, 8, 'F');
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('Contrato', margemEsquerda + 2, y + 5.5);
        y += 15;

        y = adicionarDadosClientePDF(doc, cliente, margemEsquerda, y);
        y = adicionarInfoEventoPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y);

        const resultado = adicionarServicosPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y);
        y = resultado.y;
        let totalServicos = resultado.totalServicos;

        // Total
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);
        y += 6;

        const valorTotal = parseFloat(cliente.valor_total || 0) || totalServicos;
        const desconto = totalServicos - valorTotal;

        if (desconto > 0) {
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text('Subtotal', 115, y);
            doc.text(formatMoney(totalServicos), margemDireita, y, { align: 'right' });
            y += 5;
            doc.text('Desconto', 115, y);
            doc.text('- ' + formatMoney(desconto), margemDireita, y, { align: 'right' });
            y += 5;
            doc.setLineWidth(0.2);
            doc.line(margemEsquerda, y, margemDireita, y);
            y += 6;
        }

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('VALOR TOTAL', 115, y);
        doc.text(formatMoney(valorTotal), margemDireita, y, { align: 'right' });

        // Tabela de pagamentos
        y += 8;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 7, 'F');

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('PAGAMENTOS', margemEsquerda + 2, y + 5);
        doc.text('Vencimento', 115, y + 5);
        doc.text('Valor', margemDireita, y + 5, { align: 'right' });

        y += 7;
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);

        const sinalValor = parseFloat(cliente.valor_sinal || 0);
        const sinalData = cliente.data_sinal ? formatDatePdf(cliente.data_sinal) : formatDatePdf(new Date().toISOString().split('T')[0]);
        const saldoAPagar = valorTotal - sinalValor;
        const vencimento = cliente.data_vencimento ? formatDatePdf(cliente.data_vencimento) : (evento ? formatDatePdf(evento.data_evento || evento.data) : formatDatePdf(new Date().toISOString().split('T')[0]));

        y += 5;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text('Entrada', margemEsquerda + 2, y);
        doc.text(sinalData, 115, y);
        doc.setFont('helvetica', 'bold');
        doc.text(formatMoney(sinalValor), margemDireita, y, { align: 'right' });
        doc.setFont('helvetica', 'normal');

        y += 5;
        doc.text('A receber', margemEsquerda + 2, y);
        doc.text(vencimento, 115, y);
        doc.setFont('helvetica', 'bold');
        doc.text(formatMoney(saldoAPagar), margemDireita, y, { align: 'right' });
        doc.setFont('helvetica', 'normal');

        y += 6;
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);

        // Página 2: Cláusulas
        doc.addPage();
        y = 20;

        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text('Cláusulas Contratuais', 105, y, { align: 'center' });
        y += 15;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');

        const clausulas = config.clausulasContratuais || 'Nenhuma cláusula configurada. Configure em: Configurações > Documentos > Contratos';
        const clausulasLines = doc.splitTextToSize(clausulas, larguraPagina);

        clausulasLines.forEach(linha => {
            if (y > 270) {
                doc.addPage();
                y = 20;
            }
            doc.text(linha, margemEsquerda, y);
            y += 4;
        });

        // Assinaturas
        if (y > 200) {
            doc.addPage();
            y = 20;
        } else {
            y += 20;
        }

        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('Assinaturas', 105, y, { align: 'center' });
        y += 15;

        const yInicial = y;

        // Empresa (esquerda)
        doc.setFontSize(10);
        doc.setLineWidth(0.3);
        doc.line(15, yInicial, 90, yInicial);

        if (config.assinaturaBase64) {
            try { doc.addImage(config.assinaturaBase64, 'PNG', 22, yInicial - 10, 60, 10); } catch (e) {}
        }

        doc.setFont('helvetica', 'normal');
        doc.text('Assinatura do responsável da empresa', 52, yInicial + 4, { align: 'center' });
        doc.setFont('helvetica', 'bold');
        doc.text(config.nomeEmpresa || 'EventProDJ', 52, yInicial + 8, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        const dataExtenso = new Date().toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' });
        doc.text((config.cidade || 'Rio de Janeiro') + ', ' + dataExtenso, 52, yInicial + 12, { align: 'center' });

        // Cliente (direita)
        doc.setFontSize(10);
        doc.setLineWidth(0.3);
        doc.line(120, yInicial, 195, yInicial);
        doc.setFont('helvetica', 'bold');
        doc.text(cliente.nome, 157, yInicial + 4, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        if (cliente.cpf) {
            doc.text('CPF: ' + formatarCpfCnpjString(cliente.cpf), 157, yInicial + 8, { align: 'center' });
        }

        // Rodapé com paginação
        const totalPages = doc.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
            doc.setPage(i);
            doc.setFontSize(9);
            doc.setTextColor(150);
            doc.text('Página ' + i + '/' + totalPages, 105, 285, { align: 'center' });
            doc.setTextColor(0);
        }

        const pdfBlob = doc.output('blob');
        mostrarModalCompartilhar(pdfBlob, nomeArquivo, 'Segue o contrato assinado com todos os serviços contratados e as clausulas contratuais da prestação do serviço.');
    }

    // ===== 3. RECIBO =====
    async function gerarReciboProfissional(cliente, evento, config, nomeArquivo, dadosRecibo) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const margemEsquerda = 15;
        const margemDireita = 195;
        const larguraPagina = 180;
        const dataAtual = new Date().toLocaleDateString('pt-BR');

        // Cabeçalho padrão igual aos outros documentos
        let y = adicionarCabecalhoPDF(doc, config, margemEsquerda, margemDireita);

        // Título
        doc.setFillColor(220, 220, 220);
        doc.rect(margemEsquerda, y, larguraPagina, 8, 'F');
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('Recibo', margemEsquerda + 2, y + 5.5);
        y += 15;

        // Declaração
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');

        const valorRecebido = dadosRecibo ? dadosRecibo.valor : parseFloat(cliente.valor_sinal || 0);
        const dataRecebimento = dadosRecibo ? formatDatePdf(dadosRecibo.data) : (cliente.data_sinal ? formatDatePdf(cliente.data_sinal) : dataAtual);
        const referentePagamento = dadosRecibo ? dadosRecibo.referente : (evento ? 'Contratação do evento: ' + (evento.tipo || '') : 'Contratação de serviços');

        const enderecoCompleto = cliente.endereco || 'não informado';

        const declaracao = 'Declaro que recebi de ' + cliente.nome + ', inscrito no CPF sob o n. ' + (formatarCpfCnpjString(cliente.cpf) || '000.000.000-00') + ', com endereço em ' + enderecoCompleto + ', o valor de ' + formatMoney(valorRecebido) + ' (' + numeroParaExtenso(valorRecebido) + ') em ' + dataRecebimento + ', referente a ' + referentePagamento + '.';

        const declaracaoLines = doc.splitTextToSize(declaracao, larguraPagina);
        declaracaoLines.forEach(linha => {
            doc.text(linha, margemEsquerda, y);
            y += 5;
        });

        // Serviços
        y += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 6, 'F');
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text('Serviços', margemEsquerda + 2, y + 4);
        y += 10;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        if (evento && evento.servicos && evento.servicos.length > 0) {
            evento.servicos.forEach(s => {
                doc.text('• ' + (s.servico_nome || s.nome || s.descricao || 'Serviço'), margemEsquerda, y);
                y += 5;
            });
        } else {
            doc.text('• Serviços de DJ e Sonorização', margemEsquerda, y);
            y += 5;
        }

        // Evento
        y += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 6, 'F');
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('Evento', margemEsquerda + 2, y + 4);
        y += 10;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        if (evento && evento.tipo) {
            doc.setFont('helvetica', 'bold');
            doc.text('Tipo de evento:', margemEsquerda, y);
            const wTipo = doc.getTextWidth('Tipo de evento:');
            doc.setFont('helvetica', 'normal');
            doc.text(evento.tipo, margemEsquerda + wTipo + 2, y);
            y += 5;
        }
        if (evento && (evento.data_evento || evento.data)) {
            doc.setFont('helvetica', 'bold');
            doc.text('Data do evento:', margemEsquerda, y);
            const wData = doc.getTextWidth('Data do evento:');
            doc.setFont('helvetica', 'normal');
            doc.text(formatDatePdf(evento.data_evento || evento.data), margemEsquerda + wData + 2, y);
            y += 5;
        }

        // Pagamento
        y += 5;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 6, 'F');
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text('Pagamento', margemEsquerda + 2, y + 4);
        y += 10;

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('Meios de pagamento', margemEsquerda, y);
        y += 5;
        doc.setFont('helvetica', 'normal');

        const formaPagamento = cliente.forma_pagamento_sinal || 'pix';
        const formasLabels = {
            'pix': 'Pix', 'dinheiro': 'Dinheiro', 'debito': 'Cartão de Débito',
            'credito': 'Cartão de Crédito', 'transferencia': 'Transferência Bancária', 'boleto': 'Boleto'
        };
        doc.text(formasLabels[formaPagamento] || 'Pix', margemEsquerda, y);

        // Assinatura
        y += 35;
        y = adicionarAssinaturaPDF(doc, config, margemEsquerda, margemDireita, y);

        // Rodapé
        doc.setFontSize(10);
        doc.setTextColor(150);
        doc.text('Página 1/1', 105, 285, { align: 'center' });
        doc.setTextColor(0);

        const pdfBlob = doc.output('blob');
        mostrarModalCompartilhar(pdfBlob, nomeArquivo, 'Segue o recibo referente ao recebimento do pagamento de sinal para, reserva e agendamento da data e os serviços especificados abaixo.');
    }

    // ===== 4. RESUMO FINANCEIRO =====
    async function gerarResumoFinanceiroProfissional(cliente, evento, config, nomeArquivo) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const margemEsquerda = 15;
        const margemDireita = 195;
        const larguraPagina = margemDireita - margemEsquerda;

        let y = adicionarCabecalhoPDF(doc, config, margemEsquerda, margemDireita);

        // Título
        doc.setFillColor(220, 220, 220);
        doc.rect(margemEsquerda, y, larguraPagina, 8, 'F');
        doc.setFontSize(16);
        doc.setFont('helvetica', 'bold');
        doc.text('Resumo Financeiro', margemEsquerda + 2, y + 5.5);
        y += 15;

        y = adicionarDadosClientePDF(doc, cliente, margemEsquerda, y);
        y = adicionarInfoEventoPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y);

        const resultado = adicionarServicosPDF(doc, evento, margemEsquerda, margemDireita, larguraPagina, y);
        y = resultado.y;
        let totalServicos = resultado.totalServicos;

        // Total
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);
        y += 6;

        const valorTotal = parseFloat(cliente.valor_total || 0) || totalServicos;
        const desconto = totalServicos - valorTotal;

        if (desconto > 0) {
            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text('Subtotal', 115, y);
            doc.text(formatMoney(totalServicos), margemDireita, y, { align: 'right' });
            y += 5;
            doc.text('Desconto', 115, y);
            doc.text('- ' + formatMoney(desconto), margemDireita, y, { align: 'right' });
            y += 5;
            doc.setLineWidth(0.2);
            doc.line(margemEsquerda, y, margemDireita, y);
            y += 6;
        }

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('VALOR TOTAL', 115, y);
        doc.text(formatMoney(valorTotal), margemDireita, y, { align: 'right' });

        // Tabela de pagamentos
        y += 8;
        doc.setFillColor(240, 240, 240);
        doc.rect(margemEsquerda, y, larguraPagina, 7, 'F');

        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text('PAGAMENTOS', margemEsquerda + 2, y + 5);
        doc.text('Vencimento', 115, y + 5);
        doc.text('Valor', margemDireita, y + 5, { align: 'right' });

        y += 7;
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);

        const sinalValor = parseFloat(cliente.valor_sinal || 0);
        const sinalData = cliente.data_sinal ? formatDatePdf(cliente.data_sinal) : formatDatePdf(new Date().toISOString().split('T')[0]);
        let totalPago = sinalValor;

        y += 5;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.text('Entrada', margemEsquerda + 2, y);
        doc.text(sinalData, 115, y);
        doc.setFont('helvetica', 'bold');
        doc.text(formatMoney(sinalValor), margemDireita, y, { align: 'right' });
        doc.setFont('helvetica', 'normal');
        y += 5;

        // Saldo a receber
        const saldoAPagar = valorTotal - totalPago;
        const vencimento = cliente.data_vencimento ? formatDatePdf(cliente.data_vencimento) : (evento ? formatDatePdf(evento.data_evento || evento.data) : formatDatePdf(new Date().toISOString().split('T')[0]));

        doc.text('A receber', margemEsquerda + 2, y);
        doc.text(vencimento, 115, y);
        doc.setFont('helvetica', 'bold');
        doc.text(formatMoney(saldoAPagar), margemDireita, y, { align: 'right' });
        doc.setFont('helvetica', 'normal');

        y += 6;
        doc.setLineWidth(0.3);
        doc.line(margemEsquerda, y, margemDireita, y);

        // Assinatura
        y += 20;
        doc.setFontSize(11);
        doc.setFont('helvetica', 'bold');
        doc.text('Assinatura', 105, y, { align: 'center' });
        y += 15;

        doc.setFontSize(10);
        doc.setLineWidth(0.3);
        doc.line(55, y, 155, y);

        if (config.assinaturaBase64) {
            try { doc.addImage(config.assinaturaBase64, 'PNG', 75, y - 10, 60, 10); } catch (e) {}
        }

        doc.setFont('helvetica', 'normal');
        doc.text('Assinatura do responsável da empresa', 105, y + 4, { align: 'center' });
        doc.setFont('helvetica', 'bold');
        doc.text(config.nomeEmpresa || 'EventProDJ', 105, y + 8, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        const dataExtenso = new Date().toLocaleDateString('pt-BR', { day: 'numeric', month: 'long', year: 'numeric' });
        doc.text((config.cidade || 'Rio de Janeiro') + ', ' + dataExtenso, 105, y + 12, { align: 'center' });

        // Rodapé
        doc.setFontSize(9);
        doc.setTextColor(150);
        doc.text('Página 1/1', 105, 285, { align: 'center' });
        doc.setTextColor(0);

        const pdfBlob = doc.output('blob');
        mostrarModalCompartilhar(pdfBlob, nomeArquivo, 'Segue o resumo financeiro com todos os valores pagos e a pagar.');
    }

    // ===== MODAL ESCOLHER RECIBO =====
    function abrirModalEscolherRecibo(cliente, evento, config) {
        document.getElementById('reciboClienteNome').textContent = cliente.nome;
        document.getElementById('reciboClienteId').value = cliente.id;

        const listaPagamentos = document.getElementById('listaPagamentosRecibo');
        const valorTotal = parseFloat(cliente.valor_total || 0);
        const sinalValor = parseFloat(cliente.valor_sinal || 0);
        let totalPago = sinalValor;
        let html = '';

        // Sinal / Entrada
        if (sinalValor > 0) {
            html += `
                <button class="btn btn-primary" onclick="gerarReciboSelecionado('sinal')" style="padding: 16px; font-size: 15px; text-align: left; width: 100%;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600;">Entrada / Sinal</div>
                            <div style="font-size: 13px; opacity: 0.8; margin-top: 4px;">
                                Pago em: ${cliente.data_sinal ? formatarData(cliente.data_sinal) : 'Data não informada'}
                            </div>
                        </div>
                        <div style="font-size: 18px; font-weight: 700;">
                            ${formatarMoeda(sinalValor)}
                        </div>
                    </div>
                </button>
            `;
        }

        // Saldo devedor
        const saldoDevedor = valorTotal - totalPago;
        if (saldoDevedor > 0) {
            html += `
                <button class="btn btn-primary" onclick="gerarReciboSelecionado('saldo')" style="padding: 16px; font-size: 15px; text-align: left; width: 100%;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600;">Pagamento Final / Saldo</div>
                            <div style="font-size: 13px; opacity: 0.8; margin-top: 4px;">A receber</div>
                        </div>
                        <div style="font-size: 18px; font-weight: 700;">
                            ${formatarMoeda(saldoDevedor)}
                        </div>
                    </div>
                </button>
            `;
        }

        // Valor total
        html += `
            <button class="btn btn-secondary" onclick="gerarReciboSelecionado('total')" style="padding: 16px; font-size: 15px; text-align: left; width: 100%;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600;">Valor Total do Serviço</div>
                        <div style="font-size: 13px; opacity: 0.7; margin-top: 4px;">Recibo completo</div>
                    </div>
                    <div style="font-size: 18px; font-weight: 700;">
                        ${formatarMoeda(valorTotal)}
                    </div>
                </div>
            </button>
        `;

        listaPagamentos.innerHTML = html;
        fecharModalDocumentos();
        document.getElementById('modalEscolherRecibo').classList.add('active');
    }

    function fecharModalEscolherRecibo() {
        document.getElementById('modalEscolherRecibo').classList.remove('active');
    }

    async function gerarReciboSelecionado(tipo) {
        const clienteId = parseInt(document.getElementById('reciboClienteId').value);
        const cliente = todosClientes.find(c => c.id === clienteId);
        if (!cliente) return;

        const config = configEmpresa;
        let evento = todosEventos.find(e => e.cliente_id == clienteId);

        if (evento) {
            try {
                const resp = await fetch(`../api/eventos.php?action=get&id=${evento.id}`);
                const evtData = await resp.json();
                if (evtData.success) evento = evtData.evento;
            } catch (e) {}
        }

        const valorTotal = parseFloat(cliente.valor_total || 0);
        const sinalValor = parseFloat(cliente.valor_sinal || 0);
        let descricao = '', valor = 0, data = '', referente = '';

        if (tipo === 'sinal') {
            descricao = 'Entrada / Sinal';
            valor = sinalValor;
            data = cliente.data_sinal || new Date().toISOString().split('T')[0];
            referente = evento ? 'Contratação do evento: ' + (evento.tipo || '') : 'Contratação de serviços';
        } else if (tipo === 'saldo') {
            descricao = 'Pagamento Final / Saldo Devedor';
            valor = valorTotal - sinalValor;
            data = new Date().toISOString().split('T')[0];
            referente = evento ? 'Quitação do evento: ' + (evento.tipo || '') : 'Quitação de serviços';
        } else if (tipo === 'total') {
            descricao = 'Valor Total do Serviço';
            valor = valorTotal;
            data = new Date().toISOString().split('T')[0];
            referente = evento ? 'Pagamento completo do evento: ' + (evento.tipo || '') : 'Pagamento completo de serviços';
        }

        const nomeArquivo = `Recibo_${cliente.nome.replace(/\s/g, '_')}_${descricao.replace(/\s/g, '_')}.pdf`;

        try {
            await gerarReciboProfissional(cliente, evento, config, nomeArquivo, { descricao, valor, data, referente });
            fecharModalEscolherRecibo();
        } catch (error) {
            console.error('Erro ao gerar recibo:', error);
            alert('Erro ao gerar recibo. Verifique os dados e tente novamente.');
        }
    }

    // Fechar modal ao clicar fora
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });

    // Carregar ao iniciar
    document.addEventListener('DOMContentLoaded', async function() {
        await carregarConfigEmpresa();
        await carregarDados();

        // Se URL tem #novo, abrir modal de novo cliente
        if (window.location.hash === '#novo') {
            setTimeout(() => {
                abrirModalNovoCliente();
                history.replaceState(null, null, ' ');
            }, 300);
        }

        // Verificar se veio de outra página com cliente_id na URL
        const urlParams = new URLSearchParams(window.location.search);
        const clienteIdParam = urlParams.get('cliente_id');
        if (clienteIdParam) {
            // Limpar busca para garantir que o cliente esteja visível
            currentClientSearch = '';
            const searchInput = document.querySelector('input[type="text"]');
            if (searchInput) searchInput.value = '';
            renderizarClientes();

            setTimeout(() => {
                const clienteCard = document.querySelector(`[data-cliente-id="${clienteIdParam}"]`);
                if (clienteCard) {
                    clienteCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    clienteCard.style.transition = 'box-shadow 0.3s, transform 0.3s';
                    clienteCard.style.boxShadow = '0 0 0 3px var(--primary), 0 8px 32px rgba(108, 99, 255, 0.3)';
                    clienteCard.style.transform = 'scale(1.02)';
                    // Remover destaque após 3 segundos
                    setTimeout(() => {
                        clienteCard.style.boxShadow = '';
                        clienteCard.style.transform = '';
                    }, 3000);
                }
                // Limpar URL
                history.replaceState(null, null, window.location.pathname);
            }, 300);
        }
    });
</script>

<!-- Modal Documentos -->
<div class="modal" id="modalDocumentos">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Gerar Documento</h3>
            <button class="close-btn" onclick="fecharModalDocumentos()">×</button>
        </div>
        <input type="hidden" id="documentosClienteId">
        <div style="padding: 24px;">
            <p style="color: var(--text-secondary); margin-bottom: 16px;">
                Cliente: <strong id="documentosClienteNome"></strong>
            </p>
            <div style="display: grid; gap: 12px;">
                <button onclick="gerarDocumento('orcamento')" class="btn btn-primary" style="padding: 16px; font-size: 15px; text-align: left;">
                    <div style="font-weight: 600;">Orçamento</div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">Gerar orçamento detalhado com serviços e valores</div>
                </button>
                <button onclick="gerarDocumento('contrato')" class="btn btn-primary" style="padding: 16px; font-size: 15px; text-align: left;">
                    <div style="font-weight: 600;">Contrato</div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">Contrato com cláusulas e dados de pagamento</div>
                </button>
                <button onclick="gerarDocumento('recibo')" class="btn btn-primary" style="padding: 16px; font-size: 15px; text-align: left;">
                    <div style="font-weight: 600;">Recibo</div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">Recibo de pagamento (sinal, parcela ou total)</div>
                </button>
                <button onclick="gerarDocumento('resumo')" class="btn btn-primary" style="padding: 16px; font-size: 15px; text-align: left;">
                    <div style="font-weight: 600;">Resumo Financeiro</div>
                    <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">Visão geral de valores, pagamentos e saldo</div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Escolher Recibo -->
<div class="modal" id="modalEscolherRecibo">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Escolher Pagamento para Recibo</h3>
            <button class="close-btn" onclick="fecharModalEscolherRecibo()">×</button>
        </div>
        <input type="hidden" id="reciboClienteId">
        <div style="padding: 24px;">
            <p style="color: var(--text-secondary); margin-bottom: 16px;">
                Cliente: <strong id="reciboClienteNome"></strong>
            </p>
            <div id="listaPagamentosRecibo" style="display: grid; gap: 12px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal Compartilhar/Baixar PDF -->
<div class="modal" id="modalCompartilhar">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">Documento Gerado!</h3>
            <button class="close-btn" onclick="fecharModalCompartilhar()">×</button>
        </div>
        <div style="padding: 24px; text-align: center;">
            <div style="font-size: 64px; margin-bottom: 16px;">📄</div>
            <p style="color: var(--text-secondary); margin-bottom: 24px;">
                Seu documento foi gerado com sucesso!
            </p>
            <div style="display: grid; gap: 12px;">
                <button onclick="baixarPDF()" class="btn btn-primary" style="padding: 14px; font-size: 15px;">
                    Baixar PDF
                </button>
                <button onclick="compartilharPDF()" class="btn btn-secondary" style="padding: 14px; font-size: 15px;">
                    Compartilhar
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
