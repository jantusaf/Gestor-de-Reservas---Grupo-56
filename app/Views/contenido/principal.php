<div class="container dashboard-container">

    <div class="row g-4 justify-content-center">

        <!-- RECINTOS -->
        <div class="col-md-6">
            <div class="card-dashboard card-blue h-100">
                <div class="text-center w-100">
                    <h3><i class="bi bi-building dashboard-icon"></i> Recintos</h3>
                    <p>Gestionar recintos del sistema</p>
                    <div class="dashboard-btns">
                        <a href="<?= base_url('recinto/alta') ?>" class="btn btn-custom">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </a>
                        <a href="<?= base_url('recinto/listar') ?>" class="btn btn-custom">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGOS -->
        <div class="col-md-6">
            <div class="card-dashboard card-green h-100">
                <div class="text-center w-100">
                    <h3><i class="bi bi-credit-card dashboard-icon"></i>Historial de Pagos</h3>
                    <p>Historial de pagos registrados</p>
                    <div class="dashboard-btns">
                        <a href="<?= base_url('pago/listar') ?>" class="btn btn-custom">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESERVAS -->
        <div class="col-md-6">
            <div class="card-dashboard card-orange h-100">
                <div class="text-center w-100">
                    <h3><i class="bi bi-calendar-check dashboard-icon"></i> Reservas</h3>
                    <p>Generar reservas fácilmente</p>
                    <div class="dashboard-btns">
                        <a href="<?= base_url('reserva/crear') ?>" class="btn btn-custom">
                            <i class="bi bi-calendar-plus"></i> Reservar
                        </a>
                        <a href="<?= base_url('reserva/listar') ?>" class="btn btn-custom">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CLIENTES -->
        <div class="col-md-6">
            <div class="card-dashboard card-dark h-100">
                <div class="text-center w-100">
                    <h3><i class="bi bi-people dashboard-icon"></i> Clientes</h3>
                    <p>Administrar clientes</p>
                    <div class="dashboard-btns">
                        <a href="<?= base_url('cliente/alta') ?>" class="btn btn-custom">
                            <i class="bi bi-person-plus"></i> Agregar
                        </a>
                        <a href="<?= base_url('cliente/listar') ?>" class="btn btn-custom">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
