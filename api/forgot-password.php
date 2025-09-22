<?php
require_once '../includes/config.php';
require_once '../includes/email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'send_code') {
    handleSendCode();
} elseif ($action === 'reset_password') {
    handleResetPassword();
} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function handleSendCode() {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email es requerido']);
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Verificar si el usuario existe
        $sql = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            return;
        }
        
        // Generar código de verificación
        $verificationCode = mt_rand(100000, 999999);
        
        // Guardar código en la base de datos con timestamp
        $sql = "UPDATE usuarios SET codigo_verificacion = ?, codigo_timestamp = NOW() WHERE correo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$verificationCode, $email]);
        
        // Verificar si hay configuración SMTP completa
        $hasSMTP = !empty(SMTP_SERVER) && !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD);
        
        if ($hasSMTP) {
            // Enviar email real
            $userName = $user['nombre'] . ' ' . $user['apellido_1'];
            $emailSent = sendVerificationEmail($email, $userName, $verificationCode);
            
            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Código de verificación enviado a tu correo electrónico'
                ]);
            } else {
                error_log("Error enviando email a $email. Código: $verificationCode");
                echo json_encode([
                    'success' => true,
                    'message' => 'Error al enviar correo. Código: ' . $verificationCode,
                    'debug_code' => $verificationCode
                ]);
            }
        } else {
            // No hay SMTP configurado, mostrar código en pantalla
            echo json_encode([
                'success' => true,
                'message' => 'Código de verificación generado. Configura SMTP para envío automático.',
                'debug_code' => $verificationCode,
                'smtp_not_configured' => true
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error en send_code: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    }
}

function handleResetPassword() {
    $email = $_POST['email'] ?? '';
    $code = $_POST['code'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    
    if (empty($email) || empty($code) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }
    
    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        return;
    }
    
    try {
        $pdo = getDBConnection();
        
        // Verificar código de verificación y tiempo de expiración (15 minutos)
        $sql = "SELECT * FROM usuarios WHERE correo = ? AND codigo_verificacion = ? AND codigo_timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Verificar si el código existe pero ha expirado
            $sql = "SELECT * FROM usuarios WHERE correo = ? AND codigo_verificacion = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $code]);
            $expiredUser = $stmt->fetch();
            
            if ($expiredUser) {
                echo json_encode(['success' => false, 'message' => 'El código de verificación ha expirado. Solicita uno nuevo.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Código de verificación incorrecto']);
            }
            return;
        }
        
        // Actualizar contraseña
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET clave = ?, codigo_verificacion = NULL WHERE correo = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$hashedPassword, $email]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Contraseña actualizada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
        }
        
    } catch (Exception $e) {
        error_log("Error en reset_password: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
    }
}
?>
