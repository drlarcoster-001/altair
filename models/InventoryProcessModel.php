<?php
/**
 * Modulo: Análisis de Inventario / Modelo
 * Archivo: /models/InventoryProcessModel.php
 * Proposito: Vaciar, insertar masivamente y consultar las 4 tablas temporales.
 * Version: 0.0.1 - Transacciones PDO.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class InventoryProcessModel {

    // 1. Vaciar las 4 tablas
    public static function mdlVaciarTablas() {
        $conexion = Conexion::conectar();
        try {
            $conexion->exec("TRUNCATE TABLE tbl_shopify1");
            $conexion->exec("TRUNCATE TABLE tbl_ebay1");
            $conexion->exec("TRUNCATE TABLE tbl_shopify2");
            $conexion->exec("TRUNCATE TABLE tbl_ebay2");
            return "ok";
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }

    // 2. Guardar Fechas en el Lote
    public static function mdlActualizarFechasLote($id, $fecha_inicio, $fecha_fin) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("UPDATE inventory_batches SET fecha_inicio = :inicio, fecha_fin = :fin WHERE id = :id");
        $stmt->execute(['inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'id' => $id]);
        return "ok";
    }

    // 3. Inserción Masiva Genérica (Sirve para las 4 tablas ya que tienen misma estructura)
    public static function mdlInsertarDatos($tabla, $datos) {
        $conexion = Conexion::conectar();
        try {
            $conexion->beginTransaction();
            $stmt = $conexion->prepare("INSERT INTO $tabla (sku, title, inventory) VALUES (:sku, :title, :inventory)");
            foreach ($datos as $fila) {
                $stmt->execute([
                    'sku' => $fila['sku'], 
                    'title' => $fila['title'], 
                    'inventory' => $fila['inventory']
                ]);
            }
            $conexion->commit();
            return "ok";
        } catch (PDOException $e) {
            $conexion->rollBack();
            return "error: " . $e->getMessage();
        }
    }

    // 4. Vista Previa y Conteos
    public static function mdlObtenerVistaPrevia($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT * FROM $tabla LIMIT 5");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function mdlContarRegistros($tabla) {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM $tabla");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>