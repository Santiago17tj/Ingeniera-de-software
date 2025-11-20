@echo off
echo ========================================
echo Configurando proyecto en XAMPP
echo ========================================
echo.

REM Verificar si existe XAMPP
if not exist "C:\xampp\htdocs" (
    echo ERROR: XAMPP no encontrado en C:\xampp
    echo Por favor, verifica la ruta de instalación de XAMPP
    pause
    exit /b 1
)

echo XAMPP encontrado!
echo.
echo Copiando archivos a C:\xampp\htdocs\reserva_espacios\...
echo.

REM Crear directorio si no existe
if not exist "C:\xampp\htdocs\reserva_espacios" (
    mkdir "C:\xampp\htdocs\reserva_espacios"
)

REM Copiar archivos PHP
xcopy /Y /I *.php "C:\xampp\htdocs\reserva_espacios\" >nul 2>&1
xcopy /Y /I *.sql "C:\xampp\htdocs\reserva_espacios\" >nul 2>&1
xcopy /Y /I .htaccess "C:\xampp\htdocs\reserva_espacios\" >nul 2>&1

echo Archivos copiados exitosamente!
echo.
echo ========================================
echo SIGUIENTES PASOS:
echo ========================================
echo.
echo 1. Abre XAMPP Control Panel
echo 2. Inicia Apache (boton Start)
echo 3. Inicia MySQL (boton Start)
echo 4. Abre: http://localhost/phpmyadmin
echo 5. Crea la base de datos (ver instrucciones abajo)
echo 6. Configura config.php con las credenciales
echo 7. Abre: http://localhost/reserva_espacios/
echo.
echo ========================================
echo CREAR BASE DE DATOS:
echo ========================================
echo.
echo 1. Ve a: http://localhost/phpmyadmin
echo 2. Click en "Nueva" (New) en el menu izquierdo
echo 3. Nombre: reserva_espacios
echo 4. Click en "Crear" (Create)
echo 5. Click en "Importar" (Import) en el menu superior
echo 6. Selecciona el archivo: base_de_datos.sql
echo 7. Click en "Continuar" (Go)
echo.
echo ========================================
echo CONFIGURAR config.php:
echo ========================================
echo.
echo Abre: C:\xampp\htdocs\reserva_espacios\config.php
echo.
echo Cambia estas lineas:
echo   DB_USER = 'root'
echo   DB_PASS = '' (dejar vacio, XAMPP por defecto no tiene password)
echo.
pause

