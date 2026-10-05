// public/js/modules/dashboard.js
// Lógica del Dashboard y Vistas de Eventos (Día, Semana, Mes)

let recoleccionesDelDia = [];
let rutasDisponibles = [];
let sucursalesDisponibles = [];
let fechaBaseOffset = 0; // 0 = hoy, 1 = mañana, etc.
let filtroSucursalTexto = '';
let filtroEstadoWaSeleccionado = 'all';
let mesPopoverActual = new Date(); // Para navegar meses en el popover
let conteosMesPopover = {}; // Cache de conteos para el mes cargado en el popover

function obtenerBadgeEstado(estado, esTentativa) {
    if (esTentativa || estado === 'tentativa') {
        return { 
            text: 'TENTATIVA', 
            badgeHtml: `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200 font-bold text-[11px]"><span class="material-symbols-outlined text-[13px]">pending_actions</span> TENTATIVA</span>`,
            rowClass: 'hover:bg-slate-50/70 transition',
            isConfirmed: false,
            isDenied: false,
            isAttention: false
        };
    }

    const est = (estado || 'programada').toLowerCase();

    switch (est) {
        case 'aceptado':
        case 'aceptada':
        case 'completada':
            return { 
                text: 'CONFIRMADO', 
                badgeHtml: `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> CONFIRMADO</span>`,
                rowClass: 'hover:bg-slate-50/70 transition',
                isConfirmed: true,
                isDenied: false,
                isAttention: false
            };
        case 'denegado':
        case 'denegada':
        case 'rechazado':
        case 'rechazada':
        case 'cancelada':
            return { 
                text: 'LIBERADO / CANC.', 
                badgeHtml: `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 font-bold text-[11px]"><span class="material-symbols-outlined text-[13px]">free_cancellation</span> LIBERADO</span>`,
                rowClass: 'bg-slate-50/50 hover:bg-slate-50 transition border-dashed opacity-85',
                isConfirmed: false,
                isDenied: true,
                isAttention: false
            };
        case 'consulta':
            return { 
                text: 'CONSULTA / ATENCIÓN', 
                badgeHtml: `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[11px] animate-pulse"><span class="material-symbols-outlined text-[13px]">chat</span> ATENCIÓN</span>`,
                rowClass: 'bg-amber-50/30 hover:bg-amber-50/60 transition',
                isConfirmed: false,
                isDenied: false,
                isAttention: true
            };
        case 'notificacion1':
        case 'notificacion2':
        case 'notificacion3':
            const numNotif = est.replace('notificacion', '');
            return { 
                text: `NOTIFICADO (${numNotif})`, 
                badgeHtml: `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-bold text-[11px]"><span class="material-symbols-outlined text-[13px]">outgoing_mail</span> NOTIFICADO ${numNotif}</span>`,
                rowClass: 'hover:bg-slate-50/70 transition',
                isConfirmed: false,
                isDenied: false,
                isAttention: false
            };
        case 'agendado':
        case 'agendada':
        case 'programado':
        case 'programada':
        default:
            return { 
                text: 'PROGRAMADO', 
                badgeHtml: `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-50 text-slate-700 border border-slate-200 font-bold text-[11px]"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> PROGRAMADO</span>`,
                rowClass: 'hover:bg-slate-50/70 transition',
                isConfirmed: false,
                isDenied: false,
                isAttention: false
            };
    }
}

// Carga las sucursales para el select interactivo con búsqueda
async function cargarDatalistSucursalesDashboard() {
    try {
        const res = await fetch(`${API_BASE}/core/sucursales.php?limit=100`);
        const result = await res.json();
        if (result.success && result.data) {
            sucursalesDisponibles = result.data;
            renderOpcionesSucursalesDropdown();
        }
    } catch (e) {
        console.error("Error cargando sucursales:", e);
    }
}

// Renderiza las opciones en el menú desplegable del select de sucursal
function renderOpcionesSucursalesDropdown(queryFiltro = '') {
    const listaEl = document.getElementById('lista-opciones-sucursales');
    if (!listaEl) return;

    const q = (queryFiltro || '').toLowerCase().trim();
    let html = '';

    // Opción: "Todas las sucursales"
    const esTodasActiva = (!filtroSucursalTexto || filtroSucursalTexto === '');
    if (!q || 'todas las sucursales'.includes(q) || 'todas'.includes(q)) {
        html += `
            <div onclick="seleccionarSucursalFiltro('')" class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer transition ${esTodasActiva ? 'bg-emerald-50 text-emerald-900 font-bold' : 'hover:bg-slate-50 text-slate-700 font-medium'}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] ${esTodasActiva ? 'text-emerald-600' : 'text-slate-400'}">domain</span>
                    <span>Todas las sucursales</span>
                </div>
                ${esTodasActiva ? '<span class="material-symbols-outlined text-[16px] text-emerald-600">check</span>' : ''}
            </div>
        `;
    }

    // Opciones por cada sucursal disponible
    const filtradas = sucursalesDisponibles.filter(s => {
        if (!q) return true;
        return (s.nombre || '').toLowerCase().includes(q) || (s.ciudad || '').toLowerCase().includes(q);
    });

    filtradas.forEach(s => {
        const esActiva = (filtroSucursalTexto.toLowerCase() === s.nombre.toLowerCase());
        html += `
            <div onclick="seleccionarSucursalFiltro('${s.nombre.replace(/'/g, "\\'")}')" class="flex items-center justify-between px-3 py-2 rounded-xl cursor-pointer transition ${esActiva ? 'bg-emerald-50 text-emerald-900 font-bold' : 'hover:bg-slate-50 text-slate-700 font-medium'}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] ${esActiva ? 'text-emerald-600' : 'text-slate-400'}">store</span>
                    <span>${s.nombre}</span>
                </div>
                ${esActiva ? '<span class="material-symbols-outlined text-[16px] text-emerald-600">check</span>' : ''}
            </div>
        `;
    });

    if (filtradas.length === 0 && q && !'todas las sucursales'.includes(q)) {
        html += `
            <div class="p-3 text-center text-slate-400 font-medium text-xs">
                No se encontraron sucursales
            </div>
        `;
    }

    listaEl.innerHTML = html;
}

