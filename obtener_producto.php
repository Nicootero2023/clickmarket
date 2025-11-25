<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if ($producto) {
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
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    }
    
} catch (Exception $e) {
    error_log("Error en obtener_producto.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
}
?>