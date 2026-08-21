// public/js/modules/mensajes.js
// Módulo de Bandeja de Mensajes y Chats WhatsApp sincronizados con Chatwoot
// Carga paginada de 20 en 20, búsqueda en vivo, polling en vivo cada 5s y apertura del chat.

let paginaConversacionesActual = 1;
let listaConversacionesCache = [];
let isLoadingConversaciones = false;
let hasMoreConversaciones = true;
let pollingBandejaTimer = null;

/**
 * Cargar listado de conversaciones desde el backend
 * @param {number} pagina Número de página
 * @param {boolean} append Si se agregan a la lista existente (paginación)
 * @param {boolean} esSilencioso Si es una actualización en segundo plano sin mostrar loaders
 */
async function cargarConversaciones(pagina = 1, append = false, esSilencioso = false) {
    if (isLoadingConversaciones) return;
    isLoadingConversaciones = true;
    paginaConversacionesActual = pagina;

    const listaEl = document.getElementById('lista-conversaciones-chatwoot');
    const btnVerMas = document.getElementById('btn-ver-mas-chats');
    const contVerMas = document.getElementById('contenedor-ver-mas-chats');

    if (!append && !esSilencioso && listaEl) {
        listaEl.innerHTML = `
            <div class="p-16 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2"></div>
                <p class="text-xs text-gray-500 font-semibold">Cargando chats de WhatsApp...</p>
            </div>
        `;
    }

    if (btnVerMas && append) {
        btnVerMas.disabled = true;
        btnVerMas.innerHTML = `
            <span class="animate-spin inline-block w-4 h-4 border-2 border-charcoal border-t-transparent rounded-full"></span>
            <span>Cargando chats...</span>
        `;
    }

    try {
        const response = await fetch(`${API_BASE}/chatwoot/index.php?action=conversations&page=${pagina}`);
        const result = await response.json();

        if (result.success && result.data) {
            const data = result.data;
            const newConversations = data.conversations || [];
            hasMoreConversaciones = data.meta?.has_more || (newConversations.length >= 20);

            if (append) {
                listaConversacionesCache = [...listaConversacionesCache, ...newConversations];
            } else if (esSilencioso && pagina === 1 && listaConversacionesCache.length > 20) {
                // Actualizar las primeras 20 manteniendo las páginas ya cargadas por el usuario
                const restantes = listaConversacionesCache.slice(newConversations.length);
                listaConversacionesCache = [...newConversations, ...restantes];
            } else {
                listaConversacionesCache = newConversations;
            }

            renderizarListaConversaciones(listaConversacionesCache);

            if (contVerMas) {
                if (hasMoreConversaciones && listaConversacionesCache.length > 0) {
                    contVerMas.classList.remove('hidden');
                } else {
                    contVerMas.classList.add('hidden');
                }
            }
        } else if (!esSilencioso && !append && listaEl) {
            listaEl.innerHTML = `
                <div class="p-12 text-center">
                    <span class="material-symbols-outlined text-red-400 text-4xl mb-2">error</span>
                    <p class="text-red-500 font-bold text-sm">Error cargando lista de chats</p>
                    <p class="text-xs text-gray-400 mt-1">Verifica la conexión con el servidor de Chatwoot.</p>
                </div>
            `;
        }
    } catch (error) {
        if (!esSilencioso) {
            console.error("Error al cargar conversaciones:", error);
            if (!append && listaEl) {
                listaEl.innerHTML = `
                    <div class="p-12 text-center">
                        <span class="material-symbols-outlined text-red-400 text-4xl mb-2">wifi_off</span>
                        <p class="text-red-500 font-bold text-sm">Error de conexión</p>
                    </div>
                `;
            }
        }
    } finally {
        isLoadingConversaciones = false;
        if (btnVerMas) {
            btnVerMas.disabled = false;
            btnVerMas.innerHTML = `
                <span class="material-symbols-outlined text-[18px]">expand_more</span>
                <span>Ver más chats</span>
            `;
        }
    }
}

let pollingGlobalNuevosTimer = null;

/**
 * Consulta ligera cada minuto para actualizar el badge de mensajes no leídos en la barra de navegación
 */
