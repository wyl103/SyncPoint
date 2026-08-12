<?php
// app/services/core/clientes.php
require_once __DIR__ . '/../../models/core/clientes.php';

class ClienteService {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new Cliente();
    }

    public function listarClientes($busqueda = null, $rutaId = null, $sucursalId = null, $estado = null, $page = 1, $limit = 10) {
        return $this->clienteModel->getAll($busqueda, $rutaId, $sucursalId, $estado, $page, $limit);
    }

    public function obtenerCliente($id) {
        if (empty($id)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        $cliente = $this->clienteModel->getById($id);
        if (!$cliente) {
            throw new Exception("Cliente no encontrado.");
        }
        return $cliente;
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
            trim($datos['nombre']),
            trim($datos['telefono_whatsapp']),
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

        $actualizado = $this->clienteModel->update(
            $id,
            trim($datos['nombre']),
            trim($datos['telefono_whatsapp']),
            $frecuenciaId,
            $rutaId,
            $estado
        );

        if (!$actualizado) {
            throw new Exception("No se pudo actualizar el cliente.");
        }
        return true;
    }

    public function eliminarCliente($id) {
        if (empty($id)) {
            throw new Exception("El ID del cliente es obligatorio.");
        }
        $eliminado = $this->clienteModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar el cliente.");
        }
        return true;
    }
}
