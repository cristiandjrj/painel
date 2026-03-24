<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
$userId = requireAuthPage();
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - EventProDJ</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=IBM+Plex+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/eventprodj-styles.css">
    <style>
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
            #usersStats { grid-template-columns: repeat(2, 1fr) !important; }
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
        <h1 class="page-title">Configurações</h1>
        <p class="page-subtitle">Personalize seu sistema e configure dados da empresa</p>
    </div>

    <!-- Sub-navegação -->
    <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
        <button class="btn btn-primary" id="btnConfigPerfil" onclick="mostrarConfigSecao('perfil')">
            Perfil da Empresa
        </button>
        <button class="btn btn-secondary" id="btnConfigDocumentos" onclick="mostrarConfigSecao('documentos')">
            Documentos
        </button>
        <button class="btn btn-secondary" id="btnConfigLogo" onclick="mostrarConfigSecao('logo')">
            Logo
        </button>
        <?php if ($isAdmin): ?>
        <button class="btn btn-secondary" id="btnConfigUsuarios" onclick="mostrarConfigSecao('usuarios')">
            👥 Usuários
        </button>
        <?php endif; ?>
    </div>

    <!-- ===== PERFIL DA EMPRESA ===== -->
    <div id="configPerfilSecao">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Perfil da Empresa</h2>
            </div>
            <form onsubmit="salvarConfigEmpresa(event)" style="padding: 24px;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nome da Empresa *</label>
                        <input type="text" class="form-input" id="configNomeEmpresa" required placeholder="Ex: EventProDJ Produções">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nome Corporativo</label>
                        <input type="text" class="form-input" id="configNomeCorporativo" placeholder="Ex: EventProDJ Produções LTDA">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">CNPJ</label>
                        <input type="text" class="form-input" id="configCNPJ" placeholder="00.000.000/0000-00" maxlength="18" oninput="formatCNPJ(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefone *</label>
                        <input type="text" class="form-input" id="configTelefone" required placeholder="(00) 00000-0000" maxlength="15" oninput="formatTelefone(this)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">WhatsApp Business</label>
                        <input type="text" class="form-input" id="configWhatsapp" placeholder="(00) 00000-0000" maxlength="15" oninput="formatTelefone(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-input" id="configEmail" placeholder="contato@eventpro.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Endereço</label>
                        <input type="text" class="form-input" id="configEndereco" placeholder="Rua, Número, Bairro">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CEP</label>
                        <input type="text" class="form-input" id="configCep" placeholder="00000-000" maxlength="9">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cidade</label>
                        <input type="text" class="form-input" id="configCidade" placeholder="Ex: Rio de Janeiro">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-input" id="configEstado" placeholder="Ex: RJ" maxlength="2" style="text-transform: uppercase;">
                    </div>
                </div>

                <div style="background: rgba(79, 70, 229, 0.1); padding: 16px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4F46E5;">
                    <h4 style="margin-bottom: 12px; color: #4F46E5;">Redes Sociais</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Instagram</label>
                            <input type="text" class="form-input" id="configInstagram" placeholder="@eventpro">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Facebook</label>
                            <input type="text" class="form-input" id="configFacebook" placeholder="facebook.com/eventpro">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">YouTube</label>
                            <input type="text" class="form-input" id="configYoutube" placeholder="youtube.com/@eventpro">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-input" id="configSite" placeholder="https://eventpro.com">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== DOCUMENTOS / CONTRATO ===== -->
    <div id="configDocumentosSecao" style="display: none;">
        <!-- Contrato -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2 class="card-title">Contrato</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">
                    Configure o texto do contrato que será enviado aos seus clientes
                </p>
            </div>
            <div style="padding: 24px;">
                <div class="form-group">
                    <label class="form-label">Texto do Contrato</label>
                    <textarea
                        id="configClausulasContratuais"
                        class="form-input"
                        rows="15"
                        style="font-family: 'IBM Plex Mono', monospace; font-size: 13px; line-height: 1.6;"
                        placeholder="Digite aqui as cláusulas do contrato..."></textarea>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">
                        Dica: Use placeholders como [DATA], [VALOR], [CLIENTE] que serão substituídos automaticamente
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                    <button onclick="salvarClausulasContratuais()" class="btn btn-primary">
                        Salvar Contrato
                    </button>
                    <button onclick="resetarClausulas()" class="btn btn-secondary">
                        Resetar para Padrão
                    </button>
                </div>
            </div>
        </div>

        <!-- Assinatura Digital -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Assinatura Digital</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">
                    Faça upload da sua assinatura para que ela apareça automaticamente nos documentos gerados
                </p>
            </div>
            <div style="padding: 24px;">
                <!-- Área clicável da assinatura -->
                <div id="previewAssinatura" onclick="document.getElementById('uploadAssinatura').click()" style="cursor:pointer; min-height: 120px; background: rgba(255,255,255,0.05); border: 2px dashed var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 24px; transition: border-color 0.2s; margin-bottom: 16px;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                    <div id="assinaturaPlaceholder" style="text-align: center; color: var(--text-secondary);">
                        <div style="font-size: 36px; margin-bottom: 10px;">✍️</div>
                        <div style="font-size: 14px;">Nenhuma assinatura carregada</div>
                        <div style="font-size: 12px; margin-top: 6px;">Clique aqui ou no botão abaixo para adicionar</div>
                    </div>
                    <img id="imgPreviewAssinatura" style="max-width: 300px; max-height: 80px; object-fit: contain; display: none;">
                </div>

                <div style="text-align: center;">
                    <label class="btn btn-primary" style="margin-right: 12px; cursor: pointer; display: inline-block;">
                        Escolher Assinatura
                        <input type="file" id="uploadAssinatura" accept="image/png,image/jpeg,image/jpg" style="width:0;height:0;opacity:0;position:absolute;overflow:hidden;" onchange="carregarAssinatura(event)">
                    </label>
                    <button onclick="removerAssinatura()" class="btn" style="background: rgba(239, 71, 111, 0.2); color: var(--danger); border: 1px solid var(--danger);">
                        Remover Assinatura
                    </button>
                </div>

                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 16px; text-align: center;">
                    Formatos aceitos: PNG, JPG, JPEG &nbsp;|&nbsp; Recomendado: fundo transparente (PNG) &nbsp;|&nbsp; Tamanho máximo: 2MB
                </div>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <!-- ===== GERENCIAMENTO DE USUÁRIOS (ADMIN) ===== -->
    <div id="configUsuariosSecao" style="display: none;">
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 class="card-title">Gerenciamento de Usuários</h2>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Crie, gerencie e controle o acesso dos usuários do sistema</p>
                </div>
                <button class="btn btn-primary" onclick="abrirModalNovoUsuario()">+ Novo Usuário</button>
            </div>
            <div style="padding: 24px;">
                <!-- Stats -->
                <div id="usersStats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px;"></div>
                <!-- Lista de Usuários -->
                <div id="usersListContainer">
                    <div style="text-align: center; padding: 40px; color: var(--text-secondary);">Carregando usuários...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Criar/Editar Usuário -->
    <div id="modalUsuario" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card); border-radius: 16px; padding: 32px; width: 90%; max-width: 520px; max-height: 90vh; overflow-y: auto; border: 1px solid var(--border);">
            <h3 id="modalUsuarioTitulo" style="margin-bottom: 20px; font-size: 18px;">Novo Usuário</h3>
            <input type="hidden" id="editUserId">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Nome *</label>
                <input type="text" class="form-input" id="userNome" required placeholder="Nome completo">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Email *</label>
                <input type="email" class="form-input" id="userEmail" required placeholder="email@exemplo.com">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" id="labelSenha">Senha *</label>
                <input type="password" class="form-input" id="userSenha" placeholder="Mínimo 6 caracteres" minlength="6">
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Telefone</label>
                <input type="text" class="form-input" id="userTelefone" placeholder="(00) 00000-0000">
            </div>
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Plano</label>
                    <select class="form-input" id="userPlano">
                        <option value="teste">Teste (7 dias)</option>
                        <option value="mensal">Mensal</option>
                        <option value="anual">Anual</option>
                        <option value="vitalicio">Vitalício</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select class="form-input" id="userRole">
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                <button class="btn btn-secondary" onclick="fecharModalUsuario()">Cancelar</button>
                <button class="btn btn-primary" onclick="salvarUsuario()">Salvar</button>
            </div>
        </div>
    </div>

    <!-- Modal Estatísticas do Usuário -->
    <div id="modalUserStats" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: var(--bg-card); border-radius: 16px; padding: 32px; width: 90%; max-width: 480px; border: 1px solid var(--border);">
            <h3 id="statsUserNome" style="margin-bottom: 20px; font-size: 18px;">Estatísticas</h3>
            <div id="statsContent" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;"></div>
            <div style="text-align: right; margin-top: 24px;">
                <button class="btn btn-secondary" onclick="document.getElementById('modalUserStats').style.display='none'">Fechar</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== LOGO ===== -->
    <div id="configLogoSecao" style="display: none;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Logo da Empresa</h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">
                    Adicione a logo da sua empresa para usar em orçamentos e documentos
                </p>
            </div>
            <div style="padding: 24px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div id="logoPreview" onclick="document.getElementById('logoUpload').click()" style="cursor:pointer; min-height: 200px; background: rgba(255, 255, 255, 0.05); border: 2px dashed var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; padding: 40px; transition: border-color 0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                        <div id="logoPlaceholder" style="text-align: center; color: var(--text-secondary);">
                            <div style="font-size: 48px; margin-bottom: 16px;">🎨</div>
                            <div style="font-size: 14px;">Nenhuma logo carregada</div>
                            <div style="font-size: 12px; margin-top: 8px;">Clique aqui ou no botão abaixo para adicionar</div>
                        </div>
                        <img id="logoImage" style="max-width: 100%; max-height: 300px; display: none; border-radius: 8px;">
                    </div>
                </div>

                <div style="text-align: center;">
                    <label class="btn btn-primary" style="margin-right: 12px; cursor: pointer; display: inline-block;">
                        Carregar Logo
                        <input type="file" id="logoUpload" accept="image/*" style="width:0;height:0;opacity:0;position:absolute;overflow:hidden;" onchange="handleLogoUpload(event)">
                    </label>
                    <button onclick="removerLogo()" class="btn" style="background: rgba(239, 71, 111, 0.2); color: var(--danger); border: 1px solid var(--danger);">
                        Remover Logo
                    </button>
                </div>

                <div style="background: rgba(255, 210, 63, 0.1); padding: 16px; border-radius: 8px; margin-top: 24px; border-left: 4px solid var(--accent);">
                    <strong style="color: var(--accent);">Requisitos da Imagem:</strong>
                    <ul style="margin: 8px 0 0 20px; font-size: 13px; color: var(--text-secondary);">
                        <li>Formato: JPG, PNG ou SVG</li>
                        <li>Tamanho máximo: 2MB</li>
                        <li>Recomendado: Fundo transparente (PNG)</li>
                        <li>Proporção ideal: 2:1 (horizontal)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== NAVEGAÇÃO ENTRE SEÇÕES =====
    function mostrarConfigSecao(secao) {
        document.getElementById('configPerfilSecao').style.display = 'none';
        document.getElementById('configDocumentosSecao').style.display = 'none';
        document.getElementById('configLogoSecao').style.display = 'none';
        const secaoUsuarios = document.getElementById('configUsuariosSecao');
        if (secaoUsuarios) secaoUsuarios.style.display = 'none';

        document.getElementById('btnConfigPerfil').className = 'btn btn-secondary';
        document.getElementById('btnConfigDocumentos').className = 'btn btn-secondary';
        document.getElementById('btnConfigLogo').className = 'btn btn-secondary';
        const btnUsuarios = document.getElementById('btnConfigUsuarios');
        if (btnUsuarios) btnUsuarios.className = 'btn btn-secondary';

        if (secao === 'perfil') {
            document.getElementById('configPerfilSecao').style.display = 'block';
            document.getElementById('btnConfigPerfil').className = 'btn btn-primary';
        } else if (secao === 'documentos') {
            document.getElementById('configDocumentosSecao').style.display = 'block';
            document.getElementById('btnConfigDocumentos').className = 'btn btn-primary';
            carregarAssinaturaPreview();
        } else if (secao === 'logo') {
            document.getElementById('configLogoSecao').style.display = 'block';
            document.getElementById('btnConfigLogo').className = 'btn btn-primary';
        } else if (secao === 'usuarios') {
            if (secaoUsuarios) secaoUsuarios.style.display = 'block';
            if (btnUsuarios) btnUsuarios.className = 'btn btn-primary';
            carregarUsuarios();
        }
    }

    // ===== CARREGAR CONFIGURAÇÕES =====
    async function carregarConfigEmpresa() {
        try {
            const response = await fetch('../api/configuracoes.php?action=get');
            const data = await response.json();

            if (data.success && data.config && data.config.id) {
                const c = data.config;
                document.getElementById('configNomeEmpresa').value = c.nome_empresa || '';
                document.getElementById('configNomeCorporativo').value = c.nome_corporativo || '';
                document.getElementById('configCNPJ').value = c.cnpj || '';
                document.getElementById('configTelefone').value = c.telefone || '';
                document.getElementById('configWhatsapp').value = c.whatsapp || '';
                document.getElementById('configEmail').value = c.email || '';
                document.getElementById('configEndereco').value = c.endereco || '';
                document.getElementById('configCep').value = c.cep || '';
                document.getElementById('configCidade').value = c.cidade || '';
                document.getElementById('configEstado').value = c.estado || '';
                document.getElementById('configInstagram').value = c.instagram || '';
                document.getElementById('configFacebook').value = c.facebook || '';
                document.getElementById('configYoutube').value = c.youtube || '';
                document.getElementById('configSite').value = c.site || '';
                document.getElementById('configClausulasContratuais').value = c.clausulas_contratuais || '';

                // Aplicar formatação
                formatCNPJ(document.getElementById('configCNPJ'));
                formatTelefone(document.getElementById('configTelefone'));
                formatTelefone(document.getElementById('configWhatsapp'));

                // Logo
                if (c.logo_base64) {
                    document.getElementById('logoImage').src = c.logo_base64;
                    document.getElementById('logoImage').style.display = 'block';
                    document.getElementById('logoPlaceholder').style.display = 'none';
                }

                // Assinatura
                if (c.assinatura_base64) {
                    document.getElementById('imgPreviewAssinatura').src = c.assinatura_base64;
                    document.getElementById('imgPreviewAssinatura').style.display = 'block';
                    const placeholder = document.getElementById('assinaturaPlaceholder');
                    if (placeholder) placeholder.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Erro ao carregar configurações:', error);
        }
    }

    // ===== SALVAR PERFIL =====
    async function salvarConfigEmpresa(e) {
        e.preventDefault();

        try {
            const formData = new FormData();
            formData.append('action', 'save_profile');
            formData.append('nome_empresa', document.getElementById('configNomeEmpresa').value);
            formData.append('nome_corporativo', document.getElementById('configNomeCorporativo').value);
            formData.append('cnpj', document.getElementById('configCNPJ').value);
            formData.append('telefone', document.getElementById('configTelefone').value);
            formData.append('whatsapp', document.getElementById('configWhatsapp').value);
            formData.append('email', document.getElementById('configEmail').value);
            formData.append('endereco', document.getElementById('configEndereco').value);
            formData.append('cep', document.getElementById('configCep').value);
            formData.append('cidade', document.getElementById('configCidade').value);
            formData.append('estado', document.getElementById('configEstado').value);
            formData.append('instagram', document.getElementById('configInstagram').value);
            formData.append('facebook', document.getElementById('configFacebook').value);
            formData.append('youtube', document.getElementById('configYoutube').value);
            formData.append('site', document.getElementById('configSite').value);

            const response = await fetch('../api/configuracoes.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Configurações salvas com sucesso!');
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao salvar configurações');
        }
    }

    // ===== SALVAR CONTRATO =====
    async function salvarClausulasContratuais() {
        try {
            const formData = new FormData();
            formData.append('action', 'save_contract');
            formData.append('clausulas_contratuais', document.getElementById('configClausulasContratuais').value);

            const response = await fetch('../api/configuracoes.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Cláusulas contratuais salvas com sucesso!');
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao salvar contrato');
        }
    }

    // ===== RESETAR CLÁUSULAS =====
    function resetarClausulas() {
        const textoDefault = `CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE EVENTOS

1. DO OBJETO
O presente contrato tem por objeto a prestação de serviços de [TIPO DE SERVIÇO] pela CONTRATADA em favor do CONTRATANTE.

2. DAS CONDIÇÕES
2.1. Data do Evento: [DATA]
2.2. Horário: [HORÁRIO]
2.3. Local: [ENDEREÇO]

3. DO VALOR E FORMA DE PAGAMENTO
3.1. O valor total do serviço é de R$ [VALOR]
3.2. Sinal de R$ [SINAL] pago em [DATA_SINAL]
3.3. Restante de R$ [RESTANTE] até [VENCIMENTO]

4. DAS OBRIGAÇÕES DA CONTRATADA
4.1. Fornecer os serviços contratados com qualidade e pontualidade
4.2. Disponibilizar os equipamentos e materiais necessários

5. DAS OBRIGAÇÕES DO CONTRATANTE
5.1. Efetuar os pagamentos nas datas acordadas
5.2. Fornecer as informações necessárias para execução do serviço

6. DO CANCELAMENTO
6.1. Em caso de cancelamento pela CONTRATANTE com até 30 dias de antecedência, haverá devolução de 50% do sinal
6.2. Cancelamentos com menos de 30 dias não haverá devolução do sinal

7. DISPOSIÇÕES GERAIS
Este contrato é regido pelas leis brasileiras.

Data: [DATA_CONTRATO]

_________________________________
CONTRATADA

_________________________________
CONTRATANTE`;

        if (confirm('Deseja resetar as cláusulas para o texto padrão?')) {
            document.getElementById('configClausulasContratuais').value = textoDefault;
        }
    }

    // ===== LOGO =====
    function handleLogoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('Arquivo muito grande! Máximo 2MB.');
            return;
        }

        if (!file.type.match('image.*')) {
            alert('Apenas imagens são permitidas!');
            return;
        }

        const reader = new FileReader();
        reader.onload = async function(e) {
            const base64 = e.target.result;

            document.getElementById('logoImage').src = base64;
            document.getElementById('logoImage').style.display = 'block';
            document.getElementById('logoPlaceholder').style.display = 'none';

            // Salvar no banco
            try {
                const formData = new FormData();
                formData.append('action', 'save_logo');
                formData.append('logo_base64', base64);

                const response = await fetch('../api/configuracoes.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Logo carregada com sucesso!');
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar logo');
            }
        };
        reader.readAsDataURL(file);
    }

    async function removerLogo() {
        if (!confirm('Deseja realmente remover a logo?')) return;

        document.getElementById('logoImage').style.display = 'none';
        document.getElementById('logoPlaceholder').style.display = 'block';
        document.getElementById('logoImage').src = '';
        document.getElementById('logoUpload').value = '';

        try {
            const formData = new FormData();
            formData.append('action', 'remove_logo');

            await fetch('../api/configuracoes.php', {
                method: 'POST',
                body: formData
            });

            alert('Logo removida!');
        } catch (error) {
            console.error('Erro:', error);
        }
    }

    // ===== ASSINATURA =====
    function carregarAssinaturaPreview() {
        // Já carregada no carregarConfigEmpresa
    }

    function carregarAssinatura(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.match('image/png') && !file.type.match('image/jpeg') && !file.type.match('image/jpg')) {
            alert('Por favor, selecione uma imagem PNG, JPG ou JPEG');
            event.target.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('A imagem deve ter no máximo 2MB');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = async function(e) {
            const base64 = e.target.result;

            // Mostrar preview e ocultar placeholder
            document.getElementById('imgPreviewAssinatura').src = base64;
            document.getElementById('imgPreviewAssinatura').style.display = 'block';
            const placeholder = document.getElementById('assinaturaPlaceholder');
            if (placeholder) placeholder.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('action', 'save_signature');
                formData.append('assinatura_base64', base64);

                const response = await fetch('../api/configuracoes.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert('Assinatura salva com sucesso!');
                } else {
                    alert('Erro: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao salvar assinatura');
            }
        };
        reader.readAsDataURL(file);
    }

    async function removerAssinatura() {
        if (!confirm('Tem certeza que deseja remover a assinatura?')) return;

        document.getElementById('uploadAssinatura').value = '';
        document.getElementById('imgPreviewAssinatura').src = '';
        document.getElementById('imgPreviewAssinatura').style.display = 'none';
        const placeholder = document.getElementById('assinaturaPlaceholder');
        if (placeholder) placeholder.style.display = 'block';

        try {
            const formData = new FormData();
            formData.append('action', 'remove_signature');

            await fetch('../api/configuracoes.php', {
                method: 'POST',
                body: formData
            });

            alert('Assinatura removida!');
        } catch (error) {
            console.error('Erro:', error);
        }
    }

    // ===== FORMATAÇÃO =====
    function formatCNPJ(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            input.value = value;
        }
    }

    function formatTelefone(input) {
        let digits = (input.value || '').replace(/\D/g, '');
        if (digits.length > 11) digits = digits.substring(0, 11);

        if (digits.length <= 2) {
            input.value = digits;
        } else if (digits.length <= 6) {
            input.value = '(' + digits.substring(0, 2) + ') ' + digits.substring(2);
        } else if (digits.length <= 10) {
            input.value = '(' + digits.substring(0, 2) + ') ' + digits.substring(2, 6) + '-' + digits.substring(6);
        } else {
            input.value = '(' + digits.substring(0, 2) + ') ' + digits.substring(2, 7) + '-' + digits.substring(7);
        }
    }

    // ===== GERENCIAMENTO DE USUÁRIOS (ADMIN) =====
    let todosUsuarios = [];

    async function carregarUsuarios() {
        try {
            const response = await fetch('../api/usuarios.php?action=list');
            const data = await response.json();

            if (!data.success) {
                document.getElementById('usersListContainer').innerHTML = '<div style="text-align:center;padding:20px;color:#EF476F;">Erro: ' + data.message + '</div>';
                return;
            }

            todosUsuarios = data.usuarios || [];
            renderizarUsuarios();
        } catch (error) {
            console.error('Erro ao carregar usuários:', error);
        }
    }

    function renderizarUsuarios() {
        const container = document.getElementById('usersListContainer');
        const stats = document.getElementById('usersStats');

        // Stats
        const total = todosUsuarios.length;
        const ativos = todosUsuarios.filter(u => u.status === 'ativo').length;
        const bloqueados = todosUsuarios.filter(u => u.status === 'bloqueado').length;
        const expirados = todosUsuarios.filter(u => u.status === 'expirado').length;

        stats.innerHTML = `
            <div style="background: rgba(6, 214, 160, 0.1); padding: 16px; border-radius: 10px; text-align: center; border: 1px solid rgba(6, 214, 160, 0.3);">
                <div style="font-size: 24px; font-weight: 800; color: #06D6A0;">${total}</div>
                <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase;">Total</div>
            </div>
            <div style="background: rgba(79, 70, 229, 0.1); padding: 16px; border-radius: 10px; text-align: center; border: 1px solid rgba(79, 70, 229, 0.3);">
                <div style="font-size: 24px; font-weight: 800; color: #4F46E5;">${ativos}</div>
                <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase;">Ativos</div>
            </div>
            <div style="background: rgba(239, 71, 111, 0.1); padding: 16px; border-radius: 10px; text-align: center; border: 1px solid rgba(239, 71, 111, 0.3);">
                <div style="font-size: 24px; font-weight: 800; color: #EF476F;">${bloqueados}</div>
                <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase;">Bloqueados</div>
            </div>
            <div style="background: rgba(255, 107, 53, 0.1); padding: 16px; border-radius: 10px; text-align: center; border: 1px solid rgba(255, 107, 53, 0.3);">
                <div style="font-size: 24px; font-weight: 800; color: #FF6B35;">${expirados}</div>
                <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase;">Expirados</div>
            </div>
        `;

        if (todosUsuarios.length === 0) {
            container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-secondary);">Nenhum usuário cadastrado</div>';
            return;
        }

        let html = '<div style="overflow-x: auto;"><table style="width:100%;border-collapse:collapse;font-size:13px;">';
        html += '<thead><tr style="border-bottom:2px solid var(--border);text-align:left;">';
        html += '<th style="padding:12px 8px;">Nome</th>';
        html += '<th style="padding:12px 8px;">Email</th>';
        html += '<th style="padding:12px 8px;">Plano</th>';
        html += '<th style="padding:12px 8px;">Status</th>';
        html += '<th style="padding:12px 8px;">Dados</th>';
        html += '<th style="padding:12px 8px;">Último Acesso</th>';
        html += '<th style="padding:12px 8px;text-align:center;">Ações</th>';
        html += '</tr></thead><tbody>';

        todosUsuarios.forEach(u => {
            const statusColors = {ativo:'#06D6A0', bloqueado:'#EF476F', expirado:'#FF6B35'};
            const planoLabels = {teste:'🧪 Teste', mensal:'📅 Mensal', anual:'📆 Anual', vitalicio:'♾️ Vitalício'};
            const roleIcon = u.role === 'admin' ? '👑' : '👤';
            const diasRestantes = u.dias_restantes !== null && u.dias_restantes !== undefined
                ? (u.dias_restantes > 0 ? `<span style="color:#06D6A0;">${u.dias_restantes}d</span>` : `<span style="color:#EF476F;">Expirado</span>`)
                : '';
            const ultimoAcesso = u.ultimo_acesso ? new Date(u.ultimo_acesso).toLocaleDateString('pt-BR') : 'Nunca';

            html += `<tr style="border-bottom:1px solid var(--border);">`;
            html += `<td style="padding:10px 8px;">${roleIcon} ${u.nome}</td>`;
            html += `<td style="padding:10px 8px;color:var(--text-secondary);">${u.email}</td>`;
            html += `<td style="padding:10px 8px;">${planoLabels[u.plano] || u.plano} ${diasRestantes}</td>`;
            html += `<td style="padding:10px 8px;"><span style="background:${statusColors[u.status] || '#6B7280'}22;color:${statusColors[u.status] || '#6B7280'};padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;">${u.status}</span></td>`;
            html += `<td style="padding:10px 8px;font-size:11px;color:var(--text-secondary);">${u.total_eventos || 0} eventos<br>${u.total_clientes || 0} clientes</td>`;
            html += `<td style="padding:10px 8px;font-size:11px;color:var(--text-secondary);">${ultimoAcesso}</td>`;
            html += `<td style="padding:10px 8px;text-align:center;">`;
            html += `<div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">`;
            html += `<button onclick="editarUsuario(${u.id})" style="background:rgba(79,70,229,0.2);color:#4F46E5;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:11px;">✏️ Editar</button>`;
            html += `<button onclick="verEstatisticas(${u.id})" style="background:rgba(6,214,160,0.2);color:#06D6A0;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:11px;">📊 Stats</button>`;

            if (u.id != <?php echo $userId; ?>) {
                const toggleLabel = u.status === 'ativo' ? '🔒 Bloquear' : '🔓 Desbloquear';
                const toggleColor = u.status === 'ativo' ? 'rgba(255,107,53,0.2)' : 'rgba(6,214,160,0.2)';
                const toggleTextColor = u.status === 'ativo' ? '#FF6B35' : '#06D6A0';
                html += `<button onclick="toggleStatusUsuario(${u.id})" style="background:${toggleColor};color:${toggleTextColor};border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:11px;">${toggleLabel}</button>`;
                html += `<button onclick="deletarUsuario(${u.id},'${u.nome.replace(/'/g, "\\'")}')" style="background:rgba(239,71,111,0.2);color:#EF476F;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;font-size:11px;">🗑️</button>`;
            }

            html += `</div></td></tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function abrirModalNovoUsuario() {
        document.getElementById('editUserId').value = '';
        document.getElementById('userNome').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userSenha').value = '';
        document.getElementById('userTelefone').value = '';
        document.getElementById('userPlano').value = 'teste';
        document.getElementById('userRole').value = 'usuario';
        document.getElementById('modalUsuarioTitulo').textContent = 'Novo Usuário';
        document.getElementById('labelSenha').textContent = 'Senha *';
        document.getElementById('userSenha').required = true;
        document.getElementById('modalUsuario').style.display = 'flex';
    }

    function fecharModalUsuario() {
        document.getElementById('modalUsuario').style.display = 'none';
    }

    function editarUsuario(id) {
        const u = todosUsuarios.find(u => u.id == id);
        if (!u) return;

        document.getElementById('editUserId').value = u.id;
        document.getElementById('userNome').value = u.nome;
        document.getElementById('userEmail').value = u.email;
        document.getElementById('userSenha').value = '';
        document.getElementById('userTelefone').value = u.telefone || '';
        document.getElementById('userPlano').value = u.plano;
        document.getElementById('userRole').value = u.role;
        document.getElementById('modalUsuarioTitulo').textContent = 'Editar Usuário';
        document.getElementById('labelSenha').textContent = 'Nova Senha (deixe vazio para manter)';
        document.getElementById('userSenha').required = false;
        document.getElementById('modalUsuario').style.display = 'flex';
    }

    async function salvarUsuario() {
        const id = document.getElementById('editUserId').value;
        const nome = document.getElementById('userNome').value.trim();
        const email = document.getElementById('userEmail').value.trim();
        const senha = document.getElementById('userSenha').value;
        const telefone = document.getElementById('userTelefone').value.trim();
        const plano = document.getElementById('userPlano').value;
        const role = document.getElementById('userRole').value;

        if (!nome || !email) {
            alert('Nome e email são obrigatórios');
            return;
        }
        if (!id && !senha) {
            alert('Senha é obrigatória para novo usuário');
            return;
        }

        const formData = new FormData();
        formData.append('action', id ? 'update' : 'create');
        formData.append('nome', nome);
        formData.append('email', email);
        formData.append('telefone', telefone);
        formData.append('plano', plano);
        formData.append('role', role);

        if (id) {
            formData.append('id', id);
            if (senha) formData.append('nova_senha', senha);
        } else {
            formData.append('senha', senha);
        }

        try {
            const response = await fetch('../api/usuarios.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                fecharModalUsuario();
                carregarUsuarios();
                alert(data.message);
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro de conexão');
        }
    }

    async function toggleStatusUsuario(id) {
        if (!confirm('Deseja alterar o status deste usuário?')) return;

        const formData = new FormData();
        formData.append('action', 'toggle_status');
        formData.append('id', id);

        try {
            const response = await fetch('../api/usuarios.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                carregarUsuarios();
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro de conexão');
        }
    }

    async function deletarUsuario(id, nome) {
        if (!confirm(`ATENÇÃO: Deseja realmente excluir o usuário "${nome}"?\n\nTODOS os dados (eventos, clientes, financeiro) serão removidos permanentemente!\n\nEsta ação NÃO pode ser desfeita.`)) return;
        if (!confirm(`Confirme novamente: EXCLUIR "${nome}" e TODOS os seus dados?`)) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        try {
            const response = await fetch('../api/usuarios.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                carregarUsuarios();
                alert(data.message);
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            alert('Erro de conexão');
        }
    }

    async function verEstatisticas(id) {
        const u = todosUsuarios.find(u => u.id == id);

        try {
            const response = await fetch(`../api/usuarios.php?action=stats&id=${id}`);
            const data = await response.json();

            if (data.success) {
                const s = data.stats;
                document.getElementById('statsUserNome').textContent = `📊 Estatísticas - ${u ? u.nome : 'Usuário'}`;
                document.getElementById('statsContent').innerHTML = `
                    <div style="background:rgba(79,70,229,0.1);padding:16px;border-radius:10px;text-align:center;">
                        <div style="font-size:22px;font-weight:800;color:#4F46E5;">${s.eventos}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Eventos</div>
                    </div>
                    <div style="background:rgba(6,214,160,0.1);padding:16px;border-radius:10px;text-align:center;">
                        <div style="font-size:22px;font-weight:800;color:#06D6A0;">${s.clientes}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Clientes</div>
                    </div>
                    <div style="background:rgba(255,107,53,0.1);padding:16px;border-radius:10px;text-align:center;">
                        <div style="font-size:22px;font-weight:800;color:#FF6B35;">${s.servicos}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Serviços</div>
                    </div>
                    <div style="background:rgba(168,85,247,0.1);padding:16px;border-radius:10px;text-align:center;">
                        <div style="font-size:22px;font-weight:800;color:#A855F7;">${s.movimentacoes}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Movimentações</div>
                    </div>
                    <div style="background:rgba(6,214,160,0.1);padding:16px;border-radius:10px;text-align:center;">
                        <div style="font-size:16px;font-weight:800;color:#06D6A0;">R$ ${parseFloat(s.receita_total).toLocaleString('pt-BR',{minimumFractionDigits:2})}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Receitas</div>
                    </div>
                    <div style="background:rgba(239,71,111,0.1);padding:16px;border-radius:10px;text-align:center;">
                        <div style="font-size:16px;font-weight:800;color:#EF476F;">R$ ${parseFloat(s.despesa_total).toLocaleString('pt-BR',{minimumFractionDigits:2})}</div>
                        <div style="font-size:11px;color:var(--text-secondary);">Despesas</div>
                    </div>
                `;
                document.getElementById('modalUserStats').style.display = 'flex';
            }
        } catch (error) {
            alert('Erro ao carregar estatísticas');
        }
    }

    // Fechar modais ao clicar fora
    document.addEventListener('click', function(e) {
        if (e.target.id === 'modalUsuario') fecharModalUsuario();
        if (e.target.id === 'modalUserStats') e.target.style.display = 'none';
    });

    // ===== INICIALIZAÇÃO =====
    document.addEventListener('DOMContentLoaded', () => {
        carregarConfigEmpresa();
    });
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
