<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo admin puede gestionar usuarios)
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
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    error_log("Error en users.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function handleGet($pdo) {
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // Obtener un usuario específico
        $sql = "SELECT id, nombre, apellido_1, apellido_2, correo, perfil, sexo, activo, mensaje_bienvenida FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    } else {
        // Obtener todos los usuarios
        $sql = "SELECT id, nombre, apellido_1, apellido_2, correo, perfil, sexo, activo, mensaje_bienvenida FROM usuarios ORDER BY nombre";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'users' => $users]);
    }
}

function handlePost($pdo) {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    // Validar datos requeridos
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido1 = trim($_POST['apellido_1'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $perfil = $_POST['perfil'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $activo = $_POST['activo'] ?? '1';
    $mensaje = trim($_POST['mensaje_bienvenida'] ?? '');
    
    if (empty($nombre) || empty($apellido1) || empty($correo) || empty($perfil) || empty($sexo)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
        return;
    }
    
    // Validar email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email no válido']);
        return;
    }
    
    // Validar perfil
    if (!in_array($perfil, ['admin', 'user', 'edit'])) {
        echo json_encode(['success' => false, 'message' => 'Perfil no válido']);
        return;
    }
    
    // Validar sexo
    if (!in_array($sexo, ['H', 'M'])) {
        echo json_encode(['success' => false, 'message' => 'Sexo no válido']);
        return;
    }
    
    if ($action === 'create') {
        // Crear nuevo usuario
        $clave = $_POST['clave'] ?? '';
        if (empty($clave) || strlen($clave) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }
        
        // Verificar si el email ya existe
        $sql = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un usuario con este email']);
            return;
        }
        
        // Crear usuario
        $hashedPassword = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, apellido_1, apellido_2, correo, clave, perfil, sexo, activo, mensaje_bienvenida) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$nombre, $apellido1, $_POST['apellido_2'] ?? '', $correo, $hashedPassword, $perfil, $sexo, $activo, $mensaje]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario']);
        }
        
    } elseif ($action === 'update') {
        // Actualizar usuario existente
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
            return;
        }
        
        // Verificar si el email ya existe en otro usuario
        $sql = "SELECT id FROM usuarios WHERE correo = ? AND id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$correo, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ya existe otro usuario con este email']);
            return;
        }
        
        // Actualizar usuario
        $clave = $_POST['clave'] ?? '';
        if (!empty($clave)) {
            // Si se proporciona nueva contraseña
            if (strlen($clave) < 6) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
                return;
            }
            $hashedPassword = password_hash($clave, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre = ?, apellido_1 = ?, apellido_2 = ?, correo = ?, clave = ?, perfil = ?, sexo = ?, activo = ?, mensaje_bienvenida = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$nombre, $apellido1, $_POST['apellido_2'] ?? '', $correo, $hashedPassword, $perfil, $sexo, $activo, $mensaje, $id]);
        } else {
            // Sin cambiar contraseña
            $sql = "UPDATE usuarios SET nombre = ?, apellido_1 = ?, apellido_2 = ?, correo = ?, perfil = ?, sexo = ?, activo = ?, mensaje_bienvenida = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$nombre, $apellido1, $_POST['apellido_2'] ?? '', $correo, $perfil, $sexo, $activo, $mensaje, $id]);
        }
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}

function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';
    
    
    // Validar datos requeridos
    $nombre = trim($input['nombre'] ?? '');
    $apellido1 = trim($input['apellido_1'] ?? '');
    $correo = trim($input['correo'] ?? '');
    $perfil = $input['perfil'] ?? '';
    $sexo = $input['sexo'] ?? '';
    $activo = $input['activo'] ?? '1';
    $mensaje = trim($input['mensaje_bienvenida'] ?? '');
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
        return;
    }
    
    if (empty($nombre) || empty($apellido1) || empty($correo) || empty($perfil) || empty($sexo)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
        return;
    }
    
    // Validar email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email no válido']);
        return;
    }
    
    // Validar perfil
    if (!in_array($perfil, ['admin', 'user', 'edit'])) {
        echo json_encode(['success' => false, 'message' => 'Perfil no válido']);
        return;
    }
    
    // Validar sexo
    if (!in_array($sexo, ['H', 'M'])) {
        echo json_encode(['success' => false, 'message' => 'Sexo no válido']);
        return;
    }
    
    // Verificar si el email ya existe en otro usuario
    $sql = "SELECT id FROM usuarios WHERE correo = ? AND id != ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$correo, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otro usuario con este email']);
        return;
    }
    
    // Actualizar usuario
    $clave = $input['clave'] ?? '';
    if (!empty($clave)) {
        // Si se proporciona nueva contraseña
        if (strlen($clave) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }
        $hashedPassword = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre = ?, apellido_1 = ?, apellido_2 = ?, correo = ?, clave = ?, perfil = ?, sexo = ?, activo = ?, mensaje_bienvenida = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$nombre, $apellido1, $input['apellido_2'] ?? '', $correo, $hashedPassword, $perfil, $sexo, $activo, $mensaje, $id]);
    } else {
        // Sin cambiar contraseña
        $sql = "UPDATE usuarios SET nombre = ?, apellido_1 = ?, apellido_2 = ?, correo = ?, perfil = ?, sexo = ?, activo = ?, mensaje_bienvenida = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$nombre, $apellido1, $input['apellido_2'] ?? '', $correo, $perfil, $sexo, $activo, $mensaje, $id]);
    }
    
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario']);
    }
}

function handleDelete($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
        return;
    }
    
    // Verificar que no se elimine el último admin
    $sql = "SELECT COUNT(*) as count FROM usuarios WHERE perfil = 'admin' AND activo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $adminCount = $stmt->fetch()['count'];
    
    if ($adminCount <= 1) {
        // Verificar si el usuario a eliminar es admin
        $sql = "SELECT perfil FROM usuarios WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user && $user['perfil'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar el último administrador activo']);
            return;
        }
    }
    
    // Eliminar usuario
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario']);
    }
}
?>
