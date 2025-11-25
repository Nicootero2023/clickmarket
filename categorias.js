// Función para abrir el modal de categorías y cargar la lista
function abrirModalCategorias() {
    mostrarModal('modalCategorias');
    cargarCategorias();
}

// Función para cargar la lista de categorías
function cargarCategorias() {
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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

// Función para mostrar la lista de categorías
function mostrarListaCategorias(categorias) {
    const listaCategorias = document.getElementById('listaCategorias');
    
    if (categorias.length === 0) {
        listaCategorias.innerHTML = '<p style="text-align: center; color: #999; padding: 2rem;">No hay categorías registradas</p>';
        return;
    }
    
    let html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
    
    categorias.forEach(categoria => {
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px;">
                <div>
                    <strong style="color: #333;">${escapeHtml(categoria.nombre)}</strong>
                    <span style="color: #999; font-size: 0.85rem; margin-left: 0.5rem;">(${categoria.productos_count} productos)</span>
                </div>
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

// Función para agregar una nueva categoría
function agregarCategoria(event) {
    event.preventDefault();
    
    const nombreInput = document.getElementById('nombreCategoria');
    const nombre = nombreInput.value.trim();
    
    if (!nombre) {
        alert('Por favor ingresa un nombre para la categoría');
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('nombre', nombre);
    
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            nombreInput.value = '';
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

// Función para eliminar una categoría
function eliminarCategoria(id, nombre, productosCount) {
    let mensaje = `¿Estás seguro de eliminar la categoría "${nombre}"?`;
    
    if (productosCount > 0) {
        mensaje += `\n\nEsta categoría tiene ${productosCount} producto(s). Se moverán a "Sin categoría".`;
    }
    
    if (!confirm(mensaje)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);
    
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cargarCategorias();
            // Recargar la página después de 2 segundos para actualizar los filtros
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

// Función auxiliar para escapar HTML
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