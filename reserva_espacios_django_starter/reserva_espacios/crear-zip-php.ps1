# Script para crear ZIP del proyecto PHP
$nombreZip = "reserva-espacios-php.zip"

# Eliminar ZIP anterior si existe
if (Test-Path $nombreZip) {
    Remove-Item $nombreZip -Force
    Write-Host "ZIP anterior eliminado." -ForegroundColor Yellow
}

Write-Host "Creando ZIP del proyecto PHP..." -ForegroundColor Green

# Crear lista de archivos a incluir
$archivos = @(
    "index.php",
    "login.php",
    "logout.php",
    "reservas.php",
    "nueva_reserva.php",
    "cancelar_reserva.php",
    "admin.php",
    "api_disponibilidad.php",
    "config.php",
    "header.php",
    "footer.php",
    "base_de_datos.sql",
    ".htaccess",
    "README_PHP.md",
    "INSTRUCCIONES_PHP.txt"
)

# Filtrar solo los que existen
$archivosExistentes = $archivos | Where-Object { Test-Path $_ }

if ($archivosExistentes.Count -eq 0) {
    Write-Host "ERROR: No se encontraron archivos para comprimir." -ForegroundColor Red
    exit 1
}

# Crear ZIP usando Compress-Archive
Compress-Archive -Path $archivosExistentes -DestinationPath $nombreZip -Force

if (Test-Path $nombreZip) {
    $tamano = (Get-Item $nombreZip).Length / 1MB
    Write-Host "`n¡ZIP creado exitosamente: $nombreZip" -ForegroundColor Green
    Write-Host "Tamaño: $([math]::Round($tamano, 2)) MB" -ForegroundColor Green
    Write-Host "`nPuedes enviar este archivo a tu compañero para el hosting." -ForegroundColor Cyan
} else {
    Write-Host "ERROR: No se pudo crear el ZIP." -ForegroundColor Red
    exit 1
}

