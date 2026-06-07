<div class="reserva-wrapper">
    <div class="reserva-card">

        <h2 class="reserva-title">Editar Recinto</h2>
        <p class="reserva-subtitle">Modificá los datos del recinto</p>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= base_url('/recinto/actualizar/' . $recinto['id_recinto']) ?>" method="post">

            <div class="form-group-modern">
                <input type="text" name="Tarifa_por_Hora" value="<?= esc($recinto['tarifa']) ?>" >
                <label>Tarifa por Hora</label>
            </div>

            <div class="form-group-modern">
                <select name="Tipo_de_Recinto" >
                    <option value="" disabled></option>
                    <?php foreach($tipos as $tipo): ?>
                        <option value="<?= $tipo['id_tipo_recinto'] ?>"
                            <?= $tipo['id_tipo_recinto'] == $recinto['id_tipo_recinto'] ? 'selected' : '' ?>>
                            <?= esc($tipo['nombre_tipo_recinto']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Tipo de Recinto</label>
            </div>

            <div class="form-group-modern">
                <input type="text" name="Descripcion" value="<?= esc($recinto['descripcion']) ?>" >
                <label>Descripción</label>
            </div>

            <div class="form-group-modern">
                <select name="estado_recinto" >
                    <option value="activo"   <?= $recinto['estado_recinto'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $recinto['estado_recinto'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <label>Estado</label>
            </div>

            <button type="submit" class="btn-login">Guardar cambios</button>
        </form>

        <div class="login-footer mt-3">
            <a href="<?= base_url('/recinto') ?>">Volver al listado de recintos</a>
        </div>

    </div>
</div>
