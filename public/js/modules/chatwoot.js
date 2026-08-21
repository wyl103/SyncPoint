// public/js/modules/chatwoot.js
// Lógica del Modal de Chatwoot, Mensajes, Estados de Envío (Check / Triángulo Rojo),
// Modal Flotante de Plantillas WhatsApp con template_params, Polling rápido a 1s y Polling a 5s.

let clienteChatActualId = null;
let conversationIdActual = null;
let plantillasDisponibles = [];
let clienteDatosActuales = null;
let mensajesChatwoot = [];
let plantillaSeleccionadaActual = null;
let pollingIntervalTimer = null;
let fastPollingTimer = null;
let isFetchingChatwoot = false;
let fastPollingRemainingTicks = 0;

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
 * Renderiza la lista de mensajes en el contenedor de Chatwoot
 * Muestra el estado (Enviando con spinner, Enviado con Check, Fallido con Triángulo Rojo)
 */
function renderizarMensajesChatwoot(mensajes, forzarScroll = false) {
    const list = document.getElementById('chatwoot-mensajes-lista');
    if (!list) return;

    // Detectar si el usuario está cerca del final para mantener el auto-scroll
    const isNearBottom = (list.scrollHeight - list.scrollTop - list.clientHeight) < 120;

    if (!mensajes || mensajes.length === 0) {
        list.innerHTML = `
            <div class="text-center py-20">
                <span class="material-symbols-outlined text-gray-300 text-5xl mb-2">chat_bubble_outline</span>
                <p class="text-gray-500 font-bold text-sm">No hay mensajes en el chat</p>
                <p class="text-xs text-gray-400 mt-1">Escribe un mensaje abajo o elige una plantilla para interactuar vía WhatsApp.</p>
            </div>
        `;
        return;
    }

    list.innerHTML = '';
    let ultimaFechaHeader = null;

    mensajes.forEach(msg => {
        const contenido = (msg.content || '').trim();
        if (!contenido) return;

        // Mensaje de actividad del sistema
        if (msg.message_type === 2 || msg.message_type === 'activity') {
            list.innerHTML += `
                <div class="flex justify-center my-2">
                    <span class="bg-gray-200/80 text-gray-600 text-[10px] font-semibold px-3 py-1 rounded-full text-center shadow-xs">
                        ${contenido}
                    </span>
                </div>
            `;
            return;
        }

        // Encabezado de fecha (Hoy, Ayer, Fecha completa)
        const fechaHeader = (typeof formatFechaHeaderChatwoot === 'function') 
            ? formatFechaHeaderChatwoot(msg.created_at) 
            : null;

        if (fechaHeader && fechaHeader !== ultimaFechaHeader) {
            ultimaFechaHeader = fechaHeader;
            list.innerHTML += `
                <div class="flex justify-center my-3.5">
                    <span class="bg-gray-200/90 text-gray-700 text-xs font-bold px-3.5 py-1 rounded-full shadow-2xs">
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

        // Renderizado del pie de la burbuja con hora y estado
        let footerHtml = '';
        if (isIncoming) {
            footerHtml = `
                <div class="flex items-center justify-start gap-1 mt-1 text-[9px] font-semibold text-gray-400">
                    <span>${horaStr}</span>
                </div>
            `;
        } else {
            const status = msg.status || 'sent';

            if (status === 'sending') {
                footerHtml = `
                    <div class="flex items-center justify-end gap-1.5 mt-1 text-[9px] font-semibold text-charcoal/70">
                        <span>${horaStr}</span>
                        <span class="inline-flex items-center gap-1 bg-black/10 text-charcoal px-1.5 py-0.5 rounded text-[8.5px] font-bold">
                            <span class="animate-spin inline-block w-2.5 h-2.5 border-2 border-charcoal border-t-transparent rounded-full"></span>
                            <span>Enviando...</span>
                        </span>
                    </div>
                `;
            } else if (status === 'failed' || status === 'error') {
                footerHtml = `
                    <div class="flex items-center justify-end gap-1.5 mt-1">
                        <span class="text-[9px] text-gray-500 font-semibold">${horaStr}</span>
                        <span class="inline-flex items-center gap-0.5 text-red-600 bg-red-100 px-1.5 py-0.5 rounded text-[9px] font-bold shadow-2xs" title="Error al enviar el mensaje">
                            <span class="material-symbols-outlined text-[13px] text-red-600">warning</span>
                            <span>No enviado</span>
                        </span>
                    </div>
                `;
            } else {
                // Mensaje enviado con éxito: check
                footerHtml = `
                    <div class="flex items-center justify-end gap-1 mt-1 text-[9px] font-semibold text-charcoal/75">
                        <span>${horaStr}</span>
                        <span class="material-symbols-outlined text-[14px] leading-none text-charcoal/80" title="Enviado">check</span>
                    </div>
                `;
            }
        }

        list.innerHTML += `
            <div class="${rowClass}" data-msg-id="${msg.id || ''}">
                <div class="${bubbleClass}">
                    <p class="text-sm whitespace-pre-wrap leading-relaxed">${contenido}</p>
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

        if (result.success && result.data) {
            const data = result.data;
            conversationIdActual = data.conversation_id;
            plantillasDisponibles = data.plantillas || [];
            clienteDatosActuales = data.cliente || {};

            const phoneEl = document.getElementById('chatwoot-modal-telefono');
            const convIdEl = document.getElementById('chatwoot-modal-conv-id');
            const titleEl = document.getElementById('chatwoot-modal-nombre-cliente');

            if (titleEl && (!titleEl.innerText || titleEl.innerText === 'Cargando chat...' || titleEl.innerText === 'Cliente')) {
                titleEl.innerText = clienteDatosActuales.nombre || 'Cliente';
            }
            if (phoneEl) phoneEl.innerText = clienteDatosActuales.telefono_whatsapp || '';
            if (convIdEl) {
                convIdEl.innerText = conversationIdActual ? `Conv. #${conversationIdActual}` : 'Sin conv. en Chatwoot';
            }

            // Alerta de ventana de 24 horas
            const banner24h = document.getElementById('chatwoot-banner-24h');
            const inputMsg = document.getElementById('input-chatwoot-mensaje');
            if (data.is_24h_expired) {
                if (banner24h) banner24h.classList.remove('hidden-view');
                if (inputMsg) {
                    inputMsg.disabled = true;
                    inputMsg.placeholder = "Ventana de 24h expirada. Usa el botón de plantillas para enviar un mensaje...";
                    inputMsg.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-75');
                }
            } else {
                if (banner24h) banner24h.classList.add('hidden-view');
                if (inputMsg && inputMsg.disabled) {
                    inputMsg.disabled = false;
                    inputMsg.placeholder = "Escribe un mensaje para Chatwoot...";
                    inputMsg.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-75');
                }
            }

            const serverMessages = data.messages || [];

            // Filtrar mensajes temporales locales que aún no hayan sido devueltos por el servidor
            const tempPendingMessages = mensajesChatwoot.filter(m => m.is_temp && (m.status === 'sending' || m.status === 'failed'));
            const filteredTemp = tempPendingMessages.filter(temp => {
                const yaExisteEnServidor = serverMessages.some(srv => 
                    !srv.is_incoming && srv.content.trim() === temp.content.trim()
                );
                return !yaExisteEnServidor;
            });

            mensajesChatwoot = [...serverMessages, ...filteredTemp];
            renderizarMensajesChatwoot(mensajesChatwoot, !esSilencioso);

            // Actualizar contador global de mensajes nuevos en barra de navegación
            if (typeof verificarMensajesNuevosGlobal === 'function') {
                verificarMensajesNuevosGlobal();
            }
        } else if (!esSilencioso) {
            const list = document.getElementById('chatwoot-mensajes-lista');
            if (list) {
                list.innerHTML = `<p class="text-red-500 text-center py-8 text-sm font-bold">Error obteniendo conversación de Chatwoot.</p>`;
            }
        }
    } catch (err) {
        if (!esSilencioso) {
            console.error("Error al consultar chatwoot:", err);
            const list = document.getElementById('chatwoot-mensajes-lista');
            if (list) {
                list.innerHTML = `<p class="text-red-500 text-center py-8 text-sm font-bold">No se pudo conectar con el servidor de Chatwoot.</p>`;
            }
        }
    } finally {
        isFetchingChatwoot = false;
    }
}

/**
 * Inicia el polling recurrente cada 5 segundos mientras el modal esté abierto
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
 * Inicia una ráfaga de polling rápido (cada 1 segundo) tras enviar un mensaje
 */
function iniciarFastPolling() {
    if (fastPollingTimer) clearInterval(fastPollingTimer);
    fastPollingRemainingTicks = 6; // 6 comprobaciones cada 1 segundo

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
 * Detiene todos los timers de polling activos
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

/**
 * Abrir el modal de Chatwoot para un cliente o conversación
 */
async function abrirModalChatwoot(clienteIdOrObj, clienteNombreParam) {
    let clienteId = null;
    let convId = null;
    let clienteNombre = clienteNombreParam;
    let telefono = '';

    if (typeof clienteIdOrObj === 'object' && clienteIdOrObj !== null) {
        clienteId = clienteIdOrObj.cliente_id || clienteIdOrObj.id || null;
        convId = clienteIdOrObj.conversation_id || null;
        clienteNombre = clienteIdOrObj.cliente_nombre || clienteIdOrObj.nombre || clienteNombreParam;
        telefono = clienteIdOrObj.telefono_whatsapp || clienteIdOrObj.telefono || '';
    } else {
        clienteId = clienteIdOrObj;
    }

    const modal = document.getElementById('modal-chatwoot');
    const title = document.getElementById('chatwoot-modal-nombre-cliente');
    const phoneEl = document.getElementById('chatwoot-modal-telefono');
    const convIdEl = document.getElementById('chatwoot-modal-conv-id');
    const list = document.getElementById('chatwoot-mensajes-lista');
    const inputMsg = document.getElementById('input-chatwoot-mensaje');
    const btnEnviar = document.getElementById('btn-enviar-chatwoot');

    if (!modal) return;

    detenerPollingChatwoot();
    cerrarModalPlantillas();

    clienteChatActualId = clienteId;
    conversationIdActual = convId;
    plantillasDisponibles = [];
    clienteDatosActuales = null;
    mensajesChatwoot = [];

    if (title) title.innerText = clienteNombre || 'Cliente';
    if (phoneEl) phoneEl.innerText = telefono || 'Cargando...';
    if (convIdEl) convIdEl.innerText = convId ? `Conv. #${convId}` : '';
    if (inputMsg) inputMsg.value = '';
    if (btnEnviar) btnEnviar.classList.add('hidden');

    list.innerHTML = `
        <div class="text-center py-20">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2"></div>
            <p class="text-xs text-gray-500 font-semibold">Cargando mensajes de Chatwoot...</p>
        </div>
    `;

    modal.classList.remove('hidden-view');

    // Primera carga
    await consultarActualizacionesChatwoot(false);

    // Activar polling en vivo cada 5s
    iniciarPollingChatwoot();
}

/**
 * Cerrar el modal y detener el polling
 */
function cerrarModalChatwoot() {
    detenerPollingChatwoot();
    cerrarModalPlantillas();
    const modal = document.getElementById('modal-chatwoot');
    if (modal) modal.classList.add('hidden-view');
    clienteChatActualId = null;
    mensajesChatwoot = [];
}

/**
 * Controla la visibilidad del botón de enviar según si hay texto escrito
 */
function alEscribirMensajeChatwoot() {
    const input = document.getElementById('input-chatwoot-mensaje');
    const btnEnviar = document.getElementById('btn-enviar-chatwoot');
    if (!input || !btnEnviar) return;

    if (input.value.trim().length > 0) {
        btnEnviar.classList.remove('hidden');
    } else {
        btnEnviar.classList.add('hidden');
    }
}

/**
 * Enviar mensaje saliente con estado optimista y polling de confirmación
 */
async function enviarMensajeChatwoot() {
    const input = document.getElementById('input-chatwoot-mensaje');
    const btnEnviar = document.getElementById('btn-enviar-chatwoot');
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
    if (btnEnviar) btnEnviar.classList.add('hidden');

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

/**
 * Abre el Modal de Plantillas WhatsApp (con fondo gris/oscuro superpuesto)
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
        // Seleccionar por defecto la primera para conveniencia del usuario
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
 * Controlador al cambiar la selección en el selector de plantillas del modal
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
            valorDefecto = hoyStr;
        } else if (varName === 'sucursal') {
            valorDefecto = clienteDatosActuales?.sucursal_nombre || '';
        } else if (varName === 'ruta') {
            valorDefecto = clienteDatosActuales?.ruta_nombre || '';
        } else if (varName === 'motivo') {
            valorDefecto = 'Ajuste de programación';
        }

        listaInputs.innerHTML += `
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">${labelLimpio} <span class="text-red-500">*</span></label>
                <input type="text" data-var="${varName}" value="${valorDefecto}" oninput="actualizarPreviewPlantillaModal()" class="input-var-plantilla-modal w-full p-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
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
 * Actualiza la vista previa del mensaje formateado en tiempo real con variables en negrilla
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
            ? `<strong class="font-bold text-charcoal bg-amber-100/80 px-1.5 py-0.5 rounded shadow-2xs">${val}</strong>` 
            : `<strong class="font-bold text-amber-700 bg-amber-100/80 px-1.5 py-0.5 rounded shadow-2xs">[${varName}]</strong>`;
        textoHtml = textoHtml.replace(regex, formattedVal);
    });

    txtPreview.innerHTML = textoHtml;
}

/**
 * Envía la plantilla oficial con template_params a Chatwoot y actualiza la interfaz
 */
async function enviarPlantillaConfirmada() {
    if (!plantillaSeleccionadaActual || !conversationIdActual) {
        alert("No hay una conversación activa o plantilla seleccionada.");
        return;
    }

    // Recolectar parámetros procesados de las variables y construir el texto plano final
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

    // Cerrar el modal de plantilla
    cerrarModalPlantillas();

    // 1. Mensaje temporal optimista con estado "sending"
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

    // 2. Iniciar polling rápido a 1s
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

// Cierre de modales con tecla Escape
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

// Exposición Global en window para invocación desde cualquier módulo o vista
window.abrirModalChatwoot = abrirModalChatwoot;
window.cerrarModalChatwoot = cerrarModalChatwoot;
window.alEscribirMensajeChatwoot = alEscribirMensajeChatwoot;
window.enviarMensajeChatwoot = enviarMensajeChatwoot;
window.abrirModalPlantillas = abrirModalPlantillas;
window.cerrarModalPlantillas = cerrarModalPlantillas;
window.alCambiarPlantillaEnModal = alCambiarPlantillaEnModal;
window.actualizarPreviewPlantillaModal = actualizarPreviewPlantillaModal;
window.enviarPlantillaConfirmada = enviarPlantillaConfirmada;
