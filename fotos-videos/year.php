<?php
require_once '../includes/config.php';

// Obtener el año de la URL
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;

if (!$year) {
    header('Location: ' . BASE_URL . 'fotos-videos/');
    exit;
}

// Obtener álbumes del año
$albums = getAlbumsByYear($year);

$pageTitle = "Fotos y Videos - $year";
include '../includes/header.php';
?>

<div class="page-title">
    <h1>FOTOS Y VIDEOS - <?php echo $year; ?></h1>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>fotos-videos/" class="btn btn-secondary">← Volver a Fotos y Videos</a>
    </div>
</div>

<div class="container">
    <?php if (!empty($albums)): ?>
        <div class="albums-grid">
            <?php foreach ($albums as $album): ?>
            <div class="album-card">
                <a href="<?php echo htmlspecialchars(getAlbumUrl($album)); ?>" 
                   class="album-link-main" 
                   <?php if (!$album['es_pagina_intermedia']): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
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
            <h3>No hay álbumes disponibles para el año <?php echo $year; ?></h3>
            <p>Vuelve a la <a href="<?php echo BASE_URL; ?>fotos-videos/">página principal</a> para ver otros años.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>