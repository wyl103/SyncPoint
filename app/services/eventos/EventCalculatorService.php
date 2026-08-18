<?php
// app/services/eventos/EventCalculatorService.php
require_once __DIR__ . '/../../services/Database.php';

class EventCalculatorService {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Obtiene la información del cliente y los días de su frecuencia asignada.
     */
    private function obtenerInfoClienteConFrecuencia($clienteId) {
        $sql = "SELECT c.id, c.nombre, c.fecha_base, c.ruta_id, c.frecuencia_id, f.dias, f.nombre AS frecuencia_nombre 
                FROM clientes c 
                LEFT JOIN frecuencias f ON c.frecuencia_id = f.id 
                WHERE c.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $clienteId]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            throw new Exception("Cliente con ID {$clienteId} no encontrado.");
        }

        $dias = (int)($cliente['dias'] ?? 0);
        if ($dias <= 0) {
            throw new Exception("El cliente '{$cliente['nombre']}' no tiene una frecuencia de recolección válida configurada.");
        }

        return [
            'cliente' => $cliente,
            'dias' => $dias
        ];
    }

    /**
     * Helper estático para ajustar una fecha al día de la semana correspondiente a la Ruta.
     */
    public static function ajustarFechaADiaRuta($fechaStr, $nombreRuta) {
        if (empty($nombreRuta) || empty($fechaStr)) return $fechaStr;

        $nombreLower = mb_strtolower($nombreRuta);
        $diasMapa = [
            'domingo' => 0, 'sunday' => 0,
            'lunes' => 1, 'monday' => 1,
            'martes' => 2, 'tuesday' => 2,
            'miercoles' => 3, 'miércoles' => 3, 'wednesday' => 3,
            'jueves' => 4, 'thursday' => 4,
            'viernes' => 5, 'friday' => 5,
            'sabado' => 6, 'sábado' => 6, 'saturday' => 6
        ];

        $targetDay = null;
        foreach ($diasMapa as $kw => $wNum) {
            if (strpos($nombreLower, $kw) !== false) {
                $targetDay = $wNum;
                break;
            }
        }

        if ($targetDay === null) return $fechaStr;

        $ts = strtotime($fechaStr);
        if (!$ts) return $fechaStr;

        $currentDay = (int)date('w', $ts);
        if ($currentDay === $targetDay) return $fechaStr;

        $minDiff = 999;
        $bestTs = $ts;

        for ($offset = -3; $offset <= 3; $offset++) {
            $testTs = $ts + ($offset * 86400);
            if ((int)date('w', $testTs) === $targetDay) {
                if (abs($offset) < $minDiff) {
                    $minDiff = abs($offset);
                    $bestTs = $testTs;
                }
            }
        }

        return date('Y-m-d', $bestTs);
    }

    /**
     * 1. Calcular fechas proyectadas enviando la cantidad deseada de ciclos/tiempos.
     */
    public function calcularFechasPorCantidad($clienteId, $fechaInicio = null, $cantidad = 6) {
        if (empty($clienteId)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }

        $info = $this->obtenerInfoClienteConFrecuencia($clienteId);
        $cliente = $info['cliente'];
        $dias = $info['dias'];

        $cantidad = max(1, (int)$cantidad);

        // Determinar fecha de inicio
        $inicioStr = !empty($fechaInicio) ? $fechaInicio : (!empty($cliente['fecha_base']) ? $cliente['fecha_base'] : date('Y-m-d'));
        $tsInicio = strtotime($inicioStr);

        if (!$tsInicio) {
            throw new Exception("La fecha de inicio '{$fechaInicio}' no tiene un formato válido.");
        }

        $fechasCalculadas = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $ts = $tsInicio + ($i * $dias * 86400);
            $fechasCalculadas[] = date('Y-m-d', $ts);
        }

        return [
            'cliente_id' => (int)$clienteId,
            'cliente_nombre' => $cliente['nombre'],
            'frecuencia_nombre' => $cliente['frecuencia_nombre'] ?? "{$dias} días",
            'frecuencia_dias' => $dias,
            'fecha_inicio' => date('Y-m-d', $tsInicio),
            'cantidad' => $cantidad,
            'fechas' => $fechasCalculadas
        ];
    }

    /**
     * 2. Calcular fechas proyectadas enviando un rango (desde cuando y hasta cuando).
     */
    public function calcularFechasPorRango($clienteId, $fechaDesde, $fechaHasta) {
        if (empty($clienteId)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        if (empty($fechaDesde) || empty($fechaHasta)) {
            throw new Exception("Las fechas 'desde' y 'hasta' son obligatorias.");
        }

        $tsDesde = strtotime($fechaDesde);
        $tsHasta = strtotime($fechaHasta);

        if (!$tsDesde || !$tsHasta) {
            throw new Exception("Formatos de fecha inválidos para el rango.");
        }
        if ($tsDesde > $tsHasta) {
            throw new Exception("La fecha 'desde' ({$fechaDesde}) debe ser menor o igual a la fecha 'hasta' ({$fechaHasta}).");
        }

        $info = $this->obtenerInfoClienteConFrecuencia($clienteId);
        $cliente = $info['cliente'];
        $dias = $info['dias'];

        // Determinar punto de partida para la secuencia del ciclo
        $baseStr = !empty($cliente['fecha_base']) ? $cliente['fecha_base'] : $fechaDesde;
        $tsBase = strtotime($baseStr);

        $fechasCalculadas = [];

        if ($tsBase <= $tsHasta) {
            // Avanzar o retroceder para alinear el primer ciclo dentro o antes del rango
            $tsActual = $tsBase;
            if ($tsActual < $tsDesde) {
                $diffSec = $tsDesde - $tsActual;
                $ciclosSaltar = (int)ceil($diffSec / ($dias * 86400));
                $tsActual = $tsActual + ($ciclosSaltar * $dias * 86400);
            }

            // Recopilar todas las fechas en el rango
            while ($tsActual <= $tsHasta) {
                if ($tsActual >= $tsDesde) {
                    $fechasCalculadas[] = date('Y-m-d', $tsActual);
                }
                $tsActual += ($dias * 86400);
            }
        }

        return [
            'cliente_id' => (int)$clienteId,
            'cliente_nombre' => $cliente['nombre'],
            'frecuencia_nombre' => $cliente['frecuencia_nombre'] ?? "{$dias} días",
            'frecuencia_dias' => $dias,
            'fecha_desde' => date('Y-m-d', $tsDesde),
            'fecha_hasta' => date('Y-m-d', $tsHasta),
            'total_fechas' => count($fechasCalculadas),
            'fechas' => $fechasCalculadas
        ];
    }

    /**
     * 3. Agendar (crear en lote / bulk) un listado de fechas para un cliente.
     */
    public function agendarFechasLote($clienteId, array $fechas, $rutaId = null, $estado = 'programado', $tipo = 'frecuente', $origen = 'user') {
        if (empty($clienteId)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        if (empty($fechas) || !is_array($fechas)) {
            throw new Exception("Debe proporcionar un listado de fechas para agendar.");
        }

        // Obtener cliente y su ruta por defecto si no viene especificada
        $stmtC = $this->pdo->prepare("SELECT ruta_id, nombre FROM clientes WHERE id = :id");
        $stmtC->execute(['id' => $clienteId]);
        $cliente = $stmtC->fetch();

        if (!$cliente) {
            throw new Exception("Cliente con ID {$clienteId} no encontrado.");
        }

        $finalRutaId = !empty($rutaId) ? $rutaId : $cliente['ruta_id'];
        $originVal = (!empty($origen) && is_numeric($origen)) ? (int)$origen : null;

        // Consultar eventos ya agendados para este cliente en las fechas enviadas
        $placeholders = implode(',', array_fill(0, count($fechas), '?'));
        $sqlExist = "SELECT fecha_programada::text FROM eventos WHERE cliente_id = ? AND fecha_programada IN ({$placeholders})";
        $stmtExist = $this->pdo->prepare($sqlExist);
        $stmtExist->execute(array_merge([$clienteId], array_values($fechas)));
        $existentesMap = array_flip($stmtExist->fetchAll(PDO::FETCH_COLUMN));

        $eventosCreados = 0;
        $eventosExistentes = 0;
        $creadosList = [];

        $stmtIns = $this->pdo->prepare("INSERT INTO eventos (cliente_id, ruta_id, fecha_programada, estado, tipo, notificaciones, evento_origin, created_at, update_at)
                                       VALUES (:cliente_id, :ruta_id, :fecha_programada, :estado, :tipo, :notif, :origin, CURRENT_DATE, CURRENT_DATE)
                                       RETURNING id");

        foreach ($fechas as $fechaStr) {
            $fechaClean = date('Y-m-d', strtotime($fechaStr));
            if (isset($existentesMap[$fechaClean])) {
                $eventosExistentes++;
                continue;
            }

            $notifInit = [
                [
                    'timestamp' => date('c'),
                    'accion' => 'agendamiento_lote',
                    'origen' => is_string($origen) ? strtolower($origen) : 'user',
                    'detalle' => "Evento agendado en lote desde el servicio de cálculo"
                ]
            ];

            $stmtIns->execute([
                'cliente_id' => $clienteId,
                'ruta_id' => $finalRutaId,
                'fecha_programada' => $fechaClean,
                'estado' => strtolower($estado),
                'tipo' => strtolower($tipo),
                'notif' => json_encode($notifInit),
                'origin' => $originVal
            ]);

            $newId = $stmtIns->fetchColumn();
            $creadosList[] = [
                'id' => $newId,
                'fecha_programada' => $fechaClean
            ];

            $eventosCreados++;
        }

        return [
            'cliente_id' => (int)$clienteId,
            'cliente_nombre' => $cliente['nombre'],
            'eventos_creados' => $eventosCreados,
            'eventos_existentes' => $eventosExistentes,
            'eventos' => $creadosList
        ];
    }

    /**
     * 4. Programar Eventos Globales:
     * Consulta la fecha agendada más lejana en la tabla `eventos`, calcula los días que faltan
     * para completar el horizonte configurado (por defecto 30 días) y agenda masivamente para todos los clientes
     * ajustando la fecha según el día de la semana de su ruta.
     */
    public function programarEventosGlobales($diasHorizonte = 30) {
        $diasHorizonte = max(1, (int)$diasHorizonte);

        // 1. Obtener la fecha más lejana agendada
        $sqlMax = "SELECT MAX(fecha_programada)::text FROM eventos WHERE fecha_programada >= CURRENT_DATE";
        $stmtMax = $this->pdo->query($sqlMax);
        $maxFecha = $stmtMax->fetchColumn();

        $hoy = date('Y-m-d');
        $fechaDesde = (!empty($maxFecha) && $maxFecha >= $hoy) ? $maxFecha : $hoy;
        $fechaHasta = date('Y-m-d', strtotime("{$fechaDesde} + {$diasHorizonte} days"));

        // 2. Consultar únicamente clientes activos que tengan una fecha_base configurada y una frecuencia válida
        $sqlClientes = "SELECT c.id, c.nombre, c.fecha_base, c.ruta_id, c.frecuencia_id, f.dias, r.nombre AS ruta_nombre 
                        FROM clientes c
                        INNER JOIN frecuencias f ON c.frecuencia_id = f.id
                        LEFT JOIN rutas r ON c.ruta_id = r.id
                        WHERE f.dias > 0 
                          AND c.fecha_base IS NOT NULL 
                          AND TRIM(c.fecha_base::text) != ''";
        $stmtC = $this->pdo->query($sqlClientes);
        $clientes = $stmtC->fetchAll();

        $totalEventosCreados = 0;
        $totalEventosExistentes = 0;
        $clientesProcesados = 0;
        $resumenClientes = [];

        foreach ($clientes as $cli) {
            if (empty($cli['fecha_base'])) {
                continue; // Ignorar clientes sin fecha_base
            }

            $clienteId = (int)$cli['id'];
            $diasFrecuencia = (int)$cli['dias'];
            $rutaNombre = $cli['ruta_nombre'] ?? '';

            // Punto base para cálculo de frecuencia obligatorio desde fecha_base
            $baseStr = $cli['fecha_base'];
            $tsBase = strtotime($baseStr);
            $tsDesde = strtotime($fechaDesde);
            $tsHasta = strtotime($fechaHasta);

            $fechasProyectadas = [];
            if ($tsBase <= $tsHasta) {
                $tsActual = $tsBase;
                if ($tsActual < $tsDesde) {
                    $diffSec = $tsDesde - $tsActual;
                    $ciclosSaltar = (int)ceil($diffSec / ($diasFrecuencia * 86400));
                    $tsActual = $tsActual + ($ciclosSaltar * $diasFrecuencia * 86400);
                }

                while ($tsActual <= $tsHasta) {
                    if ($tsActual >= $tsDesde) {
                        $fechaOriginal = date('Y-m-d', $tsActual);
                        // Ajustar la fecha según el día de la semana que indique la ruta
                        $fechaAjustada = self::ajustarFechaADiaRuta($fechaOriginal, $rutaNombre);
                        $fechasProyectadas[] = $fechaAjustada;
                    }
                    $tsActual += ($diasFrecuencia * 86400);
                }
            }

            if (!empty($fechasProyectadas)) {
                $resAgendado = $this->agendarFechasLote(
                    $clienteId, 
                    array_values(array_unique($fechasProyectadas)), 
                    $cli['ruta_id'], 
                    'programado', 
                    'frecuente', 
                    'sistem'
                );

                $totalEventosCreados += $resAgendado['eventos_creados'];
                $totalEventosExistentes += $resAgendado['eventos_existentes'];
                $clientesProcesados++;

                $resumenClientes[] = [
                    'cliente_id' => $clienteId,
                    'nombre' => $cli['nombre'],
                    'ruta' => $rutaNombre,
                    'fechas_agendadas' => $resAgendado['eventos_creados']
                ];
            }
        }

        return [
            'fecha_mas_lejana_actual' => $fechaDesde,
            'fecha_proyectada_hasta' => $fechaHasta,
            'dias_horizonte' => $diasHorizonte,
            'clientes_procesados' => $clientesProcesados,
            'total_eventos_creados' => $totalEventosCreados,
            'total_eventos_existentes' => $totalEventosExistentes,
            'detalle' => $resumenClientes
        ];
    }
}
