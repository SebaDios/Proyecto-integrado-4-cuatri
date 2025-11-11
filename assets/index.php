<?php
/**
 * Punto de entrada principal del sistema
 * Redirige a login si no hay sesión, o a dashboard si ya está autenticado
 */

session_start();

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    header('Location: views/dashboard.php');
    exit();
}

// Si no está autenticado, redirigir al login
header('Location: views/login.php');
exit();

