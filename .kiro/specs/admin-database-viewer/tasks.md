# Plan de Implementación: Panel de Administración de Base de Datos SQLite

## Descripción General

Este plan descompone la implementación del panel de administración web en tareas incrementales y ejecutables. Cada tarea construye sobre las anteriores, validando funcionalidad core tempranamente mediante código. El sistema utiliza PHP con SQLite y sigue un patrón MVC simplificado.

## Tareas

- [x] 1. Configurar estructura del proyecto y componentes base
  - Crear estructura de directorios (/admin/, /admin/config/, /admin/includes/, /admin/models/, /admin/assets/, /admin/logs/)
  - Crear archivo de configuración de base de datos (config/database.php) con conexión PDO a SQLite
  - Implementar tabla usuarios_admin en la base de datos si no existe
  - Crear archivo de funciones auxiliares básicas (includes/functions.php) con sanitizar(), formatearFecha(), jsonALista()
  - _Requisitos: 1.1, 9.2, 2.5_

- [x] 2. Implementar sistema de autenticación y sesiones
  - [x] 2.1 Crear módulo de autenticación (includes/auth.php)
    - Implementar verificarCredenciales() con password_verify()
    - Implementar obtenerHashPassword() con consultas preparadas
    - Implementar crearUsuarioAdmin() con password_hash()
    - _Requisitos: 1.1, 1.2, 1.3, 1.5_
  
  - [ ]* 2.2 Escribir property test para autenticación
    - **Property 2: Autenticación con credenciales inválidas deniega acceso**
    - **Valida: Requisitos 1.3**
  
  - [ ]* 2.3 Escribir property test para hashing de contraseñas
    - **Property 4: Contraseñas almacenadas con hash seguro**
    - **Valida: Requisitos 1.5**
  
  - [x] 2.4 Crear módulo de gestión de sesiones (includes/session.php)
    - Implementar iniciarSesion() con generación de token CSRF
    - Implementar verificarSesion() con validación de expiración
    - Implementar actualizarActividad() y sesionExpirada() (30 minutos)
    - Implementar cerrarSesion()
    - _Requisitos: 1.2, 1.4, 9.5_
  
  - [ ]* 2.5 Escribir property test para expiración de sesión
    - **Property 25: Expiración de sesión por inactividad**
    - **Valida: Requisitos 9.5**
  
  - [x] 2.6 Crear módulo de protección CSRF (includes/csrf.php)
    - Implementar generarTokenCSRF()
    - Implementar validarTokenCSRF()
    - Implementar campoTokenCSRF() para generar HTML
    - _Requisitos: 9.1_
  
  - [ ]* 2.7 Escribir property test para protección CSRF
    - **Property 22: Protección CSRF en formularios de modificación**
    - **Valida: Requisitos 9.1**

- [x] 3. Checkpoint - Verificar autenticación y seguridad
  - Asegurar que todos los tests pasen, preguntar al usuario si surgen dudas.

