/**
 * Modulo: Gestión de Incidencias
 * Archivo: /assets/js/gestion_incidencias_reportes.js
 * Proposito: Controlar la generación del reporte vía AJAX y la exportación a Excel/Word.
 * Version: 0.0.1 - Separación de responsabilidades.
 */

// Variables globales para guardar la data
let dataCed = [];
let dataEbay = [];

/**
 * Función principal para consultar la base de datos por AJAX y pintar la Grid
 */
function generarReporte() {
    Swal.fire({
        title: 'El reporte se está generando...',
        html: '<div class="gear-icon">⚙️</div><br><span class="text-muted">Analizando diferencias de inventario, por favor espere.</span>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            
            let formData = new FormData();
            formData.append('action', 'generar_reporte');

            fetch('controllers/IncidentReportController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    dataCed = data.dataCed;
                    dataEbay = data.dataEbay;

                    // Actualizar Badges
                    document.getElementById('badge-ced').innerText = dataCed.length;
                    document.getElementById('badge-ebay').innerText = dataEbay.length;

                    // Llenar Grid Ced Commerce
                    let htmlCed = '';
                    if(dataCed.length > 0) {
                        dataCed.forEach(item => {
                            htmlCed += `<tr><td class="fw-bold">${item.sku}</td><td>${item.title}</td><td class="text-center text-success fw-bold">${item.inventory}</td></tr>`;
                        });
                    } else {
                        htmlCed = '<tr><td colspan="3" class="text-center py-4">Todo está sincronizado correctamente. No hay incidencias.</td></tr>';
                    }
                    document.getElementById('tbody-ced').innerHTML = htmlCed;

                    // Llenar Grid eBay
                    let htmlEbay = '';
                    if(dataEbay.length > 0) {
                        dataEbay.forEach(item => {
                            htmlEbay += `<tr><td class="fw-bold">${item.sku}</td><td>${item.title}</td><td class="text-center text-success fw-bold">${item.inventory}</td></tr>`;
                        });
                    } else {
                        htmlEbay = '<tr><td colspan="3" class="text-center py-4">Todo está sincronizado correctamente. No hay incidencias.</td></tr>';
                    }
                    document.getElementById('tbody-ebay').innerHTML = htmlEbay;

                    // Mostrar contenedores y ocultar botón generar
                    document.getElementById('reporteContenedor').classList.remove('d-none');
                    document.getElementById('botoneraExportar').classList.remove('d-none');
                    document.getElementById('btnGenerar').classList.add('d-none');

                    Swal.close();
                }
            })
            .catch(error => {
                Swal.fire('Error', 'No se pudo generar el reporte. Fallo de red.', 'error');
                console.error('Error AJAX:', error);
            });
        }
    });
}

/**
 * Generador de Sufijo Dinámico para nombres de archivo
 */
function generarCorrelativo() {
    const fecha = new Date();
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');
    
    return `${año}${mes}${dia} - ${horas}${minutos}${segundos}`;
}

/**
 * Exportar a Excel
 */
function exportarExcel() {
    if(dataCed.length === 0 && dataEbay.length === 0) {
        Swal.fire('Atención', 'No hay datos para exportar.', 'warning');
        return;
    }

    const sufijo = generarCorrelativo();
    const nombreArchivo = `Reporte_Incidencias - ${sufijo}.xlsx`;

    const ws_ced = XLSX.utils.json_to_sheet(dataCed);
    const ws_ebay = XLSX.utils.json_to_sheet(dataEbay);
    const wb = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(wb, ws_ced, "no ced");
    XLSX.utils.book_append_sheet(wb, ws_ebay, "no ebay");
    XLSX.writeFile(wb, nombreArchivo);
}

/**
 * Exportar a Word
 */
function exportarWord() {
    if(dataCed.length === 0 && dataEbay.length === 0) {
        Swal.fire('Atención', 'No hay datos para exportar.', 'warning');
        return;
    }

    const sufijo = generarCorrelativo();
    const nombreArchivo = `Analisis_Sincronizacion - ${sufijo}.doc`;

    let filasCed = '';
    dataCed.forEach(row => {
        filasCed += `<tr><td>${row.sku}</td><td>${row.title}</td><td style="text-align:center;">${row.inventory}</td></tr>`;
    });

    let filasEbay = '';
    dataEbay.forEach(row => {
        filasEbay += `<tr><td>${row.sku}</td><td>${row.title}</td><td style="text-align:center;">${row.inventory}</td></tr>`;
    });

    const wordContent = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <title>Reporte de Incidencias</title>
            <style>
                body { font-family: 'Arial', sans-serif; }
                h1 { color: #0056b3; }
                h2 { color: #333333; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #999; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1>Reporte de Análisis de Sincronización</h1>
            <p><strong>Fecha de Generación:</strong> ${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</p>
            
            <h2>Explicación de Discrepancias</h2>
            <p>Las siguientes tablas detallan los productos que poseen inventario físico activo (mayor a cero) en la tienda matriz (Shopify), pero que <strong>no se encuentran registrados</strong> en los canales de venta correspondientes (Ced Commerce o eBay).</p>

            <h2>1. Tabla de productos que no están en Ced Commerce</h2>
            <table>
                <thead><tr><th>SKU</th><th>Title (Shopify)</th><th>Inventario</th></tr></thead>
                <tbody>${filasCed || '<tr><td colspan="3" style="text-align:center;">Sin incidencias</td></tr>'}</tbody>
            </table>

            <br><br>

            <h2>2. Tabla de productos que no están en eBay</h2>
            <table>
                <thead><tr><th>SKU</th><th>Title (Shopify)</th><th>Inventario</th></tr></thead>
                <tbody>${filasEbay || '<tr><td colspan="3" style="text-align:center;">Sin incidencias</td></tr>'}</tbody>
            </table>
        </body>
        </html>
    `;

    const blob = new Blob(['\ufeff', wordContent], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    link.href = url;
    link.download = nombreArchivo;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}