<?php
// ==================== CONFIGURACIÓN DE BASE DE DATOS PARA PRODUCCIÓN ====================
// ESTE ES UN ARCHIVO DE EJEMPLO PARA CONFIGURAR LA BASE DE DATOS EN PRODUCCIÓN
// Copia este archivo como 'database.php' y modifica los valores según tu servidor

// Configuración para producción (ejemplo)
define('DB_HOST', 'tu-servidor-bd.com');
define('DB_USER', 'tu_usuario_bd');
define('DB_PASSWORD', 'tu_password_seguro');
define('DB_NAME', 'intocables_production');

// Función para conectar a la base de datos
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
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
        die("Error de conexión: " . $e->getMessage());
    }
}
?>
