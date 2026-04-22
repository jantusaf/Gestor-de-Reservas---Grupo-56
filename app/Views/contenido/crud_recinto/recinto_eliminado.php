<div class="container mt-5">
    <div class="d-flex justify-content-end">
        <a href="<?= site_url('/recinto') ?>" class="btn btn-primary btn-sm mt-1">Atrás</a>
    </div>

    <h2>Lista de Recintos Eliminados (Deshabilitados)</h2>
    <div class="mt-3">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tarifa por Hora</th>
                        <th>Tipo de Recinto</th>
                        <!--<th>Habilitado</th> -->
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $perPage = 10; // cantidad de recintos por página
                    $totalPages = ceil(count($recintos) / $perPage);
                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $start_index = ($current_page - 1) * $perPage;
                    $recintos_pagina = array_slice($recintos, $start_index, $perPage);

                    if (!empty($recintos_pagina)):
                        foreach ($recintos_pagina as $recinto): ?>
                            <tr>
                                <td><?= $recinto['nro_recinto'] ?></td>
                                <td><?= $recinto['tarifa_hora'] ?></td>
                                <td><?= $recinto['id_tipo_recinto'] ?></td>
                                <!-- <td><?= $recinto['habilitado'] == 1 ? 'Sí' : 'No' ?></td>-->
                                <td>
                                    <a href="<?= site_url('recinto/activar/' . $recinto['nro_recinto']) ?>" 
                                       class="btn btn-success btn-sm mt-1">Restaurar</a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="4">No hay recintos eliminados para mostrar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Botones de paginación -->
    <div class="pagination justify-content-center mt-5">
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
