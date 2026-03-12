<?php
/**
 * Script de inicialización del panel de administración
 * 
 * Este script debe ejecutarse una vez para:
 * - Crear la tabla usuarios_admin si no existe
 * - Verificar permisos de escritura en directorio logs/
 * - Verificar conexión a la base de datos
 */

require_once __DIR__ . '/config/database.php';

echo "=== Inicialización del Panel de Administración ===\n\n";

// 1. Verificar conexión a base de datos
echo "1. Verificando conexión a base de datos...\n";
try {
    $pdo = obtenerConexion();
    echo "   ✓ Conexión exitosa a database.db\n\n";
} catch (PDOException $e) {
    echo "   ✗ Error: No se pudo conectar a la base de datos\n";
    echo "   Detalles: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Crear tabla usuarios_admin
echo "2. Creando tabla usuarios_admin...\n";
if (inicializarTablaUsuarios($pdo)) {
    echo "   ✓ Tabla usuarios_admin creada o ya existe\n\n";
} else {
    echo "   ✗ Error: No se pudo crear la tabla usuarios_admin\n";
    exit(1);
}

// 3. Verificar permisos de escritura en logs/
echo "3. Verificando permisos de escritura en logs/...\n";
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    echo "   ! Directorio logs/ no existe, creándolo...\n";
    if (!mkdir($logsDir, 0755, true)) {
        echo "   ✗ Error: No se pudo crear el directorio logs/\n";
        exit(1);
    }
}

if (is_writable($logsDir)) {
    echo "   ✓ Directorio logs/ tiene permisos de escritura\n\n";
} else {
    echo "   ✗ Error: El directorio logs/ no tiene permisos de escritura\n";
    echo "   Ejecute: chmod 755 " . $logsDir . "\n";
    exit(1);
}

// 4. Verificar si existe al menos un usuario administrador
echo "4. Verificando usuarios administradores...\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios_admin");
$result = $stmt->fetch();

if ($result['total'] == 0) {
    echo "   ! No hay usuarios administradores registrados\n";
    echo "   Para crear un usuario administrador, ejecute:\n";
    echo "   php admin/crear_usuario.php\n\n";
} else {
    echo "   ✓ Existen " . $result['total'] . " usuario(s) administrador(es)\n\n";
}

echo "=== Inicialización completada exitosamente ===\n";
echo "\nPróximos pasos:\n";
echo "1. Si no tiene usuarios, ejecute: php admin/crear_usuario.php\n";
echo "2. Acceda al panel en: http://localhost/admin/login.php\n";
