<?php
/**
 * Modulo: Gestión de Incidencias / Controlador
 * Archivo: /controllers/IncidentProcessController.php
 * Proposito: Leer los CSV, mapear columnas, y gestionar el vaciado de tablas.
 * Version: 0.0.2 - Se añade acción AJAX para limpiar las tablas.
 */

if(!class_exists('IncidentProcessModel')) require_once __DIR__ . "/../models/IncidentProcessModel.php";
if(!class_exists('IncidentBatchModel')) require_once __DIR__ . "/../models/IncidentBatchModel.php";

class IncidentProcessController {
    
    public static function ctrObtenerConteo($tabla) {
        return IncidentProcessModel::mdlContarRegistros($tabla);
    }

    public static function ctrObtenerPrevia($tabla) {
        return IncidentProcessModel::mdlObtenerVistaPrevia($tabla);
    }
}

/* =======================================================
   RECEPTOR AJAX (Desde JS) PARA PROCESAR Y LIMPIAR
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // --- ACCIÓN: PROCESAR ARCHIVOS ---
    if ($_POST['action'] == 'ejecutar_procesamiento') {
        $carga = IncidentBatchModel::mdlObtenerUltimaCarga("incident_batches");
        
        if (!$carga) {
            echo json_encode(["status" => "error", "message" => "No hay archivos cargados para procesar."]);
            exit;
        }

        $fileShopify = $carga['shopify_file_path'];
        $fileCed = $carga['cedcommerce_file_path'];
        $fileEbay = $carga['ebay_file_path'];

        if (!file_exists($fileShopify) || !file_exists($fileCed) || !file_exists($fileEbay)) {
            echo json_encode(["status" => "error", "message" => "Los archivos físicos no se encuentran en C:\\TEMP."]);
            exit;
        }

        IncidentProcessModel::mdlVaciarTablas();

        function procesarCSV($ruta, $tipo) {
            $datos = [];
            if (($handle = fopen($ruta, "r")) !== FALSE) {
                $row = 0;
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $row++;
                    if ($row == 1) continue; 
                    
                    if ($tipo == 'shopify') {
                        $datos[] = ['sku' => isset($data[8]) ? trim($data[8]) : '', 'title' => isset($data[1]) ? trim($data[1]) : '', 'inventory' => isset($data[11]) ? (int)$data[11] : 0];
                    } elseif ($tipo == 'ced') {
                        $datos[] = ['sku' => isset($data[11]) ? trim($data[11]) : '', 'title' => isset($data[1]) ? trim($data[1]) : '', 'inventory' => isset($data[9]) ? (int)$data[9] : 0, 'profile' => isset($data[14]) ? trim($data[14]) : ''];
                    } elseif ($tipo == 'ebay') {
                        $datos[] = ['sku' => isset($data[3]) ? trim($data[3]) : '', 'title' => isset($data[1]) ? trim($data[1]) : '', 'inventory' => isset($data[4]) ? (int)$data[4] : 0];
                    }
                }
                fclose($handle);
            }
            return $datos;
        }

        IncidentProcessModel::mdlInsertarShopify(procesarCSV($fileShopify, 'shopify'));
        IncidentProcessModel::mdlInsertarCed(procesarCSV($fileCed, 'ced'));
        IncidentProcessModel::mdlInsertarEbay(procesarCSV($fileEbay, 'ebay'));

        echo json_encode(["status" => "success", "message" => "Proceso culminado"]);
        exit;
    }

    // --- ACCIÓN: LIMPIAR TABLAS ---
    if ($_POST['action'] == 'limpiar_tablas') {
        $respuesta = IncidentProcessModel::mdlVaciarTablas();
        if ($respuesta == "ok") {
            echo json_encode(["status" => "success", "message" => "Tablas de procesamiento limpiadas correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al vaciar las tablas en la base de datos."]);
        }
        exit;
    }
}
?>