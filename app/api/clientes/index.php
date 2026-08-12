<?php
// app/api/clientes/index.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../controllers/ClienteController.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $controller = new ClienteController();

    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $data = $controller->show($id);
        } else {
            $busqueda = $_GET['q'] ?? $_GET['busqueda'] ?? null;
            $rutaId = $_GET['ruta_id'] ?? null;
            $sucursalId = $_GET['sucursal_id'] ?? null;
            $estado = $_GET['estado'] ?? null;

            $data = $controller->index($busqueda, $rutaId, $sucursalId, $estado);
        }
        echo json_encode(['success' => true, 'total' => is_array($data) ? count($data) : 1, 'data' => $data]);
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $controller->store($input);
        echo json_encode(['success' => true, 'message' => 'Cliente creado exitosamente', 'id' => $id]);
    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? $input['id'] ?? null;
        $controller->update($id, $input);
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        $controller->destroy($id);
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