- [x] 4. Implementar modelo de datos (FormularioModel.php)
  - [x] 4.1 Crear clase FormularioModel con constructor PDO
    - Implementar constructor que recibe conexión PDO
    - Implementar método privado decodificarCamposJSON() para procesar arrays JSON
    - _Requisitos: 2.5, 4.2_
  
  - [x] 4.2 Implementar método listar() con paginación y filtros
    - Implementar paginación (20 registros por página)
    - Implementar ordenamiento por fecha_registro DESC
    - Implementar filtros: búsqueda en múltiples campos, rango de fechas, servicio específico
    - Usar consultas preparadas para prevenir SQL injection
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 9.3_
  
  - [ ]* 4.3 Escribir property test para ordenamiento de registros
    - **Property 5: Registros ordenados por fecha descendente**
    - **Valida: Requisitos 2.3**
  
  - [ ]* 4.4 Escribir property test para paginación
    - **Property 6: Paginación con más de 20 registros**
    - **Valida: Requisitos 2.4**
  
  - [ ]* 4.5 Escribir property test para búsqueda en múltiples campos
    - **Property 8: Búsqueda filtra en múltiples campos**
    - **Valida: Requisitos 3.2**
  
  - [ ]* 4.6 Escribir property test para filtro por fechas
    - **Property 9: Filtro por rango de fechas**
    - **Valida: Requisitos 3.4**
  
  - [ ]* 4.7 Escribir property test para filtro por servicio
    - **Property 10: Filtro por servicio específico**
    - **Valida: Requisitos 3.6**
  
  - [x] 4.8 Implementar método obtenerPorId()
    - Retornar registro completo con campos JSON decodificados
    - Retornar null si no existe
    - _Requisitos: 4.1, 4.2_
  
  - [x] 4.9 Implementar método actualizar() con validación
    - Validar campos requeridos (nombre_comercial, ruc, correo_contacto)
    - Usar consultas preparadas
    - Retornar true/false según éxito
    - _Requisitos: 5.3, 5.4, 9.3_
  
  - [ ]* 4.10 Escribir property test para validación de campos requeridos
    - **Property 15: Validación de campos requeridos**
    - **Valida: Requisitos 5.4**
  
  - [ ]* 4.11 Escribir property test para round-trip de actualización
    - **Property 14: Actualización persiste cambios y confirma**
    - **Valida: Requisitos 5.3, 5.5**
  
  - [x] 4.12 Implementar método eliminar()
    - Eliminar registro por ID usando consultas preparadas
    - Retornar true/false según éxito
    - _Requisitos: 6.3, 9.3_
  
  - [ ]* 4.13 Escribir property test para eliminación
    - **Property 16: Eliminación exitosa remueve registro y confirma**
    - **Valida: Requisitos 6.3, 6.4**
  
  - [x] 4.14 Implementar método obtenerEstadisticas()
    - Calcular total de registros
    - Calcular registros del mes actual
    - Calcular registros de la semana actual
    - Calcular servicios más solicitados con conteo
    - _Requisitos: 8.2, 8.3, 8.4, 8.5_
  
  - [ ]* 4.15 Escribir property test para estadísticas de conteo
    - **Property 20: Estadísticas de conteo correctas**
    - **Valida: Requisitos 8.2, 8.3, 8.4**
  
  - [ ]* 4.16 Escribir property test para servicios más solicitados
    - **Property 21: Servicios más solicitados calculados correctamente**
    - **Valida: Requisitos 8.5**
  
  - [x] 4.17 Implementar método exportarCSV()
    - Generar archivo CSV con registros filtrados
    - Incluir todas las columnas de la tabla
    - Convertir campos JSON a texto legible
    - Nombrar archivo con formato: formularios_asesoria_YYYYMMDD_HHMMSS.csv
    - _Requisitos: 7.2, 7.3, 7.4, 7.6_
  
  - [ ]* 4.18 Escribir property test para exportación CSV
    - **Property 17: Exportación CSV respeta filtros activos**
    - **Property 18: CSV contiene todas las columnas con JSON convertido**
    - **Property 19: Nombre de archivo CSV con formato correcto**
    - **Valida: Requisitos 7.2, 7.3, 7.4, 7.6**

- [x] 5. Checkpoint - Verificar modelo de datos
  - Asegurar que todos los tests pasen, preguntar al usuario si surgen dudas.

- [x] 6. Implementar funciones auxiliares completas
  - [x] 6.1 Completar includes/functions.php
    - Implementar registrarLog() para escribir en admin_actions.log con timestamp y usuario
    - Implementar generarPaginacion() para HTML de navegación entre páginas
    - _Requisitos: 9.4, 2.4_
  
  - [ ]* 6.2 Escribir property test para sanitización XSS
    - **Property 23: Sanitización previene XSS**
    - **Valida: Requisitos 9.2**
  
  - [ ]* 6.3 Escribir property test para logging de acciones
    - **Property 24: Logging de acciones de modificación**
    - **Valida: Requisitos 9.4**

