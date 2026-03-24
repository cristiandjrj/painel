<?php
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
if ($currentDir === 'pages') {
    $menuPagesPath = '';
    $menuRootPath  = '../';
} else {
    $menuPagesPath = 'pages/';
    $menuRootPath  = '';
}
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>

<!-- Overlay escuro ao abrir menu mobile -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>

<!-- Sidebar deslizante (mobile) -->
<nav class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <div class="logo" style="font-size: 22px; pointer-events: none;">
            EventProDJ
            <div style="font-size: 13px; font-weight: 500; color: var(--primary); opacity: 0.9; margin-top: 2px; letter-spacing: 1px;">
                by Cristian Dj
            </div>
        </div>
        <button class="mobile-menu-close" onclick="closeMobileMenu()">✕</button>
    </div>

    <div class="mobile-nav">
        <a href="<?= $menuPagesPath ?>dashboard.php"           class="nav-tab <?= $currentPage==='dashboard.php'           ? 'active':'' ?>">🏢 Dashboard</a>
        <a href="<?= $menuPagesPath ?>clientes-eventpro.php"   class="nav-tab <?= $currentPage==='clientes-eventpro.php'   ? 'active':'' ?>">👥 Clientes</a>
        <a href="<?= $menuPagesPath ?>eventos-eventpro.php"    class="nav-tab <?= $currentPage==='eventos-eventpro.php'    ? 'active':'' ?>">📅 Eventos</a>
        <a href="<?= $menuPagesPath ?>servicos-eventpro.php"   class="nav-tab <?= $currentPage==='servicos-eventpro.php'   ? 'active':'' ?>">📦 Serviços</a>
        <a href="<?= $menuPagesPath ?>custos-eventpro.php"     class="nav-tab <?= $currentPage==='custos-eventpro.php'     ? 'active':'' ?>">💸 Custos</a>
        <a href="<?= $menuPagesPath ?>financeiro-dashboard.php" class="nav-tab <?= $currentPage==='financeiro-dashboard.php'? 'active':'' ?>">💰 Financeiro</a>
        <a href="<?= $menuPagesPath ?>pessoal-eventpro.php"    class="nav-tab <?= $currentPage==='pessoal-eventpro.php'    ? 'active':'' ?>">👤 Pessoal</a>
        <a href="<?= $menuPagesPath ?>mei-eventpro.php"        class="nav-tab <?= $currentPage==='mei-eventpro.php'        ? 'active':'' ?>">📋 MEI</a>
        <a href="<?= $menuPagesPath ?>bot-whatsapp.php"      class="nav-tab <?= $currentPage==='bot-whatsapp.php'      ? 'active':'' ?>">🤖 Bot</a>
        <a href="<?= $menuPagesPath ?>configuracoes.php"       class="nav-tab <?= $currentPage==='configuracoes.php'       ? 'active':'' ?>">⚙️ Configurações</a>
        <a href="<?= $menuRootPath ?>logout.php" class="nav-tab" style="color: #EF476F; margin-top: 10px; border-top: 1px solid var(--border); padding-top: 16px;">🚪 Sair</a>
    </div>
</nav>

<!-- Header principal -->
<header>
    <div class="header-content">
        <!-- Botão hamburger (visível só no mobile) -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Abrir menu">☰</button>

        <!-- Logo centralizada no mobile, normal no desktop -->
        <a href="<?= $menuPagesPath ?>dashboard.php" class="logo" style="text-decoration:none;">
            EventProDJ
            <div style="font-family: cursive; font-size: 13px; font-weight: 500; color: var(--primary); opacity: 0.9; margin-top: 1px; letter-spacing: 1px;">
                by Cristian Dj
            </div>
        </a>

        <!-- Navegação desktop -->
        <nav class="main-nav">
            <a href="<?= $menuPagesPath ?>dashboard.php"            class="nav-tab <?= $currentPage==='dashboard.php'            ? 'active':'' ?>">🏢 Dashboard</a>
            <a href="<?= $menuPagesPath ?>clientes-eventpro.php"    class="nav-tab <?= $currentPage==='clientes-eventpro.php'    ? 'active':'' ?>">👥 Clientes</a>
            <a href="<?= $menuPagesPath ?>eventos-eventpro.php"     class="nav-tab <?= $currentPage==='eventos-eventpro.php'     ? 'active':'' ?>">📅 Eventos</a>
            <a href="<?= $menuPagesPath ?>servicos-eventpro.php"    class="nav-tab <?= $currentPage==='servicos-eventpro.php'    ? 'active':'' ?>">📦 Serviços</a>
            <a href="<?= $menuPagesPath ?>custos-eventpro.php"      class="nav-tab <?= $currentPage==='custos-eventpro.php'      ? 'active':'' ?>">💸 Custos</a>
            <a href="<?= $menuPagesPath ?>financeiro-dashboard.php" class="nav-tab <?= $currentPage==='financeiro-dashboard.php' ? 'active':'' ?>">💰 Financeiro</a>
            <a href="<?= $menuPagesPath ?>pessoal-eventpro.php"     class="nav-tab <?= $currentPage==='pessoal-eventpro.php'     ? 'active':'' ?>">👤 Pessoal</a>
            <a href="<?= $menuPagesPath ?>mei-eventpro.php"         class="nav-tab <?= $currentPage==='mei-eventpro.php'         ? 'active':'' ?>">📋 MEI</a>
            <a href="<?= $menuPagesPath ?>bot-whatsapp.php"       class="nav-tab <?= $currentPage==='bot-whatsapp.php'       ? 'active':'' ?>">🤖 Bot</a>
            <a href="<?= $menuPagesPath ?>configuracoes.php"        class="nav-tab <?= $currentPage==='configuracoes.php'        ? 'active':'' ?>">⚙️ Config</a>
            <a href="<?= $menuRootPath ?>logout.php"                class="nav-tab" style="color:#EF476F;">🚪 Sair</a>
        </nav>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        const menu    = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileMenuOverlay');
        if (!menu || !overlay) return;
        const isOpen = menu.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    function closeMobileMenu() {
        const menu    = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileMenuOverlay');
        if (!menu || !overlay) return;
        menu.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Fechar menu ao pressionar ESC
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobileMenu(); });
</script>
