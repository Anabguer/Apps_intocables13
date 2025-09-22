<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo admin puede gestionar configuración)
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    error_log("Error en settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function handleGet($pdo) {
    // Obtener configuración actual desde las constantes
    $settings = [
        'site_name' => SITE_NAME,
        'site_url' => SITE_URL,
        'admin_email' => ADMIN_EMAIL,
        'timezone' => TIMEZONE,
        'max_file_size' => MAX_FILE_SIZE,
        'allowed_extensions' => ALLOWED_EXTENSIONS,
        'thumbnail_size' => THUMBNAIL_SIZE,
        'image_quality' => IMAGE_QUALITY,
        'session_timeout' => SESSION_TIMEOUT,
        'max_login_attempts' => MAX_LOGIN_ATTEMPTS,
        'password_min_length' => PASSWORD_MIN_LENGTH,
        'require_strong_passwords' => REQUIRE_STRONG_PASSWORDS ? '1' : '0',
        'email_notifications' => EMAIL_NOTIFICATIONS ? '1' : '0',
        'smtp_server' => SMTP_SERVER,
        'smtp_port' => SMTP_PORT,
        'smtp_username' => SMTP_USERNAME,
        'smtp_password' => SMTP_PASSWORD,
        'from_email' => FROM_EMAIL,
        'from_name' => FROM_NAME,
        'items_per_page' => ITEMS_PER_PAGE,
        'gallery_layout' => GALLERY_LAYOUT,
        'enable_zoom' => ENABLE_ZOOM ? '1' : '0',
        'auto_play_videos' => AUTO_PLAY_VIDEOS ? '1' : '0'
    ];
    
    echo json_encode(['success' => true, 'settings' => $settings]);
}

function handlePost($pdo) {
    // Validar datos
    $validatedData = validateSettings($_POST);
    if (!$validatedData['valid']) {
        echo json_encode(['success' => false, 'message' => $validatedData['message']]);
        return;
    }
    
    $settings = $validatedData['data'];
    
    // Actualizar archivo config.php
    $success = updateConfigFile($settings);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Configuración guardada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la configuración']);
    }
}

