<?php
require_once '../includes/config.php';

// Obtener el ID del álbum padre de la URL
$albumId = isset($_GET['album_id']) ? (int)$_GET['album_id'] : null;

if (!$albumId) {
    header('Location: ' . BASE_URL . 'fotos-videos/');
    exit;
}

// Obtener información de la página intermedia
$parentAlbum = getIntermediatePageInfo($albumId);

if (!$parentAlbum) {
    header('Location: ' . BASE_URL . 'fotos-videos/');
    exit;
}

// Obtener álbumes hijos
$childAlbums = getAlbumsByParent($albumId);

$pageTitle = $parentAlbum['titulo'] . " - Fotos y Videos";
include '../includes/header.php';
?>

<div class="page-title">
    <h1><?php echo htmlspecialchars($parentAlbum['titulo']); ?></h1>
    <?php if ($parentAlbum['subtitulo']): ?>
    <p class="page-subtitle"><?php echo htmlspecialchars($parentAlbum['subtitulo']); ?></p>
    <?php endif; ?>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>fotos-videos/" class="btn btn-secondary">← Volver a Fotos y Videos</a>
        <?php if ($parentAlbum['año']): ?>
        <a href="<?php echo BASE_URL; ?>fotos-videos/year.php?year=<?php echo $parentAlbum['año']; ?>" class="btn btn-secondary">← Volver a <?php echo $parentAlbum['año']; ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <?php if (!empty($childAlbums)): ?>
        <div class="albums-grid">
            <?php foreach ($childAlbums as $album): ?>
            <div class="album-card">
                <a href="<?php echo htmlspecialchars($album['enlace']); ?>" 
                   class="album-link-main" 
                   target="_blank" 
                   rel="noopener noreferrer">
                    <div class="album-image-container">
                        <img src="<?php echo getImagePath(htmlspecialchars($album['imagen'])); ?>" 
                             alt="<?php echo htmlspecialchars($album['titulo']); ?>"
                             class="album-image"
                             onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                        <?php if (!empty($album['video'])): ?>
                        <div class="video-overlay">
                            <a href="<?php echo htmlspecialchars($album['video']); ?>" 
                               class="video-icon" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               title="Ver Video"
                               onclick="event.stopPropagation();">
                                📹
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="album-content">
                        <h4 class="album-title"><?php echo htmlspecialchars($album['titulo']); ?></h4>
                        <?php if ($album['subtitulo']): ?>
                        <p class="album-subtitle"><?php echo htmlspecialchars($album['subtitulo']); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-albums">
            <h3>No hay álbumes disponibles en esta sección</h3>
            <p>Vuelve a la <a href="<?php echo BASE_URL; ?>fotos-videos/">página principal</a> para ver otros álbumes.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>