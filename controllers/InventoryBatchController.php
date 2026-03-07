<?php
/**
 * Modulo: Análisis de Inventario / Controlador
 * Archivo: /controllers/InventoryBatchController.php
 * Proposito: Recibir 4 archivos por AJAX, crear carpetas con prefijo 'inv-' y guardarlos.
 * Version: 0.0.1 - Adaptado para 4 archivos y prefijo de carpeta.
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
        
        // Limpiamos el nombre y le agregamos el prefijo inv-
        $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $batch_name);
        $folder_name = "inv-" . $clean_name; 
        
        $base_dir = "C:\\TEMP";
        $target_dir = $base_dir . "\\" . $folder_name;

        if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $path_shopify1 = $target_dir . "\\" . basename($_FILES["file_shopify1"]["name"]);
        $path_ebay1 = $target_dir . "\\" . basename($_FILES["file_ebay1"]["name"]);
        $path_shopify2 = $target_dir . "\\" . basename($_FILES["file_shopify2"]["name"]);
        $path_ebay2 = $target_dir . "\\" . basename($_FILES["file_ebay2"]["name"]);

        $move1 = move_uploaded_file($_FILES["file_shopify1"]["tmp_name"], $path_shopify1);
        $move2 = move_uploaded_file($_FILES["file_ebay1"]["tmp_name"], $path_ebay1);
        $move3 = move_uploaded_file($_FILES["file_shopify2"]["tmp_name"], $path_shopify2);
        $move4 = move_uploaded_file($_FILES["file_ebay2"]["tmp_name"], $path_ebay2);

        if ($move1 && $move2 && $move3 && $move4) {
            $datos = array(
                "batch_name" => $batch_name,
                "shopify1" => $path_shopify1,
                "ebay1" => $path_ebay1,
                "shopify2" => $path_shopify2,
                "ebay2" => $path_ebay2
            );

            $respuesta = InventoryBatchModel::mdlRegistrarLote("inventory_batches", $datos);
            if ($respuesta == "ok") {
                echo json_encode(["status" => "success", "message" => "Archivos guardados en $target_dir."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al guardar en la BD."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al mover los archivos a C:\\TEMP."]);
        }
        exit;
    }

    // --- ACCIÓN: ELIMINAR CARGA ---
    if ($_POST['action'] == 'eliminar_carga') {
        $id = $_POST['id'];
        $carga = InventoryBatchModel::mdlObtenerCargaPorId("inventory_batches", $id);
        
        if ($carga) {
            if (file_exists($carga['shopify1_file_path'])) unlink($carga['shopify1_file_path']);
            if (file_exists($carga['ebay1_file_path'])) unlink($carga['ebay1_file_path']);
            if (file_exists($carga['shopify2_file_path'])) unlink($carga['shopify2_file_path']);
            if (file_exists($carga['ebay2_file_path'])) unlink($carga['ebay2_file_path']);

            $folder = dirname($carga['shopify1_file_path']);
            if (is_dir($folder)) rmdir($folder);

            $respuesta = InventoryBatchModel::mdlEliminarLote("inventory_batches", $id);
            
            if ($respuesta == "ok") {
                echo json_encode(["status" => "success", "message" => "Registro, archivos y carpeta eliminados."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error al borrar en BD."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "El registro no existe."]);
        }
        exit;
    }
}
?>