<?php // Vista de inicio de sesión ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center">
                    <h4>Iniciar Sesión</h4>
                </div>
                <div class="card-body">
                    <!-- Mensajes de éxito -->
                    <?php if(session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessage">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Mensajes de error -->
                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de login -->
                    <form action="<?= base_url('/login/auth'); ?>" method="post">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">DNI</label>
                            <input type="text" name="usuario" id="usuario" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Ingresar</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small>¿No tenés cuenta? <a href="<?= base_url('/registrarse'); ?>">Registrate aquí</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Hace que el mensaje de éxito se desvanezca después de 3 segundos
    setTimeout(function() {
        let msg = document.getElementById('successMessage');
        if (msg) {
            msg.classList.remove('show'); // quita la clase "show" y se oculta
        }
    }, 3000);
</script>
