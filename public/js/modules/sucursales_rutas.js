// public/js/modules/sucursales_rutas.js
// Lógica del Módulo de Administración de Sucursales y Rutas (Acordeón, CRUD de Sucursales y Rutas)

let cacheSucursales = [];
let cacheRutas = [];
let cacheClientes = [];
let sucursalesAcordeonEstado = {};
let timerFiltroSucRutas = null;

async function cargarSucursalesYRutas() {
    const container = document.getElementById('lista-sucursales-rutas');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center py-16 bg-white border border-gray-200 rounded-2xl shadow-xs">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2"></div>
            <p class="text-xs text-gray-500 font-semibold">Cargando sucursales y rutas...</p>
        </div>
    `;

    const apiBaseUrl = window.API_BASE || `${window.location.pathname.includes('/app_bless') ? '/app_bless' : ''}/app/api`;

    try {
        const [resSuc, resRutas, resCli] = await Promise.all([
            fetch(`${apiBaseUrl}/core/sucursales.php?limit=100`),
            fetch(`${apiBaseUrl}/core/rutas.php?limit=100`),
            fetch(`${apiBaseUrl}/core/clientes.php?limit=5000`)
        ]);

        const resultSuc = await resSuc.json();
        const resultRutas = await resRutas.json();
        const resultCli = await resCli.json();

        if (resultSuc.success || Array.isArray(resultSuc.data) || Array.isArray(resultSuc)) {
            cacheSucursales = resultSuc.data || (Array.isArray(resultSuc) ? resultSuc : []);
            cacheRutas = resultRutas.data || (Array.isArray(resultRutas) ? resultRutas : []);
            cacheClientes = resultCli.data || (Array.isArray(resultCli) ? resultCli : []);

            poblarSelectsFiltroSucRutas();
            filtrarSucursalesYRutas();
        } else {
            container.innerHTML = `<p class="text-red-500 text-center font-bold py-8">Error al cargar las sucursales.</p>`;
        }
    } catch (err) {
        console.error("Error al obtener sucursales y rutas:", err);
        container.innerHTML = `<p class="text-red-500 text-center font-bold py-8">Error de conexión al servidor.</p>`;
    }
}

function poblarSelectsFiltroSucRutas() {
    const selSuc = document.getElementById('filtro-sucursal-select');
    const selRuta = document.getElementById('filtro-ruta-select');

    if (selSuc) {
        let valSuc = selSuc.value || 'todas';
        let options = '<option value="todas">Todas las sucursales</option>';
        cacheSucursales.forEach(s => {
            options += `<option value="${s.id}">${s.nombre}</option>`;
        });
        selSuc.innerHTML = options;
        selSuc.value = valSuc;
    }

    if (selRuta) {
        let valRuta = selRuta.value || 'todas';
        let options = '<option value="todas">Todas las rutas</option>';
        cacheRutas.forEach(r => {
            options += `<option value="${r.id}">${r.nombre} (${r.ciudad || 'Sin ciudad'})</option>`;
        });
        selRuta.innerHTML = options;
        selRuta.value = valRuta;
    }
}

function filtrarSucursalesYRutasDebounced() {
    clearTimeout(timerFiltroSucRutas);
    timerFiltroSucRutas = setTimeout(() => {
        filtrarSucursalesYRutas();
    }, 300);
}

function filtrarSucursalesYRutas() {
    const q = (document.getElementById('input-buscar-sucursal-ruta')?.value || '').toLowerCase().trim();
    const selSuc = document.getElementById('filtro-sucursal-select')?.value || 'todas';
    const selRuta = document.getElementById('filtro-ruta-select')?.value || 'todas';

    let sucursalesFiltradas = cacheSucursales.filter(suc => {
        if (selSuc !== 'todas' && suc.id != selSuc) return false;

        const rutasDeEstaSuc = cacheRutas.filter(r => r.fk_sucursal == suc.id || r.sucursal_id == suc.id);

        if (selRuta !== 'todas') {
            const tieneRuta = rutasDeEstaSuc.some(r => r.id == selRuta);
            if (!tieneRuta) return false;
        }

        if (q) {
            const coincideSuc = (suc.nombre || '').toLowerCase().includes(q) || `suc-${suc.id}`.includes(q);
            const coincideRuta = rutasDeEstaSuc.some(r => 
                (r.nombre || '').toLowerCase().includes(q) || 
                (r.ciudad || '').toLowerCase().includes(q)
            );
            if (!coincideSuc && !coincideRuta) return false;
        }

        return true;
    });

    renderizarListaSucursalesYRutas(sucursalesFiltradas, cacheRutas, cacheClientes);
}

function renderizarListaSucursalesYRutas(sucursales, rutas, clientes) {
    const container = document.getElementById('lista-sucursales-rutas');
    if (!container) return;

    if (sucursales.length === 0) {
        container.innerHTML = `
            <div class="bg-white border border-gray-200 rounded-2xl p-10 text-center shadow-xs">
                <span class="material-symbols-outlined text-gray-300 text-6xl mb-3">storefront</span>
                <h3 class="text-lg font-bold text-charcoal">No se encontraron sucursales</h3>
                <p class="text-xs text-gray-400 mt-1 mb-4">Intenta ajustar los criterios de búsqueda o filtros.</p>
                <button onclick="abrirModalSucursalRapida()" class="btn-primary-main">
                    <span class="material-symbols-outlined text-[18px]">add</span> Nueva Sucursal
                </button>
            </div>
        `;
        return;
    }

    const clientesPorSucursal = {};
    const clientesPorRuta = {};

    clientes.forEach(c => {
        const rId = c.ruta_id || c.fk_ruta;
        let sId = c.sucursal_id || c.fk_sucursal;

        if (rId) {
            clientesPorRuta[rId] = (clientesPorRuta[rId] || 0) + 1;
            if (!sId) {
                const route = cacheRutas.find(r => r.id == rId);
                if (route) {
                    sId = route.fk_sucursal || route.sucursal_id;
                }
            }
        }

        if (sId) {
            clientesPorSucursal[sId] = (clientesPorSucursal[sId] || 0) + 1;
        }
    });

    const selRuta = document.getElementById('filtro-ruta-select')?.value || 'todas';
    const q = (document.getElementById('input-buscar-sucursal-ruta')?.value || '').toLowerCase().trim();

    container.innerHTML = '';

    sucursales.forEach(suc => {
        let rutasSucursal = rutas.filter(r => r.fk_sucursal == suc.id || r.sucursal_id == suc.id);

        if (selRuta !== 'todas') {
            rutasSucursal = rutasSucursal.filter(r => r.id == selRuta);
        }

        if (q) {
            const coincideSuc = (suc.nombre || '').toLowerCase().includes(q) || `suc-${suc.id}`.includes(q);
            if (!coincideSuc) {
                rutasSucursal = rutasSucursal.filter(r => 
                    (r.nombre || '').toLowerCase().includes(q) || 
                    (r.ciudad || '').toLowerCase().includes(q)
                );
            }
        }

        const totalClientesSucursal = clientesPorSucursal[suc.id] || 0;

        if (sucursalesAcordeonEstado[suc.id] === undefined) {
            sucursalesAcordeonEstado[suc.id] = true;
        }
        const isExpanded = sucursalesAcordeonEstado[suc.id];

        let rutasHtml = '';
        if (rutasSucursal.length === 0) {
            rutasHtml = `
                <div class="p-6 text-center bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
                    <p class="text-xs text-gray-400 font-semibold mb-2">No hay rutas asignadas a esta sucursal.</p>
                    <button onclick="abrirModalCrearRutaEnSucursal(${suc.id}, '${(suc.nombre || '').replace(/'/g, "\\'")}')" class="btn-add-subaction">
                        <span class="material-symbols-outlined text-[15px]">add</span> Asignar Primera Ruta
                    </button>
                </div>
            `;
        } else {
            rutasHtml = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">`;
            rutasSucursal.forEach(r => {
                const totalCliRuta = clientesPorRuta[r.id] || 0;
                rutasHtml += `
                    <div class="bg-gray-50/80 border border-gray-200 p-4 rounded-xl shadow-2xs hover:border-primary/50 transition flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-gray-400 text-[18px]">local_shipping</span>
                                    <h5 class="font-bold text-charcoal text-sm leading-tight">${r.nombre}</h5>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button onclick="abrirModalEditarRuta(${r.id}, '${(r.nombre || '').replace(/'/g, "\\'")}', '${(r.ciudad || '').replace(/'/g, "\\'")}', ${suc.id}, '${(suc.nombre || '').replace(/'/g, "\\'")}')" class="p-1 rounded-lg text-gray-400 hover:text-charcoal hover:bg-gray-200/60 transition" title="Editar Ruta">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <button onclick="eliminarRuta(${r.id}, '${(r.nombre || '').replace(/'/g, "\\'")}')" class="p-1 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition" title="Eliminar Ruta">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                    </button>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 bg-gray-200/70 text-gray-600 text-[10px] font-bold rounded uppercase tracking-wider inline-block mt-1 mb-2">
                                ${r.ciudad || 'Sin Ciudad'}
                            </span>
                        </div>

                        <div class="pt-3 border-t border-gray-200/80 flex items-center justify-between text-xs text-gray-500">
                            <span class="font-semibold text-[11px]">Clientes asignados:</span>
                            <span class="font-extrabold text-charcoal bg-white px-2 py-0.5 rounded-md border border-gray-200/80 shadow-2xs">${totalCliRuta} Clientes</span>
                        </div>
                    </div>
                `;
            });
            rutasHtml += `</div>`;
        }

        container.innerHTML += `
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden transition-all">
                <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white hover:bg-gray-50/50 transition">
                    <div class="flex items-center gap-4">
                        <div class="bg-primary/20 w-12 h-12 rounded-2xl flex items-center justify-center text-charcoal shrink-0 border border-primary/30 mr-1">
                            <span class="material-symbols-outlined text-2xl">store</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="font-bold text-charcoal text-lg leading-tight">${suc.nombre}</h3>
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-900 text-[10px] font-extrabold rounded-md uppercase tracking-wider">
                                    ID: SUC-${String(suc.id).padStart(3, '0')}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-500 font-medium mt-1.5">
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-primary">route</span> ${rutasSucursal.length} Rutas Activas</span>
                                <span>•</span>
                                <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-gray-400">group</span> ${totalClientesSucursal} Clientes</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                        <button onclick="abrirModalEditarSucursal(${suc.id}, '${(suc.nombre || '').replace(/'/g, "\\'")}')" class="p-2 rounded-xl text-gray-500 hover:text-charcoal hover:bg-gray-100 transition border border-gray-200/80 shadow-2xs" title="Editar Sucursal">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                        </button>
                        <button onclick="eliminarSucursal(${suc.id}, '${(suc.nombre || '').replace(/'/g, "\\'")}')" class="p-2 rounded-xl text-red-500 hover:text-red-700 hover:bg-red-50 transition border border-red-200/80 shadow-2xs" title="Eliminar Sucursal">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                        <button onclick="toggleAcordeonSucursal(${suc.id})" class="p-2 rounded-xl text-gray-500 hover:text-charcoal hover:bg-gray-100 transition border border-gray-200/80 shadow-2xs" title="Desplegar/Colapsar Rutas">
                            <span id="icon-acordeon-sucursal-${suc.id}" class="material-symbols-outlined text-[20px] transition-transform ${isExpanded ? 'rotate-180' : ''}">expand_more</span>
                        </button>
                    </div>
                </div>

                <div id="cuerpo-acordeon-sucursal-${suc.id}" class="${isExpanded ? '' : 'hidden'} border-t border-gray-100 p-5 bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-primary text-[16px]">alt_route</span> RUTAS ASIGNADAS
                        </h4>
                        <button onclick="abrirModalCrearRutaEnSucursal(${suc.id}, '${(suc.nombre || '').replace(/'/g, "\\'")}')" class="btn-add-subaction">
                            <span class="material-symbols-outlined text-[15px]">add</span> Nueva Ruta
                        </button>
                    </div>

                    ${rutasHtml}
                </div>
            </div>
        `;
    });
}

