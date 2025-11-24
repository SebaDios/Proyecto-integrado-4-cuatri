<?php
session_start();
session_unset();
session_destroy();

// Redirigir al index (login)
header('Location: ../index.php');
exit();
?>
