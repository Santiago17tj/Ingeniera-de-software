<?php
require_once 'config.php';
requireAdmin();

$pdo = getDB();

// Obtener parámetros de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');

// Estadísticas generales
$total_espacios = $pdo->query("SELECT COUNT(*) FROM espacios WHERE activo = 1")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE is_active = 1")->fetchColumn();

// Reservas en el período
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total,
           SUM(CASE WHEN estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas,
           SUM(CASE WHEN estado = 'CANCELADA' THEN 1 ELSE 0 END) as canceladas
    FROM reservas
    WHERE fecha BETWEEN ? AND ?
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$stats = $stmt->fetch();

// Uso por espacio en el período
$stmt = $pdo->prepare("
    SELECT e.nombre, e.tipo,
           COUNT(r.id) as total_reservas,
           SUM(CASE WHEN r.estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas
    FROM espacios e
    LEFT JOIN reservas r ON e.id = r.espacio_id AND r.fecha BETWEEN ? AND ?
    GROUP BY e.id
    ORDER BY total_reservas DESC
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$uso_espacios = $stmt->fetchAll();

// Reservas detalladas
$stmt = $pdo->prepare("
    SELECT r.*, e.nombre as espacio_nombre, u.username
    FROM reservas r
    JOIN espacios e ON r.espacio_id = e.id
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.fecha BETWEEN ? AND ?
    ORDER BY r.fecha DESC, r.hora_inicio DESC
    LIMIT 100
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$reservas = $stmt->fetchAll();

// Configurar headers para PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="reporte_reservas_' . date('Y-m-d') . '.pdf"');

// Nota: Para una implementación real de PDF, necesitarías una librería como FPDF, TCPDF o DomPDF
// Por simplicidad, generaremos un HTML que el navegador puede imprimir como PDF
// En producción, deberías instalar una librería de PDF real

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Reservas</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3273dc;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #3273dc;
            font-size: 24pt;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section h2 {
            background-color: #3273dc;
            color: white;
            padding: 10px;
            margin: 0 0 15px 0;
            font-size: 16pt;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            border: 2px solid #3273dc;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        .stat-box .number {
            font-size: 32pt;
            font-weight: bold;
            color: #3273dc;
            margin: 0;
        }
        .stat-box .label {
            font-size: 10pt;
            color: #666;
            margin: 5px 0 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 30px;
            background-color: #3273dc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14pt;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .print-button:hover {
            background-color: #2366d1;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Imprimir / Guardar como PDF
    </button>

    <div class="header">
        <h1>📊 REPORTE DE RESERVAS</h1>
        <p><strong>Sistema de Reserva de Espacios</strong></p>
        <p>Período: <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></p>
        <p>Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <!-- Estadísticas Generales -->
    <div class="section">
        <h2>📈 Estadísticas Generales</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <p class="number"><?php echo $total_espacios; ?></p>
                <p class="label">Espacios Activos</p>
            </div>
            <div class="stat-box">
                <p class="number"><?php echo $total_usuarios; ?></p>
                <p class="label">Usuarios Activos</p>
            </div>
            <div class="stat-box">
                <p class="number"><?php echo $stats['total']; ?></p>
                <p class="label">Total Reservas</p>
            </div>
            <div class="stat-box">
                <p class="number"><?php echo $stats['confirmadas']; ?></p>
                <p class="label">Confirmadas</p>
            </div>
        </div>
    </div>

    <!-- Uso por Espacio -->
    <div class="section">
        <h2>🏢 Uso por Espacio</h2>
        <table>
            <thead>
                <tr>
                    <th>Espacio</th>
                    <th>Tipo</th>
                    <th>Total Reservas</th>
                    <th>Confirmadas</th>
                    <th>Tasa de Uso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uso_espacios as $espacio): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($espacio['nombre']); ?></strong></td>
                        <td><?php echo htmlspecialchars($espacio['tipo']); ?></td>
                        <td style="text-align: center;"><?php echo $espacio['total_reservas']; ?></td>
                        <td style="text-align: center;"><?php echo $espacio['confirmadas']; ?></td>
                        <td style="text-align: center;">
                            <?php 
                            $porcentaje = $espacio['total_reservas'] > 0 
                                ? round(($espacio['confirmadas'] / $espacio['total_reservas']) * 100) 
                                : 0;
                            echo $porcentaje . '%';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Detalle de Reservas -->
    <div class="section">
        <h2>📅 Detalle de Reservas (Últimas 100)</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Espacio</th>
                    <th>Usuario</th>
                    <th>Horario</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($reserva['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($reserva['espacio_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($reserva['username']); ?></td>
                        <td><?php echo substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5); ?></td>
                        <td><?php echo $reserva['estado']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer no-print">
        <p>Sistema de Reserva de Espacios - © <?php echo date('Y'); ?></p>
    </div>

    <script>
        // Auto-abrir diálogo de impresión después de cargar
        window.addEventListener('load', function() {
            setTimeout(function() {
                // window.print(); // Descomenta si quieres auto-imprimir
            }, 500);
        });
    </script>
</body>
</html>
