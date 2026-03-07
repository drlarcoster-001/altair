<?php
/**
 * Modulo: Gestión de Incidencias
 * Archivo: /views/includes/sidebar_gestion.php
 * Proposito: Menú lateral de opciones para el módulo de incidencias.
 * Version: 0.0.7 - Ajuste exacto de opciones solicitadas.
 */
?>
<div class="bg-dark text-white p-3" style="min-width: 250px; min-height: 100vh;">
    <h5 class="text-info border-bottom pb-2">Gestión de Incidencias</h5>
    <ul class="nav flex-column mt-3">
        <li class="nav-item mb-1">
            <a href="index.php?route=incidencias&action=index" class="nav-link text-white">🏠 Inicio</a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?route=incidencias&action=cargar" class="nav-link text-white">📥 Cargar Archivos</a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?route=incidencias&action=procesar" class="nav-link text-white">⚙️ Procesar Archivos</a>
        </li>
        <li class="nav-item mb-1">
            <a href="index.php?route=incidencias&action=reportes" class="nav-link text-white">📋 Generar Reportes</a>
        </li>
        <li class="nav-item mt-5">
            <a href="index.php" class="nav-link text-warning small">← Volver al Dashboard</a>
        </li>
    </ul>
</div>