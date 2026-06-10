<?php
// Definir la cantidad de recintos por página
$perPage = 10;

// Calcular el número total de páginas
$totalPages = ceil(count($recintos) / $perPage);

// Obtener la página actual
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calcular el índice de inicio
$start_index = ($current_page - 1) * $perPage;

// Obtener solo los recintos para esta página
$recintos_pagina = array_slice($recintos, $start_index, $perPage);
?>

<div class="tabla-wrapper">

    <div class="tabla-card">

        <div class="tabla-header">
            <h2><i class="bi bi-building"></i> Recintos</h2>
            <a href="<?= site_url('/recinto/alta') ?>" class="btn-nueva">+ Nuevo Recinto</a>
        </div>

                <!-- BLOQUE DE MENSAJES -->
        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <!-- FIN BLOQUE DE MENSAJES -->

        <div class="tabla-container">
            <table class="tabla-moderna">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Tarifa/h</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($recintos_pagina)): ?>
                        <?php foreach ($recintos_pagina as $recinto): ?>
                            <?php $inactivo = $recinto['estado_recinto'] === 'inactivo'; ?>
                            <?php $tdStyle  = $inactivo ? 'style="color:#aaa; background-color:#f8f8f8;"' : ''; ?>
                            <tr>
                                <td <?= $tdStyle ?>><?= esc($recinto['nombre_tipo_recinto']) ?></td>
                                <td <?= $tdStyle ?>><?= esc($recinto['descripcion']) ?></td>
                                <td <?= $tdStyle ?>>$<?= number_format($recinto['tarifa'], 2) ?></td>
                                <td <?= $tdStyle ?>>
                                    <?php if($inactivo): ?>
                                        <span style="color:#c0392b; font-weight:600;">Deshabilitado</span>
                                    <?php else: ?>
                                        <span style="color:#27ae60; font-weight:600;">Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones">
                                    <?php if(!$inactivo): ?>
                                        <a href="<?= site_url('recinto/editar/' . $recinto['id_recinto']) ?>"
                                           class="btn-action edit">Editar</a>
                                        <form method="post" action="<?= site_url('recinto/deshabilitar/' . $recinto['id_recinto']) ?>" style="display:inline;">
                                            <button type="submit" class="btn-action delete">Deshabilitar</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="<?= site_url('recinto/habilitar/' . $recinto['id_recinto']) ?>" style="display:inline;">
                                            <button type="submit" class="btn-action edit" style="background-color:#27ae60; opacity:1;">Habilitar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty">No hay recintos</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="tabla-pagination">
            <?php if ($totalPages > 1): ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>"
                       class="page-btn <?= $i == $current_page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

    </div>

</div>