<?php
/**
 * Módulo de protección CSRF para el panel de administración
 * 
 * Este archivo contiene funciones para proteger formularios contra
 * ataques Cross-Site Request Forgery (CSRF) mediante tokens únicos
 * de sesión que deben ser validados en cada operación de modificación
 */

/**
 * Genera un token CSRF único para la sesión
 * 
 * Si no existe un token CSRF en la sesión actual, genera uno nuevo
 * usando bytes criptográficamente seguros. Si ya existe, retorna
 * el token existente para mantener consistencia durante la sesión.
 * 
 * @return string Token CSRF de 64 caracteres hexadecimales
 */
function generarTokenCSRF(): string {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Si ya existe un token en la sesión, retornarlo
    if (isset($_SESSION['csrf_token']) && !empty($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token'];
    }
    
    // Generar nuevo token con 32 bytes aleatorios criptográficamente seguros
    $bytes = random_bytes(32);
    $token = bin2hex($bytes);
    
    // Almacenar en sesión
    $_SESSION['csrf_token'] = $token;
    
    return $token;
}

/**
 * Valida el token CSRF recibido contra el token de sesión
 * 
 * Compara el token recibido (típicamente desde un formulario POST)
 * con el token almacenado en la sesión actual. Usa comparación
 * segura contra timing attacks.
 * 
 * @param string $token Token CSRF a validar
 * @return bool True si el token es válido, false en caso contrario
 */
function validarTokenCSRF(string $token): bool {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar que exista un token en la sesión
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Verificar que el token recibido no esté vacío
    if (empty($token)) {
        return false;
    }
    
    // Comparación segura contra timing attacks usando hash_equals
    // Esta función compara strings en tiempo constante para prevenir
    // que un atacante pueda deducir el token mediante análisis de tiempo
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Genera el HTML del campo hidden con token CSRF
 * 
 * Crea un campo input hidden con el nombre 'csrf_token' y el valor
 * del token CSRF actual. Este campo debe incluirse en todos los
 * formularios que realicen operaciones de modificación (POST, PUT, DELETE).
 * 
 * @return string HTML del campo input hidden con el token CSRF
 */
function campoTokenCSRF(): string {
    $token = generarTokenCSRF();
    
    // Escapar el token para prevenir XSS (aunque el token es hexadecimal,
    // es buena práctica escapar siempre los valores en HTML)
    $tokenEscapado = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    
    // Generar HTML del campo hidden
    return '<input type="hidden" name="csrf_token" value="' . $tokenEscapado . '">';
}
