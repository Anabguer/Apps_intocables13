<?php
require_once '../includes/auth.php';
require_once '../includes/paths.php';  // Incluir paths para CSS_URL
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentUser = getCurrentUser();


// Configuración de la galería
$DIR = __DIR__ . '/../img/alessandro';
$URL = IMG_URL . 'alessandro';  // Usar URL dinámica
$ALLOWED_EXT = ['jpg','jpeg','png','gif','webp','mp4','mov','webm','mkv'];

$items = [];
if (is_dir($DIR)) {
    $dh = opendir($DIR);
    while (($file = readdir($dh)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $ALLOWED_EXT)) continue;
        $path = $DIR . '/' . $file;
        if (!is_file($path)) continue;
        $mtime = filemtime($path) ?: 0;
        $isVideo = in_array($ext, ['mp4','mov','webm','mkv']);
        
        // Intentar obtener fecha de captura (EXIF para imágenes, fecha de modificación para videos)
        $captureDate = $mtime; // Por defecto usar fecha de modificación
        
        if (!$isVideo && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            if ($exif && isset($exif['DateTimeOriginal'])) {
                $captureDate = strtotime($exif['DateTimeOriginal']);
            } elseif ($exif && isset($exif['DateTime'])) {
                $captureDate = strtotime($exif['DateTime']);
            }
        }
        
        
        $items[] = [
            'name' => $file,
            'url'  => $URL . '/' . rawurlencode($file),
            'mtime'=> $mtime,
            'kind' => $isVideo ? 'video' : 'image',
            'date' => date('Y-m-d', $captureDate),
            'year' => date('Y', $captureDate),
            'month' => date('m', $captureDate),
            'day' => date('d', $captureDate)
        ];
    }
    closedir($dh);
}

