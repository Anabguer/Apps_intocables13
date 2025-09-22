<?php
require_once '../includes/config.php';
require_once '../includes/paths.php';
require_once '../includes/auth.php';
require_once '../includes/aray-functions.php';

// Verificar permisos de administrador
requireAdmin();

$pageTitle = 'Administración de Años de Aray';
include '../includes/header.php';
?>

<script src="<?php echo JS_URL; ?>aray-years.js?v=<?php echo time(); ?>" defer></script>

<?php
?>

<div class="admin-container">
    <a href="index.php" class="back-link">← Volver a Administración</a>
    
    <div class="admin-header">
        <h1>Administración de Años de Aray</h1>
    </div>

    <div class="admin-section">
        <div class="section-header">
            <h2>Gestión de Años</h2>
            <button class="btn btn-primary" onclick="openYearModal()">
                ➕ Nuevo Año
            </button>
        </div>


        <!-- Tabla de años -->
        <div class="table-container">
            <table class="admin-table" id="years-table">
                <thead>
                    <tr>
                        <th onclick="sortTable('id')">ID <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('year')">Año <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('image')">Imagen <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('es_pagina_intermedia')">Intermedia <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('orden')">Orden <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('activo')">Estado <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('fecha_creacion')">Fecha Creación <span class="sort-arrow">↕</span></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $years = getArayYears();
                    foreach ($years as $year): 
                    ?>
                    <tr>
                        <td><?php echo $year['id']; ?></td>
                        <td><?php echo $year['year']; ?></td>
                        <td>
                            <img src="<?php echo getImagePath(htmlspecialchars($year['image'])); ?>" alt="Año <?php echo $year['year']; ?>" class="table-image" 
                                 onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                        </td>
                        <td>
                            <span class="status-badge <?php echo $year['es_pagina_intermedia'] ? 'intermediate' : 'final'; ?>">
                                <?php echo $year['es_pagina_intermedia'] ? 'Sí' : 'No'; ?>
                            </span>
                        </td>
                        <td><?php echo $year['orden']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $year['activo'] ? 'active' : 'inactive'; ?>">
                                <?php echo $year['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($year['fecha_creacion'])); ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-primary" onclick="editYear(<?php echo $year['id']; ?>)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteYear(<?php echo $year['id']; ?>)" title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal para crear/editar año -->
<div id="year-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title-section">
                <h3 id="modal-title">👶 Crear Nuevo Año de Aray</h3>
                <p id="modal-subtitle">Completa la información del año</p>
            </div>
            <button class="close-btn" onclick="closeYearModal()" title="Cerrar">
                <span>&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="year-form" method="POST" action="../api/aray-years.php">
                <input type="hidden" id="year-id" name="id" value="">
                <input type="hidden" name="action" value="create">
                
                <!-- ID del año (más pequeño, al inicio) -->
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div class="form-group" style="max-width: 200px;">
                        <label for="year-id-display">ID del Año</label>
                        <input type="text" id="year-id-display" readonly value="Nuevo año" class="readonly-field readonly-field-small" tabindex="-1">
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">📝 Información Básica</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year-year">Año *</label>
                            <input type="number" id="year-year" name="year" required min="2000" max="2030" placeholder="2024">
                        </div>
                        <div class="form-group">
                            <label for="year-orden">Orden de Visualización</label>
                            <input type="number" id="year-orden" name="orden" min="0" value="0" placeholder="0">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4 class="section-title">🔗 Configuración de Enlace</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="year-es-pagina-intermedia" name="es_pagina_intermedia" checked>
                                <span class="checkmark"></span>
                                Es página intermedia
                            </label>
                            <small class="form-help">Si está marcado, el año será una página intermedia que muestra trimestres</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4 class="section-title">🖼️ Imagen</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year-image">Ruta de Imagen *</label>
                            <input type="text" id="year-image" name="image" required placeholder="/img/botones/2024/2024_foto_aray_1.webp">
                            <small class="form-help">Ruta relativa desde la raíz del sitio</small>
                        </div>
                        <div class="form-group">
                            <div class="image-preview-container">
                                <img id="year-image-preview" src="" alt="Vista previa" class="image-preview" 
                                     onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4 class="section-title">⚙️ Configuración</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="year-activo" name="activo" checked>
                                <span class="checkmark"></span>
                                Año activo (visible en la web)
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar Año</button>
                    <button type="button" class="btn btn-secondary" onclick="closeYearModal()">❌ Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php include '../includes/footer.php'; ?>
