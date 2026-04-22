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

<div class="container-fluid">
    <div class="row">
        <div class="col">

            <h2>Listado de Recintos (Habilitados)</h2>
            <div class="d-flex justify-content-end mb-3">
                <a href="<?= site_url('/recintos-eliminados') ?>" class="btn btn-danger ms-2">Eliminados</a>
            </div>

            <!-- Mostrar la tabla de recintos -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Recinto Nro</th>
                            <th>Tarifa por Hora</th>
                            <th>Tipo de Recinto</th>
                            <!-- <th>Habilitado</th>-->
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recintos_pagina)): ?>
                            <?php foreach ($recintos_pagina as $recinto): ?>
                                <tr>
                                    <td><?= $recinto['nro_recinto']; ?></td>
                                    <td><?= $recinto['tarifa_hora']; ?></td>
                                    <td><?= $recinto['id_tipo_recinto']; ?></td>
                                    <!-- <td><?= $recinto['habilitado'] == 1 ? 'Sí' : 'No'; ?></td>-->
                                    <td>
                                        <a href="<?= site_url('recinto/eliminar/' . $recinto['nro_recinto']) ?>" 
                                           class="btn btn-danger btn-sm">Eliminar</a>
                                                                                   <a href="<?= site_url('recinto/editar/' . $recinto['nro_recinto']) ?>" 
                                        class="btn btn-primary btn-sm ms-1">Editar</a>
                                    </td>


                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No hay recintos para mostrar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Botones de paginación -->
            <div class="pagination justify-content-center mt-4">
                <?php if ($totalPages > 1): ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                        $url = "?page=" . $i;
                        $style = ($i == $current_page) ? 'style="pointer-events: none; background-color: darkred;"' : '';
                        echo '<a class="btn btn-primary mx-1" href="' . $url . '" ' . $style . '>' . $i . '</a>';
                        ?>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
