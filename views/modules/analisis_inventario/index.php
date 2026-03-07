<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: /views/modules/analisis_inventario/index.php
 * Proposito: Pantalla principal del módulo de inventario con tarjetas.
 * Version: 0.0.3 - Archivo completo con tarjetas actualizadas.
 */
?>
<div class="container mt-4">
    <h2 class="mb-4 text-success">Análisis de Inventario ✈️</h2>
    <hr class="mb-5">
    
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm card-modulo border-success" onclick="window.location.href='index.php?route=inventario&action=cargar'" style="cursor: pointer;">
                <h4 class="text-success fw-bold">Cargar Archivos</h4>
                <p class="text-muted small mt-2">Sube los datos y listados del inventario actual</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm card-modulo border-warning" onclick="window.location.href='index.php?route=inventario&action=procesar'" style="cursor: pointer;">
                <h4 class="text-warning fw-bold">Procesar Archivos</h4>
                <p class="text-muted small mt-2">Analizar y cruzar la información del stock físico y digital</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm card-modulo border-info" onclick="window.location.href='index.php?route=inventario&action=reportes'" style="cursor: pointer;">
                <h4 class="text-info fw-bold">Generar Reportes</h4>
                <p class="text-muted small mt-2">Obtener los reportes finales de las existencias</p>
            </div>
        </div>
    </div>
</div>