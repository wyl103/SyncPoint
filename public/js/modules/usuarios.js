// public/js/modules/usuarios.js
// Lógica del Módulo de Usuarios (Directorio, Subtabs y Modal)

let paginaActualUsuarios = 1;
let debounceTimerUsuarios = null;

function cambiarSubTabUsuario(subTab) {
    const btnDirectorio = document.getElementById('header-subtab-usuarios-directorio');
    const btnProgramacion = document.getElementById('header-subtab-usuarios-programacion');
    const viewDirectorio = document.getElementById('subtab-directorio-usuarios');
    const viewProgramacion = document.getElementById('subtab-programacion-usuarios');

    if (!viewDirectorio || !viewProgramacion) return;

    const activeTextClass = 'text-charcoal font-extrabold cursor-pointer hover:text-black transition border-b-2 border-primary pb-0.5';
    const inactiveTextClass = 'text-gray-400 font-semibold cursor-pointer hover:text-charcoal transition border-b-2 border-transparent pb-0.5';

    if (subTab === 'directorio') {
        viewDirectorio.classList.remove('hidden-view');
        viewProgramacion.classList.add('hidden-view');

        if (btnDirectorio) btnDirectorio.className = activeTextClass;
        if (btnProgramacion) btnProgramacion.className = inactiveTextClass;

        cargarUsuarios(paginaActualUsuarios);
    } else if (subTab === 'programacion') {
        viewDirectorio.classList.add('hidden-view');
        viewProgramacion.classList.remove('hidden-view');

        if (btnProgramacion) btnProgramacion.className = activeTextClass;
        if (btnDirectorio) btnDirectorio.className = inactiveTextClass;

        cargarConfiguracionProgramacion();
        inicializarEnvioMasivoNotificaciones();
    }
}

