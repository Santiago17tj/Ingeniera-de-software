<?php
$page_title = 'Mi Perfil';
require_once 'config.php';
requireLogin();

$pdo = getDB();
$usuario = getUsuario();
$error = '';
$success = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'actualizar_perfil') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $apellido = sanitize($_POST['apellido'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($nombre) || empty($apellido) || empty($email)) {
            $error = 'Por favor, completa todos los campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, ingresa un email válido.';
        } else {
            // Verificar si el email ya existe (excluyendo el actual)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $usuario['id']]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'El email ya está en uso por otro usuario.';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$nombre, $apellido, $email, $usuario['id']])) {
                    $success = 'Perfil actualizado correctamente.';
                    // Recargar usuario
                    $usuario = getUsuario();
                } else {
                    $error = 'Error al actualizar el perfil.';
                }
            }
        }
    } elseif ($accion === 'cambiar_password') {
        $password_actual = $_POST['password_actual'] ?? '';
        $password_nueva = $_POST['password_nueva'] ?? '';
        $password_confirmar = $_POST['password_confirmar'] ?? '';
        
        if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
            $error = 'Por favor, completa todos los campos de contraseña.';
        } elseif (strlen($password_nueva) < 6) {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($password_nueva !== $password_confirmar) {
            $error = 'Las contraseñas nuevas no coinciden.';
        } else {
            // Verificar contraseña actual
            $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            $hash_actual = $stmt->fetchColumn();
            
            if (!password_verify($password_actual, $hash_actual)) {
                $error = 'La contraseña actual es incorrecta.';
            } else {
                $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                if ($stmt->execute([$password_hash, $usuario['id']])) {
                    $success = 'Contraseña actualizada correctamente.';
                } else {
                    $error = 'Error al actualizar la contraseña.';
                }
            }
        }
    }
}

// Obtener estadísticas del usuario
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_reservas,
        SUM(CASE WHEN estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas,
        SUM(CASE WHEN estado = 'CANCELADA' THEN 1 ELSE 0 END) as canceladas,
        MIN(fecha) as primera_reserva
    FROM reservas
    WHERE usuario_id = ?
");
$stmt->execute([$usuario['id']]);
$stats = $stmt->fetch();

// Obtener historial de reservas (últimas 10)
$stmt = $pdo->prepare("
    SELECT r.*, e.nombre as espacio_nombre, e.tipo as espacio_tipo
    FROM reservas r
    JOIN espacios e ON r.espacio_id = e.id
    WHERE r.usuario_id = ?
    ORDER BY r.fecha DESC, r.hora_inicio DESC
    LIMIT 10
");
$stmt->execute([$usuario['id']]);
$historial = $stmt->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-user-circle"></i>
    Mi Perfil
</h1>

<div class="buttons" style="margin-bottom: 1rem;">
    <a class="button is-link" href="index.php">
        <i class="fas fa-home"></i>
        <span style="margin-left: 0.5rem;">Inicio</span>
    </a>
    <a class="button is-info" href="reservas.php">
        <i class="fas fa-list"></i>
        <span style="margin-left: 0.5rem;">Mis Reservas</span>
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

<!-- Información del Usuario -->
<div class="columns">
    <div class="column is-4">
        <div class="box has-text-centered">
            <div style="font-size: 80px; color: #3273dc;">
                <i class="fas fa-user-circle"></i>
            </div>
            <h2 class="title is-4" style="margin-top: 1rem;">
                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
            </h2>
            <p class="subtitle is-6">@<?php echo htmlspecialchars($usuario['username']); ?></p>
            <p class="has-text-grey">
                <i class="fas fa-envelope"></i>
                <?php echo htmlspecialchars($usuario['email']); ?>
            </p>
            <?php if ($usuario['is_admin']): ?>
                <span class="tag is-danger" style="margin-top: 1rem;">
                    <i class="fas fa-crown"></i>
                    <span style="margin-left: 0.3rem;">Administrador</span>
                </span>
            <?php endif; ?>
            <p class="is-size-7 has-text-grey" style="margin-top: 1rem;">
                Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
            </p>
        </div>

        <!-- Estadísticas -->
        <div class="box">
            <h3 class="subtitle is-5">
                <i class="fas fa-chart-bar"></i>
                Estadísticas
            </h3>
            <div class="content">
                <div class="level" style="margin-bottom: 0.5rem;">
                    <div class="level-left">
                        <span>Total Reservas:</span>
                    </div>
                    <div class="level-right">
                        <strong><?php echo $stats['total_reservas']; ?></strong>
                    </div>
                </div>
                <div class="level" style="margin-bottom: 0.5rem;">
                    <div class="level-left">
                        <span>Confirmadas:</span>
                    </div>
                    <div class="level-right">
                        <span class="tag is-success"><?php echo $stats['confirmadas']; ?></span>
                    </div>
                </div>
                <div class="level" style="margin-bottom: 0.5rem;">
                    <div class="level-left">
                        <span>Canceladas:</span>
                    </div>
                    <div class="level-right">
                        <span class="tag is-danger"><?php echo $stats['canceladas']; ?></span>
                    </div>
                </div>
                <?php if ($stats['primera_reserva']): ?>
                    <div class="level">
                        <div class="level-left">
                            <span>Primera Reserva:</span>
                        </div>
                        <div class="level-right">
                            <small><?php echo date('d/m/Y', strtotime($stats['primera_reserva'])); ?></small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="column is-8">
        <!-- Editar Perfil -->
        <div class="box">
            <h3 class="subtitle">
                <i class="fas fa-user-edit"></i>
                Información Personal
            </h3>
            <form method="post">
                <input type="hidden" name="accion" value="actualizar_perfil">
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Nombre</label>
                            <div class="control">
                                <input class="input" type="text" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Apellido</label>
                            <div class="control">
                                <input class="input" type="text" name="apellido" 
                                       value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            <i class="fas fa-save"></i>
                            <span style="margin-left: 0.5rem;">Guardar Cambios</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="box">
            <h3 class="subtitle">
                <i class="fas fa-key"></i>
                Cambiar Contraseña
            </h3>
            <form method="post">
                <input type="hidden" name="accion" value="cambiar_password">
                <div class="field">
                    <label class="label">Contraseña Actual</label>
                    <div class="control">
                        <input class="input" type="password" name="password_actual" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Nueva Contraseña</label>
                    <div class="control">
                        <input class="input" type="password" name="password_nueva" required>
                    </div>
                    <p class="help">Mínimo 6 caracteres</p>
                </div>
                <div class="field">
                    <label class="label">Confirmar Nueva Contraseña</label>
                    <div class="control">
                        <input class="input" type="password" name="password_confirmar" required>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-warning">
                            <i class="fas fa-lock"></i>
                            <span style="margin-left: 0.5rem;">Cambiar Contraseña</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Historial de Reservas -->
        <div class="box">
            <h3 class="subtitle">
                <i class="fas fa-history"></i>
                Historial Reciente
            </h3>
            <?php if (count($historial) > 0): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Espacio</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $reserva): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($reserva['espacio_nombre']); ?></strong>
                                        <br>
                                        <small class="has-text-grey"><?php echo htmlspecialchars($reserva['espacio_tipo']); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></td>
                                    <td>
                                        <small><?php echo substr($reserva['hora_inicio'], 0, 5); ?> - <?php echo substr($reserva['hora_fin'], 0, 5); ?></small>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="reservas.php" class="button is-small is-light">Ver todas mis reservas</a>
            <?php else: ?>
                <article class="message is-info">
                    <div class="message-body">
                        <i class="fas fa-info-circle"></i>
                        No tienes reservas en tu historial.
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
