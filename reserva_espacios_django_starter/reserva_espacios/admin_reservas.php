<?php
$page_title = 'Gestión de Reservas';
require_once 'config.php';
requireAdmin();

$pdo = getDB();
$error = '';
$success = '';

// Procesar cancelación de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_id'])) {
    $id = intval($_POST['cancelar_id']);
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE reservas SET estado = 'CANCELADA' WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = 'Reserva cancelada exitosamente.';
        } else {
            $error = 'Error al cancelar la reserva.';
        }
    }
}

// Filtros
$filtro_espacio = intval($_GET['espacio'] ?? 0);
$filtro_usuario = intval($_GET['usuario'] ?? 0);
$filtro_fecha = sanitize($_GET['fecha'] ?? '');
$filtro_estado = sanitize($_GET['estado'] ?? '');

// Construir consulta con filtros
$sql = "SELECT r.*, 
        e.nombre as espacio_nombre, e.tipo as espacio_tipo,
        u.username, u.nombre as usuario_nombre, u.apellido as usuario_apellido
        FROM reservas r
        JOIN espacios e ON r.espacio_id = e.id
        JOIN usuarios u ON r.usuario_id = u.id
        WHERE 1=1";

$params = [];

if ($filtro_espacio > 0) {
    $sql .= " AND r.espacio_id = ?";
    $params[] = $filtro_espacio;
}

if ($filtro_usuario > 0) {
    $sql .= " AND r.usuario_id = ?";
    $params[] = $filtro_usuario;
}

if (!empty($filtro_fecha)) {
    $sql .= " AND r.fecha = ?";
    $params[] = $filtro_fecha;
}

