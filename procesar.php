<?php
require_once 'config.php';
verificarLogin();

$pdo = conectarDB();

// Función para generar códigos únicos de producto
function generarCodigoProducto($pdo) {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(codigo, 6) AS UNSIGNED)) as max_num FROM productos WHERE codigo LIKE 'prod-%'");
    $result = $stmt->fetch();
    $numero = ($result['max_num'] ?? 0) + 1;
    return sprintf('prod-%03d', $numero);
}

// Función para redireccionar con mensajes de respuesta
function redirigirConMensaje($mensaje, $tipo = 'success') {
    $url = 'index.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo;
    header("Location: $url");
    exit;
}

// Procesamiento de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    try {
        switch ($accion) {
            // Proceso de agregar nuevo producto
            case 'agregar':
                $nombre = trim($_POST['nombre'] ?? '');
                $precio = floatval($_POST['precio'] ?? 0);
                $stock = intval($_POST['stock'] ?? 0);
                $categoria_id = intval($_POST['categoria_id'] ?? 0);
                
                // Validaciones
                if (empty($nombre)) {
                    redirigirConMensaje('El nombre del producto es obligatorio', 'error');
                }
                
                if ($precio <= 0) {
                    redirigirConMensaje('El precio debe ser mayor a 0', 'error');
                }
                
                if ($stock < 0) {
                    redirigirConMensaje('El stock no puede ser negativo', 'error');
                }
                
                if ($categoria_id <= 0) {
                    redirigirConMensaje('Debes seleccionar una categoría válida', 'error');
                }
                
                // Verificar que la categoría existe
                $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
                $stmt->execute([$categoria_id]);
                if (!$stmt->fetch()) {
                    redirigirConMensaje('La categoría seleccionada no existe', 'error');
                }
                
                // Generar código único
                $codigo = generarCodigoProducto($pdo);
                
                // Insertar producto
                $stmt = $pdo->prepare("INSERT INTO productos (codigo, nombre, precio, stock, categoria_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$codigo, $nombre, $precio, $stock, $categoria_id]);
                
                redirigirConMensaje('Producto agregado exitosamente');
                break;
                
            // Proceso de editar producto existente
            case 'editar':
                $id = intval($_POST['id'] ?? 0);
                $nombre = trim($_POST['nombre'] ?? '');
                $precio = floatval($_POST['precio'] ?? 0);
                $stock = intval($_POST['stock'] ?? 0);
                $categoria_id = intval($_POST['categoria_id'] ?? 0);
                
                // Validaciones
                if ($id <= 0) {
                    redirigirConMensaje('ID de producto inválido', 'error');
                }
                
                if (empty($nombre)) {
                    redirigirConMensaje('El nombre del producto es obligatorio', 'error');
                }
                
                if ($precio <= 0) {
                    redirigirConMensaje('El precio debe ser mayor a 0', 'error');
                }
                
                if ($stock < 0) {
                    redirigirConMensaje('El stock no puede ser negativo', 'error');
                }
                
                if ($categoria_id <= 0) {
                    redirigirConMensaje('Debes seleccionar una categoría válida', 'error');
                }
                
                // Verificar que el producto existe
                $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    redirigirConMensaje('El producto no existe', 'error');
                }
                
                // Verificar que la categoría existe
                $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
                $stmt->execute([$categoria_id]);
                if (!$stmt->fetch()) {
                    redirigirConMensaje('La categoría seleccionada no existe', 'error');
                }
                
                // Actualizar producto
                $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, precio = ?, stock = ?, categoria_id = ? WHERE id = ?");
                $stmt->execute([$nombre, $precio, $stock, $categoria_id, $id]);
                
                redirigirConMensaje('Producto actualizado exitosamente');
                break;
                
            // Proceso de eliminar producto
            case 'eliminar':
                $id = intval($_POST['id'] ?? 0);
                
                // Validaciones
                if ($id <= 0) {
                    redirigirConMensaje('ID de producto inválido', 'error');
                }
                
                // Verificar que el producto existe
                $stmt = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
                $stmt->execute([$id]);
                $producto = $stmt->fetch();
                
                if (!$producto) {
                    redirigirConMensaje('El producto no existe', 'error');
                }
                
                // Eliminar producto
                $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                $stmt->execute([$id]);
                
                redirigirConMensaje('Producto "' . $producto['nombre'] . '" eliminado exitosamente');
                break;
                
            default:
                redirigirConMensaje('Acción no válida', 'error');
                break;
        }
        
    } catch (Exception $e) {
        error_log("Error en procesar.php: " . $e->getMessage());
        redirigirConMensaje('Error interno del servidor. Inténtalo de nuevo.', 'error');
    }
    
} else {
    // Si no es POST, redirigir al índice
    header('Location: index.php');
    exit;
}
?>