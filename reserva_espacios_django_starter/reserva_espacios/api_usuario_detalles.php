<?php
require_once 'config.php';
requireAdmin();

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'ID de usuario inválido']);
    exit;
}

$pdo = getDB();

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT id, username, email, nombre, apellido, is_admin, is_active, 
                       DATE_FORMAT(fecha_registro, '%d/%m/%Y %H:%i') as fecha_registro 
                       FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}

// Obtener últimas 10 reservas del usuario
$stmt = $pdo->prepare("
    SELECT r.*, e.nombre as espacio_nombre,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') as fecha,
           TIME_FORMAT(r.hora_inicio, '%H:%i') as hora_inicio,
           TIME_FORMAT(r.hora_fin, '%H:%i') as hora_fin
    FROM reservas r
    JOIN espacios e ON r.espacio_id = e.id
    WHERE r.usuario_id = ?
    ORDER BY r.fecha DESC, r.hora_inicio DESC
    LIMIT 10
");
$stmt->execute([$id]);
$reservas = $stmt->fetchAll();

echo json_encode([
    'usuario' => $usuario,
    'reservas' => $reservas
]);
?>
