<?php
/**
 * Modulo: Dashboard Principal
 * Archivo: /views/dashboard.php
 * Proposito: Pantalla inicial de bienvenida con las tarjetas de acceso a los módulos.
 * Version: 0.0.1 - Interfaz de selección de módulos.
 */
?>
<div class="container mt-5">
    <h2 class="text-center mb-5">Seleccione un Módulo</h2>
    
    <div class="row justify-content-center g-4">
        <div class="col-md-5">
            <div class="card p-5 text-center shadow-sm card-modulo" onclick="navegarModulo('incidencias')">
                <h3 class="text-primary fw-bold">Gestión de Incidencias</h3>
                <p class="text-muted mt-2">Comparativa de productos Shopify vs eBay</p>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card p-5 text-center shadow-sm card-modulo" onclick="navegarModulo('inventario')">
                <h3 class="text-success fw-bold">Análisis de Inventario</h3>
                <p class="text-muted mt-2">Control y gestión de existencias</p>
            </div>
        </div>
    </div>
</div>