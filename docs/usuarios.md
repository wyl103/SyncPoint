# 👤 Documentación API - Gestión de Usuarios (`core/usuarios.php`)

Esta documentación describe la estructura, las restricciones del CRUD de **Usuarios** (`usuarios`) y la especificación técnica del endpoint `/app/api/core/usuarios.php`.

---

## 📁 Estructura del Proyecto (Arquitectura Core)

* **Modelo**: `app/models/core/usuarios.php` (`class Usuario`)
* **Servicio**: `app/services/core/usuarios.php` (`class UsuarioService`)
* **Controlador**: `app/controllers/core/usuarios.php` (`class UsuarioController`)
* **Endpoint API**: `app/api/core/usuarios.php`

---

## 📌 Información General

* **Base URL**: `/app/api/core/usuarios.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Lista de Usuarios (Paginada y Filtrada)

Retorna la lista de usuarios registrados en el sistema (excluyendo hashes de contraseñas).

* **Método**: `GET`
* **URL**: `/app/api/core/usuarios.php`

#### Parámetros de Consulta (Query Params)

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `q` / `busqueda` | `string` | No | Término de búsqueda por nombre o correo electrónico. | `admin` |
| `page` | `int` | No | Número de página (por defecto `1`). | `1` |
| `limit` | `int` | No | Límite de registros por página (`10`, `50`, `100`). Por defecto `10`. | `10` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/usuarios.php?q=admin&page=1&limit=10" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Administrador Principal",
      "correo": "admin@oilbless.com",
      "tipo": "administrador",
      "created_at": "2026-08-17"
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

### 2. 🔍 Obtener Detalle de un Usuario por ID

* **Método**: `GET`
* **URL**: `/app/api/core/usuarios.php?id={ID}`

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/core/usuarios.php?id=1" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Administrador Principal",
    "correo": "admin@oilbless.com",
    "tipo": "administrador",
    "created_at": "2026-08-17"
  }
}
```

---

### 3. ➕ Crear un Nuevo Usuario

* **Método**: `POST`
* **URL**: `/app/api/core/usuarios.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `nombre` | `string` | **Sí** | Nombre completo del usuario. |
| `correo` / `email` | `string` | **Sí** | Correo electrónico único del usuario. |
| `password` / `clave` | `string` | **Sí** | Contraseña (mínimo 6 caracteres). |
| `tipo` | `string` | No | Rol/Tipo de usuario: `administrador` o `normal` (por defecto `normal`). |

```json
{
  "nombre": "Carlos Pérez",
  "correo": "carlos.perez@oilbless.com",
  "password": "Password123",
  "tipo": "normal"
}
```

#### Respuesta Exitosa (`201 Created`)
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "id": 2
}
```

---

### 4. ✏️ Actualizar un Usuario Existente

* **Método**: `PUT`
* **URL**: `/app/api/core/usuarios.php?id={ID}`
* **Header**: `Content-Type: application/json`

```json
{
  "nombre": "Carlos Alberto Pérez",
  "correo": "carlos.perez@oilbless.com",
  "password": "NuevaPassword123"
}
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Usuario actualizado exitosamente"
}
```

---

### 5. 🗑️ Eliminar un Usuario

* **Método**: `DELETE`
* **URL**: `/app/api/core/usuarios.php?id={ID}`

```bash
curl -X DELETE "http://localhost:1019/app/api/core/usuarios.php?id=2" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Usuario eliminado exitosamente"
}
```
