<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: /views/modules/analisis_inventario/procesar.php
 * Proposito: Interfaz de procesamiento de los 4 archivos con validación de fechas.
 * Version: 0.0.1 - Inclusión de campos de fecha y 4 modales.
 */

require_once "controllers/InventoryBatchController.php";
require_once "controllers/InventoryProcessController.php";

$ultimaCarga = InventoryBatchController::ctrObtenerUltimaCarga();

$archivosFisicosExisten = false;
if ($ultimaCarga) {
    $archivosFisicosExisten = (
        file_exists($ultimaCarga['shopify1_file_path']) && 
        file_exists($ultimaCarga['ebay1_file_path']) && 
        file_exists($ultimaCarga['shopify2_file_path']) && 
        file_exists($ultimaCarga['ebay2_file_path'])
    );
}

$totShop1 = InventoryProcessController::ctrObtenerConteo('tbl_shopify1');
$totEbay1 = InventoryProcessController::ctrObtenerConteo('tbl_ebay1');
$totShop2 = InventoryProcessController::ctrObtenerConteo('tbl_shopify2');
$totEbay2 = InventoryProcessController::ctrObtenerConteo('tbl_ebay2');

$hayProcesados = ($totShop1 > 0);
?>

<div class="container-fluid">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white fw-bold">
            <h5 class="mb-0">⚙️ Archivos Listos para Procesar</h5>
        </div>
        <div class="card-body">
            <?php if($ultimaCarga): ?>
                
                <div class="row mb-3 border-bottom pb-3">
                    <div class="col-md-12">
                        <p class="mb-1"><strong>Lote de Carga:</strong> <?php echo $ultimaCarga['batch_name']; ?></p>
                        <p class="mb-1"><strong>Fecha de subida:</strong> <?php echo date("d/m/Y H:i", strtotime($ultimaCarga['created_at'])); ?></p>
                    </div>
                </div>

                <div class="row align-items-end mb-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Fecha Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_inicio" value="<?php echo $ultimaCarga['fecha_inicio'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-secondary">Fecha Fin <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_fin" value="<?php echo $ultimaCarga['fecha_fin'] ?? ''; ?>">
                    </div>
                </div>

                <?php if(!$archivosFisicosExisten): ?>
                    <div class="alert alert-danger mt-3 mb-0 border-0 shadow-sm">
                        <strong>⚠️ Archivos Extraviados:</strong> El registro existe, pero los 4 archivos no se encuentran en <code>C:\TEMP</code>.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-warning mb-0 border-0 shadow-sm">
                    <strong>ℹ️ Sin carga activa:</strong> Ve a "Cargar Archivos" primero.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <a href="index.php?route=inventario&action=index" class="btn btn-secondary">← Volver</a>
        
        <?php if($ultimaCarga && $archivosFisicosExisten): ?>
            <button type="button" class="btn btn-warning fw-bold text-dark px-4" onclick="iniciarProcesamiento()">▶️ Procesar</button>
        <?php else: ?>
            <button type="button" class="btn btn-secondary px-4" disabled>▶️ Procesar</button>
        <?php endif; ?>

        <?php if($hayProcesados): ?>
            <button type="button" class="btn btn-danger fw-bold text-white px-4" onclick="limpiarTablas()">🗑️ Limpiar</button>
            <a href="index.php?route=inventario&action=reportes" class="btn btn-info fw-bold text-dark px-4 ms-auto">👁️ Ver Reportes</a>
        <?php else: ?>
            <button type="button" class="btn btn-secondary px-4 ms-auto" disabled>👁️ Ver Reportes</button>
        <?php endif; ?>
    </div>

    <?php if($hayProcesados): ?>
        <h4 class="mb-3 text-secondary border-bottom pb-2">Vista Previa de Tablas Procesadas</h4>
        <div class="row g-4">
            
            <div class="col-md-3">
                <div class="card border-success shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-0 fw-bold">Shopify 1</h6>
                            <small class="text-muted"><?php echo number_format($totShop1); ?> reg</small>
                        </div>
                        <button class="btn btn-sm btn-outline-success rounded-circle" data-bs-toggle="modal" data-bs-target="#modalS1">👁️</button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-danger shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-0 fw-bold">eBay 1</h6>
                            <small class="text-muted"><?php echo number_format($totEbay1); ?> reg</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger rounded-circle" data-bs-toggle="modal" data-bs-target="#modalE1">👁️</button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-primary shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary mb-0 fw-bold">Shopify 2</h6>
                            <small class="text-muted"><?php echo number_format($totShop2); ?> reg</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#modalS2">👁️</button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning text-dark mb-0 fw-bold">eBay 2</h6>
                            <small class="text-muted"><?php echo number_format($totEbay2); ?> reg</small>
                        </div>
                        <button class="btn btn-sm btn-outline-warning rounded-circle" data-bs-toggle="modal" data-bs-target="#modalE2">👁️</button>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<?php if($hayProcesados): ?>
    <?php 
        $tablas = [
            'S1' => ['nombre' => 'tbl_shopify1', 'color' => 'success', 'titulo' => 'Shopify 1'],
            'E1' => ['nombre' => 'tbl_ebay1', 'color' => 'danger', 'titulo' => 'eBay 1'],
            'S2' => ['nombre' => 'tbl_shopify2', 'color' => 'primary', 'titulo' => 'Shopify 2'],
            'E2' => ['nombre' => 'tbl_ebay2', 'color' => 'warning text-dark', 'titulo' => 'eBay 2']
        ];
        
        foreach($tablas as $id => $info):
            $previa = InventoryProcessController::ctrObtenerPrevia($info['nombre']);
    ?>
    <div class="modal fade" id="modal<?php echo $id; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-<?php echo $info['color']; ?>">
                    <h5 class="modal-title <?php echo ($id=='E2')?'text-dark':'text-white'; ?>">Previsualización: <?php echo $info['titulo']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead class="table-dark"><tr><th>ID</th><th>SKU</th><th>Title</th><th>Inventory</th></tr></thead>
                        <tbody>
                            <?php 
                            foreach($previa as $row) { echo "<tr><td>{$row['id']}</td><td>{$row['sku']}</td><td>{$row['title']}</td><td>{$row['inventory']}</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>