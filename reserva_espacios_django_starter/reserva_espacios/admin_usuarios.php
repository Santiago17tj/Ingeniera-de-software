<?php
$page_title = 'Gestión de Usuarios';
require_once 'config.php';
requireAdmin();

$pdo = getDB();
$error = '';
$success = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'toggle_activo') {
        $id = intval($_POST['id'] ?? 0);
        $is_active = intval($_POST['is_active'] ?? 0);
        
        if ($id > 0) {
            // No permitir desactivar al propio usuario
            $usuario_actual = getUsuario();
            if ($id == $usuario_actual['id']) {
                $error = 'No puedes desactivar tu propia cuenta.';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET is_active = ? WHERE id = ?");
                if ($stmt->execute([$is_active, $id])) {
                    $success = 'Estado del usuario actualizado exitosamente.';
                } else {
                    $error = 'Error al actualizar el estado del usuario.';
                }
            }
        }
    } elseif ($accion === 'toggle_admin') {
        $id = intval($_POST['id'] ?? 0);
        $is_admin = intval($_POST['is_admin'] ?? 0);
        
        if ($id > 0) {
            // No permitir quitarse a sí mismo los permisos de admin
            $usuario_actual = getUsuario();
            if ($id == $usuario_actual['id'] && $is_admin == 0) {
                $error = 'No puedes quitarte tus propios permisos de administrador.';
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET is_admin = ? WHERE id = ?");
                if ($stmt->execute([$is_admin, $id])) {
                    $success = 'Permisos del usuario actualizados exitosamente.';
                } else {
                    $error = 'Error al actualizar los permisos del usuario.';
                }
            }
        }
    }
}

