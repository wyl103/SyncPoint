<?php
// app/models/core/eventos.php
require_once __DIR__ . '/../../services/Database.php';

class Evento {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $clienteId = null, $rutaId = null, $fecha = null, $estado = null, $tipo = null, $page = 1, $limit = 10) {
        $whereSql = " FROM eventos e
                      LEFT JOIN clientes c ON e.cliente_id = c.id
                      LEFT JOIN rutas r ON e.ruta_id = r.id
                      LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                      WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND (c.nombre ILIKE :busqueda OR r.nombre ILIKE :busqueda OR e.tipo ILIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        if (!empty($clienteId)) {
            $whereSql .= " AND e.cliente_id = :cliente_id";
            $params['cliente_id'] = $clienteId;
        }

        if (!empty($rutaId) && $rutaId !== 'todas') {
            $whereSql .= " AND e.ruta_id = :ruta_id";
            $params['ruta_id'] = $rutaId;
        }

        if (!empty($fecha)) {
            $whereSql .= " AND e.fecha_programada = :fecha_programada";
            $params['fecha_programada'] = $fecha;
        }

        if (!empty($estado) && $estado !== 'todos') {
            $whereSql .= " AND e.estado::text = :estado";
            $params['estado'] = $estado;
        }

        if (!empty($tipo)) {
            $whereSql .= " AND e.tipo ILIKE :tipo";
            $params['tipo'] = '%' . $tipo . '%';
        }

        // 1. Contador total
        $countSql = "SELECT COUNT(e.id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        // 2. Parámetros de paginación
        $page = max(1, (int)$page);
        $limit = in_array((int)$limit, [10, 50, 100]) ? (int)$limit : 10;
        $offset = ($page - 1) * $limit;

        // 3. Consulta de datos
        $dataSql = "SELECT 
                        e.id,
                        e.cliente_id,
                        e.ruta_id,
                        e.fecha_programada,
                        e.estado,
                        e.tipo,
                        e.notificaciones,
                        e.evento_origin,
                        e.created_at,
                        e.update_at,
                        c.nombre AS cliente_nombre,
                        c.telefono_whatsapp AS cliente_telefono,
                        r.nombre AS ruta_nombre,
                        r.ciudad AS ruta_ciudad,
                        s.id AS sucursal_id,
                        s.nombre AS sucursal_nombre"
                 . $whereSql . " ORDER BY e.fecha_programada ASC, e.id DESC LIMIT :limit OFFSET :offset";

        $stmtData = $this->pdo->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmtData->bindValue(':' . $key, $val);
        }
        $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtData->execute();
        $rows = $stmtData->fetchAll();

        // Parsear notificaciones si vienen como string JSON
        foreach ($rows as &$row) {
            if (isset($row['notificaciones']) && is_string($row['notificaciones'])) {
                $row['notificaciones'] = json_decode($row['notificaciones'], true);
            }
        }

