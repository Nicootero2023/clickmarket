// ============================================================================
// GESTIÓN DE CATEGORÍAS
// ============================================================================

function abrirModalCategorias() {
    mostrarModal('modalCategorias');
    cargarCategorias();
}

function cargarCategorias() {
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    fetch('gestionar_categorias.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarListaCategorias(data.categorias);
        } else {
            alert('Error al cargar categorías: ' + data.error);
        }
    })
    .catch(error => { console.error('Error:', error); alert('Error al cargar las categorías'); });
}

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
                <div style="display: flex; gap: 0.5rem;">
                    <button 
                        class="btn btn-outline" 
                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background-color: #007bff; color: white; border: none;"
                        onclick="prepararEdicionCategoria(${categoria.id}, '${escapeHtml(categoria.nombre)}')"
                    >
                        ✏️
                    </button>
                    <button 
                        class="btn btn-danger" 
                        style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"
                        onclick="eliminarCategoria(${categoria.id}, '${escapeHtml(categoria.nombre)}', ${categoria.productos_count})"
                    >
                        🗑️
                    </button>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    listaCategorias.innerHTML = html;
}

function agregarCategoria(event) {
    event.preventDefault();
    const nombreInput = document.getElementById('nombreCategoria');
    const nombre = nombreInput.value.trim();
    
    if (!nombre) { alert('Por favor ingresa un nombre'); return; }
    
    const formData = new FormData();
    formData.append('accion', 'agregar');
    formData.append('nombre', nombre);
    
    fetch('gestionar_categorias.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            nombreInput.value = '';
            cargarCategorias();
            actualizarDesplegablesCategorias(); // Actualiza los selectores
        } else {
            alert('Error: ' + data.error);
        }
    });
}

// --- FUNCIONES DE EDICIÓN DE CATEGORÍA ---

// 1. Abre el modal de edición con los datos cargados
function prepararEdicionCategoria(id, nombre) {
    document.getElementById('editarCategoriaId').value = id;
    document.getElementById('editarCategoriaNombre').value = nombre;
    
    cerrarModal('modalCategorias');      // Cierra la lista
    mostrarModal('modalEditarCategoria'); // Abre el formulario de edición
}

// 2. Guarda los cambios en el servidor
function guardarEdicionCategoria(event) {
    event.preventDefault();
    
    const id = document.getElementById('editarCategoriaId').value;
    const nombre = document.getElementById('editarCategoriaNombre').value.trim();
    
    if (!nombre) { alert('El nombre es obligatorio'); return; }
    
    const formData = new FormData();
    formData.append('accion', 'editar');
    formData.append('id', id);
    formData.append('nombre', nombre);
    
    fetch('gestionar_categorias.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            actualizarDesplegablesCategorias(); // Actualiza selectores globales
            volverAListaCategorias();           // Vuelve a la lista
        } else {
            alert('Error: ' + data.error);
        }
    });
}

// 3. Botón Cancelar: Vuelve a la lista
function volverAListaCategorias() {
    cerrarModal('modalEditarCategoria');
    abrirModalCategorias();
}

// -----------------------------------------

function eliminarCategoria(id, nombre, productosCount) {
    let mensaje = `¿Eliminar categoría "${nombre}"?`;
    if (productosCount > 0) mensaje += `\n\n${productosCount} productos pasarán a "Sin categoría".`;
    
    if (!confirm(mensaje)) return;
    
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);
    
    fetch('gestionar_categorias.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(data.mensaje, 'success');
            cargarCategorias();
            actualizarDesplegablesCategorias();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
}

// Actualiza los <select> de la página (Agregar/Editar producto y Filtros)
function actualizarDesplegablesCategorias() {
    const formData = new FormData();
    formData.append('accion', 'listar');
    
    fetch('gestionar_categorias.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const categorias = data.categorias;
            const selectAgregar = document.querySelector('#formAgregar select[name="categoria_id"]');
            if (selectAgregar) llenarSelect(selectAgregar, categorias, 'Selecciona una categoría');
            
            const selectEditar = document.getElementById('editarCategoria');
            if (selectEditar) llenarSelect(selectEditar, categorias, 'Selecciona una categoría');
            
            const selectFiltro = document.querySelector('select[name="categoria"]');
            if (selectFiltro) llenarSelect(selectFiltro, categorias, 'Todas las categorías');
        }
    });
}

function llenarSelect(selectElement, categorias, textoDefault) {
    const valorActual = selectElement.value;
    selectElement.innerHTML = '';
    const optionDefault = document.createElement('option');
    optionDefault.value = '';
    optionDefault.textContent = textoDefault;
    selectElement.appendChild(optionDefault);
    
    categorias.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.nombre;
        selectElement.appendChild(option);
    });
    selectElement.value = valorActual;
}