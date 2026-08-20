<?php
// app/services/core/eventos.php
require_once __DIR__ . '/../../models/core/eventos.php';

class EventoService {
    private $eventoModel;

    public function __construct() {
        $this->eventoModel = new Evento();
    }

    public function listarEventos($busqueda = null, $clienteId = null, $rutaId = null, $fecha = null, $estado = null, $tipo = null, $page = 1, $limit = 10) {
        return $this->eventoModel->getAll($busqueda, $clienteId, $rutaId, $fecha, $estado, $tipo, $page, $limit);
    }

    public function obtenerEvento($id) {
        if (empty($id)) {
            throw new Exception("El ID del evento es obligatorio.");
        }
        $evento = $this->eventoModel->getById($id);
        if (!$evento) {
            throw new Exception("Evento no encontrado.");
        }
        return $evento;
    }

    public function crearEvento($datos) {
        if (empty($datos['fecha_programada'])) {
            throw new Exception("La fecha programada (fecha_programada) es obligatoria.");
        }

        $clienteId = !empty($datos['cliente_id']) ? $datos['cliente_id'] : null;
        $rutaId = !empty($datos['ruta_id']) ? $datos['ruta_id'] : null;
        $fechaProgramada = $datos['fecha_programada'];
        $estado = !empty($datos['estado']) ? $datos['estado'] : 'programado';
        $tipo = !empty($datos['tipo']) ? trim($datos['tipo']) : 'frecuente';
        $notificaciones = isset($datos['notificaciones']) ? $datos['notificaciones'] : null;
        $eventoOrigin = !empty($datos['evento_origin']) ? $datos['evento_origin'] : 'user';

        return $this->eventoModel->create($clienteId, $rutaId, $fechaProgramada, $estado, $tipo, $notificaciones, $eventoOrigin);
    }

    public function actualizarEvento($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID del evento es obligatorio.");
        }

        $clienteId = array_key_exists('cliente_id', $datos) ? $datos['cliente_id'] : null;
        $rutaId = array_key_exists('ruta_id', $datos) ? $datos['ruta_id'] : null;
        $fechaProgramada = array_key_exists('fecha_programada', $datos) ? $datos['fecha_programada'] : null;
        $estado = array_key_exists('estado', $datos) ? $datos['estado'] : null;
        $tipo = array_key_exists('tipo', $datos) ? $datos['tipo'] : null;
        $notificaciones = array_key_exists('notificaciones', $datos) ? $datos['notificaciones'] : null;
        $eventoOrigin = array_key_exists('evento_origin', $datos) ? $datos['evento_origin'] : null;

        $actualizado = $this->eventoModel->update($id, $clienteId, $rutaId, $fechaProgramada, $estado, $tipo, $notificaciones, $eventoOrigin);
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar el evento.");
        }
        return true;
    }

    public function eliminarEvento($id) {
        if (empty($id)) {
            throw new Exception("El ID del evento es obligatorio.");
        }
        $eliminado = $this->eventoModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar el evento.");
        }
        return true;
    }
}
