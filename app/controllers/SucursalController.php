<?php
// app/controllers/SucursalController.php
require_once __DIR__ . '/../services/SucursalService.php';

class SucursalController {
    private $service;

    public function __construct() {
        $this->service = new SucursalService();
    }

    public function index() {
        return $this->service->listarSucursales();
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
