// public/js/modules/chatwoot.js
// Lógica del Modal de Chatwoot y Gestión Logística (3 Paneles):
// Panel 1: Lista de Conversaciones con Búsqueda y Filtros
// Panel 2: Historial del Chat de WhatsApp, Envío Libre y Plantillas HSM
// Panel 3: Ficha del Generador / Cliente y Tarjeta de Recolección con Acciones

let clienteChatActualId = null;
let conversationIdActual = null;
let plantillasDisponibles = [];
let clienteDatosActuales = null;
let mensajesChatwoot = [];
let todasLasConversacionesModal = [];
let filtroModalConversaciones = 'todos'; // 'todos' | 'atencion' | 'confirmados'
let busquedaModalConversaciones = '';
let plantillaSeleccionadaActual = null;
let pollingIntervalTimer = null;
let fastPollingTimer = null;
let isFetchingChatwoot = false;
let fastPollingRemainingTicks = 0;
let panelInfoAbiertoManualmente = false;
let panelChatsAbiertoManualmente = null; // null = auto segun ancho de pantalla
let estaCargandoConversacion = false; // Bandera para evitar clics concurrentes mientras carga un chat

/**
 * Nombres amigables para variables de plantilla
 */
const NOMBRES_VARIABLES_PLANTILLAS = {
    cliente: 'Nombre del Cliente',
    sucursal: 'Sucursal',
    ruta: 'Ruta / Zona',
    fecha: 'Fecha de Recolección',
    motivo: 'Motivo de Reprogramación'
};

/**
 * Helper para obtener iniciales del nombre de un cliente
 */
function obtenerIniciales(nombre) {
    if (!nombre) return 'WA';
    const partes = nombre.trim().split(/\s+/).filter(Boolean);
    if (partes.length === 0) return 'WA';
    if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
    return (partes[0][0] + partes[1][0]).toUpperCase();
}

/**
 * Helper para formatear hora o fecha de un mensaje en la lista de chats
 */
function formatHoraRelativaConversacion(timestamp) {
    if (!timestamp) return '';
    const date = (typeof timestamp === 'number' || !isNaN(timestamp)) 
        ? new Date(Number(timestamp) * 1000) 
        : new Date(timestamp);
    if (isNaN(date.getTime())) return '';

    const hoy = new Date();
    const ayer = new Date();
    ayer.setDate(hoy.getDate() - 1);

    if (date.toDateString() === hoy.toDateString()) {
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
    }
    if (date.toDateString() === ayer.toDateString()) {
        return 'Ayer';
    }
    return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
}

/**
 * Bloquea la interacción de la lista lateral de conversaciones mientras carga un chat
 */
function bloquearListaConversaciones() {
    const listEl = document.getElementById('lista-conversaciones-modal');
    if (listEl) {
        listEl.classList.add('pointer-events-none', 'opacity-60', 'cursor-wait');
    }
}

/**
 * Desbloquea la interacción de la lista lateral de conversaciones
 */
function desbloquearListaConversaciones() {
    const listEl = document.getElementById('lista-conversaciones-modal');
    if (listEl) {
        listEl.classList.remove('pointer-events-none', 'opacity-60', 'cursor-wait');
    }
}

/**
 * Pone el Panel 3 (Ficha y Logística) en estado visual de carga
 */
function mostrarCargandoPanelInfo(nombre = '') {
    const nomEl = document.getElementById('panel-info-nombre-cliente');
    const dirEl = document.getElementById('panel-info-direccion-cliente');
    const sucEl = document.getElementById('panel-info-sucursal');
    const telLink = document.getElementById('panel-info-telefono-link');
    const telText = document.getElementById('panel-info-telefono-texto');
    const frecEl = document.getElementById('panel-info-frecuencia');

    const badgeNotif = document.getElementById('panel-info-badge-notif');
    const estadoDia = document.getElementById('panel-info-estado-dia');
    const fechaProp = document.getElementById('panel-info-fecha-propuesta');
    const rutaNom = document.getElementById('panel-info-ruta-nombre');
    const volEl = document.getElementById('panel-info-volumen');
    const contenedorAcciones = document.getElementById('panel-info-acciones-evento');

    if (nomEl) nomEl.innerText = nombre || 'Cargando información...';
    if (dirEl) dirEl.innerText = 'Cargando ubicación...';
    if (sucEl) sucEl.innerText = '...';
    if (telText) telText.innerText = '...';
    if (telLink) telLink.href = '#';
    if (frecEl) frecEl.innerText = '...';

    if (badgeNotif) {
        badgeNotif.className = 'px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-slate-500 text-[10px] font-bold flex items-center gap-1.5';
        badgeNotif.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-pulse"></span>Cargando';
    }
    if (estadoDia) {
        estadoDia.innerText = 'Consultando...';
        estadoDia.className = 'text-xs font-semibold text-slate-400';
    }
    if (fechaProp) fechaProp.innerText = '...';
    if (rutaNom) rutaNom.innerText = '...';
    if (volEl) volEl.innerText = 'Aceite Vegetal Usado (UCO)';

    if (contenedorAcciones) {
        contenedorAcciones.innerHTML = `
            <div class="py-3 flex flex-col items-center justify-center gap-1.5 text-slate-400 bg-white/60 rounded-xl border border-slate-200/60">
                <div class="animate-spin rounded-full h-5 w-5 border-2 border-emerald-600 border-t-transparent"></div>
                <span class="text-[11px] font-semibold text-slate-400">Cargando logística...</span>
            </div>
        `;
    }
}

/**
 * Limpia los campos del Panel 3 para no dejar residuos de un cliente anterior
 */
function limpiarPanelInfoCliente() {
    const nomEl = document.getElementById('panel-info-nombre-cliente');
    const dirEl = document.getElementById('panel-info-direccion-cliente');
    const sucEl = document.getElementById('panel-info-sucursal');
    const telLink = document.getElementById('panel-info-telefono-link');
    const telText = document.getElementById('panel-info-telefono-texto');
    const frecEl = document.getElementById('panel-info-frecuencia');

    const badgeNotif = document.getElementById('panel-info-badge-notif');
    const estadoDia = document.getElementById('panel-info-estado-dia');
    const fechaProp = document.getElementById('panel-info-fecha-propuesta');
    const rutaNom = document.getElementById('panel-info-ruta-nombre');
    const volEl = document.getElementById('panel-info-volumen');
    const contenedorAcciones = document.getElementById('panel-info-acciones-evento');

    if (nomEl) nomEl.innerText = '';
    if (dirEl) dirEl.innerText = '';
    if (sucEl) sucEl.innerText = 'N/A';
    if (telText) telText.innerText = '+57 ...';
    if (telLink) telLink.href = '#';
    if (frecEl) frecEl.innerText = 'N/A';

    if (badgeNotif) {
        badgeNotif.className = 'px-2 py-0.5 rounded-full bg-slate-100 text-slate-400 text-[10px] font-bold';
        badgeNotif.innerHTML = '';
    }
    if (estadoDia) {
        estadoDia.innerText = '';
        estadoDia.className = 'text-xs font-bold text-slate-400';
    }
    if (fechaProp) fechaProp.innerText = '';
    if (rutaNom) rutaNom.innerText = '';
    if (volEl) volEl.innerText = 'Aceite Vegetal Usado (UCO)';
    if (contenedorAcciones) contenedorAcciones.innerHTML = '';
}

/**
 * Limpia por completo el estado del chat en memoria y restablece el DOM a estado vacío
 */
function limpiarEstadoChatModal() {
    estaCargandoConversacion = false;
    desbloquearListaConversaciones();

    clienteChatActualId = null;
    conversationIdActual = null;
    clienteDatosActuales = null;
    mensajesChatwoot = [];

    // Ocultar paneles activos y mostrar paneles vacíos
    const panelChatVacio = document.getElementById('panel-chat-vacio');
    const panelChatActivo = document.getElementById('panel-chat-activo');
    const panelInfoVacio = document.getElementById('panel-info-vacio');
    const panelInfoActivo = document.getElementById('panel-info-activo');

    if (panelChatVacio) panelChatVacio.classList.remove('hidden');
    if (panelChatActivo) {
        panelChatActivo.classList.add('hidden');
        panelChatActivo.classList.remove('flex');
    }

    if (panelInfoVacio) panelInfoVacio.classList.remove('hidden');
    if (panelInfoActivo) {
        panelInfoActivo.classList.add('hidden');
        panelInfoActivo.classList.remove('flex');
    }

    // Limpiar textos y estados de la cabecera central
    const titleEl = document.getElementById('chatwoot-modal-nombre-cliente');
    const convIdEl = document.getElementById('chatwoot-modal-conv-id');
    const phoneEl = document.getElementById('chatwoot-modal-telefono');
    const avatarHeader = document.getElementById('chatwoot-modal-avatar-header');
    const tag24h = document.getElementById('chatwoot-modal-tag-24h');
    const banner24h = document.getElementById('chatwoot-banner-24h');
    const list = document.getElementById('chatwoot-mensajes-lista');
    const inputMsg = document.getElementById('input-chatwoot-mensaje');

    if (titleEl) titleEl.innerText = 'Cargando...';
    if (convIdEl) convIdEl.innerText = '';
    if (phoneEl) phoneEl.innerText = '';
    if (avatarHeader) avatarHeader.innerText = 'WA';
    if (tag24h) {
        tag24h.innerHTML = '';
        tag24h.className = 'hidden';
    }
    if (banner24h) banner24h.classList.add('hidden-view');
    if (list) list.innerHTML = '';
    if (inputMsg) {
        inputMsg.value = '';
        inputMsg.disabled = false;
        inputMsg.placeholder = "Escribe un mensaje de WhatsApp...";
    }

    // Limpiar panel de información
    limpiarPanelInfoCliente();

    // Re-renderizar lista para desmarcar cualquier chat activo
    renderizarConversacionesModal();
}

