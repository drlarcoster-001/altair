<?php
/**
 * Modulo: Análisis de Inventario / Modelo
 * Archivo: /models/InventoryBatchModel.php
 * Proposito: Manejar las operaciones de BD para la tabla inventory_batches.
 * Version: 0.0.1 - Inserción, consulta y eliminación para 4 archivos.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class InventoryBatchModel {

    public static function mdlRegistrarLote($tabla, $datos) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("INSERT INTO $tabla (batch_name, shopify1_file_path, ebay1_file_path, shopify2_file_path, ebay2_file_path) VALUES (:batch_name, :shopify1, :ebay1, :shopify2, :ebay2)");

        $stmt->bindParam(":batch_name", $datos["batch_name"], PDO::PARAM_STR);
        $stmt->bindParam(":shopify1", $datos["shopify1"], PDO::PARAM_STR);
        $stmt->bindParam(":ebay1", $datos["ebay1"], PDO::PARAM_STR);
        $stmt->bindParam(":shopify2", $datos["shopify2"], PDO::PARAM_STR);
        $stmt->bindParam(":ebay2", $datos["ebay2"], PDO::PARAM_STR);

        if ($stmt->execute()) return "ok";
        else return "error";
    }

    public static function mdlObtenerUltimaCarga($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT * FROM $tabla ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function mdlObtenerCargaPorId($tabla, $id) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function mdlEliminarLote($tabla, $id) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) return "ok";
        else return "error";
    }
}
?>