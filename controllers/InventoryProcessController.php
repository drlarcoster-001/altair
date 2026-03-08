<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: controllers/InventoryProcessController.php
 * Proposito: Controlador de orquestación para el procesamiento de archivos. Captura fechas de auditoría, limpia las tablas de resultados, mapea columnas dinámicamente por nombre desde los archivos CSV (Shopify/eBay) y gestiona la exportación de reportes a Excel mediante peticiones GET.
 * Version: 1.0.3 - Implementación de mapeo dinámico por nombres de columna ("SKU", "Available quantity", etc.) y sincronización de persistencia de fechas en el lote maestro.
 */

if(!class_exists('InventoryProcessModel')) require_once __DIR__ . "/../models/InventoryProcessModel.php";
if(!class_exists('InventoryBatchModel')) require_once __DIR__ . "/../models/InventoryBatchModel.php";

class InventoryProcessController {
    public static function ctrObtenerConteo($tabla) { return InventoryProcessModel::mdlContarRegistros($tabla); }
    public static function ctrObtenerPrevia($tabla) { return InventoryProcessModel::mdlObtenerVistaPrevia($tabla); }
}

/* =======================================================
    RECEPTOR DE PETICIONES (GET/POST)
   ======================================================= */

// --- MANEJO DE DESCARGAS EXCEL (GET) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] == 'exportar_excel') {
        $tabla = $_GET['tabla'] ?? '';
        $tablasPermitidas = ['tbl_shopify1', 'tbl_ebay1', 'tbl_shopify2', 'tbl_ebay2'];
        
        if (!in_array($tabla, $tablasPermitidas)) die("Acceso no autorizado.");

        $datos = InventoryProcessModel::mdlObtenerTodo($tabla);
        $filename = "Export_" . str_replace('tbl_', '', $tabla) . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel
        fputcsv($output, ['ID', 'SKU', 'Title', 'Inventory']);

        foreach ($datos as $f) {
            fputcsv($output, [$f['id'], $f['sku'], $f['title'], $f['inventory']]);
        }
        fclose($output);
        exit;
    }
}

// --- MANEJO DE PROCESAMIENTO (POST AJAX) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] == 'ejecutar_procesamiento') {
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        $carga = InventoryBatchModel::mdlObtenerUltimaCarga("inventory_batches");
        if (!$carga) {
            echo json_encode(["status" => "error", "message" => "No hay lote activo."]); exit;
        }

        // 1. Limpieza de tablas y persistencia de fechas en el lote 
        InventoryProcessModel::mdlVaciarTablas();
        InventoryProcessModel::mdlActualizarFechasLote($carga['id'], $fecha_inicio, $fecha_fin);

        /**
         * Lector de CSV con Mapeo por Nombre de Columna solicitado 
         */
        function procesarCSVConMapeo($ruta, $tipo) {
            $resultado = [];
            if (($handle = fopen($ruta, "r")) !== FALSE) {
                $headers = fgetcsv($handle, 10000, ",");
                if (!$headers) return [];

                // Definición de nombres exactos según tipo
                if ($tipo == 'shopify') {
                    $colSKU = "SKU"; $colTitle = "Title"; $colInv = "12405 Northwest 39th Avenue";
                } else {
                    $colSKU = "Custom label (SKU)"; $colTitle = "Title"; $colInv = "Available quantity";
                }

                $idxSKU = array_search($colSKU, $headers);
                $idxTitle = array_search($colTitle, $headers);
                $idxInv = array_search($colInv, $headers);

                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $resultado[] = [
                        'sku'       => ($idxSKU !== false && isset($data[$idxSKU])) ? trim($data[$idxSKU]) : '',
                        'title'     => ($idxTitle !== false && isset($data[$idxTitle])) ? trim($data[$idxTitle]) : '',
                        'inventory' => ($idxInv !== false && isset($data[$idxInv])) ? (int)$data[$idxInv] : 0
                    ];
                }
                fclose($handle);
            }
            return $resultado;
        }

        // 2. Ejecución secuencial de la importación
        try {
            InventoryProcessModel::mdlInsertarDatos('tbl_shopify1', procesarCSVConMapeo($carga['shopify1_file_path'], 'shopify'));
            InventoryProcessModel::mdlInsertarDatos('tbl_ebay1', procesarCSVConMapeo($carga['ebay1_file_path'], 'ebay'));
            InventoryProcessModel::mdlInsertarDatos('tbl_shopify2', procesarCSVConMapeo($carga['shopify2_file_path'], 'shopify'));
            InventoryProcessModel::mdlInsertarDatos('tbl_ebay2', procesarCSVConMapeo($carga['ebay2_file_path'], 'ebay'));

            echo json_encode(["status" => "success", "message" => "Los 4 archivos han sido cargados y procesados."]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] == 'limpiar_tablas') {
        $res = InventoryProcessModel::mdlVaciarTablas();
        echo json_encode(["status" => ($res == "ok" ? "success" : "error"), "message" => $res]);
        exit;
    }
}