// Abrir/cerrar dropdown de sucursal
function toggleDropdownFiltroSucursal(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('menu-filtro-sucursal-opciones');
    if (!menu) return;

    const estaOculto = menu.classList.contains('hidden');
    if (estaOculto) {
        menu.classList.remove('hidden');
        renderOpcionesSucursalesDropdown();
        const inputBuscar = document.getElementById('input-buscar-sucursal-en-select');
        if (inputBuscar) {
            inputBuscar.value = '';
            setTimeout(() => inputBuscar.focus(), 50);
        }
        const btnLimpiar = document.getElementById('btn-limpiar-busqueda-sucursal');
        if (btnLimpiar) btnLimpiar.classList.add('hidden');
    } else {
        menu.classList.add('hidden');
    }
}

function cerrarDropdownFiltroSucursal() {
    const menu = document.getElementById('menu-filtro-sucursal-opciones');
    if (menu) menu.classList.add('hidden');
}

// Filtrar opciones en vivo mientras se escribe dentro del input del select
function alFiltrarOpcionesSucursales(valor) {
    const btnLimpiar = document.getElementById('btn-limpiar-busqueda-sucursal');
    if (btnLimpiar) {
        if (valor && valor.trim().length > 0) {
            btnLimpiar.classList.remove('hidden');
        } else {
            btnLimpiar.classList.add('hidden');
        }
    }
    renderOpcionesSucursalesDropdown(valor);
}

function limpiarTextoBuscarSucursal() {
    const input = document.getElementById('input-buscar-sucursal-en-select');
    const btnLimpiar = document.getElementById('btn-limpiar-busqueda-sucursal');
    if (input) {
        input.value = '';
        input.focus();
    }
    if (btnLimpiar) btnLimpiar.classList.add('hidden');
    renderOpcionesSucursalesDropdown('');
}

// Seleccionar una opción del dropdown
function seleccionarSucursalFiltro(nombreSucursal) {
    filtroSucursalTexto = nombreSucursal || '';
    const label = document.getElementById('label-filtro-sucursal-actual');
    if (label) {
        label.innerText = filtroSucursalTexto ? filtroSucursalTexto : 'Todas las sucursales';
    }
    cerrarDropdownFiltroSucursal();
    renderTablaRutasEventos();
}

// Cerrar el dropdown al hacer clic afuera
document.addEventListener('click', (e) => {
    const cont = document.getElementById('contenedor-filtro-sucursal-dropdown');
    if (cont && !cont.contains(e.target)) {
        cerrarDropdownFiltroSucursal();
    }
});

// Inicializa o actualiza la tira de días (D-0 a D+3)
async function setupTiraDias() {
    const containerTira = document.getElementById('tira-dias-container');
    const labelMesTicker = document.getElementById('ticker-mes-label');
    if (!containerTira) return;

    const baseDate = new Date();
    baseDate.setDate(baseDate.getDate() + fechaBaseOffset);

    // Obtener los 4 días a mostrar (D-0 a D+3 a partir de baseDate)
    const dias = [];
    for (let i = 0; i < 4; i++) {
        const d = new Date(baseDate);
        d.setDate(d.getDate() + i);
        dias.push(d);
    }

    // Actualizar etiqueta del mes compacto (ej: "Oct 2026")
    if (labelMesTicker) {
        const mesesNombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const m1 = mesesNombres[dias[0].getMonth()];
        const m2 = mesesNombres[dias[3].getMonth()];
        const year = dias[0].getFullYear();
        labelMesTicker.innerText = (m1 === m2) ? `${m1} ${year}` : `${m1} / ${m2} ${year}`;
    }

    // Si aún no hay fechaActualIso, fijarla en dias[0]
    if (!fechaActualIso) {
        fechaActualIso = formatLocalIso(dias[0]);
    }

    // Consultar conteos de recolecciones para el rango D-0 a D+3
    const fechaInicioIso = formatLocalIso(dias[0]);
    const fechaFinIso = formatLocalIso(dias[3]);
    let conteosPorFecha = {};

    try {
        const resRango = await fetch(`${API_BASE}/recolecciones/rango.php?inicio=${fechaInicioIso}&fin=${fechaFinIso}`);
        const dataRango = await resRango.json();
        if (dataRango.success && dataRango.data) {
            conteosPorFecha = dataRango.data;
        }
    } catch (err) {
        console.error("Error obteniendo conteos de tira de días:", err);
    }

    // Renderizar las 4 tarjetas de días compactas
    const diasSemanaNombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const hoyStr = formatLocalIso(new Date());

    let tiraHtml = '';
    dias.forEach((d, idx) => {
        const dIso = formatLocalIso(d);
        const isActive = (dIso === fechaActualIso);
        const count = conteosPorFecha[dIso] || 0;

        let tituloDia = `${diasSemanaNombres[d.getDay()]} ${d.getDate()}`;
        if (dIso === hoyStr) {
            tituloDia = `Hoy (${d.getDate()} ${mesesCortos[d.getMonth()]})`;
        } else if (idx === 1 && fechaBaseOffset === 0) {
            tituloDia = `Mañana (${d.getDate()} ${mesesCortos[d.getMonth()]})`;
        } else {
            tituloDia = `${diasSemanaNombres[d.getDay()]} ${d.getDate()} (D+${idx})`;
        }

        const activeClasses = isActive 
            ? 'bg-emerald-50/70 border-emerald-300 ring-2 ring-emerald-100 shadow-xs' 
            : 'bg-slate-50 hover:bg-white border-slate-200 shadow-2xs group';
        const activeTextClass = isActive ? 'text-emerald-900 font-extrabold' : 'text-slate-800 font-bold';
        const activeDot = isActive ? 'bg-emerald-600' : 'bg-slate-400';
        const badgeBg = isActive ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700';

        tiraHtml += `
            <div onclick="seleccionarDiaTira('${dIso}', '${tituloDia}')" class="${activeClasses} border rounded-xl px-2.5 py-1.5 flex flex-col justify-between cursor-pointer transition">
                <div class="flex items-center justify-between gap-1">
                    <div class="flex items-center gap-1 min-w-0">
                        <span class="w-1.5 h-1.5 rounded-full ${activeDot} shrink-0"></span>
                        <span class="text-[11px] ${activeTextClass} truncate">${tituloDia}</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded-full ${badgeBg} text-[10px] font-bold shrink-0">${count} pts</span>
                </div>
                <div class="mt-1 flex items-center justify-between text-[10px] text-slate-500">
                    <span class="truncate">${count > 0 ? `${count} paradas` : 'Sin paradas'}</span>
                    <div class="w-8 bg-slate-200 h-1 rounded-full overflow-hidden ml-1 flex-shrink-0">
                        <div class="${isActive ? 'bg-emerald-600' : 'bg-slate-400'} h-full rounded-full" style="width: ${count > 0 ? Math.min(100, count * 10) : 0}%;"></div>
                    </div>
                </div>
            </div>
        `;
    });

    containerTira.innerHTML = tiraHtml;
}

