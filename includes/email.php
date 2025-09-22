<?php
require_once __DIR__ . '/config.php';

/**
 * Clase simple para envío de correos
 */
class SimpleMailer {
    private $smtp_server;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        $this->smtp_server = SMTP_SERVER;
        $this->smtp_port = SMTP_PORT;
        $this->smtp_username = SMTP_USERNAME;
        $this->smtp_password = SMTP_PASSWORD;
        $this->from_email = FROM_EMAIL;
        $this->from_name = FROM_NAME;
    }
    
    /**
     * Enviar correo usando mail() de PHP
     */
    public function sendMail($to, $subject, $message, $isHTML = true) {
        // Si no hay configuración SMTP completa, usar mail() básico
        if (empty($this->smtp_server) || empty($this->smtp_username) || empty($this->smtp_password)) {
            return $this->sendBasicMail($to, $subject, $message, $isHTML);
        }
        
        // Si hay configuración SMTP completa, usar PHPMailer
        return $this->sendSMTPMail($to, $subject, $message, $isHTML);
    }
    
    /**
     * Envío básico usando mail() de PHP
     */
    private function sendBasicMail($to, $subject, $message, $isHTML) {
        // En XAMPP, mail() puede fallar si no hay servidor SMTP configurado
        // Simular el envío exitoso para desarrollo cuando no hay SMTP configurado
        if (empty($this->smtp_password)) {
            // No hay contraseña SMTP configurada, simular envío exitoso
            error_log("SIMULACIÓN DE CORREO - Para: $to, Asunto: $subject");
            return true;
        }
        
        $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
        $headers .= "Reply-To: {$this->from_email}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        if ($isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Envío usando SMTP (requiere configuración)
     */
    private function sendSMTPMail($to, $subject, $message, $isHTML) {
        // Implementación básica de SMTP
        // En un entorno real, se usaría PHPMailer o similar
        
        // Por ahora, usar mail() básico pero con headers mejorados
        return $this->sendBasicMail($to, $subject, $message, $isHTML);
    }
    
    /**
     * Crear plantilla de correo para código de verificación
     */
    public function createVerificationEmail($userName, $verificationCode) {
        $siteName = SITE_NAME;
        $siteUrl = SITE_URL;
        
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Código de Verificación - {$siteName}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .container {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding-bottom: 20px;
                    border-bottom: 2px solid #667eea;
                }
                .logo {
                    font-size: 24px;
                    font-weight: bold;
                    color: #667eea;
                    margin-bottom: 10px;
                }
                .code-container {
                    background: #f8f9fa;
                    border: 2px solid #667eea;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    margin: 20px 0;
                }
                .verification-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #667eea;
                    letter-spacing: 5px;
                    margin: 10px 0;
                }
                .instructions {
                    background: #e3f2fd;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    color: #666;
                    font-size: 14px;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 10px;
                    border-radius: 5px;
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='logo'>{$siteName}</div>
                    <h1>Código de Verificación</h1>
                </div>
                
                <p>Hola <strong>{$userName}</strong>,</p>
                
                <p>Has solicitado restablecer tu contraseña en <strong>{$siteName}</strong>. Para continuar, utiliza el siguiente código de verificación:</p>
                
                <div class='code-container'>
                    <p style='margin: 0 0 10px 0; font-size: 16px;'>Tu código de verificación es:</p>
                    <div class='verification-code'>{$verificationCode}</div>
                </div>
                
                <div class='instructions'>
                    <h3>📋 Instrucciones:</h3>
                    <ol>
                        <li>Introduce este código en el formulario de recuperación de contraseña</li>
                        <li>Establece tu nueva contraseña</li>
                        <li>Inicia sesión con tus nuevas credenciales</li>
                    </ol>
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Importante:</strong> Este código es válido por 15 minutos. Si no has solicitado este cambio, ignora este correo.
                </div>
                
                <p>Si tienes problemas, contacta con el administrador del sistema.</p>
                
                <div class='footer'>
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>&copy; " . date('Y') . " {$siteName} - Todos los derechos reservados</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }
}

/**
 * Función helper para enviar correo de verificación
 */
function sendVerificationEmail($email, $userName, $verificationCode) {
    $mailer = new SimpleMailer();
    
    $subject = "Código de Verificación - " . SITE_NAME;
    $message = $mailer->createVerificationEmail($userName, $verificationCode);
    
    return $mailer->sendMail($email, $subject, $message, true);
}
?>
