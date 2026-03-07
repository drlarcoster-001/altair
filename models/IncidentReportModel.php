<?php
/**
 * Modulo: Gestión de Incidencias / Modelo
 * Archivo: /models/IncidentReportModel.php
 * Proposito: Ejecutar las consultas SQL complejas para encontrar diferencias de inventario (Shopify vs Ced/eBay).
 * Version: 0.0.1 - Creación de left joins con validación de inventario > 0.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class IncidentReportModel {

    // 1. Productos en Shopify (Inv > 0) que NO están en Ced Commerce
    public static function mdlFaltantesCed() {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("
            SELECT s.sku, s.title, s.inventory 
            FROM tbl_gestion_shopify s 
            LEFT JOIN tbl_gestion_ced c ON s.sku = c.sku 
            WHERE s.inventory > 0 AND c.sku IS NULL
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Productos en Shopify (Inv > 0) que NO están en eBay
    public static function mdlFaltantesEbay() {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("
            SELECT s.sku, s.title, s.inventory 
            FROM tbl_gestion_shopify s 
            LEFT JOIN tbl_gestion_ebay e ON s.sku = e.sku 
            WHERE s.inventory > 0 AND e.sku IS NULL
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>