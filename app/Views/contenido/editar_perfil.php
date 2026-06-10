<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Editar Mi Perfil</h2>
        <p class="reserva-subtitle">Modificá tus datos (el DNI no se puede cambiar)</p>

        <?php $errores = session()->getFlashdata('errors') ?? []; ?>

        <form action="<?= site_url('usuario/actualizar') ?>" method="post">

            <!-- DNI (solo lectura) + FECHA -->
            <div class="form-row-2">
                <div>
                    <div class="form-group-modern">
                        <input type="text" value="<?= esc($persona['dni']) ?>" readonly>
                        <label class="label-active">DNI (no editable)</label>
                    </div>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="date" name="fecha_nacimiento" value="<?= esc(old('fecha_nacimiento', $persona['fecha_nacimiento'])) ?>">
                        <label class="label-active">Fecha de Nacimiento</label>
                    </div>
                    <?php if (!empty($errores['fecha_nacimiento'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['fecha_nacimiento']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- NOMBRE + APELLIDO -->
            <div class="form-row-2">
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="nombre" value="<?= esc(old('nombre', $persona['nombre'])) ?>">
                        <label class="label-active">Nombre</label>
                    </div>
                    <?php if (!empty($errores['nombre'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['nombre']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="apellido" value="<?= esc(old('apellido', $persona['apellido'])) ?>">
                        <label class="label-active">Apellido</label>
                    </div>
                    <?php if (!empty($errores['apellido'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['apellido']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CALLE + ALTURA -->
            <div class="form-row-2">
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="calle" value="<?= esc(old('calle', $persona['calle'])) ?>">
                        <label class="label-active">Calle</label>
                    </div>
                    <?php if (!empty($errores['calle'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['calle']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="altura" value="<?= esc(old('altura', $persona['altura'])) ?>">
                        <label class="label-active">Altura</label>
                    </div>
                    <?php if (!empty($errores['altura'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['altura']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TELÉFONO -->
            <div class="form-group-modern">
                <input type="text" name="telefono" value="<?= esc(old('telefono', $persona['telefono'])) ?>">
                <label class="label-active">Teléfono</label>
            </div>
            <?php if (!empty($errores['telefono'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['telefono']) ?></small>
            <?php endif; ?>

            <!-- NOMBRE DE USUARIO -->
            <div class="form-group-modern">
                <input type="text" name="nombre_usuario" value="<?= esc(old('nombre_usuario', $usuario['nombre_usuario'])) ?>">
                <label class="label-active">Nombre de Usuario</label>
            </div>
            <?php if (!empty($errores['nombre_usuario'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['nombre_usuario']) ?></small>
            <?php endif; ?>

            <button type="submit" class="btn-login">Guardar cambios</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= site_url('usuario/perfil') ?>">Volver a mi perfil</a>
        </div>

    </div>
</div>
