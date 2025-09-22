// JavaScript principal para el panel de administración

// Variables globales para ordenación
let currentSortColumn = null;
let currentSortDirection = 'asc';

// Variables globales para filtros
let savedFilters = {
    year: '',
    search: ''
};

// Variables globales para la selección de archivos
let selectedFiles = new Set();

// Navegación del panel
document.querySelectorAll('.admin-nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
        // Si es un enlace, no hacer nada más (dejar que navegue)
        if (this.tagName === 'A') {
            return;
        }
        
        e.preventDefault();
        const section = this.getAttribute('data-section');
        
        // Remover clase active de todos los items
        document.querySelectorAll('.admin-nav-item').forEach(nav => nav.classList.remove('active'));
        // Agregar clase active al item clickeado
        this.classList.add('active');
        
        // Ocultar todas las secciones
        document.querySelectorAll('.admin-section').forEach(sec => sec.style.display = 'none');
        
        // Mostrar la sección correspondiente
        if (section === 'stats') {
            const statsSection = document.getElementById('stats-section');
            if (statsSection) {
                statsSection.style.display = 'block';
            }
        } else {
            const targetSection = document.getElementById(section + '-section');
            if (targetSection) {
                targetSection.style.display = 'block';
            }
        }
    });
});

// Evento para manejar páginas intermedias (solo para admin)
const esPaginaIntermediaField = document.getElementById('es_pagina_intermedia');
if (esPaginaIntermediaField) {
    esPaginaIntermediaField.addEventListener('change', function() {
        const enlaceField = document.getElementById('enlace');
        
        if (this.checked) {
            // Si se marca como página intermedia, configurar enlace como #
            enlaceField.value = '#';
            enlaceField.placeholder = '# (para páginas intermedias)';
        } else {
            // Si se desmarca, restaurar placeholder normal
            enlaceField.placeholder = 'https://ejemplo.com/album o # para páginas intermedias';
        }
    });
}

// Evento para mostrar lista de páginas intermedias (solo para admin)
const albumPadreIdField = document.getElementById('album_padre_id');
if (albumPadreIdField) {
    albumPadreIdField.addEventListener('focus', function() {
        loadParentAlbums();
    });
}

// Función para cargar páginas intermedias
function loadParentAlbums() {
    const listContainer = document.getElementById('parent-albums-list');
    const optionsContainer = document.getElementById('parent-albums-options');
    
    // Mostrar lista
    listContainer.style.display = 'block';
    
    // Si ya está cargada, no volver a cargar
    if (optionsContainer.children.length > 0) {
        return;
    }
    
    // Cargar páginas intermedias desde la API
    fetch('../api/albums.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const intermediateAlbums = data.data.filter(album => album.es_pagina_intermedia == 1);
                
                if (intermediateAlbums.length === 0) {
                    optionsContainer.innerHTML = '<small style="color: #6c757d; font-style: italic;">No hay páginas intermedias disponibles</small>';
                } else {
                    intermediateAlbums.forEach(album => {
                        const option = document.createElement('div');
                        option.className = 'parent-albums-option';
                        option.textContent = `ID: ${album.id} - ${album.titulo}`;
                        option.onclick = () => {
                            document.getElementById('album_padre_id').value = album.id;
                            listContainer.style.display = 'none';
                        };
                        optionsContainer.appendChild(option);
                    });
                }
            }
        })
        .catch(error => {
            optionsContainer.innerHTML = '<small style="color: #dc3545;">Error al cargar páginas intermedias</small>';
        });
}

// Ocultar lista cuando se hace click fuera
document.addEventListener('click', function(e) {
    const parentField = document.getElementById('album_padre_id');
    const listContainer = document.getElementById('parent-albums-list');
    
    if (parentField && listContainer && !parentField.contains(e.target) && !listContainer.contains(e.target)) {
        listContainer.style.display = 'none';
    }
});

