/**
 * Modulo: Análisis de Inventario
 * Archivo: assets/js/analisis_inventario_reportes.js
 * Proposito: Gestionar la interacción de la interfaz de reportes. Controla la ejecución del Stored Procedure de discrepancias con barra de progreso visual, realiza búsquedas dinámicas en tiempo real, maneja la paginación de hasta 1000 registros y coordina las exportaciones a Excel y Word.
 * Version: 1.0.1 - Implementación de lógica de progreso simulado sincronizado con fetch y filtrado dinámico global.
 */

// Variables globales para control de estado
let paginaActual = 1;

/**
 * Inicia el proceso de cálculo de discrepancias en el servidor.
 * Muestra un popup con barra de progreso real/simulada.
 */
function generarReporteDiscrepancias() {
    let timerInterval;
    
    Swal.fire({
        title: 'Generando Análisis de Discrepancias',
        html: `
            <div class="text-start small mb-2">Calculando estados, causas y prioridades según reglas ALTAIR...</div>
            <div class="progress" style="height: 25px;">
                <div id="progreso-reporte" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                     role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            const progressBar = document.getElementById('progreso-reporte');
            let progreso = 0;

            // Simulación de progreso visual mientras el servidor procesa el Stored Procedure
            timerInterval = setInterval(() => {
                if (progreso < 95) {
                    progreso += Math.floor(Math.random() * 5) + 1;
                    if (progreso > 95) progreso = 95;
                    progressBar.style.width = progreso + '%';
                    progressBar.innerText = progreso + '%';
                }
            }, 200);

            // Petición al controlador para ejecutar la lógica de negocio (Stored Procedure)
            let formData = new FormData();
            formData.append('action', 'generar_reporte');

            fetch('controllers/InventoryReportController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(timerInterval);
                if (data.status === 'success') {
                    // Completar barra al 100%
                    progressBar.style.width = '100%';
                    progressBar.innerText = '100%';
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Reporte Generado!',
                            text: 'Los datos han sido procesados exitosamente.',
                            confirmButtonColor: '#0d6efd'
                        }).then(() => {
                            paginaActual = 1;
                            actualizarGridReporte();
                        });
                    }, 500);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                clearInterval(timerInterval);
                Swal.fire('Error Crítico', 'No se pudo completar la generación del reporte.', 'error');
                console.error(error);
            });
        }
    });
}

/**
 * Filtra la información de la grid en tiempo real mientras el usuario escribe.
 */
function filtrarGridReporte() {
    paginaActual = 1; // Reiniciar a la primera página en cada búsqueda
    actualizarGridReporte();
}

/**
 * Cambia la cantidad de registros a mostrar.
 */
function cambiarPaginacion() {
    paginaActual = 1;
    actualizarGridReporte();
}

/**
 * Obtiene los datos del servidor y renderiza la tabla.
 */
function actualizarGridReporte() {
    const busqueda = document.getElementById('filtro_global').value;
    const limite = document.getElementById('paginacion_reporte').value;
    const tbody = document.getElementById('tbody_reporte');

    // Mostrar loader interno en la tabla
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando datos...</td></tr>`;

    let formData = new FormData();
    formData.append('action', 'listar_reporte');
    formData.append('search', busqueda);
    formData.append('limit', limite);
    formData.append('page', paginaActual);

    fetch('controllers/InventoryReportController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let html = '';
        if (data.length > 0) {
            data.forEach(row => {
                // Lógica de colores para prioridad y estado
                let badgePrioridad = '';
                switch(row.prioridad) {
                    case 'ALTA': badgePrioridad = 'text-danger fw-bold'; break;
                    case 'MEDIA': badgePrioridad = 'text-warning fw-bold'; break;
                    case 'BAJA': badgePrioridad = 'text-success fw-bold'; break;
                }

                html += `
                    <tr>
                        <td class="ps-3 fw-bold text-primary">${row.sku}</td>
                        <td class="text-center">${row.inv1}</td>
                        <td class="text-center">${row.ebay1}</td>
                        <td class="text-center">${row.inv2}</td>
                        <td class="text-center">${row.ebay2}</td>
                        <td class="text-center bg-light">${row.disc_inv1}</td>
                        <td class="text-center bg-light">${row.disc_inv2}</td>
                        <td class="text-center"><span class="badge rounded-pill bg-dark">${row.estado}</span></td>
                        <td class="text-center small">${row.causa}</td>
                        <td class="text-center pe-3 ${badgePrioridad}">${row.prioridad}</td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="10" class="text-center py-4 text-muted">No se encontraron resultados con los filtros aplicados.</td></tr>';
        }
        tbody.innerHTML = html;
    })
    .catch(error => {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error al cargar la grid.</td></tr>';
    });
}

/**
 * Limpia los resultados procesados previa confirmación.
 */
function limpiarReporte() {
    Swal.fire({
        title: '¿Limpiar reporte actual?',
        text: "Se eliminarán los cálculos de discrepancias generados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('action', 'limpiar_reporte');

            fetch('controllers/InventoryReportController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('¡Limpiado!', 'La tabla de resultados ha sido vaciada.', 'success');
                    actualizarGridReporte();
                }
            });
        }
    });
}

/**
 * Gestiona la exportación del reporte a Excel o Word.
 * @param {string} formato - 'excel' o 'word'
 */
function exportarReporte(formato) {
    const busqueda = document.getElementById('filtro_global').value;
    
    Swal.fire({
        title: 'Preparando exportación...',
        text: `Generando archivo ${formato.toUpperCase()}, por favor espere.`,
        timer: 2000,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    // Redirección al controlador para descarga directa
    window.location.href = `controllers/InventoryReportController.php?action=exportar_reporte&formato=${formato}&search=${busqueda}`;
}