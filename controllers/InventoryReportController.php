<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: controllers/InventoryReportController.php
 * Proposito: Controlador para reportes y exportación multiformato.
 * Version: 1.0.5
 */

if(!class_exists('InventoryReportModel')) { require_once __DIR__ . "/../models/InventoryReportModel.php"; }

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] == 'exportar_reporte') {
        $formato = $_GET['formato'];
        $search = $_GET['search'] ?? '';
        $datos = InventoryReportModel::mdlObtenerReporteGrid($search, 100000, 0);
        $filename = "ALTAIR_Discrepancias_" . date('Ymd_His');

        if ($formato == 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename . '.csv');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['SKU','Inv 1','Ebay 1','Inv 2','Ebay 2','Disc 1','Disc 2','Estado','Causa','Prioridad']);
            foreach ($datos as $f) { fputcsv($output, $f); }
            fclose($output);
        } elseif ($formato == 'word') {
            header("Content-type: application/vnd.ms-word");
            header("Content-Disposition: attachment; filename=" . $filename . ".doc");
            echo "<html><body><table border='1'><tr><th>SKU</th><th>Inv 1</th><th>Ebay 1</th><th>Inv 2</th><th>Ebay 2</th><th>Disc 1</th><th>Disc 2</th><th>Estado</th><th>Causa</th><th>Prioridad</th></tr>";
            foreach ($datos as $f) { echo "<tr><td>".implode("</td><td>", $f)."</td></tr>"; }
            echo "</table></body></html>";
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'generar_reporte') { echo json_encode(["status" => InventoryReportModel::mdlGenerarDiscrepancias()]); exit; }
    if ($_POST['action'] == 'listar_reporte') {
        $search = $_POST['search'] ?? ''; $limit = $_POST['limit'] ?? 25; $page = $_POST['page'] ?? 1;
        echo json_encode(InventoryReportModel::mdlObtenerReporteGrid($search, $limit, ($page - 1) * $limit)); exit;
    }
    if ($_POST['action'] == 'limpiar_reporte') { echo json_encode(["status" => InventoryReportModel::mdlLimpiarReporte()]); exit; }
}