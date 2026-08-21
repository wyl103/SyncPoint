<?php
// app/services/core/sucursales.php
require_once __DIR__ . '/../../models/core/sucursales.php';

class SucursalService {
    private $sucursalModel;

    public function __construct() {
        $this->sucursalModel = new Sucursal();
    }

    public function listarSucursales($busqueda = null, $destacada = null, $page = 1, $limit = 10) {
        return $this->sucursalModel->getAll($busqueda, $destacada, $page, $limit);
    }

    public function obtenerSucursal($id) {
        if (empty($id)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        $sucursal = $this->sucursalModel->getById($id);
        if (!$sucursal) {
            throw new Exception("Sucursal no encontrada.");
        }
        return $sucursal;
    }

    public function crearSucursal($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la sucursal es obligatorio.");
        }
        $destacada = isset($datos['destacada']) ? (int)$datos['destacada'] : 0;
        return $this->sucursalModel->create(trim($datos['nombre']), $destacada);
    }

    public function actualizarSucursal($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la sucursal es obligatorio.");
        }
        $destacada = isset($datos['destacada']) ? (int)$datos['destacada'] : 0;
        $actualizado = $this->sucursalModel->update($id, trim($datos['nombre']), $destacada);
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar la sucursal.");
        }
        return true;
    }

    public function eliminarSucursal($id) {
        if (empty($id)) {
            throw new Exception("El ID de la sucursal es obligatorio.");
        }
        $eliminado = $this->sucursalModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar la sucursal.");
        }
        return true;
    }
}
