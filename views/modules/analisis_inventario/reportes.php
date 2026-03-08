<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: views/modules/analisis_inventario/reportes.php
 * Proposito: Interfaz principal para el reporte de discrepancias con grid dinámica y filtros.
 * Version: 1.0.5 - Se corrige el orden de botones y la estructura de columnas.
 */

if(!class_exists('InventoryBatchController')){ require_once "controllers/InventoryBatchController.php"; }
$ultimaCarga = InventoryBatchController::ctrObtenerUltimaCarga();
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 class="fw-bold mb-0">Auditoría de Discrepancias</h3>
                    <p class="text-muted small mb-0">Lote: <strong><?php echo $ultimaCarga['batch_name'] ?? 'N/A'; ?></strong></p>
                </div>
                <div class="col-md-7 text-end">
                    <button class="btn btn-primary px-4 shadow-sm" onclick="generarReporteDiscrepancias()">📊 Generar Reporte</button>
                    <button class="btn btn-danger px-4 shadow-sm" onclick="limpiarReporte()">🗑️ Eliminar Reporte</button>
                    <div class="btn-group">
                        <button class="btn btn-dark dropdown-toggle px-4 shadow-sm" data-bs-toggle="dropdown">📥 Exportar</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportarReporte('excel')"><i class="bi bi-file-earmark-excel text-success me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportarReporte('word')"><i class="bi bi-file-earmark-word text-primary me-2"></i> Word</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-md-9">
            <input type="text" id="filtro_global" class="form-control" placeholder="🔍 Buscar por SKU, Estado, Causa o Prioridad..." onkeyup="filtrarGridReporte()">
        </div>
        <div class="col-md-3">
            <select id="paginacion_reporte" class="form-select" onchange="cambiarPaginacion()">
                <option value="25">25 registros</option>
                <option value="50">50 registros</option>
                <option value="100">100 registros</option>
                <option value="500">500 registros</option>
                <option value="1000">1000 registros</option>
            </select>
        </div>
    </div>

    <div class="table-responsive shadow-sm bg-white rounded">
        <table class="table table-hover mb-0 small">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3">SKU</th>
                    <th class="text-center">Inv 1</th><th class="text-center">Ebay 1</th>
                    <th class="text-center">Inv 2</th><th class="text-center">Ebay 2</th>
                    <th class="text-center bg-secondary">Disc. 1</th>
                    <th class="text-center bg-secondary">Disc. 2</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Causa</th>
                    <th class="text-center pe-3">Prioridad</th>
                </tr>
            </thead>
            <tbody id="tbody_reporte">
                <tr><td colspan="10" class="text-center py-5 text-muted">No hay datos. Presione "Generar Reporte".</td></tr>
            </tbody>
        </table>
    </div>
</div>