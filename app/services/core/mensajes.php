<?php
// app/services/core/mensajes.php
require_once __DIR__ . '/../../models/core/mensajes.php';

class MensajeService {
    private $mensajeModel;

    public function __construct() {
        $this->mensajeModel = new Mensaje();
    }

    public function listarMensajes($busqueda = null, $estado = null, $chatwootConversationId = null, $page = 1, $limit = 10) {
        return $this->mensajeModel->getAll($busqueda, $estado, $chatwootConversationId, $page, $limit);
    }

    public function obtenerMensaje($id) {
        if (empty($id)) {
            throw new Exception("El ID del mensaje es obligatorio.");
        }
        $mensaje = $this->mensajeModel->getById($id);
        if (!$mensaje) {
            throw new Exception("Mensaje no encontrado.");
        }
        return $mensaje;
    }

    public function crearMensaje($datos) {
        $estado = !empty($datos['estado']) ? $datos['estado'] : 'enviado';
        $chatwootConversationId = !empty($datos['chatwoot_conversation_id']) ? trim($datos['chatwoot_conversation_id']) : null;

        return $this->mensajeModel->create($chatwootConversationId, $estado);
    }

    public function actualizarMensaje($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID del mensaje es obligatorio.");
        }

        $estado = array_key_exists('estado', $datos) ? $datos['estado'] : null;
        $chatwootConversationId = array_key_exists('chatwoot_conversation_id', $datos) ? $datos['chatwoot_conversation_id'] : null;

        $actualizado = $this->mensajeModel->update($id, $chatwootConversationId, $estado);
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar el registro de mensaje.");
        }
        return true;
    }

    public function eliminarMensaje($id) {
        if (empty($id)) {
            throw new Exception("El ID del mensaje es obligatorio.");
        }
        $eliminado = $this->mensajeModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar el registro de mensaje.");
        }
        return true;
    }
}
