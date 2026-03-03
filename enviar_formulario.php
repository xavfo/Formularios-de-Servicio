<?php
// enviar_formulario.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si el formulario fue enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado.");
}

// Verificar si ya se envió recientemente usando la cookie
if (isset($_COOKIE['form_submitted_recently'])) {
    http_response_code(429); // Código de estado HTTP para "Too Many Requests"
    die("El formulario ya fue enviado recientemente. Inténtelo de nuevo en unas 48 horas.");
}

// Función para sanitizar cadenas
function sanitizeString($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

// Recoger y sanitizar datos del formulario
$destIdentificador = sanitizeString($_POST['dest_identificador'] ?? '');
$nombreComercial = sanitizeString($_POST['nombre_comercial']);
$ruc = sanitizeString($_POST['ruc']);
$telefonoEmpresa = sanitizeString($_POST['telefono_empresa']);
$direccionRuc = sanitizeString($_POST['direccion_ruc']);
$personaContacto = sanitizeString($_POST['persona_contacto']);
$cargoContacto = sanitizeString($_POST['cargo_contacto']);
$correoContacto = filter_var($_POST['correo_contacto'], FILTER_SANITIZE_EMAIL);
$telefonoContacto = sanitizeString($_POST['telefono_contacto']);
$direccionOficina = sanitizeString($_POST['direccion_oficina']);
$ciudadOficina = sanitizeString($_POST['ciudad_oficina']);
$direccionPlanta = sanitizeString($_POST['direccion_planta']);
$ciudadPlanta = sanitizeString($_POST['ciudad_planta']);
$certificaciones = sanitizeString($_POST['certificaciones']);
$organismoCertificador = sanitizeString($_POST['organismo_certificador']);
$alcanceCertificacion = sanitizeString($_POST['alcance_certificacion']);

// Manejar arrays
$serviciosRequeridos = isset($_POST['servicios_requeridos']) ? array_map('sanitizeString', $_POST['servicios_requeridos']) : [];
$direccionesEstablecimiento = isset($_POST['direccion_establecimiento']) ? array_map('sanitizeString', $_POST['direccion_establecimiento']) : [];
$ciudadesEstablecimiento = isset($_POST['ciudad_establecimiento']) ? array_map('sanitizeString', $_POST['ciudad_establecimiento']) : [];

$descripcionNegocio = sanitizeString($_POST['descripcion_negocio']);
$motivoCertificacion = sanitizeString($_POST['motivo_certificacion']);

$empleadosAdmin = (int)($_POST['empleados_administrativos'] ?? 0);
$empleadosOper = (int)($_POST['empleados_operativos'] ?? 0);
$cantidadTurnos = (int)($_POST['cantidad_turnos'] ?? 0);
$personalPorTurno = (int)($_POST['personal_por_turno'] ?? 0);
$horariosTurnos = sanitizeString($_POST['horarios_turnos']);

$departamentosNombre = isset($_POST['departamento_nombre']) ? array_map('sanitizeString', $_POST['departamento_nombre']) : [];
$departamentosResponsable = isset($_POST['departamento_responsable']) ? array_map('sanitizeString', $_POST['departamento_responsable']) : [];
$departamentosPersonal = isset($_POST['departamento_personal']) ? array_map('intval', $_POST['departamento_personal']) : []; // intval para números enteros

// Validar correos electrónicos
if (!filter_var($correoContacto, FILTER_VALIDATE_EMAIL)) {
    die("Correo electrónico inválido.");
}


// *** GUARDAR EN SQLITE ***
try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear tabla si no existe
    $db->exec("CREATE TABLE IF NOT EXISTS formularios_asesoria (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        fecha_registro TEXT DEFAULT CURRENT_TIMESTAMP,
        dest_identificador TEXT,
        nombre_comercial TEXT,
        ruc TEXT,
        telefono_empresa TEXT,
        direccion_ruc TEXT,
        persona_contacto TEXT,
        cargo_contacto TEXT,
        correo_contacto TEXT,
        telefono_contacto TEXT,
        direccion_oficina TEXT,
        ciudad_oficina TEXT,
        direccion_planta TEXT,
        ciudad_planta TEXT,
        certificaciones TEXT,
        organismo_certificador TEXT,
        alcance_certificacion TEXT,
        servicios_requeridos TEXT, -- Almacenamos como JSON string
        direcciones_establecimiento TEXT, -- Almacenamos como JSON string
        ciudades_establecimiento TEXT, -- Almacenamos como JSON string
        descripcion_negocio TEXT,
        motivo_certificacion TEXT,
        empleados_administrativos INTEGER,
        empleados_operativos INTEGER,
        cantidad_turnos INTEGER,
        personal_por_turno INTEGER,
        horarios_turnos TEXT,
        departamentos_nombre TEXT, -- Almacenamos como JSON string
        departamentos_responsable TEXT, -- Almacenamos como JSON string
        departamentos_personal TEXT  -- Almacenamos como JSON string
    )");

    $stmt = $db->prepare("
        INSERT INTO formularios_asesoria (
            dest_identificador, nombre_comercial, ruc, telefono_empresa, direccion_ruc,
            persona_contacto, cargo_contacto, correo_contacto, telefono_contacto,
            direccion_oficina, ciudad_oficina, direccion_planta, ciudad_planta,
            certificaciones, organismo_certificador, alcance_certificacion,
            servicios_requeridos, direcciones_establecimiento, ciudades_establecimiento,
            descripcion_negocio, motivo_certificacion,
            empleados_administrativos, empleados_operativos, cantidad_turnos, personal_por_turno, horarios_turnos,
            departamentos_nombre, departamentos_responsable, departamentos_personal
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $destIdentificador, $nombreComercial, $ruc, $telefonoEmpresa, $direccionRuc,
        $personaContacto, $cargoContacto, $correoContacto, $telefonoContacto,
        $direccionOficina, $ciudadOficina, $direccionPlanta, $ciudadPlanta,
        $certificaciones, $organismoCertificador, $alcanceCertificacion,
        json_encode($serviciosRequeridos),
        json_encode($direccionesEstablecimiento),
        json_encode($ciudadesEstablecimiento),
        $descripcionNegocio, $motivoCertificacion,
        $empleadosAdmin, $empleadosOper, $cantidadTurnos, $personalPorTurno, $horariosTurnos,
        json_encode($departamentosNombre),
        json_encode($departamentosResponsable),
        json_encode($departamentosPersonal)
    ]);

    $insertId = $db->lastInsertId(); // Opcional: Obtener el ID del registro insertado

} catch (PDOException $e) {
    error_log("Error SQLite: " . $e->getMessage()); // Loguear el error
    die("Error al guardar los datos. Por favor, inténtelo más tarde.");
}


