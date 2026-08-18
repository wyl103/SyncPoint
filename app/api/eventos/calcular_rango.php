<?php
// app/api/eventos/calcular_rango.php
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
    $fechaDesde = $input['desde'] ?? $input['fecha_desde'] ?? $_GET['desde'] ?? $_GET['fecha_desde'] ?? null;
    $fechaHasta = $input['hasta'] ?? $input['fecha_hasta'] ?? $_GET['hasta'] ?? $_GET['fecha_hasta'] ?? null;

    if (empty($clienteId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El parámetro cliente_id es obligatorio.']);
        exit;
    }
    if (empty($fechaDesde) || empty($fechaHasta)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Las fechas desde y hasta son obligatorias.']);
        exit;
    }

    $calculator = new EventCalculatorService();
    $resultado = $calculator->calcularFechasPorRango($clienteId, $fechaDesde, $fechaHasta);

    echo json_encode([
        'success' => true,
        'message' => 'Fechas proyectadas por rango calculadas correctamente.',
        'data' => $resultado
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
