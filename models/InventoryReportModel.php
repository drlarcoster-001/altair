<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: models/InventoryReportModel.php
 * Proposito: Gestionar la obtención de datos para reportes. Incluye cálculos de porcentajes para indicadores y filtros específicos por estado para el reporte gerencial.
 * Version: 1.1.0 - Inclusión de porcentajes y filtros de alerta para Word.
 */

if(!class_exists('Conexion')) {
    require_once __DIR__ . "/../config/db.php";
}

class InventoryReportModel {

    public static function mdlGenerarDiscrepancias() {
        try {
            $stmt = Conexion::conectar()->prepare("CALL sp_generar_reporte_discrepancias()");
            return $stmt->execute() ? "success" : "error";
        } catch (PDOException $e) {
            return "error";
        }
    }

    public static function mdlObtenerReporteGrid($filtros, $limit, $offset) {
        $where = "WHERE 1=1";
        if(!empty($filtros['sku']))       { $where .= " AND sku LIKE :sku"; }
        if(!empty($filtros['estado']))    { $where .= " AND estado = :estado"; }
        if(!empty($filtros['causa']))     { $where .= " AND causa = :causa"; }
        if(!empty($filtros['prioridad'])) { $where .= " AND prioridad = :prioridad"; }

        $sql = "SELECT sku, inv_shopify1 as inv1, inv_ebay1 as ebay1, inv_shopify2 as inv2, inv_ebay2 as ebay2, 
                       disc_inv1, disc_inv2, estado, causa, prioridad 
                FROM tbl_discrepancias $where LIMIT :l OFFSET :o";
        
        $stmt = Conexion::conectar()->prepare($sql);
        if(!empty($filtros['sku']))       { $stmt->bindValue(":sku", "%".$filtros['sku']."%", PDO::PARAM_STR); }
        if(!empty($filtros['estado']))    { $stmt->bindValue(":estado", $filtros['estado'], PDO::PARAM_STR); }
        if(!empty($filtros['causa']))     { $stmt->bindValue(":causa", $filtros['causa'], PDO::PARAM_STR); }
        if(!empty($filtros['prioridad'])) { $stmt->bindValue(":prioridad", $filtros['prioridad'], PDO::PARAM_STR); }
        
        $stmt->bindValue(":l", (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(":o", (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function mdlObtenerIndicadores() {
        $stmt = Conexion::conectar()->prepare("
            SELECT estado, COUNT(*) as cantidad, 
            ROUND((COUNT(*) * 100 / (SELECT COUNT(*) FROM tbl_discrepancias)), 2) as porcentaje 
            FROM tbl_discrepancias GROUP BY estado
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function mdlObtenerPorEstado($estado) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM tbl_discrepancias WHERE estado = :estado");
        $stmt->bindValue(":estado", $estado, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function mdlLimpiarReporte() {
        try {
            Conexion::conectar()->exec("TRUNCATE TABLE tbl_discrepancias");
            return "ok";
        } catch (PDOException $e) {
            return "error";
        }
    }
}