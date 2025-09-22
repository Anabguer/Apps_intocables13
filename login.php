<?php
require_once 'includes/paths.php';
require_once 'includes/config.php';

$pageTitle = 'Login - Intocables';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>styles.css?v=<?php echo filemtime(__DIR__ . '/css/styles.css'); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 2rem;
        }
        
        .logo img {
            height: 80px;
            width: auto;
        }
        
        .login-title {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 2rem;
            letter-spacing: 1px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: none;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }
        
        .loading {
            display: none;
            color: #667eea;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="<?php echo getImagePath('/img/LogosBanners/Logo2conTitulo.png'); ?>?v=<?php echo time(); ?>" alt="Intocables" onerror="console.log('Error cargando logo:', this.src);">
        </div>
        
        <h1 class="login-title">Identificación</h1>
        
        <div id="alert" class="alert"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="login-btn" id="loginBtn">
                Iniciar Sesión
            </button>
            
            <div class="loading" id="loading">
                Verificando credenciales...
            </div>
        </form>
        
        <a href="forgot-password.php" class="forgot-password">
            ¿Ha olvidado su contraseña?
        </a>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const alert = document.getElementById('alert');
            const loginBtn = document.getElementById('loginBtn');
            const loading = document.getElementById('loading');
            
            // Limpiar alertas anteriores
            alert.style.display = 'none';
            alert.className = 'alert';
            
            // Mostrar loading
            loginBtn.style.display = 'none';
            loading.style.display = 'block';
            
            // Enviar datos al servidor
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                loginBtn.style.display = 'block';
                
                if (data.success) {
                    // Login exitoso
                    alert.className = 'alert alert-success';
                    alert.textContent = 'Login exitoso. Redirigiendo...';
                    alert.style.display = 'block';
                    
                    // Guardar datos en localStorage
                    localStorage.setItem('user', JSON.stringify(data.user));
                    localStorage.setItem('isLoggedIn', 'true');
                    
                    // Redirigir después de 1 segundo
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    // Error en el login
                    alert.className = 'alert alert-error';
                    alert.textContent = data.message || 'Error en el login';
                    alert.style.display = 'block';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                loginBtn.style.display = 'block';
                
                alert.className = 'alert alert-error';
                alert.textContent = 'Error de conexión. Inténtelo de nuevo.';
                alert.style.display = 'block';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
