<div class="container dashboard-container">

    <div class="row g-4 justify-content-center">

        <!-- PAGOS -->
        <div class="col-md-6">
            <div class="card-dashboard card-green h-100">
                <div class="text-center w-100">
                    <h3><i class="bi bi-credit-card dashboard-icon"></i> Pagos</h3>
                    <p>Historial de pagos registrados</p>
                    <div class="dashboard-btns">
                        <a href="<?= base_url('pago/listar') ?>" class="btn btn-custom">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- USUARIOS -->
        <div class="col-md-6">
            <div class="card-dashboard card-dark h-100">
                <div class="text-center w-100">
                    <h3><i class="bi bi-person-gear dashboard-icon"></i> Usuarios</h3>
                    <p>Administrar usuarios del sistema</p>
                    <div class="dashboard-btns">
                        <a href="<?= base_url('usuario/alta') ?>" class="btn btn-custom">
                            <i class="bi bi-person-plus"></i> Agregar
                        </a>
                        <a href="<?= base_url('usuario/listar') ?>" class="btn btn-custom">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
