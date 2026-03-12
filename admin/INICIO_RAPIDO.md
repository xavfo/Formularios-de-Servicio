# 🚀 Inicio Rápido - Panel de Administración

## Instalación en 3 Pasos

### 1️⃣ Inicializar el Sistema
```bash
php admin/init.php
```

### 2️⃣ Crear Usuario Administrador
```bash
php admin/crear_usuario.php
```

### 3️⃣ Iniciar Servidor
```bash
php -S localhost:8000
```

## 🌐 Acceder al Panel

Abra su navegador en: **http://localhost:8000/admin/login.php**

## 📋 Credenciales

Use el usuario y contraseña que creó en el paso 2.

## ✨ Funcionalidades Disponibles

- ✅ **Dashboard** con estadísticas en tiempo real
- 🔍 **Búsqueda y filtros** avanzados
- 👁️ **Ver detalles** completos de cada registro
- ✏️ **Editar** registros existentes
- 🗑️ **Eliminar** registros con confirmación
- 📊 **Exportar a CSV** con filtros aplicados
- 🔒 **Sesiones seguras** con expiración automática

## 🛡️ Seguridad

- Contraseñas hasheadas con bcrypt
- Protección CSRF en todos los formularios
- Prevención de SQL injection con prepared statements
- Sanitización de salidas (prevención XSS)
- Logging de todas las acciones

## 📱 Responsive

El panel funciona perfectamente en:
- 💻 Escritorio
- 📱 Tablets
- 📱 Móviles

## 🆘 Problemas Comunes

### No puedo conectarme a la base de datos
```bash
# Verificar que database.db existe
ls -la database.db

# Verificar permisos
chmod 644 database.db
```

### Error de permisos en logs/
```bash
chmod 755 admin/logs/
```

### Olvidé mi contraseña
```bash
# Crear un nuevo usuario
php admin/crear_usuario.php
```

## 📚 Documentación Completa

- `README_INSTALACION.md` - Guía detallada de instalación
- `RESUMEN_IMPLEMENTACION.md` - Detalles técnicos completos

## 🎯 Próximos Pasos

1. Inicie sesión en el panel
2. Explore el dashboard y las estadísticas
3. Pruebe los filtros de búsqueda
4. Exporte algunos registros a CSV
5. Edite un registro de prueba

---

**¿Listo para comenzar?** Ejecute los 3 comandos de instalación y acceda al panel. ¡Es así de simple! 🎉
