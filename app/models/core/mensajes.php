<?php
// app/models/core/mensajes.php
require_once __DIR__ . '/../../services/Database.php';

class Mensaje {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll($busqueda = null, $estado = null, $chatwootConversationId = null, $page = 1, $limit = 10) {
        $whereSql = " FROM mensajes m WHERE 1=1";
        $params = [];

        if (!empty($busqueda)) {
            $whereSql .= " AND (m.chatwoot_conversation_id ILIKE :busqueda OR m.estado ILIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        if (!empty($estado) && $estado !== 'todos') {
            $whereSql .= " AND m.estado = :estado";
            $params['estado'] = $estado;
        }

        if (!empty($chatwootConversationId)) {
            $whereSql .= " AND m.chatwoot_conversation_id = :chatwoot_conv_id";
            $params['chatwoot_conv_id'] = $chatwootConversationId;
        }

        // 1. Contador total
        $countSql = "SELECT COUNT(m.id)" . $whereSql;
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalRows = (int)$stmtCount->fetchColumn();

        // 2. Parámetros de paginación
        $page = max(1, (int)$page);
        $limit = in_array((int)$limit, [10, 50, 100]) ? (int)$limit : 10;
        $offset = ($page - 1) * $limit;

        // 3. Consulta de datos
        $dataSql = "SELECT 
                        m.id,
                        m.chatwoot_conversation_id,
                        m.fecha_actualizacion,
                        m.estado"
                 . $whereSql . " ORDER BY m.id DESC LIMIT :limit OFFSET :offset";

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
        $sql = "SELECT 
                    m.id,
                    m.chatwoot_conversation_id,
                    m.fecha_actualizacion,
                    m.estado
                FROM mensajes m
                WHERE m.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($chatwootConversationId = null, $estado = 'enviado') {
        $sql = "INSERT INTO mensajes (chatwoot_conversation_id, fecha_actualizacion, estado) 
                VALUES (:chatwoot_conversation_id, CURRENT_TIMESTAMP, :estado) 
                RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'chatwoot_conversation_id' => $chatwootConversationId ?: null,
            'estado' => $estado ?: 'enviado'
        ]);
        $result = $stmt->fetch();
        return $result ? $result['id'] : true;
    }

    public function update($id, $chatwootConversationId = null, $estado = null) {
        $fields = [];
        $params = ['id' => $id];

        if ($chatwootConversationId !== null) {
            $fields[] = "chatwoot_conversation_id = :chatwoot_conversation_id";
            $params['chatwoot_conversation_id'] = $chatwootConversationId ?: null;
        }

        if ($estado !== null) {
            $fields[] = "estado = :estado";
            $params['estado'] = $estado;
        }

        $fields[] = "fecha_actualizacion = CURRENT_TIMESTAMP";

        $sql = "UPDATE mensajes SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM mensajes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
