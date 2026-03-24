/**
 * Sistema de Temas - EventProDJ
 * Toggle entre tema claro e escuro
 */

// ============================================
// VARIÁVEIS CSS PARA TEMAS
// ============================================

const themes = {
    dark: {
        '--primary': '#FF6B35',
        '--primary-dark': '#E85A2B',
        '--secondary': '#004E89',
        '--accent': '#FFD23F',
        '--success': '#06D6A0',
        '--danger': '#EF476F',
        '--bg-dark': '#0A0E27',
        '--bg-card': '#141B3D',
        '--bg-card-hover': '#1A2249',
        '--text-primary': '#FFFFFF',
        '--text-secondary': '#A0AEC0',
        '--border': '#2D3561'
    },
    light: {
        '--primary': '#FF6B35',
        '--primary-dark': '#E85A2B',
        '--secondary': '#2563EB',
        '--accent': '#F59E0B',
        '--success': '#10B981',
        '--danger': '#EF4444',
        '--bg-dark': '#F8FAFC',
        '--bg-card': '#FFFFFF',
        '--bg-card-hover': '#F1F5F9',
        '--text-primary': '#0F172A',
        '--text-secondary': '#64748B',
        '--border': '#E2E8F0'
    }
};

// ============================================
// FUNÇÕES DO TEMA
// ============================================

function initTheme() {
    // Verificar tema salvo no localStorage
    const savedTheme = localStorage.getItem('eventprodj_theme') || 'dark';
    applyTheme(savedTheme);
    updateToggleButton(savedTheme);
}

function applyTheme(themeName) {
    const theme = themes[themeName];
    const root = document.documentElement;
    
    // Aplicar variáveis CSS
    Object.keys(theme).forEach(property => {
        root.style.setProperty(property, theme[property]);
    });
    
    // Adicionar classe no body
    document.body.setAttribute('data-theme', themeName);
    
    // Salvar preferência
    localStorage.setItem('eventprodj_theme', themeName);
}

function toggleTheme() {
    const currentTheme = document.body.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    applyTheme(newTheme);
    updateToggleButton(newTheme);
}

function updateToggleButton(themeName) {
    const button = document.getElementById('themeToggle');
    if (!button) return;
    
    if (themeName === 'dark') {
        button.innerHTML = '<i class="fas fa-sun"></i>';
        button.title = 'Ativar tema claro';
    } else {
        button.innerHTML = '<i class="fas fa-moon"></i>';
        button.title = 'Ativar tema escuro';
    }
}

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    initTheme();
});

// ============================================
// EXPORTAR FUNÇÕES
// ============================================

window.themeSystem = {
    init: initTheme,
    toggle: toggleTheme,
    apply: applyTheme
};
