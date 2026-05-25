<?php // Vista de alta de cliente ?>

<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Alta de Cliente</h2>
        <p class="reserva-subtitle">Completá los datos</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(isset($validation)): ?>
            <div class="alert alert-warning"><?= $validation->listErrors() ?></div>
        <?php endif; ?>

        <form action="<?= base_url('/cliente/alta'); ?>" method="post">

            <!-- FILA 1: DNI + FECHA -->
            <div class="form-row-2">
                <div class="form-group-modern">
                    <input type="text" name="dni" id="dni" required>
                    <label for="dni">DNI</label>
                </div>
                <div class="form-group-modern">
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
                    <label for="fecha_nacimiento" class="label-active">Fecha de Nacimiento</label>
                </div>
            </div>

            <!-- FILA 2: NOMBRE -->
            <div class="form-group-modern">
                <input type="text" name="nombre" id="nombre" required>
                <label for="nombre">Nombre</label>
            </div>

            <!-- FILA 3: APELLIDO -->
            <div class="form-group-modern">
                <input type="text" name="apellido" id="apellido" required>
                <label for="apellido">Apellido</label>
            </div>

            <!-- FILA 4: CALLE + ALTURA -->
            <div class="form-row-2">
                <div class="form-group-modern">
                    <input type="text" name="calle" id="calle" required>
                    <label for="calle">Calle</label>
                </div>
                <div class="form-group-modern">
                    <input type="text" name="altura" id="altura" required>
                    <label for="altura">Altura</label>
                </div>
            </div>

            <!-- FILA 5: EMAIL -->
            <div class="form-group-modern">
                <input type="email" name="email" id="email" required>
                <label for="email">Email</label>
            </div>

            <!-- FILA 6: TELÉFONO (opcional) -->
            <div class="form-group-modern">
                <input type="text" name="telefono" id="telefono">
                <label for="telefono">Teléfono</label>
            </div>

            <button type="submit" class="btn-login">Guardar Cliente</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= base_url('/cliente/listar'); ?>">Ver listado de clientes</a>
        </div>

    </div>
</div>