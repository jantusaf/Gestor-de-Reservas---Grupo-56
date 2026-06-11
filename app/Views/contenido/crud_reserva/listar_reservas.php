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
            <div class="alert alert-success d-flex align-items-center gap-2">
                <span>✓</span> <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <span>⚠</span> <?= session()->getFlashdata('error') ?>
            </div>
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
                            <?php
                                $estado        = $r['estado_reserva'];
                                $estadoPago    = $r['estado_pago'];
                                $inicioTurno   = strtotime($r['fecha_reserva'] . ' ' . $r['hora']);
                                $turnoIniciado = time() >= $inicioTurno;           // ya no se puede cancelar
                                $esPasada      = time() >= $inicioTurno + 3600;    // turno terminado, nada disponible
                            ?>
                            <tr style="<?= ($estado === 'cancelada' || $esPasada) ? 'background:#fafafa; color:#9ca3af;' : '' ?>">
                                <td><?= esc($r['nombre']).' '.esc($r['apellido']) ?></td>
                                <td><?= esc($r['nombre_tipo_recinto']).' · '.esc($r['recinto_desc']) ?></td>
                                <td><?= esc($r['fecha_reserva']) ?></td>
                                <td><?= esc($r['hora']) ?></td>
                                <td>$<?= number_format($r['monto'], 2) ?></td>
                                <td>
                                    <span class="badge-estado badge-<?= $estado ?>">
                                        <?= ucfirst($estado) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-estado badge-<?= $estadoPago ?>">
                                        <?= ucfirst($estadoPago) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $r['nombre_medio_pago'] ? esc($r['nombre_medio_pago']) : '<span style="color:#aaa;font-size:13px;">—</span>' ?>
                                </td>

                                <!-- Acciones según estado (patrón State) + momento del turno -->
                                <td class="acciones">
                                    <?php if ($esPasada): ?>
                                        <!-- Turno terminado: sin acciones de gestión -->
                                        <?php if ($estado === 'confirmada'): ?>
                                            <span class="badge-estado" style="background:#dbeafe; color:#1e40af; font-size:12px; padding:4px 10px; border-radius:20px;">
                                                Turno completado
                                            </span>
                                            <a href="<?= site_url('pago/factura/'.$r['id_reserva']) ?>"
                                               class="btn-action" target="_blank"
                                               style="background:#6366f1; color:#fff;"
                                               title="Ver factura">Factura</a>
                                        <?php elseif ($estado === 'cancelada'): ?>
                                            <span class="badge-estado" style="background:#f3f4f6; color:#9ca3af; font-size:12px; padding:4px 10px; border-radius:20px;">
                                                Cancelada
                                            </span>
                                        <?php else: ?>
                                            <!-- pendiente vencida = no se presentó al turno -->
                                            <span class="badge-estado" style="background:#fef3c7; color:#92400e; font-size:12px; padding:4px 10px; border-radius:20px;">
                                                No se presentó
                                            </span>
                                        <?php endif; ?>

                                    <?php elseif ($estado === 'pendiente'): ?>
                                        <!-- PENDIENTE: Editar + Pagar siempre; Cancelar solo antes del inicio -->
                                        <a href="<?= site_url('reserva/editar/'.$r['id_reserva']) ?>"
                                           class="btn-action edit" title="Editar reserva">Editar</a>
                                        <a href="<?= site_url('pago/alta/'.$r['id_reserva']) ?>"
                                           class="btn-action success" title="Registrar pago">Pagar</a>
                                        <?php if (!$turnoIniciado): ?>
                                            <button class="btn-action delete"
                                                title="Cancelar reserva (sin reembolso)"
                                                onclick="abrirModalCancelar(
                                                    '<?= site_url('reserva/cancelar/'.$r['id_reserva']) ?>',
                                                    '<?= esc($r['nombre']).' '.esc($r['apellido']) ?>',
                                                    '<?= esc($r['fecha_reserva']) ?>',
                                                    '<?= esc($estadoPago) ?>',
                                                    '<?= esc($r['hora']) ?>'
                                                )">Cancelar</button>
                                        <?php else: ?>
                                            <span style="color:#f59e0b; font-size:12px; font-style:italic;">Turno en curso</span>
                                        <?php endif; ?>

                                    <?php elseif ($estado === 'confirmada'): ?>
                                        <!-- CONFIRMADA: Editar + Factura siempre; Cancelar solo antes del inicio -->
                                        <a href="<?= site_url('reserva/editar/'.$r['id_reserva']) ?>"
                                           class="btn-action edit" title="Editar reserva">Editar</a>
                                        <a href="<?= site_url('pago/factura/'.$r['id_reserva']) ?>"
                                           class="btn-action" target="_blank"
                                           style="background:#6366f1; color:#fff;"
                                           title="Ver factura">Factura</a>
                                        <?php if (!$turnoIniciado): ?>
                                            <button class="btn-action delete"
                                                title="Cancelar (reembolso según anticipación)"
                                                onclick="abrirModalCancelar(
                                                    '<?= site_url('reserva/cancelar/'.$r['id_reserva']) ?>',
                                                    '<?= esc($r['nombre']).' '.esc($r['apellido']) ?>',
                                                    '<?= esc($r['fecha_reserva']) ?>',
                                                    '<?= esc($estadoPago) ?>',
                                                    '<?= esc($r['hora']) ?>'
                                                )">Cancelar</button>
                                        <?php else: ?>
                                            <span style="color:#f59e0b; font-size:12px; font-style:italic;">Turno en curso</span>
                                        <?php endif; ?>

                                    <?php elseif ($estado === 'cancelada'): ?>
                                        <span class="badge-estado" style="background:#f3f4f6; color:#9ca3af; font-size:12px; padding:4px 10px; border-radius:20px;">
                                            Sin acciones disponibles
                                        </span>

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

                <!-- Aviso dinámico: se muestra según si aplica reembolso en este momento -->
                <div id="modalAvisoConReembolso" class="mt-3 p-3 rounded" style="display:none; background:#dcfce7; border:1px solid #86efac;">
                    <p class="mb-1 small fw-bold" style="color:#166534;">✓ Aplica reembolso</p>
                    <p class="mb-0 small" style="color:#15803d;">
                        Cancelás con más de 30 minutos de anticipación.<br>
                        El pago será <strong>reembolsado</strong> automáticamente.
                    </p>
                </div>
                <div id="modalAvisoSinReembolso" class="mt-3 p-3 rounded" style="display:none; background:#fef3c7; border:1px solid #f59e0b;">
                    <p class="mb-1 small fw-bold" style="color:#92400e;">⚠ Sin reembolso</p>
                    <p class="mb-0 small" style="color:#78350f;">
                        Faltan 30 minutos o menos para el inicio del turno.<br>
                        La cancelación <strong>no genera reembolso</strong>.
                    </p>
                </div>

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
    function abrirModalCancelar(url, cliente, fecha, estadoPago, horaInicio) {
        document.getElementById('modalClienteNombre').textContent = cliente;
        document.getElementById('modalFecha').textContent = fecha;
        document.getElementById('formCancelar').action = url;

        var divConReembolso  = document.getElementById('modalAvisoConReembolso');
        var divSinReembolso  = document.getElementById('modalAvisoSinReembolso');

        divConReembolso.style.display = 'none';
        divSinReembolso.style.display = 'none';

        if (estadoPago === 'pagada') {
            // Calcular si en este momento aplica reembolso (> 30 min antes del inicio)
            var ahora        = new Date();
            var inicioTurno  = new Date(fecha + 'T' + horaInicio);
            var minutosRest  = (inicioTurno - ahora) / 60000;

            if (minutosRest > 30) {
                divConReembolso.style.display = 'block';
            } else {
                divSinReembolso.style.display = 'block';
            }
        }

        new bootstrap.Modal(document.getElementById('modalCancelar')).show();
    }

    document.getElementById('btnConfirmarCancelar').addEventListener('click', function () {
        document.getElementById('formCancelar').submit();
    });
</script>