// =========================================================================
// PANEL 1: LISTADO DE CONVERSACIONES, BÚSQUEDA Y FILTROS
// =========================================================================

/**
 * Carga el listado de conversaciones desde el servidor
 */
async function cargarConversacionesModal(filtro = null) {
    const listEl = document.getElementById('lista-conversaciones-modal');
    if (!listEl) return;

    if (filtro) {
        filtroModalConversaciones = filtro;
    }

    try {
        const response = await fetch(`${API_BASE}/chatwoot/index.php?action=conversations&page=1`);
        const result = await response.json();

        if (result.success && result.data) {
            todasLasConversacionesModal = result.data.conversations || [];
            
            // Actualizar contadores en los chips
            const contadorTodos = document.getElementById('chip-contador-modal-todos');
            const contadorAtencion = document.getElementById('chip-contador-modal-atencion');

            if (contadorTodos) {
                contadorTodos.innerText = todasLasConversacionesModal.length;
            }

            if (contadorAtencion) {
                const numAtencion = todasLasConversacionesModal.filter(c => 
                    (c.unread_count && c.unread_count > 0) || c.is_24h_expired
                ).length;
                contadorAtencion.innerText = numAtencion;
            }

            renderizarConversacionesModal();
        } else {
            listEl.innerHTML = `
                <div class="p-8 text-center text-slate-400 text-xs">
                    <p class="font-bold text-slate-500">No se pudieron cargar las conversaciones.</p>
                </div>
            `;
        }
    } catch (e) {
        console.error("Error al cargar lista de conversaciones en modal:", e);
        listEl.innerHTML = `
            <div class="p-8 text-center text-slate-400 text-xs">
                <p class="font-bold text-rose-500">Error de conexión al cargar chats.</p>
            </div>
        `;
    }
}

/**
 * Renderiza los elementos de conversación en el Panel 1 según filtro y búsqueda
 */
