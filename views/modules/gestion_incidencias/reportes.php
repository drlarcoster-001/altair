<?php
/**
 * Modulo: Gestión de Incidencias
 * Archivo: /views/modules/gestion_incidencias/reportes.php
 * Proposito: Visualizar cruces de datos con validación estricta de tablas vacías.
 * Version: 0.0.6 - Bloqueo del botón generar si no hay datos procesados.
 */

// Necesitamos el controlador de procesos para saber si hay datos en las tablas
require_once "controllers/IncidentProcessController.php";

$totalShopify = IncidentProcessController::ctrObtenerConteo('tbl_gestion_shopify');
$hayProcesados = ($totalShopify > 0);
?>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div class="d-flex align-items-center gap-3">
            <h4 class="text-info fw-bold mb-0">📊 Reporte de Incidencias</h4>
            
            <?php if($hayProcesados): ?>
                <button class="btn btn-warning fw-bold text-dark" id="btnGenerar" onclick="generarReporte()">
                    ⚙️ Generar Reporte
                </button>
            <?php else: ?>
                <button class="btn btn-secondary fw-bold text-white" id="btnGenerar" onclick="Swal.fire('Atención', 'No hay datos procesados. Por favor, ve a la pantalla de Procesar Archivos y ejecuta el proceso primero.', 'warning')">
                    ⚙️ Generar Reporte
                </button>
            <?php endif; ?>
        </div>
        
        <div class="d-flex gap-2">
            <div class="dropdown d-none" id="botoneraExportar">
                <button class="btn btn-success dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    📥 Exportar
                </button>
                <ul class="dropdown-menu shadow-sm">
                    <li><button class="dropdown-item" type="button" onclick="exportarExcel()">🟢 A Excel (.xlsx)</button></li>
                    <li><button class="dropdown-item" type="button" onclick="exportarWord()">🔵 A Word (.doc)</button></li>
                </ul>
            </div>
            
            <a href="index.php?route=incidencias&action=procesar" class="btn btn-secondary">← Volver a Procesar</a>
        </div>
    </div>

    <div id="reporteContenedor" class="d-none">
        
        <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-primary" id="ced-tab" data-bs-toggle="tab" data-bs-target="#ced-content" type="button" role="tab">
                    En Shopify, NO en Ced Commerce
                    <span class="badge bg-primary ms-2" id="badge-ced">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-danger" id="ebay-tab" data-bs-toggle="tab" data-bs-target="#ebay-content" type="button" role="tab">
                    En Shopify, NO en eBay
                    <span class="badge bg-danger ms-2" id="badge-ebay">0</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            
            <div class="tab-pane fade show active" id="ced-content" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted">Mostrando productos cuyo inventario en Shopify es mayor a cero (0) pero su SKU no existe en la carga de Ced Commerce.</p>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-striped table-bordered align-middle">
                                <thead class="table-primary" style="position: sticky; top: 0; z-index: 1;">
                                    <tr><th>SKU</th><th>Title (Shopify)</th><th class="text-center">Inventario Actual</th></tr>
                                </thead>
                                <tbody id="tbody-ced"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="ebay-content" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <p class="text-muted">Mostrando productos cuyo inventario en Shopify es mayor a cero (0) pero su SKU no existe en la carga de eBay.</p>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-striped table-bordered align-middle">
                                <thead class="table-danger" style="position: sticky; top: 0; z-index: 1;">
                                    <tr><th>SKU</th><th>Title (Shopify)</th><th class="text-center">Inventario Actual</th></tr>
                                </thead>
                                <tbody id="tbody-ebay"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .gear-icon { font-size: 50px; display: inline-block; animation: spin 2s linear infinite; color: #ffc107; margin-bottom: 15px;}
</style>

<script src="assets/js/gestion_incidencias_reportes.js"></script>