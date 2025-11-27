<?php
// ============================================================================
// OBTENER DATOS DE PRODUCTO - API AJAX para cargar producto en modal editar
// ============================================================================

require_once 'config.php';
verificarLogin();

// Establecer tipo de respuesta como JSON
header('Content-Type: application/json');

// ============================================================================
// VALIDACIÓN DE MÉTODO HTTP
// ============================================================================

// Solo permitir solicitudes GET (no POST, PUT, DELETE, etc.)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// ============================================================================
// OBTENCIÓN Y VALIDACIÓN DEL ID
// ============================================================================

// Obtener ID del producto desde parámetros GET
$id = intval($_GET['id'] ?? 0);

// Validar que el ID sea positivo
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

// ============================================================================
// CONSULTA A BASE DE DATOS
// ============================================================================

try {
    // Conectar a la base de datos
    $pdo = conectarDB();
    
    // Preparar consulta SQL para obtener producto por ID
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    
    // Ejecutar con el ID proporcionado
    $stmt->execute([$id]);
    
    // Obtener resultado (fetch devuelve un array o null)
    $producto = $stmt->fetch();
    
    // ========================================================================
    // MANEJO DE RESULTADO
    // ========================================================================
    
    if ($producto) {
        // Producto encontrado: devolver datos en JSON
        echo json_encode([
            'success' => true,
            'producto' => [
                'id' => $producto['id'],
                'codigo' => $producto['codigo'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'stock' => $producto['stock'],
                'categoria_id' => $producto['categoria_id']
            ]
        ]);
    } else {
        // Producto no encontrado
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    }
    
} catch (Exception $e) {
    // Capturar excepciones de base de datos
    error_log("Error en obtener_producto.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
}
?>