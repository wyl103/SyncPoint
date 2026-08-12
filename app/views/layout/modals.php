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
