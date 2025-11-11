<?php
require_once '../../inc/session.php';
requireAdmin();

require_once '../../models/User.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_GET['id'];

// Evitar que el admin se elimine a sí mismo
if ($user_id == $_SESSION['user_id']) {
    header('Location: index.php?msg=error');
    exit();
}

$userModel = new User();

if ($userModel->delete($user_id)) {
    header('Location: index.php?msg=deleted');
} else {
    header('Location: index.php?msg=error');
}

exit();
?>
