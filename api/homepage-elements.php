<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo admin puede gestionar elementos de página principal)
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
    error_log("Error en homepage-elements.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function handleGet($pdo) {
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // Obtener un elemento específico
        $element = getHomepageElementById($id);
        if ($element) {
            echo json_encode(['success' => true, 'element' => $element]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Elemento no encontrado']);
        }
    } else {
        // Obtener todos los elementos
        $elements = getHomepageElements();
        echo json_encode(['success' => true, 'elements' => $elements]);
    }
}

function handlePost($pdo) {
    // Crear nuevo elemento
    
    $data = [
        'titulo' => $_POST['titulo'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'imagen' => $_POST['imagen'] ?? '',
        'enlace' => $_POST['enlace'] ?? '',
        'orden' => (int)($_POST['orden'] ?? 0),
        'activo' => (int)($_POST['activo'] ?? 1)
    ];
    
    // Validar datos
    if (empty($data['titulo']) || empty($data['imagen']) || empty($data['enlace'])) {
        echo json_encode(['success' => false, 'message' => 'Título, imagen y enlace son obligatorios']);
        return;
    }
    
    if (createHomepageElement($data)) {
        echo json_encode(['success' => true, 'message' => 'Elemento creado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el elemento']);
    }
}

function handlePut($pdo) {
    // Actualizar elemento existente
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    
    
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID del elemento requerido']);
        return;
    }
    
    $data = [
        'titulo' => $input['titulo'] ?? '',
        'descripcion' => $input['descripcion'] ?? '',
        'imagen' => $input['imagen'] ?? '',
        'enlace' => $input['enlace'] ?? '',
        'orden' => (int)($input['orden'] ?? 0),
        'activo' => (int)($input['activo'] ?? 1)
    ];
    
    // Validar datos
    if (empty($data['titulo']) || empty($data['imagen']) || empty($data['enlace'])) {
        echo json_encode(['success' => false, 'message' => 'Título, imagen y enlace son obligatorios']);
        return;
    }
    
    if (updateHomepageElement($id, $data)) {
        echo json_encode(['success' => true, 'message' => 'Elemento actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el elemento']);
    }
}

function handleDelete($pdo) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID del elemento requerido']);
        return;
    }
    
    if (deleteHomepageElement($id)) {
        echo json_encode(['success' => true, 'message' => 'Elemento eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el elemento']);
    }
}
?>
