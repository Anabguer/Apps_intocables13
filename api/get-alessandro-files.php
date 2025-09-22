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

// Configuración de la carpeta
$DIR = __DIR__ . '/../img/alessandro';
$URL = '../img/alessandro';
$ALLOWED_EXT = ['jpg','jpeg','png','gif','webp','mp4','mov','webm','mkv'];

$files = [];

if (is_dir($DIR)) {
    $dh = opendir($DIR);
    while (($file = readdir($dh)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXT)) continue;
        $path = $DIR . '/' . $file;
        if (!is_file($path)) continue;
        
        $mtime = filemtime($path) ?: 0;
        $isVideo = in_array($ext, ['mp4','mov','webm','mkv']);
        
        // Intentar obtener fecha de captura (EXIF para imágenes)
        $captureDate = $mtime;
        if (!$isVideo && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            if ($exif && isset($exif['DateTimeOriginal'])) {
                $captureDate = strtotime($exif['DateTimeOriginal']);
            } elseif ($exif && isset($exif['DateTime'])) {
                $captureDate = strtotime($exif['DateTime']);
            }
        }
        
        $files[] = [
            'name' => $file,
            'url' => $URL . '/' . rawurlencode($file),
            'kind' => $isVideo ? 'video' : 'image',
            'size' => filesize($path),
            'date' => date('Y-m-d H:i:s', $captureDate),
            'mtime' => $mtime
        ];
    }
    closedir($dh);
}

// Ordenar por fecha de captura (más recientes primero)
usort($files, fn($a,$b)=> strtotime($b['date']) <=> strtotime($a['date']));

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'files' => $files,
    'count' => count($files)
]);
?>
