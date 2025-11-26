<?php
require_once 'config.php';
verificarLogin();

verificarLogin();

// --- SEGURIDAD: Verificar si es administrador ---
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'error' => 'Acceso denegado. No tienes permisos de administrador.'
    ]);
    exit;
}

header('Content-Type: application/json');

$pdo = conectarDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'listar':
                // Obtener todos los usuarios
                $stmt = $pdo->query("SELECT id, usuario, nombre_completo, email FROM usuarios ORDER BY usuario");
                $usuarios = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'usuarios' => $usuarios
                ]);
                break;
                
            case 'obtener':
                $id = intval($_POST['id'] ?? 0);
                
                if ($id <= 0) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID de usuario invÃ¡lido'
                    ]);
                    exit;
                }
                
                $stmt = $pdo->prepare("SELECT id, usuario, nombre_completo, email FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch();
                
                if ($usuario) {
                    echo json_encode([
                        'success' => true,
                        'usuario' => $usuario
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Usuario no encontrado'
                    ]);
                }
                break;
                
            case 'agregar':
                $usuario = trim($_POST['usuario'] ?? '');
                $contrasena = trim($_POST['contrasena'] ?? '');
                $nombre_completo = trim($_POST['nombre_completo'] ?? '');
                $email = trim($_POST['email'] ?? '');
                
                // Validaciones
                if (empty($usuario) || empty($contrasena) || empty($nombre_completo) || empty($email)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Todos los campos son obligatorios'
                    ]);
                    exit;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El email no es vÃ¡lido'
                    ]);
                    exit;
                }
                
                // Verificar si el usuario ya existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                $stmt->execute([$usuario]);
                
                if ($stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El nombre de usuario ya estÃ¡ en uso'
                    ]);
                    exit;
                }
                
                // Verificar si el email ya existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El email ya estÃ¡ registrado'
                    ]);
                    exit;
                }
                
                // Insertar nuevo usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, contrasena, nombre_completo, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$usuario, $contrasena, $nombre_completo, $email]);
                
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Usuario agregado exitosamente',
                    'id' => $pdo->lastInsertId()
                ]);
                break;
                
            case 'editar':
                $id = intval($_POST['id'] ?? 0);
                $usuario = trim($_POST['usuario'] ?? '');
                $nombre_completo = trim($_POST['nombre_completo'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $contrasena = trim($_POST['contrasena'] ?? '');
                
                // Validaciones
                if ($id <= 0) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID de usuario invÃ¡lido'
                    ]);
                    exit;
                }
                
                if (empty($usuario) || empty($nombre_completo) || empty($email)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El usuario, nombre completo y email son obligatorios'
                    ]);
                    exit;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El email no es vÃ¡lido'
                    ]);
                    exit;
                }
                
                // Verificar que el usuario existe
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El usuario no existe'
                    ]);
                    exit;
                }
                
                // Verificar si el nombre de usuario ya existe (excepto el actual)
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
                $stmt->execute([$usuario, $id]);
                
                if ($stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El nombre de usuario ya estÃ¡ en uso'
                    ]);
                    exit;
                }
                
                // Verificar si el email ya existe (excepto el actual)
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $stmt->execute([$email, $id]);
                
                if ($stmt->fetch()) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El email ya estÃ¡ registrado'
                    ]);
                    exit;
                }
                
                // Actualizar usuario
                if (!empty($contrasena)) {
                    // Si se proporcionÃ³ una nueva contraseÃ±a, actualizarla tambiÃ©n
                    $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre_completo = ?, email = ?, contrasena = ? WHERE id = ?");
                    $stmt->execute([$usuario, $nombre_completo, $email, $contrasena, $id]);
                } else {
                    // Si no se proporcionÃ³ contraseÃ±a, no actualizarla
                    $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre_completo = ?, email = ? WHERE id = ?");
                    $stmt->execute([$usuario, $nombre_completo, $email, $id]);
                }
                
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Usuario actualizado exitosamente'
                ]);
                break;
                
            case 'eliminar':
                $id = intval($_POST['id'] ?? 0);
                
                if ($id <= 0) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'ID de usuario invÃ¡lido'
                    ]);
                    exit;
                }
                
                // No permitir eliminar el usuario actual
                if ($id == $_SESSION['usuario_id']) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'No puedes eliminar tu propio usuario'
                    ]);
                    exit;
                }
                
                // Verificar que el usuario existe
                $stmt = $pdo->prepare("SELECT usuario FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch();
                
                if (!$usuario) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'El usuario no existe'
                    ]);
                    exit;
                }
                
                // Eliminar el usuario
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                
                echo json_encode([
                    'success' => true,
                    'mensaje' => "Usuario '{$usuario['usuario']}' eliminado exitosamente"
                ]);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'error' => 'AcciÃ³n no vÃ¡lida'
                ]);
                break;
        }
    } catch (Exception $e) {
        error_log("Error en gestionar_usuarios.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'MÃ©todo no permitido'
    ]);
}
?>