function validateSettings($data) {
    $settings = [];
    $errors = [];
    
    // Configuración General
    $settings['site_name'] = trim($data['site_name'] ?? '');
    if (empty($settings['site_name'])) {
        $errors[] = 'El nombre del sitio es obligatorio';
    }
    
    $settings['site_url'] = trim($data['site_url'] ?? '');
    if (empty($settings['site_url']) || !filter_var($settings['site_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'La URL del sitio debe ser válida';
    }
    
    $settings['admin_email'] = trim($data['admin_email'] ?? '');
    if (empty($settings['admin_email']) || !filter_var($settings['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email del administrador debe ser válido';
    }
    
    $settings['timezone'] = $data['timezone'] ?? 'Europe/Madrid';
    
    // Configuración de Archivos
    $settings['max_file_size'] = (int)($data['max_file_size'] ?? 10);
    if ($settings['max_file_size'] < 1 || $settings['max_file_size'] > 100) {
        $errors[] = 'El tamaño máximo de archivo debe estar entre 1 y 100 MB';
    }
    
    $settings['allowed_extensions'] = trim($data['allowed_extensions'] ?? '');
    if (empty($settings['allowed_extensions'])) {
        $errors[] = 'Las extensiones permitidas son obligatorias';
    }
    
    $settings['thumbnail_size'] = (int)($data['thumbnail_size'] ?? 200);
    if ($settings['thumbnail_size'] < 100 || $settings['thumbnail_size'] > 500) {
        $errors[] = 'El tamaño de miniaturas debe estar entre 100 y 500 px';
    }
    
    $settings['image_quality'] = (int)($data['image_quality'] ?? 85);
    if ($settings['image_quality'] < 50 || $settings['image_quality'] > 100) {
        $errors[] = 'La calidad de imagen debe estar entre 50 y 100%';
    }
    
    // Configuración de Seguridad
    $settings['session_timeout'] = (int)($data['session_timeout'] ?? 120);
    if ($settings['session_timeout'] < 15 || $settings['session_timeout'] > 480) {
        $errors[] = 'El tiempo de sesión debe estar entre 15 y 480 minutos';
    }
    
    $settings['max_login_attempts'] = (int)($data['max_login_attempts'] ?? 5);
    if ($settings['max_login_attempts'] < 3 || $settings['max_login_attempts'] > 10) {
        $errors[] = 'Los intentos de login deben estar entre 3 y 10';
    }
    
    $settings['password_min_length'] = (int)($data['password_min_length'] ?? 6);
    if ($settings['password_min_length'] < 6 || $settings['password_min_length'] > 20) {
        $errors[] = 'La longitud mínima de contraseña debe estar entre 6 y 20 caracteres';
    }
    
    $settings['require_strong_passwords'] = $data['require_strong_passwords'] ?? '0';
    
    // Configuración de Notificaciones
    $settings['email_notifications'] = $data['email_notifications'] ?? '1';
    $settings['smtp_server'] = trim($data['smtp_server'] ?? '');
    $settings['smtp_port'] = (int)($data['smtp_port'] ?? 0);
    $settings['smtp_username'] = trim($data['smtp_username'] ?? '');
    $settings['smtp_password'] = trim($data['smtp_password'] ?? '');
    $settings['from_email'] = trim($data['from_email'] ?? '');
    if (empty($settings['from_email']) || !filter_var($settings['from_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email de envío debe ser válido';
    }
    $settings['from_name'] = trim($data['from_name'] ?? '');
    if (empty($settings['from_name'])) {
        $errors[] = 'El nombre del remitente es obligatorio';
    }
    
    // Configuración de Galería
    $settings['items_per_page'] = (int)($data['items_per_page'] ?? 20);
    if ($settings['items_per_page'] < 10 || $settings['items_per_page'] > 100) {
        $errors[] = 'Los elementos por página deben estar entre 10 y 100';
    }
    
    $settings['gallery_layout'] = $data['gallery_layout'] ?? 'grid';
    $settings['enable_zoom'] = $data['enable_zoom'] ?? '1';
    $settings['auto_play_videos'] = $data['auto_play_videos'] ?? '1';
    
    if (!empty($errors)) {
        return ['valid' => false, 'message' => implode(', ', $errors)];
    }
    
    return ['valid' => true, 'data' => $settings];
}

function updateConfigFile($settings) {
    $configFile = '../includes/config.php';
    
    // Leer el archivo actual
    $content = file_get_contents($configFile);
    if ($content === false) {
        return false;
    }
    
    // Mapear los nombres de configuración a las constantes
    // NOTA: NO incluir DB_HOST, DB_USER, DB_PASSWORD, DB_NAME
    // Estos valores son específicos del entorno y no deben ser modificables
    $configMap = [
        'site_name' => 'SITE_NAME',
        'site_url' => 'SITE_URL',
        'admin_email' => 'ADMIN_EMAIL',
        'timezone' => 'TIMEZONE',
        'max_file_size' => 'MAX_FILE_SIZE',
        'allowed_extensions' => 'ALLOWED_EXTENSIONS',
        'thumbnail_size' => 'THUMBNAIL_SIZE',
        'image_quality' => 'IMAGE_QUALITY',
        'session_timeout' => 'SESSION_TIMEOUT',
        'max_login_attempts' => 'MAX_LOGIN_ATTEMPTS',
        'password_min_length' => 'PASSWORD_MIN_LENGTH',
        'require_strong_passwords' => 'REQUIRE_STRONG_PASSWORDS',
        'email_notifications' => 'EMAIL_NOTIFICATIONS',
        'smtp_server' => 'SMTP_SERVER',
        'smtp_port' => 'SMTP_PORT',
        'smtp_username' => 'SMTP_USERNAME',
        'smtp_password' => 'SMTP_PASSWORD',
        'from_email' => 'FROM_EMAIL',
        'from_name' => 'FROM_NAME',
        'items_per_page' => 'ITEMS_PER_PAGE',
        'gallery_layout' => 'GALLERY_LAYOUT',
        'enable_zoom' => 'ENABLE_ZOOM',
        'auto_play_videos' => 'AUTO_PLAY_VIDEOS'
    ];
    
    // Actualizar cada constante
    foreach ($settings as $key => $value) {
        if (isset($configMap[$key])) {
            $constantName = $configMap[$key];
            
            // Determinar el tipo de valor para formatear correctamente
            if (is_numeric($value)) {
                $formattedValue = $value;
            } elseif (in_array($key, ['require_strong_passwords', 'email_notifications', 'enable_zoom', 'auto_play_videos'])) {
                $formattedValue = $value === '1' ? 'true' : 'false';
            } else {
                $formattedValue = "'" . addslashes($value) . "'";
            }
            
            // Buscar y reemplazar la constante
            // Patrón más flexible que maneja cadenas vacías y valores con comillas
            $pattern = "/define\('" . $constantName . "',\s*[^;]+\);/";
            $replacement = "define('" . $constantName . "', " . $formattedValue . ");";
            
            $content = preg_replace($pattern, $replacement, $content);
            
            // Si no se encontró el patrón, intentar con un patrón más específico para cadenas vacías
            if (strpos($content, "define('" . $constantName . "', " . $formattedValue . ");") === false) {
                $pattern2 = "/define\('" . $constantName . "',\s*''\);/";
                $replacement2 = "define('" . $constantName . "', " . $formattedValue . ");";
                $content = preg_replace($pattern2, $replacement2, $content);
            }
        }
    }
    
    // Escribir el archivo actualizado
    return file_put_contents($configFile, $content) !== false;
}
?>
