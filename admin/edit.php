<?php
/**
 * edit.php - Edición de registro
 * 
 * Permite editar un registro existente
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

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.');
    }
    
    // Preparar datos para actualización
    $datos = [
        'nombre_comercial' => trim($_POST['nombre_comercial'] ?? ''),
        'ruc' => trim($_POST['ruc'] ?? ''),
        'correo_contacto' => trim($_POST['correo_contacto'] ?? ''),
        'persona_contacto' => trim($_POST['persona_contacto'] ?? ''),
        'telefono_empresa' => trim($_POST['telefono_empresa'] ?? ''),
        'telefono_contacto' => trim($_POST['telefono_contacto'] ?? ''),
        'cargo_contacto' => trim($_POST['cargo_contacto'] ?? ''),
        'direccion_ruc' => trim($_POST['direccion_ruc'] ?? ''),
        'direccion_oficina' => trim($_POST['direccion_oficina'] ?? ''),
        'ciudad_oficina' => trim($_POST['ciudad_oficina'] ?? ''),
        'direccion_planta' => trim($_POST['direccion_planta'] ?? ''),
        'ciudad_planta' => trim($_POST['ciudad_planta'] ?? ''),
        'descripcion_negocio' => trim($_POST['descripcion_negocio'] ?? ''),
        'motivo_certificacion' => trim($_POST['motivo_certificacion'] ?? ''),
    ];
    
    // Intentar actualizar
    if ($modelo->actualizar($id, $datos)) {
        // Registrar en log
        registrarLog('editar_registro', $_SESSION['usuario'], [
            'registro_id' => $id,
            'campos_modificados' => array_keys($datos)
        ]);
        
        $_SESSION['success'] = 'Registro actualizado exitosamente';
        header('Location: view.php?id=' . $id);
        exit();
    } else {
        $_SESSION['error'] = 'Error al actualizar el registro. Verifique que los campos requeridos no estén vacíos.';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Registro #<?php echo $registro['id']; ?> - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/js/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Editar Registro #<?php echo $registro['id']; ?></h1>
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

        <!-- Formulario -->
        <section class="registros">
            <form method="POST" action="edit.php?id=<?php echo $registro['id']; ?>">
                <?php echo campoTokenCSRF(); ?>
                
                <div style="background: white; padding: 30px; border-radius: 8px;">
                    <h2>Información General</h2>
                    <div class="filtros-grid" style="margin-bottom: 30px;">
                        <div class="form-group">
                            <label for="nombre_comercial">Nombre Comercial: *</label>
                            <input 
                                type="text" 
                                id="nombre_comercial" 
                                name="nombre_comercial" 
                                value="<?php echo sanitizar($registro['nombre_comercial']); ?>"
                                class="form-control"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="ruc">RUC: *</label>
                            <input 
                                type="text" 
                                id="ruc" 
                                name="ruc" 
                                value="<?php echo sanitizar($registro['ruc']); ?>"
                                class="form-control"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono_empresa">Teléfono Empresa:</label>
                            <input 
                                type="text" 
                                id="telefono_empresa" 
                                name="telefono_empresa" 
                                value="<?php echo sanitizar($registro['telefono_empresa'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion_ruc">Dirección RUC:</label>
                            <input 
                                type="text" 
                                id="direccion_ruc" 
                                name="direccion_ruc" 
                                value="<?php echo sanitizar($registro['direccion_ruc'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                    </div>

                    <h2>Información de Contacto</h2>
                    <div class="filtros-grid" style="margin-bottom: 30px;">
                        <div class="form-group">
                            <label for="persona_contacto">Persona de Contacto: *</label>
                            <input 
                                type="text" 
                                id="persona_contacto" 
                                name="persona_contacto" 
                                value="<?php echo sanitizar($registro['persona_contacto']); ?>"
                                class="form-control"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="cargo_contacto">Cargo:</label>
                            <input 
                                type="text" 
                                id="cargo_contacto" 
                                name="cargo_contacto" 
                                value="<?php echo sanitizar($registro['cargo_contacto'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="correo_contacto">Correo Electrónico: *</label>
                            <input 
                                type="email" 
                                id="correo_contacto" 
                                name="correo_contacto" 
                                value="<?php echo sanitizar($registro['correo_contacto']); ?>"
                                class="form-control"
                                required
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono_contacto">Teléfono:</label>
                            <input 
                                type="text" 
                                id="telefono_contacto" 
                                name="telefono_contacto" 
                                value="<?php echo sanitizar($registro['telefono_contacto'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                    </div>

                    <h2>Ubicaciones</h2>
                    <div class="filtros-grid" style="margin-bottom: 30px;">
                        <div class="form-group">
                            <label for="direccion_oficina">Dirección Oficina:</label>
                            <input 
                                type="text" 
                                id="direccion_oficina" 
                                name="direccion_oficina" 
                                value="<?php echo sanitizar($registro['direccion_oficina'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="ciudad_oficina">Ciudad Oficina:</label>
                            <input 
                                type="text" 
                                id="ciudad_oficina" 
                                name="ciudad_oficina" 
                                value="<?php echo sanitizar($registro['ciudad_oficina'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion_planta">Dirección Planta:</label>
                            <input 
                                type="text" 
                                id="direccion_planta" 
                                name="direccion_planta" 
                                value="<?php echo sanitizar($registro['direccion_planta'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="ciudad_planta">Ciudad Planta:</label>
                            <input 
                                type="text" 
                                id="ciudad_planta" 
                                name="ciudad_planta" 
                                value="<?php echo sanitizar($registro['ciudad_planta'] ?? ''); ?>"
                                class="form-control"
                            >
                        </div>
                    </div>

                    <h2>Información del Negocio</h2>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="descripcion_negocio">Descripción del Negocio:</label>
                        <textarea 
                            id="descripcion_negocio" 
                            name="descripcion_negocio" 
                            rows="4"
                            class="form-control"
                        ><?php echo sanitizar($registro['descripcion_negocio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 30px;">
                        <label for="motivo_certificacion">Motivo de Certificación:</label>
                        <textarea 
                            id="motivo_certificacion" 
                            name="motivo_certificacion" 
                            rows="4"
                            class="form-control"
                        ><?php echo sanitizar($registro['motivo_certificacion'] ?? ''); ?></textarea>
                    </div>

                    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                        * Campos requeridos
                    </p>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="view.php?id=<?php echo $registro['id']; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Panel de Administración - Formularios de Asesoría</p>
        </footer>
    </div>
</body>
</html>
