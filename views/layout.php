<?php
/**
 * Modulo: Vistas Principales
 * Archivo: /views/layout.php
 * Proposito: Plantilla maestra que ensambla el menú, contenido, recursos y breadcrumbs.
 * Version: 0.0.3 - Implementación de Breadcrumbs dinámicos y estructura completa.
 */

// Lógica para formatear los nombres en el Breadcrumb
$nombreModulo = "Dashboard";
if ($route == 'incidencias') {
    $nombreModulo = "Gestión de Incidencias";
} elseif ($route == 'inventario') {
    $nombreModulo = "Análisis de Inventario";
}

$nombreAccion = "Inicio";
if ($action == 'cargar') {
    $nombreAccion = "Cargar Archivos";
} elseif ($action == 'procesar') {
    $nombreAccion = "Procesar Archivos";
} elseif ($action == 'reportes') {
    $nombreAccion = "Generar Reportes";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Altair Amarellus - Sistema Integral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/altair_general.css">
    <link rel="stylesheet" href="<?php echo $custom_css; ?>">
</head>
<body>
    
    <?php include "views/includes/topnav.php"; ?>

    <div class="d-flex wrapper-principal">
        
        <?php if($sidebar != "none") include $sidebar; ?>

        <div id="main-content" class="container-fluid p-4">
            
            <nav aria-label="breadcrumb" class="mb-4 bg-light p-2 rounded shadow-sm">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="index.php" class="text-decoration-none">🏠 Inicio</a>
                    </li>
                    
                    <?php if($route != 'dashboard'): ?>
                        <li class="breadcrumb-item">
                            <a href="index.php?route=<?php echo $route; ?>&action=index" class="text-decoration-none">
                                <?php echo $nombreModulo; ?>
                            </a>
                        </li>
                        <?php if($action != 'index'): ?>
                            <li class="breadcrumb-item active fw-bold" aria-current="page">
                                <?php echo $nombreAccion; ?>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ol>
            </nav>
            <?php 
                // Evitamos errores si el archivo físico aún no existe
                if (file_exists($view)) {
                    include $view; 
                } else {
                    echo "<div class='alert alert-warning'>La vista <strong>{$view}</strong> aún no ha sido creada.</div>";
                }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="assets/js/altair_general.js"></script>
    
    <script>
        // Verificamos si el script existe antes de intentar cargarlo para evitar errores 404 en consola
        let jsFile = "<?php echo $custom_js; ?>";
        let script = document.createElement('script');
        script.src = jsFile;
        document.body.appendChild(script);
    </script>
</body>
</html>