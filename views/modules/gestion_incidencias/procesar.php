<?php
/**
 * Modulo: Gestión de Incidencias
 * Archivo: /views/modules/gestion_incidencias/procesar.php
 * Proposito: Formulario de procesamiento y botón de limpiar tablas.
 * Version: 0.0.4 - Se añade botón 'Limpiar' junto a 'Procesar'.
 */

require_once "controllers/IncidentBatchController.php";
require_once "controllers/IncidentProcessController.php";

$ultimaCarga = IncidentBatchController::ctrObtenerUltimaCarga();

$archivosFisicosExisten = false;
if ($ultimaCarga) {
    $archivosFisicosExisten = (
        file_exists($ultimaCarga['shopify_file_path']) && 
        file_exists($ultimaCarga['cedcommerce_file_path']) && 
        file_exists($ultimaCarga['ebay_file_path'])
    );
}

$totalShopify = IncidentProcessController::ctrObtenerConteo('tbl_gestion_shopify');
$totalCed = IncidentProcessController::ctrObtenerConteo('tbl_gestion_ced');
$totalEbay = IncidentProcessController::ctrObtenerConteo('tbl_gestion_ebay');

$hayProcesados = ($totalShopify > 0);
?>

<div class="container-fluid">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white fw-bold">
            <h5 class="mb-0">⚙️ Archivos Listos para Procesar</h5>
        </div>
        <div class="card-body">
            <?php if($ultimaCarga): ?>
                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-1"><strong>Lote de Carga:</strong> <?php echo $ultimaCarga['batch_name']; ?></p>
                        <p class="mb-1"><strong>Fecha:</strong> <?php echo date("d/m/Y H:i", strtotime($ultimaCarga['created_at'])); ?></p>
                    </div>
                </div>

                <?php if(!$archivosFisicosExisten): ?>
                    <div class="alert alert-danger mt-3 mb-0 border-0 shadow-sm">
                        <strong>⚠️ Archivos Físicos Extraviados:</strong> El registro existe en la base de datos, pero los archivos no se encuentran en la carpeta <code>C:\TEMP</code>. No se puede procesar. Por favor, elimina esta carga y vuelve a subir los archivos.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-warning mb-0 border-0 shadow-sm">
                    <strong>ℹ️ Sin carga activa:</strong> No hay archivos cargados actualmente. Ve a "Cargar Archivos" primero.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <a href="index.php?route=incidencias&action=index" class="btn btn-secondary">← Volver</a>
        
        <?php if($ultimaCarga && $archivosFisicosExisten): ?>
            <button type="button" class="btn btn-warning fw-bold text-dark px-4" onclick="iniciarProcesamiento()">
                ▶️ Procesar
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-secondary px-4" disabled>▶️ Procesar</button>
        <?php endif; ?>

        <?php if($hayProcesados): ?>
            <button type="button" class="btn btn-danger fw-bold text-white px-4" onclick="limpiarTablas()">
                🗑️ Limpiar
            </button>
        <?php endif; ?>

        <?php if($hayProcesados): ?>
            <a href="index.php?route=incidencias&action=reportes" class="btn btn-info fw-bold text-dark px-4 ms-auto">
                👁️ Ver Reportes
            </a>
        <?php else: ?>
            <button type="button" class="btn btn-secondary px-4 ms-auto" disabled>👁️ Ver Reportes</button>
        <?php endif; ?>
    </div>

    <?php if($hayProcesados): ?>
        <h4 class="mb-3 text-secondary border-bottom pb-2">Vista Previa de Tablas Procesadas</h4>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-success shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-success mb-0 fw-bold">Shopify</h5>
                            <small class="text-muted"><?php echo number_format($totalShopify); ?> registros</small>
                        </div>
                        <button class="btn btn-outline-success rounded-circle" data-bs-toggle="modal" data-bs-target="#modalPrevShopify">👁️</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-primary mb-0 fw-bold">Ced Commerce</h5>
                            <small class="text-muted"><?php echo number_format($totalCed); ?> registros</small>
                        </div>
                        <button class="btn btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#modalPrevCed">👁️</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-danger shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-danger mb-0 fw-bold">eBay</h5>
                            <small class="text-muted"><?php echo number_format($totalEbay); ?> registros</small>
                        </div>
                        <button class="btn btn-outline-danger rounded-circle" data-bs-toggle="modal" data-bs-target="#modalPrevEbay">👁️</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php if($hayProcesados): ?>
    <div class="modal fade" id="modalPrevShopify" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Previsualización: tbl_gestion_shopify</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-dark"><tr><th>ID</th><th>SKU</th><th>Title</th><th>Inventory</th></tr></thead>
                        <tbody>
                            <?php 
                            $prevShopify = IncidentProcessController::ctrObtenerPrevia('tbl_gestion_shopify');
                            foreach($prevShopify as $row) { echo "<tr><td>{$row['id']}</td><td>{$row['sku']}</td><td>{$row['title']}</td><td>{$row['inventory']}</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPrevCed" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Previsualización: tbl_gestion_ced</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-dark"><tr><th>ID</th><th>SKU</th><th>Title</th><th>Inventory</th><th>Profile</th></tr></thead>
                        <tbody>
                            <?php 
                            $prevCed = IncidentProcessController::ctrObtenerPrevia('tbl_gestion_ced');
                            foreach($prevCed as $row) { echo "<tr><td>{$row['id']}</td><td>{$row['sku']}</td><td>{$row['title']}</td><td>{$row['inventory']}</td><td>{$row['profile']}</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPrevEbay" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Previsualización: tbl_gestion_ebay</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-dark"><tr><th>ID</th><th>SKU</th><th>Title</th><th>Inventory</th></tr></thead>
                        <tbody>
                            <?php 
                            $prevEbay = IncidentProcessController::ctrObtenerPrevia('tbl_gestion_ebay');
                            foreach($prevEbay as $row) { echo "<tr><td>{$row['id']}</td><td>{$row['sku']}</td><td>{$row['title']}</td><td>{$row['inventory']}</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>