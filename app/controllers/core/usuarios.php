<?php
// app/controllers/core/usuarios.php
require_once __DIR__ . '/../../services/core/usuarios.php';

class UsuarioController {
    private $usuarioService;

    public function __construct() {
        $this->usuarioService = new UsuarioService();
    }

    public function index($busqueda = null, $page = 1, $limit = 10) {
        return $this->usuarioService->listarUsuarios($busqueda, $page, $limit);
    }

    public function show($id) {
        return $this->usuarioService->obtenerUsuario($id);
    }

    public function store($datos) {
        return $this->usuarioService->crearUsuario($datos);
    }

    public function update($id, $datos) {
        return $this->usuarioService->actualizarUsuario($id, $datos);
    }

    public function destroy($id) {
        return $this->usuarioService->eliminarUsuario($id);
    }
}
