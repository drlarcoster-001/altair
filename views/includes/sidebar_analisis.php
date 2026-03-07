<?php
/**
 * Modulo: Análisis de Inventario
 * Archivo: /views/includes/sidebar_analisis.php
 * Proposito: Menú lateral de opciones para el módulo de inventario.
 * Version: 0.0.6 - 'Principal' apunta correctamente al index del módulo.
 */
?>
<div class="bg-dark text-white p-3" style="min-width: 250px; min-height: 100vh;">
    <h5 class="text-success border-bottom pb-2">Análisis de Inventario</h5>
    <ul class="nav flex-column mt-3">
        <li class="nav-item mb-1">
            <a href="index.php?route=inventario&action=index" class="nav-link text-white">🏠 Principal</a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?route=inventario&action=cargar" class="nav-link text-white">📥 Cargar Archivos</a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?route=inventario&action=procesar" class="nav-link text-white">⚙️ Procesar Archivos</a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?route=inventario&action=reportes" class="nav-link text-white">📋 Generar Reportes</a>
        </li>
        <li class="nav-item mt-5">
            <a href="index.php" class="nav-link text-warning small">← Volver al Dashboard</a>
        </li>
    </ul>
</div>