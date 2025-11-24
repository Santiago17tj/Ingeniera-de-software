<?php
$page_title = 'Todas las Reservas';
require_once 'config.php';
requireAdmin();

$pdo = getDB();

// Cambiar estado de reserva
if (isset($_GET['cambiar_estado'])) {
    $id = intval($_GET['cambiar_estado']);
    $nuevo_estado = sanitize($_GET['estado'] ?? 'CONFIRMADA');
    
    if (in_array($nuevo_estado, ['CONFIRMADA', 'CANCELADA', 'PENDIENTE'])) {
        $stmt = $pdo->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        flashMessage('Estado de reserva actualizado.', 'success');
        redirect('admin_reservas.php');
    }
}

// Filtrar reservas
$filtro_estado = $_GET['estado'] ?? 'TODAS';
$filtro_espacio = intval($_GET['espacio_id'] ?? 0);

// Verificar si los campos de contacto existen
$campos_contacto_existen = false;
try {
    $test = $pdo->query("SELECT nombre_contacto, telefono, numero_identificacion FROM reservas LIMIT 1");
    $campos_contacto_existen = true;
} catch (PDOException $e) {
    // Los campos no existen aún
}

$sql = "SELECT r.*, e.nombre as espacio_nombre, e.tipo as espacio_tipo, u.username, u.email 
        FROM reservas r 
        JOIN espacios e ON r.espacio_id = e.id 
        JOIN usuarios u ON r.usuario_id = u.id 
        WHERE 1=1";

$params = [];

if ($filtro_estado !== 'TODAS') {
    $sql .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

if ($filtro_espacio > 0) {
    $sql .= " AND r.espacio_id = ?";
    $params[] = $filtro_espacio;
}

$sql .= " ORDER BY r.fecha DESC, r.hora_inicio DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

$espacios = $pdo->query("SELECT * FROM espacios WHERE activo = 1 ORDER BY nombre")->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-calendar"></i>
    Todas las Reservas
</h1>

<div class="box" style="margin-bottom: 2rem;">
    <h2 class="subtitle">Filtros</h2>
    <form method="get" class="columns">
        <div class="column is-4">
            <div class="field">
                <label class="label">Estado</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="estado">
                            <option value="TODAS" <?php echo $filtro_estado === 'TODAS' ? 'selected' : ''; ?>>Todas</option>
                            <option value="CONFIRMADA" <?php echo $filtro_estado === 'CONFIRMADA' ? 'selected' : ''; ?>>Confirmadas</option>
                            <option value="PENDIENTE" <?php echo $filtro_estado === 'PENDIENTE' ? 'selected' : ''; ?>>Pendientes</option>
                            <option value="CANCELADA" <?php echo $filtro_estado === 'CANCELADA' ? 'selected' : ''; ?>>Canceladas</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-4">
            <div class="field">
                <label class="label">Espacio</label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="espacio_id">
                            <option value="0">Todos los espacios</option>
                            <?php foreach ($espacios as $espacio): ?>
                                <option value="<?php echo $espacio['id']; ?>" <?php echo $filtro_espacio == $espacio['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($espacio['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-2">
            <label class="label">&nbsp;</label>
            <div class="control">
                <button class="button is-link is-fullwidth" type="submit">
                    <i class="fas fa-filter"></i>
                    <span style="margin-left: 0.5rem;">Filtrar</span>
                </button>
            </div>
        </div>
        <div class="column is-2">
            <label class="label">&nbsp;</label>
            <div class="control">
                <a href="admin_reservas.php" class="button is-light is-fullwidth">
                    <i class="fas fa-times"></i>
                    <span style="margin-left: 0.5rem;">Limpiar</span>
                </a>
            </div>
        </div>
    </form>
</div>

<div class="box">
    <h2 class="subtitle">Reservas (<?php echo count($reservas); ?>)</h2>
    <?php if (count($reservas) > 0): ?>
    <div class="table-container">
        <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Espacio</th>
                    <th>Usuario</th>
                    <?php if ($campos_contacto_existen): ?>
                    <th>Contacto</th>
                    <?php endif; ?>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Estado</th>
                    <th>Creado</th>
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
                            <small class="has-text-grey"><?php echo htmlspecialchars($reserva['email']); ?></small>
                        </td>
                        <?php if ($campos_contacto_existen): ?>
                        <td>
                            <?php if (!empty($reserva['nombre_contacto'])): ?>
                                <strong><?php echo htmlspecialchars($reserva['nombre_contacto']); ?></strong>
                                <?php if (!empty($reserva['telefono'])): ?>
                                    <br>
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
                            <?php echo substr($reserva['hora_inicio'], 0, 5); ?> - 
                            <?php echo substr($reserva['hora_fin'], 0, 5); ?>
                        </td>
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
                            <div class="buttons">
                                <?php if ($reserva['estado'] != 'CONFIRMADA'): ?>
                                    <a href="?cambiar_estado=<?php echo $reserva['id']; ?>&estado=CONFIRMADA" 
                                       class="button is-small is-success is-light"
                                       title="Confirmar">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($reserva['estado'] != 'CANCELADA'): ?>
                                    <a href="?cambiar_estado=<?php echo $reserva['id']; ?>&estado=CANCELADA" 
                                       class="button is-small is-danger is-light"
                                       title="Cancelar">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($reserva['estado'] != 'PENDIENTE'): ?>
                                    <a href="?cambiar_estado=<?php echo $reserva['id']; ?>&estado=PENDIENTE" 
                                       class="button is-small is-warning is-light"
                                       title="Marcar como Pendiente">
                                        <i class="fas fa-clock"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>No hay reservas con los filtros seleccionados.</p>
    <?php endif; ?>
</div>

<div style="margin-top: 2rem;">
    <a href="admin.php" class="button is-light">
        <i class="fas fa-arrow-left"></i>
        <span style="margin-left: 0.5rem;">Volver al Panel</span>
    </a>
</div>

<?php require_once 'footer.php'; ?>

