<?php
$page_title = 'Mis Reservas';
require_once 'config.php';
requireLogin();

$pdo = getDB();
$usuario = getUsuario();

$stmt = $pdo->prepare("SELECT r.*, e.nombre as espacio_nombre, e.tipo as espacio_tipo 
                       FROM reservas r 
                       JOIN espacios e ON r.espacio_id = e.id 
                       WHERE r.usuario_id = ? 
                       ORDER BY r.fecha DESC, r.hora_inicio DESC");
$stmt->execute([$usuario['id']]);
$reservas = $stmt->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-list"></i>
    Mis Reservas
</h1>

<div class="buttons" style="margin-bottom: 1rem;">
    <a class="button is-primary" href="nueva_reserva.php">
        <i class="fas fa-plus-circle"></i>
        <span style="margin-left: 0.5rem;">Nueva Reserva</span>
    </a>
    <a class="button is-light" href="index.php">
        <i class="fas fa-home"></i>
        <span style="margin-left: 0.5rem;">Inicio</span>
    </a>
</div>

<?php if (count($reservas) > 0): ?>
<div class="table-container">
    <table class="table is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th><i class="fas fa-building"></i> Espacio</th>
                <th><i class="fas fa-calendar"></i> Fecha</th>
                <th><i class="fas fa-clock"></i> Hora Inicio</th>
                <th><i class="fas fa-clock"></i> Hora Fin</th>
                <th><i class="fas fa-info-circle"></i> Estado</th>
                <th><i class="fas fa-calendar-plus"></i> Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $reserva): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($reserva['espacio_nombre']); ?></strong>
                        <br>
                        <small class="has-text-grey"><?php echo htmlspecialchars($reserva['espacio_tipo']); ?></small>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></td>
                    <td><?php echo substr($reserva['hora_inicio'], 0, 5); ?></td>
                    <td><?php echo substr($reserva['hora_fin'], 0, 5); ?></td>
                    <td>
                        <?php
                        $estado_class = [
                            'CONFIRMADA' => 'is-success',
                            'CANCELADA' => 'is-danger',
                            'PENDIENTE' => 'is-warning'
                        ];
                        $estado_text = [
                            'CONFIRMADA' => 'Confirmada',
                            'CANCELADA' => 'Cancelada',
                            'PENDIENTE' => 'Pendiente'
                        ];
                        $class = $estado_class[$reserva['estado']] ?? 'is-info';
                        $text = $estado_text[$reserva['estado']] ?? $reserva['estado'];
                        ?>
                        <span class="tag <?php echo $class; ?>"><?php echo $text; ?></span>
                    </td>
                    <td>
                        <small><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])); ?></small>
                    </td>
                    <td>
                        <?php if ($reserva['estado'] != 'CANCELADA'): ?>
                            <a href="cancelar_reserva.php?id=<?php echo $reserva['id']; ?>" 
                               class="button is-small is-danger is-light"
                               onclick="return confirm('¿Estás seguro de cancelar esta reserva?');">
                                <i class="fas fa-times"></i>
                                <span>Cancelar</span>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="box">
    <article class="message is-info">
        <div class="message-body">
            <i class="fas fa-info-circle"></i>
            No tienes reservas registradas. 
            <a href="nueva_reserva.php">Crea tu primera reserva</a>
        </div>
    </article>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

