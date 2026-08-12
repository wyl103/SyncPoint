<?php
// app/api/auth/check_session.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_start();

try {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_nombre'] ?? 'Usuario',
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['authenticated' => false, 'message' => 'No hay sesión activa']);
    }
} catch (Exception $e) {
    error_log("Error en check_session: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['authenticated' => false, 'message' => 'Error en el servidor']);
}