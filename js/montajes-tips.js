// Variables globales para Montajes & Tips
let isEditing = false;

// Mostrar formulario para nueva presentación
function showPresentationForm() {
    isEditing = false;
    document.getElementById('modalTitle').textContent = '🎬 Nuevo Montaje & Tip';
    document.getElementById('modalSubtitle').textContent = 'Completa la información del montaje & tip';
    document.getElementById('presentation-id-display').value = 'Nueva presentación';
    document.getElementById('submit-btn').innerHTML = '<span>💾</span> Crear Montaje & Tip';
    document.getElementById('presentationForm').reset();
    document.getElementById('presentation-id').value = '';
    document.getElementById('orden').value = '0';
    document.getElementById('activo').value = '1';
    document.getElementById('presentationModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

// Editar presentación
function editPresentation(presentation) {
    isEditing = true;
    document.getElementById('modalTitle').textContent = '✏️ Editar Montaje & Tip';
    document.getElementById('modalSubtitle').textContent = 'Modifica la información del montaje & tip';
    document.getElementById('presentation-id-display').value = `ID: ${presentation.id}`;
    document.getElementById('submit-btn').innerHTML = '<span>💾</span> Guardar Cambios';
    
    // Llenar el formulario con los datos de la presentación
    document.getElementById('presentation-id').value = presentation.id;
    document.getElementById('titulo').value = presentation.titulo || '';
    document.getElementById('subtitulo').value = presentation.subtitulo || '';
    document.getElementById('imagen').value = presentation.imagen || '';
    document.getElementById('enlace').value = presentation.enlace || '';
    document.getElementById('es_pagina_intermedia').checked = presentation.es_pagina_intermedia || false;
    document.getElementById('descripcion').value = presentation.descripcion || '';
    document.getElementById('orden').value = presentation.orden || 0;
    document.getElementById('activo').value = presentation.activo || 1;
    
    document.getElementById('presentationModal').style.display = 'block';
    document.body.classList.add('modal-open');
}

// Eliminar presentación
function deletePresentation(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este montaje & tip? Esta acción no se puede deshacer.')) {
        fetch('montajes-tips.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.body.classList.remove('modal-open');
                    location.reload();
                } else {
                    alert('Error al eliminar el montaje & tip');
                }
            })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el montaje & tip');
        });
    }
}

// Cerrar modal
function closeModal() {
    document.getElementById('presentationModal').style.display = 'none';
    document.body.classList.remove('modal-open');
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Manejar envío del formulario
    const presentationForm = document.getElementById('presentationForm');
    if (presentationForm) {
        presentationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Convertir valores numéricos
            data.orden = parseInt(data.orden);
            data.activo = parseInt(data.activo);
            
            // Debug: mostrar datos que se envían
            console.log('Datos del formulario:', data);
            
            const url = 'montajes-tips.php';
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
                    document.body.classList.remove('modal-open');
                    location.reload();
                } else {
                    alert('Error al guardar el montaje & tip');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el montaje & tip');
            });
        });
    }
    
    // Manejar checkbox "Es página intermedia"
    const esIntermediaCheckbox = document.getElementById('es_pagina_intermedia');
    if (esIntermediaCheckbox) {
        esIntermediaCheckbox.addEventListener('change', function() {
            console.log('Checkbox cambiado:', this.checked);
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
    if (presentationForm) {
        presentationForm.addEventListener('submit', function(e) {
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
    window.onclick = function(event) {
        const modal = document.getElementById('presentationModal');
        if (modal && event.target === modal) {
            closeModal();
        }
    }
});
