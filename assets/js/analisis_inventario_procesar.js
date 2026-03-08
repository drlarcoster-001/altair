/**
 * Modulo: Análisis de Inventario
 * Archivo: /assets/js/analisis_inventario_procesar.js
 * Proposito: Gestionar la lógica de validación de fechas, el procesamiento masivo de los 4 archivos CSV, la limpieza de tablas temporales y la exportación de resultados a formato Excel mediante Fetch API y SweetAlert2.
 * Version: 1.0.1 - Se incorpora la funcionalidad de exportación a Excel para las 4 tablas de resultados y se estandariza el flujo de notificaciones para el usuario.
 */

/**
 * Valida los rangos de fecha y dispara el proceso de lectura y carga de los 4 CSV en el servidor.
 */
function iniciarProcesamiento() {
    
    // Captura de valores de los inputs de fecha definidos en procesar.php
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;

    // Validación de campos obligatorios
    if(fechaInicio === "" || fechaFin === "") {
        Swal.fire({
            icon: 'warning',
            title: 'Información Incompleta',
            text: 'Debes seleccionar la Fecha de Inicio y la Fecha Fin antes de procesar.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    // Validación lógica de cronología
    if(fechaInicio > fechaFin) {
        Swal.fire({
            icon: 'error',
            title: 'Fechas Inválidas',
            text: 'La Fecha Inicio no puede ser posterior a la Fecha Fin.',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    // Feedback visual de procesamiento largo
    Swal.fire({
        title: 'Se están procesando los archivos...',
        html: `
            <div class="py-3">
                <div class="spinner-border text-primary" role="status"></div>
                <br><br>
                <span class="text-muted">Cruzando y mapeando datos de inventario (Shopify/eBay), por favor espere.</span>
            </div>
        `,
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
                        text: 'Los 4 archivos fueron procesados y guardados correctamente.',
                        confirmButtonText: 'Ver Resultados',
                        confirmButtonColor: '#198754'
                    }).then(() => { 
                        location.reload(); // Recarga para actualizar conteos y grids en la vista
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Procesamiento',
                        text: data.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                Swal.fire('Error Crítico', 'Fallo en la comunicación con el controlador de procesos.', 'error');
                console.error('Error en fetch:', error);
            });
        }
    });
}

/**
 * Ejecuta el vaciado (TRUNCATE) de las 4 tablas temporales de resultados.
 */
function limpiarTablas() {
    Swal.fire({
        title: '¿Limpiar datos procesados?',
        text: "Esta acción vaciará las 4 tablas temporales de forma permanente.",
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
                    Swal.fire({
                        icon: 'success',
                        title: '¡Tablas Vacías!',
                        text: data.message,
                        confirmButtonColor: '#198754'
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'No se pudo completar la limpieza de tablas.', 'error');
            });
        }
    });
}

/**
 * Solicita al servidor la descarga de la tabla seleccionada en formato CSV/Excel.
 * @param {string} tabla - Nombre de la tabla a exportar (tbl_shopify1, tbl_ebay1, etc.)
 */
function exportarExcel(tabla) {
    // Feedback inmediato de inicio de descarga
    Swal.fire({
        title: 'Generando reporte...',
        text: 'Estamos preparando su archivo, la descarga iniciará pronto.',
        timer: 2000,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    /**
     * Se redirige a una acción GET en el controlador para disparar el flujo de descarga de PHP.
     * El controlador debe manejar las cabeceras Content-Type: application/vnd.ms-excel.
     */
    window.location.href = `controllers/InventoryProcessController.php?action=exportar_excel&tabla=${tabla}`;

    // Mensaje de éxito solicitado tras disparar la descarga
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: '¡Descarga Exitosa!',
            text: 'Su archivo ha sido descargado.',
            timer: 3000,
            showConfirmButton: false
        });
    }, 1500);
}