<?php
require_once '../includes/config.php';
require_once '../includes/paths.php';
require_once '../includes/auth.php';
require_once '../includes/aray-functions.php';

// Verificar permisos de administrador
requireAdmin();

$pageTitle = 'Administración de Trimestres de Aray';
include '../includes/header.php';
?>

<script src="<?php echo JS_URL; ?>aray-trimestres.js?v=<?php echo time(); ?>" defer></script>

<?php

// Obtener años para el filtro
$years = getArayYears();
?>

<div class="admin-container">
    <a href="index.php" class="back-link">← Volver a Administración</a>
    
    <div class="admin-header">
        <h1>Administración de Trimestres de Aray</h1>
    </div>

    <div class="admin-section">
        <div class="section-header">
            <h2>Gestión de Trimestres</h2>
            <button class="btn btn-primary" onclick="openTrimestreModal()">
                ➕ Nuevo Trimestre
            </button>
        </div>


        <!-- Tabla de trimestres -->
        <div class="table-container">
            <table class="admin-table" id="trimestres-table">
                <thead>
                    <tr>
                        <th onclick="sortTable('id')">ID <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('year')">Año <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('trimestre')">Trimestre <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('titulo')">Título <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('tipo_url_fotos')">Tipo <span class="sort-arrow">↕</span></th>
                        <th>Enlaces</th>
                        <th onclick="sortTable('orden')">Orden <span class="sort-arrow">↕</span></th>
                        <th onclick="sortTable('activo')">Estado <span class="sort-arrow">↕</span></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $pdo = getDBConnection();
                    $sql = "SELECT t.*, y.year FROM aray_trimestres t 
                            JOIN aray_years y ON t.year_id = y.id 
                            WHERE t.activo = 1 
                            ORDER BY y.year DESC, t.orden ASC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $trimestres = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($trimestres as $trimestre): 
                    ?>
                    <tr>
                        <td><?php echo $trimestre['id']; ?></td>
                        <td><?php echo $trimestre['year']; ?></td>
                        <td><?php echo $trimestre['trimestre']; ?></td>
                        <td><?php echo htmlspecialchars($trimestre['titulo']); ?></td>
                        <td>
                            <span class="tipo-badge tipo-<?php echo $trimestre['tipo_url_fotos']; ?>">
                                <?php echo strtoupper($trimestre['tipo_url_fotos']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="links-cell">
                                <a href="<?php echo htmlspecialchars($trimestre['url_fotos']); ?>" target="_blank" class="link-btn fotos-link" title="Ver fotos">
                                    📷
                                </a>
                                <?php if (!empty($trimestre['url_video'])): ?>
                                <a href="javascript:void(0);" 
                                   onclick="window.open('<?php echo htmlspecialchars($trimestre['url_video']); ?>', 'popup', 'left=390, top=150, width=860, height=520, toolbar=0, resizable=1')"
                                   class="link-btn video-link" title="Ver video">
                                    🎥
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo $trimestre['orden']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $trimestre['activo'] ? 'active' : 'inactive'; ?>">
                                <?php echo $trimestre['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="btn btn-sm btn-primary" onclick="editTrimestre(<?php echo $trimestre['id']; ?>)" title="Editar">
                                ✏️
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTrimestre(<?php echo $trimestre['id']; ?>)" title="Eliminar">
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

<!-- Modal para crear/editar trimestre -->
<div id="trimestre-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title-section">
                <h3 id="modal-title">📅 Crear Nuevo Trimestre de Aray</h3>
                <p id="modal-subtitle">Completa la información del trimestre</p>
            </div>
            <button class="close-btn" onclick="closeTrimestreModal()" title="Cerrar">
                <span>&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="trimestre-form" method="POST" action="../api/aray-trimestres.php">
                <input type="hidden" id="trimestre-id" name="id" value="">
                <input type="hidden" name="action" value="create">
                
                <!-- ID del trimestre (más pequeño, al inicio) -->
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div class="form-group" style="max-width: 200px;">
                        <label for="trimestre-id-display">ID del Trimestre</label>
                        <input type="text" id="trimestre-id-display" readonly value="Nuevo trimestre" class="readonly-field readonly-field-small" tabindex="-1">
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">📝 Información Básica</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="trimestre-year-id">Año *</label>
                            <select id="trimestre-year-id" name="year_id" required>
                                <option value="">Seleccionar año</option>
                                <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year['id']; ?>"><?php echo $year['year']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="trimestre-trimestre">Número de Trimestre *</label>
                            <select id="trimestre-trimestre" name="trimestre" required>
                                <option value="">Seleccionar</option>
                                <option value="1">1er Trimestre</option>
                                <option value="2">2o Trimestre</option>
                                <option value="3">3r Trimestre</option>
                                <option value="4">4o Trimestre</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="trimestre-titulo">Título *</label>
                            <input type="text" id="trimestre-titulo" name="titulo" required placeholder="Ej: 1er Trimestre">
                        </div>
                        <div class="form-group">
                            <label for="trimestre-orden">Orden de Visualización</label>
                            <input type="number" id="trimestre-orden" name="orden" min="0" value="0" placeholder="0">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4 class="section-title">🔗 Enlaces</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="trimestre-url-fotos">URL de Fotos *</label>
                            <input type="url" id="trimestre-url-fotos" name="url_fotos" required placeholder="https://photos.app.goo.gl/...">
                            <small class="form-help">Enlace a Google Photos, Amazon Photos o Albums</small>
                        </div>
                        <div class="form-group">
                            <label for="trimestre-tipo-url-fotos">Tipo de Enlace *</label>
                            <select id="trimestre-tipo-url-fotos" name="tipo_url_fotos" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="google">Google Photos</option>
                                <option value="amazon">Amazon Photos</option>
                                <option value="albums">Albums</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="trimestre-url-video">URL de Video (opcional)</label>
                            <input type="text" id="trimestre-url-video" name="url_video" placeholder="../img/Videos/Aray/2024/2024 - aray - 1r trimestre.mp4">
                            <small class="form-help">Ruta relativa al video local o URL externa</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4 class="section-title">⚙️ Configuración</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="trimestre-activo" name="activo" checked>
                                <span class="checkmark"></span>
                                Trimestre activo (visible en la web)
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar Trimestre</button>
                    <button type="button" class="btn btn-secondary" onclick="closeTrimestreModal()">❌ Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php include '../includes/footer.php'; ?>
