<?php
// app/controllers/RutaController.php
require_once __DIR__ . '/../services/RutaService.php';

class RutaController {
    private $service;

    public function __construct() {
        $this->service = new RutaService();
    }

    public function index($sucursalId = null) {
        if (!empty($sucursalId)) {
            return $this->service->listarPorSucursal($sucursalId);
        }
        return $this->service->listarRutas();
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
