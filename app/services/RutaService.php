<?php
// app/services/RutaService.php
require_once __DIR__ . '/../models/Ruta.php';

class RutaService {
    private $rutaModel;

    public function __construct() {
        $this->rutaModel = new Ruta();
    }

    public function listarRutas() {
        return $this->rutaModel->getAll();
    }

    public function obtenerRuta($id) {
        if (empty($id)) {
            throw new Exception("El ID de la ruta es obligatorio.");
        }
        return $this->rutaModel->getById($id);
    }

    public function listarPorSucursal($sucursalId) {
        if (empty($sucursalId)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        return $this->rutaModel->getBySucursal($sucursalId);
    }

    public function crearRuta($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la ruta es obligatorio.");
        }
        if (empty($datos['ciudad'])) {
            throw new Exception("La ciudad de la ruta es obligatoria.");
        }
        if (empty($datos['fk_sucursal'])) {
            throw new Exception("La sucursal asignada a la ruta es obligatoria.");
        }
        return $this->rutaModel->create($datos['nombre'], $datos['ciudad'], $datos['fk_sucursal']);
    }

    public function actualizarRuta($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID de la ruta es obligatorio.");
        }
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la ruta es obligatorio.");
        }
        if (empty($datos['ciudad'])) {
            throw new Exception("La ciudad de la ruta es obligatoria.");
        }
        if (empty($datos['fk_sucursal'])) {
            throw new Exception("La sucursal asignada a la ruta es obligatoria.");
        }
        return $this->rutaModel->update($id, $datos['nombre'], $datos['ciudad'], $datos['fk_sucursal']);
    }

    public function eliminarRuta($id) {
        if (empty($id)) {
            throw new Exception("El ID de la ruta es obligatorio.");
        }
        return $this->rutaModel->delete($id);
    }
}