function toggleAcordeonSucursal(sucursalId) {
    const cuerpo = document.getElementById(`cuerpo-acordeon-sucursal-${sucursalId}`);
    const icono = document.getElementById(`icon-acordeon-sucursal-${sucursalId}`);
    if (!cuerpo) return;

    const isCurrentlyHidden = cuerpo.classList.contains('hidden');
    if (isCurrentlyHidden) {
        cuerpo.classList.remove('hidden');
        if (icono) icono.classList.add('rotate-180');
        sucursalesAcordeonEstado[sucursalId] = true;
    } else {
        cuerpo.classList.add('hidden');
        if (icono) icono.classList.remove('rotate-180');
        sucursalesAcordeonEstado[sucursalId] = false;
    }
}

// ----------------------------------------------------
// Acciones CRUD de Sucursal desde el Módulo
// ----------------------------------------------------

function abrirModalEditarSucursal(sucursalId, sucursalNombre) {
    const modal = document.getElementById('modal-sucursal-rapida');
    const inputNombre = document.getElementById('form-sucursal-nombre');
    const form = document.getElementById('form-sucursal-rapida');
    if (!modal || !inputNombre) return;

    form.setAttribute('data-edit-id', sucursalId);
    inputNombre.value = sucursalNombre || '';
    modal.classList.remove('hidden-view');
}

