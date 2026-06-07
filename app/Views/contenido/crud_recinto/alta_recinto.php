<?php // Vista de creación de recinto ?>

<div class="reserva-wrapper">
    <div class="reserva-card">

        <h2 class="reserva-title">Nuevo Recinto</h2>
        <p class="reserva-subtitle">Completá los datos</p>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php $errores = session()->getFlashdata('errors') ?? []; ?>

        <form action="<?= base_url('/recinto/guardar'); ?>" method="post">
            <div class="form-group-modern">
                <input type="text" name="tarifa" value="<?= old('tarifa') ?>">
                <label>Tarifa por Hora</label>
            </div>
            <?php if (!empty($errores['tarifa'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['tarifa']) ?></small>
            <?php endif; ?>

            <div class="form-group-modern">
                <select name="id_tipo_recinto">
                    <option value="" disabled selected></option>
                    <?php foreach($tipos as $tipo): ?>
                        <option value="<?= $tipo['id_tipo_recinto']; ?>" <?= old('id_tipo_recinto') == $tipo['id_tipo_recinto'] ? 'selected' : '' ?>>
                            <?= $tipo['nombre_tipo_recinto']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Tipo de Recinto</label>
            </div>
            <?php if (!empty($errores['id_tipo_recinto'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['id_tipo_recinto']) ?></small>
            <?php endif; ?>

            <div class="form-group-modern">
                <input type="text" name="descripcion" value="<?= old('descripcion') ?>">
                <label>Descripción</label>
            </div>
            <?php if (!empty($errores['descripcion'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['descripcion']) ?></small>
            <?php endif; ?>

            <button type="submit" class="btn-login">Agregar</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= base_url('/recinto'); ?>">Volver al listado de recintos</a>
        </div>

    </div>
</div>
