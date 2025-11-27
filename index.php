<?php
// ============================================================================
// PÁGINA PRINCIPAL - Dashboard e inventario de productos
// ============================================================================

require_once 'config.php';
verificarLogin(); // Verificar que el usuario esté autenticado

// ============================================================================
// OBTENCIÓN DE ESTADÍSTICAS
// ============================================================================

// Obtener conteos de productos (total, en stock, stock bajo, sin stock)
$stats = obtenerEstadisticas();

// ============================================================================
// OBTENCIÓN DE PARÁMETROS DE FILTRADO
// ============================================================================

// Obtener parámetros GET para filtros
$busqueda = $_GET['busqueda'] ?? ''; // Búsqueda por nombre/código
$categoria = $_GET['categoria'] ?? ''; // Filtro por categoría
$estado_stock = $_GET['estado_stock'] ?? ''; // Filtro por estado de stock

// ============================================================================
// CONFIGURACIÓN DE PAGINACIÓN
// ============================================================================

$registrosPorPagina = 10; // Cantidad de productos por página
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Validar que la página actual sea positiva
if ($paginaActual < 1) $paginaActual = 1;

// Calcular el índice de inicio para LIMIT
$inicio = ($paginaActual - 1) * $registrosPorPagina;

// ============================================================================
// CONEXIÓN A BASE DE DATOS
// ============================================================================

// Inicializar conexión PDO
$pdo = conectarDB();

// ============================================================================
// CONSTRUCCIÓN DE CONDICIONES DE FILTRO
// ============================================================================

// Inicializar condiciones SQL
$condiciones = "WHERE 1=1";
$params = [];

