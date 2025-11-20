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
    $nombre_contacto = trim(sanitize($_POST['nombre_contacto'] ?? ''));
    $telefono = trim(sanitize($_POST['telefono'] ?? ''));
    $numero_identificacion = trim(sanitize($_POST['numero_identificacion'] ?? ''));
    
    // Validaciones
    if (empty($espacio_id) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif (empty($nombre_contacto)) {
        $error = 'El nombre completo es obligatorio.';
    } elseif (empty($telefono)) {
        $error = 'El número de teléfono es obligatorio.';
    } elseif (empty($numero_identificacion)) {
        $error = 'El número de identificación es obligatorio.';
    } elseif ($hora_fin <= $hora_inicio) {
        $error = 'La hora de fin debe ser posterior a la hora de inicio.';
    } elseif (!verificarDisponibilidad($espacio_id, $fecha, $hora_inicio, $hora_fin)) {
        $error = 'El espacio ya está reservado en ese rango horario.';
    } else {
        // Crear reserva
        $stmt = $pdo->prepare("INSERT INTO reservas (espacio_id, usuario_id, fecha, hora_inicio, hora_fin, nombre_contacto, telefono, numero_identificacion, observaciones, estado) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'CONFIRMADA')");
        if ($stmt->execute([$espacio_id, $usuario['id'], $fecha, $hora_inicio, $hora_fin, $nombre_contacto, $telefono, $numero_identificacion, $observaciones])) {
            // Obtener datos del espacio para email
            $stmt_espacio = $pdo->prepare("SELECT * FROM espacios WHERE id = ?");
            $stmt_espacio->execute([$espacio_id]);
            $espacio_data = $stmt_espacio->fetch();
            
            // Preparar datos de la reserva para email
            $reserva_data = [
                'fecha' => $fecha,
                'hora_inicio' => $hora_inicio,
                'hora_fin' => $hora_fin,
                'observaciones' => $observaciones,
                'nombre_contacto' => $nombre_contacto,
                'telefono' => $telefono,
                'numero_identificacion' => $numero_identificacion
            ];
            
            // Enviar notificación por email
            require_once 'email_config.php';
            enviarNotificacionReservaConfirmada($usuario, $reserva_data, $espacio_data);
            
            flashMessage('Reserva creada correctamente. Te hemos enviado un email de confirmación.', 'success');
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

                    <hr style="margin: 1.5rem 0;">

                    <h3 class="subtitle is-5" style="margin-bottom: 1rem;">
                        <i class="fas fa-user"></i>
                        Datos de Contacto
                    </h3>
                    <p class="help" style="margin-bottom: 1rem;">
                        Por favor, proporciona tus datos de contacto para dejar constancia de la reserva.
                    </p>

                    <div class="field">
                        <label class="label">
                            Nombre Completo
                            <span class="has-text-danger">*</span>
                        </label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="nombre_contacto" 
                                   placeholder="Ej: Juan Pérez García" required
                                   value="<?php echo htmlspecialchars($_POST['nombre_contacto'] ?? ''); ?>">
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="field">
                                <label class="label">
                                    Número de Teléfono
                                    <span class="has-text-danger">*</span>
                                </label>
                                <div class="control has-icons-left">
                                    <input class="input" type="tel" name="telefono" 
                                           placeholder="Ej: 3001234567" required
                                           pattern="[0-9+\-\s()]+"
                                           value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>">
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                </div>
                                <p class="help">Incluye el código de área si es necesario</p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">
                                    Número de Identificación
                                    <span class="has-text-danger">*</span>
                                </label>
                                <div class="control has-icons-left">
                                    <input class="input" type="text" name="numero_identificacion" 
                                           placeholder="Ej: 1234567890" required
                                           value="<?php echo htmlspecialchars($_POST['numero_identificacion'] ?? ''); ?>">
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                </div>
                                <p class="help">CC, CE, NIT, etc.</p>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Observaciones</label>
                        <div class="control">
                            <textarea class="textarea" name="observaciones" rows="3" 
                                      placeholder="Información adicional sobre la reserva (opcional)"><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
                        </div>
                        <p class="help">Puedes agregar detalles adicionales sobre tu reserva</p>
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
