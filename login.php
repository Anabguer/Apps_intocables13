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
        
        /* Estilos para el modal de recuperación de contraseña */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: none;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title-section h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .modal-title-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }
        
        .close-btn:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .forgot-step {
            display: none;
        }
        
        .forgot-step.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0;
                transform: translateY(-50px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
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
        
        <a href="#" class="forgot-password" onclick="openForgotPasswordModal()">
            ¿Ha olvidado su contraseña?
        </a>
    </div>

    <!-- Modal de Recuperación de Contraseña -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-section">
                    <h3>Recuperar Contraseña</h3>
                    <p>Introduce tu correo electrónico para recibir un código de verificación</p>
                </div>
                <button class="close-btn" onclick="closeForgotPasswordModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="forgotAlert" class="alert"></div>
                
                <!-- Paso 1: Solicitar correo -->
                <div id="forgotStep1" class="forgot-step active">
                    <form id="forgotEmailForm">
                        <div class="form-group">
                            <label for="forgotEmail">Correo electrónico</label>
                            <input type="email" id="forgotEmail" name="email" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="forgotSendBtn">
                                Enviar Código
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="closeForgotPasswordModal()">
                                Cancelar
                            </button>
                        </div>
                        
                        <div class="loading" id="forgotLoading1">
                            Enviando código de verificación...
                        </div>
                    </form>
                </div>
                
                <!-- Paso 2: Introducir código y nueva contraseña -->
                <div id="forgotStep2" class="forgot-step">
                    <form id="forgotResetForm">
                        <div class="form-group">
                            <label for="forgotCode">Código de verificación</label>
                            <input type="text" id="forgotCode" name="code" required maxlength="6" placeholder="123456">
                        </div>
                        
                        <div class="form-group">
                            <label for="forgotNewPassword">Nueva contraseña</label>
                            <input type="password" id="forgotNewPassword" name="newPassword" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="forgotConfirmPassword">Confirmar contraseña</label>
                            <input type="password" id="forgotConfirmPassword" name="confirmPassword" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="forgotResetBtn">
                                Cambiar Contraseña
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="backToForgotStep1()">
                                ← Volver
                            </button>
                        </div>
                        
                        <div class="loading" id="forgotLoading2">
                            Cambiando contraseña...
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
        
        // Variables globales para el modal de recuperación de contraseña
        let currentForgotEmail = '';
        
        // Función para abrir el modal de recuperación de contraseña
        function openForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'block';
            // Resetear el modal al estado inicial
            resetForgotPasswordModal();
        }
        
        // Función para cerrar el modal de recuperación de contraseña
        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'none';
            resetForgotPasswordModal();
        }
        
        // Función para resetear el modal
        function resetForgotPasswordModal() {
            currentForgotEmail = '';
            document.getElementById('forgotStep1').classList.add('active');
            document.getElementById('forgotStep2').classList.remove('active');
            document.getElementById('forgotEmailForm').reset();
            document.getElementById('forgotResetForm').reset();
            document.getElementById('forgotAlert').style.display = 'none';
            document.getElementById('forgotAlert').className = 'alert';
            document.getElementById('forgotLoading1').style.display = 'none';
            document.getElementById('forgotLoading2').style.display = 'none';
            document.getElementById('forgotSendBtn').style.display = 'block';
            document.getElementById('forgotResetBtn').style.display = 'block';
        }
        
        // Función para volver al paso 1
        function backToForgotStep1() {
            document.getElementById('forgotStep1').classList.add('active');
            document.getElementById('forgotStep2').classList.remove('active');
            document.getElementById('forgotAlert').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('forgotPasswordModal');
            if (event.target === modal) {
                closeForgotPasswordModal();
            }
        }
        
        // Paso 1: Enviar código por email
        document.getElementById('forgotEmailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('forgotEmail').value;
            const alert = document.getElementById('forgotAlert');
            const sendBtn = document.getElementById('forgotSendBtn');
            const loading = document.getElementById('forgotLoading1');
            
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
                    currentForgotEmail = email;
                    // Cambiar al paso 2
                    document.getElementById('forgotStep1').classList.remove('active');
                    document.getElementById('forgotStep2').classList.add('active');
                    
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
        document.getElementById('forgotResetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const code = document.getElementById('forgotCode').value;
            const newPassword = document.getElementById('forgotNewPassword').value;
            const confirmPassword = document.getElementById('forgotConfirmPassword').value;
            const alert = document.getElementById('forgotAlert');
            const resetBtn = document.getElementById('forgotResetBtn');
            const loading = document.getElementById('forgotLoading2');
            
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
            formData.append('email', currentForgotEmail);
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
                    
                    // Cerrar modal y mostrar mensaje en el login principal
                    setTimeout(() => {
                        closeForgotPasswordModal();
                        // Mostrar mensaje de éxito en el login principal
                        const mainAlert = document.getElementById('alert');
                        mainAlert.className = 'alert alert-success';
                        mainAlert.textContent = 'Contraseña cambiada exitosamente. Puedes iniciar sesión ahora.';
                        mainAlert.style.display = 'block';
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