// Compatibilidad con la llamada antigua de initApp()
function setupBotonesDias() {
    cargarDatalistSucursalesDashboard();
    setupTiraDias();
    if (!fechaActualIso) {
        fechaActualIso = formatLocalIso(new Date());
    }
    renderDia(fechaActualIso, 'Hoy');
}

// Al hacer clic en un día de la tira
function seleccionarDiaTira(fechaIso, titulo) {
    fechaActualIso = fechaIso;
    tituloActual = titulo;
    setupTiraDias();
    renderDia(fechaIso, titulo);
}

// Mover el offset de la tira (-1 día o +1 día)
function cambiarDiaRelativo(delta) {
    fechaBaseOffset += delta;
    setupTiraDias();
}

function alCambiarFiltroEstadoWa() {
    const sel = document.getElementById('filtro-estado-wa');
    filtroEstadoWaSeleccionado = sel ? sel.value : 'all';
    renderTablaRutasEventos();
}

// Clic en KPI Requieren Atención: filtrar tabla por estado 'consulta'
function filtrarRequierenAtencion() {
    const sel = document.getElementById('filtro-estado-wa');
    if (sel) {
        sel.value = 'wait';
        filtroEstadoWaSeleccionado = 'wait';
    }
    renderTablaRutasEventos();
}

// ====================================================
// Lógica del Popover Filtro Calendario (filtroCalendario)
// ====================================================

function togglePopoverCalendario() {
    const popover = document.getElementById('datepickerPopover');
    if (!popover) return;

    const estaOculto = popover.classList.contains('hidden');
    if (estaOculto) {
        // Inicializar con la fecha actual seleccionada o hoy
        if (fechaActualIso) {
            const partes = fechaActualIso.split('-');
            if (partes.length === 3) {
                mesPopoverActual = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
            } else {
                mesPopoverActual = new Date();
            }
        } else {
            mesPopoverActual = new Date();
        }
        popover.classList.remove('hidden');
        renderGridCalendarioPopover();
    } else {
        popover.classList.add('hidden');
    }
}

function cerrarPopoverCalendario() {
    const popover = document.getElementById('datepickerPopover');
    if (popover) popover.classList.add('hidden');
}

function navegarMesCalendarioPopover(delta) {
    mesPopoverActual.setMonth(mesPopoverActual.getMonth() + delta);
    renderGridCalendarioPopover();
}

function irAHoyCalendarioPopover() {
    mesPopoverActual = new Date();
    const hoyIso = formatLocalIso(new Date());
    seleccionarFechaDesdeCalendarioPopover(hoyIso, 'Hoy');
}

async function renderGridCalendarioPopover() {
    const tituloEl = document.getElementById('cal-popover-mes-titulo');
    const gridEl = document.getElementById('cal-popover-grid-dias');
    const infoSelEl = document.getElementById('cal-popover-seleccion-info');
    if (!gridEl || !tituloEl) return;

    const year = mesPopoverActual.getFullYear();
    const month = mesPopoverActual.getMonth(); // 0 a 11

    const mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    tituloEl.innerText = `${mesesNombres[month]} ${year}`;

    if (infoSelEl) {
        infoSelEl.innerText = fechaActualIso ? `Seleccionado: ${fechaActualIso}` : 'Selecciona un día';
    }

    gridEl.innerHTML = `<div class="col-span-7 py-6 text-center"><div class="animate-spin rounded-full h-5 w-5 border-b-2 border-emerald-600 mx-auto"></div></div>`;

    // Primer día del mes y último día del mes
    const primerDia = new Date(year, month, 1);
    const ultimoDia = new Date(year, month + 1, 0);

    // Días del mes anterior para completar la semana (Semana iniciando en Lunes = 1, Domingo = 0)
    let diaSemanaInicio = primerDia.getDay() === 0 ? 6 : primerDia.getDay() - 1; // 0=Lun, 6=Dom
    const fechaInicioRango = new Date(year, month, 1 - diaSemanaInicio);
    
    // Total de celdas a mostrar (múltiplo de 7, usualmente 35 o 42)
    const totalDiasMes = ultimoDia.getDate();
    const totalCeldas = Math.ceil((diaSemanaInicio + totalDiasMes) / 7) * 7;
    const fechaFinRango = new Date(year, month, 1 - diaSemanaInicio + totalCeldas - 1);

    const inicioIso = formatLocalIso(fechaInicioRango);
    const finIso = formatLocalIso(fechaFinRango);

    try {
        const res = await fetch(`${API_BASE}/recolecciones/rango.php?inicio=${inicioIso}&fin=${finIso}`);
        const result = await res.json();
        conteosMesPopover = (result.success && result.data) ? result.data : {};
    } catch (err) {
        console.error("Error al obtener conteos del calendario:", err);
        conteosMesPopover = {};
    }

    const hoyIso = formatLocalIso(new Date());
    let htmlCeldas = '';

    for (let i = 0; i < totalCeldas; i++) {
        const celdaFecha = new Date(fechaInicioRango);
        celdaFecha.setDate(celdaFecha.getDate() + i);

        const celdaIso = formatLocalIso(celdaFecha);
        const diaNum = celdaFecha.getDate();
        const esDelMesActual = (celdaFecha.getMonth() === month);
        const esSeleccionado = (celdaIso === fechaActualIso);
        const esHoy = (celdaIso === hoyIso);
        const pts = conteosMesPopover[celdaIso] || 0;

        // Título descriptivo para la recolección
        const diasSemanaNombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        const mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        let tituloVisual = `${diasSemanaNombres[celdaFecha.getDay()]} ${diaNum} ${mesesCortos[celdaFecha.getMonth()]}`;
        if (esHoy) tituloVisual = `Hoy (${diaNum} ${mesesCortos[celdaFecha.getMonth()]})`;

        // Clases de estilo según diseño NewView/filtroCalendario
        let estiloCard = '';
        let estiloNumero = '';
        let badgePts = '';

        if (esSeleccionado) {
            // Día Seleccionado: Anillo esmeralda / fondo de acento
            estiloCard = 'bg-emerald-50/90 border-2 border-emerald-600 shadow-sm ring-2 ring-emerald-200 cursor-pointer';
            estiloNumero = 'text-emerald-950 font-extrabold';
            badgePts = `<span class="px-1 rounded bg-emerald-600 text-white text-[9px] font-bold">${pts} pts</span>`;
        } else if (esHoy) {
            // Hoy: Borde esmeralda suave
            estiloCard = 'bg-emerald-50/40 border border-emerald-400 text-emerald-900 ring-1 ring-emerald-200 hover:bg-emerald-100/60 cursor-pointer';
            estiloNumero = 'text-emerald-900 font-extrabold';
            badgePts = `<span class="px-1 rounded bg-emerald-600 text-white text-[9px] font-bold">${pts} pts</span>`;
        } else if (!esDelMesActual) {
            // Días fuera de este mes (atenuados)
            estiloCard = 'bg-slate-50/40 border border-transparent text-slate-400 hover:bg-slate-100/60 cursor-pointer opacity-70';
            estiloNumero = 'text-slate-400 font-medium';
            badgePts = `<span class="px-1 rounded ${pts > 0 ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-300'} text-[9px] font-bold">${pts} pts</span>`;
        } else {
            // Días normales del mes actual
            if (pts > 0) {
                estiloCard = 'bg-white hover:bg-emerald-50/50 border border-slate-200 text-slate-700 hover:border-emerald-300 cursor-pointer transition shadow-2xs';
                estiloNumero = 'text-slate-800 font-bold';
                badgePts = `<span class="px-1 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-bold">${pts} pts</span>`;
            } else {
                estiloCard = 'bg-slate-50/50 border border-slate-100 text-slate-400 hover:bg-slate-100/60 cursor-pointer transition';
                estiloNumero = 'text-slate-500 font-medium';
                badgePts = `<span class="px-1 rounded bg-slate-100 text-slate-400 text-[9px] font-bold">0 pts</span>`;
            }
        }

        htmlCeldas += `
            <div onclick="seleccionarFechaDesdeCalendarioPopover('${celdaIso}', '${tituloVisual}')" class="p-1 rounded-xl ${estiloCard} flex flex-col items-center justify-between min-h-[48px] transition select-none group">
                <div class="flex items-center gap-0.5">
                    ${esHoy ? '<span class="w-1 h-1 rounded-full bg-emerald-600"></span>' : ''}
                    <span class="text-[11px] ${estiloNumero}">${diaNum}</span>
                </div>
                ${badgePts}
            </div>
        `;
    }

    gridEl.innerHTML = htmlCeldas;
}

