// Funciones para gestión de usuarios

function loadUsers() {
    fetch('../api/users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsers(data.users);
            } else {
                showAlert('Error al cargar usuarios', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
}

function displayUsers(users) {
    const tbody = document.getElementById('users-table-body');
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay usuarios registrados</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>${user.nombre} ${user.apellido_1} ${user.apellido_2 || ''}</td>
            <td>${user.correo}</td>
            <td>
                <span class="badge badge-${getProfileBadgeClass(user.perfil)}">
                    ${getProfileLabel(user.perfil)}
                </span>
            </td>
            <td>${user.sexo === 'M' ? 'Mujer' : 'Hombre'}</td>
            <td>
                <span class="badge badge-${user.activo ? 'success' : 'danger'}">
                    ${user.activo ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td class="actions">
                <button class="btn btn-sm btn-primary" onclick="editUser(${user.id})" title="Editar">
                    ✏️
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})" title="Eliminar">
                    🗑️
                </button>
            </td>
        </tr>
    `).join('');
}

function showUserForm() {
    document.getElementById('user-modal').style.display = 'block';
    document.body.classList.add('modal-open');
    resetUserForm();
}

function closeUserForm() {
    document.getElementById('user-modal').style.display = 'none';
    document.body.classList.remove('modal-open');
    resetUserForm();
}

function resetUserForm() {
    const form = document.getElementById('user-form');
    form.reset();
    document.getElementById('user-id').value = '';
    document.getElementById('user-id-display').value = 'Nuevo usuario';
    document.getElementById('user-modal-title').textContent = '👤 Crear Nuevo Usuario';
    document.getElementById('user-modal-subtitle').textContent = 'Completa la información del usuario';
    
    // Habilitar campos
    document.getElementById('user-email').readOnly = false;
    document.getElementById('user-password').required = true;
    document.getElementById('user-password').parentElement.style.display = 'block';
    
    // Limpiar mensaje de bienvenida
    document.getElementById('user-mensaje').value = '';
}

function editUser(id) {
    fetch(`../api/users.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                
                // Llenar formulario
                document.getElementById('user-id').value = user.id;
                document.getElementById('user-id-display').value = `ID: ${user.id}`;
                document.getElementById('user-nombre').value = user.nombre;
                document.getElementById('user-apellido1').value = user.apellido_1;
                document.getElementById('user-apellido2').value = user.apellido_2 || '';
                document.getElementById('user-email').value = user.correo;
                document.getElementById('user-perfil').value = user.perfil;
                document.getElementById('user-sexo').value = user.sexo;
                document.getElementById('user-activo').checked = user.activo;
                document.getElementById('user-mensaje').value = user.mensaje_bienvenida || '';
                
                // Cambiar título
                document.getElementById('user-modal-title').textContent = '✏️ Editar Usuario';
                document.getElementById('user-modal-subtitle').textContent = 'Modifica la información del usuario';
                
                // Hacer email readonly (no editable pero se envía) y password opcional
                document.getElementById('user-email').readOnly = true;
                document.getElementById('user-password').required = false;
                document.getElementById('user-password').value = ''; // Limpiar contraseña
                document.getElementById('user-password').parentElement.style.display = 'block';
                
                // Mostrar modal
                document.getElementById('user-modal').style.display = 'block';
                document.body.classList.add('modal-open');
            } else {
                showAlert('Error al cargar usuario', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading user:', error);
            showAlert('Error de conexión', 'error');
        });
}

function deleteUser(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este usuario?')) {
        fetch('../api/users.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Usuario eliminado correctamente', 'success');
                document.body.classList.remove('modal-open');
                loadUsers();
            } else {
                showAlert('Error al eliminar usuario', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión', 'error');
        });
    }
}

function getProfileBadgeClass(perfil) {
    const classes = {
        'admin': 'danger',
        'edit': 'warning',
        'view': 'info'
    };
    return classes[perfil] || 'secondary';
}

function getProfileLabel(perfil) {
    const labels = {
        'admin': 'Administrador',
        'edit': 'Editor',
        'view': 'Visualizador'
    };
    return labels[perfil] || 'Desconocido';
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Cargar usuarios cuando se active la sección
    const usersNavItem = document.querySelector('[data-section="users"]');
    
    if (usersNavItem) {
        usersNavItem.addEventListener('click', function() {
            // Pequeño delay para asegurar que la sección esté visible
            setTimeout(() => {
                const usersSection = document.getElementById('users-section');
                if (usersSection && usersSection.style.display !== 'none') {
                    loadUsers();
                }
            }, 100);
        });
    }
    
    // Manejar formulario de usuarios
    const userForm = document.getElementById('user-form');
    if (userForm) {
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Convertir checkbox a número
            data.activo = document.getElementById('user-activo').checked ? 1 : 0;
            
            const isEdit = data.id !== '';
            
            // Corregir action según si es edición o creación
            data.action = isEdit ? 'update' : 'create';
            
            
            const method = isEdit ? 'PUT' : 'POST';
            
            fetch('../api/users.php', {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(isEdit ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente', 'success');
                    closeUserForm();
                    document.body.classList.remove('modal-open');
                    loadUsers();
                } else {
                    showAlert(data.message || 'Error al guardar usuario', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error de conexión', 'error');
            });
        });
    }
    
    // Cerrar modal de usuarios al hacer clic fuera
    window.addEventListener('click', function(event) {
        const userModal = document.getElementById('user-modal');
        if (event.target === userModal) {
            closeUserForm();
        }
    });
});
