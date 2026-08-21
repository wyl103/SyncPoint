# 💬 Documentación API e Integración - Chatwoot (`chatwoot/index.php`)

Esta API e integración permite la comunicación bidireccional entre la plataforma SyncPoint y **Chatwoot** para la atención de clientes vía WhatsApp, incluyendo la consulta de conversaciones por teléfono de cliente, sincronización en base de datos local, envío de mensajes salientes y validación de la regla de 24 horas para uso de plantillas.

---

## 📁 Estructura del Proyecto

* **Servicio de Integración**: `app/integrations/chatwoot/ChatwootService.php` (`class ChatwootService`)
* **Endpoint API**: `app/api/chatwoot/index.php`
* **Modelo Asociado Local**: `app/models/core/mensajes.php` (`class Mensaje`)
* **Servicio Local**: `app/services/core/mensajes.php` (`class MensajeService`)
* **Vista / Componente UI**: `app/views/layout/modals.php` (`#modal-chatwoot`)
* **Lógica JavaScript**: `public/js/modules/chatwoot.js`

---

## 🔑 Variables de Entorno Requeridas

La integración obtiene sus parámetros de conexión a través de las variables de entorno configuradas en `docker-compose.yml` (o archivo `.env`):

| Variable de Entorno | Tipo | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- |
| `CHATWOOT_BASE_URL` | `string` | URL base de la instancia de Chatwoot (sin barra final `/`). | `https://chat.tu-dominio.com` |
| `CHATWOOT_ACCOUNT_ID` | `string` / `int` | ID de la cuenta en Chatwoot. | `1` |
| `CHATWOOT_API_TOKEN` | `string` | Token de API personal de usuario/agente para autenticar las peticiones contra Chatwoot. | `xyz123abc...` |

---

## 📌 Información General

* **Base URL**: `/app/api/chatwoot/index.php`
* **Formato de Petición/Respuesta**: `application/json`
* **Autenticación**: Sesión de usuario activa mediante Cookie PHP (`PHPSESSID`).

---

## ⚙️ Funcionamiento de la Integración

1. **Búsqueda por Teléfono**: Al solicitar la conversación de un cliente (`cliente_id`), el sistema obtiene el teléfono registrado (`telefono_whatsapp`) y consulta a Chatwoot probando diferentes variantes del número (con/sin prefijo internacional `+`, últimos 10 dígitos).
2. **Vinculación Local**: Al hallar la conversación en Chatwoot, el `conversation_id` se vincula automáticamente en la tabla local `mensajes` (`chatwoot_conversation_id`).
3. **Clasificación de Mensajes**: Clasifica los mensajes entrantes (*incoming* - cliente a la izquierda) y salientes (*outgoing* - agente a la derecha).
4. **Regla de 24 Horas de WhatsApp (`is_24h_expired`)**: Evalúa el timestamp del último mensaje entrante enviado por el cliente. Si han transcurrido más de 24 horas (86,400 segundos), la bandera `is_24h_expired` se marca en `true`, alertando en la interfaz la necesidad de usar una plantilla predefinida para reiniciar la conversación.
5. **Plantillas Disponibles**: Retorna un catálogo de plantillas predefinidas con variables dinámicas (`{{cliente}}`, `{{fecha}}`, `{{sucursal}}`, `{{ruta}}`, `{{motivo}}`) para facilitar el contacto formal.

---

## 📖 Endpoints Disponibles

### 1. 🔍 Obtener Conversación y Mensajes del Cliente

Consulta la conversación, historial de mensajes, estado de la regla de 24 horas y plantillas para un cliente específico.

* **Método**: `GET`
* **URL**: `/app/api/chatwoot/index.php?cliente_id={ID}`

#### Parámetros Query

| Parámetro | Tipo | Requerido | Descripción | Ejemplo |
| :--- | :--- | :--- | :--- | :--- |
| `cliente_id` | `int` | **Sí** | ID del cliente registrado en la base de datos. | `539` |

