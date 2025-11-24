<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    // Comparación case-insensitive y sin espacios
    $role = trim($_SESSION['user_role']);
    return strtolower($role) === 'admin';
}

/**
 * Calcula la ruta relativa a un archivo desde la ubicación actual del script
 * @param string $target Archivo destino (ej: 'index.php' o 'views/dashboard.php')
 * @return string Ruta relativa calculada
 */
function getRelativePath($target) {
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    $script_dir = str_replace('\\', '/', $script_dir);
    
    // Contar niveles de profundidad (views/, controllers/, etc.)
    $depth = substr_count(trim($script_dir, '/'), '/');
    
    // Construir la ruta relativa
    if ($depth > 0) {
        return str_repeat('../', $depth) . $target;
    } else {
        return $target;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . getRelativePath('index.php'));
        exit();
    }
}

function requireAdmin() {
    // Primero verificar que esté logueado
    if (!isLoggedIn()) {
        header('Location: ' . getRelativePath('index.php'));
        exit();
    }
    
    // Luego verificar que sea admin
    if (!isAdmin()) {
        header('Location: ' . getRelativePath('views/dashboard.php'));
        exit();
    }
}

function regenerateSession() {
    session_regenerate_id(true);
}
?>
