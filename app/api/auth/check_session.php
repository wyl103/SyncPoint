<?php
// app/api/auth/check_session.php

// Mismas configuraciones estrictas del login
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

header('Content-Type: application/json');

// Revisar si existe el ID de usuario en la sesión
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'nombre' => $_SESSION['user_nombre'],
            // Puedes enviar más datos no sensibles aquí
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['authenticated' => false]);
}