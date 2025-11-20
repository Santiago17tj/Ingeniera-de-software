@echo off
echo ========================================
echo Iniciando servidor PHP local
echo ========================================
echo.

REM Verificar si PHP está instalado
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PHP no está instalado o no está en el PATH
    echo.
    echo Opciones:
    echo 1. Instala PHP desde: https://windows.php.net/download/
    echo 2. O usa XAMPP: https://www.apachefriends.org/
    echo.
    echo Para más información, lee: INSTRUCCIONES_SERVIDOR_LOCAL.txt
    echo.
    pause
    exit /b 1
)

echo PHP encontrado!
echo.
echo Servidor iniciado en: http://localhost:8000
echo.
echo Presiona Ctrl+C para detener el servidor
echo ========================================
echo.

REM Iniciar servidor PHP
php -S localhost:8000

