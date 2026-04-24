<div class="container dashboard-container">

    <div class="row g-4 justify-content-center">

        <!-- RECINTOS -->
        <div class="col-md-6">
            <div class="card-dashboard card-blue h-100">
                <div class="text-center w-100">
                    <h3>Recintos</h3>
                    <p>Gestionar recintos del sistema</p>
                    <a href="<?= base_url('recinto') ?>" class="btn btn-custom">Ingresar</a>
                </div>
            </div>
        </div>

        <!-- NUEVO RECINTO -->
        <div class="col-md-6">
            <div class="card-dashboard card-green h-100">
                <div class="text-center w-100">
                    <h3>Nuevo Recinto</h3>
                    <p>Agregar un nuevo recinto</p>
                    <a href="<?= base_url('recinto/crear') ?>" class="btn btn-custom">Crear</a>
                </div>
            </div>
        </div>

        <!-- RESERVAS -->
        <div class="col-md-6">
            <div class="card-dashboard card-orange h-100">
                <div class="text-center w-100">
                    <h3>Reservas</h3>
                    <p>Generar reservas fácilmente</p>
                    <a href="<?= base_url('reserva/crear') ?>" class="btn btn-custom">Ir</a>
                </div>
            </div>
        </div>

        <!-- CLIENTES -->
        <div class="col-md-6">
            <div class="card-dashboard card-dark h-100">
                <div class="text-center w-100">
                    <h3>Clientes</h3>
                    <p>Administrar clientes</p>
                    <a href="<?= base_url('cliente/alta') ?>" class="btn btn-custom">Ir</a>
                </div>
            </div>
        </div>

    </div>

</div>