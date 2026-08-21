# 🗺️ Documentación API - Rutas (Zonas) (`core/rutas.php`)

Esta API permite realizar la gestión completa (CRUD) de las **Rutas/Zonas**, incluyendo filtrado por nombre, ciudad y sucursal asociada (`fk_sucursal`), además de paginación server-side.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/rutas.php` (`class Ruta`)
* **Servicio**: `app/services/core/rutas.php` (`class RutaService`)
* **Controlador**: `app/controllers/core/rutas.php` (`class RutaController`)
* **Endpoint API**: `app/api/core/rutas.php`

---

## 📌 Información General

* **Base URL**: `/app/api/core/rutas.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Rutas (Paginada y Filtrada)

Retorna la lista de rutas registradas que coincidan con los filtros aplicados.

* **Método**: `GET`
* **URL**: `/app/api/core/rutas.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Término de búsqueda por nombre de la ruta o ciudad. | `Jueves` |
| `sucursal_id` / `fk_sucursal` | `int` | No | ID de la sucursal para filtrar sus rutas asociadas. | `1` |
| `ciudad` | `string` | No | Filtrar por el nombre de la ciudad. | `Ibagué` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (por defecto `10`). | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/rutas.php?sucursal_id=1&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "nombre": "Jueves",
      "ciudad": "Ibagué",
      "fk_sucursal": 1,
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

### 2. 👁️ Obtener Detalles de una Ruta por ID

* **Método**: `GET`
* **URL**: `/app/api/core/rutas.php?id={ID}`

#### Parámetros Query
* `id` (`int`, Requerido): ID único de la ruta.

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/rutas.php?id=2" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 2,
    "nombre": "Jueves",
    "ciudad": "Ibagué",
    "fk_sucursal": 1,
    "sucursal_nombre": "Ibagué principal"
  }
}
```

---

### 3. ➕ Crear una Nueva Ruta

* **Método**: `POST`
* **URL**: `/app/api/core/rutas.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `nombre` | `string` | **Sí** | Nombre de la ruta (ej: "Ruta Viernes Centro"). |
| `ciudad` | `string` | **Sí** | Ciudad de la ruta. |
| `fk_sucursal` | `int` | **Sí** | ID de la sucursal a la que pertenece la ruta. |

#### Ejemplo JSON Body
```json
{
  "nombre": "Ruta Sabatina",
  "ciudad": "Ibagué",
  "fk_sucursal": 1
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/core/rutas.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"nombre": "Ruta Sabatina", "ciudad": "Ibagué", "fk_sucursal": 1}'
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Ruta creada exitosamente",
  "id": 15
}
```

---

### 4. ✏️ Actualizar una Ruta Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/rutas.php?id={ID}`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `id` | `int` | Opcional si va en la URL | ID de la ruta. |
| `nombre` | `string` | **Sí** | Nuevo nombre de la ruta. |
| `ciudad` | `string` | **Sí** | Nueva ciudad de la ruta. |
| `fk_sucursal` | `int` | **Sí** | Nuevo ID de sucursal asignada. |

#### Ejemplo JSON Body
```json
{
  "nombre": "Ruta Sabatina Especial",
  "ciudad": "Ibagué",
  "fk_sucursal": 1
}
```

#### Ejemplo cURL
```bash
curl -X PUT "http://localhost:1019/app/api/core/rutas.php?id=15" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"nombre": "Ruta Sabatina Especial", "ciudad": "Ibagué", "fk_sucursal": 1}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Ruta actualizada exitosamente"
}
```

---

### 5. 🗑️ Eliminar una Ruta

* **Método**: `DELETE`
* **URL**: `/app/api/core/rutas.php?id={ID}`

#### Ejemplo cURL
```bash
curl -X DELETE "http://localhost:1019/app/api/core/rutas.php?id=15" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Ruta eliminada exitosamente"
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `405 Method Not Allowed` | Método HTTP no permitido. | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de validación o fallo de servidor. | `{"success": false, "message": "Error: La sucursal asignada (fk_sucursal) es obligatoria."}` |
