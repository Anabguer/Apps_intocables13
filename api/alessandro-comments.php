<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
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
            break;
    }
} catch (PDOException $e) {
    error_log("Error en alessandro-comments.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function handleGet($pdo) {
    // Obtener comentarios por archivo o todos
    $filename = $_GET['filename'] ?? null;
    
    if ($filename) {
        // Obtener TODOS los comentarios de un archivo (historial completo)
        $sql = "SELECT * FROM alessandro_comments WHERE filename = ? ORDER BY created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$filename]);
        $comments = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'comments' => $comments]);
    } else {
        // Obtener todos los comentarios de todos los archivos
        $sql = "SELECT * FROM alessandro_comments ORDER BY filename, created_at ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $comments = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'comments' => $comments]);
    }
}

function handlePost($pdo) {
    // Crear nuevo comentario (siempre crear, no actualizar)
    $input = json_decode(file_get_contents('php://input'), true);
    
    $filename = $input['filename'] ?? '';
    $comment = $input['comment'] ?? '';
    $usuario = getCurrentUser()['email'] ?? 'admin@intocables.com';
    $nombre_usuario = getCurrentUser()['nombre'] ?? 'Usuario';
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'message' => 'Nombre de archivo requerido']);
        return;
    }
    
    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Comentario requerido']);
        return;
    }
    
    // Siempre crear un nuevo comentario (historial completo)
    $sql = "INSERT INTO alessandro_comments (filename, comment, usuario, nombre_usuario) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$filename, $comment, $usuario, $nombre_usuario]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Comentario guardado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar comentario']);
    }
}

function handlePut($pdo) {
    // Actualizar comentario específico
    $input = json_decode(file_get_contents('php://input'), true);
    
    $id = $input['id'] ?? null;
    $comment = $input['comment'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID del comentario requerido']);
        return;
    }
    
    $sql = "UPDATE alessandro_comments SET comment = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$comment, $id]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Comentario actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar comentario']);
    }
}

function handleDelete($pdo) {
    // Eliminar comentario
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID del comentario requerido']);
        return;
    }
    
    $sql = "DELETE FROM alessandro_comments WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$id]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Comentario eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar comentario']);
    }
}
?>
