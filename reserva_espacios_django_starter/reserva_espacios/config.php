<?php
/**
 * Configuración de la base de datos y aplicación
 * Ajusta estos valores según tu hosting
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'reserva_espacios');
define('DB_USER', 'tu_usuario_db');  // Cambiar por tu usuario de base de datos
define('DB_PASS', 'tu_password_db');  // Cambiar por tu contraseña de base de datos
define('DB_CHARSET', 'utf8mb4');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Reserva de Espacios');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']); // Se ajusta automáticamente
define('DEBUG', false); // Cambiar a true para desarrollo

// Configuración de sesiones
define('SESSION_LIFETIME', 3600); // 1 hora en segundos
define('SESSION_NAME', 'reserva_espacios_session');

// Configuración de seguridad
define('SECRET_KEY', 'cambiar-esta-clave-secreta-por-una-aleatoria'); // Cambiar esto

// Zona horaria
date_default_timezone_set('America/Bogota');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_name(SESSION_NAME);
    session_start();
}

// Conexión a la base de datos
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG) {
                die("Error de conexión: " . $e->getMessage());
            } else {
                die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
            }
        }
    }
    
    return $pdo;
}

// Funciones de utilidad
function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function getUsuario() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, username, email, nombre, apellido, is_admin FROM usuarios WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['usuario_id']]);
    return $stmt->fetch();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    $usuario = getUsuario();
    if (!$usuario || !$usuario['is_admin']) {
        header('Location: index.php');
        exit;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function flashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Sanitizar entrada
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Verificar solapamiento de reservas
function verificarDisponibilidad($espacio_id, $fecha, $hora_inicio, $hora_fin, $excluir_id = null) {
    $pdo = getDB();
    $sql = "SELECT COUNT(*) FROM reservas 
            WHERE espacio_id = ? 
            AND fecha = ? 
            AND estado != 'CANCELADA'
            AND (
                (hora_inicio < ? AND hora_fin > ?) OR
                (hora_inicio < ? AND hora_fin > ?) OR
                (hora_inicio >= ? AND hora_fin <= ?)
            )";
    
    if ($excluir_id) {
        $sql .= " AND id != ?";
    }
    
    $params = [$espacio_id, $fecha, $hora_fin, $hora_inicio, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin];
    if ($excluir_id) {
        $params[] = $excluir_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}
?>

