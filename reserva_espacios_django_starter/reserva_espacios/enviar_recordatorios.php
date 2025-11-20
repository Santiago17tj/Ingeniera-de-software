<?php
/**
 * Script para enviar recordatorios de reservas
 * Este script debe ejecutarse diariamente con un cron job
 * 
 * Ejemplo de cron (ejecutar todos los días a las 9:00 AM):
 * 0 9 * * * php /ruta/al/proyecto/enviar_recordatorios.php
 * 
 * O ejecutar manualmente:
 * php enviar_recordatorios.php
 */

require_once 'config.php';
require_once 'email_config.php';

// Solo permitir ejecución desde línea de comandos o localhost
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    die('Este script solo puede ejecutarse desde la línea de comandos o localhost.');
}

$pdo = getDB();

// Obtener reservas para mañana que estén confirmadas
$fecha_manana = date('Y-m-d', strtotime('+1 day'));

$stmt = $pdo->prepare("
    SELECT r.*, 
           e.nombre as espacio_nombre, e.tipo, e.capacidad,
           u.id as usuario_id, u.username, u.email, u.nombre as usuario_nombre, u.apellido as usuario_apellido
    FROM reservas r
    JOIN espacios e ON r.espacio_id = e.id
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.fecha = ? AND r.estado = 'CONFIRMADA'
    ORDER BY r.hora_inicio
");
$stmt->execute([$fecha_manana]);
$reservas = $stmt->fetchAll();

$enviados = 0;
$errores = 0;

echo "====================================\n";
echo "ENVÍO DE RECORDATORIOS DE RESERVAS\n";
echo "====================================\n";
echo "Fecha: " . date('d/m/Y H:i:s') . "\n";
echo "Reservas para mañana (" . date('d/m/Y', strtotime($fecha_manana)) . "): " . count($reservas) . "\n";
echo "------------------------------------\n\n";

foreach ($reservas as $reserva) {
    $usuario_data = [
        'id' => $reserva['usuario_id'],
        'username' => $reserva['username'],
        'email' => $reserva['email'],
        'nombre' => $reserva['usuario_nombre'],
        'apellido' => $reserva['usuario_apellido']
    ];
    
    $espacio_data = [
        'nombre' => $reserva['espacio_nombre'],
        'tipo' => $reserva['tipo'],
        'capacidad' => $reserva['capacidad']
    ];
    
    echo "Enviando recordatorio a: " . $reserva['email'] . "\n";
    echo "  Usuario: " . $reserva['usuario_nombre'] . " " . $reserva['usuario_apellido'] . "\n";
    echo "  Espacio: " . $reserva['espacio_nombre'] . "\n";
    echo "  Horario: " . substr($reserva['hora_inicio'], 0, 5) . " - " . substr($reserva['hora_fin'], 0, 5) . "\n";
    
    if (enviarRecordatorioReserva($usuario_data, $reserva, $espacio_data)) {
        echo "  ✓ Enviado correctamente\n\n";
        $enviados++;
    } else {
        echo "  ✗ Error al enviar\n\n";
        $errores++;
    }
}

echo "------------------------------------\n";
echo "RESUMEN:\n";
echo "  Total reservas: " . count($reservas) . "\n";
echo "  Emails enviados: " . $enviados . "\n";
echo "  Errores: " . $errores . "\n";
echo "====================================\n";

// Si se ejecuta desde web, redirigir al panel de admin
if (php_sapi_name() !== 'cli') {
    echo "<br><a href='admin.php'>Volver al Panel de Administración</a>";
}
?>
