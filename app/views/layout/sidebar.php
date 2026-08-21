<aside class="hidden md:flex flex-col w-64 bg-white border-r border-gray-200 shadow-sm z-40">
    <div class="p-6 border-b border-gray-100 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-3xl filled">water_drop</span>
        <h2 class="text-xl font-bold text-charcoal">OilBless</h2>
    </div>
    <nav class="flex-1 p-4 space-y-2">
        <button onclick="switchTab('dashboard')" class="nav-btn w-full flex items-center gap-3 p-3 rounded-xl text-charcoal bg-primary/20 font-bold transition" data-target="dashboard">
            <span class="material-symbols-outlined filled">event</span> Eventos
        </button>
        <button onclick="switchTab('clientes')" class="nav-btn w-full flex items-center gap-3 p-3 rounded-xl text-gray-500 hover:bg-gray-50 font-semibold transition" data-target="clientes">
            <span class="material-symbols-outlined">group</span> Clientes
        </button>
        <button onclick="switchTab('mensajes')" class="nav-btn w-full flex items-center gap-3 p-3 rounded-xl text-gray-500 hover:bg-gray-50 font-semibold transition relative" data-target="mensajes">
            <span class="material-symbols-outlined">forum</span>
            <span>Mensajes</span>
            <span id="badge-nav-mensajes-sidebar" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; border-radius: 50%; display: none; align-items: center; justify-content: center; line-height: 1; padding: 0;" class="ml-auto bg-primary text-charcoal text-[11px] font-black shadow-xs">0</span>
        </button>
        <button onclick="switchTab('usuarios')" class="nav-btn w-full flex items-center gap-3 p-3 rounded-xl text-gray-500 hover:bg-gray-50 font-semibold transition" data-target="usuarios">
            <span class="material-symbols-outlined">settings</span> Configuración
        </button>
    </nav>
    <div class="p-4 border-t border-gray-100">
        <button id="btn-logout" class="w-full flex items-center gap-3 p-3 rounded-xl text-red-500 hover:bg-red-50 font-bold transition">
            <span class="material-symbols-outlined">logout</span> Salir
        </button>
    </div>
</aside>