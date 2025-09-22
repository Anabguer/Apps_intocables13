<?php
require_once '../includes/config.php';
require_once '../includes/paths.php';
require_once '../includes/auth.php';

// Verificar permisos de administrador
requireAdmin();

// Obtener el ID de la presentación desde la URL
$presentation_id = isset($_GET['presentation_id']) ? (int)$_GET['presentation_id'] : 0;

if (!$presentation_id) {
    header('Location: montajes-tips.php');
    exit;
}

// Obtener información de la presentación
$presentation = getPresentationById($presentation_id);
if (!$presentation) {
    header('Location: montajes-tips.php');
    exit;
}

// Procesar acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = [
        'presentation_id' => $presentation_id,
        'titulo' => $_POST['titulo'] ?? '',
        'subtitulo' => $_POST['subtitulo'] ?? '',
        'imagen' => $_POST['imagen'] ?? '',
        'enlace' => $_POST['enlace'] ?? '',
        'es_pagina_intermedia' => isset($_POST['es_pagina_intermedia']) ? 1 : 0,
        'padre_id' => !empty($_POST['padre_id']) ? (int)$_POST['padre_id'] : null,
        'orden' => (int)($_POST['orden'] ?? 0),
        'activo' => (int)($_POST['activo'] ?? 1)
    ];
    
    if (createPresentationItem($data)) {
        echo json_encode(['success' => true, 'message' => 'Item creado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el item']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        exit;
    }
    
    if (updatePresentationItem($id, $input)) {
        echo json_encode(['success' => true, 'message' => 'Item actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el item']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        exit;
    }
    
    if (deletePresentationItem($id)) {
        echo json_encode(['success' => true, 'message' => 'Item eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el item']);
    }
    exit;
}

// Obtener items de la presentación
$items = getPresentationItems($presentation_id);

// Obtener todas las presentaciones para filtros
$presentations = getPresentations();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Items de Montajes & Tips - Intocables</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>styles.css?v=<?php echo time(); ?>">
    <script src="<?php echo JS_URL; ?>presentation-items.js?v=<?php echo time(); ?>" defer></script>
</head>
<body>
    <div class="admin-container">
        <a href="montajes-tips.php" class="back-link">← Volver a Montajes & Tips</a>
        
        <div class="admin-header">
            <h1>Administración de Items de Montajes & Tips</h1>
            <p>Gestiona los items individuales de cada montaje & tip</p>
        </div>

        <div class="admin-actions">
            <div>
                <h2>Presentación: <?php echo htmlspecialchars($presentation['titulo']); ?></h2>
                <p>Items: <?php echo count($items); ?></p>
            </div>
            <button class="btn btn-primary" onclick="showItemForm()">
                ➕ Nuevo Item
            </button>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label for="presentation-filter">Filtrar por presentación:</label>
                    <select id="presentation-filter" onchange="filterItems()">
                        <option value="">Todas las presentaciones</option>
                        <?php foreach ($presentations as $pres): ?>
                        <option value="<?php echo htmlspecialchars($pres['titulo']); ?>" 
                                <?php echo $pres['id'] == $presentation_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pres['titulo']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search-filter">Buscar por título:</label>
                    <input type="text" id="search-filter" placeholder="Escribe parte del título..." onkeyup="filterItems()">
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-secondary" onclick="clearFilters()">
                        🔄 Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($items)): ?>
        <div class="no-items">
            <h3>📝 No hay items en esta presentación</h3>
            <p>Haz clic en "Nuevo Item" para agregar el primer item.</p>
        </div>
        <?php else: ?>
        <!-- Tabla de items -->
        <div class="items-table">
            <table>
                <thead>
                    <tr>
                        <th><a href="#" onclick="sortTable(0); return false;">ID ↕️</a></th>
                        <th>Imagen</th>
                        <th><a href="#" onclick="sortTable(2); return false;">Título ↕️</a></th>
                        <th><a href="#" onclick="sortTable(3); return false;">Subtítulo ↕️</a></th>
                        <th><a href="#" onclick="sortTable(4); return false;">Presentación ↕️</a></th>
                        <th><a href="#" onclick="sortTable(5); return false;">Enlace ↕️</a></th>
                        <th><a href="#" onclick="sortTable(6); return false;">Padre ID ↕️</a></th>
                        <th><a href="#" onclick="sortTable(7); return false;">Orden ↕️</a></th>
                        <th><a href="#" onclick="sortTable(8); return false;">Estado ↕️</a></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td>
                            <img src="<?php echo getImagePath(htmlspecialchars($item['imagen'])); ?>" 
                                 alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                                 class="item-image"
                                 onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                        </td>
                        <td>
                            <div class="item-info">
                                <div class="item-title"><?php echo htmlspecialchars($item['titulo']); ?></div>
                                <?php if ($item['subtitulo']): ?>
                                <div class="item-subtitle"><?php echo htmlspecialchars($item['subtitulo']); ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($item['subtitulo'] ?: '-'); ?></td>
                        <td>
                            <span class="presentation-badge">
                                <?php echo htmlspecialchars($presentation['titulo']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars($item['enlace']); ?>" target="_blank">
                                <?php echo strlen($item['enlace']) > 30 ? substr($item['enlace'], 0, 30) . '...' : $item['enlace']; ?>
                            </a>
                            <div class="enlace-preview"><?php echo htmlspecialchars($item['enlace']); ?></div>
                        </td>
                        <td><?php echo $item['padre_id'] ?: '-'; ?></td>
                        <td><?php echo $item['orden']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $item['activo'] ? 'success' : 'danger'; ?>">
                                <?php echo $item['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" 
                                    onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" 
                                    title="Editar">
                                ✏️
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="deleteItem(<?php echo $item['id']; ?>)" 
                                    title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal para formulario de item -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Item</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <form id="itemForm">
                <input type="hidden" id="item-id" name="id">
                
                <div class="form-group">
                    <label for="item-id-display">ID del Item</label>
                    <input type="text" id="item-id-display" readonly class="readonly-field readonly-field-small" tabindex="-1">
                </div>
                
                <div class="form-group">
                    <label for="presentation_id">ID de Presentación *</label>
                    <input type="number" id="presentation_id" name="presentation_id" 
                           value="<?php echo $presentation_id; ?>" readonly class="readonly-field">
                </div>
                
                <div class="form-group">
                    <label for="titulo">Título *</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                
                <div class="form-group">
                    <label for="subtitulo">Subtítulo</label>
                    <input type="text" id="subtitulo" name="subtitulo">
                </div>
                
                <div class="form-fields-inline">
                    <div class="form-group">
                        <label for="imagen">Ruta de Imagen *</label>
                        <input type="text" id="imagen" name="imagen" required placeholder="/img/botones/imagen.webp">
                    </div>
                    
                    <div class="form-group">
                        <label for="enlace">Enlace</label>
                        <input type="text" id="enlace" name="enlace" placeholder="https://ejemplo.com o ruta relativa">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="es_pagina_intermedia" name="es_pagina_intermedia" value="1">
                        Es página intermedia
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="padre_id">ID del Padre (opcional)</label>
                    <input type="number" id="padre_id" name="padre_id" placeholder="Solo si pertenece a otra presentación">
                </div>
                
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
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        ❌ Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
