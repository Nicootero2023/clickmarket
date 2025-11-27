<?php
require_once 'config.php';
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
                // Ahora traemos también el rol
                $stmt = $pdo->query("SELECT id, usuario, nombre_completo, email, rol FROM usuarios ORDER BY usuario");
                $usuarios = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'usuarios' => $usuarios
                ]);
                break;
                
            case 'obtener':
                $id = intval($_POST['id'] ?? 0);
                // Traemos el rol para llenar el formulario de edición
                $stmt = $pdo->prepare("SELECT id, usuario, nombre_completo, email, rol FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                $usuario = $stmt->fetch();
                
                if ($usuario) {
                    echo json_encode(['success' => true, 'usuario' => $usuario]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
                }
                break;
                
            case 'agregar':
                $usuario = trim($_POST['usuario'] ?? '');
                $contrasena = trim($_POST['contrasena'] ?? '');
                $nombre_completo = trim($_POST['nombre_completo'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $rol = trim($_POST['rol'] ?? 'usuario'); // Recibimos el rol
                
                if (empty($usuario) || empty($contrasena) || empty($nombre_completo) || empty($email)) {
                    echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
                    exit;
                }
                
                // Validar duplicados (usuario o email)
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
                $stmt->execute([$usuario, $email]);
                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'error' => 'El usuario o email ya existen']);
                    exit;
                }
                
                // Encriptar contraseña
                $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);
                
                // INSERTAR CON ROL
                $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, contrasena, nombre_completo, email, rol) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$usuario, $passwordHash, $nombre_completo, $email, $rol]);
                
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
                $rol = trim($_POST['rol'] ?? 'usuario'); // Recibimos el rol
                
                if ($id <= 0 || empty($usuario) || empty($nombre_completo) || empty($email)) {
                    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                    exit;
                }

                // Evitar que un admin se quite permisos a sí mismo por error
                if ($id == $_SESSION['usuario_id'] && $rol !== 'admin') {
                    echo json_encode(['success' => false, 'error' => 'No puedes quitarte el rol de administrador a ti mismo']);
                    exit;
                }
                
                // Actualizar usuario
                if (!empty($contrasena)) {
                    $passwordHash = password_hash($contrasena, PASSWORD_DEFAULT);
                    // UPDATE CON ROL Y CONTRASEÑA
                    $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre_completo = ?, email = ?, rol = ?, contrasena = ? WHERE id = ?");
                    $stmt->execute([$usuario, $nombre_completo, $email, $rol, $passwordHash, $id]);
                } else {
                    // UPDATE CON ROL (SIN CONTRASEÑA)
                    $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, nombre_completo = ?, email = ?, rol = ? WHERE id = ?");
                    $stmt->execute([$usuario, $nombre_completo, $email, $rol, $id]);
                }
                
                echo json_encode(['success' => true, 'mensaje' => 'Usuario actualizado exitosamente']);
                break;
                
            case 'eliminar':
                $id = intval($_POST['id'] ?? 0);
                if ($id == $_SESSION['usuario_id']) {
                    echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propio usuario']);
                    exit;
                }
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'mensaje' => "Usuario eliminado exitosamente"]);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Acción no válida']);
                break;
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
    }
}
?>