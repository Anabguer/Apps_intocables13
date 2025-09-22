<?php
require_once 'includes/config.php';

$pageTitle = 'Recuperar Contraseña - Intocables';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/styles.css">
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
        
        .forgot-container {
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
        
        .forgot-title {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }
        
        .forgot-subtitle {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
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
        
        .btn {
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
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-link:hover {
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
        
        .step {
            display: none;
        }
        
        .step.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="logo">
            <img src="<?php echo getImagePath('/img/LogosBanners/Logo2conTitulo.png'); ?>" alt="Intocables">
        </div>
        
        <h1 class="forgot-title">Recuperar Contraseña</h1>
        <p class="forgot-subtitle">Introduce tu correo electrónico para recibir un código de verificación</p>
        
        <div id="alert" class="alert"></div>
        
        <!-- Paso 1: Solicitar correo -->
        <div id="step1" class="step active">
            <form id="emailForm">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="btn" id="sendBtn">
                    Enviar Código
                </button>
                
                <div class="loading" id="loading1">
                    Enviando código de verificación...
                </div>
            </form>
        </div>
        
        <!-- Paso 2: Introducir código y nueva contraseña -->
        <div id="step2" class="step">
            <form id="resetForm">
                <div class="form-group">
                    <label for="code">Código de verificación</label>
                    <input type="text" id="code" name="code" required maxlength="6">
                </div>
                
                <div class="form-group">
                    <label for="newPassword">Nueva contraseña</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirmar contraseña</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                
                <button type="submit" class="btn" id="resetBtn">
                    Cambiar Contraseña
                </button>
                
                <div class="loading" id="loading2">
                    Cambiando contraseña...
                </div>
            </form>
        </div>
        
        <a href="login.php" class="back-link">← Volver al Login</a>
    </div>

    <script>
        let currentEmail = '';
        
        // Paso 1: Enviar código por email
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const alert = document.getElementById('alert');
            const sendBtn = document.getElementById('sendBtn');
            const loading = document.getElementById('loading1');
            
            // Limpiar alertas anteriores
            alert.style.display = 'none';
            alert.className = 'alert';
            
            // Mostrar loading
            sendBtn.style.display = 'none';
            loading.style.display = 'block';
            
            // Enviar datos al servidor
            const formData = new FormData();
            formData.append('action', 'send_code');
            formData.append('email', email);
            
            fetch('api/forgot-password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                sendBtn.style.display = 'block';
                
                if (data.success) {
                    currentEmail = email;
                    // Cambiar al paso 2
                    document.getElementById('step1').classList.remove('active');
                    document.getElementById('step2').classList.add('active');
                    
                    alert.className = 'alert alert-success';
                    if (data.debug_code) {
                        if (data.smtp_not_configured) {
                            alert.innerHTML = 'Código generado: <strong>' + data.debug_code + '</strong><br><small>⚠️ SMTP no configurado. Configura el correo en Administración → Configuración para envío automático.</small>';
                        } else {
                            alert.innerHTML = 'Código enviado. <strong>Tu código es: ' + data.debug_code + '</strong>';
                        }
                    } else {
                        alert.textContent = 'Código enviado. Revisa tu correo electrónico.';
                    }
                    alert.style.display = 'block';
                } else {
                    alert.className = 'alert alert-error';
                    alert.textContent = data.message || 'Error al enviar el código';
                    alert.style.display = 'block';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                sendBtn.style.display = 'block';
                
                alert.className = 'alert alert-error';
                alert.textContent = 'Error de conexión. Inténtelo de nuevo.';
                alert.style.display = 'block';
                console.error('Error:', error);
            });
        });
        
        // Paso 2: Cambiar contraseña
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = document.getElementById('code').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const alert = document.getElementById('alert');
            const resetBtn = document.getElementById('resetBtn');
            const loading = document.getElementById('loading2');
            
            // Validar que las contraseñas coincidan
            if (newPassword !== confirmPassword) {
                alert.className = 'alert alert-error';
                alert.textContent = 'Las contraseñas no coinciden';
                alert.style.display = 'block';
                return;
            }
            
            // Limpiar alertas anteriores
            alert.style.display = 'none';
            alert.className = 'alert';
            
            // Mostrar loading
            resetBtn.style.display = 'none';
            loading.style.display = 'block';
            
            // Enviar datos al servidor
            const formData = new FormData();
            formData.append('action', 'reset_password');
            formData.append('email', currentEmail);
            formData.append('code', code);
            formData.append('newPassword', newPassword);
            
            fetch('api/forgot-password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                resetBtn.style.display = 'block';
                
                if (data.success) {
                    alert.className = 'alert alert-success';
                    alert.textContent = 'Contraseña cambiada exitosamente. Redirigiendo al login...';
                    alert.style.display = 'block';
                    
                    // Redirigir al login después de 2 segundos
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    alert.className = 'alert alert-error';
                    alert.textContent = data.message || 'Error al cambiar la contraseña';
                    alert.style.display = 'block';
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                resetBtn.style.display = 'block';
                
                alert.className = 'alert alert-error';
                alert.textContent = 'Error de conexión. Inténtelo de nuevo.';
                alert.style.display = 'block';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
