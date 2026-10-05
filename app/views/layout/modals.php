<?php
// app/views/layout/modals.php
// Modales Globales de la Aplicación (Disponibles desde cualquier módulo o vista)
?>

<!-- MODAL DE CHATWOOT GLOBAL (Centrado, flotante, máx 900px con bordes superior e inferior) -->
<!-- ======================================================== -->
<!-- MODAL COMPLETO DE MENSAJERÍA WHATSAPP Y GESTIÓN DE CITAS (3 PANELES) -->
<!-- ======================================================== -->
<div id="modal-chatwoot" onclick="if(event.target === this) cerrarModalChatwoot()" class="hidden-view fixed inset-0 z-50 flex items-center justify-center p-1 sm:p-2.5 md:p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200">
    <!-- CONTENEDOR MODAL PRINCIPAL (Sin restricción de max-width para aprovechar toda la pantalla) -->
    <div id="modal-chatwoot-panel" class="relative flex flex-col w-full h-[94vh] max-h-[960px] bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- HEADER DEL MODAL -->
        <header class="h-16 px-4 sm:px-6 bg-white border-b border-slate-200 flex items-center justify-between flex-shrink-0 z-20">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-2 h-9 cursor-pointer" onclick="cerrarModalChatwoot()">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-xs">
                        <span class="material-symbols-outlined text-[20px] filled">water_drop</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-extrabold tracking-tight text-slate-900 leading-none">OilBless</span>
                        <span class="text-[9px] font-semibold text-emerald-700 tracking-wider uppercase mt-0.5">Gestión Logística</span>
                    </div>
                </div>
                <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                    </div>
                    <h2 class="font-bold text-sm sm:text-base text-slate-900 tracking-tight">Mesa de Despacho &amp; Notificaciones WhatsApp</h2>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-50 border border-slate-200 text-slate-600 text-xs font-medium">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Conectado</span>
                </div>
                <button onclick="cerrarModalChatwoot()" aria-label="Cerrar modal" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" type="button">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
        </header>

        <!-- CUERPO MODAL: 3 PANELES CON COMPORTAMIENTO RESPONSIVO Y GAVETAS FLOTANTES -->
        <div class="relative flex flex-1 min-h-0 overflow-hidden">
            <!-- BACKDROP PARA GAVETAS EN PANTALLAS COMPACTAS -->
            <div id="modal-chatwoot-drawer-backdrop" onclick="cerrarTodosLosDrawersModal()" class="hidden absolute inset-0 bg-slate-900/40 backdrop-blur-2xs z-30 transition-opacity duration-200"></div>

            <!-- ============================================== -->
            <!-- PANEL 1: LISTADO DE CONVERSACIONES (~320px)    -->
            <!-- ============================================== -->
            <aside id="modal-chatwoot-panel-chats" class="w-80 flex flex-col flex-shrink-0 bg-slate-50/80 border-r border-slate-200 transition-all duration-200 z-20">
                <!-- Buscador Superior -->
                <div class="p-3.5 bg-white border-b border-slate-200 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                            <input id="input-buscar-modal-conversaciones" oninput="alFiltrarConversacionesModal()" class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition" placeholder="Buscar cliente, teléfono o ruta..." type="text">
                        </div>
                        <button onclick="togglePanelChatsModal(false)" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer lg:hidden" title="Cerrar lista">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <!-- Chips de Filtro -->
                    <div class="flex items-center gap-1.5">
                        <button id="chip-filtro-modal-todos" onclick="cambiarFiltroConversacionesModal('todos')" class="px-3 py-1 rounded-lg bg-emerald-600 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                            Todos <span id="chip-contador-modal-todos" class="ml-1 text-[11px] opacity-90 font-medium">0</span>
                        </button>
                        <button id="chip-filtro-modal-atencion" onclick="cambiarFiltroConversacionesModal('atencion')" class="px-3 py-1 rounded-lg bg-amber-50 border border-amber-200/80 text-amber-800 hover:bg-amber-100 font-semibold text-xs flex items-center gap-1 transition cursor-pointer">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Atención <span id="chip-contador-modal-atencion" class="text-[11px] font-bold">0</span>
                        </button>
                        <button id="chip-filtro-modal-confirmados" onclick="cambiarFiltroConversacionesModal('confirmados')" class="px-3 py-1 rounded-lg text-slate-600 hover:bg-slate-200/70 text-xs font-medium transition cursor-pointer">
                            Confirmados
                        </button>
                    </div>
                </div>

                <!-- Lista de Conversaciones con Scroll -->
                <div id="lista-conversaciones-modal" class="flex-1 overflow-y-auto divide-y divide-slate-100 scrollbar-thin">
                    <div class="p-10 text-center">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-emerald-600 mx-auto mb-2"></div>
                        <p class="text-xs text-slate-400 font-semibold">Cargando conversaciones...</p>
                    </div>
                </div>
            </aside>

            <!-- ============================================================== -->
            <!-- PANEL 2: CONVERSACIÓN PRINCIPAL (CENTRAL - MÁS GRANDE 800px+)  -->
            <!-- ============================================================== -->
            <section id="modal-chatwoot-panel-chat-center" class="flex-1 flex flex-col min-w-0 md:min-w-[450px] lg:min-w-[550px] xl:min-w-[700px] 2xl:min-w-[800px] bg-white relative h-full">
                <!-- Estado Vacío (Cuando no hay chat seleccionado) -->
                <div id="panel-chat-vacio" class="flex flex-1 flex-col items-center justify-center p-8 text-center bg-slate-50/50">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 border border-emerald-100 shadow-xs">
                        <span class="material-symbols-outlined text-3xl">chat</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Selecciona una conversación</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm">Haz clic en cualquier cliente del panel izquierdo para ver el historial de WhatsApp, responder o gestionar su recolección.</p>
                    <button onclick="togglePanelChatsModal(true)" class="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition shadow-xs flex items-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                        <span>Abrir lista de chats</span>
                    </button>
                </div>

                <!-- Contenedor del Chat Activo -->
                <div id="panel-chat-activo" class="hidden flex-1 flex flex-col min-w-0 h-full">
                    <!-- Encabezado del Chat Seleccionado -->
                    <div class="h-16 px-4 sm:px-6 bg-white border-b border-slate-200 flex items-center justify-between flex-shrink-0 gap-2">
                        <div class="flex items-center gap-2 sm:gap-3.5 min-w-0">
                            <!-- Botón para alternar visibilidad de la lista de chats -->
                            <button id="btn-toggle-chats-modal" onclick="togglePanelChatsModal()" class="p-2 -ml-2 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-slate-100 transition cursor-pointer flex-shrink-0" title="Ver / Ocultar chats">
                                <span class="material-symbols-outlined text-[22px]">menu_open</span>
                            </button>

                            <!-- Clic en la foto de perfil o nombre para alternar la ficha del cliente -->
                            <div onclick="togglePanelInfoModal()" class="flex items-center gap-3 min-w-0 cursor-pointer group p-1.5 -m-1.5 rounded-xl hover:bg-slate-50 transition" title="Clic en la foto de perfil para ver ficha e info del cliente">
                                <div id="chatwoot-modal-avatar-header" class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-xs group-hover:scale-105 group-hover:ring-2 group-hover:ring-emerald-500/40 transition">
                                    WA
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 id="chatwoot-modal-nombre-cliente" class="font-bold text-sm text-slate-900 truncate group-hover:text-emerald-700 transition">Cargando...</h3>
                                        <span id="chatwoot-modal-conv-id" class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            Conv.
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <span id="chatwoot-modal-telefono" class="font-mono text-slate-600 font-medium"></span>
                                        <span class="text-slate-300">•</span>
                                        <span id="chatwoot-modal-tag-24h" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold"></span>
                                        <span class="hidden xl:inline-flex text-[11px] text-emerald-600 font-medium group-hover:underline ml-1">Ficha cliente →</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción del Chat (Derecha) -->
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <!-- Botón para alternar ficha e información del cliente -->
                            <button id="btn-toggle-info-chat" onclick="togglePanelInfoModal()" class="p-2 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer" title="Ver / Ocultar ficha del cliente">
                                <span class="material-symbols-outlined text-[20px]">badge</span>
                            </button>
                            <button onclick="abrirModalPlantillas()" class="p-2 rounded-lg text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer" title="Plantillas oficiales">
                                <span class="material-symbols-outlined text-[20px]">description</span>
                            </button>
                            <button onclick="consultarActualizacionesChatwoot(false)" class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer" title="Actualizar chat">
                                <span class="material-symbols-outlined text-[20px]">refresh</span>
                            </button>
                        </div>
                    </div>

                    <!-- Historial de Conversación WhatsApp -->
                    <div id="chatwoot-mensajes-lista" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/60 scrollbar-thin">
                        <!-- Se llena dinámicamente -->
                    </div>

                    <!-- ÁREA DE ENTRADA Y ACCIONES RÁPIDAS -->
                    <div class="p-3.5 bg-white border-t border-slate-200 space-y-2.5">
                        <!-- Banner Informativo Meta WhatsApp 24h -->
                        <div id="chatwoot-banner-24h" class="hidden-view p-3 rounded-xl bg-amber-50/90 border border-amber-200 text-amber-900 flex items-start gap-2.5 shadow-xs">
                            <span class="material-symbols-outlined text-[20px] text-amber-600 flex-shrink-0 mt-0.5">warning</span>
                            <div class="flex-1 text-xs leading-relaxed">
                                <span class="font-bold block text-amber-950">Ventana de conversación de 24 horas cerrada</span>
                                <span>Han transcurrido más de 24h desde el último mensaje del cliente. Para escribir libremente, primero envía una <strong class="font-semibold">Plantilla Oficial HSM aprobada</strong> para que el cliente responda.</span>
                            </div>
                        </div>

                        <!-- Plantillas HSM de Acceso Rápido -->
                        <div id="chatwoot-plantillas-quick-bar" class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin pt-1">
                            <span class="flex-shrink-0 text-xs font-bold text-slate-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-amber-600">verified</span>Plantilla:
                            </span>
                            <button onclick="abrirModalPlantillas('confirmacion_entrega')" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-semibold whitespace-nowrap transition cursor-pointer flex items-center gap-1.5 active:scale-95">
                                <span class="material-symbols-outlined text-[14px] text-emerald-600">notifications_active</span>
                                <span>Confirmación Servicio</span>
                            </button>
                            <button onclick="abrirModalPlantillas('hola_oilbless')" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium whitespace-nowrap transition cursor-pointer flex items-center gap-1.5 active:scale-95">
                                <span class="material-symbols-outlined text-[14px] text-slate-500">handshake</span>
                                <span>Bienvenida Canal</span>
                            </button>
                            <button onclick="abrirModalPlantillas()" class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 text-xs font-medium whitespace-nowrap transition cursor-pointer flex items-center gap-1 ml-auto">
                                <span class="material-symbols-outlined text-[14px]">dashboard_customize</span>
                                <span>Ver todas</span>
                            </button>
                        </div>

                        <!-- Input de Escritura & Botón de Enviar -->
                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1.5 transition-all focus-within:border-emerald-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-emerald-500/20">
                            <button onclick="abrirModalPlantillas()" type="button" class="p-2 text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Elegir plantilla oficial">
                                <span class="material-symbols-outlined text-[20px]">description</span>
                            </button>
                            <input id="input-chatwoot-mensaje" type="text" oninput="alEscribirMensajeChatwoot()" onkeydown="if(event.key === 'Enter') enviarMensajeChatwoot()" placeholder="Escribe un mensaje de WhatsApp..." class="flex-1 bg-transparent border-0 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0 px-2 font-body">
                            <button id="btn-enviar-chatwoot" onclick="enviarMensajeChatwoot()" type="button" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-xs transition active:scale-95 whitespace-nowrap cursor-pointer">
                                <span class="material-symbols-outlined text-[17px]">send</span>
                                <span>Enviar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================== -->
            <!-- PANEL 3: INFORMACIÓN DEL CLIENTE & ACCIONES LOGÍSTICAS (~340px) -->
            <!-- ============================================================== -->
            <aside id="modal-chatwoot-panel-info" class="w-84 flex flex-col flex-shrink-0 bg-slate-50 border-l border-slate-200 p-5 overflow-y-auto scrollbar-thin justify-between transition-all duration-200 z-20">
                <!-- Estado Vacío Info Cliente -->
                <div id="panel-info-vacio" class="flex flex-1 flex-col items-center justify-center text-center p-4">
                    <div class="flex justify-end w-full mb-2">
                        <button onclick="togglePanelInfoModal(false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer" title="Cerrar panel de información">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>
                    <span class="material-symbols-outlined text-slate-300 text-4xl mb-2">badge</span>
                    <p class="text-xs text-slate-400 font-semibold">Sin información de cliente seleccionada</p>
                </div>

                <!-- Contenido con Datos de Cliente -->
                <div id="panel-info-activo" class="hidden flex-col justify-between flex-1 space-y-4">
                    <div class="space-y-4">
                        <!-- FICHA DE GENERADOR HEADER -->
                        <div class="border-b border-slate-200 pb-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">FICHA DE GENERADOR</span>
                                <div class="flex items-center gap-1">
                                    <button onclick="editarClienteDesdeModalChat()" class="px-2 py-0.5 rounded text-xs font-semibold text-emerald-700 hover:bg-emerald-100/70 flex items-center gap-1 transition cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                        <span>Editar</span>
                                    </button>
                                    <button onclick="togglePanelInfoModal(false)" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer" title="Cerrar ficha">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>
                            </div>
                            <h3 id="panel-info-nombre-cliente" class="text-sm font-bold text-slate-900 leading-snug">Cargando...</h3>
                            <p class="text-xs text-slate-600 flex items-start gap-1 mt-1 font-body">
                                <span class="material-symbols-outlined text-[16px] text-slate-400 flex-shrink-0 mt-0.5">location_on</span>
                                <span id="panel-info-direccion-cliente">Ibagué, Tolima</span>
                            </p>
                        </div>

                        <!-- Datos de Contacto y Perfil -->
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-xs space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Sucursal / Sede:</span>
                                <span id="panel-info-sucursal" class="font-bold text-slate-800">N/A</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Teléfono:</span>
                                <a id="panel-info-telefono-link" class="font-bold text-emerald-700 hover:underline flex items-center gap-1" href="#">
                                    <span id="panel-info-telefono-texto">+57 ...</span>
                                    <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                                </a>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Ciclo / Frecuencia:</span>
                                <span id="panel-info-frecuencia" class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                    N/A
                                </span>
                            </div>
                        </div>

                        <!-- TARJETA DESTACADA: RECOLECCIÓN PROGRAMADA -->
                        <div class="p-4 rounded-xl bg-amber-50/60 border-2 border-amber-200/80 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <span id="panel-info-badge-notif" class="px-2 py-0.5 rounded-full bg-amber-100 border border-amber-300/80 text-amber-900 text-[10px] font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>Activa
                                </span>
                                <span id="panel-info-estado-dia" class="text-xs font-bold text-amber-800">En ruta</span>
                            </div>
                            <div>
                                <span class="text-[11px] text-slate-500 uppercase tracking-wide font-medium block">Fecha Propuesta:</span>
                                <span id="panel-info-fecha-propuesta" class="text-sm font-bold text-slate-900">Hoy</span>
                            </div>
                            <div class="space-y-1.5 text-xs text-slate-700">
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-[17px] text-slate-500 mt-0.5">local_shipping</span>
                                    <div>
                                        <span id="panel-info-ruta-nombre" class="font-bold text-slate-900 block">Ruta Asignada</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-1 border-t border-amber-200/40">
                                    <span class="material-symbols-outlined text-[17px] text-emerald-600">water_drop</span>
                                    <span id="panel-info-volumen" class="font-bold text-slate-900">Aceite Vegetal Usado</span>
                                </div>
                            </div>

                            <!-- BOTONES DE ACCIÓN LOGÍSTICA PRINCIPAL -->
                            <div id="panel-info-acciones-evento" class="pt-2 space-y-2">
                                <button onclick="confirmarCitaDesdeModalChat()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl shadow-xs flex items-center justify-center gap-2 text-xs transition active:scale-98 cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    <span>Confirmar</span>
                                </button>
                                <button onclick="liberarCitaDesdeModalChat()" class="w-full border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 px-4 rounded-xl font-medium flex items-center justify-center gap-2 text-xs bg-white transition active:scale-98 cursor-pointer">
                                    <span class="material-symbols-outlined text-[17px] text-slate-500">event_busy</span>
                                    <span>Denegar</span>
                                </button>
                            </div>
                        </div>

                        <!-- BOTÓN ACCIÓN ADICIONAL -->
                        <button onclick="programarRecoleccionDesdeModalChat()" class="w-full py-2.5 px-3 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition shadow-xs cursor-pointer">
                            <span class="material-symbols-outlined text-[17px] text-emerald-600">add_circle</span>
                            <span>+ Programar Recolección</span>
                        </button>
                    </div>

                    <!-- HISTORIAL RESUMIDO INFERIOR -->
                    <div class="pt-4 border-t border-slate-200 mt-4 space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                            Estado de Sincronización
                        </span>
                        <div class="p-2.5 rounded-lg bg-white border border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-600">Canal WhatsApp</span>
                            <span class="font-bold text-emerald-700 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Conectado
                            </span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- MODAL DE PLANTILLAS DE MENSAJE WHATSAPP (Superpuesto en gris/blur sobre el chat) -->