#### Ejemplo de Petición cURL
```bash
curl -X GET "http://localhost:1019/app/api/chatwoot/index.php?cliente_id=539" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "data": {
    "cliente": {
      "id": 539,
      "nombre": "Comidas Rápidas Samys",
      "telefono_whatsapp": "573106288747",
      "ruta_nombre": "Jueves",
      "sucursal_nombre": "Ibagué principal"
    },
    "conversation_id": 591,
    "messages": [
      {
        "id": 1024,
        "content": "Hola, ¿cuándo realizan la recolección esta semana?",
        "message_type": 0,
        "created_at": 1723800000,
        "sender": "Comidas Rápidas Samys",
        "is_incoming": true
      },
      {
        "id": 1025,
        "content": "Buenas tardes, su recolección está programada para el día Jueves.",
        "message_type": 1,
        "created_at": 1723800500,
        "sender": "Nosotros",
        "is_incoming": false
      }
    ],
    "is_24h_expired": false,
    "last_incoming_at": 1723800000,
    "plantillas": [
      {
        "id": "recordatorio_recoleccion",
        "titulo": "Recordatorio de Recolección",
        "texto": "Hola {{cliente}}, le recordamos que su recolección de aceite vegetal está programada para el día {{fecha}} en la sucursal {{sucursal}}.",
        "variables": ["cliente", "fecha", "sucursal"]
      },
      {
        "id": "confirmacion_ruta",
        "titulo": "Confirmación de Ruta",
        "texto": "Estimado {{cliente}}, nuestro vehículo de la ruta {{ruta}} pasará hoy {{fecha}} por su establecimiento.",
        "variables": ["cliente", "ruta", "fecha"]
      }
    ]
  }
}
```

---

### 2. 📤 Enviar Mensaje Saliente a Chatwoot

Envía un nuevo mensaje de texto o plantilla procesada a la conversación activa en Chatwoot.

* **Método**: `POST`
* **URL**: `/app/api/chatwoot/index.php`
* **Header**: `Content-Type: application/json`

#### Cuerpo de la Petición (JSON Body)

| Campo | Tipo | Requerido | Descripción |
| :--- | :--- | :--- | :--- |
| `conversation_id` | `int` / `string` | **Sí** | ID de la conversación en Chatwoot. |
| `content` | `string` | **Sí** | Texto o contenido procesado del mensaje a enviar al cliente. |
| `template_params` | `object` | No (Requerido si 24h expiró) | Objeto con la estructura de plantilla oficial de WhatsApp (`name`, `category`, `language`, `processed_params`). |

#### Ejemplo JSON Body (Mensaje Regular)
```json
{
  "conversation_id": 591,
  "content": "Hola Comidas Rápidas Samys, le recordamos que su recolección está programada para hoy."
}
```

#### Ejemplo JSON Body (Plantilla Oficial WhatsApp / Fuera de Ventana 24h)
```json
{
  "conversation_id": 591,
  "content": "Hola Comidas Rápidas Samys, le recordamos que su recolección de aceite vegetal está programada para el día 2026-08-20 en la sucursal Ibagué principal.",
  "template_params": {
    "name": "recordatorio_recoleccion",
    "category": "UTILITY",
    "language": "es",
    "processed_params": {
      "cliente": "Comidas Rápidas Samys",
      "fecha": "2026-08-20",
      "sucursal": "Ibagué principal"
    }
  }
}
```

#### Ejemplo cURL
```bash
curl -X POST "http://localhost:1019/app/api/chatwoot/index.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{
    "conversation_id": 591,
    "content": "Hola Comidas Rápidas Samys, le recordamos que su recolección está programada para hoy."
  }'
```

#### Respuesta Exitosa (`200 OK`)
```json
{
  "success": true,
  "message": "Mensaje enviado a Chatwoot",
  "response": {
    "id": 1026,
    "content": "Hola Comidas Rápidas Samys, le recordamos que su recolección está programada para hoy.",
    "message_type": 1,
    "created_at": 1723810000
  }
}
```

---

## ❌ Respuestas de Error Comunes

| Código HTTP | Descripción | Ejemplo de Respuesta JSON |
| :--- | :--- | :--- |
| `401 Unauthorized` | Sesión no iniciada. | `{"success": false, "message": "No autorizado"}` |
| `400 Bad Request` | Parámetros faltantes (`cliente_id`, `conversation_id` o `content`). | `{"success": false, "message": "Parámetro cliente_id requerido"}` |
| `405 Method Not Allowed` | Método HTTP no permitido (distinto de GET/POST). | `{"success": false, "message": "Método no permitido"}` |
| `500 Internal Error` | Error de conexión con la API de Chatwoot o fallo de servidor. | `{"success": false, "message": "Error: Cliente no encontrado."}` |
