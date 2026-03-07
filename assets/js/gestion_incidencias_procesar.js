/**
 * Modulo: Gestión de Incidencias
 * Archivo: /assets/js/gestion_incidencias_procesar.js
 * Proposito: Controlar procesamiento y limpieza de tablas vía AJAX.
 * Version: 0.0.2 - Se añade función limpiarTablas().
 */

function iniciarProcesamiento() {
    Swal.fire({
        title: 'Se están procesando los archivos...',
        html: '<div class="gear-icon">⚙️</div><br><span class="text-muted">Por favor espere. Esto puede tardar unos segundos.</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            let formData = new FormData();
            formData.append('action', 'ejecutar_procesamiento');

            fetch('controllers/IncidentProcessController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Proceso culminado!',
                        text: 'Los archivos han sido leídos y guardados en la base de datos.',
                        confirmButtonText: 'Ver Resultados'
                    }).then(() => {
                        location.reload();
                    });
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
        text: "Esta acción vaciará las 3 tablas temporales. Tendrás que procesar los archivos nuevamente si deseas ver los reportes.",
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

            fetch('controllers/IncidentProcessController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('¡Limpiado!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Fallo de red al intentar limpiar las tablas.', 'error');
                console.error('Error AJAX:', error);
            });
        }
    });
}