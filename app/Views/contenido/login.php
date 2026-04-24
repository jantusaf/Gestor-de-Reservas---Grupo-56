<?php // Vista de inicio de sesión ?>

<div class="login-wrapper">
    <div class="login-card">
       
    <h2 class="login-title">Bienvenido</h2>
        <p class="login-subtitle">Ingresá a tu cuenta</p>
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

                        <div class="form-group-modern">
                            <input type="text" name="usuario" required>
                          <label>DNI</label>
                        </div>

                        <div class="form-group-modern">
                            
                            <input type="password" name="password"  required>
                            <label>Contraseña</label>
                        </div>

                        
                            <button type="submit" class="btn-login">Ingresar</button>
                       
                    </form>
                
                <div class="login-footer">
                    ¿No tenés cuenta? <a href="<?= base_url('/registrarse'); ?>">Registrate aquí</a>
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
