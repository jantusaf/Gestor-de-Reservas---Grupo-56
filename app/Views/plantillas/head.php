<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=yes">

    <!-- Bootstrap CSS local -->
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">

    <!-- Estilos propios -->
    <link href="<?= base_url('assets/css/estilo_grupo_56.css'); ?>" rel="stylesheet">

    <title><?= $title ?? 'Proyecto Grupo 56' ?></title>
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/') ?>">Grupo 56</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav" aria-controls="navbarNav" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('/contacto') ?>">Contacto</a></li>

                    <!-- Menús CRUD solo para Administrador (Id_Tipo 1) o Supervisor (Id_Tipo 2) -->
                    <?php if(session()->get('logged_in') && in_array(session()->get('id_tipo'), [1])): ?>

                       <!-- <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownClientes" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">CRUD Clientes</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownClientes">
                                <li><a class="dropdown-item" href="<?= base_url('cliente/listar'); ?>">
                                    Lista de Clientes (Editar - Eliminar)</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('cliente/eliminados'); ?>">
                                    Clientes Eliminados</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('cliente/alta'); ?>">
                                    Alta de Cliente</a></li>
                            </ul>
                        </li> -->


                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownRecintos" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">CRUD Recintos</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownRecintos">
                                <li><a class="dropdown-item" href="<?= base_url('recinto'); ?>">
                                    Lista de Recintos</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('recintos-eliminados'); ?>">
                                    Recintos Eliminados</a></li>
                                <li><a class="dropdown-item" href="<?= base_url('recinto/crear'); ?>">
                                    Alta de Recinto</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                        <!-- Botón Generar Reserva -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('reserva/crear'); ?>">
                                Generar Reserva
                            </a>
                        </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if(!session()->get('logged_in')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('/login') ?>">Iniciar sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('/registrarse') ?>">Registrarse</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('/logout') ?>">Cerrar sesión</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container mt-4">
