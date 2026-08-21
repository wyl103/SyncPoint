# 🔄 Documentación API - Frecuencias (`core/frecuencias.php`)

Esta API permite realizar la gestión completa (CRUD) de las **Frecuencias de Recolección**, incluyendo filtrado por nombre y número de días, además de paginación server-side.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/frecuencias.php` (`class Frecuencia`)
* **Servicio**: `app/services/core/frecuencias.php` (`class FrecuenciaService`)
* **Controlador**: `app/controllers/core/frecuencias.php` (`class FrecuenciaController`)
* **Endpoint API**: `app/api/core/frecuencias.php`

---

## 📌 Información General

* **Base URL**: `/app/api/core/frecuencias.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Frecuencias (Paginada y Filtrada)

Retorna la lista de frecuencias que coincidan con los filtros de búsqueda y paginación.

* **Método**: `GET`
* **URL**: `/app/api/core/frecuencias.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Término de búsqueda por nombre de la frecuencia. | `Mensual` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (por defecto `10`). | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/frecuencias.php?q=Mensual&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Mensual",
      "dias": 30
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

### 2. 👁️ Obtener Detalles de una Frecuencia por ID

* **Método**: `GET`
* **URL**: `/app/api/core/frecuencias.php?id={ID}`

#### Parámetros Query
* `id` (`int`, Requerido): ID único de la frecuencia.

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/frecuencias.php?id=1" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Mensual",
    "dias": 30
  }
}
```

---

### 3. ➕ Crear una Nueva Frecuencia

* **Método**: `POST`
* **URL**: `/app/api/core/frecuencias.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `nombre` | `string` | **Sí** | Nombre descriptivo de la frecuencia (ej: "Quincenal"). |
| `dias` | `int` | **Sí** | Número de días entre recolecciones (ej: `15`). |

#### Ejemplo JSON Body
```json
{
  "nombre": "Quincenal",
  "dias": 15
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/core/frecuencias.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"nombre": "Quincenal", "dias": 15}'
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Frecuencia creada exitosamente",
  "id": 5
}
```

---

### 4. ✏️ Actualizar una Frecuencia Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/frecuencias.php?id={ID}`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `id` | `int` | Opcional en body si va en la URL | ID de la frecuencia. |
| `nombre` | `string` | **Sí** | Nuevo nombre de la frecuencia. |
| `dias` | `int` | **Sí** | Nuevo número de días. |

#### Ejemplo JSON Body
```json
{
  "nombre": "Bimensual",
  "dias": 60
}
```

#### Ejemplo cURL
```bash
curl -X PUT "http://localhost:1019/app/api/core/frecuencias.php?id=5" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"nombre": "Bimensual", "dias": 60}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Frecuencia actualizada exitosamente"
}
```

---

### 5. 🗑️ Eliminar una Frecuencia

* **Método**: `DELETE`
* **URL**: `/app/api/core/frecuencias.php?id={ID}`

#### Ejemplo cURL
```bash
curl -X DELETE "http://localhost:1019/app/api/core/frecuencias.php?id=5" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Frecuencia eliminada exitosamente"
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `405 Method Not Allowed` | Método HTTP no permitido. | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de validación o fallo de servidor. | `{"success": false, "message": "Error: El número de días es obligatorio."}` |
