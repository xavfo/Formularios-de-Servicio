# Formulario de Servicios de Asesoría

Este proyecto contiene una aplicación web simple desarrollada en PHP que permite a los usuarios completar un formulario de solicitud de servicios de asesoría. Los datos ingresados se almacenan localmente en una base de datos SQLite y se envía un correo electrónico de confirmación al contacto proporcionado. El acceso al formulario se bloquea durante 48 horas después de un envío exitoso mediante el uso de cookies.

## Características

*   **Formulario Web:** Interfaz HTML basada en el archivo `Formulario_Servicios_Asesoria.xlsx`.
*   **Persistencia de Datos:** Utiliza SQLite para almacenar los datos del formulario localmente en el servidor.
*   **Confirmación por Correo:** Envía un correo electrónico de confirmación con los detalles del formulario enviado utilizando PHPMailer.
*   **Bloqueo Temporal:** Impide reenvíos frecuentes del formulario bloqueando el acceso por 48 horas mediante cookies del lado del cliente.
*   **Identificador de Origen:** Captura y registra un identificador opcional pasado como parámetro en la URL (por ejemplo, `?dest=corpfavorita`).

## Requisitos del Sistema

*   **Servidor Web:** Apache, Nginx u otro compatible con PHP.
*   **PHP:** Versión 7.4 o superior recomendada.
*   **Extensiones PHP:** `pdo_sqlite`, `mbstring`, `json`.
*   **Composer:** Para gestionar dependencias de PHP (requerido para PHPMailer).

## Instalación

1.  **Clonar o Descargar:** Coloque los archivos del proyecto en el directorio raíz de su servidor web.
2.  **Instalar Dependencias:** Navegue hasta el directorio del proyecto en su terminal y ejecute:
    ```bash
    composer install
    ```
    Esto instalará PHPMailer y sus dependencias en una carpeta `vendor`.
3.  **Configurar Correo:** Edite el archivo `enviar_formulario.php`. Actualice las variables de configuración de SMTP (líneas relacionadas con `$mail->Host`, `$mail->Username`, `$mail->Password`, etc.) con los datos de su servidor de correo saliente (SMTP). Es **altamente recomendable** usar contraseñas de aplicación si su proveedor de correo las soporta (por ejemplo, Gmail).
4.  **Permisos del Servidor Web:**
    *   Asegúrese de que el servidor web tenga permisos de **lectura y escritura** en el directorio donde se encuentra el script `enviar_formulario.php`. Esto es necesario para que PHP pueda crear y escribir en el archivo de base de datos `database.db` si no existe, y para actualizarlo posteriormente.
5.  **Prueba:** Acceda al archivo `formularioServicios.php` desde su navegador web (por ejemplo, `http://localhost/tuproyecto/formularioServicios.php`).

## Uso

1.  Acceda a `formularioServicios.php` en su navegador.
2.  Opcionalmente, puede pasar un identificador en la URL: `formularioServicios.php?dest=miidentificador`.
3.  Complete todos los campos requeridos del formulario.
4.  Haga clic en "Enviar Formulario".
5.  Si todo es correcto:
    *   Los datos se guardarán en `database.db`.
    *   Se enviará un correo de confirmación al correo especificado en el formulario.
    *   El formulario se ocultará en la página actual y futuras recargas estarán bloqueadas por 48 horas para ese navegador (debido a la cookie).
    *   Se mostrará un mensaje de éxito (opcionalmente puede redirigir a una página específica).
6.  Si el formulario fue enviado recientemente (dentro de las últimas 48 horas), verá un mensaje de bloqueo y el formulario estará oculto.

## Seguridad

*   **Correos Electrónicos:** Las credenciales del servidor SMTP (`$mail->Username`, `$mail->Password`) son sensibles. No las revele en el código fuente si este es público. Considere usar variables de entorno para almacen   **Cookies:** El bloqueo por cookie (`form_submitted_recently`) se basa en el cliente. Un usuario puede evadirlo borrando las cookies o usando otro navegador. Para un bloqueo más estricto (por ejemplo, por IP), se debería implementar un control adicional en el servidor, posiblemente almacenando la información en la base de datos.
*   **Entrada de Usuario:** El código incluye sanitización básica de cadenas (`htmlspecialchars`, `strip_tags`, `trim`) y validación de correo electrónico (`filter_var`). Considere añadir validaciones más exhaustivas según las reglas de negocio específicas de cada campo.

## Archivos Principales

*   `formularioServicios.php`: Muestra el formulario HTML y maneja la lógica de visualización (bloqueo).
*   `enviar_formulario.php`: Procesa los datos POST, los guarda en SQLite, envía el correo de confirmación y establece la cookie de bloqueo.
*   `database.db`: Archivo de base de datos SQLite generado dinámicamente. **No lo edite manualmente mientras la aplicación esté en ejecución.**
*   `vendor/`: Directorio generado por Composer que contiene las bibliotecas externas (como PHPMailer).
*   `composer.json` / `composer.lock`: Archivos de gestión de dependencias de Composer.
*   `README.md`: Este archivo.