<?php
// ==================== CONFIGURACIÓN DE RUTAS ====================
// Incluir configuración de rutas
require_once __DIR__ . '/paths.php';

// ==================== CONFIGURACIÓN DE BASE DE DATOS ====================
// Incluir configuración de base de datos (NO MODIFICABLE DESDE ADMIN)
require_once __DIR__ . '/database.php';

// ==================== CONFIGURACIÓN DEL SISTEMA ====================
// ESTOS VALORES SÍ PUEDEN SER MODIFICADOS DESDE EL PANEL DE ADMINISTRACIÓN

// Configuración General
define('SITE_NAME', 'INTOCABLES');

// Detectar entorno para SITE_URL
$isLocalhost = ($_SERVER['HTTP_HOST'] === 'localhost' || 
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
                strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

if ($isLocalhost) {
    define('SITE_URL', 'http://localhost/intocables');
} else {
    // Construir URL automáticamente para Hostalia
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    define('SITE_URL', $protocol . '://' . $_SERVER['HTTP_HOST']);
}

define('ADMIN_EMAIL', '1954amg@gmail.com');
define('TIMEZONE', 'Europe/Madrid');

// Configuración de Archivos
define('MAX_FILE_SIZE', 10); // MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,webp,mp4,mov,webm');
define('THUMBNAIL_SIZE', 200); // px
define('IMAGE_QUALITY', 85); // %

// Configuración de Seguridad
define('SESSION_TIMEOUT', 120); // minutos
define('MAX_LOGIN_ATTEMPTS', 5);
define('PASSWORD_MIN_LENGTH', 6);
define('REQUIRE_STRONG_PASSWORDS', 0);

// Configuración de Notificaciones
define('EMAIL_NOTIFICATIONS', 1);
// Configuración SMTP para Hostalia
define('SMTP_SERVER', 'smtp.colisan.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'info@colisan.com');
define('SMTP_PASSWORD', 'IgdAmg19521954');
define('FROM_EMAIL', 'noreply@intocables13.com');
define('FROM_NAME', 'INTOCABLES');

// Configuración de Galería
define('ITEMS_PER_PAGE', 20);
define('GALLERY_LAYOUT', 'grid');
define('ENABLE_ZOOM', 1);
define('AUTO_PLAY_VIDEOS', 1);

// Función para obtener la ruta base
function getBasePath() {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    return $currentDir == '/' ? '' : '..';
}

// Función para obtener la ruta de una imagen
function getImagePath($imagePath) {
    // Si la ruta empieza con /img/, hacerla absoluta desde la raíz del sitio
    if (strpos($imagePath, '/img/') === 0) {
        // Asegurar que BASE_URL termine con / y que imagePath no empiece con /
        $baseUrl = rtrim(BASE_URL, '/') . '/';
        return $baseUrl . ltrim($imagePath, '/');
    }
    // Si es un enlace de año (ej: /fotos-videos/2024), convertirlo a year.php
    if (preg_match('/^\/fotos-videos\/(\d{4})$/', $imagePath, $matches)) {
        return BASE_URL . 'fotos-videos/year.php?year=' . $matches[1];
    }
    // Si ya es relativa, usarla tal como está
    return $imagePath;
}

// Función para obtener la URL de un álbum (maneja páginas intermedias)
function getAlbumUrl($album) {
    // Si es una página intermedia, usar intermediate.php
    if ($album['es_pagina_intermedia']) {
        return BASE_URL . 'fotos-videos/intermediate.php?album_id=' . $album['id'];
    }
    // Si no, usar el enlace normal
    return $album['enlace'];
}


// Función para obtener todos los álbumes
function getAllAlbums($filters = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM albums WHERE 1=1";
    $params = [];
    
    if (isset($filters['activo'])) {
        $sql .= " AND activo = ?";
        $params[] = $filters['activo'];
    }
    
    if (isset($filters['año'])) {
        $sql .= " AND año = ?";
        $params[] = $filters['año'];
    }
    
    if (isset($filters['tipo'])) {
        $sql .= " AND tipo = ?";
        $params[] = $filters['tipo'];
    }
    
    // Por defecto, excluir álbumes hijos (solo mostrar álbumes principales)
    if (!isset($filters['include_children']) || !$filters['include_children']) {
        $sql .= " AND (album_padre_id IS NULL OR album_padre_id = 0)";
    }
    
    $sql .= " ORDER BY CASE WHEN año IS NULL THEN 1 ELSE 0 END, año DESC, orden ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener años disponibles
function getAvailableYears() {
    $pdo = getDBConnection();
    
    $sql = "SELECT titulo, enlace, imagen FROM albums 
            WHERE año IS NULL 
            AND titulo REGEXP '^[0-9]{4}$'
            AND enlace LIKE '/fotos-videos/%'
            ORDER BY titulo DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Función para obtener álbumes por año
function getAlbumsByYear($year) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM albums 
            WHERE año = ? 
            AND activo = 1 
            AND (album_padre_id IS NULL OR album_padre_id = 0)
            ORDER BY orden ASC, titulo ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    return $stmt->fetchAll();
}

// Función para obtener álbumes hijos de una página intermedia
function getAlbumsByParent($parentId) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM albums 
            WHERE album_padre_id = ? 
            AND album_padre_id > 0
            AND activo = 1 
            ORDER BY orden ASC, titulo ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

// Función para obtener información de una página intermedia
function getIntermediatePageInfo($albumId) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM albums 
            WHERE id = ? 
            AND es_pagina_intermedia = 1 
            AND activo = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$albumId]);
    return $stmt->fetch();
}

