// public/js/modules/clientes.js
// Lógica del Directorio de Clientes, Filtros Dinámicos, CRUD de Clientes, Sub-modales y Searchable Selects

let timerBusquedaClientes = null;
let clientePaginaActual = 1;
let clienteTotalPaginas = 1;

let listaSucursalesForm = [];
let listaRutasForm = [];
let listaFrecuenciasForm = [];
let sucursalSeleccionadaModal = null;
let rutaSeleccionadaModal = null;

async function cargarFiltrosDinamicos() {
    try {
        // Cargar sucursales desde la nueva API core (/core/sucursales.php)
        const resSuc = await fetch(`${API_BASE}/core/sucursales.php?limit=100`);
        const resultSuc = await resSuc.json();

        if (resultSuc.success) {
            const sucursales = resultSuc.data || [];
            listaSucursalesForm = sucursales;
            
            const selectSucursal = document.getElementById('filtro-sucursal');
            if (selectSucursal) {
                selectSucursal.innerHTML = `
                    <option value="todas">Todas las sucursales</option>
                    <option value="otras">Otras sucursales</option>
                `;
                sucursales.forEach(suc => {
                    if (suc.destacada == 1) {
                        selectSucursal.innerHTML += `<option value="${suc.id}">${suc.nombre}</option>`;
                    }
                });
            }

            const selectClienteSucursal = document.getElementById('filtro-cliente-sucursal');
            if (selectClienteSucursal) {
                selectClienteSucursal.innerHTML = `<option value="todas">Todas las sucursales</option>`;
                sucursales.forEach(suc => {
                    selectClienteSucursal.innerHTML += `<option value="${suc.id}">${suc.nombre}</option>`;
                });
            }
        }

        // Cargar estados desde el endpoint de sistema o estáticos
        const selectEstado = document.getElementById('filtro-estado');
        if (selectEstado && selectEstado.options.length <= 1) {
            selectEstado.innerHTML = `
                <option value="todos">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="completada">Completada</option>
            `;
        }

        cargarRutasPorSucursal('todas');
    } catch (error) {
        console.error("Error cargando filtros:", error);
    }
}

function filtrarClientesDebounced() {
    clearTimeout(timerBusquedaClientes);
    timerBusquedaClientes = setTimeout(() => {
        cargarClientes(1);
    }, 300);
}