if (!empty($filtro_estado)) {
    $sql .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

$sql .= " ORDER BY r.fecha DESC, r.hora_inicio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Verificar si los campos de contacto existen en la tabla
$campos_contacto_existen = false;
try {
    $test = $pdo->query("SELECT nombre_contacto, telefono, numero_identificacion FROM reservas LIMIT 1");
    $campos_contacto_existen = true;
} catch (PDOException $e) {
    // Los campos no existen aún
}

// Obtener listas para filtros
$espacios = $pdo->query("SELECT id, nombre FROM espacios ORDER BY nombre")->fetchAll();
$usuarios = $pdo->query("SELECT id, username, nombre, apellido FROM usuarios ORDER BY username")->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-calendar-alt"></i>
    Gestión de Todas las Reservas
</h1>

<div class="buttons" style="margin-bottom: 1rem;">
    <a class="button is-link" href="admin.php">
        <i class="fas fa-arrow-left"></i>
        <span style="margin-left: 0.5rem;">Volver al Panel</span>
    </a>
    <a class="button is-primary" href="admin_reportes.php">
        <i class="fas fa-file-pdf"></i>
        <span style="margin-left: 0.5rem;">Generar Reporte PDF</span>
    </a>
</div>

<?php if ($error): ?>
    <div class="notification is-danger is-light">
        <button class="delete"></button>
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="notification is-success is-light">
        <button class="delete"></button>
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<!-- Filtros -->
<div class="box">
    <h2 class="subtitle">
        <i class="fas fa-filter"></i>
        Filtros de Búsqueda
    </h2>
    <form method="get" class="columns is-multiline">
        <div class="column is-3">
            <label class="label is-small">Espacio</label>
            <div class="select is-fullwidth">
                <select name="espacio">
                    <option value="">Todos los espacios</option>
                    <?php foreach ($espacios as $espacio): ?>
                        <option value="<?php echo $espacio['id']; ?>" 
                                <?php echo $filtro_espacio == $espacio['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($espacio['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="column is-3">
            <label class="label is-small">Usuario</label>
            <div class="select is-fullwidth">
                <select name="usuario">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id']; ?>" 
                                <?php echo $filtro_usuario == $usuario['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($usuario['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="column is-2">
            <label class="label is-small">Fecha</label>
            <input class="input" type="date" name="fecha" value="<?php echo htmlspecialchars($filtro_fecha); ?>">
        </div>
        
        <div class="column is-2">
            <label class="label is-small">Estado</label>
            <div class="select is-fullwidth">
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <option value="CONFIRMADA" <?php echo $filtro_estado === 'CONFIRMADA' ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="CANCELADA" <?php echo $filtro_estado === 'CANCELADA' ? 'selected' : ''; ?>>Cancelada</option>
                    <option value="PENDIENTE" <?php echo $filtro_estado === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                </select>
            </div>
        </div>
        
        <div class="column is-2">
            <label class="label is-small">&nbsp;</label>
            <button type="submit" class="button is-info is-fullwidth">
                <i class="fas fa-search"></i>
                <span style="margin-left: 0.5rem;">Filtrar</span>
            </button>
        </div>
    </form>
    
    <?php if ($filtro_espacio || $filtro_usuario || $filtro_fecha || $filtro_estado): ?>
        <div style="margin-top: 0.5rem;">
            <a href="admin_reservas.php" class="button is-small is-light">
                <i class="fas fa-times"></i>
                <span style="margin-left: 0.3rem;">Limpiar Filtros</span>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Tabla de reservas -->
<div class="box">
    <h2 class="subtitle">
        Reservas Encontradas: <?php echo count($reservas); ?>
    </h2>
    
    <?php if (count($reservas) > 0): ?>
    <div class="table-container">
        <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Espacio</th>
                    <th>Usuario</th>
                    <?php if ($campos_contacto_existen): ?>
                    <th><i class="fas fa-user"></i> Contacto</th>
                    <?php endif; ?>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                    <th>Creada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?php echo $reserva['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($reserva['espacio_nombre']); ?></strong>
                            <br>
                            <small class="has-text-grey"><?php echo htmlspecialchars($reserva['espacio_tipo']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($reserva['username']); ?></strong>
                            <br>
                            <small><?php echo htmlspecialchars($reserva['usuario_nombre'] . ' ' . $reserva['usuario_apellido']); ?></small>
                        </td>
                        <?php if ($campos_contacto_existen): ?>
                        <td>
                            <?php if (!empty($reserva['nombre_contacto'])): ?>
                                <strong><?php echo htmlspecialchars($reserva['nombre_contacto']); ?></strong>
                                <br>
                                <?php if (!empty($reserva['telefono'])): ?>
                                    <small class="has-text-grey">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($reserva['telefono']); ?>
                                    </small>
                                <?php endif; ?>
                                <?php if (!empty($reserva['numero_identificacion'])): ?>
                                    <br>
                                    <small class="has-text-grey">
                                        <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($reserva['numero_identificacion']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="has-text-grey">-</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></td>
                        <td>
                            <span class="tag is-light">
                                <i class="fas fa-clock"></i>
                                <?php echo substr($reserva['hora_inicio'], 0, 5); ?> - <?php echo substr($reserva['hora_fin'], 0, 5); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $estado_class = [
                                'CONFIRMADA' => 'is-success',
                                'CANCELADA' => 'is-danger',
                                'PENDIENTE' => 'is-warning'
                            ];
                            $class = $estado_class[$reserva['estado']] ?? 'is-info';
                            ?>
                            <span class="tag <?php echo $class; ?>"><?php echo $reserva['estado']; ?></span>
                        </td>
                        <td>
                            <?php if (!empty($reserva['observaciones'])): ?>
                                <small><?php echo htmlspecialchars(substr($reserva['observaciones'], 0, 30)); ?>...</small>
                            <?php else: ?>
                                <small class="has-text-grey">-</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($reserva['fecha_creacion'])); ?></small>
                        </td>
                        <td>
                            <?php if ($reserva['estado'] != 'CANCELADA'): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="cancelar_id" value="<?php echo $reserva['id']; ?>">
                                    <button type="submit" class="button is-small is-danger"
                                            onclick="return confirm('¿Estás seguro de cancelar esta reserva?');">
                                        <i class="fas fa-times"></i>
                                        <span>Cancelar</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="has-text-grey">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <article class="message is-info">
            <div class="message-body">
                <i class="fas fa-info-circle"></i>
                No se encontraron reservas con los filtros seleccionados.
            </div>
        </article>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
