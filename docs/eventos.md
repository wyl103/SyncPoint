# 📅 Documentación API - Eventos / Recolecciones (`core/eventos.php`)

Esta API permite realizar la gestión completa (CRUD) de la tabla de **Eventos** (`eventos`), correspondiente a la programación de recolecciones, estados, notificaciones JSON y seguimiento de eventos originados.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/eventos.php` (`class Evento`)
* **Servicio**: `app/services/core/eventos.php` (`class EventoService`)
* **Controlador**: `app/controllers/core/eventos.php` (`class EventoController`)
* **Endpoint API**: `app/api/core/eventos.php`

---

## 📌 Esquema de la Tabla (`eventos`)

```sql
CREATE TABLE pruebas.eventos (
	id int8 DEFAULT nextval('pruebas.recolecciones_id_seq'::regclass) NOT NULL,
	cliente_id int8 NULL,
	ruta_id int8 NULL,
	fecha_programada date NOT NULL,
	estado pruebas.recolecciones_estado DEFAULT 'pendiente'::pruebas.recolecciones_estado NULL,
	tipo text NULL,
	notificaciones json NULL,
	evento_origin int8 NULL,
	created_at date NULL,
	update_at date NULL,
	CONSTRAINT idx_27564_primary PRIMARY KEY (id),
	CONSTRAINT recolecciones_fecha_programada_not_null NOT NULL fecha_programada,
	CONSTRAINT recolecciones_id_not_null NOT NULL id,
	CONSTRAINT recolecciones_ibfk_1 FOREIGN KEY (cliente_id) REFERENCES pruebas.clientes(id),
	CONSTRAINT recolecciones_ibfk_2 FOREIGN KEY (ruta_id) REFERENCES pruebas.rutas(id)
);
```

---

## 📌 Información General

* **Base URL**: `/app/api/core/eventos.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Eventos (Paginada y Filtrada)

Retorna la lista de eventos o recolecciones registradas que coincidan con los filtros aplicados.

* **Método**: `GET`
* **URL**: `/app/api/core/eventos.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Búsqueda por nombre de cliente, ruta o tipo de evento. | `Samys` |
| `cliente_id` | `int` | No | Filtrar por ID del cliente. | `539` |
| `ruta_id` | `int` | No | Filtrar por ID de la ruta. | `2` |
| `fecha` / `fecha_programada` | `string` (date) | No | Filtrar por fecha exacta (`YYYY-MM-DD`). | `2026-08-15` |
| `estado` | `string` | No | Estado del evento (`pendiente`, `completada`, `cancelada`, etc.). | `pendiente` |
| `tipo` | `string` | No | Tipo de evento o recolección. | `programada` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (por defecto `10`). | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/eventos.php?fecha=2026-08-15&estado=pendiente&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "cliente_id": 539,
      "ruta_id": 2,
      "fecha_programada": "2026-08-15",
      "estado": "pendiente",
      "tipo": "recoleccion_ordinaria",
      "notificaciones": {
        "whatsapp_enviado": true,
        "fecha_notificacion": "2026-08-14"
      },
      "evento_origin": null,
      "created_at": "2026-08-14",
      "update_at": "2026-08-14",
      "cliente_nombre": "Comidas Rápidas Samys",
      "cliente_telefono": "573106288747",
      "ruta_nombre": "Jueves",
      "ruta_ciudad": "Ibagué",
      "sucursal_id": 1,
      "sucursal_nombre": "Ibagué principal"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 1,
    "total_pages": 1
  }
}
```

---

### 2. 👁️ Obtener Detalles de un Evento por ID

* **Método**: `GET`
* **URL**: `/app/api/core/eventos.php?id={ID}`

#### Parámetros Query
* `id` (`int`, Requerido): ID único del evento.

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/eventos.php?id=101" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 101,
    "cliente_id": 539,
    "ruta_id": 2,
    "fecha_programada": "2026-08-15",
    "estado": "pendiente",
    "tipo": "recoleccion_ordinaria",
    "notificaciones": {
      "whatsapp_enviado": true
    },
    "evento_origin": null,
    "created_at": "2026-08-14",
    "update_at": "2026-08-14",
    "cliente_nombre": "Comidas Rápidas Samys",
    "cliente_telefono": "573106288747",
    "ruta_nombre": "Jueves",
    "ruta_ciudad": "Ibagué",
    "sucursal_id": 1,
    "sucursal_nombre": "Ibagué principal"
  }
}
```

---

### 3. ➕ Crear un Nuevo Evento

* **Método**: `POST`
* **URL**: `/app/api/core/eventos.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `cliente_id` | `int` | No | ID del cliente. |
| `ruta_id` | `int` | No | ID de la ruta. |
| `fecha_programada` | `string` (date) | **Sí** | Fecha de programación en formato `YYYY-MM-DD`. |
| `estado` | `string` | No | Estado del evento. Por defecto `'pendiente'`. |
| `tipo` | `string` | No | Tipo o categoría del evento. |
| `notificaciones` | `object` / `json` | No | Objeto JSON con estado de notificaciones enviadas. |
| `evento_origin` | `int` | No | ID del evento origen si fue reprogramado. |

#### Ejemplo JSON Body
```json
{
  "cliente_id": 539,
  "ruta_id": 2,
  "fecha_programada": "2026-08-20",
  "estado": "pendiente",
  "tipo": "recoleccion_ordinaria",
  "notificaciones": {
    "aviso_enviado": true
  }
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/core/eventos.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{
    "cliente_id": 539,
    "ruta_id": 2,
    "fecha_programada": "2026-08-20",
    "estado": "pendiente",
    "tipo": "recoleccion_ordinaria"
  }'
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Evento creado exitosamente",
  "id": 102
}
```

---

### 4. ✏️ Actualizar un Evento Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/eventos.php?id={ID}`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `id` | `int` | Opcional en body si va en la URL | ID del evento. |
| `cliente_id` | `int` | No | Nuevo ID de cliente. |
| `ruta_id` | `int` | No | Nuevo ID de ruta. |
| `fecha_programada` | `string` (date) | No | Nueva fecha programada (`YYYY-MM-DD`). |
| `estado` | `string` | No | Nuevo estado del evento (ej: `'completada'`). |
| `tipo` | `string` | No | Nuevo tipo de evento. |
| `notificaciones` | `object` / `json` | No | Objeto de notificaciones actualizado. |
| `evento_origin` | `int` | No | ID del evento origen. |

#### Ejemplo JSON Body
```json
{
  "estado": "completada"
}
```

#### Ejemplo cURL
```bash
curl -X PUT "http://localhost:1019/app/api/core/eventos.php?id=102" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"estado": "completada"}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Evento actualizado exitosamente"
}
```

---

### 5. 🗑️ Eliminar un Evento

* **Método**: `DELETE`
* **URL**: `/app/api/core/eventos.php?id={ID}`

#### Ejemplo cURL
```bash
curl -X DELETE "http://localhost:1019/app/api/core/eventos.php?id=102" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Evento eliminado exitosamente"
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `405 Method Not Allowed` | Método HTTP no permitido. | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de validación o servidor. | `{"success": false, "message": "Error: La fecha programada (fecha_programada) es obligatoria."}` |
