<?php
// app/controllers/core/eventos.php
require_once __DIR__ . '/../../services/core/eventos.php';

class EventoController {
    private $service;

    public function __construct() {
        $this->service = new EventoService();
    }

    public function index($busqueda = null, $clienteId = null, $rutaId = null, $fecha = null, $estado = null, $tipo = null, $page = 1, $limit = 10) {
        return $this->service->listarEventos($busqueda, $clienteId, $rutaId, $fecha, $estado, $tipo, $page, $limit);
    }

    public function show($id) {
        return $this->service->obtenerEvento($id);
    }

    public function store($datos) {
        return $this->service->crearEvento($datos);
    }

    public function update($id, $datos) {
        return $this->service->actualizarEvento($id, $datos);
    }

    public function destroy($id) {
        return $this->service->eliminarEvento($id);
    }
}
