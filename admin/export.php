<?php
/**
 * export.php - Exportación de registros a CSV
 * 
 * Genera y descarga un archivo CSV con los registros filtrados
 */

// Incluir dependencias
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/FormularioModel.php';

// Verificar sesión activa
if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}

// Obtener conexión a la base de datos
$pdo = obtenerConexion();
$modelo = new FormularioModel($pdo);

// Procesar filtros desde GET
$filtros = [];
$busqueda = $_GET['busqueda'] ?? '';
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$servicio = $_GET['servicio'] ?? '';

if (!empty($busqueda)) {
    $filtros['busqueda'] = $busqueda;
}
if (!empty($fechaInicio)) {
    $filtros['fecha_inicio'] = $fechaInicio;
}
if (!empty($fechaFin)) {
    $filtros['fecha_fin'] = $fechaFin;
}
if (!empty($servicio)) {
    $filtros['servicio'] = $servicio;
}

try {
    // Generar archivo CSV
    $rutaArchivo = $modelo->exportarCSV($filtros);
    
    // Verificar que el archivo existe
    if (!file_exists($rutaArchivo)) {
        throw new Exception('No se pudo generar el archivo CSV');
    }
    
    // Obtener nombre del archivo
    $nombreArchivo = basename($rutaArchivo);
    
    // Configurar headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . filesize($rutaArchivo));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Enviar archivo
    readfile($rutaArchivo);
    
    // Eliminar archivo temporal
    unlink($rutaArchivo);
    
    // Registrar acción en log
    require_once __DIR__ . '/includes/functions.php';
    registrarLog('exportar_csv', $_SESSION['usuario'], [
        'filtros' => $filtros,
        'archivo' => $nombreArchivo
    ]);
    
    exit();
    
} catch (Exception $e) {
    error_log("Error en exportación: " . $e->getMessage());
    $_SESSION['error'] = 'Error al exportar los datos. Por favor, intente nuevamente.';
    header('Location: index.php');
    exit();
}