// Al hacer clic en un día del popover de calendario
function seleccionarFechaDesdeCalendarioPopover(fechaIso, titulo) {
    fechaActualIso = fechaIso;
    tituloActual = titulo;

    // Calcular el offset en días con respecto a hoy para alinear la tira de días D-0 a D+3
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const partes = fechaIso.split('-');
    const fechaObj = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
    fechaObj.setHours(0, 0, 0, 0);

    const diffTiempo = fechaObj.getTime() - hoy.getTime();
    const diffDias = Math.round(diffTiempo / (1000 * 60 * 60 * 24));

    fechaBaseOffset = diffDias;

    cerrarPopoverCalendario();
    setupTiraDias();
    renderDia(fechaIso, titulo);
}

// Cerrar el popover al hacer clic afuera
document.addEventListener('click', (e) => {
    const popover = document.getElementById('datepickerPopover');
    const trigger = document.getElementById('selector-mes-compacto');
    if (popover && !popover.classList.contains('hidden') && trigger) {
        if (!popover.contains(e.target) && !trigger.contains(e.target)) {
            cerrarPopoverCalendario();
        }
    }
});

// Función principal que consulta el endpoint del día y renderiza el dashboard
async function renderDia(fechaIso, titulo) {
    fechaActualIso = fechaIso;
    tituloActual = titulo || fechaIso;

    const containerRutas = document.getElementById('contenedor-rutas-eventos');
    if (containerRutas) {
        containerRutas.innerHTML = `
            <div class="p-12 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
                <p class="text-xs text-slate-400 font-semibold mt-3">Cargando eventos de recolección...</p>
            </div>
        `;
    }

    try {
        const response = await fetch(`${API_BASE}/recolecciones/dia.php?fecha=${fechaIso}&estado=todos&sucursal=todas`);
        const result = await response.json();

        if (result.success) {
            recoleccionesDelDia = result.data || [];
            actualizarKpisDashboard(recoleccionesDelDia);
            renderTablaRutasEventos();
        } else {
            if (containerRutas) {
                containerRutas.innerHTML = `<div class="p-8 text-center text-red-500 font-bold text-sm">${result.message || 'Error al obtener eventos.'}</div>`;
            }
        }
    } catch (error) {
        console.error("Error al conectar con recolecciones/dia.php:", error);
        if (containerRutas) {
            containerRutas.innerHTML = `<p class="text-red-500 text-center text-sm font-bold p-8">Error conectando con la base de datos.</p>`;
        }
    }
}

// Calcula y actualiza las 4 tarjetas KPI superiores
function actualizarKpisDashboard(recolecciones) {
    const totalProg = recolecciones.length;
    let confirmados = 0;
    let denegados = 0;
    let atencion = 0;

    recolecciones.forEach(rec => {
        const est = (rec.estado_recoleccion || rec.estado || '').toLowerCase();
        const esTent = rec.es_tentativa || (est === 'tentativa');

        if (est === 'aceptado' || est === 'aceptada' || est === 'completada') {
            confirmados++;
        } else if (est === 'denegado' || est === 'denegada' || est === 'rechazado' || est === 'rechazada' || est === 'cancelada') {
            denegados++;
        } else if (est === 'consulta' || (!esTent && (est === 'notificacion1' || est === 'notificacion2' || est === 'notificacion3'))) {
            // Notificados o clientes con respuesta en duda
            if (est === 'consulta') atencion++;
        }
    });

    const pctConfirmados = totalProg > 0 ? ((confirmados / totalProg) * 100).toFixed(1) : '0.0';

    // KPI 1: Confirmados
    const kpiConfCount = document.getElementById('kpi-confirmados-count');
    const kpiConfTotal = document.getElementById('kpi-confirmados-total');
    const kpiConfPct = document.getElementById('kpi-confirmados-pct');
    if (kpiConfCount) kpiConfCount.innerText = confirmados;
    if (kpiConfTotal) kpiConfTotal.innerText = `/ ${totalProg} prog.`;
    if (kpiConfPct) kpiConfPct.innerText = `${pctConfirmados}%`;

    // KPI 2: Denegados / Cancelados
    const kpiDenCount = document.getElementById('kpi-denegados-count');
    const kpiDenBadge = document.getElementById('kpi-denegados-badge');
    if (kpiDenCount) kpiDenCount.innerText = denegados;
    if (kpiDenBadge) kpiDenBadge.innerText = `${denegados} Paradas`;

    // KPI 3: Requieren Atención
    const kpiAtenCount = document.getElementById('kpi-atencion-count');
    if (kpiAtenCount) kpiAtenCount.innerText = atencion;

    // KPI 4: Automatización WhatsApp
    const kpiWaInfo = document.getElementById('kpi-wa-info');
    if (kpiWaInfo) kpiWaInfo.innerText = `Programadas hoy: ${totalProg}`;
}

