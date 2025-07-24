<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($product_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product && $product['stock'] >= $quantity) {
            if (addToCart($pdo, $_SESSION['user_id'], $product_id, $quantity)) {
                header('Location: cart.php?added=1');
            } else {
                header('Location: cart.php?error=1');
            }
        } else {
            header('Location: cart.php?error=stock');
        }
    } else {
        header('Location: cart.php?error=invalid');
    }
} else {
    header('Location: products.php');
}
exit();
?>