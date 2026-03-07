<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Altair Amarellus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        #content { width: 100%; padding: 20px; min-height: 100vh; transition: all 0.3s; }
        .sidebar { min-width: 250px; max-width: 250px; min-height: 100vh; }
    </style>
</head>
<body>

    <?php include "includes/topnav.php"; ?>

    <div class="wrapper">
        <?php if($sidebar != "none") include $sidebar; ?>

        <div id="content">
            <?php 
                // Lógica de enrutamiento de contenido
                if($modulo == "dashboard"){
                    include "views/dashboard.php";
                } elseif($modulo == "incidencias"){
                    echo "<h2>Módulo: Gestión de Incidencias</h2>";
                } elseif($modulo == "inventario"){
                    echo "<h2>Módulo: Análisis de Inventario</h2>";
                }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>