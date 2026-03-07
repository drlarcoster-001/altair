<?php
// Modulo: Core / Routing
// Archivo: /index.php

$modulo = isset($_GET['route']) ? $_GET['route'] : 'dashboard';

// Definir qué sidebar cargar según el módulo
$sidebar = "none";
if ($modulo == "incidencias") {
    $sidebar = "views/includes/sidebar_gestion.php";
} elseif ($modulo == "inventario") {
    $sidebar = "views/includes/sidebar_analisis.php";
}

// Cargar el Layout Principal
include "views/layout.php";
?>