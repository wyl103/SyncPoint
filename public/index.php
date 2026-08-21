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
                    <div id="header-subtab-usuarios-nav" class="hidden flex items-center gap-3 text-lg sm:text-xl tracking-tight">
                        <button onclick="cambiarSubTabUsuario('directorio')" id="header-subtab-usuarios-directorio" class="text-charcoal font-extrabold cursor-pointer hover:text-black transition border-b-2 border-primary pb-0.5">
                            Usuarios
                        </button>
                        <span class="text-gray-300 font-light text-base sm:text-lg">|</span>
                        <button onclick="cambiarSubTabUsuario('programacion')" id="header-subtab-usuarios-programacion" class="text-gray-400 font-semibold cursor-pointer hover:text-charcoal transition border-b-2 border-transparent pb-0.5">
                            Programación
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

        <nav class="md:hidden bg-white border-t border-gray-200 py-2 px-1 flex justify-around items-center z-50 fixed bottom-0 left-0 right-0 w-full shadow-[0_-4px_12px_rgba(0,0,0,0.08)]">
            <button onclick="switchTab('dashboard')" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-charcoal cursor-pointer" data-target="dashboard">
                <span class="material-symbols-outlined text-[20px] filled">event</span>
                <span class="text-[9.5px] font-bold">Eventos</span>
            </button>
            <button onclick="switchTab('clientes'); if(typeof cambiarSubTabCliente === 'function') cambiarSubTabCliente('directorio');" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-gray-400 hover:text-charcoal cursor-pointer" data-target="clientes">
                <span class="material-symbols-outlined text-[20px]">group</span>
                <span class="text-[9.5px] font-bold">Clientes</span>
            </button>
            <button onclick="switchTab('mensajes')" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-gray-400 hover:text-charcoal cursor-pointer relative" data-target="mensajes">
                <span class="material-symbols-outlined text-[20px]">forum</span>
                <span class="text-[9.5px] font-bold">Mensajes</span>
                <span id="badge-nav-mensajes-mobile" style="width: 16px; height: 16px; min-width: 16px; min-height: 16px; border-radius: 50%; display: none; align-items: center; justify-content: center; line-height: 1; padding: 0;" class="absolute top-0.5 right-3 sm:right-6 bg-primary text-charcoal text-[9px] font-black shadow-xs">0</span>
            </button>
            <button onclick="switchTab('usuarios'); if(typeof cambiarSubTabUsuario === 'function') cambiarSubTabUsuario('directorio');" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-gray-400 hover:text-charcoal cursor-pointer" data-target="usuarios">
                <span class="material-symbols-outlined text-[20px]">settings</span>
                <span class="text-[9.5px] font-bold">Ajustes</span>
            </button>
            <button id="btn-logout-mobile" class="nav-btn flex-1 flex flex-col items-center justify-center gap-1 text-red-500 hover:text-red-600 cursor-pointer">
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