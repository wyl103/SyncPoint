<?php
// app/api/eventos/envio_masivo.php
header('Content-Type: application/json; charset=utf-8');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../integrations/chatwoot/ChatwootService.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $service = new ChatwootService();

    if ($method === 'GET') {
        $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-d');
        $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d', strtotime('+2 days'));
        $estadosParam = $_GET['estados'] ?? 'programado';

        $estados = is_array($estadosParam) ? $estadosParam : explode(',', (string)$estadosParam);
        $estados = array_filter(array_map('trim', $estados));

        $eventos = $service->obtenerEventosParaEnvioMasivo($fechaDesde, $fechaHasta, $estados);

        echo json_encode([
            'success' => true,
            'total' => count($eventos),
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'estados' => $estados,
            'eventos' => $eventos
        ]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $fechaDesde = $input['fecha_desde'] ?? date('Y-m-d');
        $fechaHasta = $input['fecha_hasta'] ?? date('Y-m-d', strtotime('+2 days'));
        $estados = $input['estados'] ?? ['programado'];
        $plantillaId = $input['plantilla_id'] ?? 'confirmacion_entrega';
        $eventosIdsSeleccionados = $input['eventos_ids'] ?? null;

        // Consultar eventos que cumplen el criterio
        $eventos = $service->obtenerEventosParaEnvioMasivo($fechaDesde, $fechaHasta, $estados);

        if (!empty($eventosIdsSeleccionados) && is_array($eventosIdsSeleccionados)) {
            $selectedMap = array_flip($eventosIdsSeleccionados);
            $eventos = array_filter($eventos, function($ev) use ($selectedMap) {
                return isset($selectedMap[$ev['evento_id']]);
            });
        }

        if (empty($eventos)) {
            echo json_encode([
                'success' => false,
                'message' => 'No hay eventos destinatarios que coincidan con los filtros seleccionados.',
                'total' => 0,
                'enviados' => 0,
                'fallidos' => 0
            ]);
            exit;
        }

        $enviados = 0;
        $fallidos = 0;
        $detalles = [];

        foreach ($eventos as $ev) {
            $eventoId = $ev['evento_id'];
            $clienteNombre = $ev['cliente_nombre'];
            $telefono = $ev['cliente_telefono'];

            try {
                $res = $service->enviarNotificacionEventoMasivo($eventoId, $plantillaId);
                $enviados++;
                $detalles[] = [
                    'evento_id' => $eventoId,
                    'cliente_nombre' => $clienteNombre,
                    'telefono' => $telefono,
                    'success' => true,
                    'nuevo_estado' => $res['nuevo_estado'] ?? 'notificacion1'
                ];
            } catch (Exception $e) {
                $fallidos++;
                $detalles[] = [
                    'evento_id' => $eventoId,
                    'cliente_nombre' => $clienteNombre,
                    'telefono' => $telefono,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'total' => count($eventos),
            'enviados' => $enviados,
            'fallidos' => $fallidos,
            'plantilla_usada' => $plantillaId,
            'detalles' => $detalles
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
