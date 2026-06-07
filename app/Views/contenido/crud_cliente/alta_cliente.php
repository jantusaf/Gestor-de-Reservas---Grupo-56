<?php // Vista de alta de cliente ?>

<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Alta de Cliente</h2>
        <p class="reserva-subtitle">Completá los datos</p>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php $errores = session()->getFlashdata('errors') ?? []; ?>

        <form action="<?= base_url('/cliente/alta'); ?>" method="post">

            <!-- FILA 1: DNI + FECHA -->
            <div class="form-row-2">
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="dni" id="dni" value="<?= old('dni') ?>">
                        <label for="dni">DNI</label>
                    </div>
                    <?php if (!empty($errores['dni'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['dni']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="<?= old('fecha_nacimiento') ?>">
                        <label for="fecha_nacimiento" class="label-active">Fecha de Nacimiento</label>
                    </div>
                    <?php if (!empty($errores['fecha_nacimiento'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['fecha_nacimiento']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FILA 2: NOMBRE -->
            <div class="form-group-modern">
                <input type="text" name="nombre" id="nombre" value="<?= old('nombre') ?>">
                <label for="nombre">Nombre</label>
            </div>
            <?php if (!empty($errores['nombre'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['nombre']) ?></small>
            <?php endif; ?>

            <!-- FILA 3: APELLIDO -->
            <div class="form-group-modern">
                <input type="text" name="apellido" id="apellido" value="<?= old('apellido') ?>">
                <label for="apellido">Apellido</label>
            </div>
            <?php if (!empty($errores['apellido'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['apellido']) ?></small>
            <?php endif; ?>

            <!-- FILA 4: CALLE + ALTURA -->
            <div class="form-row-2">
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="calle" id="calle" value="<?= old('calle') ?>">
                        <label for="calle">Calle</label>
                    </div>
                    <?php if (!empty($errores['calle'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['calle']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="altura" id="altura" value="<?= old('altura') ?>">
                        <label for="altura">Altura</label>
                    </div>
                    <?php if (!empty($errores['altura'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['altura']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FILA 5: EMAIL -->
            <div class="form-group-modern">
                <input type="email" name="email" id="email" value="<?= old('email') ?>">
                <label for="email">Email</label>
            </div>
            <?php if (!empty($errores['email'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['email']) ?></small>
            <?php endif; ?>

            <!-- FILA 6: TELÉFONO (opcional) -->
            <div class="form-group-modern">
                <input type="text" name="telefono" id="telefono" value="<?= old('telefono') ?>">
                <label for="telefono">Teléfono</label>
            </div>
            <?php if (!empty($errores['telefono'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['telefono']) ?></small>
            <?php endif; ?>

            <button type="submit" class="btn-login">Guardar Cliente</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= base_url('/cliente/listar'); ?>">Ver listado de clientes</a>
        </div>

    </div>
</div>
