// Funciones para gestión de elementos de página principal

let currentEditMode = 'create';
let currentEditId = null;

function loadHomepageElements() {
    fetch('../api/homepage-elements.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayHomepageElements(data.elements);
            } else {
                showAlert('Error al cargar elementos', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function displayHomepageElements(elements) {
    const tbody = document.getElementById('homepage-elements-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (elements.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No hay elementos configurados</td></tr>';
        return;
    }
    
    elements.forEach(element => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${element.id}</td>
            <td>
                <img src="${window.APP_CONFIG ? window.APP_CONFIG.BASE_URL.replace(/\/$/, '') : ''}${element.imagen}" alt="${element.titulo}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" 
                     onerror="this.src='${window.APP_CONFIG ? window.APP_CONFIG.IMG_URL : '/img/'}botones/portada_fotos_videos.png'">
            </td>
            <td>${element.titulo}</td>
            <td>${element.descripcion || 'Sin descripción'}</td>
            <td>
                <a href="${element.enlace}" target="_blank" class="link-preview">
                    ${element.enlace.length > 30 ? element.enlace.substring(0, 30) + '...' : element.enlace}
                </a>
            </td>
            <td>${element.orden}</td>
            <td>
                <span class="badge badge-${element.activo ? 'success' : 'danger'}">
                    ${element.activo ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td class="actions">
                <button class="btn btn-sm btn-primary" onclick="editHomepageElement(${element.id})" title="Editar">
                    ✏️
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteHomepageElement(${element.id})" title="Eliminar">
                    🗑️
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showHomepageElementForm() {
    currentEditMode = 'create';
    currentEditId = null;
    document.getElementById('homepage-element-modal-title').textContent = '🏠 Nuevo Elemento';
    document.getElementById('homepage-element-modal-subtitle').textContent = 'Configura el elemento de la página principal';
    document.getElementById('homepage-element-id-display').value = 'Nuevo elemento';
    document.getElementById('homepage-submit-btn').innerHTML = '<span>💾</span> Crear Elemento';
    document.getElementById('homepage-element-modal').style.display = 'block';
    document.body.classList.add('modal-open');
    resetHomepageElementForm();
}

function closeHomepageElementForm() {
    document.getElementById('homepage-element-modal').style.display = 'none';
    document.body.classList.remove('modal-open');
    resetHomepageElementForm();
}

function resetHomepageElementForm() {
    const form = document.getElementById('homepage-element-form');
    form.reset();
    document.getElementById('homepage-element-id').value = '';
    document.getElementById('homepage-element-id-display').value = 'Nuevo elemento';
    
    // Limpiar campos específicos
    document.getElementById('homepage-element-titulo').value = '';
    document.getElementById('homepage-element-descripcion').value = '';
    document.getElementById('homepage-element-imagen').value = '';
    document.getElementById('homepage-element-enlace').value = '';
    document.getElementById('homepage-element-orden').value = '';
    document.getElementById('homepage-element-activo').checked = true;
}

function editHomepageElement(id) {
    currentEditMode = 'update';
    currentEditId = id;
    
    fetch(`../api/homepage-elements.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const element = data.element;
                
                // Llenar formulario
                document.getElementById('homepage-element-id').value = element.id;
                document.getElementById('homepage-element-id-display').value = `ID: ${element.id}`;
                document.getElementById('homepage-element-titulo').value = element.titulo;
                document.getElementById('homepage-element-descripcion').value = element.descripcion || '';
                document.getElementById('homepage-element-imagen').value = element.imagen;
                document.getElementById('homepage-element-enlace').value = element.enlace;
                document.getElementById('homepage-element-orden').value = element.orden;
                document.getElementById('homepage-element-activo').checked = element.activo;
                
                // Cambiar título y botón
                document.getElementById('homepage-element-modal-title').textContent = '✏️ Editar Elemento';
                document.getElementById('homepage-element-modal-subtitle').textContent = 'Modifica la información del elemento';
                document.getElementById('homepage-submit-btn').innerHTML = '<span>💾</span> Guardar Cambios';
                
                // Mostrar modal
                document.getElementById('homepage-element-modal').style.display = 'block';
                document.body.classList.add('modal-open');
            } else {
                showAlert('Error al cargar elemento', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading element:', error);
            showAlert('Error de conexión', 'error');
        });
}

function deleteHomepageElement(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
        fetch(`../api/homepage-elements.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Elemento eliminado correctamente', 'success');
                loadHomepageElements();
            } else {
                showAlert('Error al eliminar elemento', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
    }
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Cargar elementos cuando se active la sección
    const homepageNavItem = document.querySelector('[data-section="homepage-elements"]');
    
    if (homepageNavItem) {
        homepageNavItem.addEventListener('click', function() {
            // Pequeño delay para asegurar que la sección esté visible
            setTimeout(() => {
                const homepageSection = document.getElementById('homepage-elements-section');
                if (homepageSection && homepageSection.style.display !== 'none') {
                    loadHomepageElements();
                }
            }, 100);
        });
    }
    
    // Manejar formulario de elementos
    const homepageForm = document.getElementById('homepage-element-form');
    if (homepageForm) {
        homepageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Convertir checkbox a número
            data.activo = document.getElementById('homepage-element-activo').checked ? 1 : 0;
            
            const method = currentEditMode === 'update' ? 'PUT' : 'POST';
            if (currentEditMode === 'update') {
                data.id = currentEditId;
            }
            
            fetch('../api/homepage-elements.php', {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(currentEditMode === 'update' ? 'Elemento actualizado correctamente' : 'Elemento creado correctamente', 'success');
                    closeHomepageElementForm();
                    loadHomepageElements();
                } else {
                    showAlert(data.message || 'Error al guardar elemento', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            });
        });
    }
    
    // Cerrar modal de elementos al hacer clic fuera
    window.addEventListener('click', function(event) {
        const homepageModal = document.getElementById('homepage-element-modal');
        if (event.target === homepageModal) {
            closeHomepageElementForm();
        }
    });
});