async function verificarMensajesNuevosGlobal() {
    try {
        const response = await fetch(`${API_BASE}/chatwoot/index.php?action=unread_summary`);
        const result = await response.json();

        if (result.success && result.data) {
            const count = parseInt(result.data.unread_conversations_count || 0, 10);
            const badgeSidebar = document.getElementById('badge-nav-mensajes-sidebar');
            const badgeMobile = document.getElementById('badge-nav-mensajes-mobile');

            const textCount = count > 99 ? '99+' : String(count);

            if (count > 0) {
                if (badgeSidebar) {
                    badgeSidebar.innerText = textCount;
                    badgeSidebar.style.display = 'inline-flex';
                }
                if (badgeMobile) {
                    badgeMobile.innerText = textCount;
                    badgeMobile.style.display = 'inline-flex';
                }
            } else {
                if (badgeSidebar) badgeSidebar.style.display = 'none';
                if (badgeMobile) badgeMobile.style.display = 'none';
            }
        }
    } catch (e) {
        console.warn("Error en sondeo global de mensajes nuevos:", e);
    }
}

/**
 * Iniciar el sondeo global cada 1 minuto (60s) al arrancar la aplicación
 */
function iniciarPollingGlobalNuevosMensajes() {
    if (pollingGlobalNuevosTimer) clearInterval(pollingGlobalNuevosTimer);
    // Ejecutar verificación inmediata al inicio
    verificarMensajesNuevosGlobal();
    // Programar recurrencia cada 1 minuto (60000ms)
    pollingGlobalNuevosTimer = setInterval(verificarMensajesNuevosGlobal, 60000);
}

/**
 * Iniciar el polling en vivo cada 5 segundos mientras el usuario esté en la pestaña de Mensajes
 */
function iniciarPollingBandejaMensajes() {
    detenerPollingBandejaMensajes();
    pollingBandejaTimer = setInterval(() => {
        const tab = document.getElementById('tab-mensajes');
        if (tab && !tab.classList.contains('hidden-view')) {
            cargarConversaciones(1, false, true);
            verificarMensajesNuevosGlobal();
        } else {
            detenerPollingBandejaMensajes();
        }
    }, 5000);
}

/**
 * Detener el polling de la bandeja de mensajes
 */
function detenerPollingBandejaMensajes() {
    if (pollingBandejaTimer) {
        clearInterval(pollingBandejaTimer);
        pollingBandejaTimer = null;
    }
}

/**
 * Cargar la siguiente página de 20 chats
 */
function cargarMasChats() {
    if (!isLoadingConversaciones && hasMoreConversaciones) {
        cargarConversaciones(paginaConversacionesActual + 1, true);
    }
}

/**
 * Recargar la lista desde la página 1
 */
function recargarListaConversaciones() {
    const inputSearch = document.getElementById('input-buscar-conversacion');
    if (inputSearch) inputSearch.value = '';
    cargarConversaciones(1, false);
}

/**
 * Filtrar localmente en el listado cargado
 */
function alBuscarConversacion() {
    const input = document.getElementById('input-buscar-conversacion');
    if (!input) return;

    const query = input.value.trim().toLowerCase();
    if (!query) {
        renderizarListaConversaciones(listaConversacionesCache);
        return;
    }

    const filtrados = listaConversacionesCache.filter(conv => {
        const nombre = (conv.nombre || '').toLowerCase();
        const tel = (conv.telefono || '').toLowerCase();
        const sucursal = (conv.sucursal_nombre || '').toLowerCase();
        const ruta = (conv.ruta_nombre || '').toLowerCase();
        const ultimoMsg = (conv.ultimo_mensaje || '').toLowerCase();
        const convId = String(conv.conversation_id || '');

        return nombre.includes(query) || 
               tel.includes(query) || 
               sucursal.includes(query) || 
               ruta.includes(query) || 
               ultimoMsg.includes(query) ||
               convId.includes(query);
    });

    renderizarListaConversaciones(filtrados);
}

/**
 * Renderiza los elementos de la lista en HTML
 */
