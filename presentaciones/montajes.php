<?php
require_once '../includes/header.php';
require_once '../includes/config.php';

// Obtener todos los años (items intermedios de la presentación MONTAJES)
$years = getPresentationItems(70, ['where' => 'es_pagina_intermedia = 1']);
?>

<main>
    <div class="container">
        <div class="page-header">
            <h1>MONTAJES</h1>
            <p>Montajes organizados por años</p>
        </div>

        <div class="presentations-grid">
            <?php foreach ($years as $year): ?>
                <div class="presentation-card">
                    <a href="presentation.php?id=<?php echo $year['id']; ?>" class="presentation-link">
                        <div class="presentation-image-container">
                            <img src="<?php echo $year['imagen']; ?>" alt="<?php echo htmlspecialchars($year['titulo']); ?>" class="presentation-image">
                        </div>
                        <div class="presentation-content">
                            <h3 class="presentation-title"><?php echo htmlspecialchars($year['titulo']); ?></h3>
                            <?php if (!empty($year['subtitulo'])): ?>
                                <p class="presentation-subtitle"><?php echo htmlspecialchars($year['subtitulo']); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
