<?php
$page_title = 'Editar Reserva';
require_once 'config.php';
requireLogin();

$pdo = getDB();
$usuario = getUsuario();
$error = '';
$id = intval($_GET['id'] ?? 0);

// Verificar que la reserva existe y pertenece al usuario
$stmt = $pdo->prepare("SELECT r.*, e.nombre as espacio_nombre 
                       FROM reservas r 
                       JOIN espacios e ON r.espacio_id = e.id 
                       WHERE r.id = ? AND r.usuario_id = ?");
$stmt->execute([$id, $usuario['id']]);
$reserva = $stmt->fetch();

if (!$reserva) {
    flashMessage('Reserva no encontrada o no tienes permiso para editarla.', 'danger');
    redirect('reservas.php');
}

// No permitir editar reservas canceladas
if ($reserva['estado'] === 'CANCELADA') {
    flashMessage('No puedes editar una reserva cancelada.', 'warning');
    redirect('reservas.php');
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $espacio_id = intval($_POST['espacio_id'] ?? 0);
    $fecha = sanitize($_POST['fecha'] ?? '');
    $hora_inicio = sanitize($_POST['hora_inicio'] ?? '');
    $hora_fin = sanitize($_POST['hora_fin'] ?? '');
    $observaciones = sanitize($_POST['observaciones'] ?? '');
    
    // Validaciones
    if (empty($espacio_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif ($hora_fin <= $hora_inicio) {
        $error = 'La hora de fin debe ser posterior a la hora de inicio.';
    } elseif (!verificarDisponibilidad($espacio_id, $fecha, $hora_inicio, $hora_fin, $id)) {
        $error = 'El espacio ya está reservado en ese rango horario.';
    } else {
        // Actualizar reserva
        $stmt = $pdo->prepare("UPDATE reservas 
                              SET espacio_id = ?, fecha = ?, hora_inicio = ?, hora_fin = ?, observaciones = ?
                              WHERE id = ? AND usuario_id = ?");
        if ($stmt->execute([$espacio_id, $fecha, $hora_inicio, $hora_fin, $observaciones, $id, $usuario['id']])) {
            flashMessage('Reserva actualizada correctamente.', 'success');
            redirect('reservas.php');
        } else {
            $error = 'Error al actualizar la reserva. Por favor, intenta de nuevo.';
        }
    }
}

$espacios = $pdo->query("SELECT * FROM espacios WHERE activo = 1 ORDER BY nombre")->fetchAll();

require_once 'header.php';
?>

<section class="section">
    <div class="columns is-centered">
        <div class="column is-8">
            <h1 class="title">
                <i class="fas fa-edit"></i>
                Editar Reserva
            </h1>
            <div class="box">
                <?php if ($error): ?>
                    <div class="notification is-danger is-light">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="notification is-info is-light">
                    <strong>Nota:</strong> Al editar esta reserva, se verificará nuevamente la disponibilidad del espacio.
                </div>
                
                <form method="post">
                    <div class="field">
                        <label class="label">Espacio</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="espacio_id" required>
                                    <?php foreach ($espacios as $espacio): ?>
                                        <option value="<?php echo $espacio['id']; ?>"
                                                <?php echo $reserva['espacio_id'] == $espacio['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($espacio['nombre']); ?> 
                                            (<?php echo htmlspecialchars($espacio['tipo']); ?> - 
                                            Capacidad: <?php echo $espacio['capacidad']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Fecha</label>
                        <div class="control">
                            <input class="input" type="date" name="fecha" required 
                                   value="<?php echo htmlspecialchars($reserva['fecha']); ?>"
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="field">
                                <label class="label">Hora Inicio</label>
                                <div class="control">
                                    <input class="input" type="time" name="hora_inicio" required
                                           value="<?php echo htmlspecialchars($reserva['hora_inicio']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">Hora Fin</label>
                                <div class="control">
                                    <input class="input" type="time" name="hora_fin" required
                                           value="<?php echo htmlspecialchars($reserva['hora_fin']); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Observaciones</label>
                        <div class="control">
                            <textarea class="textarea" name="observaciones" rows="3" 
                                      placeholder="Información adicional sobre la reserva (opcional)"><?php echo htmlspecialchars($reserva['observaciones']); ?></textarea>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button class="button is-primary" type="submit">
                                <i class="fas fa-save"></i>
                                <span style="margin-left: 0.5rem;">Guardar Cambios</span>
                            </button>
                        </div>
                        <div class="control">
                            <a class="button is-light" href="reservas.php">
                                <i class="fas fa-times"></i>
                                <span style="margin-left: 0.5rem;">Cancelar</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
