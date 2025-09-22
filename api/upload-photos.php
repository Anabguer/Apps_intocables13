<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Verificar que el usuario esté logueado y tenga permisos de editor o admin
if (!isLoggedIn() || (!isEdit() && !isAdmin())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    exit;
}

$uploadType = $_POST['upload_type'] ?? 'general';
$file = $_FILES['photo'];

// Validar tipo de archivo (fotos y videos)
$allowedTypes = [
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
    'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'
];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten fotos (JPG, PNG, GIF, WEBP) y videos (MP4, AVI, MOV, WMV, WEBM)']);
    exit;
}

// Validar tamaño (máximo 50MB para videos, 10MB para fotos)
$isVideo = strpos($file['type'], 'video/') === 0;
$maxSize = $isVideo ? (50 * 1024 * 1024) : (10 * 1024 * 1024); // 50MB para videos, 10MB para fotos
if ($file['size'] > $maxSize) {
    $maxSizeMB = $isVideo ? '50MB' : '10MB';
    echo json_encode(['success' => false, 'message' => "El archivo es demasiado grande (máximo {$maxSizeMB})"]);
    exit;
}

try {
    // Crear directorio de destino según el tipo
    $uploadDir = '../img/';
    if ($uploadType === 'alessandro') {
        $uploadDir .= 'alessandro/';
    } else {
        $uploadDir .= 'uploads/';
    }
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nombre único para el archivo
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // Mover archivo subido
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Generar URL pública
        $publicPath = BASE_URL . 'img/' . ($uploadType === 'alessandro' ? 'alessandro/' : 'uploads/') . $fileName;
        
        echo json_encode([
            'success' => true,
            'message' => 'Archivo subido correctamente',
            'file_path' => $publicPath,
            'file_name' => $fileName,
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'is_video' => $isVideo
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo']);
    }
    
} catch (Exception $e) {
    error_log("Error en upload-photos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
