<?php
$page_title = 'Reportes y Estadísticas';
require_once 'config.php';
requireAdmin();

$pdo = getDB();

// Obtener estadísticas generales
$total_espacios = $pdo->query("SELECT COUNT(*) FROM espacios WHERE activo = 1")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE is_active = 1")->fetchColumn();
$total_reservas = $pdo->query("SELECT COUNT(*) FROM reservas")->fetchColumn();
$reservas_confirmadas = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'CONFIRMADA'")->fetchColumn();
$reservas_canceladas = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'CANCELADA'")->fetchColumn();

// Uso por espacio
$uso_espacios = $pdo->query("
    SELECT e.nombre, e.tipo, 
           COUNT(r.id) as total_reservas,
           SUM(CASE WHEN r.estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas
    FROM espacios e
    LEFT JOIN reservas r ON e.id = r.espacio_id
    GROUP BY e.id
    ORDER BY total_reservas DESC
")->fetchAll();

// Reservas por mes (últimos 6 meses)
$reservas_por_mes = $pdo->query("
    SELECT DATE_FORMAT(fecha, '%Y-%m') as mes,
           COUNT(*) as total,
           SUM(CASE WHEN estado = 'CONFIRMADA' THEN 1 ELSE 0 END) as confirmadas
    FROM reservas
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes DESC
")->fetchAll();

// Usuarios más activos
$usuarios_activos = $pdo->query("
    SELECT u.username, u.nombre, u.apellido,
           COUNT(r.id) as total_reservas
    FROM usuarios u
    LEFT JOIN reservas r ON u.id = r.usuario_id
    WHERE u.is_active = 1
    GROUP BY u.id
    HAVING total_reservas > 0
    ORDER BY total_reservas DESC
    LIMIT 10
")->fetchAll();

require_once 'header.php';
?>

<h1 class="title">
    <i class="fas fa-chart-bar"></i>
    Reportes y Estadísticas
</h1>

<div class="buttons" style="margin-bottom: 1rem;">
    <a class="button is-link" href="admin.php">
        <i class="fas fa-arrow-left"></i>
        <span style="margin-left: 0.5rem;">Volver al Panel</span>
    </a>
    <a class="button is-danger" href="generar_reporte_pdf.php" target="_blank">
        <i class="fas fa-file-pdf"></i>
        <span style="margin-left: 0.5rem;">Descargar Reporte PDF</span>
    </a>
</div>

<!-- Estadísticas Generales -->
<div class="box">
    <h2 class="subtitle">
        <i class="fas fa-info-circle"></i>
        Estadísticas Generales
    </h2>
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="notification is-info has-text-centered">
                <p class="title"><?php echo $total_espacios; ?></p>
                <p class="subtitle is-6">Espacios Activos</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="notification is-primary has-text-centered">
                <p class="title"><?php echo $total_usuarios; ?></p>
                <p class="subtitle is-6">Usuarios Activos</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="notification is-link has-text-centered">
                <p class="title"><?php echo $total_reservas; ?></p>
                <p class="subtitle is-6">Total Reservas</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="notification is-success has-text-centered">
                <p class="title"><?php echo $reservas_confirmadas; ?></p>
                <p class="subtitle is-6">Confirmadas</p>
            </div>
        </div>
    </div>
</div>

<!-- Uso por Espacio -->
<div class="box">
    <h2 class="subtitle">
        <i class="fas fa-building"></i>
        Uso por Espacio
    </h2>
    <div class="table-container">
        <table class="table is-fullwidth is-striped">
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
                        <td><span class="tag is-info"><?php echo htmlspecialchars($espacio['tipo']); ?></span></td>
                        <td><?php echo $espacio['total_reservas']; ?></td>
                        <td>
                            <span class="tag is-success">
                                <?php echo $espacio['confirmadas']; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            $porcentaje = $espacio['total_reservas'] > 0 
                                ? round(($espacio['confirmadas'] / $espacio['total_reservas']) * 100) 
                                : 0;
                            ?>
                            <progress class="progress is-success" value="<?php echo $porcentaje; ?>" max="100">
                                <?php echo $porcentaje; ?>%
                            </progress>
                            <small><?php echo $porcentaje; ?>%</small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reservas por Mes -->
<div class="columns">
    <div class="column is-half">
        <div class="box">
            <h2 class="subtitle">
                <i class="fas fa-calendar-alt"></i>
                Reservas por Mes (Últimos 6 meses)
            </h2>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Total</th>
                            <th>Confirmadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas_por_mes as $mes): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $fecha = DateTime::createFromFormat('Y-m', $mes['mes']);
                                    echo $fecha ? $fecha->format('F Y') : $mes['mes']; 
                                    ?>
                                </td>
                                <td><?php echo $mes['total']; ?></td>
                                <td><span class="tag is-success"><?php echo $mes['confirmadas']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Usuarios Más Activos -->
    <div class="column is-half">
        <div class="box">
            <h2 class="subtitle">
                <i class="fas fa-users"></i>
                Usuarios Más Activos
            </h2>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Total Reservas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios_activos as $usuario): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($usuario['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></td>
                                <td>
                                    <span class="tag is-primary">
                                        <?php echo $usuario['total_reservas']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Formulario para reporte personalizado -->
<div class="box">
    <h2 class="subtitle">
        <i class="fas fa-file-export"></i>
        Generar Reporte Personalizado
    </h2>
    <form method="get" action="generar_reporte_pdf.php" target="_blank">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">Fecha Inicio</label>
                    <div class="control">
                        <input class="input" type="date" name="fecha_inicio" 
                               value="<?php echo date('Y-m-01'); ?>">
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Fecha Fin</label>
                    <div class="control">
                        <input class="input" type="date" name="fecha_fin" 
                               value="<?php echo date('Y-m-t'); ?>">
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">&nbsp;</label>
                    <div class="control">
                        <button type="submit" class="button is-danger is-fullwidth">
                            <i class="fas fa-file-pdf"></i>
                            <span style="margin-left: 0.5rem;">Generar PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
