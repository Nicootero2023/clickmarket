// Funciones para manejo de modales
// Función para mostrar un modal específico
function mostrarModal(id) {
    document.getElementById(id).classList.add('active');
}

// Función para cerrar un modal y limpiar su formulario
function cerrarModal(id) {
    document.getElementById(id).classList.remove('active');
    // Limpiar formularios cuando se cierre
    const form = document.querySelector(`#${id} form`);
    if (form) {
        form.reset();
    }
}

// Abre el modal para agregar un nuevo producto
function mostrarModalAgregar() {
    mostrarModal('modalAgregar');
}

// Carga y muestra los datos de un producto en el modal de edición
function editarProducto(id) {
    // Hacer petición AJAX para obtener los datos del producto
    fetch(`obtener_producto.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Llenar el formulario con los datos
                document.getElementById('editarId').value = data.producto.id;
                document.getElementById('editarNombre').value = data.producto.nombre;
                document.getElementById('editarPrecio').value = data.producto.precio;
                document.getElementById('editarStock').value = data.producto.stock;
                document.getElementById('editarCategoria').value = data.producto.categoria_id;
                
                // Mostrar modal
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

// Muestra el modal de confirmación para eliminar un producto
function confirmarEliminar(id, nombre) {
    document.getElementById('eliminarId').value = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    mostrarModal('modalEliminar');
}

// Eventos del DOM para el manejo de modales
// Cerrar modal al hacer clic fuera de él
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// Cerrar modales con la tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modales = document.querySelectorAll('.modal.active');
        modales.forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// Validación de formularios y eventos cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Formatear números en inputs de precio
    const inputsPrecios = document.querySelectorAll('input[name="precio"]');
    inputsPrecios.forEach(input => {
        input.addEventListener('input', function(e) {
            // Permitir solo números y punto decimal
            let value = e.target.value.replace(/[^0-9.]/g, '');
            
            // Permitir solo un punto decimal
            const puntos = value.split('.').length - 1;
            if (puntos > 1) {
                value = value.substring(0, value.lastIndexOf('.'));
            }
            
            e.target.value = value;
        });
    });
    
    // Validar que el stock sea un número entero positivo
    const inputsStock = document.querySelectorAll('input[name="stock"]');
    inputsStock.forEach(input => {
        input.addEventListener('input', function(e) {
            // Permitir solo números enteros
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    });
    
    // Auto-submit en filtros de búsqueda
    const busquedaInput = document.querySelector('input[name="busqueda"]');
    if (busquedaInput) {
        let timeout;
        busquedaInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.form.submit();
            }, 500); // Buscar después de 500ms de parar de escribir
        });
    }
});

// Manejo de mensajes de éxito/error desde la URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    const tipo = urlParams.get('tipo');
    
    if (mensaje && tipo) {
        mostrarNotificacion(mensaje, tipo);
        
        // Limpiar la URL
        const newUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, newUrl);
    }
});

// Función para mostrar notificaciones temporales
function mostrarNotificacion(mensaje, tipo) {
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
    
    if (tipo === 'success') {
        notificacion.style.background = '#28a745';
        notificacion.innerHTML = `✅ ${mensaje}`;
    } else if (tipo === 'error') {
        notificacion.style.background = '#dc3545';
        notificacion.innerHTML = `❌ ${mensaje}`;
    }
    
    document.body.appendChild(notificacion);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        notificacion.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => {
            notificacion.remove();
        }, 300);
    }, 5000);
}

// Añadir estilos para las animaciones
const style = document.createElement('style');
style.textContent = `
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