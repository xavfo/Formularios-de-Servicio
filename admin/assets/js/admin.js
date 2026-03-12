/**
 * admin.js - JavaScript para el panel de administración
 * 
 * Proporciona interactividad del lado del cliente:
 * - Confirmación de eliminación
 * - Validación de formularios en tiempo real
 * - Manejo de filtros
 */

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    
    // Confirmación de eliminación
    initDeleteConfirmation();
    
    // Validación de formularios
    initFormValidation();
    
    // Manejo de filtros
    initFilterHandling();
    
});

/**
 * Inicializa la confirmación de eliminación con diálogo JavaScript
 */
function initDeleteConfirmation() {
    // Buscar todos los enlaces de eliminación
    const deleteLinks = document.querySelectorAll('a[href*="delete.php"]');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Solo aplicar confirmación si no es la página de confirmación
            if (!window.location.pathname.includes('delete.php')) {
                const confirmed = confirm('¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.');
                
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    });
}

/**
 * Inicializa la validación de formularios en tiempo real
 */
function initFormValidation() {
    // Buscar todos los formularios
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Validar campos requeridos
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            // Validar al perder el foco
            field.addEventListener('blur', function() {
                validateField(field);
            });
            
            // Limpiar error al escribir
            field.addEventListener('input', function() {
                clearFieldError(field);
            });
        });
        
        // Validar al enviar el formulario
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, complete todos los campos requeridos correctamente.');
            }
        });
    });
}

/**
 * Valida un campo individual
 * @param {HTMLElement} field - Campo a validar
 * @returns {boolean} - True si es válido, false en caso contrario
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Validar campo requerido
    if (field.hasAttribute('required') && value === '') {
        isValid = false;
        errorMessage = 'Este campo es requerido';
    }
    
    // Validar email
    if (field.type === 'email' && value !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Ingrese un correo electrónico válido';
        }
    }
    
    // Mostrar u ocultar error
    if (!isValid) {
        showFieldError(field, errorMessage);
    } else {
        clearFieldError(field);
    }
    
    return isValid;
}

/**
 * Muestra un mensaje de error en un campo
 * @param {HTMLElement} field - Campo con error
 * @param {string} message - Mensaje de error
 */
function showFieldError(field, message) {
    // Limpiar error previo
    clearFieldError(field);
    
    // Agregar clase de error al campo
    field.classList.add('field-error');
    
    // Crear elemento de mensaje de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#c33';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '4px';
    
    // Insertar después del campo
    field.parentNode.insertBefore(errorDiv, field.nextSibling);
}

/**
 * Limpia el mensaje de error de un campo
 * @param {HTMLElement} field - Campo a limpiar
 */
function clearFieldError(field) {
    field.classList.remove('field-error');
    
    // Buscar y eliminar mensaje de error
    const errorMessage = field.parentNode.querySelector('.field-error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

/**
 * Inicializa el manejo de filtros con actualización dinámica
 */
function initFilterHandling() {
    const filterForm = document.querySelector('.filtros-form');
    
    if (!filterForm) return;
    
    // Agregar indicador de filtros activos
    const filterInputs = filterForm.querySelectorAll('input[type="text"], input[type="date"]');
    let activeFilters = 0;
    
    filterInputs.forEach(input => {
        if (input.value.trim() !== '') {
            activeFilters++;
        }
    });
    
    // Mostrar contador de filtros activos
    if (activeFilters > 0) {
        const filterTitle = document.querySelector('.filtros h2');
        if (filterTitle) {
            const badge = document.createElement('span');
            badge.className = 'filter-badge';
            badge.textContent = activeFilters;
            badge.style.cssText = `
                display: inline-block;
                background: #667eea;
                color: white;
                font-size: 12px;
                padding: 2px 8px;
                border-radius: 12px;
                margin-left: 8px;
            `;
            filterTitle.appendChild(badge);
        }
    }
    
    // Auto-submit al cambiar fechas (opcional)
    const dateInputs = filterForm.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Opcional: descomentar para auto-submit
            // filterForm.submit();
        });
    });
}

/**
 * Agrega estilos CSS dinámicos para campos con error
 */
(function addErrorStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .field-error {
            border-color: #c33 !important;
            box-shadow: 0 0 0 3px rgba(204, 51, 51, 0.1) !important;
        }
    `;
    document.head.appendChild(style);
})();
