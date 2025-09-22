<?php
require_once '../includes/paths.php';  // Incluir paths para constantes
require_once '../includes/config.php';

// Obtener el ID de la presentación de la URL
$presentationId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$presentationId) {
    header('Location: ' . BASE_URL . 'presentaciones/');
    exit;
}

// Obtener información de la presentación
$presentation = getPresentationById($presentationId);

if (!$presentation) {
    header('Location: ' . BASE_URL . 'presentaciones/');
    exit;
}

// Verificar si estamos accediendo a un item específico (año) o a la presentación principal
$itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : null;

if ($itemId) {
    // Accediendo a un item específico (año) - mostrar sus items hijos
    $parentItem = getPresentationItemById($itemId);
    if ($parentItem) {
        $items = getPresentationItemsByParent($itemId);
        $pageTitle = $presentation['titulo'] . " - " . $parentItem['titulo'];
    } else {
        header('Location: ' . BASE_URL . 'presentaciones/');
        exit;
    }
} else {
    // Accediendo a la presentación principal
    // Verificar si la presentación tiene estructura intermedia
    $hasIntermediateItems = getPresentationItems($presentationId, ['where' => 'es_pagina_intermedia = 1']);
    
    if (!empty($hasIntermediateItems)) {
        // Si tiene items intermedios, mostrar solo esos
        $items = $hasIntermediateItems;
    } else {
        // Si no tiene items intermedios, mostrar todos los items
        $items = getPresentationItems($presentationId);
    }
}

include '../includes/header.php';
?>

<div class="page-title">
    <h1><?php echo htmlspecialchars($presentation['titulo']); ?><?php echo isset($parentItem) ? " - " . htmlspecialchars($parentItem['titulo']) : ""; ?></h1>
    <?php if ($presentation['subtitulo']): ?>
    <h2><?php echo htmlspecialchars($presentation['subtitulo']); ?></h2>
    <?php endif; ?>
    <hr>
    <div class="breadcrumb">
        <?php if (isset($parentItem)): ?>
            <a href="presentation.php?id=<?php echo $presentationId; ?>" class="btn btn-secondary">← Volver a <?php echo htmlspecialchars($presentation['titulo']); ?></a>
        <?php else: ?>
            <a href="index.php" class="btn btn-secondary">← Volver a Montajes & Tips</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <?php if (!empty($items)): ?>
        <div class="items-grid">
            <?php foreach ($items as $item): ?>
            <div class="item-card">
                <?php 
                // Determinar el enlace correcto
                if ($item['es_pagina_intermedia']) {
                    // Si es página intermedia (año), ir a mostrar sus items hijos
                    $itemUrl = "presentation.php?id=" . $presentationId . "&item_id=" . $item['id'];
                    $target = "";
                    $rel = "";
                    $onclick = "";
                } else {
                    // Si no es intermedia, verificar si es video local o enlace externo
                    $enlace = $item['enlace'];
                    if (preg_match('/\.(mp4|avi|mov|wmv|flv|webm)$/i', $enlace)) {
                        // Es un video local - construir la ruta completa y usar JavaScript para abrir en popup
                        $videoPath = $enlace;
                        // Si la ruta no empieza con BASE_URL, añadirlo
                        if (strpos($videoPath, BASE_URL) !== 0) {
                            $videoPath = BASE_URL . ltrim($videoPath, '/');
                        }
                        $itemUrl = "javascript:void(0);";
                        $target = "";
                        $rel = "";
                        // Codificar solo los caracteres especiales pero mantener las barras
                        $encodedPath = str_replace('%2F', '/', rawurlencode($videoPath));
                        $onclick = "onclick=\"window.open('" . $encodedPath . "', 'popup', 'left=550,top=150,width=660,height=520,toolbar=no,resizable=yes,location=no,status=no,menubar=no')\"";
                    } else {
                        // Es un enlace externo
                        $itemUrl = htmlspecialchars($enlace);
                        $target = "target=\"_blank\"";
                        $rel = "rel=\"noopener noreferrer\"";
                        $onclick = "";
                    }
                }
                ?>
                <a href="<?php echo $itemUrl; ?>" 
                   class="item-link-main" 
                   <?php echo $target; ?> 
                   <?php echo $rel; ?>
                   <?php echo $onclick; ?>>
                    <div class="item-image-container">
                        <img src="<?php echo getImagePath(htmlspecialchars($item['imagen'])); ?>" 
                             alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                             class="item-image"
                             onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                    </div>
                    <div class="item-content">
                        <h4 class="item-title"><?php echo htmlspecialchars($item['titulo']); ?></h4>
                        <?php if ($item['subtitulo']): ?>
                        <p class="item-subtitle"><?php echo htmlspecialchars($item['subtitulo']); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-items">
            <h3>No hay items disponibles para esta presentación</h3>
            <p>Vuelve a la <a href="index.php">página de presentaciones</a> para ver otros contenidos.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.breadcrumb {
    margin-top: 1rem;
    text-align: center;
}

.breadcrumb .btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.breadcrumb .btn:hover {
    background: #5a6268;
    color: white;
}

.no-items {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.no-items h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.no-items p {
    color: #7f8c8d;
}

.no-items a {
    color: #667eea;
    text-decoration: none;
}

.no-items a:hover {
    text-decoration: underline;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.item-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    display: block;
}

.item-link-main {
    text-decoration: none;
    color: inherit;
    display: block;
    cursor: pointer;
}

.item-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    border: 2px solid #667eea;
}

.item-card:focus-within {
    outline: 3px solid #667eea;
    outline-offset: 2px;
}

.item-image-container {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
}

.item-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.item-card:hover .item-image {
    transform: scale(1.05);
}

.item-content {
    padding: 1.5rem;
    text-align: center;
}

.item-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.item-subtitle {
    font-size: 1rem;
    font-weight: bold;
    color: #7f8c8d;
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .items-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .item-image-container {
        height: 250px;
    }
    
    .item-title {
        font-size: 1.1rem;
    }
    
    .item-subtitle {
        font-size: 0.9rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
