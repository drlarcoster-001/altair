/*
 * Modulo: Dashboard Principal
 * Archivo: /assets/js/dashboard.js
 * Proposito: Control de interacción de las tarjetas de selección de módulo.
 * Version: 0.0.1 - Redirección con SweetAlert2.
 */

function navegarModulo(rutaDestino) {
    Swal.fire({
        title: 'Accediendo...',
        text: 'Cargando el módulo seleccionado',
        icon: 'info',
        timer: 800,
        showConfirmButton: false,
        didClose: () => {
            window.location.href = 'index.php?route=' + rutaDestino + '&action=index';
        }
    });
}