<div id="modal-plantillas-whatsapp" onclick="if(event.target === this) cerrarModalPlantillas()" class="hidden-view fixed inset-0 z-[100] bg-slate-900/75 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-charcoal text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-2xl">description</span>
                <span>Plantillas de Mensaje WhatsApp</span>
            </h3>
            <button type="button" onclick="cerrarModalPlantillas()" class="p-1.5 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition" title="Cerrar">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Body con scroll -->
        <div class="p-6 overflow-y-auto space-y-4 flex-1">
            <!-- Selector de Plantilla -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-primary">list_alt</span>
                    Seleccionar Plantilla Oficial <span class="text-red-500">*</span>
                </label>
                <select id="select-modal-plantillas" onchange="alCambiarPlantillaEnModal()" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition cursor-pointer">
                    <option value="">-- Elige una plantilla --</option>
                </select>
            </div>

            <!-- Contenedor de Campos dinámicos de Variables -->
            <div id="contenedor-variables-plantilla-modal" class="hidden space-y-3.5 pt-1">
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-bold text-gray-600 mb-2">Completar datos de la plantilla:</p>
                    <div id="lista-inputs-variables" class="space-y-3"></div>
                </div>
            </div>

            <!-- Vista previa del mensaje formateado -->
            <div id="box-preview-plantilla-modal" class="hidden bg-amber-50/80 border border-amber-200 rounded-2xl p-4 sm:p-5 space-y-2.5 shadow-2xs">
                <span class="block text-[11px] font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-amber-700">visibility</span> Vista Previa del Mensaje
                </span>
                <div class="bg-white p-4 sm:p-5 rounded-xl border border-amber-200/80 shadow-2xs">
                    <p id="txt-preview-plantilla-modal" class="text-sm sm:text-base text-charcoal font-medium whitespace-pre-wrap leading-relaxed"></p>
                </div>
            </div>
        </div>

        <!-- Footer con Botones -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 shrink-0">
            <button type="button" onclick="cerrarModalPlantillas()" class="btn-secondary-main">
                Cancelar
            </button>
            <button type="button" id="btn-enviar-plantilla-accion" onclick="enviarPlantillaConfirmada()" class="btn-primary-main">
                <span class="material-symbols-outlined text-[18px]">send</span> Enviar Plantilla
            </button>
        </div>
    </div>
