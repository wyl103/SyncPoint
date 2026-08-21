<?php
// app/views/layout/modals.php
// Modales Globales de la Aplicación (Disponibles desde cualquier módulo o vista)
?>

<!-- MODAL DE CHATWOOT GLOBAL (Centrado, flotante, máx 900px con bordes superior e inferior) -->
<div id="modal-chatwoot" onclick="if(event.target === this) cerrarModalChatwoot()" class="hidden-view fixed inset-0 z-50 bg-slate-900/65 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
    <div id="modal-chatwoot-panel" class="bg-white w-full max-w-[900px] h-[calc(100vh-2rem)] max-h-[calc(100vh-2rem)] flex flex-col rounded-2xl overflow-hidden shadow-2xl border border-gray-200 relative z-50 animate-in fade-in zoom-in-95 duration-200">
        <!-- Header del Chat -->
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center z-10 shadow-xs shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="bg-primary/20 w-10 h-10 rounded-full flex items-center justify-center text-charcoal shrink-0">
                    <span class="material-symbols-outlined text-2xl">forum</span>
                </div>
                <div class="min-w-0">
                    <h3 id="chatwoot-modal-nombre-cliente" class="font-bold text-charcoal text-base leading-tight truncate">Cargando chat...</h3>
                    <p id="chatwoot-modal-subtitulo" class="text-xs text-gray-500 font-semibold flex items-center gap-2 mt-0.5 truncate">
                        <span id="chatwoot-modal-telefono"></span>
                        <span id="chatwoot-modal-conv-id" class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold"></span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button onclick="cerrarModalChatwoot()" class="p-2 rounded-full text-gray-400 hover:text-charcoal hover:bg-gray-100 transition" title="Cerrar chat">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <!-- Alerta de Restricción de 24 horas WhatsApp (Estilo Notificación Móvil Centrada y Flotante) -->
        <div id="chatwoot-banner-24h" class="hidden-view w-full px-4 pt-3 pb-1 flex justify-center shrink-0 z-10">
            <div class="bg-amber-50/95 border border-amber-200 text-amber-950 px-4 py-2.5 rounded-2xl shadow-xs max-w-xl w-full flex items-center justify-between gap-3 text-xs font-medium animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="bg-amber-100 text-amber-700 w-7 h-7 rounded-full flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[18px]">warning</span>
                    </div>
                    <span class="leading-tight text-xs text-amber-950">Restricción de 24h: Solo puedes responder con una <strong class="font-bold">plantilla oficial</strong>.</span>
                </div>
                <button type="button" onclick="abrirModalPlantillas()" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-3 py-1.5 rounded-xl text-xs shrink-0 transition flex items-center gap-1 shadow-xs">
                    <span class="material-symbols-outlined text-[16px]">description</span> Elegir
                </button>
            </div>
        </div>

        <!-- Cuerpo del Chat (Mensajes) -->
        <div id="chatwoot-mensajes-lista" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-3 bg-gray-50/60 min-h-0">
            <!-- Se llena dinámicamente con JS -->
        </div>

        <!-- Barra de Envío de Mensajes con Padding Generoso y Botón Plantillas (Estilo WhatsApp) -->
        <div class="p-4 sm:p-5 bg-white border-t border-gray-100 flex items-center gap-3 shrink-0 rounded-b-2xl">
            <!-- Botón Plantillas (Abre el Modal de Plantillas WhatsApp) -->
            <button type="button" id="btn-adjuntar-plantilla" onclick="abrirModalPlantillas()" class="p-2.5 rounded-xl text-gray-500 hover:text-charcoal hover:bg-gray-100 transition shrink-0 flex items-center justify-center focus:outline-none" title="Plantillas de Mensaje WhatsApp">
                <span class="material-symbols-outlined text-[24px]">description</span>
            </button>

            <!-- Campo de Texto -->
            <input id="input-chatwoot-mensaje" type="text" oninput="alEscribirMensajeChatwoot()" onkeydown="if(event.key === 'Enter') enviarMensajeChatwoot()" placeholder="Escribe un mensaje para Chatwoot..." class="flex-1 py-3 px-4 rounded-xl border border-gray-200 bg-gray-50 text-sm font-semibold text-charcoal outline-none focus:border-primary focus:bg-white transition">
            
            <!-- Botón de Envío (Solo visible cuando hay texto escrito) -->
            <button id="btn-enviar-chatwoot" onclick="enviarMensajeChatwoot()" class="hidden bg-primary hover:bg-yellow-400 text-charcoal font-bold p-3 rounded-xl shadow-xs flex items-center justify-center transition shrink-0">
                <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
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