async function cargarUsuarios(page = 1) {
    paginaActualUsuarios = page;
    const container = document.getElementById('lista-usuarios');
    const paginacionContainer = document.getElementById('paginacion-usuarios');
    const inputSearch = document.getElementById('input-buscar-usuario');

    if (!container) return;

    container.innerHTML = `
        <div class="p-8 text-center text-gray-500 bg-white border border-gray-200 rounded-xl">
            <span class="material-symbols-outlined text-3xl animate-spin text-primary mb-2">progress_activity</span>
            <p class="text-sm font-semibold">Cargando lista de usuarios...</p>
        </div>
    `;

    try {
        const query = inputSearch ? inputSearch.value.trim() : '';
        const url = `${API_BASE}/core/usuarios.php?page=${page}&limit=10&q=${encodeURIComponent(query)}`;

        const response = await fetch(url);
        const result = await response.json();

        if (!result.success || !result.data || result.data.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center text-gray-500 bg-white border border-gray-200 rounded-xl space-y-2">
                    <span class="material-symbols-outlined text-4xl text-gray-300">manage_accounts</span>
                    <p class="text-sm font-bold text-gray-700">No se encontraron usuarios</p>
                    <p class="text-xs text-gray-400">Intenta cambiar el término de búsqueda o agrega un nuevo usuario.</p>
                </div>
            `;
            if (paginacionContainer) paginacionContainer.innerHTML = '';
            return;
        }

        container.innerHTML = '';
        result.data.forEach(user => {
            let isAdmin = (user.tipo || 'normal').toLowerCase() === 'administrador';
            let badgeClass = isAdmin ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-gray-100 text-gray-600 border-gray-200';
            let badgeText = isAdmin ? 'ADMINISTRADOR' : 'NORMAL';

            container.innerHTML += `
                <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-xs flex flex-col justify-between gap-3 hover:border-primary/70 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary/20 text-charcoal flex items-center justify-center font-bold text-base shrink-0">
                            ${(user.nombre || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-bold text-charcoal text-base">${user.nombre}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 border text-[10px] font-bold rounded-md uppercase tracking-wider ${badgeClass}">
                                    ${badgeText}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[14px]">mail</span> ${user.correo}
                            </p>
                        </div>
                    </div>

                    <!-- Botones separados holgadamente de la línea superior -->
                    <div class="flex items-center gap-2 pt-3.5 mt-1 border-t border-gray-100 justify-start">
                        <button onclick="editarUsuario(${user.id}, '${(user.nombre || '').replace(/'/g, "\\'")}', '${user.correo}', '${user.tipo || 'normal'}')" class="btn-card-edit" title="Editar usuario">
                            <span class="material-symbols-outlined text-[16px]">edit</span> Editar
                        </button>
                        <button onclick="eliminarUsuario(${user.id}, '${(user.nombre || '').replace(/'/g, "\\'")}')" class="px-3 py-1.5 rounded-xl bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 text-xs font-bold transition flex items-center gap-1 shadow-xs" title="Eliminar usuario">
                            <span class="material-symbols-outlined text-[16px]">delete</span> Eliminar
                        </button>
                    </div>
                </div>
            `;
        });

        // Renderizar Paginación
        if (paginacionContainer && result.pagination) {
            const { page, total_pages, total } = result.pagination;
            paginacionContainer.innerHTML = `
                <span class="text-xs text-gray-500 font-medium">Página <strong>${page}</strong> de <strong>${total_pages}</strong> (${total} usuarios)</span>
                <div class="flex gap-2">
                    <button onclick="cargarUsuarios(${page - 1})" ${page <= 1 ? 'disabled' : ''} class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg disabled:opacity-50 hover:bg-gray-200 transition">Anterior</button>
                    <button onclick="cargarUsuarios(${page + 1})" ${page >= total_pages ? 'disabled' : ''} class="px-3 py-1 bg-primary text-charcoal text-xs font-bold rounded-lg disabled:opacity-50 hover:bg-yellow-400 transition">Siguiente</button>
                </div>
            `;
        }
    } catch (err) {
        console.error("Error cargando usuarios:", err);
        container.innerHTML = `
            <div class="p-6 text-center text-red-600 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm font-bold">Error de conexión al cargar la lista de usuarios.</p>
            </div>
        `;
    }
}

function filtrarUsuariosDebounced() {
    clearTimeout(debounceTimerUsuarios);
    debounceTimerUsuarios = setTimeout(() => {
        cargarUsuarios(1);
    }, 300);
}

function abrirModalCrearUsuario() {
    const modal = document.getElementById('modal-crear-usuario');
    const titulo = document.getElementById('modal-usuario-titulo');
    const form = document.getElementById('form-crear-usuario');
    const helpPass = document.getElementById('txt-usuario-pass-help');
    const passInput = document.getElementById('form-usuario-password');
    const selectTipo = document.getElementById('form-usuario-tipo');

    if (!modal) return;

    if (form) form.reset();
    document.getElementById('form-usuario-id').value = '';
    if (selectTipo) selectTipo.value = 'normal';
    
    if (titulo) titulo.innerHTML = `<span class="material-symbols-outlined text-primary">person_add</span> Nuevo Usuario`;
    if (helpPass) helpPass.classList.add('hidden');
    if (passInput) passInput.required = true;

    modal.classList.remove('hidden-view');
}

function editarUsuario(id, nombre, correo, tipo = 'normal') {
    const modal = document.getElementById('modal-crear-usuario');
    const titulo = document.getElementById('modal-usuario-titulo');
    const helpPass = document.getElementById('txt-usuario-pass-help');
    const passInput = document.getElementById('form-usuario-password');
    const selectTipo = document.getElementById('form-usuario-tipo');

    if (!modal) return;

    document.getElementById('form-usuario-id').value = id;
    document.getElementById('form-usuario-nombre').value = nombre;
    document.getElementById('form-usuario-correo').value = correo;
    document.getElementById('form-usuario-password').value = '';
    if (selectTipo) selectTipo.value = tipo || 'normal';

    if (titulo) titulo.innerHTML = `<span class="material-symbols-outlined text-primary">edit</span> Editar Usuario`;
    if (helpPass) helpPass.classList.remove('hidden');
    if (passInput) passInput.required = false;

    modal.classList.remove('hidden-view');
}

function cerrarModalCrearUsuario() {
    const modal = document.getElementById('modal-crear-usuario');
    if (modal) modal.classList.add('hidden-view');
}

async function guardarUsuario() {
    const id = document.getElementById('form-usuario-id')?.value;
    const nombre = document.getElementById('form-usuario-nombre')?.value.trim();
    const correo = document.getElementById('form-usuario-correo')?.value.trim();
    const password = document.getElementById('form-usuario-password')?.value.trim();
    const tipo = document.getElementById('form-usuario-tipo')?.value || 'normal';
    const btnGuardar = document.getElementById('btn-guardar-usuario');

    if (!nombre || !correo) {
        alert("Por favor completa el nombre y el correo electrónico.");
        return;
    }

    if (!id && !password) {
        alert("La contraseña es requerida para un nuevo usuario.");
        return;
    }

    if (btnGuardar) { btnGuardar.disabled = true; btnGuardar.innerText = "Guardando..."; }

    try {
        const isEdit = !!id;
        const url = isEdit ? `${API_BASE}/core/usuarios.php?id=${id}` : `${API_BASE}/core/usuarios.php`;
        const method = isEdit ? 'PUT' : 'POST';

        const payload = { nombre, correo, tipo };
        if (password) payload.password = password;

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success || response.ok) {
            cerrarModalCrearUsuario();
            cargarUsuarios(paginaActualUsuarios);
        } else {
            alert(result.message || "Error al guardar el usuario.");
        }
    } catch (err) {
        console.error("Error guardando usuario:", err);
        alert("Error de conexión al guardar el usuario.");
    } finally {
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = `<span class="material-symbols-outlined text-[18px]">save</span> Guardar Usuario`;
        }
    }
}

async function eliminarUsuario(id, nombre) {
    if (!confirm(`¿Estás seguro de que deseas eliminar al usuario '${nombre}'? Esta acción no se puede deshacer.`)) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/core/usuarios.php?id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (result.success || response.ok) {
            cargarUsuarios(paginaActualUsuarios);
        } else {
            alert(result.message || "Error al eliminar el usuario.");
        }
    } catch (err) {
        console.error("Error eliminando usuario:", err);
        alert("Error de conexión al eliminar el usuario.");
    }
}

async function ejecutarProgramacionGlobalEventos() {
    const btn = document.getElementById('btn-programar-eventos-global');
    const inputHorizonte = document.getElementById('input-dias-horizonte');
    const resContainer = document.getElementById('resultado-programacion-global');

    const diasHorizonte = inputHorizonte ? parseInt(inputHorizonte.value) || 30 : 30;

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="material-symbols-outlined text-[20px] animate-spin">progress_activity</span> Calculando y Agendando...`;
    }

    if (resContainer) {
        resContainer.classList.remove('hidden');
        resContainer.innerHTML = `
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-xs font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                Procesando recolecciones masivas para los próximos ${diasHorizonte} días...
            </div>
        `;
    }

    try {
        const response = await fetch(`${API_BASE}/eventos/programar_global.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ dias_horizonte: diasHorizonte })
        });

        const result = await response.json();

        if (result.success && result.data) {
            const data = result.data;
            resContainer.innerHTML = `
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span> Programación Completada
                        </span>
                        <span class="text-[11px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md">${data.dias_horizonte} Días Proyectados</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs pt-1 border-t border-emerald-200/60">
                        <div><span class="text-emerald-700">Fecha partida:</span> <strong>${data.fecha_mas_lejana_actual}</strong></div>
                        <div><span class="text-emerald-700">Fecha hasta:</span> <strong>${data.fecha_proyectada_hasta}</strong></div>
                        <div><span class="text-emerald-700">Clientes procesados:</span> <strong>${data.clientes_procesados}</strong></div>
                    </div>
                    <div class="p-2.5 bg-white/80 rounded-lg text-xs flex justify-between items-center font-bold text-charcoal shadow-2xs">
                        <span>Eventos Nuevos Creados: <strong class="text-emerald-600">${data.total_eventos_creados}</strong></span>
                        <span class="text-gray-500 font-normal">Preexistentes: ${data.total_eventos_existentes}</span>
                    </div>
                </div>
            `;
        } else {
            resContainer.innerHTML = `
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-semibold">
                    ${result.message || 'Ocurrió un error al ejecutar la programación de eventos.'}
                </div>
            `;
        }
    } catch (err) {
        console.error("Error en programación global de eventos:", err);
        if (resContainer) {
            resContainer.innerHTML = `
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-semibold">
                    Error de conexión al ejecutar la programación automática de eventos.
                </div>
            `;
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-[20px]">calendar_month</span> Programar Eventos`;
        }
    }
}

async function cargarConfiguracionProgramacion() {
    const toggle = document.getElementById('toggle-usar-dia-ruta');
    if (!toggle) return;

    try {
        const response = await fetch(`${API_BASE}/sistema/configuracion.php`);
        const result = await response.json();
        if (result.success && result.data) {
            toggle.checked = !!result.data.programacion_usar_dia_ruta;
        }
    } catch (err) {
        console.error("Error cargando configuración de programación:", err);
    }
}

async function guardarConfiguracionProgramacion(activo) {
    try {
        const response = await fetch(`${API_BASE}/sistema/configuracion.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ programacion_usar_dia_ruta: activo })
        });
        const result = await response.json();
        if (!result.success) {
            alert(result.message || "Error al actualizar la configuración.");
        }
    } catch (err) {
        console.error("Error guardando configuración de programación:", err);
        alert("Error de conexión al actualizar la configuración.");
    }
}

// =========================================================================
// MÓDULO: ENVÍO MASIVO DE NOTIFICACIONES WHATSAPP DE EVENTOS
// =========================================================================

let cacheDestinatariosMasivos = [];
let debounceTimerMasivo = null;

/**
 * Inicializar fechas por defecto (próximos 3 días) y consultar destinatarios
 */
function inicializarEnvioMasivoNotificaciones() {
    const inputDesde = document.getElementById('masivo-fecha-desde');
    const inputHasta = document.getElementById('masivo-fecha-hasta');

    if (inputDesde && !inputDesde.value) {
        const hoy = new Date();
        inputDesde.value = hoy.toISOString().split('T')[0];
    }

    if (inputHasta && !inputHasta.value) {
        const hoy = new Date();
        hoy.setDate(hoy.getDate() + 2); // 3 días en total (hoy + 2)
        inputHasta.value = hoy.toISOString().split('T')[0];
    }

    actualizarVistaPreviaPlantillaMasiva();
    consultarDestinatariosMasivos();
}

/**
 * Establecer rangos de fechas rápidos ('hoy', 'manana', 'proximos3')
 */
function establecerRangoFechasMasivo(tipo) {
    const inputDesde = document.getElementById('masivo-fecha-desde');
    const inputHasta = document.getElementById('masivo-fecha-hasta');
    if (!inputDesde || !inputHasta) return;

    const ahora = new Date();
    const hoyIso = ahora.toISOString().split('T')[0];

    if (tipo === 'hoy') {
        inputDesde.value = hoyIso;
        inputHasta.value = hoyIso;
    } else if (tipo === 'manana') {
        const manana = new Date(ahora);
        manana.setDate(manana.getDate() + 1);
        const mananaIso = manana.toISOString().split('T')[0];
        inputDesde.value = mananaIso;
        inputHasta.value = mananaIso;
    } else if (tipo === 'proximos3') {
        const hasta = new Date(ahora);
        hasta.setDate(hasta.getDate() + 2);
        inputDesde.value = hoyIso;
        inputHasta.value = hasta.toISOString().split('T')[0];
    }

    consultarDestinatariosMasivos();
}

/**
 * Marcar o desmarcar todos los checkboxes de estados
 */
function marcarTodosEstadosMasivos(marcar) {
    const checks = document.querySelectorAll('input[name="chk-estado-masivo"]');
    checks.forEach(chk => {
        chk.checked = !!marcar;
    });
    consultarDestinatariosMasivos();
}

/**
 * Actualizar texto de vista previa según la plantilla seleccionada
 */
function actualizarVistaPreviaPlantillaMasiva() {
    const select = document.getElementById('select-plantilla-masiva');
    const previewTexto = document.getElementById('preview-plantilla-masiva-texto');
    if (!select || !previewTexto) return;

    const val = select.value;
    if (val === 'confirmacion_entrega') {
        previewTexto.innerHTML = `"Hola <strong>[Nombre Cliente]</strong>, te escribimos de OilBless (GreenFuel) para confirmar tu servicio del día <strong>[Fecha Programada]</strong>. 🚛♻️<br><br>¿Podemos pasar a retirar el aceite usado en esa fecha?"`;
    } else if (val === 'hola_oilbless') {
        previewTexto.innerHTML = `"¡Hola <strong>[Nombre Cliente]</strong>! Bienvenido a nuestro canal oficial de WhatsApp de OilBless (GreenFuel). 🚛♻️"`;
    } else if (val === 'hello_world') {
        previewTexto.innerHTML = `"Hello World"`;
    } else {
        previewTexto.innerHTML = `"Notificación de servicio OilBless para <strong>[Nombre Cliente]</strong> el día <strong>[Fecha Programada]</strong>."`;
    }
}

/**
 * Consultar en el backend cuántos clientes/eventos cumplen con los filtros
 */
function consultarDestinatariosMasivos() {
    clearTimeout(debounceTimerMasivo);
    debounceTimerMasivo = setTimeout(async () => {
        const inputDesde = document.getElementById('masivo-fecha-desde');
        const inputHasta = document.getElementById('masivo-fecha-hasta');
        const numBadge = document.getElementById('conteo-destinatarios-numero');
        const btnEnviar = document.getElementById('btn-ejecutar-envio-masivo');
        const txtBtnEnviar = document.getElementById('texto-boton-envio-masivo');
        const tbody = document.getElementById('tbody-destinatarios-masivos');

        const fechaDesde = inputDesde ? inputDesde.value : '';
        const fechaHasta = inputHasta ? inputHasta.value : '';

        const checks = document.querySelectorAll('input[name="chk-estado-masivo"]:checked');
        const estados = Array.from(checks).map(c => c.value);

        if (!fechaDesde || !fechaHasta || estados.length === 0) {
            if (numBadge) numBadge.innerText = '0';
            if (txtBtnEnviar) txtBtnEnviar.innerText = 'Enviar Notificaciones a 0 Clientes';
            if (btnEnviar) btnEnviar.disabled = true;
            if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-gray-400 font-medium italic">Selecciona al menos una fecha y un estado para consultar destinatarios.</td></tr>`;
            cacheDestinatariosMasivos = [];
            return;
        }

        if (numBadge) numBadge.innerHTML = `<span class="inline-block animate-pulse text-gray-400">Consultando...</span>`;

        try {
            const queryParams = new URLSearchParams({
                fecha_desde: fechaDesde,
                fecha_hasta: fechaHasta,
                estados: estados.join(',')
            });

            const response = await fetch(`${API_BASE}/eventos/envio_masivo.php?${queryParams.toString()}`);
            const result = await response.json();

            if (result.success && Array.isArray(result.eventos)) {
                cacheDestinatariosMasivos = result.eventos;
                const total = cacheDestinatariosMasivos.length;

                if (numBadge) numBadge.innerText = total;
                if (txtBtnEnviar) txtBtnEnviar.innerText = `Enviar Notificaciones a los ${total} Clientes`;
                if (btnEnviar) btnEnviar.disabled = (total === 0);

                renderizarTablaDestinatariosMasivos(cacheDestinatariosMasivos);
            } else {
                if (numBadge) numBadge.innerText = '0';
                if (txtBtnEnviar) txtBtnEnviar.innerText = 'Enviar Notificaciones';
                if (btnEnviar) btnEnviar.disabled = true;
                if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-red-500 font-medium">${result.message || 'Error al consultar destinatarios.'}</td></tr>`;
            }
        } catch (err) {
            console.error("Error consultando destinatarios:", err);
            if (numBadge) numBadge.innerText = '0';
            if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-red-500 font-medium">Error de conexión al consultar destinatarios.</td></tr>`;
        }
    }, 250);
}

