# Panel de Administración - Base de Datos SQLite

Panel web de administración para gestionar los registros de formularios de asesoría almacenados en la base de datos SQLite.

## Estructura del Proyecto

```
/admin/
├── config/
│   └── database.php          # Configuración de conexión PDO a SQLite
├── includes/
│   └── functions.php         # Funciones auxiliares (sanitizar, formatearFecha, jsonALista)
├── models/
│   └── (próximamente)        # Modelos de acceso a datos
├── assets/
│   ├── css/
│   │   └── (próximamente)    # Estilos del panel
│   └── js/
│       └── (próximamente)    # JavaScript del panel
├── logs/
│   └── (logs de acciones)    # Registro de acciones de administradores
├── init.php                  # Script de inicialización
└── README.md                 # Este archivo
```

## Instalación

1. Ejecutar el script de inicialización:
   ```bash
   php admin/init.php
   ```

2. Crear un usuario administrador:
   ```bash
   php admin/crear_usuario.php
   ```

3. Acceder al panel:
   ```
   http://localhost/admin/login.php
   ```

## Funciones Disponibles

### config/database.php
- `obtenerConexion()`: Retorna una conexión PDO a la base de datos SQLite
- `inicializarTablaUsuarios()`: Crea la tabla usuarios_admin si no existe

### includes/functions.php
- `sanitizar($string)`: Sanitiza cadenas para prevenir XSS
- `formatearFecha($fecha)`: Convierte fechas ISO 8601 a formato dd/mm/yyyy HH:mm
- `jsonALista($json)`: Convierte arrays JSON a listas HTML

## Requisitos

- PHP 7.4 o superior
- Extensión PDO SQLite habilitada
- Permisos de escritura en el directorio logs/

## Seguridad

- Todas las consultas usan prepared statements (PDO)
- Sanitización de entradas para prevenir XSS
- Protección CSRF en formularios (próximamente)
- Sesiones seguras con expiración automática (próximamente)
