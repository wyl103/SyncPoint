<main id="main-content" class="mobile-scroll-container flex-1 overflow-y-auto p-4 md:p-8 relative w-full max-w-[1920px] mx-auto pb-28 md:pb-8">
    <style>
    @keyframes blinkConsultaCard {
        0%, 100% {
            background-color: #ffffff;
            border-color: #f59e0b;
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
        }
        50% {
            background-color: #fef3c7;
            border-color: #d97706;
            box-shadow: 0 0 16px 3px rgba(245, 158, 11, 0.5);
        }
    }
    .card-consulta-blink {
        animation: blinkConsultaCard 1.4s infinite ease-in-out !important;
        border-width: 2px !important;
        border-color: #f59e0b !important;
    }
    </style>
    <div id="tab-dashboard" class="space-y-5 max-w-6xl mx-auto pb-28 md:pb-8">
        <!-- Tarjetas KPI Superiores -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- KPI 1: Puntos Confirmados -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Puntos Confirmados</span>
                        <div class="flex items-baseline gap-1.5 mt-1.5">
                            <span id="kpi-confirmados-count" class="text-3xl font-extrabold text-slate-900 tracking-tight">0</span>
                            <span id="kpi-confirmados-total" class="text-xs text-slate-400 font-medium">/ 0 prog.</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">check_circle</span>
                    </div>
                </div>
                <div class="mt-3.5 flex items-center gap-1.5 text-xs text-slate-500">
                    <span id="kpi-confirmados-badge" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        <span id="kpi-confirmados-pct">0.0%</span>
                    </span>
                    <span class="truncate">Recolecciones aseguradas</span>
                </div>
            </div>

            <!-- KPI 2: Denegados / Cancelados (Liberados) -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Denegados / Cancelados</span>
                        <div class="flex items-baseline gap-1.5 mt-1.5">
                            <span id="kpi-denegados-count" class="text-3xl font-extrabold text-slate-900 tracking-tight">0</span>
                            <span class="text-xs text-slate-400 font-medium">liberados</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">free_cancellation</span>
                    </div>
                </div>
                <div class="mt-3.5 flex items-center gap-1.5 text-xs text-slate-500">
                    <span id="kpi-denegados-badge" class="inline-flex items-center px-1.5 py-0.5 rounded font-bold bg-amber-50 text-amber-700 border border-amber-200 text-[10px]">
                        0 Paradas
                    </span>
                    <span class="truncate">Cupos no requeridos</span>
                </div>
            </div>

            <!-- KPI 3: Requieren Atención (Chat Manual) -->
            <div class="bg-amber-50/40 rounded-2xl p-4 border border-amber-200 shadow-xs flex flex-col justify-between hover:border-amber-300 transition ring-1 ring-amber-100">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block">Requieren Atención</span>
                        <div class="flex items-baseline gap-1.5 mt-1.5">
                            <span id="kpi-atencion-count" class="text-3xl font-extrabold text-amber-600 tracking-tight">0</span>
                            <span class="text-xs text-amber-600/80 font-medium">en revisión</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">support_agent</span>
                    </div>
                </div>
                <div class="mt-3.5 flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded font-bold bg-amber-100 text-amber-800 text-[10px]">
                        <span class="material-symbols-outlined text-[12px]">chat</span>
                        Chat Manual
                    </span>
                    <button type="button" onclick="filtrarRequierenAtencion()" class="text-amber-700 hover:text-amber-900 font-bold text-[11px] flex items-center gap-0.5 transition cursor-pointer">
                        <span>Ver lista</span>
                        <span class="material-symbols-outlined text-[13px]">arrow_forward</span>
                    </button>
                </div>
            </div>

            <!-- KPI 4: Automatización WhatsApp -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">ENVÍO MENSAJES</span>
                        <div class="flex items-baseline gap-1.5 mt-1.5">
                            <span id="kpi-wa-hora" class="text-3xl font-extrabold text-slate-900 tracking-tight">Activo</span>
                            <span class="text-xs text-emerald-600 font-bold">Auto</span>
                        </div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">mark_chat_read</span>
                    </div>
                </div>
                <div class="mt-3.5 flex items-center justify-between text-xs text-slate-500">
                    <span id="kpi-wa-info" class="truncate">Programadas hoy: 0</span>
                    <button onclick="recargarDiaActual()" class="text-slate-400 hover:text-emerald-700 p-0.5 rounded transition" title="Refrescar datos">
                        <span class="material-symbols-outlined text-[16px]">refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Selector de Rutas & Navegador de Días -->
        <div class="w-full bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-5">
            <!-- Selector de Rutas con Filtros y Botón Programar -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtro de Sucursal (Select con Búsqueda Integrada) -->
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sucursal:</span>
                        <div class="relative inline-flex items-center" id="contenedor-filtro-sucursal-dropdown">
                            <!-- Botón disparador del Dropdown Select -->
                            <button type="button" onclick="toggleDropdownFiltroSucursal(event)" id="btn-filtro-sucursal-select" class="pl-10 pr-8 py-1.5 bg-slate-50 hover:bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200 shadow-2xs focus:ring-2 focus:ring-emerald-500 focus:outline-none focus:bg-white transition flex items-center justify-between gap-2 min-w-[190px] sm:min-w-[210px] cursor-pointer text-left">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-600">
                                    <span class="material-symbols-outlined text-[18px]">domain</span>
                                </div>
                                <span id="label-filtro-sucursal-actual" class="truncate text-slate-800">Todas las sucursales</span>
                                <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                    <span class="material-symbols-outlined text-[16px]">unfold_more</span>
                                </div>
                            </button>

                            <!-- Menú flotante del Select con input de búsqueda y opciones filtrables -->
                            <div id="menu-filtro-sucursal-opciones" class="hidden absolute top-full left-0 mt-1.5 z-40 w-64 bg-white rounded-2xl border border-slate-200 shadow-xl p-2 flex flex-col gap-1 animate-in fade-in zoom-in-95 duration-150">
                                <!-- Input buscador dentro del select -->
                                <div class="relative flex items-center mb-1">
                                    <span class="material-symbols-outlined absolute left-2.5 text-slate-400 text-[16px] pointer-events-none">search</span>
                                    <input type="text" id="input-buscar-sucursal-en-select" oninput="alFiltrarOpcionesSucursales(this.value)" placeholder="Escribe para filtrar sucursales..." autocomplete="off" class="w-full pl-8 pr-7 py-1.5 bg-slate-50 focus:bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <button type="button" onclick="limpiarTextoBuscarSucursal()" id="btn-limpiar-busqueda-sucursal" class="hidden absolute right-2 text-slate-400 hover:text-slate-600 cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                    </button>
                                </div>

                                <!-- Lista de opciones clickeables -->
                                <div id="lista-opciones-sucursales" class="max-h-52 overflow-y-auto space-y-0.5 scrollbar-thin text-xs">
                                    <!-- Generado dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

                    <!-- Selector de Estado WA -->
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estado WA:</span>
                        <div class="relative inline-flex items-center">
                            <select id="filtro-estado-wa" onchange="alCambiarFiltroEstadoWa()" class="px-3 pr-7 py-1.5 bg-slate-50 hover:bg-white text-slate-800 text-xs font-semibold rounded-xl border border-slate-200 shadow-2xs focus:ring-2 focus:ring-emerald-500 focus:outline-none focus:bg-white transition cursor-pointer appearance-none">
                                <option value="all">Todos los estados</option>
                                <option value="conf">Confirmados / Aceptados</option>
                                <option value="wait">En espera / Notificados</option>
                                <option value="rej">Rechazados / Cancelados</option>
                                <option value="tent">Tentativas</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[16px]">unfold_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción (Programar Recolección y Exportar Excel) -->
                <div class="flex items-center gap-2">
                    <button onclick="descargarExcel()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs cursor-pointer" title="Exportar CSV/Excel">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">download</span>
                        <span>Excel</span>
                    </button>
                    <button onclick="abrirModalProgramarRecoleccion()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                        <span>Programar</span>
                    </button>
                </div>
            </div>

            <!-- Navegador de Días (Selector de Mes Compacto + Tira D-0 a D+3) -->
            <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 pt-1">
                <!-- Selector de Mes / Fecha Compacto con Popover de Calendario -->
                <div class="relative flex items-center gap-1.5 flex-shrink-0">
                    <button onclick="cambiarDiaRelativo(-1)" class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition shadow-2xs cursor-pointer" title="Día anterior">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                    </button>
                    <div id="selector-mes-compacto" onclick="togglePopoverCalendario()" class="px-3 py-1 bg-slate-50 hover:bg-white border border-slate-200 hover:border-emerald-300 rounded-lg text-center flex items-center gap-1.5 shadow-2xs cursor-pointer transition select-none group" title="Abrir filtro de calendario">
                        <span class="material-symbols-outlined text-[14px] text-slate-400 group-hover:text-emerald-600 transition">calendar_month</span>
                        <span id="ticker-mes-label" class="text-[11px] font-bold text-slate-800 group-hover:text-emerald-900 transition">Cargando...</span>
                    </div>
                    <button onclick="cambiarDiaRelativo(1)" class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition shadow-2xs cursor-pointer" title="Día siguiente">
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </button>

                    <!-- Popover Filtro Calendario (diseño NewView/filtroCalendario) -->
                    <div id="datepickerPopover" class="hidden absolute top-10 left-0 z-40 w-[380px] sm:w-[420px] bg-white rounded-2xl border border-slate-200 shadow-2xl p-4 transition-all">
                        <!-- Cabecera del Popover de Calendario -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs border border-emerald-100">
                                    <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                                </div>
                                <div>
                                    <h4 id="cal-popover-mes-titulo" class="text-xs font-bold text-slate-900 leading-tight">Cargando...</h4>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="irAHoyCalendarioPopover()" class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-[10px] font-bold text-slate-700 transition cursor-pointer">
                                    Hoy
                                </button>
                                <button type="button" onclick="navegarMesCalendarioPopover(-1)" class="w-6 h-6 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition cursor-pointer" title="Mes anterior">
                                    <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                                </button>
                                <button type="button" onclick="navegarMesCalendarioPopover(1)" class="w-6 h-6 rounded-lg border border-slate-200 hover:bg-slate-50 flex items-center justify-center text-slate-600 transition cursor-pointer" title="Mes siguiente">
                                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                                </button>
                            </div>
                        </div>

                        <!-- Encabezado Días de la Semana -->
                        <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 my-2 uppercase tracking-wider">
                            <div>Lun</div>
                            <div>Mar</div>
                            <div>Mié</div>
                            <div>Jue</div>
                            <div>Vie</div>
                            <div>Sáb</div>
                            <div>Dom</div>
                        </div>

                        <!-- Grid de Días del Mes con conteo de puntos -->
                        <div id="cal-popover-grid-dias" class="grid grid-cols-7 gap-1 text-xs">
                            <!-- Se renderiza dinámicamente con renderGridCalendarioPopover() -->
                        </div>

                        <!-- Pie del Popover -->
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <button type="button" onclick="cerrarPopoverCalendario()" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 font-semibold text-xs transition cursor-pointer">
                                Cerrar
                            </button>
                            <span id="cal-popover-seleccion-info" class="text-[11px] font-medium text-slate-500 truncate">
                                Selecciona un día
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Carrusel / Tira de Días (D-0 a D+3) -->
                <div id="tira-dias-container" class="grid grid-cols-2 sm:grid-cols-4 gap-2 flex-1 min-w-0">
                    <!-- Se llena dinámicamente con setupTiraDias() -->
                </div>
            </div>
        </div>

        <!-- Encabezado de la Vista de Paradas / Sucursales -->
        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <div class="flex items-center gap-2 text-xs text-slate-600">
                <span class="material-symbols-outlined text-[16px] text-emerald-600">domain</span>
                <span id="rutas-resumen-texto" class="font-semibold text-slate-700">Cargando sucursales...</span>
                <span class="text-slate-300">•</span>
                <span id="paradas-resumen-texto" class="text-slate-500 font-medium">0 paradas</span>
            </div>
            <div class="flex items-center bg-white border border-slate-200 shadow-2xs rounded-xl p-1 gap-1">
                <button class="px-3 py-1 rounded-lg bg-white border border-slate-200 shadow-2xs font-bold text-xs text-emerald-700 transition flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">table_rows</span>
                    <span>Tabla Detallada</span>
                </button>
            </div>
        </div>

        <!-- Contenedor de Bloques de Rutas en Tabla -->
        <div id="contenedor-rutas-eventos" class="space-y-6">
            <div class="p-12 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
                <p class="text-xs text-slate-400 font-semibold mt-3">Cargando eventos de recolección...</p>
            </div>
        </div>
    </div>

    <div id="tab-clientes" class="hidden-view space-y-6 max-w-4xl mx-auto pb-28 md:pb-8">
        <!-- SUB-PESTAÑA 1: DIRECTORIO DE CLIENTES -->
        <div id="subtab-directorio-clientes" class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-charcoal flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-3xl">group</span>
                        Clientes
                    </h2>
                    <p class="text-xs text-gray-500 font-semibold mt-1">Gestión de clientes registrados</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white border-0 px-4 py-2.5 rounded-xl text-charcoal flex items-center gap-2 shadow-2xs">
                        <span class="material-symbols-outlined text-primary">person_search</span>
                        <span class="text-xs font-bold">Total: <span id="total-clientes-badge" class="text-sm font-extrabold text-charcoal">0</span></span>
                    </div>
                </div>
            </div>

            <!-- Barra de Búsqueda y Filtros de Clientes (Horizontal y Compacta) -->
            <div class="client-filters-container">
                <!-- Input de Búsqueda Principal -->
                <div class="client-search-box">
                    <span class="material-symbols-outlined client-search-icon">search</span>
                    <input id="input-buscar-cliente" type="text" oninput="filtrarClientesDebounced()" placeholder="Buscar cliente por nombre, teléfono o ID..." class="client-search-input">
                </div>

                <!-- Selector de Filtros Horizontales (4 columnas en PC) -->
                <div class="client-filters-grid">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px] text-gray-400">store</span> Sucursal
                        </label>
                        <select id="filtro-cliente-sucursal" onchange="alCambiarSucursalCliente()" class="client-select-control">
                            <option value="todas">Todas las sucursales</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px] text-primary">route</span> Ruta / Zona
                        </label>
                        <select id="filtro-cliente-ruta" onchange="cargarClientes(1)" class="client-select-control">
                            <option value="todas">Todas las rutas</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px] text-gray-400">tune</span> Estado
                        </label>
                        <select id="filtro-cliente-estado" onchange="cargarClientes(1)" class="client-select-control">
                            <option value="todos">Todos los estados</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px] text-gray-400">format_list_numbered</span> Mostrar
                        </label>
                        <select id="filtro-cliente-limit" onchange="cargarClientes(1)" class="client-select-control">
                            <option value="10">10 por pág.</option>
                            <option value="20">20 por pág.</option>
                            <option value="50">50 por pág.</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botón Crear Nuevo Cliente (Ubicado Debajo del Cuadro de Filtros) -->
            <div class="flex justify-end pt-1">
                <button onclick="abrirModalCrearCliente()" class="btn-primary-main">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    <span>Nuevo Cliente</span>
                </button>
            </div>

            <!-- Lista de Clientes -->
            <div id="lista-clientes" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>

            <!-- Paginación de Clientes -->
            <div id="paginacion-clientes-container" class="hidden mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <p id="paginacion-info-texto" class="text-xs font-semibold text-gray-500 text-center sm:text-left">
                    Mostrando <span id="paginacion-rango-inicio" class="font-bold text-charcoal">0</span> a <span id="paginacion-rango-fin" class="font-bold text-charcoal">0</span> de <span id="paginacion-total-registros" class="font-bold text-charcoal">0</span> clientes
                </p>

                <div class="flex items-center gap-2">
                    <button id="btn-pagina-prev" onclick="cambiarPaginaCliente(-1)" class="flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span> Anterior
                    </button>

                    <span class="text-xs font-bold text-gray-600 px-2">
                        Pág. <span id="paginacion-pagina-actual">1</span> de <span id="paginacion-total-paginas">1</span>
                    </span>

                    <button id="btn-pagina-next" onclick="cambiarPaginaCliente(1)" class="flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        Siguiente <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- SUB-PESTAÑA 2: ADMINISTRACIÓN DE SUCURSALES Y RUTAS -->
        <div id="subtab-sucursales-rutas" class="hidden-view space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-charcoal flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-3xl">alt_route</span>
                        Administración de Sucursales y Rutas
                    </h2>
                    <p class="text-xs text-gray-500 font-semibold mt-1">Gestiona la estructura operativa, asigna rutas y supervisa la capacidad logística.</p>
                </div>
                <div>
                    <button onclick="abrirModalSucursalRapida()" class="btn-primary-main">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Nueva Sucursal</span>
                    </button>
                </div>
            </div>

            <!-- Barra de Filtros de Sucursales y Rutas (Búsqueda, Sucursal, Ruta) -->
            <div class="client-filters-container space-y-4">
                <!-- Input de Búsqueda Principal -->
                <div class="client-search-box">
                    <span class="material-symbols-outlined client-search-icon">search</span>
                    <input id="input-buscar-sucursal-ruta" type="text" oninput="filtrarSucursalesYRutasDebounced()" placeholder="Buscar por sucursal, ruta o ciudad..." class="client-search-input">
                </div>

                <!-- Selector de Filtros (Sucursal y Ruta) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-gray-400">store</span> Filtrar por Sucursal
                        </label>
                        <select id="filtro-sucursal-select" onchange="filtrarSucursalesYRutas()" class="client-select-control">
                            <option value="todas">Todas las sucursales</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-primary">route</span> Filtrar por Ruta / Zona
                        </label>
                        <select id="filtro-ruta-select" onchange="filtrarSucursalesYRutas()" class="client-select-control">
                            <option value="todas">Todas las rutas</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Lista Dinámica de Sucursales y sus Rutas (Acordeón) -->
            <div id="lista-sucursales-rutas" class="space-y-4">
                <!-- Renderizado por JS sucursales_rutas.js -->
            </div>
        </div>
    </div>

    <!-- ==================== PESTAÑA USUARIOS ==================== -->
    <div id="tab-usuarios" class="hidden-view space-y-6 max-w-4xl mx-auto pb-28 md:pb-8">
        
        <!-- SUBTAB 1: Directorio de Usuarios -->
        <div id="subtab-directorio-usuarios" class="space-y-4">
            <!-- Barra de Búsqueda -->
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
                <input id="input-buscar-usuario" type="text" oninput="filtrarUsuariosDebounced()" placeholder="Buscar usuario por nombre o correo..." class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary shadow-xs">
            </div>

            <!-- Botón Nuevo Usuario debajo de la búsqueda -->
            <button onclick="abrirModalCrearUsuario()" class="w-full bg-white border border-gray-200 text-charcoal p-3 rounded-xl font-bold shadow-xs hover:bg-gray-50 flex justify-center items-center gap-2 transition">
                <span class="material-symbols-outlined text-primary">person_add</span> Nuevo Usuario
            </button>

            <!-- Lista de Usuarios -->
            <div id="lista-usuarios" class="grid gap-3">
                <!-- Se renderiza mediante JS usuarios.js -->
            </div>

            <!-- Paginación de Usuarios -->
            <div id="paginacion-usuarios" class="flex justify-between items-center bg-white border border-gray-200 p-3 rounded-xl shadow-xs">
                <!-- Renderizado por JS usuarios.js -->
            </div>
        </div>

        <!-- SUBTAB 2: Programación -->
        <div id="subtab-programacion-usuarios" class="hidden-view space-y-6">
            <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-xs space-y-6">
                <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                    <div class="p-3 bg-primary/20 text-charcoal rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-2xl">event_repeat</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-charcoal">Programación Automática de Eventos</h2>
                        <p class="text-xs text-gray-500">Calcula la fecha agendada más lejana en el sistema y genera los eventos faltantes exclusivamente para clientes con fecha base asignada y el día de su ruta.</p>
                    </div>
                </div>

                <!-- Configuración de Ajuste por Día de Ruta -->
                <div class="bg-amber-50/60 border border-amber-200 p-5 rounded-2xl flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600 text-xl">route</span>
                            <h3 class="text-sm font-bold text-gray-800">Ajustar eventos al día de la Ruta</h3>
                        </div>
                        <p class="text-xs text-gray-600">Al activar esta opción, las fechas de recolección tentativas se proyectarán teniendo en cuenta el día de la semana configurado en la Ruta del cliente (Lunes, Martes, Miércoles, etc.).</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" id="toggle-usar-dia-ruta" onchange="guardarConfiguracionProgramacion(this.checked)" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700">Días de Proyección (Horizonte)</label>
                            <span class="text-xs text-gray-500">Días adicionales a programar a partir de la fecha más lejana existente</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input id="input-dias-horizonte" type="number" min="1" max="365" value="30" class="w-24 border border-gray-300 rounded-xl p-2.5 text-center text-sm font-bold bg-white focus:outline-none focus:border-primary">
                            <span class="text-xs font-bold text-gray-600">Días</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-xs text-gray-500 font-medium">Ajusta automáticamente las fechas de recolección al día de la semana configurado en la Ruta de cada cliente.</p>
                        <button onclick="ejecutarProgramacionGlobalEventos()" id="btn-programar-eventos-global" class="w-full sm:w-auto bg-primary hover:bg-yellow-400 text-charcoal font-extrabold px-6 py-3 rounded-xl text-sm flex items-center justify-center gap-2 shadow-xs transition shrink-0 cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">calendar_month</span> Programar Eventos
                        </button>
                    </div>
                </div>

                <!-- Resultado de la Ejecución de Programación Automática -->
                <div id="resultado-programacion-global" class="hidden space-y-3">
                    <!-- Se llena mediante JS -->
                </div>
            </div>

            <!-- CARD 2: Envío Masivo de Notificaciones por WhatsApp -->
            <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-xs space-y-6">
                <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                    <div class="p-3 bg-primary/20 text-charcoal rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-2xl">forward_to_inbox</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-charcoal">Envío Masivo de Notificaciones por WhatsApp</h2>
                        <p class="text-xs text-gray-500">Envía plantillas oficiales de WhatsApp a los clientes con eventos en las fechas y estados seleccionados.</p>
                    </div>
                </div>

                <!-- Configuración de Filtros de Envío -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. Rango de Fechas -->
                    <div class="bg-gray-50/80 border border-gray-200 p-4 rounded-xl space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-charcoal flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px] text-gray-500">date_range</span>
                                Fechas de los Eventos
                            </label>
                            <div class="flex items-center gap-1 text-[11px] font-bold">
                                <button type="button" onclick="establecerRangoFechasMasivo('hoy')" class="px-2 py-0.5 rounded bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 transition cursor-pointer">Hoy</button>
                                <button type="button" onclick="establecerRangoFechasMasivo('manana')" class="px-2 py-0.5 rounded bg-white hover:bg-gray-100 border border-gray-200 text-gray-600 transition cursor-pointer">Mañana</button>
                                <button type="button" onclick="establecerRangoFechasMasivo('proximos3')" class="px-2 py-0.5 rounded bg-primary/20 hover:bg-primary/30 border border-primary/40 text-charcoal transition cursor-pointer">3 Días</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="block text-[11px] font-bold text-gray-500 mb-1">Desde:</span>
                                <input id="masivo-fecha-desde" type="date" onchange="consultarDestinatariosMasivos()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold text-charcoal outline-none focus:border-primary">
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-500 mb-1">Hasta:</span>
                                <input id="masivo-fecha-hasta" type="date" onchange="consultarDestinatariosMasivos()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs font-bold text-charcoal outline-none focus:border-primary">
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-tight">Para notificar un solo día, selecciona la misma fecha en 'Desde' y 'Hasta'.</p>
                    </div>

                    <!-- 2. Plantilla de WhatsApp -->
                    <div class="bg-gray-50/80 border border-gray-200 p-4 rounded-xl space-y-3">
                        <label class="text-xs font-bold text-charcoal flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-gray-500">chat</span>
                            Plantilla de WhatsApp
                        </label>
                        <select id="select-plantilla-masiva" onchange="actualizarVistaPreviaPlantillaMasiva()" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-bold text-charcoal outline-none focus:border-primary">
                            <option value="confirmacion_entrega">Confirmación de Recolección (confirmacion_entrega)</option>
                            <option value="hola_oilbless">Bienvenida OilBless (hola_oilbless)</option>
                            <option value="hello_world">Prueba Rápida (hello_world)</option>
                        </select>
                        <div id="preview-plantilla-masiva-box" class="p-3 bg-white border border-gray-200 rounded-xl text-xs text-gray-600 space-y-1">
                            <div class="font-bold text-charcoal text-[11px] flex items-center gap-1">
                                <span class="material-symbols-outlined text-primary text-[16px]">visibility</span>
                                Vista previa aproximada:
                            </div>
                            <p id="preview-plantilla-masiva-texto" class="text-[11.5px] italic text-gray-700 leading-relaxed bg-amber-50/50 p-2 rounded-lg border border-amber-100">
                                "Hola <strong>[Nombre Cliente]</strong>, te escribimos de OilBless para confirmar tu servicio del día <strong>[Fecha Programada]</strong>. ¿Podemos pasar a retirar el aceite?"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 3. Selección de Estados de Eventos -->
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="text-xs font-bold text-charcoal flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-gray-500">checklist</span>
                            Estados de los Eventos a Incluir
                        </label>
                        <div class="flex items-center gap-2 text-xs font-bold">
                            <button type="button" onclick="marcarTodosEstadosMasivos(true)" class="text-primary hover:underline cursor-pointer">Seleccionar todos</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" onclick="marcarTodosEstadosMasivos(false)" class="text-gray-500 hover:underline cursor-pointer">Desmarcar todos</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                        <label class="flex items-center gap-2 p-2.5 bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl cursor-pointer transition select-none">
                            <input type="checkbox" name="chk-estado-masivo" value="programado" checked onchange="consultarDestinatariosMasivos()" class="rounded text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-bold text-charcoal">Programado</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl cursor-pointer transition select-none">
                            <input type="checkbox" name="chk-estado-masivo" value="notificacion1" onchange="consultarDestinatariosMasivos()" class="rounded text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-bold text-charcoal">Notificación 1</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl cursor-pointer transition select-none">
                            <input type="checkbox" name="chk-estado-masivo" value="notificacion2" onchange="consultarDestinatariosMasivos()" class="rounded text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-bold text-charcoal">Notificación 2</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl cursor-pointer transition select-none">
                            <input type="checkbox" name="chk-estado-masivo" value="notificacion3" onchange="consultarDestinatariosMasivos()" class="rounded text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-bold text-charcoal">Notificación 3</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl cursor-pointer transition select-none">
                            <input type="checkbox" name="chk-estado-masivo" value="rechazado" onchange="consultarDestinatariosMasivos()" class="rounded text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-bold text-charcoal">Rechazado</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-xl cursor-pointer transition select-none">
                            <input type="checkbox" name="chk-estado-masivo" value="aceptado" onchange="consultarDestinatariosMasivos()" class="rounded text-primary focus:ring-primary w-4 h-4">
                            <span class="text-xs font-bold text-charcoal">Aceptado</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Resumen de Destinatarios y Previsualización -->
                <div class="bg-amber-50/50 border border-amber-200/80 p-4 rounded-xl space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary text-charcoal flex items-center justify-center font-black text-sm shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-[20px]">groups</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Total Destinatarios Encontrados</span>
                                <div id="resumen-conteo-destinatarios" class="text-base font-extrabold text-charcoal">
                                    <span id="conteo-destinatarios-numero" class="text-lg text-emerald-700">0</span> clientes listos para recibir notificación
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="alternarListaPreviewDestinatarios()" id="btn-toggle-preview-destinatarios" class="px-3 py-2 bg-white hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-bold text-charcoal flex items-center gap-1.5 transition cursor-pointer shadow-2xs">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                <span id="texto-toggle-preview">Ver detalles</span>
                            </button>
                            <button type="button" onclick="consultarDestinatariosMasivos()" class="p-2 bg-white hover:bg-gray-100 border border-gray-200 rounded-xl text-gray-600 transition cursor-pointer shadow-2xs" title="Actualizar conteo">
                                <span class="material-symbols-outlined text-[18px]">refresh</span>
                            </button>
                        </div>
                    </div>

                    <!-- Lista Desplegable de Destinatarios -->
                    <div id="contenedor-tabla-preview-destinatarios" class="hidden pt-3 border-t border-amber-200/60">
                        <div class="max-h-56 overflow-y-auto rounded-xl border border-gray-200 bg-white">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 text-gray-500 font-bold border-b border-gray-200 sticky top-0">
                                    <tr>
                                        <th class="p-2.5">Cliente</th>
                                        <th class="p-2.5">Teléfono</th>
                                        <th class="p-2.5">Fecha Evento</th>
                                        <th class="p-2.5">Estado</th>
                                        <th class="p-2.5">Ruta / Sucursal</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-destinatarios-masivos" class="divide-y divide-gray-100">
                                    <!-- Se llena con JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. Botón de Ejecución -->
                <div class="pt-2 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-gray-500 font-medium">Al enviar, los mensajes saldrán a través del WhatsApp oficial de Chatwoot y los eventos pasarán al siguiente estado.</p>
                    <button onclick="confirmarYEjecutarEnvioMasivo()" id="btn-ejecutar-envio-masivo" class="w-full sm:w-auto bg-primary hover:bg-yellow-400 text-charcoal font-extrabold px-6 py-3 rounded-xl text-sm flex items-center justify-center gap-2 shadow-xs transition shrink-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-[20px]">send</span>
                        <span id="texto-boton-envio-masivo">Enviar Notificaciones</span>
                    </button>
                </div>

                <!-- 6. Contenedor de Resultados del Envío Masivo -->
                <div id="resultado-envio-masivo" class="hidden space-y-3">
                    <!-- Se llena mediante JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PESTAÑA: MENSAJES / CHATS WHATSAPP (CHATWOOT) -->
    <!-- ========================================== -->
    <div id="tab-mensajes" class="hidden-view space-y-4 max-w-5xl mx-auto pb-28 md:pb-8">
        <!-- Cabecera y Barra de Búsqueda -->
        <div class="bg-white border border-gray-200 p-5 sm:p-6 rounded-2xl shadow-xs space-y-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 bg-primary/20 text-charcoal rounded-2xl flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-2xl">forum</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-charcoal leading-tight">Chats de WhatsApp</h2>
                    <p class="text-xs text-gray-500 font-medium">Bandeja de conversaciones sincronizadas con Chatwoot</p>
                </div>
            </div>

            <!-- Fila: Buscador (sin lupa, con padding generoso) + Botón Actualizar a la derecha -->
            <div class="flex items-center gap-3 w-full">
                <div class="flex-1 min-w-0">
                    <input id="input-buscar-conversacion" type="text" oninput="alBuscarConversacion()" placeholder="Buscar conversación por nombre de cliente, teléfono, sucursal o mensaje..." style="height: 48px; padding-left: 20px; padding-right: 20px;" class="w-full bg-gray-50/80 border border-gray-200 rounded-xl text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400 placeholder:font-normal shadow-2xs">
                </div>
                <button onclick="recargarListaConversaciones()" style="height: 48px; padding-left: 20px; padding-right: 20px;" class="flex items-center justify-center gap-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl text-xs font-bold text-charcoal transition shadow-2xs cursor-pointer shrink-0" title="Actualizar lista">
                    <span class="material-symbols-outlined text-[20px]">refresh</span>
                    <span>Actualizar</span>
                </button>
            </div>
        </div>

        <!-- Contenedor Lista de Chats -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden">
            <div id="lista-conversaciones-chatwoot" class="divide-y divide-gray-100">
                <!-- Se llena mediante JS mensajes.js -->
            </div>
        </div>

        <!-- Botón Ver Más Chats -->
        <div id="contenedor-ver-mas-chats" class="text-center pt-2">
            <button id="btn-ver-mas-chats" onclick="cargarMasChats()" class="btn-secondary-main inline-flex items-center gap-2 text-xs font-bold py-2.5 px-6 shadow-xs">
                <span class="material-symbols-outlined text-[18px]">expand_more</span>
                <span>Ver más chats</span>
            </button>
        </div>
    </div>
</main>