<?php
require_once 'config.php';

header('Content-Type: application/json');

$espacio_id = $_GET['espacio_id'] ?? null;
$fecha = $_GET['fecha'] ?? null;

$data = ['ocupado' => []];

if ($espacio_id && $fecha) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT hora_inicio, hora_fin FROM reservas 
                          WHERE espacio_id = ? AND fecha = ? AND estado != 'CANCELADA' 
                          ORDER BY hora_inicio");
    $stmt->execute([$espacio_id, $fecha]);
    $reservas = $stmt->fetchAll();
    
    foreach ($reservas as $reserva) {
        $data['ocupado'][] = [
            'inicio' => substr($reserva['hora_inicio'], 0, 5),
            'fin' => substr($reserva['hora_fin'], 0, 5)
        ];
    }
}

echo json_encode($data);
?>

