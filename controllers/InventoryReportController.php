<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: controllers/InventoryReportController.php
 * Proposito: Exportación corporativa. Genera el reporte Word con Tablas 1 a 5, omitiendo registros OK y formateando SKUs en Excel como texto.
 * Version: 1.1.3 - Inclusión de Tabla de Reposición y limpieza de búfer para comunicación JSON.
 */

if(!class_exists('InventoryReportModel')) { require_once __DIR__ . "/../models/InventoryReportModel.php"; }
if(!class_exists('InventoryBatchModel')) { require_once __DIR__ . "/../models/InventoryBatchModel.php"; }

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] == 'exportar_reporte') {
        
        $filtros = [
            'sku' => $_GET['sku'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'causa' => $_GET['causa'] ?? '',
            'prioridad' => $_GET['prioridad'] ?? ''
        ];

        $lote = InventoryBatchModel::mdlObtenerUltimaCarga("inventory_batches");
        $filename = "AnalsisInventario-" . date('Ymd-His');

        if ($_GET['formato'] == 'excel') {
            $datos = InventoryReportModel::mdlObtenerReporteGrid($filtros, 100000, 0);
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=" . $filename . ".xls");
            echo "<table border='1'><tr style='background:#000; color:#fff;'><th>SKU</th><th>Inv 1</th><th>Ebay 1</th><th>Inv 2</th><th>Ebay 2</th><th>Disc 1</th><th>Disc 2</th><th>Estado</th><th>Causa</th><th>Prioridad</th></tr>";
            foreach ($datos as $f) {
                echo "<tr><td style='mso-number-format:\"\\@\"'>{$f['sku']}</td><td>{$f['inv1']}</td><td>{$f['ebay1']}</td><td>{$f['inv2']}</td><td>{$f['ebay2']}</td><td>{$f['disc_inv1']}</td><td>{$f['disc_inv2']}</td><td>{$f['estado']}</td><td>{$f['causa']}</td><td>{$f['prioridad']}</td></tr>";
            }
            echo "</table>";

        } elseif ($_GET['formato'] == 'word') {
            header("Content-type: application/vnd.ms-word");
            header("Content-Disposition: attachment; filename=" . $filename . ".doc");
            
            $indicadores = InventoryReportModel::mdlObtenerIndicadores();
            $oversell = InventoryReportModel::mdlObtenerPorEstado('RIESGO OVERSELL');
            $ventas = InventoryReportModel::mdlObtenerPorEstado('VENTA');
            $noSinc = InventoryReportModel::mdlObtenerPorEstado('DESINCRONIZADO');
            $reposicion = InventoryReportModel::mdlObtenerPorEstado('REPOSICION');

            echo "<html><meta charset='utf-8'><body>";
            echo "<h2 style='text-align:center;'>ESTATUS DE INVENTARIO Y GESTIÓN DE CANALES</h2>";
            echo "<p style='text-align:center;'>Fecha Inicio: {$lote['fecha_inicio']} &nbsp;&nbsp;&nbsp;&nbsp; Fecha Final: {$lote['fecha_fin']}</p>";
            
            echo "<h3>Tabla 1.- Indicadores de Gestión</h3>";
            echo "<table border='1' style='width:60%; border-collapse:collapse;'>";
            echo "<tr style='background:#f2f2f2;'><th>ESTADO</th><th>CANTIDAD</th><th>%</th></tr>";
            foreach($indicadores as $r) { 
                echo "<tr><td>{$r['estado']}</td><td align='center'>{$r['cantidad']}</td><td align='center'>{$r['porcentaje']}%</td></tr>"; 
            }
            echo "</table>";

            echo "<h4>Glosario de Términos (Unidades Físicas):</h4><ul>";
            echo "<li><b>RIESGO OVERSELL:</b> Unidades publicadas en eBay cuyo stock en Shopify es 0.</li>";
            echo "<li><b>VENTA:</b> Unidades físicas cuya salida fue confirmada mediante la baja de stock en eBay.</li>";
            echo "<li><b>REPOSICIÓN:</b> Unidades nuevas ingresadas al sistema detectadas como incremento de inventario.</li>";
            echo "<li><b>DESINCRONIZADO:</b> SKUs con diferencias de paridad entre canales (Excluyendo ventas).</li>";
            echo "<li><b>OK:</b> SKUs en total paridad y sin movimientos en el periodo.</li></ul>";

            // Tablas de detalle (Solo si no es OK)
            echo "<h3>Tabla 2.- ALERTA: RIESGO DE OVERSELL (STOCK AGOTADO)</h3>";
            if(count($oversell) > 0){
                echo "<table border='1' style='width:100%; border-collapse:collapse;'><tr><th>SKU</th><th>Inv Shopify 2</th><th>Inv Ebay 2</th></tr>";
                foreach($oversell as $o) { echo "<tr><td>{$o['sku']}</td><td align='center'>{$o['inv_shopify2']}</td><td align='center'>{$o['inv_ebay2']}</td></tr>"; }
                echo "</table>";
            } else { echo "<p><i>No hay discrepancias de Riesgo de Oversell en este periodo.</i></p>"; }

            echo "<h3>Tabla 3.- RELACIÓN DE VENTAS CONFORMADAS</h3>";
            if(count($ventas) > 0){
                echo "<table border='1' style='width:100%; border-collapse:collapse;'><tr><th>SKU</th><th>Inv Shp 1</th><th>Eby 1</th><th>Inv Shp 2</th><th>Eby 2</th><th>Vendidos</th></tr>";
                foreach($ventas as $v) { echo "<tr><td>{$v['sku']}</td><td align='center'>{$v['inv_shopify1']}</td><td align='center'>{$v['inv_ebay1']}</td><td align='center'>{$v['inv_shopify2']}</td><td align='center'>{$v['inv_ebay2']}</td><td align='center'>{$v['disc_inv1']}</td></tr>"; }
                echo "</table>";
                echo "<p><b>Análisis de Movimiento:</b> La columna 'Vendidos' refleja las unidades físicas que salieron del inventario.</p>";
            } else { echo "<p><i>No hay registros de ventas conformadas en este periodo.</i></p>"; }

            echo "<h3>Tabla 4.- ALERTA: VENTAS NO SINCRONIZADAS</h3>";
            if(count($noSinc) > 0){
                echo "<table border='1' style='width:100%; border-collapse:collapse;'><tr><th>SKU</th><th>Inv Shp 1</th><th>Eby 1</th><th>Inv Shp 2</th><th>Eby 2</th><th>Estado</th></tr>";
                foreach($noSinc as $n) { echo "<tr><td>{$n['sku']}</td><td align='center'>{$n['inv_shopify1']}</td><td align='center'>{$n['inv_ebay1']}</td><td align='center'>{$n['inv_shopify2']}</td><td align='center'>{$n['inv_ebay2']}</td><td align='center'>{$n['estado']}</td></tr>"; }
                echo "</table>";
            } else { echo "<p><i>No hay discrepancias de ventas no sincronizadas en este periodo.</i></p>"; }

            echo "<h3>Tabla 5.- RELACIÓN DE REPOSICIÓN DE INVENTARIO</h3>";
            if(count($reposicion) > 0){
                echo "<table border='1' style='width:100%; border-collapse:collapse;'><tr><th>SKU</th><th>Inv Shp 1</th><th>Eby 1</th><th>Inv Shp 2</th><th>Eby 2</th><th>Incremento</th></tr>";
                foreach($reposicion as $rep) { 
                    $inc = abs($rep['disc_inv1']);
                    echo "<tr><td>{$rep['sku']}</td><td align='center'>{$rep['inv_shopify1']}</td><td align='center'>{$rep['inv_ebay1']}</td><td align='center'>{$rep['inv_shopify2']}</td><td align='center'>{$rep['inv_ebay2']}</td><td align='center'>{$inc}</td></tr>"; 
                }
                echo "</table>";
            } else { echo "<p><i>No hay registros de reposición de inventario en este periodo.</i></p>"; }

            echo "</body></html>";
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    ob_clean();
    if ($_POST['action'] == 'generar_reporte') { echo json_encode(["status" => InventoryReportModel::mdlGenerarDiscrepancias()]); }
    elseif ($_POST['action'] == 'listar_reporte') {
        $filtros = ['sku' => $_POST['sku'], 'estado' => $_POST['estado'], 'causa' => $_POST['causa'], 'prioridad' => $_POST['prioridad']];
        echo json_encode(InventoryReportModel::mdlObtenerReporteGrid($filtros, $_POST['limit'], ($_POST['page']-1)*$_POST['limit']));
    }
    elseif ($_POST['action'] == 'limpiar_reporte') { echo json_encode(["status" => InventoryReportModel::mdlLimpiarReporte()]); }
    exit;
}