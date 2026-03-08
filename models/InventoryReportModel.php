<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: models/InventoryReportModel.php
 * Proposito: Consultas a tbl_discrepancias con mapeo de columnas.
 * Version: 1.0.5
 */

if(!class_exists('Conexion')) { require_once __DIR__ . "/../config/db.php"; }

class InventoryReportModel {
    public static function mdlGenerarDiscrepancias() {
        try {
            $stmt = Conexion::conectar()->prepare("CALL sp_generar_reporte_discrepancias()");
            return $stmt->execute() ? "success" : "error";
        } catch (PDOException $e) { return "error"; }
    }

    public static function mdlObtenerReporteGrid($search, $limit, $offset) {
        $sql = "SELECT sku, inv_shopify1 as inv1, inv_ebay1 as ebay1, inv_shopify2 as inv2, inv_ebay2 as ebay2, disc_inv1, disc_inv2, estado, causa, prioridad 
                FROM tbl_discrepancias WHERE sku LIKE :s OR estado LIKE :s OR causa LIKE :s OR prioridad LIKE :s LIMIT :l OFFSET :o";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindValue(":s", "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(":l", (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(":o", (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function mdlLimpiarReporte() {
        try { Conexion::conectar()->exec("TRUNCATE TABLE tbl_discrepancias"); return "ok"; } catch (PDOException $e) { return "error"; }
    }
}