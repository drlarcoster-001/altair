/**
 * Modulo: Gestión de Incidencias
 * Archivo: /assets/js/gestion_incidencias_cargar.js
 * Proposito: Validar, enviar archivos y procesar eliminación mediante AJAX (Fetch API).
 * Version: 0.0.3 - Integración de la función eliminarCargaActual con Fetch.
 */

function verNombreArchivo(nombreArchivo) {
    Swal.fire({
        title: 'Archivo Cargado',
        text: nombreArchivo,
        icon: 'info',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Cerrar'
    });
}

// Ahora la función de eliminar recibe el ID de la base de datos
function eliminarCargaActual(id_carga) {
    Swal.fire({
        title: '¿Estás seguro?',
        // Se actualiza el mensaje para referenciar el servidor en lugar de rutas locales
        text: "Se eliminarán los archivos del servidor y el registro de la base de datos permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            
            let formData = new FormData();
            formData.append('action', 'eliminar_carga');
            formData.append('id', id_carga);

            fetch('controllers/IncidentBatchController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('¡Eliminado!', data.message, 'success').then(() => {
                        location.reload(); // Recarga para mostrar los textos de "No hay registros"
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                console.error('Error:', error);
            });
        }
    });
}

function actualizarDisplay(inputId, displayId) {
    const inputOculto = document.getElementById(inputId);
    const inputVisible = document.getElementById(displayId);
    
    if (inputOculto.files && inputOculto.files.length > 0) {
        inputVisible.value = inputOculto.files[0].name;
    } else {
        inputVisible.value = "";
    }
}

function validarYGuardar() {
    const nombreCarga = document.getElementById('txt_nombre_carga').value.trim();
    const fileShopify = document.getElementById('file_shopify').files[0];
    const fileCed = document.getElementById('file_cedcommerce').files[0];
    const fileEbay = document.getElementById('file_ebay').files[0];

    if (nombreCarga === "") {
        Swal.fire({ icon: 'error', title: 'Falta información', text: 'Coloca un nombre para el registro de la carga.' });
        return;
    }
    if (!fileShopify || !fileCed || !fileEbay) {
        Swal.fire({ icon: 'error', title: 'Archivos incompletos', text: 'Debes seleccionar los 3 archivos (.csv) para poder guardar.' });
        return;
    }

    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '⏳ Subiendo...';

    let formData = new FormData();
    formData.append('action', 'subir_archivos');
    formData.append('batch_name', nombreCarga);
    formData.append('file_shopify', fileShopify);
    formData.append('file_cedcommerce', fileCed);
    formData.append('file_ebay', fileEbay);

    fetch('controllers/IncidentBatchController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '💾 Guardar';

        if(data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Carga Exitosa!',
                text: data.message,
                showConfirmButton: true
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error del servidor', text: data.message });
        }
    })
    .catch(error => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '💾 Guardar';
        Swal.fire({ icon: 'error', title: 'Error de Red', text: 'No se pudo conectar con el servidor.' });
        console.error('Error:', error);
    });
}