<?php
// app/integrations/chatwoot/ChatwootService.php
require_once __DIR__ . '/../../services/Database.php';
require_once __DIR__ . '/../../models/core/clientes.php';
require_once __DIR__ . '/../../services/core/mensajes.php';

class ChatwootService {
    private $baseUrl;
    private $accountId;
    private $apiToken;
    private $pdo;

    public function __construct() {
        $env = @parse_ini_file(__DIR__ . '/../../../.env');
        $this->baseUrl = trim(rtrim($env['CHATWOOT_BASE_URL'] ?? 'https://chat.oilbless.com', '/'));
        $this->accountId = trim($env['CHATWOOT_ACCOUNT_ID'] ?? '1');
        $this->apiToken = trim($env['CHATWOOT_API_TOKEN'] ?? '');

        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Helper genérico para peticiones HTTP a la API de Chatwoot
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'api_access_token: ' . $this->apiToken,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SyncPoint/1.0'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Error cURL Chatwoot ($url): " . $error);
            return null;
        }

        if ($httpCode >= 400) {
            error_log("Respuesta Chatwoot HTTP $httpCode ($url): " . $response);
        }

        return json_decode($response, true);
    }

    /**
     * Obtener plantillas disponibles para mensajes predefinidos
     */
    public function obtenerPlantillas() {
        return [
            [
                'id' => 'recordatorio_recoleccion',
                'titulo' => 'Recordatorio de Recolección',
                'texto' => 'Hola {{cliente}}, le recordamos que su recolección de aceite vegetal está programada para el día {{fecha}} en la sucursal {{sucursal}}.',
                'variables' => ['cliente', 'fecha', 'sucursal']
            ],
            [
                'id' => 'confirmacion_ruta',
                'titulo' => 'Confirmación de Ruta',
                'texto' => 'Estimado {{cliente}}, nuestro vehículo de la ruta {{ruta}} pasará hoy {{fecha}} por su establecimiento.',
                'variables' => ['cliente', 'ruta', 'fecha']
            ],
            [
                'id' => 'aviso_reprogramacion',
                'titulo' => 'Aviso de Reprogramación',
                'texto' => 'Hola {{cliente}}, le informamos que su recolección ha sido reprogramada para el día {{fecha}}. Motivo: {{motivo}}.',
                'variables' => ['cliente', 'fecha', 'motivo']
            ],
            [
                'id' => 'agradecimiento_servicio',
                'titulo' => 'Agradecimiento de Servicio',
                'texto' => 'Estimado {{cliente}}, hemos completado la recolección en {{sucursal}}. ¡Gracias por confiar en OilBless!',
                'variables' => ['cliente', 'sucursal']
            ]
        ];
    }

    /**
     * Obtener mensajes de una conversación por su Chatwoot Conversation ID
     */
    public function getConversationMessages($conversationId) {
        if (empty($conversationId)) return [];
        $endpoint = "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages";
        $response = $this->makeRequest($endpoint, 'GET');
        
        if (isset($response['payload']) && is_array($response['payload'])) {
            return $response['payload'];
        }
        return is_array($response) ? $response : [];
    }

    /**
     * Enviar un mensaje a una conversación en Chatwoot
     */
    public function sendMessage($conversationId, $content) {
        if (empty($conversationId) || empty($content)) {
            throw new Exception("Conversation ID y contenido son requeridos.");
        }

        $endpoint = "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages";
        $data = [
            'content' => $content,
            'message_type' => 'outgoing',
            'private' => false
        ];

        return $this->makeRequest($endpoint, 'POST', $data);
    }

    /**
     * Buscar un contacto en Chatwoot probando múltiples variaciones del teléfono
     */
    public function searchContactByPhone($phone) {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        if (empty($cleanPhone)) return null;

        $variantes = array_unique([
            $cleanPhone,
            '+' . $cleanPhone,
            substr($cleanPhone, -10)
        ]);

        foreach ($variantes as $v) {
            $endpoint = "/api/v1/accounts/{$this->accountId}/contacts/search?q=" . urlencode($v);
            $response = $this->makeRequest($endpoint, 'GET');

            if (isset($response['payload']) && !empty($response['payload'])) {
                return $response['payload'][0];
            }
        }

        return null;
    }

    /**
     * Obtener conversaciones de un contacto en Chatwoot
     */
    public function getContactConversations($contactId) {
        if (empty($contactId)) return [];
        $endpoint = "/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/conversations";
        $response = $this->makeRequest($endpoint, 'GET');
        
        if (isset($response['payload']) && is_array($response['payload'])) {
            return $response['payload'];
        }
        return is_array($response) ? $response : [];
    }

    /**
     * Guardar o vincular el conversation_id de Chatwoot en la BD local en la tabla 'mensajes'
     * utilizando el servicio MensajeService (CRUD de mensajes core/mensajes.php)
     */
    private function guardarConversationIdLocal($conversationId) {
        if (empty($conversationId)) return;

        try {
            $mensajeService = new MensajeService();

            // Verificar si ya existe el registro de conversación en la tabla mensajes
            $stmtM = $this->pdo->prepare("SELECT id FROM mensajes WHERE chatwoot_conversation_id = :conv_id ORDER BY id DESC LIMIT 1");
            $stmtM->execute(['conv_id' => (string)$conversationId]);
            $mensajeExistente = $stmtM->fetch();

            if (!$mensajeExistente) {
                $mensajeService->crearMensaje([
                    'chatwoot_conversation_id' => (string)$conversationId,
                    'estado' => 'enviado'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error al guardar conversation_id localmente en mensajes: " . $e->getMessage());
        }
    }

    /**
     * Método principal para consultar conversación y mensajes de un cliente
     */
    public function obtenerChatCliente($clienteId) {
        if (empty($clienteId)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }

        $clienteModel = new Cliente();
        $cliente = $clienteModel->getById($clienteId);

        if (!$cliente) {
            throw new Exception("Cliente no encontrado.");
        }

        $conversationId = null;

        // 1. Buscar conversación por número de teléfono en la API de Chatwoot
        if (!empty($cliente['telefono_whatsapp'])) {
            $contact = $this->searchContactByPhone($cliente['telefono_whatsapp']);
            if ($contact && isset($contact['id'])) {
                $conversations = $this->getContactConversations($contact['id']);
                if (!empty($conversations)) {
                    $conversationId = $conversations[0]['id'] ?? null;
                }
            }
        }

        // Si se halló en Chatwoot, vincular/guardar conversation_id en la BD local
        if ($conversationId) {
            $this->guardarConversationIdLocal($conversationId);
        }

        // 3. Traer los mensajes de Chatwoot si tenemos un ID de conversación
        $mensajes = [];
        $lastIncomingTimestamp = null;

        if ($conversationId) {
            $rawMessages = $this->getConversationMessages($conversationId);
            
            // Formatear mensajes para la vista y detectar el último mensaje del cliente (incoming)
            foreach ($rawMessages as $msg) {
                $msgType = $msg['message_type'] ?? null;
                $senderType = $msg['sender']['type'] ?? null;

                // Clasificación estricta estilo WhatsApp:
                // contact / incoming / 0 = Cliente (Ellos - izquierda)
                // user / outgoing / 1 = Nosotros (Agente - derecha)
                $isIncoming = ($senderType === 'contact' || $msgType === 0 || (string)$msgType === '0' || $msgType === 'incoming');
                if ($senderType === 'user' || $msgType === 1 || (string)$msgType === '1' || $msgType === 'outgoing') {
                    $isIncoming = false;
                }

                $sender = $msg['sender']['name'] ?? ($isIncoming ? 'Cliente' : 'Nosotros');
                $createdAt = $msg['created_at'] ?? null;

                if ($isIncoming && $createdAt) {
                    $ts = is_numeric($createdAt) ? (int)$createdAt : strtotime($createdAt);
                    if (!$lastIncomingTimestamp || $ts > $lastIncomingTimestamp) {
                        $lastIncomingTimestamp = $ts;
                    }
                }

                $mensajes[] = [
                    'id' => $msg['id'] ?? null,
                    'content' => $msg['content'] ?? '',
                    'message_type' => $msg['message_type'] ?? 0,
                    'created_at' => $createdAt,
                    'sender' => $sender,
                    'is_incoming' => $isIncoming
                ];
            }
        }

        // Evaluar la regla de la ventana de 24 horas de WhatsApp
        $is24hExpired = true;
        if ($lastIncomingTimestamp) {
            $diffSeconds = time() - $lastIncomingTimestamp;
            $is24hExpired = ($diffSeconds > 86400); // Pasaron más de 24 horas (86,400 seg)
        }

        return [
            'cliente' => [
                'id' => $cliente['id'],
                'nombre' => $cliente['nombre'],
                'telefono_whatsapp' => $cliente['telefono_whatsapp'],
                'ruta_nombre' => $cliente['ruta_nombre'] ?? 'N/A',
                'sucursal_nombre' => $cliente['sucursal_nombre'] ?? 'N/A'
            ],
            'conversation_id' => $conversationId,
            'messages' => $mensajes,
            'is_24h_expired' => $is24hExpired,
            'last_incoming_at' => $lastIncomingTimestamp,
            'plantillas' => $this->obtenerPlantillas()
        ];
    }
}
