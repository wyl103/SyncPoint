<?php
// app/controllers/core/sucursales.php
require_once __DIR__ . '/../../services/core/sucursales.php';

class SucursalController {
    private $service;

    public function __construct() {
        $this->service = new SucursalService();
    }

    public function index($busqueda = null, $destacada = null, $page = 1, $limit = 10) {
        return $this->service->listarSucursales($busqueda, $destacada, $page, $limit);
    }

    public function show($id) {
        return $this->service->obtenerSucursal($id);
    }

    public function store($datos) {
        return $this->service->crearSucursal($datos);
    }

    public function update($id, $datos) {
        return $this->service->actualizarSucursal($id, $datos);
    }

    public function destroy($id) {
        return $this->service->eliminarSucursal($id);
    }
}