// Función para obtener un álbum por ID
function getAlbumById($id) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM albums WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para crear un álbum
function createAlbum($data) {
    $pdo = getDBConnection();
    
    $sql = "INSERT INTO albums (titulo, subtitulo, año, imagen, enlace, video, es_pagina_intermedia, album_padre_id, tipo, categoria, orden, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['subtitulo'],
        $data['año'],
        $data['imagen'],
        $data['enlace'],
        $data['video'] ?? null,
        $data['es_pagina_intermedia'] ?? 0,
        $data['album_padre_id'] ?? null,
        $data['tipo'],
        $data['categoria'],
        $data['orden'],
        $data['activo']
    ]);
}

// Función para actualizar un álbum
function updateAlbum($id, $data) {
    $pdo = getDBConnection();
    
    $sql = "UPDATE albums SET titulo=?, subtitulo=?, año=?, imagen=?, enlace=?, video=?, es_pagina_intermedia=?, album_padre_id=?, tipo=?, categoria=?, orden=?, activo=? WHERE id=?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['subtitulo'],
        $data['año'],
        $data['imagen'],
        $data['enlace'],
        $data['video'] ?? null,
        $data['es_pagina_intermedia'] ?? 0,
        $data['album_padre_id'] ?? null,
        $data['tipo'],
        $data['categoria'],
        $data['orden'],
        $data['activo'],
        $id
    ]);
}

// Función para eliminar un álbum
function deleteAlbum($id) {
    $pdo = getDBConnection();
    
    $sql = "DELETE FROM albums WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

// Función para obtener estadísticas
function getAlbumStats() {
    $pdo = getDBConnection();
    
    $stats = [];
    
    // Total de álbumes
    $sql = "SELECT COUNT(*) as total FROM albums WHERE activo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats['total'] = $stmt->fetch()['total'];
    
    // Por tipo
    $sql = "SELECT tipo, COUNT(*) as count FROM albums WHERE activo = 1 GROUP BY tipo";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats['por_tipo'] = $stmt->fetchAll();
    
    // Por año
    $sql = "SELECT año, COUNT(*) as count FROM albums WHERE activo = 1 AND año IS NOT NULL GROUP BY año ORDER BY año DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats['por_año'] = $stmt->fetchAll();
    
    return $stats;
}

// ==================== FUNCIONES PARA PRESENTACIONES ====================

// Función para obtener todas las presentaciones activas
function getPresentations($options = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM presentations WHERE activo = 1";
    $params = [];
    
    if (isset($options['where'])) {
        $sql .= " AND " . $options['where'];
    }
    
    if (isset($options['order_by'])) {
        $sql .= " ORDER BY " . $options['order_by'];
    } else {
        $sql .= " ORDER BY orden ASC, titulo ASC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener una presentación por ID
function getPresentationById($id) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM presentations WHERE id = ? AND activo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para obtener los items de una presentación
function getPresentationItems($presentationId, $options = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM presentation_items WHERE presentation_id = ? AND activo = 1";
    $params = [$presentationId];
    
    // Añadir condiciones WHERE adicionales
    if (isset($options['where'])) {
        $sql .= " AND " . $options['where'];
    }
    
    if (isset($options['order_by'])) {
        $sql .= " ORDER BY " . $options['order_by'];
    } else {
        $sql .= " ORDER BY orden ASC, titulo ASC";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Función para obtener un item específico por ID
function getPresentationItemById($itemId) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM presentation_items WHERE id = ? AND activo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$itemId]);
    return $stmt->fetch();
}

// Función para obtener items hijos de un item padre
function getPresentationItemsByParent($parentId) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM presentation_items WHERE padre_id = ? AND activo = 1 ORDER BY orden ASC, titulo ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
}

// Función para crear una nueva presentación
function createPresentation($data) {
    $pdo = getDBConnection();
    
    $sql = "INSERT INTO presentations (titulo, subtitulo, imagen, enlace, descripcion, orden, activo, es_pagina_intermedia) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['subtitulo'] ?? null,
        $data['imagen'],
        $data['enlace'] ?? null,
        $data['descripcion'] ?? null,
        $data['orden'] ?? 0,
        $data['activo'] ?? 1,
        $data['es_pagina_intermedia'] ?? false
    ]);
}