        return [
            'data' => $rows,
            'total' => $totalRows,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $limit > 0 ? (int)ceil($totalRows / $limit) : 1
        ];
    }

    public function getById($id) {
        $sql = "SELECT 
                    e.id,
                    e.cliente_id,
                    e.ruta_id,
                    e.fecha_programada,
                    e.estado,
                    e.tipo,
                    e.notificaciones,
                    e.evento_origin,
                    e.created_at,
                    e.update_at,
                    c.nombre AS cliente_nombre,
                    c.telefono_whatsapp AS cliente_telefono,
                    r.nombre AS ruta_nombre,
                    r.ciudad AS ruta_ciudad,
                    s.id AS sucursal_id,
                    s.nombre AS sucursal_nombre
                FROM eventos e
                LEFT JOIN clientes c ON e.cliente_id = c.id
                LEFT JOIN rutas r ON e.ruta_id = r.id
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                WHERE e.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row && isset($row['notificaciones']) && is_string($row['notificaciones'])) {
            $row['notificaciones'] = json_decode($row['notificaciones'], true);
        }

        return $row;
    }

    public function create($clienteId, $rutaId, $fechaProgramada, $estado = 'pendiente', $tipo = null, $notificaciones = null, $eventoOrigin = null) {
        $notifJson = is_array($notificaciones) || is_object($notificaciones) ? json_encode($notificaciones) : $notificaciones;

        $sql = "INSERT INTO eventos (cliente_id, ruta_id, fecha_programada, estado, tipo, notificaciones, evento_origin, created_at, update_at) 
                VALUES (:cliente_id, :ruta_id, :fecha_programada, :estado::recolecciones_estado, :tipo, :notificaciones, :evento_origin, CURRENT_DATE, CURRENT_DATE) 
                RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cliente_id' => $clienteId ?: null,
            'ruta_id' => $rutaId ?: null,
            'fecha_programada' => $fechaProgramada,
            'estado' => $estado ?: 'pendiente',
            'tipo' => $tipo ?: null,
            'notificaciones' => $notifJson ?: null,
            'evento_origin' => $eventoOrigin ?: null
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $clienteId = null, $rutaId = null, $fechaProgramada = null, $estado = null, $tipo = null, $notificaciones = null, $eventoOrigin = null) {
        $fields = [];
        $params = ['id' => $id];

        if ($clienteId !== null) {
            $fields[] = "cliente_id = :cliente_id";
            $params['cliente_id'] = $clienteId ?: null;
        }

        if ($rutaId !== null) {
            $fields[] = "ruta_id = :ruta_id";
            $params['ruta_id'] = $rutaId ?: null;
        }

        if ($fechaProgramada !== null) {
            $fields[] = "fecha_programada = :fecha_programada";
            $params['fecha_programada'] = $fechaProgramada;
        }

        if ($estado !== null) {
            $fields[] = "estado = :estado::recolecciones_estado";
            $params['estado'] = $estado;
        }

        if ($tipo !== null) {
            $fields[] = "tipo = :tipo";
            $params['tipo'] = $tipo;
        }

        if ($notificaciones !== null) {
            $fields[] = "notificaciones = :notificaciones";
            $params['notificaciones'] = is_array($notificaciones) || is_object($notificaciones) ? json_encode($notificaciones) : $notificaciones;
        }

        if ($eventoOrigin !== null) {
            $fields[] = "evento_origin = :evento_origin";
            $params['evento_origin'] = $eventoOrigin ?: null;
        }

        $fields[] = "update_at = CURRENT_DATE";

        if (empty($fields)) {
            return true;
        }

        $sql = "UPDATE eventos SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM eventos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function obtenerEventosYTentativosDelDia($fecha, $estado = 'todos', $sucursal = 'todas') {
        $sqlEventos = "SELECT 
                        e.id,
                        e.cliente_id,
                        e.ruta_id,
                        e.fecha_programada,
                        COALESCE(e.estado::text, 'agendado') AS estado_recoleccion,
                        e.tipo,
                        e.notificaciones,
                        c.nombre AS cliente_nombre,
                        c.telefono_whatsapp,
                        c.fecha_base,
                        r.nombre AS ruta_nombre,
                        r.ciudad AS ruta_ciudad,
                        s.id AS sucursal_id,
                        s.nombre AS sucursal_nombre,
                        s.destacada,
                        f.nombre AS frecuencia_nombre,
                        f.dias AS frecuencia_dias,
                        false AS es_tentativa
                    FROM eventos e
                    JOIN clientes c ON e.cliente_id = c.id
                    LEFT JOIN rutas r ON e.ruta_id = r.id
                    LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                    LEFT JOIN frecuencias f ON c.frecuencia_id = f.id
                    WHERE e.fecha_programada = :fecha";

        $stmtE = $this->pdo->prepare($sqlEventos);
        $stmtE->execute(['fecha' => $fecha]);
        $eventosExplicitos = $stmtE->fetchAll();

        $clientesConEvento = [];
        foreach ($eventosExplicitos as $ev) {
            if (!empty($ev['cliente_id'])) {
                $clientesConEvento[$ev['cliente_id']] = true;
            }
        }

        $sqlClientes = "SELECT 
                            c.id AS cliente_id,
                            c.nombre AS cliente_nombre,
                            c.telefono_whatsapp,
                            c.fecha_base,
                            c.ruta_id,
                            r.nombre AS ruta_nombre,
                            r.ciudad AS ruta_ciudad,
                            s.id AS sucursal_id,
                            s.nombre AS sucursal_nombre,
                            s.destacada,
                            f.id AS frecuencia_id,
                            f.nombre AS frecuencia_nombre,
                            f.dias AS frecuencia_dias
                        FROM clientes c
                        LEFT JOIN rutas r ON c.ruta_id = r.id
                        LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                        LEFT JOIN frecuencias f ON c.frecuencia_id = f.id";
        
        $stmtC = $this->pdo->query($sqlClientes);
        $todosClientes = $stmtC->fetchAll();

        $targetTs = strtotime($fecha);
        $eventosTentativos = [];

        foreach ($todosClientes as $c) {
            $clienteId = $c['cliente_id'];
            if (isset($clientesConEvento[$clienteId])) {
                continue;
            }

            $diasFrecuencia = (int)($c['frecuencia_dias'] ?? 0);
            if ($diasFrecuencia <= 0) {
                continue;
            }

            $esCiclo = false;

            if (!empty($c['fecha_base'])) {
                $baseTs = strtotime($c['fecha_base']);
                $diffDays = (int)round(($targetTs - $baseTs) / 86400);
                if ($diffDays >= 0 && ($diffDays % $diasFrecuencia === 0)) {
                    $esCiclo = true;
                }
            } else {
                $daysEpoch = (int)floor($targetTs / 86400);
                if ($daysEpoch % $diasFrecuencia === 0) {
                    $esCiclo = true;
                }
            }

            if ($esCiclo) {
                $eventosTentativos[] = [
                    'id' => null,
                    'cliente_id' => $c['cliente_id'],
                    'ruta_id' => $c['ruta_id'],
                    'fecha_programada' => $fecha,
                    'estado_recoleccion' => 'tentativa',
                    'tipo' => 'tentativa',
                    'notificaciones' => null,
                    'cliente_nombre' => $c['cliente_nombre'],
                    'telefono_whatsapp' => $c['telefono_whatsapp'],
                    'ruta_nombre' => $c['ruta_nombre'] ?? 'Sin Ruta',
                    'ruta_ciudad' => $c['ruta_ciudad'] ?? '',
                    'sucursal_id' => $c['sucursal_id'] ?? 0,
                    'sucursal_nombre' => $c['sucursal_nombre'] ?? 'Otras Sucursales',
                    'destacada' => $c['destacada'] ?? 0,
                    'frecuencia_nombre' => $c['frecuencia_nombre'] ?? 'N/A',
                    'frecuencia_dias' => $diasFrecuencia,
                    'es_tentativa' => true
                ];
            }
        }

        $todosCombinados = array_merge($eventosExplicitos, $eventosTentativos);

        $resultadoFiltrado = array_filter($todosCombinados, function($item) use ($estado, $sucursal) {
            if ($estado !== null && $estado !== '' && $estado !== 'todos') {
                if (strtolower($item['estado_recoleccion']) !== strtolower($estado)) {
                    return false;
                }
            }

            if ($sucursal !== null && $sucursal !== '' && $sucursal !== 'todas') {
                if ($sucursal === 'otras') {
                    if ($item['destacada'] == 1) {
                        return false;
                    }
                } else {
                    if ($item['sucursal_id'] != $sucursal) {
                        return false;
                    }
                }
            }

            return true;
        });

        return array_values($resultadoFiltrado);
    }

    public function obtenerConteoEventosYTentativosPorRango($inicio, $fin) {
        $tsInicio = strtotime($inicio);
        $tsFin = strtotime($fin);

        $sqlE = "SELECT fecha_programada, COUNT(id) as total 
                 FROM eventos 
                 WHERE fecha_programada BETWEEN :inicio AND :fin 
                 GROUP BY fecha_programada";
        $stmtE = $this->pdo->prepare($sqlE);
        $stmtE->execute(['inicio' => $inicio, 'fin' => $fin]);
        $eventosExplicitos = $stmtE->fetchAll(PDO::FETCH_KEY_PAIR);

        $sqlEClientes = "SELECT fecha_programada, cliente_id 
                         FROM eventos 
                         WHERE fecha_programada BETWEEN :inicio AND :fin AND cliente_id IS NOT NULL";
        $stmtEC = $this->pdo->prepare($sqlEClientes);
        $stmtEC->execute(['inicio' => $inicio, 'fin' => $fin]);
        $clienteEventosMap = [];
        foreach ($stmtEC->fetchAll() as $row) {
            $clienteEventosMap[$row['fecha_programada']][$row['cliente_id']] = true;
        }

        $sqlC = "SELECT c.id, c.fecha_base, f.dias AS frecuencia_dias 
                 FROM clientes c 
                 LEFT JOIN frecuencias f ON c.frecuencia_id = f.id";
        $stmtC = $this->pdo->query($sqlC);
        $clientes = $stmtC->fetchAll();

        $resultados = [];

        for ($ts = $tsInicio; $ts <= $tsFin; $ts += 86400) {
            $fechaIso = date('Y-m-d', $ts);
            $conteoExpl = $eventosExplicitos[$fechaIso] ?? 0;
            $conteoTent = 0;

            foreach ($clientes as $c) {
                $clienteId = $c['id'];
                if (isset($clienteEventosMap[$fechaIso][$clienteId])) {
                    continue;
                }

                $diasFrecuencia = (int)($c['frecuencia_dias'] ?? 0);
                if ($diasFrecuencia <= 0) continue;

                if (!empty($c['fecha_base'])) {
                    $baseTs = strtotime($c['fecha_base']);
                    $diffDays = (int)round(($ts - $baseTs) / 86400);
                    if ($diffDays >= 0 && ($diffDays % $diasFrecuencia === 0)) {
                        $conteoTent++;
                    }
                } else {
                    $daysEpoch = (int)floor($ts / 86400);
                    if ($daysEpoch % $diasFrecuencia === 0) {
                        $conteoTent++;
                    }
                }
            }

            $resultados[$fechaIso] = $conteoExpl + $conteoTent;
        }

        return $resultados;
    }
}
