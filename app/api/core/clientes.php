<?php
// app/api/core/clientes.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../controllers/core/clientes.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $controller = new ClienteController();

    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $data = $controller->show($id);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            $busqueda = $_GET['q'] ?? $_GET['busqueda'] ?? null;
            $rutaId = $_GET['ruta_id'] ?? null;
            $sucursalId = $_GET['sucursal_id'] ?? null;
            $estado = $_GET['estado'] ?? null;
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            $result = $controller->index($busqueda, $rutaId, $sucursalId, $estado, $page, $limit);
            echo json_encode([
                'success' => true, 
                'data' => $result['data'],
                'pagination' => [
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'total' => $result['total'],
                    'total_pages' => $result['total_pages']
                ]
            ]);
        }
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $controller->store($input);
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Cliente creado exitosamente', 'id' => $id]);
    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $_GET['id'] ?? $input['id'] ?? null;
        $controller->update($id, $input);
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente']);
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $_GET['id'] ?? $input['id'] ?? null;
        $controller->destroy($id);
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado exitosamente']);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    error_log("Error en clientes API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
