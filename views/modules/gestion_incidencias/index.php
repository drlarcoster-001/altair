<?php
/**
 * Modulo: Gestión de Incidencias
 * Archivo: /views/modules/gestion_incidencias/index.php
 * Proposito: Pantalla principal del módulo con tarjetas de acceso rápido a las opciones.
 * Version: 0.0.3 - Archivo completo con tarjetas actualizadas.
 */
?>
<div class="container mt-4">
    <h2 class="mb-4 text-primary">Gestión de Incidencias ✈️</h2>
    <hr class="mb-5">
    
    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm card-modulo border-primary" onclick="window.location.href='index.php?route=incidencias&action=cargar'" style="cursor: pointer;">
                <h4 class="text-primary fw-bold">Cargar Archivos</h4>
                <p class="text-muted small mt-2">Sube los archivos CSV de Shopify y eBay para comparar</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm card-modulo border-warning" onclick="window.location.href='index.php?route=incidencias&action=procesar'" style="cursor: pointer;">
                <h4 class="text-warning fw-bold">Procesar Archivos</h4>
                <p class="text-muted small mt-2">Ejecutar la comparativa entre los productos cargados</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 text-center shadow-sm card-modulo border-info" onclick="window.location.href='index.php?route=incidencias&action=reportes'" style="cursor: pointer;">
                <h4 class="text-info fw-bold">Generar Reportes</h4>
                <p class="text-muted small mt-2">Descargar los reportes de las diferencias encontradas</p>
            </div>
        </div>
    </div>
</div>