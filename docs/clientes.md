# 👥 Documentación API - Clientes (`core/clientes.php`)

Esta API permite realizar la gestión completa (CRUD) de los **Clientes**, incluyendo filtrado por nombre, teléfono de WhatsApp, ruta, sucursal y estado (`agendado` o `no agendado`), además de manejar la `fecha_base` de ciclo de recolección y paginación server-side.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/clientes.php` (`class Cliente`)
* **Servicio**: `app/services/core/clientes.php` (`class ClienteService`)
* **Controlador**: `app/controllers/core/clientes.php` (`class ClienteController`)
* **Endpoint API**: `app/api/core/clientes.php`

---

## 📌 Información General

* **Base URL**: `/app/api/core/clientes.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Clientes (Paginada y Filtrada)

Retorna la lista de clientes registrados que coincidan con los filtros aplicados.

* **Método**: `GET`
* **URL**: `/app/api/core/clientes.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Término de búsqueda por nombre o número de WhatsApp del cliente. | `Samys` |
| `ruta_id` | `int` | No | ID de la ruta asignada al cliente. | `2` |
| `sucursal_id` | `int` | No | ID de la sucursal (filtra los clientes cuyas rutas pertenecen a esa sucursal). | `1` |
| `estado` | `string` | No | Estado del cliente: `agendado` o `no agendado`. | `agendado` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (opciones comunes: `10`, `50`, `100`). Por defecto `10`. | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/clientes.php?q=Samys&estado=no%20agendado&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 539,
      "nombre": "Comidas Rápidas Samys",
      "telefono_whatsapp": "573106288747",
      "frecuencia_id": 1,
      "ruta_id": 2,
      "estado": "no agendado",
      "fecha_base": "2026-08-01",
      "ruta_nombre": "Jueves",
      "ruta_ciudad": "Ibagué",
      "sucursal_id": 1,
      "sucursal_nombre": "Ibagué principal",
      "frecuencia_nombre": "Mensual"
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

### 2. 👁️ Obtener Detalles de un Cliente por ID

* **Método**: `GET`
* **URL**: `/app/api/core/clientes.php?id={ID}`

#### Parámetros Query
* `id` (`int`, Requerido): ID único del cliente.

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/clientes.php?id=539" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 539,
    "nombre": "Comidas Rápidas Samys",
    "telefono_whatsapp": "573106288747",
    "frecuencia_id": 1,
    "ruta_id": 2,
    "estado": "no agendado",
    "fecha_base": "2026-08-01",
    "ruta_nombre": "Jueves",
    "ruta_ciudad": "Ibagué",
    "sucursal_id": 1,
    "sucursal_nombre": "Ibagué principal",
    "frecuencia_nombre": "Mensual"
  }
}
```

---

### 3. ➕ Crear un Nuevo Cliente

* **Método**: `POST`
* **URL**: `/app/api/core/clientes.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `nombre` | `string` | **Sí** | Nombre completo o razón social del cliente. |
| `telefono_whatsapp` | `string` | **Sí** | Número de teléfono / WhatsApp del cliente. |
| `frecuencia_id` | `int` | No | ID de la frecuencia de recolección (`null` si no aplica). |
| `ruta_id` | `int` | No | ID de la ruta asignada (`null` si no aplica). |
| `estado` | `string` | No | Estado del cliente: `'agendado'` o `'no agendado'`. Por defecto `'no agendado'`. |
| `fecha_base` | `string` (date) | No | Fecha base para cálculo de frecuencia (`YYYY-MM-DD`). |

#### Ejemplo JSON Body
```json
{
  "nombre": "Restaurante Don Pedro",
  "telefono_whatsapp": "573119876543",
  "frecuencia_id": 1,
  "ruta_id": 2,
  "estado": "no agendado",
  "fecha_base": "2026-08-15"
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/core/clientes.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{
    "nombre": "Restaurante Don Pedro",
    "telefono_whatsapp": "573119876543",
    "frecuencia_id": 1,
    "ruta_id": 2,
    "estado": "no agendado",
    "fecha_base": "2026-08-15"
  }'
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Cliente creado exitosamente",
  "id": 625
}
```

---

### 4. ✏️ Actualizar un Cliente Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/clientes.php?id={ID}`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `id` | `int` | Opcional si va en la URL | ID del cliente. |
| `nombre` | `string` | **Sí** | Nuevo nombre del cliente. |
| `telefono_whatsapp` | `string` | **Sí** | Nuevo número de teléfono / WhatsApp. |
| `frecuencia_id` | `int` | No | ID de la frecuencia de recolección. |
| `ruta_id` | `int` | No | ID de la ruta asignada. |
| `estado` | `string` | No | Estado: `'agendado'` o `'no agendado'`. |
| `fecha_base` | `string` (date) | No | Fecha base en formato `YYYY-MM-DD`. |

#### Ejemplo JSON Body
```json
{
  "nombre": "Restaurante Don Pedro II",
  "telefono_whatsapp": "573119876543",
  "frecuencia_id": 2,
  "ruta_id": 2,
  "estado": "agendado",
  "fecha_base": "2026-08-20"
}
```

#### Ejemplo cURL
```bash
curl -X PUT "http://localhost:1019/app/api/core/clientes.php?id=625" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{
    "nombre": "Restaurante Don Pedro II",
    "telefono_whatsapp": "573119876543",
    "frecuencia_id": 2,
    "ruta_id": 2,
    "estado": "agendado",
    "fecha_base": "2026-08-20"
  }'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Cliente actualizado exitosamente"
}
```

---

### 5. 🗑️ Eliminar un Cliente

* **Método**: `DELETE`
* **URL**: `/app/api/core/clientes.php?id={ID}`

#### Ejemplo cURL
```bash
curl -X DELETE "http://localhost:1019/app/api/core/clientes.php?id=625" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Cliente eliminado exitosamente"
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `405 Method Not Allowed` | Método HTTP no permitido. | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de validación o fallo de servidor. | `{"success": false, "message": "Error en el servidor: El nombre del cliente es obligatorio."}` |
