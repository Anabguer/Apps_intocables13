<?php
require_once '../includes/config.php';

$pageTitle = 'Cumpleaños - Intocables';
include '../includes/header.php';
?>

<div class="page-title">
    <h1>CUMPLEAÑOS</h1>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>fotos-videos/" class="btn btn-secondary">← Volver a Fotos y Videos</a>
    </div>
</div>

<div class="container">

    <!-- Grid de signos del zodíaco -->
    <div class="contenedorFotosVideos-grid">
        <!-- Piscis -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/FuTm86jPMgedAtpK8" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_01_piscis.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Piscis</h1>
        </div>

        <!-- Tauro -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/sERfuMwureSWymNN6" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_02_tauro.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Tauro</h1>
        </div>

        <!-- Cáncer -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/FCHqkHCzhqofP1LEA" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_03_cancer.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Cáncer</h1>
        </div>

        <!-- Leo -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/qh1HY1C6jq6z51Pe8" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_04_leo.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Leo</h1>
        </div>

        <!-- Libra -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/gbU4Cc348maTHYcc7" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_05_libra.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Libra</h1>
        </div>

        <!-- Scorpio -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/5ftrCxYJYE4KnXee8" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_06_scorpio.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Scorpio</h1>
        </div>

        <!-- Sagitario -->
        <div class="contenedorRestoAños-grid">
            <a title="Cumpleaños" href="https://goo.gl/photos/8b2PJCwuie5B4neL8" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo getImagePath('/img/botones/cumples_07_sagitario.webp'); ?>" 
                     alt="Cumpleaños"
                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
            </a>
            <h1>Sagitario</h1>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>