</div>

<!-- MODAL DE CREAR / EDITAR CLIENTE -->
<div id="modal-cliente" onclick="if(event.target === this) cerrarModalCliente()" class="hidden-view fixed inset-0 z-50 bg-charcoal/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center shrink-0">
            <h3 id="modal-cliente-titulo" class="font-bold text-charcoal text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person_add</span>
                <span id="txt-modal-cliente-accion">Nuevo Cliente</span>
            </h3>
            <button onclick="cerrarModalCliente()" class="p-1.5 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Form Body (Scrollable) -->
        <form id="form-cliente" onsubmit="guardarCliente(event)" class="p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" id="form-cliente-id" value="">

            <!-- Nombre -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Nombre Completo / Razón Social <span class="text-red-500">*</span></label>
                <input type="text" id="form-cliente-nombre" required placeholder="Ej: Restaurante Don Pedro" class="w-full p-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <!-- Teléfono WhatsApp -->
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Teléfono WhatsApp <span class="text-red-500">*</span></label>
                <input type="text" id="form-cliente-telefono" required placeholder="Ej: 573119876543" class="w-full p-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <!-- Sucursal con Botón + -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-bold text-gray-700">Sucursal</label>
                    <button type="button" onclick="abrirModalSucursalRapida()" class="btn-add-subaction" title="Crear nueva sucursal">
                        <span class="material-symbols-outlined text-[15px]">add</span> Nueva Sucursal
                    </button>
                </div>
                <!-- Searchable Select para Sucursal -->
                <div id="wrapper-select-sucursal" class="relative">
                    <input type="hidden" id="form-cliente-sucursal-id" value="">
                    <div class="relative">
                        <input type="text" id="search-sucursal-input" placeholder="Buscar sucursal..." autocomplete="off" onfocus="mostrarDropdownSearchable('sucursal')" oninput="filtrarDropdownSearchable('sucursal')" class="w-full p-3 pr-8 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
                        <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 pointer-events-none text-[18px]">arrow_drop_down</span>
                    </div>
                    <div id="dropdown-sucursal-options" class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-xs font-semibold text-gray-700"></div>
                </div>
            </div>

            <!-- Ruta / Zona con Botón + -->
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label class="block text-xs font-bold text-gray-700">Ruta / Zona</label>
                    <button type="button" id="btn-nueva-ruta-rapida" onclick="abrirModalRutaRapida()" disabled class="btn-add-subaction" title="Selecciona primero una sucursal para crear una ruta">
                        <span class="material-symbols-outlined text-[15px]">add</span> Nueva Ruta
                    </button>
                </div>
                <!-- Searchable Select para Ruta -->
                <div id="wrapper-select-ruta" class="relative">
                    <input type="hidden" id="form-cliente-ruta-id" value="">
                    <div class="relative">
                        <input type="text" id="search-ruta-input" placeholder="Selecciona primero una sucursal..." autocomplete="off" onfocus="mostrarDropdownSearchable('ruta')" oninput="filtrarDropdownSearchable('ruta')" class="w-full p-3 pr-8 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
                        <span class="material-symbols-outlined absolute right-3 top-3.5 text-gray-400 pointer-events-none text-[18px]">arrow_drop_down</span>
                    </div>
                    <div id="dropdown-ruta-options" class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-xs font-semibold text-gray-700"></div>
                </div>
            </div>

            <!-- Frecuencia -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Frecuencia de Recolección</label>
                <select id="form-cliente-frecuencia" onchange="alCambiarFrecuenciaCliente()" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 outline-none focus:border-primary focus:bg-white transition">
                    <option value="">-- Seleccionar frecuencia --</option>
                </select>
            </div>

            <!-- Campos adicionales para Frecuencia OTRA -->
            <div id="box-frecuencia-otra" class="hidden p-4 bg-amber-50/90 border border-amber-200 rounded-2xl space-y-3 shadow-2xs">
                <p class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-amber-600 text-[18px]">info</span> Crear Nueva Frecuencia
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Nombre Frecuencia <span class="text-red-500">*</span></label>
                        <input type="text" id="form-cliente-frecuencia-nombre" placeholder="Ej: Quincenal Especial" class="w-full p-2.5 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-charcoal outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 mb-1">Días (Intervalo) <span class="text-red-500">*</span></label>
                        <input type="number" id="form-cliente-frecuencia-dias" min="1" placeholder="Ej: 15" class="w-full p-2.5 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-charcoal outline-none focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Próxima Fecha de Recolección (Fecha Base) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Próxima Fecha de Recolección (Fecha Base)</label>
                <input type="date" id="form-cliente-fecha-base" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Estado del Cliente</label>
                <select id="form-cliente-estado" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-gray-700 outline-none focus:border-primary focus:bg-white transition">
                    <option value="no agendado">No Agendado</option>
                    <option value="agendado">Agendado</option>
                </select>
            </div>

            <!-- Footer con Botones -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="cerrarModalCliente()" class="btn-secondary-main">
                    Cancelar
                </button>
                <button type="submit" id="btn-guardar-cliente" class="btn-primary-main">
                    <span class="material-symbols-outlined text-[18px]">save</span> Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SUB-MODAL NUEVA SUCURSAL RÁPIDA -->
