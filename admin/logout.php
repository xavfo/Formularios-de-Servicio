<?php
/**
 * Página de cierre de sesión del panel de administración
 * 
 * Cierra la sesión del usuario actual y redirige a la página de login
 * 
 * Requisitos: 1.4
 */

// Incluir módulos necesarios
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Iniciar sesión para poder cerrarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener usuario antes de cerrar sesión (para el log)
$usuario = $_SESSION['usuario'] ?? 'desconocido';

// Registrar logout en el log
if ($usuario !== 'desconocido') {
    registrarLog('logout', $usuario, [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
    ]);
}

// Cerrar sesión
cerrarSesion();

// Iniciar nueva sesión para mostrar mensaje
session_start();
$_SESSION['mensaje'] = 'Sesión cerrada exitosamente';

// Redirigir a login
header('Location: login.php');
exit();