async function cargarRutasPorSucursal(sucursalId = 'todas') {
    const selectRuta = document.getElementById('filtro-cliente-ruta');
    if (!selectRuta) return;

    try {
        let url = `${API_BASE}/core/rutas.php?limit=100`;
        if (sucursalId !== 'todas') {
            url += `&sucursal_id=${encodeURIComponent(sucursalId)}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            selectRuta.innerHTML = `<option value="todas">Todas las rutas</option>`;
            (result.data || []).forEach(ruta => {
                let sucursalTag = sucursalId === 'todas' && ruta.sucursal_nombre ? ` (${ruta.sucursal_nombre})` : '';
                selectRuta.innerHTML += `<option value="${ruta.id}">${ruta.nombre}${sucursalTag}</option>`;
            });
        }
    } catch (error) {
        console.error("Error cargando rutas para sucursal:", error);
    }
}

async function alCambiarSucursalCliente() {
    const sucursalId = document.getElementById('filtro-cliente-sucursal')?.value || 'todas';
    await cargarRutasPorSucursal(sucursalId);
    cargarClientes(1);
}

async function cargarClientes(page = 1) {
    const container = document.getElementById('lista-clientes');
    const totalBadge = document.getElementById('total-clientes-badge');
    const paginationContainer = document.getElementById('paginacion-clientes-container');
    if (!container) return;

    clientePaginaActual = max(1, page);

    const busqueda = document.getElementById('input-buscar-cliente')?.value.trim() || '';
    const sucursalId = document.getElementById('filtro-cliente-sucursal')?.value || 'todas';
    const rutaId = document.getElementById('filtro-cliente-ruta')?.value || 'todas';
    const estado = document.getElementById('filtro-cliente-estado')?.value || 'todos';
    const limit = document.getElementById('filtro-cliente-limit')?.value || 10;

    container.innerHTML = `<div class="col-span-1 md:col-span-2 text-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div><p class="text-xs text-gray-400 mt-2 font-semibold">Cargando clientes...</p></div>`;

    try {
        let url = `${API_BASE}/core/clientes.php?page=${clientePaginaActual}&limit=${limit}&q=${encodeURIComponent(busqueda)}`;
        if (sucursalId !== 'todas') url += `&sucursal_id=${encodeURIComponent(sucursalId)}`;
        if (rutaId !== 'todas') url += `&ruta_id=${encodeURIComponent(rutaId)}`;
        if (estado !== 'todos') url += `&estado=${encodeURIComponent(estado)}`;

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            const clientes = result.data || [];
            const pag = result.pagination || { page: 1, limit: 10, total: clientes.length, total_pages: 1 };
            
            clienteTotalPaginas = pag.total_pages || 1;
            if (totalBadge) totalBadge.innerText = pag.total || 0;

            if (clientes.length === 0) {
                container.innerHTML = `
                    <div class="col-span-1 md:col-span-2 bg-white border border-gray-200 rounded-xl p-8 text-center shadow-sm">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-2">person_off</span>
                        <p class="text-gray-500 font-bold text-base">No se encontraron clientes</p>
                        <p class="text-xs text-gray-400 mt-1">Prueba con otros términos de búsqueda o filtros.</p>
                    </div>
                `;
                if (paginationContainer) paginationContainer.classList.add('hidden');
                return;
            }

            container.innerHTML = '';
            clientes.forEach(cliente => {
                let estadoBadgeClass = cliente.estado === 'agendado' 
                    ? 'bg-green-50 text-green-700 border-green-200' 
                    : 'bg-yellow-50 text-yellow-700 border-yellow-200';

                let whatsappLimpio = (cliente.telefono_whatsapp || '').replace(/\D/g, '');
                let whatsappLink = whatsappLimpio ? `https://wa.me/${whatsappLimpio}` : '#';

                container.innerHTML += `
                    <div class="bg-white border border-gray-200/90 p-5 rounded-2xl shadow-xs hover:shadow-md hover:border-primary/50 transition-all flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-3">
                                <h3 class="font-bold text-charcoal text-base leading-snug">${cliente.nombre}</h3>
                                <span class="px-2.5 py-0.5 border text-[10px] font-extrabold rounded-md uppercase tracking-wider shrink-0 ${estadoBadgeClass}">
                                    ${cliente.estado || 'no agendado'}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-xs text-gray-600 font-medium bg-gray-50/70 p-3 rounded-xl border border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px]">call</span>
                                    <a href="${whatsappLink}" target="_blank" class="hover:text-primary hover:underline font-semibold text-gray-800">
                                        ${cliente.telefono_whatsapp || 'Sin teléfono'}
                                    </a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-[16px]">route</span>
                                    <span>Ruta: <strong class="text-gray-800">${cliente.ruta_nombre || 'Sin asignación'}</strong></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px]">store</span>
                                    <span>Sucursal: <strong class="text-gray-800">${cliente.sucursal_nombre || 'N/A'}</strong></span>
                                </div>
                                ${cliente.frecuencia_nombre ? `
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[16px]">update</span>
                                    <span>Frecuencia: <strong class="text-gray-800">${cliente.frecuencia_nombre}</strong></span>
                                </div>` : ''}
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-gray-400">ID: #${cliente.id}</span>
                            <div class="flex items-center gap-2">
                                <button onclick="abrirModalEditarCliente(${cliente.id})" class="btn-card-edit" title="Editar datos del cliente">
                                    <span class="material-symbols-outlined text-[16px]">edit</span> Editar
                                </button>
                                <button onclick="abrirModalChatwoot(${cliente.id}, '${(cliente.nombre || '').replace(/'/g, "\\'")}')" class="btn-card-chat">
                                    <span class="material-symbols-outlined text-[17px]">forum</span> Chat
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            actualizarUIPaginacion(pag);
        } else {
            container.innerHTML = `<p class="text-red-500 col-span-2 text-center text-sm font-bold">Error cargando lista de clientes.</p>`;
            if (paginationContainer) paginationContainer.classList.add('hidden');
        }
    } catch (error) {
        console.error("Error al obtener clientes:", error);
        container.innerHTML = `<p class="text-red-500 col-span-2 text-center text-sm font-bold">Error de conexión con el servidor.</p>`;
        if (paginationContainer) paginationContainer.classList.add('hidden');
    }
}

function actualizarUIPaginacion(pag) {
    const container = document.getElementById('paginacion-clientes-container');
    if (!container) return;

    if (!pag || pag.total === 0) {
        container.classList.add('hidden');
        return;
    }

    container.classList.remove('hidden');

    const inicio = (pag.page - 1) * pag.limit + 1;
    const fin = Math.min(pag.page * pag.limit, pag.total);

    const txtInicio = document.getElementById('paginacion-rango-inicio');
    const txtFin = document.getElementById('paginacion-rango-fin');
    const txtTotal = document.getElementById('paginacion-total-registros');
    const txtPaginaActual = document.getElementById('paginacion-pagina-actual');
    const txtTotalPaginas = document.getElementById('paginacion-total-paginas');

    if (txtInicio) txtInicio.innerText = inicio;
    if (txtFin) txtFin.innerText = fin;
    if (txtTotal) txtTotal.innerText = pag.total;
    if (txtPaginaActual) txtPaginaActual.innerText = pag.page;
    if (txtTotalPaginas) txtTotalPaginas.innerText = pag.total_pages;

    const btnPrev = document.getElementById('btn-pagina-prev');
    const btnNext = document.getElementById('btn-pagina-next');

    if (btnPrev) btnPrev.disabled = pag.page <= 1;
    if (btnNext) btnNext.disabled = pag.page >= pag.total_pages;
}

function cambiarPaginaCliente(delta) {
    const nuevaPagina = clientePaginaActual + delta;
    if (nuevaPagina >= 1 && nuevaPagina <= clienteTotalPaginas) {
        cargarClientes(nuevaPagina);
    }
}

// ----------------------------------------------------
// Lógica de Formulario Modal de Cliente (Crear / Editar)
// ----------------------------------------------------

async function cargarFrecuenciasForm() {
    const select = document.getElementById('form-cliente-frecuencia');
    if (!select) return;

    try {
        const response = await fetch(`${API_BASE}/core/frecuencias.php?limit=100`);
        const result = await response.json();

        if (result.success) {
            listaFrecuenciasForm = result.data || [];
            select.innerHTML = `<option value="">-- Seleccionar frecuencia --</option>`;
            listaFrecuenciasForm.forEach(f => {
                select.innerHTML += `<option value="${f.id}">${f.nombre} (${f.dias} días)</option>`;
            });
            select.innerHTML += `<option value="otra" class="font-bold text-primary">+ Otra (Crear nueva frecuencia)...</option>`;
        }
    } catch (err) {
        console.error("Error cargando frecuencias:", err);
    }
}

function alCambiarFrecuenciaCliente() {
    const val = document.getElementById('form-cliente-frecuencia')?.value;
    const boxOtra = document.getElementById('box-frecuencia-otra');
    const inputNombre = document.getElementById('form-cliente-frecuencia-nombre');
    const inputDias = document.getElementById('form-cliente-frecuencia-dias');

    if (val === 'otra') {
        if (boxOtra) boxOtra.classList.remove('hidden');
        if (inputNombre) inputNombre.required = true;
        if (inputDias) inputDias.required = true;
    } else {
        if (boxOtra) boxOtra.classList.add('hidden');
        if (inputNombre) { inputNombre.required = false; inputNombre.value = ''; }
        if (inputDias) { inputDias.required = false; inputDias.value = ''; }
    }
}

// ----------------------------------------------------
// Selects con Búsqueda (Searchable Select) para Sucursal y Ruta
// ----------------------------------------------------

async function cargarSucursalesFormSearchable() {
    try {
        const res = await fetch(`${API_BASE}/core/sucursales.php?limit=100`);
        const result = await res.json();
        if (result.success) {
            listaSucursalesForm = result.data || [];
            renderDropdownOptions('sucursal', listaSucursalesForm);
        }
    } catch (err) {
        console.error("Error cargando sucursales para modal:", err);
    }
}

async function cargarRutasFormSearchable(sucursalId = null) {
    const btnNuevaRuta = document.getElementById('btn-nueva-ruta-rapida');
    const searchRutaInput = document.getElementById('search-ruta-input');
    const hiddenRutaId = document.getElementById('form-cliente-ruta-id');
    const dropdownRuta = document.getElementById('dropdown-ruta-options');

    if (!sucursalId) {
        if (btnNuevaRuta) {
            btnNuevaRuta.disabled = true;
            btnNuevaRuta.classList.add('cursor-not-allowed', 'opacity-60', 'bg-gray-100', 'text-gray-400');
            btnNuevaRuta.classList.remove('bg-primary/20', 'text-charcoal', 'hover:bg-primary/40');
        }
        if (searchRutaInput) {
            searchRutaInput.value = '';
            searchRutaInput.placeholder = 'Selecciona primero una sucursal...';
        }
        if (hiddenRutaId) hiddenRutaId.value = '';
        listaRutasForm = [];
        if (dropdownRuta) dropdownRuta.innerHTML = `<div class="p-3 text-center text-gray-400">Selecciona primero una sucursal</div>`;
        return;
    }

    if (btnNuevaRuta) {
        btnNuevaRuta.disabled = false;
        btnNuevaRuta.classList.remove('cursor-not-allowed', 'opacity-60', 'bg-gray-100', 'text-gray-400');
        btnNuevaRuta.classList.add('bg-primary/20', 'text-charcoal', 'hover:bg-primary/40');
    }
    if (searchRutaInput) searchRutaInput.placeholder = 'Buscar ruta...';

    try {
        const res = await fetch(`${API_BASE}/core/rutas.php?sucursal_id=${encodeURIComponent(sucursalId)}&limit=100`);
        const result = await res.json();
        if (result.success) {
            listaRutasForm = result.data || [];
            renderDropdownOptions('ruta', listaRutasForm);
        }
    } catch (err) {
        console.error("Error cargando rutas para modal:", err);
    }
}

function renderDropdownOptions(tipo, items, filtro = '') {
    const dropdown = document.getElementById(`dropdown-${tipo}-options`);
    if (!dropdown) return;

    const itemsFiltrados = items.filter(item => 
        (item.nombre || '').toLowerCase().includes(filtro.toLowerCase())
    );

    if (itemsFiltrados.length === 0) {
        dropdown.innerHTML = `<div class="p-3 text-center text-gray-400">No hay opciones encontradas</div>`;
        return;
    }

    dropdown.innerHTML = '';
    itemsFiltrados.forEach(item => {
        const div = document.createElement('div');
        div.className = 'p-2.5 hover:bg-primary/20 cursor-pointer transition flex justify-between items-center border-b border-gray-100 last:border-0';
        div.innerHTML = `<span>${item.nombre}</span>${item.ciudad ? `<span class="text-[10px] text-gray-400 font-semibold">${item.ciudad}</span>` : ''}`;
        div.onclick = (e) => {
            e.stopPropagation();
            seleccionarSearchable(tipo, item.id, item.nombre);
        };
        dropdown.appendChild(div);
    });
}

function mostrarDropdownSearchable(tipo) {
    const dropdown = document.getElementById(`dropdown-${tipo}-options`);
    if (dropdown) dropdown.classList.remove('hidden');

    if (tipo === 'sucursal') {
        renderDropdownOptions('sucursal', listaSucursalesForm, document.getElementById('search-sucursal-input')?.value || '');
    } else if (tipo === 'ruta') {
        renderDropdownOptions('ruta', listaRutasForm, document.getElementById('search-ruta-input')?.value || '');
    }
}

function filtrarDropdownSearchable(tipo) {
    const inputVal = document.getElementById(`search-${tipo}-input`)?.value || '';
    if (tipo === 'sucursal') {
        renderDropdownOptions('sucursal', listaSucursalesForm, inputVal);
    } else if (tipo === 'ruta') {
        renderDropdownOptions('ruta', listaRutasForm, inputVal);
    }
}

function seleccionarSearchable(tipo, id, nombre) {
    const inputSearch = document.getElementById(`search-${tipo}-input`);
    const inputHidden = document.getElementById(`form-cliente-${tipo}-id`);
    const dropdown = document.getElementById(`dropdown-${tipo}-options`);

    if (inputSearch) inputSearch.value = nombre;
    if (inputHidden) inputHidden.value = id;
    if (dropdown) dropdown.classList.add('hidden');

    if (tipo === 'sucursal') {
        sucursalSeleccionadaModal = { id, nombre };
        // Reset ruta al cambiar sucursal
        const hiddenRuta = document.getElementById('form-cliente-ruta-id');
        const searchRuta = document.getElementById('search-ruta-input');
        if (hiddenRuta) hiddenRuta.value = '';
        if (searchRuta) searchRuta.value = '';
        cargarRutasFormSearchable(id);
    } else if (tipo === 'ruta') {
        rutaSeleccionadaModal = { id, nombre };
    }
}

// Cerrar dropdowns si se hace clic fuera
document.addEventListener('click', (e) => {
    const wrapSuc = document.getElementById('wrapper-select-sucursal');
    const wrapRuta = document.getElementById('wrapper-select-ruta');

    if (wrapSuc && !wrapSuc.contains(e.target)) {
        document.getElementById('dropdown-sucursal-options')?.classList.add('hidden');
    }
    if (wrapRuta && !wrapRuta.contains(e.target)) {
        document.getElementById('dropdown-ruta-options')?.classList.add('hidden');
    }
});

// ----------------------------------------------------
// Funciones de Modal Cliente (Crear / Editar)
// ----------------------------------------------------

async function abrirModalCrearCliente() {
    const modal = document.getElementById('modal-cliente');
    const txtAccion = document.getElementById('txt-modal-cliente-accion');
    const form = document.getElementById('form-cliente');
    if (!modal) return;

    if (txtAccion) txtAccion.innerText = 'Nuevo Cliente';
    if (form) form.reset();

    document.getElementById('form-cliente-id').value = '';
    document.getElementById('form-cliente-sucursal-id').value = '';
    document.getElementById('form-cliente-ruta-id').value = '';
    document.getElementById('search-sucursal-input').value = '';
    document.getElementById('search-ruta-input').value = '';

    sucursalSeleccionadaModal = null;
    rutaSeleccionadaModal = null;

    alCambiarFrecuenciaCliente();
    await cargarFrecuenciasForm();
    await cargarSucursalesFormSearchable();
    await cargarRutasFormSearchable(null);

    modal.classList.remove('hidden-view');
}

async function abrirModalEditarCliente(clienteId) {
    const modal = document.getElementById('modal-cliente');
    const txtAccion = document.getElementById('txt-modal-cliente-accion');
    if (!modal) return;

    if (txtAccion) txtAccion.innerText = 'Editar Cliente';

    await cargarFrecuenciasForm();
    await cargarSucursalesFormSearchable();

    try {
        const response = await fetch(`${API_BASE}/core/clientes.php?id=${clienteId}`);
        const result = await response.json();

        if (result.success && result.data) {
            const cliente = result.data;
            document.getElementById('form-cliente-id').value = cliente.id;
            document.getElementById('form-cliente-nombre').value = cliente.nombre || '';
            document.getElementById('form-cliente-telefono').value = cliente.telefono_whatsapp || '';
            document.getElementById('form-cliente-estado').value = cliente.estado || 'no agendado';
            document.getElementById('form-cliente-fecha-base').value = cliente.fecha_base || '';

            // Frecuencia
            const selectFrec = document.getElementById('form-cliente-frecuencia');
            if (selectFrec) selectFrec.value = cliente.frecuencia_id || '';
            alCambiarFrecuenciaCliente();

            // Sucursal
            if (cliente.sucursal_id) {
                seleccionarSearchable('sucursal', cliente.sucursal_id, cliente.sucursal_nombre || 'Sucursal #' + cliente.sucursal_id);
                await cargarRutasFormSearchable(cliente.sucursal_id);
            } else {
                seleccionarSearchable('sucursal', '', '');
                await cargarRutasFormSearchable(null);
            }

            // Ruta
            if (cliente.ruta_id) {
                seleccionarSearchable('ruta', cliente.ruta_id, cliente.ruta_nombre || 'Ruta #' + cliente.ruta_id);
            }

            modal.classList.remove('hidden-view');
        } else {
            alert(result.message || "Error obteniendo información del cliente.");
        }
    } catch (err) {
        console.error("Error al cargar datos del cliente:", err);
        alert("Error de conexión al cargar datos del cliente.");
    }
}

function cerrarModalCliente() {
    const modal = document.getElementById('modal-cliente');
    if (modal) modal.classList.add('hidden-view');
}

async function guardarCliente(event) {
    event.preventDefault();

    const id = document.getElementById('form-cliente-id')?.value;
    const nombre = document.getElementById('form-cliente-nombre')?.value.trim();
    const telefono = document.getElementById('form-cliente-telefono')?.value.trim();
    const sucursalId = document.getElementById('form-cliente-sucursal-id')?.value || null;
    const rutaId = document.getElementById('form-cliente-ruta-id')?.value || null;
    let frecuenciaId = document.getElementById('form-cliente-frecuencia')?.value || null;
    const fechaBase = document.getElementById('form-cliente-fecha-base')?.value || null;
    const estado = document.getElementById('form-cliente-estado')?.value || 'no agendado';

    const btnGuardar = document.getElementById('btn-guardar-cliente');
    if (btnGuardar) { btnGuardar.disabled = true; btnGuardar.innerText = "Guardando..."; }

    try {
        // Manejo de frecuencia OTRA
        if (frecuenciaId === 'otra') {
            const frecNombre = document.getElementById('form-cliente-frecuencia-nombre')?.value.trim();
            const frecDias = document.getElementById('form-cliente-frecuencia-dias')?.value;

            if (!frecNombre || !frecDias) {
                alert("Por favor completa el nombre y los días para la nueva frecuencia.");
                if (btnGuardar) { btnGuardar.disabled = false; btnGuardar.innerHTML = `<span class="material-symbols-outlined text-[18px]">save</span> Guardar Cliente`; }
                return;
            }

            // Crear nueva frecuencia primero en API
            const resFrec = await fetch(`${API_BASE}/core/frecuencias.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre: frecNombre, dias: parseInt(frecDias) })
            });

            const resultFrec = await resFrec.json();
            if (resultFrec.success && resultFrec.id) {
                frecuenciaId = resultFrec.id;
            } else {
                alert(resultFrec.message || "Error al crear la nueva frecuencia.");
                if (btnGuardar) { btnGuardar.disabled = false; btnGuardar.innerHTML = `<span class="material-symbols-outlined text-[18px]">save</span> Guardar Cliente`; }
                return;
            }
        }

        const payload = {
            nombre,
            telefono_whatsapp: telefono,
            sucursal_id: sucursalId,
            ruta_id: rutaId,
            frecuencia_id: frecuenciaId,
            fecha_base: fechaBase,
            estado: estado
        };

        let url = `${API_BASE}/core/clientes.php`;
        let method = 'POST';

        if (id) {
            url += `?id=${id}`;
            method = 'PUT';
            payload.id = id;
        }

        const response = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            cerrarModalCliente();
            await cargarClientes(clientePaginaActual);
        } else {
            alert(result.message || "Error al guardar el cliente.");
        }
    } catch (err) {
        console.error("Error guardando cliente:", err);
        alert("Error de conexión al guardar cliente.");
    } finally {
        if (btnGuardar) {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = `<span class="material-symbols-outlined text-[18px]">save</span> Guardar Cliente`;
        }
    }
}

