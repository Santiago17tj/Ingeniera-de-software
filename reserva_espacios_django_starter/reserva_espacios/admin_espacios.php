<?php
$page_title = 'Gestión de Espacios';
require_once 'config.php';
requireAdmin();

$pdo = getDB();
$error = '';
$success = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'agregar') {
        $nombre = sanitize($_POST['nombre'] ?? '');
        $tipo = sanitize($_POST['tipo'] ?? '');
        $capacidad = intval($_POST['capacidad'] ?? 0);
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        
        if (empty($nombre) || empty($tipo) || $capacidad <= 0) {
            $error = 'Por favor, completa todos los campos obligatorios.';
        } else {
            // Verificar si el nombre ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM espacios WHERE nombre = ?");
            $stmt->execute([$nombre]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Ya existe un espacio con ese nombre.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO espacios (nombre, tipo, capacidad, descripcion, activo) 
                                      VALUES (?, ?, ?, ?, 1)");
                if ($stmt->execute([$nombre, $tipo, $capacidad, $descripcion])) {
                    $success = 'Espacio agregado exitosamente.';
                } else {
                    $error = 'Error al agregar el espacio.';
                }
            }
        }
    } elseif ($accion === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $nombre = sanitize($_POST['nombre'] ?? '');
        $tipo = sanitize($_POST['tipo'] ?? '');
        $capacidad = intval($_POST['capacidad'] ?? 0);
        $descripcion = sanitize($_POST['descripcion'] ?? '');
        
        if ($id > 0 && !empty($nombre) && !empty($tipo) && $capacidad > 0) {
            // Verificar si el nombre ya existe (excluyendo el actual)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM espacios WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Ya existe otro espacio con ese nombre.';
            } else {
                $stmt = $pdo->prepare("UPDATE espacios SET nombre = ?, tipo = ?, capacidad = ?, descripcion = ? 
                                      WHERE id = ?");
                if ($stmt->execute([$nombre, $tipo, $capacidad, $descripcion, $id])) {
                    $success = 'Espacio actualizado exitosamente.';
                } else {
                    $error = 'Error al actualizar el espacio.';
                }
            }
        } else {
            $error = 'Datos inválidos.';
        }
    } elseif ($accion === 'toggle_activo') {
        $id = intval($_POST['id'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);
        
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE espacios SET activo = ? WHERE id = ?");
            if ($stmt->execute([$activo, $id])) {
                $success = 'Estado actualizado exitosamente.';
            } else {
                $error = 'Error al actualizar el estado.';
            }
        }
    } elseif ($accion === 'eliminar') {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id > 0) {
            // Verificar si hay reservas asociadas
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE espacio_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'No se puede eliminar el espacio porque tiene reservas asociadas. Puedes desactivarlo en su lugar.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM espacios WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $success = 'Espacio eliminado exitosamente.';
                } else {
                    $error = 'Error al eliminar el espacio.';
                }
            }
        }
    }
}

// Obtener todos los espacios
$espacios = $pdo->query("SELECT * FROM espacios ORDER BY activo DESC, nombre")->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-building"></i>
    Gestión de Espacios
</h1>

<div class="buttons" style="margin-bottom: 1rem;">
    <a class="button is-link" href="admin.php">
        <i class="fas fa-arrow-left"></i>
        <span style="margin-left: 0.5rem;">Volver al Panel</span>
    </a>
    <button class="button is-primary" onclick="mostrarModal('agregar')">
        <i class="fas fa-plus-circle"></i>
        <span style="margin-left: 0.5rem;">Agregar Nuevo Espacio</span>
    </button>
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

