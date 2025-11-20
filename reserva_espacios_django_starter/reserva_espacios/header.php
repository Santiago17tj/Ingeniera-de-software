<?php
require_once 'config.php';
$usuario = getUsuario();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.25rem;
        }
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        footer {
            margin-top: 3rem;
            padding: 2rem 0;
        }
    </style>
</head>
<body>
    <nav class="navbar is-primary" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="index.php">
                <i class="fas fa-calendar-check"></i>
                <span style="margin-left: 0.5rem;">Reserva de Espacios</span>
            </a>
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>
        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="index.php">
                    <i class="fas fa-home"></i>
                    <span style="margin-left: 0.5rem;">Inicio</span>
                </a>
                <?php if ($usuario): ?>
                <a class="navbar-item" href="reservas.php">
                    <i class="fas fa-list"></i>
                    <span style="margin-left: 0.5rem;">Mis Reservas</span>
                </a>
                <a class="navbar-item" href="nueva_reserva.php">
                    <i class="fas fa-plus-circle"></i>
                    <span style="margin-left: 0.5rem;">Nueva Reserva</span>
                </a>
                <a class="navbar-item" href="perfil.php">
                    <i class="fas fa-user-circle"></i>
                    <span style="margin-left: 0.5rem;">Mi Perfil</span>
                </a>
                <?php if ($usuario['is_admin']): ?>
                <a class="navbar-item" href="admin.php">
                    <i class="fas fa-cog"></i>
                    <span style="margin-left: 0.5rem;">Administración</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <?php if ($usuario): ?>
                            <span class="button is-light">
                                <i class="fas fa-user"></i>
                                <span style="margin-left: 0.5rem;"><?php echo htmlspecialchars($usuario['username']); ?></span>
                            </span>
                            <a class="button is-danger is-light" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                <span style="margin-left: 0.5rem;">Salir</span>
                            </a>
                        <?php else: ?>
                            <a class="button is-primary" href="registro.php">
                                <i class="fas fa-user-plus"></i>
                                <span style="margin-left: 0.5rem;">Registrarse</span>
                            </a>
                            <a class="button is-light" href="login.php">
                                <i class="fas fa-sign-in-alt"></i>
                                <span style="margin-left: 0.5rem;">Iniciar Sesión</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($flash): ?>
    <div class="container" style="margin-top: 1rem;">
        <div class="notification is-<?php echo $flash['type']; ?> is-light">
            <button class="delete"></button>
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    </div>
    <?php endif; ?>

    <main>
        <div class="container" style="margin-top: 2rem; margin-bottom: 2rem;">

