<?php
$page_title = 'Nueva Reserva';
require_once 'config.php';
requireLogin();

$pdo = getDB();
$usuario = getUsuario();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $espacio_id = intval($_POST['espacio_id'] ?? 0);
    $fecha = sanitize($_POST['fecha'] ?? '');
    $hora_inicio = sanitize($_POST['hora_inicio'] ?? '');
    $hora_fin = sanitize($_POST['hora_fin'] ?? '');
    $observaciones = sanitize($_POST['observaciones'] ?? '');
    
    // Validaciones
    if (empty($espacio_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif ($hora_fin <= $hora_inicio) {
        $error = 'La hora de fin debe ser posterior a la hora de inicio.';
    } elseif (!verificarDisponibilidad($espacio_id, $fecha, $hora_inicio, $hora_fin)) {
        $error = 'El espacio ya está reservado en ese rango horario.';
    } else {
        // Crear reserva
        $stmt = $pdo->prepare("INSERT INTO reservas (espacio_id, usuario_id, fecha, hora_inicio, hora_fin, observaciones, estado) 
                              VALUES (?, ?, ?, ?, ?, ?, 'CONFIRMADA')");
        if ($stmt->execute([$espacio_id, $usuario['id'], $fecha, $hora_inicio, $hora_fin, $observaciones])) {
            flashMessage('Reserva creada correctamente.', 'success');
            redirect('reservas.php');
        } else {
            $error = 'Error al crear la reserva. Por favor, intenta de nuevo.';
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
                <i class="fas fa-plus-circle"></i>
                Crear Nueva Reserva
            </h1>
            <div class="box">
                <?php if ($error): ?>
                    <div class="notification is-danger is-light">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <div class="field">
                        <label class="label">Espacio</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="espacio_id" required>
                                    <option value="">Selecciona un espacio</option>
                                    <?php foreach ($espacios as $espacio): ?>
                                        <option value="<?php echo $espacio['id']; ?>">
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
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="field">
                                <label class="label">Hora Inicio</label>
                                <div class="control">
                                    <input class="input" type="time" name="hora_inicio" required>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">Hora Fin</label>
                                <div class="control">
                                    <input class="input" type="time" name="hora_fin" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button class="button is-primary" type="submit">
                                <i class="fas fa-save"></i>
                                <span style="margin-left: 0.5rem;">Guardar Reserva</span>
                            </button>
                        </div>
                        <div class="control">
                            <a class="button is-light" href="index.php">
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

