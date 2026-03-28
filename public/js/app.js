// public/js/app.js

// --- EL TRUCO PROFESIONAL (Ruta dinámica) ---
const baseTag = document.querySelector('base');
const baseHref = baseTag ? baseTag.getAttribute('href') : '/';
const BASE_PATH = baseHref.endsWith('/') ? baseHref.slice(0, -1) : baseHref;
let fechaActualIso = '';
let tituloActual = '';

const API_BASE = '../app/api'; 

// --- NAVEGACIÓN PRINCIPAL ---
function switchTab(tabId) {
    ['tab-dashboard', 'tab-clientes', 'tab-rutas'].forEach(id => {
        document.getElementById(id).classList.add('hidden-view');
    });
    document.getElementById(`tab-${tabId}`).classList.remove('hidden-view');
    
    const titles = { dashboard: 'Dashboard', clientes: 'Directorio de Clientes', rutas: 'Gestión de Zonas' };
    document.getElementById('header-title').innerText = titles[tabId];

    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('text-charcoal', 'bg-primary/20');
        btn.classList.add('text-gray-400', 'text-gray-500');
        btn.querySelector('.material-symbols-outlined').classList.remove('filled');
    });
    
    document.querySelectorAll(`.nav-btn[data-target="${tabId}"]`).forEach(activeBtn => {
        activeBtn.classList.remove('text-gray-400', 'text-gray-500');
        activeBtn.classList.add('text-charcoal');
        if(activeBtn.classList.contains('w-full')) activeBtn.classList.add('bg-primary/20');
        activeBtn.querySelector('.material-symbols-outlined').classList.add('filled');
    });
}

// --- LÓGICA DEL DASHBOARD Y API ---
const formatLocalIso = (d) => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getDayName = (d) => {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return dias[d.getDay()];
};