// Funciones del modal de álbumes
function showAlbumForm() {
    // Guardar filtros actuales antes de crear
    saveFiltersToStorage();
    
    // Resetear el campo de ID para nuevo álbum
    document.getElementById('album-id-display').value = 'Nuevo álbum';
    
    // Establecer valores por defecto
    document.getElementById('tipo').value = 'photos';
    document.getElementById('categoria').value = 'general';
    
    document.getElementById('album-modal').style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeAlbumForm() {
    document.getElementById('album-modal').style.display = 'none';
    document.body.classList.remove('modal-open');
    resetForm(document.getElementById('album-form'));
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('album-modal');
    if (event.target === modal) {
        closeAlbumForm();
    }
}

// Función para editar álbum
function editAlbum(id) {
    // Guardar filtros actuales antes de editar
    saveFiltersToStorage();
    
    fetch(`../api/albums.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const album = data.data;
                
                // Llenar formulario
                document.getElementById('album-id').value = album.id;
                document.getElementById('album-id-display').value = `ID: ${album.id}`;
                document.getElementById('titulo').value = album.titulo;
                document.getElementById('subtitulo').value = album.subtitulo;
                document.getElementById('año').value = album.año || '';
                document.getElementById('tipo').value = album.tipo;
                document.getElementById('imagen').value = album.imagen;
                document.getElementById('enlace').value = album.enlace;
                document.getElementById('video').value = album.video || '';
                document.getElementById('categoria').value = album.categoria;
                document.getElementById('orden').value = album.orden;
                document.getElementById('es_pagina_intermedia').checked = album.es_pagina_intermedia == 1;
                document.getElementById('album_padre_id').value = album.album_padre_id || '';
                document.getElementById('activo').checked = album.activo == 1;
                
                // Configurar enlace para páginas intermedias
                if (album.es_pagina_intermedia == 1 && album.enlace === '#') {
                    document.getElementById('enlace').value = '#';
                }
                
                // Cambiar título y acción
                document.getElementById('modal-title').textContent = '✏️ Editar Álbum';
                document.getElementById('modal-subtitle').textContent = 'Modifica la información del álbum';
                document.getElementById('submit-btn').innerHTML = '<span>💾</span> Actualizar Álbum';
                document.querySelector('input[name="action"]').value = 'update';
                
                // Mostrar modal
                document.getElementById('album-modal').style.display = 'block';
                document.body.classList.add('modal-open');
            } else {
                showAlert('Error al cargar el álbum', 'error');
            }
        })
        .catch(error => {
            showAlert('Error de conexión', 'error');
        });
}

// Función para eliminar álbum
function deleteAlbum(id) {
    if (confirmDelete('¿Estás seguro de que quieres eliminar este álbum?')) {
        fetch(`../api/albums.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id, _method: 'DELETE' })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('Álbum eliminado correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                console.error('Error del servidor:', data);
                showAlert(`Error al eliminar el álbum: ${data.error}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            showAlert(`Error de conexión: ${error.message}`, 'error');
        });
    }
}

// Función para resetear formulario
function resetForm(form) {
    form.reset();
    document.getElementById('modal-title').textContent = '📸 Crear Nuevo Álbum';
    document.getElementById('modal-subtitle').textContent = 'Completa la información del álbum';
    document.getElementById('submit-btn').innerHTML = '<span>💾</span> Crear Álbum';
    document.querySelector('input[name="action"]').value = 'create';
    document.getElementById('album-id').value = '';
    document.getElementById('album-id-display').value = 'Nuevo álbum';
    
    // Establecer valores por defecto
    document.getElementById('tipo').value = 'photos';
    document.getElementById('categoria').value = 'general';
    
    const fields = form.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        field.style.borderColor = '#e1e8ed';
        field.style.background = '#f8f9fa';
    });
}

// Funciones para manejar filtros persistentes
function saveCurrentFilters() {
    savedFilters.year = document.getElementById('year-filter').value;
    savedFilters.search = document.getElementById('search-filter').value;
}

function restoreFilters() {
    document.getElementById('year-filter').value = savedFilters.year;
    document.getElementById('search-filter').value = savedFilters.search;
    if (savedFilters.year || savedFilters.search) {
        filterAlbums();
    }
}

function clearAllFilters() {
    savedFilters.year = '';
    savedFilters.search = '';
    
    // Limpiar localStorage
    localStorage.removeItem('albumFilterYear');
    localStorage.removeItem('albumFilterSearch');
    
    clearFilters();
}

// Función para recargar la tabla manteniendo filtros
function reloadTableWithFilters() {
    // Guardar filtros actuales
    saveCurrentFilters();
    
    // Recargar la página
    location.reload();
}

// Función para restaurar filtros al cargar la página
function restoreFiltersOnLoad() {
    // Restaurar filtros guardados en localStorage
    const savedYear = localStorage.getItem('albumFilterYear');
    const savedSearch = localStorage.getItem('albumFilterSearch');
    
    if (savedYear || savedSearch) {
        document.getElementById('year-filter').value = savedYear || '';
        document.getElementById('search-filter').value = savedSearch || '';
        filterAlbums();
    }
}

// Función para guardar filtros en localStorage
function saveFiltersToStorage() {
    const year = document.getElementById('year-filter').value;
    const search = document.getElementById('search-filter').value;
    
    if (year) {
        localStorage.setItem('albumFilterYear', year);
    } else {
        localStorage.removeItem('albumFilterYear');
    }
    
    if (search) {
        localStorage.setItem('albumFilterSearch', search);
    } else {
        localStorage.removeItem('albumFilterSearch');
    }
}

// Funciones de ordenación
function initSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            sortTable(column);
        });
    });
}

function sortTable(column) {
    const table = document.querySelector('.admin-table tbody');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    // Determinar dirección de ordenación
    if (currentSortColumn === column) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortDirection = 'asc';
    }
    currentSortColumn = column;
    
    // Actualizar indicadores visuales
    updateSortIndicators(column, currentSortDirection);
    
    // Filtrar solo las filas de álbumes (no separadores)
    const albumRows = rows.filter(row => !row.classList.contains('year-separator') && row.querySelectorAll('td').length > 0);
    
    // Ordenar las filas
    albumRows.sort((a, b) => {
        const cellsA = a.querySelectorAll('td');
        const cellsB = b.querySelectorAll('td');
        
        let valueA, valueB;
        
        // Obtener valores según la columna
        switch(column) {
            case 'id':
                valueA = parseInt(cellsA[0].textContent.trim());
                valueB = parseInt(cellsB[0].textContent.trim());
                break;
            case 'titulo':
                valueA = cellsA[2].textContent.trim().toLowerCase();
                valueB = cellsB[2].textContent.trim().toLowerCase();
                break;
            case 'año':
                valueA = cellsA[3].textContent.trim() || '0';
                valueB = cellsB[3].textContent.trim() || '0';
                valueA = valueA === '-' ? '0' : valueA;
                valueB = valueB === '-' ? '0' : valueB;
                valueA = parseInt(valueA);
                valueB = parseInt(valueB);
                break;
            case 'orden':
                valueA = parseInt(cellsA[4].textContent.trim()) || 0;
                valueB = parseInt(cellsB[4].textContent.trim()) || 0;
                break;
            case 'tipo':
                valueA = cellsA[5].textContent.trim().toLowerCase();
                valueB = cellsB[5].textContent.trim().toLowerCase();
                break;
            case 'es_pagina_intermedia':
                valueA = cellsA[6].textContent.trim() === 'SÍ' ? 1 : 0;
                valueB = cellsB[6].textContent.trim() === 'SÍ' ? 1 : 0;
                break;
            case 'categoria':
                valueA = cellsA[7].textContent.trim().toLowerCase();
                valueB = cellsB[7].textContent.trim().toLowerCase();
                break;
            case 'activo':
                valueA = cellsA[8].textContent.trim() === 'Activo' ? 1 : 0;
                valueB = cellsB[8].textContent.trim() === 'Activo' ? 1 : 0;
                break;
            default:
                return 0;
        }
        
        // Comparar valores
        if (valueA < valueB) {
            return currentSortDirection === 'asc' ? -1 : 1;
        }
        if (valueA > valueB) {
            return currentSortDirection === 'asc' ? 1 : -1;
        }
        return 0;
    });
    
    // Reorganizar las filas en el DOM
    const allRows = Array.from(table.querySelectorAll('tr'));
    const yearSeparators = allRows.filter(row => row.classList.contains('year-separator'));
    
    // Limpiar la tabla
    table.innerHTML = '';
    
    // Reinsertar las filas ordenadas
    albumRows.forEach(row => {
        table.appendChild(row);
    });
}

function updateSortIndicators(column, direction) {
    // Limpiar todos los indicadores
    document.querySelectorAll('.sortable').forEach(header => {
        header.classList.remove('sorted-asc', 'sorted-desc');
    });
    
    // Añadir indicador a la columna actual
    const currentHeader = document.querySelector(`[data-column="${column}"]`);
    if (currentHeader) {
        currentHeader.classList.add(direction === 'asc' ? 'sorted-asc' : 'sorted-desc');
    }
}

// Funciones de filtrado
function filterAlbums() {
    const yearFilter = document.getElementById('year-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();
    
    // Guardar filtros en localStorage
    saveFiltersToStorage();
    const table = document.querySelector('.admin-table tbody');
    const rows = table.querySelectorAll('tr');
    
    let visibleCount = 0;
    let yearSeparators = new Map(); // Para rastrear qué años tienen álbumes visibles
    
    // Primera pasada: filtrar álbumes y rastrear años visibles
    rows.forEach(row => {
        if (row.classList.contains('year-separator')) {
            return; // Las procesaremos en la segunda pasada
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const title = cells[2].textContent.toLowerCase();
        const yearCell = cells[3].textContent;
        
        let showRow = true;
        
        // Filtro por año
        if (yearFilter) {
            if (yearFilter === 'null') {
                showRow = showRow && (!yearCell || yearCell.trim() === '');
            } else {
                showRow = showRow && yearCell.includes(yearFilter);
            }
        }
        
        // Filtro por búsqueda
        if (searchFilter) {
            showRow = showRow && title.includes(searchFilter);
        }
        
        if (showRow) {
            row.style.display = '';
            visibleCount++;
            
            // Rastrear que este año tiene álbumes visibles
            if (yearCell && yearCell.trim() !== '') {
                yearSeparators.set(yearCell.trim(), true);
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Segunda pasada: mostrar/ocultar separadores de año
    rows.forEach(row => {
        if (row.classList.contains('year-separator')) {
            const yearText = row.textContent.trim();
            const yearMatch = yearText.match(/Año (\d+)/);
            
            if (yearMatch) {
                const year = yearMatch[1];
                if (yearSeparators.has(year)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            } else if (yearText === 'Navegación') {
                // Mostrar navegación si hay álbumes sin año
                if (yearSeparators.has('')) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });
    
    // Mostrar mensaje si no hay resultados
    showFilterResults(visibleCount);
}

function clearFilters() {
    document.getElementById('year-filter').value = '';
    document.getElementById('search-filter').value = '';
    
    // Mostrar todas las filas
    const table = document.querySelector('.admin-table tbody');
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        row.style.display = '';
    });
    
    // Ocultar mensaje de resultados
    const resultsMsg = document.getElementById('filter-results');
    if (resultsMsg) {
        resultsMsg.remove();
    }
}

function showFilterResults(count) {
    // Remover mensaje anterior si existe
    const existingMsg = document.getElementById('filter-results');
    if (existingMsg) {
        existingMsg.remove();
    }
    
    if (count === 0) {
        const table = document.querySelector('.admin-table');
        const resultsMsg = document.createElement('div');
        resultsMsg.id = 'filter-results';
        resultsMsg.style.cssText = `
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 1rem 0;
        `;
        resultsMsg.textContent = 'No se encontraron álbumes con los filtros aplicados';
        table.appendChild(resultsMsg);
    }
}

// Funcionalidad de subir fotos Alessandro
function initPhotoUpload() {
    const uploadArea = document.querySelector('.upload-area');
    const fileInput = document.getElementById('photo-upload');
    const progressDiv = document.getElementById('upload-progress');
    const progressFill = document.getElementById('progress-fill');
    const progressText = document.getElementById('progress-text');
    const uploadStatus = document.getElementById('upload-status');
    const statusMessage = document.getElementById('status-message');
    const uploadSummary = document.getElementById('upload-summary');

    if (!uploadArea) return;

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Click to upload
    uploadArea.addEventListener('click', function(e) {
        // Evitar que se active si se hace click en el botón
        if (e.target.tagName !== 'BUTTON') {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFiles(e.target.files);
            // Limpiar el input para permitir seleccionar los mismos archivos
            e.target.value = '';
        }
    });

    function handleFiles(files) {
        if (files.length === 0) return;

        // Mostrar progreso
        progressDiv.style.display = 'block';
        uploadStatus.classList.remove('show');

        let uploadedCount = 0;
        let successCount = 0;
        let errorCount = 0;
        const totalFiles = files.length;

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                uploadFile(file, index, totalFiles);
            }
        });

        function uploadFile(file, index, total) {
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('upload_type', 'alessandro');

            fetch('../api/upload-photos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadedCount++;
                updateProgress(uploadedCount, total);
                
                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                uploadedCount++;
                updateProgress(uploadedCount, total);
                errorCount++;
            });
        }

        function updateProgress(current, total) {
            const percentage = (current / total) * 100;
            progressFill.style.width = percentage + '%';
            progressText.textContent = `Subiendo archivos... ${current}/${total}`;
            
            if (current === total) {
                setTimeout(() => {
                    progressDiv.style.display = 'none';
                    showUploadSummary();
                }, 1000);
            }
        }

        function showUploadSummary() {
            uploadStatus.classList.add('show');
            
            if (successCount > 0 && errorCount === 0) {
                statusMessage.innerHTML = '✅ <strong>Subida completada exitosamente</strong>';
                statusMessage.style.color = '#28a745';
            } else if (successCount > 0 && errorCount > 0) {
                statusMessage.innerHTML = '⚠️ <strong>Subida completada con errores</strong>';
                statusMessage.style.color = '#ffc107';
            } else {
                statusMessage.innerHTML = '❌ <strong>Error en la subida</strong>';
                statusMessage.style.color = '#dc3545';
            }
            
            uploadSummary.innerHTML = `
                <p><strong>Archivos subidos:</strong> ${successCount}</p>
                <p><strong>Errores:</strong> ${errorCount}</p>
                <p><strong>Total procesados:</strong> ${successCount + errorCount}</p>
            `;
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                uploadStatus.classList.remove('show');
                uploadArea.style.display = 'block';
                // Limpiar contadores
                successCount = 0;
                errorCount = 0;
                uploadedCount = 0;
            }, 5000);
        }
    }
}

// Funciones para gestionar archivos de Alessandro
function loadAlessandroFiles() {
    const filesGrid = document.getElementById('files-grid');
    if (!filesGrid) return;
    
    // Limpiar selección anterior
    selectedFiles.clear();
    const deleteBtn = document.getElementById('delete-selected-btn');
    deleteBtn.style.display = 'none';
    
    // Mostrar indicador de carga
    const loadBtn = document.getElementById('load-files-btn');
    loadBtn.disabled = true;
    loadBtn.textContent = '🔄 Cargando...';
    
    fetch('../api/get-alessandro-files.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFiles(data.files);
            } else {
                filesGrid.innerHTML = '<div class="empty-files"><div class="icon">📁</div><p>Error al cargar archivos</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            filesGrid.innerHTML = '<div class="empty-files"><div class="icon">❌</div><p>Error al cargar archivos</p></div>';
        })
        .finally(() => {
            // Rehabilitar botón
            loadBtn.disabled = false;
            loadBtn.textContent = '🔄 Cargar Archivos';
        });
}

function displayFiles(files) {
    const filesGrid = document.getElementById('files-grid');
    
    if (files.length === 0) {
        filesGrid.innerHTML = '<div class="empty-files"><div class="icon">📁</div><p>No hay archivos subidos</p></div>';
        return;
    }
    
    filesGrid.innerHTML = files.map(file => {
        const isVideo = file.kind === 'video';
        const fileType = isVideo ? 'Video' : 'Imagen';
        const fileIcon = isVideo ? '🎥' : '📷';
        
        return `
            <div class="file-item" data-filename="${file.name}">
                <input type="checkbox" class="file-checkbox" onchange="toggleFileSelection('${file.name}')">
                <div class="file-actions">
                    <button class="file-action-btn view" onclick="viewFile('${file.url}')" title="Ver archivo">
                        👁️
                    </button>
                </div>
                ${isVideo ? 
                    `<video muted loop><source src="${file.url}" type="video/mp4"></video>` :
                    `<img src="${file.url}" alt="">`
                }
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-type">${fileIcon} ${fileType}</div>
                </div>
            </div>
        `;
    }).join('');
}

function viewFile(url) {
    window.open(url, '_blank');
}

function hideFilesList() {
    const filesGrid = document.getElementById('files-grid');
    const deleteBtn = document.getElementById('delete-selected-btn');
    
    // Limpiar selección
    selectedFiles.clear();
    deleteBtn.style.display = 'none';
    
    // Mostrar mensaje de que la lista se ha ocultado
    filesGrid.innerHTML = `
        <div class="empty-files">
            <div class="icon">✅</div>
            <p>Archivos eliminados correctamente</p>
            <p><small>Haz clic en "Cargar Archivos" para ver la lista actualizada</small></p>
        </div>
    `;
}

function toggleFileSelection(filename) {
    const fileItem = document.querySelector(`[data-filename="${filename}"]`);
    const checkbox = fileItem.querySelector('.file-checkbox');
    
    if (checkbox.checked) {
        selectedFiles.add(filename);
        fileItem.classList.add('selected');
    } else {
        selectedFiles.delete(filename);
        fileItem.classList.remove('selected');
    }
    
    // Mostrar/ocultar botón de eliminar seleccionados
    const deleteBtn = document.getElementById('delete-selected-btn');
    if (selectedFiles.size > 0) {
        deleteBtn.style.display = 'inline-block';
        deleteBtn.textContent = `🗑️ Eliminar Seleccionados (${selectedFiles.size})`;
    } else {
        deleteBtn.style.display = 'none';
    }
}

function deleteSelectedFiles() {
    if (selectedFiles.size === 0) {
        showMessage('No hay archivos seleccionados', 'error');
        return;
    }
    
    const fileList = Array.from(selectedFiles).join(', ');
    if (!confirm(`¿Estás seguro de que quieres eliminar ${selectedFiles.size} archivo(s)?\n\n${fileList}`)) {
        return;
    }
    
    // Deshabilitar botones durante la eliminación
    const deleteBtn = document.getElementById('delete-selected-btn');
    const loadBtn = document.getElementById('load-files-btn');
    deleteBtn.disabled = true;
    loadBtn.disabled = true;
    deleteBtn.textContent = '🗑️ Eliminando...';
    
    // Eliminar archivos uno por uno
    const deletePromises = Array.from(selectedFiles).map(filename => {
        return fetch('../api/delete-alessandro-file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ filename: filename })
        }).then(response => response.json());
    });
    
    Promise.all(deletePromises)
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            const errorCount = results.length - successCount;
            
            if (successCount > 0) {
                showMessage(`${successCount} archivo(s) eliminado(s) correctamente`, 'success');
                // Ocultar la lista de archivos después de eliminar
                hideFilesList();
            }
            
            if (errorCount > 0) {
                showMessage(`${errorCount} archivo(s) no se pudieron eliminar`, 'error');
            }
            
            // Limpiar selección
            selectedFiles.clear();
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error al eliminar archivos', 'error');
        })
        .finally(() => {
            // Rehabilitar botones
            deleteBtn.disabled = false;
            loadBtn.disabled = false;
            deleteBtn.style.display = 'none';
        });
}

function showMessage(message, type) {
    // Crear un mensaje temporal
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transition: all 0.3s ease;
        ${type === 'success' ? 'background: #28a745;' : 'background: #dc3545;'}
    `;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
}

// Funciones para gestión de configuración
function loadSettings() {
    fetch('../api/settings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateSettingsForm(data.settings);
            } else {
                showAlert('Error al cargar configuración', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function populateSettingsForm(settings) {
    // Configuración General
    document.getElementById('site-name').value = settings.site_name || '';
    document.getElementById('site-url').value = settings.site_url || '';
    document.getElementById('admin-email').value = settings.admin_email || '';
    document.getElementById('timezone').value = settings.timezone || 'Europe/Madrid';
    
    // Configuración de Archivos
    document.getElementById('max-file-size').value = settings.max_file_size || 10;
    document.getElementById('allowed-extensions').value = settings.allowed_extensions || 'jpg,jpeg,png,gif,webp,mp4,mov,webm';
    document.getElementById('thumbnail-size').value = settings.thumbnail_size || 200;
    document.getElementById('image-quality').value = settings.image_quality || 85;
    
    // Configuración de Seguridad
    document.getElementById('session-timeout').value = settings.session_timeout || 120;
    document.getElementById('max-login-attempts').value = settings.max_login_attempts || 5;
    document.getElementById('password-min-length').value = settings.password_min_length || 6;
    document.getElementById('require-strong-passwords').value = settings.require_strong_passwords || '0';
    
    // Configuración de Notificaciones
    document.getElementById('email-notifications').value = settings.email_notifications || '1';
    document.getElementById('smtp-server').value = settings.smtp_server || '';
    document.getElementById('smtp-port').value = settings.smtp_port || '';
    document.getElementById('smtp-username').value = settings.smtp_username || '';
    document.getElementById('smtp-password').value = settings.smtp_password || '';
    document.getElementById('from-email').value = settings.from_email || '';
    document.getElementById('from-name').value = settings.from_name || '';
    
    // Configuración de Galería
    document.getElementById('items-per-page').value = settings.items_per_page || 20;
    document.getElementById('gallery-layout').value = settings.gallery_layout || 'grid';
    document.getElementById('enable-zoom').value = settings.enable_zoom || '1';
    document.getElementById('auto-play-videos').value = settings.auto_play_videos || '1';
}

function resetSettings() {
    if (confirm('¿Estás seguro de que quieres restaurar los valores por defecto? Se perderán todos los cambios no guardados.')) {
        // Valores por defecto (coinciden con config.php)
        const defaultSettings = {
            site_name: 'Intocables',
            site_url: window.APP_CONFIG ? window.APP_CONFIG.SITE_URL : window.location.origin,
            admin_email: 'admin@intocables.com',
            timezone: 'Europe/Madrid',
            max_file_size: 10,
            allowed_extensions: 'jpg,jpeg,png,gif,webp,mp4,mov,webm',
            thumbnail_size: 200,
            image_quality: 85,
            session_timeout: 120,
            max_login_attempts: 5,
            password_min_length: 6,
            require_strong_passwords: '0',
            email_notifications: '1',
            smtp_server: '',
            smtp_port: 587,
            smtp_username: '',
            items_per_page: 20,
            gallery_layout: 'grid',
            enable_zoom: '1',
            auto_play_videos: '1'
        };
        
        populateSettingsForm(defaultSettings);
        showAlert('Valores por defecto restaurados', 'success');
    }
}

// Inicialización principal
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar funciones de álbumes si el usuario es admin
    if (document.getElementById('albums-section')) {
        initSorting();
        restoreFiltersOnLoad();
    }
    
    // Inicializar subida de fotos si el usuario es editor
    if (document.getElementById('subir-fotos-alessandro-section')) {
        initPhotoUpload();
    }
    
    // Inicializar configuración si el usuario es admin
    if (document.getElementById('settings-section')) {
        loadSettings();
    }
    
    // Manejar envío del formulario de álbumes (solo para admin)
    const albumForm = document.getElementById('album-form');
    if (albumForm) {
        albumForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validación inteligente para páginas intermedias
            const esPaginaIntermedia = document.getElementById('es_pagina_intermedia').checked;
            const enlace = data.enlace;
            
            if (esPaginaIntermedia) {
                // Para páginas intermedias, permitir # o URL vacía
                if (enlace && enlace !== '#' && !enlace.startsWith('http')) {
                    showAlert('Para páginas intermedias, use # o una URL válida', 'error');
                    return;
                }
            } else {
                // Para álbumes normales, validar que sea una URL válida
                if (!enlace || enlace === '#') {
                    showAlert('Los álbumes normales deben tener una URL válida', 'error');
                    return;
                }
            }
            
            const action = data.action;
            
            // Preparar datos con method override si es necesario
            const requestData = { ...data };
            if (action !== 'create') {
                requestData._method = 'PUT';
            }
            
            fetch('../api/albums.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(`Álbum ${action === 'create' ? 'creado' : 'actualizado'} correctamente`, 'success');
                    closeAlbumForm();
                    // Recargar la tabla manteniendo los filtros
                    setTimeout(() => {
                        reloadTableWithFilters();
                    }, 1000);
                } else {
                    showAlert(data.error || 'Error en la operación', 'error');
                }
            })
            .catch(error => {
                showAlert('Error de conexión', 'error');
            });
        });
    }
    
    // Manejar envío del formulario de configuración
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Configuración guardada correctamente', 'success');
                } else {
                    showAlert(data.message || 'Error al guardar configuración', 'error');
                }
            })
            .catch(error => {
                showAlert('Error de conexión', 'error');
            });
        });
    }
});
