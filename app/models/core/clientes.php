<?php
// app/models/core/clientes.php
require_once __DIR__ . '/../../services/Database.php';

class Cliente {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $rutaId = null, $sucursalId = null, $estado = null, $page = 1, $limit = 10) {
        $whereSql = " FROM clientes c
                      LEFT JOIN rutas r ON c.ruta_id = r.id
                      LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                      LEFT JOIN frecuencias f ON c.frecuencia_id = f.id
                      WHERE 1=1";
        
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND (c.nombre ILIKE :busqueda OR c.telefono_whatsapp ILIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        if (!empty($rutaId) && $rutaId !== 'todas') {
            $whereSql .= " AND c.ruta_id = :ruta_id";
            $params['ruta_id'] = $rutaId;
        }

        if (!empty($sucursalId) && $sucursalId !== 'todas') {
            $whereSql .= " AND r.fk_sucursal = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        if (!empty($estado) && $estado !== 'todos') {
            $whereSql .= " AND c.estado::text = :estado";
            $params['estado'] = $estado;
        }

        $countSql = "SELECT COUNT(c.id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        $page = max(1, (int)$page);
        $limit = in_array((int)$limit, [10, 50, 100]) ? (int)$limit : 10;
        $offset = ($page - 1) * $limit;

        $dataSql = "SELECT 
                        c.id, 
                        c.nombre, 
                        c.telefono_whatsapp, 
                        c.frecuencia_id, 
                        c.ruta_id, 
                        c.estado,
                        c.fecha_base,
                        r.nombre AS ruta_nombre,
                        r.ciudad AS ruta_ciudad,
                        s.id AS sucursal_id,
                        s.nombre AS sucursal_nombre,
                        f.nombre AS frecuencia_nombre" 
                    . $whereSql . 
                    " ORDER BY c.nombre ASC LIMIT :limit OFFSET :offset";

        $stmtData = $this->pdo->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmtData->bindValue(':' . $key, $val);
        }
        $stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmtData->execute();
        $rows = $stmtData->fetchAll();

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
                    c.id, 
                    c.nombre, 
                    c.telefono_whatsapp, 
                    c.frecuencia_id, 
                    c.ruta_id, 
                    c.estado,
                    c.fecha_base,
                    r.nombre AS ruta_nombre,
                    r.ciudad AS ruta_ciudad,
                    s.id AS sucursal_id,
                    s.nombre AS sucursal_nombre,
                    f.nombre AS frecuencia_nombre
                FROM clientes c
                LEFT JOIN rutas r ON c.ruta_id = r.id
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                LEFT JOIN frecuencias f ON c.frecuencia_id = f.id
                WHERE c.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($nombre, $telefonoWhatsapp, $frecuenciaId = null, $rutaId = null, $estado = 'no agendado', $fechaBase = null) {
        $sql = "INSERT INTO clientes (nombre, telefono_whatsapp, frecuencia_id, ruta_id, estado, fecha_base) 
                VALUES (:nombre, :telefono_whatsapp, :frecuencia_id, :ruta_id, :estado::clientes_estado, :fecha_base) 
                RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'telefono_whatsapp' => $telefonoWhatsapp,
            'frecuencia_id' => $frecuenciaId ?: null,
            'ruta_id' => $rutaId ?: null,
            'estado' => $estado ?: 'no agendado',
            'fecha_base' => $fechaBase ?: null
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $nombre, $telefonoWhatsapp, $frecuenciaId = null, $rutaId = null, $estado = 'no agendado', $fechaBase = null) {
        $sql = "UPDATE clientes 
                SET nombre = :nombre, 
                    telefono_whatsapp = :telefono_whatsapp, 
                    frecuencia_id = :frecuencia_id, 
                    ruta_id = :ruta_id, 
                    estado = :estado::clientes_estado,
                    fecha_base = :fecha_base
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'telefono_whatsapp' => $telefonoWhatsapp,
            'frecuencia_id' => $frecuenciaId ?: null,
            'ruta_id' => $rutaId ?: null,
            'estado' => $estado ?: 'no agendado',
            'fecha_base' => $fechaBase ?: null
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
