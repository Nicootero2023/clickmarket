<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'clickmarket');
define('DB_USER', 'root');
define('DB_PASS', '');

// Función para establecer conexión con la base de datos usando
function conectarDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Inicialización de la sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está logueado
function usuarioLogueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Función para redirigir al login si no está logueado
function verificarLogin() {
    if (!usuarioLogueado()) {
        header('Location: login.php');
        exit;
    }
}

// Funciones para obtener estadísticas y estados del inventario
function obtenerEstadisticas() {
    try {
        $pdo = conectarDB();
        
        // Total de productos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
        $resultado = $stmt->fetch();
        $total = $resultado ? $resultado['total'] : 0;
        
        // En stock (más de 0)
        $stmt = $pdo->query("SELECT COUNT(*) as en_stock FROM productos WHERE stock > 0");
        $resultado = $stmt->fetch();
        $enStock = $resultado ? $resultado['en_stock'] : 0;
        
        // Stock bajo (entre 1 y 4)
        $stmt = $pdo->query("SELECT COUNT(*) as stock_bajo FROM productos WHERE stock > 0 AND stock < 5");
        $resultado = $stmt->fetch();
        $stockBajo = $resultado ? $resultado['stock_bajo'] : 0;
        
        // Sin stock (0)
        $stmt = $pdo->query("SELECT COUNT(*) as sin_stock FROM productos WHERE stock = 0");
        $resultado = $stmt->fetch();
        $sinStock = $resultado ? $resultado['sin_stock'] : 0;
        
        return [
            'total' => $total,
            'en_stock' => $enStock,
            'stock_bajo' => $stockBajo,
            'sin_stock' => $sinStock
        ];
    } catch (Exception $e) {
        error_log("Error en obtenerEstadisticas: " . $e->getMessage());
        return [
            'total' => 0,
            'en_stock' => 0,
            'stock_bajo' => 0,
            'sin_stock' => 0
        ];
    }
}

// Función para obtener el estado de stock de un producto
function obtenerEstadoStock($stock) {
    $stock = intval($stock);
    if ($stock == 0) {
        return 'sin_stock';
    } elseif ($stock < 5) {
        return 'stock_bajo';
    } else {
        return 'en_stock';
    }
}

// Función para obtener el texto del estado de stock
function obtenerTextoEstado($stock) {
    $stock = intval($stock);
    if ($stock == 0) {
        return 'Sin stock';
    } elseif ($stock < 5) {
        return 'Stock bajo';
    } else {
        return 'En stock';
    }
}

// Funciones de utilidad para validación y limpieza de datos
function limpiarEntrada($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Función para validar números
function validarNumero($numero, $min = 0, $max = null) {
    $numero = filter_var($numero, FILTER_VALIDATE_FLOAT);
    if ($numero === false) return false;
    if ($numero < $min) return false;
    if ($max !== null && $numero > $max) return false;
    return $numero;
}
?>