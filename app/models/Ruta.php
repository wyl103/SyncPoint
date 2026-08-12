<?php
// app/models/Ruta.php
require_once __DIR__ . '/../services/Database.php';

class Ruta {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll() {
        $sql = "SELECT r.id, r.nombre, r.ciudad, r.fk_sucursal, s.nombre AS sucursal_nombre 
                FROM rutas r
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                ORDER BY r.nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
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

    public function getBySucursal($sucursalId) {
        $sql = "SELECT r.id, r.nombre, r.ciudad, r.fk_sucursal, s.nombre AS sucursal_nombre 
                FROM rutas r
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                WHERE r.fk_sucursal = :sucursal_id
                ORDER BY r.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['sucursal_id' => $sucursalId]);
        return $stmt->fetchAll();
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
