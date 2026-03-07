<?php
/**
 * Modulo: Gestión de Incidencias / Controlador
 * Archivo: /controllers/IncidentReportController.php
 * Proposito: Intermediario para enviar los datos procesados a la vista de reportes vía AJAX.
 * Version: 0.0.2 - Se agrega receptor POST para generar el reporte de forma asíncrona.
 */

if(!class_exists('IncidentReportModel')){
    require_once __DIR__ . "/../models/IncidentReportModel.php";
}

class IncidentReportController {
    public static function ctrObtenerFaltantesCed() {
        return IncidentReportModel::mdlFaltantesCed();
    }
    public static function ctrObtenerFaltantesEbay() {
        return IncidentReportModel::mdlFaltantesEbay();
    }
}

/* =======================================================
   RECEPTOR AJAX (Desde JS) PARA GENERAR REPORTE
   ======================================================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'generar_reporte') {
    
    // Obtenemos los datos desde el modelo
    $faltantesCed = IncidentReportModel::mdlFaltantesCed();
    $faltantesEbay = IncidentReportModel::mdlFaltantesEbay();

    // Enviamos JSON al frontend
    echo json_encode([
        "status" => "success",
        "dataCed" => $faltantesCed,
        "dataEbay" => $faltantesEbay
    ]);
    exit;
}
?>