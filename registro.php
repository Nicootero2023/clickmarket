<?php
require_once 'config.php';

// Si ya está logueado, redirigir al dashboard
if (usuarioLogueado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $confirmar_contrasena = trim($_POST['confirmar_contrasena'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validar que todos los campos estén llenos
    if (empty($usuario) || empty($contrasena) || empty($confirmar_contrasena) || empty($nombre_completo) || empty($email)) {
        $error = 'Todos los campos son obligatorios';
    } elseif ($contrasena !== $confirmar_contrasena) {
        $error = 'Las contraseñas no coinciden';
    } else {
        $pdo = conectarDB();
        
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        
        if ($stmt->fetch()) {
            $error = 'El nombre de usuario ya está en uso';
        } else {
            // Verificar si el email ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'El email ya está registrado';
            } else {
                // Insertar nuevo usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, contrasena, nombre_completo, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$usuario, $contrasena, $nombre_completo, $email]);
                
                $success = 'Registro exitoso. Redirigiendo al login...';
                header("refresh:2;url=login.php");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clickmarket - Registro</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon">C</div>
                <h1>Clickmarket</h1>
                <p>Crea tu cuenta para acceder</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre_completo" class="form-input" placeholder="Ingresa tu nombre completo" value="<?php echo htmlspecialchars($_POST['nombre_completo'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="Ingresa tu email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-input" placeholder="Elige un nombre de usuario" value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="password-container">
                        <input type="password" name="contrasena" id="contrasena" class="form-input" placeholder="Crea una contraseña" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('contrasena', 'eyeIcon1')" aria-label="Mostrar contraseña">
                            <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmar Contraseña</label>
                    <div class="password-container">
                        <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-input" placeholder="Confirma tu contraseña" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirmar_contrasena', 'eyeIcon2')" aria-label="Mostrar contraseña">
                            <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Registrarse
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem; color: #666; font-size: 0.9rem;">
                ¿Ya tienes cuenta? <a href="login.php" style="color: #667eea; text-decoration: none; font-weight: 500;">Inicia sesión</a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>
</html>