<aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200 justify-between shrink-0 z-40 shadow-xs select-none">
    <div class="p-5 flex flex-col gap-6">
        <!-- Logo Header OilBless -->
        <div class="flex items-center pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5 h-10 w-full cursor-pointer" onclick="switchTab('dashboard')">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-sm shadow-emerald-600/30">
                    <span class="material-symbols-outlined text-[22px] filled">water_drop</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-extrabold tracking-tight text-slate-900 leading-none">OilBless</span>
                    <span class="text-[10px] font-semibold text-emerald-700 tracking-wider uppercase mt-1">Gestión Logística</span>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="space-y-5">
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-400 tracking-wider uppercase block mb-2">Menú Principal</span>
                <nav class="space-y-1">
                    <button onclick="switchTab('dashboard')" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold text-sm shadow-sm shadow-emerald-600/20 transition group text-left cursor-pointer" data-target="dashboard">
                        <span class="material-symbols-outlined text-[20px] text-white">dashboard</span>
                        <span class="text-white">Centro de Operaciones</span>
                    </button>
                    
                    <button onclick="switchTab('sucursales-rutas')" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 font-medium text-sm transition group text-left cursor-pointer" data-target="sucursales-rutas">
                        <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-emerald-600 transition">local_shipping</span>
                        <span>Recolecciones &amp; Rutas</span>
                    </button>

                    <button onclick="abrirModalChatwoot(null)" class="nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 font-medium text-sm transition group text-left cursor-pointer relative" data-target="mensajes">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-emerald-600 transition">chat</span>
                            <span>Mensajes</span>
                        </div>
                        <span id="badge-nav-mensajes-sidebar" style="display: none;" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">0</span>
                    </button>

                    <button onclick="switchTab('clientes')" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 font-medium text-sm transition group text-left cursor-pointer" data-target="clientes">
                        <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-emerald-600 transition">storefront</span>
                        <span>Clientes</span>
                    </button>
                </nav>
            </div>

            <div>
                <span class="px-3 text-[10px] font-bold text-slate-400 tracking-wider uppercase block mb-2">Sistema</span>
                <nav class="space-y-1">
                    <button onclick="switchTab('usuarios')" class="nav-btn w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 font-medium text-sm transition group text-left cursor-pointer" data-target="usuarios">
                        <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-emerald-600 transition">tune</span>
                        <span>Configuración</span>
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bottom Status & Logout -->
    <div class="p-4 border-t border-slate-100 bg-slate-50/60 space-y-3">
        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Gateway WhatsApp</span>
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
            </div>
            <p class="text-xs font-bold text-emerald-700 mt-1 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span>
                En línea &amp; Sincronizado
            </p>
        </div>

        <button id="btn-logout" class="w-full flex items-center gap-2.5 px-3 py-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl text-xs font-semibold transition text-left cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">logout</span>
            <span>Cerrar Sesión</span>
        </button>
    </div>
</aside>