function renderizarConversacionesModal() {
    const listEl = document.getElementById('lista-conversaciones-modal');
    if (!listEl) return;

    let items = [...todasLasConversacionesModal];

    // 1. Filtrado por chip
    if (filtroModalConversaciones === 'atencion') {
        items = items.filter(c => (c.unread_count && c.unread_count > 0) || c.is_24h_expired);
    } else if (filtroModalConversaciones === 'confirmados') {
        items = items.filter(c => c.status === 'resolved' || c.status === 'snoozed');
    }

    // 2. Filtrado por texto de búsqueda
    if (busquedaModalConversaciones.trim() !== '') {
        const q = busquedaModalConversaciones.trim().toLowerCase();
        items = items.filter(c => {
            const nom = (c.nombre || '').toLowerCase();
            const tel = (c.telefono || '').toLowerCase();
            const rta = (c.ruta_nombre || '').toLowerCase();
            const msg = (c.ultimo_mensaje || '').toLowerCase();
            return nom.includes(q) || tel.includes(q) || rta.includes(q) || msg.includes(q);
        });
    }

    if (items.length === 0) {
        listEl.innerHTML = `
            <div class="p-10 text-center text-slate-400 text-xs space-y-1">
                <span class="material-symbols-outlined text-slate-300 text-3xl">inbox</span>
                <p class="font-bold text-slate-600">No hay chats en esta sección</p>
                <p class="text-[11px] text-slate-400">Intenta con otro término de búsqueda o filtro.</p>
            </div>
        `;
        return;
    }

    let html = '';
    items.forEach(c => {
        const isSelected = (conversationIdActual && String(c.conversation_id) === String(conversationIdActual)) ||
                           (clienteChatActualId && c.cliente_id && String(c.cliente_id) === String(clienteChatActualId));

        const iniciales = obtenerIniciales(c.nombre);
        const horaStr = formatHoraRelativaConversacion(c.ultimo_mensaje_at);
        const safeNombre = typeof escapeHtml === 'function' ? escapeHtml(c.nombre || 'Cliente') : (c.nombre || 'Cliente');
        const previewMsg = typeof escapeHtml === 'function' ? escapeHtml(c.ultimo_mensaje || 'Sin mensajes recientes') : (c.ultimo_mensaje || 'Sin mensajes recientes');

        // Estado y atención
        let badgeEstadoHtml = '';
        if (c.unread_count && c.unread_count > 0) {
            badgeEstadoHtml = `
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200/60 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Atención (${c.unread_count})
                </span>
            `;
        } else if (c.status === 'resolved') {
            badgeEstadoHtml = `
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="material-symbols-outlined text-[12px]">check_circle</span>Confirmado
                </span>
            `;
        } else {
            badgeEstadoHtml = `
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                    Al día
                </span>
            `;
        }

        // Estado ventana 24h
        let tag24hHtml = '';
        if (c.is_24h_expired) {
            tag24hHtml = `
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700/90" title="Ventana 24h cerrada (Solo plantillas)">
                    <span class="material-symbols-outlined text-[13px] text-amber-600">lock_clock</span>Plantillas
                </span>
            `;
        } else {
            tag24hHtml = `
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700">
                    <span class="material-symbols-outlined text-[13px] text-emerald-600">timer</span>Activa
                </span>
            `;
        }

        const rowClasses = isSelected
            ? 'p-3.5 bg-white border-l-4 border-l-emerald-600 shadow-xs cursor-pointer relative flex gap-3 transition'
            : 'p-3.5 hover:bg-white cursor-pointer border-l-4 border-l-transparent flex gap-3 transition';

        const avatarClasses = isSelected
            ? 'w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm shadow-2xs'
            : 'w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm';

        const paramClienteId = c.cliente_id ? c.cliente_id : 'null';
        const escapedForParam = (c.nombre || '').replace(/'/g, "\\'");

        html += `
            <div onclick="seleccionarConversacionModal(${c.conversation_id}, ${paramClienteId}, '${escapedForParam}')" class="${rowClasses}">
                <div class="relative flex-shrink-0">
                    <div class="${avatarClasses}">
                        ${iniciales}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <h4 class="text-xs font-bold text-slate-900 truncate">${safeNombre}</h4>
                        <span class="text-[11px] text-slate-400 font-medium flex items-center gap-1 flex-shrink-0">
                            <span class="material-symbols-outlined text-[13px] text-slate-400">schedule</span>
                            ${horaStr}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 line-clamp-1 font-body">
                        ${previewMsg}
                    </p>
                    <div class="flex items-center justify-between gap-2 mt-1.5 flex-wrap">
                        ${badgeEstadoHtml}
                        ${tag24hHtml}
                    </div>
                </div>
            </div>
        `;
    });

    listEl.innerHTML = html;
}

/**
 * Maneja el cambio de filtro en los chips superiores de Panel 1
 */
function cambiarFiltroConversacionesModal(filtro) {
    filtroModalConversaciones = filtro;

    const btnTodos = document.getElementById('chip-filtro-modal-todos');
    const btnAtencion = document.getElementById('chip-filtro-modal-atencion');
    const btnConfirmados = document.getElementById('chip-filtro-modal-confirmados');

    if (btnTodos) {
        if (filtro === 'todos') {
            btnTodos.className = 'px-3 py-1 rounded-lg bg-emerald-600 text-white font-semibold text-xs shadow-xs transition cursor-pointer';
        } else {
            btnTodos.className = 'px-3 py-1 rounded-lg text-slate-600 hover:bg-slate-200/70 text-xs font-medium transition cursor-pointer';
        }
    }

    if (btnAtencion) {
        if (filtro === 'atencion') {
            btnAtencion.className = 'px-3 py-1 rounded-lg bg-amber-500 text-white font-semibold text-xs shadow-xs flex items-center gap-1 transition cursor-pointer';
        } else {
            btnAtencion.className = 'px-3 py-1 rounded-lg bg-amber-50 border border-amber-200/80 text-amber-800 hover:bg-amber-100 font-semibold text-xs flex items-center gap-1 transition cursor-pointer';
        }
    }

    if (btnConfirmados) {
        if (filtro === 'confirmados') {
            btnConfirmados.className = 'px-3 py-1 rounded-lg bg-emerald-600 text-white font-semibold text-xs shadow-xs transition cursor-pointer';
        } else {
            btnConfirmados.className = 'px-3 py-1 rounded-lg text-slate-600 hover:bg-slate-200/70 text-xs font-medium transition cursor-pointer';
        }
    }

    renderizarConversacionesModal();
}

/**
 * Maneja la entrada de texto en el buscador en vivo de Panel 1
 */
function alFiltrarConversacionesModal() {
    const input = document.getElementById('input-buscar-modal-conversaciones');
    busquedaModalConversaciones = input ? input.value : '';
    renderizarConversacionesModal();
}

/**
 * Selecciona una conversación del listado y pasa al estado ACTIVO (Paneles 2 y 3)
 */
async function seleccionarConversacionModal(convId, clienteId = null, nombreParam = '') {
    // Si ya hay una carga en progreso, ignorar clics adicionales para evitar sobreescrituras inconsistentes
    if (estaCargandoConversacion) return;
    estaCargandoConversacion = true;
    bloquearListaConversaciones();

    try {
        conversationIdActual = convId;
        clienteChatActualId = clienteId;

        // Actualizar estados visuales de los paneles
        const panelChatVacio = document.getElementById('panel-chat-vacio');
        const panelChatActivo = document.getElementById('panel-chat-activo');
        const panelInfoVacio = document.getElementById('panel-info-vacio');
        const panelInfoActivo = document.getElementById('panel-info-activo');

        if (panelChatVacio) panelChatVacio.classList.add('hidden');
        if (panelChatActivo) {
            panelChatActivo.classList.remove('hidden');
            panelChatActivo.classList.add('flex');
        }

        if (panelInfoVacio) panelInfoVacio.classList.add('hidden');
        if (panelInfoActivo) {
            panelInfoActivo.classList.remove('hidden');
            panelInfoActivo.classList.add('flex');
        }

        // Refrescar el highlight en la lista de Panel 1
        renderizarConversacionesModal();

        // Si la pantalla es pequeña (< 1024px), cerrar la gaveta de chats para enfocar la conversación
        if (window.innerWidth < 1024 && typeof togglePanelChatsModal === 'function') {
            togglePanelChatsModal(false);
        }

        // Valores iniciales y de carga en cabecera
        const titleEl = document.getElementById('chatwoot-modal-nombre-cliente');
        const convIdEl = document.getElementById('chatwoot-modal-conv-id');
        const phoneEl = document.getElementById('chatwoot-modal-telefono');
        const avatarHeader = document.getElementById('chatwoot-modal-avatar-header');
        const tag24h = document.getElementById('chatwoot-modal-tag-24h');
        const list = document.getElementById('chatwoot-mensajes-lista');

        if (titleEl) titleEl.innerText = nombreParam || 'Cliente WhatsApp';
        if (convIdEl) convIdEl.innerText = convId ? `Conv. #${convId}` : '';
        if (phoneEl) phoneEl.innerText = 'Cargando...';
        if (avatarHeader) avatarHeader.innerText = obtenerIniciales(nombreParam);
        if (tag24h) {
            tag24h.innerHTML = '';
            tag24h.className = 'hidden';
        }

        // Indicador de carga en el chat central (Panel 2)
        if (list) {
            list.innerHTML = `
                <div class="flex flex-col items-center justify-center py-28 space-y-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-2 border-emerald-600 border-t-transparent"></div>
                    <p class="text-xs text-slate-500 font-semibold">Cargando mensajes del chat...</p>
                </div>
            `;
        }

        // Indicador de carga en la ficha del cliente (Panel 3)
        mostrarCargandoPanelInfo(nombreParam);

        // Cargar mensajes y perfil del cliente
        await consultarActualizacionesChatwoot(false);

        // Iniciar polling
        iniciarPollingChatwoot();
    } catch (e) {
        console.error("Error al seleccionar conversación:", e);
    } finally {
        estaCargandoConversacion = false;
        desbloquearListaConversaciones();
    }
}

// =========================================================================
// PANEL 2: MENSAJES DE WHATSAPP, ESCRITURA Y ENVIOS
// =========================================================================

/**
 * Renderiza la lista de mensajes en el contenedor central de Chatwoot
 */
function renderizarMensajesChatwoot(mensajes, forzarScroll = false) {
    const list = document.getElementById('chatwoot-mensajes-lista');
    if (!list) return;

    const isNearBottom = (list.scrollHeight - list.scrollTop - list.clientHeight) < 120;

    if (!mensajes || mensajes.length === 0) {
        list.innerHTML = `
            <div class="text-center py-20 space-y-2">
                <span class="material-symbols-outlined text-slate-300 text-5xl">chat_bubble_outline</span>
                <p class="text-slate-600 font-bold text-sm">No hay mensajes en este chat</p>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Escribe un mensaje abajo o selecciona una plantilla oficial para interactuar por WhatsApp.</p>
            </div>
        `;
        return;
    }

    list.innerHTML = '';
    let ultimaFechaHeader = null;

    mensajes.forEach(msg => {
        const contenido = (msg.content || '').trim();
        const attachments = Array.isArray(msg.attachments) ? msg.attachments : [];

        if (!contenido && attachments.length === 0 && msg.message_type !== 2 && msg.message_type !== 'activity') {
            return;
        }

        // Mensaje de actividad del sistema
        if (msg.message_type === 2 || msg.message_type === 'activity') {
            list.innerHTML += `
                <div class="flex justify-center my-2">
                    <span class="bg-slate-200/90 text-slate-600 text-[10px] font-semibold px-3 py-1 rounded-full text-center shadow-xs">
                        ${typeof escapeHtml === 'function' ? escapeHtml(contenido) : contenido}
                    </span>
                </div>
            `;
            return;
        }

        // Encabezado de fecha
        const fechaHeader = (typeof formatFechaHeaderChatwoot === 'function') 
            ? formatFechaHeaderChatwoot(msg.created_at) 
            : null;

        if (fechaHeader && fechaHeader !== ultimaFechaHeader) {
            ultimaFechaHeader = fechaHeader;
            list.innerHTML += `
                <div class="flex justify-center my-3.5">
                    <span class="bg-white text-slate-600 text-xs font-bold px-3.5 py-1 rounded-full shadow-xs border border-slate-200">
                        ${fechaHeader}
                    </span>
                </div>
            `;
        }

        const horaStr = (typeof formatHoraChatwoot === 'function') 
            ? formatHoraChatwoot(msg.created_at) 
            : '';

        const isIncoming = Boolean(msg.is_incoming);
        const rowClass = isIncoming ? 'chat-row chat-row-incoming' : 'chat-row chat-row-outgoing';
        
        let bubbleClass = isIncoming 
            ? 'chat-bubble-base chat-bubble-incoming' 
            : 'chat-bubble-base chat-bubble-outgoing';

        if (!isIncoming && (msg.status === 'failed' || msg.status === 'error')) {
            bubbleClass += ' chat-bubble-failed';
        }

        // Adjuntos
        let attachmentsHtml = '';
        if (attachments.length > 0) {
            attachments.forEach(att => {
                const fileType = (att.file_type || '').toLowerCase();
                const ext = (att.extension || '').toLowerCase();
                const dataUrl = att.data_url || '';
                const thumbUrl = att.thumb_url || dataUrl;

                if (!dataUrl) return;

                if (fileType.includes('audio') || ['ogg', 'oga', 'mp3', 'wav', 'm4a', 'aac', 'opus'].includes(ext)) {
                    attachmentsHtml += `
                        <div class="my-1 p-2 rounded-xl bg-black/5 flex flex-col gap-1.5 min-w-[240px]">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                <span class="material-symbols-outlined text-[18px] text-amber-700">mic</span>
                                <span>Mensaje de voz</span>
                            </div>
                            <audio controls preload="metadata" class="w-full h-8 outline-none">
                                <source src="${dataUrl}" type="audio/${ext || 'ogg'}">
                                <source src="${dataUrl}">
                                Tu navegador no soporta reproducción de audio.
                            </audio>
                        </div>
                    `;
                } else if (fileType.includes('image') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
                    attachmentsHtml += `
                        <div class="my-1 rounded-xl overflow-hidden max-w-xs border border-black/10 shadow-2xs">
                            <a href="${dataUrl}" target="_blank" rel="noopener noreferrer" class="block group relative" title="Click para ver imagen completa">
                                <img src="${thumbUrl || dataUrl}" alt="Imagen WhatsApp" class="w-full max-h-64 object-cover group-hover:opacity-90 transition rounded-lg" loading="lazy" />
                            </a>
                        </div>
                    `;
                } else if (fileType.includes('video') || ['mp4', 'mov', 'webm', 'avi', 'mkv'].includes(ext)) {
                    attachmentsHtml += `
                        <div class="my-1 rounded-xl overflow-hidden max-w-xs border border-black/10 shadow-2xs">
                            <video controls preload="metadata" class="w-full max-h-64 rounded-lg outline-none">
                                <source src="${dataUrl}">
                                Tu navegador no soporta reproducción de video.
                            </video>
                        </div>
                    `;
                } else {
                    const rawName = dataUrl.split('/').pop().split('?')[0] || `archivo.${ext || 'dat'}`;
                    const fileName = decodeURIComponent(rawName);
                    const safeName = typeof escapeHtml === 'function' ? escapeHtml(fileName) : fileName;
                    attachmentsHtml += `
                        <div class="my-1">
                            <a href="${dataUrl}" target="_blank" rel="noopener noreferrer" download class="inline-flex items-center gap-2 p-2.5 bg-black/5 hover:bg-black/10 transition rounded-xl text-xs font-bold text-slate-800 border border-black/5 shadow-2xs">
                                <span class="material-symbols-outlined text-[22px] text-amber-700">description</span>
                                <div class="flex flex-col text-left overflow-hidden">
                                    <span class="truncate max-w-[180px] font-bold">${safeName}</span>
                                    <span class="text-[10px] text-slate-500 font-normal">Descargar adjunto</span>
                                </div>
                                <span class="material-symbols-outlined text-[16px] text-slate-500 ml-1">download</span>
                            </a>
                        </div>
                    `;
                }
            });
        }

        // Footer con estado y hora
        let footerHtml = '';
        if (isIncoming) {
            footerHtml = `
                <div class="flex items-center justify-start gap-1 mt-1 text-[9px] font-semibold text-slate-400">
                    <span>${horaStr}</span>
                </div>
            `;
        } else {
            const status = msg.status || 'sent';

            if (status === 'sending') {
                footerHtml = `
                    <div class="flex items-center justify-end gap-1.5 mt-1 text-[9px] font-semibold text-emerald-800">
                        <span>${horaStr}</span>
                        <span class="inline-flex items-center gap-1 bg-black/10 text-emerald-900 px-1.5 py-0.5 rounded text-[8.5px] font-bold">
                            <span class="animate-spin inline-block w-2.5 h-2.5 border-2 border-emerald-900 border-t-transparent rounded-full"></span>
                            <span>Enviando...</span>
                        </span>
                    </div>
                `;
            } else if (status === 'failed' || status === 'error') {
                footerHtml = `
                    <div class="flex items-center justify-end gap-1.5 mt-1">
                        <span class="text-[9px] text-slate-400 font-semibold">${horaStr}</span>
                        <span class="inline-flex items-center gap-0.5 text-rose-700 bg-rose-100 px-1.5 py-0.5 rounded text-[9px] font-bold" title="Error al enviar">
                            <span class="material-symbols-outlined text-[13px] text-rose-600">warning</span>
                            <span>No enviado</span>
                        </span>
                    </div>
                `;
            } else {
                footerHtml = `
                    <div class="flex items-center justify-end gap-1 mt-1 text-[9px] font-semibold text-emerald-800">
                        <span>${horaStr}</span>
                        <span class="material-symbols-outlined text-[14px] leading-none text-emerald-700" title="Enviado">check</span>
                    </div>
                `;
            }
        }

        let bodyHtml = '';
        if (attachmentsHtml) bodyHtml += attachmentsHtml;
        if (contenido) {
            const safeContenido = typeof escapeHtml === 'function' ? escapeHtml(contenido) : contenido;
            bodyHtml += `<p class="text-xs sm:text-sm whitespace-pre-wrap leading-relaxed ${attachmentsHtml ? 'mt-1.5' : ''}">${safeContenido}</p>`;
        }

        list.innerHTML += `
            <div class="${rowClass}" data-msg-id="${msg.id || ''}">
                <div class="${bubbleClass}">
                    ${bodyHtml}
                    ${footerHtml}
                </div>
            </div>
        `;
    });

    if (forzarScroll || isNearBottom) {
        list.scrollTop = list.scrollHeight;
    }
}

/**
 * Consulta actualizaciones del chat con el servidor
 */
async function consultarActualizacionesChatwoot(esSilencioso = false) {
    if ((!clienteChatActualId && !conversationIdActual) || isFetchingChatwoot) return;
    
    const modal = document.getElementById('modal-chatwoot');
    if (!modal || modal.classList.contains('hidden-view')) {
        detenerPollingChatwoot();
        return;
    }

    isFetchingChatwoot = true;

    try {
        const queryParam = clienteChatActualId 
            ? `cliente_id=${clienteChatActualId}` 
            : `conversation_id=${conversationIdActual}`;

        const response = await fetch(`${API_BASE}/chatwoot/index.php?${queryParam}`);
        const result = await response.json();

        // Si el modal fue cerrado o se limpió el estado mientras se esperaba la respuesta, descartar
        const modalActual = document.getElementById('modal-chatwoot');
        if (!modalActual || modalActual.classList.contains('hidden-view') || (!clienteChatActualId && !conversationIdActual)) {
            return;
        }

        // Si el cliente o conversación activa cambió durante la petición, ignorar datos obsoletos
        if (clienteChatActualId && result.data?.cliente?.id && String(result.data.cliente.id) !== String(clienteChatActualId)) {
            return;
        }
        if (conversationIdActual && result.data?.conversation_id && String(result.data.conversation_id) !== String(conversationIdActual)) {
            return;
        }

        if (result.success && result.data) {
            const data = result.data;
            conversationIdActual = data.conversation_id;
            plantillasDisponibles = data.plantillas || [];
            clienteDatosActuales = data.cliente || {};

            const phoneEl = document.getElementById('chatwoot-modal-telefono');
            const convIdEl = document.getElementById('chatwoot-modal-conv-id');
            const titleEl = document.getElementById('chatwoot-modal-nombre-cliente');
            const avatarHeader = document.getElementById('chatwoot-modal-avatar-header');
            const tag24h = document.getElementById('chatwoot-modal-tag-24h');

            const nombreCliente = clienteDatosActuales.nombre || 'Cliente WhatsApp';

            if (titleEl) titleEl.innerText = nombreCliente;
            if (phoneEl) phoneEl.innerText = clienteDatosActuales.telefono_whatsapp || 'Sin teléfono';
            if (convIdEl) {
                convIdEl.innerText = conversationIdActual ? `Conv. #${conversationIdActual}` : '';
            }
            if (avatarHeader) {
                avatarHeader.innerText = obtenerIniciales(nombreCliente);
            }

            // Tag 24h en cabecera
            if (tag24h) {
                if (data.is_24h_expired) {
                    tag24h.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-amber-700';
                    tag24h.innerHTML = `<span class="material-symbols-outlined text-[13px] text-amber-600">lock_clock</span>Plantillas`;
                    tag24h.title = "Ventana de 24 horas de WhatsApp cerrada. Solo se permite iniciar con plantillas.";
                } else {
                    tag24h.className = 'inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700';
                    tag24h.innerHTML = `<span class="material-symbols-outlined text-[13px] text-emerald-600">timer</span>Ventana activa`;
                    tag24h.title = "Ventana de 24 horas activa.";
                }
            }

            // Control de la regla de ventana de 24 horas y campo de escritura:
            // "en el caso que la ventana esta cerrada pero cuando este abierta quiero que permita escribir lo que queramos"
            const banner24h = document.getElementById('chatwoot-banner-24h');
            const inputMsg = document.getElementById('input-chatwoot-mensaje');

            if (data.is_24h_expired) {
                if (banner24h) banner24h.classList.remove('hidden-view');
                if (inputMsg) {
                    inputMsg.placeholder = "Ventana 24h cerrada. Envía una plantilla oficial o escribe para reactivar...";
                }
            } else {
                if (banner24h) banner24h.classList.add('hidden-view');
                if (inputMsg) {
                    inputMsg.disabled = false;
                    inputMsg.placeholder = "Escribe un mensaje de WhatsApp...";
                    inputMsg.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-75', 'bg-slate-100');
                }
            }

            // Renderizado de mensajes
            const serverMessages = data.messages || [];
            const tempPendingMessages = mensajesChatwoot.filter(m => m.is_temp && (m.status === 'sending' || m.status === 'failed'));
            const filteredTemp = tempPendingMessages.filter(temp => {
                const yaExisteEnServidor = serverMessages.some(srv => 
                    !srv.is_incoming && srv.content.trim() === temp.content.trim()
                );
                return !yaExisteEnServidor;
            });

            mensajesChatwoot = [...serverMessages, ...filteredTemp];
            renderizarMensajesChatwoot(mensajesChatwoot, !esSilencioso);

            // Cargar datos en Panel 3 (Ficha del generador y cita)
            cargarDetallesClientePanel3(clienteDatosActuales);

            // Actualizar contador global de mensajes nuevos en la barra de navegación
            if (typeof verificarMensajesNuevosGlobal === 'function') {
                verificarMensajesNuevosGlobal();
            }
        } else if (!esSilencioso) {
            const list = document.getElementById('chatwoot-mensajes-lista');
            if (list) {
                list.innerHTML = `<p class="text-rose-500 text-center py-8 text-xs font-bold">Error obteniendo conversación de Chatwoot.</p>`;
            }
        }
    } catch (err) {
        if (!esSilencioso) {
            console.error("Error al consultar chatwoot:", err);
            const list = document.getElementById('chatwoot-mensajes-lista');
            if (list) {
                list.innerHTML = `<p class="text-rose-500 text-center py-8 text-xs font-bold">No se pudo conectar con el servidor de Chatwoot.</p>`;
            }
        }
    } finally {
        isFetchingChatwoot = false;
    }
}

/**
 * Controla la interacción del input de escritura libre y botón de enviar
 */
function alEscribirMensajeChatwoot() {
    const input = document.getElementById('input-chatwoot-mensaje');
    const btnEnviar = document.getElementById('btn-enviar-chatwoot');
    if (!input || !btnEnviar) return;
    // El botón se mantiene visible y listo para enviar
}

/**
 * Enviar mensaje saliente escrito libremente
 */
async function enviarMensajeChatwoot() {
    const input = document.getElementById('input-chatwoot-mensaje');
    if (!input) return;

    const texto = input.value.trim();
    if (!texto) return;

    if (!conversationIdActual) {
        alert("No hay una conversación activa de Chatwoot para este cliente.");
        return;
    }

    // 1. Mensaje temporal optimista con estado "sending"
    const tempId = 'temp_' + Date.now();
    const tempMsg = {
        id: tempId,
        content: texto,
        status: 'sending',
        created_at: Math.floor(Date.now() / 1000),
        is_incoming: false,
        is_temp: true
    };

    mensajesChatwoot.push(tempMsg);
    renderizarMensajesChatwoot(mensajesChatwoot, true);
    input.value = '';

    // 2. Iniciar polling rápido a 1s para detectar confirmación de Chatwoot
    iniciarFastPolling();

    try {
        const response = await fetch(`${API_BASE}/chatwoot/index.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                conversation_id: conversationIdActual,
                content: texto
            })
        });

        const result = await response.json();
        const msgObj = mensajesChatwoot.find(m => m.id === tempId);

        if (result.success) {
            if (msgObj) {
                msgObj.status = 'sent';
                if (result.response && result.response.id) {
                    msgObj.id = result.response.id;
                    msgObj.is_temp = false;
                }
            }
            renderizarMensajesChatwoot(mensajesChatwoot, false);
            consultarActualizacionesChatwoot(true);
        } else {
            console.error("Error al enviar mensaje:", result.message);
            if (msgObj) {
                msgObj.status = 'failed';
            }
            renderizarMensajesChatwoot(mensajesChatwoot, false);
        }
    } catch (err) {
        console.error("Error de red enviando mensaje a Chatwoot:", err);
        const msgObj = mensajesChatwoot.find(m => m.id === tempId);
        if (msgObj) {
            msgObj.status = 'failed';
        }
        renderizarMensajesChatwoot(mensajesChatwoot, false);
    }
}

// =========================================================================
// PANEL 3: INFORMACIÓN DEL CLIENTE Y GESTIÓN LOGÍSTICA
// =========================================================================

/**
 * Carga los datos del cliente y su cita próxima en el Panel 3
 */
function cargarDetallesClientePanel3(cliente) {
    if (!cliente) return;

    // Header Ficha de Generador
    const nomEl = document.getElementById('panel-info-nombre-cliente');
    const dirEl = document.getElementById('panel-info-direccion-cliente');
    const sucEl = document.getElementById('panel-info-sucursal');
    const telLink = document.getElementById('panel-info-telefono-link');
    const telText = document.getElementById('panel-info-telefono-texto');
    const frecEl = document.getElementById('panel-info-frecuencia');

    if (nomEl) nomEl.innerText = cliente.nombre || 'Cliente WhatsApp';
    if (dirEl) {
        const ciudad = cliente.ciudad || 'Ibagué';
        dirEl.innerText = cliente.direccion ? `${cliente.direccion}, ${ciudad}` : `${ciudad}, Tolima`;
    }
    if (sucEl) sucEl.innerText = cliente.sucursal_nombre || 'Principal';
    
    const rawTel = cliente.telefono_whatsapp || '';
    if (telText) telText.innerText = rawTel || 'Sin teléfono';
    if (telLink) {
        const digits = rawTel.replace(/\D/g, '');
        telLink.href = digits ? `https://wa.me/${digits}` : '#';
        telLink.target = '_blank';
    }
    if (frecEl) frecEl.innerText = cliente.frecuencia_nombre || 'Quincenal';

    // Tarjeta destacada de Recolección
    const badgeNotif = document.getElementById('panel-info-badge-notif');
    const estadoDia = document.getElementById('panel-info-estado-dia');
    const fechaProp = document.getElementById('panel-info-fecha-propuesta');
    const rutaNom = document.getElementById('panel-info-ruta-nombre');
    const volEl = document.getElementById('panel-info-volumen');
    const contenedorAcciones = document.getElementById('panel-info-acciones-evento');

    const evento = cliente.evento;
    const estadoStr = (evento?.estado || (evento?.es_tentativa ? 'tentativa' : '')).toLowerCase();
    const esTentativa = !evento || !evento.id || estadoStr === 'tentativa' || (evento.tipo || '').toLowerCase() === 'tentativa' || Boolean(evento.es_tentativa);
    const esNotificacion = ['notificacion1', 'notificacion2', 'notificacion3'].includes(estadoStr) || estadoStr.startsWith('notificacion');
    const esConsulta = estadoStr === 'consulta';
    const esConfirmado = ['completada', 'confirmado', 'confirmada', 'aceptado', 'aceptada'].includes(estadoStr);
    const esLiberado = ['cancelada', 'cancelado', 'denegada', 'denegado', 'rechazada', 'rechazado'].includes(estadoStr);

    if (fechaProp) {
        fechaProp.innerText = evento?.fecha_programada || 'Fecha sugerida';
    }
    if (rutaNom) {
        rutaNom.innerText = evento?.ruta_nombre || cliente.ruta_nombre || 'Sin ruta';
    }
    if (volEl) {
        volEl.innerText = 'Aceite Vegetal Usado (UCO)';
    }

    if (esTentativa) {
        // ESTADO TENTATIVA: Mostrar Programar o Cambiar
        if (badgeNotif) {
            badgeNotif.className = 'px-2 py-0.5 rounded-full bg-slate-100 border border-slate-300 text-slate-700 text-[10px] font-bold flex items-center gap-1.5';
            badgeNotif.innerHTML = '<span class="material-symbols-outlined text-[13px] text-slate-500">pending_actions</span>TENTATIVA';
        }
        if (estadoDia) {
            estadoDia.innerText = 'Por programar';
            estadoDia.className = 'text-xs font-bold text-slate-600';
        }
        if (contenedorAcciones) {
            contenedorAcciones.innerHTML = `
                <button onclick="programarEventoDesdeModalChat()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs flex items-center justify-center gap-2 text-xs transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">event_available</span>
                    <span>Programar</span>
                </button>
                <button onclick="cambiarFechaEventoDesdeModalChat()" class="w-full border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 px-4 rounded-xl font-medium flex items-center justify-center gap-2 text-xs bg-white transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px] text-slate-500">edit_calendar</span>
                    <span>Cambiar</span>
                </button>
            `;
        }
    } else if (esNotificacion || esConsulta) {
        // ESTADO NOTIFICACIÓN O CONSULTA: Mostrar Confirmar o Denegar
        const numNotif = (estadoStr.match(/\d+/) || ['1'])[0];
        if (badgeNotif) {
            if (esConsulta) {
                badgeNotif.className = 'px-2 py-0.5 rounded-full bg-amber-100 border border-amber-300 text-amber-900 text-[10px] font-bold flex items-center gap-1.5 animate-pulse';
                badgeNotif.innerHTML = '<span class="material-symbols-outlined text-[13px] text-amber-600">chat</span>ATENCIÓN WA';
            } else {
                badgeNotif.className = 'px-2 py-0.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold flex items-center gap-1.5';
                badgeNotif.innerHTML = `<span class="material-symbols-outlined text-[13px] text-blue-600">outgoing_mail</span>NOTIFICADO ${numNotif}`;
            }
        }
        if (estadoDia) {
            estadoDia.innerText = esConsulta ? 'Requiere Atención' : 'Esperando Respuesta';
            estadoDia.className = esConsulta ? 'text-xs font-bold text-amber-800' : 'text-xs font-bold text-blue-700';
        }
        if (contenedorAcciones) {
            contenedorAcciones.innerHTML = `
                <button onclick="confirmarCitaDesdeModalChat()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs flex items-center justify-center gap-2 text-xs transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>Confirmar</span>
                </button>
                <button onclick="liberarCitaDesdeModalChat()" class="w-full border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 px-4 rounded-xl font-medium flex items-center justify-center gap-2 text-xs bg-white transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px] text-slate-500">event_busy</span>
                    <span>Denegar</span>
                </button>
            `;
        }
    } else if (esConfirmado) {
        // ESTADO CONFIRMADO / COMPLETADA / ACEPTADA
        if (badgeNotif) {
            badgeNotif.className = 'px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold flex items-center gap-1.5';
            badgeNotif.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>CONFIRMADO';
        }
        if (estadoDia) {
            estadoDia.innerText = 'Cita asegurada';
            estadoDia.className = 'text-xs font-bold text-emerald-700';
        }
        if (contenedorAcciones) {
            contenedorAcciones.innerHTML = `
                <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">verified</span>
                    <span>Cita Confirmada</span>
                </div>
                <button onclick="cambiarFechaEventoDesdeModalChat()" class="w-full border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 px-4 rounded-xl font-medium flex items-center justify-center gap-2 text-xs bg-white transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px] text-slate-500">edit_calendar</span>
                    <span>Cambiar</span>
                </button>
            `;
        }
    } else if (esLiberado) {
        // ESTADO DENEGADO / CANCELADA
        if (badgeNotif) {
            badgeNotif.className = 'px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-slate-500 text-[10px] font-bold flex items-center gap-1.5';
            badgeNotif.innerHTML = '<span class="material-symbols-outlined text-[13px]">free_cancellation</span>LIBERADO';
        }
        if (estadoDia) {
            estadoDia.innerText = 'Parada denegada';
            estadoDia.className = 'text-xs font-bold text-slate-500';
        }
        if (contenedorAcciones) {
            contenedorAcciones.innerHTML = `
                <div class="p-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-xs font-semibold flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">event_busy</span>
                    <span>Parada Liberada / Denegada</span>
                </div>
                <button onclick="cambiarFechaEventoDesdeModalChat()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs flex items-center justify-center gap-2 text-xs transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">event_available</span>
                    <span>Programar Nueva</span>
                </button>
            `;
        }
    } else {
        // OTROS CASOS: PROGRAMADO / AGENDADO
        if (badgeNotif) {
            badgeNotif.className = 'px-2 py-0.5 rounded-full bg-slate-100 border border-slate-300 text-slate-700 text-[10px] font-bold flex items-center gap-1.5';
            badgeNotif.innerHTML = '<span class="material-symbols-outlined text-[13px] text-slate-500">calendar_today</span>PROGRAMADO';
        }
        if (estadoDia) {
            estadoDia.innerText = 'En programación';
            estadoDia.className = 'text-xs font-bold text-slate-700';
        }
        if (contenedorAcciones) {
            contenedorAcciones.innerHTML = `
                <button onclick="confirmarCitaDesdeModalChat()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs flex items-center justify-center gap-2 text-xs transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>Confirmar</span>
                </button>
                <button onclick="cambiarFechaEventoDesdeModalChat()" class="w-full border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 px-4 rounded-xl font-medium flex items-center justify-center gap-2 text-xs bg-white transition active:scale-98 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px] text-slate-500">edit_calendar</span>
                    <span>Cambiar</span>
                </button>
            `;
        }
    }
}

/**
 * Agendar / programar evento tentativo directamente desde el modal de chat
 */
async function programarEventoDesdeModalChat() {
    if (!clienteDatosActuales || !clienteDatosActuales.id) {
        alert("Selecciona un cliente válido para programar.");
        return;
    }

    const clienteId = clienteDatosActuales.id;
    const rutaId = clienteDatosActuales.evento?.ruta_id || clienteDatosActuales.ruta_id || null;
    const fechaProgramada = clienteDatosActuales.evento?.fecha_programada || new Date().toISOString().split('T')[0];
    const eventoId = clienteDatosActuales.evento?.id || null;

    try {
        let res;
        if (eventoId) {
            // Actualizar evento existente a agendado
            const response = await fetch(`${API_BASE}/core/eventos.php?id=${eventoId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    estado: 'programado',
                    tipo: 'frecuente'
                })
            });
            res = await response.json();
        } else {
            // Crear nuevo evento programado según docs/eventos.md
            const response = await fetch(`${API_BASE}/core/eventos.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cliente_id: clienteId,
                    ruta_id: rutaId,
                    fecha_programada: fechaProgramada,
                    estado: 'programado',
                    tipo: 'frecuente',
                    evento_origin: null
                })
            });
            res = await response.json();
            if (res && res.success && (res.id || res.evento_id)) {
                if (!clienteDatosActuales.evento) clienteDatosActuales.evento = {};
                clienteDatosActuales.evento.id = res.id || res.evento_id;
            }
        }

        if (res && res.success) {
            alert("✓ Recolección programada exitosamente.");
            if (clienteDatosActuales.evento) {
                clienteDatosActuales.evento.estado = 'programado';
                clienteDatosActuales.evento.tipo = 'frecuente';
                clienteDatosActuales.evento.es_tentativa = false;
            }
            cargarDetallesClientePanel3(clienteDatosActuales);
            if (typeof recargarDiaActual === 'function') {
                recargarDiaActual();
            }
        } else {
            alert("Error al programar: " + ((res && res.message) || 'No se pudo agendar'));
        }
    } catch (e) {
        console.error("Error programando recolección desde chat:", e);
        alert("Error de conexión al programar la recolección.");
    }
}

