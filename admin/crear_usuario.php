<?php
/**
 * Script para crear usuarios administradores
 * 
 * Este script permite crear nuevos usuarios administradores
 * con contraseñas hasheadas de forma segura
 */

require_once __DIR__ . '/config/database.php';

// Verificar si se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos\n");
}

echo "=== Crear Usuario Administrador ===\n\n";

// Solicitar nombre de usuario
echo "Ingrese nombre de usuario: ";
$usuario = trim(fgets(STDIN));

if (empty($usuario)) {
    die("Error: El nombre de usuario no puede estar vacío\n");
}

// Solicitar contraseña
echo "Ingrese contraseña: ";
$password = trim(fgets(STDIN));

if (empty($password)) {
    die("Error: La contraseña no puede estar vacía\n");
}

if (strlen($password) < 8) {
    die("Error: La contraseña debe tener al menos 8 caracteres\n");
}

// Confirmar contraseña
echo "Confirme contraseña: ";
$passwordConfirm = trim(fgets(STDIN));

if ($password !== $passwordConfirm) {
    die("Error: Las contraseñas no coinciden\n");
}

// Conectar a la base de datos
try {
    $pdo = obtenerConexion();
    
    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios_admin WHERE usuario = ?");
    $stmt->execute([$usuario]);
    
    if ($stmt->fetch()) {
        die("Error: El usuario '$usuario' ya existe\n");
    }
    
    // Hashear la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar el nuevo usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios_admin (usuario, password_hash) VALUES (?, ?)");
    $stmt->execute([$usuario, $passwordHash]);
    
    echo "\n✓ Usuario '$usuario' creado exitosamente\n";
    echo "Ahora puede iniciar sesión en: http://localhost/admin/login.php\n";
    
} catch (PDOException $e) {
    echo "Error: No se pudo crear el usuario\n";
    echo "Detalles: " . $e->getMessage() . "\n";
    exit(1);
}
