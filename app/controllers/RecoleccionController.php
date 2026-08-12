<?php
// app/controllers/RecoleccionController.php
require_once __DIR__ . '/../models/Recoleccion.php';

class RecoleccionController {
    public function obtenerDelDia($fecha, $estado = null, $sucursal = null) {
        $modelo = new Recoleccion();
        return $modelo->obtenerPorFecha($fecha, $estado, $sucursal);
    }
}