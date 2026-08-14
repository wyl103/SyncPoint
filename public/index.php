<?php
// public/index.php

// 1. Incluimos la cabecera (CSS, Tailwind, Head)
require_once __DIR__ . '/../app/views/layout/head.php';

// 2. Incluimos la vista de Login (oculta o visible según el JS)
require_once __DIR__ . '/../app/views/auth/login.php';
?>

<div id="view-app" class="hidden-view h-full w-full flex flex-col md:flex-row bg-background-light">
    
    <?php require_once __DIR__ . '/../app/views/layout/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        <header class="bg-white border-b border-gray-200 px-4 py-4 md:px-8 z-30 shadow-sm">
            <div class="w-full max-w-[1920px] mx-auto flex justify-between items-center">
                <div id="header-title-wrapper" class="flex items-center">
                    <h1 id="header-title" class="text-xl font-bold tracking-tight text-charcoal">Eventos</h1>
                    <div id="header-subtab-nav" class="hidden flex items-center gap-3 text-lg sm:text-xl tracking-tight">
                        <button onclick="cambiarSubTabCliente('directorio')" id="header-subtab-directorio" class="text-charcoal font-extrabold cursor-pointer hover:text-black transition">
                            Clientes
                        </button>
                        <span class="text-gray-300 font-light text-base sm:text-lg">|</span>
                        <button onclick="cambiarSubTabCliente('sucursales-rutas')" id="header-subtab-sucursales-rutas" class="text-gray-400 font-semibold cursor-pointer hover:text-charcoal transition">
                            Sucursales y Rutas
                        </button>
                    </div>
                </div>
                <div class="flex gap-3 items-center">
                    <span id="user-name-display" class="text-sm font-semibold hidden md:block text-gray-500"></span>
                    
                    <div class="bg-primary/20 w-10 h-10 flex items-center justify-center rounded-full text-charcoal">
                        <span class="material-symbols-outlined">account_circle</span>
                    </div>
                </div>
            </div>
        </header>

        <?php require_once __DIR__ . '/../app/views/dashboard/main.php'; ?>

        <nav class="md:hidden bg-white border-t border-gray-200 px-6 py-3 flex justify-around items-center z-50 fixed bottom-0 w-full shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <button onclick="switchTab('dashboard')" class="nav-btn flex flex-col items-center gap-1 text-charcoal" data-target="dashboard">
                <span class="material-symbols-outlined filled">event</span><span class="text-[10px] font-bold">Eventos</span>
            </button>
            <button onclick="switchTab('clientes')" class="nav-btn flex flex-col items-center gap-1 text-gray-400" data-target="clientes">
                <span class="material-symbols-outlined">group</span><span class="text-[10px] font-bold">Clientes</span>
            </button>
            <button id="btn-logout-mobile" class="nav-btn flex flex-col items-center gap-1 text-red-500">
                <span class="material-symbols-outlined">menu</span><span class="text-[10px] font-bold">Salir</span>
            </button>
        </nav>
    </div>
</div>

<?php 
// 4. Incluimos las modales globales de la aplicación
require_once __DIR__ . '/../app/views/layout/modals.php'; 
?>

<script src="js/auth.js"></script>
<script src="js/modules/utils.js"></script>
<script src="js/modules/dashboard.js"></script>
<script src="js/modules/clientes.js"></script>
<script src="js/modules/sucursales_rutas.js"></script>
<script src="js/modules/chatwoot.js"></script>
<script src="js/app.js"></script>

</body>
</html>