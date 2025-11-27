// ============================================================================
// GESTIÓN DE MODALES - Funciones para mostrar, cerrar y manipular modales
// ============================================================================

/**
 * Muestra un modal agregando la clase 'active'
 * @param {string} id - ID del modal a mostrar
 */
function mostrarModal(id) {
    document.getElementById(id).classList.add('active');
}

/**
 * Cierra un modal y limpia su formulario asociado
 * @param {string} id - ID del modal a cerrar
 */
function cerrarModal(id) {
    document.getElementById(id).classList.remove('active');
    // Limpiar formularios cuando se cierre el modal
    const form = document.querySelector(`#${id} form`);
    if (form) {
        form.reset();
    }
}

/**
 * Abre el modal para agregar un nuevo producto
 */
function mostrarModalAgregar() {
    mostrarModal('modalAgregar');
}

/**
 * Carga los datos de un producto y abre el modal de edición
 * @param {number} id - ID del producto a editar
 */
function editarProducto(id) {
    // Realizar solicitud AJAX para obtener los datos del producto
    fetch(`obtener_producto.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Llenar el formulario con los datos obtenidos
                document.getElementById('editarId').value = data.producto.id;
                document.getElementById('editarNombre').value = data.producto.nombre;
                document.getElementById('editarPrecio').value = data.producto.precio;
                document.getElementById('editarStock').value = data.producto.stock;
                document.getElementById('editarCategoria').value = data.producto.categoria_id;
                
                // Mostrar modal de edición
                mostrarModal('modalEditar');
            } else {
                alert('Error al cargar los datos del producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del producto');
        });
}

/**
 * Muestra el modal de confirmación para eliminar un producto
 * @param {number} id - ID del producto a eliminar
 * @param {string} nombre - Nombre del producto a eliminar
 */
function confirmarEliminar(id, nombre) {
    document.getElementById('eliminarId').value = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    mostrarModal('modalEliminar');
}

// ============================================================================
// EVENTOS DE MODALES - Manejo de interacciones con los modales
// ============================================================================

// Cerrar modal al hacer clic fuera de él (en el fondo oscuro)
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Cerrar todos los modales activos al presionar la tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modales = document.querySelectorAll('.modal.active');
        modales.forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// ============================================================================
// VALIDACIÓN DE FORMULARIOS - Se ejecuta cuando el DOM está listo
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // --- Validar campos de PRECIO ---
    // Solo permitir números y un punto decimal
    const inputsPrecios = document.querySelectorAll('input[name="precio"]');
    inputsPrecios.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remover caracteres que no sean números o punto
            let value = e.target.value.replace(/[^0-9.]/g, '');
            
            // Permitir solo un punto decimal
            const puntos = value.split('.').length - 1;
            if (puntos > 1) {
                value = value.substring(0, value.lastIndexOf('.'));
            }
            
            e.target.value = value;
        });
    });
    
    // --- Validar campos de STOCK ---
    // Solo permitir números enteros positivos
    const inputsStock = document.querySelectorAll('input[name="stock"]');
    inputsStock.forEach(input => {
        input.addEventListener('input', function(e) {
            // Permitir solo números enteros
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    });
    
    // --- Auto-envío en búsqueda ---
    // Enviar el formulario automáticamente después de dejar de escribir
    const busquedaInput = document.querySelector('input[name="busqueda"]');
    if (busquedaInput) {
        let timeout;
        busquedaInput.addEventListener('input', function() {
            clearTimeout(timeout);
            // Enviar después de 500ms de parar de escribir
            timeout = setTimeout(() => {
                this.form.submit();
            }, 500);
        });
    }
});

// ============================================================================
// MANEJO DE MENSAJES - Notificaciones de éxito/error desde la URL
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Obtener parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    const tipo = urlParams.get('tipo'); // 'success' o 'error'
    
    // Si hay mensaje, mostrar notificación
    if (mensaje && tipo) {
        mostrarNotificacion(mensaje, tipo);
        
        // Limpiar los parámetros de la URL sin recargar la página
        const newUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, newUrl);
    }
});

/**
 * Muestra una notificación temporal en la pantalla
 * @param {string} mensaje - Texto del mensaje a mostrar
 * @param {string} tipo - Tipo de notificación ('success' o 'error')
 */
function mostrarNotificacion(mensaje, tipo) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1001;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
    `;
    
    // Definir estilos según el tipo de notificación
    if (tipo === 'success') {
        notificacion.style.background = '#28a745'; // Verde
        notificacion.innerHTML = `✅ ${mensaje}`;
    } else if (tipo === 'error') {
        notificacion.style.background = '#dc3545'; // Rojo
        notificacion.innerHTML = `❌ ${mensaje}`;
    }
    
    // Agregar notificación al DOM
    document.body.appendChild(notificacion);
    
    // Remover notificación después de 5 segundos con animación
    setTimeout(() => {
        notificacion.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => {
            notificacion.remove();
        }, 300);
    }, 5000);
}

// ============================================================================
// ANIMACIONES CSS - Estilos para las notificaciones
// ============================================================================

// Agregar estilos de animación al documento
const style = document.createElement('style');
style.textContent = `
    /* Animación de entrada (deslizar desde la derecha) */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Animación de salida (deslizar hacia la derecha) */
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);