<?php
/**
 * Configuración de Email para el sistema de notificaciones
 * 
 * CONFIGURACIÓN PARA DESARROLLO LOCAL:
 * Opción 1: Usar mail() nativo de PHP (requiere sendmail configurado)
 * Opción 2: Usar SMTP de Gmail/Outlook (recomendado para testing)
 * Opción 3: Usar MailHog o Papercut para capturar emails localmente
 */

// Método de envío: 'smtp', 'mail', 'sendmail'
define('EMAIL_METHOD', 'mail'); // Cambiar a 'smtp' si quieres usar SMTP

// Configuración SMTP (solo si EMAIL_METHOD = 'smtp')
define('SMTP_HOST', 'smtp.gmail.com'); // o smtp-mail.outlook.com para Outlook
define('SMTP_PORT', 587); // 587 para TLS, 465 para SSL
define('SMTP_SECURE', 'tls'); // 'tls' o 'ssl'
define('SMTP_USERNAME', 'tu_email@gmail.com'); // Tu email
define('SMTP_PASSWORD', 'tu_contraseña_app'); // Contraseña de aplicación
define('SMTP_DEBUG', 0); // 0 = sin debug, 2 = debug completo

// Configuración del remitente
define('EMAIL_FROM', 'noreply@reservaespacios.local');
define('EMAIL_FROM_NAME', 'Sistema de Reserva de Espacios');

// Configuración general
define('EMAIL_ENABLED', true); // Cambiar a false para deshabilitar emails
define('EMAIL_CHARSET', 'UTF-8');

/**
 * Función para enviar emails
 * 
 * @param string $to Email del destinatario
 * @param string $to_name Nombre del destinatario
 * @param string $subject Asunto del email
 * @param string $body Cuerpo del email en HTML
 * @return bool true si se envió correctamente, false en caso contrario
 */
function enviarEmail($to, $to_name, $subject, $body) {
    if (!EMAIL_ENABLED) {
        return true; // Simular envío exitoso si está deshabilitado
    }
    
    try {
        if (EMAIL_METHOD === 'mail') {
            // Usar mail() nativo de PHP
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=" . EMAIL_CHARSET . "\r\n";
            $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
            $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            return mail($to, $subject, $body, $headers);
        } elseif (EMAIL_METHOD === 'smtp') {
            // Para SMTP necesitarías incluir PHPMailer aquí
            // Por ahora, retornamos true para simular
            // En producción, implementa PHPMailer aquí
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $e->getMessage());
        return false;
    }
}

/**
 * Generar HTML para emails con estilo
 */
