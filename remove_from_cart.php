<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    
    if ($product_id > 0) {
        if (removeFromCart($pdo, $_SESSION['user_id'], $product_id)) {
            header('Location: cart.php?removed=1');
        } else {
            header('Location: cart.php?error=1');
        }
    } else {
        header('Location: cart.php?error=invalid');
    }
} else {
    header('Location: cart.php');
}
exit();
?>