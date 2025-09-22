<?php
require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/auth.php';

// Obtener información del usuario actual
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Intocables - Fotos y Videos'; ?></title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>styles.css?v=<?php echo filemtime(__DIR__ . '/../css/styles.css'); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo IMG_URL; ?>favicon.ico">
    
    <!-- Variables globales para JavaScript -->
    <script>
        window.APP_CONFIG = {
            BASE_URL: '<?php echo BASE_URL; ?>',
            SITE_URL: '<?php echo SITE_URL; ?>',
            IMG_URL: '<?php echo IMG_URL; ?>',
            VERSION: '<?php echo filemtime(__DIR__ . '/../js/main.js'); ?>'
        };
    </script>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo-section">
                <div class="logo">
                    <a href="../vista/in_paginaInicial.php">
                        <img src="<?php echo getImagePath('/img/LogosBanners/Logo2conTitulo.png'); ?>" alt="Logo" class="logo-image">
                    </a>
                </div>
                <div class="welcome-message">
                    <span class="welcome-text">
                        <?php 
                        if ($currentUser && isset($currentUser['sexo'])) {
                            echo $currentUser['sexo'] === 'M' ? 'Bienvenida' : 'Bienvenido';
                        } else {
                            echo 'Bienvenido';
                        }
                        ?>
                    </span>
                    <span class="user-name" id="userName">
                        <?php echo $currentUser ? $currentUser['nombre'] : 'Usuario'; ?>
                    </span>
                </div>
            </div>
            <nav class="nav">
                <a href="<?php echo BASE_URL; ?>" class="nav-link">Inicio</a>
                <a href="<?php echo BASE_URL; ?>fotos-videos/" class="nav-link">Fotos & Videos</a>
                <a href="<?php echo BASE_URL; ?>presentaciones/" class="nav-link">Montajes & Tips</a>
                <a href="<?php echo htmlspecialchars(getRecipesLink()); ?>" class="nav-link" target="_blank" rel="noopener noreferrer">Recetas</a>
                <a href="#" class="nav-link" onclick="logout()">Salir</a>
            </nav>
        </div>
    </header>
    
    <main class="main">
    
    <script>
    // Función para logout
    function logout() {
        if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
            fetch('<?php echo BASE_URL; ?>api/logout.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar localStorage
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('user');
                    
                    // Redirigir al login
                    window.location.href = '<?php echo BASE_URL; ?>login.php';
                } else {
                    alert('Error al cerrar sesión');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cerrar sesión');
            });
        }
    }
    
    // Función para actualizar el nombre del usuario
    function updateUserName(name) {
        const userNameElement = document.getElementById('userName');
        if (userNameElement) {
            userNameElement.textContent = name;
        }
    }
    
    // Verificar autenticación al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        const user = localStorage.getItem('user');
        
        // Solo actualizar si hay datos válidos y el usuario está realmente logueado
        if (isLoggedIn === 'true' && user) {
            try {
                const userData = JSON.parse(user);
                updateUserName(userData.nombre);
            } catch (e) {
                console.error('Error parsing user data:', e);
                // Limpiar datos corruptos
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('user');
            }
        }
    });
    </script>
