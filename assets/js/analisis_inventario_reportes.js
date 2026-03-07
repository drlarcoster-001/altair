/**
 * Modulo: Análisis de Inventario
 * archivo: assets/js/analisis_inventario_reportes.js
 * Proposito: Ejecutar la llamada al proceso de generación modular y notificar el progreso al usuario.
 * Version: 1.1.3 - Gestión de petición AJAX para proceso modular de actualización secuencial.
 */

function generarReporteInventario() {
    Swal.fire({
        title: 'Generando Reporte...',
        html: '<div class="gear-icon">⚙️</div><br>Reiniciando tabla y cruzando Shopify 1...',
        showConfirmButton: false, 
        allowOutsideClick: false,
        didOpen: () => {
            let fd = new FormData();
            fd.append('action', 'generar_reporte');

            fetch('controllers/InventoryReportController.php', { 
                method: 'POST', 
                body: fd 
            })
            .then(response => response.json())
            .then(res => {
                if(res.status === 'success') {
                    Swal.fire('¡Éxito!', res.message, 'success');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            });
        }
    });
}