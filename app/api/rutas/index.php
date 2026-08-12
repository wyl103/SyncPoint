<?php
// app/api/rutas/index.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../controllers/RutaController.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $controller = new RutaController();

    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        $sucursalId = $_GET['sucursal_id'] ?? null;

        if ($id) {
            $data = $controller->show($id);
        } else {
            $data = $controller->index($sucursalId);
        }
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $controller->store($input);
        echo json_encode(['success' => true, 'message' => 'Ruta creada exitosamente', 'id' => $id]);
    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? $input['id'] ?? null;
        $controller->update($id, $input);
        echo json_encode(['success' => true, 'message' => 'Ruta actualizada exitosamente']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        $controller->destroy($id);
        echo json_encode(['success' => true, 'message' => 'Ruta eliminada exitosamente']);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
