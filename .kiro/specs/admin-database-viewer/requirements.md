# Documento de Requisitos

## Introducción

Este documento especifica los requisitos para un panel de administración web que permita visualizar y gestionar los datos almacenados en la base de datos SQLite (database.db) del sistema de formularios de asesoría. El panel proporcionará funcionalidades de lectura, edición, eliminación y exportación de registros de formularios enviados por clientes.

## Glosario

- **Panel_Admin**: Interfaz web de administración ubicada en la carpeta /admin/
- **Base_Datos**: Base de datos SQLite ubicada en ./database.db
- **Registro**: Fila individual en la tabla formularios_asesoria
- **Administrador**: Usuario autorizado para acceder al Panel_Admin
- **Tabla_Formularios**: Tabla formularios_asesoria que contiene los datos de solicitudes de asesoría
- **Sistema_Autenticación**: Mecanismo de verificación de credenciales para acceso al Panel_Admin
- **Exportador**: Componente que genera archivos descargables con datos de registros

## Requisitos

### Requisito 1: Autenticación de Administrador

**User Story:** Como administrador del sistema, quiero autenticarme con credenciales seguras, para que solo usuarios autorizados puedan acceder a los datos sensibles de clientes.

#### Acceptance Criteria

1. THE Sistema_Autenticación SHALL solicitar usuario y contraseña antes de permitir acceso al Panel_Admin
2. WHEN un Administrador ingresa credenciales válidas, THE Sistema_Autenticación SHALL crear una sesión PHP segura
3. WHEN un usuario ingresa credenciales inválidas, THE Sistema_Autenticación SHALL mostrar un mensaje de error y denegar el acceso
4. WHILE no exista una sesión activa, THE Panel_Admin SHALL redirigir al usuario a la página de login
5. THE Sistema_Autenticación SHALL almacenar las contraseñas utilizando hash seguro (password_hash de PHP)

### Requisito 2: Visualización de Registros

**User Story:** Como administrador, quiero ver todos los registros de formularios en una tabla organizada, para que pueda revisar las solicitudes de asesoría recibidas.

#### Acceptance Criteria

1. WHEN un Administrador accede al Panel_Admin, THE Panel_Admin SHALL mostrar todos los registros de la Tabla_Formularios en formato tabular
2. THE Panel_Admin SHALL mostrar las columnas: ID, fecha_registro, nombre_comercial, ruc, persona_contacto, correo_contacto, y servicios_requeridos
3. THE Panel_Admin SHALL ordenar los registros por fecha_registro en orden descendente (más recientes primero)
4. WHEN la Tabla_Formularios contiene más de 20 registros, THE Panel_Admin SHALL implementar paginación con 20 registros por página
5. THE Panel_Admin SHALL decodificar campos JSON (servicios_requeridos, departamentos) para mostrarlos en formato legible

### Requisito 3: Búsqueda y Filtrado

**User Story:** Como administrador, quiero buscar y filtrar registros por diferentes criterios, para que pueda encontrar rápidamente solicitudes específicas.

#### Acceptance Criteria

1. THE Panel_Admin SHALL proporcionar un campo de búsqueda de texto
2. WHEN un Administrador ingresa texto en el campo de búsqueda, THE Panel_Admin SHALL filtrar registros que coincidan en nombre_comercial, ruc, persona_contacto o correo_contacto
3. THE Panel_Admin SHALL proporcionar filtros por rango de fechas
4. WHEN un Administrador selecciona un rango de fechas, THE Panel_Admin SHALL mostrar solo registros dentro de ese período
5. THE Panel_Admin SHALL proporcionar filtro por servicios_requeridos
6. WHEN un Administrador selecciona un servicio específico, THE Panel_Admin SHALL mostrar solo registros que incluyan ese servicio

### Requisito 4: Visualización Detallada de Registro

**User Story:** Como administrador, quiero ver todos los detalles de un registro individual, para que pueda revisar la información completa de una solicitud.

#### Acceptance Criteria

1. WHEN un Administrador hace clic en un Registro, THE Panel_Admin SHALL mostrar una vista detallada con todos los campos del formulario
2. THE Panel_Admin SHALL mostrar arrays JSON (establecimientos, departamentos) en formato estructurado y legible
3. THE Panel_Admin SHALL mostrar la fecha y hora de registro en formato local (dd/mm/yyyy HH:mm)
4. THE Panel_Admin SHALL proporcionar un botón para regresar a la lista de registros

### Requisito 5: Edición de Registros

**User Story:** Como administrador, quiero editar registros existentes, para que pueda corregir errores o actualizar información de clientes.

#### Acceptance Criteria

