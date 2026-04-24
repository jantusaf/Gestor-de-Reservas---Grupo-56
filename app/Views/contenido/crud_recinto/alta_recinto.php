<?php // Vista de creación de recinto ?>

<div class="reserva-wrapper">

    <div class="reserva-card">
        <div class="col-md-8">

                      <h2 class="reserva-title">Nuevo Recinto</h2>
        <p class="reserva-subtitle">Completá los datos</p>

                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>
                    <?php if(session()->getFlashdata('success')): ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('/recinto/save'); ?>" method="post">
                         <div class="form-group-modern">
                <input type="text" name="tarifa_hora" required>
                <label>Tarifa por Hora</label>
            </div>

                        <div class="form-group-modern">
                           <select name="id_tipo_recinto" required>
                    <option value="" disabled selected></option>
                                <?php foreach($tipos as $tipo): ?>
                                    <option value="<?= $tipo['id_tipo_recinto']; ?>">
                                        <?= $tipo['nombre_tipo_recinto']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select><label>Tipo de Recinto</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class=" btn-login">Agregar</button>
                        </div>
                    </form>
                
                <div class="login-footer mt-3">
                    <a href="<?= base_url('/recinto'); ?>">Volver al listado de recintos</a>
                </div>
            </div>
        </div>
 