/**
 * Modulo: Análisis de Inventario
 * Archivo: assets/js/analisis_inventario_cargar.js
 * Proposito: Gestionar la validación, envío asíncrono de los 4 archivos de inventario (Snapshots) y la eliminación de cargas existentes mediante Fetch API y SweetAlert2.
 * Version: 1.0.1 - Se implementa manejo de carga visual con Swal.showLoading(), captura de errores mediante try-catch y sincronización con la lógica de renombrado del controlador.
 */

/**
 * Muestra información básica sobre un archivo cargado.
 */
function verNombreArchivo(nombreArchivo) {
    Swal.fire({ 
        title: 'Detalle del Archivo', 
        text: `Archivo seleccionado: ${nombreArchivo}`, 
        icon: 'info',
        confirmButtonColor: '#0d6efd'
    });
}

/**
 * Gestiona la eliminación de un lote de inventario tanto en base de datos como en archivos físicos.
 */
function eliminarCargaActual(id_carga) {
    Swal.fire({
        title: '¿Confirmar eliminación?',
        text: "Se borrarán los registros y los archivos físicos en el servidor de forma permanente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar permanentemente',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Mostrar estado de carga durante el borrado
            Swal.fire({
                title: 'Eliminando...',
                didOpen: () => { Swal.showLoading(); }
            });

            let formData = new FormData();
            formData.append('action', 'eliminar_carga');
            formData.append('id', id_carga);

            try {
                const response = await fetch('controllers/InventoryBatchController.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if(data.status === 'success') {
                    Swal.fire('¡Eliminado!', data.message, 'success')
                        .then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error Crítico', 'No se pudo comunicar con el servidor para eliminar el registro.', 'error');
            }
        }
    });
}

/**
 * Sincroniza visualmente el nombre del archivo seleccionado en un input de texto de apoyo.
 */
function actualizarDisplay(inputId, displayId) {
    const inputOculto = document.getElementById(inputId);
    const inputVisible = document.getElementById(displayId);
    inputVisible.value = (inputOculto.files && inputOculto.files.length > 0) ? inputOculto.files[0].name : "";
}

/**
 * Valida los requerimientos mínimos y envía los archivos al servidor para su procesamiento.
 */
async function validarYGuardar() {
    const nombreCarga = document.getElementById('txt_nombre_carga').value.trim();
    const fShop1 = document.getElementById('file_shopify1').files[0];
    const fEbay1 = document.getElementById('file_ebay1').files[0];
    const fShop2 = document.getElementById('file_shopify2').files[0];
    const fEbay2 = document.getElementById('file_ebay2').files[0];

    // Validaciones de frontend
    if (nombreCarga === "") {
        Swal.fire({ icon: 'error', title: 'Falta información', text: 'Debe asignar un nombre al lote de inventario.' }); 
        return;
    }
    
    if (!fShop1 || !fEbay1 || !fShop2 || !fEbay2) {
        Swal.fire({ 
            icon: 'error', 
            title: 'Archivos incompletos', 
            text: 'Para el análisis de discrepancias se requieren los 4 archivos (Snapshots 1 y 2).' 
        }); 
        return;
    }

    // Preparación de UI para el proceso de carga
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;

    Swal.fire({
        title: 'Subiendo archivos...',
        text: 'Por favor, no cierre la ventana mientras se procesan los archivos en el servidor.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    let formData = new FormData();
    formData.append('action', 'subir_archivos');
    formData.append('batch_name', nombreCarga);
    formData.append('file_shopify1', fShop1);
    formData.append('file_ebay1', fEbay1);
    formData.append('file_shopify2', fShop2);
    formData.append('file_ebay2', fEbay2);

    try {
        const response = await fetch('controllers/InventoryBatchController.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if(data.status === 'success') {
            Swal.fire({ 
                icon: 'success', 
                title: '¡Carga Exitosa!', 
                text: data.message 
            }).then(() => { location.reload(); });
        } else {
            Swal.fire({ 
                icon: 'error', 
                title: 'Error en el proceso', 
                text: data.message 
            });
            btnGuardar.disabled = false;
        }
    } catch (error) {
        Swal.fire({ 
            icon: 'error', 
            title: 'Error de Red', 
            text: 'No se pudo establecer conexión con el controlador de inventario.' 
        });
        btnGuardar.disabled = false;
    }
}