<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Intocables - Fotos, Videos, Montajes y Recetas';
include 'includes/header.php';
?>

<div class="page-title">
    <h1><?php echo htmlspecialchars(SITE_NAME); ?></h1>
    <hr>
    <?php 
    $currentUser = getCurrentUser();
    if (isAdmin() || ($currentUser && $currentUser['perfil'] === 'edit')): 
    ?>
    <div class="admin-link">
        <a href="admin/" class="btn btn-secondary">⚙️ Administración</a>
    </div>
    <?php endif; ?>
</div>

<div class="container">
    <!-- Sección principal con enlaces a las diferentes áreas -->
    <div class="main-sections">
        <div class="sections-grid">
            <?php
            // Obtener elementos de la página principal desde la base de datos
            $homepageElements = getHomepageElements();
            
            foreach ($homepageElements as $element):
                // Determinar si es enlace externo o interno
                $isExternal = strpos($element['enlace'], 'http') === 0;
                $linkTarget = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
            ?>
            <a href="<?php echo htmlspecialchars($element['enlace']); ?>" 
               class="section-card"<?php echo $linkTarget; ?>>
                <div class="section-image-container">
                    <img src="<?php echo getImagePath($element['imagen']); ?>" 
                         alt="<?php echo htmlspecialchars($element['titulo']); ?>"
                         class="section-image"
                         onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                </div>
                <div class="section-content">
                    <h3 class="section-title"><?php echo htmlspecialchars($element['titulo']); ?></h3>
                    <p class="section-description"><?php echo htmlspecialchars($element['descripcion']); ?></p>
                </div>
            </a>
            <?php endforeach; ?>

        </div>
    </div>
    
    <!-- Sección de imágenes familiares -->
    <div class="family-images-section">
        <div class="imagenes">
            <img src="<?php echo getImagePath('/img/LogosBanners/Logo2conTituloIzquierdo.png'); ?>" id="portadaLogoIzquierdo" alt="">
            <img src="<?php echo getImagePath('/img/botones/portada_papas.png'); ?>" id="portadaPapas" alt="">
            <img src="<?php echo getImagePath('/img/botones/portada_jessi.png'); ?>" id="portadaJessi" alt="">
            <img src="<?php echo getImagePath('/img/botones/portada_aray.png'); ?>" id="portadaAray" alt="">
            <img src="<?php echo getImagePath('/img/botones/portada_anabel.png'); ?>" id="portadaAnabel" alt="">
            <img src="<?php echo getImagePath('/img/LogosBanners/Logo2conTituloDerecho.png'); ?>" id="portadaLogoDerecho" alt="">
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>