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
    <title>Novo Orçamento Completo - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <style>
        .section-box {
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .section-cliente { background: rgba(255, 107, 53, 0.1); border-left: 4px solid var(--primary); }
        .section-evento { background: rgba(0, 78, 137, 0.1); border-left: 4px solid var(--secondary); }
        .section-servicos { background: rgba(6, 214, 160, 0.1); border-left: 4px solid var(--success); }
        .section-info { background: rgba(255, 210, 63, 0.1); border-left: 4px solid var(--accent); }
        
        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .section-cliente .section-title { color: var(--primary); }
        .section-evento .section-title { color: var(--secondary); }
        .section-servicos .section-title { color: var(--success); }
        .section-info .section-title { color: var(--accent); }
        
        .horario-box {
            background: rgba(0, 78, 137, 0.1);
            border: 1px solid var(--secondary);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }
        
        .horario-label {
            font-size: 13px;
            color: var(--secondary);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .endereco-sugestao {
            background: rgba(6, 214, 160, 0.1);
            border: 1px solid var(--success);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
        }
        
        .servico-checkbox {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .servico-checkbox:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--success);
        }
        
        .servico-checkbox input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .servico-selecionado {
            background: rgba(6, 214, 160, 0.1);
            border: 1px solid var(--success);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .taxa-item {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid var(--primary);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }
        
        .resumo-financeiro {
            background: rgba(255, 107, 53, 0.1);
            border: 2px solid var(--primary);
            border-radius: 12px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .resumo-linha {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .resumo-total {
            border-top: 2px solid var(--primary);
            padding-top: 12px;
            margin-top: 8px;
            font-size: 18px;
            font-weight: 700;
        }
        
        .cep-feedback {
            text-align: center;
            padding: 8px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 13px;
            display: none;
        }
        
        .cep-loading {
            background: rgba(255, 210, 63, 0.1);
            color: var(--accent);
        }
        
        .cep-success {
            background: rgba(6, 214, 160, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .cep-error {
            background: rgba(239, 71, 111, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        /* Mobile - Criar Orçamento */
        @media (max-width: 768px) {
            .section-box { padding: 16px !important; }
            .form-row { grid-template-columns: 1fr !important; }
            .horario-box .form-row { grid-template-columns: 1fr 1fr !important; }
            .resumo-financeiro { padding: 12px !important; }
            .btn-submit { font-size: 14px !important; padding: 14px !important; }
        }
        @media (max-width: 480px) {
            .section-box { padding: 12px !important; }
            .horario-box .form-row { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>

<?php include '../includes/menu.php'; ?>

<div class="container">
    <div class="page-header">
        <h1 class="page-title" id="pageTitulo">💼 Novo Orçamento Completo</h1>
        <p class="page-subtitle" id="pageSubtitulo">Crie um orçamento detalhado com serviços e valores</p>
    </div>

    <form id="formOrcamento" onsubmit="salvarOrcamento(event)">
        
        <!-- SEÇÃO 1: DADOS DO CLIENTE -->
        <div class="section-box section-cliente">
            <h3 class="section-title">👤 Dados do Cliente</h3>
            <div class="form-group">
                <label class="form-label">Cliente *</label>
                <input type="text" class="form-input" id="orcCliente" required placeholder="Nome do cliente" readonly style="background: rgba(255, 255, 255, 0.05);">
                <input type="hidden" id="orcClienteId">
            </div>
        </div>

        <!-- SEÇÃO 2: DADOS DO EVENTO -->
        <div class="section-box section-evento">
            <h3 class="section-title">🎉 Dados do Evento</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo de Evento *</label>
                    <select class="form-select" id="orcTipoEvento" required>
                        <option value="">Selecione o tipo...</option>
                        <option value="Casamento">Casamento</option>
                        <option value="Aniversário">Aniversário</option>
                        <option value="Aniversário 15 Anos">Aniversário 15 Anos</option>
                        <option value="Formatura">Formatura</option>
                        <option value="Corporativo">Evento Corporativo</option>
                        <option value="Confraternização">Confraternização</option>
                        <option value="Batizado">Batizado</option>
                        <option value="Chá de Bebê">Chá de Bebê</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Data Prevista *</label>
                    <input type="date" class="form-input" id="orcData" required>
                </div>
            </div>

            <!-- Horários com Duração -->
            <div class="horario-box">
                <div class="horario-label">⏰ Horário do Evento</div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Hora de Início *</label>
                        <input type="time" class="form-input" id="orcHoraInicio" oninput="calcularDuracao()" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora de Término *</label>
                        <input type="time" class="form-input" id="orcHoraFim" oninput="calcularDuracao()" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duração do Serviço</label>
                        <input type="text" class="form-input" id="orcDuracao" readonly style="background: rgba(0, 78, 137, 0.2); font-weight: 600; color: var(--secondary);" placeholder="Automático">
                    </div>
                </div>
            </div>
            
            <!-- Opção de usar endereço do cliente -->
            <div id="opcoesEnderecoCliente" class="endereco-sugestao" style="display: none;">
                <div style="font-size: 14px; color: var(--success); font-weight: 600; margin-bottom: 8px;">
                    📍 Endereço do Cliente Cadastrado
                </div>
                <div id="enderecoClienteDisplay" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                    <!-- Endereço será inserido aqui -->
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="usarEnderecoCliente()" class="btn btn-secondary" style="flex: 1;">
                        ✅ Usar Este Endereço
                    </button>
                    <button type="button" onclick="ocultarSugestaoEndereco()" class="btn" style="flex: 1; background: transparent; border: 1px solid var(--border);">
                        📝 Digitar Outro Endereço
                    </button>
                </div>
            </div>

            <!-- Endereço do Evento -->
            <div class="form-group">
                <label class="form-label">Endereço do Evento</label>
                <div class="form-row" style="margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">CEP 📮</label>
                        <input type="text" class="form-input" id="orcCep" placeholder="00000-000 (opcional)" maxlength="9" oninput="buscarCepEvento()">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Número *</label>
                        <input type="text" class="form-input" id="orcNumero" placeholder="Nº" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label">Rua/Avenida *</label>
                    <input type="text" class="form-input" id="orcRua" placeholder="Digite a rua ou preencha pelo CEP" required>
                </div>
                <div class="form-row" style="margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Bairro *</label>
                        <input type="text" class="form-input" id="orcBairro" placeholder="Digite o bairro" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Complemento</label>
                        <input type="text" class="form-input" id="orcComplemento" placeholder="Salão, Quadra, etc.">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Cidade *</label>
                        <input type="text" class="form-input" id="orcCidade" placeholder="Digite a cidade" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Estado *</label>
                        <input type="text" class="form-input" id="orcEstado" placeholder="UF" style="text-transform: uppercase;" maxlength="2" required>
                    </div>
                </div>
                <div id="cepLoading" class="cep-feedback cep-loading">
                    🔍 Buscando endereço...
                </div>
                <div id="cepSuccess" class="cep-feedback cep-success">
                    ✅ Endereço encontrado!
                </div>
                <div id="cepError" class="cep-feedback cep-error">
                    ❌ CEP não encontrado. Verifique o número digitado.
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: SERVIÇOS E VALORES -->
        <div class="section-box section-servicos">
            <h3 class="section-title">💰 Serviços e Valores</h3>
            
            <!-- Seleção de Serviços -->
            <div class="form-group">
                <label class="form-label">Serviços Inclusos</label>
                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 14px; color: var(--text-secondary);">Selecione os serviços:</span>
                        <button type="button" class="btn btn-secondary" style="padding: 8px 16px; font-size: 12px;" onclick="abrirModalServicos()">
                            + Novo Serviço
                        </button>
                    </div>
                    <div id="listaServicosDisponiveis" style="max-height: 300px; overflow-y: auto;">
                        <!-- Lista de checkboxes de serviços -->
                    </div>
                </div>
                <div id="servicosSelecionados" style="background: rgba(6, 214, 160, 0.1); border: 1px solid var(--success); border-radius: 8px; padding: 12px; display: none;">
                    <div style="font-size: 13px; color: var(--success); margin-bottom: 8px; font-weight: 600;">✓ Serviços Selecionados:</div>
                    <div id="servicosSelecionadosList"></div>
                </div>
            </div>

            <!-- Outras Taxas -->
            <div class="form-group">
                <label class="form-label">Outras Taxas 📋</label>
                <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); border-radius: 12px; padding: 16px;">
                    <!-- Taxa Deslocamento -->
                    <div id="taxaDeslocamento" style="background: rgba(255, 210, 63, 0.1); border: 1px solid var(--accent); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                            <div style="flex: 1;">
                                <div style="font-size: 13px; color: var(--accent); font-weight: 600; margin-bottom: 4px;">
                                    🚗 Deslocamento
                                </div>
                                <input type="number" step="0.01" class="form-input" id="taxaDeslocamentoValor" placeholder="Valor (R$)" oninput="calcularTotal()" style="background: rgba(255, 255, 255, 0.05);">
                            </div>
                            <button type="button" onclick="removerTaxaDeslocamento()" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 20px; padding: 8px;">
                                🗑️
                            </button>
                        </div>
                    </div>
                    
                    <div id="outrasTaxasList">
                        <!-- Taxas adicionadas aparecerão aqui -->
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="adicionarTaxa()" style="width: 100%; margin-top: 8px;">
                        + Adicionar Taxa
                    </button>
                </div>
            </div>

            <!-- Desconto -->
            <div class="form-group">
                <label class="form-label">Desconto 🏷️</label>
                <div class="form-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <input type="number" step="0.01" class="form-input" id="orcDesconto" placeholder="0,00" oninput="calcularTotal()">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <select class="form-select" id="orcDescontoTipo" onchange="calcularTotal()">
                            <option value="valor">R$ (Valor)</option>
                            <option value="percentual">% (Percentual)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Resumo Financeiro -->
            <div class="resumo-financeiro">
                <div style="font-size: 14px; color: var(--primary); font-weight: 600; margin-bottom: 12px;">💰 Resumo Financeiro:</div>
                <div class="resumo-linha">
                    <span>Subtotal (Serviços):</span>
                    <strong id="orcSubtotalServicos">R$ 0,00</strong>
                </div>
                <div class="resumo-linha">
                    <span>Outras Taxas:</span>
                    <strong id="orcTotalTaxas">R$ 0,00</strong>
                </div>
                <div class="resumo-linha" style="color: var(--danger);">
                    <span>Desconto:</span>
                    <strong id="orcTotalDesconto">- R$ 0,00</strong>
                </div>
                <div class="resumo-linha resumo-total">
                    <span>VALOR TOTAL:</span>
                    <strong id="orcValorTotalDisplay" style="color: var(--success);">R$ 0,00</strong>
                </div>
            </div>
            <input type="hidden" id="orcValorTotal">
        </div>

        <!-- SEÇÃO 4: INFORMAÇÕES ADICIONAIS -->
        <div class="section-box section-info">
            <h3 class="section-title">📝 Informações Adicionais</h3>
            
            <div class="form-group">
                <label class="form-label">Status do Evento</label>
                <select class="form-select" id="orcStatus" required>
                    <option value="aguardando" selected>⏳ Aguardando Aprovação</option>
                    <option value="confirmado">✅ Confirmado</option>
                    <option value="andamento">🔄 Em Andamento</option>
                    <option value="concluido">✅ Concluído</option>
                    <option value="cancelado">❌ Cancelado</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Observações do Orçamento</label>
                <textarea class="form-textarea" id="orcObservacoes" placeholder="Detalhes, condições especiais, etc..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data de Criação</label>
                    <input type="date" class="form-input" id="orcDataCriacao" readonly style="background: rgba(255, 255, 255, 0.03);">
                </div>
                <div class="form-group">
                    <label class="form-label">Validade do Orçamento (30 dias)</label>
                    <input type="date" class="form-input" id="orcValidade" readonly style="background: rgba(255, 255, 255, 0.03);">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-submit" id="btnSubmit">💼 Gerar Orçamento e Criar Evento</button>
    </form>
</div>

<script>
    // ===================================
    // VARIÁVEIS GLOBAIS
    // ===================================
    let servicosDisponiveis = [];
    let servicosSelecionados = [];
    let outrasTaxas = [];
    let taxasCounter = 0;
    let clienteData = null;
    let modoEdicao = false;
    let eventoEditandoId = null;

    // ===================================
    // INICIALIZAÇÃO
    // ===================================
    document.addEventListener('DOMContentLoaded', async function() {
        // Pegar parâmetros da URL
        const urlParams = new URLSearchParams(window.location.search);
        const clienteId = urlParams.get('cliente_id');
        const clienteNome = urlParams.get('cliente_nome');
        const eventoId = urlParams.get('id');

        // Carregar serviços primeiro (precisa estar pronto antes de preencher)
        await carregarServicos();

        if (eventoId) {
            // MODO EDIÇÃO: carregar dados do evento
            modoEdicao = true;
            eventoEditandoId = eventoId;
            document.getElementById('pageTitulo').textContent = '✏️ Editar Evento';
            document.getElementById('pageSubtitulo').textContent = 'Edite os dados do evento e salve as alterações';
            document.getElementById('btnSubmit').textContent = '💾 Salvar Alterações';
            document.title = 'Editar Evento - EventProDJ';

            await carregarEventoParaEdicao(eventoId);
        } else {
            // MODO CRIAÇÃO
            if (clienteNome) {
                document.getElementById('orcCliente').value = decodeURIComponent(clienteNome);
                document.getElementById('orcClienteId').value = clienteId || '';

                if (clienteId) {
                    carregarDadosCliente(clienteId);
                }
            }

            // Definir datas
            const hoje = new Date();
            document.getElementById('orcDataCriacao').value = hoje.toISOString().split('T')[0];

            const validade = new Date();
            validade.setDate(validade.getDate() + 30);
            document.getElementById('orcValidade').value = validade.toISOString().split('T')[0];
        }
    });

    // ===================================
    // CARREGAR EVENTO PARA EDIÇÃO
    // ===================================
    async function carregarEventoParaEdicao(eventoId) {
        try {
            const response = await fetch(`../api/eventos.php?action=get&id=${eventoId}`);
            const data = await response.json();

            if (!data.success || !data.evento) {
                alert('Erro ao carregar evento para edição');
                window.location.href = 'eventos-eventpro.php';
                return;
            }

            const ev = data.evento;

            // Cliente
            document.getElementById('orcCliente').value = ev.cliente_nome || '';
            document.getElementById('orcClienteId').value = ev.cliente_id || '';

            // Tipo de evento
            document.getElementById('orcTipoEvento').value = ev.tipo || '';

            // Data
            document.getElementById('orcData').value = ev.data_evento || '';

            // Horários
            if (ev.hora_inicio) document.getElementById('orcHoraInicio').value = ev.hora_inicio.substring(0, 5);
            if (ev.hora_fim) document.getElementById('orcHoraFim').value = ev.hora_fim.substring(0, 5);
            if (ev.duracao) document.getElementById('orcDuracao').value = ev.duracao;
            calcularDuracao();

            // Endereço - preencher campos individuais
            document.getElementById('orcCep').value = ev.cep || '';
            document.getElementById('orcNumero').value = ev.numero || '';
            document.getElementById('orcComplemento').value = ev.complemento || '';
            document.getElementById('orcBairro').value = ev.bairro || '';
            document.getElementById('orcCidade').value = ev.cidade || '';
            document.getElementById('orcEstado').value = ev.estado || '';

            // Se não tem campos individuais mas tem endereco combinado, tentar parsear
            if (!ev.numero && !ev.bairro && ev.endereco) {
                document.getElementById('orcRua').value = ev.endereco;
            } else {
                // Extrair rua do endereço combinado (primeiro segmento antes da vírgula)
                if (ev.endereco) {
                    const partes = ev.endereco.split(',').map(p => p.trim());
                    if (partes.length > 0) {
                        document.getElementById('orcRua').value = partes[0] || '';
                    }
                }
            }

            // Status
            document.getElementById('orcStatus').value = ev.status || 'aguardando';

            // Observações
            document.getElementById('orcObservacoes').value = ev.observacoes || '';

            // Datas de criação e validade
            if (ev.data_criacao) {
                const dataCriacao = ev.data_criacao.substring(0, 10);
                document.getElementById('orcDataCriacao').value = dataCriacao;
            }
            if (ev.data_validade) {
                document.getElementById('orcValidade').value = ev.data_validade;
            }

            // Serviços - marcar checkboxes e popular array
            if (ev.servicos && ev.servicos.length > 0) {
                ev.servicos.forEach(servico => {
                    const servicoId = servico.servico_id;
                    const valor = parseFloat(servico.valor_unitario || servico.valor_total || 0);
                    const nome = servico.servico_nome || 'Serviço';

                    // Adicionar ao array
                    servicosSelecionados.push({
                        id: parseInt(servicoId),
                        nome: nome,
                        valor: valor
                    });

                    // Marcar checkbox
                    const checkbox = document.querySelector(`input[type="checkbox"][value="${servicoId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                atualizarServicosSelecionados();
            }

            // Taxas
            if (ev.taxas && ev.taxas.length > 0) {
                ev.taxas.forEach(taxa => {
                    const descricao = taxa.descricao || '';
                    const valor = parseFloat(taxa.valor || 0);

                    if (descricao.toLowerCase() === 'deslocamento') {
                        // Preencher taxa de deslocamento existente
                        document.getElementById('taxaDeslocamentoValor').value = valor;
                    } else {
                        // Adicionar como taxa extra
                        taxasCounter++;
                        const taxaId = taxasCounter;
                        const taxaHtml = `
                            <div class="taxa-item" id="taxa-${taxaId}">
                                <div style="display: flex; gap: 12px; align-items: start;">
                                    <div style="flex: 1;">
                                        <input type="text" class="form-input" id="taxaNome-${taxaId}" placeholder="Nome da taxa" value="${descricao}" style="margin-bottom: 8px;">
                                        <input type="number" step="0.01" class="form-input" id="taxaValor-${taxaId}" placeholder="Valor (R$)" value="${valor}" oninput="calcularTotal()">
                                    </div>
                                    <button type="button" onclick="removerTaxa(${taxaId})" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 20px; padding: 8px;">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        `;
                        document.getElementById('outrasTaxasList').insertAdjacentHTML('beforeend', taxaHtml);
                    }
                });
            }

            // Desconto
            if (ev.desconto && parseFloat(ev.desconto) > 0) {
                document.getElementById('orcDesconto').value = parseFloat(ev.desconto);
            }

            // Recalcular total
            calcularTotal();

        } catch (error) {
            console.error('Erro ao carregar evento:', error);
            alert('Erro ao carregar dados do evento');
        }
    }

    // ===================================
    // CARREGAR DADOS DO CLIENTE
    // ===================================
    async function carregarDadosCliente(clienteId) {
        try {
            const response = await fetch(`../api/clientes.php?action=get&id=${clienteId}`);
            const data = await response.json();
            
            if (data.success && data.cliente) {
                clienteData = data.cliente;
                
                // Se cliente tem endereço completo, mostrar sugestão
                if (clienteData.cep && clienteData.rua && clienteData.cidade) {
                    const enderecoCompleto = [
                        clienteData.rua,
                        clienteData.numero,
                        clienteData.bairro,
                        clienteData.cidade,
                        clienteData.estado
                    ].filter(Boolean).join(', ');
                    
                    document.getElementById('enderecoClienteDisplay').textContent = enderecoCompleto;
                    document.getElementById('opcoesEnderecoCliente').style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Erro ao carregar cliente:', error);
        }
    }

    // ===================================
    // USAR ENDEREÇO DO CLIENTE
    // ===================================
    function usarEnderecoCliente() {
        if (!clienteData) return;
        
        document.getElementById('orcCep').value = clienteData.cep || '';
        document.getElementById('orcNumero').value = clienteData.numero || '';
        document.getElementById('orcRua').value = clienteData.rua || '';
        document.getElementById('orcBairro').value = clienteData.bairro || '';
        document.getElementById('orcComplemento').value = clienteData.complemento || '';
        document.getElementById('orcCidade').value = clienteData.cidade || '';
        document.getElementById('orcEstado').value = clienteData.estado || '';
        
        document.getElementById('opcoesEnderecoCliente').style.display = 'none';
    }

    function ocultarSugestaoEndereco() {
        document.getElementById('opcoesEnderecoCliente').style.display = 'none';
    }

    // ===================================
    // CALCULAR DURAÇÃO DO EVENTO
    // ===================================
    function calcularDuracao() {
        const inicio = document.getElementById('orcHoraInicio').value;
        const fim = document.getElementById('orcHoraFim').value;
        
        if (!inicio || !fim) {
            document.getElementById('orcDuracao').value = '';
            return;
        }
        
        const [horaIni, minIni] = inicio.split(':').map(Number);
        const [horaFim, minFim] = fim.split(':').map(Number);
        
        let minutosTotais = (horaFim * 60 + minFim) - (horaIni * 60 + minIni);
        
        if (minutosTotais < 0) {
            minutosTotais += 24 * 60; // Evento passa da meia-noite
        }
        
        const horas = Math.floor(minutosTotais / 60);
        const minutos = minutosTotais % 60;
        
        document.getElementById('orcDuracao').value = `${horas}h${minutos > 0 ? minutos + 'min' : ''}`;
    }

    // ===================================
    // BUSCAR CEP DO EVENTO
    // ===================================
    let cepTimeout;
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

    async function buscarCepEvento() {
        const cepInput = document.getElementById('orcCep');
        const cep = cepInput.value.replace(/\D/g, '');

        // Formatar CEP
        if (cep.length <= 8) {
            const formatted = cep.length > 5 ? cep.substring(0,5) + '-' + cep.substring(5) : cep;
            cepInput.value = formatted;
        }

        // Limpar feedbacks
        document.getElementById('cepLoading').style.display = 'none';
        document.getElementById('cepSuccess').style.display = 'none';
        document.getElementById('cepError').style.display = 'none';

        if (cep.length !== 8) return;

        clearTimeout(cepTimeout);
        cepTimeout = setTimeout(async () => {
            try {
                document.getElementById('cepLoading').style.display = 'block';

                const data = await consultarCepApi(cep);

                document.getElementById('cepLoading').style.display = 'none';

                document.getElementById('orcRua').value = data.logradouro;
                document.getElementById('orcBairro').value = data.bairro;
                document.getElementById('orcCidade').value = data.localidade;
                document.getElementById('orcEstado').value = data.uf;

                document.getElementById('cepSuccess').style.display = 'block';
                document.getElementById('orcNumero').focus();

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

    // ===================================
    // CARREGAR SERVIÇOS
    // ===================================
    async function carregarServicos() {
        try {
            const response = await fetch('../api/servicos.php?action=list');
            const data = await response.json();
            
            if (data.success) {
                servicosDisponiveis = data.servicos || [];
                renderizarServicos();
            }
        } catch (error) {
            console.error('Erro ao carregar serviços:', error);
        }
    }

    function renderizarServicos() {
        const container = document.getElementById('listaServicosDisponiveis');
        
        if (servicosDisponiveis.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 20px; color: var(--text-secondary);">
                    Nenhum serviço cadastrado.
                    <br>Clique em "Novo Serviço" para adicionar.
                </div>
            `;
            return;
        }
        
        container.innerHTML = servicosDisponiveis.map(servico => {
            // Tentar valor_base primeiro, depois valor
            const valor = parseFloat(servico.valor_base || servico.valor) || 0;
            return `
                <label class="servico-checkbox">
                    <input type="checkbox" 
                           value="${servico.id}" 
                           data-nome="${servico.nome}"
                           data-valor="${valor}"
                           onchange="toggleServico(this)">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 4px;">${servico.nome}</div>
                        <div style="font-size: 13px; color: var(--text-secondary);">
                            ${formatarMoeda(valor)} ${servico.descricao ? '• ' + servico.descricao : ''}
                        </div>
                    </div>
                </label>
            `;
        }).join('');
    }

    // ===================================
    // TOGGLE SERVIÇO
    // ===================================
    function toggleServico(checkbox) {
        const servicoId = parseInt(checkbox.value);
        const servicoNome = checkbox.dataset.nome;
        const servicoValor = parseFloat(checkbox.dataset.valor);
        
        if (checkbox.checked) {
            servicosSelecionados.push({
                id: servicoId,
                nome: servicoNome,
                valor: servicoValor
            });
        } else {
            servicosSelecionados = servicosSelecionados.filter(s => s.id !== servicoId);
        }
        
        atualizarServicosSelecionados();
        calcularTotal();
    }

    function atualizarServicosSelecionados() {
        const container = document.getElementById('servicosSelecionadosList');
        const box = document.getElementById('servicosSelecionados');
        
        if (servicosSelecionados.length === 0) {
            box.style.display = 'none';
            return;
        }
        
        box.style.display = 'block';
        
        container.innerHTML = servicosSelecionados.map(servico => `
            <div class="servico-selecionado">
                <div>
                    <div style="font-weight: 600;">${servico.nome}</div>
                    <div style="font-size: 13px; color: var(--text-secondary);">${formatarMoeda(servico.valor)}</div>
                </div>
                <button type="button" onclick="removerServico(${servico.id})" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 18px;">
                    ×
                </button>
            </div>
        `).join('');
    }

    function removerServico(servicoId) {
        // Desmarcar checkbox
        const checkbox = document.querySelector(`input[value="${servicoId}"]`);
        if (checkbox) checkbox.checked = false;
        
        // Remover da lista
        servicosSelecionados = servicosSelecionados.filter(s => s.id !== servicoId);
        atualizarServicosSelecionados();
        calcularTotal();
    }

    // ===================================
    // TAXAS ADICIONAIS
    // ===================================
    function adicionarTaxa() {
        taxasCounter++;
        const taxaId = taxasCounter;
        
        const taxaHtml = `
            <div class="taxa-item" id="taxa-${taxaId}">
                <div style="display: flex; gap: 12px; align-items: start;">
                    <div style="flex: 1;">
                        <input type="text" class="form-input" id="taxaNome-${taxaId}" placeholder="Nome da taxa" style="margin-bottom: 8px;">
                        <input type="number" step="0.01" class="form-input" id="taxaValor-${taxaId}" placeholder="Valor (R$)" oninput="calcularTotal()">
                    </div>
                    <button type="button" onclick="removerTaxa(${taxaId})" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 20px; padding: 8px;">
                        🗑️
                    </button>
                </div>
            </div>
        `;
        
        document.getElementById('outrasTaxasList').insertAdjacentHTML('beforeend', taxaHtml);
    }

    function removerTaxa(taxaId) {
        document.getElementById(`taxa-${taxaId}`).remove();
        calcularTotal();
    }

    function removerTaxaDeslocamento() {
        document.getElementById('taxaDeslocamento').style.display = 'none';
        document.getElementById('taxaDeslocamentoValor').value = '';
        calcularTotal();
    }

    // ===================================
    // CALCULAR TOTAL
    // ===================================
    function calcularTotal() {
        // Subtotal de serviços (garantir que valores são números)
        const subtotalServicos = servicosSelecionados.reduce((acc, s) => {
            const valor = parseFloat(s.valor) || 0;
            return acc + valor;
        }, 0);
        
        // Taxas
        let totalTaxas = 0;
        
        // Taxa de deslocamento
        const taxaDesl = parseFloat(document.getElementById('taxaDeslocamentoValor')?.value) || 0;
        totalTaxas += taxaDesl;
        
        // Outras taxas
        document.querySelectorAll('[id^="taxaValor-"]').forEach(input => {
            totalTaxas += parseFloat(input.value) || 0;
        });
        
        // Desconto
        const descontoValor = parseFloat(document.getElementById('orcDesconto')?.value) || 0;
        const descontoTipo = document.getElementById('orcDescontoTipo')?.value || 'valor';
        
        let valorDesconto = 0;
        if (descontoTipo === 'percentual') {
            valorDesconto = (subtotalServicos + totalTaxas) * (descontoValor / 100);
        } else {
            valorDesconto = descontoValor;
        }
        
        // Total
        const valorTotal = Math.max(0, subtotalServicos + totalTaxas - valorDesconto);
        
        // Atualizar displays
        document.getElementById('orcSubtotalServicos').textContent = formatarMoeda(subtotalServicos);
        document.getElementById('orcTotalTaxas').textContent = formatarMoeda(totalTaxas);
        document.getElementById('orcTotalDesconto').textContent = '- ' + formatarMoeda(valorDesconto);
        document.getElementById('orcValorTotalDisplay').textContent = formatarMoeda(valorTotal);
        document.getElementById('orcValorTotal').value = valorTotal.toFixed(2);
        
        console.log('Cálculo:', {
            subtotalServicos,
            totalTaxas,
            valorDesconto,
            valorTotal
        });
    }

    // ===================================
    // SALVAR ORÇAMENTO
    // ===================================
    async function salvarOrcamento(e) {
        e.preventDefault();
        
        if (servicosSelecionados.length === 0) {
            alert('Selecione pelo menos um serviço!');
            return;
        }
        
        try {
            // Coletar taxas adicionais
            const taxasAdicionais = [];
            
            // Taxa de deslocamento
            const taxaDesl = parseFloat(document.getElementById('taxaDeslocamentoValor').value) || 0;
            if (taxaDesl > 0) {
                taxasAdicionais.push({
                    nome: 'Deslocamento',
                    valor: taxaDesl
                });
            }
            
            // Outras taxas
            document.querySelectorAll('[id^="taxaNome-"]').forEach(input => {
                const taxaId = input.id.split('-')[1];
                const nome = input.value;
                const valor = parseFloat(document.getElementById(`taxaValor-${taxaId}`).value) || 0;
                
                if (nome && valor > 0) {
                    taxasAdicionais.push({ nome, valor });
                }
            });
            
            // Montar dados do orçamento/evento
            const orcamentoData = {
                action: modoEdicao ? 'update_orcamento' : 'create_orcamento',
                cliente_id: document.getElementById('orcClienteId').value,
                cliente_nome: document.getElementById('orcCliente').value,
                tipo_evento: document.getElementById('orcTipoEvento').value,
                data_evento: document.getElementById('orcData').value,
                hora_inicio: document.getElementById('orcHoraInicio').value,
                hora_fim: document.getElementById('orcHoraFim').value,
                duracao: document.getElementById('orcDuracao').value,
                cep: document.getElementById('orcCep').value,
                numero: document.getElementById('orcNumero').value,
                rua: document.getElementById('orcRua').value,
                bairro: document.getElementById('orcBairro').value,
                complemento: document.getElementById('orcComplemento').value,
                cidade: document.getElementById('orcCidade').value,
                estado: document.getElementById('orcEstado').value,
                servicos: JSON.stringify(servicosSelecionados),
                taxas: JSON.stringify(taxasAdicionais),
                desconto_valor: document.getElementById('orcDesconto').value || 0,
                desconto_tipo: document.getElementById('orcDescontoTipo').value,
                valor_total: document.getElementById('orcValorTotal').value,
                status: document.getElementById('orcStatus').value,
                observacoes: document.getElementById('orcObservacoes').value,
                data_criacao: document.getElementById('orcDataCriacao').value,
                validade: document.getElementById('orcValidade').value
            };

            // Em modo edição, adicionar o ID do evento
            if (modoEdicao && eventoEditandoId) {
                orcamentoData.evento_id = eventoEditandoId;
            }
            
            const formData = new FormData();
            Object.keys(orcamentoData).forEach(key => {
                formData.append(key, orcamentoData[key]);
            });
            
            const response = await fetch('../api/eventos.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(modoEdicao ? '✅ Evento atualizado com sucesso!' : '✅ Orçamento e evento criados com sucesso!');
                window.location.href = 'eventos-eventpro.php';
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro ao salvar:', error);
            alert(modoEdicao ? 'Erro ao atualizar evento' : 'Erro ao salvar orçamento');
        }
    }

    // ===================================
    // ABRIR MODAL DE SERVIÇOS
    // ===================================
    function abrirModalServicos() {
        window.location.href = 'servicos-eventpro.php';
    }

    // ===================================
    // FUNÇÕES AUXILIARES
    // ===================================
    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(valor);
    }
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