async function eliminarSucursal(sucursalId, sucursalNombre) {
    if (!confirm(`¿Estás seguro de eliminar la sucursal "${sucursalNombre}"? Se desasociarán sus rutas.`)) return;

    try {
        const response = await fetch(`${API_BASE}/core/sucursales.php?id=${sucursalId}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (result.success) {
            await cargarSucursalesYRutas();
        } else {
            alert(result.message || "Error al eliminar la sucursal.");
        }
    } catch (err) {
        console.error("Error eliminando sucursal:", err);
        alert("Error de conexión al eliminar la sucursal.");
    }
}

// ----------------------------------------------------
// Acciones CRUD de Ruta desde el Módulo
// ----------------------------------------------------

function abrirModalCrearRutaEnSucursal(sucursalId, sucursalNombre) {
    const hiddenSucId = document.getElementById('form-cliente-sucursal-id');
    const searchSucInput = document.getElementById('search-sucursal-input');
    if (hiddenSucId) hiddenSucId.value = sucursalId;
    if (searchSucInput) searchSucInput.value = sucursalNombre;

    const modal = document.getElementById('modal-ruta-rapida');
    const form = document.getElementById('form-ruta-rapida');
    const lblSucursal = document.getElementById('lbl-ruta-sucursal-nombre');

    if (lblSucursal) lblSucursal.innerText = sucursalNombre || `ID #${sucursalId}`;
    if (form) {
        form.reset();
        form.removeAttribute('data-edit-id');
    }
    if (modal) modal.classList.remove('hidden-view');
}

function abrirModalEditarRuta(rutaId, rutaNombre, ciudad, sucursalId, sucursalNombre) {
    abrirModalCrearRutaEnSucursal(sucursalId, sucursalNombre);
    const form = document.getElementById('form-ruta-rapida');
    const inputNombre = document.getElementById('form-ruta-nombre');
    const inputCiudad = document.getElementById('form-ruta-ciudad');

    if (form) form.setAttribute('data-edit-id', rutaId);
    if (inputNombre) inputNombre.value = rutaNombre || '';
    if (inputCiudad) inputCiudad.value = ciudad || '';
}

async function eliminarRuta(rutaId, rutaNombre) {
    if (!confirm(`¿Estás seguro de eliminar la ruta "${rutaNombre}"?`)) return;

    try {
        const response = await fetch(`${API_BASE}/core/rutas.php?id=${rutaId}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (result.success) {
            await cargarSucursalesYRutas();
        } else {
            alert(result.message || "Error al eliminar la ruta.");
        }
    } catch (err) {
        console.error("Error eliminando ruta:", err);
        alert("Error de conexión al eliminar la ruta.");
    }
}

// Interceptar submit de guardarSucursalRapida para manejar Edición además de Creación
const originalGuardarSucursalRapida = window.guardarSucursalRapida;
window.guardarSucursalRapida = async function(event) {
    event.preventDefault();
    const form = document.getElementById('form-sucursal-rapida');
    const editId = form?.getAttribute('data-edit-id');
    const nombre = document.getElementById('form-sucursal-nombre')?.value.trim();

    if (!nombre) return;

    if (!editId) {
        // Creación normal
        return originalGuardarSucursalRapida(event);
    }

    // Edición
    try {
        const response = await fetch(`${API_BASE}/core/sucursales.php?id=${editId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: editId, nombre: nombre, destacada: 0 })
        });
        const result = await response.json();

        if (result.success) {
            form.removeAttribute('data-edit-id');
            cerrarModalSucursalRapida();
            await cargarSucursalesYRutas();
        } else {
            alert(result.message || "Error al actualizar la sucursal.");
        }
    } catch (err) {
        console.error("Error actualizando sucursal:", err);
        alert("Error de conexión al actualizar la sucursal.");
    }
};

