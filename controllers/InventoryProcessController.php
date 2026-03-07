<?php
/**
 * Modulo: Análisis de Inventario / Controlador
 * Archivo: /controllers/InventoryProcessController.php
 * Proposito: Leer 4 CSV, mapear columnas Shopify/eBay y enviarlas a BD.
 * Version: 0.0.1 - Parseo con actualización de fechas.
 */

if(!class_exists('InventoryProcessModel')) require_once __DIR__ . "/../models/InventoryProcessModel.php";
if(!class_exists('InventoryBatchModel')) require_once __DIR__ . "/../models/InventoryBatchModel.php";

class InventoryProcessController {
    public static function ctrObtenerConteo($tabla) { return InventoryProcessModel::mdlContarRegistros($tabla); }
    public static function ctrObtenerPrevia($tabla) { return InventoryProcessModel::mdlObtenerVistaPrevia($tabla); }
}

/* =======================================================
   RECEPTOR AJAX (Desde JS)
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // --- ACCIÓN: PROCESAR ARCHIVOS ---
    if ($_POST['action'] == 'ejecutar_procesamiento') {
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        $carga = InventoryBatchModel::mdlObtenerUltimaCarga("inventory_batches");
        if (!$carga) {
            echo json_encode(["status" => "error", "message" => "No hay archivos cargados."]); exit;
        }

        $fShop1 = $carga['shopify1_file_path'];
        $fEbay1 = $carga['ebay1_file_path'];
        $fShop2 = $carga['shopify2_file_path'];
        $fEbay2 = $carga['ebay2_file_path'];

        if (!file_exists($fShop1) || !file_exists($fEbay1) || !file_exists($fShop2) || !file_exists($fEbay2)) {
            echo json_encode(["status" => "error", "message" => "Faltan archivos físicos en C:\\TEMP."]); exit;
        }

        // 1. Vaciar Tablas y Actualizar Fechas
        InventoryProcessModel::mdlVaciarTablas();
        InventoryProcessModel::mdlActualizarFechasLote($carga['id'], $fecha_inicio, $fecha_fin);

        // 2. Función Lector CSV
        function procesarCSV($ruta, $tipo) {
            $datos = [];
            if (($handle = fopen($ruta, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $row++;
                    if ($row == 1) continue; 
                    
                    if ($tipo == 'shopify') {
                        // Shopify: SKU(I=8), Title(B=1), Inventory(L=11)
                        $datos[] = ['sku' => isset($data[8]) ? trim($data[8]) : '', 'title' => isset($data[1]) ? trim($data[1]) : '', 'inventory' => isset($data[11]) ? (int)$data[11] : 0];
                    } elseif ($tipo == 'ebay') {
                        // eBay: SKU(D=3), Title(B=1), Inventory(E=4)
                        $datos[] = ['sku' => isset($data[3]) ? trim($data[3]) : '', 'title' => isset($data[1]) ? trim($data[1]) : '', 'inventory' => isset($data[4]) ? (int)$data[4] : 0];
                    }
                }
                fclose($handle);
            }
            return $datos;
        }

        // 3. Ejecutar Inserciones
        InventoryProcessModel::mdlInsertarDatos('tbl_shopify1', procesarCSV($fShop1, 'shopify'));
        InventoryProcessModel::mdlInsertarDatos('tbl_ebay1', procesarCSV($fEbay1, 'ebay'));
        InventoryProcessModel::mdlInsertarDatos('tbl_shopify2', procesarCSV($fShop2, 'shopify'));
        InventoryProcessModel::mdlInsertarDatos('tbl_ebay2', procesarCSV($fEbay2, 'ebay'));

        echo json_encode(["status" => "success", "message" => "Proceso culminado"]);
        exit;
    }

    // --- ACCIÓN: LIMPIAR TABLAS ---
    if ($_POST['action'] == 'limpiar_tablas') {
        $respuesta = InventoryProcessModel::mdlVaciarTablas();
        if ($respuesta == "ok") echo json_encode(["status" => "success", "message" => "Tablas limpiadas correctamente."]);
        else echo json_encode(["status" => "error", "message" => "Error al vaciar tablas."]);
        exit;
    }
}
?>