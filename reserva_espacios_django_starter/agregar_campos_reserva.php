<?php
/**
 * Script para agregar campos de contacto a la tabla reservas
 * Ejecuta este archivo una vez y luego elimínalo por seguridad
 */

require_once 'config.php';
requireAdmin();

$pdo = getDB();

echo "<h1>Agregar Campos de Contacto a Reservas</h1>";
echo "<hr>";

// Verificar si los campos ya existen
$stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'nombre_contacto'");
$campo_nombre = $stmt->fetch();

$stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'telefono'");
$campo_telefono = $stmt->fetch();

$stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'numero_identificacion'");
$campo_identificacion = $stmt->fetch();

if ($campo_nombre && $campo_telefono && $campo_identificacion) {
    echo "<p style='color: green;'>✓ Los campos ya existen en la tabla reservas</p>";
    echo "<p>No es necesario hacer cambios.</p>";
} else {
    try {
        // Agregar campos si no existen
        if (!$campo_nombre) {
            $pdo->exec("ALTER TABLE reservas ADD COLUMN nombre_contacto VARCHAR(150) NULL AFTER usuario_id");
            echo "<p style='color: green;'>✓ Campo 'nombre_contacto' agregado</p>";
        }
        
        if (!$campo_telefono) {
            $pdo->exec("ALTER TABLE reservas ADD COLUMN telefono VARCHAR(20) NULL AFTER nombre_contacto");
            echo "<p style='color: green;'>✓ Campo 'telefono' agregado</p>";
        }
        
        if (!$campo_identificacion) {
            $pdo->exec("ALTER TABLE reservas ADD COLUMN numero_identificacion VARCHAR(50) NULL AFTER telefono");
            echo "<p style='color: green;'>✓ Campo 'numero_identificacion' agregado</p>";
        }
        
        // Agregar índices
        try {
            $pdo->exec("CREATE INDEX idx_nombre_contacto ON reservas(nombre_contacto)");
            echo "<p style='color: green;'>✓ Índice 'idx_nombre_contacto' creado</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Índice 'idx_nombre_contacto' ya existe o no se pudo crear</p>";
        }
        
        try {
            $pdo->exec("CREATE INDEX idx_telefono ON reservas(telefono)");
            echo "<p style='color: green;'>✓ Índice 'idx_telefono' creado</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Índice 'idx_telefono' ya existe o no se pudo crear</p>";
        }
        
        try {
            $pdo->exec("CREATE INDEX idx_numero_identificacion ON reservas(numero_identificacion)");
            echo "<p style='color: green;'>✓ Índice 'idx_numero_identificacion' creado</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Índice 'idx_numero_identificacion' ya existe o no se pudo crear</p>";
        }
        
        echo "<hr>";
        echo "<h2 style='color: green;'>✓ ¡Campos agregados correctamente!</h2>";
        echo "<p>Ahora el formulario de reserva pedirá:</p>";
        echo "<ul>";
        echo "<li>Nombre completo</li>";
        echo "<li>Número de teléfono</li>";
        echo "<li>Número de identificación</li>";
        echo "</ul>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='nueva_reserva.php'>Ir a Crear Reserva</a> | <a href='admin.php'>Volver al Panel</a></p>";
echo "<p style='color: red;'><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (agregar_campos_reserva.php) por seguridad después de usarlo.</p>";
?>

