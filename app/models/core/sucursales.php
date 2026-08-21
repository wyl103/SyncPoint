<?php
// app/models/core/sucursales.php
require_once __DIR__ . '/../../services/Database.php';

class Sucursal {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $destacada = null, $page = 1, $limit = 10) {
        $whereSql = " FROM sucursales WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND nombre ILIKE :busqueda";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        if ($destacada !== null && $destacada !== '' && $destacada !== 'todas') {
            $whereSql .= " AND destacada = :destacada";
            $params['destacada'] = (int)$destacada;
        }

        $countSql = "SELECT COUNT(id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $dataSql = "SELECT id, nombre, destacada" . $whereSql . " ORDER BY nombre ASC LIMIT :limit OFFSET :offset";
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
        $sql = "SELECT id, nombre, destacada FROM sucursales WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($nombre, $destacada = 0) {
        $sql = "INSERT INTO sucursales (nombre, destacada) VALUES (:nombre, :destacada) RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'destacada' => (int)$destacada
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $nombre, $destacada = 0) {
        $sql = "UPDATE sucursales SET nombre = :nombre, destacada = :destacada WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'destacada' => (int)$destacada
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM sucursales WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
