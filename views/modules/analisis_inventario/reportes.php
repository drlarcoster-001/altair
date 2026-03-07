<?php
/**
 * Modulo: Análisis de Inventario
 * archivo: views/modules/analisis_inventario/reportes.php
 * Proposito: Interfaz de usuario para accionar el reinicio de tabla y carga modular de inventarios.
 * Version: 1.1.3 - Botón de generación con limpieza automática y carga secuencial de datos.
 */
?>

<div class="container-fluid">
    <div class="card shadow-sm border-start border-danger border-4">
        <div class="card-body">
            <h4 class="text-dark fw-bold">Análisis Maestro de Inventario</h4>
            <p>Al presionar <b>Generar Reporte</b>, la tabla de discrepancias se borrará por completo y se iniciará la carga secuencial.</p>
            
            <button class="btn btn-danger fw-bold px-4" onclick="generarReporteInventario()">
                ⚙️ Generar Reporte
            </button>
            
            <a href="index.php?route=analisis_inventario&action=index" class="btn btn-secondary px-4">Volver</a>
        </div>
    </div>
</div>