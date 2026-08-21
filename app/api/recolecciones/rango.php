<?php
// app/api/recolecciones/rango.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../models/core/eventos.php';

$inicio = $_GET['inicio'] ?? date('Y-m-d');
$fin = $_GET['fin'] ?? date('Y-m-d');

try {
    $eventoModel = new Evento();
    $resultados = $eventoModel->obtenerConteoEventosYTentativosPorRango($inicio, $fin);
    
    echo json_encode(['success' => true, 'data' => $resultados]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}