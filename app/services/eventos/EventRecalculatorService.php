<?php
// app/services/eventos/EventRecalculatorService.php
require_once __DIR__ . '/../../services/Database.php';

class EventRecalculatorService {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Recalcula las fechas programadas de los eventos futuros de un cliente
     * cuando cambia su frecuencia de recolección a partir de una fecha determinada.
     *
     * @param int $clienteId ID del cliente
     * @param string $fechaCambio Fecha YYYY-MM-DD a partir de la cual aplica el cambio
     * @param int|null $nuevaFrecuenciaId ID de la nueva frecuencia (opcional si se pasan los días)
     * @param int|null $nuevosDias Días de la nueva frecuencia (opcional si se pasa el id)
     * @param string $origen 'user' o 'sistem'
     * @return array Resumen del proceso de recálculo
     */
    public function recalcularEventosPorCambioFrecuencia($clienteId, $fechaCambio, $nuevaFrecuenciaId = null, $nuevosDias = null, $origen = 'user') {
        if (empty($clienteId)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        if (empty($fechaCambio)) {
            throw new Exception("La fecha de inicio del cambio (fecha_cambio) es obligatoria.");
        }

        // 1. Obtener información de la nueva frecuencia o de la frecuencia actual del cliente
        if (empty($nuevosDias) && !empty($nuevaFrecuenciaId)) {
            $stmtFrec = $this->pdo->prepare("SELECT dias FROM frecuencias WHERE id = :id");
            $stmtFrec->execute(['id' => $nuevaFrecuenciaId]);
            $frecRow = $stmtFrec->fetch();
            if ($frecRow) {
                $nuevosDias = (int)$frecRow['dias'];
            }
        }

        if (empty($nuevosDias)) {
            $stmtCF = $this->pdo->prepare("SELECT c.frecuencia_id, f.dias FROM clientes c LEFT JOIN frecuencias f ON c.frecuencia_id = f.id WHERE c.id = :id");
            $stmtCF->execute(['id' => $clienteId]);
            $cRow = $stmtCF->fetch();
            if ($cRow) {
                if (empty($nuevaFrecuenciaId)) {
                    $nuevaFrecuenciaId = $cRow['frecuencia_id'];
                }
                if (!empty($cRow['dias'])) {
                    $nuevosDias = (int)$cRow['dias'];
                }
            }
        }

        if (empty($nuevosDias) || $nuevosDias <= 0) {
            throw new Exception("No se pudo determinar el número de días de la frecuencia para el recálculo.");
        }

        // 2. Actualizar la fecha_base y la frecuencia_id en la tabla clientes
        $stmtUpdCliente = $this->pdo->prepare("UPDATE clientes SET fecha_base = :fecha_base, frecuencia_id = COALESCE(:frecuencia_id, frecuencia_id) WHERE id = :id");
        $stmtUpdCliente->execute([
            'fecha_base' => $fechaCambio,
            'frecuencia_id' => $nuevaFrecuenciaId ?: null,
            'id' => $clienteId
        ]);

        // 3. Buscar eventos existentes del cliente desde la fecha de cambio en adelante
        $stmtEv = $this->pdo->prepare("SELECT * FROM eventos WHERE cliente_id = :cliente_id AND fecha_programada >= :fecha_cambio ORDER BY fecha_programada ASC, id ASC");
        $stmtEv->execute([
            'cliente_id' => $clienteId,
            'fecha_cambio' => $fechaCambio
        ]);
        $eventosFuturos = $stmtEv->fetchAll();

        $eventosModificados = 0;
        $eventosCreados = 0;
        $tsInicio = strtotime($fechaCambio);

        if (!empty($eventosFuturos)) {
            // Recalcular las fechas de los eventos existentes
            foreach ($eventosFuturos as $index => $evento) {
                $nuevaFechaTs = $tsInicio + ($index * $nuevosDias * 86400);
                $nuevaFechaStr = date('Y-m-d', $nuevaFechaTs);

                $notifArray = [];
                if (!empty($evento['notificaciones'])) {
                    $decoded = is_string($evento['notificaciones']) ? json_decode($evento['notificaciones'], true) : $evento['notificaciones'];
                    if (is_array($decoded)) {
                        $notifArray = $decoded;
                    }
                }

                $notifArray[] = [
                    'timestamp' => date('c'),
                    'accion' => 'recalculo_frecuencia',
                    'origen' => is_string($origen) ? strtolower($origen) : 'user',
                    'detalle' => "Fecha reprogramada de {$evento['fecha_programada']} a {$nuevaFechaStr} por cambio de frecuencia a {$nuevosDias} días",
                    'fecha_anterior' => $evento['fecha_programada'],
                    'fecha_nueva' => $nuevaFechaStr
                ];

                $stmtUpdEv = $this->pdo->prepare("UPDATE eventos SET fecha_programada = :fecha, notificaciones = :notif, update_at = CURRENT_DATE WHERE id = :id");
                $stmtUpdEv->execute([
                    'fecha' => $nuevaFechaStr,
                    'notif' => json_encode($notifArray),
                    'id' => $evento['id']
                ]);

                $eventosModificados++;
            }
        } else {
            // Si el cliente no tenía eventos guardados futuros, generar los eventos automáticos para los próximos 6 ciclos
            $stmtCliente = $this->pdo->prepare("SELECT ruta_id FROM clientes WHERE id = :id");
            $stmtCliente->execute(['id' => $clienteId]);
            $clienteInfo = $stmtCliente->fetch();
            $rutaId = $clienteInfo ? $clienteInfo['ruta_id'] : null;

            $originVal = (!empty($origen) && is_numeric($origen)) ? (int)$origen : null;

            for ($i = 0; $i < 6; $i++) {
                $fechaTs = $tsInicio + ($i * $nuevosDias * 86400);
                $fechaStr = date('Y-m-d', $fechaTs);

                $notifInit = [
                    [
                        'timestamp' => date('c'),
                        'accion' => 'creacion_automatica_frecuencia',
                        'origen' => is_string($origen) ? strtolower($origen) : 'sistem',
                        'detalle' => "Evento generado automáticamente por cambio de frecuencia a {$nuevosDias} días"
                    ]
                ];

                $stmtIns = $this->pdo->prepare("INSERT INTO eventos (cliente_id, ruta_id, fecha_programada, estado, tipo, notificaciones, evento_origin, created_at, update_at)
                                               VALUES (:cliente_id, :ruta_id, :fecha_programada, 'programado', 'frecuente', :notif, :origin, CURRENT_DATE, CURRENT_DATE)");
                $stmtIns->execute([
                    'cliente_id' => $clienteId,
                    'ruta_id' => $rutaId,
                    'fecha_programada' => $fechaStr,
                    'notif' => json_encode($notifInit),
                    'origin' => $originVal
                ]);
                $eventosCreados++;
            }
        }

        return [
            'cliente_id' => (int)$clienteId,
            'fecha_cambio' => $fechaCambio,
            'nuevos_dias_frecuencia' => (int)$nuevosDias,
            'eventos_modificados' => $eventosModificados,
            'eventos_creados' => $eventosCreados
        ];
    }
}
