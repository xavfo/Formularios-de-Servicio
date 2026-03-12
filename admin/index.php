<?php
/**
 * index.php - Dashboard principal del panel de administración
 * 
 * Muestra estadísticas, lista de registros con filtros, paginación
 * y opciones de exportación
 */

// Iniciar output buffering para evitar problemas con headers
ob_start();

// Manejo de errores (comentar en producción)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Incluir dependencias
try {
    require_once __DIR__ . '/includes/session.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/FormularioModel.php';
} catch (Exception $e) {
    die("Error al cargar dependencias: " . $e->getMessage());
}

// Verificar sesión activa (Tarea 8.1)
if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}

try {
    // Obtener conexión a la base de datos
    $pdo = obtenerConexion();
    $modelo = new FormularioModel($pdo);

    // Procesar filtros desde GET (Tarea 8.4)
    $filtros = [];
    $busqueda = is_string($_GET['busqueda'] ?? '') ? $_GET['busqueda'] : '';
    $fechaInicio = is_string($_GET['fecha_inicio'] ?? '') ? $_GET['fecha_inicio'] : '';
    $fechaFin = is_string($_GET['fecha_fin'] ?? '') ? $_GET['fecha_fin'] : '';
    $servicio = is_string($_GET['servicio'] ?? '') ? $_GET['servicio'] : '';

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

    // Obtener página actual
    $paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;

    // Obtener estadísticas (Tarea 8.3)
    $estadisticas = $modelo->obtenerEstadisticas();

    // Obtener registros con filtros y paginación (Tarea 8.4)
    $resultado = $modelo->listar($paginaActual, 20, $filtros);
    $registros = $resultado['registros'];
    $totalRegistros = $resultado['total'];
    $totalPaginas = $resultado['paginas'];
} catch (Exception $e) {
    error_log("Error en index.php: " . $e->getMessage());
    die("Error al cargar el dashboard. Por favor, contacte al administrador.");
}

// Construir URL base para paginación (Tarea 8.5)
$urlBase = 'index.php';
$paramsUrl = [];
if (!empty($busqueda)) {
    $paramsUrl[] = 'busqueda=' . urlencode($busqueda);
}
if (!empty($fechaInicio)) {
    $paramsUrl[] = 'fecha_inicio=' . urlencode($fechaInicio);
}
if (!empty($fechaFin)) {
    $paramsUrl[] = 'fecha_fin=' . urlencode($fechaFin);
}
if (!empty($servicio)) {
    $paramsUrl[] = 'servicio=' . urlencode($servicio);
}
if (!empty($paramsUrl)) {
    $urlBase .= '?' . implode('&', $paramsUrl);
}

