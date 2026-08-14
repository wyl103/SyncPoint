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
}
