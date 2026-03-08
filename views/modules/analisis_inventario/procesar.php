<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: views/modules/analisis_inventario/procesar.php
 * Proposito: Interfaz técnica para el procesamiento masivo de inventarios. Permite definir rangos de fecha, ejecutar la lectura de archivos CSV hacia las tablas temporales (Shopify/eBay) y ofrece previsualización de datos procesados con opción de exportación a Excel.
 * Version: 1.0.0 - Implementación de 4 grids con botones de visualizar y descargar Excel por tabla.
 */

require_once "controllers/InventoryBatchController.php";
require_once "controllers/InventoryProcessController.php";

// Se obtiene la información del lote para validar rutas de archivos físicos
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

// Consultar el estado actual de las tablas temporales
$totShop1 = InventoryProcessController::ctrObtenerConteo('tbl_shopify1');
$totEbay1 = InventoryProcessController::ctrObtenerConteo('tbl_ebay1');
$totShop2 = InventoryProcessController::ctrObtenerConteo('tbl_shopify2');
$totEbay2 = InventoryProcessController::ctrObtenerConteo('tbl_ebay2');

// Se considera que hay datos procesados si la tabla principal de Shopify 1 tiene registros
$hayProcesados = ($totShop1 > 0);
?>

<div class="container-fluid py-4">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-cpu-fill me-2"></i> Procesamiento de Inventario Activo</h5>
            <?php if($ultimaCarga): ?>
                <span class="badge bg-success">Lote: <?php echo htmlspecialchars($ultimaCarga['batch_name']); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body p-4">
            <?php if($ultimaCarga): ?>
                <div class="row g-3">
                    <div class="col-md-6 border-end">
                        <p class="mb-1 text-muted small uppercase fw-bold">Información del Lote</p>
                        <h5 class="mb-0 text-dark"><?php echo htmlspecialchars($ultimaCarga['batch_name']); ?></h5>
                        <p class="text-muted small mb-0">Subido el: <?php echo date("d/m/Y H:i", strtotime($ultimaCarga['created_at'])); ?></p>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary small">Fecha Inicio Auditoría</label>
                        <input type="date" class="form-control" id="fecha_inicio" value="<?php echo $ultimaCarga['fecha_inicio'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-secondary small">Fecha Fin Auditoría</label>
                        <input type="date" class="form-control" id="fecha_fin" value="<?php echo $ultimaCarga['fecha_fin'] ?? ''; ?>">
                    </div>
                </div>

                <?php if(!$archivosFisicosExisten): ?>
                    <div class="alert alert-danger mt-4 mb-0 border-0 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Archivos no encontrados:</strong> Se detecta el registro en BD, pero los archivos físicos han sido movidos o eliminados de <code>C:\TEMP</code>.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-3">
                    <p class="text-muted mb-3">No hay un lote de archivos disponible para procesar.</p>
                    <a href="index.php?route=inventario&action=cargar" class="btn btn-primary btn-sm">Ir a Carga de Archivos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="index.php?route=inventario&action=index" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        
        <?php if($ultimaCarga && $archivosFisicosExisten): ?>
            <button type="button" class="btn btn-warning fw-bold text-dark px-4 shadow-sm" onclick="iniciarProcesamiento()">
                <i class="bi bi-play-fill"></i> Iniciar Procesamiento CSV
            </button>
        <?php endif; ?>

        <?php if($hayProcesados): ?>
            <button type="button" class="btn btn-danger fw-bold text-white px-4 shadow-sm" onclick="limpiarTablas()">
                <i class="bi bi-eraser-fill"></i> Limpiar Tablas
            </button>
            <a href="index.php?route=inventario&action=reportes" class="btn btn-info fw-bold text-dark px-4 ms-auto shadow-sm">
                Analizar Discrepancias <i class="bi bi-arrow-right-short"></i>
            </a>
        <?php endif; ?>
    </div>

    <?php if($hayProcesados): ?>
        <h5 class="mb-4 text-secondary"><i class="bi bi-table me-2"></i> Datos Importados Correctamente</h5>
        <div class="row g-4">
            
            <?php 
                $cards = [
                    ['id' => 'S1', 'tab' => 'tbl_shopify1', 'color' => 'success', 'title' => 'Shopify Snapshot 1', 'total' => $totShop1],
                    ['id' => 'E1', 'tab' => 'tbl_ebay1', 'color' => 'danger', 'title' => 'eBay Snapshot 1', 'total' => $totEbay1],
                    ['id' => 'S2', 'tab' => 'tbl_shopify2', 'color' => 'primary', 'title' => 'Shopify Snapshot 2', 'total' => $totShop2],
                    ['id' => 'E2', 'tab' => 'tbl_ebay2', 'color' => 'warning', 'title' => 'eBay Snapshot 2', 'total' => $totEbay2]
                ];

                foreach($cards as $c):
            ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-top border-4 border-<?php echo $c['color']; ?> h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="fw-bold mb-0"><?php echo $c['title']; ?></h6>
                            <span class="badge bg-light text-dark border"><?php echo number_format($c['total']); ?> SKU</span>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button class="btn btn-sm btn-<?php echo $c['color']; ?> <?php echo ($c['id']=='E2')?'text-dark':'text-white'; ?> flex-fill" data-bs-toggle="modal" data-bs-target="#modal<?php echo $c['id']; ?>">
                                <i class="bi bi-eye"></i> Visualizar
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportarExcel('<?php echo $c['tab']; ?>')" title="Descargar en Excel">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    <?php endif; ?>

</div>

<?php if($hayProcesados): ?>
    <?php 
        foreach($cards as $c):
            $previa = InventoryProcessController::ctrObtenerPrevia($c['tab']);
    ?>
    <div class="modal fade" id="modal<?php echo $c['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-<?php echo $c['color']; ?> <?php echo ($c['id']=='E2')?'text-dark':'text-white'; ?>">
                    <h5 class="modal-title fw-bold">Vista Previa: <?php echo $c['title']; ?></h5>
                    <button type="button" class="btn-close <?php echo ($c['id']!='E2')?'btn-close-white':''; ?>" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-hover table-striped mb-0 small">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>SKU (Identificador)</th>
                                <th>Título del Producto</th>
                                <th class="text-center">Inventario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($previa as $row): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?php echo $row['id']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['sku']); ?></td>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="text-center bg-light fw-bold"><?php echo $row['inventory']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light py-2">
                    <span class="me-auto text-muted small">Mostrando los primeros 5 registros cargados.</span>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>