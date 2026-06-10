<div class="tabla-wrapper">
    <div class="tabla-card">

        <div class="tabla-header">
            <h2><i class="bi bi-calendar-check"></i> Reservas
                <?php if(($dni_busqueda ?? '') !== ''): ?>
                    <span class="busqueda-badge"><?= count($reservas) ?> resultado<?= count($reservas) !== 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h2>
            <div class="tabla-header-right">
                <a href="<?= site_url('reserva/crear') ?>" class="btn-nueva">+ Nueva Reserva</a>
                <form method="GET" action="<?= site_url('reserva/listar') ?>" class="busqueda-form">
                    <div class="busqueda-input-wrap">
                        <input type="text" name="dni" value="<?= esc($dni_busqueda ?? '') ?>"
                               placeholder="Buscar por DNI..." maxlength="20"
                               oninput="this.value = this.value.replace(/\D/g,'')">
                        <button type="submit" class="busqueda-btn">Buscar</button>
                        <?php if(($dni_busqueda ?? '') !== ''): ?>
                            <a href="<?= site_url('reserva/listar') ?>" class="busqueda-clear" title="Limpiar">&#x2715;</a>
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
                        <th>Cliente</th>
                        <th>Recinto</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Monto</th>
                        <th>Reserva</th>
                        <th>Pago</th>
                        <th>Medio de pago</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($reservas)): ?>
                        <tr>
                            <td colspan="9" class="empty">No hay reservas registradas.</td>
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
                                <td>
                                    <?= $r['nombre_medio_pago'] ? esc($r['nombre_medio_pago']) : '<span style="color:#aaa;font-size:13px;">—</span>' ?>
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
                                        <?php if($r['estado_pago'] !== 'pagada'): ?>
                                            <a href="<?= site_url('pago/alta/'.$r['id_reserva']) ?>"
                                               class="btn-action success">Pagar</a>
                                        <?php endif; ?>
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

<?php $idReservaFactura = session()->getFlashdata('pago_confirmado'); ?>
<?php if($idReservaFactura): ?>
<div class="modal-overlay" id="modalPagoOk">
    <div class="modal-reserva">
        <div class="modal-reserva-icon">✓</div>
        <h3 class="modal-reserva-title">¡Pago confirmado!</h3>
        <p class="modal-reserva-msg">El pago fue registrado correctamente.<br>La reserva quedó confirmada.</p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:8px;">
            <a href="<?= site_url('pago/factura/'.$idReservaFactura) ?>" target="_blank"
               class="btn-login" style="background:#1a7a4a; text-decoration:none; display:inline-block;">
                🖨 Imprimir Factura
            </a>
            <button class="btn-login" style="background:#6b7280;"
                onclick="document.getElementById('modalPagoOk').style.display='none'">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
document.getElementById('modalPagoOk').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
<?php endif; ?>

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
                <button type="button" id="btnConfirmarCancelar" class="btn btn-danger px-4">Sí, cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Form oculto que ejecuta la cancelación por POST -->
<form id="formCancelar" method="post" class="d-none"></form>

<script>
    function abrirModalCancelar(url, cliente, fecha) {
        document.getElementById('modalClienteNombre').textContent = cliente;
        document.getElementById('modalFecha').textContent = fecha;
        document.getElementById('formCancelar').action = url;
        new bootstrap.Modal(document.getElementById('modalCancelar')).show();
    }
    document.getElementById('btnConfirmarCancelar').addEventListener('click', function () {
        document.getElementById('formCancelar').submit();
    });
</script>