// Renderiza la tabla de paradas agrupadas por sucursales aplicando los filtros locales
function renderTablaRutasEventos() {
    const container = document.getElementById('contenedor-rutas-eventos');
    const rutasResumenTxt = document.getElementById('rutas-resumen-texto');
    const paradasResumenTxt = document.getElementById('paradas-resumen-texto');
    if (!container) return;

    // 1. Filtrado local de datos
    let filtrados = recoleccionesDelDia.filter(rec => {
        // Filtro por sucursal (escritura en tiempo real o coincidencia)
        if (filtroSucursalTexto && filtroSucursalTexto.length > 0) {
            const query = filtroSucursalTexto.toLowerCase();
            const nomSucursal = (rec.sucursal_nombre || '').toLowerCase();
            const ciudadRuta = (rec.ruta_ciudad || '').toLowerCase();
            if (!nomSucursal.includes(query) && !ciudadRuta.includes(query)) {
                return false;
            }
        }

        // Filtro por estado WA
        if (filtroEstadoWaSeleccionado !== 'all') {
            const est = (rec.estado_recoleccion || rec.estado || '').toLowerCase();
            const esTent = rec.es_tentativa || (est === 'tentativa');

            if (filtroEstadoWaSeleccionado === 'conf') {
                if (est !== 'aceptado' && est !== 'aceptada' && est !== 'completada') return false;
            } else if (filtroEstadoWaSeleccionado === 'wait') {
                if (est !== 'consulta' && est !== 'notificacion1' && est !== 'notificacion2' && est !== 'notificacion3') return false;
            } else if (filtroEstadoWaSeleccionado === 'rej') {
                if (est !== 'denegado' && est !== 'denegada' && est !== 'rechazado' && est !== 'rechazada' && est !== 'cancelada') return false;
            } else if (filtroEstadoWaSeleccionado === 'tent') {
                if (!esTent) return false;
            }
        }

        return true;
    });

    if (filtrados.length === 0) {
        if (rutasResumenTxt) rutasResumenTxt.innerText = `0 sucursales`;
        if (paradasResumenTxt) paradasResumenTxt.innerText = `0 paradas activas`;
        container.innerHTML = `
            <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center shadow-xs">
                <span class="material-symbols-outlined text-slate-300 text-5xl mb-2">event_available</span>
                <p class="text-slate-700 font-bold text-base">Sin recolecciones para esta selección</p>
                <p class="text-xs text-slate-400 mt-1">No hay paradas que coincidan con la fecha y filtros seleccionados.</p>
            </div>
        `;
        return;
    }

    // 2. Agrupar por sucursal
    const gruposPorSucursal = {};
    filtrados.forEach(rec => {
        const sucursalKey = rec.sucursal_id || rec.sucursal_nombre || 'sin_sucursal';
        if (!gruposPorSucursal[sucursalKey]) {
            gruposPorSucursal[sucursalKey] = {
                id: rec.sucursal_id || 0,
                nombre: rec.sucursal_nombre || 'Sucursal Principal',
                ciudad: rec.ruta_ciudad || '',
                recolecciones: []
            };
        }
        gruposPorSucursal[sucursalKey].recolecciones.push(rec);
    });

    const totalSucursales = Object.keys(gruposPorSucursal).length;
    if (rutasResumenTxt) rutasResumenTxt.innerText = `Mostrando ${totalSucursales} sucursal${totalSucursales > 1 ? 'es' : ''}`;
    if (paradasResumenTxt) paradasResumenTxt.innerText = `${filtrados.length} paradas activas`;

    // 3. Renderizar cada bloque de sucursal
    let htmlFinal = '';

    for (const key in gruposPorSucursal) {
        const grupo = gruposPorSucursal[key];

        const totalPuntosSucursal = grupo.recolecciones.length;
        const confirmadosSucursal = grupo.recolecciones.filter(r => {
            const est = (r.estado_recoleccion || r.estado || '').toLowerCase();
            return est === 'aceptado' || est === 'aceptada' || est === 'completada';
        }).length;

        const pctSucursal = totalPuntosSucursal > 0 ? Math.round((confirmadosSucursal / totalPuntosSucursal) * 100) : 0;

        let filasTablaHtml = '';
        grupo.recolecciones.forEach((rec, idx) => {
            const indexFilaStr = String(idx + 1).padStart(2, '0');
            const esTentativa = rec.es_tentativa || (rec.estado_recoleccion === 'tentativa');
            const estadoValor = rec.estado_recoleccion || rec.estado || 'programada';
            const badge = obtenerBadgeEstado(estadoValor, esTentativa);

            const tel = rec.telefono_whatsapp || '';
            const telFormateado = tel ? (tel.startsWith('+') ? tel : `+57 ${tel}`) : 'Sin número';

            filasTablaHtml += `
                <tr class="${badge.rowClass}">
                    <td class="py-3.5 pl-4 pr-2 font-bold text-slate-400 w-12">${indexFilaStr}</td>
                    <td class="py-3.5 px-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold ${badge.isDenied ? 'text-slate-400 line-through' : 'text-slate-900'} text-[13px]">${rec.cliente_nombre || 'Cliente sin nombre'}</span>
                            ${rec.frecuencia_nombre ? `<span class="px-1.5 py-0.5 bg-slate-100 rounded text-slate-600 font-medium text-[11px]">${rec.frecuencia_nombre}</span>` : ''}
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-500">
                            <span class="material-symbols-outlined text-[13px] text-slate-400">phone</span>
                            <span class="text-[11px] font-medium text-slate-500">${telFormateado}</span>
                            ${tel ? `
                            <button onclick="copiarAlPortapapeles('${tel}')" class="p-0.5 text-slate-400 hover:text-emerald-700 rounded transition flex items-center cursor-pointer" title="Copiar número">
                                <span class="material-symbols-outlined text-[12px]">content_copy</span>
                            </button>` : ''}
                        </div>
                    </td>
                    <td class="py-3.5 px-3">
                        ${badge.badgeHtml}
                    </td>
                    <td class="py-3.5 pr-4 pl-2 text-right w-36">
                        <div class="flex items-center justify-end gap-1">
                            ${esTentativa ? `
                            <button onclick="agendarEventoTentativo(${rec.cliente_id}, ${rec.ruta_id || 'null'}, '${rec.fecha_programada || fechaActualIso}')" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 border border-emerald-200 transition cursor-pointer" title="Confirmar y agendar evento">
                                <span class="material-symbols-outlined text-[16px]">event_available</span>
                            </button>` : ''}

                            ${rec.cliente_id ? `
                            <button onclick="abrirModalChatwoot(${rec.cliente_id}, '${(rec.cliente_nombre || '').replace(/'/g, "\\'")}')" class="p-1.5 rounded-lg border border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 text-slate-500 transition cursor-pointer" title="Abrir Chat WhatsApp">
                                <span class="material-symbols-outlined text-[16px]">chat</span>
                            </button>` : ''}

                            <button onclick="abrirModalProgramarRecoleccion(${rec.id || 'null'}, ${rec.cliente_id || 'null'}, '${rec.fecha_programada || fechaActualIso}')" class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-500 transition cursor-pointer" title="Reasignar o editar recolección">
                                <span class="material-symbols-outlined text-[16px]">swap_horiz</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        htmlFinal += `
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                            <span class="material-symbols-outlined text-[18px]">domain</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Sucursal ${grupo.nombre}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            ${confirmadosSucursal} / ${totalPuntosSucursal} Puntos (${pctSucursal}% Confirmado)
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 pl-4 pr-2 w-12">#</th>
                                <th class="py-3 px-3">Cliente / Generador</th>
                                <th class="py-3 px-3">Estado WhatsApp</th>
                                <th class="py-3 pr-4 pl-2 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            ${filasTablaHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    container.innerHTML = htmlFinal;
}

// Utilidad para copiar número al portapapeles
function copiarAlPortapapeles(texto) {
    if (!navigator.clipboard) {
        const textArea = document.createElement("textarea");
        textArea.value = texto;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            if (typeof mostrarNotificacionToast === 'function') {
                mostrarNotificacionToast("Número copiado al portapapeles", "success");
            }
        } catch (err) {}
        document.body.removeChild(textArea);
        return;
    }
    navigator.clipboard.writeText(texto).then(() => {
        if (typeof mostrarNotificacionToast === 'function') {
            mostrarNotificacionToast("Número copiado al portapapeles", "success");
        }
    }).catch(err => {
        console.error("Error al copiar número:", err);
    });
}

function recargarDiaActual() {
    if (!fechaActualIso) {
        fechaActualIso = formatLocalIso(new Date());
    }
    setupTiraDias();
    renderDia(fechaActualIso, tituloActual || 'Hoy');
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
                estado: 'programado',
                tipo: 'frecuente',
                evento_origin: null
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
// Lógica de Modal Programar / Editar Recolección (Diseño NewView/formRecoleccion)
// ----------------------------------------------------
let clienteSeleccionadoProg = null;
let eventoIdEditandoProg = null;
let timerBusquedaClienteProg = null;

async function abrirModalProgramarRecoleccion(eventoId = null, clienteIdPreseleccionado = null, fechaPreseleccionada = null) {
    const modal = document.getElementById('modal-programar-recoleccion');
    const form = document.getElementById('form-programar-recoleccion');
    const tituloEl = document.getElementById('modal-prog-titulo');
    const btnTextoEl = document.getElementById('btn-aplicar-programacion-texto');
    const eventoIdInput = document.getElementById('form-prog-evento-id');
    if (!modal) return;

    if (form) form.reset();
    limpiarClienteSeleccionadoProg();

    eventoIdEditandoProg = eventoId;
    if (eventoIdInput) eventoIdInput.value = eventoId || '';

    if (tituloEl) {
        tituloEl.innerText = eventoId ? 'Modificar Recolección Asignada' : 'Programar Nueva Recolección';
    }
    if (btnTextoEl) {
        btnTextoEl.innerText = eventoId ? 'Actualizar Parada en Ruta' : 'Confirmar y Asignar a Ruta';
    }

    // Resetear modalidad a 'once' por defecto
    const radioOnce = document.querySelector('input[name="modalidad_recoleccion"][value="once"]');
    if (radioOnce) radioOnce.checked = true;
    actualizarEstiloModalidadProg();

    // Establecer fecha
    const fechaInput = document.getElementById('form-prog-fecha');
    const fechaDefecto = fechaPreseleccionada || fechaActualIso || formatLocalIso(new Date());
    if (fechaInput) {
        fechaInput.value = fechaDefecto;
        actualizarBadgeFechaProg(fechaDefecto);
        fechaInput.onchange = () => actualizarBadgeFechaProg(fechaInput.value);
    }

    // Cargar sucursales
    await cargarSucursalesModalProg();
    resetRutaModalProg();

    // Si viene de un evento existente o cliente preseleccionado
    if (clienteIdPreseleccionado) {
        await cargarYSeleccionarClientePorId(clienteIdPreseleccionado);
    }

    modal.classList.remove('hidden-view');
}

function cerrarModalProgramarRecoleccion() {
    const modal = document.getElementById('modal-programar-recoleccion');
    if (modal) modal.classList.add('hidden-view');
    eventoIdEditandoProg = null;
}

function actualizarEstiloModalidadProg() {
    const radioSelected = document.querySelector('input[name="modalidad_recoleccion"]:checked');
    const cardOnce = document.getElementById('card-modo-once');
    const cardRecurrent = document.getElementById('card-modo-recurrent');
    if (!radioSelected || !cardOnce || !cardRecurrent) return;

    if (radioSelected.value === 'once') {
        cardOnce.className = 'relative flex flex-col p-3.5 bg-emerald-50/50 border-2 border-emerald-600 rounded-xl shadow-2xs cursor-pointer transition';
        cardRecurrent.className = 'relative flex flex-col p-3.5 bg-white border border-slate-200 hover:border-slate-300 rounded-xl shadow-2xs cursor-pointer transition';
    } else {
        cardOnce.className = 'relative flex flex-col p-3.5 bg-white border border-slate-200 hover:border-slate-300 rounded-xl shadow-2xs cursor-pointer transition';
        cardRecurrent.className = 'relative flex flex-col p-3.5 bg-emerald-50/50 border-2 border-emerald-600 rounded-xl shadow-2xs cursor-pointer transition';
    }
}

function actualizarBadgeFechaProg(fechaIso) {
    const badgeTxt = document.getElementById('lbl-fecha-info-prog');
    if (!badgeTxt || !fechaIso) return;

    try {
        const partes = fechaIso.split('-');
        if (partes.length === 3) {
            const diasSem = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            const f = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
            badgeTxt.innerText = `${diasSem[f.getDay()]}, ${f.getDate()} ${meses[f.getMonth()]} ${f.getFullYear()}`;
        }
    } catch (e) {
        badgeTxt.innerText = fechaIso;
    }
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
    selectRuta.className = 'w-full pl-9 pr-8 py-2.5 bg-slate-100 text-slate-400 text-xs font-semibold rounded-xl border border-slate-200 shadow-2xs outline-none transition cursor-not-allowed appearance-none';
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
    selectRuta.className = 'w-full pl-9 pr-8 py-2.5 bg-slate-50 hover:bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200 shadow-2xs focus:ring-2 focus:ring-emerald-500 focus:outline-none focus:bg-white transition cursor-pointer appearance-none';
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

        dropdown.innerHTML = `<div class="p-3 text-center text-slate-400 font-semibold"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-emerald-600 mx-auto mb-1"></div> Buscando clientes...</div>`;
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
                    item.className = 'p-3 hover:bg-emerald-50 cursor-pointer transition border-b border-slate-100 last:border-0 flex justify-between items-center';
                    item.innerHTML = `
                        <div>
                            <p class="font-bold text-slate-900 text-xs">${c.nombre}</p>
                            <p class="text-[11px] text-slate-500">${c.telefono_whatsapp || 'Sin tel'} · ${c.ruta_nombre || 'Sin ruta'} · ${c.sucursal_nombre || 'Sin sucursal'}</p>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 text-[18px]">chevron_right</span>
                    `;
                    item.onclick = (e) => {
                        e.stopPropagation();
                        seleccionarClienteModalProg(c);
                    };
                    dropdown.appendChild(item);
                });
            } else {
                dropdown.innerHTML = `<div class="p-3 text-center text-slate-400 font-semibold text-xs">No se encontraron clientes</div>`;
            }
        } catch (err) {
            console.error("Error buscando clientes:", err);
            dropdown.innerHTML = `<div class="p-3 text-center text-red-500 font-semibold text-xs">Error al buscar clientes</div>`;
        }
    }, 250);
}