/**
 * Abre el modal para cambiar / modificar la fecha o datos de la recolección
 */
function cambiarFechaEventoDesdeModalChat() {
    if (!clienteDatosActuales || !clienteDatosActuales.id) {
        alert("Selecciona un cliente válido para cambiar la fecha.");
        return;
    }

    if (typeof abrirModalProgramarRecoleccion === 'function') {
        const clienteId = clienteDatosActuales.id;
        const eventoId = clienteDatosActuales.evento?.id || null;
        const fecha = clienteDatosActuales.evento?.fecha_programada || null;
        abrirModalProgramarRecoleccion(eventoId, clienteId, fecha);
    } else {
        alert("Módulo de programación de recolección no disponible.");
    }
}

/**
 * Confirmar cita logística desde el modal de chat
 */
async function confirmarCitaDesdeModalChat() {
    if (!clienteDatosActuales?.evento?.id) {
        alert("Este cliente no tiene una cita pendiente para confirmar. Puedes programarle una nueva con el botón inferior.");
        return;
    }

    const eventoId = clienteDatosActuales.evento.id;
    try {
        const response = await fetch(`${API_BASE}/core/eventos.php?id=${eventoId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                estado: 'aceptada'
            })
        });
        const res = await response.json();
        if (res.success) {
            alert("✓ Cita confirmada exitosamente.");
            if (clienteDatosActuales.evento) {
                clienteDatosActuales.evento.estado = 'aceptada';
            }
            cargarDetallesClientePanel3(clienteDatosActuales);
            if (typeof recargarDiaActual === 'function') {
                recargarDiaActual();
            }
        } else {
            alert("Error al confirmar: " + (res.message || 'No se pudo actualizar'));
        }
    } catch (e) {
        console.error("Error confirmando cita:", e);
        alert("Error de conexión al confirmar la cita.");
    }
}

/**
 * Denegar / Liberar parada logística desde el modal de chat
 */
async function liberarCitaDesdeModalChat() {
    if (!clienteDatosActuales?.evento?.id) {
        alert("Este cliente no tiene una parada o cita activa que liberar.");
        return;
    }

    if (!confirm("¿Estás seguro de denegar o liberar esta parada?")) {
        return;
    }

    const eventoId = clienteDatosActuales.evento.id;
    try {
        const response = await fetch(`${API_BASE}/core/eventos.php?id=${eventoId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                estado: 'denegada'
            })
        });
        const res = await response.json();
        if (res.success) {
            alert("Parada liberada / cita denegada exitosamente.");
            if (clienteDatosActuales.evento) {
                clienteDatosActuales.evento.estado = 'denegada';
            }
            cargarDetallesClientePanel3(clienteDatosActuales);
            if (typeof recargarDiaActual === 'function') {
                recargarDiaActual();
            }
        } else {
            alert("Error al denegar parada: " + (res.message || 'No se pudo actualizar'));
        }
    } catch (e) {
        console.error("Error denegando parada:", e);
        alert("Error de conexión al denegar la parada.");
    }
}

