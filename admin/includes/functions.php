<?php
/**
 * Funciones auxiliares para el panel de administración
 * 
 * Este archivo contiene funciones de utilidad para sanitización,
 * formateo de datos y otras operaciones comunes
 */

/**
 * Sanitiza una cadena para prevenir XSS
 * 
 * Convierte caracteres especiales HTML a entidades HTML para
 * prevenir ataques de Cross-Site Scripting (XSS)
 * 
 * @param string|array $string Cadena o array a sanitizar
 * @return string Cadena sanitizada
 */
function sanitizar($string): string {
    // Si es un array, convertir a string vacío
    if (is_array($string)) {
        return '';
    }
    
    // Si es null o no es string, convertir a string vacío
    if (!is_string($string)) {
        return '';
    }
    
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Formatea una fecha ISO 8601 a formato local legible
 * 
 * Convierte fechas del formato YYYY-MM-DD HH:MM:SS (ISO 8601)
 * al formato dd/mm/yyyy HH:mm más legible para usuarios
 * 
 * @param string $fecha Fecha en formato ISO 8601
 * @return string Fecha formateada en formato local (dd/mm/yyyy HH:mm)
 */
function formatearFecha(string $fecha): string {
    try {
        $dt = new DateTime($fecha);
        return $dt->format('d/m/Y H:i');
    } catch (Exception $e) {
        error_log("Error al formatear fecha: " . $e->getMessage());
        return $fecha; // Retornar fecha original si hay error
    }
}

/**
 * Decodifica un array JSON a lista legible en HTML
 * 
 * Convierte un string JSON que contiene un array a una lista
 * HTML (<ul><li>) para visualización en la interfaz
 * 
 * @param string $json String JSON que contiene un array
 * @return string Lista HTML con los elementos del array
 */
function jsonALista(string $json): string {
    // Si el campo está vacío o es null, retornar mensaje
    if (empty($json)) {
        return '<em>No especificado</em>';
    }
    
    // Intentar decodificar el JSON
    $array = json_decode($json, true);
    
    // Si no es un array válido, retornar el valor original
    if (!is_array($array) || empty($array)) {
        return sanitizar($json);
    }
    
    // Construir lista HTML
    $html = '<ul>';
    foreach ($array as $item) {
        if (is_array($item)) {
            // Si el item es un array, convertirlo a string
            $html .= '<li>' . sanitizar(json_encode($item, JSON_UNESCAPED_UNICODE)) . '</li>';
        } else {
            $html .= '<li>' . sanitizar($item) . '</li>';
        }
    }
    $html .= '</ul>';
    
    return $html;
}

/**
 * Registra una acción en el log de administración
 * 
 * Escribe una entrada en el archivo admin_actions.log con timestamp,
 * usuario y detalles de la acción realizada
 * 
 * @param string $accion Tipo de acción realizada (ej: 'editar_registro', 'eliminar_registro')
 * @param string $usuario Usuario que realizó la acción
 * @param array $detalles Detalles adicionales de la acción (opcional)
 * @return void
 */
function registrarLog(string $accion, string $usuario, array $detalles = []): void {
    // Ruta del archivo de log
    $logFile = __DIR__ . '/../logs/admin_actions.log';
    
    // Crear directorio logs si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Preparar timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Preparar detalles como JSON
    $detallesJson = !empty($detalles) ? json_encode($detalles, JSON_UNESCAPED_UNICODE) : '{}';
    
    // Construir línea de log
    $logLine = sprintf(
        "[%s] Usuario: %s | Acción: %s | Detalles: %s\n",
        $timestamp,
        $usuario,
        $accion,
        $detallesJson
    );
    
    // Escribir en el archivo de log
    try {
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
        error_log("Error al escribir en log de administración: " . $e->getMessage());
    }
}

/**
 * Genera el HTML de paginación para navegación entre páginas
 * 
 * Crea los enlaces de navegación (anterior, números de página, siguiente)
 * para facilitar la navegación entre páginas de resultados
 * 
 * @param int $paginaActual Número de la página actual
 * @param int $totalPaginas Total de páginas disponibles
 * @param string $urlBase URL base para los enlaces (sin parámetro de página)
 * @return string HTML con los controles de paginación
 */
function generarPaginacion(int $paginaActual, int $totalPaginas, string $urlBase): string {
    // Si solo hay una página, no mostrar paginación
    if ($totalPaginas <= 1) {
        return '';
    }
    
    // Asegurar que la URL base tenga el separador correcto para parámetros
    $separador = (strpos($urlBase, '?') !== false) ? '&' : '?';
    
    $html = '<nav class="paginacion" aria-label="Navegación de páginas">';
    $html .= '<ul class="paginacion-lista">';
    
    // Botón "Anterior"
    if ($paginaActual > 1) {
        $urlAnterior = $urlBase . $separador . 'pagina=' . ($paginaActual - 1);
        $html .= '<li class="paginacion-item">';
        $html .= '<a href="' . sanitizar($urlAnterior) . '" class="paginacion-link">&laquo; Anterior</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="paginacion-item paginacion-item-deshabilitado">';
        $html .= '<span class="paginacion-link">&laquo; Anterior</span>';
        $html .= '</li>';
    }
    
    // Números de página
    // Mostrar hasta 5 páginas alrededor de la página actual
    $rango = 2; // Páginas a cada lado de la actual
    $inicio = max(1, $paginaActual - $rango);
    $fin = min($totalPaginas, $paginaActual + $rango);
    
    // Primera página si no está en el rango
    if ($inicio > 1) {
        $urlPagina = $urlBase . $separador . 'pagina=1';
        $html .= '<li class="paginacion-item">';
        $html .= '<a href="' . sanitizar($urlPagina) . '" class="paginacion-link">1</a>';
        $html .= '</li>';
        
        if ($inicio > 2) {
            $html .= '<li class="paginacion-item paginacion-item-puntos">';
            $html .= '<span class="paginacion-link">...</span>';
            $html .= '</li>';
        }
    }
    
    // Páginas en el rango
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i == $paginaActual) {
            $html .= '<li class="paginacion-item paginacion-item-activo">';
            $html .= '<span class="paginacion-link">' . $i . '</span>';
            $html .= '</li>';
        } else {
            $urlPagina = $urlBase . $separador . 'pagina=' . $i;
            $html .= '<li class="paginacion-item">';
            $html .= '<a href="' . sanitizar($urlPagina) . '" class="paginacion-link">' . $i . '</a>';
            $html .= '</li>';
        }
    }
    
    // Última página si no está en el rango
    if ($fin < $totalPaginas) {
        if ($fin < $totalPaginas - 1) {
            $html .= '<li class="paginacion-item paginacion-item-puntos">';
            $html .= '<span class="paginacion-link">...</span>';
            $html .= '</li>';
        }
        
        $urlPagina = $urlBase . $separador . 'pagina=' . $totalPaginas;
        $html .= '<li class="paginacion-item">';
        $html .= '<a href="' . sanitizar($urlPagina) . '" class="paginacion-link">' . $totalPaginas . '</a>';
        $html .= '</li>';
    }
    
    // Botón "Siguiente"
    if ($paginaActual < $totalPaginas) {
        $urlSiguiente = $urlBase . $separador . 'pagina=' . ($paginaActual + 1);
        $html .= '<li class="paginacion-item">';
        $html .= '<a href="' . sanitizar($urlSiguiente) . '" class="paginacion-link">Siguiente &raquo;</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="paginacion-item paginacion-item-deshabilitado">';
        $html .= '<span class="paginacion-link">Siguiente &raquo;</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}
