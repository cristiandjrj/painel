/**
 * Bot WhatsApp - JavaScript Frontend
 * EventProDJ
 */

const BOT_API = '../api/bot.php';
let conversasCache = [];
let configCache = null;

// ═══════════════════════════════════════════
// NAVEGACAO DE SECOES
// ═══════════════════════════════════════════

function mostrarSecao(secao) {
  const secoes = ['Dashboard', 'Conversas', 'Mensagens', 'Config'];
  const ids = ['dashboard', 'conversas', 'mensagens', 'config'];

  ids.forEach((id, i) => {
    const el = document.getElementById('secao' + secoes[i]);
    const btn = document.getElementById('btnBot' + secoes[i]);
    if (el) el.style.display = id === secao ? 'block' : 'none';
    if (btn) {
      btn.className = id === secao ? 'btn btn-primary' : 'btn btn-secondary';
    }
  });

  // Recarrega dados da secao ativa
  if (secao === 'dashboard') { carregarStatus(); carregarStats(); }
  if (secao === 'conversas') { carregarConversas(); }
  if (secao === 'mensagens') { carregarConfigBot(); }
  if (secao === 'config') { carregarConfigBot(); }
}

// ═══════════════════════════════════════════
// STATUS DO BOT
// ═══════════════════════════════════════════

async function carregarStatus() {
  try {
    const resp = await fetch(`${BOT_API}?action=status`);
    const data = await resp.json();

    const dot = document.getElementById('botStatusDot');
    const badge = document.getElementById('botStatusBadge');
    const text = document.getElementById('botStatusText');
    const numero = document.getElementById('botNumero');
    const uptime = document.getElementById('statUptime');

    if (data.success && data.status === 'connected') {
      dot.className = 'status-dot online';
      badge.className = 'status-indicator online';
      text.textContent = 'Online';
      if (data.numero) numero.textContent = formatarTelefone(data.numero);
      if (data.uptimeFormatado) uptime.textContent = data.uptimeFormatado;

      document.getElementById('statTotalContatos').textContent = data.totalUsuarios || 0;
      document.getElementById('statFollowUps').textContent = data.followUpsAtivos || 0;
    } else {
      dot.className = 'status-dot offline';
      badge.className = 'status-indicator offline';
      text.textContent = data.offline ? 'Sem conexao' : 'Offline';
      numero.textContent = '';
      uptime.textContent = '--';
    }
  } catch (err) {
    const dot = document.getElementById('botStatusDot');
    const badge = document.getElementById('botStatusBadge');
    const text = document.getElementById('botStatusText');
    if (dot) dot.className = 'status-dot offline';
    if (badge) badge.className = 'status-indicator offline';
    if (text) text.textContent = 'Erro de conexao';
  }
}

// ═══════════════════════════════════════════
// ESTATISTICAS
// ═══════════════════════════════════════════

async function carregarStats() {
  try {
    const resp = await fetch(`${BOT_API}?action=stats`);
    const data = await resp.json();

    if (data.success && data.stats) {
      const s = data.stats;
      document.getElementById('statHoje').textContent = s.atendimentosHoje || 0;
      document.getElementById('statAtendente').textContent = s.aguardandoAtendente || 0;
      document.getElementById('statConversao').textContent = (s.taxaConversao || 0) + '%';

      if (s.opcoesPorTipo) {
        document.getElementById('statValores').textContent = s.opcoesPorTipo.valores || 0;
        document.getElementById('statRegioes').textContent = s.opcoesPorTipo.regioes || 0;
        document.getElementById('statReservar').textContent = s.opcoesPorTipo.reservar || 0;
        document.getElementById('statInstagram').textContent = s.opcoesPorTipo.instagram || 0;
        document.getElementById('statAtendenteOpcao').textContent = s.opcoesPorTipo.atendente || 0;
      }
    }
  } catch (err) {
    console.error('Erro ao carregar stats:', err);
  }
}

// ═══════════════════════════════════════════
// CONVERSAS
// ═══════════════════════════════════════════

async function carregarConversas() {
  try {
    const resp = await fetch(`${BOT_API}?action=conversas`);
    const data = await resp.json();

    if (data.success) {
      conversasCache = data.conversas || [];
      renderizarConversas(conversasCache);
    } else {
      document.getElementById('listaConversas').innerHTML =
        '<p style="text-align:center;color:var(--text-secondary);padding:40px;">Nenhuma conversa encontrada. Clique em "Sincronizar" para buscar do bot.</p>';
    }
  } catch (err) {
    document.getElementById('listaConversas').innerHTML =
      '<p style="text-align:center;color:var(--danger);padding:40px;">Erro ao carregar conversas</p>';
  }
}

