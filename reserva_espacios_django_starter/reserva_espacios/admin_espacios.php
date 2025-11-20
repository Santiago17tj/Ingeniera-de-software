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
                // Verificar si existe el campo imagen en la tabla
                $campo_imagen_existe = false;
                try {
                    $test = $pdo->query("SELECT imagen FROM espacios LIMIT 1");
                    $campo_imagen_existe = true;
                } catch (PDOException $e) {
                    // El campo no existe aún
                }
                
                $imagen_nombre = null;
                // Procesar imagen si se subió (para agregar, primero insertamos y luego subimos la imagen)
                // Por ahora, si hay imagen, la subiremos después de insertar
                $imagen_temp = null;
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK && $campo_imagen_existe) {
                    $imagen_temp = $_FILES['imagen'];
                }
                
                if (!$error) {
                    try {
                        // Primero insertar el espacio
                        $stmt = $pdo->prepare("INSERT INTO espacios (nombre, tipo, capacidad, descripcion, activo) 
                                              VALUES (?, ?, ?, ?, 1)");
                        $stmt->execute([$nombre, $tipo, $capacidad, $descripcion]);
                        $nuevo_id = $pdo->lastInsertId();
                        
                        // Si hay imagen, subirla ahora que tenemos el ID
                        if ($imagen_temp && $campo_imagen_existe) {
                            $resultado_imagen = subirImagenEspacio($imagen_temp, $nuevo_id);
                            if ($resultado_imagen['success']) {
                                // Actualizar el espacio con el nombre de la imagen
                                $stmt_img = $pdo->prepare("UPDATE espacios SET imagen = ? WHERE id = ?");
                                $stmt_img->execute([$resultado_imagen['nombre'], $nuevo_id]);
                            }
                            // Si falla la subida de imagen, continuamos de todas formas (el espacio ya está creado)
                        }
                        
                        $success = 'Espacio agregado exitosamente.';
                        // Redirigir para evitar reenvío del formulario
                        redirect('admin_espacios.php');
                    } catch (PDOException $e) {
                        $error = 'Error al agregar el espacio: ' . $e->getMessage();
                    }
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
                // Verificar si existe el campo imagen en la tabla
                $campo_imagen_existe = false;
                try {
                    $test = $pdo->query("SELECT imagen FROM espacios LIMIT 1");
                    $campo_imagen_existe = true;
                } catch (PDOException $e) {
                    // El campo no existe aún
                }
                
                // Obtener imagen actual
                $imagen_actual = null;
                if ($campo_imagen_existe) {
                    $stmt_img = $pdo->prepare("SELECT imagen FROM espacios WHERE id = ?");
                    $stmt_img->execute([$id]);
                    $imagen_actual = $stmt_img->fetchColumn();
                }
                
                $imagen_nombre = $imagen_actual; // Mantener la actual por defecto
                
                // Procesar nueva imagen si se subió
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK && $campo_imagen_existe) {
                    $resultado_imagen = subirImagenEspacio($_FILES['imagen'], $id, $imagen_actual);
                    if ($resultado_imagen['success']) {
                        $imagen_nombre = $resultado_imagen['nombre'];
                    } else {
                        $error = $resultado_imagen['error'];
                    }
                }
                
                if (!$error) {
                    try {
                        if ($campo_imagen_existe) {
                            // Si no se subió nueva imagen, mantener la actual
                            if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                                $imagen_nombre = $imagen_actual;
                            }
                            $stmt = $pdo->prepare("UPDATE espacios SET nombre = ?, tipo = ?, capacidad = ?, descripcion = ?, imagen = ? 
                                                  WHERE id = ?");
                            $stmt->execute([$nombre, $tipo, $capacidad, $descripcion, $imagen_nombre, $id]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE espacios SET nombre = ?, tipo = ?, capacidad = ?, descripcion = ? 
                                                  WHERE id = ?");
                            $stmt->execute([$nombre, $tipo, $capacidad, $descripcion, $id]);
                        }
                        
                        $success = 'Espacio actualizado exitosamente.';
                        // Redirigir para evitar reenvío del formulario
                        redirect('admin_espacios.php');
                    } catch (PDOException $e) {
                        $error = 'Error al actualizar el espacio: ' . $e->getMessage();
                    }
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
        <form method="post" id="form-espacio" enctype="multipart/form-data">
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

                <div class="field">
                    <label class="label">Imagen del Espacio</label>
                    <div class="control">
                        <div class="file has-name">
                            <label class="file-label">
                                <input class="file-input" type="file" name="imagen" id="imagen" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <span class="file-cta">
                                    <span class="file-icon">
                                        <i class="fas fa-upload"></i>
                                    </span>
                                    <span class="file-label">Elegir imagen...</span>
                                </span>
                                <span class="file-name" id="imagen-nombre">Ninguna imagen seleccionada</span>
                            </label>
                        </div>
                    </div>
                    <p class="help">Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo: 5MB</p>
                    <div id="imagen-preview" style="margin-top: 10px; display: none;">
                        <figure class="image is-128x128">
                            <img id="imagen-preview-img" src="" alt="Vista previa">
                        </figure>
                    </div>
                    <div id="imagen-actual" style="margin-top: 10px; display: none;">
                        <p class="help">Imagen actual:</p>
                        <figure class="image is-128x128">
                            <img id="imagen-actual-img" src="" alt="Imagen actual">
                        </figure>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <button type="submit" class="button is-primary" style="min-width: 120px;">
                    <i class="fas fa-save"></i>
                    <span style="margin-left: 0.5rem;">Guardar</span>
                </button>
                <button type="button" class="button" onclick="cerrarModal()" style="min-width: 120px;">
                    <i class="fas fa-times"></i>
                    <span style="margin-left: 0.5rem;">Cancelar</span>
                </button>
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
        
        // Resetear vista de imagen
        document.getElementById('imagen-preview').style.display = 'none';
        document.getElementById('imagen-actual').style.display = 'none';
        document.getElementById('imagen-nombre').textContent = 'Ninguna imagen seleccionada';
    }
    document.getElementById('modal-espacio').classList.add('is-active');
}

// Manejar selección de imagen
document.addEventListener('DOMContentLoaded', function() {
    const imagenInput = document.getElementById('imagen');
    if (imagenInput) {
        imagenInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const imagenNombre = document.getElementById('imagen-nombre');
            const imagenPreview = document.getElementById('imagen-preview');
            const imagenPreviewImg = document.getElementById('imagen-preview-img');
            const imagenActual = document.getElementById('imagen-actual');
            
            if (file) {
                imagenNombre.textContent = file.name;
                
                // Mostrar vista previa
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagenPreviewImg.src = e.target.result;
                    imagenPreview.style.display = 'block';
                    imagenActual.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                imagenNombre.textContent = 'Ninguna imagen seleccionada';
                imagenPreview.style.display = 'none';
            }
        });
    }
});

