<div class="cliente-wrapper">
    <div class="cliente-card">

        <h2 class="reserva-title">Mi Perfil</h2>
        <p class="reserva-subtitle">Tus datos personales y de cuenta</p>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="form-row-2">
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['nombre']) ?>" readonly>
                <label class="label-active">Nombre</label>
            </div>
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['apellido']) ?>" readonly>
                <label class="label-active">Apellido</label>
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['dni']) ?>" readonly>
                <label class="label-active">DNI</label>
            </div>
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['fecha_nacimiento']) ?>" readonly>
                <label class="label-active">Fecha de Nacimiento</label>
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['calle']) ?>" readonly>
                <label class="label-active">Calle</label>
            </div>
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['altura']) ?>" readonly>
                <label class="label-active">Altura</label>
            </div>
        </div>

        <div class="form-row-2">
            <div class="form-group-modern">
                <input type="text" value="<?= esc($persona['telefono']) ?>" readonly>
                <label class="label-active">Teléfono</label>
            </div>
            <div class="form-group-modern">
                <input type="text" value="<?= esc($usuario['nombre_usuario']) ?>" readonly>
                <label class="label-active">Nombre de Usuario</label>
            </div>
        </div>

        <div class="dashboard-btns mt-3">
            <a href="<?= site_url('usuario/perfil/editar') ?>" class="btn-action edit">Editar</a>
            <button type="button" class="btn-action delete" onclick="abrirModalBaja()">Darme de baja</button>
        </div>

    </div>
</div>

<!-- Formulario oculto para la baja lógica del propio usuario -->
<form id="formBaja" action="<?= site_url('usuario/baja') ?>" method="post" class="d-none"></form>

<!-- Modal de confirmación de baja -->
<div class="modal fade" id="modalBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">
            <div class="modal-header border-0" style="background:#dc2626;">
                <h5 class="modal-title fw-bold text-white">Darme de baja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1 text-muted">Estás por dar de baja tu cuenta.</p>
                <p class="mt-3 small" style="color:#dc2626;">No vas a poder volver a iniciar sesión y se cerrará tu sesión actual.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger px-4" onclick="document.getElementById('formBaja').submit()">Sí, darme de baja</button>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalBaja() {
        new bootstrap.Modal(document.getElementById('modalBaja')).show();
    }
</script>
