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

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function regenerateSession() {
    session_regenerate_id(true);
}
?>
