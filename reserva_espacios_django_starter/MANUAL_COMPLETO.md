# 📅 Sistema de Reserva de Espacios Compartidos

## 🎯 Descripción del Proyecto
Sistema completo de gestión y reserva de espacios compartidos (salas de reuniones, auditorios, canchas deportivas, etc.) desarrollado en **PHP** con **MySQL** y diseño moderno con **Bulma CSS**.

---

## ✨ Funcionalidades Implementadas

### 👥 1. Gestión de Usuarios
- ✅ **Registro de nuevos usuarios** con validación completa
- ✅ **Login/Logout** seguro con sesiones PHP
- ✅ **Roles diferenciados:**
  - 🔹 **Usuario regular:** Puede ver espacios y crear/editar/cancelar sus propias reservas
  - 🔹 **Administrador:** Acceso total a todas las funcionalidades del sistema
- ✅ **Perfil de usuario** completo con:
  - Edición de información personal
  - Cambio de contraseña
  - Estadísticas personales
  - Historial de reservas

### 🏢 2. Sistema de Reservas
- ✅ **Visualización de espacios disponibles** con información detallada
- ✅ **Consulta de disponibilidad en tiempo real** por fecha y espacio
- ✅ **Creación de reservas** con validación de horarios
- ✅ **Edición de reservas existentes** (solo reservas activas)
- ✅ **Cancelación de reservas** con confirmación
- ✅ **Campo de observaciones** para notas adicionales
- ✅ **Lista de reservas del usuario** con filtros

### 🚫 3. Prevención de Conflictos
- ✅ **Validación de horarios disponibles** antes de confirmar reserva
- ✅ **Bloqueo automático de horarios** ya reservados
- ✅ **Verificación de solapamientos** al crear o editar
- ✅ **API de disponibilidad** para consultas en tiempo real

### 📧 4. Sistema de Notificaciones
- ✅ **Email de confirmación** al crear reserva
- ✅ **Email de notificación** al cancelar reserva
- ✅ **Recordatorios automáticos** un día antes de la reserva
- ✅ **Plantillas HTML profesionales** para emails
- ✅ **Configuración flexible:** mail() nativo, SMTP o MailHog

### 🛠️ 5. Panel de Administración Completo
- ✅ **Dashboard con estadísticas** generales del sistema
- ✅ **Gestión de Espacios:**
  - Crear, editar, activar/desactivar y eliminar espacios
  - Control de capacidad y descripción
  - Tipos: Sala, Auditorio, Cancha, Otro
- ✅ **Gestión de Usuarios:**
  - Ver todos los usuarios con estadísticas
  - Activar/desactivar usuarios
  - Cambiar roles (admin/usuario)
  - Ver detalles y reservas de cada usuario
- ✅ **Gestión de Reservas:**
  - Ver todas las reservas del sistema
  - Filtros avanzados por espacio, usuario, fecha y estado
  - Cancelar cualquier reserva

### 📊 6. Reportes y Estadísticas
- ✅ **Estadísticas generales** del sistema
- ✅ **Uso por espacio** con tasas de ocupación
- ✅ **Reservas por mes** (últimos 6 meses)
- ✅ **Usuarios más activos** con ranking
- ✅ **Generación de reportes en PDF** con filtros personalizados
- ✅ **Exportación de datos** para análisis

---

## 🗄️ Estructura de la Base de Datos

### Tabla: `usuarios`
```sql
- id (PK)
- username (UNIQUE)
- email
- password (hash bcrypt)
- nombre
- apellido
- is_admin (0 o 1)
- is_active (0 o 1)
- fecha_registro
```

### Tabla: `espacios`
```sql
- id (PK)
- nombre (UNIQUE)
- tipo (SALA, AUDI, CANCHA, OTRO)
- capacidad
- descripcion
- activo (0 o 1)
- fecha_creacion
```

### Tabla: `reservas`
```sql
- id (PK)
- espacio_id (FK → espacios)
- usuario_id (FK → usuarios)
- fecha
- hora_inicio
- hora_fin
- estado (CONFIRMADA, CANCELADA, PENDIENTE)
- observaciones
- fecha_creacion
- fecha_actualizacion
```

---

## 📁 Estructura de Archivos