// Ordenar por fecha de captura (más recientes primero)
usort($items, fn($a,$b)=> strtotime($b['date']) <=> strtotime($a['date']));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Alessandro - Fotos y Videos</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>styles.css?v=<?php echo filemtime(__DIR__ . '/../css/styles.css'); ?>">
    <style>
        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .gallery-header {
            text-align: center;
            margin-bottom: 0;
            margin-top: -1rem;
        }
        
        .gallery-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .gallery-subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .gallery-item img,
        .gallery-item video {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .gallery-item .video-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.6);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover .video-overlay {
            background: rgba(0,0,0,0.8);
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        
        .year-section {
            margin-bottom: 2rem;
        }
        
        .year-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid #667eea;
        }
        
        .month-section {
            margin-bottom: 1.5rem;
        }
        
        .month-title {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 0.8rem;
            padding-left: 1rem;
            border-left: 4px solid #667eea;
        }
        
        .empty-gallery {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-gallery .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .breadcrumb {
            margin-top: 0.5rem;
            margin-bottom: -1rem;
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
        
        /* Reducir espacio superior del título */
        .page-title {
            padding-top: 60px !important;
        }
        
        
        .stats {
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .stats span {
            margin: 0 1rem;
            color: #666;
        }
        
        .stats .count {
            font-weight: 600;
            color: #333;
        }
        
        /* Estilos para comentarios */
        .comment-controls {
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }
        
        .comment-controls.show {
            display: block;
        }
        
        .selected-count {
            font-weight: 600;
            color: #667eea;
            margin-right: 1rem;
        }
        
        .comment-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 0.5rem;
            transition: background 0.3s ease;
        }
        
        .comment-btn:hover {
            background: #5a6fd8;
        }
        
        .comment-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .gallery-item {
            position: relative;
        }
        
        .gallery-item .checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .gallery-item .comment-indicator {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 10;
        }
        
        .comment-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .comment-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .comment-modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .comment-modal h3 {
            margin-top: 0;
            color: #333;
        }
        
        .comment-textarea {
            width: 100%;
            height: 120px;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
            margin-bottom: 1rem;
        }
        
        .comment-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .comment-preview {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border-left: 4px solid #667eea;
        }
        
        .comment-preview h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .comment-preview p {
            margin: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="page-title">
        <h1>📷 Galería de Alessandro</h1>
        <hr>
        <div class="breadcrumb">
            <a href="index.php" class="btn btn-secondary">← Volver a Fotos y Videos</a>
        </div>
    </div>

    <div class="gallery-container">
        <div class="gallery-header">
            <button id="toggle-selection" class="comment-btn">✏️ Gestionar Comentarios</button>
        </div>
        
        <div id="comment-controls" class="comment-controls">
            <span class="selected-count">0 archivos seleccionados</span>
            <button id="add-comment-btn" class="comment-btn" disabled>💬 Añadir Comentario</button>
            <button id="view-comments-btn" class="comment-btn" disabled>👁️ Ver Comentarios</button>
            <button id="clear-selection-btn" class="comment-btn">❌ Limpiar Selección</button>
        </div>
        
        <?php if (empty($items)): ?>
            <div class="empty-gallery">
                <div class="icon">📷</div>
                <h3>No hay fotos ni videos todavía</h3>
                <p>Las fotos y videos de Alessandro aparecerán aquí una vez que se suban.</p>
            </div>
        <?php else: ?>
            <div class="stats">
                <span>Total: <span class="count"><?php echo count($items); ?></span> archivos</span>
                <span>Fotos: <span class="count"><?php echo count(array_filter($items, fn($item) => $item['kind'] === 'image')); ?></span></span>
                <span>Videos: <span class="count"><?php echo count(array_filter($items, fn($item) => $item['kind'] === 'video')); ?></span></span>
            </div>
            
            <?php
            // Agrupar por año
            $itemsByYear = [];
            foreach ($items as $item) {
                $itemsByYear[$item['year']][] = $item;
            }
            
            // Ordenar años de más reciente a más antiguo
            krsort($itemsByYear);
            ?>
            
            <?php foreach ($itemsByYear as $year => $yearItems): ?>
                <div class="year-section">
                    <h2 class="year-title">📅 <?php echo $year; ?></h2>
                    
                    <?php
                    // Agrupar por mes dentro del año
                    $itemsByMonth = [];
                    foreach ($yearItems as $item) {
                        $itemsByMonth[$item['month']][] = $item;
                    }
                    
                    // Ordenar meses de más reciente a más antiguo
                    krsort($itemsByMonth);
                    ?>
                    
                    <?php foreach ($itemsByMonth as $month => $monthItems): ?>
                        <div class="month-section">
                            <h3 class="month-title"><?php 
                                $meses = [
                                    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                                    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                                    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                                ];
                                echo $meses[$month];
                            ?></h3>
                            
                            <div class="gallery-grid">
                                <?php foreach ($monthItems as $item): ?>
                                    <div class="gallery-item" data-filename="<?php echo htmlspecialchars($item['name']); ?>">
                                        <input type="checkbox" class="checkbox" data-filename="<?php echo htmlspecialchars($item['name']); ?>">
                                        <div class="comment-indicator" id="comment-<?php echo htmlspecialchars($item['name']); ?>" style="display: none;" onclick="showFileComments('<?php echo htmlspecialchars($item['name']); ?>')" title="Ver historial de comentarios">💬</div>
                                        
                                        <div onclick="openMedia('<?php echo htmlspecialchars($item['url']); ?>', '<?php echo $item['kind']; ?>')">
                                            <?php if ($item['kind'] === 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($item['url']); ?>" alt="">
                                            <?php else: ?>
                                                <video muted loop>
                                                    <source src="<?php echo htmlspecialchars($item['url']); ?>" type="video/mp4">
                                                </video>
                                                <div class="video-overlay">▶</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Modal de comentarios -->
    <div id="comment-modal" class="comment-modal">
        <div class="comment-modal-content">
            <h3 id="comment-modal-title">💬 Añadir Comentario</h3>
            <div id="comment-files-list"></div>
            <textarea id="comment-text" class="comment-textarea" placeholder="Escribe tu comentario aquí..."></textarea>
            <div class="comment-modal-actions">
                <button id="cancel-comment" class="comment-btn" style="background: #6c757d;">❌ Cancelar</button>
                <button id="save-comment" class="comment-btn">💾 Guardar</button>
            </div>
        </div>
    </div>
    
    <script>
        let selectedFiles = new Set();
        let comments = {};
        let isSelectionMode = false;
        
        // Cargar comentarios existentes al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadComments();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            // Toggle selección
            document.getElementById('toggle-selection').addEventListener('click', toggleSelectionMode);
            
            // Botones de comentarios
            document.getElementById('add-comment-btn').addEventListener('click', showAddCommentModal);
            document.getElementById('view-comments-btn').addEventListener('click', showViewCommentsModal);
            document.getElementById('clear-selection-btn').addEventListener('click', clearSelection);
            
            // Modal
            document.getElementById('cancel-comment').addEventListener('click', hideCommentModal);
            document.getElementById('save-comment').addEventListener('click', saveComment);
            
            // Checkboxes
            document.querySelectorAll('.checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', handleCheckboxChange);
            });
        }
        
        function toggleSelectionMode() {
            isSelectionMode = !isSelectionMode;
            const controls = document.getElementById('comment-controls');
            const checkboxes = document.querySelectorAll('.checkbox');
            
            if (isSelectionMode) {
                controls.classList.add('show');
                checkboxes.forEach(cb => cb.style.display = 'block');
            } else {
                controls.classList.remove('show');
                checkboxes.forEach(cb => cb.style.display = 'none');
                clearSelection();
            }
        }
        
        function handleCheckboxChange(event) {
            const filename = event.target.dataset.filename;
            
            if (event.target.checked) {
                selectedFiles.add(filename);
            } else {
                selectedFiles.delete(filename);
            }
            
            updateSelectionUI();
        }
        
        function updateSelectionUI() {
            const count = selectedFiles.size;
            document.querySelector('.selected-count').textContent = `${count} archivos seleccionados`;
            
            document.getElementById('add-comment-btn').disabled = count === 0;
            document.getElementById('view-comments-btn').disabled = count === 0;
        }
        
        function clearSelection() {
            selectedFiles.clear();
            document.querySelectorAll('.checkbox').forEach(cb => cb.checked = false);
            updateSelectionUI();
        }
        
        function loadComments() {
            fetch('../api/alessandro-comments.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        comments = {};
                        // Agrupar comentarios por archivo
                        data.comments.forEach(comment => {
                            if (!comments[comment.filename]) {
                                comments[comment.filename] = [];
                            }
                            comments[comment.filename].push(comment);
                        });
                        
                        // Mostrar indicador de comentario si hay comentarios
                        Object.keys(comments).forEach(filename => {
                            const indicator = document.getElementById(`comment-${filename}`);
                            if (indicator) {
                                indicator.style.display = 'block';
                                indicator.textContent = comments[filename].length; // Mostrar número de comentarios
                            }
                        });
                    }
                })
                .catch(error => console.error('Error cargando comentarios:', error));
        }
        
        function showAddCommentModal() {
            const modal = document.getElementById('comment-modal');
            const title = document.getElementById('comment-modal-title');
            const filesList = document.getElementById('comment-files-list');
            const textarea = document.getElementById('comment-text');
            
            title.textContent = '💬 Añadir Comentario';
            filesList.innerHTML = `<p><strong>Archivos seleccionados:</strong> ${Array.from(selectedFiles).join(', ')}</p>`;
            textarea.value = '';
            
            modal.classList.add('show');
        }
        
        function showViewCommentsModal() {
            const modal = document.getElementById('comment-modal');
            const title = document.getElementById('comment-modal-title');
            const filesList = document.getElementById('comment-files-list');
            const textarea = document.getElementById('comment-text');
            
            title.textContent = '👁️ Ver Historial de Comentarios';
            
            let html = '<h4>Historial de comentarios de archivos seleccionados:</h4>';
            Array.from(selectedFiles).forEach(filename => {
                const fileComments = comments[filename];
                if (fileComments && fileComments.length > 0) {
                    html += `<div class="comment-preview">
                        <h4>📁 ${filename}</h4>
                        <p><strong>Total de comentarios: ${fileComments.length}</strong></p>`;
                    
                    fileComments.forEach((comment, index) => {
                        const fecha = new Date(comment.created_at).toLocaleString('es-ES');
                        html += `<div style="margin: 1rem 0; padding: 0.8rem; background: #f8f9fa; border-radius: 5px; border-left: 3px solid #667eea;">
                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">
                                <strong>👤 ${comment.nombre_usuario}</strong> - ${fecha}
                            </div>
                            <div>${comment.comment}</div>
                        </div>`;
                    });
                    
                    html += `</div>`;
                } else {
                    html += `<div class="comment-preview">
                        <h4>📁 ${filename}</h4>
                        <p><em>Sin comentarios</em></p>
                    </div>`;
                }
            });
            
            filesList.innerHTML = html;
            textarea.style.display = 'none';
            
            modal.classList.add('show');
        }
        
        function hideCommentModal() {
            document.getElementById('comment-modal').classList.remove('show');
            document.getElementById('comment-text').style.display = 'block';
        }
        
        function saveComment() {
            const comment = document.getElementById('comment-text').value.trim();
            
            if (!comment) {
                alert('Por favor, escribe un comentario');
                return;
            }
            
            // Guardar comentario para cada archivo seleccionado
            const promises = Array.from(selectedFiles).map(filename => {
                return fetch('../api/alessandro-comments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        filename: filename,
                        comment: comment
                    })
                });
            });
            
            Promise.all(promises)
                .then(responses => Promise.all(responses.map(r => r.json())))
                .then(results => {
                    const allSuccess = results.every(r => r.success);
                    if (allSuccess) {
                        alert('Comentarios guardados correctamente');
                        loadComments(); // Recargar comentarios
                        hideCommentModal();
                        clearSelection();
                    } else {
                        alert('Error al guardar algunos comentarios');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al guardar comentarios');
                });
        }
        
        function showFileComments(filename) {
            const modal = document.getElementById('comment-modal');
            const title = document.getElementById('comment-modal-title');
            const filesList = document.getElementById('comment-files-list');
            const textarea = document.getElementById('comment-text');
            
            title.textContent = `👁️ Historial de Comentarios - ${filename}`;
            
            const fileComments = comments[filename];
            if (fileComments && fileComments.length > 0) {
                let html = `<h4>📁 ${filename}</h4>
                    <p><strong>Total de comentarios: ${fileComments.length}</strong></p>`;
                
                fileComments.forEach((comment, index) => {
                    const fecha = new Date(comment.created_at).toLocaleString('es-ES');
                    html += `<div style="margin: 1rem 0; padding: 0.8rem; background: #f8f9fa; border-radius: 5px; border-left: 3px solid #667eea;">
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">
                            <strong>👤 ${comment.nombre_usuario}</strong> - ${fecha}
                        </div>
                        <div>${comment.comment}</div>
                    </div>`;
                });
                
                filesList.innerHTML = html;
            } else {
                filesList.innerHTML = `<h4>📁 ${filename}</h4><p><em>Sin comentarios</em></p>`;
            }
            
            textarea.style.display = 'none';
            modal.classList.add('show');
        }
        
        function openMedia(url, type) {
            if (isSelectionMode) {
                return; // No abrir en modo selección
            }
            
            if (type === 'video') {
                window.open(url, '_blank');
            } else {
                window.open(url, '_blank');
            }
        }
    </script>
</body>
</html>
