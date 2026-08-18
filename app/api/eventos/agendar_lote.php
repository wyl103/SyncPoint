<?php
// app/api/eventos/agendar_lote.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado. Inicie sesión.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido. Use POST.']);
    exit;
}

require_once __DIR__ . '/../../services/eventos/EventCalculatorService.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $clienteId = $input['cliente_id'] ?? null;
    $fechas = $input['fechas'] ?? [];
    $rutaId = $input['ruta_id'] ?? null;
    $estado = $input['estado'] ?? 'programado';
    $tipo = $input['tipo'] ?? 'frecuente';
    $origen = $input['evento_origin'] ?? $input['origen'] ?? 'user';

    if (empty($clienteId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El parámetro cliente_id es obligatorio.']);
        exit;
    }

    if (empty($fechas) || !is_array($fechas)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El parámetro fechas debe ser un arreglo de cadenas YYYY-MM-DD.']);
        exit;
    }

    $calculator = new EventCalculatorService();
    $resultado = $calculator->agendarFechasLote($clienteId, $fechas, $rutaId, $estado, $tipo, $origen);

    echo json_encode([
        'success' => true,
        'message' => 'Fechas agendadas correctamente en lote.',
        'data' => $resultado
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
