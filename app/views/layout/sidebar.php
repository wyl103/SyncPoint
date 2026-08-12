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
    </nav>
    <div class="p-4 border-t border-gray-100">
        <button id="btn-logout" class="w-full flex items-center gap-3 p-3 rounded-xl text-red-500 hover:bg-red-50 font-bold transition">
            <span class="material-symbols-outlined">logout</span> Salir
        </button>
    </div>
</aside>