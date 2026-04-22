<?php // Vista de edición de recinto ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h4>Editar Recinto</h4>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if(session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('/recinto/update/' . $recinto['nro_recinto']); ?>" method="post">
                        <div class="mb-3">
                            <label for="tarifa_hora" class="form-label">Tarifa por Hora</label>
                            <input type="text" name="tarifa_hora" id="tarifa_hora" 
                                   value="<?= $recinto['tarifa_hora']; ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_tipo_recinto" class="form-label">Tipo de Recinto</label>
                            <select name="id_tipo_recinto" id="id_tipo_recinto" class="form-control" required>
                                <?php foreach($tipos as $tipo): ?>
                                    <option value="<?= $tipo['id_tipo_recinto']; ?>"
                                        <?= $tipo['id_tipo_recinto'] == $recinto['id_tipo_recinto'] ? 'selected' : '' ?>>
                                        <?= $tipo['nombre_tipo_recinto']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="habilitado" class="form-label">Estado</label>
                            <select name="habilitado" id="habilitado" class="form-control">
                                <option value="1" <?= $recinto['habilitado'] == 1 ? 'selected' : '' ?>>Habilitado</option>
                                <option value="0" <?= $recinto['habilitado'] == 0 ? 'selected' : '' ?>>Deshabilitado</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Actualizar Recinto</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small><a href="<?= base_url('/recinto'); ?>">Volver al listado</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