function setupBotonesDias() {
    const hoy = new Date();
    const manana = new Date(hoy); manana.setDate(manana.getDate() + 1);
    const pasado = new Date(hoy); pasado.setDate(pasado.getDate() + 2);

    const containerBotones = document.querySelector('.flex.justify-between.items-end.mb-4 .flex.gap-1');
    
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
            tituloElement.innerText = `${titulo} (${result.data.length})`;
            recoleccionesDelDia = result.data; // Guardamos para el modal de detalles
            
            if (result.data.length === 0) {
                container.innerHTML = `<div class="...">Día libre...</div>`; // Tu diseño de día libre
                return;
            }

            // Lógica de agrupación
            const sucursalesDestacadas = [1, 3, 4, 6, 10];
            const grupos = {
                otras: { nombre: 'Otras Sucursales', recolecciones: [] }
            };

            result.data.forEach(rec => {
                // Usamos la propiedad 'destacada' que ahora viene de la BD
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
            
            // Renderizar cada grupo
            for (const key in grupos) {
                const grupo = grupos[key];
                if (grupo.recolecciones.length === 0) continue;

                // Título de la sucursal/grupo
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
                    let colorEstado = rec.estado_recoleccion === 'pendiente' ? 'bg-yellow-50 text-yellow-800 border-yellow-200' : 
                                     (rec.estado_recoleccion === 'completada' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-gray-50 text-gray-800 border-gray-200');

                    // Notar el onclick="verDetallesRecoleccion(${rec.id})" y la clase cursor-pointer
                    grupoContainer.innerHTML += `
                        <div onclick="verDetallesRecoleccion(${rec.id})" class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 cursor-pointer hover:border-primary transition">
                            <div class="flex-1">
                                <h3 class="font-bold text-charcoal text-lg">${rec.cliente_nombre}</h3>
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-xs font-semibold text-gray-500 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">call</span>${rec.telefono_whatsapp}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 justify-between border-t md:border-t-0 border-gray-100 pt-3 md:pt-0">
                                <span class="flex items-center gap-1 text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                                    <span class="material-symbols-outlined text-[16px] text-primary">route</span>Ruta ${rec.ruta_nombre || 'N/A'}
                                </span>
                                <span class="px-2 py-1 border text-[10px] font-bold rounded uppercase ${colorEstado}">
                                    ${rec.estado_recoleccion}
                                </span>
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

// Esta función se llamará DESDE auth.js cuando el login sea exitoso
function initApp() {
    setupBotonesDias();
}

// --- NUEVA LÓGICA DASHBOARD: SEMANA Y MES ---

// Cambiar entre pestañas internas del Dashboard
function changeDashView(vista) {
    ['dia', 'semana', 'mes'].forEach(v => {
        const lbl = document.getElementById(`lbl-${v}`);
        lbl.classList.remove('bg-white', 'shadow-sm', 'text-charcoal', 'font-bold');
        lbl.classList.add('text-gray-500', 'font-semibold');
        document.getElementById(`dash-${v}`).classList.add('hidden-view');
    });
    
    const activeLbl = document.getElementById(`lbl-${vista}`);
    activeLbl.classList.add('bg-white', 'shadow-sm', 'text-charcoal', 'font-bold');
    activeLbl.classList.remove('text-gray-500', 'font-semibold');
    document.getElementById(`dash-${vista}`).classList.remove('hidden-view');

    if (vista === 'semana') renderSemana();
    if (vista === 'mes') renderMes();
}

// Renderizar la semana (próximos 7 días)
async function renderSemana() {
    const container = document.getElementById('lista-semana');
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

// Lógica para el Mes
let mesActual = new Date();

function cambiarMes(direccion) {
    mesActual.setMonth(mesActual.getMonth() + direccion);
    renderMes();
}

async function renderMes() {
    const container = document.getElementById('grid-mes');
    const titulo = document.getElementById('mes-titulo');
    
    // Configurar fechas del mes
    const primerDiaMes = new Date(mesActual.getFullYear(), mesActual.getMonth(), 1);
    const ultimoDiaMes = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 0);
    
    const mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const mesesCortos = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    titulo.innerText = `${mesesNombres[mesActual.getMonth()]} ${mesActual.getFullYear()}`;

    try {
        const response = await fetch(`${API_BASE}/recolecciones/rango.php?inicio=${formatLocalIso(primerDiaMes)}&fin=${formatLocalIso(ultimoDiaMes)}`);
        const result = await response.json();

        container.innerHTML = '';
        
        // Espacios en blanco para alinear el primer día (Ajuste para que Lunes sea 0)
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

// Función puente para cuando haces clic en la Semana o el Mes
function irADiaDesdeCalendario(fechaIso, titulo) {
    changeDashView('dia');
    
    // Llamar a renderDia quitando la selección visual de los botones "Hoy/Mañana"
    renderDia(fechaIso, titulo, null); 
}

// public/js/app.js
function verDetallesRecoleccion(id) {
    const recoleccion = recoleccionesDelDia.find(r => r.id == id);
    if (!recoleccion) return;

    // Aquí iría la lógica para abrir tu modal (ej: modal.classList.remove('hidden'))
    console.log("Mostrando detalles de:", recoleccion);
    
    // Ejemplo de cómo llenar un modal teórico:
    // document.getElementById('modal-cliente-nombre').innerText = recoleccion.cliente_nombre;
    // document.getElementById('modal-ruta').innerText = recoleccion.ruta_nombre;
    // document.getElementById('modal-recoleccion-estado').innerText = recoleccion.estado_recoleccion;
    
    // Abrir modal
    // document.getElementById('modal-detalles').classList.remove('hidden');
}

// Nueva función que se llama cuando cambias el <select> en el HTML
function recargarDiaActual() {
    // Buscamos cuál es el botón que está activo actualmente para mantener sus estilos
    const btnActivo = document.querySelector('.btn-dia-tab.bg-white');
    renderDia(fechaActualIso, tituloActual, btnActivo);
}

function descargarExcel() {
    if (!recoleccionesDelDia || recoleccionesDelDia.length === 0) {
        alert("No hay datos para exportar en este día o con estos filtros.");
        return;
    }

    // \uFEFF es un BOM que le dice a Excel que el archivo es UTF-8 (evita que las tildes se rompan)
    let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; 
    
    // Cabeceras
    csvContent += "Nombre del Cliente,Teléfono,Sucursal,Estado\n";

    // Filas de datos
    recoleccionesDelDia.forEach(rec => {
        // Envolvemos en comillas por si el nombre tiene comas, para no romper las columnas
        let nombre = `"${rec.cliente_nombre || ''}"`;
        let telefono = `"${rec.telefono_whatsapp || ''}"`;
        let sucursal = `"${rec.sucursal_nombre || 'N/A'}"`;
        let estado = `"${rec.estado_recoleccion || ''}"`.toUpperCase();
        
        csvContent += `${nombre},${telefono},${sucursal},${estado}\n`;
    });

    // Crear un enlace invisible y forzar el clic para descargar
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    // Asignar el nombre del archivo dinámicamente con la fecha
    link.setAttribute("download", `Recolecciones_${fechaActualIso}.csv`);
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// --- CARGAR FILTROS DINÁMICOS ---
async function cargarFiltrosDinamicos() {
    try {
        const response = await fetch(`${API_BASE}/sistema/filtros.php`);
        const result = await response.json();

        if (result.success) {
            // Llenar estados
            const selectEstado = document.getElementById('filtro-estado');
            selectEstado.innerHTML = `<option value="todos">Todos los estados</option>`;
            result.data.estados.forEach(estado => {
                // Capitalizar primera letra para que se vea bonito
                const textoEstado = estado.charAt(0).toUpperCase() + estado.slice(1);
                selectEstado.innerHTML += `<option value="${estado}">${textoEstado}</option>`;
            });

            // Llenar sucursales
            const selectSucursal = document.getElementById('filtro-sucursal');
            selectSucursal.innerHTML = `
                <option value="todas">Todas las sucursales</option>
                <option value="otras">Otras sucursales</option>
            `;
            
            // Solo agregamos al filtro principal las que son destacadas (opcional, puedes ponerlas todas)
            result.data.sucursales.forEach(suc => {
                if(suc.destacada == 1) {
                    selectSucursal.innerHTML += `<option value="${suc.id}">${suc.nombre}</option>`;
                }
            });
        }
    } catch (error) {
        console.error("Error cargando filtros:", error);
    }
}

// Llama a esta función dentro de tu initApp()
function initApp() {
    cargarFiltrosDinamicos();
    setupBotonesDias();
}

function toggleFiltros() {
    const panel = document.getElementById('panel-filtros');
    // Alternamos la clase 'hidden' de Tailwind para mostrar/ocultar el contenedor
    panel.classList.toggle('hidden');
}