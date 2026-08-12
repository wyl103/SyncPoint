// public/js/app.js
// Orquestador Principal de la Aplicación y Navegación entre Pestañas

const baseTag = document.querySelector('base');
const baseHref = baseTag ? baseTag.getAttribute('href') : '/';
const BASE_PATH = baseHref.endsWith('/') ? baseHref.slice(0, -1) : baseHref;
let fechaActualIso = '';
let tituloActual = '';

const currentPath = window.location.pathname;
const APP_ROOT = currentPath.includes('/app_bless') ? '/app_bless' : '';
const API_BASE = `${APP_ROOT}/app/api`;

// --- NAVEGACIÓN PRINCIPAL ENTRE PESTAÑAS ---
function switchTab(tabId) {
    ['tab-dashboard', 'tab-clientes'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden-view');
    });
    
    const targetEl = document.getElementById(`tab-${tabId}`);
    if (targetEl) targetEl.classList.remove('hidden-view');
    
    const titles = { dashboard: 'Eventos', clientes: 'Directorio de Clientes' };
    const headerTitle = document.getElementById('header-title');
    if (headerTitle && titles[tabId]) headerTitle.innerText = titles[tabId];

    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('text-charcoal', 'bg-primary/20');
        btn.classList.add('text-gray-400', 'text-gray-500');
        const icon = btn.querySelector('.material-symbols-outlined');
        if (icon) icon.classList.remove('filled');
    });
    
    document.querySelectorAll(`.nav-btn[data-target="${tabId}"]`).forEach(activeBtn => {
        activeBtn.classList.remove('text-gray-400', 'text-gray-500');
        activeBtn.classList.add('text-charcoal');
        if (activeBtn.classList.contains('w-full')) activeBtn.classList.add('bg-primary/20');
        const icon = activeBtn.querySelector('.material-symbols-outlined');
        if (icon) icon.classList.add('filled');
    });

    if (tabId === 'clientes') {
        cargarClientes();
    }
}

// Inicializador principal llamado al autenticarse o cargar la aplicación
function initApp() {
    cargarFiltrosDinamicos();
    setupBotonesDias();
}