- [x] 7. Crear páginas de autenticación
  - [x] 7.1 Implementar login.php
    - Crear formulario de login con campos usuario y contraseña
    - Procesar POST con verificarCredenciales()
    - Crear sesión con iniciarSesion() si credenciales válidas
    - Mostrar mensaje de error si credenciales inválidas
    - Incluir token CSRF en formulario
    - _Requisitos: 1.1, 1.2, 1.3_
  
  - [ ]* 7.2 Escribir property test para flujo de login
    - **Property 1: Autenticación con credenciales válidas crea sesión**
    - **Valida: Requisitos 1.2**
  
  - [x] 7.3 Implementar logout.php
    - Llamar cerrarSesion()
    - Redirigir a login.php
    - _Requisitos: 1.4_

- [x] 8. Crear página principal del dashboard (index.php)
  - [x] 8.1 Implementar verificación de sesión y redirección
    - Verificar sesión activa con verificarSesion()
    - Redirigir a login.php si no hay sesión
    - _Requisitos: 1.4_
  
  - [ ]* 8.2 Escribir property test para protección de páginas
    - **Property 3: Páginas protegidas redirigen sin sesión**
    - **Valida: Requisitos 1.4**
  
  - [x] 8.3 Implementar panel de estadísticas
    - Obtener estadísticas con FormularioModel::obtenerEstadisticas()
    - Mostrar total de registros, registros del mes, registros de la semana
    - Mostrar gráfico o lista de servicios más solicitados
    - _Requisitos: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6_
  
  - [x] 8.4 Implementar tabla de registros con filtros
    - Procesar filtros de búsqueda, fechas y servicio desde GET
    - Obtener registros con FormularioModel::listar() aplicando filtros
    - Mostrar tabla con columnas: ID, fecha_registro, nombre_comercial, ruc, persona_contacto, correo_contacto, servicios_requeridos
    - Decodificar campos JSON para visualización legible
    - Formatear fechas con formatearFecha()
    - _Requisitos: 2.1, 2.2, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_
  
  - [x] 8.5 Implementar paginación
    - Generar HTML de paginación con generarPaginacion()
    - Mantener filtros activos en enlaces de paginación
    - _Requisitos: 2.4_
  
  - [x] 8.6 Agregar botón de exportación
    - Enlace a export.php con filtros actuales como parámetros
    - _Requisitos: 7.1_

- [x] 9. Crear página de vista detallada (view.php)
  - [x] 9.1 Implementar visualización de registro completo
    - Verificar sesión activa
    - Obtener ID desde GET
    - Cargar registro con FormularioModel::obtenerPorId()
    - Mostrar error 404 si registro no existe
    - Mostrar todos los campos del formulario
    - Formatear arrays JSON (establecimientos, departamentos) en formato estructurado
    - Formatear fecha_registro en formato local (dd/mm/yyyy HH:mm)
    - _Requisitos: 4.1, 4.2, 4.3_
  
  - [ ]* 9.2 Escribir property test para visualización completa
    - **Property 11: Todos los campos presentes en vistas y formularios**
    - **Valida: Requisitos 4.1, 5.2**
  
  - [ ]* 9.3 Escribir property test para formato de arrays JSON
    - **Property 12: Arrays JSON formateados estructuradamente**
    - **Valida: Requisitos 4.2**
  
  - [ ]* 9.4 Escribir property test para formato de fechas
    - **Property 13: Fechas en formato local**
    - **Valida: Requisitos 4.3**
  
  - [x] 9.5 Agregar botones de acción
    - Botón para regresar a lista (index.php)
    - Botón para editar (edit.php)
    - Botón para eliminar (delete.php)
    - _Requisitos: 4.4, 5.1, 6.1_

- [x] 10. Checkpoint - Verificar visualización y navegación
  - Asegurar que todos los tests pasen, preguntar al usuario si surgen dudas.

- [x] 11. Crear página de edición (edit.php)
  - [x] 11.1 Implementar formulario de edición
    - Verificar sesión activa
    - Obtener ID desde GET
    - Cargar registro con FormularioModel::obtenerPorId()
    - Mostrar formulario con todos los campos pre-poblados
    - Incluir campo hidden con token CSRF
    - _Requisitos: 5.1, 5.2, 9.1_
  
  - [x] 11.2 Implementar procesamiento de actualización
    - Validar token CSRF con validarTokenCSRF()
    - Validar campos requeridos (nombre_comercial, ruc, correo_contacto)
    - Actualizar registro con FormularioModel::actualizar()
    - Registrar acción en log con registrarLog()
    - Mostrar mensaje de confirmación si exitoso
    - Mostrar mensaje de error si falla
    - Redirigir a view.php después de actualización exitosa
    - _Requisitos: 5.3, 5.4, 5.5, 9.1, 9.4_
  
  - [ ]* 11.3 Escribir tests de integración para flujo de edición
    - Test de actualización exitosa con datos válidos
    - Test de rechazo por campos requeridos vacíos
    - Test de rechazo por token CSRF inválido