/**
 * Abre el modal para programar una recolección para el cliente
 */
function programarRecoleccionDesdeModalChat() {
    if (typeof abrirModalProgramarRecoleccion === 'function') {
        const clienteId = clienteDatosActuales?.id || null;
        abrirModalProgramarRecoleccion(null, clienteId);
    } else {
        alert("Módulo de programación de recolección no disponible.");
    }
}

/**
 * Abre el formulario de edición de datos del cliente
 */
function editarClienteDesdeModalChat() {
    if (clienteChatActualId && typeof abrirModalEditarCliente === 'function') {
        abrirModalEditarCliente(clienteChatActualId);
    } else {
        alert("Selecciona un cliente registrado para editar sus datos.");
    }
}

// =========================================================================
// GESTIÓN DE APERTURA, CIERRE Y POLLING
// =========================================================================

/**
 * Abre el modal de Chatwoot.
 * Si recibe null o nada, muestra todas las conversaciones con paneles central y derecho vacíos.
 * Si recibe un ID o cliente, abre directamente ese chat.
 */
async function abrirModalChatwoot(clienteIdOrObj = null, clienteNombreParam = null) {
    const modal = document.getElementById('modal-chatwoot');
    if (!modal) return;

    detenerPollingChatwoot();
    cerrarModalPlantillas();

    // 1. Limpiar siempre el estado anterior para que no persista ningún chat ni selección previa
    limpiarEstadoChatModal();

    modal.classList.remove('hidden-view');

    // Inicializar estado de gavetas responsivas
    panelInfoAbiertoManualmente = false;
    panelChatsAbiertoManualmente = null;
    ajustarResponsividadPanelesModal();

    // 1. Apertura sin cliente específico (desde botón "Mensajes" de navegación)
    if (!clienteIdOrObj) {
        // En pantallas pequeñas, abrir lista de chats para que el usuario pueda elegir
        if (window.innerWidth < 1024) {
            panelChatsAbiertoManualmente = true;
            ajustarResponsividadPanelesModal();
        }

        await cargarConversacionesModal();
        return;
    }

    // 2. Apertura con cliente o conversación específica
    let clienteId = null;
    let convId = null;
    let clienteNombre = clienteNombreParam;

    if (typeof clienteIdOrObj === 'object' && clienteIdOrObj !== null) {
        clienteId = clienteIdOrObj.cliente_id || clienteIdOrObj.id || null;
        convId = clienteIdOrObj.conversation_id || null;
        clienteNombre = clienteIdOrObj.cliente_nombre || clienteIdOrObj.nombre || clienteNombreParam;
    } else {
        clienteId = clienteIdOrObj;
    }

    // Mostrar de inmediato los paneles activos en estado de carga mientras carga la lista
    const panelChatVacio = document.getElementById('panel-chat-vacio');
    const panelChatActivo = document.getElementById('panel-chat-activo');
    const panelInfoVacio = document.getElementById('panel-info-vacio');
    const panelInfoActivo = document.getElementById('panel-info-activo');

    if (panelChatVacio) panelChatVacio.classList.add('hidden');
    if (panelChatActivo) {
        panelChatActivo.classList.remove('hidden');
        panelChatActivo.classList.add('flex');
    }
    if (panelInfoVacio) panelInfoVacio.classList.add('hidden');
    if (panelInfoActivo) {
        panelInfoActivo.classList.remove('hidden');
        panelInfoActivo.classList.add('flex');
    }

    const titleEl = document.getElementById('chatwoot-modal-nombre-cliente');
    const convIdEl = document.getElementById('chatwoot-modal-conv-id');
    const phoneEl = document.getElementById('chatwoot-modal-telefono');
    const avatarHeader = document.getElementById('chatwoot-modal-avatar-header');
    const list = document.getElementById('chatwoot-mensajes-lista');

    if (titleEl) titleEl.innerText = clienteNombre || 'Cargando conversación...';
    if (convIdEl) convIdEl.innerText = convId ? `Conv. #${convId}` : '';
    if (phoneEl) phoneEl.innerText = 'Cargando...';
    if (avatarHeader) avatarHeader.innerText = obtenerIniciales(clienteNombre);
    if (list) {
        list.innerHTML = `
            <div class="flex flex-col items-center justify-center py-28 space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-emerald-600 border-t-transparent"></div>
                <p class="text-xs text-slate-500 font-semibold">Cargando mensajes del chat...</p>
            </div>
        `;
    }
    mostrarCargandoPanelInfo(clienteNombre);

    // Cargar la lista lateral primero
    await cargarConversacionesModal();

    // Seleccionar y activar el chat correspondiente
    await seleccionarConversacionModal(convId, clienteId, clienteNombre);
}

