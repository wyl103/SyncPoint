// public/js/modules/dashboard.js
// Lógica del Dashboard y Vistas de Eventos (Día, Semana, Mes)

let recoleccionesDelDia = [];
let mesActual = new Date();

function setupBotonesDias() {
    const hoy = new Date();
    const manana = new Date(hoy); manana.setDate(manana.getDate() + 1);
    const pasado = new Date(hoy); pasado.setDate(pasado.getDate() + 2);

    const containerBotones = document.querySelector('.flex.justify-between.items-end.mb-4 .flex.gap-1');
    if (!containerBotones) return;

    containerBotones.innerHTML = `
        <button onclick="renderDia('${formatLocalIso(hoy)}', 'Hoy', this)" class="btn-dia-tab px-3 py-1 bg-white text-charcoal text-xs font-bold rounded-md shadow-sm transition">Hoy</button>
        <button onclick="renderDia('${formatLocalIso(manana)}', 'Mañana', this)" class="btn-dia-tab px-3 py-1 text-gray-500 hover:text-charcoal text-xs font-bold rounded-md transition">Mañana</button>
        <button onclick="renderDia('${formatLocalIso(pasado)}', '${getDayName(pasado)} ${pasado.getDate()}', this)" class="btn-dia-tab px-3 py-1 text-gray-500 hover:text-charcoal text-xs font-bold rounded-md transition">${getDayName(pasado)} ${pasado.getDate()}</button>
    `;
    
    // Cargar hoy por defecto
    renderDia(formatLocalIso(hoy), 'Hoy', containerBotones.firstElementChild);
}

