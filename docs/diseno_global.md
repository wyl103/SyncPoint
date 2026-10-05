# 🎨 Especificación del Sistema de Diseño Global y Navegación - SyncPoint

Esta documentación describe la arquitectura visual, tokens de diseño, componentes globales de navegación (barra lateral y barra superior) y proceso de optimización del frontend implementados a partir del diseño de **NewView/Eventos**.

---

## 🌿 1. Paleta de Colores y Tokens Globales

La interfaz transiciona de colores planos y legacy a un centro de control logístico empresarial de grado B2B:

### 🎯 Tokens de Color
- **Primary (`#059669` Emerald 600 / `#047857` Emerald 700):**
  - Representa sostenibilidad, logística inversa circular y confirmación de operaciones.
  - Usado en botones primarios (`.btn-primary-main`), elementos activos de navegación, iconos destacados y badges de confirmación.
  - Tinte de fondo: `#ecfdf5` (`emerald-50`).
  - Bordes de acento: `#a7f3d0` (`emerald-200`).
- **Secondary (`#D97706` Warm Amber / `#B45309` Amber 700):**
  - Significa urgencia operativa, estados pendientes de WhatsApp, despachos de hoy y contadores de notificaciones.
  - Usado en badges de mensajes y estados de atención prioritaria.
- **Base Canvas (`#F8FAFC` Slate 50):**
  - Fondo global que elimina estridencias y otorga confort visual prolongado.
- **Superficies y Tarjetas (`#FFFFFF` Pure White):**
  - Superficies con borde sutil `1px solid #E2E8F0` y micro-sombras refinadas (`shadow-xs` / `shadow-sm`).
- **Tipografía de Alta Jerarquía (`#0F172A` Deep Slate / Navy):**
  - Garantiza máxima legibilidad y contraste profesional.
- **Texto Secundario y Metadatos (`#64748B` Slate 500 / `#94A3B8` Slate 400):**
  - Fechas, etiquetas, subtextos y estados inactivos.

---

## 🔤 2. Tipografía

Se adopta la combinación tipográfica corporativa moderna de Google Fonts:
1. **Plus Jakarta Sans:** Títulos, encabezados de módulo, navegación principal y números destacados.
2. **Inter:** Cuerpo de texto, tablas transaccionales, formularios, entradas de búsqueda e inputs.
3. **Material Symbols Outlined:** Sistema unificado de iconografía moderna con soporte para estado relleno (`filled`).

---

## 🧭 3. Barra de Navegación Lateral (Sidebar Persistente)

Ubicada en `app/views/layout/sidebar.php`, cuenta con una estructura fija de 260px (`w-64`) en desktop:

### Secciones:
1. **Header de Marca OilBless:**
   - Contenedor con icono de gota estilizado en esmeralda (`water_drop`), logotipo `OilBless` y subtítulo `Gestión Logística`.
2. **Menú Principal:**
   - **Centro de Operaciones (`dashboard`):** Icono `dashboard`, enlace directo a la vista de Eventos y métricas.
   - **Recolecciones & Rutas (`sucursales-rutas`):** Icono `local_shipping`, redirige al sub-módulo de sucursales y programación de rutas.
   - **Mensajes (`mensajes`):** Icono `chat`, incluye badge numérico con estilo Amber para conversaciones entrantes no leídas.
   - **Clientes (`clientes`):** Icono `storefront`, directorio general de clientes.
3. **Sistema:**
   - **Configuración (`usuarios`):** Icono `tune`, gestión de usuarios y reglas de programación (visible según rol).
4. **Área Inferior Operativa:**
   - **Gateway WhatsApp:** Tarjeta de estado en tiempo real con indicador animado pulsante (`animate-ping`) en esmeralda y confirmación de conectividad.
   - **Cerrar Sesión (`#btn-logout`):** Botón estilizado con icono `logout` y micro-interacción en tono rose.

---

## 📱 4. Barra de Navegación Móvil (Bottom Navigation Bar)

Ubicada en `public/index.php`:
- Barra fija inferior adaptada a dispositivos móviles (`md:hidden fixed bottom-0`).
- Soporte para áreas seguras (`safe-area-inset-bottom`).
- Estados activos sincronizados con acento esmeralda, tipografía en negrita e iconos con relleno (`filled`).

---

## ⚡ 5. Barra Superior Global (Header)

Ubicada en `public/index.php`:
- Altura estandarizada `h-16`, fondo blanco, borde inferior `border-slate-200`.

### Componentes:
1. **Título de Módulo y Subtabs Dinámicas:**
   - Muestra el título actual o las subpestañas contextuales (`Clientes | Sucursales y Rutas` o `Usuarios | Programación`).