function renderizarConversas(conversas) {
  const container = document.getElementById('listaConversas');

  if (!conversas || conversas.length === 0) {
    container.innerHTML = '<p style="text-align:center;color:var(--text-secondary);padding:40px;">Nenhuma conversa encontrada</p>';
    return;
  }

  let html = '';
  conversas.forEach(conv => {
    const nome = conv.nome_contato || conv.nome || 'Cliente';
    const telefone = conv.telefone || '';
    const ultimaInteracao = conv.ultima_interacao ? formatarData(conv.ultima_interacao) : '--';
    const status = conv.cliente_id || conv.cliente_vinculado_id ? 'convertido' : (conv.status || 'ativo');
    const statusLabel = {
      ativo: 'Ativo',
      atendente: 'Aguardando',
      inativo: 'Inativo',
      convertido: 'Convertido'
    }[status] || status;
    const jid = conv.jid || '';
    const tel = jid.split('@')[0] || telefone.replace(/\D/g, '');

    html += `
      <div class="conversa-item" data-nome="${nome.toLowerCase()}" data-telefone="${telefone}" data-status="${status}">
        <div class="conversa-info">
          <div class="conversa-nome">${escapeHtml(nome)}</div>
          <div class="conversa-telefone">${escapeHtml(telefone || tel)}</div>
        </div>
        <div class="conversa-meta">
          <div class="conversa-data">${ultimaInteracao}</div>
          <span class="conversa-badge ${status}">${statusLabel}</span>
        </div>
        <div class="conversa-acoes">
          <button class="btn-action" onclick="verDetalhe('${jid}')">Detalhe</button>
          <button class="btn-action" onclick="abrirModalMsg('${tel}')">Msg</button>
          ${status !== 'convertido' ? `<button class="btn-action" onclick="converterCliente('${jid}', '${escapeHtml(nome)}', '${tel}')">Converter</button>` : ''}
          <button class="btn-action" onclick="resetarUsuario('${jid}')">Reset</button>
        </div>
      </div>`;
  });

  container.innerHTML = html;
}

function filtrarConversas() {
  const busca = document.getElementById('buscaConversa').value.toLowerCase();
  const filtro = document.getElementById('filtroStatus').value;

  const items = document.querySelectorAll('.conversa-item');
  items.forEach(item => {
    const nome = item.dataset.nome || '';
    const telefone = item.dataset.telefone || '';
    const status = item.dataset.status || '';

    const matchBusca = !busca || nome.includes(busca) || telefone.includes(busca);
    const matchStatus = !filtro || status === filtro;

    item.style.display = (matchBusca && matchStatus) ? 'flex' : 'none';
  });
}

async function sincronizarConversas() {
  const btn = document.getElementById('btnSync');
  if (btn) { btn.disabled = true; btn.textContent = 'Sincronizando...'; }

  try {
    const resp = await fetch(`${BOT_API}?action=sync_conversas`);
    const data = await resp.json();

    if (data.success) {
      alert(data.message || 'Sincronizado!');
      carregarConversas();
      carregarStats();
    } else {
      alert('Erro: ' + (data.message || 'Falha na sincronizacao'));
    }
  } catch (err) {
    alert('Erro de conexao com o painel');
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Sincronizar'; }
  }
}

