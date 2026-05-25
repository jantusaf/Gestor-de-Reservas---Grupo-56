<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Editar Cliente</h2>
        <p class="reserva-subtitle">Modificá los datos del cliente</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if(isset($validation)): ?>
            <div class="alert alert-warning"><?= $validation->listErrors() ?></div>
        <?php endif; ?>

        <form action="<?= site_url('cliente/actualizar/'.$cliente['id_cliente']) ?>" method="post">

            <!-- FILA 1: DNI + FECHA -->
            <div class="form-row-2">
                <div class="form-group-modern">
                    <input type="text" name="dni" value="<?= esc($persona['dni']) ?>" required>
                    <label class="label-active">DNI</label>
                </div>
                <div class="form-group-modern">
                    <input type="date" name="fecha_nacimiento" value="<?= esc($persona['fecha_nacimiento']) ?>" required>
                    <label class="label-active">Fecha de Nacimiento</label>
                </div>
            </div>

            <!-- FILA 2: NOMBRE + APELLIDO -->
            <div class="form-row-2">
                <div class="form-group-modern">
                    <input type="text" name="nombre" value="<?= esc($persona['nombre']) ?>" required>
                    <label class="label-active">Nombre</label>
                </div>
                <div class="form-group-modern">
                    <input type="text" name="apellido" value="<?= esc($persona['apellido']) ?>" required>
                    <label class="label-active">Apellido</label>
                </div>
            </div>

            <!-- FILA 3: CALLE + ALTURA -->
            <div class="form-row-2">
                <div class="form-group-modern">
                    <input type="text" name="calle" value="<?= esc($persona['calle']) ?>" required>
                    <label class="label-active">Calle</label>
                </div>
                <div class="form-group-modern">
                    <input type="text" name="altura" value="<?= esc($persona['altura']) ?>" required>
                    <label class="label-active">Altura</label>
                </div>
            </div>

            <!-- EMAIL -->
            <div class="form-group-modern">
                <input type="email" name="email" value="<?= esc($cliente['email']) ?>" required>
                <label class="label-active">Email</label>
            </div>

            <!-- TELÉFONO -->
            <div class="form-group-modern">
                <input type="text" name="telefono" value="<?= esc($persona['telefono']) ?>">
                <label class="label-active">Teléfono</label>
            </div>

            <!-- ESTADO -->
            <div class="form-group-modern">
                <select name="estado_cliente" required>
                    <option value="activo"   <?= $cliente['estado_cliente'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $cliente['estado_cliente'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <label class="label-active">Estado</label>
            </div>

            <button type="submit" class="btn-login">Guardar cambios</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= site_url('cliente/listar') ?>">Volver al listado de clientes</a>
        </div>

    </div>
</div>