// *** ENVIAR CORREO ELECTRÓNICO ***
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP; // Opcional, para depuración SMTP
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php'; // Asegúrate de tener PHPMailer instalado via Composer

$mail = new PHPMailer(true);

try {
    // Configuración del servidor de correo (ejemplo con Gmail)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tu_correo@gmail.com'; // Tu dirección de correo
    $mail->Password   = 'app_password_aqui';   // Contraseña de aplicación o contraseña real si no usas 2FA
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Remitente y destinatario(s)
    $mail->setFrom('tu_correo@gmail.com', 'Sistema de Asesoría'); // Debe coincidir con Username generalmente
    $mail->addAddress($correoContacto, $personaContacto); // Destinatario principal
    $mail->addCC('admin@tuempresa.com'); // Opcional: enviar copia a admin

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Confirmación de Recepción de Formulario de Asesoría - ID: ' . $insertId;

    // Construir cuerpo del mensaje
    $body = "<h2>Confirmación de Recepción de Formulario</h2>";
    $body .= "<p>Hola <strong>" . $personaContacto . "</strong>,</p>";
    $body .= "<p>Hemos recibido correctamente su formulario de solicitud de asesoría para <strong>" . $nombreComercial . "</strong>.</p>";
    $body .= "<p><strong>ID de Seguimiento:</strong> " . $insertId . "</p>";
    $body .= "<p><strong>Identificador de Origen:</strong> " . $destIdentificador . "</p>";

    $body .= "<h3>Resumen de la Información Proporcionada:</h3>";
    $body .= "<ul>";
    $body .= "<li><strong>RUC:</strong> " . $ruc . "</li>";
    $body .= "<li><strong>Teléfono Empresa:</strong> " . $telefonoEmpresa . "</li>";
    $body .= "<li><strong>Dirección (RUC):</strong> " . $direccionRuc . "</li>";
    $body .= "<li><strong>Cargo:</strong> " . $cargoContacto . "</li>";
    $body .= "<li><strong>Correo de Contacto:</strong> " . $correoContacto . "</li>";
    $body .= "<li><strong>Teléfono Contacto:</strong> " . $telefonoContacto . "</li>";
    $body .= "<li><strong>Dirección Oficina:</strong> " . $direccionOficina . "</li>";
    $body .= "<li><strong>Ciudad Oficina:</strong> " . $ciudadOficina . "</li>";
    $body .= "<li><strong>Dirección Planta:</strong> " . $direccionPlanta . "</li>";
    $body .= "<li><strong>Ciudad Planta:</strong> " . $ciudadPlanta . "</li>";
    $body .= "<li><strong>Certificaciones Actuales:</strong> " . nl2br(htmlspecialchars($certificaciones, ENT_QUOTES, 'UTF-8')) . "</li>";
    $body .= "<li><strong>Organismo Certificador:</strong> " . nl2br(htmlspecialchars($organismoCertificador, ENT_QUOTES, 'UTF-8')) . "</li>";
    $body .= "<li><strong>Alcance de la Certificación:</strong> " . nl2br(htmlspecialchars($alcanceCertificacion, ENT_QUOTES, 'UTF-8')) . "</li>";
    $body .= "<li><strong>Servicios Requeridos:</strong> " . implode(', ', $serviciosRequeridos) . "</li>";

    // Establecimientos extra
    $body .= "<li><strong>Establecimientos Extra:</strong><ul>";
    for ($i = 0; $i < count($direccionesEstablecimiento); $i++) {
        $dir = htmlspecialchars($direccionesEstablecimiento[$i], ENT_QUOTES, 'UTF-8');
        $ciu = htmlspecialchars($ciudadesEstablecimiento[$i], ENT_QUOTES, 'UTF-8');
        $body .= "<li>Dirección: $dir, Ciudad: $ciu</li>";
    }
    $body .= "</ul></li>";

    $body .= "<li><strong>Descripción del Negocio:</strong> " . nl2br(htmlspecialchars($descripcionNegocio, ENT_QUOTES, 'UTF-8')) . "</li>";
    $body .= "<li><strong>Motivo de Certificación:</strong> " . nl2br(htmlspecialchars($motivoCertificacion, ENT_QUOTES, 'UTF-8')) . "</li>";
    $body .= "<li><strong>Empleados Administrativos:</strong> " . $empleadosAdmin . "</li>";
    $body .= "<li><strong>Empleados Operativos:</strong> " . $empleadosOper . "</li>";
    $body .= "<li><strong>Cantidad de Turnos:</strong> " . $cantidadTurnos . "</li>";
    $body .= "<li><strong>Personal por Turno:</strong> " . $personalPorTurno . "</li>";
    $body .= "<li><strong>Horarios de Turnos:</strong> " . $horariosTurnos . "</li>";

    // Departamentos
    $body .= "<li><strong>Departamentos:</strong><ul>";
    for ($j = 0; $j < count($departamentosNombre); $j++) {
        $nom = htmlspecialchars($departamentosNombre[$j], ENT_QUOTES, 'UTF-8');
        $res = htmlspecialchars($departamentosResponsable[$j], ENT_QUOTES, 'UTF-8');
        $per = $departamentosPersonal[$j];
        $body .= "<li>Área: $nom, Responsable: $res, Personal: $per</li>";
    }
    $body .= "</ul></li>";

    $body .= "</ul>";

    $body .= "<p>Nuestro departamento comercial revisará su solicitud y un asesor se pondrá en contacto con usted próximamente para presentarle una propuesta.</p>";
    $body .= "<p>Gracias por confiar en nuestros servicios.</p>";

    $mail->Body = $body;
    $mail->AltBody = strip_tags($body); // Versión texto plano alternativa

    $mail->send();

    // echo "¡Formulario enviado exitosamente! Se ha enviado un correo de confirmación."; // Mensaje temporal para debug

} catch (Exception $e) {
    // Si falla el envío del correo, loguearlo pero no mostrar el error al usuario
    error_log("Error al enviar correo: " . $mail->ErrorInfo);
    // No es ideal, pero tal vez quieras manejarlo de otra forma
    // die("Hubo un problema al enviar la confirmación por correo. Los datos fueron guardados correctamente.");
}


// *** ESTABLECER COOKIE DE BLOQUEO ***
// Establecer una cookie que expire en 48 horas (48 * 60 * 60 segundos)
setcookie('form_submitted_recently', 'true', time() + (48 * 60 * 60), '/', '', false, true); // httponly=true para mayor seguridad

// Redirigir a una página de éxito o mostrar mensaje
header('Location: formularioServicios.php?mensaje=enviado&dest=' . urlencode($destIdentificador));
exit();

?>