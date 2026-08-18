<?php
// app/api/eventos/recalcular.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../services/eventos/EventRecalculatorService.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido. Utilice POST.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $clienteId = $input['cliente_id'] ?? null;
    $fechaCambio = $input['fecha_cambio'] ?? date('Y-m-d');
    $frecuenciaId = $input['frecuencia_id'] ?? $input['nueva_frecuencia_id'] ?? null;
    $dias = $input['dias'] ?? $input['nuevos_dias'] ?? null;
    $origen = $input['evento_origin'] ?? $input['origen'] ?? 'user';

    $recalculator = new EventRecalculatorService();
    $resultado = $recalculator->recalcularEventosPorCambioFrecuencia($clienteId, $fechaCambio, $frecuenciaId, $dias, $origen);

    echo json_encode([
        'success' => true,
        'message' => 'Eventos recalculados exitosamente tras cambio de frecuencia',
        'data' => $resultado
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
