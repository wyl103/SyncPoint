<?php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); echo json_encode(['success' => false]); exit;
}

require_once __DIR__ . '/../../models/Sistema.php';

try {
    $sistema = new Sistema();
    $estados = $sistema->obtenerEstadosRecoleccion();
    $sucursales = $sistema->obtenerSucursalesFiltro();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'estados' => $estados,
            'sucursales' => $sucursales
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}