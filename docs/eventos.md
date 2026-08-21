# 📅 Documentación API - Gestión de Eventos y Servicio de Recálculo (`eventos`)

Esta documentación describe la estructura, las restricciones del CRUD de **Eventos** (`eventos`), y la especificación técnica del **Servicio de Recálculo de Eventos por Cambio de Frecuencia** (`app/api/eventos/recalcular.php`).

---

## 📌 1. Esquema de la Tabla (`eventos`) y Restricciones

```sql
CREATE TABLE SyncPoint.eventos (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT REFERENCES SyncPoint.clientes(id) ON DELETE SET NULL,
    ruta_id BIGINT REFERENCES SyncPoint.rutas(id) ON DELETE SET NULL,
    fecha_programada DATE NOT NULL,
    estado VARCHAR(50) DEFAULT 'programado' NOT NULL,
    tipo VARCHAR(50) DEFAULT 'frecuente' NOT NULL,
    notificaciones JSONB DEFAULT '[]'::jsonb,
    evento_origin VARCHAR(20) DEFAULT 'sistem' NOT NULL,
    created_at DATE DEFAULT CURRENT_DATE,
    update_at DATE DEFAULT CURRENT_DATE
);
```

### 🔒 Restricciones de Valores de Campos (Enum / Check List)

| Campo | Tipo | Valores Permitidos / Restringidos | Valor por Defecto | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `int8` | Autoincremental (`BIGSERIAL`) | - | Identificador único del evento. |
| `cliente_id` | `int8` | FK a `clientes(id)` | `NULL` | ID del cliente al que pertenece la recolección/evento. |
| `ruta_id` | `int8` | FK a `rutas(id)` | `NULL` | ID de la ruta o zona asignada. |
| `fecha_programada` | `DATE` | Cadena `YYYY-MM-DD` | **Requerido** | Fecha en la que está agendada la recolección. |
| `estado` | `string` | `'programado'`, `'notificacion1'`, `'notificacion2'`, `'notificacion3'`, `'aceptada'`, `'denegada'`, `'no_respondida'`, `'error'` | `'programado'` | Estado del ciclo de notificación y confirmación del evento. |
| `tipo` | `string` | `'frecuente'`, `'reprogramada'`, `'unica'` | `'frecuente'` | Tipo de recolección programada. |
| `notificaciones` | `json` | Objeto/Array JSON | `[]` | Historial / Log de notificaciones enviadas y cambios de estado. |
| `evento_origin` | `string` | `'user'`, `'sistem'` | `'sistem'` | Origen del evento: `'user'` (creado manualmente por un usuario) o `'sistem'` (generado automáticamente por el programa). |

---

## 🚀 2. Endpoints Core del CRUD (`/app/api/core/eventos.php`)

### 1. 🔍 Obtener Lista de Eventos (Paginada y Filtrada)
* **Método**: `GET`
* **URL**: `/app/api/core/eventos.php`
* **Query Params**: `q`, `cliente_id`, `ruta_id`, `fecha`, `estado`, `tipo`, `page`, `limit`

#### Ejemplo cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/eventos.php?fecha=2026-08-20&estado=programado" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

---

