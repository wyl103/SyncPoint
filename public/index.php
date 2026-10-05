<?php
// public/index.php

// Manejar peticiones a la API (/app/api/...)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$cleanUri = preg_replace('/^\/app_bless(\/public)?/', '', $requestUri);

if (strpos($cleanUri, '/app/api/') === 0 || strpos($requestUri, '/app/api/') === 0) {
    $targetPath = strpos($cleanUri, '/app/api/') === 0 ? $cleanUri : $requestUri;
    $apiFile = __DIR__ . '/..' . $targetPath;
    if (file_exists($apiFile) && !is_dir($apiFile)) {
        require $apiFile;
        exit;
    }
}

// 1. Incluimos la cabecera (CSS, Tailwind, Head)
require_once __DIR__ . '/../app/views/layout/head.php';

// 2. Incluimos la vista de Login (oculta o visible según el JS)
require_once __DIR__ . '/../app/views/auth/login.php';
?>

<div id="view-app" class="hidden-view h-full w-full flex flex-col md:flex-row bg-background-light">
    
    <?php require_once __DIR__ . '/../app/views/layout/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- TOP NAVIGATION BAR GLOBAL -->
        <header class="h-16 bg-white border-b border-slate-200 px-4 md:px-6 flex items-center justify-between shrink-0 z-30 shadow-xs">
            <div class="flex items-center gap-4 md:gap-6 flex-1 min-w-0 mr-4 max-w-2xl">
                <!-- Título dinámico y sub-navegación por módulo -->
                <div id="header-title-wrapper" class="flex items-center shrink-0">
                    <h1 id="header-title" class="text-sm md:text-base font-bold tracking-tight text-slate-900 truncate">Centro de Operaciones</h1>
                    
                    <div id="header-subtab-nav" class="hidden flex items-center gap-2 md:gap-3 text-xs md:text-sm tracking-tight">
                        <button onclick="cambiarSubTabCliente('directorio')" id="header-subtab-directorio" class="text-slate-900 font-extrabold cursor-pointer hover:text-emerald-700 transition border-b-2 border-emerald-600 pb-0.5">
                            Clientes
                        </button>
                        <span class="text-slate-300 font-light">|</span>
                        <button onclick="cambiarSubTabCliente('sucursales-rutas')" id="header-subtab-sucursales-rutas" class="text-slate-400 font-semibold cursor-pointer hover:text-slate-900 transition border-b-2 border-transparent pb-0.5">
                            Sucursales y Rutas
                        </button>
                    </div>

                    <div id="header-subtab-usuarios-nav" class="hidden flex items-center gap-2 md:gap-3 text-xs md:text-sm tracking-tight">
                        <button onclick="cambiarSubTabUsuario('directorio')" id="header-subtab-usuarios-directorio" class="text-slate-900 font-extrabold cursor-pointer hover:text-emerald-700 transition border-b-2 border-emerald-600 pb-0.5">
                            Usuarios
                        </button>
                        <span class="text-slate-300 font-light">|</span>
                        <button onclick="cambiarSubTabUsuario('programacion')" id="header-subtab-usuarios-programacion" class="text-slate-400 font-semibold cursor-pointer hover:text-slate-900 transition border-b-2 border-transparent pb-0.5">
                            Programación
                        </button>
                    </div>
                </div>

                <div class="hidden sm:block h-5 w-px bg-slate-200 shrink-0"></div>

                <!-- Barra de Búsqueda Global (Sin el selector de Ibagué) -->
                <div class="relative flex-1 max-w-xs md:max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">search</span>
                    </div>
                    <input id="global-search-input" class="block w-full pl-9 pr-3 py-1.5 md:py-2 border border-slate-200 rounded-xl text-xs placeholder-slate-400 bg-slate-50/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" placeholder="Buscar cliente, dirección o ID..." type="text">
                </div>
            </div>

            <!-- Acciones Globales Derecha -->
            <div class="flex items-center gap-2.5 md:gap-4 shrink-0">
                <button onclick="abrirModalProgramarRecoleccion()" class="inline-flex items-center gap-1.5 px-3 md:px-3.5 py-1.5 md:py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold text-white shadow-sm shadow-emerald-600/20 transition active:scale-[0.98] cursor-pointer">
                    <span class="material-symbols-outlined text-[18px] text-white">add</span>
                    <span class="text-white font-semibold">Nueva Recolección</span>
                </button>

                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                <div class="flex items-center gap-2.5 cursor-pointer group">
                    <div id="user-avatar-initials" class="w-8 h-8 rounded-full bg-emerald-700 text-white font-bold text-xs flex items-center justify-center ring-2 ring-emerald-100 group-hover:ring-emerald-300 transition">
                        US
                    </div>
                    <div class="hidden md:flex flex-col text-left">
                        <span id="user-name-display" class="text-xs font-bold text-slate-800 leading-tight">Usuario</span>
                        <span id="user-role-display" class="text-[11px] text-slate-400">Operador Logístico</span>
                    </div>
                </div>
            </div>
        </header>

        <?php require_once __DIR__ . '/../app/views/dashboard/main.php'; ?>

        <nav class="md:hidden mobile-bottom-nav bg-white border-t border-slate-200 py-2 px-1 flex justify-around items-center z-50 fixed bottom-0 left-0 right-0 w-full shadow-[0_-4px_12px_rgba(15,23,42,0.06)]">
            <button onclick="switchTab('dashboard')" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-emerald-600 cursor-pointer" data-target="dashboard">
                <span class="material-symbols-outlined text-[20px] text-emerald-600 filled">dashboard</span>
                <span class="text-[9.5px] font-bold">Eventos</span>
            </button>
            <button onclick="switchTab('clientes'); if(typeof cambiarSubTabCliente === 'function') cambiarSubTabCliente('directorio');" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-emerald-700 cursor-pointer" data-target="clientes">
                <span class="material-symbols-outlined text-[20px] text-slate-400">storefront</span>
                <span class="text-[9.5px] font-bold">Clientes</span>
            </button>
            <button onclick="abrirModalChatwoot(null)" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-emerald-700 cursor-pointer relative" data-target="mensajes">
                <span class="material-symbols-outlined text-[20px] text-slate-400">chat</span>
                <span class="text-[9.5px] font-bold">Mensajes</span>
                <span id="badge-nav-mensajes-mobile" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; border-radius: 50%; display: none; align-items: center; justify-content: center; line-height: 1; padding: 0;" class="absolute top-0.5 right-3 sm:right-6 bg-amber-500 text-white text-[9px] font-black shadow-xs">0</span>
            </button>
            <button onclick="switchTab('usuarios'); if(typeof cambiarSubTabUsuario === 'function') cambiarSubTabUsuario('directorio');" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-slate-400 hover:text-emerald-700 cursor-pointer" data-target="usuarios">
                <span class="material-symbols-outlined text-[20px] text-slate-400">tune</span>
                <span class="text-[9.5px] font-bold">Ajustes</span>
            </button>
            <button id="btn-logout-mobile" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-rose-500 hover:text-rose-600 cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                <span class="text-[9.5px] font-bold">Salir</span>
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
<script src="js/modules/usuarios.js"></script>
<script src="js/modules/chatwoot.js"></script>
<script src="js/modules/mensajes.js"></script>
<script src="js/app.js"></script>
</body>
</html>