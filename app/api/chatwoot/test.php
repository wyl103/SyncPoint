<?php
// app/api/chatwoot/test.php
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

require_once __DIR__ . '/../../integrations/chatwoot/ChatwootService.php';

try {
    $envFile = __DIR__ . '/../../../.env';
    $env = (file_exists($envFile) && is_readable($envFile)) ? @parse_ini_file($envFile) : [];
    if (!is_array($env)) $env = [];

    $baseUrl   = getenv('CHATWOOT_BASE_URL') ?: ($env['CHATWOOT_BASE_URL'] ?? 'https://chat.oilbless.com');
    $baseUrl   = trim(rtrim($baseUrl, '/'));
    $accountId = getenv('CHATWOOT_ACCOUNT_ID') ?: ($env['CHATWOOT_ACCOUNT_ID'] ?? '1');
    $apiToken  = getenv('CHATWOOT_API_TOKEN') ?: ($env['CHATWOOT_API_TOKEN'] ?? '');

    $testPhone = $_GET['phone'] ?? '3106288747';
    $cleanPhone = preg_replace('/\D/', '', $testPhone);

    // Helper curl que prueba enviar el token por Header y por Query Param (evita descarte de guiones bajos por Nginx)
    $makeCurl = function($endpoint) use ($baseUrl, $apiToken) {
        $sep = (strpos($endpoint, '?') === false) ? '?' : '&';
        $url = "{$baseUrl}{$endpoint}{$sep}api_access_token=" . urlencode($apiToken);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'api_access_token: ' . $apiToken,
            'api-access-token: ' . $apiToken,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) SyncPoint/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        return [
            'url' => $url,
            'http_code' => $code,
            'curl_error' => $err ?: null,
            'response' => json_decode($res, true) ?? $res
        ];
    };

    // 1. Probar búsqueda de contacto
    $searchRes = $makeCurl("/api/v1/accounts/{$accountId}/contacts/search?q=" . urlencode($cleanPhone));

    $contactId = null;
    if (isset($searchRes['response']['payload'][0]['id'])) {
        $contactId = $searchRes['response']['payload'][0]['id'];
    }

    // 2. Probar obtención de conversaciones si el contacto fue hallado
    $conversationsRes = null;
    if ($contactId) {
        $conversationsRes = $makeCurl("/api/v1/accounts/{$accountId}/contacts/{$contactId}/conversations");
    }

    echo json_encode([
        'success' => ($searchRes['http_code'] >= 200 && $searchRes['http_code'] < 300),
        'configuracion' => [
            'base_url' => $baseUrl,
            'account_id' => $accountId,
            'api_token_mascarado' => !empty($apiToken) ? (substr($apiToken, 0, 4) . '...' . substr($apiToken, -4)) : 'VACÍO',
            'telefono_probado' => $testPhone
        ],
        'paso_1_busqueda_contacto' => $searchRes,
        'paso_2_conversaciones_contacto' => $conversationsRes ?: 'No se ejecutó porque el contacto no fue hallado en paso 1'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
