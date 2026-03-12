# Módulo de Protección CSRF

Este módulo proporciona funciones para proteger formularios contra ataques Cross-Site Request Forgery (CSRF) mediante tokens únicos de sesión.

## Funciones Disponibles

### `generarTokenCSRF(): string`

Genera o retorna el token CSRF de la sesión actual.

**Retorna:** Token CSRF de 64 caracteres hexadecimales

**Ejemplo:**
```php
require_once 'includes/csrf.php';
$token = generarTokenCSRF();
echo "Token: " . $token;
```

### `validarTokenCSRF(string $token): bool`

Valida un token CSRF contra el token almacenado en la sesión.

**Parámetros:**
- `$token`: Token CSRF a validar (típicamente desde `$_POST['csrf_token']`)

**Retorna:** `true` si el token es válido, `false` en caso contrario

**Ejemplo:**
```php
require_once 'includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        die('Token de seguridad inválido');
    }
    
    // Procesar formulario...
}
```

### `campoTokenCSRF(): string`

Genera el HTML de un campo hidden con el token CSRF.

**Retorna:** HTML del campo `<input type="hidden" name="csrf_token" value="...">`

**Ejemplo:**
```php
require_once 'includes/csrf.php';
?>
<form method="POST" action="procesar.php">
    <?php echo campoTokenCSRF(); ?>
    <input type="text" name="nombre">
    <button type="submit">Enviar</button>
</form>
```

## Uso Completo

### En el Formulario (HTML)

```php
<?php
require_once 'includes/session.php';
require_once 'includes/csrf.php';

// Verificar sesión activa
if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<body>
    <form method="POST" action="editar.php">
        <?php echo campoTokenCSRF(); ?>
        
        <label>Nombre:</label>
        <input type="text" name="nombre" required>
        
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <button type="submit">Guardar</button>
    </form>
</body>
</html>
```

### En el Procesamiento (PHP)

```php
<?php
require_once 'includes/session.php';
require_once 'includes/csrf.php';

// Verificar sesión activa
if (!verificarSesion()) {
    header('Location: login.php');
    exit();
}

// Procesar solo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.');
    }
    
    // Token válido, procesar datos
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Validar y guardar datos...
    
    $_SESSION['success'] = 'Datos guardados exitosamente';
    header('Location: index.php');
    exit();
}
?>
```

## Integración con el Sistema de Sesiones

El módulo CSRF está integrado con el sistema de sesiones (`session.php`). Cuando se inicia una sesión con `iniciarSesion()`, automáticamente se genera un token CSRF que permanece válido durante toda la sesión.

```php
require_once 'includes/session.php';
require_once 'includes/csrf.php';

// Al iniciar sesión, se genera automáticamente el token CSRF
iniciarSesion('admin');

// El token está disponible inmediatamente
$token = generarTokenCSRF();
```

## Seguridad

- **Tokens únicos por sesión:** Cada sesión tiene su propio token CSRF único
- **Generación criptográficamente segura:** Usa `random_bytes()` para generar tokens impredecibles
- **Comparación segura:** Usa `hash_equals()` para prevenir timing attacks
- **Validación estricta:** Rechaza tokens vacíos, nulos o que no coincidan

## Requisitos

- PHP 7.0 o superior (para `random_bytes()` y `hash_equals()`)
- Sesiones PHP habilitadas
- El módulo `session.php` debe estar incluido para gestión de sesiones

## Notas Importantes

1. **Todos los formularios de modificación deben incluir el token CSRF:**
   - Formularios de edición
   - Formularios de eliminación
   - Cualquier operación POST/PUT/DELETE

2. **Validar el token antes de procesar datos:**
   - Siempre validar antes de realizar cambios en la base de datos
   - Retornar error 403 si el token es inválido

3. **No exponer tokens en URLs:**
   - Los tokens CSRF deben enviarse en campos hidden de formularios
   - Nunca incluir tokens en parámetros GET o URLs

4. **Regenerar token al cambiar de sesión:**
   - El token se regenera automáticamente al iniciar sesión
   - El token se elimina al cerrar sesión
