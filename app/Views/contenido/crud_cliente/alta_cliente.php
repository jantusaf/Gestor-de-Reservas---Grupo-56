<?php // Vista de alta de cliente ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h4>Alta de Cliente</h4>
                </div>
                <div class="card-body">
                    
                    <!-- Mensajes de error -->
                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Mensajes de éxito -->
                    <?php if(session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Errores de validación -->
                    <?php if(isset($validation)): ?>
                        <div class="alert alert-warning">
                            <?= $validation->listErrors() ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de alta de cliente -->
                    <form action="<?= base_url('/cliente/guardar'); ?>" method="post">

                        <!-- DATOS PERSONALES -->
                        <h5 class="mb-3">Datos Personales</h5>
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" name="dni" id="dni" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" name="apellido" id="apellido" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="calle" class="form-label">Calle</label>
                            <input type="text" name="calle" id="calle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="altura" class="form-label">Altura</label>
                            <input type="text" name="altura" id="altura" class="form-control" required>
                        </div>

                        <!-- DATOS DE CLIENTE -->
                        <h5 class="mt-4 mb-3">Datos de Cliente</h5>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Guardar Cliente</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small><a href="<?= base_url('/cliente/listar'); ?>">Ver listado de clientes</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
