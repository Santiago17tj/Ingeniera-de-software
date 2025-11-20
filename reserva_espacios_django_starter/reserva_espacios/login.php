<?php
$page_title = 'Iniciar Sesión';
require_once 'config.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            flashMessage('Bienvenido, ' . $usuario['username'] . '!', 'success');
            redirect('index.php');
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}

require_once 'header.php';
?>

<section class="section">
    <div class="columns is-centered">
        <div class="column is-5">
            <div class="box">
                <h1 class="title has-text-centered">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </h1>
                <?php if ($error): ?>
                    <div class="notification is-danger is-light">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="post">
                    <div class="field">
                        <label class="label">Usuario</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="username" required autofocus>
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Contraseña</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="password" required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-primary is-fullwidth" type="submit">
                                <i class="fas fa-sign-in-alt"></i>
                                <span style="margin-left: 0.5rem;">Iniciar Sesión</span>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="has-text-centered" style="margin-top: 1rem;">
                    <p class="is-size-7 has-text-grey">
                        Usuario de prueba: <strong>admin</strong> / Contraseña: <strong>admin123</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>

