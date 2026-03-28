<?php
// app/api/recolecciones/dia.php
header('Content-Type: application/json');

// Mantenemos la seguridad verificando la sesión (opcional pero recomendado)
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../controllers/RecoleccionController.php';

// Recibimos la fecha por GET, si no viene, usamos la fecha actual del servidor
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? 'todos';
$sucursal = $_GET['sucursal'] ?? 'todas';

try {
    $controller = new RecoleccionController();
    $datos = $controller->obtenerDelDia($fecha, $estado, $sucursal);
    
    echo json_encode([
        'success' => true,
        'fecha' => $fecha,
        'data' => $datos
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}