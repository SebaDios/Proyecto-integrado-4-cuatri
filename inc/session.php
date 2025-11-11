<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'Admin';
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
