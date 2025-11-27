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

// Función para mostrar la lista de usuarios (CON ROL VISUAL)
function mostrarListaUsuarios(usuarios) {
    const listaUsuarios = document.getElementById('listaUsuarios');
    
    if (usuarios.length === 0) {
        listaUsuarios.innerHTML = '<p style="text-align: center; color: #999; padding: 2rem;">No hay usuarios registrados</p>';
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
    
    usuarios.forEach(usuario => {
        // Etiqueta visual para el rol
        let etiquetaRol = usuario.rol === 'admin' 
            ? '<span style="background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; margin-left: 8px;">ADMIN</span>' 
            : '<span style="background: #f3f4f6; color: #4b5563; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">Usuario</span>';

        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <strong style="color: #333;">${escapeHtml(usuario.usuario)}</strong>
                        ${etiquetaRol}
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

function mostrarModalAgregarUsuario() {
    cerrarModal('modalUsuarios');
    mostrarModal('modalAgregarUsuario');
}

// Agregar usuario (CON ROL)
function agregarUsuario(event) {
    event.preventDefault();
    
    const usuario = document.getElementById('nuevoUsuario').value.trim();
    const contrasena = document.getElementById('nuevaContrasena').value.trim();
    const nombre = document.getElementById('nuevoNombreCompleto').value.trim();
    const email = document.getElementById('nuevoEmail').value.trim();
    const rol = document.getElementById('nuevoRol').value; // Obtener rol
    
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
    formData.append('rol', rol); // Enviar rol
    
    fetch('gestionar_usuarios.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            document.getElementById('formAgregarUsuario').reset();
            cerrarModal('modalAgregarUsuario');
            abrirModalUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => { console.error(error); alert('Error al agregar el usuario'); });
}

// Editar usuario (CARGAR ROL)
function editarUsuario(id) {
    const formData = new FormData();
    formData.append('accion', 'obtener');
    formData.append('id', id);
    
    fetch('gestionar_usuarios.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editarUsuarioId').value = data.usuario.id;
            document.getElementById('editarUsuarioNombre').value = data.usuario.usuario;
            document.getElementById('editarUsuarioNombreCompleto').value = data.usuario.nombre_completo;
            document.getElementById('editarUsuarioEmail').value = data.usuario.email;
            document.getElementById('editarUsuarioRol').value = data.usuario.rol; // Cargar rol actual
            document.getElementById('editarUsuarioContrasena').value = '';
            
            cerrarModal('modalUsuarios');
            mostrarModal('modalEditarUsuario');
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => { console.error(error); alert('Error al cargar los datos'); });
}

// Guardar edición (ENVIAR ROL)
function guardarEdicionUsuario(event) {
    event.preventDefault();
    
    const id = document.getElementById('editarUsuarioId').value;
    const usuario = document.getElementById('editarUsuarioNombre').value.trim();
    const nombreCompleto = document.getElementById('editarUsuarioNombreCompleto').value.trim();
    const email = document.getElementById('editarUsuarioEmail').value.trim();
    const rol = document.getElementById('editarUsuarioRol').value; // Obtener rol modificado
    const contrasena = document.getElementById('editarUsuarioContrasena').value.trim();
    
    const formData = new FormData();
    formData.append('accion', 'editar');
    formData.append('id', id);
    formData.append('usuario', usuario);
    formData.append('nombre_completo', nombreCompleto);
    formData.append('email', email);
    formData.append('rol', rol); // Enviar rol
    if (contrasena) formData.append('contrasena', contrasena);
    
    fetch('gestionar_usuarios.php', { method: 'POST', body: formData })
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
    .catch(error => { console.error(error); alert('Error al actualizar'); });
}

function eliminarUsuario(id, usuario) {
    if (!confirm(`¿Estás seguro de eliminar el usuario "${usuario}"?`)) return;
    
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);
    
    fetch('gestionar_usuarios.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cargarUsuarios();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => { console.error(error); alert('Error al eliminar'); });
}

function volverAListaUsuarios() {
    cerrarModal('modalAgregarUsuario');
    cerrarModal('modalEditarUsuario');
    abrirModalUsuarios();
}