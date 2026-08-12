<?php
// app/api/auth/login.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');

$sessionLifetime = 7200;
session_set_cookie_params([
    'lifetime' => $sessionLifetime,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once __DIR__ . '/../../services/Database.php';

try {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    $correo = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($correo) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Por favor ingrese correo y contraseña.']);
        exit;
    }

    $db = new Database();
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("SELECT id, nombre, clave FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['clave'])) {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['last_activity'] = time();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login exitoso',
            'user' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    }
} catch (PDOException $e) {
    error_log("Error de BD en login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ocurrió un problema en el servidor. Inténtelo más tarde.']);
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ocurrió un problema en el servidor. Inténtelo más tarde.']);
}