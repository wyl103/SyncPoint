<?php
// app/api/eventos/calcular_cantidad.php
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

require_once __DIR__ . '/../../services/eventos/EventCalculatorService.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    $clienteId = $input['cliente_id'] ?? $_GET['cliente_id'] ?? null;
    $fechaInicio = $input['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? null;
    $cantidad = $input['cantidad'] ?? $_GET['cantidad'] ?? 6;

    if (empty($clienteId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El parámetro cliente_id es obligatorio.']);
        exit;
    }

    $calculator = new EventCalculatorService();
    $resultado = $calculator->calcularFechasPorCantidad($clienteId, $fechaInicio, $cantidad);

    echo json_encode([
        'success' => true,
        'message' => 'Fechas proyectadas por cantidad calculadas correctamente.',
        'data' => $resultado
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
