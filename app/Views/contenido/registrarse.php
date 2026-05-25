<?php // Vista de registro de usuario ?>

<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Crear cuenta</h2>
        <p class="reserva-subtitle">Completá tus datos para registrarte</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(isset($validation)): ?>
            <div class="alert alert-warning"><?= $validation->listErrors() ?></div>
        <?php endif; ?>

        <form action="<?= base_url('/registrarse/guardar') ?>" method="post">

            <!-- DNI + FECHA -->
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

            <!-- NOMBRE + APELLIDO -->
            <div class="form-row-2">
                <div class="form-group-modern">
                    <input type="text" name="nombre" id="nombre" required>
                    <label for="nombre">Nombre</label>
                </div>
                <div class="form-group-modern">
                    <input type="text" name="apellido" id="apellido" required>
                    <label for="apellido">Apellido</label>
                </div>
            </div>

            <!-- CALLE + ALTURA -->
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

            <!-- TELÉFONO -->
            <div class="form-group-modern">
                <input type="text" name="telefono" id="telefono">
                <label for="telefono">Teléfono</label>
            </div>

            <!-- NOMBRE DE USUARIO -->
            <div class="form-group-modern">
                <input type="text" name="usuario" id="usuario" required>
                <label for="usuario">Nombre de Usuario</label>
            </div>

            <!-- CONTRASEÑA -->
            <div class="form-group-modern">
                <input type="password" name="password" id="password" required>
                <label for="password">Contraseña</label>
            </div>

            <button type="submit" class="btn-login">Registrarse</button>
        </form>

        <div class="login-footer mt-3">
            ¿Ya tenés cuenta? <a href="<?= base_url('/login') ?>">Iniciá sesión aquí</a>
        </div>

    </div>
</div>