/**
 * Cierra el modal y detiene timers
 */
function cerrarModalChatwoot() {
    detenerPollingChatwoot();
    cerrarModalPlantillas();
    cerrarTodosLosDrawersModal();
    const modal = document.getElementById('modal-chatwoot');
    if (modal) modal.classList.add('hidden-view');
    limpiarEstadoChatModal();
}

/**
 * Inicia el polling recurrente cada 5 segundos
 */
function iniciarPollingChatwoot() {
    detenerPollingChatwoot();
    pollingIntervalTimer = setInterval(() => {
        const modal = document.getElementById('modal-chatwoot');
        if (modal && !modal.classList.contains('hidden-view') && (clienteChatActualId || conversationIdActual)) {
            consultarActualizacionesChatwoot(true);
        } else {
            detenerPollingChatwoot();
        }
    }, 5000);
}

/**
 * Inicia polling rápido cada 1 segundo tras enviar un mensaje
 */
function iniciarFastPolling() {
    if (fastPollingTimer) clearInterval(fastPollingTimer);
    fastPollingRemainingTicks = 6;

    fastPollingTimer = setInterval(async () => {
        const modal = document.getElementById('modal-chatwoot');
        if (!modal || modal.classList.contains('hidden-view') || (!clienteChatActualId && !conversationIdActual)) {
            clearInterval(fastPollingTimer);
            fastPollingTimer = null;
            return;
        }

        fastPollingRemainingTicks--;
        await consultarActualizacionesChatwoot(true);

        if (fastPollingRemainingTicks <= 0) {
            clearInterval(fastPollingTimer);
            fastPollingTimer = null;
        }
    }, 1000);
}

