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

function switchTab(tabId) {
    if (tabId === 'mensajes') {
        if (typeof abrirModalChatwoot === 'function') {
            abrirModalChatwoot(null);
        }
        return;
    }

    let mainTab = tabId;
    let targetSubTab = null;

    if (tabId === 'sucursales-rutas') {
        mainTab = 'clientes';
        targetSubTab = 'sucursales-rutas';
    } else if (tabId === 'clientes') {
        mainTab = 'clientes';
        targetSubTab = 'directorio';
    }

    ['tab-dashboard', 'tab-clientes', 'tab-usuarios', 'tab-mensajes'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden-view');
    });
    
    const targetEl = document.getElementById(`tab-${mainTab}`);
    if (targetEl) targetEl.classList.remove('hidden-view');
    
    const headerTitle = document.getElementById('header-title');
    const headerSubtabNav = document.getElementById('header-subtab-nav');
    const headerSubtabUsuariosNav = document.getElementById('header-subtab-usuarios-nav');

    if (mainTab === 'clientes') {
        if (headerTitle) headerTitle.classList.add('hidden');
        if (headerSubtabUsuariosNav) headerSubtabUsuariosNav.classList.add('hidden');
        if (headerSubtabNav) headerSubtabNav.classList.remove('hidden');
        if (targetSubTab === 'sucursales-rutas') {
            if (typeof cambiarSubTabCliente === 'function') {
                cambiarSubTabCliente('sucursales-rutas');
            }
        } else {
            if (typeof cambiarSubTabCliente === 'function') {
                cambiarSubTabCliente('directorio');
            } else if (typeof cargarClientes === 'function') {
                cargarClientes();
            }
        }
    } else if (mainTab === 'usuarios') {
        if (headerTitle) headerTitle.classList.add('hidden');
        if (headerSubtabNav) headerSubtabNav.classList.add('hidden');
        if (headerSubtabUsuariosNav) headerSubtabUsuariosNav.classList.remove('hidden');
        if (typeof cambiarSubTabUsuario === 'function') {
            cambiarSubTabUsuario('directorio');
        } else if (typeof cargarUsuarios === 'function') {
            cargarUsuarios();
        }
    } else if (mainTab === 'mensajes') {
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
            headerTitle.innerText = 'Centro de Operaciones';
        }
        if (mainTab === 'dashboard' && typeof recargarDiaActual === 'function') {
            recargarDiaActual();
        }
    }

    actualizarEstilosNavBtns(tabId, mainTab);
}

// Actualiza las clases visuales de los botones de navegación (desktop sidebar & mobile bottom nav)
function actualizarEstilosNavBtns(tabId, mainTab) {
    // 1. Sidebar desktop (botones con clase w-full)
    document.querySelectorAll('aside .nav-btn').forEach(btn => {
        const target = btn.getAttribute('data-target');
        const isMatched = (target === tabId) || (!tabId.includes('sucursales') && target === mainTab);
        const icon = btn.querySelector('.material-symbols-outlined');

        const textSpan = btn.querySelector('span:not(.material-symbols-outlined):not(#badge-nav-mensajes-sidebar)');

        if (isMatched) {
            btn.className = 'nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold text-sm shadow-sm shadow-emerald-600/20 transition group text-left cursor-pointer';
            if (icon) {
                icon.className = 'material-symbols-outlined text-[20px] text-white filled';
            }
            if (textSpan) {
                textSpan.className = 'text-white font-semibold';
            }
        } else {
            btn.className = 'nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 font-medium text-sm transition group text-left cursor-pointer';
            if (icon) {
                icon.className = 'material-symbols-outlined text-[20px] text-slate-400 group-hover:text-emerald-600 transition';
            }
            if (textSpan) {
                textSpan.className = '';
            }
        }
    });

    // 2. Mobile nav (barra inferior fija)
    document.querySelectorAll('.mobile-bottom-nav .nav-btn').forEach(btn => {
        const target = btn.getAttribute('data-target');
        const isMatched = (target === mainTab);
        const icon = btn.querySelector('.material-symbols-outlined');

        if (isMatched) {
            btn.className = 'nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-emerald-600 font-bold cursor-pointer';
            if (icon) {
                icon.className = 'material-symbols-outlined text-[20px] text-emerald-600 filled';
            }
        } else {
            btn.className = 'nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-emerald-700 cursor-pointer';
            if (icon) {
                icon.className = 'material-symbols-outlined text-[20px] text-slate-400';
            }
        }
    });
}
window.actualizarEstilosNavBtns = actualizarEstilosNavBtns;

// Buscador Global en el Header Superior
function setupGlobalSearch() {
    const globalSearch = document.getElementById('global-search-input');
    if (!globalSearch) return;

    let debounceTimer = null;
    globalSearch.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const val = e.target.value.trim();
            const tabClientes = document.getElementById('tab-clientes');
            const tabUsuarios = document.getElementById('tab-usuarios');
            const tabMensajes = document.getElementById('tab-mensajes');
            const tabDashboard = document.getElementById('tab-dashboard');

            if (tabClientes && !tabClientes.classList.contains('hidden-view')) {
                const subDirectorio = document.getElementById('subtab-directorio-clientes');
                const subSucRutas = document.getElementById('subtab-sucursales-rutas');

                if (subDirectorio && !subDirectorio.classList.contains('hidden-view')) {
                    const inputCliente = document.getElementById('input-buscar-cliente');
                    if (inputCliente) {
                        inputCliente.value = val;
                        if (typeof filtrarClientesDebounced === 'function') filtrarClientesDebounced();
                    }
                } else if (subSucRutas && !subSucRutas.classList.contains('hidden-view')) {
                    const inputSucRuta = document.getElementById('input-buscar-sucursal-ruta');
                    if (inputSucRuta) {
                        inputSucRuta.value = val;
                        if (typeof filtrarSucursalesYRutasDebounced === 'function') filtrarSucursalesYRutasDebounced();
                    }
                }
            } else if (tabUsuarios && !tabUsuarios.classList.contains('hidden-view')) {
                const inputUsuario = document.getElementById('input-buscar-usuario');
                if (inputUsuario) {
                    inputUsuario.value = val;
                    if (typeof filtrarUsuariosDebounced === 'function') filtrarUsuariosDebounced();
                }
            } else if (tabMensajes && !tabMensajes.classList.contains('hidden-view')) {
                const inputMsg = document.getElementById('input-buscar-conversacion');
                if (inputMsg) {
                    inputMsg.value = val;
                    if (typeof alBuscarConversacion === 'function') alBuscarConversacion();
                }
            }
        }, 250);
    });

    globalSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const val = globalSearch.value.trim();
            const tabDashboard = document.getElementById('tab-dashboard');
            if (tabDashboard && !tabDashboard.classList.contains('hidden-view') && val) {
                switchTab('clientes');
                const inputCliente = document.getElementById('input-buscar-cliente');
                if (inputCliente) {
                    inputCliente.value = val;
                    if (typeof filtrarClientesDebounced === 'function') filtrarClientesDebounced();
                }
            }
        }
    });
}

// Inicializador principal llamado al autenticarse o cargar la aplicación
function initApp() {
    cargarFiltrosDinamicos();
    setupBotonesDias();
    setupGlobalSearch();
    if (typeof iniciarPollingGlobalNuevosMensajes === 'function') {
        iniciarPollingGlobalNuevosMensajes();
    }
}