async function renderDia(fechaIso, titulo, btnElement) {
    fechaActualIso = fechaIso;
    tituloActual = titulo;
    const container = document.getElementById('lista-dia');
    const tituloElement = document.getElementById('dia-titulo');
    if (!container) return;
    
    const filtroEstado = document.getElementById('filtro-estado')?.value || 'todos';
    const filtroSucursal = document.getElementById('filtro-sucursal')?.value || 'todas';
    
    document.querySelectorAll('.btn-dia-tab').forEach(btn => {
        btn.classList.remove('bg-white', 'text-charcoal', 'shadow-sm');
        btn.classList.add('text-gray-500');
    });
    if (btnElement) {
        btnElement.classList.remove('text-gray-500');
        btnElement.classList.add('bg-white', 'text-charcoal', 'shadow-sm');
    }

    container.innerHTML = `<div class="p-8 text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div></div>`;
    
    try {
        const response = await fetch(`${API_BASE}/recolecciones/dia.php?fecha=${fechaIso}&estado=${filtroEstado}&sucursal=${filtroSucursal}`);
        const result = await response.json();

        if (result.success) {
            if (tituloElement) tituloElement.innerText = `${titulo} (${result.data.length})`;
            recoleccionesDelDia = result.data;
            
            if (result.data.length === 0) {
                container.innerHTML = `
                    <div class="bg-white border border-gray-200 rounded-xl p-8 text-center shadow-sm">
                        <span class="material-symbols-outlined text-gray-300 text-5xl mb-2">event_available</span>
                        <p class="text-gray-500 font-bold text-base">Día libre o sin recolecciones</p>
                        <p class="text-xs text-gray-400 mt-1">No hay recolecciones agendadas para esta fecha.</p>
                    </div>
                `;
                return;
            }

            const grupos = {
                otras: { nombre: 'Otras Sucursales', recolecciones: [] }
            };

            result.data.forEach(rec => {
                if (rec.destacada == 1) {
                    if (!grupos[rec.sucursal_id]) {
                        grupos[rec.sucursal_id] = { nombre: rec.sucursal_nombre, recolecciones: [] };
                    }
                    grupos[rec.sucursal_id].recolecciones.push(rec);
                } else {
                    grupos.otras.recolecciones.push(rec);
                }
            });

            container.innerHTML = '';
            
            for (const key in grupos) {
                const grupo = grupos[key];
                if (grupo.recolecciones.length === 0) continue;

                container.innerHTML += `
                    <div class="mt-6 mb-3 border-b-2 border-gray-100 pb-2">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">store</span>
                            ${grupo.nombre} <span class="text-sm font-normal text-gray-500">(${grupo.recolecciones.length})</span>
                        </h2>
                    </div>
                    <div class="grid gap-3" id="grupo-${key}"></div>
                `;

                const grupoContainer = document.getElementById(`grupo-${key}`);

                grupo.recolecciones.forEach(rec => {
                    let esTentativa = rec.es_tentativa || rec.estado_recoleccion === 'tentativa';
                    let colorEstado = esTentativa ? 'bg-blue-50 text-blue-800 border-blue-200' :
                                     (rec.estado_recoleccion === 'completada' ? 'bg-green-50 text-green-800 border-green-200' : 
                                     (rec.estado_recoleccion === 'cancelada' ? 'bg-red-50 text-red-800 border-red-200' : 'bg-yellow-50 text-yellow-800 border-yellow-200'));

                    let estadoTexto = esTentativa ? 'TENTATIVA' : (rec.estado_recoleccion || 'AGENDADO');

                    grupoContainer.innerHTML += `
                        <div class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 hover:border-primary transition">
                            <div class="flex-1 cursor-pointer" onclick="verDetallesRecoleccion(${rec.id || 'null'})">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-charcoal text-lg">${rec.cliente_nombre}</h3>
                                    ${rec.frecuencia_nombre ? `<span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-bold">${rec.frecuencia_nombre}</span>` : ''}
                                </div>
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-xs font-semibold text-gray-500 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">call</span>${rec.telefono_whatsapp || 'Sin número'}</p>
                                    ${rec.fecha_base ? `<p class="text-xs text-gray-400 font-semibold">Base: ${rec.fecha_base}</p>` : '<p class="text-xs text-amber-600 font-semibold">Sin fecha base</p>'}
                                </div>
                            </div>
                            <div class="flex items-center gap-2 justify-between border-t md:border-t-0 border-gray-100 pt-3 md:pt-0">
                                <span class="flex items-center gap-1 text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                                    <span class="material-symbols-outlined text-[16px] text-primary">route</span>Ruta ${rec.ruta_nombre || 'N/A'}
                                </span>
                                <span class="px-2 py-1 border text-[10px] font-bold rounded uppercase ${colorEstado}">
                                    ${estadoTexto}
                                </span>
                                ${esTentativa ? `
                                <button onclick="agendarEventoTentativo(${rec.cliente_id}, ${rec.ruta_id || 'null'}, '${rec.fecha_programada}')" class="px-2.5 py-1 rounded-lg bg-primary hover:bg-yellow-400 text-charcoal font-bold text-xs flex items-center gap-1 transition shadow-2xs" title="Convertir en evento agendado">
                                    <span class="material-symbols-outlined text-[15px]">event_available</span>
                                    <span>Agendar</span>
                                </button>` : ''}
                                ${rec.cliente_id ? `
                                <button onclick="abrirModalChatwoot(${rec.cliente_id}, '${(rec.cliente_nombre || '').replace(/'/g, "\\'")}')" class="p-1.5 rounded-lg bg-primary/20 hover:bg-yellow-400 text-charcoal transition" title="Abrir Chat de WhatsApp">
                                    <span class="material-symbols-outlined text-[18px]">forum</span>
                                </button>` : ''}
                            </div>
                        </div>
                    `;
                });
            }
        }
    } catch (error) {
        container.innerHTML = `<p class="text-red-500 text-center text-sm font-bold">Error conectando con la base de datos.</p>`;
    }
}

function changeDashView(vista) {
    ['dia', 'semana', 'mes'].forEach(v => {
        const lbl = document.getElementById(`lbl-${v}`);
        if (lbl) {
            lbl.classList.remove('bg-white', 'shadow-sm', 'text-charcoal', 'font-bold');
            lbl.classList.add('text-gray-500', 'font-semibold');
        }
        const dashEl = document.getElementById(`dash-${v}`);
        if (dashEl) dashEl.classList.add('hidden-view');
    });
    
    const activeLbl = document.getElementById(`lbl-${vista}`);
    if (activeLbl) {
        activeLbl.classList.add('bg-white', 'shadow-sm', 'text-charcoal', 'font-bold');
        activeLbl.classList.remove('text-gray-500', 'font-semibold');
    }
    const targetDash = document.getElementById(`dash-${vista}`);
    if (targetDash) targetDash.classList.remove('hidden-view');

    if (vista === 'semana') renderSemana();
    if (vista === 'mes') renderMes();
}

async function renderSemana() {
    const container = document.getElementById('lista-semana');
    if (!container) return;
    container.innerHTML = `<div class="col-span-2 text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div></div>`;
    
    const hoy = new Date();
    const finSemana = new Date(hoy);
    finSemana.setDate(finSemana.getDate() + 6);

    try {
        const response = await fetch(`${API_BASE}/recolecciones/rango.php?inicio=${formatLocalIso(hoy)}&fin=${formatLocalIso(finSemana)}`);
        const result = await response.json();
        
        container.innerHTML = '';
        for (let i = 0; i < 7; i++) {
            const currentDate = new Date(hoy);
            currentDate.setDate(currentDate.getDate() + i);
            const dateIso = formatLocalIso(currentDate);
            const totalRecs = result.data[dateIso] || 0;
            const tituloVisual = i === 0 ? 'Hoy' : i === 1 ? 'Mañana' : `${getDayName(currentDate)} ${currentDate.getDate()}`;

            container.innerHTML += `
                <div onclick="irADiaDesdeCalendario('${dateIso}', '${tituloVisual}')" class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm hover:border-primary cursor-pointer transition flex justify-between items-center group">
                    <div class="flex items-center gap-3">
                        <div class="bg-gray-100 p-2 rounded-lg text-gray-500 group-hover:bg-primary/20 group-hover:text-charcoal transition"><span class="material-symbols-outlined">calendar_today</span></div>
                        <h3 class="font-bold text-charcoal">${tituloVisual}</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-bold ${totalRecs > 0 ? 'text-primary' : 'text-gray-400'}">${totalRecs}</span>
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Clientes</p>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = `<p class="text-red-500 col-span-2 text-center">Error cargando semana.</p>`;
    }
}

function cambiarMes(direccion) {
    mesActual.setMonth(mesActual.getMonth() + direccion);
    renderMes();
}

async function renderMes() {
    const container = document.getElementById('grid-mes');
    const titulo = document.getElementById('mes-titulo');
    if (!container || !titulo) return;
    
    const primerDiaMes = new Date(mesActual.getFullYear(), mesActual.getMonth(), 1);
    const ultimoDiaMes = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 0);
    
    const mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    titulo.innerText = `${mesesNombres[mesActual.getMonth()]} ${mesActual.getFullYear()}`;

    try {
        const response = await fetch(`${API_BASE}/recolecciones/rango.php?inicio=${formatLocalIso(primerDiaMes)}&fin=${formatLocalIso(ultimoDiaMes)}`);
        const result = await response.json();

        container.innerHTML = '';
        
        let diaSemanaInicio = primerDiaMes.getDay() === 0 ? 6 : primerDiaMes.getDay() - 1;
        for (let i = 0; i < diaSemanaInicio; i++) {
            container.innerHTML += `<div class="h-14 md:h-20 bg-transparent"></div>`;
        }

        const hoyStr = formatLocalIso(new Date());

        for (let i = 1; i <= ultimoDiaMes.getDate(); i++) {
            const fechaIteracion = new Date(mesActual.getFullYear(), mesActual.getMonth(), i);
            const dateIso = formatLocalIso(fechaIteracion);
            const totalRecs = result.data[dateIso] || 0;
            
            let isToday = dateIso === hoyStr;
            let dot = totalRecs > 0 ? '<div class="w-1.5 h-1.5 bg-charcoal rounded-full mt-1"></div>' : '';
            let bgClass = isToday ? 'bg-primary text-charcoal shadow-md border-primary' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200';
            
            const tituloCorto = `${i} ${mesesCortos[mesActual.getMonth()]}`;

            container.innerHTML += `
                <div onclick="irADiaDesdeCalendario('${dateIso}', '${tituloCorto}')" class="h-14 md:h-20 ${bgClass} rounded-lg border flex flex-col items-center justify-center cursor-pointer transition shadow-sm relative">
                    <span class="font-bold text-sm md:text-base">${i}</span>
                    ${dot}
                    ${totalRecs > 0 ? `<span class="absolute top-1 right-1 text-[8px] font-bold text-gray-500">${totalRecs}</span>` : ''}
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = `<p class="text-red-500 col-span-7 text-center">Error cargando el calendario.</p>`;
    }
}

function irADiaDesdeCalendario(fechaIso, titulo) {
    changeDashView('dia');
    renderDia(fechaIso, titulo, null); 
}

function verDetallesRecoleccion(id) {
    const recoleccion = recoleccionesDelDia.find(r => r.id == id);
    if (!recoleccion) return;
    console.log("Mostrando detalles de:", recoleccion);
}

function recargarDiaActual() {
    const btnActivo = document.querySelector('.btn-dia-tab.bg-white');
    renderDia(fechaActualIso, tituloActual, btnActivo);
}

function descargarExcel() {
    if (!recoleccionesDelDia || recoleccionesDelDia.length === 0) {
        alert("No hay datos para exportar en este día o con estos filtros.");
        return;
    }

    let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; 
    csvContent += "Nombre del Cliente,Teléfono,Sucursal,Estado\n";

    recoleccionesDelDia.forEach(rec => {
        let nombre = `"${rec.cliente_nombre || ''}"`;
        let telefono = `"${rec.telefono_whatsapp || ''}"`;
        let sucursal = `"${rec.sucursal_nombre || 'N/A'}"`;
        let estado = `"${rec.estado_recoleccion || ''}"`.toUpperCase();
        
        csvContent += `${nombre},${telefono},${sucursal},${estado}\n`;
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Recolecciones_${fechaActualIso}.csv`);
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function agendarEventoTentativo(clienteId, rutaId, fechaProgramada) {
    try {
        const response = await fetch(`${API_BASE}/core/eventos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cliente_id: clienteId,
                ruta_id: rutaId,
                fecha_programada: fechaProgramada,
                estado: 'agendado',
                tipo: 'programada'
            })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            recargarDiaActual();
        } else {
            alert(data.message || "No se pudo agendar el evento.");
        }
    } catch (error) {
        console.error("Error agendando evento tentativo:", error);
        alert("Error conectando con el servidor para agendar el evento.");
    }
}

// ----------------------------------------------------
// Lógica de Modal Programar Recolección
// ----------------------------------------------------
let clienteSeleccionadoProg = null;
let timerBusquedaClienteProg = null;

async function abrirModalProgramarRecoleccion() {
    const modal = document.getElementById('modal-programar-recoleccion');
    const form = document.getElementById('form-programar-recoleccion');
    if (!modal) return;

    if (form) form.reset();
    limpiarClienteSeleccionadoProg();

    // Establecer fecha por defecto (hoy)
    const fechaInput = document.getElementById('form-prog-fecha');
    if (fechaInput) {
        fechaInput.value = fechaActualIso || formatLocalIso(new Date());
    }

    // Cargar sucursales
    await cargarSucursalesModalProg();
    
    // Resetear selector de ruta (disabled)
    resetRutaModalProg();

    modal.classList.remove('hidden-view');
}

function cerrarModalProgramarRecoleccion() {
    const modal = document.getElementById('modal-programar-recoleccion');
    if (modal) modal.classList.add('hidden-view');
}

async function cargarSucursalesModalProg() {
    const selectSuc = document.getElementById('form-prog-sucursal');
    if (!selectSuc) return;

    selectSuc.innerHTML = `<option value="">-- Seleccionar sucursal --</option>`;

    try {
        const res = await fetch(`${API_BASE}/core/sucursales.php?limit=100`);
        const result = await res.json();
        if (result.success && result.data) {
            result.data.forEach(suc => {
                selectSuc.innerHTML += `<option value="${suc.id}">${suc.nombre}</option>`;
            });
        }
    } catch (err) {
        console.error("Error cargando sucursales para modal prog:", err);
    }
}

function resetRutaModalProg() {
    const selectRuta = document.getElementById('form-prog-ruta');
    if (!selectRuta) return;

    selectRuta.disabled = true;
    selectRuta.className = 'w-full p-3 rounded-xl border border-gray-200 bg-gray-100 text-gray-400 text-sm font-semibold outline-none transition cursor-not-allowed';
    selectRuta.innerHTML = `<option value="">Selecciona primero una sucursal...</option>`;
}

async function alCambiarSucursalModalProg() {
    const sucursalId = document.getElementById('form-prog-sucursal')?.value;
    const selectRuta = document.getElementById('form-prog-ruta');
    if (!selectRuta) return;

    if (!sucursalId) {
        resetRutaModalProg();
        buscarClienteModalProg();
        return;
    }

    selectRuta.disabled = false;
    selectRuta.className = 'w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition cursor-pointer';
    selectRuta.innerHTML = `<option value="">Todas las rutas de esta sucursal</option>`;

    try {
        const res = await fetch(`${API_BASE}/core/rutas.php?sucursal_id=${encodeURIComponent(sucursalId)}&limit=100`);
        const result = await res.json();
        if (result.success && result.data) {
            result.data.forEach(ruta => {
                selectRuta.innerHTML += `<option value="${ruta.id}">${ruta.nombre}${ruta.ciudad ? ` (${ruta.ciudad})` : ''}</option>`;
            });
        }
    } catch (err) {
        console.error("Error cargando rutas para sucursal:", err);
    }

    buscarClienteModalProg();
}

function alCambiarRutaModalProg() {
    buscarClienteModalProg();
}

function buscarClienteModalProg() {
    clearTimeout(timerBusquedaClienteProg);
    timerBusquedaClienteProg = setTimeout(async () => {
        const dropdown = document.getElementById('dropdown-prog-clientes-options');
        const query = document.getElementById('form-prog-cliente-search')?.value.trim() || '';
        const sucursalId = document.getElementById('form-prog-sucursal')?.value || '';
        const rutaId = document.getElementById('form-prog-ruta')?.value || '';

        if (!dropdown) return;

        dropdown.innerHTML = `<div class="p-3 text-center text-gray-400 font-semibold"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary mx-auto mb-1"></div> Buscando clientes...</div>`;
        dropdown.classList.remove('hidden');

        try {
            let url = `${API_BASE}/core/clientes.php?limit=20&q=${encodeURIComponent(query)}`;
            if (sucursalId) url += `&sucursal_id=${encodeURIComponent(sucursalId)}`;
            if (rutaId) url += `&ruta_id=${encodeURIComponent(rutaId)}`;

            const res = await fetch(url);
            const result = await res.json();

            if (result.success && result.data && result.data.length > 0) {
                dropdown.innerHTML = '';
                result.data.forEach(c => {
                    const item = document.createElement('div');
                    item.className = 'p-3 hover:bg-primary/20 cursor-pointer transition border-b border-gray-100 last:border-0 flex justify-between items-center';
                    item.innerHTML = `
                        <div>
                            <p class="font-bold text-charcoal text-xs">${c.nombre}</p>
                            <p class="text-[10px] text-gray-500">${c.telefono_whatsapp || 'Sin tel'} | Ruta: ${c.ruta_nombre || 'N/A'}</p>
                        </div>
                        <span class="material-symbols-outlined text-gray-400 text-[16px]">chevron_right</span>
                    `;
                    item.onclick = (e) => {
                        e.stopPropagation();
                        seleccionarClienteModalProg(c);
                    };
                    dropdown.appendChild(item);
                });
            } else {
                dropdown.innerHTML = `<div class="p-3 text-center text-gray-400 font-semibold">No se encontraron clientes</div>`;
            }
        } catch (err) {
            console.error("Error buscando clientes:", err);
            dropdown.innerHTML = `<div class="p-3 text-center text-red-500 font-semibold">Error al buscar clientes</div>`;
        }
    }, 250);
}

function seleccionarClienteModalProg(cliente) {
    clienteSeleccionadoProg = cliente;
    document.getElementById('form-prog-cliente-id').value = cliente.id;
    document.getElementById('dropdown-prog-clientes-options')?.classList.add('hidden');

    const searchInput = document.getElementById('form-prog-cliente-search');
    const infoBox = document.getElementById('cliente-seleccionado-info');
    const lblNombre = document.getElementById('lbl-cliente-sel-nombre');
    const lblDetalle = document.getElementById('lbl-cliente-sel-detalle');

    if (searchInput) searchInput.classList.add('hidden');
    if (infoBox) infoBox.classList.remove('hidden');

    if (lblNombre) lblNombre.innerText = cliente.nombre;
    if (lblDetalle) {
        lblDetalle.innerText = `Tel: ${cliente.telefono_whatsapp || 'N/A'} | Ruta: ${cliente.ruta_nombre || 'Sin asignación'} | Sucursal: ${cliente.sucursal_nombre || 'N/A'}`;
    }
}

function limpiarClienteSeleccionadoProg() {
    clienteSeleccionadoProg = null;
    const clienteIdInput = document.getElementById('form-prog-cliente-id');
    const searchInput = document.getElementById('form-prog-cliente-search');
    const infoBox = document.getElementById('cliente-seleccionado-info');
    const dropdown = document.getElementById('dropdown-prog-clientes-options');

    if (clienteIdInput) clienteIdInput.value = '';
    if (searchInput) {
        searchInput.value = '';
        searchInput.classList.remove('hidden');
    }
    if (infoBox) infoBox.classList.add('hidden');
    if (dropdown) dropdown.classList.add('hidden');
}

// Cerrar dropdown de clientes al hacer clic fuera
document.addEventListener('click', (e) => {
    const inputSearch = document.getElementById('form-prog-cliente-search');
    const dropdown = document.getElementById('dropdown-prog-clientes-options');
    if (dropdown && inputSearch && !inputSearch.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

function solicitarConfirmacionRecoleccion(event) {
    event.preventDefault();

    const clienteId = document.getElementById('form-prog-cliente-id')?.value;
    const fechaProg = document.getElementById('form-prog-fecha')?.value;

    if (!clienteId || !clienteSeleccionadoProg) {
        alert("Por favor selecciona un cliente de la lista.");
        return;
    }

    if (!fechaProg) {
        alert("Por favor selecciona una fecha válida para la recolección.");
        return;
    }

    // Abrir modal de confirmación de frecuencia
    const modalConfirm = document.getElementById('modal-confirmar-frecuencia');
    if (modalConfirm) modalConfirm.classList.remove('hidden-view');
}

function cerrarModalConfirmarFrecuencia() {
    const modalConfirm = document.getElementById('modal-confirmar-frecuencia');
    if (modalConfirm) modalConfirm.classList.add('hidden-view');
}

async function procesarProgramacionRecoleccion(tipoCambio) {
    const clienteId = document.getElementById('form-prog-cliente-id')?.value;
    const fechaProg = document.getElementById('form-prog-fecha')?.value;
    const btnAplicar = document.getElementById('btn-aplicar-programacion');

    if (!clienteId || !fechaProg) return;

    cerrarModalConfirmarFrecuencia();
    if (btnAplicar) { btnAplicar.disabled = true; btnAplicar.innerText = "Procesando..."; }

    try {
        // 1. Si elige "todas", actualizamos la fecha_base del cliente
        if (tipoCambio === 'todas') {
            const resCliente = await fetch(`${API_BASE}/core/clientes.php?id=${clienteId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: clienteId,
                    nombre: clienteSeleccionadoProg.nombre,
                    telefono_whatsapp: clienteSeleccionadoProg.telefono_whatsapp,
                    fecha_base: fechaProg,
                    estado: 'agendado'
                })
            });
            const dataCliente = await resCliente.json();
            if (!dataCliente.success) {
                console.warn("Advertencia actualizando cliente:", dataCliente.message);
            }
        }

        // 2. Crear evento programado en recolecciones/eventos
        const resEvento = await fetch(`${API_BASE}/core/eventos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                cliente_id: parseInt(clienteId),
                ruta_id: clienteSeleccionadoProg.ruta_id || null,
                fecha_programada: fechaProg,
                estado: 'agendado',
                tipo: 'programada'
            })
        });

        const dataEvento = await resEvento.json();

        if (dataEvento.success || resEvento.ok) {
            cerrarModalProgramarRecoleccion();
            recargarDiaActual();
        } else {
            alert(dataEvento.message || "Error al programar la recolección.");
        }
    } catch (err) {
        console.error("Error al programar recolección:", err);
        alert("Error de conexión al procesar la recolección.");
    } finally {
        if (btnAplicar) {
            btnAplicar.disabled = false;
            btnAplicar.innerHTML = `<span class="material-symbols-outlined text-[18px]">check_circle</span> Aplicar`;
        }
    }
}

// Exponer funciones globales
window.abrirModalProgramarRecoleccion = abrirModalProgramarRecoleccion;
window.cerrarModalProgramarRecoleccion = cerrarModalProgramarRecoleccion;
window.alCambiarSucursalModalProg = alCambiarSucursalModalProg;
window.alCambiarRutaModalProg = alCambiarRutaModalProg;
window.buscarClienteModalProg = buscarClienteModalProg;
window.seleccionarClienteModalProg = seleccionarClienteModalProg;
window.limpiarClienteSeleccionadoProg = limpiarClienteSeleccionadoProg;
window.solicitarConfirmacionRecoleccion = solicitarConfirmacionRecoleccion;
window.cerrarModalConfirmarFrecuencia = cerrarModalConfirmarFrecuencia;
window.procesarProgramacionRecoleccion = procesarProgramacionRecoleccion;