<div id="modal-sucursal-rapida" onclick="if(event.target === this) cerrarModalSucursalRapida()" class="hidden-view fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="font-bold text-charcoal text-base flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">store</span>
                Nueva Sucursal
            </h4>
            <button onclick="cerrarModalSucursalRapida()" class="p-1 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition"><span class="material-symbols-outlined text-[20px]">close</span></button>
        </div>

        <form id="form-sucursal-rapida" onsubmit="guardarSucursalRapida(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nombre de la Sucursal <span class="text-red-500">*</span></label>
                <input type="text" id="form-sucursal-nombre" required placeholder="Ej: Sucursal Neiva Norte" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="cerrarModalSucursalRapida()" class="btn-secondary-main">Cancelar</button>
                <button type="submit" class="btn-primary-main">Crear Sucursal</button>
            </div>
        </form>
    </div>
</div>

<!-- SUB-MODAL NUEVA RUTA RÁPIDA -->
<div id="modal-ruta-rapida" onclick="if(event.target === this) cerrarModalRutaRapida()" class="hidden-view fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-gray-200 p-6 space-y-4">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h4 class="font-bold text-charcoal text-base flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">route</span>
                Nueva Ruta / Zona
            </h4>
            <button onclick="cerrarModalRutaRapida()" class="p-1 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition"><span class="material-symbols-outlined text-[20px]">close</span></button>
        </div>

        <form id="form-ruta-rapida" onsubmit="guardarRutaRapida(event)" class="space-y-4">
            <div class="bg-amber-50 p-3 rounded-xl border border-amber-200 text-xs font-bold text-amber-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-600 text-[20px]">store</span>
                <span>Sucursal: <strong id="lbl-ruta-sucursal-nombre" class="text-black font-extrabold">...</strong></span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Nombre de la Ruta <span class="text-red-500">*</span></label>
                <input type="text" id="form-ruta-nombre" required placeholder="Ej: Ruta Sabatina Centro" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Ciudad <span class="text-red-500">*</span></label>
                <input type="text" id="form-ruta-ciudad" required placeholder="Ej: Ibagué" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="cerrarModalRutaRapida()" class="btn-secondary-main">Cancelar</button>
                <button type="submit" class="btn-primary-main">Crear Ruta</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PROGRAMAR / REASIGNAR RECOLECCIÓN (Diseño NewView/formRecoleccion Ampliado y Espacioso) -->
