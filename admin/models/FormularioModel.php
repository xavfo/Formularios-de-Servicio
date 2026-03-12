<?php
/**
 * FormularioModel.php
 * 
 * Modelo de acceso a datos para la tabla formularios_asesoria.
 * Encapsula toda la lógica de acceso a datos usando PDO con prepared statements.
 */

class FormularioModel {
    private PDO $db;
    
    /**
     * Constructor que recibe conexión PDO
     * 
     * @param PDO $db Conexión PDO a la base de datos SQLite
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Decodifica campos JSON para visualización
     * 
     * Procesa los campos que contienen arrays JSON y los convierte
     * a arrays PHP para facilitar su manipulación y visualización.
     * 
     * @param array $registro Registro de la base de datos
     * @return array Registro con campos JSON decodificados
     */
    private function decodificarCamposJSON(array $registro): array {
        // Lista de campos que contienen JSON
        $camposJSON = [
            'servicios_requeridos',
            'direcciones_establecimiento',
            'ciudades_establecimiento',
            'departamentos_nombre',
            'departamentos_responsable',
            'departamentos_personal'
        ];
        
        // Decodificar cada campo JSON
        foreach ($camposJSON as $campo) {
            if (isset($registro[$campo]) && !empty($registro[$campo])) {
                $decodificado = json_decode($registro[$campo], true);
                
                // Si la decodificación fue exitosa, usar el array decodificado
                // Si falla, mantener el valor original
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodificado)) {
                    $registro[$campo] = $decodificado;
                } else {
                    // Si no es JSON válido, convertir a array vacío
                    $registro[$campo] = [];
                }
            } else {
                // Si el campo está vacío o no existe, usar array vacío
                $registro[$campo] = [];
            }
        }
        
        return $registro;
    }
    
    /**
     * Lista registros con paginación y filtros
     * 
     * @param int $pagina Número de página (1-indexed)
     * @param int $porPagina Cantidad de registros por página (default: 20)
     * @param array $filtros Filtros opcionales: ['busqueda' => string, 'fecha_inicio' => string, 'fecha_fin' => string, 'servicio' => string]
     * @return array ['registros' => array, 'total' => int, 'paginas' => int]
     */
    public function listar(int $pagina = 1, int $porPagina = 20, array $filtros = []): array {
        // Construir la consulta base
        $sql = "SELECT * FROM formularios_asesoria WHERE 1=1";
        $params = [];
        
        // Aplicar filtro de búsqueda en múltiples campos
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                nombre_comercial LIKE :busqueda1 OR 
                ruc LIKE :busqueda2 OR 
                persona_contacto LIKE :busqueda3 OR 
                correo_contacto LIKE :busqueda4
            )";
            $terminoBusqueda = '%' . $filtros['busqueda'] . '%';
            $params[':busqueda1'] = $terminoBusqueda;
            $params[':busqueda2'] = $terminoBusqueda;
            $params[':busqueda3'] = $terminoBusqueda;
            $params[':busqueda4'] = $terminoBusqueda;
        }
        
        // Aplicar filtro de rango de fechas
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND fecha_registro >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            // Agregar 23:59:59 para incluir todo el día final
            $sql .= " AND fecha_registro <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'] . ' 23:59:59';
        }
        
        // Aplicar filtro por servicio específico
        if (!empty($filtros['servicio'])) {
            $sql .= " AND servicios_requeridos LIKE :servicio";
            $params[':servicio'] = '%' . $filtros['servicio'] . '%';
        }
        
        // Contar total de registros (sin paginación)
        $sqlCount = "SELECT COUNT(*) as total FROM formularios_asesoria WHERE 1=1";
        if (!empty($filtros['busqueda'])) {
            $sqlCount .= " AND (
                nombre_comercial LIKE :busqueda1 OR 
                ruc LIKE :busqueda2 OR 
                persona_contacto LIKE :busqueda3 OR 
                correo_contacto LIKE :busqueda4
            )";
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sqlCount .= " AND fecha_registro >= :fecha_inicio";
        }
        if (!empty($filtros['fecha_fin'])) {
            $sqlCount .= " AND fecha_registro <= :fecha_fin";
        }
        if (!empty($filtros['servicio'])) {
            $sqlCount .= " AND servicios_requeridos LIKE :servicio";
        }
        
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch()['total'];
        
        // Calcular número de páginas
        $totalPaginas = ceil($total / $porPagina);
        
        // Aplicar ordenamiento por fecha descendente
        $sql .= " ORDER BY fecha_registro DESC";
        
        // Aplicar paginación
        $offset = ($pagina - 1) * $porPagina;
        $sql .= " LIMIT :limit OFFSET :offset";
        
        // Ejecutar consulta
        $stmt = $this->db->prepare($sql);
        
        // Bind de parámetros
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $registros = $stmt->fetchAll();
        
        // Decodificar campos JSON en cada registro
        $registros = array_map([$this, 'decodificarCamposJSON'], $registros);
        
        return [
            'registros' => $registros,
            'total' => $total,
            'paginas' => $totalPaginas
        ];
    }
    
    /**
     * Obtiene un registro por ID
     * 
     * @param int $id ID del registro
     * @return array|null Registro completo con campos JSON decodificados, o null si no existe
     */
    public function obtenerPorId(int $id): ?array {
        $sql = "SELECT * FROM formularios_asesoria WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $registro = $stmt->fetch();
        
        if ($registro === false) {
            return null;
        }
        
        return $this->decodificarCamposJSON($registro);
    }
    
    /**
     * Actualiza un registro existente
     * 
     * @param int $id ID del registro a actualizar
     * @param array $datos Array asociativo con los campos a actualizar
     * @return bool True si la actualización fue exitosa, false en caso contrario
     */
    public function actualizar(int $id, array $datos): bool {
        // Validar campos requeridos
        if (isset($datos['nombre_comercial']) && empty(trim($datos['nombre_comercial']))) {
            return false;
        }
        if (isset($datos['ruc']) && empty(trim($datos['ruc']))) {
            return false;
        }
        if (isset($datos['correo_contacto']) && empty(trim($datos['correo_contacto']))) {
            return false;
        }
        
        // Construir la consulta de actualización dinámicamente
        $campos = [];
        $params = [':id' => $id];
        
        // Lista de campos permitidos para actualización
        $camposPermitidos = [
            'dest_identificador', 'nombre_comercial', 'ruc', 'telefono_empresa',
            'direccion_ruc', 'persona_contacto', 'cargo_contacto', 'correo_contacto',
            'telefono_contacto', 'direccion_oficina', 'ciudad_oficina', 'direccion_planta',
            'ciudad_planta', 'certificaciones', 'organismo_certificador', 'alcance_certificacion',
            'servicios_requeridos', 'direcciones_establecimiento', 'ciudades_establecimiento',
            'descripcion_negocio', 'motivo_certificacion', 'empleados_administrativos',
            'empleados_operativos', 'cantidad_turnos', 'personal_por_turno', 'horarios_turnos',
            'departamentos_nombre', 'departamentos_responsable', 'departamentos_personal'
        ];
        
        foreach ($datos as $campo => $valor) {
            if (in_array($campo, $camposPermitidos)) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $valor;
            }
        }
        
        // Si no hay campos para actualizar, retornar false
        if (empty($campos)) {
            return false;
        }
        
        $sql = "UPDATE formularios_asesoria SET " . implode(', ', $campos) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al actualizar registro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un registro
     * 
     * @param int $id ID del registro a eliminar
     * @return bool True si la eliminación fue exitosa, false en caso contrario
     */
    public function eliminar(int $id): bool {
        $sql = "DELETE FROM formularios_asesoria WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            
            // Verificar que se eliminó al menos una fila
            return $resultado && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al eliminar registro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene estadísticas del dashboard
     * 
     * @return array ['total' => int, 'mes_actual' => int, 'semana_actual' => int, 'servicios_populares' => array]
     */
    public function obtenerEstadisticas(): array {
        // Total de registros
        $sqlTotal = "SELECT COUNT(*) as total FROM formularios_asesoria";
        $stmtTotal = $this->db->query($sqlTotal);
        $total = $stmtTotal->fetch()['total'];
        
        // Registros del mes actual
        $sqlMes = "SELECT COUNT(*) as total FROM formularios_asesoria 
                   WHERE strftime('%Y-%m', fecha_registro) = strftime('%Y-%m', 'now')";
        $stmtMes = $this->db->query($sqlMes);
        $mesActual = $stmtMes->fetch()['total'];
        
        // Registros de la semana actual (últimos 7 días)
        $sqlSemana = "SELECT COUNT(*) as total FROM formularios_asesoria 
                      WHERE fecha_registro >= date('now', '-7 days')";
        $stmtSemana = $this->db->query($sqlSemana);
        $semanaActual = $stmtSemana->fetch()['total'];
        
        // Servicios más solicitados
        $sqlServicios = "SELECT servicios_requeridos FROM formularios_asesoria 
                         WHERE servicios_requeridos IS NOT NULL AND servicios_requeridos != ''";
        $stmtServicios = $this->db->query($sqlServicios);
        $registros = $stmtServicios->fetchAll();
        
        // Contar servicios
        $conteoServicios = [];
        foreach ($registros as $registro) {
            $servicios = json_decode($registro['servicios_requeridos'], true);
            if (is_array($servicios)) {
                foreach ($servicios as $servicio) {
                    if (!empty($servicio)) {
                        if (!isset($conteoServicios[$servicio])) {
                            $conteoServicios[$servicio] = 0;
                        }
                        $conteoServicios[$servicio]++;
                    }
                }
            }
        }
        
        // Ordenar por cantidad descendente
        arsort($conteoServicios);
        
        // Convertir a formato de array de objetos
        $serviciosPopulares = [];
        foreach ($conteoServicios as $servicio => $cantidad) {
            $serviciosPopulares[] = [
                'servicio' => $servicio,
                'cantidad' => $cantidad
            ];
        }
        
        return [
            'total' => $total,
            'mes_actual' => $mesActual,
            'semana_actual' => $semanaActual,
            'servicios_populares' => $serviciosPopulares
        ];
    }
    
    /**
     * Exporta registros a CSV
     * 
     * @param array $filtros Filtros opcionales (mismos que listar())
     * @return string Ruta del archivo CSV generado
     */
    public function exportarCSV(array $filtros = []): string {
        // Obtener todos los registros con filtros (sin paginación)
        $resultado = $this->listar(1, 999999, $filtros);
        $registros = $resultado['registros'];
        
        // Generar nombre de archivo con timestamp
        $timestamp = date('Ymd_His');
        $nombreArchivo = "formularios_asesoria_{$timestamp}.csv";
        $rutaArchivo = __DIR__ . '/../exports/' . $nombreArchivo;
        
        // Crear directorio exports si no existe
        $dirExports = __DIR__ . '/../exports/';
        if (!is_dir($dirExports)) {
            mkdir($dirExports, 0755, true);
        }
        
        // Abrir archivo para escritura
        $archivo = fopen($rutaArchivo, 'w');
        
        // Escribir BOM para UTF-8 (para Excel)
        fprintf($archivo, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        $encabezados = [
            'ID', 'Fecha Registro', 'Identificador', 'Nombre Comercial', 'RUC',
            'Teléfono Empresa', 'Dirección RUC', 'Persona Contacto', 'Cargo Contacto',
            'Correo Contacto', 'Teléfono Contacto', 'Dirección Oficina', 'Ciudad Oficina',
            'Dirección Planta', 'Ciudad Planta', 'Certificaciones', 'Organismo Certificador',
            'Alcance Certificación', 'Servicios Requeridos', 'Direcciones Establecimiento',
            'Ciudades Establecimiento', 'Descripción Negocio', 'Motivo Certificación',
            'Empleados Administrativos', 'Empleados Operativos', 'Cantidad Turnos',
            'Personal por Turno', 'Horarios Turnos', 'Departamentos Nombre',
            'Departamentos Responsable', 'Departamentos Personal'
        ];
        fputcsv($archivo, $encabezados);
        
        // Escribir datos
        foreach ($registros as $registro) {
            $fila = [
                $registro['id'],
                $registro['fecha_registro'],
                $registro['dest_identificador'] ?? '',
                $registro['nombre_comercial'] ?? '',
                $registro['ruc'] ?? '',
                $registro['telefono_empresa'] ?? '',
                $registro['direccion_ruc'] ?? '',
                $registro['persona_contacto'] ?? '',
                $registro['cargo_contacto'] ?? '',
                $registro['correo_contacto'] ?? '',
                $registro['telefono_contacto'] ?? '',
                $registro['direccion_oficina'] ?? '',
                $registro['ciudad_oficina'] ?? '',
                $registro['direccion_planta'] ?? '',
                $registro['ciudad_planta'] ?? '',
                $registro['certificaciones'] ?? '',
                $registro['organismo_certificador'] ?? '',
                $registro['alcance_certificacion'] ?? '',
                // Convertir arrays JSON a texto legible
                is_array($registro['servicios_requeridos']) ? implode(', ', $registro['servicios_requeridos']) : '',
                is_array($registro['direcciones_establecimiento']) ? implode(', ', $registro['direcciones_establecimiento']) : '',
                is_array($registro['ciudades_establecimiento']) ? implode(', ', $registro['ciudades_establecimiento']) : '',
                $registro['descripcion_negocio'] ?? '',
                $registro['motivo_certificacion'] ?? '',
                $registro['empleados_administrativos'] ?? '',
                $registro['empleados_operativos'] ?? '',
                $registro['cantidad_turnos'] ?? '',
                $registro['personal_por_turno'] ?? '',
                $registro['horarios_turnos'] ?? '',
                is_array($registro['departamentos_nombre']) ? implode(', ', $registro['departamentos_nombre']) : '',
                is_array($registro['departamentos_responsable']) ? implode(', ', $registro['departamentos_responsable']) : '',
                is_array($registro['departamentos_personal']) ? implode(', ', $registro['departamentos_personal']) : ''
            ];
            
            fputcsv($archivo, $fila);
        }
        
        fclose($archivo);
        
        return $rutaArchivo;
    }
}
