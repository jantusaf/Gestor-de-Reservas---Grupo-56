<div class="tabla-wrapper">
    <div class="tabla-card">

        <div class="tabla-header">
            <h2><i class="bi bi-person-gear"></i> Usuarios</h2>
            <div class="tabla-header-right">
                <a href="<?= site_url('usuario/alta') ?>" class="btn-nueva">+ Nuevo Usuario</a>
            </div>
        </div>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="tabla-container">
            <table class="tabla-moderna">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>DNI</th>
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($usuarios)): ?>
                        <?php foreach($usuarios as $u): ?>
                            <?php $inactivo = $u['estado_usuario'] === 'inactivo'; ?>
                            <?php $tdStyle  = $inactivo ? 'style="color:#aaa; background-color:#f8f8f8;"' : ''; ?>
                            <tr>
                                <td <?= $tdStyle ?>><?= esc($u['nombre']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($u['apellido']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($u['dni']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($u['nombre_usuario']) ?></td>
                                <td <?= $tdStyle ?>><?= $u['id_tipo_usuario'] == 1 ? 'Administrador' : 'Recepcionista' ?></td>
                                <td <?= $tdStyle ?>>
                                    <?php if($inactivo): ?>
                                        <span style="color:#c0392b; font-weight:600;">Deshabilitado</span>
                                    <?php else: ?>
                                        <span style="color:#27ae60; font-weight:600;">Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones">
                                    <?php if(!$inactivo): ?>
                                        <a href="<?= site_url('usuario/editar/'.$u['id_usuario']) ?>"
                                           class="btn-action edit">Editar</a>
                                        <button class="btn-action delete"
                                            onclick="abrirModalDeshabilitar(
                                                '<?= site_url('usuario/deshabilitar/'.$u['id_usuario']) ?>',
                                                '<?= esc($u['nombre']).' '.esc($u['apellido']) ?>'
                                            )">Deshabilitar</button>
                                    <?php else: ?>
                                        <a href="<?= site_url('usuario/habilitar/'.$u['id_usuario']) ?>"
                                           class="btn-action edit" style="background-color:#27ae60; opacity:1;">
                                           Habilitar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal de confirmación de baja -->
<div class="modal fade" id="modalDeshabilitar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">
            <div class="modal-header border-0" style="background:#dc2626;">
                <h5 class="modal-title fw-bold text-white">Deshabilitar usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1 text-muted">Estás por deshabilitar al usuario</p>
                <p class="fw-bold fs-5 mb-0" id="modalNombreUsuario"></p>
                <p class="mt-3 small" style="color:#dc2626;">El usuario no podrá iniciar sesión.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnConfirmarDeshabilitar" href="#" class="btn btn-danger px-4">Sí, deshabilitar</a>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalDeshabilitar(url, nombre) {
        document.getElementById('modalNombreUsuario').textContent = nombre;
        document.getElementById('btnConfirmarDeshabilitar').href = url;
        new bootstrap.Modal(document.getElementById('modalDeshabilitar')).show();
    }
</script>
