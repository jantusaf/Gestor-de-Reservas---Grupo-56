<div class="tabla-wrapper">
    <div class="tabla-card">

        <div class="tabla-header">
            <h2>Reservas</h2>
            <a href="<?= site_url('reserva/crear') ?>" class="btn-action edit">+ Nueva Reserva</a>
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
                        <th>Cliente</th>
                        <th>Recinto</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Monto</th>
                        <th>Reserva</th>
                        <th>Pago</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($reservas)): ?>
                        <tr>
                            <td colspan="8" class="empty">No hay reservas registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($reservas as $r): ?>
                            <?php $cancelada = $r['estado_reserva'] === 'cancelada'; ?>
                            <tr style="<?= $cancelada ? 'opacity:.55;' : '' ?>">
                                <td><?= esc($r['nombre']).' '.esc($r['apellido']) ?></td>
                                <td><?= esc($r['nombre_tipo_recinto']).' · '.esc($r['recinto_desc']) ?></td>
                                <td><?= esc($r['fecha_reserva']) ?></td>
                                <td><?= esc($r['hora']) ?></td>
                                <td>$<?= number_format($r['monto'], 2) ?></td>
                                <td>
                                    <span class="badge-estado badge-<?= $r['estado_reserva'] ?>">
                                        <?= ucfirst($r['estado_reserva']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-estado badge-<?= $r['estado_pago'] ?>">
                                        <?= ucfirst($r['estado_pago']) ?>
                                    </span>
                                </td>
                                <td class="acciones">
                                    <?php if(!$cancelada): ?>
                                        <a href="<?= site_url('reserva/editar/'.$r['id_reserva']) ?>"
                                           class="btn-action edit">Editar</a>
                                        <button class="btn-action delete"
                                            onclick="abrirModalCancelar(
                                                '<?= site_url('reserva/cancelar/'.$r['id_reserva']) ?>',
                                                '<?= esc($r['nombre']).' '.esc($r['apellido']) ?>',
                                                '<?= esc($r['fecha_reserva']) ?>'
                                            )">Cancelar</button>
                                        <a href="<?= site_url('pago/alta/'.$r['id_reserva']) ?>" 
                                           class="btn-action success">Pagar</a>

                                    <?php else: ?>
                                        <span style="color:#aaa; font-size:13px;">Cancelada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal de confirmación de cancelación -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; overflow:hidden; border:none;">
            <div class="modal-header border-0" style="background:#dc2626;">
                <h5 class="modal-title fw-bold text-white">Cancelar reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="mb-1 text-muted">Estás por cancelar la reserva de</p>
                <p class="fw-bold fs-5 mb-1" id="modalClienteNombre"></p>
                <p class="text-muted mb-0">para el <strong id="modalFecha"></strong></p>
                <p class="mt-3 small" style="color:#dc2626;">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Volver</button>
                <a id="btnConfirmarCancelar" href="#" class="btn btn-danger px-4">Sí, cancelar</a>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalCancelar(url, cliente, fecha) {
        document.getElementById('modalClienteNombre').textContent = cliente;
        document.getElementById('modalFecha').textContent = fecha;
        document.getElementById('btnConfirmarCancelar').href = url;
        new bootstrap.Modal(document.getElementById('modalCancelar')).show();
    }
</script>
