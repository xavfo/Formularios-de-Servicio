<?php
/**
 * Módulo de autenticación para el panel de administración
 * 
 * Este archivo contiene funciones para verificar credenciales,
 * gestionar usuarios administradores y manejar contraseñas de forma segura
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Verifica las credenciales del usuario
 * 
 * Compara el usuario y contraseña proporcionados con los datos
 * almacenados en la base de datos usando password_verify() para
 * validar el hash de la contraseña de forma segura
 * 
 * @param string $usuario Nombre de usuario
 * @param string $password Contraseña en texto plano
 * @return bool True si las credenciales son válidas, false en caso contrario
 */
function verificarCredenciales(string $usuario, string $password): bool {
    // Validar que los parámetros no estén vacíos
    if (empty($usuario) || empty($password)) {
        return false;
    }
    
    try {
        // Obtener el hash de la contraseña almacenada
        $hash = obtenerHashPassword($usuario);
        
        // Si no se encontró el usuario, retornar false
        if ($hash === false) {
            return false;
        }
        
        // Verificar la contraseña usando password_verify()
        return password_verify($password, $hash);
        
    } catch (Exception $e) {
        error_log("Error al verificar credenciales: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el hash de contraseña almacenado para un usuario
 * 
 * Consulta la base de datos usando prepared statements para
 * obtener el hash de contraseña de un usuario específico
 * 
 * @param string $usuario Nombre de usuario
 * @return string|false Hash de la contraseña si el usuario existe, false en caso contrario
 */
function obtenerHashPassword(string $usuario): string|false {
    // Validar que el usuario no esté vacío
    if (empty($usuario)) {
        return false;
    }
    
    try {
        // Obtener conexión a la base de datos
        $pdo = obtenerConexion();
        
        // Preparar consulta con prepared statement para prevenir SQL injection
        $stmt = $pdo->prepare("SELECT password_hash FROM usuarios_admin WHERE usuario = :usuario");
        
        // Ejecutar consulta con el parámetro
        $stmt->execute(['usuario' => $usuario]);
        
        // Obtener el resultado
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si se encontró el usuario, retornar el hash
        if ($resultado && isset($resultado['password_hash'])) {
            return $resultado['password_hash'];
        }
        
        // Usuario no encontrado
        return false;
        
    } catch (PDOException $e) {
        error_log("Error al obtener hash de contraseña: " . $e->getMessage());
        return false;
    }
}

/**
 * Crea un nuevo usuario administrador
 * 
 * Inserta un nuevo usuario en la base de datos con su contraseña
 * hasheada usando password_hash() con el algoritmo por defecto de PHP
 * (actualmente bcrypt)
 * 
 * @param string $usuario Nombre de usuario (debe ser único)
 * @param string $password Contraseña en texto plano
 * @return bool True si el usuario se creó exitosamente, false en caso contrario
 */
function crearUsuarioAdmin(string $usuario, string $password): bool {
    // Validar que los parámetros no estén vacíos
    if (empty($usuario) || empty($password)) {
        return false;
    }
    
    try {
        // Obtener conexión a la base de datos
        $pdo = obtenerConexion();
        
        // Asegurar que la tabla usuarios_admin existe
        inicializarTablaUsuarios($pdo);
        
        // Hashear la contraseña usando password_hash() con algoritmo por defecto
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Preparar consulta con prepared statement
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios_admin (usuario, password_hash, fecha_creacion) 
             VALUES (:usuario, :password_hash, datetime('now'))"
        );
        
        // Ejecutar consulta con los parámetros
        $resultado = $stmt->execute([
            'usuario' => $usuario,
            'password_hash' => $passwordHash
        ]);
        
        return $resultado;
        
    } catch (PDOException $e) {
        // Si el error es por usuario duplicado (UNIQUE constraint)
        if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            error_log("Error: El usuario '$usuario' ya existe");
        } else {
            error_log("Error al crear usuario administrador: " . $e->getMessage());
        }
        return false;
    }
}
