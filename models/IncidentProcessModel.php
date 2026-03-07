<?php
/**
 * Modulo: Gestión de Incidencias / Modelo
 * Archivo: /models/IncidentProcessModel.php
 * Proposito: Manejar las operaciones de procesamiento y vaciado de CSV a las tablas temporales.
 * Version: 0.0.1 - Transacciones PDO para inserción masiva y consultas de vista previa.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class IncidentProcessModel {

    // 1. Vaciar las 3 tablas antes de procesar para no duplicar datos
    public static function mdlVaciarTablas() {
        $conexion = Conexion::conectar();
        try {
            $conexion->exec("TRUNCATE TABLE tbl_gestion_shopify");
            $conexion->exec("TRUNCATE TABLE tbl_gestion_ced");
            $conexion->exec("TRUNCATE TABLE tbl_gestion_ebay");
            return "ok";
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }

    // 2. Insertar array de datos en Shopify
    public static function mdlInsertarShopify($datos) {
        $conexion = Conexion::conectar();
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare("INSERT INTO tbl_gestion_shopify (sku, title, inventory) VALUES (:sku, :title, :inventory)");
            foreach ($datos as $fila) {
                $stmt->execute(['sku' => $fila['sku'], 'title' => $fila['title'], 'inventory' => $fila['inventory']]);
            }
            $conexion->commit();
            return "ok";
        } catch (PDOException $e) {
            $conexion->rollBack();
            return "error";
        }
    }

    // 3. Insertar array de datos en Ced Commerce
    public static function mdlInsertarCed($datos) {
        $conexion = Conexion::conectar();
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare("INSERT INTO tbl_gestion_ced (sku, title, inventory, profile) VALUES (:sku, :title, :inventory, :profile)");
            foreach ($datos as $fila) {
                $stmt->execute(['sku' => $fila['sku'], 'title' => $fila['title'], 'inventory' => $fila['inventory'], 'profile' => $fila['profile']]);
            }
            $conexion->commit();
            return "ok";
        } catch (PDOException $e) {
            $conexion->rollBack();
            return "error";
        }
    }

    // 4. Insertar array de datos en eBay
    public static function mdlInsertarEbay($datos) {
        $conexion = Conexion::conectar();
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare("INSERT INTO tbl_gestion_ebay (sku, title, inventory) VALUES (:sku, :title, :inventory)");
            foreach ($datos as $fila) {
                $stmt->execute(['sku' => $fila['sku'], 'title' => $fila['title'], 'inventory' => $fila['inventory']]);
            }
            $conexion->commit();
            return "ok";
        } catch (PDOException $e) {
            $conexion->rollBack();
            return "error";
        }
    }

    // 5. Obtener vista previa (5 registros)
    public static function mdlObtenerVistaPrevia($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT * FROM $tabla LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. Contar registros para saber si hay datos procesados
    public static function mdlContarRegistros($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM $tabla");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>