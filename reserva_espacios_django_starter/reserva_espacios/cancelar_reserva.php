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
        // Obtener datos del espacio
        $stmt_espacio = $pdo->prepare("SELECT * FROM espacios WHERE id = ?");
        $stmt_espacio->execute([$reserva['espacio_id']]);
        $espacio_data = $stmt_espacio->fetch();
        
        // Obtener datos del usuario (si es admin cancelando reserva de otro usuario)
        $stmt_usuario = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt_usuario->execute([$reserva['usuario_id']]);
        $usuario_reserva = $stmt_usuario->fetch();
        
        // Cancelar reserva
        $stmt = $pdo->prepare("UPDATE reservas SET estado = 'CANCELADA' WHERE id = ?");
        $stmt->execute([$id]);
        
        // Enviar notificación por email
        require_once 'email_config.php';
        enviarNotificacionReservaCancelada($usuario_reserva, $reserva, $espacio_data);
        
        flashMessage('Reserva cancelada correctamente. Se ha enviado un email de notificación.', 'success');
    } else {
        flashMessage('No tienes permiso para cancelar esta reserva.', 'danger');
    }
}

redirect('reservas.php');
?>

