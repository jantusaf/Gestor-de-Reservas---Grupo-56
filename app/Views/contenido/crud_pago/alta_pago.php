<div class="reserva-wrapper">
    <div class="reserva-card">
        <h2 class="reserva-title">Registrar Pago</h2>
        <p class="reserva-subtitle">Completá los datos del pago</p>

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

        <?php $errores = session()->getFlashdata('errors') ?? []; ?>

        <form action="<?= site_url('pago/guardar') ?>" method="post">
            <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">

            <div class="form-group-modern">
                <input type="text" id="monto_total" value="$<?= number_format($reserva['monto'], 2) ?>" readonly>
                <label for="monto_total" class="label-active">Monto a pagar</label>
            </div>

            <div class="form-group-modern">
                <select name="id_medio_pago" id="id_medio_pago">
                    <option value="" disabled selected></option>
                    <?php foreach($medios as $m): ?>
                        <option value="<?= $m['id_medio_pago'] ?>">
                            <?= $m['nombre_medio_pago'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="id_medio_pago">Medio de pago</label>
            </div>
            <?php if (!empty($errores['id_medio_pago'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['id_medio_pago']) ?></small>
            <?php endif; ?>

            <button type="submit" class="btn-login">Confirmar Pago</button>
        </form>

        <div class="login-footer mt-3">

            <a href="<?= site_url('reserva/listar') ?>">Volver al listado de reservas</a>
            <br>
            <a href="<?= site_url('pago/listar') ?>">Ver listado de pagos</a>

        </div>
    </div>
</div>
