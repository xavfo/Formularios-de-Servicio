# Panel de Administración - Guía de Instalación y Uso

## Descripción

Panel web de administración completo para gestionar los registros de formularios de asesoría almacenados en la base de datos SQLite. Incluye autenticación segura, operaciones CRUD, filtros avanzados, estadísticas y exportación a CSV.

## Requisitos del Sistema

- PHP 7.4 o superior
- Extensión PDO SQLite habilitada
- Servidor web (Apache, Nginx, o PHP built-in server)
- Permisos de escritura en el directorio del proyecto

## Instalación

### Paso 1: Verificar la estructura de archivos

Asegúrese de que la carpeta `/admin/` contenga la siguiente estructura:

```
/admin/
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── session.php
│   ├── csrf.php
│   └── functions.php
├── models/
│   └── FormularioModel.php
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── logs/
│   └── (se creará automáticamente)
├── exports/
│   └── (se creará automáticamente)
├── init.php
├── crear_usuario.php
├── login.php
├── logout.php
├── index.php
├── view.php
├── edit.php
├── delete.php
└── export.php
```

### Paso 2: Ejecutar el script de inicialización

Desde la línea de comandos, ejecute:

```bash
php admin/init.php
```

Este script:
- Verifica la conexión a la base de datos SQLite
- Crea la tabla `usuarios_admin` si no existe
- Verifica permisos de escritura en el directorio `logs/`
- Muestra el estado de usuarios administradores

### Paso 3: Crear un usuario administrador

Ejecute el script de creación de usuarios:

```bash
php admin/crear_usuario.php
```

Siga las instrucciones en pantalla:
1. Ingrese un nombre de usuario
2. Ingrese una contraseña (mínimo 8 caracteres)
3. Confirme la contraseña

El script creará el usuario con la contraseña hasheada de forma segura usando bcrypt.

### Paso 4: Configurar el servidor web

#### Opción A: Servidor PHP integrado (desarrollo)

```bash
php -S localhost:8000
```

Luego acceda a: `http://localhost:8000/admin/login.php`

#### Opción B: Apache/Nginx (producción)

Configure su servidor web para que apunte al directorio raíz del proyecto. El panel estará disponible en:

```
http://su-dominio.com/admin/login.php
```

**Importante para producción:**
- Habilite HTTPS
- Configure `session.cookie_secure` en `1` en `includes/session.php`
- Proteja el directorio `/admin/logs/` con `.htaccess` (ya incluido)

## Uso del Panel

### Inicio de Sesión

1. Acceda a `http://localhost/admin/login.php`
2. Ingrese sus credenciales de administrador
3. Será redirigido al dashboard principal

### Dashboard Principal

El dashboard muestra:
- **Estadísticas**: Total de registros, registros del mes, registros de la semana
- **Servicios más solicitados**: Top 5 de servicios con mayor demanda
- **Filtros de búsqueda**: Búsqueda por texto, rango de fechas, servicio específico
- **Tabla de registros**: Lista paginada con 20 registros por página
- **Botón de exportación**: Descarga CSV con los registros filtrados

### Operaciones Disponibles

#### Ver Detalles de un Registro
- Haga clic en el botón "Ver" en la tabla
- Se mostrarán todos los campos del formulario
- Los campos JSON se muestran en formato estructurado

#### Editar un Registro
- Haga clic en el botón "Editar" en la tabla o en la vista detallada
- Modifique los campos necesarios
- Los campos requeridos son: Nombre Comercial, RUC, Correo Contacto
- Haga clic en "Guardar Cambios"

#### Eliminar un Registro
- Haga clic en el botón "Eliminar" en la tabla o en la vista detallada
- Confirme la eliminación en el diálogo
- La acción no se puede deshacer

#### Exportar a CSV
- Aplique los filtros deseados (opcional)
- Haga clic en "Exportar a CSV"
- El archivo se descargará automáticamente con formato: `formularios_asesoria_YYYYMMDD_HHMMSS.csv`

#### Cerrar Sesión
- Haga clic en "Cerrar Sesión" en el header
- La sesión se cerrará de forma segura

