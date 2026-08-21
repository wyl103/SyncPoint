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
    ['tab-dashboard', 'tab-clientes', 'tab-usuarios', 'tab-mensajes'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden-view');
    });
    
    const targetEl = document.getElementById(`tab-${tabId}`);
    if (targetEl) targetEl.classList.remove('hidden-view');
    
    const headerTitle = document.getElementById('header-title');
    const headerSubtabNav = document.getElementById('header-subtab-nav');
    const headerSubtabUsuariosNav = document.getElementById('header-subtab-usuarios-nav');

    if (tabId === 'clientes') {
        if (headerTitle) headerTitle.classList.add('hidden');
        if (headerSubtabUsuariosNav) headerSubtabUsuariosNav.classList.add('hidden');
        if (headerSubtabNav) headerSubtabNav.classList.remove('hidden');
        if (typeof cambiarSubTabCliente === 'function') {
            cambiarSubTabCliente('directorio');
        } else if (typeof cargarClientes === 'function') {
            cargarClientes();
        }
    } else if (tabId === 'usuarios') {
        if (headerTitle) headerTitle.classList.add('hidden');
        if (headerSubtabNav) headerSubtabNav.classList.add('hidden');
        if (headerSubtabUsuariosNav) headerSubtabUsuariosNav.classList.remove('hidden');
        if (typeof cambiarSubTabUsuario === 'function') {
            cambiarSubTabUsuario('directorio');
        } else if (typeof cargarUsuarios === 'function') {
            cargarUsuarios();
        }
    } else if (tabId === 'mensajes') {
        if (headerSubtabNav) headerSubtabNav.classList.add('hidden');
        if (headerSubtabUsuariosNav) headerSubtabUsuariosNav.classList.add('hidden');
        if (headerTitle) {
            headerTitle.classList.remove('hidden');
            headerTitle.innerText = 'Mensajes WhatsApp';
        }
        if (typeof cargarConversaciones === 'function') {
            cargarConversaciones(1);
        }
        if (typeof iniciarPollingBandejaMensajes === 'function') {
            iniciarPollingBandejaMensajes();
        }
    } else {
        if (typeof detenerPollingBandejaMensajes === 'function') {
            detenerPollingBandejaMensajes();
        }
        if (headerSubtabNav) headerSubtabNav.classList.add('hidden');
        if (headerSubtabUsuariosNav) headerSubtabUsuariosNav.classList.add('hidden');
        if (headerTitle) {
            headerTitle.classList.remove('hidden');
            headerTitle.innerText = tabId === 'dashboard' ? 'Eventos' : 'OilBless';
        }
        if (tabId === 'dashboard' && typeof recargarDiaActual === 'function') {
            recargarDiaActual();
        }
    }

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
}

// Inicializador principal llamado al autenticarse o cargar la aplicación
function initApp() {
    cargarFiltrosDinamicos();
    setupBotonesDias();
    if (typeof iniciarPollingGlobalNuevosMensajes === 'function') {
        iniciarPollingGlobalNuevosMensajes();
    }
}