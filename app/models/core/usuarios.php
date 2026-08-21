<?php
// app/models/core/usuarios.php
require_once __DIR__ . '/../../services/Database.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
        try {
            $this->pdo->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS tipo VARCHAR(50) DEFAULT 'normal';");
        } catch (Exception $e) {
            // Ignorar si ya existe o por permisos
        }
    }

    public static function validarTipo($tipo) {
        $permitidos = ['administrador', 'normal'];
        $val = strtolower(trim($tipo ?? ''));
        return in_array($val, $permitidos) ? $val : 'normal';
    }

    public function getAll($busqueda = null, $page = 1, $limit = 10) {
        $whereSql = " FROM usuarios u WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND (u.nombre ILIKE :busqueda OR u.correo ILIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        // 1. Contador total
        $countSql = "SELECT COUNT(u.id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        // 2. Parámetros de paginación
        $page = max(1, (int)$page);
        $limit = in_array((int)$limit, [10, 50, 100]) ? (int)$limit : 10;
        $offset = ($page - 1) * $limit;

        // 3. Consulta de datos
        $dataSql = "SELECT 
                        u.id,
                        u.nombre,
                        u.correo,
                        COALESCE(u.tipo, 'normal') AS tipo,
                        u.created_at"
                 . $whereSql . " ORDER BY u.id DESC LIMIT :limit OFFSET :offset";

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
        $sql = "SELECT id, nombre, correo, COALESCE(tipo, 'normal') AS tipo, created_at FROM usuarios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getByCorreo($correo) {
        $sql = "SELECT id, nombre, correo, clave, COALESCE(tipo, 'normal') AS tipo FROM usuarios WHERE LOWER(TRIM(correo)) = LOWER(TRIM(:correo))";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['correo' => $correo]);
        return $stmt->fetch();
    }

    public static function generateUuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function create($nombre, $correo, $passwordHash, $tipo = 'normal') {
        $tipoValidado = self::validarTipo($tipo);
        $correoLwr = strtolower(trim($correo));

        // 1. Intentar inserción estándar (para tablas con id bigint autoincremental o DEFAULT)
        try {
            $sql = "INSERT INTO usuarios (nombre, correo, clave, tipo, created_at) 
                    VALUES (:nombre, :correo, :clave, :tipo, CURRENT_DATE) 
                    RETURNING id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'correo' => $correoLwr,
                'clave' => $passwordHash,
                'tipo' => $tipoValidado
            ]);
            $result = $stmt->fetch();
            return $result ? $result['id'] : true;
        } catch (Exception $e1) {
            try {
                $sql = "INSERT INTO usuarios (nombre, correo, clave, tipo) 
                        VALUES (:nombre, :correo, :clave, :tipo) 
                        RETURNING id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'nombre' => $nombre,
                    'correo' => $correoLwr,
                    'clave' => $passwordHash,
                    'tipo' => $tipoValidado
                ]);
                $result = $stmt->fetch();
                return $result ? $result['id'] : true;
            } catch (Exception $e2) {
                // 2. Si falla por not-null constraint en 'id' (ej. columna bpchar / uuid sin DEFAULT sequence)
                $uuid = self::generateUuid();
                try {
                    $sql = "INSERT INTO usuarios (id, nombre, correo, clave, tipo, created_at) 
                            VALUES (:id, :nombre, :correo, :clave, :tipo, CURRENT_DATE) 
                            RETURNING id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        'id' => $uuid,
                        'nombre' => $nombre,
                        'correo' => $correoLwr,
                        'clave' => $passwordHash,
                        'tipo' => $tipoValidado
                    ]);
                    $result = $stmt->fetch();
                    return $result ? $result['id'] : $uuid;
                } catch (Exception $e3) {
                    $sql = "INSERT INTO usuarios (id, nombre, correo, clave, tipo) 
                            VALUES (:id, :nombre, :correo, :clave, :tipo) 
                            RETURNING id";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        'id' => $uuid,
                        'nombre' => $nombre,
                        'correo' => $correoLwr,
                        'clave' => $passwordHash,
                        'tipo' => $tipoValidado
                    ]);
                    $result = $stmt->fetch();
                    return $result ? $result['id'] : $uuid;
                }
            }
        }
    }

    public function update($id, $nombre = null, $correo = null, $passwordHash = null, $tipo = null) {
        $fields = [];
        $params = ['id' => $id];

        if ($nombre !== null) {
            $fields[] = "nombre = :nombre";
            $params['nombre'] = trim($nombre);
        }

        if ($correo !== null) {
            $fields[] = "correo = :correo";
            $params['correo'] = strtolower(trim($correo));
        }

        if (!empty($passwordHash)) {
            $fields[] = "clave = :clave";
            $params['clave'] = $passwordHash;
        }

        if ($tipo !== null) {
            $fields[] = "tipo = :tipo";
            $params['tipo'] = self::validarTipo($tipo);
        }

        if (empty($fields)) {
            return true;
        }

        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
