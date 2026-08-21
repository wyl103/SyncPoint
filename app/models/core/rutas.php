<?php
// app/models/core/rutas.php
require_once __DIR__ . '/../../services/Database.php';

class Ruta {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $sucursalId = null, $ciudad = null, $page = 1, $limit = 10) {
        $whereSql = " FROM rutas r
                      LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                      WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND (r.nombre ILIKE :busqueda OR r.ciudad ILIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        if (!empty($sucursalId) && $sucursalId !== 'todas') {
            $whereSql .= " AND r.fk_sucursal = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        if (!empty($ciudad)) {
            $whereSql .= " AND r.ciudad ILIKE :ciudad";
            $params['ciudad'] = '%' . $ciudad . '%';
        }

        $countSql = "SELECT COUNT(r.id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $dataSql = "SELECT r.id, r.nombre, r.ciudad, r.fk_sucursal, s.nombre AS sucursal_nombre" 
                 . $whereSql . " ORDER BY r.nombre ASC LIMIT :limit OFFSET :offset";
        
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
        $sql = "SELECT r.id, r.nombre, r.ciudad, r.fk_sucursal, s.nombre AS sucursal_nombre 
                FROM rutas r
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                WHERE r.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($nombre, $ciudad, $fk_sucursal) {
        $sql = "INSERT INTO rutas (nombre, ciudad, fk_sucursal) VALUES (:nombre, :ciudad, :fk_sucursal) RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'ciudad' => $ciudad,
            'fk_sucursal' => $fk_sucursal
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $nombre, $ciudad, $fk_sucursal) {
        $sql = "UPDATE rutas SET nombre = :nombre, ciudad = :ciudad, fk_sucursal = :fk_sucursal WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'ciudad' => $ciudad,
            'fk_sucursal' => $fk_sucursal
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM rutas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