async function cargarYSeleccionarClientePorId(clienteId) {
    try {
        const res = await fetch(`${API_BASE}/core/clientes.php?id=${encodeURIComponent(clienteId)}`);
        const result = await res.json();
        if (result.success && result.data) {
            seleccionarClienteModalProg(result.data);
            if (result.data.sucursal_id) {
                const selectSuc = document.getElementById('form-prog-sucursal');
                if (selectSuc) {
                    selectSuc.value = result.data.sucursal_id;
                    await alCambiarSucursalModalProg();
                    if (result.data.ruta_id) {
                        const selectRuta = document.getElementById('form-prog-ruta');
                        if (selectRuta) selectRuta.value = result.data.ruta_id;
                    }
                }
            }
        }
    } catch (e) {
        console.error("Error cargando cliente por id:", e);
    }
}

function seleccionarClienteModalProg(cliente) {
    clienteSeleccionadoProg = cliente;
    const inputId = document.getElementById('form-prog-cliente-id');
    if (inputId) inputId.value = cliente.id;

    document.getElementById('dropdown-prog-clientes-options')?.classList.add('hidden');

    const wrapperSearch = document.getElementById('wrapper-input-cliente-search');
    const infoBox = document.getElementById('cliente-seleccionado-info');
    const panelCiclo = document.getElementById('panel-historial-ciclo-cliente');
    const lblNombre = document.getElementById('lbl-cliente-sel-nombre');
    const lblDetalle = document.getElementById('lbl-cliente-sel-detalle');

    if (wrapperSearch) wrapperSearch.classList.add('hidden');
    if (infoBox) infoBox.classList.remove('hidden');
    if (panelCiclo) panelCiclo.classList.remove('hidden');

    if (lblNombre) lblNombre.innerText = cliente.nombre;
    if (lblDetalle) {
        lblDetalle.innerText = `${cliente.telefono_whatsapp || 'Sin tel'} · ${cliente.ruta_nombre || 'Sin ruta'}`;
    }

    // Datos del panel dinámico de ciclo
    const lblFrec = document.getElementById('lbl-cliente-sel-frecuencia');
    const lblProxima = document.getElementById('lbl-cliente-proxima-visita');
    const lblTel = document.getElementById('lbl-cliente-telefono');
    const lblCiudad = document.getElementById('lbl-cliente-ciudad');

    if (lblFrec) lblFrec.innerText = cliente.frecuencia_nombre || 'No asignada';
    if (lblProxima) lblProxima.innerText = cliente.fecha_base || 'Sin fecha base';
    if (lblTel) lblTel.innerText = cliente.telefono_whatsapp || 'Sin número';
    if (lblCiudad) lblCiudad.innerText = `${cliente.sucursal_nombre || 'Sede'} / ${cliente.ruta_ciudad || 'Tolima'}`;
}