<div class="box">
    <h2 class="subtitle">Espacios Registrados (<?php echo count($espacios); ?>)</h2>
    
    <?php if (count($espacios) > 0): ?>
    <div class="table-container">
        <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Capacidad</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($espacios as $espacio): ?>
                    <tr>
                        <td><?php echo $espacio['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($espacio['nombre']); ?></strong></td>
                        <td>
                            <span class="tag is-info"><?php echo htmlspecialchars($espacio['tipo']); ?></span>
                        </td>
                        <td>
                            <i class="fas fa-users"></i>
                            <?php echo $espacio['capacidad']; ?>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars(substr($espacio['descripcion'], 0, 50)); ?><?php echo strlen($espacio['descripcion']) > 50 ? '...' : ''; ?></small>
                        </td>
                        <td>
                            <?php if ($espacio['activo']): ?>
                                <span class="tag is-success">Activo</span>
                            <?php else: ?>
                                <span class="tag is-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="buttons are-small">
                                <button class="button is-info" onclick='editarEspacio(<?php echo json_encode($espacio); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="accion" value="toggle_activo">
                                    <input type="hidden" name="id" value="<?php echo $espacio['id']; ?>">
                                    <input type="hidden" name="activo" value="<?php echo $espacio['activo'] ? 0 : 1; ?>">
                                    <button type="submit" class="button is-<?php echo $espacio['activo'] ? 'warning' : 'success'; ?>">
                                        <i class="fas fa-<?php echo $espacio['activo'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                </form>
                                <button class="button is-danger" onclick="confirmarEliminar(<?php echo $espacio['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
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
                No hay espacios registrados. Agrega el primero.
            </div>
        </article>
    <?php endif; ?>
</div>

<!-- Modal para agregar/editar espacio -->
<div id="modal-espacio" class="modal">
    <div class="modal-background" onclick="cerrarModal()"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title" id="modal-titulo">Agregar Nuevo Espacio</p>
            <button class="delete" aria-label="close" onclick="cerrarModal()"></button>
        </header>
        <form method="post" id="form-espacio">
            <section class="modal-card-body">
                <input type="hidden" name="accion" id="accion" value="agregar">
                <input type="hidden" name="id" id="espacio_id" value="">
                
                <div class="field">
                    <label class="label">Nombre del Espacio *</label>
                    <div class="control">
                        <input class="input" type="text" name="nombre" id="nombre" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Tipo *</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="tipo" id="tipo" required>
                                <option value="SALA">Sala de Reuniones</option>
                                <option value="AUDI">Auditorio</option>
                                <option value="CANCHA">Cancha Deportiva</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Capacidad *</label>
                    <div class="control">
                        <input class="input" type="number" name="capacidad" id="capacidad" min="1" required>
                    </div>
                    <p class="help">Número máximo de personas</p>
                </div>

                <div class="field">
                    <label class="label">Descripción</label>
                    <div class="control">
                        <textarea class="textarea" name="descripcion" id="descripcion" rows="3"></textarea>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button type="submit" class="button is-primary">
                    <i class="fas fa-save"></i>
                    <span style="margin-left: 0.5rem;">Guardar</span>
                </button>
                <button type="button" class="button" onclick="cerrarModal()">Cancelar</button>
            </footer>
        </form>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form method="post" id="form-eliminar" style="display: none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="eliminar_id" value="">
</form>

<script>
function mostrarModal(accion) {
    if (accion === 'agregar') {
        document.getElementById('modal-titulo').textContent = 'Agregar Nuevo Espacio';
        document.getElementById('accion').value = 'agregar';
        document.getElementById('form-espacio').reset();
        document.getElementById('espacio_id').value = '';
    }
    document.getElementById('modal-espacio').classList.add('is-active');
}

function editarEspacio(espacio) {
    document.getElementById('modal-titulo').textContent = 'Editar Espacio';
    document.getElementById('accion').value = 'editar';
    document.getElementById('espacio_id').value = espacio.id;
    document.getElementById('nombre').value = espacio.nombre;
    document.getElementById('tipo').value = espacio.tipo;
    document.getElementById('capacidad').value = espacio.capacidad;
    document.getElementById('descripcion').value = espacio.descripcion || '';
    document.getElementById('modal-espacio').classList.add('is-active');
}

function cerrarModal() {
    document.getElementById('modal-espacio').classList.remove('is-active');
}

function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de eliminar este espacio? Esta acción no se puede deshacer.')) {
        document.getElementById('eliminar_id').value = id;
        document.getElementById('form-eliminar').submit();
    }
}
</script>

<style>
.modal {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php require_once 'footer.php'; ?>
