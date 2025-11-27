<?php
// ============================================================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// ============================================================================

// Credenciales de conexión a MySQL
define('DB_HOST', 'localhost');    // Servidor de base de datos
define('DB_NAME', 'clickmarket');  // Nombre de la base de datos
define('DB_USER', 'root');         // Usuario de MySQL
define('DB_PASS', '');             // Contraseña de MySQL (vacía por defecto en XAMPP)

// ============================================================================
// CONEXIÓN A BASE DE DATOS
// ============================================================================

/**
 * Establece conexión con la base de datos usando PDO
 * @return PDO Objeto de conexión a la base de datos
 * @throws PDOException Si la conexión falla
 */
function conectarDB() {
    try {
        // Crear instancia de PDO con conexión a MySQL
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS
        );
        
        // Configurar modo de error para excepciones
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Configurar modo de fetch para devolver arrays asociativos
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch(PDOException $e) {
        // Si hay error, mostrar mensaje y detener ejecución
        die("Error de conexión: " . $e->getMessage());
    }
}

// ============================================================================
// GESTIÓN DE SESIONES
// ============================================================================

// Iniciar sesión si no está ya activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario está logueado
 * @return bool True si el usuario está autenticado, False si no
 */
function usuarioLogueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Redirige al login si el usuario no está autenticado
 * Se usa en páginas protegidas que requieren login
 */
function verificarLogin() {
    if (!usuarioLogueado()) {
        // Si no está logueado, redirigir al formulario de login
        header('Location: login.php');
        exit;
    }
}

// ============================================================================
// FUNCIONES DE ESTADÍSTICAS DEL INVENTARIO
// ============================================================================

/**
 * Obtiene las estadísticas generales del inventario
 * @return array Array con conteos de: total, en_stock, stock_bajo, sin_stock
 */
function obtenerEstadisticas() {
    try {
        $pdo = conectarDB();
        
        // --- Total de productos ---
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
        $resultado = $stmt->fetch();
        $total = $resultado ? $resultado['total'] : 0;
        
        // --- Productos en stock (stock > 0) ---
        $stmt = $pdo->query("SELECT COUNT(*) as en_stock FROM productos WHERE stock > 0");
        $resultado = $stmt->fetch();
        $enStock = $resultado ? $resultado['en_stock'] : 0;
        
        // --- Productos con stock bajo (entre 1 y 4 unidades) ---
        $stmt = $pdo->query("SELECT COUNT(*) as stock_bajo FROM productos WHERE stock > 0 AND stock < 5");
        $resultado = $stmt->fetch();
        $stockBajo = $resultado ? $resultado['stock_bajo'] : 0;
        
        // --- Productos sin stock (stock = 0) ---
        $stmt = $pdo->query("SELECT COUNT(*) as sin_stock FROM productos WHERE stock = 0");
        $resultado = $stmt->fetch();
        $sinStock = $resultado ? $resultado['sin_stock'] : 0;
        
        // Retornar array con estadísticas
        return [
            'total' => $total,
            'en_stock' => $enStock,
            'stock_bajo' => $stockBajo,
            'sin_stock' => $sinStock
        ];
    } catch (Exception $e) {
        // En caso de error, registrar en log y retornar ceros
        error_log("Error en obtenerEstadisticas: " . $e->getMessage());
        return [
            'total' => 0,
            'en_stock' => 0,
            'stock_bajo' => 0,
            'sin_stock' => 0
        ];
    }
}

/**
 * Obtiene la clase CSS según el estado del stock de un producto
 * @param int $stock Cantidad de stock del producto
 * @return string Clase CSS: 'sin_stock', 'stock_bajo' o 'en_stock'
 */
function obtenerEstadoStock($stock) {
    $stock = intval($stock);
    
    if ($stock == 0) {
        return 'sin_stock';      // Sin stock
    } elseif ($stock < 5) {
        return 'stock_bajo';     // Stock bajo (1-4 unidades)
    } else {
        return 'en_stock';       // En stock (5+ unidades)
    }
}

/**
 * Obtiene el texto del estado del stock para mostrar al usuario
 * @param int $stock Cantidad de stock del producto
 * @return string Texto: 'Sin stock', 'Stock bajo' o 'En stock'
 */
function obtenerTextoEstado($stock) {
    $stock = intval($stock);
    
    if ($stock == 0) {
        return 'Sin stock';      // Sin stock
    } elseif ($stock < 5) {
        return 'Stock bajo';     // Stock bajo
    } else {
        return 'En stock';       // En stock
    }
}

// ============================================================================
// FUNCIONES AUXILIARES DE VALIDACIÓN Y LIMPIEZA
// ============================================================================

/**
 * Limpia y valida entrada de usuario para evitar XSS
 * @param string $data Dato a limpiar
 * @return string Dato limpio y escapado
 */
function limpiarEntrada($data) {
    // Remover tags HTML, espacios en blanco y escapar HTML
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Valida si un número está dentro de rangos permitidos
 * @param float $numero Número a validar
 * @param float $min Valor mínimo permitido (default: 0)
 * @param float|null $max Valor máximo permitido (default: null para sin límite)
 * @return float|false El número si es válido, false si no
 */
function validarNumero($numero, $min = 0, $max = null) {
    // Validar que sea un número válido
    $numero = filter_var($numero, FILTER_VALIDATE_FLOAT);
    if ($numero === false) return false;
    
    // Validar mínimo
    if ($numero < $min) return false;
    
    // Validar máximo si se especificó
    if ($max !== null && $numero > $max) return false;
    
    return $numero;
}
?>