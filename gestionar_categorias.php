<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'listar':
                // Obtener todas las categorías con conteo de productos
                $stmt = $pdo->query("
                    SELECT c.id, c.nombre, COUNT(p.id) as productos_count 
                    FROM categorias c 
                    LEFT JOIN productos p ON c.id = p.categoria_id 
                    GROUP BY c.id, c.nombre 
                    ORDER BY c.nombre
                ");
                $categorias = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'categorias' => $categorias
                ]);
                break;
                
            case 'agregar':
                $nombre = trim($_POST['nombre'] ?? '');
                
                if (empty($nombre)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El nombre de la categoría es obligatorio'
                    ]);
                    exit;
                }
                
                // Verificar si ya existe
                $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
                $stmt->execute([$nombre]);
                
                if ($stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Ya existe una categoría con ese nombre'
                    ]);
                    exit;
                }
                
                // Insertar nueva categoría
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Categoría agregada exitosamente',
                    'id' => $pdo->lastInsertId()
                ]);
                break;
                
            case 'eliminar':
                $id = intval($_POST['id'] ?? 0);
                
                if ($id <= 0) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID de categoría inválido'
                    ]);
                    exit;
                }
                
                // Verificar que la categoría existe
                $stmt = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ?");
                $stmt->execute([$id]);
                $categoria = $stmt->fetch();
                
                if (!$categoria) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'La categoría no existe'
                    ]);
                    exit;
                }
                
                // Actualizar productos de esta categoría a NULL (Sin categoría)
                $stmt = $pdo->prepare("UPDATE productos SET categoria_id = NULL WHERE categoria_id = ?");
                $stmt->execute([$id]);
                $productosActualizados = $stmt->rowCount();
                
                // Eliminar la categoría
                $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
                $stmt->execute([$id]);
                
                $mensaje = 'Categoría eliminada exitosamente';
                if ($productosActualizados > 0) {
                    $mensaje .= ". $productosActualizados producto(s) movidos a 'Sin categoría'";
                }
                
                echo json_encode([
                    'success' => true,
                    'mensaje' => $mensaje
                ]);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'error' => 'Acción no válida'
                ]);
                break;
        }
    } catch (Exception $e) {
        error_log("Error en gestionar_categorias.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}
?>