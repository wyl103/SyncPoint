// public/js/modules/clientes.js
// Lógica del Directorio de Clientes, Filtros Dinámicos y Paginación

let timerBusquedaClientes = null;
let clientePaginaActual = 1;
let clienteTotalPaginas = 1;

async function cargarFiltrosDinamicos() {
    try {
        // Cargar sucursales desde la nueva API core (/core/sucursales.php)
        const resSuc = await fetch(`${API_BASE}/core/sucursales.php?limit=100`);
        const resultSuc = await resSuc.json();

        if (resultSuc.success) {
            const sucursales = resultSuc.data || [];
            
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
                            <button onclick="abrirModalChatwoot(${cliente.id}, '${(cliente.nombre || '').replace(/'/g, "\\'")}')" class="inline-flex items-center gap-1.5 text-xs font-bold text-charcoal bg-primary hover:bg-yellow-400 px-4 py-2 rounded-xl transition shadow-xs cursor-pointer">
                                <span class="material-symbols-outlined text-[17px]">forum</span> Abrir Chat
                            </button>
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

function toggleFiltros() {
    const panel = document.getElementById('panel-filtros');
    if (panel) panel.classList.toggle('hidden');
}
