// Variables para el ordenamiento
let currentSortColumn = null;
let currentSortDirection = 'asc';

// Funciones para manejar el modal de años
function openYearModal(yearId = null) {
    const modal = document.getElementById('year-modal');
    const form = document.getElementById('year-form');
    const title = document.getElementById('modal-title');
    const subtitle = document.getElementById('modal-subtitle');
    const idDisplay = document.getElementById('year-id-display');
    
    if (yearId) {
        title.textContent = '✏️ Editar Año de Aray';
        subtitle.textContent = 'Modifica la información del año';
        idDisplay.value = `ID: ${yearId}`;
        loadYearData(yearId);
    } else {
        title.textContent = '👶 Crear Nuevo Año de Aray';
        subtitle.textContent = 'Completa la información del año';
        idDisplay.value = 'Nuevo año';
        form.reset();
        document.getElementById('year-activo').checked = true;
        document.getElementById('year-id').value = '';
        // Limpiar vista previa de imagen
        const preview = document.getElementById('year-image-preview');
        if (preview) {
            preview.src = '';
        }
    }
    
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}

function closeYearModal() {
    document.getElementById('year-modal').style.display = 'none';
    document.getElementById('year-form').reset();
    document.body.classList.remove('modal-open');
}

function loadYearData(yearId) {
    fetch(`../api/aray-years.php?id=${yearId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const year = data.data;
                document.getElementById('year-id').value = year.id;
                document.getElementById('year-year').value = year.year;
                document.getElementById('year-image').value = year.image;
                document.getElementById('year-orden').value = year.orden;
                document.getElementById('year-es-pagina-intermedia').checked = year.es_pagina_intermedia;
                document.getElementById('year-activo').checked = year.activo;
                
                // Actualizar vista previa de imagen
                updateImagePreview(year.image);
            } else {
                showAlert('Error al cargar datos del año: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al cargar datos del año', 'error');
        });
}

function editYear(yearId) {
    openYearModal(yearId);
}

function deleteYear(yearId) {
    if (confirm('¿Estás seguro de que quieres eliminar este año? Esta acción no se puede deshacer.')) {
        fetch(`../api/aray-years.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: yearId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Año eliminado correctamente', 'success');
                document.body.classList.remove('modal-open');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al eliminar el año', 'error');
        });
    }
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 1000;
        max-width: 400px;
    `;
    
    if (type === 'success') {
        alert.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        alert.style.backgroundColor = '#dc3545';
    }
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Función para ordenar tabla
function sortTable(column) {
    const table = document.querySelector('#years-table tbody');
    const rows = Array.from(table.querySelectorAll('tr'));
    
    // Determinar dirección de ordenación
    if (currentSortColumn === column) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortDirection = 'asc';
    }
    currentSortColumn = column;
    
    // Ordenar filas
    rows.sort((a, b) => {
        const cellsA = a.querySelectorAll('td');
        const cellsB = b.querySelectorAll('td');
        
        let valueA, valueB;
        
        // Obtener valores según la columna
        switch(column) {
            case 'id':
                valueA = parseInt(cellsA[0].textContent.trim());
                valueB = parseInt(cellsB[0].textContent.trim());
                break;
            case 'year':
                valueA = parseInt(cellsA[1].textContent.trim());
                valueB = parseInt(cellsB[1].textContent.trim());
                break;
            case 'image':
                valueA = cellsA[2].querySelector('img').alt.toLowerCase();
                valueB = cellsB[2].querySelector('img').alt.toLowerCase();
                break;
            case 'es_pagina_intermedia':
                valueA = cellsA[3].textContent.trim();
                valueB = cellsB[3].textContent.trim();
                break;
            case 'orden':
                valueA = parseInt(cellsA[4].textContent.trim()) || 0;
                valueB = parseInt(cellsB[4].textContent.trim()) || 0;
                break;
            case 'activo':
                valueA = cellsA[5].textContent.trim();
                valueB = cellsB[5].textContent.trim();
                break;
            case 'fecha_creacion':
                valueA = new Date(cellsA[6].textContent.trim());
                valueB = new Date(cellsB[6].textContent.trim());
                break;
            default:
                valueA = cellsA[0].textContent.trim();
                valueB = cellsB[0].textContent.trim();
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
    
    // Reordenar filas en la tabla
    rows.forEach(row => table.appendChild(row));
    
    // Actualizar indicadores de ordenación
    document.querySelectorAll('#years-table th').forEach((header, index) => {
        const span = header.querySelector('.sort-arrow');
        if (span) {
            // Obtener el texto base sin flechas
            let baseText = header.textContent.replace(/[↑↓↕]/g, '').trim();
            
            if (header.onclick && header.onclick.toString().includes(column)) {
                span.textContent = currentSortDirection === 'asc' ? ' ↑' : ' ↓';
            } else {
                span.textContent = ' ↕';
            }
        }
    });
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Manejar envío del formulario
    const yearForm = document.getElementById('year-form');
    if (yearForm) {
        yearForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.activo = document.getElementById('year-activo').checked ? 1 : 0;
            data.es_pagina_intermedia = document.getElementById('year-es-pagina-intermedia').checked ? 1 : 0;
            
            console.log('Datos a enviar:', data); // Para debug
            
            const isEdit = data.id !== '';
            const url = `../api/aray-years.php`;
            const method = isEdit ? 'PUT' : 'POST';
            
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
                    showAlert(isEdit ? 'Año actualizado correctamente' : 'Año creado correctamente', 'success');
                    closeYearModal();
                    document.body.classList.remove('modal-open');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error al procesar la solicitud', 'error');
            });
        });
    }
    
    // Listener para actualizar vista previa cuando cambie el campo de imagen
    const yearImageInput = document.getElementById('year-image');
    if (yearImageInput) {
        yearImageInput.addEventListener('input', function() {
            updateImagePreview(this.value);
        });
    }
});

// Función para actualizar vista previa de imagen
function updateImagePreview(imagePath) {
    const preview = document.getElementById('year-image-preview');
    if (preview && imagePath) {
        // Construir la ruta completa de la imagen
        const baseUrl = window.APP_CONFIG ? window.APP_CONFIG.BASE_URL.replace(/\/$/, '') : '';
        const fullPath = imagePath.startsWith('/') ? baseUrl + imagePath : baseUrl + '/' + imagePath;
        preview.src = fullPath;
    }
}