// ----------------------------------------------------
// Sub-modales de Sucursal Rápida y Ruta Rápida
// ----------------------------------------------------

function abrirModalSucursalRapida() {
    const modal = document.getElementById('modal-sucursal-rapida');
    const form = document.getElementById('form-sucursal-rapida');
    if (form) form.reset();
    if (modal) modal.classList.remove('hidden-view');
}

function cerrarModalSucursalRapida() {
    const modal = document.getElementById('modal-sucursal-rapida');
    if (modal) modal.classList.add('hidden-view');
}

async function guardarSucursalRapida(event) {
    event.preventDefault();
    const nombre = document.getElementById('form-sucursal-nombre')?.value.trim();
    if (!nombre) return;

    try {
        const response = await fetch(`${API_BASE}/core/sucursales.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre: nombre, destacada: 0 })
        });
        const result = await response.json();

        if (result.success && result.id) {
            cerrarModalSucursalRapida();
            await cargarSucursalesFormSearchable();
            seleccionarSearchable('sucursal', result.id, nombre);
        } else {
            alert(result.message || "Error al crear la sucursal.");
        }
    } catch (err) {
        console.error("Error creando sucursal rápida:", err);
        alert("Error de conexión al crear sucursal.");
    }
}

function abrirModalRutaRapida() {
    const sucursalId = document.getElementById('form-cliente-sucursal-id')?.value;
    const sucursalNombre = document.getElementById('search-sucursal-input')?.value;

    if (!sucursalId) {
        alert("Debes seleccionar primero una sucursal para poder crear una ruta.");
        return;
    }

    const modal = document.getElementById('modal-ruta-rapida');
    const form = document.getElementById('form-ruta-rapida');
    const lblSucursal = document.getElementById('lbl-ruta-sucursal-nombre');

    if (lblSucursal) lblSucursal.innerText = sucursalNombre || `ID #${sucursalId}`;
    if (form) form.reset();
    if (modal) modal.classList.remove('hidden-view');
}

function cerrarModalRutaRapida() {
    const modal = document.getElementById('modal-ruta-rapida');
    if (modal) modal.classList.add('hidden-view');
}

async function guardarRutaRapida(event) {
    event.preventDefault();
    const sucursalId = document.getElementById('form-cliente-sucursal-id')?.value;
    const nombre = document.getElementById('form-ruta-nombre')?.value.trim();
    const ciudad = document.getElementById('form-ruta-ciudad')?.value.trim();

    if (!sucursalId || !nombre || !ciudad) return;

    try {
        const response = await fetch(`${API_BASE}/core/rutas.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, ciudad, fk_sucursal: parseInt(sucursalId) })
        });
        const result = await response.json();

        if (result.success && result.id) {
            cerrarModalRutaRapida();
            await cargarRutasFormSearchable(sucursalId);
            seleccionarSearchable('ruta', result.id, nombre);
        } else {
            alert(result.message || "Error al crear la ruta.");
        }
    } catch (err) {
        console.error("Error creando ruta rápida:", err);
        alert("Error de conexión al crear la ruta.");
    }
}

// ----------------------------------------------------
// Exposición Global en Window
// ----------------------------------------------------

window.cargarFiltrosDinamicos = cargarFiltrosDinamicos;
window.filtrarClientesDebounced = filtrarClientesDebounced;
window.alCambiarSucursalCliente = alCambiarSucursalCliente;
window.cargarClientes = cargarClientes;
window.cambiarPaginaCliente = cambiarPaginaCliente;
window.abrirModalCrearCliente = abrirModalCrearCliente;
window.abrirModalEditarCliente = abrirModalEditarCliente;
window.cerrarModalCliente = cerrarModalCliente;
window.guardarCliente = guardarCliente;
window.alCambiarFrecuenciaCliente = alCambiarFrecuenciaCliente;
window.mostrarDropdownSearchable = mostrarDropdownSearchable;
window.filtrarDropdownSearchable = filtrarDropdownSearchable;
window.seleccionarSearchable = seleccionarSearchable;
window.abrirModalSucursalRapida = abrirModalSucursalRapida;
window.cerrarModalSucursalRapida = cerrarModalSucursalRapida;
window.guardarSucursalRapida = guardarSucursalRapida;
window.abrirModalRutaRapida = abrirModalRutaRapida;
window.cerrarModalRutaRapida = cerrarModalRutaRapida;
window.guardarRutaRapida = guardarRutaRapida;