- [x] 12. Crear página de eliminación (delete.php)
  - [x] 12.1 Implementar confirmación y eliminación
    - Verificar sesión activa
    - Obtener ID desde GET
    - Mostrar diálogo de confirmación con detalles del registro
    - Incluir formulario de confirmación con token CSRF
    - _Requisitos: 6.1, 6.2, 9.1_
  
  - [x] 12.2 Implementar procesamiento de eliminación
    - Validar token CSRF
    - Eliminar registro con FormularioModel::eliminar()
    - Registrar acción en log con registrarLog()
    - Mostrar mensaje de confirmación si exitoso
    - Mostrar mensaje de error descriptivo si falla
    - Redirigir a index.php después de eliminación exitosa
    - _Requisitos: 6.3, 6.4, 6.5, 9.1, 9.4_
  
  - [ ]* 12.3 Escribir tests de integración para flujo de eliminación
    - Test de eliminación exitosa
    - Test de rechazo por token CSRF inválido
    - Test de manejo de registro inexistente

- [x] 13. Crear página de exportación (export.php)
  - [x] 13.1 Implementar generación y descarga de CSV
    - Verificar sesión activa
    - Obtener filtros desde GET
    - Generar CSV con FormularioModel::exportarCSV()
    - Configurar headers HTTP para descarga automática
    - Enviar archivo al navegador
    - Eliminar archivo temporal después de envío
    - _Requisitos: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_
  
  - [ ]* 13.2 Escribir tests de integración para exportación
    - Test de generación de CSV con todos los registros
    - Test de CSV con filtros aplicados
    - Test de formato de nombre de archivo

- [x] 14. Implementar estilos CSS (assets/css/admin.css)
  - [x] 14.1 Crear estilos responsivos
    - Diseño responsivo para escritorio, tablet y móvil
    - Reorganizar tabla en móviles para facilitar lectura
    - Usar esquema de colores consistente con formulario principal
    - Estilos para formularios, tablas, botones, mensajes de error/éxito
    - _Requisitos: 10.1, 10.2, 10.4_
  
  - [x] 14.2 Verificar compatibilidad con navegadores
    - Probar en Chrome, Firefox, Safari, Edge
    - _Requisitos: 10.3_

- [x] 15. Implementar JavaScript del panel (assets/js/admin.js)
  - [x] 15.1 Agregar interactividad del lado del cliente
    - Confirmación de eliminación con diálogo JavaScript
    - Validación de formularios en tiempo real
    - Manejo de filtros con actualización dinámica
    - _Requisitos: 6.2_

- [x] 16. Crear script de inicialización
  - [x] 16.1 Crear script para configuración inicial
    - Script PHP para crear tabla usuarios_admin si no existe
    - Script para crear primer usuario administrador
    - Verificar permisos de escritura en directorio logs/
    - Verificar conexión a base de datos SQLite
    - _Requisitos: 1.1, 1.5, 9.4_

- [x] 17. Checkpoint final - Pruebas de integración completas
  - Asegurar que todos los tests pasen, preguntar al usuario si surgen dudas.
  - Verificar flujos completos: login → dashboard → ver → editar → eliminar → exportar
  - Verificar protecciones de seguridad: CSRF, XSS, SQL injection
  - Verificar logging de acciones
  - Verificar expiración de sesión

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Los property tests validan propiedades universales de correctness
- Los unit tests validan ejemplos específicos y casos edge
- Todas las operaciones de base de datos usan consultas preparadas para prevenir SQL injection
- Todos los formularios de modificación incluyen protección CSRF
- Todas las salidas de usuario se sanitizan para prevenir XSS