```
/reserva_espacios/
│
├── config.php                  # Configuración general y BD
├── email_config.php            # Configuración de emails
│
├── header.php                  # Encabezado común
├── footer.php                  # Pie de página común
│
├── index.php                   # Página principal
├── login.php                   # Inicio de sesión
├── logout.php                  # Cerrar sesión
├── registro.php                # Registro de nuevos usuarios
├── perfil.php                  # Perfil de usuario
│
├── nueva_reserva.php           # Crear reserva
├── editar_reserva.php          # Editar reserva
├── reservas.php                # Lista de reservas del usuario
├── cancelar_reserva.php        # Cancelar reserva
│
├── api_disponibilidad.php      # API para consultar disponibilidad
├── api_usuario_detalles.php    # API para detalles de usuario
│
├── admin.php                   # Dashboard de admin
├── admin_espacios.php          # CRUD de espacios
├── admin_usuarios.php          # Gestión de usuarios
├── admin_reservas.php          # Gestión de todas las reservas
├── admin_reportes.php          # Reportes y estadísticas
├── generar_reporte_pdf.php     # Generar PDF de reportes
│
├── enviar_recordatorios.php    # Script para enviar recordatorios
│
├── base_de_datos.sql           # Script SQL para crear BD
├── INSTRUCCIONES_EMAIL.md      # Guía de configuración de emails
└── MANUAL_COMPLETO.md          # Este archivo
```

---

## 🚀 Instalación y Configuración

### 1. Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache, Nginx, o PHP built-in server)
- Extensiones PHP: PDO, pdo_mysql, mbstring

### 2. Configurar Base de Datos

```sql
-- Ejecutar en MySQL
CREATE DATABASE reserva_espacios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Luego importa el archivo `base_de_datos.sql`:
```bash
mysql -u tu_usuario -p reserva_espacios < base_de_datos.sql
```

### 3. Configurar Conexión a BD

Edita `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'reserva_espacios');
define('DB_USER', 'tu_usuario_db');
define('DB_PASS', 'tu_password_db');
```

### 4. Configurar Emails (Opcional)

Edita `email_config.php` según tus necesidades. Ver `INSTRUCCIONES_EMAIL.md` para más detalles.

### 5. Configurar Seguridad

Edita `config.php`:
```php
define('SECRET_KEY', 'tu-clave-secreta-aleatoria-muy-larga');
define('DEBUG', false); // true solo en desarrollo
```

### 6. Iniciar Servidor

**Opción 1 - XAMPP/WAMP:**
- Copia los archivos a `htdocs/reserva_espacios`
- Accede a: `http://localhost/reserva_espacios`

**Opción 2 - PHP Built-in Server:**
```bash
cd /ruta/al/proyecto
php -S localhost:8000
```
- Accede a: `http://localhost:8000`

---

## 👤 Credenciales Predeterminadas

**Administrador:**
- **Usuario:** admin
- **Contraseña:** admin123

⚠️ **IMPORTANTE:** Cambia esta contraseña después de la instalación.

---

## 📧 Configuración de Notificaciones

### Método 1: mail() nativo (Por defecto)
No requiere configuración adicional, pero necesita sendmail configurado en el servidor.

### Método 2: SMTP (Gmail, Outlook)
Edita `email_config.php`:
```php
define('EMAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'tu_email@gmail.com');
define('SMTP_PASSWORD', 'tu_contraseña_app');
```

### Método 3: MailHog (Testing Local)
Perfecto para desarrollo:
```bash
# Instalar MailHog
brew install mailhog  # Mac
# o descargar de: github.com/mailhog/MailHog

# Ejecutar
mailhog

# Ver emails en: http://localhost:8025
```

Ver `INSTRUCCIONES_EMAIL.md` para configuración completa.

---

## 🔔 Recordatorios Automáticos

### Ejecución Manual:
```bash
php enviar_recordatorios.php
```

### Configurar Cron (Linux/Mac):
```bash
crontab -e

# Agregar línea para ejecutar todos los días a las 9:00 AM
0 9 * * * php /ruta/completa/enviar_recordatorios.php
```

### Configurar Task Scheduler (Windows):
1. Abrir "Programador de tareas"
2. Crear tarea básica → Diaria → 9:00 AM
3. Acción: `C:\xampp\php\php.exe`
4. Argumentos: `C:\ruta\enviar_recordatorios.php`

---

## 🎨 Personalización

### Cambiar Colores y Estilos
El sistema usa **Bulma CSS**. Para personalizar:

