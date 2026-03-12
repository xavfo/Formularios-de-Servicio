<?php
/**
 * Página de autenticación del panel de administración
 * 
 * Proporciona un formulario de login para que los administradores
 * se autentiquen con usuario y contraseña. Procesa las credenciales
 * y crea una sesión segura si son válidas.
 * 
 * Requisitos: 1.1, 1.2, 1.3
 */

// Incluir módulos necesarios
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

// Iniciar sesión para manejar mensajes y tokens CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el usuario ya está autenticado, redirigir al dashboard
if (verificarSesion()) {
    header('Location: index.php');
    exit();
}

// Variables para mensajes
$error = '';
$mensajeInfo = '';

// Procesar formulario de login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener credenciales del formulario
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validar que los campos no estén vacíos
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, ingrese usuario y contraseña';
    } else {
        // Verificar credenciales
        if (verificarCredenciales($usuario, $password)) {
            // Credenciales válidas: crear sesión
            iniciarSesion($usuario);
            
            // Registrar login exitoso en el log
            registrarLog('login_exitoso', $usuario, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'
            ]);
            
            // Redirigir al dashboard
            header('Location: index.php');
            exit();
        } else {
            // Credenciales inválidas: mostrar error
            $error = 'Usuario o contraseña incorrectos';
            
            // Registrar intento fallido en el log
            registrarLog('login_fallido', $usuario, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
            ]);
        }
    }
}

// Verificar si hay mensajes de sesión (ej: sesión expirada)
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['mensaje'])) {
    $mensajeInfo = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

// Generar token CSRF para el formulario
$csrfToken = generarTokenCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Panel de Administración</h1>
                <p>Ingrese sus credenciales para acceder</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="mensaje mensaje-error" role="alert">
                    <strong>Error:</strong> <?php echo sanitizar($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($mensajeInfo)): ?>
                <div class="mensaje mensaje-info" role="alert">
                    <?php echo sanitizar($mensajeInfo); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" class="login-form">
                <?php echo campoTokenCSRF(); ?>
                
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input 
                        type="text" 
                        id="usuario" 
                        name="usuario" 
                        class="form-control" 
                        required 
                        autofocus
                        autocomplete="username"
                        value="<?php echo isset($_POST['usuario']) ? sanitizar($_POST['usuario']) : ''; ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <div class="login-footer">
                <p class="text-muted">
                    <small>Sistema de gestión de formularios de asesoría</small>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
