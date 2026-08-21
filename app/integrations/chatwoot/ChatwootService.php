<?php
// app/integrations/chatwoot/ChatwootService.php
require_once __DIR__ . '/../../services/Database.php';
require_once __DIR__ . '/../../models/core/clientes.php';
require_once __DIR__ . '/../../services/core/mensajes.php';

class ChatwootService {
    private $baseUrl;
    private $accountId;
    private $apiToken;
    private $cutoffDate;
    private $cutoffTimestamp;
    private $pdo;

    public function __construct() {
        $envFile = __DIR__ . '/../../../.env';
        $env = (file_exists($envFile) && is_readable($envFile)) ? @parse_ini_file($envFile) : [];
        if (!is_array($env)) {
            $env = [];
        }

        $this->baseUrl   = getenv('CHATWOOT_BASE_URL') ?: ($env['CHATWOOT_BASE_URL'] ?? 'https://chat.oilbless.com');
        $this->baseUrl   = trim(rtrim($this->baseUrl, '/'));
        $this->accountId = getenv('CHATWOOT_ACCOUNT_ID') ?: ($env['CHATWOOT_ACCOUNT_ID'] ?? '1');
        $this->apiToken   = getenv('CHATWOOT_API_TOKEN') ?: ($env['CHATWOOT_API_TOKEN'] ?? '');

        // Fecha de corte para conversaciones y mensajes (por defecto 2026-08-21 00:00:00 en America/Bogota)
        $this->cutoffDate = getenv('CHATWOOT_CUTOFF_DATE') ?: ($env['CHATWOOT_CUTOFF_DATE'] ?? '2026-08-21 00:00:00');
        if (!empty($this->cutoffDate)) {
            try {
                $dt = new DateTime($this->cutoffDate, new DateTimeZone('America/Bogota'));
                $this->cutoffTimestamp = $dt->getTimestamp();
            } catch (Exception $e) {
                $this->cutoffTimestamp = strtotime($this->cutoffDate) ?: 0;
            }
        } else {
            $this->cutoffTimestamp = 0;
        }

        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Helper para parsear timestamps numéricos o cadenas de fecha a entero UNIX
     */
    private function parseTimestamp($val) {
        if (empty($val)) return 0;
        if (is_numeric($val)) return (int)$val;
        return (int)strtotime((string)$val);
    }

    /**
     * Helper genérico para peticiones HTTP a la API de Chatwoot
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $sep = (strpos($endpoint, '?') === false) ? '?' : '&';
        $url = $this->baseUrl . $endpoint . $sep . 'api_access_token=' . urlencode($this->apiToken);
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json',
            'api_access_token: ' . $this->apiToken,
            'api-access-token: ' . $this->apiToken,
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
     * Obtener plantillas oficiales aprobadas en Meta WhatsApp Cloud API
     */
    public function obtenerPlantillas($inboxId = null) {
        $plantillas = [];
        
        // Si tenemos un inboxId, consultar las plantillas aprobadas registradas en el inbox de Chatwoot
        if (!empty($inboxId)) {
            try {
                $inbox = $this->makeRequest("/api/v1/accounts/{$this->accountId}/inboxes/{$inboxId}", 'GET');
                if (!empty($inbox['message_templates']) && is_array($inbox['message_templates'])) {
                    foreach ($inbox['message_templates'] as $tpl) {
                        if (($tpl['status'] ?? '') === 'APPROVED') {
                            $name = $tpl['name'] ?? '';
                            $lang = $tpl['language'] ?? 'es_CO';
                            $cat = $tpl['category'] ?? 'MARKETING';

                            $textoParts = [];
                            $vars = [];
                            $varLabels = [];

                            foreach ($tpl['components'] ?? [] as $comp) {
                                if ($comp['type'] === 'HEADER' && ($comp['format'] ?? '') === 'TEXT' && !empty($comp['text'])) {
                                    $textoParts[] = $comp['text'];
                                } elseif ($comp['type'] === 'BODY' && !empty($comp['text'])) {
                                    $textoParts[] = $comp['text'];
                                    preg_match_all('/\{\{(\d+)\}\}/', $comp['text'], $matches);
                                    if (!empty($matches[1])) {
                                        $vars = array_unique($matches[1]);
                                        sort($vars, SORT_NUMERIC);
                                    }
                                }
                            }

                            $fullText = implode("\n\n", $textoParts);
                            
                            if ($name === 'confirmacion_entrega') {
                                $titulo = 'Confirmación de Recolección / Servicio';
                                $varLabels = ['1' => 'Nombre del Cliente', '2' => 'Fecha Programada'];
                            } elseif ($name === 'hola_oilbless') {
                                $titulo = 'Bienvenida Canal Oficial OilBless';
                                $varLabels = ['1' => 'Nombre del Cliente'];
                            } elseif ($name === 'hello_world') {
                                $titulo = 'Hello World (Prueba Meta WhatsApp)';
                                $varLabels = [];
                            } else {
                                $titulo = ucwords(str_replace(['_', '-'], ' ', $name));
                                foreach ($vars as $v) {
                                    $varLabels[$v] = "Variable {$v}";
                                }
                            }

                            $plantillas[] = [
                                'id' => $name,
                                'name' => $name,
                                'titulo' => $titulo,
                                'texto' => $fullText,
                                'variables' => array_values($vars),
                                'variable_labels' => $varLabels,
                                'category' => $cat,
                                'language' => $lang
                            ];
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Error obteniendo plantillas del inbox: " . $e->getMessage());
            }
        }

        // Fallback predeterminado con las plantillas oficiales aprobadas de OilBless en Meta WhatsApp
        if (empty($plantillas)) {
            $plantillas = [
                [
                    'id' => 'confirmacion_entrega',
                    'name' => 'confirmacion_entrega',
                    'titulo' => 'Confirmación de Recolección / Servicio',
                    'texto' => "Recolección de Aceite\n\nHola {{1}}, te escribimos de OilBless (GreenFuel) para confirmar tu servicio del día {{2}}. 🚛♻️\n\n¿Podemos pasar a retirar el aceite usado en esa fecha?",
                    'variables' => ['1', '2'],
                    'variable_labels' => [
                        '1' => 'Nombre del Cliente',
                        '2' => 'Fecha Programada'
                    ],
                    'category' => 'MARKETING',
                    'language' => 'es_CO'
                ],
                [
                    'id' => 'hola_oilbless',
                    'name' => 'hola_oilbless',
                    'titulo' => 'Bienvenida Canal Oficial OilBless',
                    'texto' => "¡Hola {{1}}! 👋 Te escribimos de OilBless, en alianza con Greenfuel. Somos tus recolectores de aceite vegetal usado. 🚛♻️\n\nQueremos presentarte nuestro nuevo canal oficial. Por este chat coordinaremos tus próximas:\n- Recolecciones\n- Envío de certificados\n\nSi nos guardas en tus contactos ASEGURAS que nuestras notificaciones de servicio te lleguen siempre a tiempo.",
                    'variables' => ['1'],
                    'variable_labels' => [
                        '1' => 'Nombre del Cliente'
                    ],
                    'category' => 'MARKETING',
                    'language' => 'es_CO'
                ],
                [
                    'id' => 'hello_world',
                    'name' => 'hello_world',
                    'titulo' => 'Hello World (Prueba Meta WhatsApp)',
                    'texto' => "Hello World\n\nWelcome and congratulations!! This message demonstrates your ability to send a WhatsApp message notification from the Cloud API, hosted by Meta. Thank you for taking the time to test with us.",
                    'variables' => [],
                    'variable_labels' => [],
                    'category' => 'UTILITY',
                    'language' => 'en_US'
                ]
            ];
        }

        return $plantillas;
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
     * Enviar un mensaje o plantilla a una conversación en Chatwoot
     */
    public function sendMessage($conversationId, $content, $templateParams = null) {
        if (empty($conversationId)) {
            throw new Exception("Conversation ID es requerido.");
        }

        $endpoint = "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages";
        $data = [
            'content' => $content,
            'message_type' => 'outgoing',
            'private' => false
        ];

        if (!empty($templateParams) && is_array($templateParams)) {
            $data['template_params'] = $templateParams;
        }

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
     * Obtener listado paginado de conversaciones de Chatwoot vinculadas con clientes locales
     */
    public function getConversationsList($page = 1, $status = 'all') {
        $endpoint = "/api/v1/accounts/{$this->accountId}/conversations?page={$page}";
        if ($status !== 'all') {
            $endpoint .= "&status=" . urlencode($status);
        }

        $res = $this->makeRequest($endpoint, 'GET');
        $data = $res['data'] ?? [];
        $payload = $data['payload'] ?? $res['payload'] ?? (is_array($data) ? $data : []);

        if (!is_array($payload)) {
            $payload = [];
        }

        $rawPayloadCount = count($payload);
        $hitCutoffBoundary = false;

        // Filtrar por fecha de corte para excluir conversaciones previas al 21/08/2026 00:00:00
        if ($this->cutoffTimestamp > 0) {
            $filteredPayload = [];
            foreach ($payload as $conv) {
                $lastMsg = $conv['last_non_activity_message'] ?? ($conv['messages'][0] ?? null);
                $lastMsgTime = $lastMsg['created_at'] ?? ($conv['last_activity_at'] ?? $conv['updated_at'] ?? null);
                $ts = $this->parseTimestamp($lastMsgTime);
                if ($ts >= $this->cutoffTimestamp) {
                    $filteredPayload[] = $conv;
                } else {
                    $hitCutoffBoundary = true;
                }
            }
            $payload = $filteredPayload;
        }

        // Extraer teléfonos para hacer match en batch con la base de datos de clientes
        $telefonos = [];
        foreach ($payload as $conv) {
            $phone = $conv['meta']['sender']['phone_number'] ?? null;
            if ($phone) {
                $digits = preg_replace('/\D/', '', $phone);
                if (!empty($digits)) {
                    $telefonos[] = $digits;
                    if (strpos($digits, '57') === 0 && strlen($digits) === 12) {
                        $telefonos[] = substr($digits, 2);
                    }
                }
            }
        }

        $clientesPorTelefono = [];
        if (!empty($telefonos)) {
            $telefonos = array_unique($telefonos);
            $placeholders = implode(',', array_fill(0, count($telefonos), '?'));
            $sql = "SELECT c.id, c.nombre, c.telefono_whatsapp, s.nombre AS sucursal_nombre, r.nombre AS ruta_nombre 
                    FROM clientes c 
                    LEFT JOIN rutas r ON c.ruta_id = r.id 
                    LEFT JOIN sucursales s ON r.fk_sucursal = s.id 
                    WHERE REPLACE(REPLACE(REPLACE(c.telefono_whatsapp, ' ', ''), '-', ''), '+', '') IN ($placeholders)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($telefonos));
            $clientesDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($clientesDb as $c) {
                $rawPhone = preg_replace('/\D/', '', $c['telefono_whatsapp'] ?? '');
                if ($rawPhone) {
                    $clientesPorTelefono[$rawPhone] = $c;
                    if (strpos($rawPhone, '57') === 0 && strlen($rawPhone) === 12) {
                        $clientesPorTelefono[substr($rawPhone, 2)] = $c;
                    }
                }
            }
        }

        // Identificar conversaciones que requieran cálculo de conteo no leído para resolver en paralelo
        $unreadConvIds = [];
        foreach ($payload as $conv) {
            $lastMsg = $conv['last_non_activity_message'] ?? ($conv['messages'][0] ?? null);
            $lastMsgType = $lastMsg['message_type'] ?? 0;
            $isLastIncoming = ($lastMsgType === 0 || (string)$lastMsgType === '0' || $lastMsgType === 'incoming');
            if ($isLastIncoming && (int)($conv['unread_count'] ?? 0) > 0) {
                $unreadConvIds[] = $conv['id'];
            }
        }

        // Consultar conteo exacto en paralelo mediante multi-curl para máxima velocidad
        $batchTrailingCounts = !empty($unreadConvIds) ? $this->getBatchTrailingIncomingCounts($unreadConvIds) : [];

        $formatedList = [];
        foreach ($payload as $conv) {
            $sender = $conv['meta']['sender'] ?? [];
            $phone = $sender['phone_number'] ?? '';
            $digits = preg_replace('/\D/', '', $phone);
            
            $clienteMatch = null;
            if ($digits && isset($clientesPorTelefono[$digits])) {
                $clienteMatch = $clientesPorTelefono[$digits];
            } elseif ($digits && strpos($digits, '57') === 0 && isset($clientesPorTelefono[substr($digits, 2)])) {
                $clienteMatch = $clientesPorTelefono[substr($digits, 2)];
            }

            $lastMsg = $conv['last_non_activity_message'] ?? ($conv['messages'][0] ?? null);
            $lastMsgContent = $lastMsg['content'] ?? '';
            $lastMsgType = $lastMsg['message_type'] ?? 0;
            $lastMsgStatus = $lastMsg['status'] ?? 'sent';
            $lastMsgTime = $lastMsg['created_at'] ?? ($conv['last_activity_at'] ?? $conv['updated_at'] ?? null);

            $nombre = $clienteMatch['nombre'] ?? ($sender['name'] ?? ($sender['available_name'] ?? 'Cliente WhatsApp'));
            if ($nombre === 'Wilman IT Arias' && !empty($clienteMatch['nombre'])) {
                $nombre = $clienteMatch['nombre'];
            }

            $diffSeconds = $lastMsgTime ? (time() - (is_numeric($lastMsgTime) ? $lastMsgTime : strtotime($lastMsgTime))) : 999999;
            $is24hExpired = ($diffSeconds > 86400);

            // Conteo exacto resuelto en paralelo
            $exactUnread = 0;
            $isLastIncoming = ($lastMsgType === 0 || (string)$lastMsgType === '0' || $lastMsgType === 'incoming');
            if ($isLastIncoming && (int)($conv['unread_count'] ?? 0) > 0) {
                $exactUnread = $batchTrailingCounts[$conv['id']] ?? (int)$conv['unread_count'];
            }

            $formatedList[] = [
                'conversation_id' => $conv['id'],
                'inbox_id' => $conv['inbox_id'] ?? null,
                'cliente_id' => $clienteMatch['id'] ?? null,
                'nombre' => $nombre,
                'telefono' => $phone ?: ($clienteMatch['telefono_whatsapp'] ?? ''),
                'sucursal_nombre' => $clienteMatch['sucursal_nombre'] ?? null,
                'ruta_nombre' => $clienteMatch['ruta_nombre'] ?? null,
                'ultimo_mensaje' => $lastMsgContent,
                'ultimo_mensaje_tipo' => $lastMsgType,
                'ultimo_mensaje_status' => $lastMsgStatus,
                'ultimo_mensaje_at' => $lastMsgTime,
                'unread_count' => $exactUnread,
                'status' => $conv['status'] ?? 'open',
                'is_24h_expired' => $is24hExpired,
                'channel' => $conv['meta']['channel'] ?? 'WhatsApp'
            ];
        }

        return [
            'conversations' => $formatedList,
            'meta' => [
                'current_page' => (int)$page,
                'has_more' => ($rawPayloadCount >= 20) && !$hitCutoffBoundary,
                'total_count' => count($formatedList)
            ]
        ];
    }

    /**
     * Consulta en paralelo mediante multi-curl el conteo exacto de mensajes del cliente
     * posteriores al último mensaje enviado por nosotros, para un conjunto de IDs.
     */
    public function getBatchTrailingIncomingCounts(array $conversationIds) {
        if (empty($conversationIds)) return [];

        $mh = curl_multi_init();
        $curlHandles = [];

        foreach ($conversationIds as $cid) {
            $ch = curl_init();
            $sep = (strpos($this->baseUrl, '?') === false) ? '?' : '&';
            $url = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$cid}/messages" . $sep . "api_access_token=" . urlencode($this->apiToken);
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'api_access_token: ' . $this->apiToken,
                    'api-access-token: ' . $this->apiToken
                ]
            ]);
            curl_multi_add_handle($mh, $ch);
            $curlHandles[$cid] = $ch;
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 0.2);
            }
        } while ($running > 0 && $status == CURLM_OK);

