<?php
// app/api/recolecciones/dia.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../models/core/eventos.php';

$fecha = $_GET['fecha'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? 'todos';
$sucursal = $_GET['sucursal'] ?? 'todas';

try {
    $eventoModel = new Evento();
    $datos = $eventoModel->obtenerEventosYTentativosDelDia($fecha, $estado, $sucursal);
    
    echo json_encode([
        'success' => true,
        'fecha' => $fecha,
        'data' => $datos
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}