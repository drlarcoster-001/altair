<?php
/**
 * Modulo: Core / Routing
 * Archivo: /index.php
 * Proposito: Gestionar de forma dinámica la carga de la vista, el sidebar y los recursos (CSS/JS).
 * Version: 0.0.7 - Ruteo base para Dashboard, Incidencias e Inventario con soporte de Breadcrumbs.
 */

$route  = isset($_GET['route']) ? $_GET['route'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Configuración por defecto (Apunta al Dashboard)
$view       = "views/dashboard.php";
$sidebar    = "none";
$custom_css = "assets/css/dashboard.css";
$custom_js  = "assets/js/dashboard.js";

// Mapeo de Módulos (Rutas permitidas)
$modules = [
    'incidencias' => 'gestion_incidencias', 
    'inventario'  => 'analisis_inventario'
];

// Lógica de ruteo si la ruta solicitada existe en los módulos
if (array_key_exists($route, $modules)) {
    $folder     = $modules[$route];
    
    // Asignar el sidebar correspondiente
    if ($route == 'incidencias') {
        $sidebar = "views/includes/sidebar_gestion.php";
    } else {
        $sidebar = "views/includes/sidebar_analisis.php";
    }
    
    // Cargar vistas y recursos específicos por acción
    $view       = "views/modules/{$folder}/{$action}.php";
    $custom_css = "assets/css/{$folder}_{$action}.css";
    $custom_js  = "assets/js/{$folder}_{$action}.js";
}

// Renderizar el layout maestro
include "views/layout.php";
?>