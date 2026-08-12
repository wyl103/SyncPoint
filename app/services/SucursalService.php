<?php
// app/services/SucursalService.php
require_once __DIR__ . '/../models/Sucursal.php';

class SucursalService {
    private $sucursalModel;

    public function __construct() {
        $this->sucursalModel = new Sucursal();
    }

    public function listarSucursales() {
        return $this->sucursalModel->getAll();
    }

    public function obtenerSucursal($id) {
        if (empty($id)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        return $this->sucursalModel->getById($id);
    }

    public function crearSucursal($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la sucursal es obligatorio.");
        }
        $destacada = isset($datos['destacada']) ? (int)$datos['destacada'] : 0;
        return $this->sucursalModel->create($datos['nombre'], $destacada);
    }

    public function actualizarSucursal($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la sucursal es obligatorio.");
        }
        $destacada = isset($datos['destacada']) ? (int)$datos['destacada'] : 0;
        return $this->sucursalModel->update($id, $datos['nombre'], $destacada);
    }

    public function eliminarSucursal($id) {
        if (empty($id)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        return $this->sucursalModel->delete($id);
    }
}
