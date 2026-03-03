<?php
// formularioServicios.php
session_start();

// Obtener el parámetro 'dest' de la URL
$dest = isset($_GET['dest']) ? htmlspecialchars($_GET['dest'], ENT_QUOTES, 'UTF-8') : '';
$destHiddenField = '<input type="hidden" name="dest_identificador" value="' . $dest . '">';

// Verificar si hay una cookie que indique que el formulario fue enviado recientemente
$isBlocked = isset($_COOKIE['form_submitted_recently']);
$blockMessage = '';

if ($isBlocked) {
    $blockMessage = "El formulario ya fue enviado recientemente. Inténtelo de nuevo en unas 48 horas.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Servicio de Asesoría</title>
    <!-- Opcional: Incluir Bootstrap o CSS propio para mejorar la apariencia -->
     <style>
        :root {
            --brand-color: #2c7be5;
            --brand-text-color: #ffffff;
            --bg: #f5f7fb;
            --card-bg: #ffffff;
            --border: #e5e7eb;
        }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: var(--bg); color: #111827; }
        .brand-header { background: var(--brand-color); color: var(--brand-text-color); }
        .brand-inner { max-width: 1000px; margin: 0 auto; padding: 16px 24px; display: flex; align-items: center; gap: 16px; }
        .logo-slot { width: 140px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 8px; }
        .brand-title { font-size: 20px; font-weight: 700; }
        .page { max-width: 1000px; margin: 24px auto; padding: 0 24px; }
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.06); padding: 24px; }
        h2 { margin-top: 0; font-size: 24px; font-weight: 700; }
        .section-title { width: 100%; background: var(--brand-color); color: var(--brand-text-color); padding: 10px 14px; border-radius: 8px; font-size: 16px; margin: 24px 0 12px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea, select { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; }
        .checkbox-group { display: inline-flex; align-items: center; gap: 6px; margin-right: 16px; margin-bottom: 8px; }
        .blocked-message { color: #b91c1c; font-weight: 700; margin-bottom: 15px; }
        .submit-btn { background-color: var(--brand-color); color: var(--brand-text-color); padding: 12px 18px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .submit-btn:disabled { background-color: #cccccc; cursor: not-allowed; }
        @media (max-width: 640px) {
            .brand-inner { padding: 14px 18px; }
            .logo-slot { width: 110px; height: 50px; }
            .card { padding: 16px; }
        }
    </style>

</head>
<body>
<header class="brand-header">
    <div class="brand-inner">
        <div class="logo-slot"></div>
        <div class="brand-title">Formulario Servicio de Asesoría</div>
    </div>
</header>
<div class="page">
<div class="card">

<h2>Servicio de Asesoría</h2>
<p>Le agradecemos por su tiempo al llenar el siguiente formulario que nos proporcionará de información valiosa para poder realizar la propuesta de prestación de servicios de asesoría adecuada a sus necesidades.</p>

<?php if ($blockMessage): ?>
    <div class="blocked-message"><?php echo $blockMessage; ?></div>
<?php endif; ?>

<form id="asesoriaForm" action="enviar_formulario.php" method="POST" <?php if ($isBlocked) echo 'style="display:none;"'; ?>>
    <?php echo $destHiddenField; // Campo oculto con el valor de 'dest' ?>
    
    <h3 class="section-title">DATOS DE LA EMPRESA</h3>
    <div class="form-group">
        <label for="nombre_comercial">Nombre Comercial/Razón Social:</label>
        <input type="text" id="nombre_comercial" name="nombre_comercial" required>
    </div>
    <div class="form-group">
        <label for="ruc">RUC:</label>
        <input type="text" id="ruc" name="ruc" required>
        <label for="telefono_empresa">Teléfono:</label>
        <input type="tel" id="telefono_empresa" name="telefono_empresa">
    </div>
    <div class="form-group">
        <label for="direccion_ruc">Dirección (RUC):</label>
        <input type="text" id="direccion_ruc" name="direccion_ruc" required>
    </div>
    <div class="form-group">
        <label for="persona_contacto">Persona de Contacto:</label>
        <input type="text" id="persona_contacto" name="persona_contacto" required>
        <label for="cargo_contacto">Cargo:</label>
        <input type="text" id="cargo_contacto" name="cargo_contacto">
    </div>
    <div class="form-group">
        <label for="correo_contacto">Correo de Contacto:</label>
        <input type="email" id="correo_contacto" name="correo_contacto" required>
        <label for="telefono_contacto">Teléfono:</label>
        <input type="tel" id="telefono_contacto" name="telefono_contacto">
    </div>
    <div class="form-group">
        <label for="direccion_oficina">Dirección/ubicación de oficina:</label>
        <input type="text" id="direccion_oficina" name="direccion_oficina">
        <label for="ciudad_oficina">Ciudad:</label>
        <input type="text" id="ciudad_oficina" name="ciudad_oficina">
    </div>
    <div class="form-group">
        <label for="direccion_planta">Dirección/ubicación de Planta:</label>
        <input type="text" id="direccion_planta" name="direccion_planta">
        <label for="ciudad_planta">Ciudad:</label>
        <input type="text" id="ciudad_planta" name="ciudad_planta">
    </div>
    <div class="form-group">
        <label>Si son más establecimientos para plantas u oficinas:</label>
        <div id="establecimientos_extra">
            <div class="establecimiento-item">
                <input type="text" name="direccion_establecimiento[]" placeholder="Dirección/ubicación">
                <input type="text" name="ciudad_establecimiento[]" placeholder="Ciudad">
            </div>
        </div>
        <button type="button" onclick="agregarEstablecimiento()">Agregar otro establecimiento</button>
    </div>
    <div class="form-group">
        <label for="certificaciones">Certificaciones que actualmente posee:</label>
        <textarea id="certificaciones" name="certificaciones" rows="3"></textarea>
    </div>
    <div class="form-group">
        <label for="organismo_certificador">Organismo certificador/acreditador:</label>
        <textarea id="organismo_certificador" name="organismo_certificador" rows="3"></textarea>
    </div>
    <div class="form-group">
        <label for="alcance_certificacion">Alcance de la certificación:</label>
        <textarea id="alcance_certificacion" name="alcance_certificacion" rows="3"></textarea>
    </div>

    <h3 class="section-title">SERVICIO REQUERIDO</h3>
    <div class="form-group">
        <div class="checkbox-group">
            <input type="checkbox" id="iso_9001" name="servicios_requeridos[]" value="ISO 9001">
            <label for="iso_9001">ISO 9001</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="bpm" name="servicios_requeridos[]" value="BPM">
            <label for="bpm">BPM</label>
        </div>
        <!-- Agrega más checkboxes según sea necesario -->
        <div class="checkbox-group">
            <input type="checkbox" id="iso_45001" name="servicios_requeridos[]" value="ISO 45001">
            <label for="iso_45001">ISO 45001</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="haccp" name="servicios_requeridos[]" value="HACCP">
            <label for="haccp">HACCP</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="iso_14001" name="servicios_requeridos[]" value="ISO 14001">
            <label for="iso_14001">ISO 14001</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="iso_22000" name="servicios_requeridos[]" value="ISO 22000">
            <label for="iso_22000">ISO 22000</label>
        </div>
         <div class="checkbox-group">
            <input type="checkbox" id="iso_27001" name="servicios_requeridos[]" value="ISO 27001">
            <label for="iso_27001">ISO 27001</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="fssc" name="servicios_requeridos[]" value="FSSC">
            <label for="fssc">FSSC</label>
        </div>
         <div class="checkbox-group">
            <input type="checkbox" id="iso_17025" name="servicios_requeridos[]" value="ISO 17025">
            <label for="iso_17025">ISO 17025</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="brc" name="servicios_requeridos[]" value="BRC">
            <label for="brc">BRC</label>
        </div>
         <div class="checkbox-group">
            <input type="checkbox" id="smeta" name="servicios_requeridos[]" value="SMETA">
            <label for="smeta">SMETA</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="basc" name="servicios_requeridos[]" value="BASC">
            <label for="basc">BASC</label>
        </div>
         <div class="checkbox-group">
            <input type="checkbox" id="rse" name="servicios_requeridos[]" value="RSE">
            <label for="rse">RSE</label>
        </div>
        <div class="checkbox-group">
            <input type="checkbox" id="otro_servicio" name="servicios_requeridos[]" value="Otro">
            <label for="otro_servicio">Otro (especifique)</label>
        </div>
    </div>
    <div class="form-group" id="otro_servicio_contenedor" style="display:none;">
        <label for="otro_servicio_texto">Especificar otro servicio:</label>
        <input type="text" id="otro_servicio_texto" name="otro_servicio_texto" placeholder="Detalle del servicio">
    </div>

    <h3 class="section-title">INFORMACIÓN ADICIONAL</h3>
    <div class="form-group">
        <label for="descripcion_negocio">Breve descripción de su Flujo de proceso o su giro de negocio:</label>
        <textarea id="descripcion_negocio" name="descripcion_negocio" rows="4"></textarea>
    </div>
    <div class="form-group">
        <label for="motivo_certificacion">Motivo que lo lleva a buscar la Certificación:</label>
        <textarea id="motivo_certificacion" name="motivo_certificacion" rows="4"></textarea>
    </div>
    <div class="form-group">
        <label>Cantidad de empleados:</label>
        <input type="number" name="empleados_administrativos" placeholder="Administrativos" min="0">
        <input type="number" name="empleados_operativos" placeholder="Operativos" min="0">
    </div>
    <div class="form-group">
        <label for="cantidad_turnos">Cantidad de Turnos:</label>
        <input type="number" id="cantidad_turnos" name="cantidad_turnos" min="0">
        <label for="personal_por_turno">Personal por turno:</label>
        <input type="number" id="personal_por_turno" name="personal_por_turno" min="0">
    </div>
    <div class="form-group">
        <label for="horarios_turnos">Horarios de Turnos:</label>
        <input type="text" id="horarios_turnos" name="horarios_turnos">
    </div>
    <div class="form-group">
        <label>Departamentos/áreas de la empresa:</label>
        <div id="departamentos_container">
            <div class="departamento-item">
                <input type="text" name="departamento_nombre[]" placeholder="Departamento/área">
                <input type="text" name="departamento_responsable[]" placeholder="Cargo Responsable">
                <input type="number" name="departamento_personal[]" placeholder="# Personal" min="0">
            </div>
        </div>
        <button type="button" onclick="agregarDepartamento()">Agregar otro departamento</button>
    </div>

    <div class="form-group">
        <input type="submit" value="Enviar Formulario" class="submit-btn" <?php if ($isBlocked) echo 'disabled'; ?>>
    </div>
</form>

<script>
function agregarEstablecimiento() {
    const container = document.getElementById('establecimientos_extra');
    const newItem = document.createElement('div');
    newItem.className = 'establecimiento-item';
    newItem.innerHTML = `
        <input type="text" name="direccion_establecimiento[]" placeholder="Dirección/ubicación">
        <input type="text" name="ciudad_establecimiento[]" placeholder="Ciudad">
        <button type="button" onclick="this.parentElement.remove()">Eliminar</button>
    `;
    container.appendChild(newItem);
}

function agregarDepartamento() {
    const container = document.getElementById('departamentos_container');
    const newItem = document.createElement('div');
    newItem.className = 'departamento-item';
    newItem.innerHTML = `
        <input type="text" name="departamento_nombre[]" placeholder="Departamento/área">
        <input type="text" name="departamento_responsable[]" placeholder="Cargo Responsable">
        <input type="number" name="departamento_personal[]" placeholder="# Personal" min="0">
        <button type="button" onclick="this.parentElement.remove()">Eliminar</button>
    `;
    container.appendChild(newItem);
}
const otroChk = document.getElementById('otro_servicio');
const otroCont = document.getElementById('otro_servicio_contenedor');
const otroTxt = document.getElementById('otro_servicio_texto');
if (otroChk && otroCont && otroTxt) {
    const toggleOtro = () => {
        if (otroChk.checked) {
            otroCont.style.display = 'block';
            otroTxt.required = true;
        } else {
            otroCont.style.display = 'none';
            otroTxt.required = false;
            otroTxt.value = '';
        }
    };
    otroChk.addEventListener('change', toggleOtro);
    toggleOtro();
}
</script>

</div>
</div>
</body>
</html>
