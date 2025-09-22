<?php
// Sistema de autenticación y verificación de sesiones

// Función para verificar si el usuario está logueado
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para obtener datos del usuario actual
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return $_SESSION['user'] ?? null;
}

// Función para hacer login
function loginUser($userData) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user'] = $userData;
    $_SESSION['is_logged_in'] = true;
}

// Función para hacer logout
function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

// Función para verificar permisos de administrador
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['perfil'] === 'admin';
}

// Función para verificar permisos de editor
function isEdit() {
    $user = getCurrentUser();
    return $user && $user['perfil'] === 'edit';
}

// Función para verificar si es usuario normal
function isUser() {
    $user = getCurrentUser();
    return $user && $user['perfil'] === 'user';
}

// Función para requerir login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Función para requerir permisos de administrador
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

// Función para requerir permisos de administrador o editor
function requireAdminOrEdit() {
    requireLogin();
    
    if (!isAdmin() && !isEdit()) {
        header('Location: index.php');
        exit;
    }
}

// Función para verificar autenticación desde JavaScript
function checkAuthFromJS() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user = getCurrentUser();
    
    if ($user) {
        echo '<script>
            localStorage.setItem("isLoggedIn", "true");
            localStorage.setItem("user", JSON.stringify(' . json_encode($user) . '));
        </script>';
    } else {
        echo '<script>
            localStorage.removeItem("isLoggedIn");
            localStorage.removeItem("user");
        </script>';
    }
}
?>
