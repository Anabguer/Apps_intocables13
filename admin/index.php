<?php
require_once '../includes/config.php';
require_once '../includes/paths.php';
require_once '../includes/auth.php';

// Verificar permisos de administrador o editor
requireAdminOrEdit();

$pageTitle = 'Panel de Administración - Intocables';
include '../includes/header.php';
?>

<script src="<?php echo JS_URL; ?>admin-main.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo JS_URL; ?>admin-users.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo JS_URL; ?>admin-homepage.js?v=<?php echo time(); ?>" defer></script>

<?php

// Obtener estadísticas
$stats = getAlbumStats();

// Obtener todos los álbumes para la tabla (incluyendo álbumes hijos)
$albums = getAllAlbums(['activo' => 1, 'include_children' => true]);
?>

<div class="page-title">
    <h1>PANEL DE ADMINISTRACIÓN</h1>
    <hr>
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary">← Volver al Inicio</a>
    </div>
</div>

<div class="container">
    <!-- Menú de navegación del panel -->
    <div class="admin-nav">
        <?php if (isAdmin()): ?>
        <div class="admin-nav-item active" data-section="albums">
            <span class="nav-icon">📸</span>
            <span class="nav-text">Álbumes</span>
        </div>
        <a href="montajes-tips.php" class="admin-nav-item">
            <span class="nav-icon">🎬</span>
            <span class="nav-text">Montajes & Tips</span>
        </a>
        <a href="aray-years.php" class="admin-nav-item">
            <span class="nav-icon">👶</span>
            <span class="nav-text">Aray - Años</span>
        </a>
        <a href="aray-trimestres.php" class="admin-nav-item">
            <span class="nav-icon">📅</span>
            <span class="nav-text">Aray - Trimestres</span>
        </a>
        <div class="admin-nav-item" data-section="users">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Usuarios</span>
        </div>
        <div class="admin-nav-item" data-section="homepage-elements">
            <span class="nav-icon">🏠</span>
            <span class="nav-text">Página Principal</span>
        </div>
        <div class="admin-nav-item" data-section="settings">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text">Configuración</span>
        </div>
        <div class="admin-nav-item" data-section="stats">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Estadísticas</span>
        </div>
        <?php endif; ?>
        
        <?php if (isEdit() || isAdmin()): ?>
        <div class="admin-nav-item" data-section="subir-fotos-alessandro">
            <span class="nav-icon">📷🎥</span>
            <span class="nav-text">Subir Fotos y Videos Alessandro</span>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isAdmin()): ?>
    <!-- Estadísticas -->
    <div class="stats-grid" id="stats-section">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total de Álbumes</div>
        </div>
        
        <?php foreach ($stats['por_tipo'] as $tipo): ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $tipo['count']; ?></div>
            <div class="stat-label"><?php echo ucfirst($tipo['tipo']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Sección de Álbumes -->
    <div class="admin-section" id="albums-section">
        <div class="section-header">
            <h2>Gestión de Álbumes</h2>
            <button class="btn btn-primary" onclick="showAlbumForm()">
                <span>➕</span> Nuevo Álbum
            </button>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="filter-group">
                    <label for="year-filter">Filtrar por año:</label>
                    <select id="year-filter" onchange="filterAlbums()">
                        <option value="">Todos los años</option>
                        <?php
                        $years = array_unique(array_column($albums, 'año'));
                        rsort($years);
                        foreach ($years as $year):
                            if ($year): // Solo mostrar años válidos
                        ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                        <option value="null">Navegación (sin año)</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search-filter">Buscar por nombre:</label>
                    <input type="text" id="search-filter" placeholder="Escribe parte del título..." onkeyup="filterAlbums()">
                </div>
                
                <div class="filter-group">
                    <button class="btn btn-secondary" onclick="clearAllFilters()">
                        <span>🔄</span> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de álbumes -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th class="sortable" data-column="id">ID <span class="sort-arrow">↕</span></th>
                        <th>Imagen</th>
                        <th class="sortable" data-column="titulo">Título <span class="sort-arrow">↕</span></th>
                        <th class="sortable" data-column="año">Año <span class="sort-arrow">↕</span></th>
                        <th class="sortable" data-column="orden">Orden <span class="sort-arrow">↕</span></th>
                        <th class="sortable" data-column="tipo">Tipo <span class="sort-arrow">↕</span></th>
                        <th class="sortable" data-column="es_pagina_intermedia">Intermedia <span class="sort-arrow">↕</span></th>
                        <th class="sortable" data-column="categoria">Categoría <span class="sort-arrow">↕</span></th>
                        <th class="sortable" data-column="activo">Estado <span class="sort-arrow">↕</span></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $currentYear = null;
                    foreach ($albums as $album): 
                        if ($album['año'] !== $currentYear):
                            $currentYear = $album['año'];
                    ?>
                    <tr class="year-separator">
                        <td colspan="10">
                            <strong>
                                <?php echo $album['año'] ? "Año {$album['año']}" : 'Navegación'; ?>
                            </strong>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="album-id">
                            <strong><?php echo $album['id']; ?></strong>
                        </td>
                        <td class="album-thumbnail">
                            <img src="<?php echo getImagePath(htmlspecialchars($album['imagen'])); ?>" 
                                 alt="<?php echo htmlspecialchars($album['titulo']); ?>"
                                 class="thumbnail"
                                 onerror="this.src='<?php echo getImagePath('/img/botones/portada_fotos_videos.png'); ?>'">
                        </td>
                        <td>
                            <div class="album-info">
                                <strong><?php echo htmlspecialchars($album['titulo']); ?></strong>
                                <?php if ($album['subtitulo']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($album['subtitulo']); ?></small>
                                <?php endif; ?>
                                <?php if ($album['album_padre_id']): ?>
                                <br><small class="text-info">Padre: <?php echo $album['album_padre_id']; ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo $album['año'] ?: '-'; ?></td>
                        <td class="album-order"><?php echo $album['orden']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo $album['tipo'] == 'photos' ? 'primary' : 'success'; ?>">
                                <?php echo ucfirst($album['tipo']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($album['es_pagina_intermedia']): ?>
                            <span class="badge badge-warning">SÍ</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">NO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo htmlspecialchars($album['categoria']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $album['activo'] ? 'success' : 'danger'; ?>">
                                <?php echo $album['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="btn btn-sm btn-primary" onclick="editAlbum(<?php echo $album['id']; ?>)" title="Editar">
                                ✏️
                            </button>
                            <?php if (!empty($album['video'])): ?>
                            <button class="btn btn-sm btn-success" onclick="window.open('<?php echo htmlspecialchars($album['video']); ?>', '_blank')" title="Ver Video">
                                🎥
                            </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteAlbum(<?php echo $album['id']; ?>)" title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal para formulario de álbum (solo para admin) -->
    <?php if (isAdmin()): ?>
    <div id="album-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-section">
                    <h3 id="modal-title">📸 Crear Nuevo Álbum</h3>
                    <p id="modal-subtitle">Completa la información del álbum</p>
                </div>
                <button class="close-btn" onclick="closeAlbumForm()" title="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="album-form" method="POST" action="../api/albums.php">
                    <input type="hidden" id="album-id" name="id" value="">
                    <input type="hidden" name="action" value="create">
                    
                    <!-- ID del álbum (más pequeño, al inicio) -->
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <div class="form-group" style="max-width: 200px;">
                            <label for="album-id-display">ID del Álbum</label>
                            <input type="text" id="album-id-display" readonly value="Nuevo álbum" class="readonly-field readonly-field-small" tabindex="-1">
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="section-title">📝 Información Básica</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="titulo">Título del Álbum *</label>
                                <input type="text" id="titulo" name="titulo" required placeholder="Ej: Fotos de Verano 2025">
                            </div>
                            <div class="form-group">
                                <label for="subtitulo">Subtítulo</label>
                                <input type="text" id="subtitulo" name="subtitulo" placeholder="Ej: Vacaciones en la playa">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="section-title">📅 Clasificación</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="año">Año</label>
                                <input type="number" id="año" name="año" min="1990" max="2030" placeholder="2025">
                            </div>
                            <div class="form-group">
                                <label for="tipo">Tipo de Contenido *</label>
                                <select id="tipo" name="tipo" required>
                                    <option value="">Selecciona el tipo</option>
                                    <option value="photos" selected>📸 Fotos</option>
                                    <option value="videos">🎥 Videos</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoria">Categoría</label>
                                <select id="categoria" name="categoria">
                                    <option value="general">General</option>
                                    <option value="vacaciones">Vacaciones</option>
                                    <option value="eventos">Eventos</option>
                                    <option value="familia">Familia</option>
                                    <option value="trabajo">Trabajo</option>
                                    <option value="deportes">Deportes</option>
                                    <option value="viajes">Viajes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="orden">Orden de Visualización</label>
                                <input type="number" id="orden" name="orden" value="1" min="1" placeholder="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="section-title">🔗 Enlaces y Recursos</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="imagen">Ruta de la Imagen *</label>
                                <input type="text" id="imagen" name="imagen" required placeholder="/img/botones/imagen.webp">
                                <small class="form-help">Ruta completa de la imagen desde la carpeta img/</small>
                            </div>
                            <div class="form-group">
                                <label for="enlace">Enlace del Álbum *</label>
                                <input type="text" id="enlace" name="enlace" required placeholder="https://ejemplo.com/album o # para páginas intermedias">
                                <small class="form-help">URL completa del álbum, galería o # para páginas intermedias</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="video">Enlace del Video</label>
                                <input type="text" id="video" name="video" placeholder="https://ejemplo.com/video">
                                <small class="form-help">URL del video (opcional)</small>
                            </div>
                            <div class="form-group">
                                <!-- Espacio vacío para mantener el layout -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="section-title">⚙️ Configuración</h4>
                        <div class="form-row">
                            <div class="form-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="es_pagina_intermedia" name="es_pagina_intermedia" value="1">
                                    <span class="checkmark"></span>
                                    <span class="checkbox-text">Es página intermedia (contiene otros álbumes)</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="album_padre_id">Álbum Padre (ID)</label>
                                <input type="number" id="album_padre_id" name="album_padre_id" placeholder="ID del álbum padre">
                                <small class="form-help">Solo si este álbum pertenece a una página intermedia</small>
                                <div id="parent-albums-list" class="parent-albums-list" style="display: none;">
                                    <small><strong>Páginas intermedias disponibles:</strong></small>
                                    <div id="parent-albums-options"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="activo" name="activo" value="1" checked>
                                <span class="checkmark"></span>
                                <span class="checkbox-text">Álbum activo (visible en la web)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeAlbumForm()">
                            <span>❌</span> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <span>💾</span> Crear Álbum
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal para formulario de usuario (solo para admin) -->
    <?php if (isAdmin()): ?>
    <div id="user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-section">
                    <h3 id="user-modal-title">👤 Crear Nuevo Usuario</h3>
                    <p id="user-modal-subtitle">Completa la información del usuario</p>
                </div>
                <button class="close-btn" onclick="closeUserForm()" title="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="user-form" method="POST" action="../api/users.php">
                    <input type="hidden" id="user-id" name="id" value="">
                    <input type="hidden" name="action" value="create">
                    
                    <!-- ID del usuario (más pequeño, al inicio) -->
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <div class="form-group" style="max-width: 200px;">
                            <label for="user-id-display">ID del Usuario</label>
                            <input type="text" id="user-id-display" readonly value="Nuevo usuario" class="readonly-field readonly-field-small" tabindex="-1">
                        </div>
                    </div>

                    <!-- Nombre y Apellidos -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-nombre">Nombre *</label>
                            <input type="text" id="user-nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="user-apellido1">Primer Apellido *</label>
                            <input type="text" id="user-apellido1" name="apellido_1" required>
                        </div>
                        <div class="form-group">
                            <label for="user-apellido2">Segundo Apellido</label>
                            <input type="text" id="user-apellido2" name="apellido_2">
                        </div>
                    </div>

                    <!-- Email y Contraseña -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-email">Email *</label>
                            <input type="email" id="user-email" name="correo" required>
                        </div>
                        <div class="form-group">
                            <label for="user-password">Contraseña <span id="password-required">*</span></label>
                            <input type="password" id="user-password" name="clave" required>
                            <small class="form-help" id="password-help">Mínimo 6 caracteres</small>
                        </div>
                    </div>

                    <!-- Perfil y Sexo -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-perfil">Perfil *</label>
                            <select id="user-perfil" name="perfil" required>
                                <option value="">Seleccionar perfil</option>
                                <option value="admin">Administrador</option>
                                <option value="user">Usuario</option>
                                <option value="edit">Editor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user-sexo">Sexo *</label>
                            <select id="user-sexo" name="sexo" required>
                                <option value="">Seleccionar sexo</option>
                                <option value="H">Hombre</option>
                                <option value="M">Mujer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Estado y Mensaje de bienvenida -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user-activo">Estado</label>
                            <select id="user-activo" name="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user-mensaje">Mensaje de Bienvenida</label>
                            <textarea id="user-mensaje" name="mensaje_bienvenida" rows="3" placeholder="Mensaje personalizado de bienvenida..."></textarea>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeUserForm()">
                            ❌ Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            💾 Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sección Subir Fotos y Videos Alessandro (para edit y admin) -->
    <?php if (isEdit() || isAdmin()): ?>
    <div class="admin-section" id="subir-fotos-alessandro-section">
        <h2>Gestionar Fotos y Videos Alessandro</h2>
        
        <!-- Subir archivos -->
        <div class="upload-section">
            <div class="upload-area">
                <div class="upload-icon">📷🎥</div>
                <h3>Subir Fotos y Videos de Alessandro</h3>
                <p>Arrastra y suelta los archivos aquí o haz clic para seleccionar fotos y videos</p>
                <input type="file" id="photo-upload" multiple accept="image/*,video/*" style="display: none;">
                <button class="btn btn-primary" onclick="document.getElementById('photo-upload').click()">
                    Seleccionar Fotos y Videos
                </button>
            </div>
            
            <div class="upload-progress" id="upload-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill"></div>
                </div>
                <div class="progress-text" id="progress-text">Subiendo archivos...</div>
            </div>
            
            <div class="upload-status" id="upload-status" style="display: none;">
                <div class="status-message" id="status-message"></div>
                <div class="upload-summary" id="upload-summary"></div>
            </div>
        </div>
        
        <!-- Gestión de archivos existentes -->
        <div class="files-management">
            <div class="files-header">
                <h3>Archivos Existentes</h3>
                <div class="files-actions">
                    <button class="btn btn-secondary" onclick="loadAlessandroFiles()" id="load-files-btn">
                        🔄 Cargar Archivos
                    </button>
                    <button class="btn btn-danger" onclick="deleteSelectedFiles()" id="delete-selected-btn" style="display: none;">
                        🗑️ Eliminar Seleccionados
                    </button>
                </div>
            </div>
            <div class="files-grid" id="files-grid">
                <!-- Los archivos se cargarán dinámicamente -->
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Otras secciones del panel (solo para admin) -->
    <?php if (isAdmin()): ?>
    <div class="admin-section" id="users-section" style="display: none;">
        <h2>Gestión de Usuarios</h2>
        
        <!-- Botón para crear nuevo usuario -->
        <div class="section-actions">
            <button class="btn btn-primary" onclick="showUserForm()">
                ➕ Nuevo Usuario
            </button>
        </div>
        
        <!-- Tabla de usuarios -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="sortable" data-column="id">ID</th>
                        <th class="sortable" data-column="nombre">Nombre</th>
                        <th class="sortable" data-column="correo">Email</th>
                        <th class="sortable" data-column="perfil">Perfil</th>
                        <th class="sortable" data-column="sexo">Sexo</th>
                        <th class="sortable" data-column="activo">Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- Los usuarios se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-section" id="homepage-elements-section" style="display: none;">
        <h2>Elementos de Página Principal</h2>
        
        <!-- Botón para agregar nuevo elemento -->
        <div class="admin-actions">
            <button class="btn btn-primary" onclick="showHomepageElementForm()">
                ➕ Nuevo Elemento
            </button>
        </div>
        
        <!-- Tabla de elementos -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Enlace</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="homepage-elements-table-body">
                    <!-- Los elementos se cargarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-section" id="settings-section" style="display: none;">
        <h2>Configuración del Sistema</h2>
        
        <!-- Formulario de configuración -->
        <div class="settings-container">
            <form id="settings-form" method="POST" action="../api/settings.php">
                <div class="settings-grid">
                    
                    <!-- Configuración General -->
                    <div class="settings-group">
                        <h3>🌐 Configuración General</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="site-name">Nombre del Sitio *</label>
                                <input type="text" id="site-name" name="site_name" required>
                            </div>
                            <div class="form-group">
                                <label for="site-url">URL del Sitio *</label>
                                <input type="url" id="site-url" name="site_url" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="admin-email">Email del Administrador *</label>
                                <input type="email" id="admin-email" name="admin_email" required>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Zona Horaria *</label>
                                <select id="timezone" name="timezone" required>
                                    <option value="Europe/Madrid">Europe/Madrid</option>
                                    <option value="Europe/London">Europe/London</option>
                                    <option value="America/New_York">America/New_York</option>
                                    <option value="America/Los_Angeles">America/Los_Angeles</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración de Archivos -->
                    <div class="settings-group">
                        <h3>📁 Configuración de Archivos</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="max-file-size">Tamaño Máximo de Archivo (MB) *</label>
                                <input type="number" id="max-file-size" name="max_file_size" min="1" max="100" required>
                                <small class="form-help">Tamaño máximo para subir fotos y videos</small>
                            </div>
                            <div class="form-group">
                                <label for="allowed-extensions">Extensiones Permitidas *</label>
                                <input type="text" id="allowed-extensions" name="allowed_extensions" required>
                                <small class="form-help">Separadas por comas (ej: jpg,png,mp4)</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="thumbnail-size">Tamaño de Miniaturas (px) *</label>
                                <input type="number" id="thumbnail-size" name="thumbnail_size" min="100" max="500" required>
                                <small class="form-help">Tamaño para generar miniaturas</small>
                            </div>
                            <div class="form-group">
                                <label for="image-quality">Calidad de Imagen (%) *</label>
                                <input type="number" id="image-quality" name="image_quality" min="50" max="100" required>
                                <small class="form-help">Calidad de compresión de imágenes</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración de Seguridad -->
                    <div class="settings-group">
                        <h3>🔒 Configuración de Seguridad</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="session-timeout">Tiempo de Sesión (minutos) *</label>
                                <input type="number" id="session-timeout" name="session_timeout" min="15" max="480" required>
                                <small class="form-help">Tiempo antes de cerrar sesión automáticamente</small>
                            </div>
                            <div class="form-group">
                                <label for="max-login-attempts">Intentos de Login Máximos *</label>
                                <input type="number" id="max-login-attempts" name="max_login_attempts" min="3" max="10" required>
                                <small class="form-help">Intentos antes de bloquear temporalmente</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password-min-length">Longitud Mínima de Contraseña *</label>
                                <input type="number" id="password-min-length" name="password_min_length" min="6" max="20" required>
                                <small class="form-help">Longitud mínima para contraseñas de usuarios</small>
                            </div>
                            <div class="form-group">
                                <label for="require-strong-passwords">Contraseñas Fuertes</label>
                                <select id="require-strong-passwords" name="require_strong_passwords">
                                    <option value="0">No requerir</option>
                                    <option value="1">Requerir (letras, números, símbolos)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración de Notificaciones -->
                    <div class="settings-group">
                        <h3>📧 Configuración de Notificaciones</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email-notifications">Notificaciones por Email</label>
                                <select id="email-notifications" name="email_notifications">
                                    <option value="1">Activadas</option>
                                    <option value="0">Desactivadas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="smtp-server">Servidor SMTP</label>
                                <input type="text" id="smtp-server" name="smtp_server" placeholder="smtp.gmail.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp-port">Puerto SMTP</label>
                                <input type="number" id="smtp-port" name="smtp_port" placeholder="587">
                            </div>
                            <div class="form-group">
                                <label for="smtp-username">Usuario SMTP</label>
                                <input type="text" id="smtp-username" name="smtp_username">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp-password">Contraseña SMTP</label>
                                <input type="password" id="smtp-password" name="smtp_password" placeholder="Contraseña de aplicación">
                                <small class="form-help">Para Gmail, usa una contraseña de aplicación</small>
                            </div>
                            <div class="form-group">
                                <label for="from-email">Email de Envío *</label>
                                <input type="email" id="from-email" name="from_email" required placeholder="noreply@tudominio.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="from-name">Nombre del Remitente *</label>
                                <input type="text" id="from-name" name="from_name" required placeholder="Nombre de tu sitio">
                            </div>
                            <div class="form-group">
                                <!-- Campo vacío para mantener el layout -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración de Galería -->
                    <div class="settings-group">
                        <h3>🖼️ Configuración de Galería</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="items-per-page">Elementos por Página *</label>
                                <input type="number" id="items-per-page" name="items_per_page" min="10" max="100" required>
                                <small class="form-help">Número de elementos a mostrar por página</small>
                            </div>
                            <div class="form-group">
                                <label for="gallery-layout">Diseño de Galería</label>
                                <select id="gallery-layout" name="gallery_layout">
                                    <option value="grid">Cuadrícula</option>
                                    <option value="list">Lista</option>
                                    <option value="masonry">Masonry</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="enable-zoom">Habilitar Zoom en Imágenes</label>
                                <select id="enable-zoom" name="enable_zoom">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="auto-play-videos">Reproducción Automática de Videos</label>
                                <select id="auto-play-videos" name="auto_play_videos">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Botones de acción -->
                <div class="settings-actions">
                    <button type="button" class="btn btn-secondary" onclick="resetSettings()">
                        🔄 Restaurar Valores por Defecto
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para elementos de página principal -->
    <div id="homepage-element-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-section">
                    <h3 id="homepage-element-modal-title">🏠 Nuevo Elemento</h3>
                    <p id="homepage-element-modal-subtitle">Configura el elemento de la página principal</p>
                </div>
                <button class="close-btn" onclick="closeHomepageElementForm()" title="Cerrar">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="homepage-element-form">
                    <input type="hidden" id="homepage-element-id" name="id">
                    <input type="hidden" name="action" value="create">
                    
                    <!-- ID del elemento (más pequeño, al inicio) -->
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <div class="form-group" style="max-width: 200px;">
                            <label for="homepage-element-id-display">ID del Elemento</label>
                            <input type="text" id="homepage-element-id-display" readonly value="Nuevo elemento" class="readonly-field readonly-field-small" tabindex="-1">
                        </div>
                    </div>

                    <div class="form-section">
                        <h4 class="section-title">📝 Información Básica</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="homepage-element-titulo">Título *</label>
                                <input type="text" id="homepage-element-titulo" name="titulo" required>
                            </div>
                            <div class="form-group">
                                <label for="homepage-element-orden">Orden *</label>
                                <input type="number" id="homepage-element-orden" name="orden" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="homepage-element-descripcion">Descripción</label>
                                <textarea id="homepage-element-descripcion" name="descripcion" rows="3" placeholder="Descripción opcional del elemento"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="section-title">🔗 Enlaces y Recursos</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="homepage-element-imagen">Ruta de Imagen *</label>
                                <input type="text" id="homepage-element-imagen" name="imagen" required placeholder="/img/botones/fotos_videos.png">
                                <small class="form-help">Ruta relativa desde la raíz del sitio</small>
                            </div>
                            <div class="form-group">
                                <label for="homepage-element-enlace">Enlace *</label>
                                <input type="text" id="homepage-element-enlace" name="enlace" required placeholder="/fotos-videos/ o URL externa">
                                <small class="form-help">Ruta relativa o URL completa</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="section-title">⚙️ Configuración</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="homepage-element-activo">Estado</label>
                                <select id="homepage-element-activo" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeHomepageElementForm()">
                            <span>❌</span> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="homepage-submit-btn">
                            <span>💾</span> Crear Elemento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
