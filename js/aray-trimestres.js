// Variables para el ordenamiento
let currentSortColumn = null;
let currentSortDirection = 'asc';

// Funciones para manejar el modal de trimestres
function openTrimestreModal(trimestreId = null) {
    const modal = document.getElementById('trimestre-modal');
    const form = document.getElementById('trimestre-form');
    const title = document.getElementById('modal-title');
    const subtitle = document.getElementById('modal-subtitle');
    const idDisplay = document.getElementById('trimestre-id-display');
    
    if (trimestreId) {
        title.textContent = '✏️ Editar Trimestre de Aray';
        subtitle.textContent = 'Modifica la información del trimestre';
        idDisplay.value = `ID: ${trimestreId}`;
        loadTrimestreData(trimestreId);
    } else {
        title.textContent = '📅 Crear Nuevo Trimestre de Aray';
        subtitle.textContent = 'Completa la información del trimestre';
        idDisplay.value = 'Nuevo trimestre';
        form.reset();
        document.getElementById('trimestre-activo').checked = true;
        document.getElementById('trimestre-id').value = '';
    }
    
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}

function closeTrimestreModal() {
    document.getElementById('trimestre-modal').style.display = 'none';
    document.getElementById('trimestre-form').reset();
    document.body.classList.remove('modal-open');
}

function loadTrimestreData(trimestreId) {
    fetch(`../api/aray-trimestres.php?id=${trimestreId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const trimestre = data.data;
                document.getElementById('trimestre-id').value = trimestre.id;
                document.getElementById('trimestre-year-id').value = trimestre.year_id;
                document.getElementById('trimestre-trimestre').value = trimestre.trimestre;
                document.getElementById('trimestre-titulo').value = trimestre.titulo;
                document.getElementById('trimestre-url-fotos').value = trimestre.url_fotos;
                document.getElementById('trimestre-tipo-url-fotos').value = trimestre.tipo_url_fotos;
                document.getElementById('trimestre-url-video').value = trimestre.url_video || '';
                document.getElementById('trimestre-orden').value = trimestre.orden;
                document.getElementById('trimestre-activo').checked = trimestre.activo;
            } else {
                showAlert('Error al cargar datos del trimestre: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error al cargar datos del trimestre', 'error');
        });
}

function editTrimestre(trimestreId) {
    openTrimestreModal(trimestreId);
}

function deleteTrimestre(trimestreId) {
    if (confirm('¿Estás seguro de que quieres eliminar este trimestre? Esta acción no se puede deshacer.')) {
        fetch(`../api/aray-trimestres.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: trimestreId})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Trimestre eliminado correctamente', 'success');
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
            showAlert('Error al eliminar el trimestre', 'error');
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
    const table = document.querySelector('#trimestres-table tbody');
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
            case 'trimestre':
                valueA = cellsA[2].textContent.trim();
                valueB = cellsB[2].textContent.trim();
                break;
            case 'titulo':
                valueA = cellsA[3].textContent.trim().toLowerCase();
                valueB = cellsB[3].textContent.trim().toLowerCase();
                break;
            case 'tipo_url_fotos':
                valueA = cellsA[4].textContent.trim();
                valueB = cellsB[4].textContent.trim();
                break;
            case 'orden':
                valueA = parseInt(cellsA[6].textContent.trim()) || 0;
                valueB = parseInt(cellsB[6].textContent.trim()) || 0;
                break;
            case 'activo':
                valueA = cellsA[7].textContent.trim();
                valueB = cellsB[7].textContent.trim();
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
    document.querySelectorAll('#trimestres-table th').forEach((header, index) => {
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
    const trimestreForm = document.getElementById('trimestre-form');
    if (trimestreForm) {
        trimestreForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.activo = document.getElementById('trimestre-activo').checked ? 1 : 0;
            
            const isEdit = data.id !== '';
            const url = `../api/aray-trimestres.php`;
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
                    showAlert(isEdit ? 'Trimestre actualizado correctamente' : 'Trimestre creado correctamente', 'success');
                    closeTrimestreModal();
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
});
