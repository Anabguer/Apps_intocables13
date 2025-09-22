<?php
// ==================== CONFIGURACIÓN DE BASE DE DATOS ====================
// ESTE ARCHIVO CONTIENE LA CONFIGURACIÓN DE BASE DE DATOS
// DETECCIÓN AUTOMÁTICA DE ENTORNO: LOCALHOST vs HOSTALIA

// Detectar entorno automáticamente
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || 
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

if ($isLocalhost) {
    // ==================== DESARROLLO LOCAL (XAMPP) ====================
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'intocables_db');
} else {
    // ==================== PRODUCCIÓN (HOSTALIA) ====================
    define('DB_HOST', 'PMYSQL165.dns-servicio.com');
    define('DB_USER', 'amg');
    define('DB_PASSWORD', 'Amg626782287');
    define('DB_NAME', '9606966_intocables');
}

// Función para conectar a la base de datos
function getDBConnection() {
    try {
        // Usar charset apropiado según entorno
        $charset = defined('DB_HOST') && DB_HOST === 'localhost' ? 'utf8mb4' : 'utf8';
        
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . $charset,
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        // En producción, log del error sin exponer detalles
        if (DB_HOST !== 'localhost') {
            error_log("Error de conexión BD: " . $e->getMessage());
            die("Error de conexión a la base de datos. Contacte al administrador.");
        } else {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>