async function verDetalhe(jid) {
  document.getElementById('modalDetalhe').classList.add('active');
  document.getElementById('modalDetalheConteudo').innerHTML = '<p style="color:var(--text-secondary);">Carregando...</p>';

  try {
    const resp = await fetch(`${BOT_API}?action=conversa_detalhe&jid=${encodeURIComponent(jid)}`);
    const data = await resp.json();

    if (data.success && data.conversa) {
      const c = data.conversa;
      document.getElementById('modalDetalheNome').textContent = c.nome || 'Cliente';

      let html = `
        <div style="margin-bottom: 16px;">
          <p><strong>Telefone:</strong> ${escapeHtml(c.telefone || jid.split('@')[0])}</p>
          <p><strong>Primeiro contato:</strong> ${c.primeiroContato ? formatarData(c.primeiroContato) : '--'}</p>
          <p><strong>Ultima interacao:</strong> ${c.ultimaInteracao ? formatarData(c.ultimaInteracao) : '--'}</p>
          <p><strong>Total interacoes:</strong> ${c.totalInteracoes || 0}</p>
          <p><strong>Ultima opcao:</strong> ${c.ultimaOpcao || '--'}</p>
          <p><strong>Status:</strong> ${c.statusAtual || '--'}</p>
          <p><strong>Opcoes escolhidas:</strong> ${(c.opcoesEscolhidas || []).join(', ') || '--'}</p>
        </div>`;

      // Historico de mensagens
      if (c.historicoMensagens && c.historicoMensagens.length > 0) {
        html += '<h4 style="margin-bottom:10px;font-family:Syne,sans-serif;color:var(--text-primary);">Historico</h4>';
        html += '<div class="msg-historico">';
        c.historicoMensagens.forEach(msg => {
          html += `
            <div class="msg-item ${msg.tipo}">
              ${escapeHtml(msg.texto)}
              <div class="msg-data">${msg.data ? formatarData(msg.data) : ''}</div>
            </div>`;
        });
        html += '</div>';
      }

      document.getElementById('modalDetalheConteudo').innerHTML = html;
    } else {
      document.getElementById('modalDetalheConteudo').innerHTML =
        '<p style="color:var(--danger);">Erro ao carregar detalhes</p>';
    }
  } catch (err) {
    document.getElementById('modalDetalheConteudo').innerHTML =
      '<p style="color:var(--danger);">Erro de conexao</p>';
  }
}

async function converterCliente(jid, nome, telefone) {
  if (!confirm(`Converter "${nome}" (${telefone}) em cliente?`)) return;

  try {
    const form = new FormData();
    form.append('action', 'converter_cliente');
    form.append('jid', jid);
    form.append('nome', nome);
    form.append('telefone', telefone);

    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();

    alert(data.message || (data.success ? 'Convertido!' : 'Erro'));
    if (data.success) carregarConversas();
  } catch (err) {
    alert('Erro de conexao');
  }
}

async function resetarUsuario(jid) {
  if (!confirm('Resetar fluxo deste usuario? Ele receberá o menu novamente.')) return;

  try {
    const form = new FormData();
    form.append('action', 'reset_usuario');
    form.append('jid', jid);

    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();
    alert(data.message || 'Resetado');
  } catch (err) {
    alert('Erro de conexao');
  }
}

// ═══════════════════════════════════════════
// ENVIAR MENSAGEM
// ═══════════════════════════════════════════

function abrirModalMsg(telefone) {
  document.getElementById('modalTelefone').value = telefone;
  document.getElementById('modalMensagem').value = '';
  document.getElementById('modalEnviarMsg').classList.add('active');
}

async function enviarMensagemManual(e) {
  e.preventDefault();

  const telefone = document.getElementById('modalTelefone').value;
  const mensagem = document.getElementById('modalMensagem').value;

  if (!telefone || !mensagem) return;

  try {
    const form = new FormData();
    form.append('action', 'enviar_mensagem');
    form.append('telefone', telefone);
    form.append('mensagem', mensagem);

    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();

    if (data.success) {
      alert('Mensagem enviada!');
      fecharModal('modalEnviarMsg');
    } else {
      alert('Erro: ' + (data.message || 'Falha no envio'));
    }
  } catch (err) {
    alert('Erro de conexao');
  }
}

// ═══════════════════════════════════════════
// CONFIGURACAO DO BOT
// ═══════════════════════════════════════════