function renderizarListaConversaciones(conversaciones) {
    const listEl = document.getElementById('lista-conversaciones-chatwoot');
    if (!listEl) return;

    if (!conversaciones || conversaciones.length === 0) {
        listEl.innerHTML = `
            <div class="p-16 text-center">
                <span class="material-symbols-outlined text-gray-300 text-5xl mb-2">forum</span>
                <p class="text-gray-500 font-bold text-sm">No se encontraron conversaciones</p>
                <p class="text-xs text-gray-400 mt-1">Todas las conversaciones activas de WhatsApp aparecerán aquí.</p>
            </div>
        `;
        return;
    }

    listEl.innerHTML = '';

    conversaciones.forEach(conv => {
        const nombre = conv.nombre || 'Cliente WhatsApp';
        const telefono = conv.telefono || 'Sin teléfono';
        const ultimoMsg = (conv.ultimo_mensaje || '').trim() || 'Sin mensajes recientes';
        const unreadCount = parseInt(conv.unread_count || 0, 10);
        
        // Determinar si el último mensaje fue entrante (del cliente) o saliente (nuestro)
        const isIncoming = (conv.ultimo_mensaje_tipo === 0 || (String(conv.ultimo_mensaje_tipo) === '0') || conv.ultimo_mensaje_tipo === 'incoming');
        const isOutgoing = !isIncoming;

        // Formato de hora/fecha amigable
        let fechaStr = '';
        if (conv.ultimo_mensaje_at) {
            const isNum = !isNaN(conv.ultimo_mensaje_at) && !isNaN(parseFloat(conv.ultimo_mensaje_at));
            const dateObj = new Date(isNum ? Number(conv.ultimo_mensaje_at) * 1000 : conv.ultimo_mensaje_at);
            const hoy = new Date();
            const esHoy = dateObj.toDateString() === hoy.toDateString();

            if (esHoy) {
                fechaStr = dateObj.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit', hour12: true });
            } else {
                fechaStr = dateObj.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
            }
        }

        // Ícono de estado de la ventana 24h debajo del nombre (Círculo perfecto, solo ícono, sin texto)
        const badge24hIcon = !conv.is_24h_expired
            ? `<span style="width: 17px; height: 17px; min-width: 17px; min-height: 17px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" class="bg-emerald-100 text-emerald-700 shadow-2xs" title="Ventana 24h activa (chat libre)">
                 <span class="material-symbols-outlined text-[11px] font-bold" style="font-size: 11px;">check</span>
               </span>`
            : `<span style="width: 17px; height: 17px; min-width: 17px; min-height: 17px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" class="bg-amber-100 text-amber-800 shadow-2xs" title="Ventana 24h expirada (requiere plantilla)">
                 <span class="material-symbols-outlined text-[11px] font-black" style="font-size: 11px;">priority_high</span>
               </span>`;

        const safeNombre = escapeHtml(nombre);
        const safeTel = escapeHtml(telefono);

        // Limpiar mensaje a 1 sola línea y limitar caracteres como red de seguridad
        let rawMsg = (conv.ultimo_mensaje || '').replace(/[\r\n]+/g, ' ').replace(/\s+/g, ' ').trim() || 'Sin mensajes recientes';
        let previewMsg = rawMsg.length > 70 ? rawMsg.substring(0, 70) + '...' : rawMsg;

        // Tags de sucursal y ruta (Ruta solo visible en PC/Tablet)
        let tagsHtml = '';
        if (conv.sucursal_nombre) {
            tagsHtml += `<span class="chat-badge-sucursal">${escapeHtml(conv.sucursal_nombre)}</span>`;
        }
        if (conv.ruta_nombre) {
            tagsHtml += `<span class="chat-badge-ruta chat-badge-ruta-desktop">Ruta: ${escapeHtml(conv.ruta_nombre)}</span>`;
        }

        // Ícono de estado en la columna derecha (debajo de la fecha):
        // 1. Si el último mensaje es saliente: chulitos de enviado
        // 2. Si el mensaje es entrante no leído: badge con número
        let statusIconRight = '';
        if (isOutgoing) {
            statusIconRight = `<span class="material-symbols-outlined" style="font-size: 16px; color: #f59e0b; line-height: 1;" title="Enviado">done_all</span>`;
        } else if (isIncoming && unreadCount > 0) {
            statusIconRight = `<span style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0; background-color: #facc14; color: #2d3436; font-size: 10px; font-weight: 900; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">${unreadCount}</span>`;
        }

        const tagsRowHtml = tagsHtml ? `<div class="chat-item-tags-row">${tagsHtml}</div>` : '';

        listEl.innerHTML += `
            <div onclick="abrirChatDesdeLista(${conv.conversation_id}, ${conv.cliente_id || 'null'}, '${safeNombre.replace(/'/g, "\\'")}', '${safeTel.replace(/'/g, "\\'")}')" 
                 class="chat-item-row">
                
                <!-- Avatar Circular -->
                <div class="chat-item-avatar-wrapper">
                    <div class="chat-item-avatar">
                        ${nombre.charAt(0).toUpperCase()}
                    </div>
                    <span class="chat-item-online-dot"></span>
                </div>

                <!-- Cuerpo Central: Nombre, Sucursal/Ruta y Previsualización de 1 Línea -->
                <div class="chat-item-body">
                    <!-- Fila 1: Nombre con ellipsis + ID (solo en PC) -->
                    <div class="chat-item-title-row">
                        <span class="chat-item-name" title="${safeNombre}">${safeNombre}</span>
                        <span class="chat-item-conv-id chat-item-conv-id-desktop">#${conv.conversation_id}</span>
                    </div>

                    <!-- Fila 2: Sucursal (móvil y PC) y Ruta (solo PC) -->
                    ${tagsRowHtml}

                    <!-- Fila 3: Ventana 24h + Mensaje estricto de 1 línea con ellipsis -->
                    <div class="chat-item-message-preview">
                        ${badge24hIcon}
                        <span class="chat-item-message-text" title="${escapeHtml(rawMsg)}">${escapeHtml(previewMsg)}</span>
                    </div>
                </div>

                <!-- Columna Derecha: Hora pequeña y Chulitos/Unread debajo -->
                <div class="chat-item-meta-right">
                    <span class="chat-item-time">${fechaStr}</span>
                    <div class="chat-item-status-icon">
                        ${statusIconRight}
                    </div>
                </div>
            </div>
        `;
    });
}

