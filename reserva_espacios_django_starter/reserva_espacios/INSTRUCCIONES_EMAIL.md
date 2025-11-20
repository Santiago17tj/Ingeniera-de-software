# 📧 Configuración del Sistema de Notificaciones por Email

## Descripción
El sistema incluye notificaciones automáticas por email para:
- ✅ Confirmación de reservas
- ❌ Cancelación de reservas
- 🔔 Recordatorios de reservas (un día antes)

---

## 🔧 Configuración

### Archivo de Configuración
Edita el archivo `email_config.php` para configurar el método de envío:

```php
// Método de envío
define('EMAIL_METHOD', 'mail'); // 'mail' o 'smtp'

// Activar/Desactivar emails
define('EMAIL_ENABLED', true); // false para deshabilitar
```

---

## 📮 Métodos de Envío

### Opción 1: mail() nativo de PHP (Por defecto)
**Ventajas:** Simple, no requiere configuración adicional
**Desventajas:** Requiere que el servidor tenga sendmail o similar configurado

```php
define('EMAIL_METHOD', 'mail');
```

**Requisitos en el servidor:**
- PHP con función `mail()` habilitada
- Sendmail, Postfix o similar configurado

---

### Opción 2: SMTP (Gmail, Outlook, etc.)
**Ventajas:** Más confiable, funciona en cualquier servidor
**Desventajas:** Requiere credenciales de email

#### Configuración para Gmail:

1. **Activar "Acceso de aplicaciones menos seguras"** o crear una **Contraseña de Aplicación**:
   - Ve a: https://myaccount.google.com/security
   - Activa la verificación en 2 pasos
   - Crea una "Contraseña de aplicación"

2. **Editar `email_config.php`:**
```php
define('EMAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu_email@gmail.com');
define('SMTP_PASSWORD', 'tu_contraseña_app'); // Contraseña de aplicación
```

#### Configuración para Outlook/Hotmail:
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu_email@outlook.com');
define('SMTP_PASSWORD', 'tu_contraseña');
```

---

### Opción 3: MailHog (Testing Local)
**Perfecto para desarrollo local - captura emails sin enviarlos**

1. **Instalar MailHog:**
```bash
# Windows: Descargar de https://github.com/mailhog/MailHog/releases
# Linux/Mac:
brew install mailhog  # Mac
go get github.com/mailhog/MailHog  # Linux
```

2. **Ejecutar MailHog:**
```bash
mailhog
```

3. **Configurar PHP para usar MailHog:**
```php
define('EMAIL_METHOD', 'smtp');
define('SMTP_HOST', '127.0.0.1');
define('SMTP_PORT', 1025);
define('SMTP_SECURE', '');
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
```

4. **Ver emails capturados:**
   - Abre en el navegador: http://localhost:8025

---

## 🔔 Recordatorios Automáticos

El sistema incluye un script para enviar recordatorios un día antes de cada reserva.

### Ejecución Manual:
```bash
php enviar_recordatorios.php
```

### Configurar Cron Job (Ejecución Automática):

**Linux/Mac:**
```bash
# Editar crontab
crontab -e

# Agregar línea para ejecutar todos los días a las 9:00 AM
0 9 * * * php /ruta/completa/enviar_recordatorios.php
```

**Windows (Task Scheduler):**
1. Abrir "Programador de tareas"
2. Crear tarea básica
3. Ejecutar diariamente a las 9:00 AM
4. Acción: Iniciar programa
5. Programa: `C:\xampp\php\php.exe`
6. Argumentos: `C:\ruta\completa\enviar_recordatorios.php`

---

## 🧪 Probar el Sistema

### Enviar Email de Prueba:
Crea una reserva desde la interfaz web y verifica:
1. Mensaje de confirmación en la página
2. Email recibido en la bandeja

### Verificar Configuración:
```bash
# Probar envío de recordatorios
php enviar_recordatorios.php
```

---

## ❌ Desactivar Emails

Si no quieres usar notificaciones por email:

```php
define('EMAIL_ENABLED', false);
```

El sistema seguirá funcionando normalmente, solo sin enviar emails.

---

## 🔍 Solución de Problemas

### "mail() function not found"
- Instala y configura sendmail/postfix en tu servidor
- O cambia a SMTP

### "SMTP Error: Could not authenticate"
- Verifica username y password
- Usa contraseña de aplicación (Gmail)
- Verifica que el servidor permita conexiones SMTP salientes

### "Failed to connect to server"
- Verifica SMTP_HOST y SMTP_PORT
- Verifica firewall
- Prueba con diferentes puertos (587, 465, 25)

### Emails no llegan
- Revisa carpeta de SPAM
- Verifica que EMAIL_ENABLED = true
- Revisa logs del servidor PHP
- Prueba con MailHog para debugging

---

## 📝 Notas Importantes

1. **Seguridad:** Nunca subas credenciales de email a repositorios públicos
2. **Producción:** Usa variables de entorno para credenciales sensibles
3. **Límites:** Gmail limita envíos (≈500/día), considera servicios como SendGrid para producción
4. **Testing:** Usa MailHog durante desarrollo para no enviar emails reales

---

## 📚 Recursos Adicionales

- PHPMailer: https://github.com/PHPMailer/PHPMailer
- MailHog: https://github.com/mailhog/MailHog
- Gmail App Passwords: https://support.google.com/accounts/answer/185833
- SendGrid (Alternativa para producción): https://sendgrid.com/

---

¡Sistema de notificaciones listo! 🎉
