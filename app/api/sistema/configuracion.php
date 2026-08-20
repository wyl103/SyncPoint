<?php
// app/api/sistema/configuracion.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../services/AppConfig.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $config = AppConfig::getAll();
        echo json_encode([
            'success' => true,
            'data' => $config
        ]);
    } else if ($method === 'POST' || $method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        if (isset($input['programacion_usar_dia_ruta'])) {
            $val = filter_var($input['programacion_usar_dia_ruta'], FILTER_VALIDATE_BOOLEAN);
            AppConfig::set('programacion_usar_dia_ruta', $val);
        }

        $config = AppConfig::getAll();
        echo json_encode([
            'success' => true,
            'message' => 'Configuración actualizada correctamente',
            'data' => $config
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
