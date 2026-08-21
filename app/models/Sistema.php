<?php
// app/models/Sistema.php
require_once __DIR__ . '/../services/Database.php';

class Sistema {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function obtenerEstadosRecoleccion() {
        try {
            // Consulta compatible con PostgreSQL para obtener los valores del ENUM 'recolecciones_estado'
            $sql = "SELECT e.enumlabel 
                    FROM pg_enum e 
                    JOIN pg_type t ON e.enumtypid = t.oid 
                    WHERE t.typname = 'recolecciones_estado'
                    ORDER BY e.enumsortorder";
            $stmt = $this->pdo->query($sql);
            $estados = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($estados)) {
                return $estados;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo estados en PostgreSQL: " . $e->getMessage());
        }

        // Fallback seguro de estados
        return ['pendiente', 'completado', 'cancelado'];
    }

    public function obtenerSucursalesFiltro() {
        $sql = "SELECT id, nombre, destacada FROM sucursales ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}