2. **Buscador Global Unificado (`#global-search-input`):**
   - Entrada con icono de búsqueda estilizado y placeholder *"Buscar cliente, dirección o ID..."*.
   - **Exclusión por requerimiento:** Se omite el selector local "Ibagué Hub", manteniendo el buscador como herramienta global limpia.
   - **Sincronización:** Vinculado con el orquestador (`public/js/app.js`), transmitiendo automáticamente la consulta al módulo activo (Clientes, Rutas, Usuarios o Mensajes).
3. **Acciones Globales:**
   - **Botón "Nueva Recolección":** CTA primario en verde esmeralda que dispara la modal `abrirModalProgramarRecoleccion()`.
   - **Centro de Notificaciones:** Botón con icono de campana y punto luminoso activo.
   - **Perfil de Usuario:** Avatar circular con iniciales generadas dinámicamente (`user-avatar-initials`), nombre de usuario (`user-name-display`) y rol logístico (`user-role-display`).

---

## 📅 7. Dashboard de Eventos y Recolecciones (Nuevo Centro de Control)

Basado en la arquitectura visual de `NewView/Eventos/code.html`, se eliminó el selector legacy de pestañas (Día / Semana / Mes) en favor de un panel unificado de alta densidad operativa:

### 1. 📊 4 Tarjetas KPI Superiores
- **Puntos Confirmados:** Conteo de clientes confirmados respecto al total programado, porcentaje de efectividad y badge verde esmeralda.
- **Denegados / Cancelados (Liberados):** Conteo de paradas liberadas o rechazadas para ajuste inmediato de recorridos logísticos.
- **Requieren Atención (Chat Manual):** Alertas de clientes con dudas o consultas pendientes de respuesta con botón de filtrado rápido a la tabla.
- **Automatización WhatsApp:** Estado del servicio automático de mensajería y total de mensajes programados en el día.

### 2. 🎛️ Barra de Filtros y Navegación Temporal (Tira D-0 a D+3)
- **Selector de Ruta:** Desplegable dinámico sincronizado con `/app/api/core/rutas.php` para consolidar o filtrar una ruta individual.
- **Filtro de Estado WA:** Opciones para filtrar paradas confirmadas, en espera, rechazadas o tentativas.
- **Selector de Mes Compacto con Popover de Calendario (`filtroCalendario`):** 
  - Al hacer clic en la etiqueta del selector de fecha/mes, se despliega el popover de calendario interactivo de alta fidelidad basado en `NewView/filtroCalendario/code.html`.
  - Navegación entre meses (anterior/siguiente) y botón de acceso rápido "Hoy".
  - Grilla mensual con conteos de puntos reales por cada día (`/app/api/recolecciones/rango.php`).
  - Al seleccionar cualquier fecha del calendario, se actualiza automáticamente el dashboard de eventos, cargando las recolecciones de ese día y sincronizando la tira de días D-0 a D+3.
  - Cierre automático al hacer clic fuera del popover o en el botón "Cerrar".
- **Tira de Días Esbelta (D-0 a D+3):** Cuatro tarjetas compactas con conteo dinámico de paradas obtenido de `/app/api/recolecciones/rango.php`, barra de progreso y selección visual con anillo esmeralda.
- **Acciones Rápidas:** Botón para exportar datos en CSV/Excel y botón "Programar" para abrir el modal de nueva recolección.

### 3. 📋 Vista de Paradas por Ruta (Tabla Detallada)
- Las recolecciones se agrupan automáticamente por `ruta_id` con tarjeta individual por ruta.
- Cada ruta cuenta con cabecera identificadora (número de ruta, nombre, ciudad y badge de paradas confirmadas sobre el total).
- Filas de tabla con numeración secuencial `#`, datos del cliente, frecuencia, teléfono formateado con botón de copiado de un clic, badge de estado visual y acciones directas (confirmar tentativa, abrir chat WhatsApp en modal Chatwoot, reasignar/programar).

---

## 💬 9. Modal de Mensajería WhatsApp & Mesa de Despacho (3 Paneles Responsivos)

Basado en la arquitectura visual de `NewView/ModalChat/code.html` y optimizado para aprovechar al máximo el ancho de pantalla:

### 1. 📐 Dimensiones y Aprovechamiento de Pantalla
- **Ancho Extendido del Modal:** Se configuró como `w-full h-[94vh] max-h-[960px]` sin límites restrictivos de `max-width`, adaptándose al 100% del contenedor y eliminando cualquier espacio sobrante a los lados.
- **Jerarquía Visual Centrada:** El panel central de chat (`Panel 2`) es el principal con un ancho mínimo de hasta **800px** (`2xl:min-w-[800px] flex-1`), garantizando que la conversación, los mensajes y el área de escritura nunca se compriman.

