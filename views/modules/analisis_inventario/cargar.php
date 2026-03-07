<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: /views/modules/analisis_inventario/cargar.php
 * Proposito: Formulario de carga de 4 archivos (Shopify 1, eBay 1, Shopify 2, eBay 2).
 * Version: 0.0.1 - Grid de 4 elementos y validación de carga activa.
 */

require_once "controllers/InventoryBatchController.php";

$ultimaCarga = InventoryBatchController::ctrObtenerUltimaCarga();
?>

<div class="container-fluid">
    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white text-dark fw-bold border-bottom-0 pt-4 pb-0">
            <h4 class="mb-0"><span class="text-success">🕒 Última carga</span> de inventario</h4>
        </div>
        <div class="card-body pt-3">
            
            <?php if($ultimaCarga): ?>
                <div class="mb-4">
                    <h5 class="fw-bold text-secondary">Nombre: 
                        <span class="text-dark fw-normal" id="lbl_nombre_carga">
                            <?php echo htmlspecialchars($ultimaCarga['batch_name']); ?>
                        </span>
                    </h5>
                    <p class="text-muted small">Fecha de carga: <?php echo date("d/m/Y H:i A", strtotime($ultimaCarga['created_at'])); ?></p>
                </div>

                <h6 class="fw-bold mb-3">Archivos subidos:</h6>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold">Shopify 1</span>
                            <?php $nShop1 = basename($ultimaCarga['shopify1_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nShop1; ?>')" title="Ver archivo">👁️</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-danger fw-bold">eBay 1</span>
                            <?php $nEbay1 = basename($ultimaCarga['ebay1_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nEbay1; ?>')" title="Ver archivo">👁️</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-bold">Shopify 2</span>
                            <?php $nShop2 = basename($ultimaCarga['shopify2_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nShop2; ?>')" title="Ver archivo">👁️</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-warning fw-bold text-dark">eBay 2</span>
                            <?php $nEbay2 = basename($ultimaCarga['ebay2_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nEbay2; ?>')" title="Ver archivo">👁️</button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="mb-4">
                    <h5 class="fw-bold text-secondary">Nombre: 
                        <span class="text-danger fw-normal">No hay registros cargados</span>
                    </h5>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold text-secondary">Archivos subidos: 
                        <span class="text-danger fw-normal">No hay archivos cargados</span>
                    </h6>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="index.php?route=inventario&action=index" class="btn btn-secondary">← Volver</a>
        
        <?php if($ultimaCarga): ?>
            <button type="button" class="btn btn-outline-danger" onclick="eliminarCargaActual(<?php echo $ultimaCarga['id']; ?>)">🗑️ Eliminar</button>
            <button type="button" class="btn btn-secondary ms-auto" onclick="Swal.fire('Acción Bloqueada', 'Debes eliminar la carga actual antes de subir nuevos archivos.', 'warning')">
                ➕ Cargar Archivos
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#modalCargaArchivos">
                ➕ Cargar Archivos
            </button>
        <?php endif; ?>
    </div>

</div>

<div class="modal fade" id="modalCargaArchivos" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLabel">Subir Inventario Actual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="frmCargaArchivos">
                    
                    <div class="mb-4">
                        <label for="txt_nombre_carga" class="form-label fw-bold">Nombre del registro de la carga <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="txt_nombre_carga" placeholder="Ej: Inventario Febrero">
                    </div>

                    <hr class="mb-4">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-success">Archivo Shopify 1 (.csv) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <button class="btn btn-success" type="button" onclick="document.getElementById('file_shopify1').click()">Buscar</button>
                                <input type="text" class="form-control bg-white" id="display_shopify1" placeholder="Ningún archivo..." readonly>
                                <input type="file" id="file_shopify1" class="d-none" accept=".csv" onchange="actualizarDisplay('file_shopify1', 'display_shopify1')">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-danger">Archivo eBay 1 (.csv) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <button class="btn btn-danger" type="button" onclick="document.getElementById('file_ebay1').click()">Buscar</button>
                                <input type="text" class="form-control bg-white" id="display_ebay1" placeholder="Ningún archivo..." readonly>
                                <input type="file" id="file_ebay1" class="d-none" accept=".csv" onchange="actualizarDisplay('file_ebay1', 'display_ebay1')">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-primary">Archivo Shopify 2 (.csv) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <button class="btn btn-primary" type="button" onclick="document.getElementById('file_shopify2').click()">Buscar</button>
                                <input type="text" class="form-control bg-white" id="display_shopify2" placeholder="Ningún archivo..." readonly>
                                <input type="file" id="file_shopify2" class="d-none" accept=".csv" onchange="actualizarDisplay('file_shopify2', 'display_shopify2')">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-warning text-dark">Archivo eBay 2 (.csv) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <button class="btn btn-warning fw-bold text-dark" type="button" onclick="document.getElementById('file_ebay2').click()">Buscar</button>
                                <input type="text" class="form-control bg-white" id="display_ebay2" placeholder="Ningún archivo..." readonly>
                                <input type="file" id="file_ebay2" class="d-none" accept=".csv" onchange="actualizarDisplay('file_ebay2', 'display_ebay2')">
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardar" class="btn btn-dark px-4" onclick="validarYGuardar()">💾 Guardar</button>
            </div>

        </div>
    </div>
</div>