<?php
// app/controllers/core/rutas.php
require_once __DIR__ . '/../../services/core/rutas.php';

class RutaController {
    private $service;

    public function __construct() {
        $this->service = new RutaService();
    }

    public function index($busqueda = null, $sucursalId = null, $ciudad = null, $page = 1, $limit = 10) {
        return $this->service->listarRutas($busqueda, $sucursalId, $ciudad, $page, $limit);
    }

    public function show($id) {
        return $this->service->obtenerRuta($id);
    }

    public function store($datos) {
        return $this->service->crearRuta($datos);
    }

    public function update($id, $datos) {
        return $this->service->actualizarRuta($id, $datos);
    }

    public function destroy($id) {
        return $this->service->eliminarRuta($id);
    }
}
