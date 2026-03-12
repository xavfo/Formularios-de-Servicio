# Módulo de Gestión de Sesiones

## Descripción

El módulo `session.php` proporciona funciones para gestionar sesiones PHP seguras en el panel de administración. Implementa control de expiración automática (30 minutos de inactividad), generación de tokens CSRF y protección contra ataques de session fixation.

## Funciones Implementadas

### 1. `iniciarSesion(string $usuario): void`

Inicia una sesión segura para el usuario autenticado.

**Características:**
- Configura opciones de seguridad de sesión (httponly, strict mode)
- Regenera el ID de sesión para prevenir session fixation
- Establece variables de sesión: usuario, autenticado, ultima_actividad, ip_usuario
- Genera un token CSRF único de 64 caracteres hexadecimales
- Actualiza el último acceso en la base de datos

**Uso:**
```php
require_once 'includes/session.php';
iniciarSesion('admin');
```

### 2. `verificarSesion(): bool`

Verifica si existe una sesión activa válida.

**Validaciones:**
- Verifica que existan las variables de sesión necesarias
- Verifica que el usuario esté autenticado
- Verifica que la sesión no haya expirado
- Actualiza automáticamente el timestamp de actividad

**Retorna:**
- `true` si la sesión es válida
- `false` si no hay sesión o está expirada (cierra automáticamente la sesión expirada)

**Uso:**
```php
if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}
```

### 3. `actualizarActividad(): void`

Actualiza el timestamp de última actividad en la sesión.

**Uso:**
Esta función se llama automáticamente por `verificarSesion()`, pero puede llamarse manualmente si es necesario:
```php
actualizarActividad();
```

### 4. `sesionExpirada(): bool`

Verifica si la sesión ha expirado por inactividad (30 minutos).

**Retorna:**
- `true` si han transcurrido más de 30 minutos desde la última actividad
- `false` si la sesión está activa

**Uso:**
```php
if (sesionExpirada()) {
    cerrarSesion();
    header('Location: login.php?error=sesion_expirada');
    exit();
}
```

### 5. `cerrarSesion(): void`

Cierra la sesión actual de forma segura.

**Acciones:**
- Limpia todas las variables de sesión
- Elimina la cookie de sesión del navegador
- Destruye la sesión PHP

**Uso:**
```php
cerrarSesion();
header('Location: login.php');
exit();
```

## Constantes

### `TIEMPO_EXPIRACION_SESION`

Define el tiempo de expiración de sesión en segundos.

**Valor:** `1800` (30 minutos)

## Variables de Sesión

El módulo establece las siguientes variables en `$_SESSION`:

- `usuario` (string): Nombre del usuario autenticado
- `autenticado` (bool): Indica si el usuario está autenticado
- `ultima_actividad` (int): Timestamp UNIX de la última actividad
- `ip_usuario` (string): Dirección IP del usuario
- `csrf_token` (string): Token CSRF único de 64 caracteres

## Seguridad

### Protección contra Session Fixation

El módulo regenera el ID de sesión al iniciar sesión usando `session_regenerate_id(true)`.

### Configuración de Cookies Seguras

- `httponly`: Previene acceso a cookies desde JavaScript
- `strict_mode`: Previene uso de IDs de sesión no inicializados
- `secure`: Debe habilitarse en producción con HTTPS

### Expiración Automática

Las sesiones expiran automáticamente después de 30 minutos de inactividad. La función `verificarSesion()` cierra automáticamente las sesiones expiradas.

### Token CSRF

Cada sesión genera un token CSRF único usando `random_bytes()` para proteger contra ataques CSRF. El token debe validarse en todos los formularios de modificación de datos.

## Integración con Base de Datos

El módulo actualiza el campo `ultimo_acceso` en la tabla `usuarios_admin` cada vez que se inicia sesión, permitiendo auditoría de accesos.

## Ejemplo de Uso Completo

```php
<?php
// En login.php (después de verificar credenciales)
require_once 'includes/auth.php';
require_once 'includes/session.php';

if (verificarCredenciales($_POST['usuario'], $_POST['password'])) {
    iniciarSesion($_POST['usuario']);
    header('Location: index.php');
    exit();
}

// En cualquier página protegida
require_once 'includes/session.php';

if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}

// Usuario autenticado, continuar con la página
echo "Bienvenido, " . $_SESSION['usuario'];

// En logout.php
require_once 'includes/session.php';
cerrarSesion();
header('Location: login.php');
exit();
?>
```

## Requisitos Validados

Este módulo implementa los siguientes requisitos del spec:

- **Requisito 1.2**: Creación de sesión PHP segura al autenticar
- **Requisito 1.4**: Redirección a login sin sesión activa
- **Requisito 9.5**: Expiración automática después de 30 minutos de inactividad

## Notas de Implementación

1. El módulo usa `session_status()` para verificar si la sesión ya está iniciada antes de llamar a `session_start()`, evitando errores de headers ya enviados.

2. La función `cerrarSesion()` elimina la cookie de sesión del navegador usando `setcookie()` con un timestamp pasado.

3. El token CSRF se genera usando `random_bytes(32)` y se convierte a hexadecimal, resultando en un token de 64 caracteres.

4. La actualización del último acceso en la base de datos es silenciosa - los errores se registran pero no interrumpen el flujo de la sesión.
