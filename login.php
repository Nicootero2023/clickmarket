<?php
require_once 'config.php';

// Si ya está logueado, redirigir al dashboard
if (usuarioLogueado()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_POST) {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    
    if (!empty($usuario) && !empty($contrasena)) {
        $pdo = conectarDB();
        
        // 1. Buscamos id, usuario, contrasena y rol
        $stmt = $pdo->prepare("SELECT id, usuario, contrasena, rol FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $usuarioEncontrado = $stmt->fetch();
        
        // 2. VERIFICAMOS LA CONTRASEÑA (ESTO ES LO QUE FALTABA)
        if ($usuarioEncontrado && password_verify($contrasena, $usuarioEncontrado['contrasena'])) {
            
            // Login correcto: Guardamos todo en la sesión
            $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
            $_SESSION['usuario_nombre'] = $usuarioEncontrado['usuario'];
            $_SESSION['usuario_rol'] = $usuarioEncontrado['rol'];
            
            header('Location: index.php');
            exit;
            
        } else {
            // Usuario no existe O contraseña incorrecta
            $error = 'Usuario o contraseña incorrectos';
        }
    } else {
        $error = 'Por favor completa todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clickmarket - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-icon">C</div>
                <h1>Clickmarket</h1>
                <p>Ingresa tus credenciales para acceder</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-input" placeholder="Ingresa tu usuario" value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="password-container">
                        <input type="password" name="contrasena" id="contrasena" class="form-input" placeholder="Ingresa tu contraseña" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Mostrar contraseña">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="credentials-info">
                <strong>Credenciales de acceso:</strong><br>
                <strong>Usuario:</strong> usuario<br>
                <strong>Contraseña:</strong> usuario123
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem; color: #666; font-size: 0.9rem;">
                ¿No tienes cuenta? <a href="registro.php" style="color: #667eea; text-decoration: none; font-weight: 500;">Regístrate</a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('contrasena');
            const eyeIcon = document.getElementById('eyeIcon');
            
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