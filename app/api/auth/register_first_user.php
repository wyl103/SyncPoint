<?php
// app/api/auth/register_first_user.php
header('Content-Type: application/json');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
session_start();

require_once __DIR__ . '/../../services/Database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    $db = new Database();
    $pdo = $db->getConnection();

    // Regla de seguridad: Solo se permite registrar si NO hay usuarios creados
    $stmtCount = $pdo->query("SELECT COUNT(id) FROM usuarios");
    $totalUsuarios = (int)$stmtCount->fetchColumn();

    if ($totalUsuarios > 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Ya existen usuarios registrados en el sistema.']);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? $_POST;

    $nombre = trim($input['nombre'] ?? '');
    $correo = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($nombre) || empty($correo) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos: nombre, correo y contraseña.']);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
        exit;
    }

    // Encriptar la contraseña con el algoritmo BCrypt de PHP
    $claveHash = password_hash($password, PASSWORD_BCRYPT);

    $stmtInsert = $pdo->prepare("INSERT INTO usuarios (nombre, correo, clave) VALUES (:nombre, :correo, :clave) RETURNING id");
    $stmtInsert->execute([
        'nombre' => $nombre,
        'correo' => $correo,
        'clave' => $claveHash
    ]);

    $newId = $stmtInsert->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => '¡Usuario administrador creado con éxito! Ya puedes iniciar sesión.',
        'id' => $newId,
        'email' => $correo
    ]);
} catch (PDOException $e) {
    error_log("Error BD en register_first_user: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de Base de Datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error en register_first_user: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en Servidor: ' . $e->getMessage()]);
}
