<?php
// app/services/eventos/EventRecalculatorService.php
require_once __DIR__ . '/../../services/Database.php';
require_once __DIR__ . '/../../services/eventos/EventCalculatorService.php';

class EventRecalculatorService {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Devuelve el nombre del día de la semana en español para una fecha YYYY-MM-DD
     */
    public static function obtenerNombreDiaSemana($fechaStr) {
        $ts = strtotime($fechaStr);
        $w = (int)date('w', $ts);
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
        return $dias[$w] ?? 'Lunes';
    }

    /**
     * Recalcula las fechas programadas de los eventos futuros de un cliente
     * cuando cambia su recurrencia / fecha base.
     *
     * 1. Determina el día de la semana de la nueva fecha.
     * 2. Busca la ruta correspondiente a ese día en la sucursal del cliente;
     *    si no existe, la crea automáticamente.
     * 3. Asigna la nueva ruta al cliente.
     * 4. Elimina todas las recolecciones programadas previas a partir de esa fecha
     *    (manteniendo intactas las fechas pasadas y las ya completadas/aceptadas).
     * 5. Proyecta las nuevas recolecciones recurrentes (próximos 6 ciclos) sobre la nueva ruta.
     * 6. Retorna un resumen con las alertas e información detallada para el usuario.
     */
    public function recalcularEventosPorCambioFrecuencia($clienteId, $fechaCambio, $nuevaFrecuenciaId = null, $nuevosDias = null, $origen = 'user') {
        if (empty($clienteId)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        if (empty($fechaCambio)) {
            throw new Exception("La fecha de inicio del cambio (fecha_cambio) es obligatoria.");
        }

        // 1. Obtener información completa del cliente, su ruta actual y sucursal
        $stmtCliente = $this->pdo->prepare("
            SELECT c.id, c.nombre, c.ruta_id, c.frecuencia_id, c.fecha_base,
                   r.fk_sucursal, r.ciudad, r.nombre AS ruta_actual_nombre,
                   s.id AS sucursal_id, s.nombre AS sucursal_nombre,
                   f.dias AS frecuencia_dias_actual, f.nombre AS frecuencia_nombre_actual
            FROM clientes c
            LEFT JOIN rutas r ON c.ruta_id = r.id
            LEFT JOIN sucursales s ON r.fk_sucursal = s.id
            LEFT JOIN frecuencias f ON c.frecuencia_id = f.id
            WHERE c.id = :id
        ");
        $stmtCliente->execute(['id' => $clienteId]);
        $cRow = $stmtCliente->fetch();

        if (!$cRow) {
            throw new Exception("Cliente #{$clienteId} no encontrado.");
        }

        // 2. Determinar días de la frecuencia
        if (empty($nuevosDias) && !empty($nuevaFrecuenciaId)) {
            $stmtFrec = $this->pdo->prepare("SELECT dias, nombre FROM frecuencias WHERE id = :id");
            $stmtFrec->execute(['id' => $nuevaFrecuenciaId]);
            $frecRow = $stmtFrec->fetch();
            if ($frecRow) {
                $nuevosDias = (int)$frecRow['dias'];
            }
        }

        if (empty($nuevosDias)) {
            if (!empty($cRow['frecuencia_dias_actual'])) {
                $nuevosDias = (int)$cRow['frecuencia_dias_actual'];
            }
            if (empty($nuevaFrecuenciaId)) {
                $nuevaFrecuenciaId = $cRow['frecuencia_id'];
            }
        }

        if (empty($nuevosDias) || $nuevosDias <= 0) {
            $nuevosDias = 15; // Quincenal por defecto si no tiene
        }

        // 3. Determinar el día de la semana de la fecha elegida
        $diaSemanaNombre = self::obtenerNombreDiaSemana($fechaCambio);
        $diaSinAcento = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], mb_strtolower($diaSemanaNombre));

        // 4. Determinar la sucursal del cliente
        $sucursalId = !empty($cRow['fk_sucursal']) ? (int)$cRow['fk_sucursal'] : (!empty($cRow['sucursal_id']) ? (int)$cRow['sucursal_id'] : null);
        if (!$sucursalId) {
            $stmtSucDef = $this->pdo->query("SELECT id, nombre FROM sucursales ORDER BY destacada DESC, id ASC LIMIT 1");
            $sucDef = $stmtSucDef->fetch();
            $sucursalId = $sucDef ? (int)$sucDef['id'] : 1;
        }

        $ciudadSucursal = !empty($cRow['ciudad']) ? $cRow['ciudad'] : (!empty($cRow['sucursal_nombre']) ? $cRow['sucursal_nombre'] : 'Ibagué');

        // 5. Buscar si la sucursal ya tiene una ruta asignada para ese día
        $stmtRuta = $this->pdo->prepare("
            SELECT id, nombre, ciudad, fk_sucursal 
            FROM rutas 
            WHERE fk_sucursal = :sucursal_id 
              AND (
                LOWER(nombre) = LOWER(:dia) 
                OR LOWER(TRANSLATE(nombre, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU')) = LOWER(:dia_sin_acento)
                OR LOWER(nombre) LIKE :dia_like
              )
            ORDER BY id ASC LIMIT 1
        ");
        $stmtRuta->execute([
            'sucursal_id' => $sucursalId,
            'dia' => $diaSemanaNombre,
            'dia_sin_acento' => $diaSinAcento,
            'dia_like' => '%' . $diaSinAcento . '%'
        ]);
        $rutaExistente = $stmtRuta->fetch();

        $rutaCreada = false;
        $nuevaRutaId = null;
        $nuevaRutaNombre = null;

        if ($rutaExistente) {
            $nuevaRutaId = (int)$rutaExistente['id'];
            $nuevaRutaNombre = $rutaExistente['nombre'];
        } else {
            // No tiene ruta para ese día: Crear automáticamente en la sucursal
            $stmtInsRuta = $this->pdo->prepare("INSERT INTO rutas (nombre, ciudad, fk_sucursal) VALUES (:nombre, :ciudad, :fk_sucursal) RETURNING id");
            $stmtInsRuta->execute([
                'nombre' => $diaSemanaNombre,
                'ciudad' => $ciudadSucursal,
                'fk_sucursal' => $sucursalId
            ]);
            $nuevaRutaId = (int)$stmtInsRuta->fetchColumn();
            $nuevaRutaNombre = $diaSemanaNombre;
            $rutaCreada = true;
        }

        $rutaCambiada = ((int)($cRow['ruta_id'] ?? 0) !== $nuevaRutaId);

        // 6. Actualizar cliente: nueva ruta_id, fecha_base, frecuencia_id y estado
        $stmtUpdCliente = $this->pdo->prepare("
            UPDATE clientes 
            SET ruta_id = :ruta_id, 
                fecha_base = :fecha_base, 
                frecuencia_id = COALESCE(:frecuencia_id, frecuencia_id),
                estado = 'agendado'
            WHERE id = :id
        ");
        $stmtUpdCliente->execute([
            'ruta_id' => $nuevaRutaId,
            'fecha_base' => $fechaCambio,
            'frecuencia_id' => $nuevaFrecuenciaId ?: null,
            'id' => $clienteId
        ]);

        // 7. Eliminar eventos programados previos a partir de la fecha de cambio
        // (Solo se eliminan los que NO estén completados o aceptados, y solo de fecha_cambio en adelante)
        $stmtEvPrevios = $this->pdo->prepare("
            SELECT id, fecha_programada, estado, tipo 
            FROM eventos 
            WHERE cliente_id = :cliente_id 
              AND fecha_programada >= :fecha_cambio 
              AND estado NOT IN ('completada', 'aceptada')
        ");
        $stmtEvPrevios->execute([
            'cliente_id' => $clienteId,
            'fecha_cambio' => $fechaCambio
        ]);
        $eventosAEliminar = $stmtEvPrevios->fetchAll();
        $eventosEliminados = count($eventosAEliminar);

        if ($eventosEliminados > 0) {
            $stmtDel = $this->pdo->prepare("
                DELETE FROM eventos 
                WHERE cliente_id = :cliente_id 
                  AND fecha_programada >= :fecha_cambio 
                  AND estado NOT IN ('completada', 'aceptada')
            ");
            $stmtDel->execute([
                'cliente_id' => $clienteId,
                'fecha_cambio' => $fechaCambio
            ]);
        }

        // 8. Programar ÚNICAMENTE la siguiente recolección en la fecha de cambio
        // (Las siguientes fechas del ciclo quedan como tentativas automáticas proyectadas dinámicamente)
        $notifInit = [
            [
                'timestamp' => date('c'),
                'accion' => 'creacion_recurrencia',
                'origen' => 'user',
                'detalle' => "Siguiente recolección programada en ruta {$nuevaRutaNombre} ({$diaSemanaNombre}) tras actualizar recurrencia"
            ]
        ];

        $stmtIns = $this->pdo->prepare("
            INSERT INTO eventos (cliente_id, ruta_id, fecha_programada, estado, tipo, notificaciones, evento_origin, created_at, update_at)
            VALUES (:cliente_id, :ruta_id, :fecha_programada, 'programado', 'frecuente', :notif, NULL, CURRENT_DATE, CURRENT_DATE)
        ");
        $stmtIns->execute([
            'cliente_id' => $clienteId,
            'ruta_id' => $nuevaRutaId,
            'fecha_programada' => $fechaCambio,
            'notif' => json_encode($notifInit)
        ]);
        $eventosCreados = 1;

        // 9. Construir mensajes de alerta descriptivos para el usuario
        $lineasAlerta = [];

        if ($rutaCreada) {
            $lineasAlerta[] = "⚠️ La sucursal no tenía ruta para el día {$diaSemanaNombre}, por lo que se creó automáticamente la ruta '{$nuevaRutaNombre}'.";
            $lineasAlerta[] = "📍 El cliente fue asignado a la nueva ruta '{$nuevaRutaNombre}'.";
        } elseif ($rutaCambiada) {
            $lineasAlerta[] = "📍 La ruta del cliente se actualizó a '{$nuevaRutaNombre}' ({$diaSemanaNombre}).";
        } else {
            $lineasAlerta[] = "📍 Ruta asignada: '{$nuevaRutaNombre}' ({$diaSemanaNombre}).";
        }

        if ($eventosEliminados > 0) {
            $lineasAlerta[] = "🗑️ Se eliminaron {$eventosEliminados} recolección(es) previamente programada(s) desde el {$fechaCambio}.";
        }

        $lineasAlerta[] = "🗓️ Se programó la siguiente visita para el {$fechaCambio}. Las siguientes visitas de su ciclo (cada {$nuevosDias} días) se proyectarán automáticamente como tentativas.";

        $mensajeCompleto = implode("\n\n", $lineasAlerta);

        return [
            'cliente_id' => (int)$clienteId,
            'cliente_nombre' => $cRow['nombre'] ?? 'Cliente',
            'fecha_cambio' => $fechaCambio,
            'dia_semana' => $diaSemanaNombre,
            'nuevos_dias_frecuencia' => (int)$nuevosDias,
            'ruta_id' => $nuevaRutaId,
            'ruta_nombre' => $nuevaRutaNombre,
            'ruta_creada' => $rutaCreada,
            'ruta_cambiada' => $rutaCambiada,
            'eventos_eliminados' => $eventosEliminados,
            'eventos_creados' => $eventosCreados,
            'mensaje' => $mensajeCompleto
        ];
    }
}

