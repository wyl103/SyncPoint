<?php
// app/api/recolecciones/rango.php
header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); echo json_encode(['success' => false]); exit;
}

require_once __DIR__ . '/../../models/Recoleccion.php';

$inicio = $_GET['inicio'] ?? date('Y-m-d');
$fin = $_GET['fin'] ?? date('Y-m-d');

try {
    $modelo = new Recoleccion();
    $pdo = $modelo->getPdo(); // Necesitarás agregar public function getPdo() { return $this->pdo; } en tu modelo Recoleccion.php
    
    $sql = "SELECT fecha_programada, COUNT(id) as total 
            FROM recolecciones 
            WHERE fecha_programada BETWEEN :inicio AND :fin 
            GROUP BY fecha_programada";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['inicio' => $inicio, 'fin' => $fin]);
    $resultados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Devuelve ['2026-02-24' => 3, '2026-02-25' => 1]
    
    echo json_encode(['success' => true, 'data' => $resultados]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}