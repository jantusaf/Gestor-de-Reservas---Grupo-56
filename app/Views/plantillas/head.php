<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=yes">

    <!-- Bootstrap CSS -->
    <link href="<?= base_url('assets/css/bootstrap.min.css'); ?>" rel="stylesheet">

    <!-- Estilos propios -->
    <link href="<?= base_url('assets/css/estilo_grupo_56.css'); ?>" rel="stylesheet">

    <title><?= $title ?? 'Proyecto Grupo 56' ?></title>
</head>

<body class="d-flex flex-column min-vh-100">

<header>
    <nav class="navbar navbar-expand-lg bg-white">
        <div class="container-fluid">

            <!-- Logo -->
            <a class="navbar-brand fw-bold text-dark" href="<?= base_url('/') ?>">
               
            </a>

            <!-- Botón responsive -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú -->
            <div class="collapse navbar-collapse" id="navbarNav">

                <!-- IZQUIERDA -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-semibold" href="<?= base_url('contenido/principal') ?>">
                            Menú
                        </a>
                    </li>
                </ul>

                <!-- DERECHA -->
                <ul class="navbar-nav">
                    <?php if(!session()->get('logged_in')): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= base_url('/login') ?>">Iniciar sesión</a>
                        </li>
                  
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?= base_url('/logout') ?>">Cerrar sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
    </nav>
</header>

<main class="container mt-4 flex-grow-1">