<?php
/**
 * Modulo: Análisis de Inventario
 * archivo: controllers/InventoryReportController.php
 * Proposito: Controlar la secuencia lógica de generación del reporte: limpieza, creación de base de SKUs y cruce de datos Shopify 1.
 * Version: 1.1.3 - Orquestación secuencial de limpieza y actualización de la primera columna de inventario.
 */

require_once __DIR__ . "/../models/InventoryReportModel.php";
require_once __DIR__ . "/../models/InventoryBatchModel.php";

if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'generar_reporte') {
    
    try {
        // 0. Obtener fechas del lote para el maestro
        $lote = InventoryBatchModel::mdlObtenerUltimaCarga("inventory_batches");
        $fi = $lote ? $lote['fecha_inicio'] : null;
        $ff = $lote ? $lote['fecha_fin'] : null;

        // 1. Borrar tabla cada vez que se presiona el botón
        InventoryReportModel::mdlLimpiarTabla();

        // 2. Crear el maestro de SKUs (Shopify 1 UNION Shopify 2)
        InventoryReportModel::mdlCrearMaestroSKU($fi, $ff);

        // 3. Realizar el Update (Buscarv) para Shopify 1
        InventoryReportModel::mdlActualizarShopify1();

        echo json_encode([
            "status" => "success", 
            "message" => "Tabla reiniciada, maestro de SKUs creado y columna Shopify 1 actualizada."
        ]);

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}