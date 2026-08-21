<?php
// app/services/core/usuarios.php
require_once __DIR__ . '/../../models/core/usuarios.php';

class UsuarioService {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function listarUsuarios($busqueda = null, $page = 1, $limit = 10) {
        return $this->usuarioModel->getAll($busqueda, $page, $limit);
    }

    public function obtenerUsuario($id) {
        if (empty($id)) {
            throw new Exception("El ID del usuario es obligatorio.");
        }
        $usuario = $this->usuarioModel->getById($id);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado.");
        }
        return $usuario;
    }

    public function crearUsuario($datos) {
        $nombre = trim($datos['nombre'] ?? '');
        $correo = trim($datos['correo'] ?? $datos['email'] ?? '');
        $password = trim($datos['password'] ?? $datos['clave'] ?? '');
        $tipo = trim($datos['tipo'] ?? 'normal');

        if (empty($nombre)) {
            throw new Exception("El nombre del usuario es obligatorio.");
        }
        if (empty($correo)) {
            throw new Exception("El correo electrónico es obligatorio.");
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido.");
        }
        if (empty($password)) {
            throw new Exception("La contraseña es obligatoria para la creación del usuario.");
        }
        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres.");
        }

        // Verificar si el correo ya existe
        $existente = $this->usuarioModel->getByCorreo($correo);
        if ($existente) {
            throw new Exception("Ya existe un usuario registrado con el correo '{$correo}'.");
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        return $this->usuarioModel->create($nombre, $correo, $passwordHash, $tipo);
    }

    public function actualizarUsuario($id, $datos) {
        if (empty($id)) {
            throw new Exception("El ID del usuario es obligatorio.");
        }

        $existente = $this->usuarioModel->getById($id);
        if (!$existente) {
            throw new Exception("Usuario no encontrado.");
        }

        $nombre = array_key_exists('nombre', $datos) ? trim($datos['nombre']) : null;
        $correo = array_key_exists('correo', $datos) ? trim($datos['correo']) : (array_key_exists('email', $datos) ? trim($datos['email']) : null);
        $password = !empty($datos['password']) ? trim($datos['password']) : (!empty($datos['clave']) ? trim($datos['clave']) : null);
        $tipo = array_key_exists('tipo', $datos) ? trim($datos['tipo']) : null;

        if ($correo !== null) {
            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("El formato del correo electrónico no es válido.");
            }
            $usuarioConMismoCorreo = $this->usuarioModel->getByCorreo($correo);
            if ($usuarioConMismoCorreo && (int)$usuarioConMismoCorreo['id'] !== (int)$id) {
                throw new Exception("El correo '{$correo}' ya está en uso por otro usuario.");
            }
        }

        $passwordHash = null;
        if (!empty($password)) {
            if (strlen($password) < 6) {
                throw new Exception("La contraseña debe tener al menos 6 caracteres.");
            }
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        }

        $actualizado = $this->usuarioModel->update($id, $nombre, $correo, $passwordHash, $tipo);
        if (!$actualizado) {
            throw new Exception("No se pudo actualizar el usuario.");
        }
        return true;
    }

    public function eliminarUsuario($id) {
        if (empty($id)) {
            throw new Exception("El ID del usuario es obligatorio.");
        }
        $eliminado = $this->usuarioModel->delete($id);
        if (!$eliminado) {
            throw new Exception("No se pudo eliminar el usuario.");
        }
        return true;
    }
}
