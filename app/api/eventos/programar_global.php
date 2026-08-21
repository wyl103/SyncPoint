<?php
// app/api/eventos/programar_global.php
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
    $diasHorizonte = $input['dias_horizonte'] ?? $_GET['dias_horizonte'] ?? 30;

    $calculator = new EventCalculatorService();
    $resultado = $calculator->programarEventosGlobales($diasHorizonte);

    echo json_encode([
        'success' => true,
        'message' => "Eventos programados masivamente correctamente para los próximos {$resultado['dias_horizonte']} días.",
        'data' => $resultado
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
