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
?>