### 2. ➕ Crear un Nuevo Evento
* **Método**: `POST`
* **URL**: `/app/api/core/eventos.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)
```json
{
  "cliente_id": 539,
  "ruta_id": 2,
  "fecha_programada": "2026-08-20",
  "estado": "programado",
  "tipo": "frecuente",
  "evento_origin": "user",
  "notificaciones": [
    {
      "timestamp": "2026-08-17T22:08:44-05:00",
      "accion": "creacion_manual",
      "origen": "user",
      "detalle": "Evento creado manualmente desde el panel de control"
    }
  ]
}
```

---

### 3. ✏️ Actualizar un Evento Existente
* **Método**: `PUT`
* **URL**: `/app/api/core/eventos.php?id={ID}`
* **Header**: `Content-Type: application/json`

```json
{
  "estado": "aceptada",
  "notificaciones": [
    {
      "timestamp": "2026-08-17T22:15:00-05:00",
      "accion": "respuesta_cliente",
      "origen": "user",
      "detalle": "Cliente confirmó asistencia vía WhatsApp"
    }
  ]
}
```

---

## ⚡ 3. Servicio de Recálculo por Cambio de Frecuencia (`/app/api/eventos/recalcular.php`)

Este servicio especial permite que, cuando un usuario cambie la frecuencia de recolección de un cliente (o modifique su fecha de ciclo), el sistema **recalcule automáticamente las fechas de todos los eventos futuros grabados en la tabla `eventos`** a partir de la fecha seleccionada.

### 📌 ¿Cómo funciona la lógica del servicio?

1. **Parámetros recibidos**:
   * `cliente_id` (Requerido): ID del cliente.
   * `fecha_cambio` (Requerido): Fecha (`YYYY-MM-DD`) a partir de la cual se aplica la nueva frecuencia.
   * `frecuencia_id` o `dias` (Requerido): La nueva frecuencia de días (ej: 15 días).
   * `evento_origin`: `'user'` o `'sistem'` (por defecto `'user'`).

2. **Acciones del backend**:
   * Actualiza el registro del cliente en la tabla `clientes`: asigna `fecha_base = fecha_cambio` y `frecuencia_id = frecuencia_id`.
   * Consulta los eventos guardados en la tabla `eventos` con `cliente_id = :id` y `fecha_programada >= fecha_cambio` ordenados cronológicamente.
   * **Si existen eventos futuros**: Recalcula sus fechas en secuencia:
     * Evento 0: `fecha_cambio`
     * Evento 1: `fecha_cambio + (1 * nuevos_dias)`
     * Evento 2: `fecha_cambio + (2 * nuevos_dias)`
     * ...
     * Actualiza cada evento e inserta una traza de recálculo en la columna `notificaciones` JSON.
   * **Si NO existen eventos futuros**: Genera automáticamente 6 eventos proyectados para los próximos ciclos partiendo de `fecha_cambio`.

---

### 🌐 Especificación del Endpoint de Recálculo

* **Método**: `POST`
* **URL**: `/app/api/eventos/recalcular.php`
* **Header**: `Content-Type: application/json`

#### Ejemplo de Cuerpo de Petición (JSON Body)
```json
{
  "cliente_id": 539,
  "fecha_cambio": "2026-08-20",
  "frecuencia_id": 3,
  "dias": 15,
  "evento_origin": "user"
}
```

#### Ejemplo de Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Eventos recalculados exitosamente tras cambio de frecuencia",
  "data": {
    "cliente_id": 539,
    "fecha_cambio": "2026-08-20",
    "nuevos_dias_frecuencia": 15,
    "eventos_modificados": 4,
    "eventos_creados": 0
  }
}
```

---

## 🎨 4. Guía para el Frontend (Integración en Interfaz)

Cuando un desarrollador del frontend o un modal modifique la frecuencia o fecha base de un cliente:

### Pasos que debe realizar el Frontend:

1. **Al guardar en el Modal de Editar Cliente**:
   Si el usuario modifica el campo **Frecuencia** (`frecuencia_id`) o el campo **Fecha Base** (`fecha_base`):

   ```javascript
   // 1. Guardar o actualizar datos del cliente mediante el CRUD normal
   const resCliente = await fetch('/app/api/core/clientes.php?id=' + clienteId, {
       method: 'PUT',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({
           frecuencia_id: nuevaFrecuenciaId,
           fecha_base: nuevaFechaBase
       })
   });

   // 2. Disparar el recálculo automático de eventos futuros
   const resRecalculo = await fetch('/app/api/eventos/recalcular.php', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({
           cliente_id: clienteId,
           fecha_cambio: nuevaFechaBase, // ej: "2026-08-20"
           frecuencia_id: nuevaFrecuenciaId,
           evento_origin: 'user'
       })
   });

   const dataRecalculo = await resRecalculo.json();
   if (dataRecalculo.success) {
       console.log(`Se reprogramaron ${dataRecalculo.data.eventos_modificados} eventos existentes.`);
       recargarDiaActual(); // Recargar calendario/dashboard
   }
   ```

2. **Al Agendar un Evento Tentativo desde el Dashboard**:
   El frontend debe enviar el estado `'programado'`, tipo `'frecuente'`, y `evento_origin: 'user'`:

    ```javascript
    await fetch('/app/api/core/eventos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cliente_id: clienteId,
            ruta_id: rutaId,
            fecha_programada: fechaProgramada,
            estado: 'programado',
            tipo: 'frecuente',
            evento_origin: 'user'
        })
    });
    ```

---

## 🧮 4. Nuevos Servicios de Cálculo y Agendamiento en Lote (`EventCalculatorService`)

### 1. 🔢 Calcular Fechas por Cantidad de Ciclos (`/app/api/eventos/calcular_cantidad.php`)
Calcula las próximas $N$ fechas proyectadas según la frecuencia configurada en el cliente.

