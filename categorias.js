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
            // Actualizar desplegables de categorías en formularios
            actualizarDesplegablesCategorias();
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

// ============================================================================
// FUNCIONES AUXILIARES PARA ACTUALIZACIÓN DINÁMICA
// ============================================================================

/**
 * Actualiza todos los selectores de categorías en la página sin recargar
 */
function actualizarDesplegablesCategorias() {
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    fetch('gestionar_categorias.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const categorias = data.categorias;
            
            // 1. Selector del Modal Agregar Producto
            const selectAgregar = document.querySelector('#formAgregar select[name="categoria_id"]');
            if (selectAgregar) llenarSelect(selectAgregar, categorias, 'Selecciona una categoría');
            
            // 2. Selector del Modal Editar Producto
            const selectEditar = document.getElementById('editarCategoria');
            if (selectEditar) llenarSelect(selectEditar, categorias, 'Selecciona una categoría');
            
            // 3. Selector de Filtro Principal
            const selectFiltro = document.querySelector('select[name="categoria"]');
            if (selectFiltro) llenarSelect(selectFiltro, categorias, 'Todas las categorías');
        }
    })
    .catch(error => console.error('Error al actualizar selectores:', error));
}

/**
 * Llena un elemento <select> con las categorías proporcionadas
 * @param {HTMLSelectElement} selectElement - El elemento select a llenar
 * @param {Array} categorias - Lista de categorías
 * @param {string} textoDefault - Texto para la opción por defecto
 */
function llenarSelect(selectElement, categorias, textoDefault) {
    // Guardar el valor seleccionado actual para no perderlo si es posible
    const valorActual = selectElement.value;
    
    // Limpiar opciones actuales
    selectElement.innerHTML = '';
    
    // Agregar opción por defecto (placeholder)
    const optionDefault = document.createElement('option');
    optionDefault.value = '';
    optionDefault.textContent = textoDefault;
    selectElement.appendChild(optionDefault);
    
    // Agregar las categorías actualizadas
    categorias.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.nombre;
        selectElement.appendChild(option);
    });
    
    // Intentar restaurar la selección anterior (si la categoría aún existe)
    selectElement.value = valorActual;
}