<div id="modal-programar-recoleccion" onclick="if(event.target === this) cerrarModalProgramarRecoleccion()" class="hidden-view fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 lg:p-8 overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200">
    <!-- DIALOG SHEET / MODAL CARD (Amplio, con más presencia y volumen en pantallas de PC) -->
    <div class="relative w-full max-w-5xl lg:max-w-5xl xl:max-w-6xl bg-white rounded-3xl shadow-2xl border border-slate-200/90 flex flex-col max-h-[92vh] overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-200">
        <!-- MODAL HEADER -->
        <div class="px-6 sm:px-8 py-5 sm:py-6 bg-white border-b border-slate-100 flex items-start justify-between z-10 shrink-0">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 shadow-xs shrink-0">
                    <span class="material-symbols-outlined text-[26px]">calendar_add_on</span>
                </div>
                <div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 id="modal-prog-titulo" class="text-base sm:text-xl font-extrabold text-slate-900 tracking-tight">Programar Nueva Recolección</h2>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold tracking-wide bg-emerald-100 text-emerald-800 uppercase">MÓDULO D-3</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Asigna paradas extraordinarias o ajusta el ciclo de recolección de UCO para el generador.</p>
                </div>
            </div>
            <button onclick="cerrarModalProgramarRecoleccion()" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer" type="button">
                <span class="material-symbols-outlined text-[22px]">close</span>
            </button>
        </div>

        <!-- FORM BODY SCROLLABLE -->
        <form id="form-programar-recoleccion" onsubmit="ejecutarGuardadoProgramacion(event)" class="flex flex-col flex-1 overflow-hidden">
            <input type="hidden" id="form-prog-cliente-id" value="">
            <input type="hidden" id="form-prog-evento-id" value="">

            <div class="p-6 sm:p-8 lg:p-9 overflow-y-auto space-y-6 sm:space-y-7 flex-1 custom-scrollbar">
                <!-- FILA 1: SUCURSAL / CENTRO DE ACOPIO & RUTA ASIGNADA -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                    <!-- Sucursal / Centro de Acopio -->
                    <div class="space-y-2">
                        <label class="text-xs sm:text-sm font-bold text-slate-700 flex items-center justify-between">
                            <span>Sucursal / Centro de Acopio</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">domain</span>
                            </div>
                            <select id="form-prog-sucursal" onchange="alCambiarSucursalModalProg()" class="w-full pl-10 pr-9 py-3 bg-slate-50 hover:bg-white text-slate-800 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 shadow-2xs focus:ring-2 focus:ring-emerald-500 focus:outline-none focus:bg-white transition cursor-pointer appearance-none">
                                <option value="">-- Seleccionar sucursal --</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">unfold_more</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ruta Asignada & Conductor -->
                    <div class="space-y-2">
                        <label class="text-xs sm:text-sm font-bold text-slate-700 flex items-center justify-between">
                            <span>Ruta Asignada</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">alt_route</span>
                            </div>
                            <select id="form-prog-ruta" disabled onchange="alCambiarRutaModalProg()" class="w-full pl-10 pr-9 py-3 bg-slate-100 text-slate-400 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 shadow-2xs outline-none transition cursor-not-allowed appearance-none">
                                <option value="">Selecciona primero una sucursal...</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILA 2: CLIENTE / GENERADOR & HISTORIAL -->
                <div class="space-y-3.5">
                    <!-- Campo Cliente / Generador con Búsqueda -->
                    <div class="space-y-2">
                        <label class="text-xs sm:text-sm font-bold text-slate-700 flex items-center justify-between">
                            <span>Cliente / Generador (UCO) <span class="text-rose-500">*</span></span>
                            <span class="text-xs text-slate-400 font-normal">Búsqueda rápida por nombre o teléfono</span>
                        </label>
                        
                        <!-- Input de Búsqueda -->
                        <div class="relative">
                            <div id="wrapper-input-cliente-search" class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-600">
                                    <span class="material-symbols-outlined text-[22px]">person_search</span>
                                </div>
                                <input type="text" id="form-prog-cliente-search" placeholder="Escribe el nombre o teléfono del cliente..." autocomplete="off" oninput="buscarClienteModalProg()" onfocus="buscarClienteModalProg()" class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 hover:border-slate-300 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition shadow-2xs">
                            </div>

                            <!-- Dropdown de resultados -->
                            <div id="dropdown-prog-clientes-options" class="hidden absolute z-30 w-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-56 overflow-y-auto text-xs sm:text-sm font-semibold text-slate-700 scrollbar-thin"></div>

                            <!-- Ficha de Cliente Seleccionado (Estilo NewView) -->
                            <div id="cliente-seleccionado-info" class="hidden relative flex items-center bg-white border-2 border-emerald-500/80 rounded-2xl px-4 py-3 shadow-xs">
                                <span class="material-symbols-outlined text-[22px] text-emerald-600 mr-3.5 shrink-0">check_circle</span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <span id="lbl-cliente-sel-nombre" class="text-sm sm:text-base font-bold text-slate-900">Restaurante</span>
                                        <span id="lbl-cliente-sel-detalle" class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold px-2.5 py-0.5 rounded-full">Tel / Ruta</span>
                                    </div>
                                </div>
                                <button type="button" onclick="limpiarClienteSeleccionadoProg()" class="ml-2 text-slate-400 hover:text-emerald-700 p-1.5 rounded-xl hover:bg-slate-100 transition cursor-pointer" title="Cambiar Cliente">
                                    <span class="material-symbols-outlined text-[20px]">sync</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel dinámico de Ciclo y Estado del Cliente -->
                    <div id="panel-historial-ciclo-cliente" class="hidden bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 sm:p-5 transition animate-in fade-in duration-150">
                        <div class="flex items-center justify-between mb-3.5 pb-2.5 border-b border-emerald-100 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[20px] text-emerald-700">history_toggle_off</span>
                                <span class="text-xs sm:text-sm font-bold text-emerald-900 uppercase tracking-wide">Ciclo Programado del Cliente</span>
                            </div>
                            <span class="text-xs sm:text-sm text-emerald-800 font-medium">Frecuencia: <strong id="lbl-cliente-sel-frecuencia" class="font-bold">Semanal</strong></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="bg-white p-3.5 rounded-xl border border-emerald-100 shadow-2xs flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Próxima Visita</span>
                                    <h5 id="lbl-cliente-proxima-visita" class="text-xs sm:text-sm font-bold text-slate-900">--</h5>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-1 text-[11px] text-emerald-700 font-medium">
                                    <span class="material-symbols-outlined text-[14px]">event</span> Ciclo activo
                                </div>
                            </div>
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Teléfono WhatsApp</span>
                                    <h5 id="lbl-cliente-telefono" class="text-xs sm:text-sm font-bold text-slate-900">--</h5>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-1 text-[11px] text-slate-500">
                                    <span class="material-symbols-outlined text-[14px] text-emerald-600">chat</span> Notificaciones
                                </div>
                            </div>
                            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Sucursal / Ciudad</span>
                                    <h5 id="lbl-cliente-ciudad" class="text-xs sm:text-sm font-bold text-slate-900">--</h5>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center gap-1 text-[11px] text-emerald-700 font-semibold">
                                    <span class="material-symbols-outlined text-[14px]">verified</span> Asignado
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FILA 3: SELECCIÓN DE MODALIDAD / RECURRENCIA -->
                <div class="space-y-2.5">
                    <label class="text-xs sm:text-sm font-bold text-slate-700 flex items-center gap-1.5">
                        <span>Modalidad de Programación</span>
                        <span class="material-symbols-outlined text-[16px] text-slate-400" title="Define cómo impactará la parada en las fechas automáticas">help</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- TARJETA A: SOLO POR ESTA VEZ -->
                        <label id="card-modo-once" class="relative flex flex-col p-4 sm:p-5 bg-emerald-50/50 border-2 border-emerald-600 rounded-2xl shadow-2xs cursor-pointer transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <input checked type="radio" name="modalidad_recoleccion" value="once" onchange="actualizarEstiloModalidadProg()" class="h-4.5 w-4.5 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                    <div>
                                        <span class="text-xs sm:text-sm font-bold text-emerald-950 block">Solo por esta vez (Parada Extraordinaria)</span>
                                        <span class="text-[11px] sm:text-xs font-semibold text-emerald-700">Refuerzo sin modificar el calendario</span>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-[22px] text-emerald-600 shrink-0">bolt</span>
                            </div>
                            <p class="text-[11px] sm:text-xs text-slate-600 mt-2.5 pl-7.5 leading-relaxed">
                                Agrega la recolección únicamente a la fecha seleccionada sin alterar las fechas del calendario habitual del generador.
                            </p>
                        </label>

                        <!-- TARJETA B: MODIFICAR RECURRENCIA -->
                        <label id="card-modo-recurrent" class="relative flex flex-col p-4 sm:p-5 bg-white border border-slate-200 hover:border-slate-300 rounded-2xl shadow-2xs cursor-pointer transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="modalidad_recoleccion" value="recurrent" onchange="actualizarEstiloModalidadProg()" class="h-4.5 w-4.5 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                    <div>
                                        <span class="text-xs sm:text-sm font-bold text-slate-800 block">Modificar Recurrencia (Todas)</span>
                                        <span class="text-[11px] sm:text-xs font-semibold text-slate-500">Actualizar ciclo de visitas periódico</span>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-[22px] text-slate-400 shrink-0">sync</span>
                            </div>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-2.5 pl-7.5 leading-relaxed">
                                Actualiza la fecha base del generador a partir de este día recalculando la frecuencia para los siguientes periodos.
                            </p>
                        </label>
                    </div>
                </div>

                <!-- FILA 4: FECHA DE RECOLECCIÓN ASIGNADA -->
                <div class="space-y-3 bg-slate-50/70 border border-slate-200/90 rounded-2xl p-4 sm:p-5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label class="text-xs sm:text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600">calendar_month</span>
                            <span>Fecha de Recolección Asignada <span class="text-rose-500">*</span></span>
                        </label>
                        <span id="badge-fecha-info-prog" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800 bg-emerald-100/80 border border-emerald-200 px-3 py-1 rounded-full">
                            <span class="material-symbols-outlined text-[15px] text-emerald-700">schedule</span>
                            <span id="lbl-fecha-info-prog">Turno Regular</span>
                        </span>
                    </div>
                    <div class="relative">
                        <input type="date" id="form-prog-fecha" required class="w-full px-4 py-3 bg-white border border-slate-200 hover:border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- MODAL FOOTER -->
            <div class="px-6 sm:px-8 py-4 sm:py-5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2 text-slate-500 text-xs sm:text-sm">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">verified_user</span>
                    <span>Despacho seguro · Enlace con planta de reciclaje</span>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="button" onclick="cerrarModalProgramarRecoleccion()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-aplicar-programacion" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-600/20 transition-all active:scale-[0.98] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                        <span id="btn-aplicar-programacion-texto">Confirmar y Asignar a Ruta</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- SUB-MODAL CONFIRMACIÓN DE FRECUENCIA / EVENTO PUNTUAL -->
