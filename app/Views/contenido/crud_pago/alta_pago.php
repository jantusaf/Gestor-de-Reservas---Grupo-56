<div class="pago-wrapper">
    <div class="pago-card">
        <h2 class="pago-title">Registrar Pago</h2>
        <p class="pago-subtitle">Completá los datos del pago</p>

        <!-- Mensajes de error o éxito -->
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('pago/guardar') ?>" method="post">
            <!-- Reserva asociada -->
            <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">

            <!-- Monto -->
            <div class="form-group-modern">
                <input type="number" step="0.01" name="monto_total" 
                       value="<?= $reserva['monto'] ?>" required>
                <label>Monto a pagar</label>
            </div>

            <!-- Medio de pago -->
            <div class="form-group-modern">
                <select name="id_medio_pago" required>
                    <option value="" disabled selected></option>
                    <?php foreach($medios as $m): ?>
                        <option value="<?= $m['id_medio_pago'] ?>">
                            <?= $m['nombre_medio_pago'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Medio de pago</label>
            </div>

            <button type="submit" class="btn-login">Confirmar Pago</button>
        </form>

        <div class="login-footer mt-3">
             <a href="<?= site_url('pago/listar') ?>" class="btn-action edit">Ver listado de pagos</a>
        </div>
    </div>
</div>
