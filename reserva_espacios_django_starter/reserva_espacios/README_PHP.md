# Sistema de Reserva de Espacios (PHP)

Sistema web completo para la gestión y reserva de espacios desarrollado en PHP/MySQL.

## ✨ Características

- 🔐 Sistema de autenticación de usuarios
- 📅 Consulta de disponibilidad en tiempo real
- 📝 Gestión completa de reservas (crear, listar, cancelar)
- 🎨 Interfaz moderna y responsive con Bulma CSS
- 🔒 Validación de solapamiento de horarios
- 👥 Panel de administración
- 📊 Estadísticas y reportes

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior (o MariaDB)
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL

## 🚀 Instalación

### 1. Subir archivos al servidor

Sube todos los archivos PHP al directorio de tu hosting (vía FTP, cPanel, etc.)

### 2. Crear base de datos

1. Abre tu panel de control de base de datos (phpMyAdmin, etc.)
2. Crea una nueva base de datos llamada `reserva_espacios`
3. Importa el archivo `base_de_datos.sql` o copia y pega cada tabla

### 3. Configurar conexión

Edita el archivo `config.php` y actualiza:

```php
define('DB_HOST', 'localhost');        // Host de tu base de datos
define('DB_NAME', 'reserva_espacios');  // Nombre de la base de datos
define('DB_USER', 'tu_usuario_db');     // Usuario de la base de datos
define('DB_PASS', 'tu_password_db');    // Contraseña de la base de datos
```

### 4. Configurar URL

El archivo `config.php` detecta automáticamente la URL, pero si necesitas cambiarla:

```php
define('APP_URL', 'http://tu-dominio.com');
```

### 5. Permisos

Asegúrate de que los archivos tengan permisos de lectura (644) y las carpetas (755).

## 👤 Usuario por Defecto

Después de crear la base de datos, puedes iniciar sesión con:

- **Usuario:** `admin`
- **Contraseña:** `admin123`

**⚠️ IMPORTANTE:** Cambia esta contraseña después del primer acceso.

## 📁 Estructura de Archivos

```
/
├── index.php              # Página principal
├── login.php              # Iniciar sesión
├── logout.php             # Cerrar sesión
├── reservas.php           # Lista de mis reservas
├── nueva_reserva.php      # Crear nueva reserva
├── cancelar_reserva.php   # Cancelar reserva
├── admin.php              # Panel de administración
├── api_disponibilidad.php # API para consultar disponibilidad
├── config.php             # Configuración de la aplicación
├── header.php             # Header común
├── footer.php             # Footer común
├── base_de_datos.sql      # Estructura de la base de datos
└── .htaccess              # Configuración de Apache
```

## 🗄️ Base de Datos

### Tablas principales:

- **usuarios**: Usuarios del sistema
- **espacios**: Espacios disponibles para reservar
- **reservas**: Reservas realizadas
- **sesiones**: Sesiones de usuario

### Datos de ejemplo:

El archivo SQL incluye:
- 1 usuario administrador
- 5 espacios de ejemplo
- Estructura completa de tablas

## 🌐 Páginas del Sistema

| Página | Descripción | Acceso |
|--------|-------------|--------|
| `/index.php` | Página principal y consulta de disponibilidad | Público |
| `/login.php` | Iniciar sesión | Público |
| `/reservas.php` | Lista de mis reservas | Usuarios autenticados |
| `/nueva_reserva.php` | Crear nueva reserva | Usuarios autenticados |
| `/admin.php` | Panel de administración | Solo administradores |

## 🔒 Seguridad

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Protección contra SQL Injection (PDO prepared statements)
- ✅ Validación de entrada
- ✅ Protección CSRF (sesiones)
- ✅ Sanitización de salida HTML

## 📝 Personalización

### Cambiar contraseña del admin:

```sql
UPDATE usuarios 
SET password = '$2y$10$...' 
WHERE username = 'admin';
```

Para generar un hash de contraseña en PHP:

```php
echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT);
```

### Agregar más espacios:

Usa el panel de administración o inserta directamente en la base de datos.

## 🆘 Solución de Problemas

### Error de conexión a la base de datos

- Verifica las credenciales en `config.php`
- Asegúrate de que la base de datos existe
- Verifica que el usuario tiene permisos

### Página en blanco

- Activa el modo DEBUG en `config.php`: `define('DEBUG', true);`
- Revisa los logs de error de PHP
- Verifica permisos de archivos

### Sesiones no funcionan

- Verifica que la carpeta de sesiones tiene permisos de escritura
- Revisa la configuración de `session.save_path` en PHP

## 📄 Licencia

Este proyecto es parte del Proyecto GP2 - Ingeniería de Software.

## 👥 Autores

- Grupo: LuisTj
- Fecha: 23 de octubre de 2025

---

**¡Listo para usar!** 🎉

