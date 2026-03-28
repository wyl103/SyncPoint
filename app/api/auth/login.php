<?php

require_once __DIR__ . '/../../services/Database.php';

// Configurar la cookie de sesión para que sea segura y accesible solo por HTTP
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

// Tiempo de vida de la sesión (ej: 2 horas)
$sessionLifetime = 7200;
session_set_cookie_params($sessionLifetime);
session_start();

header('Content-Type: application/json');

// Leer datos enviados desde el fetch() de JS
$input = json_decode(file_get_contents('php://input'), true);
$correo = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($correo) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Faltan credenciales']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT id, nombre, clave FROM usuarios WHERE correo = ?");
$stmt->execute([$correo]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($password, $usuario['clave'])) {
    // Regenerar ID de sesión para prevenir Session Fixation (Rehacer la cookie)
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_nombre'] = $usuario['nombre'];
    $_SESSION['last_activity'] = time(); // Marca de tiempo para mantenerla viva
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login exitoso',
        'redirect' => '/dashboard'
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
}