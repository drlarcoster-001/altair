<?php
/**
 * Modulo: Gestión de Incidencias
 * Archivo: /views/modules/gestion_incidencias/cargar.php
 * Proposito: Pantalla para visualizar la última carga y modal para subir nuevos archivos.
 * Version: 0.0.6 - Validación para impedir carga si ya existe un registro activo.
 */

require_once "controllers/IncidentBatchController.php";

$ultimaCarga = IncidentBatchController::ctrObtenerUltimaCarga();
?>

<div class="container-fluid">
    
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white text-dark fw-bold border-bottom-0 pt-4 pb-0">
            <h4 class="mb-0"><span class="text-primary">🕒 Última carga</span> realizada</h4>
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

                <h6 class="fw-bold mb-3">Archivos subidos al servidor:</h6>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-success fw-bold">Shopify</span>
                            <?php $nombreShopify = basename($ultimaCarga['shopify_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nombreShopify; ?>')" title="Ver archivo">👁️</button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-primary fw-bold">Ced Commerce</span>
                            <?php $nombreCed = basename($ultimaCarga['cedcommerce_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nombreCed; ?>')" title="Ver archivo">👁️</button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <span class="text-danger fw-bold">eBay</span>
                            <?php $nombreEbay = basename($ultimaCarga['ebay_file_path']); ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="verNombreArchivo('<?php echo $nombreEbay; ?>')" title="Ver archivo">👁️</button>
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
        <a href="index.php?route=incidencias&action=index" class="btn btn-secondary">← Volver</a>
        
        <?php if($ultimaCarga): ?>
            <button type="button" class="btn btn-outline-danger" onclick="eliminarCargaActual(<?php echo $ultimaCarga['id']; ?>)">🗑️ Eliminar</button>
            
            <button type="button" class="btn btn-secondary ms-auto" onclick="Swal.fire('Acción Bloqueada', 'Debes eliminar la carga actual antes de subir nuevos archivos.', 'warning')">
                ➕ Cargar Archivos
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalCargaArchivos">
                ➕ Cargar Archivos
            </button>
        <?php endif; ?>
    </div>

</div>

<div class="modal fade" id="modalCargaArchivos" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLabel">Subir Nuevos Archivos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <form id="frmCargaArchivos" enctype="multipart/form-data">
                    
                    <div class="mb-4">
                        <label for="txt_nombre_carga" class="form-label fw-bold">Nombre del registro de la carga <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="txt_nombre_carga" name="batch_name" placeholder="Ej: Comparativa Semana 1">
                    </div>

                    <hr class="mb-4">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Archivo Shopify (.csv) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <button class="btn btn-success px-4" type="button" onclick="document.getElementById('file_shopify').click()">Buscar</button>
                            <input type="text" class="form-control bg-white" id="display_shopify" placeholder="Ningún archivo seleccionado..." readonly>
                            <input type="file" id="file_shopify" name="file_shopify" class="d-none" accept=".csv" onchange="actualizarDisplay('file_shopify', 'display_shopify')">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Archivo Ced Commerce (.csv) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <button class="btn btn-primary px-4" type="button" onclick="document.getElementById('file_cedcommerce').click()">Buscar</button>
                            <input type="text" class="form-control bg-white" id="display_cedcommerce" placeholder="Ningún archivo seleccionado..." readonly>
                            <input type="file" id="file_cedcommerce" name="file_cedcommerce" class="d-none" accept=".csv" onchange="actualizarDisplay('file_cedcommerce', 'display_cedcommerce')">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-danger">Archivo eBay (.csv) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <button class="btn btn-danger px-4" type="button" onclick="document.getElementById('file_ebay').click()">Buscar</button>
                            <input type="text" class="form-control bg-white" id="display_ebay" placeholder="Ningún archivo seleccionado..." readonly>
                            <input type="file" id="file_ebay" name="file_ebay" class="d-none" accept=".csv" onchange="actualizarDisplay('file_ebay', 'display_ebay')">
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