<?php
/**
 * view.php - Vista detallada de un registro
 * 
 * Muestra todos los detalles de un registro específico
 */

// Incluir dependencias
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Registro #<?php echo $registro['id']; ?> - Panel de Administración</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/js/admin.js" defer></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Detalle del Registro #<?php echo $registro['id']; ?></h1>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary">Volver a la Lista</a>
                    <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <!-- Contenido -->
        <section class="registros">
            <div style="margin-bottom: 20px;">
                <a href="edit.php?id=<?php echo $registro['id']; ?>" class="btn btn-warning">Editar Registro</a>
                <a href="delete.php?id=<?php echo $registro['id']; ?>" class="btn btn-danger">Eliminar Registro</a>
            </div>

            <div style="background: white; padding: 30px; border-radius: 8px;">
                <h2>Información General</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Fecha de Registro:</th>
                        <td><?php echo formatearFecha($registro['fecha_registro']); ?></td>
                    </tr>
                    <tr>
                        <th>Identificador:</th>
                        <td><?php echo sanitizar($registro['dest_identificador'] ?? 'No especificado'); ?></td>
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
                        <th>Teléfono Empresa:</th>
                        <td><?php echo sanitizar($registro['telefono_empresa'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Dirección RUC:</th>
                        <td><?php echo sanitizar($registro['direccion_ruc'] ?? 'No especificado'); ?></td>
                    </tr>
                </table>

                <h2>Información de Contacto</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Persona de Contacto:</th>
                        <td><?php echo sanitizar($registro['persona_contacto']); ?></td>
                    </tr>
                    <tr>
                        <th>Cargo:</th>
                        <td><?php echo sanitizar($registro['cargo_contacto'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Correo Electrónico:</th>
                        <td><?php echo sanitizar($registro['correo_contacto']); ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td><?php echo sanitizar($registro['telefono_contacto'] ?? 'No especificado'); ?></td>
                    </tr>
                </table>

                <h2>Ubicaciones</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Dirección Oficina:</th>
                        <td><?php echo sanitizar($registro['direccion_oficina'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Ciudad Oficina:</th>
                        <td><?php echo sanitizar($registro['ciudad_oficina'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Dirección Planta:</th>
                        <td><?php echo sanitizar($registro['direccion_planta'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Ciudad Planta:</th>
                        <td><?php echo sanitizar($registro['ciudad_planta'] ?? 'No especificado'); ?></td>
                    </tr>
                </table>

                <h2>Certificaciones</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Certificaciones Actuales:</th>
                        <td><?php echo sanitizar($registro['certificaciones'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Organismo Certificador:</th>
                        <td><?php echo sanitizar($registro['organismo_certificador'] ?? 'No especificado'); ?></td>
                    </tr>
                    <tr>
                        <th>Alcance de Certificación:</th>
                        <td><?php echo sanitizar($registro['alcance_certificacion'] ?? 'No especificado'); ?></td>
                    </tr>
                </table>

                <h2>Servicios Requeridos</h2>
                <div style="margin-bottom: 30px;">
                    <?php echo jsonALista(json_encode($registro['servicios_requeridos'])); ?>
                </div>

                <h2>Establecimientos</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Direcciones:</th>
                        <td><?php echo jsonALista(json_encode($registro['direcciones_establecimiento'])); ?></td>
                    </tr>
                    <tr>
                        <th>Ciudades:</th>
                        <td><?php echo jsonALista(json_encode($registro['ciudades_establecimiento'])); ?></td>
                    </tr>
                </table>

                <h2>Información del Negocio</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Descripción del Negocio:</th>
                        <td><?php echo nl2br(sanitizar($registro['descripcion_negocio'] ?? 'No especificado')); ?></td>
                    </tr>
                    <tr>
                        <th>Motivo de Certificación:</th>
                        <td><?php echo nl2br(sanitizar($registro['motivo_certificacion'] ?? 'No especificado')); ?></td>
                    </tr>
                </table>

                <h2>Información de Personal</h2>
                <table class="tabla" style="margin-bottom: 30px;">
                    <tr>
                        <th style="width: 30%;">Empleados Administrativos:</th>
                        <td><?php echo $registro['empleados_administrativos'] ?? 'No especificado'; ?></td>
                    </tr>
                    <tr>
                        <th>Empleados Operativos:</th>
                        <td><?php echo $registro['empleados_operativos'] ?? 'No especificado'; ?></td>
                    </tr>
                    <tr>
                        <th>Cantidad de Turnos:</th>
                        <td><?php echo $registro['cantidad_turnos'] ?? 'No especificado'; ?></td>
                    </tr>
                    <tr>
                        <th>Personal por Turno:</th>
                        <td><?php echo $registro['personal_por_turno'] ?? 'No especificado'; ?></td>
                    </tr>
                    <tr>
                        <th>Horarios de Turnos:</th>
                        <td><?php echo sanitizar($registro['horarios_turnos'] ?? 'No especificado'); ?></td>
                    </tr>
                </table>

                <h2>Departamentos</h2>
                <table class="tabla">
                    <tr>
                        <th style="width: 30%;">Nombres:</th>
                        <td><?php echo jsonALista(json_encode($registro['departamentos_nombre'])); ?></td>
                    </tr>
                    <tr>
                        <th>Responsables:</th>
                        <td><?php echo jsonALista(json_encode($registro['departamentos_responsable'])); ?></td>
                    </tr>
                    <tr>
                        <th>Personal:</th>
                        <td><?php echo jsonALista(json_encode($registro['departamentos_personal'])); ?></td>
                    </tr>
                </table>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Panel de Administración - Formularios de Asesoría</p>
        </footer>
    </div>
</body>
</html>