        $counts = [];
        foreach ($curlHandles as $cid => $ch) {
            $content = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            $json = json_decode($content, true);
            $rawMsgs = isset($json['payload']) && is_array($json['payload']) ? $json['payload'] : (is_array($json) ? $json : []);

            $trailingCount = 0;
            for ($i = count($rawMsgs) - 1; $i >= 0; $i--) {
                $m = $rawMsgs[$i];
                if (!is_array($m)) continue;

                $createdTs = $this->parseTimestamp($m['created_at'] ?? 0);
                if ($this->cutoffTimestamp > 0 && $createdTs < $this->cutoffTimestamp) {
                    break;
                }

                $type = $m['message_type'] ?? null;
                $senderType = $m['sender']['type'] ?? ($m['sender_type'] ?? null);
                $isIncoming = ($senderType === 'contact' || $type === 0 || (string)$type === '0' || $type === 'incoming');

                if ($isIncoming) {
                    $trailingCount++;
                } else {
                    break;
                }
            }
            $counts[$cid] = $trailingCount;
        }
        curl_multi_close($mh);

        return $counts;
    }

    /**
     * Calcula la cantidad exacta de mensajes consecutivos enviados por el cliente
     * desde el último mensaje enviado por nosotros (agente o plantilla) para 1 conversación
     */
    public function getTrailingIncomingCount($conversationId) {
        $res = $this->getBatchTrailingIncomingCounts([$conversationId]);
        return $res[$conversationId] ?? 0;
    }

    /**
     * Consulta rápida de 1 solo request para verificar si hay mensajes nuevos
     * y cuántas conversaciones tienen mensajes no leídos del cliente.
     */
    public function getUnreadConversationsSummary() {
        $endpoint = "/api/v1/accounts/{$this->accountId}/conversations?status=open&page=1";
        $res = $this->makeRequest($endpoint, 'GET');
        $data = $res['data'] ?? [];
        $payload = $data['payload'] ?? $res['payload'] ?? (is_array($data) ? $data : []);

        if (!is_array($payload)) {
            $payload = [];
        }

        $unreadConversationsCount = 0;
        foreach ($payload as $conv) {
            $lastMsg = $conv['last_non_activity_message'] ?? ($conv['messages'][0] ?? null);
            $lastMsgTime = $lastMsg['created_at'] ?? ($conv['last_activity_at'] ?? $conv['updated_at'] ?? null);
            $ts = $this->parseTimestamp($lastMsgTime);

            if ($this->cutoffTimestamp > 0 && $ts < $this->cutoffTimestamp) {
                continue;
            }

            $lastMsgType = $lastMsg['message_type'] ?? null;
            $isIncoming = ($lastMsgType === 0 || (string)$lastMsgType === '0' || $lastMsgType === 'incoming');
            $unreadCount = (int)($conv['unread_count'] ?? 0);

            if ($isIncoming && $unreadCount > 0) {
                $unreadConversationsCount++;
            }
        }

        return [
            'has_unread' => ($unreadConversationsCount > 0),
            'unread_conversations_count' => $unreadConversationsCount
        ];
    }

    /**
     * Marca una conversación como leída en Chatwoot actualizando agent_last_seen_at
     */
    public function marcarConversacionLeida($conversationId) {
        if (empty($conversationId)) return false;
        try {
            $endpoint = "/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/update_last_seen";
            return $this->makeRequest($endpoint, 'POST', [
                'agent_last_seen_at' => time()
            ]);
        } catch (Exception $e) {
            error_log("Error marcando conversación {$conversationId} como leída: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método principal para consultar conversación y mensajes de un cliente o conversación
     */
    public function obtenerChatCliente($clienteId = null, $conversationId = null) {
        $cliente = null;
        $inboxId = null;

        if (!empty($clienteId)) {
            $clienteModel = new Cliente();
            $cliente = $clienteModel->getById($clienteId);
        }

        // 1. Si no tenemos conversación pero sí cliente, buscar por su teléfono en Chatwoot
        if (empty($conversationId) && $cliente && !empty($cliente['telefono_whatsapp'])) {
            $contact = $this->searchContactByPhone($cliente['telefono_whatsapp']);
            if ($contact && isset($contact['id'])) {
                $conversations = $this->getContactConversations($contact['id']);
                if (!empty($conversations)) {
                    $conversationId = $conversations[0]['id'] ?? null;
                    $inboxId = $conversations[0]['inbox_id'] ?? null;
                }
            }
        }

        // 2. Si tenemos conversationId pero no cliente, consultar datos de la conversación en Chatwoot
        if (!empty($conversationId) && empty($cliente)) {
            try {
                $convInfo = $this->makeRequest("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}", 'GET');
                if ($convInfo) {
                    $inboxId = $convInfo['inbox_id'] ?? null;
                    $sender = $convInfo['meta']['sender'] ?? [];
                    $phone = $sender['phone_number'] ?? '';
                    $digits = preg_replace('/\D/', '', $phone);

                    // Buscar si coincide con algún cliente local
                    if ($digits) {
                        $variantes = [$digits];
                        if (strpos($digits, '57') === 0 && strlen($digits) === 12) {
                            $variantes[] = substr($digits, 2);
                        }
                        $placeholders = implode(',', array_fill(0, count($variantes), '?'));
                        $stmt = $this->pdo->prepare("SELECT c.id, c.nombre, c.telefono_whatsapp, s.nombre AS sucursal_nombre, r.nombre AS ruta_nombre 
                                                     FROM clientes c 
                                                     LEFT JOIN rutas r ON c.ruta_id = r.id 
                                                     LEFT JOIN sucursales s ON r.fk_sucursal = s.id 
                                                     WHERE REPLACE(REPLACE(REPLACE(c.telefono_whatsapp, ' ', ''), '-', ''), '+', '') IN ($placeholders) LIMIT 1");
                        $stmt->execute($variantes);
                        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
                    }

                    if (!$cliente) {
                        $cliente = [
                            'id' => null,
                            'nombre' => $sender['name'] ?? ($sender['available_name'] ?? 'Cliente WhatsApp'),
                            'telefono_whatsapp' => $phone,
                            'ruta_nombre' => 'N/A',
                            'sucursal_nombre' => 'N/A'
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log("Error obteniendo conversación {$conversationId}: " . $e->getMessage());
            }
        }

        if (empty($cliente)) {
            throw new Exception("No se encontró información del cliente ni de la conversación.");
        }

        // Guardar vinculación local y marcar conversación como leída en Chatwoot
        if ($conversationId) {
            $this->guardarConversationIdLocal($conversationId);
            $this->marcarConversacionLeida($conversationId);
        }

        // 3. Traer los mensajes de Chatwoot
        $mensajes = [];
        $lastIncomingTimestamp = null;

        if ($conversationId) {
            $rawMessages = $this->getConversationMessages($conversationId);
            
            foreach ($rawMessages as $msg) {
                if (!$inboxId && !empty($msg['inbox_id'])) {
                    $inboxId = $msg['inbox_id'];
                }

                $createdAt = $msg['created_at'] ?? null;
                $createdAtTs = $this->parseTimestamp($createdAt);

                $msgType = $msg['message_type'] ?? null;
                $senderType = $msg['sender']['type'] ?? null;

                $isIncoming = ($senderType === 'contact' || $msgType === 0 || (string)$msgType === '0' || $msgType === 'incoming');
                if ($senderType === 'user' || $msgType === 1 || (string)$msgType === '1' || $msgType === 'outgoing') {
                    $isIncoming = false;
                }

                $sender = $msg['sender']['name'] ?? ($isIncoming ? 'Cliente' : 'Nosotros');

                if ($isIncoming && $createdAt) {
                    $ts = $createdAtTs;
                    if (!$lastIncomingTimestamp || $ts > $lastIncomingTimestamp) {
                        $lastIncomingTimestamp = $ts;
                    }
                }

                // Omitir mensajes anteriores a la fecha de corte (21/08/2026 00:00:00)
                if ($this->cutoffTimestamp > 0 && $createdAtTs < $this->cutoffTimestamp) {
                    continue;
                }

                $mensajes[] = [
                    'id' => $msg['id'] ?? null,
                    'content' => $msg['content'] ?? '',
                    'message_type' => $msg['message_type'] ?? 0,
                    'status' => $msg['status'] ?? 'sent',
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
            $is24hExpired = ($diffSeconds > 86400);
        }

        return [
            'cliente' => [
                'id' => $cliente['id'] ?? null,
                'nombre' => $cliente['nombre'] ?? 'Cliente WhatsApp',
                'telefono_whatsapp' => $cliente['telefono_whatsapp'] ?? '',
                'ruta_nombre' => $cliente['ruta_nombre'] ?? 'N/A',
                'sucursal_nombre' => $cliente['sucursal_nombre'] ?? 'N/A'
            ],
            'conversation_id' => $conversationId,
            'messages' => $mensajes,
            'is_24h_expired' => $is24hExpired,
            'last_incoming_at' => $lastIncomingTimestamp,
            'plantillas' => $this->obtenerPlantillas($inboxId)
        ];
    }

    /**
     * Consulta eventos destinatarios para el envío masivo de notificaciones de WhatsApp
     */
    public function obtenerEventosParaEnvioMasivo($fechaDesde, $fechaHasta, $estados = []) {
        if (empty($fechaDesde) || empty($fechaHasta)) {
            throw new Exception("Fechas 'desde' y 'hasta' son obligatorias.");
        }

        // Normalizar lista de estados permitidos y sinónimos
        $estadosDb = [];
        $estadosSolicitados = is_array($estados) ? $estados : explode(',', (string)$estados);
        $estadosSolicitados = array_map('trim', array_map('strtolower', $estadosSolicitados));

        if (empty($estadosSolicitados)) {
            $estadosSolicitados = ['programado'];
        }

        foreach ($estadosSolicitados as $est) {
            if ($est === 'programado') $estadosDb[] = 'programado';
            elseif ($est === 'notificacion1') $estadosDb[] = 'notificacion1';
            elseif ($est === 'notificacion2') $estadosDb[] = 'notificacion2';
            elseif ($est === 'notificacion3') $estadosDb[] = 'notificacion3';
            elseif ($est === 'rechazado' || $est === 'denegada') {
                $estadosDb[] = 'rechazado';
                $estadosDb[] = 'denegada';
            } elseif ($est === 'aceptado' || $est === 'aceptada') {
                $estadosDb[] = 'aceptado';
                $estadosDb[] = 'aceptada';
            }
        }

        $estadosDb = array_unique($estadosDb);
        if (empty($estadosDb)) {
            $estadosDb = ['programado'];
        }

        $placeholders = implode(',', array_fill(0, count($estadosDb), '?'));
        $sql = "SELECT 
                    e.id AS evento_id,
                    e.cliente_id,
                    e.fecha_programada,
                    e.estado,
                    e.tipo,
                    e.notificaciones,
                    c.nombre AS cliente_nombre,
                    c.telefono_whatsapp AS cliente_telefono,
                    r.nombre AS ruta_nombre,
                    s.nombre AS sucursal_nombre
                FROM eventos e
                JOIN clientes c ON e.cliente_id = c.id
                LEFT JOIN rutas r ON e.ruta_id = r.id
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                WHERE e.fecha_programada BETWEEN ? AND ?
                  AND e.estado::text IN ($placeholders)
                  AND c.telefono_whatsapp IS NOT NULL
                  AND TRIM(c.telefono_whatsapp) != ''
                ORDER BY e.fecha_programada ASC, c.nombre ASC";

        $params = array_merge([$fechaDesde, $fechaHasta], array_values($estadosDb));
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enviar plantilla de notificación de evento a un cliente mediante Chatwoot y actualizar estado
     */
    public function enviarNotificacionEventoMasivo($eventoId, $plantillaId = 'confirmacion_entrega', $paramsOverride = null) {
        // 1. Obtener datos del evento y cliente
        $sql = "SELECT 
                    e.id AS evento_id,
                    e.cliente_id,
                    e.fecha_programada,
                    e.estado,
                    e.notificaciones,
                    c.nombre AS cliente_nombre,
                    c.telefono_whatsapp AS cliente_telefono
                FROM eventos e
                JOIN clientes c ON e.cliente_id = c.id
                WHERE e.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $eventoId]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            throw new Exception("Evento #{$eventoId} no encontrado.");
        }

        $phone = $evento['cliente_telefono'];
        if (empty($phone)) {
            throw new Exception("El cliente no tiene teléfono configurado.");
        }

        // 2. Buscar o crear contacto y conversación en Chatwoot
        $contact = $this->searchContactByPhone($phone);
        $conversationId = null;

        if ($contact && isset($contact['id'])) {
            $conversations = $this->getContactConversations($contact['id']);
            if (!empty($conversations)) {
                $conversationId = $conversations[0]['id'] ?? null;
            }
        }

        if (!$conversationId) {
            // Crear conversación si no existe
            $inboxId = 2; // WhatsApp Inbox por defecto
            $contactId = $contact['id'] ?? null;
            if (!$contactId) {
                // Crear contacto
                $newContact = $this->makeRequest("/api/v1/accounts/{$this->accountId}/contacts", 'POST', [
                    'name' => $evento['cliente_nombre'],
                    'phone_number' => (strpos($phone, '+') === 0) ? $phone : ('+' . preg_replace('/\D/', '', $phone)),
                    'inbox_id' => $inboxId
                ]);
                $contactId = $newContact['payload']['contact']['id'] ?? ($newContact['id'] ?? null);
            }

            if ($contactId) {
                $newConv = $this->makeRequest("/api/v1/accounts/{$this->accountId}/conversations", 'POST', [
                    'contact_id' => $contactId,
                    'inbox_id' => $inboxId
                ]);
                $conversationId = $newConv['id'] ?? null;
            }
        }

        if (!$conversationId) {
            throw new Exception("No se pudo obtener o crear la conversación en Chatwoot para {$phone}.");
        }

        // 3. Formatear parámetros de la plantilla
        $fechaProg = $evento['fecha_programada'];
        $clienteNombre = $evento['cliente_nombre'];

        // Formato amigable de fecha (Ej: "Viernes, 21 de Agosto")
        $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $ts = strtotime($fechaProg);
        $fechaAmigable = $diasSemana[date('w', $ts)] . ', ' . date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)];

        $templateParams = null;
        $content = "";

        if ($plantillaId === 'confirmacion_entrega') {
            $content = "Recolección de Aceite\n\nHola {$clienteNombre}, te escribimos de OilBless (GreenFuel) para confirmar tu servicio del día {$fechaAmigable}. 🚛♻️\n\n¿Podemos pasar a retirar el aceite usado en esa fecha?";
            $templateParams = [
                'name' => 'confirmacion_entrega',
                'category' => 'MARKETING',
                'language' => 'es_CO',
                'processed_params' => [
                    '1' => $clienteNombre,
                    '2' => $fechaAmigable
                ]
            ];
        } elseif ($plantillaId === 'hola_oilbless') {
            $content = "¡Hola {$clienteNombre}! Bienvenido a nuestro canal oficial de WhatsApp de OilBless (GreenFuel). 🚛♻️";
            $templateParams = [
                'name' => 'hola_oilbless',
                'category' => 'MARKETING',
                'language' => 'es_CO',
                'processed_params' => [
                    '1' => $clienteNombre
                ]
            ];
        } elseif ($plantillaId === 'hello_world') {
            $content = "Hello World";
            $templateParams = [
                'name' => 'hello_world',
                'category' => 'UTILITY',
                'language' => 'en_US',
                'processed_params' => (object)[]
            ];
        } else {
            // Plantilla genérica
            $content = "Notificación de servicio OilBless para {$clienteNombre}.";
            $templateParams = is_array($paramsOverride) ? $paramsOverride : [
                'name' => $plantillaId,
                'category' => 'MARKETING',
                'language' => 'es_CO',
                'processed_params' => [
                    '1' => $clienteNombre,
                    '2' => $fechaAmigable
                ]
            ];
        }

        // 4. Enviar mensaje a través de Chatwoot
        $sendRes = $this->sendMessage($conversationId, $content, $templateParams);

        // 5. Determinar nuevo estado del evento y registrar en el historial de notificaciones
        $estadoActual = strtolower((string)$evento['estado']);
        $nuevoEstado = $estadoActual;

        if ($estadoActual === 'programado') {
            $nuevoEstado = 'notificacion1';
        } elseif ($estadoActual === 'notificacion1') {
            $nuevoEstado = 'notificacion2';
        } elseif ($estadoActual === 'notificacion2') {
            $nuevoEstado = 'notificacion3';
        }

        $historialNotificaciones = [];
        if (!empty($evento['notificaciones'])) {
            $historialNotificaciones = is_string($evento['notificaciones']) ? json_decode($evento['notificaciones'], true) : $evento['notificaciones'];
            if (!is_array($historialNotificaciones)) $historialNotificaciones = [];
        }

        $historialNotificaciones[] = [
            'timestamp' => date('c'),
            'accion' => 'envio_masivo_whatsapp',
            'plantilla' => $plantillaId,
            'conversation_id' => $conversationId,
            'estado_anterior' => $estadoActual,
            'nuevo_estado' => $nuevoEstado,
            'usuario_id' => $_SESSION['user_id'] ?? null
        ];

        // 6. Actualizar evento en la base de datos
        $stmtUp = $this->pdo->prepare("UPDATE eventos SET estado = :estado, notificaciones = :notificaciones, update_at = CURRENT_DATE WHERE id = :id");
        $stmtUp->execute([
            'estado' => $nuevoEstado,
            'notificaciones' => json_encode($historialNotificaciones),
            'id' => $eventoId
        ]);

        return [
            'success' => true,
            'evento_id' => $eventoId,
            'cliente_nombre' => $clienteNombre,
            'telefono' => $phone,
            'nuevo_estado' => $nuevoEstado,
            'conversation_id' => $conversationId,
            'chatwoot_response' => $sendRes
        ];
    }

    /**
     * Clasifica el mensaje de respuesta de un cliente
     * - 'aceptada': 'Sí, confirmar', 'Si, confirmar', 'si', etc.
     * - 'denegada': 'No, cambiar fecha', 'No cambiar fecha', 'no', etc.
     * - 'consulta': Cualquier otra consulta, pregunta o texto.
     */
    public static function clasificarRespuestaCliente($texto) {
        $limpio = mb_strtolower(trim((string)$texto), 'UTF-8');
        $normalizado = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $limpio);

        // 1. Caso Aceptado / Confirmado
        if (
            strpos($normalizado, 'si, confirmar') !== false ||
            strpos($normalizado, 'si confirmar') !== false ||
            $normalizado === 'si' ||
            $normalizado === 'sí' ||
            $normalizado === 'confirmar' ||
            $normalizado === 'confirmado' ||
            $normalizado === 'si, confirmado' ||
            $normalizado === 'si confirmado' ||
            $normalizado === 'listo' ||
            $normalizado === 'de acuerdo' ||
            $normalizado === 'claro'
        ) {
            return 'aceptada';
        }

        // 2. Caso Rechazado / Denegado / Cambiar fecha
        if (
            strpos($normalizado, 'no, cambiar fecha') !== false ||
            strpos($normalizado, 'no cambiar fecha') !== false ||
            strpos($normalizado, 'cambiar fecha') !== false ||
            $normalizado === 'no' ||
            $normalizado === 'rechazar' ||
            $normalizado === 'cancelar' ||
            strpos($normalizado, 'no puedo') !== false ||
            strpos($normalizado, 'no pasar') !== false
        ) {
            return 'denegada';
        }

        // 3. Cualquier otra respuesta ("consulta")
        return 'consulta';
    }

    /**
     * Sincroniza las respuestas de WhatsApp de clientes con eventos en estado notificacion1, 2 o 3.
     * Si respondieron, actualiza el estado del evento a 'aceptada', 'denegada' o 'consulta'.
     */
    public function sincronizarRespuestasEventosNotificados(array &$eventos) {
        if (empty($eventos)) return;

        // Filtrar eventos que estén en notificacion1, notificacion2, notificacion3
        $eventosNotificados = [];
        foreach ($eventos as &$ev) {
            $est = strtolower((string)($ev['estado_recoleccion'] ?? $ev['estado'] ?? ''));
            if (in_array($est, ['notificacion1', 'notificacion2', 'notificacion3']) && !empty($ev['id']) && !empty($ev['telefono_whatsapp'])) {
                $eventosNotificados[] = &$ev;
            }
        }

        if (empty($eventosNotificados)) return;

        // Buscar conversación de cada cliente en Chatwoot
        $eventosConConv = [];
        foreach ($eventosNotificados as &$ev) {
            $phone = $ev['telefono_whatsapp'];
            $contact = $this->searchContactByPhone($phone);
            if ($contact && isset($contact['id'])) {
                $convs = $this->getContactConversations($contact['id']);
                if (!empty($convs)) {
                    $convId = $convs[0]['id'] ?? null;
                    if ($convId) {
                        $ev['chatwoot_conversation_id'] = $convId;
                        $eventosConConv[$convId] = &$ev;
                    }
                }
            }
        }

        if (empty($eventosConConv)) return;

        // Obtener mensajes de las conversaciones en paralelo con curl_multi
        $mh = curl_multi_init();
        $curlHandles = [];
        $sep = (strpos($this->baseUrl, '?') === false) ? '?' : '&';

        foreach (array_keys($eventosConConv) as $cid) {
            $ch = curl_init();
            $url = "{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$cid}/messages" . $sep . "api_access_token=" . urlencode($this->apiToken);
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'api_access_token: ' . $this->apiToken,
                    'api-access-token: ' . $this->apiToken
                ]
            ]);
            curl_multi_add_handle($mh, $ch);
            $curlHandles[$cid] = $ch;
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 0.2);
            }
        } while ($running > 0 && $status == CURLM_OK);

        foreach ($curlHandles as $cid => $ch) {
            $content = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            $json = json_decode($content, true);
            $rawMsgs = isset($json['payload']) && is_array($json['payload']) ? $json['payload'] : (is_array($json) ? $json : []);

            if (empty($rawMsgs) || !isset($eventosConConv[$cid])) continue;

            $eventoRef = &$eventosConConv[$cid];

            // Buscar el último mensaje saliente (nuestra notificación) y el último mensaje entrante posterior
            $lastOutgoingTs = 0;
            $incomingResponse = null;

            for ($i = 0; $i < count($rawMsgs); $i++) {
                $m = $rawMsgs[$i];
                if (!is_array($m)) continue;
                $type = $m['message_type'] ?? null;
                $senderType = $m['sender']['type'] ?? ($m['sender_type'] ?? null);
                $createdTs = $this->parseTimestamp($m['created_at'] ?? 0);

                if ($this->cutoffTimestamp > 0 && $createdTs < $this->cutoffTimestamp) {
                    continue;
                }

                $isOutgoing = ($senderType === 'user' || $type === 1 || (string)$type === '1' || $type === 'outgoing');
                if ($isOutgoing && $createdTs > $lastOutgoingTs) {
                    $lastOutgoingTs = $createdTs;
                }
            }

            // Buscar si hay respuesta entrante del cliente posterior a nuestro último mensaje
            for ($i = count($rawMsgs) - 1; $i >= 0; $i--) {
                $m = $rawMsgs[$i];
                if (!is_array($m)) continue;
                $type = $m['message_type'] ?? null;
                $senderType = $m['sender']['type'] ?? ($m['sender_type'] ?? null);
                $createdTs = $this->parseTimestamp($m['created_at'] ?? 0);

                if ($this->cutoffTimestamp > 0 && $createdTs < $this->cutoffTimestamp) {
                    break;
                }

                $isIncoming = ($senderType === 'contact' || $type === 0 || (string)$type === '0' || $type === 'incoming');
                if ($isIncoming) {
                    if ($lastOutgoingTs === 0 || $createdTs >= $lastOutgoingTs) {
                        $incomingResponse = $m['content'] ?? '';
                        break;
                    }
                } else {
                    break;
                }
            }

            if ($incomingResponse !== null && trim($incomingResponse) !== '') {
                $nuevoEstado = self::clasificarRespuestaCliente($incomingResponse);

                // Actualizar en base de datos
                $eventoId = $eventoRef['id'];
                $historial = !empty($eventoRef['notificaciones']) ? (is_string($eventoRef['notificaciones']) ? json_decode($eventoRef['notificaciones'], true) : $eventoRef['notificaciones']) : [];
                if (!is_array($historial)) $historial = [];

                $historial[] = [
                    'timestamp' => date('c'),
                    'accion' => 'respuesta_cliente_detectada',
                    'respuesta_texto' => $incomingResponse,
                    'nuevo_estado' => $nuevoEstado
                ];

                $stmtUp = $this->pdo->prepare("UPDATE eventos SET estado = :estado, notificaciones = :notificaciones, update_at = CURRENT_DATE WHERE id = :id");
                $stmtUp->execute([
                    'estado' => $nuevoEstado,
                    'notificaciones' => json_encode($historial),
                    'id' => $eventoId
                ]);

                // Actualizar array en memoria
                $eventoRef['estado'] = $nuevoEstado;
                $eventoRef['estado_recoleccion'] = $nuevoEstado;
            }
        }
        curl_multi_close($mh);
    }
}