/**
 * Detiene todos los timers de polling
 */
function detenerPollingChatwoot() {
    if (pollingIntervalTimer) {
        clearInterval(pollingIntervalTimer);
        pollingIntervalTimer = null;
    }
    if (fastPollingTimer) {
        clearInterval(fastPollingTimer);
        fastPollingTimer = null;
    }
}

// =========================================================================
// MODAL DE PLANTILLAS OFICIALES META WHATSAPP (HSM)
// =========================================================================

/**
 * Abre el Modal de Plantillas WhatsApp
 */
function abrirModalPlantillas(plantillaIdPorDefecto = null) {
    const modal = document.getElementById('modal-plantillas-whatsapp');
    const select = document.getElementById('select-modal-plantillas');
    if (!modal || !select) return;

    select.innerHTML = `<option value="">-- Elige una plantilla --</option>`;
    (plantillasDisponibles || []).forEach(p => {
        select.innerHTML += `<option value="${p.id}">${p.titulo}</option>`;
    });

    if (plantillaIdPorDefecto) {
        select.value = plantillaIdPorDefecto;
    } else if (plantillasDisponibles.length > 0) {
        select.value = plantillasDisponibles[0].id;
    }

    alCambiarPlantillaEnModal();
    modal.classList.remove('hidden-view');
}

/**
 * Cierra el Modal de Plantillas WhatsApp
 */
function cerrarModalPlantillas() {
    const modal = document.getElementById('modal-plantillas-whatsapp');
    if (modal) modal.classList.add('hidden-view');
    plantillaSeleccionadaActual = null;
}

/**
 * Controlador al cambiar la plantilla seleccionada en el modal
 */
function alCambiarPlantillaEnModal() {
    const select = document.getElementById('select-modal-plantillas');
    const containerVars = document.getElementById('contenedor-variables-plantilla-modal');
    const listaInputs = document.getElementById('lista-inputs-variables');
    const boxPreview = document.getElementById('box-preview-plantilla-modal');
    const btnEnviar = document.getElementById('btn-enviar-plantilla-accion');

    if (!select || !containerVars || !listaInputs || !boxPreview) return;

    const plantillaId = select.value;
    const plantilla = plantillasDisponibles.find(p => p.id === plantillaId);

    if (!plantilla) {
        plantillaSeleccionadaActual = null;
        containerVars.classList.add('hidden');
        boxPreview.classList.add('hidden');
        if (btnEnviar) btnEnviar.disabled = true;
        return;
    }

    plantillaSeleccionadaActual = plantilla;
    listaInputs.innerHTML = '';
    if (btnEnviar) btnEnviar.disabled = false;

    const hoyStr = (typeof formatLocalIso === 'function') 
        ? formatLocalIso(new Date()) 
        : new Date().toISOString().split('T')[0];

    (plantilla.variables || []).forEach(varName => {
        let valorDefecto = '';
        const labelLimpio = plantilla.variable_labels?.[varName] 
            || NOMBRES_VARIABLES_PLANTILLAS[varName] 
            || `Variable ${varName}`;

        if (varName === '1' || varName === 'cliente') {
            valorDefecto = clienteDatosActuales?.nombre || '';
        } else if (varName === '2' || varName === 'fecha') {
            valorDefecto = clienteDatosActuales?.evento?.fecha_programada || hoyStr;
        } else if (varName === 'sucursal') {
            valorDefecto = clienteDatosActuales?.sucursal_nombre || '';
        } else if (varName === 'ruta') {
            valorDefecto = clienteDatosActuales?.ruta_nombre || '';
        } else if (varName === 'motivo') {
            valorDefecto = 'Ajuste de programación';
        }

        listaInputs.innerHTML += `
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">${labelLimpio} <span class="text-rose-500">*</span></label>
                <input type="text" data-var="${varName}" value="${valorDefecto}" oninput="actualizarPreviewPlantillaModal()" class="input-var-plantilla-modal w-full p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-800 outline-none focus:border-emerald-500 focus:bg-white transition">
            </div>
        `;
    });

    if ((plantilla.variables || []).length > 0) {
        containerVars.classList.remove('hidden');
    } else {
        containerVars.classList.add('hidden');
    }

    actualizarPreviewPlantillaModal();
    boxPreview.classList.remove('hidden');
}

/**
 * Actualiza la vista previa del mensaje formateado en tiempo real
 */
function actualizarPreviewPlantillaModal() {
    if (!plantillaSeleccionadaActual) return;
    const txtPreview = document.getElementById('txt-preview-plantilla-modal');
    if (!txtPreview) return;

    let textoHtml = plantillaSeleccionadaActual.texto;
    document.querySelectorAll('.input-var-plantilla-modal').forEach(input => {
        const varName = input.getAttribute('data-var');
        const val = input.value.trim();
        const regex = new RegExp(`{{\\s*${varName}\\s*}}`, 'g');
        const formattedVal = val 
            ? `<strong class="font-bold text-slate-900 bg-amber-100/90 px-1.5 py-0.5 rounded shadow-2xs">${val}</strong>` 
            : `<strong class="font-bold text-amber-700 bg-amber-100/90 px-1.5 py-0.5 rounded shadow-2xs">[${varName}]</strong>`;
        textoHtml = textoHtml.replace(regex, formattedVal);
    });

    txtPreview.innerHTML = textoHtml;
}

/**
 * Envía la plantilla oficial con template_params a Chatwoot
 */
