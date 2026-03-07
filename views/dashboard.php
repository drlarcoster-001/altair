<div class="container py-5 text-center">
    <h1 class="mb-5">Bienvenido al Sistema Altair</h1>
    <div class="row justify-content-center g-4">
        
        <div class="col-md-5">
            <div class="card shadow-lg border-0 h-100" style="cursor:pointer;" onclick="confirmNav('incidencias')">
                <div class="card-body py-5 text-primary">
                    <h2 class="fw-bold">Gestión de Incidencias</h2>
                    <p class="text-muted">Tickets y soporte técnico</p>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-lg border-0 h-100" style="cursor:pointer;" onclick="confirmNav('inventario')">
                <div class="card-body py-5 text-success">
                    <h2 class="fw-bold">Análisis de Inventario</h2>
                    <p class="text-muted">Control de stock y activos</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function confirmNav(target) {
    Swal.fire({
        title: 'Accediendo...',
        text: 'Cargando módulo de ' + target,
        icon: 'info',
        timer: 800,
        showConfirmButton: false,
        didClose: () => {
            window.location.href = 'index.php?route=' + target;
        }
    });
}
</script>