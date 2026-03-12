<?php
/**
 * delete.php - Eliminación de registro
 * 
 * Permite eliminar un registro con confirmación
 */

// Incluir dependencias
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/FormularioModel.php';

// Verificar sesión activa
if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}

// Obtener ID desde GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $_SESSION['error'] = 'ID de registro inválido';
    header('Location: index.php');
    exit();
}

// Obtener conexión a la base de datos
$pdo = obtenerConexion();
$modelo = new FormularioModel($pdo);

// Cargar registro
$registro = $modelo->obtenerPorId($id);

if ($registro === null) {
    $_SESSION['error'] = 'El registro solicitado no existe';
    header('Location: index.php');
    exit();
}

// Procesar eliminación si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.');
    }
    
    // Intentar eliminar
    if ($modelo->eliminar($id)) {
        // Registrar en log
        registrarLog('eliminar_registro', $_SESSION['usuario'], [
            'registro_id' => $id,
            'nombre_comercial' => $registro['nombre_comercial'],
            'ruc' => $registro['ruc']
        ]);
        
        $_SESSION['success'] = 'Registro eliminado exitosamente';
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error al eliminar el registro. Por favor, intente nuevamente.';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Registro #<?php echo $registro['id']; ?> - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/js/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Eliminar Registro #<?php echo $registro['id']; ?></h1>
                <div class="header-actions">
                    <a href="view.php?id=<?php echo $registro['id']; ?>" class="btn btn-secondary">Cancelar</a>
                    <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <!-- Mensajes -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo sanitizar($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Confirmación -->
        <section class="registros">
            <div style="background: white; padding: 30px; border-radius: 8px;">
                <div class="alert alert-error" style="margin-bottom: 30px;">
                    <strong>¡Advertencia!</strong> Esta acción no se puede deshacer. ¿Está seguro de que desea eliminar este registro?
                </div>

                <h2>Información del Registro a Eliminar</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">ID:</th>
                        <td><?php echo $registro['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Fecha de Registro:</th>
                        <td><?php echo formatearFecha($registro['fecha_registro']); ?></td>
                    </tr>
                    <tr>
                        <th>Nombre Comercial:</th>
                        <td><?php echo sanitizar($registro['nombre_comercial']); ?></td>
                    </tr>
                    <tr>
                        <th>RUC:</th>
                        <td><?php echo sanitizar($registro['ruc']); ?></td>
                    </tr>
                    <tr>
                        <th>Persona de Contacto:</th>
                        <td><?php echo sanitizar($registro['persona_contacto']); ?></td>
                    </tr>
                    <tr>
                        <th>Correo Electrónico:</th>
                        <td><?php echo sanitizar($registro['correo_contacto']); ?></td>
                    </tr>
                </table>

                <form method="POST" action="delete.php?id=<?php echo $registro['id']; ?>" onsubmit="return confirm('¿Está completamente seguro de que desea eliminar este registro? Esta acción no se puede deshacer.');">
                    <?php echo campoTokenCSRF(); ?>
                    
                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
                        <a href="view.php?id=<?php echo $registro['id']; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Panel de Administración - Formularios de Asesoría</p>
        </footer>
    </div>
</body>
</html>
