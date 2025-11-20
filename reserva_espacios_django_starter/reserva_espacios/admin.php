<?php
$page_title = 'Administración';
require_once 'config.php';
requireAdmin();

$pdo = getDB();

// Estadísticas
$total_espacios = $pdo->query("SELECT COUNT(*) FROM espacios WHERE activo = 1")->fetchColumn();
$total_reservas = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'CONFIRMADA'")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE is_active = 1")->fetchColumn();

// Reservas recientes
$reservas_recientes = $pdo->query("SELECT r.*, e.nombre as espacio_nombre, u.username 
                                   FROM reservas r 
                                   JOIN espacios e ON r.espacio_id = e.id 
                                   JOIN usuarios u ON r.usuario_id = u.id 
                                   ORDER BY r.fecha_creacion DESC 
                                   LIMIT 10")->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-cog"></i>
    Panel de Administración
</h1>

<div class="columns" style="margin-top: 2rem;">
    <div class="column">
        <div class="box has-text-centered">
            <p class="title"><?php echo $total_espacios; ?></p>
            <p class="subtitle">Espacios Activos</p>
        </div>
    </div>
    <div class="column">
        <div class="box has-text-centered">
            <p class="title"><?php echo $total_reservas; ?></p>
            <p class="subtitle">Reservas Confirmadas</p>
        </div>
    </div>
    <div class="column">
        <div class="box has-text-centered">
            <p class="title"><?php echo $total_usuarios; ?></p>
            <p class="subtitle">Usuarios Activos</p>
        </div>
    </div>
</div>

<div class="box" style="margin-top: 2rem;">
    <h2 class="subtitle">Reservas Recientes</h2>
    <?php if (count($reservas_recientes) > 0): ?>
    <table class="table is-fullwidth is-striped">
        <thead>
            <tr>
                <th>Espacio</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas_recientes as $reserva): ?>
                <tr>
                    <td><?php echo htmlspecialchars($reserva['espacio_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($reserva['username']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></td>
                    <td><?php echo substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5); ?></td>
                    <td>
                        <span class="tag is-<?php echo $reserva['estado'] == 'CONFIRMADA' ? 'success' : ($reserva['estado'] == 'CANCELADA' ? 'danger' : 'warning'); ?>">
                            <?php echo $reserva['estado']; ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No hay reservas recientes.</p>
    <?php endif; ?>
</div>

<div class="box" style="margin-top: 2rem;">
    <h2 class="subtitle">Gestión</h2>
    <div class="buttons">
        <a href="admin_espacios.php" class="button is-info">
            <i class="fas fa-building"></i>
            <span style="margin-left: 0.5rem;">Gestionar Espacios</span>
        </a>
        <a href="admin_usuarios.php" class="button is-info">
            <i class="fas fa-users"></i>
            <span style="margin-left: 0.5rem;">Gestionar Usuarios</span>
        </a>
        <a href="admin_reservas.php" class="button is-info">
            <i class="fas fa-calendar"></i>
            <span style="margin-left: 0.5rem;">Todas las Reservas</span>
        </a>
    </div>
</div>

<?php require_once 'footer.php'; ?>