function limpiarClienteSeleccionadoProg() {
    clienteSeleccionadoProg = null;
    const clienteIdInput = document.getElementById('form-prog-cliente-id');
    const wrapperSearch = document.getElementById('wrapper-input-cliente-search');
    const searchInput = document.getElementById('form-prog-cliente-search');
    const infoBox = document.getElementById('cliente-seleccionado-info');
    const panelCiclo = document.getElementById('panel-historial-ciclo-cliente');
    const dropdown = document.getElementById('dropdown-prog-clientes-options');

    if (clienteIdInput) clienteIdInput.value = '';
    if (searchInput) searchInput.value = '';
    if (wrapperSearch) wrapperSearch.classList.remove('hidden');
    if (infoBox) infoBox.classList.add('hidden');
    if (panelCiclo) panelCiclo.classList.add('hidden');
    if (dropdown) dropdown.classList.add('hidden');
}

// Cerrar dropdown de clientes al hacer clic fuera
document.addEventListener('click', (e) => {
    const wrapperSearch = document.getElementById('wrapper-input-cliente-search');
    const dropdown = document.getElementById('dropdown-prog-clientes-options');
    if (dropdown && wrapperSearch && !wrapperSearch.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Enviar formulario (creación o edición)
async function ejecutarGuardadoProgramacion(event) {
    if (event) event.preventDefault();

    const clienteId = document.getElementById('form-prog-cliente-id')?.value;
    const fechaProg = document.getElementById('form-prog-fecha')?.value;
    const rutaId = document.getElementById('form-prog-ruta')?.value;
    const modalidadRadio = document.querySelector('input[name="modalidad_recoleccion"]:checked');
    const modalidad = modalidadRadio ? modalidadRadio.value : 'once';
    const btnSubmit = document.getElementById('btn-aplicar-programacion');

    if (!clienteId || !clienteSeleccionadoProg) {
        alert("Por favor selecciona un cliente de la lista.");
        return;
    }

    if (!fechaProg) {
        alert("Por favor selecciona una fecha válida para la recolección.");
        return;
    }

    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <span class="material-symbols-outlined text-[18px] animate-spin">sync</span>
            <span>Guardando asignación...</span>
        `;
    }

    try {
        if (modalidad === 'recurrent') {
            // Modificar Recurrencia: Recalcular eventos actualizando fecha base
            const resRecalculo = await fetch(`${API_BASE}/eventos/recalcular.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cliente_id: parseInt(clienteId),
                    fecha_cambio: fechaProg,
                    frecuencia_id: clienteSeleccionadoProg?.frecuencia_id || null,
                    evento_origin: 'user'
                })
            });

            const dataRecalculo = await resRecalculo.json();

            if (dataRecalculo.success) {
                const msg = dataRecalculo.message || dataRecalculo.data?.mensaje || "Recurrencia actualizada exitosamente.";
                alert(msg);
                cerrarModalProgramarRecoleccion();
                recargarDiaActual();
            } else {
                alert(dataRecalculo.message || "Error al recalcular las recolecciones.");
            }
        } else {
            // Solo por esta vez (Parada Extraordinaria / Unica) o Edición de Evento
            let resEvento;
            if (eventoIdEditandoProg) {
                // Editar recolección existente
                resEvento = await fetch(`${API_BASE}/core/eventos.php?id=${encodeURIComponent(eventoIdEditandoProg)}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        cliente_id: parseInt(clienteId),
                        ruta_id: rutaId ? parseInt(rutaId) : (clienteSeleccionadoProg?.ruta_id || null),
                        fecha_programada: fechaProg,
                        estado: 'programado',
                        tipo: 'unica',
                        evento_origin: null
                    })
                });
            } else {
                // Crear nueva recolección puntual
                resEvento = await fetch(`${API_BASE}/core/eventos.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        cliente_id: parseInt(clienteId),
                        ruta_id: rutaId ? parseInt(rutaId) : (clienteSeleccionadoProg?.ruta_id || null),
                        fecha_programada: fechaProg,
                        estado: 'programado',
                        tipo: 'unica',
                        evento_origin: null
                    })
                });
            }

            const dataEvento = await resEvento.json();

            if (dataEvento.success || resEvento.ok) {
                cerrarModalProgramarRecoleccion();
                recargarDiaActual();
            } else {
                alert(dataEvento.message || "Error al programar la recolección.");
            }
        }
    } catch (err) {
        console.error("Error al procesar recolección:", err);
        alert("Error de conexión al procesar la recolección.");
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = `
                <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                <span id="btn-aplicar-programacion-texto">Confirmar y Asignar a Ruta</span>
            `;
        }
    }
}

