// ============================================================================
// GESTIÓN DE CATEGORÍAS - Funciones para administrar categorías de productos
// ============================================================================

/**
 * Abre el modal de gestión de categorías y carga la lista
 */
function abrirModalCategorias() {
    mostrarModal('modalCategorias');
    cargarCategorias();
}

/**
 * Carga la lista de categorías desde el servidor
 */
function cargarCategorias() {
    // Preparar solicitud AJAX
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    // Enviar solicitud al servidor
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar categorías obtenidas
            mostrarListaCategorias(data.categorias);
        } else {
            alert('Error al cargar categorías: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar las categorías');
    });
}

/**
 * Renderiza la lista de categorías en el DOM
 * @param {Array} categorias - Array de objetos categoría
 */
function mostrarListaCategorias(categorias) {
    const listaCategorias = document.getElementById('listaCategorias');
    
    // Si no hay categorías, mostrar mensaje
    if (categorias.length === 0) {
        listaCategorias.innerHTML = '<p style="text-align: center; color: #999; padding: 2rem;">No hay categorías registradas</p>';
        return;
    }
    
    // Construir HTML con la lista de categorías
    let html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
    
    categorias.forEach(categoria => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px;">
                <div>
                    <!-- Nombre de la categoría -->
                    <strong style="color: #333;">${escapeHtml(categoria.nombre)}</strong>
                    <!-- Contador de productos -->
                    <span style="color: #999; font-size: 0.85rem; margin-left: 0.5rem;">(${categoria.productos_count} productos)</span>
                </div>
                <!-- Botón eliminar -->
                <button 
                    class="btn btn-danger" 
                    style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"
                    onclick="eliminarCategoria(${categoria.id}, '${escapeHtml(categoria.nombre)}', ${categoria.productos_count})"
                >
                    🗑️ Eliminar
                </button>
            </div>
        `;
    });
    
    html += '</div>';
    listaCategorias.innerHTML = html;
}

/**
 * Agrega una nueva categoría al sistema
 * @param {Event} event - Evento del formulario
 */
function agregarCategoria(event) {
    event.preventDefault();
    
    // Obtener valor del input
    const nombreInput = document.getElementById('nombreCategoria');
    const nombre = nombreInput.value.trim();
    
    // Validar que se ingresó un nombre
    if (!nombre) {
        alert('Por favor ingresa un nombre para la categoría');
        return;
    }
    
    // Preparar datos para enviar
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('nombre', nombre);
    
    // Enviar solicitud al servidor
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarNotificacion(data.mensaje, 'success');
            // Limpiar input
            nombreInput.value = '';
            // Recargar lista
            cargarCategorias();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar la categoría');
    });
}

/**
 * Elimina una categoría del sistema
 * @param {number} id - ID de la categoría a eliminar
 * @param {string} nombre - Nombre de la categoría
 * @param {number} productosCount - Cantidad de productos en esta categoría
 */
function eliminarCategoria(id, nombre, productosCount) {
    // Construir mensaje de confirmación
    let mensaje = `¿Estás seguro de eliminar la categoría "${nombre}"?`;
    
    // Si hay productos en la categoría, avisar que se moverán
    if (productosCount > 0) {
        mensaje += `\n\nEsta categoría tiene ${productosCount} producto(s). Se moverán a "Sin categoría".`;
    }
    
    // Pedir confirmación
    if (!confirm(mensaje)) {
        return;
    }
    
    // Preparar solicitud de eliminación
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);
    
    // Enviar solicitud al servidor
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cargarCategorias();
            // Recargar página después de 2 segundos para actualizar filtros
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la categoría');
    });
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