* **Método**: `POST` / `GET`
* **URL**: `/app/api/eventos/calcular_cantidad.php`
* **Parámetros**:
  - `cliente_id` (`int`, Requerido): ID del cliente.
  - `fecha_inicio` (`string YYYY-MM-DD`, Opcional): Fecha de partida (por defecto la `fecha_base` del cliente o el día de hoy).
  - `cantidad` (`int`, Opcional): Cantidad de recolecciones a proyectar (por defecto `6`).

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/eventos/calcular_cantidad.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"cliente_id": 539, "cantidad": 4}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Fechas proyectadas por cantidad calculadas correctamente.",
  "data": {
    "cliente_id": 539,
    "cliente_nombre": "Restaurante El Sol",
    "frecuencia_nombre": "Quincenal",
    "frecuencia_dias": 15,
    "fecha_inicio": "2026-08-20",
    "cantidad": 4,
    "fechas": [
      "2026-08-20",
      "2026-09-04",
      "2026-09-19",
      "2026-10-04"
    ]
  }
}
```

---

### 2. 📅 Calcular Fechas por Rango de Fechas (`/app/api/eventos/calcular_rango.php`)
Calcula todas las fechas periódicas de recolección que caen dentro de un intervalo de fechas `desde` y `hasta`.

* **Método**: `POST` / `GET`
* **URL**: `/app/api/eventos/calcular_rango.php`
* **Parámetros**:
  - `cliente_id` (`int`, Requerido): ID del cliente.
  - `desde` / `fecha_desde` (`string YYYY-MM-DD`, Requerido): Fecha inicial del rango.
  - `hasta` / `fecha_hasta` (`string YYYY-MM-DD`, Requerido): Fecha final del rango.

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/eventos/calcular_rango.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"cliente_id": 539, "desde": "2026-08-20", "hasta": "2026-10-31"}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Fechas proyectadas por rango calculadas correctamente.",
  "data": {
    "cliente_id": 539,
    "cliente_nombre": "Restaurante El Sol",
    "frecuencia_nombre": "Quincenal",
    "frecuencia_dias": 15,
    "fecha_desde": "2026-08-20",
    "fecha_hasta": "2026-10-31",
    "total_fechas": 5,
    "fechas": [
      "2026-08-20",
      "2026-09-04",
      "2026-09-19",
      "2026-10-04",
      "2026-10-19"
    ]
  }
}
```

---

### 3. 📌 Agendar Fechas en Lote / Bulk (`/app/api/eventos/agendar_lote.php`)
Registra las fechas calculadas e inserta los eventos en la tabla `eventos`, previniendo duplicados si la fecha ya estaba agendada para ese cliente.

* **Método**: `POST`
* **URL**: `/app/api/eventos/agendar_lote.php`
* **Cuerpo de la Petición**:
  - `cliente_id` (`int`, Requerido)
  - `fechas` (`array of YYYY-MM-DD`, Requerido)
  - `ruta_id` (`int`, Opcional)
  - `estado` (`string`, Opcional, por defecto `'programado'`)
  - `tipo` (`string`, Opcional, por defecto `'frecuente'`)
  - `evento_origin` (`string/int`, Opcional, por defecto `'user'`)

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/eventos/agendar_lote.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{
    "cliente_id": 539,
    "fechas": ["2026-08-20", "2026-09-04", "2026-09-19"],
    "estado": "programado",
    "tipo": "frecuente"
  }'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Fechas agendadas correctamente en lote.",
  "data": {
    "cliente_id": 539,
    "cliente_nombre": "Restaurante El Sol",
    "eventos_creados": 3,
    "eventos_existentes": 0,
    "eventos": [
      { "id": 101, "fecha_programada": "2026-08-20" },
      { "id": 102, "fecha_programada": "2026-09-04" },
      { "id": 103, "fecha_programada": "2026-09-19" }
    ]
  }
}
```

---

### 4. 🌐 Programación Global Masiva por Horizonte de Días (`/app/api/eventos/programar_global.php`)
Identifica la fecha agendada más lejana en la base de datos, proyecta los días faltantes para completar el horizonte especificado (por defecto 30 días), ajusta cada fecha al día de la semana que indique el nombre de la Ruta asignada al cliente y agenda masivamente los eventos faltantes.

* **Método**: `POST`
* **URL**: `/app/api/eventos/programar_global.php`
* **Cuerpo de la Petición**:
  - `dias_horizonte` (`int`, Opcional): Días a proyectar desde la fecha más lejana (por defecto `30`).

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/eventos/programar_global.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"dias_horizonte": 30}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Eventos programados masivamente correctamente para los próximos 30 días.",
  "data": {
    "fecha_mas_lejana_actual": "2026-08-20",
    "fecha_proyectada_hasta": "2026-09-19",
    "dias_horizonte": 30,
    "clientes_procesados": 12,
    "total_eventos_creados": 24,
    "total_eventos_existentes": 5,
    "detalle": [
      {
        "cliente_id": 539,
        "nombre": "Restaurante El Sol",
        "ruta": "Ruta Lunes",
        "fechas_agendadas": 2
      }
    ]
  }
}
```