function editarEspacio(espacio) {
    document.getElementById('modal-titulo').textContent = 'Editar Espacio';
    document.getElementById('accion').value = 'editar';
    document.getElementById('espacio_id').value = espacio.id;
    document.getElementById('nombre').value = espacio.nombre;
    document.getElementById('tipo').value = espacio.tipo;
    document.getElementById('capacidad').value = espacio.capacidad;
    document.getElementById('descripcion').value = espacio.descripcion || '';
    
    // Manejar imagen actual
    const imagenActual = document.getElementById('imagen-actual');
    const imagenActualImg = document.getElementById('imagen-actual-img');
    const imagenPreview = document.getElementById('imagen-preview');
    
    if (espacio.imagen) {
        imagenActualImg.src = 'uploads/espacios/' + espacio.imagen;
        imagenActual.style.display = 'block';
        imagenPreview.style.display = 'none';
    } else {
        imagenActual.style.display = 'none';
    }
    
    // Resetear input de imagen
    document.getElementById('imagen').value = '';
    document.getElementById('imagen-nombre').textContent = 'Ninguna imagen seleccionada';
    
    document.getElementById('modal-espacio').classList.add('is-active');
}

function cerrarModal() {
    const modal = document.getElementById('modal-espacio');
    if (modal) {
        modal.classList.remove('is-active');
    }
}

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModal();
    }
});

function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de eliminar este espacio? Esta acción no se puede deshacer.')) {
        document.getElementById('eliminar_id').value = id;
        document.getElementById('form-eliminar').submit();
    }
}
</script>

<style>
.modal {
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}
.modal.is-active {
    display: flex;
}
.modal-card {
    background-color: #fefefe;
    margin: 2% auto;
    border-radius: 6px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    height: auto;
    display: flex;
    flex-direction: column;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    position: relative;
    z-index: 1001;
}
.modal-card-head {
    flex-shrink: 0;
    padding: 1rem;
    border-bottom: 1px solid #dbdbdb;
    background-color: #fff;
}
.modal-card-body {
    flex: 1 1 0;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 1.5rem;
    -webkit-overflow-scrolling: touch;
    min-height: 0;
    max-height: calc(90vh - 160px);
}
.modal-card-foot {
    flex-shrink: 0;
    padding: 1rem;
    border-top: 1px solid #dbdbdb;
    background-color: #fafafa;
    display: flex !important;
    justify-content: flex-end;
    gap: 0.5rem;
    position: relative;
    z-index: 10;
    visibility: visible !important;
    opacity: 1 !important;
}
.modal-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    cursor: pointer;
    z-index: -1;
}

/* Asegurar que el scroll funcione en todos los navegadores */
.modal-card-body::-webkit-scrollbar {
    width: 8px;
}
.modal-card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.modal-card-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.modal-card-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<?php require_once 'footer.php'; ?>