<div id="modal-confirmar-frecuencia" onclick="if(event.target === this) cerrarModalConfirmarFrecuencia()" class="hidden-view fixed inset-0 z-50 bg-charcoal/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-200 p-6 flex flex-col gap-4">
        <!-- Header / Preambulo -->
        <div class="flex items-start gap-3">
            <div class="bg-amber-100 text-amber-700 p-3 rounded-2xl shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">help_outline</span>
            </div>
            <div>
                <h4 class="font-bold text-charcoal text-base leading-snug">¿Cómo deseas aplicar esta recolección?</h4>
                <p class="text-xs text-gray-600 font-medium mt-1 leading-relaxed">
                    ¿Desea que cambiemos todas las recolecciones para estas fechas según la frecuencia del cliente o es por solo esta vez?
                </p>
            </div>
        </div>

        <!-- Botones de Opciones con Espaciado Generoso (Gap) -->
        <div class="confirm-options-container">
            <button type="button" onclick="procesarProgramacionRecoleccion('todas')" class="btn-confirm-option-primary">
                <div class="bg-yellow-400/30 text-amber-900 p-2.5 rounded-xl shrink-0 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">update</span>
                </div>
                <div>
                    <p class="font-extrabold text-charcoal text-sm">Cambiar todas las recolecciones</p>
                    <p class="text-xs font-medium text-gray-600 mt-0.5 leading-snug">Actualiza la fecha base del cliente para recalcular la frecuencia periódica.</p>
                </div>
            </button>

            <button type="button" onclick="procesarProgramacionRecoleccion('esta_vez')" class="btn-confirm-option-secondary">
                <div class="bg-gray-200 text-gray-700 p-2.5 rounded-xl shrink-0 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">event</span>
                </div>
                <div>
                    <p class="font-extrabold text-charcoal text-sm">Solo por esta vez</p>
                    <p class="text-xs font-medium text-gray-600 mt-0.5 leading-snug">Crea únicamente un evento de recolección puntual para esta fecha.</p>
                </div>
            </button>
        </div>

        <!-- Footer con Botón Cancelar en Rojo en la parte inferior derecha -->
        <div class="modal-footer-right">
            <button type="button" onclick="cerrarModalConfirmarFrecuencia()" class="btn-confirm-cancel">
                <span class="material-symbols-outlined text-[18px]">close</span> Cancelar
            </button>
        </div>
    </div>
