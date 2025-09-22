<?php
// ==================== CONFIGURACIÓN DE RUTAS ====================
// DETECCIÓN AUTOMÁTICA DE ENTORNO: LOCALHOST vs HOSTALIA

// Detectar entorno automáticamente
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || 
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

if ($isLocalhost) {
    // ==================== DESARROLLO LOCAL (XAMPP) ====================
    define('BASE_URL', '/intocables/');
} else {
    // ==================== PRODUCCIÓN (HOSTALIA) ====================
    define('BASE_URL', '/');  // Ajustar según la estructura en Hostalia
}

// Rutas derivadas (automáticas)
define('CSS_URL', BASE_URL . 'css/');
define('JS_URL', BASE_URL . 'js/');
define('IMG_URL', BASE_URL . 'img/');
define('API_URL', BASE_URL . 'api/');
?>
