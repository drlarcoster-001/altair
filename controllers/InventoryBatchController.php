<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: controllers/InventoryBatchController.php
 * Proposito: Gestionar la carga de lotes de inventario, aplicando un renombrado estándar (prefijos con guion bajo) para los 4 archivos de Snapshots.
 * Version: 1.0.4 - Se estandariza el prefijo de Shopify 1 a 'shopify_1_' para mantener la consistencia visual y técnica con el resto de los archivos.
 */

if(!class_exists('InventoryBatchModel')){
    require_once __DIR__ . "/../models/InventoryBatchModel.php";
}

class InventoryBatchController {
    
    public static function ctrObtenerUltimaCarga() {
        return InventoryBatchModel::mdlObtenerUltimaCarga("inventory_batches");
    }
}

/* =======================================================
    RECEPTOR DE PETICIONES AJAX
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // --- ACCIÓN: SUBIR ARCHIVOS ---
    if ($_POST['action'] == 'subir_archivos') {
        $batch_name = trim($_POST['batch_name']);
        
        // Limpiamos el nombre para la carpeta y agregamos prefijo inv- 
        $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $batch_name);
        $folder_name = "inv-" . $clean_name; 
        
        // Cambio de ruta: De C:\TEMP a carpeta /uploads relativa al servidor
        $base_dir = __DIR__ . "/../uploads";
        $target_dir = $base_dir . "/" . $folder_name;

        // Creación de directorios con permisos estándar 0755
        if (!file_exists($base_dir)) { mkdir($base_dir, 0755, true); }
        if (!file_exists($target_dir)) { mkdir($target_dir, 0755, true); }

        /**
         * RENOMBRADO ESTÁNDAR DE ARCHIVOS:
         * Se aplica el formato [proveedor]_[versión]_ para todos los archivos.
         */
        $path_shopify1 = $target_dir . "/shopify_1_" . basename($_FILES["file_shopify1"]["name"]);
        $path_ebay1    = $target_dir . "/ebay_1_" . basename($_FILES["file_ebay1"]["name"]);
        $path_shopify2 = $target_dir . "/shopify_2_" . basename($_FILES["file_shopify2"]["name"]);
        $path_ebay2    = $target_dir . "/ebay_2_" . basename($_FILES["file_ebay2"]["name"]);

        $move1 = move_uploaded_file($_FILES["file_shopify1"]["tmp_name"], $path_shopify1);
        $move2 = move_uploaded_file($_FILES["file_ebay1"]["tmp_name"], $path_ebay1);
        $move3 = move_uploaded_file($_FILES["file_shopify2"]["tmp_name"], $path_shopify2);
        $move4 = move_uploaded_file($_FILES["file_ebay2"]["tmp_name"], $path_ebay2);

        if ($move1 && $move2 && $move3 && $move4) {
            $datos = array(
                "batch_name" => $batch_name,
                "shopify1"   => $path_shopify1,
                "ebay1"      => $path_ebay1,
                "shopify2"   => $path_shopify2,
                "ebay2"      => $path_ebay2
            );

            // Registro en tabla 'inventory_batches' [cite: 1]
            $respuesta = InventoryBatchModel::mdlRegistrarLote("inventory_batches", $datos);
            
            if ($respuesta == "ok") {
                echo json_encode(["status" => "success", "message" => "Archivos estandarizados y guardados en servidor."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al registrar en la base de datos."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al mover los archivos a /uploads. Verifique permisos de escritura."]);
        }
        exit;
    }

    // --- ACCIÓN: ELIMINAR CARGA ---
    if ($_POST['action'] == 'eliminar_carga') {
        $id = $_POST['id'];
        $carga = InventoryBatchModel::mdlObtenerCargaPorId("inventory_batches", $id);
        
        if ($carga) {
            // Eliminación de archivos físicos [cite: 1]
            if (file_exists($carga['shopify1_file_path'])) unlink($carga['shopify1_file_path']);
            if (file_exists($carga['ebay1_file_path'])) unlink($carga['ebay1_file_path']);
            if (file_exists($carga['shopify2_file_path'])) unlink($carga['shopify2_file_path']);
            if (file_exists($carga['ebay2_file_path'])) unlink($carga['ebay2_file_path']);

            $folder = dirname($carga['shopify1_file_path']);
            if (is_dir($folder)) {
                $content = array_diff(scandir($folder), array('.', '..'));
                if (empty($content)) { rmdir($folder); }
            }

            $respuesta = InventoryBatchModel::mdlEliminarLote("inventory_batches", $id);
            if ($respuesta == "ok") {
                echo json_encode(["status" => "success", "message" => "Registro y archivos físicos eliminados."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al borrar en la base de datos."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "El registro no existe en el sistema."]);
        }
        exit;
    }
}