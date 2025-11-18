<?php
// Función para sanitizar entrada de usuario
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para formatear precio
function format_price($price) {
    return '$' . number_format($price, 2);
}

// Función para formatear fecha
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Función para verificar permisos de modificación en registros
function canModifyRecord($record_user_id) {
    // El admin puede modificar cualquier registro
    if (isAdmin()) {
        return true;
    }
    
    // Un usuario solo puede modificar sus propios registros (si es permitido)
    return $_SESSION['user_id'] == $record_user_id;
}

// Función para obtener filtro SQL según rol
function getUserFilter($field_name = 'id_usuario') {
    if (isAdmin()) {
        // Admin ve todo, sin filtro
        return '';
    } else {
        // Usuario solo ve sus propios registros
        return " WHERE {$field_name} = " . $_SESSION['user_id'];
    }
}

// Función paginador de tablas (adaptada del diseño del proyecto)
function paginador_tablas($pagina, $Npaginas, $url, $botones = 5) {
    if ($Npaginas <= 1) {
        return ''; // No mostrar paginador si hay una página o menos
    }
    
    $html = '<nav style="display: flex; justify-content: center; align-items: center; gap: 10px; margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">';
    
    // Botón Anterior
    if ($pagina <= 1) {
        $html .= '<a style="padding: 8px 16px; background-color: #e9ecef; color: #6c757d; text-decoration: none; border-radius: 5px; cursor: not-allowed; pointer-events: none;">Anterior</a>';
    } else {
        $html .= '<a href="' . $url . ($pagina - 1) . '" style="padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'#0056b3\'" onmouseout="this.style.backgroundColor=\'#007bff\'">Anterior</a>';
    }
    
    // Lista de páginas
    $html .= '<div style="display: flex; gap: 5px; align-items: center;">';
    
    // Mostrar primera página si no está cerca
    if ($pagina > 3) {
        $html .= '<a href="' . $url . '1" style="padding: 8px 12px; background-color: white; color: #007bff; text-decoration: none; border: 1px solid #dee2e6; border-radius: 5px;">1</a>';
        if ($pagina > 4) {
            $html .= '<span style="padding: 8px 4px; color: #6c757d;">...</span>';
        }
    }
    
    // Mostrar páginas alrededor de la actual
    $inicio = max(1, $pagina - 2);
    $fin = min($Npaginas, $pagina + 2);
    
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($pagina == $i) {
            $html .= '<a style="padding: 8px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">' . $i . '</a>';
        } else {
            $html .= '<a href="' . $url . $i . '" style="padding: 8px 12px; background-color: white; color: #007bff; text-decoration: none; border: 1px solid #dee2e6; border-radius: 5px; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'#e9ecef\'" onmouseout="this.style.backgroundColor=\'white\'">' . $i . '</a>';
        }
    }
    
    // Mostrar última página si no está cerca
    if ($pagina < $Npaginas - 2) {
        if ($pagina < $Npaginas - 3) {
            $html .= '<span style="padding: 8px 4px; color: #6c757d;">...</span>';
        }
        $html .= '<a href="' . $url . $Npaginas . '" style="padding: 8px 12px; background-color: white; color: #007bff; text-decoration: none; border: 1px solid #dee2e6; border-radius: 5px;">' . $Npaginas . '</a>';
    }
    
    $html .= '</div>';
    
    // Botón Siguiente
    if ($pagina >= $Npaginas) {
        $html .= '<a style="padding: 8px 16px; background-color: #e9ecef; color: #6c757d; text-decoration: none; border-radius: 5px; cursor: not-allowed; pointer-events: none;">Siguiente</a>';
    } else {
        $html .= '<a href="' . $url . ($pagina + 1) . '" style="padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor=\'#0056b3\'" onmouseout="this.style.backgroundColor=\'#007bff\'">Siguiente</a>';
    }
    
    $html .= '</nav>';
    return $html;
}
?>
