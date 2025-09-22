<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar permisos (edit o admin)
if (!isEdit() && !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['filename']) || empty($input['filename'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre de archivo requerido']);
    exit;
}

$filename = $input['filename'];

// Validar nombre de archivo (solo caracteres seguros)
if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre de archivo inválido']);
    exit;
}

// Configuración de la carpeta
$DIR = __DIR__ . '/../img/alessandro';
$filePath = $DIR . '/' . $filename;

// Verificar que el archivo existe
if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
    exit;
}

// Verificar que está en la carpeta correcta (seguridad adicional)
$realPath = realpath($filePath);
$realDir = realpath($DIR);
if (!$realPath || strpos($realPath, $realDir) !== 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Ruta de archivo no válida']);
    exit;
}

// Intentar eliminar el archivo
if (unlink($filePath)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Archivo eliminado correctamente'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar el archivo'
    ]);
}
?>