async function enviarPlantillaConfirmada() {
    if (!plantillaSeleccionadaActual || !conversationIdActual) {
        alert("No hay una conversación activa o plantilla seleccionada.");
        return;
    }

    let textoPlano = plantillaSeleccionadaActual.texto;
    const processedParams = {};
    document.querySelectorAll('.input-var-plantilla-modal').forEach(input => {
        const varName = input.getAttribute('data-var');
        const val = input.value.trim();
        processedParams[varName] = val;
        const regex = new RegExp(`{{\\s*${varName}\\s*}}`, 'g');
        textoPlano = textoPlano.replace(regex, val || `[${varName}]`);
    });

    if (!textoPlano) return;

    const templateParams = {
        name: plantillaSeleccionadaActual.name || plantillaSeleccionadaActual.id,
        category: plantillaSeleccionadaActual.category || 'UTILITY',
        language: plantillaSeleccionadaActual.language || 'es',
        processed_params: processedParams
    };

    cerrarModalPlantillas();

    const tempId = 'temp_' + Date.now();
    const tempMsg = {
        id: tempId,
        content: textoPlano,
        status: 'sending',
        created_at: Math.floor(Date.now() / 1000),
        is_incoming: false,
        is_temp: true
    };

    mensajesChatwoot.push(tempMsg);
    renderizarMensajesChatwoot(mensajesChatwoot, true);

    iniciarFastPolling();

    try {
        const response = await fetch(`${API_BASE}/chatwoot/index.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                conversation_id: conversationIdActual,
                content: textoPlano,
                template_params: templateParams
            })
        });

        const result = await response.json();
        const msgObj = mensajesChatwoot.find(m => m.id === tempId);

        if (result.success) {
            if (msgObj) {
                msgObj.status = 'sent';
                if (result.response && result.response.id) {
                    msgObj.id = result.response.id;
                    msgObj.is_temp = false;
                }
            }
            renderizarMensajesChatwoot(mensajesChatwoot, false);
            consultarActualizacionesChatwoot(true);
        } else {
            console.error("Error al enviar plantilla:", result.message);
            if (msgObj) {
                msgObj.status = 'failed';
            }
            renderizarMensajesChatwoot(mensajesChatwoot, false);
        }
    } catch (err) {
        console.error("Error enviando plantilla a Chatwoot:", err);
        const msgObj = mensajesChatwoot.find(m => m.id === tempId);
        if (msgObj) {
            msgObj.status = 'failed';
        }
        renderizarMensajesChatwoot(mensajesChatwoot, false);
    }
}

// =========================================================================
// RESPONSIVIDAD Y GAVETAS FLOTANTES DE PANELES (DRAWER SYSTEM)
// =========================================================================

/**
 * Ajusta visibilidad y posición de los paneles según el ancho de pantalla
 * Jerarquía: El centro (Panel 2) es prioritario y debe tener mínimo 800px en escritorio.
 * Si la pantalla es reducida, se achica primero la info (Panel 3).
 * Si es aún más chica, se achica la lista de chats (Panel 1).
 */
function ajustarResponsividadPanelesModal() {
    const asideChats = document.getElementById('modal-chatwoot-panel-chats');
    const asideInfo = document.getElementById('modal-chatwoot-panel-info');
    const backdrop = document.getElementById('modal-chatwoot-drawer-backdrop');
    if (!asideChats || !asideInfo) return;

    const w = window.innerWidth;

    // Reglas para Panel 1 (Chats):
    if (w >= 1024) {
        // En pantallas grandes (>=1024px), docked fijo a la izquierda
        asideChats.classList.remove('hidden', 'absolute', 'inset-y-0', 'left-0', 'z-40', 'shadow-2xl', 'bg-white');
        asideChats.classList.add('flex', 'bg-slate-50/80');
    } else {
        // En pantallas < 1024px, gaveta flotante si está abierta manualmente, sino oculta
        if (panelChatsAbiertoManualmente === true) {
            asideChats.classList.add('absolute', 'inset-y-0', 'left-0', 'z-40', 'shadow-2xl', 'bg-white', 'flex');
            asideChats.classList.remove('hidden', 'bg-slate-50/80');
        } else {
            asideChats.classList.add('hidden');
            asideChats.classList.remove('flex', 'absolute', 'inset-y-0', 'left-0', 'z-40', 'shadow-2xl', 'bg-white');
        }
    }

    // Reglas para Panel 3 (Ficha e Info):
    // "lo primero que achicamos es la info si la pantalla es chica"
    if (w >= 1536) {
        // En pantallas extra anchas (>=1536px 2xl), docked fijo a la derecha
        asideInfo.classList.remove('hidden', 'absolute', 'inset-y-0', 'right-0', 'z-40', 'shadow-2xl', 'bg-white');
        asideInfo.classList.add('flex', 'bg-slate-50');
    } else {
        // En pantallas < 1536px, gaveta flotante solo si el usuario le dio clic a la foto de perfil o botón de ficha
        if (panelInfoAbiertoManualmente) {
            asideInfo.classList.add('absolute', 'inset-y-0', 'right-0', 'z-40', 'shadow-2xl', 'bg-white', 'flex');
            asideInfo.classList.remove('hidden', 'bg-slate-50');
        } else {
            asideInfo.classList.add('hidden');
            asideInfo.classList.remove('flex', 'absolute', 'inset-y-0', 'right-0', 'z-40', 'shadow-2xl', 'bg-white');
        }
    }

    // Control del backdrop oscuro para gavetas en pantallas reducidas
    const algunDrawerAbierto = (w < 1024 && panelChatsAbiertoManualmente === true) || (w < 1536 && panelInfoAbiertoManualmente);
    if (backdrop) {
        if (algunDrawerAbierto) {
            backdrop.classList.remove('hidden');
        } else {
            backdrop.classList.add('hidden');
        }
    }
}

/**
 * Alterna el panel de Información del cliente (Panel 3)
 * Se activa al hacer clic en la foto de perfil del chat del medio o en el botón de badge
 */
function togglePanelInfoModal(forzar = null) {
    if (forzar !== null) {
        panelInfoAbiertoManualmente = Boolean(forzar);
    } else {
        panelInfoAbiertoManualmente = !panelInfoAbiertoManualmente;
    }

    // Si abrimos info en pantalla pequeña, cerramos chats para no saturar
    if (panelInfoAbiertoManualmente && window.innerWidth < 1024) {
        panelChatsAbiertoManualmente = false;
    }

    ajustarResponsividadPanelesModal();
}

/**
 * Alterna el panel de lista de chats (Panel 1)
 * Se activa al hacer clic en el botón de chats en la cabecera del chat del medio
 */
function togglePanelChatsModal(forzar = null) {
    const w = window.innerWidth;
    const asideChats = document.getElementById('modal-chatwoot-panel-chats');
    const estaVisible = asideChats && !asideChats.classList.contains('hidden');

    if (forzar !== null) {
        panelChatsAbiertoManualmente = Boolean(forzar);
    } else {
        panelChatsAbiertoManualmente = !estaVisible;
    }

    // Si abrimos chats en pantalla pequeña, cerramos info
    if (panelChatsAbiertoManualmente && w < 1024) {
        panelInfoAbiertoManualmente = false;
    }

    ajustarResponsividadPanelesModal();
}

/**
 * Cierra todos los drawers flotantes cuando el usuario hace clic en el backdrop
 */
function cerrarTodosLosDrawersModal() {
    panelInfoAbiertoManualmente = false;
    panelChatsAbiertoManualmente = false;
    ajustarResponsividadPanelesModal();
}

// Listener para redimensionamiento de pantalla
window.addEventListener('resize', () => {
    const modal = document.getElementById('modal-chatwoot');
    if (modal && !modal.classList.contains('hidden-view')) {
        ajustarResponsividadPanelesModal();
    }
});

// Cierre con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modalPlantillas = document.getElementById('modal-plantillas-whatsapp');
        if (modalPlantillas && !modalPlantillas.classList.contains('hidden-view')) {
            cerrarModalPlantillas();
            return;
        }

        const modalChat = document.getElementById('modal-chatwoot');
        if (modalChat && !modalChat.classList.contains('hidden-view')) {
            cerrarModalChatwoot();
        }
    }
});

// Exposición global en window
window.abrirModalChatwoot = abrirModalChatwoot;
window.cerrarModalChatwoot = cerrarModalChatwoot;
window.cargarConversacionesModal = cargarConversacionesModal;
window.renderizarConversacionesModal = renderizarConversacionesModal;
window.seleccionarConversacionModal = seleccionarConversacionModal;
window.cambiarFiltroConversacionesModal = cambiarFiltroConversacionesModal;
window.alFiltrarConversacionesModal = alFiltrarConversacionesModal;
window.alEscribirMensajeChatwoot = alEscribirMensajeChatwoot;
window.enviarMensajeChatwoot = enviarMensajeChatwoot;
window.confirmarCitaDesdeModalChat = confirmarCitaDesdeModalChat;
window.liberarCitaDesdeModalChat = liberarCitaDesdeModalChat;
window.programarEventoDesdeModalChat = programarEventoDesdeModalChat;
window.cambiarFechaEventoDesdeModalChat = cambiarFechaEventoDesdeModalChat;
window.programarRecoleccionDesdeModalChat = programarRecoleccionDesdeModalChat;
window.editarClienteDesdeModalChat = editarClienteDesdeModalChat;
window.abrirModalPlantillas = abrirModalPlantillas;
window.cerrarModalPlantillas = cerrarModalPlantillas;
window.alCambiarPlantillaEnModal = alCambiarPlantillaEnModal;
window.actualizarPreviewPlantillaModal = actualizarPreviewPlantillaModal;
window.enviarPlantillaConfirmada = enviarPlantillaConfirmada;
window.togglePanelInfoModal = togglePanelInfoModal;
window.togglePanelChatsModal = togglePanelChatsModal;
window.cerrarTodosLosDrawersModal = cerrarTodosLosDrawersModal;
window.ajustarResponsividadPanelesModal = ajustarResponsividadPanelesModal;
window.limpiarEstadoChatModal = limpiarEstadoChatModal;
