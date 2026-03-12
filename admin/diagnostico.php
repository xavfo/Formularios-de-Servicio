<?php
/**
 * Script de diagnóstico para identificar problemas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico del Panel de Administración</h1>";

// 1. Verificar PHP
echo "<h2>1. Versión de PHP</h2>";
echo "Versión: " . phpversion() . "<br>";
echo "PDO SQLite disponible: " . (extension_loaded('pdo_sqlite') ? 'Sí' : 'No') . "<br><br>";

// 2. Verificar archivos
echo "<h2>2. Archivos Requeridos</h2>";
$archivos = [
    'config/database.php',
    'includes/session.php',
    'includes/functions.php',
    'includes/auth.php',
    'includes/csrf.php',
    'models/FormularioModel.php'
];

foreach ($archivos as $archivo) {
    $ruta = __DIR__ . '/' . $archivo;
    echo "$archivo: " . (file_exists($ruta) ? '✓ Existe' : '✗ No existe') . "<br>";
}
echo "<br>";

// 3. Verificar base de datos
echo "<h2>3. Base de Datos</h2>";
$dbPath = __DIR__ . '/../database.db';
echo "Ruta: $dbPath<br>";
echo "Existe: " . (file_exists($dbPath) ? 'Sí' : 'No') . "<br>";
echo "Legible: " . (is_readable($dbPath) ? 'Sí' : 'No') . "<br>";
echo "Escribible: " . (is_writable($dbPath) ? 'Sí' : 'No') . "<br><br>";

// 4. Intentar conectar
echo "<h2>4. Conexión a Base de Datos</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $pdo = obtenerConexion();
    echo "✓ Conexión exitosa<br><br>";
    
    // 5. Verificar tablas
    echo "<h2>5. Tablas en la Base de Datos</h2>";
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablas)) {
        echo "No hay tablas en la base de datos<br>";
    } else {
        foreach ($tablas as $tabla) {
            echo "- $tabla<br>";
        }
    }
    echo "<br>";
    
    // 6. Verificar tabla formularios_asesoria
    echo "<h2>6. Tabla formularios_asesoria</h2>";
    if (in_array('formularios_asesoria', $tablas)) {
        echo "✓ La tabla existe<br>";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM formularios_asesoria");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Total de registros: $total<br>";
        
        // Mostrar estructura
        echo "<br><strong>Estructura de la tabla:</strong><br>";
        $stmt = $pdo->query("PRAGMA table_info(formularios_asesoria)");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        foreach ($columnas as $col) {
            echo $col['name'] . " (" . $col['type'] . ")\n";
        }
        echo "</pre>";
    } else {
        echo "✗ La tabla NO existe<br>";
        echo "<strong>Solución:</strong> La tabla debe ser creada por el formulario principal<br>";
    }
    echo "<br>";
    
    // 7. Verificar tabla usuarios_admin
    echo "<h2>7. Tabla usuarios_admin</h2>";
    if (in_array('usuarios_admin', $tablas)) {
        echo "✓ La tabla existe<br>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios_admin");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "Total de usuarios: $total<br>";
        
        if ($total == 0) {
            echo "<br><strong>Advertencia:</strong> No hay usuarios administradores<br>";
            echo "Ejecute: <code>php admin/crear_usuario.php</code><br>";
        }
    } else {
        echo "✗ La tabla NO existe<br>";
        echo "<strong>Solución:</strong> Ejecute <code>php admin/init.php</code><br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<br><h2>8. Permisos de Directorios</h2>";
$directorios = ['logs', 'exports'];
foreach ($directorios as $dir) {
    $ruta = __DIR__ . '/' . $dir;
    if (!is_dir($ruta)) {
        echo "$dir: ✗ No existe (se creará automáticamente)<br>";
    } else {
        echo "$dir: ✓ Existe | Escribible: " . (is_writable($ruta) ? 'Sí' : 'No') . "<br>";
    }
}

echo "<br><hr>";
echo "<p><a href='index.php'>Ir al Panel de Administración</a></p>";
?>
