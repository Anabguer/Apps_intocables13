<?php
require_once '../includes/paths.php';
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$currentUser = getCurrentUser();

// Verificar si es una petición AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Crear nueva presentación
        $result = createPresentation($input);
        echo json_encode(['success' => $result]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Actualizar presentación
        $id = $input['id'];
        unset($input['id']);
        $result = updatePresentation($id, $input);
        echo json_encode(['success' => $result]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Eliminar presentación
        $id = $input['id'];
        $result = deletePresentation($id);
        echo json_encode(['success' => $result]);
    }
    exit;
}

// Obtener todas las presentaciones
$presentations = getPresentations();

$pageTitle = 'Administración de Montajes & Tips - Intocables';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Montajes & Tips - Intocables</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>styles.css?v=<?php echo time(); ?>">
    <script src="<?php echo JS_URL; ?>montajes-tips.js?v=<?php echo time(); ?>" defer></script>
</head>
<body>
    <div class="admin-container">
        <a href="index.php" class="back-link">← Volver a Administración</a>
        
        <div class="admin-header">
            <h1>Administración de Montajes & Tips</h1>
            <p>Gestiona las presentaciones y sus items</p>
        </div>
        
        <div class="admin-actions">
            <button class="btn btn-primary" onclick="showPresentationForm()">
                ➕ Nuevo Montaje & Tip
            </button>
        </div>
        
        <?php if (!empty($presentations)): ?>
        <div class="presentations-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Subtítulo</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($presentations as $presentation): ?>
                    <tr>
                        <td class="presentation-id"><strong><?php echo $presentation['id']; ?></strong></td>
                        <td>
                            <img src="<?php echo getImagePath(htmlspecialchars($presentation['imagen'])); ?>" 
                                 alt="<?php echo htmlspecialchars($presentation['titulo']); ?>"
                                 class="presentation-image"
                                 onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                        </td>
                        <td>
                            <div class="presentation-info">
                                <div class="presentation-title"><?php echo htmlspecialchars($presentation['titulo']); ?></div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($presentation['subtitulo'] ?: '-'); ?></td>
                        <td class="presentation-order"><?php echo $presentation['orden']; ?></td>
                        <td>
                            <?php if ($presentation['activo']): ?>
                            <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                            <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="presentation-items.php?presentation_id=<?php echo $presentation['id']; ?>" class="btn btn-info btn-sm" title="Gestionar Items">
                                📋 Items
                            </a>
                            <button class="btn btn-warning btn-sm" onclick="editPresentation(<?php echo htmlspecialchars(json_encode($presentation)); ?>)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deletePresentation(<?php echo $presentation['id']; ?>)" title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="no-presentations">
            <h3>No hay montajes & tips disponibles</h3>
            <p>Crea tu primer montaje & tip usando el botón "Nuevo Montaje & Tip".</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal para formulario de presentación -->
    <div id="presentationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-section">
                    <h3 id="modalTitle">🎬 Nuevo Montaje & Tip</h3>
                    <p id="modalSubtitle">Completa la información del montaje & tip</p>
                </div>
                <button class="close-btn" onclick="closeModal()" title="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
            
            <form id="presentationForm">
                <input type="hidden" id="presentation-id" name="id">
                <input type="hidden" name="action" value="create">
                
                <!-- ID de la presentación (más pequeño, al inicio) -->
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div class="form-group" style="max-width: 200px;">
                        <label for="presentation-id-display">ID de la Presentación</label>
                        <input type="text" id="presentation-id-display" readonly value="Nueva presentación" class="readonly-field readonly-field-small" tabindex="-1">
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">📝 Información Básica</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título *</label>
                            <input type="text" id="titulo" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="subtitulo">Subtítulo</label>
                            <input type="text" id="subtitulo" name="subtitulo">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="imagen">Ruta de la Imagen *</label>
                            <input type="text" id="imagen" name="imagen" required placeholder="/img/botones/ejemplo.png">
                        </div>
                        <div class="form-group">
                            <label for="enlace">Enlace</label>
                            <input type="text" id="enlace" name="enlace" placeholder="# o URL completa">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4 class="section-title">⚙️ Configuración Avanzada</h4>
                    
                    <div class="form-group" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border: 2px solid #007bff;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: bold; color: #007bff;">
                            <input type="checkbox" id="es_pagina_intermedia" name="es_pagina_intermedia" style="margin: 0; transform: scale(1.2);">
                            ✅ Es página intermedia
                        </label>
                        <small class="form-text" style="display: block; margin-top: 0.5rem; color: #666; font-style: italic;">Si está marcado, el enlace se establecerá automáticamente como "#"</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" placeholder="Descripción opcional del montaje & tip"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="orden">Orden</label>
                            <input type="number" id="orden" name="orden" value="0" min="0">
                        </div>
                        <div class="form-group">
                            <label for="activo">Estado</label>
                            <select id="activo" name="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <span>❌</span> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span>💾</span> Guardar
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
    
</body>
</html>
