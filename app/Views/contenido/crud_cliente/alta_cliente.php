<?php // Vista de alta de cliente ?>

<div class="reserva-wrapper">
    <div class="reserva-card">
       
        
        <h2 class="reserva-title">Nuevo Cliente</h2>
        <p class="reserva-subtitle">Completá los datos</p>
        
                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                
                    <?php if(session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                  
                    <?php if(isset($validation)): ?>
                        <div class="alert alert-warning">
                            <?= $validation->listErrors() ?>
                        </div>
                    <?php endif; ?>

             
                    <form action="<?= base_url('/cliente/guardar'); ?>" method="post">
                        <div class="form-group-modern">
                            
                            <input type="text" name="dni" required>
                     <label>DNI</label>   </div>

                        <div class="form-group-modern">
                            
                          <input type="text" name="nombre" required>
                        <label >Nombre</label>  </div>

                        <div class="form-group-modern">
                           
                            <input type="text" name="apellido"  required>
                        <label>Apellido</label> </div>

                        <div class="form-group-modern">
                         
                            <input type="text" name="telefono" id="telefono" class="form-control">
                          <label >Teléfono</label> </div>

                        <div class="d-grid">
                            <button type="submit" class=" btn-login">Guardar Cliente</button>
                        </div>
                    </form>
             
                <div class="login-footer mt-3">
                    <a href="<?= base_url('/cliente/listar'); ?>">Ver listado de clientes</a>
                </div>
            </div>
        
    </div>
</div>
