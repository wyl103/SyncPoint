<?php
// app/controllers/core/mensajes.php
require_once __DIR__ . '/../../services/core/mensajes.php';

class MensajeController {
    private $service;

    public function __construct() {
        $this->service = new MensajeService();
    }

    public function index($busqueda = null, $estado = null, $chatwootConversationId = null, $page = 1, $limit = 10) {
        return $this->service->listarMensajes($busqueda, $estado, $chatwootConversationId, $page, $limit);
    }

    public function show($id) {
        return $this->service->obtenerMensaje($id);
    }

    public function store($datos) {
        return $this->service->crearMensaje($datos);
    }

    public function update($id, $datos) {
        return $this->service->actualizarMensaje($id, $datos);
    }

    public function destroy($id) {
        return $this->service->eliminarMensaje($id);
    }
}
