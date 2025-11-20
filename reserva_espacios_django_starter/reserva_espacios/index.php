<?php
$page_title = 'Inicio';
require_once 'header.php';

$pdo = getDB();
$espacios = $pdo->query("SELECT * FROM espacios WHERE activo = 1 ORDER BY nombre")->fetchAll();
?>

<section class="section">
    <div class="hero is-primary is-small" style="margin-bottom: 2rem;">
        <div class="hero-body">
            <h1 class="title">
                <i class="fas fa-calendar-check"></i>
                Sistema de Reserva de Espacios
            </h1>
            <p class="subtitle">
                Consulta la disponibilidad y reserva espacios fácilmente
            </p>
        </div>
    </div>

    <div class="box">
        <h2 class="subtitle">
            <i class="fas fa-search"></i>
            Consultar Disponibilidad
        </h2>
        <form id="disp-form" class="columns is-vcentered">
            <div class="column is-4">
                <label class="label">Espacio</label>
                <div class="select is-fullwidth">
                    <select id="espacio" required>
                        <option value="">Selecciona un espacio</option>
                        <?php foreach ($espacios as $espacio): ?>
                            <option value="<?php echo $espacio['id']; ?>">
                                <?php echo htmlspecialchars($espacio['nombre']); ?> 
                                (<?php echo htmlspecialchars($espacio['tipo']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="column is-3">
                <label class="label">Fecha</label>
                <input id="fecha" class="input" type="date" required>
            </div>
            <div class="column is-2">
                <label class="label">&nbsp;</label>
                <button type="button" id="consultar" class="button is-link is-fullwidth">
                    <i class="fas fa-search"></i>
                    <span style="margin-left: 0.5rem;">Consultar</span>
                </button>
            </div>
        </form>
        <div id="resultado" style="margin-top:1rem;"></div>
    </div>

    <div class="buttons">
        <?php if ($usuario): ?>
            <a class="button is-primary is-medium" href="nueva_reserva.php">
                <i class="fas fa-plus-circle"></i>
                <span style="margin-left: 0.5rem;">Crear Nueva Reserva</span>
            </a>
            <a class="button is-light is-medium" href="reservas.php">
                <i class="fas fa-list"></i>
                <span style="margin-left: 0.5rem;">Ver Mis Reservas</span>
            </a>
        <?php else: ?>
            <a class="button is-primary is-medium" href="login.php">
                <i class="fas fa-sign-in-alt"></i>
                <span style="margin-left: 0.5rem;">Inicia Sesión para Reservar</span>
            </a>
        <?php endif; ?>
    </div>

    <?php if (count($espacios) > 0): ?>
    <div class="box" style="margin-top: 2rem;">
        <h2 class="subtitle">
            <i class="fas fa-building"></i>
            Espacios Disponibles
        </h2>
        <div class="columns is-multiline">
            <?php foreach ($espacios as $espacio): ?>
            <div class="column is-4">
                <div class="card">
                    <?php 
                    // Verificar si el espacio tiene imagen
                    $tiene_imagen = false;
                    $ruta_imagen = '';
                    if (isset($espacio['imagen']) && !empty($espacio['imagen'])) {
                        $ruta_imagen = 'uploads/espacios/' . $espacio['imagen'];
                        if (file_exists($ruta_imagen)) {
                            $tiene_imagen = true;
                        }
                    }
                    ?>
                    <?php if ($tiene_imagen): ?>
                    <div class="card-image">
                        <figure class="image is-4by3">
                            <img src="<?php echo htmlspecialchars($ruta_imagen); ?>" alt="<?php echo htmlspecialchars($espacio['nombre']); ?>" style="object-fit: cover;">
                        </figure>
                    </div>
                    <?php else: ?>
                    <div class="card-image">
                        <figure class="image is-4by3 has-background-light" style="display: flex; align-items: center; justify-content: center;">
                            <span class="icon is-large has-text-grey">
                                <i class="fas fa-building fa-3x"></i>
                            </span>
                        </figure>
                    </div>
                    <?php endif; ?>
                    <div class="card-content">
                        <p class="title is-5"><?php echo htmlspecialchars($espacio['nombre']); ?></p>
                        <p class="subtitle is-6">
                            <span class="tag is-info"><?php echo htmlspecialchars($espacio['tipo']); ?></span>
                            <span class="tag is-light" style="margin-left: 0.5rem;">
                                <i class="fas fa-users"></i>
                                Capacidad: <?php echo $espacio['capacidad']; ?>
                            </span>
                        </p>
                        <?php if ($espacio['descripcion']): ?>
                            <p class="is-size-7"><?php echo htmlspecialchars($espacio['descripcion']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
document.getElementById('consultar').addEventListener('click', async () => {
    const espacio = document.getElementById('espacio').value;
    const fecha = document.getElementById('fecha').value;
    const resultado = document.getElementById('resultado');
    
    if (!espacio || !fecha) {
        resultado.innerHTML = '<article class="message is-warning"><div class="message-body"><i class="fas fa-exclamation-triangle"></i> Por favor, selecciona un espacio y una fecha.</div></article>';
        return;
    }

    try {
        const res = await fetch(`api_disponibilidad.php?espacio_id=${espacio}&fecha=${fecha}`);
        const data = await res.json();
        
        if (data.ocupado.length === 0) {
            resultado.innerHTML = '<article class="message is-success"><div class="message-body"><i class="fas fa-check-circle"></i> El espacio está disponible todo el día.</div></article>';
        } else {
            let html = '<h3 class="subtitle is-6">Horarios ocupados:</h3><div class="tags">';
            data.ocupado.forEach(o => {
                html += `<span class="tag is-danger is-medium" style="margin:4px;"><i class="fas fa-clock"></i> ${o.inicio} - ${o.fin}</span>`;
            });
            html += '</div>';
            resultado.innerHTML = html;
        }
    } catch (error) {
        resultado.innerHTML = '<article class="message is-danger"><div class="message-body"><i class="fas fa-exclamation-circle"></i> Error al consultar disponibilidad. Por favor, intenta de nuevo.</div></article>';
    }
});
</script>

<?php require_once 'footer.php'; ?>
