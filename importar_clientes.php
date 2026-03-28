<?php
// agendar_semanales.php
require_once __DIR__ . '/app/services/Database.php';

// 1. Diccionario para traducir los días de tu base de datos a un formato que PHP entienda
function obtenerProximaFecha($diaRuta, $fechaBaseStr) {
    $mapaDias = [
        'Lunes'     => 'Monday',
        'Martes'    => 'Tuesday',
        'Miércoles' => 'Wednesday',
        'Jueves'    => 'Thursday',
        'Viernes'   => 'Friday',
        'Sábado'    => 'Saturday',
        'Domingo'   => 'Sunday'
    ];

    $diaIngles = $mapaDias[$diaRuta] ?? null;
    
    if (!$diaIngles) {
        return null; // Si la ruta se llama diferente (ej. "Norte"), esto devolverá null
    }

    $fechaBase = new DateTime($fechaBaseStr);
    
    // Si la fecha base cae exactamente en el día que buscamos, agendamos para ese mismo día
    if ($fechaBase->format('l') === $diaIngles) {
        return $fechaBase->format('Y-m-d');
    } else {
        // Si no, buscamos el "próximo [Día]" a partir de la fecha base
        $fechaBase->modify("next $diaIngles");
        return $fechaBase->format('Y-m-d');
    }
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    $pdo->beginTransaction();

    // Fecha desde donde empezamos a agendar (Ajusta el año si es necesario)
    $fechaCorte = '2026-02-27'; 

    // 2. Traer todos los clientes semanales (frecuencia_id = 1) uniéndolos con su ruta
    $sql = "SELECT c.id AS cliente_id, c.nombre AS cliente_nombre, 
                   r.id AS ruta_id, r.nombre AS ruta_nombre
            FROM clientes c
            INNER JOIN rutas r ON c.ruta_id = r.id
            WHERE c.frecuencia_id = 1"; // 1 = Semanal
            
    $stmtClientes = $pdo->query($sql);
    $clientesSemanales = $stmtClientes->fetchAll();

    $insertados = 0;
    $omitidos = 0;

    // 3. Preparar los queries de inserción y actualización
    $stmtInsertRecoleccion = $pdo->prepare("
        INSERT INTO recolecciones (cliente_id, ruta_id, fecha_programada, estado) 
        VALUES (:cliente_id, :ruta_id, :fecha, 'pendiente')
    ");

    $stmtUpdateCliente = $pdo->prepare("
        UPDATE clientes SET estado = 'agendado' WHERE id = :cliente_id
    ");

    // 4. Recorrer y agendar
    foreach ($clientesSemanales as $cliente) {
        $fechaProgramada = obtenerProximaFecha($cliente['ruta_nombre'], $fechaCorte);

        // Si la ruta tenía un nombre de día válido
        if ($fechaProgramada) {
            // Insertar la recolección
            $stmtInsertRecoleccion->execute([
                'cliente_id' => $cliente['cliente_id'],
                'ruta_id'    => $cliente['ruta_id'],
                'fecha'      => $fechaProgramada
            ]);

            // Actualizar el estado del cliente a 'agendado'
            $stmtUpdateCliente->execute([
                'cliente_id' => $cliente['cliente_id']
            ]);

            $insertados++;
        } else {
            // Si la ruta se llama "Ruta 1" en vez de "Lunes", la omitimos
            $omitidos++;
        }
    }

    $pdo->commit();
    echo "<h2 style='color:green;'>¡Agendamiento Semanal Exitoso!</h2>";
    echo "<p>Recolecciones programadas a partir del <strong>{$fechaCorte}</strong>: <strong>{$insertados}</strong></p>";
    if ($omitidos > 0) {
        echo "<p style='color:orange;'>Se omitieron {$omitidos} clientes porque el nombre de su ruta no era un día de la semana válido.</p>";
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h2 style='color:red;'>Error al agendar:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>