/**
 * Renderiza la lista de preview de destinatarios
 */
function renderizarTablaDestinatariosMasivos(eventos) {
    const tbody = document.getElementById('tbody-destinatarios-masivos');
    if (!tbody) return;

    if (eventos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-gray-400 font-medium italic">No se encontraron eventos programados para las fechas y estados seleccionados.</td></tr>`;
        return;
    }

    const stateStyles = {
        aceptado: 'bg-emerald-50 text-emerald-700 border-emerald-300 font-bold',
        aceptada: 'bg-emerald-50 text-emerald-700 border-emerald-300 font-bold',
        rechazado: 'bg-red-50 text-red-700 border-red-300 font-bold',
        rechazada: 'bg-red-50 text-red-700 border-red-300 font-bold',
        denegada: 'bg-red-50 text-red-700 border-red-300 font-bold'
    };

    tbody.innerHTML = eventos.map(ev => {
        const est = (ev.estado || '').toLowerCase();
        const stClass = stateStyles[est] || 'bg-transparent text-charcoal border-charcoal font-bold';
        return `
            <tr class="hover:bg-gray-50/80 transition">
                <td class="p-2.5 font-bold text-charcoal">${escapeHtml(ev.cliente_nombre)}</td>
                <td class="p-2.5 font-mono text-gray-600">${escapeHtml(ev.cliente_telefono || 'Sin teléfono')}</td>
                <td class="p-2.5 font-semibold text-charcoal">${ev.fecha_programada}</td>
                <td class="p-2.5">
                    <span class="px-2 py-0.5 rounded-md text-[10.5px] font-bold border ${stClass}">
                        ${escapeHtml(ev.estado)}
                    </span>
                </td>
                <td class="p-2.5 text-gray-500 font-medium">
                    ${escapeHtml(ev.ruta_nombre || 'N/A')} <span class="text-gray-300">/</span> ${escapeHtml(ev.sucursal_nombre || 'N/A')}
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Alternar visibilidad de la tabla de detalles
 */
function alternarListaPreviewDestinatarios() {
    const cont = document.getElementById('contenedor-tabla-preview-destinatarios');
    const txt = document.getElementById('texto-toggle-preview');
    if (!cont) return;

    if (cont.classList.contains('hidden')) {
        cont.classList.remove('hidden');
        if (txt) txt.innerText = 'Ocultar detalles';
    } else {
        cont.classList.add('hidden');
        if (txt) txt.innerText = 'Ver detalles';
    }
}

/**
 * Confirmar y ejecutar el envío masivo
 */
async function confirmarYEjecutarEnvioMasivo() {
    if (!cacheDestinatariosMasivos || cacheDestinatariosMasivos.length === 0) {
        alert("No hay destinatarios seleccionados para enviar notificaciones.");
        return;
    }

    const total = cacheDestinatariosMasivos.length;
    const select = document.getElementById('select-plantilla-masiva');
    const plantillaId = select ? select.value : 'confirmacion_entrega';

    const confirmar = confirm(`¿Estás seguro de enviar la notificación por WhatsApp a los ${total} clientes seleccionados?\n\nPlantilla: ${plantillaId}\n\nLos eventos se actualizarán automáticamente.`);
    if (!confirmar) return;

    const btn = document.getElementById('btn-ejecutar-envio-masivo');
    const resContainer = document.getElementById('resultado-envio-masivo');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="material-symbols-outlined text-[20px] animate-spin">progress_activity</span> Enviando notificaciones...`;
    }

    if (resContainer) {
        resContainer.classList.remove('hidden');
        resContainer.innerHTML = `
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-xs font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-lg animate-spin">progress_activity</span>
                Procesando envío masivo a ${total} destinatarios a través de WhatsApp Cloud API...
            </div>
        `;
    }

    const inputDesde = document.getElementById('masivo-fecha-desde');
    const inputHasta = document.getElementById('masivo-fecha-hasta');
    const checks = document.querySelectorAll('input[name="chk-estado-masivo"]:checked');
    const estados = Array.from(checks).map(c => c.value);

    try {
        const payload = {
            fecha_desde: inputDesde ? inputDesde.value : '',
            fecha_hasta: inputHasta ? inputHasta.value : '',
            estados: estados,
            plantilla_id: plantillaId
        };

        const response = await fetch(`${API_BASE}/eventos/envio_masivo.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            resContainer.innerHTML = `
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span> Envío Masivo Finalizado
                        </span>
                        <span class="text-[11px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md">Plantilla: ${result.plantilla_usada}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs pt-1 border-t border-emerald-200/60 font-bold">
                        <div>Total Procesados: <strong class="text-charcoal">${result.total}</strong></div>
                        <div>Enviados Exitosos: <strong class="text-emerald-600">${result.enviados}</strong></div>
                        <div>Fallidos: <strong class="text-red-600">${result.fallidos}</strong></div>
                    </div>
                    ${result.detalles && result.detalles.length > 0 ? `
                        <div class="pt-2 text-[11px] text-emerald-800 font-medium">
                            Los eventos han sido actualizados con éxito en el sistema.
                        </div>
                    ` : ''}
                </div>
            `;
            // Recargar conteo de destinatarios
            consultarDestinatariosMasivos();
        } else {
            resContainer.innerHTML = `
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-semibold">
                    ${result.message || 'Error al ejecutar el envío masivo.'}
                </div>
            `;
        }
    } catch (err) {
        console.error("Error en envío masivo:", err);
        if (resContainer) {
            resContainer.innerHTML = `
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-semibold">
                    Error de conexión al procesar el envío masivo.
                </div>
            `;
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-[20px]">send</span> <span id="texto-boton-envio-masivo">Enviar Notificaciones</span>`;
        }
    }
}

// Helper para escapar HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Exponer en objeto Window
window.cambiarSubTabUsuario = cambiarSubTabUsuario;
window.cargarUsuarios = cargarUsuarios;
window.filtrarUsuariosDebounced = filtrarUsuariosDebounced;
window.abrirModalCrearUsuario = abrirModalCrearUsuario;
window.editarUsuario = editarUsuario;
window.cerrarModalCrearUsuario = cerrarModalCrearUsuario;
window.guardarUsuario = guardarUsuario;
window.eliminarUsuario = eliminarUsuario;
window.ejecutarProgramacionGlobalEventos = ejecutarProgramacionGlobalEventos;
window.cargarConfiguracionProgramacion = cargarConfiguracionProgramacion;
window.guardarConfiguracionProgramacion = guardarConfiguracionProgramacion;
window.inicializarEnvioMasivoNotificaciones = inicializarEnvioMasivoNotificaciones;
window.establecerRangoFechasMasivo = establecerRangoFechasMasivo;
window.marcarTodosEstadosMasivos = marcarTodosEstadosMasivos;
window.actualizarVistaPreviaPlantillaMasiva = actualizarVistaPreviaPlantillaMasiva;
window.consultarDestinatariosMasivos = consultarDestinatariosMasivos;
window.alternarListaPreviewDestinatarios = alternarListaPreviewDestinatarios;
window.confirmarYEjecutarEnvioMasivo = confirmarYEjecutarEnvioMasivo;
