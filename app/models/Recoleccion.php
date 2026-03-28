<?php
// app/models/Recoleccion.php
require_once __DIR__ . '/../services/Database.php';

class Recoleccion {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function obtenerPorFecha($fecha, $estado = null, $sucursal = null) {
        $sql = "SELECT r.id, r.fecha_programada, r.estado AS estado_recoleccion,
               c.id AS cliente_id, c.nombre AS cliente_nombre, c.telefono_whatsapp, c.estado AS estado_cliente,
               ru.nombre AS ruta_nombre,
               s.id AS sucursal_id, s.nombre AS sucursal_nombre, s.destacada
               FROM recolecciones r
                INNER JOIN clientes c ON r.cliente_id = c.id
                LEFT JOIN rutas ru ON r.ruta_id = ru.id
                LEFT JOIN sucursales s ON ru.fk_sucursal = s.id
                WHERE r.fecha_programada = :fecha";
                
        $params = ['fecha' => $fecha];
        
        // Filtro Estado
        if (!empty($estado) && $estado !== 'todos') {
            $sql .= " AND r.estado = :estado";
            $params['estado'] = $estado;
        }

        // Filtro Sucursal
        if (!empty($sucursal) && $sucursal !== 'todas') {
            if ($sucursal === 'otras') {
                $sql .= " AND s.id NOT IN (1, 3, 4, 6, 10)";
            } else {
                $sql .= " AND s.id = :sucursal";
                $params['sucursal'] = $sucursal;
            }
        }
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    function getPdo() { return $this->pdo; }
}