/**
 * Abrir el modal de chat desde el clic en un elemento de la lista
 */
function abrirChatDesdeLista(convId, clienteId, nombre, telefono) {
    // Marcar como leído localmente de inmediato para feedback instantáneo
    if (listaConversacionesCache && listaConversacionesCache.length > 0) {
        const item = listaConversacionesCache.find(c => (convId && c.conversation_id == convId) || (clienteId && c.cliente_id == clienteId));
        if (item && item.unread_count > 0) {
            item.unread_count = 0;
            renderizarListaConversaciones(listaConversacionesCache);
        }
    }

    if (typeof abrirModalChatwoot === 'function') {
        if (clienteId) {
            abrirModalChatwoot(clienteId, nombre);
        } else {
            abrirModalChatwoot({
                id: null,
                cliente_id: null,
                conversation_id: convId,
                nombre: nombre,
                cliente_nombre: nombre,
                telefono_whatsapp: telefono
            }, nombre);
        }
    }

    // Actualizar badge global de conversaciones no leídas
    setTimeout(() => {
        if (typeof verificarMensajesNuevosGlobal === 'function') {
            verificarMensajesNuevosGlobal();
        }
    }, 600);
}

/**
 * Helper para escapar HTML en strings
 */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function string(val) {
    return String(val ?? '');
}

function is_numeric(val) {
    return !isNaN(parseFloat(val)) && isFinite(val);
}

// Exposición global en window
window.cargarConversaciones = cargarConversaciones;
window.cargarMasChats = cargarMasChats;
window.recargarListaConversaciones = recargarListaConversaciones;
window.alBuscarConversacion = alBuscarConversacion;
window.abrirChatDesdeLista = abrirChatDesdeLista;
window.iniciarPollingBandejaMensajes = iniciarPollingBandejaMensajes;
window.detenerPollingBandejaMensajes = detenerPollingBandejaMensajes;
window.verificarMensajesNuevosGlobal = verificarMensajesNuevosGlobal;
window.iniciarPollingGlobalNuevosMensajes = iniciarPollingGlobalNuevosMensajes;