// Interceptar submit de guardarRutaRapida para manejar Edición además de Creación
const originalGuardarRutaRapida = window.guardarRutaRapida;
window.guardarRutaRapida = async function(event) {
    event.preventDefault();
    const form = document.getElementById('form-ruta-rapida');
    const editId = form?.getAttribute('data-edit-id');
    const sucursalId = document.getElementById('form-cliente-sucursal-id')?.value;
    const nombre = document.getElementById('form-ruta-nombre')?.value.trim();
    const ciudad = document.getElementById('form-ruta-ciudad')?.value.trim();

    if (!sucursalId || !nombre || !ciudad) return;

    if (!editId) {
        // Creación normal
        const res = await originalGuardarRutaRapida(event);
        if (typeof cargarSucursalesYRutas === 'function') await cargarSucursalesYRutas();
        return res;
    }

    // Edición
    try {
        const response = await fetch(`${API_BASE}/core/rutas.php?id=${editId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: editId, nombre, ciudad, fk_sucursal: parseInt(sucursalId) })
        });
        const result = await response.json();

        if (result.success) {
            form.removeAttribute('data-edit-id');
            cerrarModalRutaRapida();
            await cargarSucursalesYRutas();
        } else {
            alert(result.message || "Error al actualizar la ruta.");
        }
    } catch (err) {
        console.error("Error actualizando ruta:", err);
        alert("Error de conexión al actualizar la ruta.");
    }
};

// ----------------------------------------------------
// Sub-Navegación dentro del Módulo Clientes (Clientes vs Sucursales y Rutas)
// ----------------------------------------------------

function cambiarSubTabCliente(subTab) {
    const btnDirectorio = document.getElementById('header-subtab-directorio') || document.getElementById('subtab-btn-directorio');
    const btnSucRutas = document.getElementById('header-subtab-sucursales-rutas') || document.getElementById('subtab-btn-sucursales-rutas');
    const viewDirectorio = document.getElementById('subtab-directorio-clientes');
    const viewSucRutas = document.getElementById('subtab-sucursales-rutas');

    if (!viewDirectorio || !viewSucRutas) return;

    const activeTextClass = 'text-charcoal font-extrabold cursor-pointer hover:text-black transition border-b-2 border-primary pb-0.5';
    const inactiveTextClass = 'text-gray-400 font-semibold cursor-pointer hover:text-charcoal transition border-b-2 border-transparent pb-0.5';

    const mobileBtnDirectorio = document.querySelector('.nav-btn[data-target="clientes"]');
    const mobileBtnSucRutas = document.querySelector('.nav-btn[data-target="sucursales-rutas"]');

    if (subTab === 'directorio') {
        viewDirectorio.classList.remove('hidden-view');
        viewSucRutas.classList.add('hidden-view');

        if (btnDirectorio) btnDirectorio.className = activeTextClass;
        if (btnSucRutas) btnSucRutas.className = inactiveTextClass;

        if (mobileBtnDirectorio) {
            mobileBtnDirectorio.classList.remove('text-gray-400');
            mobileBtnDirectorio.classList.add('text-charcoal');
            const icon = mobileBtnDirectorio.querySelector('.material-symbols-outlined');
            if (icon) icon.classList.add('filled');
        }
        if (mobileBtnSucRutas) {
            mobileBtnSucRutas.classList.remove('text-charcoal');
            mobileBtnSucRutas.classList.add('text-gray-400');
            const icon = mobileBtnSucRutas.querySelector('.material-symbols-outlined');
            if (icon) icon.classList.remove('filled');
        }

        if (typeof cargarClientes === 'function') cargarClientes();
    } else if (subTab === 'sucursales-rutas') {
        viewDirectorio.classList.add('hidden-view');
        viewSucRutas.classList.remove('hidden-view');

        if (btnSucRutas) btnSucRutas.className = activeTextClass;
        if (btnDirectorio) btnDirectorio.className = inactiveTextClass;

        if (mobileBtnSucRutas) {
            mobileBtnSucRutas.classList.remove('text-gray-400');
            mobileBtnSucRutas.classList.add('text-charcoal');
            const icon = mobileBtnSucRutas.querySelector('.material-symbols-outlined');
            if (icon) icon.classList.add('filled');
        }
        if (mobileBtnDirectorio) {
            mobileBtnDirectorio.classList.remove('text-charcoal');
            mobileBtnDirectorio.classList.add('text-gray-400');
            const icon = mobileBtnDirectorio.querySelector('.material-symbols-outlined');
            if (icon) icon.classList.remove('filled');
        }

        cargarSucursalesYRutas();
    }
}

// Exposición Global en Window
window.cambiarSubTabCliente = cambiarSubTabCliente;
window.cargarSucursalesYRutas = cargarSucursalesYRutas;
window.filtrarSucursalesYRutas = filtrarSucursalesYRutas;
window.filtrarSucursalesYRutasDebounced = filtrarSucursalesYRutasDebounced;
window.toggleAcordeonSucursal = toggleAcordeonSucursal;
window.abrirModalEditarSucursal = abrirModalEditarSucursal;
window.eliminarSucursal = eliminarSucursal;
window.abrirModalCrearRutaEnSucursal = abrirModalCrearRutaEnSucursal;
window.abrirModalEditarRuta = abrirModalEditarRuta;
window.eliminarRuta = eliminarRuta;