// Construir URL para exportación (Tarea 8.6)
$urlExport = 'export.php';
if (!empty($paramsUrl)) {
    $urlExport .= '?' . implode('&', $paramsUrl);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Panel de Administración - Formularios de Asesoría</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/js/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Panel de Administración</h1>
                <div class="header-actions">
                    <span class="usuario-info">Usuario: <?php echo sanitizar($_SESSION['usuario']); ?></span>
                    <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <!-- Mensajes de éxito/error -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo sanitizar($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo sanitizar($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Panel de Estadísticas (Tarea 8.3) -->
        <section class="estadisticas">
            <h2>Estadísticas</h2>
            <div class="estadisticas-grid">
                <div class="estadistica-card">
                    <div class="estadistica-valor"><?php echo $estadisticas['total']; ?></div>
                    <div class="estadistica-label">Total de Registros</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor"><?php echo $estadisticas['mes_actual']; ?></div>
                    <div class="estadistica-label">Registros del Mes</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor"><?php echo $estadisticas['semana_actual']; ?></div>
                    <div class="estadistica-label">Registros de la Semana</div>
                </div>
            </div>

            <!-- Servicios más solicitados -->
            <?php if (!empty($estadisticas['servicios_populares'])): ?>
                <div class="servicios-populares">
                    <h3>Servicios Más Solicitados</h3>
                    <ul class="servicios-lista">
                        <?php foreach (array_slice($estadisticas['servicios_populares'], 0, 5) as $servicio): ?>
                            <li class="servicio-item">
                                <span class="servicio-nombre"><?php echo sanitizar($servicio['servicio']); ?></span>
                                <span class="servicio-cantidad"><?php echo $servicio['cantidad']; ?> solicitudes</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>

        <!-- Filtros de Búsqueda (Tarea 8.4) -->
        <section class="filtros">
            <h2>Filtros de Búsqueda</h2>
            <form method="GET" action="index.php" class="filtros-form">
                <div class="filtros-grid">
                    <div class="form-group">
                        <label for="busqueda">Búsqueda:</label>
                        <input 
                            type="text" 
                            id="busqueda" 
                            name="busqueda" 
                            placeholder="Nombre, RUC, contacto, correo..."
                            value="<?php echo sanitizar($busqueda); ?>"
                            class="form-control"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input 
                            type="date" 
                            id="fecha_inicio" 
                            name="fecha_inicio" 
                            value="<?php echo sanitizar($fechaInicio); ?>"
                            class="form-control"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input 
                            type="date" 
                            id="fecha_fin" 
                            name="fecha_fin" 
                            value="<?php echo sanitizar($fechaFin); ?>"
                            class="form-control"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="servicio">Servicio:</label>
                        <input 
                            type="text" 
                            id="servicio" 
                            name="servicio" 
                            placeholder="Ej: ISO 9001, BPM..."
                            value="<?php echo sanitizar($servicio); ?>"
                            class="form-control"
                        >
                    </div>
                </div>
                
                <div class="filtros-actions">
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                    <a href="index.php" class="btn btn-secondary">Limpiar Filtros</a>
                    <!-- Botón de Exportación (Tarea 8.6) -->
                    <a href="<?php echo sanitizar($urlExport); ?>" class="btn btn-success">
                        Exportar a CSV
                    </a>
                </div>
            </form>
        </section>

        <!-- Tabla de Registros (Tarea 8.4) -->
        <section class="registros">
            <h2>Registros de Formularios (<?php echo $totalRegistros; ?> total)</h2>
            
            <?php if (empty($registros)): ?>
                <div class="alert alert-info">
                    No se encontraron registros con los filtros aplicados.
                </div>
            <?php else: ?>
                <div class="tabla-responsive">
                    <table class="tabla">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha Registro</th>
                                <th>Nombre Comercial</th>
                                <th>RUC</th>
                                <th>Persona Contacto</th>
                                <th>Correo Contacto</th>
                                <th>Servicios Requeridos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo $registro['id']; ?></td>
                                    <td><?php echo formatearFecha($registro['fecha_registro']); ?></td>
                                    <td><?php echo sanitizar($registro['nombre_comercial']); ?></td>
                                    <td><?php echo sanitizar($registro['ruc']); ?></td>
                                    <td><?php echo sanitizar($registro['persona_contacto']); ?></td>
                                    <td><?php echo sanitizar($registro['correo_contacto']); ?></td>
                                    <td>
                                        <?php 
                                        // Decodificar y mostrar servicios requeridos
                                        if (is_array($registro['servicios_requeridos']) && !empty($registro['servicios_requeridos'])) {
                                            echo sanitizar(implode(', ', $registro['servicios_requeridos']));
                                        } else {
                                            echo '<em>No especificado</em>';
                                        }
                                        ?>
                                    </td>
                                    <td class="acciones">
                                        <a href="view.php?id=<?php echo $registro['id']; ?>" class="btn btn-small btn-info" title="Ver detalles">
                                            Ver
                                        </a>
                                        <a href="edit.php?id=<?php echo $registro['id']; ?>" class="btn btn-small btn-warning" title="Editar">
                                            Editar
                                        </a>
                                        <a href="delete.php?id=<?php echo $registro['id']; ?>" class="btn btn-small btn-danger" title="Eliminar">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación (Tarea 8.5) -->
                <?php echo generarPaginacion($paginaActual, $totalPaginas, $urlBase); ?>
            <?php endif; ?>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Panel de Administración - Formularios de Asesoría</p>
        </footer>
    </div>
</body>
</html>
<?php
// Enviar el buffer y terminar
ob_end_flush();
?>