</div>

<!-- MODAL CREAR / EDITAR USUARIO -->
<div id="modal-crear-usuario" class="hidden-view fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-gray-100 animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
            <h3 id="modal-usuario-titulo" class="font-bold text-charcoal text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">person_add</span> Nuevo Usuario
            </h3>
            <button onclick="cerrarModalCrearUsuario()" class="text-gray-400 hover:text-charcoal p-1 rounded-lg transition">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <form id="form-crear-usuario" onsubmit="event.preventDefault(); guardarUsuario();" class="space-y-4">
            <input type="hidden" id="form-usuario-id">

            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700">Nombre Completo *</label>
                <input id="form-usuario-nombre" type="text" required placeholder="Ej. Carlos Pérez" class="w-full border border-gray-200 rounded-xl p-2.5 text-sm focus:outline-none focus:border-primary">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700">Correo Electrónico *</label>
                <input id="form-usuario-correo" type="email" required placeholder="carlos@oilbless.com" class="w-full border border-gray-200 rounded-xl p-2.5 text-sm focus:outline-none focus:border-primary">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700">Tipo de Usuario *</label>
                <select id="form-usuario-tipo" class="w-full border border-gray-200 rounded-xl p-2.5 text-sm bg-white focus:outline-none focus:border-primary">
                    <option value="normal">Normal</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-700" id="lbl-usuario-password">Contraseña *</label>
                <input id="form-usuario-password" type="password" placeholder="••••••••" class="w-full border border-gray-200 rounded-xl p-2.5 text-sm focus:outline-none focus:border-primary">
                <span id="txt-usuario-pass-help" class="text-[11px] text-gray-400 block hidden">Dejar en blanco para mantener la contraseña actual.</span>
            </div>

            <div class="modal-footer-right pt-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="cerrarModalCrearUsuario()" class="btn-secondary-main">
                    Cancelar
                </button>
                <button type="submit" id="btn-guardar-usuario" class="btn-primary-main">
                    <span class="material-symbols-outlined text-[18px]">save</span> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

