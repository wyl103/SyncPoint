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
            ? `<span style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;" class="bg-emerald-100 text-emerald-700 shrink-0 shadow-2xs" title="Ventana 24h activa (chat libre)">
                 <span class="material-symbols-outlined text-[12px] font-bold">check</span>
               </span>`
            : `<span style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;" class="bg-amber-100 text-amber-800 shrink-0 shadow-2xs" title="Ventana 24h expirada (requiere plantilla)">
                 <span class="material-symbols-outlined text-[12px] font-black">priority_high</span>
               </span>`;

        // Círculo perfecto amarillo para mensajes no leídos: SOLO si el último mensaje es del cliente y unread_count > 0
        let badgeUnread = '';
        if (isIncoming && unreadCount > 0) {
            badgeUnread = `<span style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; line-height: 1; padding: 0;" class="bg-primary text-charcoal text-[11px] font-black shadow-xs shrink-0">${unreadCount}</span>`;
        }

        // Ícono de confirmación para mensajes salientes
        let iconoMsg = '';
        if (isOutgoing) {
            iconoMsg = `<span class="material-symbols-outlined text-[15px] text-primary shrink-0 align-middle">done_all</span>`;
        }

        // Tags de sucursal y ruta
        let tagsHtml = '';
        if (conv.sucursal_nombre) {
            tagsHtml += `<span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-md truncate max-w-[120px]">${conv.sucursal_nombre}</span>`;
        }
        if (conv.ruta_nombre) {
            tagsHtml += `<span class="bg-yellow-50 text-amber-800 border border-yellow-200 text-[10px] font-bold px-2 py-0.5 rounded-md truncate max-w-[120px]">Ruta: ${conv.ruta_nombre}</span>`;
        }

        const safeNombre = escapeHtml(nombre);
        const safeTel = escapeHtml(telefono);

        listEl.innerHTML += `
            <div onclick="abrirChatDesdeLista(${conv.conversation_id}, ${conv.cliente_id || 'null'}, '${safeNombre.replace(/'/g, "\\'")}', '${safeTel.replace(/'/g, "\\'")}')" 
                 class="p-4 hover:bg-yellow-50/50 transition cursor-pointer flex items-center justify-between gap-4">
                
                <!-- Izquierda: Avatar y Contenido -->
                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                    <div class="relative shrink-0">
                        <div class="w-12 h-12 rounded-full bg-primary/20 text-charcoal font-black flex items-center justify-center text-base shadow-2xs">
                            ${nombre.charAt(0).toUpperCase()}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                    </div>

                    <div class="min-w-0 flex-1 space-y-1">
                        <!-- Fila Superior: Nombre, ID, Sucursal y Ruta -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm font-bold text-charcoal truncate">${safeNombre}</h4>
                            <span class="text-[11px] font-semibold text-gray-400">#${conv.conversation_id}</span>
                            ${tagsHtml}
                        </div>

                        <!-- Fila Inferior: Ícono 24h (chulito o !) + Último Mensaje -->
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-medium truncate">
                            ${badge24hIcon}
                            <p class="flex items-center gap-1 truncate max-w-xl">
                                ${iconoMsg}
                                <span class="truncate">${escapeHtml(ultimoMsg)}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Derecha: Fecha y Círculo Unread (Sin flecha) -->
                <div class="flex flex-col items-end gap-1.5 shrink-0">
                    <span class="text-[11px] font-semibold text-gray-400">${fechaStr}</span>
                    ${badgeUnread}
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