async function carregarConfigBot() {
  try {
    const resp = await fetch(`${BOT_API}?action=get_config`);
    const data = await resp.json();

    if (data.success) {
      configCache = data.config;
      const bc = data.botConfig;

      // Preenche configuracoes de conexao
      if (configCache) {
        document.getElementById('cfgBotUrl').value = configCache.bot_url || 'http://localhost:3001';
        document.getElementById('cfgApiKey').value = configCache.api_key || '';
        document.getElementById('cfgNomeEmpresa').value = configCache.nome_empresa || '';
        document.getElementById('cfgAssinatura').value = configCache.assinatura || '';
        document.getElementById('cfgLinkCatalogo').value = configCache.link_catalogo || '';
        document.getElementById('cfgLinkInstagram').value = configCache.link_instagram || '';
        document.getElementById('cfgTempoLembrete').value = configCache.tempo_lembrete_min || 60;
        document.getElementById('cfgTempoFollowUp').value = configCache.tempo_followup_min || 300;
        document.getElementById('cfgSilencioInicio').value = configCache.hora_silencio_inicio || 0;
        document.getElementById('cfgSilencioFim').value = configCache.hora_silencio_fim || 9;
        document.getElementById('cfgAtivo').checked = configCache.ativo != 0;
      }

      // Preenche mensagens do bot remoto ou do banco
      if (bc) {
        document.getElementById('msgSaudacao').value = bc.saudacao || configCache?.saudacao_template || '';
        document.getElementById('msgMenuIntro').value = bc.menuIntro || configCache?.menu_intro || '';
        document.getElementById('msgMenuTexto').value = bc.menu?.texto || configCache?.menu_texto || '';
        document.getElementById('msgClienteRetorno').value = bc.respostas?.clienteRetorno || '';
        document.getElementById('msgFollowUp').value = bc.respostas?.followUp || '';

        // Opcoes do menu
        const opcoes = bc.menu?.opcoes || [];
        renderizarOpcoesMenu(opcoes);

        // Respostas
        renderizarRespostas(bc.respostas || {}, opcoes);
      } else if (configCache) {
        // Usa dados do banco
        document.getElementById('msgSaudacao').value = configCache.saudacao_template || '';
        document.getElementById('msgMenuIntro').value = configCache.menu_intro || '';
        document.getElementById('msgMenuTexto').value = configCache.menu_texto || '';

        const opcoes = configCache.opcoes_menu ? JSON.parse(configCache.opcoes_menu) : [];
        renderizarOpcoesMenu(opcoes);

        const respostas = configCache.respostas ? JSON.parse(configCache.respostas) : {};
        renderizarRespostas(respostas, opcoes);
      }
    }
  } catch (err) {
    console.error('Erro ao carregar config:', err);
  }
}

function renderizarOpcoesMenu(opcoes) {
  const container = document.getElementById('opcoesMenuEditor');
  let html = '';

  (opcoes || []).forEach((op, i) => {
    html += `
      <div class="opcao-editor" data-index="${i}">
        <input type="text" class="form-input" value="${escapeHtml(op.id || '')}" placeholder="ID" style="max-width:100px;" data-field="id">
        <input type="text" class="form-input" value="${escapeHtml(op.titulo || '')}" placeholder="Titulo" data-field="titulo">
        <input type="text" class="form-input" value="${escapeHtml(op.descricao || '')}" placeholder="Descricao" data-field="descricao">
        <button type="button" class="btn-remove" onclick="removerOpcao(${i})">&times;</button>
      </div>`;
  });

  container.innerHTML = html;
}

function renderizarRespostas(respostas, opcoes) {
  const container = document.getElementById('respostasEditor');
  let html = '';

  // Respostas para cada opcao do menu (exceto atendente que tem formato especial)
  (opcoes || []).forEach(op => {
    const id = op.id;
    const resp = respostas[id] || {};

    if (id === 'atendente') {
      html += `
        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Atendente - Permitido</label>
          <textarea class="form-input resp-field" data-resp-id="${id}" data-resp-key="permitido" rows="2">${escapeHtml(resp.permitido || '')}</textarea>
        </div>
        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">Atendente - Bloqueado</label>
          <textarea class="form-input resp-field" data-resp-id="${id}" data-resp-key="bloqueado" rows="2">${escapeHtml(resp.bloqueado || '')}</textarea>
        </div>`;
    } else {
      html += `
        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label">${escapeHtml(op.titulo || id)} - Mensagem</label>
          <textarea class="form-input resp-field" data-resp-id="${id}" data-resp-key="mensagem" rows="3">${escapeHtml(resp.mensagem || '')}</textarea>
        </div>`;

      if (resp.complemento !== undefined || id === 'valores') {
        html += `
          <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">${escapeHtml(op.titulo || id)} - Complemento</label>
            <textarea class="form-input resp-field" data-resp-id="${id}" data-resp-key="complemento" rows="2">${escapeHtml(resp.complemento || '')}</textarea>
          </div>`;
      }
    }
  });

  container.innerHTML = html;
}

function adicionarOpcao() {
  const container = document.getElementById('opcoesMenuEditor');
  const index = container.children.length;

  const div = document.createElement('div');
  div.className = 'opcao-editor';
  div.dataset.index = index;
  div.innerHTML = `
    <input type="text" class="form-input" placeholder="ID (ex: promo)" style="max-width:100px;" data-field="id">
    <input type="text" class="form-input" placeholder="Titulo" data-field="titulo">
    <input type="text" class="form-input" placeholder="Descricao" data-field="descricao">
    <button type="button" class="btn-remove" onclick="removerOpcao(${index})">&times;</button>`;
  container.appendChild(div);
}

