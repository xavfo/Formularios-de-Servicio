<?php
/**
 * Módulo de gestión de sesiones para el panel de administración
 * 
 * Este archivo contiene funciones para gestionar sesiones PHP seguras,
 * incluyendo inicio de sesión, verificación, expiración automática
 * y generación de tokens CSRF
 */

// Tiempo de expiración de sesión en segundos (30 minutos)
define('TIEMPO_EXPIRACION_SESION', 30 * 60);

/**
 * Inicia una sesión segura para el usuario
 * 
 * Crea una sesión PHP segura para el usuario autenticado,
 * genera un token CSRF único y establece el timestamp de
 * última actividad para control de expiración
 * 
 * @param string $usuario Nombre de usuario autenticado
 * @return void
 */
function iniciarSesion(string $usuario): void {
    // Iniciar sesión PHP si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar opciones de seguridad de sesión
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '0'); // Cambiar a '1' si se usa HTTPS
        ini_set('session.use_strict_mode', '1');
        
        session_start();
    }
    
    // Regenerar ID de sesión para prevenir session fixation
    session_regenerate_id(true);
    
    // Establecer datos de sesión
    $_SESSION['usuario'] = $usuario;
    $_SESSION['autenticado'] = true;
    $_SESSION['ultima_actividad'] = time();
    $_SESSION['ip_usuario'] = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Generar token CSRF único para esta sesión
    // Usar la función del módulo csrf.php para evitar duplicación
    require_once __DIR__ . '/csrf.php';
    generarTokenCSRF();
    
    // Actualizar último acceso en la base de datos
    actualizarUltimoAcceso($usuario);
}

/**
 * Verifica si existe una sesión activa válida
 * 
 * Comprueba que exista una sesión PHP activa, que el usuario
 * esté autenticado y que la sesión no haya expirado por inactividad
 * 
 * @return bool True si la sesión es válida, false en caso contrario
 */
function verificarSesion(): bool {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar que existan las variables de sesión necesarias
    if (!isset($_SESSION['autenticado']) || !isset($_SESSION['usuario'])) {
        return false;
    }
    
    // Verificar que el usuario esté autenticado
    if ($_SESSION['autenticado'] !== true) {
        return false;
    }
    
    // Verificar si la sesión ha expirado
    if (sesionExpirada()) {
        cerrarSesion();
        return false;
    }
    
    // Actualizar timestamp de última actividad
    actualizarActividad();
    
    return true;
}

/**
 * Actualiza el timestamp de última actividad
 * 
 * Actualiza el timestamp de última actividad en la sesión
 * para mantener la sesión activa mientras el usuario interactúa
 * con el sistema
 * 
 * @return void
 */
function actualizarActividad(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['ultima_actividad'] = time();
}

/**
 * Verifica si la sesión ha expirado por inactividad
 * 
 * Comprueba si han transcurrido más de 30 minutos desde
 * la última actividad del usuario
 * 
 * @return bool True si la sesión ha expirado, false en caso contrario
 */
function sesionExpirada(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Si no existe timestamp de última actividad, considerar expirada
    if (!isset($_SESSION['ultima_actividad'])) {
        return true;
    }
    
    // Calcular tiempo transcurrido desde última actividad
    $tiempoInactivo = time() - $_SESSION['ultima_actividad'];
    
    // Verificar si excede el tiempo de expiración (30 minutos)
    return $tiempoInactivo > TIEMPO_EXPIRACION_SESION;
}

/**
 * Cierra la sesión actual
 * 
 * Destruye la sesión PHP actual, elimina todas las variables
 * de sesión y elimina la cookie de sesión del navegador
 * 
 * @return void
 */
function cerrarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Limpiar todas las variables de sesión
    $_SESSION = [];
    
    // Eliminar la cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Actualiza el último acceso del usuario en la base de datos
 * 
 * Registra el timestamp del último acceso del usuario
 * en la tabla usuarios_admin
 * 
 * @param string $usuario Nombre de usuario
 * @return void
 */
function actualizarUltimoAcceso(string $usuario): void {
    try {
        require_once __DIR__ . '/../config/database.php';
        $pdo = obtenerConexion();
        
        $stmt = $pdo->prepare(
            "UPDATE usuarios_admin 
             SET ultimo_acceso = datetime('now') 
             WHERE usuario = :usuario"
        );
        
        $stmt->execute(['usuario' => $usuario]);
        
    } catch (PDOException $e) {
        error_log("Error al actualizar último acceso: " . $e->getMessage());
    }
}
