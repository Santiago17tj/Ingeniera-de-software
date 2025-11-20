<?php
require_once 'config.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $pdo = getDB();
    $usuario = getUsuario();
    
    // Verificar que la reserva pertenece al usuario (o es admin)
    $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id = ?");
    $stmt->execute([$id]);
    $reserva = $stmt->fetch();
    
    if ($reserva && ($reserva['usuario_id'] == $usuario['id'] || $usuario['is_admin'])) {
        $stmt = $pdo->prepare("UPDATE reservas SET estado = 'CANCELADA' WHERE id = ?");
        $stmt->execute([$id]);
        flashMessage('Reserva cancelada correctamente.', 'success');
    } else {
        flashMessage('No tienes permiso para cancelar esta reserva.', 'danger');
    }
}

redirect('reservas.php');
?>

