<div class="tabla-wrapper">
    <div class="tabla-card">

        <div class="tabla-header">
            <h2><i class="bi bi-credit-card"></i> Pagos</h2>
            <!-- Botón eliminado porque no existe /pago/alta sin ID -->
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
                        <th>Fecha Pago</th>
                        <th>Monto</th>
                        <th>Medio</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pagos)): ?>
                        <tr>
                            <td colspan="6" class="empty">No hay pagos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($pagos as $p): ?>
                            <tr>
                                <td><?= esc($p['nombre']).' '.esc($p['apellido']) ?></td>
                                <td><?= esc($p['fecha_pago']) ?></td>
                                <td>$<?= number_format($p['monto_total'], 2) ?></td>
                                <td><?= esc($p['nombre_medio_pago']) ?></td>
                                <td><?= esc($p['nombre_usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

    </div>
</div>
