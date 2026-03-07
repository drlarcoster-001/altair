/**
 * Modulo: Análisis de Inventario
 * Archivo: /assets/js/analisis_inventario_cargar.js
 * Proposito: Validar y enviar 4 archivos al controlador mediante AJAX.
 * Version: 0.0.1
 */

function verNombreArchivo(nombreArchivo) {
    Swal.fire({ title: 'Archivo Cargado', text: nombreArchivo, icon: 'info' });
}

function eliminarCargaActual(id_carga) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Se eliminarán los archivos y la carpeta 'inv-...' de C:\\TEMP permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar todo'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('action', 'eliminar_carga');
            formData.append('id', id_carga);

            fetch('controllers/InventoryBatchController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('¡Eliminado!', data.message, 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

function actualizarDisplay(inputId, displayId) {
    const inputOculto = document.getElementById(inputId);
    const inputVisible = document.getElementById(displayId);
    inputVisible.value = (inputOculto.files && inputOculto.files.length > 0) ? inputOculto.files[0].name : "";
}

function validarYGuardar() {
    const nombreCarga = document.getElementById('txt_nombre_carga').value.trim();
    const fShop1 = document.getElementById('file_shopify1').files[0];
    const fEbay1 = document.getElementById('file_ebay1').files[0];
    const fShop2 = document.getElementById('file_shopify2').files[0];
    const fEbay2 = document.getElementById('file_ebay2').files[0];

    if (nombreCarga === "") {
        Swal.fire({ icon: 'error', title: 'Falta información', text: 'Coloca un nombre.' }); return;
    }
    if (!fShop1 || !fEbay1 || !fShop2 || !fEbay2) {
        Swal.fire({ icon: 'error', title: 'Archivos incompletos', text: 'Debes seleccionar los 4 archivos.' }); return;
    }

    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '⏳ Subiendo...';

    let formData = new FormData();
    formData.append('action', 'subir_archivos');
    formData.append('batch_name', nombreCarga);
    formData.append('file_shopify1', fShop1);
    formData.append('file_ebay1', fEbay1);
    formData.append('file_shopify2', fShop2);
    formData.append('file_ebay2', fEbay2);

    fetch('controllers/InventoryBatchController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '💾 Guardar';

        if(data.status === 'success') {
            Swal.fire({ icon: 'success', title: '¡Carga Exitosa!', text: data.message }).then(() => { location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Error del servidor', text: data.message });
        }
    });
}