## Características de Seguridad

### Autenticación
- Contraseñas hasheadas con bcrypt (PASSWORD_DEFAULT de PHP)
- Sesiones PHP seguras con regeneración de ID
- Expiración automática después de 30 minutos de inactividad

### Protección CSRF
- Todos los formularios de modificación incluyen tokens CSRF
- Los tokens se validan antes de procesar cualquier cambio

### Prevención de Ataques
- **SQL Injection**: Todas las consultas usan prepared statements
- **XSS**: Todas las salidas se sanitizan con htmlspecialchars()
- **Session Fixation**: Regeneración de ID de sesión al iniciar sesión

### Logging
- Todas las acciones de modificación se registran en `logs/admin_actions.log`
- Incluye: timestamp, usuario, acción, detalles

## Filtros y Búsqueda

### Búsqueda por Texto
Busca en los siguientes campos:
- Nombre Comercial
- RUC
- Persona de Contacto
- Correo Electrónico

### Filtro por Fechas
- Fecha Inicio: Registros desde esta fecha (inclusive)
- Fecha Fin: Registros hasta esta fecha (inclusive)

### Filtro por Servicio
Busca registros que incluyan el servicio especificado en el campo JSON `servicios_requeridos`.

### Limpiar Filtros
Haga clic en "Limpiar Filtros" para resetear todos los filtros y mostrar todos los registros.

## Paginación

- Se muestran 20 registros por página
- Navegación con botones "Anterior" y "Siguiente"
- Números de página con rango inteligente
- Los filtros se mantienen al cambiar de página

## Exportación CSV

El archivo CSV incluye:
- Todas las columnas de la tabla `formularios_asesoria`
- Campos JSON convertidos a texto legible (separados por comas)
- Codificación UTF-8 con BOM (compatible con Excel)
- Solo los registros visibles según filtros aplicados

## Solución de Problemas

### Error: "No se pudo conectar a la base de datos"
- Verifique que el archivo `database.db` existe en la raíz del proyecto
- Verifique permisos de lectura/escritura en `database.db`

### Error: "El directorio logs/ no tiene permisos de escritura"
```bash
chmod 755 admin/logs/
```

### Error: "Token de seguridad inválido"
- Recargue la página e intente nuevamente
- Verifique que las cookies estén habilitadas en su navegador

### La sesión expira muy rápido
- La sesión expira después de 30 minutos de inactividad
- Puede modificar `TIEMPO_EXPIRACION_SESION` en `includes/session.php`

### No puedo crear usuarios
- Verifique que está ejecutando `crear_usuario.php` desde la línea de comandos
- Verifique que la tabla `usuarios_admin` existe (ejecute `init.php`)

## Mantenimiento

### Crear Usuarios Adicionales
```bash
php admin/crear_usuario.php
```

### Ver Logs de Acciones
```bash
cat admin/logs/admin_actions.log
```

### Limpiar Archivos de Exportación Antiguos
Los archivos CSV se generan en `admin/exports/` y se eliminan automáticamente después de la descarga. Si quedan archivos huérfanos:
```bash
rm admin/exports/*.csv
```

### Backup de la Base de Datos
```bash
cp database.db database_backup_$(date +%Y%m%d).db
```

## Estructura de la Base de Datos

### Tabla: usuarios_admin
```sql
CREATE TABLE usuarios_admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    fecha_creacion TEXT DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TEXT
);
```

### Tabla: formularios_asesoria
(Tabla preexistente con 31 campos incluyendo campos JSON)

## Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: SQLite 3
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Seguridad**: PDO Prepared Statements, password_hash(), CSRF tokens
- **Arquitectura**: MVC simplificado

## Soporte y Contacto

Para reportar problemas o solicitar nuevas funcionalidades, contacte al administrador del sistema.

## Licencia

Este panel de administración fue desarrollado específicamente para la gestión de formularios de asesoría.

---

**Versión**: 1.0.0  
**Fecha**: 2026-03-11  
**Desarrollado por**: Kiro AI Assistant