function removerOpcao(index) {
  const container = document.getElementById('opcoesMenuEditor');
  const items = container.querySelectorAll('.opcao-editor');
  if (items[index]) items[index].remove();
}

function coletarOpcoesMenu() {
  const items = document.querySelectorAll('#opcoesMenuEditor .opcao-editor');
  const opcoes = [];
  items.forEach(item => {
    const id = item.querySelector('[data-field="id"]').value.trim();
    const titulo = item.querySelector('[data-field="titulo"]').value.trim();
    const descricao = item.querySelector('[data-field="descricao"]').value.trim();
    if (id && titulo) {
      opcoes.push({ id, titulo, descricao });
    }
  });
  return opcoes;
}

function coletarRespostas() {
  const campos = document.querySelectorAll('.resp-field');
  const respostas = {};
  campos.forEach(campo => {
    const id = campo.dataset.respId;
    const key = campo.dataset.respKey;
    if (!respostas[id]) respostas[id] = {};
    respostas[id][key] = campo.value;
  });

  // Adiciona clienteRetorno e followUp
  respostas.clienteRetorno = document.getElementById('msgClienteRetorno').value;
  respostas.followUp = document.getElementById('msgFollowUp').value;

  return respostas;
}

async function salvarMensagens(e) {
  e.preventDefault();

  const opcoes = coletarOpcoesMenu();
  const respostas = coletarRespostas();

  const form = new FormData();
  form.append('action', 'save_config');
  form.append('nome_empresa', document.getElementById('cfgNomeEmpresa').value || configCache?.nome_empresa || '');
  form.append('assinatura', document.getElementById('cfgAssinatura').value || configCache?.assinatura || '');
  form.append('link_catalogo', document.getElementById('cfgLinkCatalogo').value || configCache?.link_catalogo || '');
  form.append('link_instagram', document.getElementById('cfgLinkInstagram').value || configCache?.link_instagram || '');
  form.append('saudacao_template', document.getElementById('msgSaudacao').value);
  form.append('menu_intro', document.getElementById('msgMenuIntro').value);
  form.append('menu_texto', document.getElementById('msgMenuTexto').value);
  form.append('menu_botao_texto', configCache?.menu_botao_texto || '📋 Ver opcoes');
  form.append('opcoes_menu', JSON.stringify(opcoes));
  form.append('respostas', JSON.stringify(respostas));
  form.append('tempo_lembrete_min', document.getElementById('cfgTempoLembrete').value || 60);
  form.append('tempo_followup_min', document.getElementById('cfgTempoFollowUp').value || 300);
  form.append('hora_silencio_inicio', document.getElementById('cfgSilencioInicio').value || 0);
  form.append('hora_silencio_fim', document.getElementById('cfgSilencioFim').value || 9);
  form.append('ativo', document.getElementById('cfgAtivo').checked ? 1 : 0);

  try {
    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();

    if (data.success) {
      alert(data.botSynced ? 'Mensagens salvas e sincronizadas com o bot!' : 'Mensagens salvas no painel! (Bot offline - sera sincronizado ao reconectar)');
    } else {
      alert('Erro: ' + (data.message || 'Falha ao salvar'));
    }
  } catch (err) {
    alert('Erro de conexao');
  }
}

// ═══════════════════════════════════════════
// CONFIGURACOES DE CONEXAO
// ═══════════════════════════════════════════

async function salvarConexao(e) {
  e.preventDefault();

  const form = new FormData();
  form.append('action', 'save_conexao');
  form.append('bot_url', document.getElementById('cfgBotUrl').value);
  form.append('api_key', document.getElementById('cfgApiKey').value);

  try {
    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();
    alert(data.message || (data.success ? 'Salvo!' : 'Erro'));
  } catch (err) {
    alert('Erro de conexao');
  }
}

