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
