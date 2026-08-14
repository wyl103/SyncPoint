<main id="main-content" class="flex-1 overflow-y-auto p-4 md:p-8 relative">
    <div id="tab-dashboard" class="space-y-6 max-w-4xl mx-auto pb-20 md:pb-0">
        <div class="flex h-12 items-center rounded-xl bg-gray-200 p-1 mb-4">
            <label id="lbl-dia" onclick="changeDashView('dia')" class="flex h-full flex-1 cursor-pointer items-center justify-center rounded-lg text-sm font-bold bg-white shadow-sm text-charcoal transition-all">Día</label>
            <label id="lbl-semana" onclick="changeDashView('semana')" class="flex h-full flex-1 cursor-pointer items-center justify-center rounded-lg text-sm font-semibold text-gray-500 transition-all">Semana</label>
            <label id="lbl-mes" onclick="changeDashView('mes')" class="flex h-full flex-1 cursor-pointer items-center justify-center rounded-lg text-sm font-semibold text-gray-500 transition-all">Mes</label>
        </div>
        
        <button onclick="openModal('modal-add-client')" class="w-full bg-primary text-charcoal p-3 rounded-xl font-bold shadow-sm hover:bg-yellow-400 flex justify-center items-center gap-2 transition">
            <span class="material-symbols-outlined">add_circle</span> Programar Recolección
        </button>

        <div id="dash-dia">
            <div class="flex justify-between items-end mb-4">
                <div><h2 class="text-2xl font-bold text-charcoal" id="dia-titulo">Cargando...</h2></div>
                <div class="flex gap-1 bg-gray-200 p-1 rounded-lg" id="botones-dias-rapidos">
                </div>
            </div>

            <div class="mb-4 flex justify-between items-center gap-2">
                <button onclick="toggleFiltros()" class="flex-1 md:flex-none flex items-center justify-center gap-1 bg-white border border-gray-200 text-charcoal px-4 py-2 rounded-xl font-bold text-sm shadow-sm hover:bg-gray-50 transition">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span> Filtros
                </button>

                <button onclick="descargarExcel()" class="flex-1 md:flex-none flex items-center justify-center gap-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition">
                    <span class="material-symbols-outlined text-[18px]">download</span> Excel
                </button>
            </div>

            <div id="panel-filtros" class="hidden mb-6 bg-white p-5 rounded-xl border border-gray-200 shadow-sm transition-all">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">tune</span> Opciones de filtrado
                </h3>
                
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Sucursal</label>
                        <select id="filtro-sucursal" onchange="recargarDiaActual()" class="w-full p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 outline-none focus:border-primary focus:bg-white transition">
                            <option value="todas">Cargando...</option>
                        </select>
                    </div>
                    
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Estado</label>
                        <select id="filtro-estado" onchange="recargarDiaActual()" class="w-full p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 outline-none focus:border-primary focus:bg-white transition">
                            <option value="todos">Cargando...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="lista-dia" class="space-y-3"></div> 
        </div>

        <div id="dash-semana" class="hidden-view space-y-3">
            <h2 class="text-xl font-bold mb-4 text-charcoal">Próximos 7 días</h2>
            <div id="lista-semana" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
        </div>

        <div id="dash-mes" class="hidden-view">
            <div class="flex justify-between items-center mb-4">
                <button onclick="cambiarMes(-1)" class="p-2 bg-white rounded-full shadow-sm"><span class="material-symbols-outlined text-charcoal">chevron_left</span></button>
                <h2 id="mes-titulo" class="text-xl font-bold text-charcoal">Cargando...</h2>
                <button onclick="cambiarMes(1)" class="p-2 bg-white rounded-full shadow-sm"><span class="material-symbols-outlined text-charcoal">chevron_right</span></button>
            </div>
            <div class="grid grid-cols-7 gap-1 md:gap-2 mb-2 text-center text-xs font-bold text-gray-400">
                <div>LUN</div><div>MAR</div><div>MIE</div><div>JUE</div><div>VIE</div><div>SAB</div><div>DOM</div>
            </div>
            <div class="grid grid-cols-7 gap-1 md:gap-2" id="grid-mes"></div>
        </div>
    </div>

    <div id="tab-clientes" class="hidden-view space-y-6 max-w-5xl mx-auto pb-20 md:pb-0">
        <!-- Sub-Navegación Superior del Módulo Clientes -->
        <div class="flex items-center gap-2 border-b border-gray-200/80 pb-3">
            <button onclick="cambiarSubTabCliente('directorio')" id="subtab-btn-directorio" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition cursor-pointer bg-primary/20 text-charcoal border border-primary/30 shadow-2xs">
                <span class="material-symbols-outlined text-[18px]">group</span> Clientes
            </button>
            <button onclick="cambiarSubTabCliente('sucursales-rutas')" id="subtab-btn-sucursales-rutas" class="px-4 py-2 rounded-xl font-semibold text-xs text-gray-500 hover:text-charcoal hover:bg-gray-100 flex items-center gap-2 transition cursor-pointer border border-transparent">
                <span class="material-symbols-outlined text-[18px]">alt_route</span> Sucursales y Rutas
            </button>
        </div>

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
                    <div class="bg-primary/10 border border-primary/30 px-3.5 py-2 rounded-xl text-charcoal flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">person_search</span>
                        <span class="text-xs font-bold">Total: <span id="total-clientes-badge" class="text-sm font-extrabold text-charcoal">0</span></span>
                    </div>
                    <button onclick="abrirModalCrearCliente()" class="btn-primary-main">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        <span>Nuevo Cliente</span>
                    </button>
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

            <!-- Lista Dinámica de Sucursales y sus Rutas (Acordeón) -->
            <div id="lista-sucursales-rutas" class="space-y-4">
                <!-- Renderizado por JS sucursales_rutas.js -->
            </div>
        </div>
    </div>

</main>