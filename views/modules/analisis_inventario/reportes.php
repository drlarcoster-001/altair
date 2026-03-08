<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: views/modules/analisis_inventario/reportes.php
 * Proposito: Interfaz de reporte de discrepancias con 4 filtros específicos y botones de acción (Generar, Limpiar, Refrescar y Exportar).
 * Version: 1.1.3 - Restauración de períodos y filtros por listas.
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
                    <p class="text-muted mb-0">
                        Auditoría: <strong><?php echo $ultimaCarga['fecha_inicio'] ?? '---'; ?></strong> al <strong><?php echo $ultimaCarga['fecha_fin'] ?? '---'; ?></strong>
                        <span class="ms-2 badge bg-light text-dark border">Lote: <?php echo $ultimaCarga['batch_name'] ?? 'N/A'; ?></span>
                    </p>
                </div>
                <div class="col-md-7 text-end">
                    <button class="btn btn-primary px-3 shadow-sm" onclick="generarReporteDiscrepancias()">📊 Generar</button>
                    <button class="btn btn-info text-white px-3 shadow-sm" onclick="actualizarGridReporte()">🔄 Refrescar</button>
                    <button class="btn btn-danger px-3 shadow-sm" onclick="limpiarReporte()">🗑️ Eliminar</button>
                    
                    <div class="btn-group">
                        <button class="btn btn-dark dropdown-toggle px-3 shadow-sm" id="btnExportar" data-bs-toggle="dropdown" disabled>📥 Exportar</button>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportarReporte('excel')"><i class="bi bi-file-earmark-excel-fill text-success me-2"></i> Excel (SKU Texto)</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportarReporte('word')"><i class="bi bi-file-earmark-word-fill text-primary me-2"></i> Word Gerencial</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body py-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small fw-bold text-secondary">SKU</label>
                    <input type="text" id="f_sku" class="form-control form-control-sm" placeholder="Escriba SKU..." onkeyup="filtrarGridReporte()">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Estado</label>
                    <select id="f_estado" class="form-select form-select-sm" onchange="filtrarGridReporte()">
                        <option value="">-- Todos --</option>
                        <option value="OK">OK</option>
                        <option value="EN TRANSITO">EN TRANSITO</option>
                        <option value="RIESGO OVERSELL">RIESGO OVERSELL</option>
                        <option value="DESINCRONIZADO">DESINCRONIZADO</option>
                        <option value="REPOSICION">REPOSICION</option>
                        <option value="VENTA">VENTA</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Causa</label>
                    <select id="f_causa" class="form-select form-select-sm" onchange="filtrarGridReporte()">
                        <option value="">-- Todas --</option>
                        <option value="Estatico">Estatico</option>
                        <option value="Montaje">Montaje</option>
                        <option value="Desfase">Desfase</option>
                        <option value="App">App</option>
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold text-secondary">Prioridad</label>
                    <select id="f_prioridad" class="form-select form-select-sm" onchange="filtrarGridReporte()">
                        <option value="">-- Todas --</option>
                        <option value="ALTA">ALTA</option>
                        <option value="MEDIA">MEDIA</option>
                        <option value="BAJA">BAJA</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <label class="small fw-bold text-secondary">Registros</label>
                    <select id="paginacion_reporte" class="form-select form-select-sm" onchange="cambiarPaginacion()">
                        <option value="25">25</option><option value="50">50</option>
                        <option value="100">100</option><option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                </div>
            </div>
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
                    <th class="text-center">Estado</th><th class="text-center">Causa</th>
                    <th class="text-center pe-3">Prioridad</th>
                </tr>
            </thead>
            <tbody id="tbody_reporte">
                </tbody>
        </table>
    </div>
</div>