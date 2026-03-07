<?php
/**
 * Modulo: Análisis de Inventario
 * archivo: models/InventoryReportModel.php
 * Proposito: Gestionar la limpieza de datos, creación del maestro de SKUs y actualización modular de inventarios mediante UPDATE.
 * Version: 1.1.3 - Limpieza total, creación de universo de SKUs y proceso de actualización para Shopify 1.
 */

if(!class_exists('Conexion')) { require_once __DIR__ . "/../config/db.php"; }

class InventoryReportModel {

    public static function mdlLimpiarTabla() {
        $conexion = Conexion::conectar();
        return $conexion->exec("TRUNCATE TABLE tbl_discrepancias");
    }

    public static function mdlCrearMaestroSKU($fi, $ff) {
        $conexion = Conexion::conectar();
        // Insertamos el universo único de SKUs de ambas tablas de Shopify
        $sql = "INSERT INTO tbl_discrepancias (sku, fecha_inicio, fecha_fin) 
                SELECT sku, :fi, :ff FROM (
                    SELECT sku FROM tbl_shopify1
                    UNION 
                    SELECT sku FROM tbl_shopify2
                ) AS master";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":fi", $fi, PDO::PARAM_STR);
        $stmt->bindParam(":ff", $ff, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public static function mdlActualizarShopify1() {
        $conexion = Conexion::conectar();
        /**
         * Lógica de BUSCARV:
         * Actualiza inv_shopify1 buscando el SKU en tbl_shopify1.
         * Si el SKU no existe en la tabla de origen, COALESCE asigna 0.
         */
        $sql = "UPDATE tbl_discrepancias d 
                LEFT JOIN tbl_shopify1 s ON d.sku = s.sku 
                SET d.inv_shopify1 = COALESCE(s.inventory, 0)";
        return $conexion->exec($sql);
    }
}