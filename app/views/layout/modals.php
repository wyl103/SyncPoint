<?php
// app/views/layout/modals.php
// Modales Globales de la Aplicación (Disponibles desde cualquier módulo o vista)
?>

<!-- PANEL DE CHATWOOT GLOBAL (Mobile: Fullscreen / Desktop: Panel Lateral Derecho 440px con Sombra) -->
<div id="modal-chatwoot" onclick="if(event.target === this) cerrarModalChatwoot()" class="hidden-view fixed inset-0 z-50 bg-charcoal/40 backdrop-blur-xs flex justify-end">
    <div id="modal-chatwoot-panel" class="bg-white w-full h-full flex flex-col overflow-hidden shadow-[-10px_0_30px_rgba(0,0,0,0.15)] border-l border-gray-200 relative z-50">
        <!-- Header del Chat -->
        <div class="px-5 py-4 bg-white border-b border-gray-100 flex justify-between items-center z-10 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="bg-primary/20 w-10 h-10 rounded-full flex items-center justify-center text-charcoal">
                    <span class="material-symbols-outlined text-2xl">forum</span>
                </div>
                <div>
                    <h3 id="chatwoot-modal-nombre-cliente" class="font-bold text-charcoal text-base leading-tight">Cargando chat...</h3>
                    <p id="chatwoot-modal-subtitulo" class="text-xs text-gray-500 font-semibold flex items-center gap-2 mt-0.5">
                        <span id="chatwoot-modal-telefono"></span>
                        <span id="chatwoot-modal-conv-id" class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold"></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="togglePanelPlantillas()" class="p-2 rounded-xl text-gray-500 hover:text-charcoal hover:bg-gray-100 transition flex items-center gap-1 font-bold text-xs" title="Plantillas de Mensajes">
                    <span class="material-symbols-outlined text-[20px]">description</span>
                    <span class="hidden sm:inline">Plantillas</span>
                </button>
                <button onclick="cerrarModalChatwoot()" class="p-2 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <!-- Alerta de Restricción de 24 horas WhatsApp -->
        <div id="chatwoot-banner-24h" class="hidden-view bg-amber-50 border-b border-amber-200 p-3 text-amber-900 text-xs font-medium flex items-center justify-between gap-2 z-10">
            <div class="flex items-center gap-1.5 leading-tight">
                <span class="material-symbols-outlined text-amber-600 text-[18px]">warning</span>
                <span>Restricción de 24h: Solo puedes responder usando una <strong>plantilla</strong>.</span>
            </div>
            <button onclick="togglePanelPlantillas()" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-2.5 py-1 rounded text-[10px] shrink-0 transition">
                Plantillas
            </button>
        </div>

        <!-- Panel Desplegable de Plantillas (Deslizable desde arriba) -->
        <div id="panel-plantillas-chatwoot" class="hidden-view bg-white border-b border-gray-200 p-4 space-y-3 z-20 shadow-md transition-all">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                <h4 class="text-sm font-bold text-charcoal flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary text-[18px]">description</span>
                    Plantillas de Mensaje
                </h4>
                <button onclick="togglePanelPlantillas()" class="text-gray-400 hover:text-charcoal"><span class="material-symbols-outlined text-[18px]">close</span></button>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Seleccionar Plantilla</label>
                <select id="select-plantilla-chatwoot" onchange="alSeleccionarPlantillaChatwoot()" class="w-full p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-xs font-semibold text-gray-700 outline-none focus:border-primary">
                    <option value="">-- Elige una plantilla --</option>
                </select>
            </div>

            <!-- Campos dinámicos para cambiar variables de la plantilla -->
            <div id="contenedor-variables-plantilla" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>

            <!-- Vista previa del mensaje formateado -->
            <div id="box-preview-plantilla" class="hidden bg-gray-50 p-3 rounded-lg border border-gray-200">
                <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Vista Previa</span>
                <p id="txt-preview-plantilla" class="text-xs text-gray-700 font-medium whitespace-pre-wrap"></p>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button onclick="usarPlantillaEnChat()" class="w-full bg-primary hover:bg-yellow-400 text-charcoal text-xs font-bold py-2 px-3 rounded-lg transition shadow-xs flex justify-center items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">send</span> Usar Plantilla
                </button>
            </div>
        </div>

        <!-- Cuerpo del Chat (Mensajes) -->
        <div id="chatwoot-mensajes-lista" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/60">
            <!-- Se llena dinámicamente con JS -->
        </div>

        <!-- Barra de Envío de Mensajes -->
        <div class="p-3 bg-white border-t border-gray-100 flex items-center gap-2">
            <input id="input-chatwoot-mensaje" type="text" onkeydown="if(event.key === 'Enter') enviarMensajeChatwoot()" placeholder="Escribe un mensaje para Chatwoot..." class="flex-1 p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            <button onclick="enviarMensajeChatwoot()" class="bg-primary hover:bg-yellow-400 text-charcoal font-bold px-4 py-3 rounded-xl shadow-xs flex items-center justify-center transition">
                <span class="material-symbols-outlined text-[20px]">send</span>
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

