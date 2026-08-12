<?php
// app/services/ClienteService.php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteService {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new Cliente();
    }

    public function listarClientes($busqueda = null, $rutaId = null, $sucursalId = null, $estado = null) {
        return $this->clienteModel->getAll($busqueda, $rutaId, $sucursalId, $estado);
    }

    public function obtenerCliente($id) {
        if (empty($id)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        return $this->clienteModel->getById($id);
    }

    public function crearCliente($datos) {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre del cliente es obligatorio.");
        }
        if (empty($datos['telefono_whatsapp'])) {
            throw new Exception("El teléfono de WhatsApp es obligatorio.");
        }
        $frecuenciaId = !empty($datos['frecuencia_id']) ? $datos['frecuencia_id'] : null;
        $rutaId = !empty($datos['ruta_id']) ? $datos['ruta_id'] : null;
        $estado = !empty($datos['estado']) ? $datos['estado'] : 'no agendado';

        return $this->clienteModel->create(
            $datos['nombre'], 
            $datos['telefono_whatsapp'], 
            $frecuenciaId, 
            $rutaId, 
            $estado
        );
    }

    public function actualizarCliente($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre del cliente es obligatorio.");
        }
        if (empty($datos['telefono_whatsapp'])) {
            throw new Exception("El teléfono de WhatsApp es obligatorio.");
        }
        $frecuenciaId = !empty($datos['frecuencia_id']) ? $datos['frecuencia_id'] : null;
        $rutaId = !empty($datos['ruta_id']) ? $datos['ruta_id'] : null;
        $estado = !empty($datos['estado']) ? $datos['estado'] : 'no agendado';

        return $this->clienteModel->update(
            $id, 
            $datos['nombre'], 
            $datos['telefono_whatsapp'], 
            $frecuenciaId, 
            $rutaId, 
            $estado
        );
    }

    public function eliminarCliente($id) {
        if (empty($id)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        return $this->clienteModel->delete($id);
    }
}
