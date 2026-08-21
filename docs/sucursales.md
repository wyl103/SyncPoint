# 🏢 Documentación API - Sucursales (`core/sucursales.php`)

Esta API permite realizar la gestión completa (CRUD) de las **Sucursales**, incluyendo filtrado por nombre y estado destacado, además de paginación server-side.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/sucursales.php` (`class Sucursal`)
* **Servicio**: `app/services/core/sucursales.php` (`class SucursalService`)
* **Controlador**: `app/controllers/core/sucursales.php` (`class SucursalController`)
* **Endpoint API**: `app/api/core/sucursales.php`

---

## 📌 Información General

* **Base URL**: `/app/api/core/sucursales.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Sucursales (Paginada y Filtrada)

Retorna la lista de sucursales que coincidan con los filtros de búsqueda y paginación.

* **Método**: `GET`
* **URL**: `/app/api/core/sucursales.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Término de búsqueda por nombre de la sucursal. | `Ibagué` |
| `destacada` | `int` | No | Filtrar por sucursal destacada (`1` para destacada, `0` para no destacada). | `1` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (por defecto `10`). | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/sucursales.php?q=Ibague&destacada=1&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Ibagué principal",
      "destacada": 1
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

### 2. 👁️ Obtener Detalles de una Sucursal por ID

* **Método**: `GET`
* **URL**: `/app/api/core/sucursales.php?id={ID}`

#### Parámetros Query
* `id` (`int`, Requerido): ID único de la sucursal.

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/sucursales.php?id=1" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Ibagué principal",
    "destacada": 1
  }
}
```

---

### 3. ➕ Crear una Nueva Sucursal

* **Método**: `POST`
* **URL**: `/app/api/core/sucursales.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `nombre` | `string` | **Sí** | Nombre de la nueva sucursal. |
| `destacada` | `int` | No | Indica si la sucursal es destacada (`1` o `0`). Por defecto `0`. |

#### Ejemplo JSON Body
```json
{
  "nombre": "Sucursal Neiva Norte",
  "destacada": 1
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/core/sucursales.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"nombre": "Sucursal Neiva Norte", "destacada": 1}'
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Sucursal creada exitosamente",
  "id": 12
}
```

---

### 4. ✏️ Actualizar una Sucursal Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/sucursales.php?id={ID}`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `id` | `int` | Opcional en body si va en la URL | ID de la sucursal. |
| `nombre` | `string` | **Sí** | Nuevo nombre de la sucursal. |
| `destacada` | `int` | **Sí** | Estado destacado (`1` o `0`). |

#### Ejemplo JSON Body
```json
{
  "nombre": "Sucursal Neiva Centro",
  "destacada": 0
}
```

#### Ejemplo cURL
```bash
curl -X PUT "http://localhost:1019/app/api/core/sucursales.php?id=12" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"nombre": "Sucursal Neiva Centro", "destacada": 0}'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Sucursal actualizada exitosamente"
}
```

---

### 5. 🗑️ Eliminar una Sucursal

* **Método**: `DELETE`
* **URL**: `/app/api/core/sucursales.php?id={ID}`

#### Ejemplo cURL
```bash
curl -X DELETE "http://localhost:1019/app/api/core/sucursales.php?id=12" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Sucursal eliminada exitosamente"
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `405 Method Not Allowed` | Método HTTP no permitido. | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de validación o fallo de servidor. | `{"success": false, "message": "Error: El nombre de la sucursal es obligatorio."}` |
