# 💬 Documentación API - Mensajes (`core/mensajes.php`)

Esta API permite realizar la gestión completa (CRUD) de la tabla de **Mensajes** (`mensajes`) registrada en la base de datos para seguimiento e integración con Chatwoot.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/mensajes.php` (`class Mensaje`)
* **Servicio**: `app/services/core/mensajes.php` (`class MensajeService`)
* **Controlador**: `app/controllers/core/mensajes.php` (`class MensajeController`)
* **Endpoint API**: `app/api/core/mensajes.php`

---

## 📌 Esquema de la Tabla (`mensajes`)

```sql
CREATE TABLE bless_app.mensajes (
	id bigserial NOT NULL,
	chatwoot_conversation_id varchar(100) DEFAULT NULL::character varying NULL,
	fecha_actualizacion timestamptz DEFAULT CURRENT_TIMESTAMP NULL,
	estado varchar NULL,
	CONSTRAINT idx_27555_primary PRIMARY KEY (id)
);
```

---

## 📌 Información General

* **Base URL**: `/app/api/core/mensajes.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Mensajes (Paginada y Filtrada)

Retorna la lista de registros de la tabla `mensajes` que coincidan con los filtros de búsqueda y paginación.

* **Método**: `GET`
* **URL**: `/app/api/core/mensajes.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Búsqueda por ID de conversación de Chatwoot o estado. | `591` |
| `estado` | `string` | No | Filtrar por estado del mensaje (ej: `enviado`, `pendiente`). | `enviado` |
| `chatwoot_conversation_id` | `string` | No | Filtrar por ID específico de conversación en Chatwoot. | `591` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (por defecto `10`). | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/mensajes.php?chatwoot_conversation_id=591&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "chatwoot_conversation_id": "591",
      "fecha_actualizacion": "2026-08-14 12:00:00-05",
      "estado": "enviado"
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

### 2. 👁️ Obtener Detalles de un Mensaje por ID

* **Método**: `GET`
* **URL**: `/app/api/core/mensajes.php?id={ID}`

#### Parámetros Query
* `id` (`int`, Requerido): ID único del registro de mensaje.

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/mensajes.php?id=1" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "chatwoot_conversation_id": "591",
    "fecha_actualizacion": "2026-08-14 12:00:00-05",
    "estado": "enviado"
  }
}
```

---

### 3. ➕ Crear un Nuevo Registro de Mensaje

* **Método**: `POST`
* **URL**: `/app/api/core/mensajes.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `chatwoot_conversation_id` | `string` | No | ID de la conversación en Chatwoot (ej: `"591"`). |
| `estado` | `string` | No | Estado del mensaje (ej: `'enviado'`). Por defecto `'enviado'`. |

#### Ejemplo JSON Body
```json
{
  "chatwoot_conversation_id": "591",
  "estado": "enviado"
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/core/mensajes.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{
    "chatwoot_conversation_id": "591",
    "estado": "enviado"
  }'
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Registro de mensaje creado exitosamente",
  "id": 15
}
```

---

### 4. ✏️ Actualizar un Registro de Mensaje Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/mensajes.php?id={ID}`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `id` | `int` | Opcional en body si va en la URL | ID del registro de mensaje. |
| `chatwoot_conversation_id` | `string` | No | Nuevo ID de conversación de Chatwoot. |
| `estado` | `string` | No | Nuevo estado del mensaje. |

#### Ejemplo JSON Body
```json
{
  "chatwoot_conversation_id": "591",
  "estado": "procesado"
}
```

#### Ejemplo cURL
```bash
curl -X PUT "http://localhost:1019/app/api/core/mensajes.php?id=15" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"chatwoot_conversation_id": "591", "estado": "procesado"}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Registro de mensaje actualizado exitosamente"
}
```

---

### 5. 🗑️ Eliminar un Registro de Mensaje

* **Método**: `DELETE`
* **URL**: `/app/api/core/mensajes.php?id={ID}`

#### Ejemplo cURL
```bash
curl -X DELETE "http://localhost:1019/app/api/core/mensajes.php?id=15" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Registro de mensaje eliminado exitosamente"
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `405 Method Not Allowed` | Método HTTP no permitido. | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de servidor. | `{"success": false, "message": "Error: No se pudo actualizar el registro de mensaje."}` |
