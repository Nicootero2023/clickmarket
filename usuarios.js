// Función para abrir el modal de usuarios y cargar la lista
function abrirModalUsuarios() {
    mostrarModal('modalUsuarios');
    cargarUsuarios();
}

// Función para cargar la lista de usuarios
function cargarUsuarios() {
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarListaUsuarios(data.usuarios);
        } else {
            alert('Error al cargar usuarios: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los usuarios');
    });
}

// Función para mostrar la lista de usuarios
function mostrarListaUsuarios(usuarios) {
    const listaUsuarios = document.getElementById('listaUsuarios');
    
    if (usuarios.length === 0) {
        listaUsuarios.innerHTML = '<p style="text-align: center; color: #999; padding: 2rem;">No hay usuarios registrados</p>';
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
    
    usuarios.forEach(usuario => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <strong style="color: #333;">${escapeHtml(usuario.usuario)}</strong>
                        <span style="color: #999; font-size: 0.85rem;">• ${escapeHtml(usuario.nombre_completo)}</span>
                    </div>
                    <div style="color: #666; font-size: 0.85rem;">
                        ${escapeHtml(usuario.email)}
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button 
                        class="btn btn-outline" 
                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background-color: #007bff; color: white; border: none;"
                        onclick="editarUsuario(${usuario.id})"
                    >
                        ✏️ Editar
                    </button>
                    <button 
                        class="btn btn-danger" 
                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"
                        onclick="eliminarUsuario(${usuario.id}, '${escapeHtml(usuario.usuario)}')"
                    >
                        🗑️ Eliminar
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    listaUsuarios.innerHTML = html;
}

// Función para mostrar el modal de agregar usuario
function mostrarModalAgregarUsuario() {
    cerrarModal('modalUsuarios');
    mostrarModal('modalAgregarUsuario');
}

// Función para agregar un nuevo usuario
function agregarUsuario(event) {
    event.preventDefault();
    
    const usuarioInput = document.getElementById('nuevoUsuario');
    const contrasenaInput = document.getElementById('nuevaContrasena');
    const nombreInput = document.getElementById('nuevoNombreCompleto');
    const emailInput = document.getElementById('nuevoEmail');
    
    const usuario = usuarioInput.value.trim();
    const contrasena = contrasenaInput.value.trim();
    const nombre = nombreInput.value.trim();
    const email = emailInput.value.trim();
    
    if (!usuario || !contrasena || !nombre || !email) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('usuario', usuario);
    formData.append('contrasena', contrasena);
    formData.append('nombre_completo', nombre);
    formData.append('email', email);
    
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            // Limpiar formulario
            usuarioInput.value = '';
            contrasenaInput.value = '';
            nombreInput.value = '';
            emailInput.value = '';
            cerrarModal('modalAgregarUsuario');
            abrirModalUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el usuario');
    });
}

// Función para cargar datos del usuario en el modal de edición
function editarUsuario(id) {
    const formData = new FormData();
    formData.append('accion', 'obtener');
    formData.append('id', id);
    
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editarUsuarioId').value = data.usuario.id;
            document.getElementById('editarUsuarioNombre').value = data.usuario.usuario;
            document.getElementById('editarUsuarioNombreCompleto').value = data.usuario.nombre_completo;
            document.getElementById('editarUsuarioEmail').value = data.usuario.email;
            document.getElementById('editarUsuarioContrasena').value = '';
            
            cerrarModal('modalUsuarios');
            mostrarModal('modalEditarUsuario');
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar los datos del usuario');
    });
}

// Función para guardar cambios del usuario editado
function guardarEdicionUsuario(event) {
    event.preventDefault();
    
    const id = document.getElementById('editarUsuarioId').value;
    const usuario = document.getElementById('editarUsuarioNombre').value.trim();
    const nombreCompleto = document.getElementById('editarUsuarioNombreCompleto').value.trim();
    const email = document.getElementById('editarUsuarioEmail').value.trim();
    const contrasena = document.getElementById('editarUsuarioContrasena').value.trim();
    
    if (!usuario || !nombreCompleto || !email) {
        alert('El usuario, nombre completo y email son obligatorios');
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'editar');
    formData.append('id', id);
    formData.append('usuario', usuario);
    formData.append('nombre_completo', nombreCompleto);
    formData.append('email', email);
    if (contrasena) {
        formData.append('contrasena', contrasena);
    }
    
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cerrarModal('modalEditarUsuario');
            abrirModalUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el usuario');
    });
}

// Función para eliminar un usuario
function eliminarUsuario(id, usuario) {
    const mensaje = `¿Estás seguro de eliminar el usuario "${usuario}"?\n\nEsta acción no se puede deshacer.`;
    
    if (!confirm(mensaje)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);
    
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cargarUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el usuario');
    });
}

// Función para volver del modal de agregar/editar al modal principal
function volverAListaUsuarios() {
    cerrarModal('modalAgregarUsuario');
    cerrarModal('modalEditarUsuario');
    abrirModalUsuarios();
}