async function testarConexao() {
  const resultado = document.getElementById('resultadoTesteConexao');
  resultado.innerHTML = '<span style="color:var(--accent);">Testando...</span>';

  try {
    const resp = await fetch(`${BOT_API}?action=testar_conexao`);
    const data = await resp.json();

    if (data.success && data.status === 'connected') {
      resultado.innerHTML = `<span style="color:var(--success);">Conectado! Bot online (${data.uptimeFormatado || 'OK'})</span>`;
    } else if (data.success) {
      resultado.innerHTML = `<span style="color:var(--accent);">Bot acessivel mas status: ${data.status}</span>`;
    } else {
      resultado.innerHTML = `<span style="color:var(--danger);">Falha: ${data.message || 'Sem resposta do bot'}</span>`;
    }
  } catch (err) {
    resultado.innerHTML = '<span style="color:var(--danger);">Erro de conexao com o painel</span>';
  }
}

async function salvarConfigGeral(e) {
  e.preventDefault();

  const form = new FormData();
  form.append('action', 'save_config');
  form.append('nome_empresa', document.getElementById('cfgNomeEmpresa').value);
  form.append('assinatura', document.getElementById('cfgAssinatura').value);
  form.append('link_catalogo', document.getElementById('cfgLinkCatalogo').value);
  form.append('link_instagram', document.getElementById('cfgLinkInstagram').value);
  form.append('saudacao_template', document.getElementById('msgSaudacao')?.value || configCache?.saudacao_template || '');
  form.append('menu_intro', document.getElementById('msgMenuIntro')?.value || configCache?.menu_intro || '');
  form.append('menu_texto', document.getElementById('msgMenuTexto')?.value || configCache?.menu_texto || '');
  form.append('menu_botao_texto', configCache?.menu_botao_texto || '📋 Ver opcoes');
  form.append('opcoes_menu', configCache?.opcoes_menu || '[]');
  form.append('respostas', configCache?.respostas || '{}');
  form.append('tempo_lembrete_min', document.getElementById('cfgTempoLembrete').value);
  form.append('tempo_followup_min', document.getElementById('cfgTempoFollowUp').value);
  form.append('hora_silencio_inicio', document.getElementById('cfgSilencioInicio').value);
  form.append('hora_silencio_fim', document.getElementById('cfgSilencioFim').value);
  form.append('ativo', document.getElementById('cfgAtivo').checked ? 1 : 0);

  try {
    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();

    if (data.success) {
      alert(data.botSynced ? 'Configuracoes salvas e bot atualizado!' : 'Configuracoes salvas! (Bot offline)');
    } else {
      alert('Erro: ' + (data.message || 'Falha'));
    }
  } catch (err) {
    alert('Erro de conexao');
  }
}

// ═══════════════════════════════════════════
// ACOES DO BOT
// ═══════════════════════════════════════════

async function reiniciarBot() {
  if (!confirm('Tem certeza que deseja reiniciar o bot? Ele ficara offline por alguns segundos.')) return;

  try {
    const form = new FormData();
    form.append('action', 'restart');

    const resp = await fetch(BOT_API, { method: 'POST', body: form });
    const data = await resp.json();
    alert(data.message || 'Reinicio solicitado');

    // Atualiza status apos 5s
    setTimeout(carregarStatus, 5000);
  } catch (err) {
    alert('Erro de conexao');
  }
}

// ═══════════════════════════════════════════
// MODAIS
// ═══════════════════════════════════════════

function fecharModal(id) {
  document.getElementById(id).classList.remove('active');
}

// Fechar modal ao clicar fora
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
  }
});

// Fechar modal com ESC
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
  }
});

// ═══════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════

function formatarTelefone(numero) {
  const n = String(numero).replace(/\D/g, '');
  if (n.length === 13) return `+${n.slice(0,2)} (${n.slice(2,4)}) ${n.slice(4,9)}-${n.slice(9)}`;
  if (n.length === 12) return `+${n.slice(0,2)} (${n.slice(2,4)}) ${n.slice(4,8)}-${n.slice(8)}`;
  if (n.length === 11) return `(${n.slice(0,2)}) ${n.slice(2,7)}-${n.slice(7)}`;
  return numero;
}

function formatarData(iso) {
  if (!iso) return '--';
  try {
    const d = new Date(iso);
    const agora = new Date();
    const diff = agora - d;

    // Menos de 1 hora
    if (diff < 3600000) {
      const min = Math.floor(diff / 60000);
      return min <= 1 ? 'agora' : `${min}min atras`;
    }
    // Menos de 24h
    if (diff < 86400000) {
      const h = Math.floor(diff / 3600000);
      return `${h}h atras`;
    }
    // Data completa
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit' });
  } catch {
    return iso;
  }
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