### 2. 📱 Sistema de Gavetas Responsivas (Drawer Hierarchy)
Cuando el ancho de pantalla disminuye, la interfaz prioriza el chat central siguiendo una jerarquía estricta:
1. **Primer nivel de achicamiento (Pantallas < 1536px):** 
   - La **Ficha del Generador (Panel 3)** se repliega automáticamente.
   - **Trigger por Clic en Avatar:** Al hacer clic en la foto de perfil o nombre del cliente en la cabecera central (`#chatwoot-modal-avatar-header`), se abre/cierra la ficha como una gaveta lateral flotante (`absolute right-0 z-40 shadow-2xl`) con backdrop oscuro. También cuenta con botón dedicado de badge y botón de cierre `close`.
2. **Segundo nivel de achicamiento (Pantallas < 1024px - Móviles y Tablets):**
   - El **Listado de Chats (Panel 1)** se repliega para que el chat central ocupe el 100% de la pantalla.
   - **Trigger por Botón de Chats:** Un botón dedicado en la cabecera central (`#btn-toggle-chats-modal` con icono `menu_open`) abre la gaveta de chats desde la izquierda. Al seleccionar cualquier conversación, la gaveta se cierra automáticamente para enfocar la conversación.
3. **Pantallas Extra Anchas (>= 1536px 2xl):**
   - Los 3 paneles conviven anclados lado a lado: Panel 1 (~320px), Panel 2 Central (~800px a 1060px+) y Panel 3 (~340px).

### 3. 💬 Funcionalidad y Triggers
- **Ventana de 24 horas:** Desbloquea libremente la entrada de texto cuando está activa, o despliega plantillas HSM oficiales cuando está cerrada.
- **Acciones Logísticas:** Confirmación de cita, liberación de parada y programación de recolección integradas en la ficha del cliente.
- **Apertura Global:** Botón *"Mensajes"* en la barra de navegación abre la mesa de despacho con chats a la izquierda y centro/derecha en estado vacío. Cualquier botón de mensaje en tablas abre directamente el chat seleccionado.

---

## 🛠️ 10. Compilación y Optimización CSS

- La hoja de estilos principal se procesa desde `public/css/input.css` hacia `public/css/output.css` usando `@tailwindcss/cli v4`.
- Comando de compilación optimizado y minificado:
  ```bash
  npx @tailwindcss/cli -i public/css/input.css -o public/css/output.css --minify
  ```

---

## 🏢 11. Filtro Operativo por Sucursal y Limpieza de Estado WA

- **Select Filtrable por Sucursal:**
  - En lugar de un simple input de texto, se implementó un **Select interactivo con buscador integrado**.
  - Muestra la opción actualmente elegida (ej. *Todas las sucursales* o una sucursal específica) con separación y holgura clara entre el icono y el texto (`pl-10 pr-8`).
  - Al abrir el selector, despliega un menú flotante con un campo de búsqueda donde el usuario puede escribir para filtrar en vivo la lista de sucursales disponibles y seleccionar la deseada.
- **Agrupación Visual de Recolecciones:**
  - La visualización de paradas y recolecciones del día se organiza y agrupa por **Sucursal** (ej. *Sucursal Ibagué*, etc.) con su respectivo conteo de puntos y porcentaje de confirmación.
- **Selector de Estado WA:**
  - Se eliminó el icono de mensaje (`chat_bubble`) para mantener una interfaz sobria y limpia, conservando las opciones desplegables con padding balanceado.
- **Modal de Chat (Chatwoot):**
  - Se removió completamente la regla `max-width: 900px` de `#modal-chatwoot-panel` en `public/css/input.css` y se recompiló en `public/css/output.css`, permitiendo que el modal ocupe todo el ancho disponible de la pantalla.

---

## 📋 12. Modal de Programación y Edición de Recolecciones (`formRecoleccion`)

- **Diseño Dialog Sheet Moderno:**
  - Se integró el diseño de [`NewView/formRecoleccion/`](file:///var/www/SyncPoint/NewView/formRecoleccion/) para la creación y edición de recolecciones.
  - **Encabezado y Módulo D-3:** Iconografía limpia `calendar_add_on`, título dinámico según acción (crear o editar) y badge `MÓDULO D-3`.
  - **Fila 1 (Sucursal y Ruta):** Selectores estructurados con íconos vectoriales `domain` y `alt_route`.
  - **Fila 2 (Cliente y Ficha de Ciclo):** Búsqueda asistida en tiempo real y panel dinámico con historial de ciclo (`Próxima Visita`, `Teléfono WhatsApp`, `Sucursal/Ciudad`, `Frecuencia`).
  - **Fila 3 (Modalidad de Programación):** Tarjetas interactivas de selección rápida:
    1. *Solo por esta vez (Parada Extraordinaria / Única)*
    2. *Modificar Recurrencia (Actualiza fecha base y recalcula ciclo periódico)*
  - **Fila 4 (Fecha Asignada):** Selector con badge reactivo que formatea el día y fecha textual.
  - **Acción Unificada:** El formulario maneja creación (`POST /api/core/eventos.php`), edición (`PUT /api/core/eventos.php`) y recálculo recurrente (`POST /api/eventos/recalcular.php`).



