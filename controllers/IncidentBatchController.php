<?php
/**
 * Modulo: Gestión de Incidencias / Controlador
 * Archivo: /controllers/IncidentBatchController.php
 * Proposito: Intermediario para guardar, consultar y eliminar cargas y archivos físicos.
 * Version: 0.0.4 - Lógica para eliminar archivos, carpetas y registros de BD por AJAX.
 */

if(!class_exists('IncidentBatchModel')){
    require_once __DIR__ . "/../models/IncidentBatchModel.php";
}

class IncidentBatchController {
    public static function ctrObtenerUltimaCarga() {
        return IncidentBatchModel::mdlObtenerUltimaCarga("incident_batches");
    }
}

/* =======================================================
   RECEPTOR DE PETICIONES AJAX (Desde JS)
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // --- ACCIÓN: SUBIR ARCHIVOS ---
    if ($_POST['action'] == 'subir_archivos') {
        $batch_name = trim($_POST['batch_name']);
        $folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $batch_name);
        
        $base_dir = "C:\\TEMP";
        $target_dir = $base_dir . "\\" . $folder_name;

        if (!file_exists($base_dir)) { mkdir($base_dir, 0777, true); }
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $path_shopify = $target_dir . "\\" . basename($_FILES["file_shopify"]["name"]);
        $path_ced = $target_dir . "\\" . basename($_FILES["file_cedcommerce"]["name"]);
        $path_ebay = $target_dir . "\\" . basename($_FILES["file_ebay"]["name"]);

        $move1 = move_uploaded_file($_FILES["file_shopify"]["tmp_name"], $path_shopify);
        $move2 = move_uploaded_file($_FILES["file_cedcommerce"]["tmp_name"], $path_ced);
        $move3 = move_uploaded_file($_FILES["file_ebay"]["tmp_name"], $path_ebay);

        if ($move1 && $move2 && $move3) {
            $datos = array(
                "batch_name" => $batch_name,
                "shopify" => $path_shopify,
                "cedcommerce" => $path_ced,
                "ebay" => $path_ebay
            );

            $respuesta = IncidentBatchModel::mdlRegistrarLote("incident_batches", $datos);
            if ($respuesta == "ok") {
                echo json_encode(["status" => "success", "message" => "Archivos guardados correctamente."]);
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
        
        // 1. Buscamos la información para saber en qué carpeta están
        $carga = IncidentBatchModel::mdlObtenerCargaPorId("incident_batches", $id);
        
        if ($carga) {
            // 2. Borramos los 3 archivos físicos de C:\TEMP usando unlink()
            if (file_exists($carga['shopify_file_path'])) unlink($carga['shopify_file_path']);
            if (file_exists($carga['cedcommerce_file_path'])) unlink($carga['cedcommerce_file_path']);
            if (file_exists($carga['ebay_file_path'])) unlink($carga['ebay_file_path']);

            // 3. Borramos la carpeta que los contenía usando rmdir() (dirname obtiene la ruta sin el archivo)
            $folder = dirname($carga['shopify_file_path']);
            if (is_dir($folder)) rmdir($folder);

            // 4. Borramos el registro de la Base de Datos
            $respuesta = IncidentBatchModel::mdlEliminarLote("incident_batches", $id);
            
            if ($respuesta == "ok") {
                echo json_encode(["status" => "success", "message" => "Registro, archivos y carpeta eliminados correctamente."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Archivos borrados, pero hubo error en BD."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "El registro no existe."]);
        }
        exit;
    }
}
?>