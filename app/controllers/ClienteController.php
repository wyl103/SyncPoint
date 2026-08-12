<?php
// app/controllers/ClienteController.php
require_once __DIR__ . '/../services/ClienteService.php';

class ClienteController {
    private $service;

    public function __construct() {
        $this->service = new ClienteService();
    }

    public function index($busqueda = null, $rutaId = null, $sucursalId = null, $estado = null, $page = 1, $limit = 10) {
        return $this->service->listarClientes($busqueda, $rutaId, $sucursalId, $estado, $page, $limit);
    }

    public function show($id) {
        return $this->service->obtenerCliente($id);
    }

    public function store($datos) {
        return $this->service->crearCliente($datos);
    }

    public function update($id, $datos) {
        return $this->service->actualizarCliente($id, $datos);
    }

    public function destroy($id) {
        return $this->service->eliminarCliente($id);
    }
}
