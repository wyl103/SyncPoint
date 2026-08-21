<?php
// app/models/core/frecuencias.php
require_once __DIR__ . '/../../services/Database.php';

class Frecuencia {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $page = 1, $limit = 10) {
        $whereSql = " FROM frecuencias WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND nombre ILIKE :busqueda";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        $countSql = "SELECT COUNT(id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);
        $offset = ($page - 1) * $limit;

        $dataSql = "SELECT id, nombre, dias" . $whereSql . " ORDER BY dias ASC, nombre ASC LIMIT :limit OFFSET :offset";
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
        $sql = "SELECT id, nombre, dias FROM frecuencias WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($nombre, $dias) {
        $sql = "INSERT INTO frecuencias (nombre, dias) VALUES (:nombre, :dias) RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'dias' => (int)$dias
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $nombre, $dias) {
        $sql = "UPDATE frecuencias SET nombre = :nombre, dias = :dias WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'dias' => (int)$dias
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM frecuencias WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
