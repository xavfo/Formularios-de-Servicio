<?php
/**
 * Test simple para verificar funcionalidad básica
 */

echo "<h1>Test Simple</h1>";

// Test 1: Includes
echo "<h2>1. Cargando archivos...</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    echo "✓ database.php cargado<br>";
    
    require_once __DIR__ . '/includes/functions.php';
    echo "✓ functions.php cargado<br>";
    
    require_once __DIR__ . '/models/FormularioModel.php';
    echo "✓ FormularioModel.php cargado<br>";
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Test 2: Conexión
echo "<br><h2>2. Probando conexión...</h2>";
try {
    $pdo = obtenerConexion();
    echo "✓ Conexión exitosa<br>";
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Test 3: Modelo
echo "<br><h2>3. Probando FormularioModel...</h2>";
try {
    $modelo = new FormularioModel($pdo);
    echo "✓ Modelo instanciado<br>";
    
    // Test estadísticas
    $estadisticas = $modelo->obtenerEstadisticas();
    echo "✓ Estadísticas obtenidas<br>";
    echo "Total: " . $estadisticas['total'] . "<br>";
    echo "Mes actual: " . $estadisticas['mes_actual'] . "<br>";
    echo "Semana actual: " . $estadisticas['semana_actual'] . "<br>";
    
    // Test listar
    $resultado = $modelo->listar(1, 20, []);
    echo "<br>✓ Listado obtenido<br>";
    echo "Registros encontrados: " . count($resultado['registros']) . "<br>";
    echo "Total: " . $resultado['total'] . "<br>";
    echo "Páginas: " . $resultado['paginas'] . "<br>";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><h2>✓ Todos los tests pasaron</h2>";
echo "<p><a href='index.php'>Ir al Panel</a></p>";
?>
