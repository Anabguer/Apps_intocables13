<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/paths.php';
require_once '../includes/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            // Verificar si es una eliminación o actualización disfrazada de POST
            if (isset($input['_method']) && $input['_method'] === 'DELETE') {
                handleDelete($input);
            } elseif (isset($input['_method']) && $input['_method'] === 'PUT') {
                handlePut($input);
            } else {
                handlePost($input);
            }
            break;
        case 'PUT':
            handlePut($input);
            break;
        case 'DELETE':
            handleDelete($input);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en albums.php: " . $e->getMessage() . " - Línea: " . $e->getLine() . " - Archivo: " . $e->getFile());
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'details' => $e->getTraceAsString()]);
}

function handleGet() {
    if (isset($_GET['id'])) {
        // Obtener un álbum específico
        $id = (int)$_GET['id'];
        $album = getAlbumById($id);
        
        if ($album) {
            echo json_encode(['success' => true, 'data' => $album]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Álbum no encontrado']);
        }
    } else {
        // Obtener todos los álbumes
        $filters = [];
        
        if (isset($_GET['activo'])) {
            $filters['activo'] = (int)$_GET['activo'];
        }
        
        if (isset($_GET['año'])) {
            $filters['año'] = (int)$_GET['año'];
        }
        
        if (isset($_GET['tipo'])) {
            $filters['tipo'] = $_GET['tipo'];
        }
        
        $albums = getAllAlbums($filters);
        echo json_encode(['success' => true, 'data' => $albums]);
    }
}

function handlePost($input) {
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos no válidos']);
        return;
    }
    
    // Validar datos requeridos
    $required = ['titulo', 'imagen', 'enlace', 'tipo'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Campo requerido: $field"]);
            return;
        }
    }
    
    $data = [
        'titulo' => $input['titulo'],
        'subtitulo' => $input['subtitulo'] ?? '',
        'año' => $input['año'] ?? null,
        'imagen' => $input['imagen'],
        'enlace' => $input['enlace'],
        'video' => $input['video'] ?? null,
        'es_pagina_intermedia' => (int)($input['es_pagina_intermedia'] ?? 0),
        'album_padre_id' => ($input['album_padre_id'] ?? null) ?: null,
        'tipo' => $input['tipo'],
        'categoria' => $input['categoria'] ?? 'general',
        'orden' => (int)($input['orden'] ?? 1),
        'activo' => (int)($input['activo'] ?? 1)
    ];
    
    if (createAlbum($data)) {
        echo json_encode(['success' => true, 'message' => 'Álbum creado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al crear el álbum']);
    }
}

function handlePut($input) {
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de álbum requerido']);
        return;
    }
    
    $id = (int)$input['id'];
    
    // Verificar que el álbum existe
    $album = getAlbumById($id);
    if (!$album) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Álbum no encontrado']);
        return;
    }
    
    $data = [
        'titulo' => $input['titulo'] ?? $album['titulo'],
        'subtitulo' => $input['subtitulo'] ?? $album['subtitulo'],
        'año' => $input['año'] ?? $album['año'],
        'imagen' => $input['imagen'] ?? $album['imagen'],
        'enlace' => $input['enlace'] ?? $album['enlace'],
        'video' => $input['video'] ?? $album['video'],
        'es_pagina_intermedia' => (int)($input['es_pagina_intermedia'] ?? $album['es_pagina_intermedia']),
        'album_padre_id' => ($input['album_padre_id'] ?? $album['album_padre_id']) ?: null,
        'tipo' => $input['tipo'] ?? $album['tipo'],
        'categoria' => $input['categoria'] ?? $album['categoria'],
        'orden' => (int)($input['orden'] ?? $album['orden']),
        'activo' => (int)($input['activo'] ?? $album['activo'])
    ];
    
    if (updateAlbum($id, $data)) {
        echo json_encode(['success' => true, 'message' => 'Álbum actualizado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al actualizar el álbum']);
    }
}

function handleDelete($input) {
    if (!$input || !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de álbum requerido']);
        return;
    }
    
    $id = (int)$input['id'];
    
    // Verificar que el álbum existe
    $album = getAlbumById($id);
    if (!$album) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Álbum no encontrado']);
        return;
    }
    
    if (deleteAlbum($id)) {
        echo json_encode(['success' => true, 'message' => 'Álbum eliminado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al eliminar el álbum']);
    }
}
?>