function generarPlantillaEmail($titulo, $contenido, $boton_url = '', $boton_texto = '') {
    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                background: #ffffff;
                padding: 30px;
                border: 1px solid #e0e0e0;
            }
            .footer {
                background: #f5f5f5;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-radius: 0 0 10px 10px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background-color: #3273dc;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
            .info-box {
                background: #f0f7ff;
                border-left: 4px solid #3273dc;
                padding: 15px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📅 ' . htmlspecialchars($titulo) . '</h1>
        </div>
        <div class="content">
            ' . $contenido . '
        </div>';
    
    if ($boton_url && $boton_texto) {
        $html .= '
        <div style="text-align: center; padding: 20px;">
            <a href="' . htmlspecialchars($boton_url) . '" class="button">' . htmlspecialchars($boton_texto) . '</a>
        </div>';
    }
    
    $html .= '
        <div class="footer">
            <p><strong>Sistema de Reserva de Espacios</strong></p>
            <p>Este es un email automático, por favor no responder.</p>
            <p>&copy; ' . date('Y') . ' - Todos los derechos reservados</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Enviar notificación de reserva confirmada
 */
function enviarNotificacionReservaConfirmada($usuario, $reserva, $espacio) {
    $subject = "Reserva Confirmada - " . $espacio['nombre'];
    
    $contenido = '
    <h2>¡Hola ' . htmlspecialchars($usuario['nombre']) . '!</h2>
    <p>Tu reserva ha sido confirmada exitosamente.</p>
    
    <div class="info-box">
        <h3>Detalles de la Reserva:</h3>
        <p><strong>📍 Espacio:</strong> ' . htmlspecialchars($espacio['nombre']) . '</p>
        <p><strong>📅 Fecha:</strong> ' . date('d/m/Y', strtotime($reserva['fecha'])) . '</p>
        <p><strong>🕐 Horario:</strong> ' . substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5) . '</p>
        <p><strong>👥 Capacidad:</strong> ' . $espacio['capacidad'] . ' personas</p>
        ' . (!empty($reserva['observaciones']) ? '<p><strong>📝 Observaciones:</strong> ' . htmlspecialchars($reserva['observaciones']) . '</p>' : '') . '
    </div>
    
    <p>Por favor, llega a tiempo y respeta el horario reservado.</p>
    <p>Si necesitas cancelar tu reserva, puedes hacerlo desde tu panel de reservas.</p>
    ';
    
    $body = generarPlantillaEmail(
        'Reserva Confirmada',
        $contenido,
        APP_URL . '/reservas.php',
        'Ver Mis Reservas'
    );
    
    return enviarEmail(
        $usuario['email'],
        $usuario['nombre'] . ' ' . $usuario['apellido'],
        $subject,
        $body
    );
}

/**
 * Enviar notificación de reserva cancelada
 */
function enviarNotificacionReservaCancelada($usuario, $reserva, $espacio) {
    $subject = "Reserva Cancelada - " . $espacio['nombre'];
    
    $contenido = '
    <h2>Hola ' . htmlspecialchars($usuario['nombre']) . ',</h2>
    <p>Tu reserva ha sido <strong>cancelada</strong>.</p>
    
    <div class="info-box">
        <h3>Detalles de la Reserva Cancelada:</h3>
        <p><strong>📍 Espacio:</strong> ' . htmlspecialchars($espacio['nombre']) . '</p>
        <p><strong>📅 Fecha:</strong> ' . date('d/m/Y', strtotime($reserva['fecha'])) . '</p>
        <p><strong>🕐 Horario:</strong> ' . substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5) . '</p>
    </div>
    
    <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
    <p>Puedes hacer una nueva reserva en cualquier momento.</p>
    ';
    
    $body = generarPlantillaEmail(
        'Reserva Cancelada',
        $contenido,
        APP_URL . '/nueva_reserva.php',
        'Hacer Nueva Reserva'
    );
    
    return enviarEmail(
        $usuario['email'],
        $usuario['nombre'] . ' ' . $usuario['apellido'],
        $subject,
        $body
    );
}

/**
 * Enviar recordatorio de reserva (para ejecutar con cron o script)
 */
function enviarRecordatorioReserva($usuario, $reserva, $espacio) {
    $subject = "Recordatorio: Reserva para mañana - " . $espacio['nombre'];
    
    $contenido = '
    <h2>¡Hola ' . htmlspecialchars($usuario['nombre']) . '!</h2>
    <p>Este es un recordatorio de que tienes una reserva confirmada para <strong>mañana</strong>.</p>
    
    <div class="info-box">
        <h3>Detalles de tu Reserva:</h3>
        <p><strong>📍 Espacio:</strong> ' . htmlspecialchars($espacio['nombre']) . '</p>
        <p><strong>📅 Fecha:</strong> ' . date('d/m/Y', strtotime($reserva['fecha'])) . '</p>
        <p><strong>🕐 Horario:</strong> ' . substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5) . '</p>
    </div>
    
    <p>No olvides llegar a tiempo. ¡Te esperamos!</p>
    ';
    
    $body = generarPlantillaEmail(
        'Recordatorio de Reserva',
        $contenido,
        APP_URL . '/reservas.php',
        'Ver Detalles'
    );
    
    return enviarEmail(
        $usuario['email'],
        $usuario['nombre'] . ' ' . $usuario['apellido'],
        $subject,
        $body
    );
}
?>
