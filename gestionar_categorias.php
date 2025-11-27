<?php
// ============================================================================
// GESTIÓN DE CATEGORÍAS - API AJAX para administrar categorías de productos
// ============================================================================

require_once 'config.php';
verificarLogin(); // Verificar que el usuario esté autenticado

// Establecer tipo de respuesta como JSON
header('Content-Type: application/json');

// ============================================================================
// CONEXIÓN A BASE DE DATOS
// ============================================================================

// Inicializar conexión PDO
$pdo = conectarDB();

// ============================================================================
// VALIDACIÓN DE MÉTODO HTTP
// ============================================================================

// Solo procesar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la acción a realizar
    $accion = $_POST['accion'] ?? '';
    
    try {
        // ====================================================================
        // SWITCH DE ACCIONES
        // ====================================================================
        
        switch ($accion) {
            // ================================================================
            // ACCIÓN: LISTAR - Obtener todas las categorías con sus productos
            // ================================================================
            case 'listar':
                // Consulta SQL que obtiene categorías con conteo de productos asociados
                $stmt = $pdo->query("
                    SELECT c.id, c.nombre, COUNT(p.id) as productos_count 
                    FROM categorias c 
                    LEFT JOIN productos p ON c.id = p.categoria_id 
                    GROUP BY c.id, c.nombre 
                    ORDER BY c.nombre
                ");
                
                // Obtener todas las categorías
                $categorias = $stmt->fetchAll();
                
                // Devolver resultado en JSON
                echo json_encode([
                    'success' => true,
                    'categorias' => $categorias
                ]);
                break;
                
            // ================================================================
            // ACCIÓN: AGREGAR - Crear una nueva categoría
            // ================================================================
            case 'agregar':
                // Obtener y limpiar el nombre de la categoría
                $nombre = trim($_POST['nombre'] ?? '');
                
                // --- VALIDACIÓN: Nombre no vacío ---
                if (empty($nombre)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El nombre de la categoría es obligatorio'
                    ]);
                    exit;
                }
                
                // --- VALIDACIÓN: Verificar si ya existe una categoría con ese nombre ---
                $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
                $stmt->execute([$nombre]);
                
                if ($stmt->fetch()) {
                    // Ya existe una categoría con ese nombre
                    echo json_encode([
                        'success' => false,
                        'error' => 'Ya existe una categoría con ese nombre'
                    ]);
                    exit;
                }
                
                // --- INSERTAR: Nueva categoría en la base de datos ---
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                
                // Devolver éxito con ID de la nueva categoría
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Categoría agregada exitosamente',
                    'id' => $pdo->lastInsertId()
                ]);
                break;
                
            // ================================================================
            // ACCIÓN: ELIMINAR - Borrar una categoría
            // ================================================================
            case 'eliminar':
                // Obtener ID de la categoría a eliminar
                $id = intval($_POST['id'] ?? 0);
                
                // --- VALIDACIÓN: ID válido ---
                if ($id <= 0) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID de categoría inválido'
                    ]);
                    exit;
                }
                
                // --- VALIDACIÓN: Verificar que la categoría existe ---
                $stmt = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ?");
                $stmt->execute([$id]);
                $categoria = $stmt->fetch();
                
                if (!$categoria) {
                    // La categoría no existe
                    echo json_encode([
                        'success' => false,
                        'error' => 'La categoría no existe'
                    ]);
                    exit;
                }
                
                // --- ACTUALIZAR: Mover productos a "Sin categoría" ---
                // Todos los productos que pertenecen a esta categoría se actualizan
                $stmt = $pdo->prepare("UPDATE productos SET categoria_id = NULL WHERE categoria_id = ?");
                $stmt->execute([$id]);
                $productosActualizados = $stmt->rowCount(); // Contar cuántos se actualizaron
                
                // --- ELIMINAR: La categoría de la base de datos ---
                $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
                $stmt->execute([$id]);
                
                // --- CONSTRUIR MENSAJE DE RESPUESTA ---
                $mensaje = 'Categoría eliminada exitosamente';
                if ($productosActualizados > 0) {
                    // Si había productos, informar cuántos se movieron
                    $mensaje .= ". $productosActualizados producto(s) movidos a 'Sin categoría'";
                }
                
                // Devolver éxito
                echo json_encode([
                    'success' => true,
                    'mensaje' => $mensaje
                ]);
                break;
                
            // ================================================================
            // ACCIÓN POR DEFECTO - Acción no válida
            // ================================================================
            default:
                echo json_encode([
                    'success' => false,
                    'error' => 'Acción no válida'
                ]);
                break;
        }
        
    } catch (Exception $e) {
        // ====================================================================
        // MANEJO DE EXCEPCIONES
        // ====================================================================
        
        // Registrar el error en el log del servidor
        error_log("Error en gestionar_categorias.php: " . $e->getMessage());
        
        // Devolver error al cliente
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
    }
    
} else {
    // ========================================================================
    // ERROR: Método HTTP no permitido
    // ========================================================================
    
    // Si no es POST, rechazar la solicitud
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}
?>