<!-- MODAL PROGRAMAR RECOLECCIÓN -->
<div id="modal-programar-recoleccion" onclick="if(event.target === this) cerrarModalProgramarRecoleccion()" class="hidden-view fixed inset-0 z-50 bg-charcoal/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden border border-gray-200 flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-charcoal text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">event_available</span>
                <span>Programar Recolección</span>
            </h3>
            <button onclick="cerrarModalProgramarRecoleccion()" class="p-1.5 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Form Body -->
        <form id="form-programar-recoleccion" onsubmit="solicitarConfirmacionRecoleccion(event)" class="p-6 overflow-y-auto space-y-4 flex-1">
            <input type="hidden" id="form-prog-cliente-id" value="">

            <!-- Sucursal -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-gray-400">store</span> Sucursal
                </label>
                <select id="form-prog-sucursal" onchange="alCambiarSucursalModalProg()" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
                    <option value="">-- Seleccionar sucursal --</option>
                </select>
            </div>

            <!-- Ruta (Solo activa si se selecciona Sucursal) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-primary">route</span> Ruta / Zona
                </label>
                <select id="form-prog-ruta" disabled onchange="alCambiarRutaModalProg()" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-100 text-gray-400 text-sm font-semibold outline-none transition cursor-not-allowed">
                    <option value="">Selecciona primero una sucursal...</option>
                </select>
            </div>

            <!-- Buscar / Seleccionar Cliente -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-gray-400">person_search</span> Cliente <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" id="form-prog-cliente-search" placeholder="Escribe el nombre o teléfono del cliente..." autocomplete="off" oninput="buscarClienteModalProg()" onfocus="buscarClienteModalProg()" class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
                    
                    <div id="dropdown-prog-clientes-options" class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-xs font-semibold text-gray-700"></div>
                </div>
                <div id="cliente-seleccionado-info" class="hidden mt-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                    <div>
                        <p id="lbl-cliente-sel-nombre" class="font-bold text-emerald-900 text-xs"></p>
                        <p id="lbl-cliente-sel-detalle" class="text-[10px] text-emerald-700 font-semibold mt-0.5"></p>
                    </div>
                    <button type="button" onclick="limpiarClienteSeleccionadoProg()" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold underline">Cambiar</button>
                </div>
            </div>

            <!-- Fecha de Recolección -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span> Fecha de Recolección <span class="text-red-500">*</span>
                </label>
                <input type="date" id="form-prog-fecha" required class="w-full p-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            </div>

            <!-- Footer con Botones Separados a la Derecha -->
            <div class="modal-footer-right">
                <button type="button" onclick="cerrarModalProgramarRecoleccion()" class="btn-secondary-main">
                    Cancelar
                </button>
                <button type="submit" id="btn-aplicar-programacion" class="btn-primary-main">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span> Aplicar
                </button>
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

