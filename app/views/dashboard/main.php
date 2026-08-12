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

    <div id="tab-clientes" class="hidden-view space-y-6 max-w-4xl mx-auto pb-20 md:pb-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-charcoal flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">group</span>
                    Clientes
                </h2>
                <p class="text-xs text-gray-500 font-semibold mt-1">Directorio y gestión de clientes registrados</p>
            </div>
            <div class="bg-primary/10 border border-primary/30 px-4 py-2 rounded-xl text-charcoal flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person_search</span>
                <span class="text-xs font-bold">Total: <span id="total-clientes-badge" class="text-sm font-extrabold text-charcoal">0</span></span>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros de Clientes -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                <input id="input-buscar-cliente" type="text" oninput="filtrarClientesDebounced()" placeholder="Buscar cliente por nombre o número de WhatsApp..." class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Sucursal / Zona</label>
                    <select id="filtro-cliente-sucursal" onchange="cargarClientes()" class="w-full p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 outline-none focus:border-primary focus:bg-white transition">
                        <option value="todas">Todas las sucursales</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Estado</label>
                    <select id="filtro-cliente-estado" onchange="cargarClientes()" class="w-full p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 outline-none focus:border-primary focus:bg-white transition">
                        <option value="todos">Todos los estados</option>
                        <option value="agendado">Agendado</option>
                        <option value="no agendado">No Agendado</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lista de Clientes -->
        <div id="lista-clientes" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
    </div>
</main>