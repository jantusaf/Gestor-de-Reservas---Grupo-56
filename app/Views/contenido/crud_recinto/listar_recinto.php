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
            <h2>Recintos</h2>
            <a href="<?= site_url('/recintos-eliminados') ?>" class="btn-outline-danger">
                Eliminados
            </a>
        </div>

        <div class="tabla-container">
            <table class="tabla-moderna">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tarifa</th>
                        <th>Tipo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($recintos_pagina)): ?>
                        <?php foreach ($recintos_pagina as $recinto): ?>
                            <tr>
                                <td><?= $recinto['nro_recinto']; ?></td>
                                <td>$<?= $recinto['tarifa_hora']; ?></td>
                                <td><?= $recinto['id_tipo_recinto']; ?></td>

                                <td class="acciones">
                                    <a href="<?= site_url('recinto/editar/' . $recinto['nro_recinto']) ?>" 
                                       class="btn-action edit">Editar</a>

                                    <a href="<?= site_url('recinto/eliminar/' . $recinto['nro_recinto']) ?>" 
                                       class="btn-action delete">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty">No hay recintos</td>
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