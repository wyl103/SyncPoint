<?php
// app/api/chatwoot/index.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../integrations/chatwoot/ChatwootService.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $service = new ChatwootService();

    if ($method === 'GET') {
        $clienteId = $_GET['cliente_id'] ?? null;
        if (!$clienteId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Parámetro cliente_id requerido']);
            exit;
        }

        $data = $service->obtenerChatCliente($clienteId);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? $_POST;

        $conversationId = $input['conversation_id'] ?? null;
        $content = trim($input['content'] ?? '');

        if (!$conversationId || empty($content)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'conversation_id y contenido del mensaje son obligatorios']);
            exit;
        }

        $res = $service->sendMessage($conversationId, $content);
        echo json_encode(['success' => true, 'message' => 'Mensaje enviado a Chatwoot', 'response' => $res]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    error_log("Error en Chatwoot API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
