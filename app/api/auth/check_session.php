<?php
// app/api/auth/check_session.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_start();

require_once __DIR__ . '/../../services/Database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $stmtCount = $pdo->query("SELECT COUNT(id) FROM usuarios");
    $totalUsuarios = (int)$stmtCount->fetchColumn();
    $hasUsers = ($totalUsuarios > 0);

    if (!$hasUsers) {
        http_response_code(200);
        echo json_encode([
            'authenticated' => false,
            'has_users' => false,
            'message' => 'No existen usuarios registrados.'
        ]);
        exit;
    }

    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'authenticated' => true,
            'has_users' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_nombre'] ?? 'Usuario',
            ]
        ]);
    } else {
        http_response_code(200);
        echo json_encode([
            'authenticated' => false,
            'has_users' => true,
            'message' => 'No hay sesión activa'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en check_session: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['authenticated' => false, 'has_users' => true, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}