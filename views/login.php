<?php
// Redirigir al index.php (nuevo punto de entrada del login)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
require_once BASE_PATH . '/inc/session.php';

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Si no está autenticado, redirigir al index
header('Location: ../index.php');
exit();
?>
