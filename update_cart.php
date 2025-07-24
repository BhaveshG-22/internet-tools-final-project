<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($product_id > 0 && $quantity > 0) {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product && $product['stock'] >= $quantity) {
            try {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
                header('Location: cart.php?updated=1');
            } catch(PDOException $e) {
                header('Location: cart.php?error=1');
            }
        } else {
            header('Location: cart.php?error=stock');
        }
    } else {
        header('Location: cart.php?error=invalid');
    }
} else {
    header('Location: cart.php');
}
exit();
?>