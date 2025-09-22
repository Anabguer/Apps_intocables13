// Variables globales
let isEditing = false;

// Variables globales para filtros y ordenación
let currentSortColumn = -1;
let currentSortDirection = 'asc';
let savedFilters = {
    presentation: '',
    search: ''
};

// Mostrar formulario para nuevo item
function showItemForm() {
    isEditing = false;
    document.getElementById('modalTitle').textContent = 'Nuevo Item';
    document.getElementById('item-id-display').value = 'Nuevo item';
    document.getElementById('itemForm').reset();
    document.getElementById('item-id').value = '';
    document.getElementById('orden').value = '0';
    document.getElementById('activo').value = '1';
    document.getElementById('itemModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

// Editar item
function editItem(item) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = 'Editar Item';
    document.getElementById('item-id-display').value = `ID: ${item.id}`;
    
    // Llenar el formulario con los datos del item
    document.getElementById('item-id').value = item.id;
    document.getElementById('presentation_id').value = item.presentation_id || '';
    document.getElementById('titulo').value = item.titulo || '';
    document.getElementById('subtitulo').value = item.subtitulo || '';
    document.getElementById('imagen').value = item.imagen || '';
    document.getElementById('enlace').value = item.enlace || '';
    document.getElementById('es_pagina_intermedia').checked = item.es_pagina_intermedia || false;
    document.getElementById('padre_id').value = item.padre_id || '';
    document.getElementById('orden').value = item.orden || 0;
    document.getElementById('activo').value = item.activo || 1;
    
    document.getElementById('itemModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

// Eliminar item
function deleteItem(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este item? Esta acción no se puede deshacer.')) {
        fetch('presentation-items.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al eliminar el item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el item');
        });
    }
}

// Cerrar modal
function closeModal() {
    document.getElementById('itemModal').style.display = 'none';
    document.body.classList.remove('modal-open');
}

// Funciones para manejar filtros
function filterItems() {
    const presentationFilter = document.getElementById('presentation-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();
    
    // Guardar filtros
    savedFilters.presentation = presentationFilter;
    savedFilters.search = searchFilter;
    saveFilters();
    
    const rows = document.querySelectorAll('tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 0) return;
        
        const presentation = cells[4].textContent.trim();
        const title = cells[2].textContent.toLowerCase();
        
        const matchesPresentation = !presentationFilter || presentation === presentationFilter;
        const matchesSearch = !searchFilter || title.includes(searchFilter);
        
        if (matchesPresentation && matchesSearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    const resultsMsg = document.getElementById('results-message');
    if (visibleCount === 0) {
        if (!resultsMsg) {
            const msg = document.createElement('div');
            msg.id = 'results-message';
            msg.className = 'alert alert-info';
            msg.textContent = 'No se encontraron items con los filtros aplicados';
            document.querySelector('.items-table').appendChild(msg);
        }
    } else {
        if (resultsMsg) {
            resultsMsg.remove();
        }
    }
}

function clearFilters() {
    document.getElementById('presentation-filter').value = '';
    document.getElementById('search-filter').value = '';
    savedFilters = { presentation: '', search: '' };
    saveFilters();
    filterItems();
}

function saveFilters() {
    localStorage.setItem('presentationItemsFilters', JSON.stringify(savedFilters));
}

function loadFilters() {
    const saved = localStorage.getItem('presentationItemsFilters');
    if (saved) {
        savedFilters = JSON.parse(saved);
        document.getElementById('presentation-filter').value = savedFilters.presentation || '';
        document.getElementById('search-filter').value = savedFilters.search || '';
    }
}

// Función para ordenar tabla
function sortTable(columnIndex) {
    const table = document.querySelector('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Determinar dirección de ordenación
    if (currentSortColumn === columnIndex) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortDirection = 'asc';
        currentSortColumn = columnIndex;
    }
    
    // Ordenar filas
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        
        // Manejar números
        if (columnIndex === 0 || columnIndex === 6 || columnIndex === 7) { // ID, Padre ID, Orden
            const aNum = parseInt(aText) || 0;
            const bNum = parseInt(bText) || 0;
            return currentSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
        }
        
        // Manejar texto
        return currentSortDirection === 'asc' 
            ? aText.localeCompare(bText)
            : bText.localeCompare(aText);
    });
    
    // Reordenar filas en la tabla
    rows.forEach(row => tbody.appendChild(row));
    
    // Actualizar indicadores de ordenación
    document.querySelectorAll('th a').forEach((link, index) => {
        // Obtener el texto base sin flechas
        let baseText = link.textContent.replace(/[↑↓↕️]/g, '').trim();
        
        if (index === columnIndex) {
            link.textContent = baseText + (currentSortDirection === 'asc' ? ' ↑' : ' ↓');
        } else {
            link.textContent = baseText + ' ↕️';
        }
    });
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Cargar filtros al cargar la página
    loadFilters();
    if (savedFilters.presentation || savedFilters.search) {
        filterItems();
    }
    
    // Manejar envío del formulario
    const form = document.getElementById('itemForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Asegurar que el checkbox se envíe correctamente
            data.es_pagina_intermedia = document.getElementById('es_pagina_intermedia').checked;
            
            // Convertir valores numéricos
            data.presentation_id = parseInt(data.presentation_id);
            data.orden = parseInt(data.orden);
            data.activo = parseInt(data.activo);
            data.padre_id = data.padre_id ? parseInt(data.padre_id) : null;
            
            const url = 'presentation-items.php';
            const method = isEditing ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al guardar el item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el item');
            });
        });
    }
    
    // Manejar checkbox "Es página intermedia"
    const checkboxIntermedia = document.getElementById('es_pagina_intermedia');
    if (checkboxIntermedia) {
        checkboxIntermedia.addEventListener('change', function() {
            const enlaceInput = document.getElementById('enlace');
            if (this.checked) {
                enlaceInput.value = '#';
                enlaceInput.readOnly = true;
            } else {
                enlaceInput.readOnly = false;
            }
        });
    }
    
    // Validación del formulario
    const enlaceValidation = document.getElementById('itemForm');
    if (enlaceValidation) {
        enlaceValidation.addEventListener('submit', function(e) {
            const enlace = document.getElementById('enlace').value;
            const esIntermedia = document.getElementById('es_pagina_intermedia').checked;
            
            // Si no es intermedia, validar que el enlace sea válido
            if (!esIntermedia && enlace && enlace !== '#') {
                try {
                    new URL(enlace);
                } catch {
                    if (!enlace.startsWith('/') && !enlace.startsWith('#')) {
                        alert('Por favor, introduce una URL válida o deja el campo vacío');
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });
    }
    
    // Cerrar modal al hacer click fuera
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('itemModal');
        if (event.target === modal) {
            closeModal();
        }
    });
});