// Función para actualizar una presentación
function updatePresentation($id, $data) {
    $pdo = getDBConnection();
    
    $sql = "UPDATE presentations SET 
            titulo = ?, 
            subtitulo = ?, 
            imagen = ?, 
            enlace = ?, 
            descripcion = ?, 
            orden = ?, 
            activo = ?,
            es_pagina_intermedia = ?,
            fecha_modificacion = CURRENT_TIMESTAMP
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['subtitulo'] ?? null,
        $data['imagen'],
        $data['enlace'] ?? null,
        $data['descripcion'] ?? null,
        $data['orden'] ?? 0,
        $data['activo'] ?? 1,
        $data['es_pagina_intermedia'] ?? false,
        $id
    ]);
}

// Función para crear un nuevo item de presentación
function createPresentationItem($data) {
    $pdo = getDBConnection();
    
    $sql = "INSERT INTO presentation_items (presentation_id, titulo, subtitulo, imagen, enlace, orden, activo, es_pagina_intermedia, padre_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['presentation_id'],
        $data['titulo'],
        $data['subtitulo'] ?? null,
        $data['imagen'],
        $data['enlace'],
        $data['orden'] ?? 0,
        $data['activo'] ?? 1,
        $data['es_pagina_intermedia'] ?? false,
        $data['padre_id'] ?? null
    ]);
}

// Función para actualizar un item de presentación
function updatePresentationItem($id, $data) {
    $pdo = getDBConnection();
    
    $sql = "UPDATE presentation_items SET 
            titulo = ?, 
            subtitulo = ?, 
            imagen = ?, 
            enlace = ?, 
            orden = ?, 
            activo = ?,
            es_pagina_intermedia = ?,
            padre_id = ?,
            fecha_modificacion = CURRENT_TIMESTAMP
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['subtitulo'] ?? null,
        $data['imagen'],
        $data['enlace'],
        $data['orden'] ?? 0,
        $data['activo'] ?? 1,
        $data['es_pagina_intermedia'] ?? false,
        $data['padre_id'] ?? null,
        $id
    ]);
}

// Función para eliminar una presentación
function deletePresentation($id) {
    $pdo = getDBConnection();
    
    $sql = "DELETE FROM presentations WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

// Función para eliminar un item de presentación
function deletePresentationItem($id) {
    $pdo = getDBConnection();
    
    $sql = "DELETE FROM presentation_items WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

// Función para obtener presentaciones por enlace
function getPresentationsByLink($link) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM presentations WHERE enlace = ? AND activo = 1 ORDER BY orden ASC, titulo ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$link]);
    return $stmt->fetchAll();
}

// ==================== FUNCIONES PARA ELEMENTOS DE PÁGINA PRINCIPAL ====================

// Función para obtener todos los elementos de la página principal
function getHomepageElements() {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM homepage_elements WHERE activo = 1 ORDER BY orden ASC, id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}


// Función para obtener un elemento por ID
function getHomepageElementById($id) {
    $pdo = getDBConnection();
    
    $sql = "SELECT * FROM homepage_elements WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Función para crear un nuevo elemento
function createHomepageElement($data) {
    $pdo = getDBConnection();
    
    $sql = "INSERT INTO homepage_elements (titulo, descripcion, imagen, enlace, orden, activo) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['descripcion'],
        $data['imagen'],
        $data['enlace'],
        $data['orden'] ?? 0,
        $data['activo'] ?? 1
    ]);
}

// Función para actualizar un elemento
function updateHomepageElement($id, $data) {
    $pdo = getDBConnection();
    
    $sql = "UPDATE homepage_elements SET 
            titulo = ?, 
            descripcion = ?, 
            imagen = ?, 
            enlace = ?, 
            orden = ?, 
            activo = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['titulo'],
        $data['descripcion'],
        $data['imagen'],
        $data['enlace'],
        $data['orden'] ?? 0,
        $data['activo'] ?? 1,
        $id
    ]);
}

// Función para eliminar un elemento
function deleteHomepageElement($id) {
    $pdo = getDBConnection();
    
    $sql = "DELETE FROM homepage_elements WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

// Función para obtener el enlace de recetas desde la base de datos
function getRecipesLink() {
    $pdo = getDBConnection();
    
    // Buscar el elemento de recetas por título o enlace
    $sql = "SELECT enlace FROM homepage_elements 
            WHERE (LOWER(titulo) LIKE '%recetas%' OR enlace LIKE '%recetas%') 
            AND activo = 1 
            ORDER BY orden ASC 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    
    // Si no se encuentra, devolver enlace por defecto
    return $result ? $result['enlace'] : 'https://recetas.intocables13.com/';
}
?>