// Obtener todos los usuarios con estadísticas
$usuarios = $pdo->query("
    SELECT u.*, 
           COUNT(r.id) as total_reservas,
           SUM(CASE WHEN r.estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as reservas_confirmadas,
           SUM(CASE WHEN r.estado = 'CANCELADA' THEN 1 ELSE 0 END) as reservas_canceladas
    FROM usuarios u
    LEFT JOIN reservas r ON u.id = r.usuario_id
    GROUP BY u.id
    ORDER BY u.is_admin DESC, u.fecha_registro DESC
")->fetchAll();

$usuario_actual = getUsuario();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-users"></i>
    Gestión de Usuarios
</h1>

<div class="buttons" style="margin-bottom: 1rem;">
    <a class="button is-link" href="admin.php">
        <i class="fas fa-arrow-left"></i>
        <span style="margin-left: 0.5rem;">Volver al Panel</span>
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

<div class="box">
    <h2 class="subtitle">Usuarios Registrados (<?php echo count($usuarios); ?>)</h2>
    
    <div class="table-container">
        <table class="table is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Nombre Completo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Reservas</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                            <?php if ($usuario['id'] == $usuario_actual['id']): ?>
                                <span class="tag is-light is-small">Tú</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($usuario['email']); ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                        </td>
                        <td>
                            <?php if ($usuario['is_admin']): ?>
                                <span class="tag is-danger">
                                    <i class="fas fa-crown"></i>
                                    <span style="margin-left: 0.3rem;">Admin</span>
                                </span>
                            <?php else: ?>
                                <span class="tag is-info">Usuario</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($usuario['is_active']): ?>
                                <span class="tag is-success">Activo</span>
                            <?php else: ?>
                                <span class="tag is-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="tag is-light">
                                Total: <?php echo $usuario['total_reservas']; ?>
                            </span>
                            <span class="tag is-success">
                                <i class="fas fa-check"></i> <?php echo $usuario['reservas_confirmadas']; ?>
                            </span>
                            <span class="tag is-danger">
                                <i class="fas fa-times"></i> <?php echo $usuario['reservas_canceladas']; ?>
                            </span>
                        </td>
                        <td>
                            <small><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></small>
                        </td>
                        <td>
                            <div class="buttons are-small">
                                <button class="button is-info" onclick="verDetalles(<?php echo $usuario['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <?php if ($usuario['id'] != $usuario_actual['id']): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="accion" value="toggle_admin">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <input type="hidden" name="is_admin" value="<?php echo $usuario['is_admin'] ? 0 : 1; ?>">
                                        <button type="submit" class="button is-<?php echo $usuario['is_admin'] ? 'warning' : 'primary'; ?>"
                                                onclick="return confirm('¿Estás seguro de cambiar el rol de este usuario?');">
                                            <i class="fas fa-<?php echo $usuario['is_admin'] ? 'user-minus' : 'user-shield'; ?>"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="accion" value="toggle_activo">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <input type="hidden" name="is_active" value="<?php echo $usuario['is_active'] ? 0 : 1; ?>">
                                        <button type="submit" class="button is-<?php echo $usuario['is_active'] ? 'danger' : 'success'; ?>"
                                                onclick="return confirm('¿Estás seguro de cambiar el estado de este usuario?');">
                                            <i class="fas fa-<?php echo $usuario['is_active'] ? 'user-slash' : 'user-check'; ?>"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para ver detalles del usuario -->
<div id="modal-detalles" class="modal">
    <div class="modal-background" onclick="cerrarModal()"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Detalles del Usuario</p>
            <button class="delete" aria-label="close" onclick="cerrarModal()"></button>
        </header>
        <section class="modal-card-body">
            <div id="contenido-detalles">
                <div class="has-text-centered">
                    <span class="icon is-large">
                        <i class="fas fa-spinner fa-pulse fa-2x"></i>
                    </span>
                    <p>Cargando...</p>
                </div>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="cerrarModal()">Cerrar</button>
        </footer>
    </div>
</div>

<script>
function verDetalles(usuarioId) {
    console.log('Cargando detalles del usuario:', usuarioId);
    document.getElementById('modal-detalles').classList.add('is-active');
    
    // Resetear contenido
    document.getElementById('contenido-detalles').innerHTML = `
        <div class="has-text-centered">
            <span class="icon is-large">
                <i class="fas fa-spinner fa-pulse fa-2x"></i>
            </span>
            <p>Cargando...</p>
        </div>
    `;
    
    // Cargar detalles del usuario con AJAX
    const url = 'api_usuario_detalles.php?id=' + usuarioId;
    console.log('Haciendo petición a:', url);
    
    fetch(url)
        .then(response => {
            console.log('Respuesta recibida, status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            console.log('Respuesta texto:', text);
            try {
                const data = JSON.parse(text);
                console.log('Datos parseados:', data);
                
                if (data.error) {
                    document.getElementById('contenido-detalles').innerHTML = `
                        <article class="message is-danger">
                            <div class="message-body">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${data.error}
                            </div>
                        </article>
                    `;
                } else if (data.usuario) {
                    let html = `
                        <div class="content">
                            <h4><i class="fas fa-user"></i> Información Personal</h4>
                            <table class="table is-fullwidth">
                                <tr><th>Nombre Completo:</th><td>${escapeHtml(data.usuario.nombre || '')} ${escapeHtml(data.usuario.apellido || '')}</td></tr>
                                <tr><th>Usuario:</th><td>${escapeHtml(data.usuario.username || '')}</td></tr>
                                <tr><th>Email:</th><td>${escapeHtml(data.usuario.email || '')}</td></tr>
                                <tr><th>Rol:</th><td>${data.usuario.is_admin ? '<span class="tag is-danger">Administrador</span>' : '<span class="tag is-info">Usuario</span>'}</td></tr>
                                <tr><th>Estado:</th><td>${data.usuario.is_active ? '<span class="tag is-success">Activo</span>' : '<span class="tag is-danger">Inactivo</span>'}</td></tr>
                                <tr><th>Fecha Registro:</th><td>${escapeHtml(data.usuario.fecha_registro || '')}</td></tr>
                            </table>
                            
                            <h4><i class="fas fa-calendar-check"></i> Reservas Recientes</h4>
                    `;
                    
                    if (data.reservas && data.reservas.length > 0) {
                        html += '<table class="table is-fullwidth is-striped">';
                        html += '<thead><tr><th>Espacio</th><th>Fecha</th><th>Horario</th><th>Estado</th></tr></thead><tbody>';
                        data.reservas.forEach(r => {
                            let estadoClass = r.estado === 'CONFIRMADA' ? 'success' : (r.estado === 'CANCELADA' ? 'danger' : 'warning');
                            html += `<tr>
                                <td>${escapeHtml(r.espacio_nombre || '')}</td>
                                <td>${escapeHtml(r.fecha || '')}</td>
                                <td>${escapeHtml(r.hora_inicio || '')} - ${escapeHtml(r.hora_fin || '')}</td>
                                <td><span class="tag is-${estadoClass}">${escapeHtml(r.estado || '')}</span></td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                    } else {
                        html += '<p class="has-text-grey">No tiene reservas registradas.</p>';
                    }
                    
                    html += '</div>';
                    document.getElementById('contenido-detalles').innerHTML = html;
                } else {
                    throw new Error('Formato de respuesta inválido');
                }
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                console.error('Texto recibido:', text);
                throw e;
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            document.getElementById('contenido-detalles').innerHTML = `
                <article class="message is-danger">
                    <div class="message-body">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error al cargar los detalles del usuario.</strong><br>
                        <small>${escapeHtml(error.message || 'Error desconocido')}</small><br>
                        <small>Revisa la consola del navegador (F12) para más detalles.</small>
                    </div>
                </article>
            `;
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function cerrarModal() {
    const modal = document.getElementById('modal-detalles');
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
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1.5rem;
    min-height: 0;
}
.modal-card-foot {
    flex-shrink: 0;
    padding: 1rem;
    border-top: 1px solid #dbdbdb;
    background-color: #fafafa;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
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
</style>

<?php require_once 'footer.php'; ?>
