<?php
require_once '../inc/session.php';
requireLogin(); // Proteger el acceso

// Redirigir al módulo de gestión de productos
header('Location: productos/index.php');
exit();
?>