// Exponer funciones globales
window.obtenerBadgeEstado = obtenerBadgeEstado;
window.setupBotonesDias = setupBotonesDias;
window.setupTiraDias = setupTiraDias;
window.seleccionarDiaTira = seleccionarDiaTira;
window.cambiarDiaRelativo = cambiarDiaRelativo;
window.toggleDropdownFiltroSucursal = toggleDropdownFiltroSucursal;
window.cerrarDropdownFiltroSucursal = cerrarDropdownFiltroSucursal;
window.alFiltrarOpcionesSucursales = alFiltrarOpcionesSucursales;
window.limpiarTextoBuscarSucursal = limpiarTextoBuscarSucursal;
window.seleccionarSucursalFiltro = seleccionarSucursalFiltro;
window.alCambiarFiltroEstadoWa = alCambiarFiltroEstadoWa;
window.filtrarRequierenAtencion = filtrarRequierenAtencion;
window.togglePopoverCalendario = togglePopoverCalendario;
window.cerrarPopoverCalendario = cerrarPopoverCalendario;
window.navegarMesCalendarioPopover = navegarMesCalendarioPopover;
window.irAHoyCalendarioPopover = irAHoyCalendarioPopover;
window.renderGridCalendarioPopover = renderGridCalendarioPopover;
window.seleccionarFechaDesdeCalendarioPopover = seleccionarFechaDesdeCalendarioPopover;
window.copiarAlPortapapeles = copiarAlPortapapeles;
window.renderDia = renderDia;
window.recargarDiaActual = recargarDiaActual;
window.descargarExcel = descargarExcel;
window.agendarEventoTentativo = agendarEventoTentativo;
window.abrirModalProgramarRecoleccion = abrirModalProgramarRecoleccion;
window.cerrarModalProgramarRecoleccion = cerrarModalProgramarRecoleccion;
window.actualizarEstiloModalidadProg = actualizarEstiloModalidadProg;
window.ejecutarGuardadoProgramacion = ejecutarGuardadoProgramacion;
window.alCambiarSucursalModalProg = alCambiarSucursalModalProg;
window.alCambiarRutaModalProg = alCambiarRutaModalProg;
window.buscarClienteModalProg = buscarClienteModalProg;
window.seleccionarClienteModalProg = seleccionarClienteModalProg;
window.limpiarClienteSeleccionadoProg = limpiarClienteSeleccionadoProg;
window.cargarYSeleccionarClientePorId = cargarYSeleccionarClientePorId;

