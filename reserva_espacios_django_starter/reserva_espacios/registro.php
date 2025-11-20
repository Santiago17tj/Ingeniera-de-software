<?php
$page_title = 'Registro';
require_once 'config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nombre = sanitize($_POST['nombre'] ?? '');
    $apellido = sanitize($_POST['apellido'] ?? '');
    
    // Validaciones
    if (empty($username) || empty($email) || empty($password) || empty($nombre) || empty($apellido)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, ingresa un email válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $pdo = getDB();
        
        // Verificar si el username ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'El nombre de usuario ya está en uso.';
        } else {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'El email ya está registrado.';
            } else {
                // Crear usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, email, password, nombre, apellido, is_admin, is_active) 
                                      VALUES (?, ?, ?, ?, ?, 0, 1)");
                
                if ($stmt->execute([$username, $email, $password_hash, $nombre, $apellido])) {
                    $success = 'Registro exitoso. Ahora puedes iniciar sesión.';
                    // Auto-login después de registro
                    $usuario_id = $pdo->lastInsertId();
                    $_SESSION['usuario_id'] = $usuario_id;
                    $_SESSION['username'] = $username;
                    flashMessage('¡Bienvenido! Tu cuenta ha sido creada exitosamente.', 'success');
                    redirect('index.php');
                } else {
                    $error = 'Error al crear la cuenta. Por favor, intenta de nuevo.';
                }
            }
        }
    }
}

require_once 'header.php';
?>

<section class="section">
    <div class="columns is-centered">
        <div class="column is-6">
            <div class="box">
                <h1 class="title has-text-centered">
                    <i class="fas fa-user-plus"></i>
                    Crear Cuenta Nueva
                </h1>
                <p class="has-text-centered has-text-grey" style="margin-bottom: 2rem;">
                    Regístrate para poder reservar espacios
                </p>
                
                <?php if ($error): ?>
                    <div class="notification is-danger is-light">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="notification is-success is-light">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="columns">
                        <div class="column">
                            <div class="field">
                                <label class="label">Nombre *</label>
                                <div class="control has-icons-left">
                                    <input class="input" type="text" name="nombre" 
                                           value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" 
                                           required autofocus>
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <label class="label">Apellido *</label>
                                <div class="control has-icons-left">
                                    <input class="input" type="text" name="apellido" 
                                           value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" 
                                           required>
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Nombre de Usuario *</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-at"></i>
                            </span>
                        </div>
                        <p class="help">Este será tu nombre de usuario para iniciar sesión</p>
                    </div>

                    <div class="field">
                        <label class="label">Email *</label>
                        <div class="control has-icons-left">
                            <input class="input" type="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Contraseña *</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="password" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <p class="help">Mínimo 6 caracteres</p>
                    </div>

                    <div class="field">
                        <label class="label">Confirmar Contraseña *</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="password_confirm" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                    </div>

                    <div class="field" style="margin-top: 1.5rem;">
                        <div class="control">
                            <button class="button is-primary is-fullwidth is-medium" type="submit">
                                <i class="fas fa-user-plus"></i>
                                <span style="margin-left: 0.5rem;">Crear Cuenta</span>
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="has-text-centered" style="margin-top: 1.5rem;">
                    <p class="is-size-6">
                        ¿Ya tienes cuenta? 
                        <a href="login.php"><strong>Inicia Sesión</strong></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
