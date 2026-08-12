<?php
// app/models/Cliente.php
require_once __DIR__ . '/../services/Database.php';

class Cliente {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $rutaId = null, $sucursalId = null, $estado = null) {
        $sql = "SELECT 
                    c.id, 
                    c.nombre, 
                    c.telefono_whatsapp, 
                    c.frecuencia_id, 
                    c.ruta_id, 
                    c.estado,
                    r.nombre AS ruta_nombre,
                    r.ciudad AS ruta_ciudad,
                    s.id AS sucursal_id,
                    s.nombre AS sucursal_nombre,
                    f.nombre AS frecuencia_nombre
                FROM clientes c
                LEFT JOIN rutas r ON c.ruta_id = r.id
                LEFT JOIN sucursales s ON r.fk_sucursal = s.id
                LEFT JOIN frecuencias f ON c.frecuencia_id = f.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (c.nombre ILIKE :busqueda OR c.telefono_whatsapp ILIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        if (!empty($rutaId) && $rutaId !== 'todas') {
            $sql .= " AND c.ruta_id = :ruta_id";
            $params['ruta_id'] = $rutaId;
        }

        if (!empty($sucursalId) && $sucursalId !== 'todas') {
            $sql .= " AND r.fk_sucursal = :sucursal_id";
            $params['sucursal_id'] = $sucursalId;
        }

        if (!empty($estado) && $estado !== 'todos') {
            $sql .= " AND c.estado::text = :estado";
            $params['estado'] = $estado;
        }

        $sql .= " ORDER BY c.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT 
                    c.id, 
                    c.nombre, 
                    c.telefono_whatsapp, 
                    c.frecuencia_id, 
                    c.ruta_id, 
                    c.estado,
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

    public function create($nombre, $telefonoWhatsapp, $frecuenciaId = null, $rutaId = null, $estado = 'no agendado') {
        $sql = "INSERT INTO clientes (nombre, telefono_whatsapp, frecuencia_id, ruta_id, estado) 
                VALUES (:nombre, :telefono_whatsapp, :frecuencia_id, :ruta_id, :estado::clientes_estado) 
                RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'telefono_whatsapp' => $telefonoWhatsapp,
            'frecuencia_id' => $frecuenciaId ?: null,
            'ruta_id' => $rutaId ?: null,
            'estado' => $estado ?: 'no agendado'
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $nombre, $telefonoWhatsapp, $frecuenciaId = null, $rutaId = null, $estado = 'no agendado') {
        $sql = "UPDATE clientes 
                SET nombre = :nombre, 
                    telefono_whatsapp = :telefono_whatsapp, 
                    frecuencia_id = :frecuencia_id, 
                    ruta_id = :ruta_id, 
                    estado = :estado::clientes_estado 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nombre' => $nombre,
            'telefono_whatsapp' => $telefonoWhatsapp,
            'frecuencia_id' => $frecuenciaId ?: null,
            'ruta_id' => $rutaId ?: null,
            'estado' => $estado ?: 'no agendado'
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
