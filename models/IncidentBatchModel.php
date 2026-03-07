<?php
/**
 * Modulo: Gestión de Incidencias / Modelo
 * Archivo: /models/IncidentBatchModel.php
 * Proposito: Manejar las operaciones de BD para la tabla incident_batches (Insertar, Consultar y Eliminar).
 * Version: 0.0.4 - Se agregan funciones para buscar por ID y eliminar lote.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class IncidentBatchModel {

    // Registrar un nuevo lote de archivos
    public static function mdlRegistrarLote($tabla, $datos) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("INSERT INTO $tabla (batch_name, shopify_file_path, cedcommerce_file_path, ebay_file_path) VALUES (:batch_name, :shopify_file_path, :cedcommerce_file_path, :ebay_file_path)");

        $stmt->bindParam(":batch_name", $datos["batch_name"], PDO::PARAM_STR);
        $stmt->bindParam(":shopify_file_path", $datos["shopify"], PDO::PARAM_STR);
        $stmt->bindParam(":cedcommerce_file_path", $datos["cedcommerce"], PDO::PARAM_STR);
        $stmt->bindParam(":ebay_file_path", $datos["ebay"], PDO::PARAM_STR);

        if ($stmt->execute()) return "ok";
        else return "error";
    }

    // Obtener el último registro insertado
    public static function mdlObtenerUltimaCarga($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener un registro específico por ID (Necesario para saber las rutas antes de borrar)
    public static function mdlObtenerCargaPorId($tabla, $id) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Eliminar el registro de la base de datos
    public static function mdlEliminarLote($tabla, $id) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) return "ok";
        else return "error";
    }
}
?>