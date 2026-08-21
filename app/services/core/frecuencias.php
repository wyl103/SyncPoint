<?php
// app/services/core/frecuencias.php
require_once __DIR__ . '/../../models/core/frecuencias.php';

class FrecuenciaService {
    private $frecuenciaModel;

    public function __construct() {
        $this->frecuenciaModel = new Frecuencia();
    }

    public function listarFrecuencias($busqueda = null, $page = 1, $limit = 10) {
        return $this->frecuenciaModel->getAll($busqueda, $page, $limit);
    }

    public function obtenerFrecuencia($id) {
        if (empty($id)) {
            throw new Exception("El ID de la frecuencia es obligatorio.");
        }
        $frecuencia = $this->frecuenciaModel->getById($id);
        if (!$frecuencia) {
            throw new Exception("Frecuencia no encontrada.");
        }
        return $frecuencia;
    }

    public function crearFrecuencia($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la frecuencia es obligatorio.");
        }
        if (!isset($datos['dias']) || $datos['dias'] === '') {
            throw new Exception("El número de días es obligatorio.");
        }
        return $this->frecuenciaModel->create(trim($datos['nombre']), (int)$datos['dias']);
    }

    public function actualizarFrecuencia($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID de la frecuencia es obligatorio.");
        }
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre de la frecuencia es obligatorio.");
        }
        if (!isset($datos['dias']) || $datos['dias'] === '') {
            throw new Exception("El número de días es obligatorio.");
        }
        $actualizado = $this->frecuenciaModel->update($id, trim($datos['nombre']), (int)$datos['dias']);
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar la frecuencia.");
        }
        return true;
    }

    public function eliminarFrecuencia($id) {
        if (empty($id)) {
            throw new Exception("El ID de la frecuencia es obligatorio.");
        }
        $eliminado = $this->frecuenciaModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar la frecuencia.");
        }
        return true;
    }
}
