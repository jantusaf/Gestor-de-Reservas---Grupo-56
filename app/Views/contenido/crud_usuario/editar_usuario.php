<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Editar Usuario</h2>
        <p class="reserva-subtitle">Modificá los datos del usuario</p>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php $errores = session()->getFlashdata('errors') ?? []; ?>

        <form action="<?= site_url('usuario/actualizar/'.$usuario['id_usuario']) ?>" method="post">

            <!-- DNI + FECHA -->
            <div class="form-row-2">
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="dni" value="<?= esc($persona['dni']) ?>">
                        <label class="label-active">DNI</label>
                    </div>
                    <?php if (!empty($errores['dni'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['dni']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="date" name="fecha_nacimiento" value="<?= esc($persona['fecha_nacimiento']) ?>">
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
                        <input type="text" name="nombre" value="<?= esc($persona['nombre']) ?>">
                        <label class="label-active">Nombre</label>
                    </div>
                    <?php if (!empty($errores['nombre'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['nombre']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="apellido" value="<?= esc($persona['apellido']) ?>">
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
                        <input type="text" name="calle" value="<?= esc($persona['calle']) ?>">
                        <label class="label-active">Calle</label>
                    </div>
                    <?php if (!empty($errores['calle'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['calle']) ?></small>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="form-group-modern">
                        <input type="text" name="altura" value="<?= esc($persona['altura']) ?>">
                        <label class="label-active">Altura</label>
                    </div>
                    <?php if (!empty($errores['altura'])): ?>
                        <small class="text-danger d-block mb-2"><?= esc($errores['altura']) ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TELÉFONO -->
            <div class="form-group-modern">
                <input type="text" name="telefono" value="<?= esc($persona['telefono']) ?>">
                <label class="label-active">Teléfono</label>
            </div>
            <?php if (!empty($errores['telefono'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['telefono']) ?></small>
            <?php endif; ?>

            <!-- NOMBRE DE USUARIO -->
            <div class="form-group-modern">
                <input type="text" name="nombre_usuario" value="<?= esc($usuario['nombre_usuario']) ?>">
                <label class="label-active">Nombre de Usuario</label>
            </div>
            <?php if (!empty($errores['nombre_usuario'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['nombre_usuario']) ?></small>
            <?php endif; ?>

            <!-- ESTADO -->
            <div class="form-group-modern">
                <select name="estado_usuario">
                    <option value="activo"   <?= $usuario['estado_usuario'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $usuario['estado_usuario'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <label class="label-active">Estado</label>
            </div>
            <?php if (!empty($errores['estado_usuario'])): ?>
                <small class="text-danger d-block mb-2"><?= esc($errores['estado_usuario']) ?></small>
            <?php endif; ?>

            <button type="submit" class="btn-login">Guardar cambios</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= site_url('usuario/listar') ?>">Volver al listado de usuarios</a>
        </div>

    </div>
</div>
