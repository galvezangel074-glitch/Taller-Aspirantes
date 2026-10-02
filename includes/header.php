<?php
/* =====================================================================
   includes/header.php
   Módulo de NAVEGACIÓN: <head> + <header> con Navbar y Breadcrumb dinámico.
   Se incluye con include en index.php y en procesar.php.
   ===================================================================== */

// basename() extrae SOLO el nombre del archivo actual (index.php o procesar.php)
// a partir de la ruta completa que devuelve $_SERVER['PHP_SELF'].
$paginaActual = basename($_SERVER['PHP_SELF']);

// SEGURIDAD: este archivo es un módulo, no una página. Si alguien lo abre
// directamente (…/includes/header.php) se devuelve al formulario, para no
// exponer HTML suelto ni la estructura interna del proyecto.
if ($paginaActual === 'header.php') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- ============ METADATOS ============ -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Admisión de la UTP</title>
    <meta name="description" content="Sistema de admisión de datos para aspirantes">
    <meta name="author" content="Universidad Tecnológica de Panamá / Angel Gálvez">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#212529">

    <!-- ============ BOOTSTRAP v5.3.8 (CSS) ============ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (para los iconos del footer) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- ============ ETIQUETA SEMÁNTICA <header> ============ -->
    <header>

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">
                    <i class="bi bi-mortarboard-fill me-1"></i> PortalU
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#menuPrincipal" aria-controls="menuPrincipal"
                        aria-expanded="false" aria-label="Alternar navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="navbar-nav ms-auto">
                        <!-- Estructura IF: la opción activa cambia según la página actual -->
                        <li class="nav-item">
                            <a class="nav-link <?php if ($paginaActual == 'index.php'): ?>active<?php endif; ?>"
                               href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if ($paginaActual == 'procesar.php'): ?>active<?php endif; ?>"
                               href="index.php#formulario">Registro</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Breadcrumb Dinámico (migas de pan) -->
        <div class="bg-white border-bottom py-2">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item">
                            <a href="index.php" class="text-decoration-none">Inicio</a>
                        </li>

                        <?php if ($paginaActual == 'procesar.php'): ?>
                            <!-- Si estamos en el procesador, mostramos el paso intermedio
                                 y activamos el enlace para regresar -->
                            <li class="breadcrumb-item">
                                <a href="index.php" class="text-decoration-none">Registro</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Procesando Datos</li>
                        <?php else: ?>
                            <!-- Si estamos en el inicio -->
                            <li class="breadcrumb-item active" aria-current="page">Registro de Aspirante</li>
                        <?php endif; ?>

                    </ol>
                </nav>
            </div>
        </div>

    </header>
