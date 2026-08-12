<?php
// app/api/core/rutas.php
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

require_once __DIR__ . '/../../controllers/core/rutas.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $controller = new RutaController();

    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $data = $controller->show($id);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            $busqueda = $_GET['q'] ?? $_GET['busqueda'] ?? null;
            $sucursalId = $_GET['sucursal_id'] ?? $_GET['fk_sucursal'] ?? null;
            $ciudad = $_GET['ciudad'] ?? null;
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            $result = $controller->index($busqueda, $sucursalId, $ciudad, $page, $limit);
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
        echo json_encode(['success' => true, 'message' => 'Ruta creada exitosamente', 'id' => $id]);
    } elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $_GET['id'] ?? $input['id'] ?? null;
        $controller->update($id, $input);
        echo json_encode(['success' => true, 'message' => 'Ruta actualizada exitosamente']);
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $_GET['id'] ?? $input['id'] ?? null;
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
