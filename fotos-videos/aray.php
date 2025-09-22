<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/aray-functions.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Aray - Fotos y Videos';
include '../includes/header.php';

// Obtener años de Aray
$years = getArayYears();
?>

<div class="page-title">
    <h1>ARAY</h1>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>fotos-videos/" class="btn btn-secondary btn-sm">← Volver a Fotos y Videos</a>
    </div>
</div>

<div class="container">
    <!-- Sección de años de Aray -->
    <div class="aray-section">
        <div class="aray-grid">
            <?php foreach ($years as $year): ?>
            <div class="aray-year-item">
                <div class="aray-year-image">
                    <img src="<?php echo getImagePath(htmlspecialchars($year['image'])); ?>" 
                         alt="Aray <?php echo $year['year']; ?>"
                         onerror="this.src='<?php echo getImagePath('/img/botones/fotos_aray.png'); ?>'">
                </div>
                <div class="aray-year-info">
                    <h3><?php echo $year['year']; ?></h3>
                    <div class="aray-trimestres">
                        <?php 
                        $trimestres = getArayTrimestres($year['id']);
                        foreach ($trimestres as $trimestre): 
                        ?>
                        <div class="trimestre-item">
                            <a href="<?php echo htmlspecialchars($trimestre['url_fotos']); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="trimestre-btn trimestre-<?php echo $trimestre['tipo_url_fotos']; ?>">
                                <?php echo htmlspecialchars($trimestre['titulo']); ?>
                            </a>
                            <?php if (!empty($trimestre['url_video'])): ?>
                            <a href="javascript:void(0);" 
                               onclick="window.open('<?php echo htmlspecialchars($trimestre['url_video']); ?>', 'popup', 'left=390, top=150, width=860, height=520, toolbar=0, resizable=1')"
                               class="video-btn" 
                               title="Ver video">
                                🎥
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>