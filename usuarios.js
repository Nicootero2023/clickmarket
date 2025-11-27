// ============================================================================
// GESTIÓN DE USUARIOS - Funciones AJAX para administrar usuarios del sistema
// ============================================================================

/**
 * Abre el modal de gestión de usuarios y carga la lista
 */
function abrirModalUsuarios() {
    mostrarModal('modalUsuarios');
    cargarUsuarios();
}

/**
 * Carga la lista de usuarios desde el servidor
 */
function cargarUsuarios() {
    // Preparar solicitud con acción 'listar'
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    // Realizar solicitud AJAX al servidor
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar lista de usuarios obtenida
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

/**
 * Renderiza la lista de usuarios en el DOM
 * @param {Array} usuarios - Array de objetos usuario
 */
function mostrarListaUsuarios(usuarios) {
    const listaUsuarios = document.getElementById('listaUsuarios');
    
    // Si no hay usuarios, mostrar mensaje
    if (usuarios.length === 0) {
        listaUsuarios.innerHTML = '<p style="text-align: center; color: #999; padding: 2rem;">No hay usuarios registrados</p>';
        return;
    }
    
    // Construir HTML de la lista
    let html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
    
    usuarios.forEach(usuario => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px;">
                <div style="flex: 1;">
                    <!-- Información del usuario -->
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <strong style="color: #333;">${escapeHtml(usuario.usuario)}</strong>
                        <span style="color: #999; font-size: 0.85rem;">• ${escapeHtml(usuario.nombre_completo)}</span>
                    </div>
                    <div style="color: #666; font-size: 0.85rem;">
                        ${escapeHtml(usuario.email)}
                    </div>
                </div>
                <!-- Botones de acción -->
                <div style="display: flex; gap: 0.5rem;">
                    <!-- Botón Editar -->
                    <button 
                        class="btn btn-accent btn-small" 
                        onclick="editarUsuario(${usuario.id})"
                    >
                        ✏️ Editar
                    </button>
                    <!-- Botón Eliminar -->
                    <button 
                        class="btn btn-danger btn-small" 
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

/**
 * Muestra el modal para agregar un nuevo usuario
 */
function mostrarModalAgregarUsuario() {
    cerrarModal('modalUsuarios');
    mostrarModal('modalAgregarUsuario');
}

/**
 * Agrega un nuevo usuario al sistema
 * @param {Event} event - Evento del formulario
 */
function agregarUsuario(event) {
    event.preventDefault();
    
    // Obtener valores del formulario
    const usuarioInput = document.getElementById('nuevoUsuario');
    const contrasenaInput = document.getElementById('nuevaContrasena');
    const nombreInput = document.getElementById('nuevoNombreCompleto');
    const emailInput = document.getElementById('nuevoEmail');
    
    const usuario = usuarioInput.value.trim();
    const contrasena = contrasenaInput.value.trim();
    const nombre = nombreInput.value.trim();
    const email = emailInput.value.trim();
    
    // Validar que todos los campos estén completos
    if (!usuario || !contrasena || !nombre || !email) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    // Preparar datos para enviar
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('usuario', usuario);
    formData.append('contrasena', contrasena);
    formData.append('nombre_completo', nombre);
    formData.append('email', email);
    
    // Enviar solicitud al servidor
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarNotificacion(data.mensaje, 'success');
            // Limpiar formulario
            usuarioInput.value = '';
            contrasenaInput.value = '';
            nombreInput.value = '';
            emailInput.value = '';
            // Cerrar modal y recargar lista
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

/**
 * Carga los datos de un usuario para editarlo
 * @param {number} id - ID del usuario a editar
 */
function editarUsuario(id) {
    // Preparar solicitud para obtener datos del usuario
    const formData = new FormData();
    formData.append('accion', 'obtener');
    formData.append('id', id);
    
    // Obtener datos del servidor
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Llenar formulario con datos del usuario
            document.getElementById('editarUsuarioId').value = data.usuario.id;
            document.getElementById('editarUsuarioNombre').value = data.usuario.usuario;
            document.getElementById('editarUsuarioNombreCompleto').value = data.usuario.nombre_completo;
            document.getElementById('editarUsuarioEmail').value = data.usuario.email;
            document.getElementById('editarUsuarioContrasena').value = ''; // Limpio por seguridad
            
            // Mostrar modal de edición
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

/**
 * Guarda los cambios realizados a un usuario
 * @param {Event} event - Evento del formulario
 */
function guardarEdicionUsuario(event) {
    event.preventDefault();
    
    // Obtener valores del formulario
    const id = document.getElementById('editarUsuarioId').value;
    const usuario = document.getElementById('editarUsuarioNombre').value.trim();
    const nombreCompleto = document.getElementById('editarUsuarioNombreCompleto').value.trim();
    const email = document.getElementById('editarUsuarioEmail').value.trim();
    const contrasena = document.getElementById('editarUsuarioContrasena').value.trim();
    
    // Validar campos obligatorios
    if (!usuario || !nombreCompleto || !email) {
        alert('El usuario, nombre completo y email son obligatorios');
        return;
    }
    
    // Preparar datos para enviar
    const formData = new FormData();
    formData.append('accion', 'editar');
    formData.append('id', id);
    formData.append('usuario', usuario);
    formData.append('nombre_completo', nombreCompleto);
    formData.append('email', email);
    
    // Solo agregar contraseña si se proporcionó una nueva
    if (contrasena) {
        formData.append('contrasena', contrasena);
    }
    
    // Enviar solicitud al servidor
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

/**
 * Elimina un usuario del sistema
 * @param {number} id - ID del usuario a eliminar
 * @param {string} usuario - Nombre de usuario (para mostrar en confirmación)
 */
function eliminarUsuario(id, usuario) {
    // Pedir confirmación al usuario
    const mensaje = `¿Estás seguro de eliminar el usuario "${usuario}"?\n\nEsta acción no se puede deshacer.`;
    
    if (!confirm(mensaje)) {
        return;
    }
    
    // Preparar solicitud de eliminación
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);
    
    // Enviar solicitud al servidor
    fetch('gestionar_usuarios.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cargarUsuarios(); // Recargar lista
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el usuario');
    });
}

/**
 * Vuelve del modal de agregar/editar al modal principal de usuarios
 */
function volverAListaUsuarios() {
    cerrarModal('modalAgregarUsuario');
    cerrarModal('modalEditarUsuario');
    abrirModalUsuarios();
}

/**
 * Escapa caracteres HTML para evitar inyecciones XSS
 * @param {string} text - Texto a escapar
 * @returns {string} Texto escapado
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}