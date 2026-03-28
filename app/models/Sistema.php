<?php
require_once __DIR__ . '/../services/Database.php';

class Sistema {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function obtenerEstadosRecoleccion() {
        // Obtenemos la definición de la columna 'estado'
        $stmt = $this->pdo->query("SHOW COLUMNS FROM recolecciones LIKE 'estado'");
        $row = $stmt->fetch();
        
        // El tipo de dato viene como: enum('pendiente','completada','reprogramada')
        // Usamos una expresión regular para extraer solo los valores
        preg_match("/^enum\(\'(.*)\'\)$/", $row['Type'], $matches);
        
        // Convertimos el string resultante en un array
        $estados = explode("','", $matches[1]);
        return $estados;
    }

    public function obtenerSucursalesFiltro() {
        // Traemos todas las sucursales activas (asumiendo que quieres mostrarlas en el filtro)
        $sql = "SELECT id, nombre, destacada FROM sucursales ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}