1. **Editar `header.php`** (sección `<style>`):
```css
.hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

2. **Cambiar colores de botones:**
Reemplaza clases Bulma:
- `is-primary` → Color primario
- `is-info` → Color info
- `is-success` → Color éxito
- `is-danger` → Color peligro

### Cambiar Nombre de la Aplicación
Edita `config.php`:
```php
define('APP_NAME', 'Tu Nombre de App');
```

---

## 📱 Diseño Responsive

El sistema es completamente responsive y se adapta a:
- 📱 Smartphones
- 💻 Tablets
- 🖥️ Desktops

Probado en Chrome, Firefox, Safari y Edge.

---

## 🔒 Seguridad Implementada

- ✅ Contraseñas hasheadas con bcrypt
- ✅ Protección contra SQL Injection (prepared statements)
- ✅ Sanitización de entradas
- ✅ Validación de sesiones
- ✅ Protección de rutas (requireLogin, requireAdmin)
- ✅ Escape de salidas HTML (htmlspecialchars)
- ✅ Configuración de cookies seguras

---

## 🧪 Testing y Uso

### Flujo de Usuario Regular:

1. **Registrarse** → `registro.php`
2. **Iniciar Sesión** → `login.php`
3. **Ver Espacios Disponibles** → `index.php`
4. **Consultar Disponibilidad** → Formulario en index
5. **Crear Reserva** → `nueva_reserva.php`
6. **Ver Mis Reservas** → `reservas.php`
7. **Editar Reserva** → `editar_reserva.php`
8. **Cancelar Reserva** → Click en botón cancelar
9. **Ver Perfil** → `perfil.php`

### Flujo de Administrador:

1. **Dashboard** → `admin.php`
2. **Gestionar Espacios** → `admin_espacios.php`
   - Agregar nuevo espacio
   - Editar espacio existente
   - Activar/Desactivar/Eliminar
3. **Gestionar Usuarios** → `admin_usuarios.php`
   - Ver todos los usuarios
   - Cambiar roles
   - Ver detalles y reservas
4. **Ver Todas las Reservas** → `admin_reservas.php`
   - Filtrar por múltiples criterios
   - Cancelar reservas
5. **Generar Reportes** → `admin_reportes.php`
   - Ver estadísticas
   - Descargar PDF

---

## 🐛 Solución de Problemas Comunes

### Error: "Connection failed"
- Verifica credenciales en `config.php`
- Asegúrate de que MySQL esté corriendo
- Verifica que la base de datos existe

### Error: "Call to undefined function mail()"
- Instala/configura sendmail en tu servidor
- O cambia a SMTP en `email_config.php`

### Emails no llegan
- Revisa carpeta de SPAM
- Verifica configuración en `email_config.php`
- Usa MailHog para testing local

### Página en blanco
- Activa `define('DEBUG', true)` en `config.php`
- Revisa logs de PHP
- Verifica permisos de archivos

### Sesión no persiste
- Verifica que las cookies estén habilitadas
- Revisa configuración de sesiones en PHP

---

## 📚 Recursos y Referencias

- **Bulma CSS:** https://bulma.io/documentation/
- **Font Awesome:** https://fontawesome.com/icons
- **PHP PDO:** https://www.php.net/manual/es/book.pdo.php
- **MySQL:** https://dev.mysql.com/doc/
- **PHPMailer:** https://github.com/PHPMailer/PHPMailer

---

## 📄 Licencia

Este proyecto fue desarrollado como parte del **Proyecto GP2** para fines educativos.

---

## 👨‍💻 Autor

**Proyecto GP2**
- Fecha: Octubre 2025
- Sistema completo mejorado y optimizado

---

## ✅ Checklist de Funcionalidades

### Gestor de Usuarios ✅
- [x] Registro e inicio de sesión
- [x] Roles (administrador y usuario regular)
- [x] Perfil de usuario con historial de reservas
- [x] Cambio de contraseña
- [x] Edición de información personal

### Sistema de Reservas ✅
- [x] Visualización del calendario con disponibilidad
- [x] Filtro por tipo de espacio, fecha y horario
- [x] Creación, edición y cancelación de reservas
- [x] Campo de observaciones

### Prevención de Conflictos ✅
- [x] Validación de horarios disponibles
- [x] Bloqueo de horarios ya reservados
- [x] API de disponibilidad en tiempo real

### Notificaciones ✅
- [x] Recordatorios por email para reservas
- [x] Alertas de cancelación o cambios
- [x] Configuración flexible de envío

### Panel de Administración ✅
- [x] Gestión de espacios (agregar, editar, eliminar)
- [x] Visualización de reportes de uso
- [x] Control de usuarios y permisos
- [x] Ver todas las reservas con filtros
- [x] Estadísticas generales
- [x] Exportación a PDF

---

¡Sistema completo y listo para usar! 🎉
