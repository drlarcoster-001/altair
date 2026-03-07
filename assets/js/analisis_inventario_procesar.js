/**
 * Modulo: Análisis de Inventario
 * Archivo: /assets/js/analisis_inventario_procesar.js
 * Proposito: Validar fechas, enviar peticiones AJAX para procesar y limpiar 4 tablas.
 * Version: 0.0.1
 */

function iniciarProcesamiento() {
    
    // Validar Fechas Obligatorias
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;

    if(fechaInicio === "" || fechaFin === "") {
        Swal.fire('Información Incompleta', 'Debes seleccionar la Fecha de Inicio y la Fecha Fin antes de procesar.', 'warning');
        return;
    }

    if(fechaInicio > fechaFin) {
        Swal.fire('Fechas Inválidas', 'La Fecha Inicio no puede ser mayor a la Fecha Fin.', 'error');
        return;
    }

    Swal.fire({
        title: 'Se están procesando los archivos...',
        html: '<div class="gear-icon">⚙️</div><br><span class="text-muted">Cruzando datos de inventario, por favor espere.</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            
            let formData = new FormData();
            formData.append('action', 'ejecutar_procesamiento');
            formData.append('fecha_inicio', fechaInicio);
            formData.append('fecha_fin', fechaFin);

            fetch('controllers/InventoryProcessController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Proceso culminado!',
                        text: 'Los 4 archivos fueron guardados correctamente.',
                        confirmButtonText: 'Ver Resultados'
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Fallo en la comunicación con el servidor.', 'error');
                console.error(error);
            });
        }
    });
}

function limpiarTablas() {
    Swal.fire({
        title: '¿Limpiar datos procesados?',
        text: "Esta acción vaciará las 4 tablas temporales.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar tablas',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('action', 'limpiar_tablas');

            fetch('controllers/InventoryProcessController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('¡Limpiado!', data.message, 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}