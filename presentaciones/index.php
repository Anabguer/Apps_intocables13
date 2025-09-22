<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Montajes & Tips - Intocables';
include '../includes/header.php';

// Obtener presentaciones disponibles
$presentations = getPresentations();
?>

<div class="page-title">
    <h1>MONTAJES & TIPS</h1>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">← Volver al Inicio</a>
    </div>
</div>

<div class="container">
    <?php if (!empty($presentations)): ?>
        <div class="presentations-grid">
            <?php foreach ($presentations as $presentation): ?>
            <div class="presentation-card">
                <a href="presentation.php?id=<?php echo $presentation['id']; ?>" class="presentation-link-main">
                    <div class="presentation-image-container">
                        <img src="<?php echo getImagePath(htmlspecialchars($presentation['imagen'])); ?>" 
                             alt="<?php echo htmlspecialchars($presentation['titulo']); ?>"
                             class="presentation-image"
                             onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                    </div>
                    <div class="presentation-content">
                        <h4 class="presentation-title"><?php echo htmlspecialchars($presentation['titulo']); ?></h4>
                        <?php if ($presentation['subtitulo']): ?>
                        <p class="presentation-subtitle"><?php echo htmlspecialchars($presentation['subtitulo']); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-presentations">
            <h3>No hay presentaciones disponibles</h3>
            <p>Vuelve a la <a href="<?php echo BASE_URL; ?>fotos-videos/">página principal</a> para ver otros contenidos.</p>
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

.no-presentations {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.no-presentations h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.no-presentations p {
    color: #7f8c8d;
}

.no-presentations a {
    color: #667eea;
    text-decoration: none;
}

.no-presentations a:hover {
    text-decoration: underline;
}

.presentations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.presentation-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    display: block;
}

.presentation-link-main {
    text-decoration: none;
    color: inherit;
    display: block;
    cursor: pointer;
}

.presentation-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    border: 2px solid #667eea;
}

.presentation-card:focus-within {
    outline: 3px solid #667eea;
    outline-offset: 2px;
}

.presentation-image-container {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
}

.presentation-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.presentation-card:hover .presentation-image {
    transform: scale(1.05);
}

.presentation-content {
    padding: 1.5rem;
    text-align: center;
}

.presentation-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.presentation-subtitle {
    font-size: 1rem;
    font-weight: bold;
    color: #7f8c8d;
    margin-bottom: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .presentations-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .presentation-image-container {
        height: 250px;
    }
    
    .presentation-title {
        font-size: 1.1rem;
    }
    
    .presentation-subtitle {
        font-size: 0.9rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>