1. WHEN un Administrador visualiza un Registro detallado, THE Panel_Admin SHALL proporcionar un botón de edición
2. WHEN un Administrador hace clic en editar, THE Panel_Admin SHALL mostrar un formulario con todos los campos del Registro pre-poblados
3. WHEN un Administrador modifica campos y guarda, THE Panel_Admin SHALL actualizar el Registro en la Base_Datos
4. THE Panel_Admin SHALL validar que campos requeridos (nombre_comercial, ruc, correo_contacto) no estén vacíos antes de guardar
5. WHEN la actualización es exitosa, THE Panel_Admin SHALL mostrar un mensaje de confirmación y regresar a la vista detallada

### Requisito 6: Eliminación de Registros

**User Story:** Como administrador, quiero eliminar registros obsoletos o duplicados, para que pueda mantener la base de datos limpia.

#### Acceptance Criteria

1. WHEN un Administrador visualiza un Registro detallado, THE Panel_Admin SHALL proporcionar un botón de eliminación
2. WHEN un Administrador hace clic en eliminar, THE Panel_Admin SHALL solicitar confirmación mediante un diálogo
3. WHEN un Administrador confirma la eliminación, THE Panel_Admin SHALL eliminar el Registro de la Base_Datos
4. WHEN la eliminación es exitosa, THE Panel_Admin SHALL mostrar un mensaje de confirmación y regresar a la lista de registros
5. IF la eliminación falla, THEN THE Panel_Admin SHALL mostrar un mensaje de error descriptivo

### Requisito 7: Exportación de Datos

**User Story:** Como administrador, quiero exportar registros a formato CSV o Excel, para que pueda analizar los datos en otras herramientas o compartirlos con el equipo comercial.

#### Acceptance Criteria

1. THE Panel_Admin SHALL proporcionar un botón de exportación en la vista de lista
2. WHEN un Administrador hace clic en exportar, THE Exportador SHALL generar un archivo CSV con todos los registros visibles (aplicando filtros activos)
3. THE Exportador SHALL incluir todas las columnas de la Tabla_Formularios en el archivo CSV
4. THE Exportador SHALL convertir campos JSON a formato de texto legible en el CSV
5. WHEN la exportación se completa, THE Panel_Admin SHALL iniciar la descarga automática del archivo
6. THE Exportador SHALL nombrar el archivo con formato: formularios_asesoria_YYYYMMDD_HHMMSS.csv

### Requisito 8: Estadísticas Básicas

**User Story:** Como administrador, quiero ver estadísticas resumidas de las solicitudes, para que pueda tener una visión general del estado del negocio.

#### Acceptance Criteria

1. THE Panel_Admin SHALL mostrar un panel de estadísticas en la página principal
2. THE Panel_Admin SHALL calcular y mostrar el total de registros en la Base_Datos
3. THE Panel_Admin SHALL calcular y mostrar el número de registros del mes actual
4. THE Panel_Admin SHALL calcular y mostrar el número de registros de la semana actual
5. THE Panel_Admin SHALL mostrar un gráfico o lista de los servicios más solicitados
6. THE Panel_Admin SHALL actualizar las estadísticas cada vez que se carga la página principal

### Requisito 9: Seguridad y Protección de Datos

**User Story:** Como administrador del sistema, quiero que el panel esté protegido contra accesos no autorizados, para que los datos sensibles de clientes estén seguros.

#### Acceptance Criteria

1. THE Panel_Admin SHALL implementar protección CSRF para todos los formularios de modificación de datos
2. THE Panel_Admin SHALL sanitizar todas las entradas de usuario antes de mostrarlas (prevención XSS)
3. THE Panel_Admin SHALL utilizar consultas preparadas (prepared statements) para todas las operaciones con la Base_Datos
4. THE Panel_Admin SHALL registrar en un archivo de log todas las acciones de modificación (edición, eliminación) con timestamp y usuario
5. THE Panel_Admin SHALL cerrar la sesión automáticamente después de 30 minutos de inactividad

### Requisito 10: Interfaz Responsiva

**User Story:** Como administrador, quiero acceder al panel desde diferentes dispositivos, para que pueda revisar solicitudes desde mi computadora o dispositivo móvil.

#### Acceptance Criteria

1. THE Panel_Admin SHALL utilizar diseño responsivo que se adapte a pantallas de escritorio, tablet y móvil
2. WHEN se visualiza en dispositivos móviles, THE Panel_Admin SHALL reorganizar la tabla de registros para facilitar la lectura
3. THE Panel_Admin SHALL mantener funcionalidad completa en navegadores modernos (Chrome, Firefox, Safari, Edge)
4. THE Panel_Admin SHALL utilizar el mismo esquema de colores y estilos del formulario principal para consistencia visual
