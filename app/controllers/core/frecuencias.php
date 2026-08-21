<?php
// app/controllers/core/frecuencias.php
require_once __DIR__ . '/../../services/core/frecuencias.php';

class FrecuenciaController {
    private $service;

    public function __construct() {
        $this->service = new FrecuenciaService();
    }

    public function index($busqueda = null, $page = 1, $limit = 10) {
        return $this->service->listarFrecuencias($busqueda, $page, $limit);
    }

    public function show($id) {
        return $this->service->obtenerFrecuencia($id);
    }

    public function store($datos) {
        return $this->service->crearFrecuencia($datos);
    }

    public function update($id, $datos) {
        return $this->service->actualizarFrecuencia($id, $datos);
    }

    public function destroy($id) {
        return $this->service->eliminarFrecuencia($id);
    }
}