// --- FILTRO: Búsqueda por nombre o código ---
if (!empty($busqueda)) {
    $condiciones .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// --- FILTRO: Categoría específica ---
if (!empty($categoria)) {
    $condiciones .= " AND p.categoria_id = ?";
    $params[] = $categoria;
}

// --- FILTRO: Estado de stock ---
if (!empty($estado_stock)) {
    switch ($estado_stock) {
        case 'en_stock':
            // Productos con 5 o más unidades
            $condiciones .= " AND p.stock >= 5";
            break;
        case 'stock_bajo':
            // Productos con 1-4 unidades
            $condiciones .= " AND p.stock > 0 AND p.stock < 5";
            break;
        case 'sin_stock':
            // Productos con 0 unidades
            $condiciones .= " AND p.stock = 0";
            break;
    }
}

// ============================================================================
// CONSULTA 1: CONTAR TOTAL DE REGISTROS (para paginación)
// ============================================================================

// SQL para contar cuántos productos coinciden con los filtros
$sqlTotal = "SELECT COUNT(*) as total FROM productos p $condiciones";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetch()['total'];

// Calcular total de páginas (redondear hacia arriba)
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// ============================================================================
// CONSULTA 2: OBTENER PRODUCTOS DE LA PÁGINA ACTUAL
// ============================================================================

// SQL para obtener solo los productos de esta página con LIMIT
$sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        $condiciones 
        ORDER BY p.codigo 
        LIMIT $inicio, $registrosPorPagina";

// Preparar y ejecutar consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// ============================================================================
// OBTENER CATEGORÍAS PARA SELECT DE FILTRO
// ============================================================================

// Consulta para obtener todas las categorías ordenadas
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clickmarket - Gestion de Inventario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- ====================================================================
         BARRA DE NAVEGACIÓN SUPERIOR
         ==================================================================== -->
    <div class="header">
        <!-- Logo e información de la aplicación -->
        <div class="logo">
            <div class="logo-icon">C</div>
            <div>
                Clickmarket
                <div class="subtitle">Gestion de inventario de productos</div>
            </div>
        </div>  
         <!-- Menú de usuario (botones y opciones) -->
         <div class="user-menu">
            <!-- Nombre del usuario logueado -->
            <span class="user-info"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'usuario'); ?></span>

            <!-- Botón Gestionar Usuarios (solo si es admin) -->
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                <button class="btn btn-primary" onclick="abrirModalUsuarios()">
                    👥 Usuarios
                </button>
            <?php endif; ?>
            
            <!-- Botón Gestionar Categorías -->
            <button class="btn btn-primary" onclick="abrirModalCategorias()">
                📁 Categorías
            </button>
            
            <!-- Botón Generar Reporte (descarga Excel) -->
            <a href="generar_reporte.php" class="btn btn-primary">
                📊 Reporte
            </a>
            
            <!-- Botón Agregar Nuevo Producto -->
            <button class="btn btn-primary" onclick="mostrarModalAgregar()">
                + Producto
            </button>
            
            <!-- Botón Cerrar Sesión -->
            <a href="logout.php" class="btn btn-outline">
                Salir
            </a>
        </div>
    </div>
    
    <!-- ====================================================================
         CONTENEDOR PRINCIPAL
         ==================================================================== -->
    <div class="container">
        <!-- ================================================================
             TARJETAS DE ESTADÍSTICAS (Dashboard)
             ================================================================ -->
        <div class="dashboard-cards">
            <!-- Tarjeta: Total de productos -->
            <div class="card card-total">
                <div class="card-number"><?php echo $stats['total']; ?></div>
                <div class="card-label">Total Productos</div>
            </div>
            
            <!-- Tarjeta: Productos en stock -->
            <div class="card card-en-stock">
                <div class="card-number"><?php echo $stats['en_stock']; ?></div>
                <div class="card-label">En Stock</div>
            </div>
            
            <!-- Tarjeta: Productos con stock bajo -->
            <div class="card card-stock-bajo">
                <div class="card-number"><?php echo $stats['stock_bajo']; ?></div>
                <div class="card-label">Stock Bajo</div>
            </div>
            
            <!-- Tarjeta: Productos sin stock -->
            <div class="card card-sin-stock">
                <div class="card-number"><?php echo $stats['sin_stock']; ?></div>
                <div class="card-label">Sin Stock</div>
            </div>
        </div>

        <!-- ================================================================
             SECCIÓN PRINCIPAL DE CONTENIDO
             ================================================================ -->
        <div class="content-section">
            <!-- ============================================================
                 CABECERA CON TÍTULO Y FILTROS
                 ============================================================ -->
            <div class="section-header">
                <!-- Información del inventario -->
                <div>
                    <h2 class="section-title">Inventario de Productos (<?php echo count($productos); ?>)</h2>
                    <p style="color: #666; font-size: 0.9rem;">Mostrando <?php echo count($productos); ?> de <?php echo $stats['total']; ?> productos</p>
                </div>
                
                <!-- Formulario de filtros y búsqueda -->
                <div class="filters">
                    <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
                        <!-- Input de búsqueda por nombre o código -->
                        <div class="search-box">
                            <input type="text" name="busqueda" placeholder="Buscar productos por nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        </div>
                        
                        <!-- Select de filtro por categoría -->
                        <select name="categoria" class="filter-select" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- Select de filtro por estado de stock -->
                        <select name="estado_stock" class="filter-select" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <option value="en_stock" <?php echo $estado_stock == 'en_stock' ? 'selected' : ''; ?>>En stock</option>
                            <option value="stock_bajo" <?php echo $estado_stock == 'stock_bajo' ? 'selected' : ''; ?>>Stock bajo</option>
                            <option value="sin_stock" <?php echo $estado_stock == 'sin_stock' ? 'selected' : ''; ?>>Sin stock</option>
                        </select>
                        
                        <!-- Botón enviar filtros -->
                        <button type="submit" class="btn btn-outline">Filtros</button>
                    </form>
                </div>
            </div>

            <!-- ============================================================
                 TABLA DE PRODUCTOS
                 ============================================================ -->
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Mostrar productos o mensaje si no hay -->
                        <?php if (count($productos) > 0): ?>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td>$ <?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                    <td>
                                        <!-- Badge con color según estado de stock -->
                                        <span class="estado <?php echo obtenerEstadoStock($producto['stock']); ?>">
                                            <?php echo obtenerTextoEstado($producto['stock']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Botones de acciones (Editar/Eliminar) -->
                                        <div class="actions">
                                            <button class="btn-icon btn-edit" onclick="editarProducto(<?php echo $producto['id']; ?>)" title="Editar">
                                                ✏️
                                            </button>
                                            <button class="btn-icon btn-delete" onclick="confirmarEliminar(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>')" title="Eliminar">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Mensaje cuando no hay productos -->
                            <tr>
                                <td colspan="7" style="text-align: center; color: #666;">No se encontraron productos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- ========================================================
                     PAGINACIÓN
                     ======================================================== -->
                <?php if ($totalPaginas > 1): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #eee;">
                        <!-- Información de página actual -->
                        <div style="color: #666; font-size: 0.9rem;">
                            Página <strong><?php echo $paginaActual; ?></strong> de <strong><?php echo $totalPaginas; ?></strong>
                        </div>
                        
                        <!-- Botones de navegación de páginas -->
                        <div style="display: flex; gap: 0.5rem;">
                            <!-- Botón Anterior -->
                            <?php if ($paginaActual > 1): ?>
                                <a href="?pagina=<?php echo $paginaActual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $categoria; ?>&estado_stock=<?php echo $estado_stock; ?>" 
                                   class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">
                                   ← Anterior
                                </a>
                            <?php endif; ?>

                            <!-- Números de página (con lógica para mostrar puntos suspensivos) -->
                            <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                                <?php if ($i == $paginaActual || $i == 1 || $i == $totalPaginas || ($i >= $paginaActual - 1 && $i <= $paginaActual + 1)): ?>
                                    <!-- Mostrar número de página -->
                                    <a href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $categoria; ?>&estado_stock=<?php echo $estado_stock; ?>" 
                                       class="btn <?php echo $i == $paginaActual ? 'btn-primary' : 'btn-outline'; ?>" 
                                       style="padding: 0.4rem 0.8rem; font-size: 0.9rem; <?php echo $i == $paginaActual ? 'background: #667eea; border-color: #667eea;' : ''; ?>">
                                       <?php echo $i; ?>
                                    </a>
                                <?php elseif ($i == $paginaActual - 2 || $i == $paginaActual + 2): ?>
                                    <!-- Mostrar puntos suspensivos -->
                                    <span style="padding: 0.4rem; color: #999;">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Botón Siguiente -->
                            <?php if ($paginaActual < $totalPaginas): ?>
                                <a href="?pagina=<?php echo $paginaActual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $categoria; ?>&estado_stock=<?php echo $estado_stock; ?>" 
                                   class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.9rem;">
                                   Siguiente →
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         MODALES - Diálogos para agregar, editar y eliminar productos
         ==================================================================== -->

    <!-- Modal Agregar Producto -->
    <div class="modal" id="modalAgregar">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nuevo Producto</h3>
                <button class="modal-close" onclick="cerrarModal('modalAgregar')">&times;</button>
            </div>
            <p class="modal-description">Completa los campos para registrar un nuevo producto en el inventario.</p>
            <form id="formAgregar" action="procesar.php" method="POST">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="form-group">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" name="nombre" class="form-input" placeholder="Ingresa el nombre del producto" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Precio (ARS)</label>
                    <input type="number" name="precio" class="form-input" placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-input" placeholder="0" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalAgregar')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Producto -->
    <div class="modal" id="modalEditar">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Editar Producto</h3>
                <button class="modal-close" onclick="cerrarModal('modalEditar')">&times;</button>
            </div>
            <p class="modal-description">Modifica la informacion del producto seleccionado.</p>
            <form id="formEditar" action="procesar.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="editarId">
                
                <div class="form-group">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" name="nombre" id="editarNombre" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Precio (ARS)</label>
                    <input type="number" name="precio" id="editarPrecio" class="form-input" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" id="editarStock" class="form-input" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" id="editarCategoria" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Eliminar Producto -->
    <div class="modal" id="modalEliminar">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">¿Eliminar producto?</h3>
                <button class="modal-close" onclick="cerrarModal('modalEliminar')">&times;</button>
            </div>
            <p class="modal-description">Esta accion no se puede deshacer. El producto "<span id="nombreEliminar"></span>" sera eliminado permanentemente del inventario.</p>
            <form action="procesar.php" method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" id="eliminarId">
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="cerrarModal('modalEliminar')">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Gestionar Categorías -->
    <div class="modal" id="modalCategorias">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Gestionar Categorías</h3>
                <button class="modal-close" onclick="cerrarModal('modalCategorias')">&times;</button>
            </div>
            <p class="modal-description">Agrega o elimina categorías para organizar tus productos.</p>
            
            <!-- Formulario para agregar nueva categoría -->
            <form id="formAgregarCategoria" onsubmit="agregarCategoria(event)" style="margin-bottom: 2rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Nueva Categoría</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="nombreCategoria" class="form-input" placeholder="Nombre de la categoría" required style="flex: 1;">
                        <button type="submit" class="btn btn-primary">Agregar</button>
                    </div>
                </div>
            </form>
            
            <!-- Lista de categorías existentes -->
            <div>
                <h4 style="margin-bottom: 1rem; color: #333; font-size: 1rem;">Categorías Existentes</h4>
                <div id="listaCategorias" style="max-height: 300px; overflow-y: auto;">
                    <!-- Las categorías se cargarán dinámicamente aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gestionar Usuarios -->
    <div class="modal" id="modalUsuarios">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title">Gestionar Usuarios</h3>
                <button class="modal-close" onclick="cerrarModal('modalUsuarios')">&times;</button>
            </div>
            <p class="modal-description">Administra los usuarios que tienen acceso al sistema.</p>
            
            <!-- Botón para agregar nuevo usuario -->
            <div style="margin-bottom: 2rem;">
                <button onclick="mostrarModalAgregarUsuario()" class="btn btn-primary">
                    + Agregar Nuevo Usuario
                </button>
            </div>
            
            <!-- Lista de usuarios existentes -->
            <div>
                <h4 style="margin-bottom: 1rem; color: #333; font-size: 1rem;">Usuarios Registrados</h4>
                <div id="listaUsuarios" style="max-height: 400px; overflow-y: auto;">
                    <!-- Los usuarios se cargarán dinámicamente aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Usuario -->
    <div class="modal" id="modalAgregarUsuario">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nuevo Usuario</h3>
                <button class="modal-close" onclick="volverAListaUsuarios()">&times;</button>
            </div>
            <p class="modal-description">Completa los campos para registrar un nuevo usuario.</p>
            
            <form id="formAgregarUsuario" onsubmit="agregarUsuario(event)">
                <div class="form-group">
                    <label class="form-label">Nombre de Usuario</label>
                    <input type="text" id="nuevoUsuario" class="form-input" placeholder="Ingresa el nombre de usuario" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" id="nuevaContrasena" class="form-input" placeholder="Ingresa la contraseña" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" id="nuevoNombreCompleto" class="form-input" placeholder="Ingresa el nombre completo" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="nuevoEmail" class="form-input" placeholder="correo@ejemplo.com" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="volverAListaUsuarios()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal" id="modalEditarUsuario">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Editar Usuario</h3>
                <button class="modal-close" onclick="volverAListaUsuarios()">&times;</button>
            </div>
            <p class="modal-description">Modifica la información del usuario. Deja la contraseña vacía si no deseas cambiarla.</p>
            
            <form id="formEditarUsuario" onsubmit="guardarEdicionUsuario(event)">
                <input type="hidden" id="editarUsuarioId">
                
                <div class="form-group">
                    <label class="form-label">Nombre de Usuario</label>
                    <input type="text" id="editarUsuarioNombre" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" id="editarUsuarioNombreCompleto" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="editarUsuarioEmail" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nueva Contraseña (opcional)</label>
                    <input type="password" id="editarUsuarioContrasena" class="form-input" placeholder="Dejar vacío para no cambiar">
                    <small style="color: #666; font-size: 0.85rem;">Solo completa este campo si deseas cambiar la contraseña</small>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="volverAListaUsuarios()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====================================================================
         SCRIPTS - Cargar archivos JavaScript
         ==================================================================== -->
    <script src="script.js"></script>
    <script src="categorias.js"></script>
    <script src="usuarios.js"></script>
</body>
</html>