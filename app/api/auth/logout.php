<?php
// app/api/auth/logout.php

session_start();

// 1. Vaciar todas las variables de la sesión actual
$_SESSION = array();

// 2. Destruir la cookie de sesión en el navegador del usuario
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Responder al JavaScript
header('Content-Type: application/json');
echo json_encode([
    'success' => true, 
    'message' => 'Sesión cerrada exitosamente'
]);