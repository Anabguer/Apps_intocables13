<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email y contraseña son requeridos']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Buscar usuario por correo
    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Verificar contraseña
    if (!password_verify($password, $user['clave'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        exit;
    }
    
    // Login exitoso
    $userData = [
        'id' => $user['id'],
        'email' => $user['correo'],
        'nombre' => $user['nombre'],
        'apellido_1' => $user['apellido_1'],
        'apellido_2' => $user['apellido_2'],
        'perfil' => $user['perfil'],
        'sexo' => $user['sexo'] ?? 'H', // Incluir sexo, por defecto 'H'
        'mensaje_bienvenida' => $user['mensaje_bienvenida']
    ];
    
    // Iniciar sesión
    require_once '../includes/auth.php';
    loginUser($userData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso',
        'user' => $userData
    ]);
    
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
