<?php // Vista de creación de recinto ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h4>Nuevo Recinto</h4>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if(session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('/recinto/save'); ?>" method="post">
                        <div class="mb-3">
                            <label for="tarifa_hora" class="form-label">Tarifa por Hora</label>
                            <input type="text" name="tarifa_hora" id="tarifa_hora" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_tipo_recinto" class="form-label">Tipo de Recinto</label>
                            <select name="id_tipo_recinto" id="id_tipo_recinto" class="form-control" required>
                                <?php foreach($tipos as $tipo): ?>
                                    <option value="<?= $tipo['id_tipo_recinto']; ?>">
                                        <?= $tipo['nombre_tipo_recinto']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Guardar Recinto</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small><a href="<?= base_url('/recinto'); ?>">Volver al listado de recintos</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
