/**
 * Modulo: Análisis de Inventario
 * Archivo: assets/js/analisis_inventario_reportes.js
 * Proposito: Gestión de grid dinámica, filtros y barra de progreso. Se asegura la limpieza visual total tras la eliminación de datos.
 * Version: 1.1.3 - Refresco automático tras limpieza y barra de progreso corregida.
 */

let paginaActual = 1;

document.addEventListener('DOMContentLoaded', () => { actualizarGridReporte(); });

function filtrarGridReporte() { paginaActual = 1; actualizarGridReporte(); }
function cambiarPaginacion() { paginaActual = 1; actualizarGridReporte(); }

function actualizarGridReporte() {
    const filters = {
        action: 'listar_reporte',
        sku: document.getElementById('f_sku').value,
        estado: document.getElementById('f_estado').value,
        causa: document.getElementById('f_causa').value,
        prioridad: document.getElementById('f_prioridad').value,
        limit: document.getElementById('paginacion_reporte').value,
        page: paginaActual
    };

    let fd = new FormData();
    for(let key in filters) fd.append(key, filters[key]);

    fetch('controllers/InventoryReportController.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('tbody_reporte');
        const btnExp = document.getElementById('btnExportar');
        let html = '';
        
        if(data && data.length > 0) {
            btnExp.disabled = false;
            data.forEach(row => {
                let colorP = row.prioridad === 'ALTA' ? 'text-danger fw-bold' : (row.prioridad === 'BAJA' ? 'text-success fw-bold' : 'text-warning fw-bold');
                html += `<tr>
                    <td class="ps-3 fw-bold">${row.sku}</td>
                    <td class="text-center">${row.inv1}</td><td class="text-center">${row.ebay1}</td>
                    <td class="text-center">${row.inv2}</td><td class="text-center">${row.ebay2}</td>
                    <td class="text-center bg-light">${row.disc_inv1}</td><td class="text-center bg-light">${row.disc_inv2}</td>
                    <td class="text-center"><span class="badge bg-dark">${row.estado}</span></td>
                    <td class="text-center">${row.causa}</td>
                    <td class="text-center pe-3 ${colorP}">${row.prioridad}</td>
                </tr>`;
            });
        } else {
            btnExp.disabled = true;
            html = '<tr><td colspan="10" class="text-center py-5 text-muted">No hay datos procesados en el reporte.</td></tr>';
        }
        tbody.innerHTML = html;
    }).catch(err => { console.error("Error cargando grid:", err); });
}

function generarReporteDiscrepancias() {
    let timerInterval;
    Swal.fire({
        title: 'Generando Análisis...',
        html: `<div class="progress" style="height:25px;"><div id="pb-sp" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:0%">0%</div></div>`,
        allowOutsideClick: false,
        didOpen: () => {
            const bar = document.getElementById('pb-sp');
            let p = 0;
            timerInterval = setInterval(() => {
                if(p < 95) { p += 5; bar.style.width = p+'%'; bar.innerText = p+'%'; }
            }, 100);

            let fd = new FormData(); fd.append('action', 'generar_reporte');
            fetch('controllers/InventoryReportController.php', { method: 'POST', body: fd })
            .then(r => r.json()).then(data => {
                clearInterval(timerInterval);
                if(data.status === 'success') {
                    bar.style.width = '100%'; bar.innerText = '100%';
                    setTimeout(() => { Swal.fire('Éxito', 'Reporte generado.', 'success'); actualizarGridReporte(); }, 500);
                }
            }).catch(() => { clearInterval(timerInterval); Swal.fire('Error', 'Respuesta inesperada.', 'error'); });
        }
    });
}

function limpiarReporte() {
    Swal.fire({
        title: '¿Limpiar reporte?',
        text: "Se vaciará la grid y la base de datos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, limpiar'
    }).then(r => {
        if(r.isConfirmed){
            let fd = new FormData(); fd.append('action', 'limpiar_reporte');
            fetch('controllers/InventoryReportController.php', { method: 'POST', body: fd })
            .then(r => r.json()).then(data => {
                if(data.status === 'success') {
                    paginaActual = 1;
                    actualizarGridReporte();
                    Swal.fire('Vaciado', 'El reporte ha sido eliminado.', 'success');
                }
            });
        }
    });
}

function exportarReporte(formato) {
    const sku = document.getElementById('f_sku').value;
    const estado = document.getElementById('f_estado').value;
    const causa = document.getElementById('f_causa').value;
    const prioridad = document.getElementById('f_prioridad').value;

    Swal.fire({
        icon: 'info',
        title: 'Generando archivo...',
        timer: 2000,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const params = `action=exportar_reporte&formato=${formato}&sku=${sku}&estado=${estado}&causa=${causa}&prioridad=${prioridad}`;
    window.location.href = `controllers/InventoryReportController.php?${params}`;
}