<?php
// router.php - Enrutador para el servidor de desarrollo nativo de PHP (`php -S`)
// Ejecutar desde la raíz del proyecto: php -S localhost:1019 router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Archivo estático o script directo en la raíz
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false; // Dejar que PHP sirva el archivo directo
}

// Limpiar posible prefijo /app_bless o /app_bless/public
$cleanUri = preg_replace('/^\/app_bless(\/public)?/', '', $uri);
if (empty($cleanUri)) $cleanUri = '/';

// 2. Petición a la API (/app/api/...)
if (strpos($cleanUri, '/app/api/') === 0 || strpos($uri, '/app/api/') === 0) {
    $apiFile = __DIR__ . (strpos($cleanUri, '/app/api/') === 0 ? $cleanUri : $uri);
    if (file_exists($apiFile) && !is_dir($apiFile)) {
        require $apiFile;
        exit;
    }
}

// 3. Archivos estáticos dentro de /public/ (ej: /js/auth.js, /css/output.css)
$publicFile = __DIR__ . '/public' . $cleanUri;
if ($cleanUri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff2'=> 'font/woff2'
    ];
    if (isset($mimeTypes[$ext])) {
        header("Content-Type: " . $mimeTypes[$ext]);
    }
    readfile($publicFile);
    exit;
}

// 4. Si la petición es la vista principal o cualquier subruta SPA (login, dash, etc.)
require_once __DIR__ . '/public/index.php';
