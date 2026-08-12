<?php
// app/services/core/rutas.php
require_once __DIR__ . '/../../models/core/rutas.php';

class RutaService {
    private $rutaModel;

    public function __construct() {
        $this->rutaModel = new Ruta();
    }

    public function listarRutas($busqueda = null, $sucursalId = null, $ciudad = null, $page = 1, $limit = 10) {
        return $this->rutaModel->getAll($busqueda, $sucursalId, $ciudad, $page, $limit);
    }

    public function obtenerRuta($id) {
        if (empty($id)) {
            throw new Exception("El ID de la ruta es obligatorio.");
        }
        $ruta = $this->rutaModel->getById($id);
        if (!$ruta) {
            throw new Exception("Ruta no encontrada.");
        }
        return $ruta;
    }

    public function crearRuta($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la ruta es obligatorio.");
        }
        if (empty($datos['ciudad'])) {
            throw new Exception("La ciudad de la ruta es obligatoria.");
        }
        if (empty($datos['fk_sucursal'])) {
            throw new Exception("La sucursal asignada (fk_sucursal) es obligatoria.");
        }
        return $this->rutaModel->create(trim($datos['nombre']), trim($datos['ciudad']), $datos['fk_sucursal']);
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
            throw new Exception("La sucursal asignada (fk_sucursal) es obligatoria.");
        }
        $actualizado = $this->rutaModel->update($id, trim($datos['nombre']), trim($datos['ciudad']), $datos['fk_sucursal']);
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar la ruta.");
        }
        return true;
    }

    public function eliminarRuta($id) {
        if (empty($id)) {
            throw new Exception("El ID de la ruta es obligatorio.");
        }
        $eliminado = $this->rutaModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar la ruta.");
        }
        return true;
    }
}
