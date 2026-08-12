<?php
// app/models/Sucursal.php
require_once __DIR__ . '/../services/Database.php';

class Sucursal {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll() {
        $sql = "SELECT id, nombre, destacada FROM sucursales ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
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
            'destacada' => $destacada
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $nombre, $destacada) {
        $sql = "UPDATE sucursales SET nombre = :nombre, destacada = :destacada WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'destacada' => $destacada
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM sucursales WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
