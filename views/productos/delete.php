<?php
require_once '../../inc/session.php';
requireAdmin();

require_once '../../models/product.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];

$productModel = new Product();

if ($productModel->delete($product_id)) {
    header('Location: index.php?msg=deleted');
} else {
    header('Location: index.php?msg=error');
}

exit();
?>

