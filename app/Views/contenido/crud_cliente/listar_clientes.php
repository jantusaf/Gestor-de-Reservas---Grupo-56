<?php
$perPage      = 10;
$totalPages   = ceil(count($clientes) / $perPage);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_index  = ($current_page - 1) * $perPage;
$clientes_pag = array_slice($clientes, $start_index, $perPage);
$dniParam     = ($dni_busqueda ?? '') !== '' ? '&dni=' . urlencode($dni_busqueda) : '';
?>

<div class="tabla-wrapper">
    <div class="tabla-card">

        <div class="tabla-header">
            <h2><i class="bi bi-people"></i> Clientes
                <?php if(($dni_busqueda ?? '') !== ''): ?>
                    <span class="busqueda-badge"><?= count($clientes) ?> resultado<?= count($clientes) !== 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h2>
            <div class="tabla-header-right">
                <a href="<?= site_url('cliente/alta') ?>" class="btn-nueva">+ Nuevo Cliente</a>
                <form method="GET" action="<?= site_url('cliente/listar') ?>" class="busqueda-form">
                    <div class="busqueda-input-wrap">
                        <input type="text" name="dni" value="<?= esc($dni_busqueda ?? '') ?>"
                               placeholder="Buscar por DNI..." maxlength="20"
                               oninput="this.value = this.value.replace(/\D/g,'')">
                        <button type="submit" class="busqueda-btn">Buscar</button>
                        <?php if(($dni_busqueda ?? '') !== ''): ?>
                            <a href="<?= site_url('cliente/listar') ?>" class="busqueda-clear" title="Limpiar">&#x2715;</a>
                        <?php endif; ?>
                    </div>
                </form>
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
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($clientes_pag)): ?>
                        <?php foreach($clientes_pag as $c): ?>
                            <?php $inactivo = $c['estado_cliente'] === 'inactivo'; ?>
                            <?php $tdStyle  = $inactivo ? 'style="color:#aaa; background-color:#f8f8f8;"' : ''; ?>
                            <tr>
                                <td <?= $tdStyle ?>><?= esc($c['nombre']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($c['apellido']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($c['dni']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($c['email']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($c['telefono']) ?: '-' ?></td>
                                <td <?= $tdStyle ?>>
                                    <?php if($inactivo): ?>
                                        <span style="color:#c0392b; font-weight:600;">Deshabilitado</span>
                                    <?php else: ?>
                                        <span style="color:#27ae60; font-weight:600;">Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones">
                                    <?php if(!$inactivo): ?>
                                        <a href="<?= site_url('cliente/editar/'.$c['id_cliente']) ?>"
                                           class="btn-action edit">Editar</a>
                                        <button class="btn-action delete"
                                            onclick="abrirModalDeshabilitar(
                                                '<?= site_url('cliente/deshabilitar/'.$c['id_cliente']) ?>',
                                                '<?= esc($c['nombre']).' '.esc($c['apellido']) ?>'
                                            )">Deshabilitar</button>
                                    <?php else: ?>
                                        <form method="post" action="<?= site_url('cliente/habilitar/'.$c['id_cliente']) ?>" style="display:inline;">
                                            <button type="submit" class="btn-action edit" style="background-color:#27ae60; opacity:1;">Habilitar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty">No hay clientes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="tabla-pagination">
            <?php if($totalPages > 1): ?>
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $dniParam ?>"
                       class="page-btn <?= $i == $current_page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Modal de confirmación de baja -->
<div class="modal fade" id="modalDeshabilitar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">
            <div class="modal-header border-0" style="background:#dc2626;">
                <h5 class="modal-title fw-bold text-white">Deshabilitar cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1 text-muted">Estás por deshabilitar al cliente</p>
                <p class="fw-bold fs-5 mb-0" id="modalNombreCliente"></p>
                <p class="mt-3 small" style="color:#dc2626;">El cliente no podrá realizar nuevas reservas.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarDeshabilitar" class="btn btn-danger px-4">Sí, deshabilitar</button>
            </div>
        </div>
    </div>
</div>

<!-- Form oculto que ejecuta la baja por POST -->
<form id="formDeshabilitar" method="post" class="d-none"></form>

<script>
    function abrirModalDeshabilitar(url, nombre) {
        document.getElementById('modalNombreCliente').textContent = nombre;
        document.getElementById('formDeshabilitar').action = url;
        new bootstrap.Modal(document.getElementById('modalDeshabilitar')).show();
    }
    document.getElementById('btnConfirmarDeshabilitar').addEventListener('click', function () {
        document.getElementById('formDeshabilitar').submit();
    });
</script>
