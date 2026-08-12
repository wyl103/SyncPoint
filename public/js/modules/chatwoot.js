// public/js/modules/chatwoot.js
// Lógica del Panel Modal de Chatwoot, Mensajes, Restricción de 24h y Plantillas

let clienteChatActualId = null;
let conversationIdActual = null;
let plantillasDisponibles = [];
let clienteDatosActuales = null;

async function abrirModalChatwoot(clienteIdOrObj, clienteNombreParam) {
    let clienteId = clienteIdOrObj;
    let clienteNombre = clienteNombreParam;

    if (typeof clienteIdOrObj === 'object' && clienteIdOrObj !== null) {
        clienteId = clienteIdOrObj.cliente_id || clienteIdOrObj.id;
        clienteNombre = clienteIdOrObj.cliente_nombre || clienteIdOrObj.nombre || clienteNombreParam;
    }

    const modal = document.getElementById('modal-chatwoot');
    const title = document.getElementById('chatwoot-modal-nombre-cliente');
    const phoneEl = document.getElementById('chatwoot-modal-telefono');
    const convIdEl = document.getElementById('chatwoot-modal-conv-id');
    const list = document.getElementById('chatwoot-mensajes-lista');
    const inputMsg = document.getElementById('input-chatwoot-mensaje');
    const panelPlantillas = document.getElementById('panel-plantillas-chatwoot');

    if (!modal) return;

    clienteChatActualId = clienteId;
    conversationIdActual = null;
    plantillasDisponibles = [];
    clienteDatosActuales = null;

    if (panelPlantillas) panelPlantillas.classList.add('hidden-view');
    if (title) title.innerText = clienteNombre || 'Cliente';
    if (phoneEl) phoneEl.innerText = 'Cargando...';
    if (convIdEl) convIdEl.innerText = '';
    if (inputMsg) inputMsg.value = '';

    list.innerHTML = `<div class="text-center py-16"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2"></div><p class="text-xs text-gray-500 font-semibold">Cargando mensajes de Chatwoot...</p></div>`;

    modal.classList.remove('hidden-view');

    try {
        const response = await fetch(`${API_BASE}/chatwoot/index.php?cliente_id=${clienteId}`);
        const result = await response.json();

        if (result.success && result.data) {
            const data = result.data;
            conversationIdActual = data.conversation_id;
            plantillasDisponibles = data.plantillas || [];
            clienteDatosActuales = data.cliente || {};

            if (phoneEl) phoneEl.innerText = clienteDatosActuales.telefono_whatsapp || '';
            if (convIdEl) {
                convIdEl.innerText = conversationIdActual ? `Conv. #${conversationIdActual}` : 'Sin conv. en Chatwoot';
            }

            const banner24h = document.getElementById('chatwoot-banner-24h');
            if (data.is_24h_expired) {
                if (banner24h) banner24h.classList.remove('hidden-view');
                if (inputMsg) {
                    inputMsg.disabled = true;
                    inputMsg.placeholder = "Ventana de 24h expirada. Usa una plantilla de mensaje...";
                    inputMsg.classList.add('bg-gray-100', 'cursor-not-allowed', 'opacity-75');
                }
            } else {
                if (banner24h) banner24h.classList.add('hidden-view');
                if (inputMsg) {
                    inputMsg.disabled = false;
                    inputMsg.placeholder = "Escribe un mensaje para Chatwoot...";
                    inputMsg.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-75');
                }
            }

            llenarSelectorPlantillas();

            const mensajes = data.messages || [];

            if (mensajes.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-16">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-2">chat_bubble_outline</span>
                        <p class="text-gray-500 font-bold text-sm">No hay mensajes en el chat</p>
                        <p class="text-xs text-gray-400 mt-1">Escribe un mensaje abajo para interactuar via Chatwoot.</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = '';
            let ultimaFechaHeader = null;

            mensajes.forEach(msg => {
                const contenido = (msg.content || '').trim();

                if (!contenido) return;

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

                const fechaHeader = formatFechaHeaderChatwoot(msg.created_at);
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

                const horaStr = formatHoraChatwoot(msg.created_at);
                const isIncoming = Boolean(msg.is_incoming);

                const rowClass = isIncoming ? 'chat-row chat-row-incoming' : 'chat-row chat-row-outgoing';
                const bubbleClass = isIncoming ? 'chat-bubble-base chat-bubble-incoming' : 'chat-bubble-base chat-bubble-outgoing';

                list.innerHTML += `
                    <div class="${rowClass}">
                        <div class="${bubbleClass}">
                            <p class="text-sm whitespace-pre-wrap leading-relaxed">${contenido}</p>
                            ${horaStr ? `<div class="text-[8.5px] text-right opacity-60 mt-1 font-semibold">${horaStr}</div>` : ''}
                        </div>
                    </div>
                `;
            });

            list.scrollTop = list.scrollHeight;
        } else {
            list.innerHTML = `<p class="text-red-500 text-center py-8 text-sm font-bold">Error obteniendo conversación de Chatwoot.</p>`;
        }
    } catch (err) {
        console.error("Error al cargar chatwoot:", err);
        list.innerHTML = `<p class="text-red-500 text-center py-8 text-sm font-bold">No se pudo conectar con el servidor de Chatwoot.</p>`;
    }
}

function cerrarModalChatwoot() {
    const modal = document.getElementById('modal-chatwoot');
    if (modal) modal.classList.add('hidden-view');
}

function togglePanelPlantillas() {
    const panel = document.getElementById('panel-plantillas-chatwoot');
    if (panel) panel.classList.toggle('hidden-view');
}

function llenarSelectorPlantillas() {
    const select = document.getElementById('select-plantilla-chatwoot');
    if (!select) return;

    select.innerHTML = `<option value="">-- Selecciona una plantilla --</option>`;
    plantillasDisponibles.forEach(plantilla => {
        select.innerHTML += `<option value="${plantilla.id}">${plantilla.titulo}</option>`;
    });

    const containerVars = document.getElementById('contenedor-variables-plantilla');
    const boxPreview = document.getElementById('box-preview-plantilla');
    if (containerVars) containerVars.innerHTML = '';
    if (boxPreview) boxPreview.classList.add('hidden');
}

function alSeleccionarPlantillaChatwoot() {
    const select = document.getElementById('select-plantilla-chatwoot');
    const containerVars = document.getElementById('contenedor-variables-plantilla');
    const boxPreview = document.getElementById('box-preview-plantilla');

    if (!select || !containerVars) return;

    const plantillaId = select.value;
    const plantilla = plantillasDisponibles.find(p => p.id === plantillaId);

    if (!plantilla) {
        containerVars.innerHTML = '';
        if (boxPreview) boxPreview.classList.add('hidden');
        return;
    }

    containerVars.innerHTML = '';
    const hoyStr = formatLocalIso(new Date());

    (plantilla.variables || []).forEach(varName => {
        let valorDefecto = '';
        if (varName === 'cliente') valorDefecto = clienteDatosActuales?.nombre || '';
        else if (varName === 'sucursal') valorDefecto = clienteDatosActuales?.sucursal_nombre || '';
        else if (varName === 'ruta') valorDefecto = clienteDatosActuales?.ruta_nombre || '';
        else if (varName === 'fecha') valorDefecto = hoyStr;

        containerVars.innerHTML += `
            <div>
                <label class="block text-[11px] font-bold text-gray-600 mb-0.5 capitalize">Variable {{${varName}}}</label>
                <input type="text" data-var="${varName}" value="${valorDefecto}" oninput="actualizarPreviewPlantilla()" class="input-var-plantilla w-full p-2 rounded-md border border-gray-200 bg-white text-xs font-semibold text-charcoal outline-none focus:border-primary">
            </div>
        `;
    });

    actualizarPreviewPlantilla();
}

function actualizarPreviewPlantilla() {
    const select = document.getElementById('select-plantilla-chatwoot');
    const boxPreview = document.getElementById('box-preview-plantilla');
    const txtPreview = document.getElementById('txt-preview-plantilla');

    if (!select) return;

    const plantillaId = select.value;
    const plantilla = plantillasDisponibles.find(p => p.id === plantillaId);

    if (!plantilla) {
        if (boxPreview) boxPreview.classList.add('hidden');
        return;
    }

    let textoFinal = plantilla.texto;
    document.querySelectorAll('.input-var-plantilla').forEach(input => {
        const varName = input.getAttribute('data-var');
        const val = input.value.trim();
        const regex = new RegExp(`{{\\s*${varName}\\s*}}`, 'g');
        textoFinal = textoFinal.replace(regex, val || `[${varName}]`);
    });

    if (txtPreview) txtPreview.innerText = textoFinal;
    if (boxPreview) boxPreview.classList.remove('hidden');
}

function usarPlantillaEnChat() {
    const txtPreview = document.getElementById('txt-preview-plantilla');
    const inputMsg = document.getElementById('input-chatwoot-mensaje');

    if (!txtPreview || !inputMsg) return;

    const textoFinal = txtPreview.innerText.trim();
    if (!textoFinal) return;

    inputMsg.disabled = false;
    inputMsg.classList.remove('bg-gray-100', 'cursor-not-allowed', 'opacity-75');
    inputMsg.value = textoFinal;
    togglePanelPlantillas();
    inputMsg.focus();
}

async function enviarMensajeChatwoot() {
    const input = document.getElementById('input-chatwoot-mensaje');
    const list = document.getElementById('chatwoot-mensajes-lista');
    if (!input) return;

    const texto = input.value.trim();
    if (!texto) return;

    if (!conversationIdActual) {
        alert("No hay una conversación activa de Chatwoot para este cliente.");
        return;
    }

    list.innerHTML += `
        <div class="chat-row chat-row-outgoing">
            <div class="chat-bubble-base chat-bubble-outgoing">
                <p class="text-sm whitespace-pre-wrap leading-relaxed">${texto}</p>
                <div class="text-[8.5px] text-right opacity-60 mt-1 font-semibold">Enviando...</div>
            </div>
        </div>
    `;
    list.scrollTop = list.scrollHeight;
    input.value = '';

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
        if (!result.success) {
            console.error("Error al enviar mensaje:", result.message);
        }
    } catch (err) {
        console.error("Error enviando mensaje a Chatwoot:", err);
    }
}

// Exposición Global en window para invocación desde cualquier módulo o botón
window.abrirModalChatwoot = abrirModalChatwoot;
window.cerrarModalChatwoot = cerrarModalChatwoot;
window.enviarMensajeChatwoot = enviarMensajeChatwoot;
window.togglePanelPlantillas = togglePanelPlantillas;
window.alSeleccionarPlantillaChatwoot = alSeleccionarPlantillaChatwoot;
window.actualizarPreviewPlantilla = actualizarPreviewPlantilla;
window.usarPlantillaEnChat = usarPlantillaEnChat;
