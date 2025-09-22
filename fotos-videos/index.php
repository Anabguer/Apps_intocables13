<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Fotos y Videos - Intocables';
include '../includes/header.php';

// Obtener años disponibles
$years = getAvailableYears();
?>

<div class="page-title">
    <h1>FOTOS Y VIDEOS</h1>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">← Volver al Inicio</a>
    </div>
</div>

<div class="container">
    <!-- Sección de imágenes familiares -->
    <div class="family-section">
        <div class="family-grid">
            <div class="family-item">
                <a href="aray.php">
                    <img src="<?php echo getImagePath('/img/botones/fotos_aray.png'); ?>" alt="Aray">
                    <h3>ARAY</h3>
                </a>
            </div>
            
            <div class="contenedorArayEvol-grid">
                <a title="ArayVideos" href="https://www.amazon.es/photos/share/LoQSMbbCkUMnetU4hcP5HLy9hKAv7a4Rvcy8MD5L1V8" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo getImagePath('/img/botones/2017/2017_foto_aray_1.webp'); ?>" alt="ArayVideos">
                </a>
                <h3>EVOLUCIÓN ARAY</h3>
            </div>
            
            <div class="family-item">
                <a href="alessandro.php">
                    <img src="<?php echo getImagePath('/img/botones/foto_alessandro.jpg'); ?>" alt="Alessandro">
                    <h3>ALESSANDRO</h3>
                </a>
            </div>
        </div>
    </div>

    <!-- Línea separadora -->
    <div class="separator-line"></div>

    <!-- Sección de años -->
    <div class="years-section">
        <div class="years-grid">
            <?php foreach ($years as $year): ?>
            <div class="year-card">
                <a href="<?php echo getImagePath($year['enlace']); ?>">
                    <div class="year-image-container">
                        <img src="<?php echo getImagePath(htmlspecialchars($year['imagen'])); ?>" 
                             alt="Fotos <?php echo $year['titulo']; ?>"
                             class="year-image"
                             onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                    </div>
                    <div class="year-content">
                        <h3 class="year-title">
                            <?php echo $year['titulo'] == '2002' ? '2002 y anteriores' : $year['titulo']; ?>
                        </h3>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Línea separadora -->
    <div class="separator-line"></div>

    <!-- Sección CUMPLEAÑOS y OTROS VIDEOS -->
    <div class="special-section">
        <div class="special-grid">
            <a href="cumpleanos.php" class="special-card">
                <div class="special-image-container">
                    <img src="<?php echo getImagePath('/img/botones/zodiaco.webp'); ?>" 
                         alt="Cumpleaños"
                         class="special-image"
                         onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                </div>
                <div class="special-content">
                    <h4 class="special-title">CUMPLEAÑOS</h4>
                </div>
            </a>

            <a href="otros-videos.php" class="special-card">
                <div class="special-image-container">
                    <img src="<?php echo getImagePath('/img/botones/camara_edited.webp'); ?>" 
                         alt="Otros Videos"
                         class="special-image"
                         onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                </div>
                <div class="special-content">
                    <h4 class="special-title">OTROS VIDEOS</h4>
                </div>
            </a>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>